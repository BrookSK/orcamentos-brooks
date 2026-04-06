<?php
/**
 * Script para testar criação de orçamento e capturar erros
 * Acesse: https://orcamento.onsolutionsbrasil.com.br/testar_criar_orcamento.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/app/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== TESTE DE CRIAÇÃO DE ORÇAMENTO ===\n\n";

// Dados mínimos para criar um orçamento
$dadosTeste = [
    'numero_proposta' => 'TESTE-' . time(),
    'cliente_nome' => 'Cliente Teste',
    'arquiteto_nome' => '',
    'obra_nome' => '',
    'endereco_obra' => '',
    'local_obra' => '',
    'data' => '',
    'referencia' => '',
    'area_m2' => '',
    'contrato' => '',
    'tipo' => '',
    'tipo_orcamento' => 'manual',
    'prazo_dias' => '',
    'rev' => '',
    'empresa_nome' => '',
    'empresa_endereco' => '',
    'empresa_telefone' => '',
    'empresa_email' => '',
    'logo_path' => '',
    'capa_path_1' => '',
    'capa_path_2' => '',
    'capa_path_3' => '',
    'capa_path_4' => '',
    'percentual_custos_adm' => '0',
    'percentual_impostos' => '0',
    'valor_entrada' => '0',
    'margem_mao_obra' => '50',
    'margem_materiais' => '20',
    'margem_equipamentos' => '20',
    'ajuste_prorata_materiais' => '0',
];

echo "1. Testando normalização...\n";
try {
    $dataNormalizado = \App\Models\Orcamento::normalize($dadosTeste);
    echo "   ✓ Normalização OK\n";
    echo "   - numero_proposta: " . $dataNormalizado['numero_proposta'] . "\n";
    echo "   - cliente_nome: " . $dataNormalizado['cliente_nome'] . "\n";
    echo "   - valor_entrada: " . $dataNormalizado['valor_entrada'] . "\n";
} catch (\Throwable $e) {
    echo "   ✗ ERRO na normalização:\n";
    echo "   Mensagem: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . "\n";
    echo "   Linha: " . $e->getLine() . "\n\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit;
}

echo "\n2. Testando validação...\n";
try {
    $erros = \App\Models\Orcamento::validate($dataNormalizado);
    if ($erros) {
        echo "   ⚠ Erros de validação:\n";
        foreach ($erros as $campo => $mensagem) {
            echo "   - $campo: $mensagem\n";
        }
        echo "\n   Continuando mesmo assim...\n";
    } else {
        echo "   ✓ Validação OK\n";
    }
} catch (\Throwable $e) {
    echo "   ✗ ERRO na validação:\n";
    echo "   Mensagem: " . $e->getMessage() . "\n";
    exit;
}

echo "\n3. Testando criação no banco de dados...\n";
try {
    $id = \App\Models\Orcamento::create($dataNormalizado);
    echo "   ✓ Orçamento criado com sucesso!\n";
    echo "   - ID: $id\n";
    echo "   - Número: " . $dataNormalizado['numero_proposta'] . "\n";
    
    // Limpar o teste
    echo "\n4. Limpando teste...\n";
    \App\Models\Orcamento::delete($id);
    echo "   ✓ Orçamento de teste removido\n";
    
} catch (\PDOException $e) {
    echo "   ✗ ERRO DE BANCO DE DADOS:\n\n";
    echo "   Mensagem: " . $e->getMessage() . "\n";
    echo "   Código: " . $e->getCode() . "\n\n";
    
    // Tentar identificar qual coluna está faltando
    if (strpos($e->getMessage(), 'Unknown column') !== false) {
        preg_match("/Unknown column '([^']+)'/", $e->getMessage(), $matches);
        if (isset($matches[1])) {
            echo "   PROBLEMA: A coluna '{$matches[1]}' não existe no banco de dados!\n";
            echo "   SOLUÇÃO: Execute a migration que adiciona essa coluna.\n";
        }
    }
    
    echo "\n   Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit;
    
} catch (\Throwable $e) {
    echo "   ✗ ERRO FATAL:\n\n";
    echo "   Tipo: " . get_class($e) . "\n";
    echo "   Mensagem: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . "\n";
    echo "   Linha: " . $e->getLine() . "\n\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit;
}

echo "\n=== TESTE CONCLUÍDO COM SUCESSO ===\n";
echo "\nO sistema está funcionando corretamente!\n";
echo "Você pode criar orçamentos normalmente.\n";
echo "\nDELETE este arquivo após usar!\n";
