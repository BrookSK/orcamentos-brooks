<?php
require_once __DIR__ . '/app/bootstrap.php';

use App\Models\Orcamento;

$orcamento = Orcamento::find(29);

echo "Orçamento ID: " . $orcamento['id'] . "\n";
echo "Ajuste Pro Rata (raw): ";
var_dump($orcamento['ajuste_prorata_materiais']);
echo "\n";

$ajuste = (float)($orcamento['ajuste_prorata_materiais'] ?? 0);
echo "Ajuste Pro Rata (float): {$ajuste}\n";
echo "Ajuste > 0? " . ($ajuste > 0 ? 'SIM' : 'NÃO') . "\n\n";

// Teste de cálculo
$custoMaterial = 0.78;
$quantidade = 76.80;
$valorUnitario = 5.00;

echo "Teste de cálculo:\n";
echo "Custo Material: {$custoMaterial}\n";
echo "Quantidade: {$quantidade}\n";
echo "Valor Unitário: {$valorUnitario}\n\n";

$testeDivisao = $custoMaterial / $quantidade;
echo "Teste divisão: {$testeDivisao}\n";

$custoMaterialUnit = $testeDivisao;
echo "Custo Material Unitário (antes): {$custoMaterialUnit}\n";

if ($ajuste > 0) {
    $custoMaterialUnitComAjuste = $custoMaterialUnit * (1 + ($ajuste / 100));
    echo "Custo Material Unitário (depois): {$custoMaterialUnitComAjuste}\n";
    echo "Diferença: " . ($custoMaterialUnitComAjuste - $custoMaterialUnit) . "\n";
} else {
    echo "Ajuste = 0, nenhum ajuste aplicado\n";
}
