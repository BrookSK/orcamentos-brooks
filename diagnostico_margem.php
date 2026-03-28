<?php
// Script de diagnóstico para verificar sistema de margens

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Database;
use App\Models\Orcamento;
use App\Models\OrcamentoItem;

echo "=== DIAGNÓSTICO DO SISTEMA DE MARGENS ===\n\n";

// 1. Verificar se colunas existem
echo "1. Verificando estrutura do banco de dados...\n";
$pdo = Database::pdo();

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM orcamentos LIKE 'margem_%'");
    $cols = $stmt->fetchAll();
    echo "   Colunas de margem em 'orcamentos': " . count($cols) . "\n";
    foreach ($cols as $col) {
        echo "   - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (\Exception $e) {
    echo "   ERRO: " . $e->getMessage() . "\n";
}

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM orcamento_itens WHERE Field IN ('margem_personalizada', 'usa_margem_personalizada')");
    $cols = $stmt->fetchAll();
    echo "   Colunas de margem em 'orcamento_itens': " . count($cols) . "\n";
    foreach ($cols as $col) {
        echo "   - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (\Exception $e) {
    echo "   ERRO: " . $e->getMessage() . "\n";
}

// 2. Verificar orçamentos existentes
echo "\n2. Verificando orçamentos cadastrados...\n";
$orcamentos = Orcamento::all();
echo "   Total de orçamentos: " . count($orcamentos) . "\n";

if (!empty($orcamentos)) {
    $orc = $orcamentos[0];
    echo "   Orçamento #" . $orc['id'] . " - " . $orc['numero_proposta'] . "\n";
    echo "   - Margem Mão de Obra: " . ($orc['margem_mao_obra'] ?? 'NÃO EXISTE') . "%\n";
    echo "   - Margem Materiais: " . ($orc['margem_materiais'] ?? 'NÃO EXISTE') . "%\n";
    echo "   - % Custos Adm: " . ($orc['percentual_custos_adm'] ?? 'NÃO EXISTE') . "%\n";
    echo "   - % Impostos: " . ($orc['percentual_impostos'] ?? 'NÃO EXISTE') . "%\n";
    
    // 3. Verificar itens
    echo "\n3. Verificando itens do orçamento...\n";
    $itens = OrcamentoItem::allByOrcamento((int)$orc['id']);
    echo "   Total de itens: " . count($itens) . "\n";
    
    if (!empty($itens)) {
        $item = $itens[0];
        echo "   Item #" . $item['id'] . " - " . substr($item['descricao'], 0, 50) . "...\n";
        echo "   - Categoria: " . ($item['categoria'] ?? 'N/A') . "\n";
        echo "   - Valor Unitário (custo): R$ " . number_format((float)($item['valor_unitario'] ?? 0), 2, ',', '.') . "\n";
        echo "   - Valor Cobrança: R$ " . number_format((float)($item['valor_cobranca'] ?? 0), 2, ',', '.') . "\n";
        echo "   - Margem Personalizada: " . ($item['margem_personalizada'] ?? 'NÃO EXISTE') . "%\n";
        echo "   - Usa Margem Personalizada: " . ($item['usa_margem_personalizada'] ?? 'NÃO EXISTE') . "\n";
        
        // Calcular margem aplicada
        $valorUnitario = (float)($item['valor_unitario'] ?? 0);
        $valorCobranca = (float)($item['valor_cobranca'] ?? 0);
        if ($valorUnitario > 0) {
            $margemAplicada = (($valorCobranca / $valorUnitario) - 1) * 100;
            echo "   - Margem Aplicada (calculada): " . number_format($margemAplicada, 2, ',', '.') . "%\n";
        }
    }
}

// 4. Teste de cálculo
echo "\n4. Teste de cálculo de margem...\n";
$custo = 100.00;
$margem = 25.00;
$valorEsperado = $custo * (1 + $margem / 100);
echo "   Custo: R$ " . number_format($custo, 2, ',', '.') . "\n";
echo "   Margem: " . $margem . "%\n";
echo "   Valor Esperado: R$ " . number_format($valorEsperado, 2, ',', '.') . "\n";
echo "   Fórmula: custo × (1 + margem/100) = " . $custo . " × 1.25 = " . $valorEsperado . "\n";

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
