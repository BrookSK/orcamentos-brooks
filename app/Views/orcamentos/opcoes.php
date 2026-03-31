<?php

declare(strict_types=1);

$tipo = (string)($tipo ?? '');
$titulo = (string)($titulo ?? '');
$items = $items ?? [];
$errors = $errors ?? [];
$success = isset($_GET['success']) && $_GET['success'] === '1';

?><div class="card" style="padding:16px; margin-bottom:12px;">
    <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap;">
        <div>
            <div style="font-weight:800; font-size:14px;"><?php echo htmlspecialchars($titulo); ?></div>
            <div class="muted" style="margin-top:4px; font-size:12px;">Cadastre e mantenha sua lista para seleção nos itens.</div>
        </div>
        <div class="actions">
            <a class="btn" href="/?route=orcamentos/index">Voltar</a>
        </div>
    </div>
</div>

<?php if ($success) : ?>
<div class="card" style="padding:16px; margin-bottom:12px; background:#e8f5e9; border-left:4px solid #4caf50;">
    <div style="color:#2e7d32; font-weight:600;">✓ Atualizado com sucesso! Todos os itens foram atualizados.</div>
</div>
<?php endif; ?>

<?php if (!empty($errors['geral'])) : ?>
<div class="card" style="padding:16px; margin-bottom:12px; background:#fee; border-left:4px solid #c33;">
    <div style="color:#c33; font-weight:600;">⚠️ <?php echo htmlspecialchars((string)$errors['geral']); ?></div>
</div>
<?php endif; ?>

<div class="card" style="padding:16px; margin-bottom:12px;">
    <form method="post" action="/?route=orcamentos/<?php echo htmlspecialchars($tipo); ?>sStore">
        <div class="form" style="padding:0;">
            <div class="field full">
                <label>Novo</label>
                <input name="nome" placeholder="Digite e clique em Adicionar" value="">
                <?php if (!empty($errors['nome'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['nome']); ?></div><?php endif; ?>
            </div>
            <div class="field" style="display:flex; justify-content:flex-end; align-items:flex-end;">
                <button class="btn primary" type="submit">Adicionar</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>Nome</th>
            <th style="width:200px"></th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($items)) : ?>
            <tr><td colspan="2" class="muted">Nenhum cadastro ainda.</td></tr>
        <?php endif; ?>
        <?php foreach ($items as $row) : ?>
            <tr data-id="<?php echo (int)($row['id'] ?? 0); ?>">
                <td>
                    <span class="nome-display"><?php echo htmlspecialchars((string)($row['nome'] ?? '')); ?></span>
                    <form class="nome-edit" method="post" action="/?route=orcamentos/<?php echo htmlspecialchars($tipo); ?>sUpdate" style="display:none;">
                        <input type="hidden" name="id" value="<?php echo (int)($row['id'] ?? 0); ?>">
                        <input type="text" name="nome" value="<?php echo htmlspecialchars((string)($row['nome'] ?? '')); ?>" style="width:100%;padding:4px;">
                    </form>
                </td>
                <td>
                    <div class="row-actions">
                        <button class="btn btn-edit" type="button" onclick="editarNome(this)">Editar</button>
                        <button class="btn primary btn-save" type="button" onclick="salvarNome(this)" style="display:none;">Salvar</button>
                        <button class="btn btn-cancel" type="button" onclick="cancelarEdicao(this)" style="display:none;">Cancelar</button>
                        <form class="inline" method="post" action="/?route=orcamentos/<?php echo htmlspecialchars($tipo); ?>sDelete" onsubmit="return confirm('Excluir este registro?');">
                            <input type="hidden" name="id" value="<?php echo (int)($row['id'] ?? 0); ?>">
                            <button class="btn danger" type="submit">Excluir</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function editarNome(btn) {
    const tr = btn.closest('tr');
    const display = tr.querySelector('.nome-display');
    const form = tr.querySelector('.nome-edit');
    const btnEdit = tr.querySelector('.btn-edit');
    const btnSave = tr.querySelector('.btn-save');
    const btnCancel = tr.querySelector('.btn-cancel');
    
    display.style.display = 'none';
    form.style.display = 'block';
    btnEdit.style.display = 'none';
    btnSave.style.display = 'inline-block';
    btnCancel.style.display = 'inline-block';
    
    form.querySelector('input[name="nome"]').focus();
}

function salvarNome(btn) {
    const tr = btn.closest('tr');
    const form = tr.querySelector('.nome-edit');
    form.submit();
}

function cancelarEdicao(btn) {
    const tr = btn.closest('tr');
    const display = tr.querySelector('.nome-display');
    const form = tr.querySelector('.nome-edit');
    const btnEdit = tr.querySelector('.btn-edit');
    const btnSave = tr.querySelector('.btn-save');
    const btnCancel = tr.querySelector('.btn-cancel');
    
    display.style.display = 'inline';
    form.style.display = 'none';
    btnEdit.style.display = 'inline-block';
    btnSave.style.display = 'none';
    btnCancel.style.display = 'none';
    
    // Restaurar valor original
    const originalValue = display.textContent;
    form.querySelector('input[name="nome"]').value = originalValue;
}

// Permitir salvar com Enter
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.nome-edit input[name="nome"]').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const tr = this.closest('tr');
                const btnSave = tr.querySelector('.btn-save');
                btnSave.click();
            }
        });
    });
});
</script>
