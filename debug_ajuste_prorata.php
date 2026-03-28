<?php
// Debug do ajuste pro rata de materiais

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Database;
use App\Models\Orcamento;

$orcamentoId = 29; // Altere para o ID do seu orçamento

// Buscar orçamento
$orcamento = Orcamento::find($orcamentoId);

echo "=== DEBUG AJUSTE PRO RATA ===\n\n";
echo "Orçamento ID: " . $orcamento['id'] . "\n";
echo "Número Proposta: " . $orcamento['numero_proposta'] . "\n";
echo "Ajuste Pro Rata Materiais: " . ($orcamento['ajuste_prorata_materiais'] ?? 'NULL') . "\n";
echo "Margem Materiais: " . ($orcamento['margem_materiais'] ?? 'NULL') . "\n";
echo "Margem Mão de Obra: " . ($orcamento['margem_mao_obra'] ?? 'NULL') . "\n";
echo "Margem Equipamentos: " . ($orcamento['margem_equipamentos'] ?? 'NULL') . "\n\n";

// Buscar um item de exemplo
$pdo = Database::pdo();
$stmt = $pdo->prepare("SELECT * FROM orcamento_itens WHERE orcamento_id = ? AND custo_material > 0 LIMIT 1");
$stmt->execute([$orcamentoId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($item) {
    echo "=== ITEM DE EXEMPLO ===\n";
    echo "ID: " . $item['id'] . "\n";
    echo "Código: " . $item['codigo'] . "\n";
    echo "Descrição: " . substr($item['descricao'], 0, 50) . "...\n";
    echo "Quantidade: " . $item['quantidade'] . "\n";
    echo "Custo Material (banco): " . $item['custo_material'] . "\n";
    echo "Custo Mão de Obra (banco): " . $item['custo_mao_obra'] . "\n";
    echo "Custo Equipamento (banco): " . $item['custo_equipamento'] . "\n";
    echo "Valor Unitário: " . $item['valor_unitario'] . "\n";
    echo "Valor Cobrança: " . $item['valor_cobranca'] . "\n\n";
    
    // Simular cálculo
    $quantidade = (float)$item['quantidade'];
    $custoMaterialTotal = (float)$item['custo_material'];
    $valorUnitario = (float)$item['valor_unitario'];
    
    echo "=== SIMULAÇÃO DE CÁLCULO ===\n";
    
    // Detectar se é unitário ou total
    $testeDivisao = $custoMaterialTotal / $quantidade;
    echo "Teste divisão: {$custoMaterialTotal} / {$quantidade} = {$testeDivisao}\n";
    echo "10% do valor unitário: " . ($valorUnitario * 0.1) . "\n";
    
    if ($testeDivisao < ($valorUnitario * 0.1) && $custoMaterialTotal < $valorUnitario) {
        echo "Decisão: Custo JÁ É UNITÁRIO\n";
        $custoMaterialUnit = $custoMaterialTotal;
    } else {
        echo "Decisão: Custo É TOTAL, dividir pela quantidade\n";
        $custoMaterialUnit = $testeDivisao;
    }
    
    echo "Custo Material Unitário (antes do ajuste): R$ " . number_format($custoMaterialUnit, 2, ',', '.') . "\n";
    
    // Aplicar ajuste pro rata
    $ajusteProRata = (float)($orcamento['ajuste_prorata_materiais'] ?? 0);
    echo "Ajuste Pro Rata: {$ajusteProRata}%\n";
    
    if ($ajusteProRata > 0) {
        $custoMaterialUnitComAjuste = $custoMaterialUnit * (1 + ($ajusteProRata / 100));
        echo "Custo Material Unitário (DEPOIS do ajuste): R$ " . number_format($custoMaterialUnitComAjuste, 2, ',', '.') . "\n";
        echo "Diferença: R$ " . number_format($custoMaterialUnitComAjuste - $custoMaterialUnit, 2, ',', '.') . "\n";
    } else {
        echo "Ajuste Pro Rata = 0, nenhum ajuste aplicado\n";
    }
} else {
    echo "Nenhum item encontrado com custo_material > 0\n";
}
