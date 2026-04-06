<?php
/**
 * Script para testar se o sistema está funcionando corretamente
 * Acesse: https://orcamento.onsolutionsbrasil.com.br/testar_sistema.php
 * DELETE após usar!
 */

require __DIR__ . '/app/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== TESTE DO SISTEMA ===\n\n";

try {
    $pdo = \App\Core\Database::pdo();
    
    // Testar se as colunas existem
    echo "1. Verificando estrutura do banco de dados...\n";
    
    // Verificar coluna valor_entrada
    $stmt = $pdo->query("SHOW COLUMNS FROM orcamentos LIKE 'valor_entrada'");
    $valorEntrada = $stmt->fetch();
    
    if ($valorEntrada) {
        echo "   ✓ Coluna 'valor_entrada' existe na tabela 'orcamentos'\n";
    } else {
        echo "   ✗ Coluna 'valor_entrada' NÃO existe na tabela 'orcamentos'\n";
    }
    
    // Verificar coluna pagamento_realizado
    $stmt = $pdo->query("SHOW COLUMNS FROM orcamento_itens LIKE 'pagamento_realizado'");
    $pagamentoRealizado = $stmt->fetch();
    
    if ($pagamentoRealizado) {
        echo "   ✓ Coluna 'pagamento_realizado' existe na tabela 'orcamento_itens'\n";
    } else {
        echo "   ✗ Coluna 'pagamento_realizado' NÃO existe na tabela 'orcamento_itens'\n";
    }
    
    // Testar se o modelo Orcamento consegue normalizar dados
    echo "\n2. Testando normalização de dados...\n";
    
    $dadosTeste = [
        'numero_proposta' => 'TESTE-001',
        'cliente_nome' => 'Cliente Teste',
        'valor_entrada' => '1.000,00',
        'margem_mao_obra' => '50',
        'margem_materiais' => '20',
        'margem_equipamentos' => '20',
    ];
    
    try {
        $normalizado = \App\Models\Orcamento::normalize($dadosTeste);
        echo "   ✓ Normalização funcionou\n";
        echo "   - valor_entrada normalizado: " . $normalizado['valor_entrada'] . "\n";
    } catch (\Exception $e) {
        echo "   ✗ Erro na normalização: " . $e->getMessage() . "\n";
    }
    
    // Verificar arquivo OrcamentoPDF.php
    echo "\n3. Verificando arquivo OrcamentoPDF.php...\n";
    $arquivo = __DIR__ . '/app/Helpers/OrcamentoPDF.php';
    $linhas = file($arquivo);
    $totalLinhas = count($linhas);
    echo "   - Total de linhas: $totalLinhas\n";
    echo "   - Última modificação: " . date('Y-m-d H:i:s', filemtime($arquivo)) . "\n";
    
    // Verificar se tem o código correto na linha 1833
    if (isset($linhas[1832])) {
        $linha1833 = trim($linhas[1832]);
        if (strpos($linha1833, 'foreach ($itensPorGrupo as $nomeGrupo => $itensGrupo)') !== false) {
            echo "   ✓ Código correto encontrado na linha 1833\n";
        } else {
            echo "   ⚠ Linha 1833 tem conteúdo diferente: $linha1833\n";
        }
    }
    
    // Verificar se tem proteção contra $itensGrupo não-array
    $conteudo = file_get_contents($arquivo);
    if (strpos($conteudo, 'if (!is_array($itensGrupo))') !== false) {
        echo "   ✓ Proteção contra \$itensGrupo não-array está presente\n";
    } else {
        echo "   ⚠ Proteção contra \$itensGrupo não-array NÃO encontrada\n";
    }
    
    echo "\n=== RESULTADO ===\n";
    
    if ($valorEntrada && $pagamentoRealizado) {
        echo "✓ SISTEMA PRONTO PARA USO!\n";
        echo "\nVocê pode:\n";
        echo "- Criar novos orçamentos (não deve dar erro 500)\n";
        echo "- Gerar PDFs (não deve ter warning de \$itensGrupo)\n";
        echo "- Marcar itens como pagos\n";
        echo "- Ver cálculos corretos de 'VALOR A PAGAR'\n";
    } else {
        echo "✗ AINDA HÁ PROBLEMAS\n";
        echo "\nExecute novamente o SQL das migrations.\n";
    }
    
} catch (\Exception $e) {
    echo "✗ ERRO: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== FIM ===\n";
echo "\nIMPORTANTE: DELETE este arquivo após usar!\n";
