<?php
/**
 * Debug direto do método showSinapi
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once __DIR__ . '/app/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');

$id = 29;

echo '<pre style="background:#1a1a1a;color:#fff;padding:20px;font-family:monospace;">';
echo "=== DEBUG DO MÉTODO SHOWSINAPI ===\n\n";

try {
    echo "1. Buscando orçamento ID $id...\n";
    $orcamento = \App\Models\Orcamento::find($id);
    
    if (!$orcamento) {
        die("ERRO: Orçamento não encontrado\n");
    }
    echo "✓ Orçamento encontrado\n\n";
    
    echo "2. Carregando itens...\n";
    $itens = \App\Models\OrcamentoItem::allByOrcamento($id);
    echo "✓ " . count($itens) . " itens carregados\n\n";
    
    echo "3. Carregando opções...\n";
    $grupos = \App\Models\OrcamentoOpcao::namesByTipo('grupo');
    $categorias = \App\Models\OrcamentoOpcao::namesByTipo('categoria');
    $unidades = \App\Models\OrcamentoOpcao::namesByTipo('unidade');
    echo "✓ Opções carregadas\n";
    echo "  - Grupos: " . count($grupos) . "\n";
    echo "  - Categorias: " . count($categorias) . "\n";
    echo "  - Unidades: " . count($unidades) . "\n\n";
    
    echo "4. Preparando parâmetros...\n";
    $params = [
        'orcamento' => $orcamento,
        'itens' => $itens,
        'grupos' => $grupos,
        'categorias' => $categorias,
        'unidades' => $unidades,
        'item' => [
            'grupo' => 'MATERIAIS',
            'categoria' => 'ALVENARIA',
            'codigo' => '1.1',
            'descricao' => '',
            'quantidade' => '0',
            'unidade' => 'un',
            'valor_unitario' => '0',
            'custo_material' => '0',
            'custo_mao_obra' => '0',
            'ordem' => '0',
        ],
        'errors' => [],
    ];
    echo "✓ Parâmetros preparados\n\n";
    
    echo "5. Extraindo variáveis...\n";
    extract($params, EXTR_SKIP);
    echo "✓ Variáveis extraídas\n\n";
    
    echo "6. Verificando caminho da view show_sinapi...\n";
    $viewPath = __DIR__ . '/app/Views/orcamentos/show_sinapi.php';
    if (!file_exists($viewPath)) {
        die("ERRO: View não encontrada: $viewPath\n");
    }
    echo "✓ View existe: $viewPath\n";
    echo "  - Tamanho: " . filesize($viewPath) . " bytes\n";
    echo "  - Modificado: " . date('Y-m-d H:i:s', filemtime($viewPath)) . "\n\n";
    
    echo "7. Capturando conteúdo da view...\n";
    ob_start();
    require $viewPath;
    $content = ob_get_clean();
    
    if ($content === false) {
        die("ERRO: Falha ao capturar conteúdo\n");
    }
    echo "✓ Conteúdo capturado: " . strlen($content) . " bytes\n\n";
    
    echo "8. Verificando caminho do layout...\n";
    $layoutPath = __DIR__ . '/app/Views/layout.php';
    if (!file_exists($layoutPath)) {
        die("ERRO: Layout não encontrado: $layoutPath\n");
    }
    echo "✓ Layout existe: $layoutPath\n\n";
    
    echo "9. Renderizando layout...\n";
    echo "</pre>";
    
    // Renderizar o layout
    require $layoutPath;
    
} catch (\Throwable $e) {
    echo "\n\n❌ ERRO CAPTURADO!\n";
    echo "================\n\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n\n";
    echo "Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    echo "</pre>";
}
