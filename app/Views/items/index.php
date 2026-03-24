<?php

declare(strict_types=1);

use App\Models\Item;

$grouped = [];
foreach (($items ?? []) as $item) {
    $cat = (string)($item['categoria'] ?? '');
    $grouped[$cat][] = $item;
}

$totalGeral = 0.0;

?><div class="card">
    <table>
        <thead>
            <tr>
                <th style="width: 20%">Categoria</th>
                <th>Nome</th>
                <th style="width: 8%">Unidade</th>
                <th class="num" style="width: 10%">Quantidade</th>
                <th class="num" style="width: 14%">Valor Unitário</th>
                <th class="num" style="width: 14%">Valor Total</th>
                <th style="width: 14%"></th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$grouped) : ?>
            <tr>
                <td colspan="7" class="muted">Nenhum item cadastrado.</td>
            </tr>
        <?php endif; ?>

        <?php foreach ($grouped as $categoria => $rows) : ?>
            <tr class="category-row">
                <td colspan="7"><?php echo htmlspecialchars($categoria !== '' ? $categoria : 'SEM CATEGORIA'); ?></td>
            </tr>

            <?php $subtotal = 0.0; ?>
            <?php foreach ($rows as $row) : ?>
                <?php
                    $valorTotal = (float)($row['valor_total'] ?? 0);
                    $subtotal += $valorTotal;
                    $totalGeral += $valorTotal;
                ?>
                <tr>
                    <td class="muted"><?php echo htmlspecialchars((string)$row['categoria']); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['nome']); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['unidade']); ?></td>
                    <td class="num"><?php echo Item::formatNumber((float)$row['quantidade']); ?></td>
                    <td class="num"><?php echo Item::formatMoney((float)$row['valor_unitario']); ?></td>
                    <td class="num"><?php echo Item::formatMoney($valorTotal); ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="btn" href="/?route=items/edit&id=<?php echo (int)$row['id']; ?>">Editar</a>
                            <form class="inline" method="post" action="/?route=items/delete" onsubmit="return confirm('Excluir este item?');">
                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                <button class="btn danger" type="submit">Excluir</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr class="subtotal-row">
                <td colspan="5" class="num">Subtotal</td>
                <td class="num"><?php echo Item::formatMoney($subtotal); ?></td>
                <td></td>
            </tr>
        <?php endforeach; ?>

        <?php if ($grouped) : ?>
            <tr class="total-row">
                <td colspan="5" class="num">Total Geral</td>
                <td class="num"><?php echo Item::formatMoney($totalGeral); ?></td>
                <td></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
