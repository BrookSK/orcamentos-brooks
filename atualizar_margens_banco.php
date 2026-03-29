<?php
// Script para atualizar margens de orçamentos manuais no banco de dados

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Database;

echo "=== ATUALIZANDO MARGENS DOS ORÇAMENTOS ===\n\n";

$pdo = Database::pdo();

// Verificar orçamentos antes
echo "📊 Orçamentos ANTES da atualização:\n";
$stmt = $pdo->query("SELECT id, numero_proposta, tipo_orcamento, margem_mao_obra, margem_materiais, margem_equipamentos FROM orcamentos ORDER BY id DESC LIMIT 5");
$antes = $stmt->fetchAll();
foreach ($antes as $orc) {
    echo "   ID {$orc['id']} - {$orc['numero_proposta']} (tipo: {$orc['tipo_orcamento']})\n";
    echo "      Mão de Obra: " . ($orc['margem_mao_obra'] ?? 'NULL') . "% | ";
    echo "Materiais: " . ($orc['margem_materiais'] ?? 'NULL') . "% | ";
    echo "Equipamentos: " . ($orc['margem_equipamentos'] ?? 'NULL') . "%\n";
}

echo "\n🔄 Atualizando margens...\n\n";

// Atualizar margem_mao_obra
$stmt = $pdo->prepare("UPDATE orcamentos SET margem_mao_obra = 50.00 WHERE (margem_mao_obra IS NULL OR margem_mao_obra = 0) AND tipo_orcamento = 'manual'");
$stmt->execute();
$count1 = $stmt->rowCount();
echo "   ✓ Margem Mão de Obra: $count1 orçamentos atualizados para 50%\n";

// Atualizar margem_materiais
$stmt = $pdo->prepare("UPDATE orcamentos SET margem_materiais = 20.00 WHERE (margem_materiais IS NULL OR margem_materiais = 0) AND tipo_orcamento = 'manual'");
$stmt->execute();
$count2 = $stmt->rowCount();
echo "   ✓ Margem Materiais: $count2 orçamentos atualizados para 20%\n";

// Atualizar margem_equipamentos
$stmt = $pdo->prepare("UPDATE orcamentos SET margem_equipamentos = 20.00 WHERE (margem_equipamentos IS NULL OR margem_equipamentos = 0) AND tipo_orcamento = 'manual'");
$stmt->execute();
$count3 = $stmt->rowCount();
echo "   ✓ Margem Equipamentos: $count3 orçamentos atualizados para 20%\n";

echo "\n📊 Orçamentos DEPOIS da atualização:\n";
$stmt = $pdo->query("SELECT id, numero_proposta, tipo_orcamento, margem_mao_obra, margem_materiais, margem_equipamentos FROM orcamentos ORDER BY id DESC LIMIT 5");
$depois = $stmt->fetchAll();
foreach ($depois as $orc) {
    echo "   ID {$orc['id']} - {$orc['numero_proposta']} (tipo: {$orc['tipo_orcamento']})\n";
    echo "      Mão de Obra: " . ($orc['margem_mao_obra'] ?? 'NULL') . "% | ";
    echo "Materiais: " . ($orc['margem_materiais'] ?? 'NULL') . "% | ";
    echo "Equipamentos: " . ($orc['margem_equipamentos'] ?? 'NULL') . "%\n";
}

echo "\n=== CONCLUÍDO ===\n";
echo "\nAgora clique no botão '🔄 Recalcular Margens' na página do orçamento para atualizar os valores dos itens.\n";
