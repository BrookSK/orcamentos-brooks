<?php
// Script para verificar margens de um orçamento específico

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Database;

$orcamentoId = isset($argv[1]) ? (int)$argv[1] : 40;

echo "=== VERIFICANDO ORÇAMENTO ID: $orcamentoId ===\n\n";

$pdo = Database::pdo();

// Buscar orçamento
$stmt = $pdo->prepare('SELECT id, numero_proposta, tipo_orcamento, margem_mao_obra, margem_materiais, margem_equipamentos FROM orcamentos WHERE id = :id');
$stmt->execute([':id' => $orcamentoId]);
$orc = $stmt->fetch();

if (!$orc) {
    echo "❌ Orçamento não encontrado!\n";
    exit(1);
}

echo "📋 Orçamento: " . $orc['numero_proposta'] . "\n";
echo "   Tipo: " . ($orc['tipo_orcamento'] ?? 'NULL') . "\n";
echo "   Margem Mão de Obra: " . ($orc['margem_mao_obra'] ?? 'NULL') . "%\n";
echo "   Margem Materiais: " . ($orc['margem_materiais'] ?? 'NULL') . "%\n";
echo "   Margem Equipamentos: " . ($orc['margem_equipamentos'] ?? 'NULL') . "%\n\n";

// Buscar alguns itens
$stmt = $pdo->prepare('SELECT id, codigo, descricao, categoria, valor_unitario, valor_cobranca, usa_margem_personalizada, margem_personalizada FROM orcamento_itens WHERE orcamento_id = :id LIMIT 5');
$stmt->execute([':id' => $orcamentoId]);
$itens = $stmt->fetchAll();

echo "📦 Primeiros 5 itens:\n\n";
foreach ($itens as $item) {
    echo "   Item #" . $item['id'] . " - " . substr($item['descricao'], 0, 40) . "...\n";
    echo "   Categoria: " . $item['categoria'] . "\n";
    echo "   Valor Unitário: R$ " . number_format((float)$item['valor_unitario'], 2, ',', '.') . "\n";
    echo "   Valor Cobrança: R$ " . number_format((float)$item['valor_cobranca'], 2, ',', '.') . "\n";
    echo "   Usa Margem Personalizada: " . ($item['usa_margem_personalizada'] ?? '0') . "\n";
    echo "   Margem Personalizada: " . ($item['margem_personalizada'] ?? '0') . "%\n";
    
    $valorUnit = (float)$item['valor_unitario'];
    $valorCob = (float)$item['valor_cobranca'];
    if ($valorUnit > 0 && $valorCob > $valorUnit) {
        $margemAplicada = (($valorCob / $valorUnit) - 1) * 100;
        echo "   Margem Aplicada (calculada): " . number_format($margemAplicada, 2, ',', '.') . "%\n";
    } else {
        echo "   Margem Aplicada: 0% (valor_cobranca = valor_unitario)\n";
    }
    echo "\n";
}

echo "=== FIM ===\n";
