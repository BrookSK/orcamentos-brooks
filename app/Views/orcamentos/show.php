<?php

declare(strict_types=1);

use App\Models\OrcamentoItem;

$itens = $itens ?? [];

$grouped = [];
foreach ($itens as $it) {
    $grupo = (string)($it['grupo'] ?? '');
    $categoria = (string)($it['categoria'] ?? '');
    $grouped[$grupo][$categoria][] = $it;
}

$totalGeral = 0.0;

?><div class="card" style="padding:16px; margin-bottom:12px;">
    <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap;">
        <div>
            <div style="font-weight:800; font-size:14px;">Orçamento: <?php echo htmlspecialchars((string)$orcamento['numero_proposta']); ?></div>
            <div class="muted" style="margin-top:4px; font-size:12px;">
                Cliente: <?php echo htmlspecialchars((string)$orcamento['cliente_nome']); ?>
                <?php if (!empty($orcamento['obra_nome'])) : ?>
                    · Obra: <?php echo htmlspecialchars((string)$orcamento['obra_nome']); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="actions">
            <a class="btn" href="/?route=orcamentos/index">Voltar</a>
            <a class="btn" href="/?route=orcamentos/edit&id=<?php echo (int)$orcamento['id']; ?>">Editar cabeçalho</a>
            <a class="btn" style="background: #e94560; color: white;" href="/?route=orcamentos/adequacao&id=<?php echo (int)$orcamento['id']; ?>">💰 Ajustar Valor do Contrato</a>
            <!-- <a class="btn" href="/?route=orcamentos/print&id=<?php echo (int)$orcamento['id']; ?>" target="_blank">Imprimir</a> -->
            <a class="btn primary" href="/?route=orcamentos/pdf&id=<?php echo (int)$orcamento['id']; ?>" target="_blank">Exportar PDF</a>
            <a class="btn" style="background: #C9973A; color: white;" href="/?route=orcamentos/pdfAdmin&id=<?php echo (int)$orcamento['id']; ?>" target="_blank">📊 PDF Administrativo</a>
        </div>
    </div>
</div>

