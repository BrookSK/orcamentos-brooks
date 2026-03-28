<?php

declare(strict_types=1);

?><div class="card">
    <div style="padding:16px; display:flex; justify-content:space-between; align-items:center; gap:12px;">
        <div>
            <div style="font-weight:800;">Orçamentos</div>
            <div class="muted" style="font-size:12px; margin-top:4px;">Crie e gerencie orçamentos com itens e exportação</div>
        </div>
        <div style="display:flex; gap:8px;">
            <a class="btn primary" href="/?route=orcamentos/create">Novo orçamento Manual</a>
            <a class="btn" style="background:#C9973A; color:#000; font-weight:600;" href="/?route=orcamentos/createSinapi">🧮 Novo orçamento SINAPI</a>
        </div>
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
                <td>
                    <?php echo htmlspecialchars((string)$o['numero_proposta']); ?>
                    <?php if (($o['tipo_orcamento'] ?? 'manual') === 'sinapi') : ?>
                        <span style="display:inline-block;margin-left:6px;padding:2px 6px;background:#C9973A;color:#000;font-size:9px;font-weight:700;border-radius:4px;letter-spacing:0.5px;">SINAPI</span>
                    <?php endif; ?>
                </td>
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
