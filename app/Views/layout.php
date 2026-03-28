<?php

declare(strict_types=1);

?><!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orçamentos</title>
    <style>
        :root {
            --bg: #0b1220;
            --card: #0f1a2e;
            --muted: #9fb0cc;
            --text: #e7eefc;
            --line: rgba(255,255,255,.10);
            --accent: #5b8cff;
            --danger: #ff5b7a;
        }
        * { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Helvetica Neue", Helvetica, sans-serif;
            background: radial-gradient(1200px 700px at 20% 0%, #11234a 0%, var(--bg) 55%), var(--bg);
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            color: var(--text);
        }
        .wrap {
            max-width: 1100px;
            margin: 32px auto;
            padding: 0 16px;
        }
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }
        h1 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            letter-spacing: .2px;
        }
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn {
            appearance: none;
            border: 1px solid var(--line);
            background: rgba(255,255,255,.06);
            color: var(--text);
            padding: 10px 12px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
        }
        .btn:hover { border-color: rgba(255,255,255,.20); }
        .btn.primary { background: rgba(91,140,255,.18); border-color: rgba(91,140,255,.35); }
        .btn.danger { background: rgba(255,91,122,.14); border-color: rgba(255,91,122,.35); }
        .card {
            border: 1px solid var(--line);
            background: rgba(255,255,255,.04);
            border-radius: 16px;
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        thead th {
            text-align: left;
            font-size: 12px;
            color: var(--muted);
            font-weight: 600;
            padding: 12px;
            border-bottom: 1px solid var(--line);
            background: rgba(255,255,255,.03);
        }
        tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(255,255,255,.06);
            vertical-align: top;
            font-size: 13px;
            word-wrap: break-word;
            word-break: break-word;
            max-width: 300px;
        }
        tbody td:last-child {
            white-space: nowrap;
            min-width: 120px;
        }
        tbody tr:hover td { background: rgba(255,255,255,.02); }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .muted { color: var(--muted); }
        .category-row td {
            background: rgba(91,140,255,.10);
            border-bottom: 1px solid rgba(91,140,255,.20);
            font-weight: 700;
            color: #d9e6ff;
        }
        .subtotal-row td {
            background: rgba(255,255,255,.03);
            font-weight: 700;
        }
        .total-row td {
            background: rgba(91,140,255,.14);
            font-weight: 800;
            border-bottom: 0;
        }
        .form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 16px;
        }
        .field { display: flex; flex-direction: column; gap: 6px; }
        label { font-size: 12px; color: var(--muted); }
        input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: rgba(255,255,255,.04);
            color: var(--text);
            outline: none;
        }
        input:focus { border-color: rgba(91,140,255,.55); }
        select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: rgba(255,255,255,.04);
            color: var(--text);
            outline: none;
        }
        select:focus { border-color: rgba(91,140,255,.55); }
        select option,
        select optgroup {
            background: #fff;
            color: #111;
        }
        .field.full { grid-column: 1 / -1; }
        .error {
            font-size: 12px;
            color: #ffd1da;
        }
        footer {
            margin-top: 10px;
            color: var(--muted);
            font-size: 12px;
        }
        .row-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .inline { display: inline; }
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <div>
            <h1>Orçamentos</h1>
            <div class="muted" style="margin-top:4px; font-size:12px;">Cadastro de orçamentos, itens e exportação</div>
        </div>
        <div class="actions">
            <a class="btn primary" href="/?route=orcamentos/create">Novo orçamento</a>
            <a class="btn" href="/?route=orcamentos/index">Orçamentos</a>
            <a class="btn" href="/?route=orcamentos/grupos">Grupos</a>
            <a class="btn" href="/?route=orcamentos/categorias">Categorias</a>
            <a class="btn" href="/?route=orcamentos/unidades">Unidades</a>
            <a class="btn" href="/?route=sinapi/gerenciar">Itens SINAPI</a>
        </div>
    </header>

    <?php if (isset($content)) { echo $content; } ?>

    <!-- <footer>
        Banco configurado via variáveis de ambiente em <span class="muted">config/config.php</span>
    </footer> -->
</div>
</body>
</html>
