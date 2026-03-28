<?php
/**
 * Script de diagnóstico para capturar erros do orçamento
 */

// Forçar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Limpar OPcache se disponível
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Incluir bootstrap
require_once __DIR__ . '/app/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Diagnóstico de Erro</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#fff;}';
echo '.box{background:#2d2d2d;border:2px solid #4CAF50;border-radius:8px;padding:20px;margin:20px 0;}';
echo '.title{color:#4CAF50;font-size:20px;font-weight:bold;margin-bottom:10px;}';
echo '.info{color:#ffaa44;font-size:14px;margin:10px 0;padding:10px;background:#1a1a1a;border-radius:4px;}';
echo '.error{color:#ff4444;} .success{color:#4CAF50;}';
echo '</style></head><body>';

echo '<div class="box">';
echo '<div class="title">🔍 Diagnóstico de Erro - Orçamento ID 29</div>';

try {
    echo '<div class="info">Carregando orçamento...</div>';
    
    $orcamento = \App\Models\Orcamento::find(29);
    
    if (!$orcamento) {
        echo '<div class="error">❌ Orçamento não encontrado!</div>';
    } else {
        echo '<div class="success">✅ Orçamento carregado com sucesso!</div>';
        echo '<div class="info">Número: ' . htmlspecialchars($orcamento['numero_proposta'] ?? '') . '</div>';
        echo '<div class="info">Cliente: ' . htmlspecialchars($orcamento['cliente_nome'] ?? '') . '</div>';
        echo '<div class="info">Tipo: ' . htmlspecialchars($orcamento['tipo_orcamento'] ?? 'manual') . '</div>';
    }
    
    echo '<div class="info">Carregando itens...</div>';
    
    $itens = \App\Models\OrcamentoItem::allByOrcamento(29);
    
    echo '<div class="success">✅ ' . count($itens) . ' itens carregados!</div>';
    
    // Verificar se há itens com grupo problemático
    $gruposEncontrados = [];
    foreach ($itens as $item) {
        $grupo = (string)($item['grupo'] ?? '');
        if (!isset($gruposEncontrados[$grupo])) {
            $gruposEncontrados[$grupo] = 0;
        }
        $gruposEncontrados[$grupo]++;
    }
    
    echo '<div class="info"><strong>Grupos encontrados:</strong></div>';
    foreach ($gruposEncontrados as $grupo => $count) {
        echo '<div class="info">- ' . htmlspecialchars($grupo ?: '(vazio)') . ': ' . $count . ' itens</div>';
    }
    
    echo '<div class="info">Carregando opções...</div>';
    
    $grupos = \App\Models\OrcamentoOpcao::namesByTipo('grupo');
    $categorias = \App\Models\OrcamentoOpcao::namesByTipo('categoria');
    $unidades = \App\Models\OrcamentoOpcao::namesByTipo('unidade');
    
    echo '<div class="success">✅ Opções carregadas!</div>';
    echo '<div class="info">Grupos disponíveis: ' . count($grupos) . '</div>';
    echo '<div class="info">Categorias disponíveis: ' . count($categorias) . '</div>';
    echo '<div class="info">Unidades disponíveis: ' . count($unidades) . '</div>';
    
    echo '<div class="info" style="margin-top:20px;"><strong>Testando renderização da view...</strong></div>';
    
    // Tentar renderizar a view
    ob_start();
    
    $grouped = [];
    foreach ($itens as $it) {
        $grupo = (string)($it['grupo'] ?? '');
        $categoria = (string)($it['categoria'] ?? '');
        $grouped[$grupo][$categoria][] = $it;
    }
    
    echo '<div class="success">✅ Agrupamento realizado com sucesso!</div>';
    echo '<div class="info">Grupos agrupados: ' . count($grouped) . '</div>';
    
    // Testar cálculos
    $totalGeral = 0.0;
    $errosCalculo = [];
    
    foreach ($grouped as $grupo => $cats) {
        foreach ($cats as $categoria => $rows) {
            foreach ($rows as $row) {
                try {
                    $quantidade = (float)($row['quantidade'] ?? 0);
                    $custoMaterialTotal = (float)($row['custo_material'] ?? 0);
                    $custoMaoObraTotal = (float)($row['custo_mao_obra'] ?? 0);
                    $custoEquipamentoTotal = (float)($row['custo_equipamento'] ?? 0);
                    $valorUnitario = (float)($row['valor_unitario'] ?? 0);
                    $valorCobranca = (float)($row['valor_cobranca'] ?? 0);
                    
                    if ($valorCobranca == 0) {
                        $valorCobranca = $valorUnitario;
                    }
                    
                    $valorTotal = round($quantidade * $valorCobranca, 2);
                    $totalGeral += $valorTotal;
                } catch (\Throwable $e) {
                    $errosCalculo[] = 'Item ' . ($row['id'] ?? '?') . ': ' . $e->getMessage();
                }
            }
        }
    }
    
    if (empty($errosCalculo)) {
        echo '<div class="success">✅ Todos os cálculos executados sem erros!</div>';
        echo '<div class="info">Total geral: R$ ' . number_format($totalGeral, 2, ',', '.') . '</div>';
    } else {
        echo '<div class="error">❌ Erros encontrados nos cálculos:</div>';
        foreach ($errosCalculo as $erro) {
            echo '<div class="error">- ' . htmlspecialchars($erro) . '</div>';
        }
    }
    
    ob_end_clean();
    
    echo '<div class="success" style="margin-top:20px;">✅ DIAGNÓSTICO COMPLETO - Nenhum erro detectado!</div>';
    echo '<div class="info">O orçamento pode ser visualizado normalmente.</div>';
    echo '<div class="info"><a href="/?route=orcamentos/show&id=29" style="color:#4CAF50;">Tentar abrir orçamento normal</a></div>';
    echo '<div class="info"><a href="/?route=orcamentos/showSinapi&id=29" style="color:#4CAF50;">Tentar abrir orçamento SINAPI</a></div>';
    
} catch (\Throwable $e) {
    echo '<div class="error">❌ ERRO DETECTADO!</div>';
    echo '<div class="error"><strong>Tipo:</strong> ' . htmlspecialchars(get_class($e)) . '</div>';
    echo '<div class="error"><strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
    echo '<div class="error"><strong>Arquivo:</strong> ' . htmlspecialchars($e->getFile()) . '</div>';
    echo '<div class="error"><strong>Linha:</strong> ' . $e->getLine() . '</div>';
    echo '<div class="info" style="margin-top:15px;"><strong>Stack Trace:</strong><pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre></div>';
}

echo '</div></body></html>';
