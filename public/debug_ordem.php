<?php
require __DIR__ . '/../app/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');

$orcamentoId = (int)($_GET['id'] ?? 64);

$pdo = \App\Core\Database::pdo();

echo "<h1>DIAGNÓSTICO DE ORDEM - Orçamento $orcamentoId</h1>";

// Buscar itens como aparecem na tela (ORDER BY ordem, id)
$stmt = $pdo->prepare('SELECT id, codigo, grupo, categoria, descricao, ordem FROM orcamento_itens WHERE orcamento_id = :id ORDER BY ordem, id');
$stmt->execute([':id' => $orcamentoId]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Total de itens: " . count($itens) . "</h2>";

echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
echo "<tr style='background:#333;color:white;'>";
echo "<th>ID</th><th>Código</th><th>Ordem</th><th>Grupo</th><th>Categoria</th><th>Descrição</th>";
echo "</tr>";

$grupoAtual = null;
foreach ($itens as $item) {
    $grupo = $item['grupo'];
    
    // Destacar quando muda de grupo
    if ($grupo !== $grupoAtual) {
        echo "<tr style='background:#4FC3F7;color:black;font-weight:bold;'>";
        echo "<td colspan='6'>▼ GRUPO: " . htmlspecialchars($grupo) . "</td>";
        echo "</tr>";
        $grupoAtual = $grupo;
    }
    
    echo "<tr>";
    echo "<td>" . $item['id'] . "</td>";
    echo "<td>" . htmlspecialchars($item['codigo']) . "</td>";
    echo "<td>" . $item['ordem'] . "</td>";
    echo "<td>" . htmlspecialchars($item['grupo']) . "</td>";
    echo "<td>" . htmlspecialchars($item['categoria']) . "</td>";
    echo "<td>" . htmlspecialchars(substr($item['descricao'], 0, 50)) . "...</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h2>Grupos Únicos Encontrados:</h2>";
$stmt = $pdo->prepare('SELECT DISTINCT grupo FROM orcamento_itens WHERE orcamento_id = :id ORDER BY ordem, id');
$stmt->execute([':id' => $orcamentoId]);
$grupos = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "<ul>";
foreach ($grupos as $grupo) {
    echo "<li>" . htmlspecialchars($grupo) . "</li>";
}
echo "</ul>";
