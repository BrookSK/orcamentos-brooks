<?php

declare(strict_types=1);

?><!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orçamento</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color:#111; margin:0; }
        .page { padding: 18px 22px; }
        .headerbar { background:#fff; padding: 10px 12px; border: 1px solid #cfcfcf; display: table; width: 100%; }
        .header-left { width: 160px; display: table-cell; vertical-align: middle; }
        .logoimg { max-width: 160px; max-height: 58px; display: block; }
        .header-right { display: table-cell; vertical-align: middle; text-align:right; font-size: 11px; line-height: 1.35; color:#111; }
        .box { margin-top: 10px; display: table; width: 100%; }
        .box .col { display: table-cell; width: 50%; font-size: 11px; line-height: 1.55; vertical-align: top; }
        .box .col b { display:inline-block; min-width: 66px; }
        .boxtchr { background:#fff; color:#111; border:1px solid #cfcfcf; padding:10px 12px; }
        .title { margin-top: 14px; background:#fff; color:#111; padding:8px 10px; text-align:center; font-weight:700; font-size: 12px; border:1px solid #cfcfcf; border-bottom: 2px solid #5b8cff; }
        .subtitle { background:#5b8cff; padding:6px 10px; font-weight:700; font-size: 11px; text-align:center; color:#fff; }
        table { width:100%; border-collapse: collapse; font-size: 10.5px; margin-top: 8px; }
        th, td { border:1px solid #cfcfcf; padding:6px 6px; vertical-align: top; }
        th { background:#5b8cff; color:#fff; text-align:center; font-weight:700; }
        td.num { text-align:right; white-space:nowrap; }
        td.center { text-align:center; }
        tr.cat td { background:#7b7b7b; color:#fff; font-weight:700; }
        tr.subtotal td { background:#efefef; font-weight:700; }
        .obs { font-size: 10px; margin-top: 8px; }
        .boxchr { background: #fff !important; }
        .pagebreak { page-break-before: always; }
        .summary-title { margin-top: 14px; background:#fff; color:#111; padding:8px 10px; text-align:center; font-weight:700; font-size: 12px; border:1px solid #cfcfcf; border-bottom: 2px solid #5b8cff; }
        table.summary { width:100%; border-collapse: collapse; font-size: 10.5px; margin-top: 8px; }
        table.summary th, table.summary td { border:1px solid #cfcfcf; padding:6px 6px; vertical-align: top; }
        table.summary th { background:#5b8cff; color:#fff; text-align:center; font-weight:700; }
        table.summary td.num { text-align:right; white-space:nowrap; }
        table.summary td.center { text-align:center; }
        table.summary tr.total td { background:#efefef; font-weight:700; }
    </style>
</head>
<body>
<div class="page">
    <?php if (isset($content)) { echo $content; } ?>
</div>
</body>
</html>
