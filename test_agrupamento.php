<?php
require_once __DIR__ . '/app/bootstrap.php';

$orcamentoId = 40;
$itens = \App\Models\OrcamentoItem::allByOrcamento($orcamentoId);

echo "Total de itens: " . count($itens) . "\n\n";

// Agrupar por GRUPO e depois por CATEGORIA
$grouped = [];
foreach ($itens as $item) {
    $grupo = (string)($item['grupo'] ?? 'SEM GRUPO');
    $categoria = (string)($item['categoria'] ?? 'SEM CATEGORIA');
    
    if (!isset($grouped[$grupo])) {
        $grouped[$grupo] = [];
    }
    if (!isset($grouped[$grupo][$categoria])) {
        $grouped[$grupo][$categoria] = [];
    }
    
    $grouped[$grupo][$categoria][] = $item;
}

echo "ESTRUTURA:\n";
echo "==========\n\n";

foreach ($grouped as $nomeGrupo => $categorias) {
    echo "GRUPO: " . $nomeGrupo . "\n";
    foreach ($categorias as $nomeCategoria => $itensCategoria) {
        echo "  └─ CATEGORIA: " . $nomeCategoria . " (" . count($itensCategoria) . " itens)\n";
    }
    echo "\n";
}

echo "\nTotal de grupos: " . count($grouped) . "\n";
