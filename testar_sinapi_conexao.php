<?php
/**
 * Script de teste para verificar a tabela SINAPI
 */

require_once __DIR__ . '/app/bootstrap.php';

echo "=== TESTE DE CONEXÃO SINAPI ===\n\n";

try {
    $pdo = \App\Core\Database::pdo();
    
    // 1. Verificar se a tabela existe
    echo "1. Verificando se a tabela existe...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'sinapi_insumos'");
    $existe = $stmt->fetch();
    
    if (!$existe) {
        echo "❌ ERRO: Tabela 'sinapi_insumos' não existe!\n";
        echo "Execute a migration: mysql -u USER -p DATABASE < database/migration_014_create_sinapi_insumos.sql\n";
        exit(1);
    }
    echo "✓ Tabela existe\n\n";
    
    // 2. Contar registros
    echo "2. Contando registros...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sinapi_insumos WHERE uf='SP'");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Total de registros: " . $total['total'] . "\n\n";
    
    if ($total['total'] == 0) {
        echo "❌ ERRO: Tabela vazia! Execute a migration.\n";
        exit(1);
    }
    
    // 3. Testar códigos específicos
    echo "3. Testando códigos SINAPI específicos...\n";
    $codigos = ['1379', '1106', '370', '367', '88316', '88309'];
    
    foreach ($codigos as $codigo) {
        $stmt = $pdo->prepare("SELECT codigo, descricao, unidade, preco_unit FROM sinapi_insumos WHERE codigo = ? AND uf = 'SP'");
        $stmt->execute([$codigo]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado) {
            echo "✓ {$codigo}: {$resultado['descricao']} - R$ {$resultado['preco_unit']}/{$resultado['unidade']}\n";
        } else {
            echo "❌ {$codigo}: NÃO ENCONTRADO\n";
        }
    }
    
    echo "\n4. Testando API...\n";
    $precos = \App\Api\SinapiPrecosApi::buscarMultiplosPrecos(['1379', '1106'], 'SP');
    echo "✓ API retornou " . count($precos) . " preços\n";
    print_r($precos);
    
    echo "\n=== TESTE CONCLUÍDO COM SUCESSO ===\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
