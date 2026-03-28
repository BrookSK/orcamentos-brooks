<?php
require __DIR__ . '/app/bootstrap.php';

echo "=== TESTE DE BUSCA BETONEIRA ===\n\n";

try {
    $pdo = \App\Core\Database::pdo();
    
    // Teste 1: Verificar se a tabela existe
    echo "1. Verificando se a tabela sinapi_insumos existe...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'sinapi_insumos'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        echo "   ❌ ERRO: Tabela sinapi_insumos NÃO EXISTE!\n";
        echo "   Execute a migração: database/migration_014_create_sinapi_insumos.sql\n";
        exit(1);
    }
    echo "   ✓ Tabela existe\n\n";
    
    // Teste 2: Contar registros
    echo "2. Contando registros na tabela...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sinapi_insumos");
    $result = $stmt->fetch();
    echo "   Total de registros: " . $result['total'] . "\n\n";
    
    if ($result['total'] == 0) {
        echo "   ❌ ERRO: Tabela está VAZIA!\n";
        echo "   Execute a migração: database/migration_014_create_sinapi_insumos.sql\n";
        exit(1);
    }
    
    // Teste 3: Buscar código 10535
    echo "3. Buscando código 10535 (Betoneira UN)...\n";
    $stmt = $pdo->prepare("SELECT * FROM sinapi_insumos WHERE codigo = '10535'");
    $stmt->execute();
    $betoneira = $stmt->fetch();
    
    if ($betoneira) {
        echo "   ✓ Encontrado!\n";
        echo "   Código: " . $betoneira['codigo'] . "\n";
        echo "   Descrição: " . substr($betoneira['descricao'], 0, 60) . "...\n";
        echo "   Unidade: " . $betoneira['unidade'] . "\n";
        echo "   Preço: R$ " . number_format($betoneira['preco_unit'], 2, ',', '.') . "\n";
        echo "   UF: " . $betoneira['uf'] . "\n\n";
    } else {
        echo "   ❌ NÃO ENCONTRADO!\n\n";
    }
    
    // Teste 4: Buscar por descrição "BETONEIRA"
    echo "4. Buscando por descrição contendo 'BETONEIRA'...\n";
    $stmt = $pdo->prepare("SELECT codigo, descricao, unidade, preco_unit FROM sinapi_insumos WHERE descricao LIKE '%BETONEIRA%' LIMIT 10");
    $stmt->execute();
    $resultados = $stmt->fetchAll();
    
    echo "   Encontrados: " . count($resultados) . " registros\n";
    foreach ($resultados as $item) {
        echo "   - " . $item['codigo'] . ": " . substr($item['descricao'], 0, 50) . "... (" . $item['unidade'] . ") - R$ " . $item['preco_unit'] . "\n";
    }
    echo "\n";
    
    // Teste 5: Buscar betoneira com CHP
    echo "5. Buscando BETONEIRA com unidade CHP...\n";
    $stmt = $pdo->prepare("SELECT codigo, descricao, unidade, preco_unit FROM sinapi_insumos WHERE descricao LIKE '%BETONEIRA%' AND unidade = 'CHP' LIMIT 5");
    $stmt->execute();
    $resultados = $stmt->fetchAll();
    
    echo "   Encontrados: " . count($resultados) . " registros\n";
    foreach ($resultados as $item) {
        echo "   - " . $item['codigo'] . ": " . substr($item['descricao'], 0, 50) . "... (" . $item['unidade'] . ") - R$ " . $item['preco_unit'] . "\n";
    }
    echo "\n";
    
    // Teste 6: Testar a API
    echo "6. Testando API listarInsumos...\n";
    $insumos = \App\Api\SinapiPrecosApi::listarInsumos('BETONEIRA', 'SP', 10);
    echo "   Retornados pela API: " . count($insumos) . " registros\n";
    foreach ($insumos as $item) {
        echo "   - " . $item['codigo'] . ": " . substr($item['descricao'], 0, 50) . "... (" . $item['unidade'] . ") - R$ " . $item['preco_unit'] . "\n";
    }
    
    echo "\n=== FIM DO TESTE ===\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
}
