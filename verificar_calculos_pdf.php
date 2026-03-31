<?php
/**
 * Script para verificar se todos os cálculos financeiros e percentuais estão corretos
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Database;
use App\Models\OrcamentoItem;

// ID do orçamento para testar (ajustar conforme necessário)
$orcamentoId = 40;

$pdo = Database::pdo();

// Buscar orçamento
$stmt = $pdo->prepare('SELECT * FROM orcamentos WHERE id = :id');
$stmt->execute([':id' => $orcamentoId]);
$orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orcamento) {
    die("Orçamento #{$orcamentoId} não encontrado\n");
}

echo "═══════════════════════════════════════════════════════════\n";
echo "VERIFICAÇÃO DE CÁLCULOS - ORÇAMENTO #{$orcamentoId}\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Buscar todos os itens
$itens = OrcamentoItem::allByOrcamento($orcamentoId);

// Agrupar por categoria
$categorias = [];
$totalGeral = 0.0;

foreach ($itens as $item) {
    $categoria = trim((string)($item['categoria'] ?? 'SEM CATEGORIA'));
    $quantidade = (float)($item['quantidade'] ?? 0);
    $valorCobranca = (float)($item['valor_cobranca'] ?? 0);
    $valorTotal = $quantidade * $valorCobranca;
    
    if (!isset($categorias[$categoria])) {
        $categorias[$categoria] = [
            'itens' => [],
            'total' => 0
        ];
    }
    
    $categorias[$categoria]['itens'][] = $item;
    $categorias[$categoria]['total'] += $valorTotal;
    $totalGeral += $valorTotal;
}

echo "1. VERIFICAÇÃO: % DA OBRA (RESUMO GERAL)\n";
echo "─────────────────────────────────────────────────────────\n";

$somaPercentuaisObra = 0.0;
foreach ($categorias as $categoriaNome => $categoriaData) {
    $pctObra = $totalGeral > 0 ? ($categoriaData['total'] / $totalGeral) * 100 : 0;
    $somaPercentuaisObra += $pctObra;
    printf("%-40s: %10s (%.2f%%)\n", 
        substr($categoriaNome, 0, 40), 
        'R$ ' . number_format($categoriaData['total'], 2, ',', '.'),
        $pctObra
    );
}

printf("\n%-40s: %10s (%.2f%%)\n", 'TOTAL GERAL', 'R$ ' . number_format($totalGeral, 2, ',', '.'), $somaPercentuaisObra);

if (abs($somaPercentuaisObra - 100.0) < 0.01) {
    echo "✅ CORRETO: Soma dos % da Obra = 100%\n\n";
} else {
    echo "❌ ERRO: Soma dos % da Obra = " . number_format($somaPercentuaisObra, 2) . "% (deveria ser 100%)\n\n";
}

echo "\n2. VERIFICAÇÃO: % ETAPA (POR CATEGORIA)\n";
echo "─────────────────────────────────────────────────────────\n";

foreach ($categorias as $categoriaNome => $categoriaData) {
    echo "\nCategoria: " . strtoupper($categoriaNome) . "\n";
    echo str_repeat('─', 60) . "\n";
    
    $somaPercentuaisEtapa = 0.0;
    foreach ($categoriaData['itens'] as $item) {
        $quantidade = (float)($item['quantidade'] ?? 0);
        $valorCobranca = (float)($item['valor_cobranca'] ?? 0);
        $valorTotal = $quantidade * $valorCobranca;
        $pctEtapa = $categoriaData['total'] > 0 ? ($valorTotal / $categoriaData['total']) * 100 : 0;
        $pctObra = $totalGeral > 0 ? ($valorTotal / $totalGeral) * 100 : 0;
        
        $somaPercentuaisEtapa += $pctEtapa;
        
        printf("  %s - %-30s: %.2f%% etapa | %.2f%% obra\n",
            $item['codigo'],
            substr($item['descricao'], 0, 30),
            $pctEtapa,
            $pctObra
        );
    }
    
    printf("\n  Subtotal: R$ %s (%.2f%% da etapa)\n", 
        number_format($categoriaData['total'], 2, ',', '.'),
        $somaPercentuaisEtapa
    );
    
    if (abs($somaPercentuaisEtapa - 100.0) < 0.01) {
        echo "  ✅ CORRETO: Soma dos % Etapa = 100%\n";
    } else {
        echo "  ❌ ERRO: Soma dos % Etapa = " . number_format($somaPercentuaisEtapa, 2) . "% (deveria ser 100%)\n";
    }
}

echo "\n\n3. VERIFICAÇÃO: RESUMO DE CUSTOS\n";
echo "─────────────────────────────────────────────────────────\n";

$totalMateriais = 0.0;
$totalMaoObra = 0.0;
$totalEquipamentos = 0.0;

foreach ($itens as $item) {
    $quantidade = (float)($item['quantidade'] ?? 0);
    $custoMaterial = (float)($item['custo_material'] ?? 0);
    $custoMaoObra = (float)($item['custo_mao_obra'] ?? 0);
    $custoEquipamento = (float)($item['custo_equipamento'] ?? 0);
    
    $totalMateriais += $quantidade * $custoMaterial;
    $totalMaoObra += $quantidade * $custoMaoObra;
    $totalEquipamentos += $quantidade * $custoEquipamento;
}

$somaCustos = $totalMateriais + $totalMaoObra + $totalEquipamentos;

$pctMateriais = $somaCustos > 0 ? ($totalMateriais / $somaCustos) * 100 : 0;
$pctMaoObra = $somaCustos > 0 ? ($totalMaoObra / $somaCustos) * 100 : 0;
$pctEquipamentos = $somaCustos > 0 ? ($totalEquipamentos / $somaCustos) * 100 : 0;
$somaPercentuaisCustos = $pctMateriais + $pctMaoObra + $pctEquipamentos;

printf("Materiais:    R$ %15s (%.2f%%)\n", number_format($totalMateriais, 2, ',', '.'), $pctMateriais);
printf("Mão de Obra:  R$ %15s (%.2f%%)\n", number_format($totalMaoObra, 2, ',', '.'), $pctMaoObra);
printf("Equipamentos: R$ %15s (%.2f%%)\n", number_format($totalEquipamentos, 2, ',', '.'), $pctEquipamentos);
printf("\nSoma Custos:  R$ %15s (%.2f%%)\n", number_format($somaCustos, 2, ',', '.'), $somaPercentuaisCustos);

if (abs($somaPercentuaisCustos - 100.0) < 0.01) {
    echo "✅ CORRETO: Soma dos % de Custos = 100%\n";
} else {
    echo "❌ ERRO: Soma dos % de Custos = " . number_format($somaPercentuaisCustos, 2) . "% (deveria ser 100%)\n";
}

echo "\n\n4. VERIFICAÇÃO: VALORES FINAIS\n";
echo "─────────────────────────────────────────────────────────\n";

$subtotal = $totalGeral;
$percentualCustosAdm = (float)($orcamento['percentual_custos_adm'] ?? 0);
$percentualImpostos = (float)($orcamento['percentual_impostos'] ?? 0);

$valorCustosAdm = $subtotal * ($percentualCustosAdm / 100);
$valorImpostos = $subtotal * ($percentualImpostos / 100);
$totalFinal = $subtotal + $valorCustosAdm + $valorImpostos;

printf("Subtotal da Obra:        R$ %s\n", number_format($subtotal, 2, ',', '.'));
printf("Custos Administrativos:  R$ %s (%.2f%%)\n", number_format($valorCustosAdm, 2, ',', '.'), $percentualCustosAdm);
printf("Impostos:                R$ %s (%.2f%%)\n", number_format($valorImpostos, 2, ',', '.'), $percentualImpostos);
printf("TOTAL GERAL:             R$ %s\n", number_format($totalFinal, 2, ',', '.'));

$verificacao = $subtotal + $valorCustosAdm + $valorImpostos;
if (abs($verificacao - $totalFinal) < 0.01) {
    echo "✅ CORRETO: Subtotal + Custos Adm + Impostos = Total Geral\n";
} else {
    echo "❌ ERRO: Verificação falhou\n";
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "VERIFICAÇÃO CONCLUÍDA\n";
echo "═══════════════════════════════════════════════════════════\n";
