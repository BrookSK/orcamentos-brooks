<?php
require __DIR__ . '/../app/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');

$orcamentoId = 64;

$pdo = \App\Core\Database::pdo();

// Buscar orçamento
$stmt = $pdo->prepare('SELECT * FROM orcamentos WHERE id = :id');
$stmt->execute([':id' => $orcamentoId]);
$orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orcamento) {
    die("Orçamento $orcamentoId não encontrado");
}

echo "<h1>Teste de Geração de PDF - Orçamento $orcamentoId</h1>";

// Buscar itens como aparecem na tela de edição
$itens = \App\Models\OrcamentoItem::allByOrcamento($orcamentoId);

echo "<h2>Total de itens: " . count($itens) . "</h2>";

// Agrupar EXATAMENTE como na tela de edição
$grouped = [];
foreach ($itens as $it) {
    $grupo = (string)($it['grupo'] ?? '');
    $categoria = (string)($it['categoria'] ?? '');
    $grouped[$grupo][$categoria][] = $it;
}

echo "<h2>Estrutura de Agrupamento (igual à tela de edição):</h2>";

echo "<table border='1' cellpadding='8' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#333;color:white;'>";
echo "<th>GRUPO</th><th>CATEGORIA</th><th>Total Itens</th>";
echo "</tr>";

foreach ($grouped as $grupo => $categorias) {
    $totalItensGrupo = 0;
    foreach ($categorias as $categoria => $itens) {
        $totalItensGrupo += count($itens);
    }
    
    echo "<tr style='background:#1e3a5f;color:white;font-weight:bold;'>";
    echo "<td colspan='3'>▼ GRUPO: " . htmlspecialchars($grupo) . " ($totalItensGrupo itens)</td>";
    echo "</tr>";
    
    foreach ($categorias as $categoria => $itens) {
        echo "<tr>";
        echo "<td></td>";
        echo "<td>" . htmlspecialchars($categoria) . "</td>";
        echo "<td style='text-align:center;'>" . count($itens) . "</td>";
        echo "</tr>";
    }
}

echo "</table>";

echo "<hr>";

echo "<h2>Teste de Geração do PDF</h2>";

try {
    // Tentar gerar o HTML do PDF
    $html = \App\Helpers\OrcamentoPDF::gerarHTML($orcamentoId, $orcamento);
    
    echo "<div style='background:#e8f5e9;border:2px solid #4caf50;padding:20px;margin:20px 0;'>";
    echo "<h3 style='color:#4caf50;'>✓ PDF gerado com sucesso!</h3>";
    echo "<p>Tamanho do HTML: " . number_format(strlen($html)) . " bytes</p>";
    echo "<p><a href='/?route=orcamentos/pdf&id=$orcamentoId' target='_blank' style='background:#4caf50;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;'>📄 Abrir PDF</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#ffebee;border:2px solid #f44336;padding:20px;margin:20px 0;'>";
    echo "<h3 style='color:#f44336;'>⚠️ Erro ao gerar PDF</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
