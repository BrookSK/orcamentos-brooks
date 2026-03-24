<?php

declare(strict_types=1);

$tipo = (string)($tipo ?? '');
$titulo = (string)($titulo ?? '');
$items = $items ?? [];
$errors = $errors ?? [];

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
            <th style="width:120px"></th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($items)) : ?>
            <tr><td colspan="2" class="muted">Nenhum cadastro ainda.</td></tr>
        <?php endif; ?>
        <?php foreach ($items as $row) : ?>
            <tr>
                <td><?php echo htmlspecialchars((string)($row['nome'] ?? '')); ?></td>
                <td>
                    <div class="row-actions">
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
