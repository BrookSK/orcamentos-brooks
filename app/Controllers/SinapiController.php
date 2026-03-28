<?php

declare(strict_types=1);

namespace App\Controllers;

class SinapiController
{
    public function diagnostico(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $pdo = \App\Core\Database::pdo();
            
            // Contar total de registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM sinapi_insumos");
            $total = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Contar betoneiras
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM sinapi_insumos WHERE descricao LIKE '%BETONEIRA%'");
            $betoneiras = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Buscar código 10535
            $stmt = $pdo->prepare("SELECT * FROM sinapi_insumos WHERE codigo = '10535'");
            $stmt->execute();
            $cod10535 = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Testar query direta
            $stmt = $pdo->prepare("SELECT codigo, descricao, unidade FROM sinapi_insumos WHERE descricao LIKE :termo LIMIT 5");
            $stmt->execute([':termo' => '%BETONEIRA%']);
            $queryDireta = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Testar query igual à API
            $sql = "SELECT codigo, descricao, unidade, tipo, preco_unit, uf, referencia, regime 
                    FROM sinapi_insumos 
                    WHERE 1=1 AND (codigo LIKE :termo OR descricao LIKE :termo)
                    ORDER BY codigo LIMIT :limite";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':termo', '%BETONEIRA%');
            $stmt->bindValue(':limite', 10, \PDO::PARAM_INT);
            $stmt->execute();
            $queryIgualAPI = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Testar API listarInsumos
            $resultadosAPI = \App\Api\SinapiPrecosApi::listarInsumos('BETONEIRA', 'SP', 10);
            
            echo json_encode([
                'success' => true,
                'total_registros' => $total['total'],
                'total_betoneiras' => $betoneiras['total'],
                'codigo_10535' => $cod10535 ? 'encontrado' : 'nao_encontrado',
                'codigo_10535_uf' => $cod10535['uf'] ?? null,
                'query_direta_count' => count($queryDireta),
                'query_direta_sample' => $queryDireta,
                'query_igual_api_count' => count($queryIgualAPI),
                'query_igual_api_sample' => array_slice($queryIgualAPI, 0, 3),
                'api_listar_count' => count($resultadosAPI),
                'api_listar_sample' => array_slice($resultadosAPI, 0, 3)
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        exit;
    }
    
    public function instalar(): void
    {
        set_time_limit(300); // 5 minutos
        ini_set('memory_limit', '512M');

        header('Content-Type: text/html; charset=utf-8');

        echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Instalar SINAPI</title>";
        echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;}pre{background:#252526;padding:15px;border-radius:5px;}</style>";
        echo "</head><body>";
        echo "<h1 style='color:#4ec9b0;'>Instalação da Tabela SINAPI</h1>";
        echo "<pre>";

        try {
            $pdo = \App\Core\Database::pdo();
            
            echo "✓ Conectado ao banco de dados...\n\n";
            
            // Ler o arquivo SQL
            $sqlFile = __DIR__ . '/../../database/migration_014_create_sinapi_insumos.sql';
            
            if (!file_exists($sqlFile)) {
                throw new \Exception("Arquivo não encontrado: $sqlFile");
            }
            
            echo "✓ Arquivo SQL encontrado\n";
            $sql = file_get_contents($sqlFile);
            
            $tamanho = strlen($sql);
            echo "✓ Tamanho do arquivo: " . number_format($tamanho / 1024, 2) . " KB\n\n";
            
            echo "Executando SQL...\n";
            echo "Aguarde, isso pode levar alguns minutos...\n\n";
            
            flush();
            if (ob_get_level() > 0) ob_flush();
            
            // Dividir em statements individuais e executar
            $statements = explode(';', $sql);
            $executed = 0;
            $errors = 0;
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement) || substr($statement, 0, 2) === '--') {
                    continue;
                }
                
                try {
                    $pdo->exec($statement);
                    $executed++;
                    
                    if ($executed % 100 === 0) {
                        echo "  Executados: $executed statements...\n";
                        flush();
                        if (ob_get_level() > 0) ob_flush();
                    }
                } catch (\Exception $e) {
                    $errors++;
                    if ($errors < 5) {
                        echo "  ⚠ Erro: " . $e->getMessage() . "\n";
                    }
                }
            }
            
            echo "\n✓ SQL executado!\n";
            echo "  Statements executados: $executed\n";
            echo "  Erros: $errors\n\n";
            
            // Verificar quantos registros foram inseridos
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM sinapi_insumos");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            echo "✓ Total de registros na tabela: " . $result['total'] . "\n\n";
            
            if ($result['total'] == 0) {
                echo "❌ ERRO: Nenhum registro foi inserido!\n";
                echo "Verifique se a tabela já existe e tem dados.\n\n";
            }
            
            // Testar busca por BETONEIRA
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM sinapi_insumos WHERE descricao LIKE '%BETONEIRA%'");
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            echo "✓ Registros com 'BETONEIRA': " . $result['total'] . "\n\n";
            
            // Mostrar alguns exemplos
            echo "Exemplos de registros:\n";
            $stmt = $pdo->query("SELECT codigo, descricao, unidade, preco_unit FROM sinapi_insumos WHERE descricao LIKE '%BETONEIRA%' LIMIT 5");
            $exemplos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($exemplos as $ex) {
                echo "  - " . $ex['codigo'] . ": " . substr($ex['descricao'], 0, 60) . "... (" . $ex['unidade'] . ") R$ " . $ex['preco_unit'] . "\n";
            }
            
            echo "\n";
            echo "═══════════════════════════════════════════════════════\n";
            echo "✓✓✓ INSTALAÇÃO CONCLUÍDA COM SUCESSO! ✓✓✓\n";
            echo "═══════════════════════════════════════════════════════\n";
            echo "\nAgora você pode fechar esta página e testar a busca.\n";
            
        } catch (\Exception $e) {
            echo "\n";
            echo "═══════════════════════════════════════════════════════\n";
            echo "❌ ERRO NA INSTALAÇÃO\n";
            echo "═══════════════════════════════════════════════════════\n";
            echo "Mensagem: " . $e->getMessage() . "\n";
            echo "Arquivo: " . $e->getFile() . "\n";
            echo "Linha: " . $e->getLine() . "\n\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }

        echo "</pre></body></html>";
        exit;
    }
}
