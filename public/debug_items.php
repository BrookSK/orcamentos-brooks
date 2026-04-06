<?php
require __DIR__ . '/../app/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');

$orcamentoId = (int)($_GET['id'] ?? 64);

$pdo = \App\Core\Database::pdo();

echo "=== DIAGNÓSTICO DE ITENS DO ORÇAMENTO $orcamentoId ===\n\n";

// Verificar se o orçamento existe
$stmt = $pdo->prepare('SELECT * FROM orcamentos WHERE id = :id');
$stmt->execute([':id' => $orcamentoId]);
$orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orcamento) {
    echo "ERRO: Orçamento $orcamentoId não encontrado!\n";
    exit;
}

echo "Orçamento encontrado: {$orcamento['numero_proposta']} - {$orcamento['cliente_nome']}\n\n";

// Buscar todos os itens
$stmt = $pdo->prepare('SELECT * FROM orcamento_itens WHERE orcamento_id = :id ORDER BY ordem, id');
$stmt->execute([':id' => $orcamentoId]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total de itens encontrados: " . count($itens) . "\n\n";

if (empty($itens)) {
    echo "PROBLEMA: Nenhum item encontrado no banco de dados!\n";
    echo "Os itens podem não ter sido salvos após arrastar.\n";
    exit;
}

// Analisar cada item
foreach ($itens as $item) {
    echo "Item ID: {$item['id']}\n";
    echo "  Código: {$item['codigo']}\n";
    echo "  Descrição: " . substr($item['descricao'], 0, 50) . "...\n";
    echo "  Grupo: {$item['grupo']}\n";
    echo "  Categoria: {$item['categoria']}\n";
    echo "  Quantidade: {$item['quantidade']}\n";
    echo "  Valor Unitário: {$item['valor_unitario']}\n";
    echo "  Valor Cobrança: " . ($item['valor_cobranca'] ?? 'NULL') . "\n";
    echo "  Valor Total: " . ($item['quantidade'] * ($item['valor_cobranca'] ?? 0)) . "\n";
    echo "  Etapa: " . ($item['etapa'] ?? 'NULL') . "\n";
    echo "\n";
}

// Testar a query usada no PDF
echo "\n=== TESTANDO QUERY DO PDF ===\n\n";
$stmt = $pdo->prepare(
    'SELECT codigo, descricao, grupo, categoria, (quantidade * valor_cobranca) as valor_total '
    . 'FROM orcamento_itens WHERE orcamento_id = :id '
    . 'ORDER BY CAST(SUBSTRING_INDEX(codigo, \'.\', 1) AS UNSIGNED), CAST(SUBSTRING_INDEX(codigo, \'.\', -1) AS UNSIGNED)'
);
$stmt->execute([':id' => $orcamentoId]);
$itensAgrupados = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Itens retornados pela query do PDF: " . count($itensAgrupados) . "\n\n";

if (empty($itensAgrupados)) {
    echo "PROBLEMA: A query do PDF não retornou nenhum item!\n";
    echo "Isso pode acontecer se valor_cobranca estiver NULL.\n";
} else {
    foreach ($itensAgrupados as $item) {
        echo "  {$item['codigo']} - {$item['categoria']} - R$ " . number_format($item['valor_total'], 2, ',', '.') . "\n";
    }
}
