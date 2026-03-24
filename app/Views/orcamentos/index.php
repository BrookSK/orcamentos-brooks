<?php

declare(strict_types=1);

?><div class="card">
    <div style="padding:16px; display:flex; justify-content:space-between; align-items:center; gap:12px;">
        <div>
            <div style="font-weight:800;">Orçamentos</div>
            <div class="muted" style="font-size:12px; margin-top:4px;">Crie e gerencie orçamentos com itens e exportação</div>
        </div>
        <a class="btn primary" href="/?route=orcamentos/create">Novo orçamento</a>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:90px">ID</th>
                <th>Nº Proposta</th>
                <th>Cliente</th>
                <th>Obra</th>
                <th style="width:220px"></th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($orcamentos)) : ?>
            <tr><td colspan="5" class="muted">Nenhum orçamento cadastrado.</td></tr>
        <?php endif; ?>

        <?php foreach (($orcamentos ?? []) as $o) : ?>
            <tr>
                <td class="muted"><?php echo (int)$o['id']; ?></td>
                <td><?php echo htmlspecialchars((string)$o['numero_proposta']); ?></td>
                <td><?php echo htmlspecialchars((string)$o['cliente_nome']); ?></td>
                <td><?php echo htmlspecialchars((string)($o['obra_nome'] ?? '')); ?></td>
                <td>
                    <div class="row-actions">
                        <a class="btn" href="/?route=orcamentos/show&id=<?php echo (int)$o['id']; ?>">Abrir</a>
                        <a class="btn" href="/?route=orcamentos/edit&id=<?php echo (int)$o['id']; ?>">Editar</a>
                        <form class="inline" method="post" action="/?route=orcamentos/delete" onsubmit="return confirm('Excluir este orçamento?');">
                            <input type="hidden" name="id" value="<?php echo (int)$o['id']; ?>">
                            <button class="btn danger" type="submit">Excluir</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
