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
            <input type="checkbox" id="toggle-admin-columns" style="width: auto;">
            <span style="font-weight: 600;">Mostrar colunas administrativas (custos, margens, % BDI)</span>
        </label>
    </div>
    
    <div style="overflow-x: auto;">
    <table id="orcamento-table">
        <thead>
        <tr>
            <th style="width:90px">Código</th>
            <th>Descrição</th>
            <th class="num" style="width:90px">Quant.</th>
            <th style="width:80px">Unid</th>
            <th class="num admin-col" style="width:100px; display:none;">Custo Mat.</th>
            <th class="num admin-col" style="width:100px; display:none;">Custo M.O.</th>
            <th class="num admin-col" style="width:80px; display:none;">% BDI</th>
            <th class="num admin-col" style="width:100px; display:none;">Margem R$</th>
            <th class="num" style="width:120px">Valor Unit.</th>
            <th class="num" style="width:120px">Valor Total</th>
            <th style="width:140px"></th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($grouped)) : ?>
            <tr><td colspan="11" class="muted">Nenhum item cadastrado neste orçamento.</td></tr>
        <?php endif; ?>

        <?php 
        // Buscar margens globais do orçamento
        $margemMaoObraGlobal = (float)($orcamento['margem_mao_obra'] ?? 0);
        $margemMateriaisGlobal = (float)($orcamento['margem_materiais'] ?? 0);
        $margemEquipamentosGlobal = (float)($orcamento['margem_equipamentos'] ?? 20);
        ?>

        <?php foreach ($grouped as $grupo => $cats) : ?>
            <tr class="category-row">
                <td colspan="11"><?php echo htmlspecialchars($grupo !== '' ? $grupo : 'SEM GRUPO'); ?></td>
            </tr>

            <?php foreach ($cats as $categoria => $rows) : ?>
                <tr class="subtotal-row">
                    <td colspan="11"><?php echo htmlspecialchars($categoria !== '' ? $categoria : 'SEM CATEGORIA'); ?></td>
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
                    <tr>
                        <td class="muted"><?php echo htmlspecialchars((string)$row['codigo']); ?></td>
                        <td style="white-space:pre-line;"><?php echo htmlspecialchars((string)$row['descricao']); ?></td>
                        <td class="num"><?php echo OrcamentoItem::formatNumber($quantidade); ?></td>
                        <td><?php echo htmlspecialchars((string)$row['unidade']); ?></td>
                        <td class="num admin-col" style="display:none;"><?php echo OrcamentoItem::formatMoney($custoMaterialUnit); ?></td>
                        <td class="num admin-col" style="display:none;"><?php echo OrcamentoItem::formatMoney($custoMaoObraUnit); ?></td>
                        <td class="num admin-col" style="display:none;"><?php echo number_format($percentualBdi, 1, ',', '.'); ?>%</td>
                        <td class="num admin-col" style="display:none;"><?php echo OrcamentoItem::formatMoney($margemUnit); ?></td>
                        <td class="num"><?php echo OrcamentoItem::formatMoney($valorCobranca); ?></td>
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
                    <td colspan="9" class="num admin-col-visible">Total <?php echo htmlspecialchars((string)$categoria); ?></td>
                    <td colspan="5" class="num admin-col-hidden" style="display:none;">Total <?php echo htmlspecialchars((string)$categoria); ?></td>
                    <td class="num"><?php echo OrcamentoItem::formatMoney($subtotalCategoria); ?></td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <?php if (!empty($grouped)) : ?>
            <tr class="total-row">
                <td colspan="9" class="num admin-col-visible">Total Geral</td>
                <td colspan="5" class="num admin-col-hidden" style="display:none;">Total Geral</td>
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
    const adminColVisible = document.querySelectorAll('.admin-col-visible');
    const adminColHidden = document.querySelectorAll('.admin-col-hidden');
    
    if (this.checked) {
        adminCols.forEach(col => col.style.display = '');
        adminColVisible.forEach(col => col.style.display = 'none');
        adminColHidden.forEach(col => col.style.display = '');
    } else {
        adminCols.forEach(col => col.style.display = 'none');
        adminColVisible.forEach(col => col.style.display = '');
        adminColHidden.forEach(col => col.style.display = 'none');
    }
});
</script>
