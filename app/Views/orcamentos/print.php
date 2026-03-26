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

$orcamentoId = (int)($orcamento['id'] ?? 0);
$resumoEtapas = $orcamentoId > 0 ? OrcamentoItem::getResumoPdfEtapas($orcamentoId) : [];
$totaisGerais = $orcamentoId > 0 ? OrcamentoItem::getTotaisGerais($orcamentoId) : ['total_cobranca' => 0];
$totalGeralCobranca = (float)($totaisGerais['total_cobranca'] ?? 0);

$groupTotals = [];
foreach ($grouped as $grupo => $cats) {
    $sum = 0.0;
    foreach ($cats as $categoria => $rows) {
        foreach ($rows as $row) {
            $sum += (float)($row['valor_total'] ?? 0);
        }
    }
    $groupTotals[(string)$grupo] = $sum;
}

$fixedLogoFile = __DIR__ . '/../../../public/brooks.png';
$fallbackLogoPath = (string)($orcamento['logo_path'] ?? '');

$logoFile = '';
if (is_file($fixedLogoFile)) {
    $logoFile = $fixedLogoFile;
} elseif ($fallbackLogoPath !== '') {
    $fallbackFile = __DIR__ . '/../../../public' . $fallbackLogoPath;
    if (is_file($fallbackFile)) {
        $logoFile = $fallbackFile;
    }
}

$logoSrc = '';
if ($logoFile !== '') {
    $ext = strtolower((string)pathinfo($logoFile, PATHINFO_EXTENSION));
    $mime = 'image/png';
    if ($ext === 'jpg' || $ext === 'jpeg') {
        $mime = 'image/jpeg';
    } elseif ($ext === 'webp') {
        $mime = 'image/webp';
    }
    $bin = file_get_contents($logoFile);
    if (is_string($bin) && $bin !== '') {
        $logoSrc = 'data:' . $mime . ';base64,' . base64_encode($bin);
    }
}

