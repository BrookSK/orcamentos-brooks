<?php
/**
 * Teste direto da view para identificar o erro
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/app/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');

echo '<pre>';
echo "=== TESTE DE CARREGAMENTO DA VIEW ===\n\n";

try {
    echo "1. Carregando orçamento ID 29...\n";
    $orcamento = \App\Models\Orcamento::find(29);
    
    if (!$orcamento) {
        die("ERRO: Orçamento não encontrado!\n");
    }
    
    echo "✓ Orçamento carregado\n";
    echo "  - Número: " . $orcamento['numero_proposta'] . "\n";
    echo "  - Cliente: " . $orcamento['cliente_nome'] . "\n";
    echo "  - Tipo: " . ($orcamento['tipo_orcamento'] ?? 'manual') . "\n\n";
    
    echo "2. Carregando itens...\n";
    $itens = \App\Models\OrcamentoItem::allByOrcamento(29);
    echo "✓ " . count($itens) . " itens carregados\n\n";
    
    echo "3. Carregando opções...\n";
    $grupos = \App\Models\OrcamentoOpcao::namesByTipo('grupo');
    $categorias = \App\Models\OrcamentoOpcao::namesByTipo('categoria');
    $unidades = \App\Models\OrcamentoOpcao::namesByTipo('unidade');
    echo "✓ Opções carregadas\n\n";
    
    echo "4. Preparando variáveis para a view...\n";
    $item = [
        'grupo' => 'SERVIÇOS PRELIMINARES',
        'categoria' => 'PROJETOS COMPLEMENTARES',
        'codigo' => '1.1',
        'descricao' => '',
        'quantidade' => '1,0',
        'unidade' => 'vb',
        'valor_unitario' => '0',
        'ordem' => '0',
    ];
    $errors = [];
    echo "✓ Variáveis preparadas\n\n";
    
    echo "5. Tentando incluir a view...\n";
    ob_start();
    
    // Extrair variáveis
    extract([
        'orcamento' => $orcamento,
        'itens' => $itens,
        'grupos' => $grupos,
        'categorias' => $categorias,
        'unidades' => $unidades,
        'item' => $item,
        'errors' => $errors
    ], EXTR_SKIP);
    
    $viewPath = __DIR__ . '/app/Views/orcamentos/show.php';
    
    if (!file_exists($viewPath)) {
        die("ERRO: View não encontrada em: $viewPath\n");
    }
    
    echo "✓ View encontrada: $viewPath\n";
    echo "✓ Tamanho: " . filesize($viewPath) . " bytes\n";
    echo "✓ Última modificação: " . date('Y-m-d H:i:s', filemtime($viewPath)) . "\n\n";
    
    echo "6. Incluindo view...\n";
    require $viewPath;
    
    $content = ob_get_clean();
    
    echo "✓ View incluída com sucesso!\n";
    echo "✓ Tamanho do conteúdo gerado: " . strlen($content) . " bytes\n\n";
    
    echo "=== TESTE CONCLUÍDO COM SUCESSO ===\n\n";
    echo "Agora vou renderizar o conteúdo:\n";
    echo "</pre>";
    
    // Renderizar o conteúdo
    echo $content;
    
} catch (\Throwable $e) {
    echo "\n\n";
    echo "❌ ERRO DETECTADO!\n";
    echo "================\n\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n\n";
    echo "Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    echo "</pre>";
}
