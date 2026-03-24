<?php

declare(strict_types=1);

?><div class="card">
    <form method="post" action="/?route=items/store">
        <div class="form">
            <div class="field full">
                <label>Nome</label>
                <input name="nome" value="<?php echo htmlspecialchars((string)($item['nome'] ?? '')); ?>">
                <?php if (!empty($errors['nome'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['nome']); ?></div><?php endif; ?>
            </div>

            <div class="field">
                <label>Categoria</label>
                <input name="categoria" value="<?php echo htmlspecialchars((string)($item['categoria'] ?? '')); ?>">
                <?php if (!empty($errors['categoria'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['categoria']); ?></div><?php endif; ?>
            </div>

            <div class="field">
                <label>Unidade</label>
                <input name="unidade" value="<?php echo htmlspecialchars((string)($item['unidade'] ?? '')); ?>">
                <?php if (!empty($errors['unidade'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['unidade']); ?></div><?php endif; ?>
            </div>

            <div class="field">
                <label>Quantidade</label>
                <input name="quantidade" inputmode="decimal" value="<?php echo htmlspecialchars((string)($item['quantidade'] ?? '')); ?>">
                <?php if (!empty($errors['quantidade'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['quantidade']); ?></div><?php endif; ?>
            </div>

            <div class="field">
                <label>Valor Unitário</label>
                <input name="valor_unitario" inputmode="decimal" value="<?php echo htmlspecialchars((string)($item['valor_unitario'] ?? '')); ?>">
                <?php if (!empty($errors['valor_unitario'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['valor_unitario']); ?></div><?php endif; ?>
            </div>

            <div class="field full" style="display:flex; justify-content:flex-end; gap:8px; flex-direction:row; align-items:center;">
                <a class="btn" href="/?route=items/index">Cancelar</a>
                <button class="btn primary" type="submit">Salvar</button>
            </div>
        </div>
    </form>
</div>
