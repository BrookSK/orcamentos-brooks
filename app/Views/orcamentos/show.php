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
                <label>Valor unitário</label>
                <input name="valor_unitario" inputmode="decimal" value="<?php echo htmlspecialchars((string)($item['valor_unitario'] ?? '')); ?>">
                <?php if (!empty($errors['valor_unitario'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['valor_unitario']); ?></div><?php endif; ?>
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
    <table>
        <thead>
        <tr>
            <th style="width:90px">Código</th>
            <th>Descrição</th>
            <th class="num" style="width:90px">Quant.</th>
            <th style="width:80px">Unid</th>
            <th class="num" style="width:120px">Valor Unit.</th>
            <th class="num" style="width:120px">Valor Total</th>
            <th style="width:140px"></th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($grouped)) : ?>
            <tr><td colspan="7" class="muted">Nenhum item cadastrado neste orçamento.</td></tr>
        <?php endif; ?>

        <?php foreach ($grouped as $grupo => $cats) : ?>
            <tr class="category-row">
                <td colspan="7"><?php echo htmlspecialchars($grupo !== '' ? $grupo : 'SEM GRUPO'); ?></td>
            </tr>

            <?php foreach ($cats as $categoria => $rows) : ?>
                <tr class="subtotal-row">
                    <td colspan="7"><?php echo htmlspecialchars($categoria !== '' ? $categoria : 'SEM CATEGORIA'); ?></td>
                </tr>

                <?php $subtotalCategoria = 0.0; ?>
                <?php foreach ($rows as $row) : ?>
                    <?php
                        $quantidade = (float)($row['quantidade'] ?? 0);
                        $valorUnitario = (float)($row['valor_unitario'] ?? 0);
                        $valorTotal = round($quantidade * $valorUnitario, 2);
                        $subtotalCategoria += $valorTotal;
                        $totalGeral += $valorTotal;
                    ?>
                    <tr>
                        <td class="muted"><?php echo htmlspecialchars((string)$row['codigo']); ?></td>
                        <td style="white-space:pre-line;"><?php echo htmlspecialchars((string)$row['descricao']); ?></td>
                        <td class="num"><?php echo OrcamentoItem::formatNumber((float)$row['quantidade']); ?></td>
                        <td><?php echo htmlspecialchars((string)$row['unidade']); ?></td>
                        <td class="num"><?php echo OrcamentoItem::formatMoney((float)$row['valor_unitario']); ?></td>
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
                    <td colspan="5" class="num">Total <?php echo htmlspecialchars((string)$categoria); ?></td>
                    <td class="num"><?php echo OrcamentoItem::formatMoney($subtotalCategoria); ?></td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <?php if (!empty($grouped)) : ?>
            <tr class="total-row">
                <td colspan="5" class="num">Total Geral</td>
                <td class="num"><?php echo OrcamentoItem::formatMoney($totalGeral); ?></td>
                <td></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
