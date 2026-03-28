<?php
require __DIR__ . '/../app/bootstrap.php';

set_time_limit(300); // 5 minutos

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Instalar SINAPI</title></head><body>";
echo "<h1>Instalação da Tabela SINAPI</h1>";
echo "<pre>";

try {
    $pdo = \App\Core\Database::pdo();
    
    echo "Conectado ao banco de dados...\n\n";
    
    // Ler o arquivo SQL
    $sqlFile = __DIR__ . '/../database/migration_014_create_sinapi_insumos.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Arquivo não encontrado: $sqlFile");
    }
    
    echo "Lendo arquivo SQL...\n";
    $sql = file_get_contents($sqlFile);
    
    echo "Tamanho do arquivo: " . strlen($sql) . " bytes\n\n";
    
    // Executar o SQL
    echo "Executando SQL...\n";
    echo "Isso pode levar alguns minutos...\n\n";
    
    $pdo->exec($sql);
    
    echo "✓ SQL executado com sucesso!\n\n";
    
    // Verificar quantos registros foram inseridos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sinapi_insumos");
    $result = $stmt->fetch();
    
    echo "Total de registros na tabela: " . $result['total'] . "\n\n";
    
    // Testar busca por BETONEIRA
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM sinapi_insumos WHERE descricao LIKE '%BETONEIRA%'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    echo "Registros com 'BETONEIRA': " . $result['total'] . "\n\n";
    
    echo "✓✓✓ INSTALAÇÃO CONCLUÍDA COM SUCESSO! ✓✓✓\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
}

echo "</pre></body></html>";
