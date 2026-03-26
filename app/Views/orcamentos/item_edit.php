<?php

declare(strict_types=1);

?><div class="card" style="padding:16px; margin-bottom:12px;">
    <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap;">
        <div>
            <div style="font-weight:800; font-size:14px;">Editar item</div>
            <div class="muted" style="margin-top:4px; font-size:12px;">Orçamento: <?php echo htmlspecialchars((string)$orcamento['numero_proposta']); ?></div>
        </div>
        <div class="actions">
            <a class="btn" href="/?route=orcamentos/show&id=<?php echo (int)$orcamento['id']; ?>">Voltar</a>
        </div>
    </div>
</div>

<div class="card" style="padding:16px;">
    <form method="post" action="/?route=orcamentos/itemUpdate">
        <input type="hidden" name="orcamento_id" value="<?php echo (int)$orcamento['id']; ?>">
        <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">
        <?php if (((string)($_GET['debug'] ?? '') === '1') || ((string)getenv('APP_DEBUG') === '1')) : ?>
            <input type="hidden" name="debug" value="1">
        <?php endif; ?>

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
                <label>Ordem</label>
                <input name="ordem" inputmode="numeric" value="<?php echo htmlspecialchars((string)($item['ordem'] ?? '0')); ?>">
            </div>

            <div class="field full">
                <label>Descrição</label>
                <textarea name="descricao" rows="6" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.04); color:var(--text); outline:none; resize:vertical;"><?php echo htmlspecialchars((string)($item['descricao'] ?? '')); ?></textarea>
                <?php if (!empty($errors['descricao'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['descricao']); ?></div><?php endif; ?>
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

            <div class="field">
                <label>% realizado</label>
                <input name="percentual_realizado" inputmode="decimal" value="<?php echo htmlspecialchars((string)($item['percentual_realizado'] ?? '0')); ?>">
            </div>

            <div class="field" style="display:flex; justify-content:flex-end; align-items:flex-end;">
                <button class="btn primary" type="submit">Salvar alterações</button>
            </div>
        </div>
    </form>
</div>