?><div>
    <div class="headerbar">
        <div class="header-left">
            <?php if ($logoSrc !== '') : ?>
                <img class="logoimg" src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Logo">
            <?php endif; ?>
        </div>
        <div class="header-right">
            <div><b><?php echo htmlspecialchars((string)($orcamento['empresa_nome'] ?? '')); ?></b></div>
            <div><?php echo htmlspecialchars((string)($orcamento['empresa_endereco'] ?? '')); ?></div>
            <div><?php echo htmlspecialchars((string)($orcamento['empresa_telefone'] ?? '')); ?></div>
            <?php if (trim((string)($orcamento['empresa_email'] ?? '')) !== '') : ?>
                <div><?php echo htmlspecialchars((string)($orcamento['empresa_email'] ?? '')); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="box boxtchr">
        <div class="col">
            <div><b>Cliente:</b> <?php echo htmlspecialchars((string)$orcamento['cliente_nome']); ?></div>
            <div><b>Arq.:</b> <?php echo htmlspecialchars((string)($orcamento['arquiteto_nome'] ?? '')); ?></div>
            <div><b>Obra:</b> <?php echo htmlspecialchars((string)($orcamento['obra_nome'] ?? '')); ?></div>
            <div><b>End.:</b> <?php echo htmlspecialchars((string)($orcamento['endereco_obra'] ?? '')); ?></div>
            <div><b>Local:</b> <?php echo htmlspecialchars((string)($orcamento['local_obra'] ?? '')); ?></div>
            <div><b>Data:</b> <?php echo htmlspecialchars((string)($orcamento['data'] ?? '')); ?></div>
        </div>
        <div class="col">
            <div><b>Rev.:</b> <?php echo htmlspecialchars((string)($orcamento['rev'] ?? '')); ?></div>
            <div><b>Área:</b> <?php echo htmlspecialchars((string)($orcamento['area_m2'] ?? '')); ?> m²</div>
            <div><b>Proposta:</b> <?php echo htmlspecialchars((string)$orcamento['numero_proposta']); ?></div>
            <div><b>Contrato:</b> <?php echo htmlspecialchars((string)($orcamento['contrato'] ?? '')); ?></div>
            <div><b>Prazo:</b> <?php echo htmlspecialchars((string)($orcamento['prazo_dias'] ?? '')); ?> dias</div>
        </div>
    </div>

    <div class="title">ESTIMATIVA DE CUSTOS</div>

    <?php foreach ($grouped as $grupo => $cats) : ?>
        <div class="subtitle"><?php echo htmlspecialchars((string)$grupo); ?></div>

        <table>
            <thead>
            <tr>
                <th style="width:70px">CÓDIGO</th>
                <th>DESCRIÇÃO</th>
                <th style="width:80px">QUANT.</th>
                <th style="width:70px">UNID</th>
                <th style="width:110px">VALOR UNIT.</th>
                <th style="width:110px">VALOR TOTAL</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($cats as $categoria => $rows) : ?>
                <tr class="cat"><td colspan="6"><?php echo htmlspecialchars($categoria); ?></td></tr>

                <?php $subtotalCategoria = 0.0; ?>
                <?php foreach ($rows as $row) : ?>
                    <?php
                        $valorTotal = (float)($row['valor_total'] ?? 0);
                        $subtotalCategoria += $valorTotal;
                        $totalGeral += $valorTotal;
                    ?>
                    <tr>
                        <td class="center"><?php echo htmlspecialchars((string)$row['codigo']); ?></td>
                        <td style="white-space:pre-line;"><?php echo htmlspecialchars((string)$row['descricao']); ?></td>
                        <td class="num"><?php echo OrcamentoItem::formatNumber((float)$row['quantidade']); ?></td>
                        <td class="center"><?php echo htmlspecialchars((string)$row['unidade']); ?></td>
                        <td class="num"><?php echo OrcamentoItem::formatMoney((float)$row['valor_unitario']); ?></td>
                        <td class="num"><?php echo OrcamentoItem::formatMoney($valorTotal); ?></td>
                    </tr>
                <?php endforeach; ?>

                <tr class="subtotal">
                    <td colspan="4"></td>
                    <td class="center"><b>R$</b></td>
                    <td class="num"><b><?php echo OrcamentoItem::formatMoney($subtotalCategoria); ?></b></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <!-- <?php //if (!empty($grouped)) : ?>
        <div class="obs"><b>Total Geral:</b> R$ <?php //echo OrcamentoItem::formatMoney($totalGeral); ?></div>
    <?php //endif; ?> -->

    <div class="pagebreak"></div>

    <div class="headerbar">
        <div class="header-left">
            <?php if ($logoSrc !== '') : ?>
                <img class="logoimg" src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Logo">
            <?php endif; ?>
        </div>
        <div class="header-right">
            <div><b><?php echo htmlspecialchars((string)($orcamento['empresa_nome'] ?? '')); ?></b></div>
            <div><?php echo htmlspecialchars((string)($orcamento['empresa_endereco'] ?? '')); ?></div>
            <div><?php echo htmlspecialchars((string)($orcamento['empresa_telefone'] ?? '')); ?></div>
            <?php if (trim((string)($orcamento['empresa_email'] ?? '')) !== '') : ?>
                <div><?php echo htmlspecialchars((string)($orcamento['empresa_email'] ?? '')); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="box boxtchr">
        <div class="col">
            <div><b>Cliente:</b> <?php echo htmlspecialchars((string)$orcamento['cliente_nome']); ?></div>
            <div><b>Arq.:</b> <?php echo htmlspecialchars((string)($orcamento['arquiteto_nome'] ?? '')); ?></div>
            <div><b>Obra:</b> <?php echo htmlspecialchars((string)($orcamento['obra_nome'] ?? '')); ?></div>
            <div><b>End.:</b> <?php echo htmlspecialchars((string)($orcamento['endereco_obra'] ?? '')); ?></div>
            <div><b>Local:</b> <?php echo htmlspecialchars((string)($orcamento['local_obra'] ?? '')); ?></div>
            <div><b>Data:</b> <?php echo htmlspecialchars((string)($orcamento['data'] ?? '')); ?></div>
        </div>
        <div class="col">
            <div><b>Rev.:</b> <?php echo htmlspecialchars((string)($orcamento['rev'] ?? '')); ?></div>
            <div><b>Área:</b> <?php echo htmlspecialchars((string)($orcamento['area_m2'] ?? '')); ?> m²</div>
            <div><b>Proposta:</b> <?php echo htmlspecialchars((string)$orcamento['numero_proposta']); ?></div>
            <div><b>Contrato:</b> <?php echo htmlspecialchars((string)($orcamento['contrato'] ?? '')); ?></div>
            <div><b>Prazo:</b> <?php echo htmlspecialchars((string)($orcamento['prazo_dias'] ?? '')); ?> dias</div>
        </div>
    </div>

    <div class="summary-title">ESTIMATIVA DE CUSTOS</div>

    <table class="summary">
        <thead>
        <tr>
            <th style="width:40px">#</th>
            <th>DESCRIÇÃO</th>
            <th style="width:140px">CATEGORIA</th>
            <th style="width:40px">R$</th>
            <th style="width:120px">TOTAL</th>
            <th style="width:90px">% da etapa</th>
            <th style="width:90px">% do total</th>
            <th style="width:110px">Realizado % da etapa</th>
            <th style="width:110px">Realizado % do total</th>
        </tr>
        </thead>
        <tbody>
        <?php $idx = 1; ?>
        <?php
            $totaisPorEtapa = [];
            foreach ($resumoEtapas as $row) {
                $etapaNome = (string)($row['etapa'] ?? '');
                $valorLinha = (float)($row['total_cobranca'] ?? 0);
                $totaisPorEtapa[$etapaNome] = ($totaisPorEtapa[$etapaNome] ?? 0.0) + $valorLinha;
            }
        ?>

        <?php foreach ($resumoEtapas as $etapa) : ?>
            <?php
                $categoriaNome = (string)($etapa['etapa'] ?? '');
                $descricaoNome = (string)($etapa['grupo'] ?? '');
                $valorLinha = (float)($etapa['total_cobranca'] ?? 0);
                $totalDaEtapa = (float)($totaisPorEtapa[$categoriaNome] ?? 0);

                $percentualDaEtapa = $totalDaEtapa > 0 ? ($valorLinha / $totalDaEtapa) * 100 : 0;
                $percentualDoTotal = $totalGeralCobranca > 0 ? ($valorLinha / $totalGeralCobranca) * 100 : 0;

                $percentualRealizado = (float)($etapa['percentual_realizado'] ?? 0);
                if ($percentualRealizado < 0) {
                    $percentualRealizado = 0;
                }
                if ($percentualRealizado > 100) {
                    $percentualRealizado = 100;
                }
                $valorRealizadoLinha = $valorLinha * ($percentualRealizado / 100);

                $realizadoPercentualDaEtapa = $totalDaEtapa > 0 ? ($valorRealizadoLinha / $totalDaEtapa) * 100 : 0;
                $realizadoPercentualDoTotal = $totalGeralCobranca > 0 ? ($valorRealizadoLinha / $totalGeralCobranca) * 100 : 0;
            ?>
            <tr>
                <td class="center"><?php echo (int)$idx; ?></td>
                <td><?php echo htmlspecialchars($descricaoNome); ?></td>
                <td><?php echo htmlspecialchars($categoriaNome); ?></td>
                <td class="center">R$</td>
                <td class="num"><?php echo OrcamentoItem::formatMoney($valorLinha); ?></td>
                <td class="num"><?php echo number_format($percentualDaEtapa, 2, ',', '.') . '%'; ?></td>
                <td class="num"><?php echo number_format($percentualDoTotal, 2, ',', '.') . '%'; ?></td>
                <td class="num"><?php echo number_format($realizadoPercentualDaEtapa, 2, ',', '.') . '%'; ?></td>
                <td class="num"><?php echo number_format($realizadoPercentualDoTotal, 2, ',', '.') . '%'; ?></td>
            </tr>
            <?php $idx++; ?>
        <?php endforeach; ?>
        <tr class="total">
            <td colspan="3"></td>
            <td class="center">R$</td>
            <td class="num"><?php echo OrcamentoItem::formatMoney((float)$totalGeralCobranca); ?></td>
            <td class="num"></td>
            <td class="num">100,00%</td>
            <td class="num"></td>
            <td class="num">100,00%</td>
        </tr>
        </tbody>
    </table>
</div>