<div class="card" style="padding:16px; margin-bottom:12px;">
    <div style="font-weight:800; margin-bottom:10px; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleAdicionarItem()">
        <span>Adicionar item</span>
        <span id="toggle-icon" style="font-size: 20px;">▼</span>
    </div>
    <div id="form-adicionar-item" style="display: none;">
    <form method="post" action="/?route=orcamentos/itemStore">
        <input type="hidden" name="orcamento_id" value="<?php echo (int)$orcamento['id']; ?>">
        <div class="form" style="padding:0;">
            <div class="field">
                <label>Grupo</label>
                <?php $grupos = $grupos ?? []; ?>
                <?php $currentGrupo = (string)($item['grupo'] ?? ''); ?>
                <select name="grupo">
                    <option value="">Selecione</option>
                    <?php if ($currentGrupo !== '' && !in_array($currentGrupo, $grupos, true)) : ?>
                        <option value="<?php echo htmlspecialchars($currentGrupo); ?>" selected><?php echo htmlspecialchars($currentGrupo); ?></option>
                    <?php endif; ?>
                    <?php foreach ($grupos as $g) : ?>
                        <option value="<?php echo htmlspecialchars((string)$g); ?>" <?php echo ((string)$g === $currentGrupo) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$g); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['grupo'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['grupo']); ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label>Categoria</label>
                <?php $categorias = $categorias ?? []; ?>
                <?php $currentCategoria = (string)($item['categoria'] ?? ''); ?>
                <select name="categoria">
                    <option value="">Selecione</option>
                    <?php if ($currentCategoria !== '' && !in_array($currentCategoria, $categorias, true)) : ?>
                        <option value="<?php echo htmlspecialchars($currentCategoria); ?>" selected><?php echo htmlspecialchars($currentCategoria); ?></option>
                    <?php endif; ?>
                    <?php foreach ($categorias as $c) : ?>
                        <option value="<?php echo htmlspecialchars((string)$c); ?>" <?php echo ((string)$c === $currentCategoria) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$c); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['categoria'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['categoria']); ?></div><?php endif; ?>
            </div>

            <div class="field">
                <label>Código</label>
                <input name="codigo" value="<?php echo htmlspecialchars((string)($item['codigo'] ?? '')); ?>">
                <?php if (!empty($errors['codigo'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['codigo']); ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label>Ordem (opcional)</label>
                <input name="ordem" inputmode="numeric" value="<?php echo htmlspecialchars((string)($item['ordem'] ?? '0')); ?>">
            </div>

            <div class="field full">
                <label>Descrição</label>
                <textarea name="descricao" rows="6" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.04); color:var(--text); outline:none; resize:vertical;"><?php echo htmlspecialchars((string)($item['descricao'] ?? '')); ?></textarea>
                <?php if (!empty($errors['descricao'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['descricao']); ?></div><?php endif; ?>
                <div class="muted" style="font-size:12px; margin-top:6px;">Dica: para múltiplas linhas, você pode colar com quebras de linha e o PDF preserva.</div>
            </div>

            <div class="field">
                <label>Quantidade</label>
                <input name="quantidade" inputmode="decimal" value="<?php echo htmlspecialchars((string)($item['quantidade'] ?? '')); ?>">
                <?php if (!empty($errors['quantidade'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['quantidade']); ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label>Unidade</label>
                <?php $unidades = $unidades ?? []; ?>
                <?php $currentUnidade = (string)($item['unidade'] ?? ''); ?>
                <select name="unidade">
                    <option value="">Selecione</option>
                    <?php if ($currentUnidade !== '' && !in_array($currentUnidade, $unidades, true)) : ?>
                        <option value="<?php echo htmlspecialchars($currentUnidade); ?>" selected><?php echo htmlspecialchars($currentUnidade); ?></option>
                    <?php endif; ?>
                    <?php foreach ($unidades as $u) : ?>
                        <option value="<?php echo htmlspecialchars((string)$u); ?>" <?php echo ((string)$u === $currentUnidade) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$u); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['unidade'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['unidade']); ?></div><?php endif; ?>
            </div>

            <div class="field">
                <label>Valor unitário (custo)</label>
                <input name="valor_unitario" inputmode="decimal" value="<?php echo htmlspecialchars((string)($item['valor_unitario'] ?? '')); ?>">
                <?php if (!empty($errors['valor_unitario'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['valor_unitario']); ?></div><?php endif; ?>
                <div class="muted" style="font-size:12px;margin-top:4px;">Custo base do item (sem margem)</div>
            </div>

            <div class="field">
                <label>
                    <input type="checkbox" name="usa_margem_personalizada" value="1" <?php echo !empty($item['usa_margem_personalizada']) ? 'checked' : ''; ?> style="width:auto;margin-right:6px;">
                    Usar margem personalizada
                </label>
            </div>

            <div class="field">
                <label>% Margem Personalizada</label>
                <input name="margem_personalizada" inputmode="decimal" value="<?php echo htmlspecialchars((string)($item['margem_personalizada'] ?? '0')); ?>">
                <div class="muted" style="font-size:12px;margin-top:4px;">Deixe 0 para usar margem global do orçamento (ex: 25 para 25%)</div>
            </div>

            <div class="field" style="display:flex; justify-content:flex-end; align-items:flex-end;">
                <button class="btn primary" type="submit">Adicionar</button>
            </div>
        </div>
    </form>
    </div>
</div>

<script>
function toggleAdicionarItem() {
    const form = document.getElementById('form-adicionar-item');
    const icon = document.getElementById('toggle-icon');
    
    if (form.style.display === 'none') {
        form.style.display = 'block';
        icon.textContent = '▲';
    } else {
        form.style.display = 'none';
        icon.textContent = '▼';
    }
}
</script>

<div class="card">
    <div style="margin-bottom: 12px; display: flex; gap: 8px; align-items: center;">
        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
            <input type="checkbox" id="toggle-admin-columns" style="width: auto;" checked>
            <span style="font-weight: 600;">Mostrar colunas administrativas (custos, margens, % BDI)</span>
        </label>
    </div>
    
    <div style="overflow-x: auto;">
    <table id="orcamento-table">
        <thead>
        <tr>
            <th style="width:30px;"></th>
            <th style="width:70px">Código</th>
            <th style="width:250px">Descrição</th>
            <th class="center" style="width:50px">Un</th>
            <th class="center" style="width:70px">Qtd</th>
            <th class="num admin-col" style="width:90px;">Custo Mat.</th>
            <th class="num admin-col" style="width:90px;">Custo M.O.</th>
            <th class="num admin-col" style="width:90px;">Custo Equip.</th>
            <th class="center admin-col" style="width:70px;">% BDI</th>
            <th class="num admin-col" style="width:90px;">Margem Un.</th>
            <th class="num" style="width:100px">Vlr Unit.</th>
            <th class="num admin-col" style="width:100px;">Lucro Total</th>
            <th class="num" style="width:100px">Vlr Total</th>
            <th style="width:140px"></th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($grouped)) : ?>
            <tr><td colspan="14" class="muted">Nenhum item cadastrado neste orçamento.</td></tr>
        <?php endif; ?>

        <?php 
        // Buscar margens globais do orçamento
        $margemMaoObraGlobal = (float)($orcamento['margem_mao_obra'] ?? 0);
        $margemMateriaisGlobal = (float)($orcamento['margem_materiais'] ?? 0);
        $margemEquipamentosGlobal = (float)($orcamento['margem_equipamentos'] ?? 20);
        ?>

        <?php foreach ($grouped as $grupo => $cats) : ?>
            <tr class="category-row">
                <td colspan="14"><?php echo htmlspecialchars($grupo !== '' ? $grupo : 'SEM GRUPO'); ?></td>
            </tr>

            <?php foreach ($cats as $categoria => $rows) : ?>
                <tr class="subtotal-row category-header" draggable="true" data-categoria="<?php echo htmlspecialchars($categoria); ?>" data-grupo="<?php echo htmlspecialchars($grupo); ?>">
                    <td colspan="14" style="cursor:move;">
                        <span style="color:#666; margin-right:8px;">⋮⋮</span>
                        <?php echo htmlspecialchars($categoria !== '' ? $categoria : 'SEM CATEGORIA'); ?>
                    </td>
                </tr>

                <?php $subtotalCategoria = 0.0; ?>
                <?php foreach ($rows as $row) : ?>
                    <?php
                        $quantidade = (float)($row['quantidade'] ?? 0);
                        $custoMaterialTotal = (float)($row['custo_material'] ?? 0);
                        $custoMaoObraTotal = (float)($row['custo_mao_obra'] ?? 0);
                        $margemPersonalizada = (float)($row['margem_personalizada'] ?? 0);
                        $usaMargemPersonalizada = (int)($row['usa_margem_personalizada'] ?? 0);
                        $valorUnitario = (float)($row['valor_unitario'] ?? 0);
                        $valorCobranca = (float)($row['valor_cobranca'] ?? 0);
                        
                        // Fallback para valor_unitario se valor_cobranca não existir
                        if ($valorCobranca == 0) {
                            $valorCobranca = $valorUnitario;
                        }
                        
                        // Calcular custos unitários
                        // IMPORTANTE: custo_material e custo_mao_obra podem estar salvos como:
                        // - UNITÁRIOS (itens do SINAPI após correção)
                        // - TOTAIS (itens antigos)
                        // Para detectar, verificamos se custo/quantidade ≈ valor_unitario
                        
                        $custoMaterialUnit = 0;
                        $custoMaoObraUnit = 0;
                        
                        if ($custoMaterialTotal > 0 && $quantidade > 0) {
                            $custoMaterialPorQtd = $custoMaterialTotal / $quantidade;
                            // Se custo/qtd é muito diferente do valor_unitario, provavelmente já é unitário
                            if (abs($custoMaterialTotal - $valorUnitario) < 0.01) {
                                // Custo já é unitário
                                $custoMaterialUnit = $custoMaterialTotal;
                            } else {
                                // Custo é total, dividir pela quantidade
                                $custoMaterialUnit = $custoMaterialPorQtd;
                            }
                            
                            // Aplicar ajuste pro rata de materiais
                            $ajusteProRata = (float)($orcamento['ajuste_prorata_materiais'] ?? 0);
                            if ($ajusteProRata > 0) {
                                $custoMaterialUnit = $custoMaterialUnit * (1 + ($ajusteProRata / 100));
                            }
                        }
                        
                        if ($custoMaoObraTotal > 0 && $quantidade > 0) {
                            $custoMaoObraPorQtd = $custoMaoObraTotal / $quantidade;
                            // Se custo/qtd é muito diferente do valor_unitario, provavelmente já é unitário
                            if (abs($custoMaoObraTotal - $valorUnitario) < 0.01) {
                                // Custo já é unitário
                                $custoMaoObraUnit = $custoMaoObraTotal;
                            } else {
                                // Custo é total, dividir pela quantidade
                                $custoMaoObraUnit = $custoMaoObraPorQtd;
                            }
                        }
                        
                        $custoUnitTotal = $custoMaterialUnit + $custoMaoObraUnit;
                        
                        // Se não há custo detalhado, usar valor_unitario como base de custo
                        $custoBase = $custoUnitTotal > 0 ? $custoUnitTotal : $valorUnitario;
                        
                        $margemUnit = $valorCobranca - $custoBase;
                        $valorTotal = round($quantidade * $valorCobranca, 2);
                        
                        // Calcular % BDI aplicado
                        $percentualBdi = 0;
                        if ($usaMargemPersonalizada && $margemPersonalizada > 0) {
                            $percentualBdi = $margemPersonalizada;
                        } elseif (!$usaMargemPersonalizada) {
                            // Detectar se é mão de obra, equipamento ou material pela categoria
                            $categoriaUpper = strtoupper($categoria);
                            if (strpos($categoriaUpper, 'MÃO DE OBRA') !== false || strpos($categoriaUpper, 'MAO DE OBRA') !== false) {
                                $percentualBdi = $margemMaoObraGlobal;
                            } elseif (strpos($categoriaUpper, 'EQUIPAMENTO') !== false) {
                                $percentualBdi = $margemEquipamentosGlobal;
                            } else {
                                $percentualBdi = $margemMateriaisGlobal;
                            }
                        } elseif ($custoBase > 0.01 && $valorCobranca > $custoBase) {
                            $percentualBdi = (($valorCobranca - $custoBase) / $custoBase) * 100;
                            if ($percentualBdi > 999) {
                                $percentualBdi = 0;
                            }
                        }
                        
                        $subtotalCategoria += $valorTotal;
                        $totalGeral += $valorTotal;
                    ?>
                    <?php
                        // Calcular custo de equipamento unitário
                        $custoEquipamentoTotal = (float)($row['custo_equipamento'] ?? 0);
                        $custoEquipamentoUnit = 0;
                        
                        if ($custoEquipamentoTotal > 0 && $quantidade > 0) {
                            // Se custo é próximo do valor_unitario, provavelmente já é unitário
                            if (abs($custoEquipamentoTotal - $valorUnitario) < 0.01) {
                                $custoEquipamentoUnit = $custoEquipamentoTotal;
                            } else {
                                // Custo é total, dividir pela quantidade
                                $custoEquipamentoUnit = $custoEquipamentoTotal / $quantidade;
                            }
                        }
                        
                        // Calcular lucro total (margem unitária × quantidade)
                        $lucroTotal = $margemUnit * $quantidade;
                    ?>
                    <tr class="item-row" draggable="true" data-item-id="<?php echo (int)$row['id']; ?>" data-ordem="<?php echo (int)($row['ordem'] ?? 0); ?>">
                        <td class="drag-handle" style="cursor:move; text-align:center; width:30px; color:#666;">⋮⋮</td>
                        <td class="muted"><?php echo htmlspecialchars((string)$row['codigo']); ?></td>
                        <td style="white-space:pre-line;"><?php echo htmlspecialchars((string)$row['descricao']); ?></td>
                        <td class="center"><?php echo htmlspecialchars((string)$row['unidade']); ?></td>
                        <td class="num"><?php echo OrcamentoItem::formatNumber($quantidade); ?></td>
                        <td class="num admin-col"><?php echo OrcamentoItem::formatMoney($custoMaterialUnit); ?></td>
                        <td class="num admin-col"><?php echo OrcamentoItem::formatMoney($custoMaoObraUnit); ?></td>
                        <td class="num admin-col"><?php echo OrcamentoItem::formatMoney($custoEquipamentoUnit); ?></td>
                        <td class="center admin-col"><?php echo number_format($percentualBdi, 1, ',', '.'); ?>%</td>
                        <td class="num admin-col"><?php echo OrcamentoItem::formatMoney($margemUnit); ?></td>
                        <td class="num"><?php echo OrcamentoItem::formatMoney($valorCobranca); ?></td>
                        <td class="num admin-col"><?php echo OrcamentoItem::formatMoney($lucroTotal); ?></td>
                        <td class="num"><?php echo OrcamentoItem::formatMoney($valorTotal); ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="btn" href="/?route=orcamentos/itemEdit&orcamento_id=<?php echo (int)$orcamento['id']; ?>&id=<?php echo (int)$row['id']; ?>">Editar</a>
                                <form class="inline" method="post" action="/?route=orcamentos/itemDelete" onsubmit="return confirm('Excluir este item?');">
                                    <input type="hidden" name="orcamento_id" value="<?php echo (int)$orcamento['id']; ?>">
                                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                    <button class="btn danger" type="submit">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <tr class="total-row">
                    <td colspan="12" class="num">Total <?php echo htmlspecialchars((string)$categoria); ?></td>
                    <td class="num"><?php echo OrcamentoItem::formatMoney($subtotalCategoria); ?></td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <?php if (!empty($grouped)) : ?>
            <tr class="total-row">
                <td colspan="12" class="num">Total Geral</td>
                <td class="num"><?php echo OrcamentoItem::formatMoney($totalGeral); ?></td>
                <td></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php if (!empty($grouped)) : ?>
<div class="card" style="padding:16px; margin-top:12px;">
    <div style="font-weight:800; margin-bottom:12px; font-size:14px;">📊 Custos Administrativos e Impostos</div>
    
    <table style="width:100%; max-width:600px;">
        <tbody>
            <tr>
                <td style="padding:8px 0; font-weight:600;">Subtotal da Obra:</td>
                <td style="padding:8px 0; text-align:right; font-weight:600;"><?php echo OrcamentoItem::formatMoney($totalGeral); ?></td>
            </tr>
            <?php 
            $taxaAdmin = (float)($orcamento['taxa_administracao'] ?? 0);
            $impostos = (float)($orcamento['impostos'] ?? 0);
            $valorTaxaAdmin = $totalGeral * ($taxaAdmin / 100);
            $valorImpostos = $totalGeral * ($impostos / 100);
            $totalComTaxas = $totalGeral + $valorTaxaAdmin + $valorImpostos;
            ?>
            <?php if ($taxaAdmin > 0) : ?>
            <tr>
                <td style="padding:8px 0; color:var(--muted);">Taxa de Administração (<?php echo number_format($taxaAdmin, 2, ',', '.'); ?>%):</td>
                <td style="padding:8px 0; text-align:right; color:var(--muted);">+ <?php echo OrcamentoItem::formatMoney($valorTaxaAdmin); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($impostos > 0) : ?>
            <tr>
                <td style="padding:8px 0; color:var(--muted);">Impostos (<?php echo number_format($impostos, 2, ',', '.'); ?>%):</td>
                <td style="padding:8px 0; text-align:right; color:var(--muted);">+ <?php echo OrcamentoItem::formatMoney($valorImpostos); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($taxaAdmin > 0 || $impostos > 0) : ?>
            <tr style="border-top:2px solid rgba(255,255,255,0.1);">
                <td style="padding:12px 0 8px; font-weight:800; font-size:16px;">TOTAL FINAL:</td>
                <td style="padding:12px 0 8px; text-align:right; font-weight:800; font-size:16px; color:#4CAF50;"><?php echo OrcamentoItem::formatMoney($totalComTaxas); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ($taxaAdmin == 0 && $impostos == 0) : ?>
    <div class="muted" style="font-size:12px; margin-top:8px;">
        💡 Configure taxa de administração e impostos no cabeçalho do orçamento para visualizar o total final.
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
document.getElementById('toggle-admin-columns').addEventListener('change', function() {
    const adminCols = document.querySelectorAll('.admin-col');
    
    if (this.checked) {
        adminCols.forEach(col => col.style.display = '');
    } else {
        adminCols.forEach(col => col.style.display = 'none');
    }
});
</script>


<script>
// Drag and Drop para reordenar itens e categorias
document.addEventListener('DOMContentLoaded', function() {
(function() {
    let draggedElement = null;
    let draggedType = null; // 'item' ou 'category'
    
    // Adicionar preventDefault no tbody para permitir drop
    const tbody = document.querySelector('#orcamento-table tbody');
    if (tbody) {
        tbody.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        tbody.addEventListener('drop', function(e) {
            e.preventDefault();
        });
    }
    
    // Configurar drag para itens
    const itemRows = document.querySelectorAll('.item-row');
    itemRows.forEach(row => {
        setupItemDrag(row);
    });
    
    // Configurar drag para categorias
    const categoryHeaders = document.querySelectorAll('.category-header');
    categoryHeaders.forEach(header => {
        setupCategoryDrag(header);
    });
    
    function setupItemDrag(row) {
        row.addEventListener('dragstart', function(e) {
            draggedElement = this;
            draggedType = 'item';
            this.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'move';
        });
        
        row.addEventListener('dragend', function(e) {
            this.style.opacity = '';
            clearHighlights();
        });
        
        row.addEventListener('dragover', function(e) {
            if (draggedType === 'item' && draggedElement !== this) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                highlightDropZone(this, e);
            }
        });
        
        row.addEventListener('dragleave', function(e) {
            this.style.borderTop = '';
            this.style.borderBottom = '';
        });
        
        row.addEventListener('drop', function(e) {
            if (draggedType === 'item' && draggedElement !== this) {
                e.stopPropagation();
                
                const rect = this.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                
                if (e.clientY < midpoint) {
                    this.parentNode.insertBefore(draggedElement, this);
                } else {
                    this.parentNode.insertBefore(draggedElement, this.nextSibling);
                }
                
                saveNewOrder();
            }
        });
    }
    
    function setupCategoryDrag(header) {
        header.addEventListener('dragstart', function(e) {
            draggedElement = this;
            draggedType = 'category';
            this.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'move';
        });
        
        header.addEventListener('dragend', function(e) {
            this.style.opacity = '';
            clearHighlights();
        });
        
        header.addEventListener('dragover', function(e) {
            if (draggedType === 'category' && draggedElement !== this) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                
                const rect = this.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                
                clearHighlights();
                if (e.clientY < midpoint) {
                    this.style.borderTop = '3px solid #2196F3';
                } else {
                    this.style.borderBottom = '3px solid #2196F3';
                }
            }
        });
        
        header.addEventListener('dragleave', function(e) {
            this.style.borderTop = '';
            this.style.borderBottom = '';
        });
        
        header.addEventListener('drop', function(e) {
            if (draggedType === 'category' && draggedElement !== this) {
                e.stopPropagation();
                
                // Coletar todos os elementos da categoria arrastada
                const draggedCategory = draggedElement.dataset.categoria;
                const draggedGrupo = draggedElement.dataset.grupo;
                const elementsToMove = [draggedElement];
                
                let nextElement = draggedElement.nextElementSibling;
                while (nextElement && nextElement.classList.contains('item-row')) {
                    elementsToMove.push(nextElement);
                    nextElement = nextElement.nextElementSibling;
                }
                
                // Coletar linha de total se existir
                if (nextElement && nextElement.classList.contains('total-row')) {
                    elementsToMove.push(nextElement);
                }
                
                // Determinar posição de inserção
                const rect = this.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;tBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                
                if (e.clientY < midpoint) {
                    // Inserir antes desta categoria
                    elementsToMove.forEach(el => {
                        this.parentNode.insertBefore(el, this);
                    });
                } else {
                    // Inserir depois desta categoria (e seus itens)
                    let insertAfter = this.nextElementSibling;
                    while (insertAfter && (insertAfter.classList.contains('item-row') || insertAfter.classList.contains('total-row'))) {
                        insertAfter = insertAfter.nextElementSibling;
                    }
                    
                    elementsToMove.forEach(el => {
                        if (insertAfter) {
                            this.parentNode.insertBefore(el, insertAfter);
                        } else {
                            this.parentNode.appendChild(el);
                        }
                    });
                }
                
                saveNewOrder();
            }
        });
    }
    
    function highlightDropZone(element, e) {
        clearHighlights();
        const rect = element.getBoundingClientRect();
        const midpoint = rect.top + rect.height / 2;
        
        if (e.clientY < midpoint) {
            element.style.borderTop = '2px solid #4CAF50';
        } else {
            element.style.borderBottom = '2px solid #4CAF50';
        }
    }
    
    function clearHighlights() {
        document.querySelectorAll('.item-row, .category-header').forEach(el => {
            el.style.borderTop = '';
            el.style.borderBottom = '';
        });
    }
    
    function saveNewOrder() {
        // Coletar ordem de categorias e itens
        const categories = [];
        const categoryHeaders = document.querySelectorAll('.category-header');
        
        categoryHeaders.forEach((header, catIndex) => {
            const categoria = header.dataset.categoria;
            const grupo = header.dataset.grupo;
            const items = [];
            
            // Coletar itens desta categoria
            let nextElement = header.nextElementSibling;
            while (nextElement && nextElement.classList.contains('item-row')) {
                items.push({
                    id: parseInt(nextElement.dataset.itemId),
                    ordem: items.length + 1
                });
                nextElement = nextElement.nextElementSibling;
            }
            
            categories.push({
                categoria: categoria,
                grupo: grupo,
                ordem_categoria: catIndex + 1,
                items: items
            });
        });
        
        // Mostrar indicador de carregamento
        const loadingMsg = document.createElement('div');
        loadingMsg.style.cssText = 'position:fixed;top:20px;right:20px;background:#4CAF50;color:white;padding:12px 20px;border-radius:8px;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,0.3);';
        loadingMsg.textContent = 'Salvando nova ordem...';
        document.body.appendChild(loadingMsg);
        
        // Enviar para o servidor
        fetch('/?route=orcamentos/reorderItems', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                orcamento_id: <?php echo (int)$orcamento['id']; ?>,
                categories: categories
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadingMsg.textContent = 'Ordem atualizada! Recarregando...';
                loadingMsg.style.background = '#4CAF50';
                
                // Recarregar página após 500ms para mostrar códigos atualizados
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                loadingMsg.textContent = 'Erro ao salvar ordem';
                loadingMsg.style.background = '#f44336';
                setTimeout(() => loadingMsg.remove(), 3000);
                console.error('Erro ao atualizar ordem:', data.error);
            }
        })
        .catch(error => {
            loadingMsg.textContent = 'Erro de conexão';
            loadingMsg.style.background = '#f44336';
            setTimeout(() => loadingMsg.remove(), 3000);
            console.error('Erro:', error);
        });
    }
})();
});
</script>
