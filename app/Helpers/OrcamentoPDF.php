<?php

declare(strict_types=1);

namespace App\Helpers;

final class OrcamentoPDF
{
    public static function gerarHTML(int $orcamentoId, array $orcamento): string
    {
        $html = self::gerarCabecalhoHTML();
        $html .= self::gerarPaginaCapa($orcamento);
        $html .= self::gerarPaginasResumo($orcamentoId, $orcamento);
        $html .= self::gerarPaginasInstitucionais();
        $html .= self::gerarPaginaDetalhamento($orcamentoId, $orcamento);
        $html .= self::gerarRodapeHTML();
        return $html;
    }
    
    private static function gerarCabecalhoHTML(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Proposta Orçamentária - Brooks Construtora</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
:root {
--navy: #0d1117;
--navy2: #131b26;
--navy3: #1a2233;
--gold: #c9a84c;
--gold2: #e8c97a;
--white: #ffffff;
--black: #1a1a1a;
--muted: #666666;
--line: rgba(201,168,76,0.3);
--row-alt: #f9f9f9;
}
@page { margin: 5mm 5mm; size: A4; }
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Outfit', sans-serif; color: var(--black); line-height: 1.3; background: var(--white); font-size: 9px; }
.page { page-break-after: always; position: relative; width: 100%; min-height: 277mm; background: white; }

/* ══ CAPA ══ */
.page-cover { background: var(--navy); color: var(--white); overflow: hidden; }
.page-cover::before { content: ''; position: absolute; inset: 0; background: radial-gradient(ellipse 60% 50% at 80% 20%, rgba(201,168,76,.07) 0%, transparent 60%), radial-gradient(ellipse 40% 60% at 10% 80%, rgba(13,17,23,.9) 0%, transparent 70%); pointer-events: none; }
.accent-bar { position: absolute; left: 52px; top: 0; bottom: 0; width: 2px; background: linear-gradient(to bottom, transparent 5%, var(--gold) 30%, var(--gold) 70%, transparent 95%); }
.corner-tl, .corner-br { position: absolute; width: 30px; height: 30px; border-color: var(--gold); border-style: solid; opacity: .4; }
.corner-tl { top: 24px; left: 24px; border-width: 1.5px 0 0 1.5px; }
.corner-br { bottom: 20px; right: 24px; border-width: 0 1.5px 1.5px 0; }
.logo-block { position: absolute; top: 44px; left: 80px; }
.logo-block .brand { font-family: 'Cormorant Garamond', serif; font-size: 38px; font-weight: 700; letter-spacing: .06em; color: var(--white); line-height: 1; }
.logo-block .sub { font-size: 9px; letter-spacing: .28em; text-transform: uppercase; color: var(--gold); margin-top: 5px; font-weight: 300; }
.top-meta { position: absolute; top: 48px; right: 56px; text-align: right; }
.top-meta .label { font-size: 9px; letter-spacing: .18em; text-transform: uppercase; color: var(--muted); margin-bottom: 10px; }
.top-meta .badge { display: inline-block; padding: 5px 14px; border: 1px solid var(--line); border-radius: 2px; font-size: 10px; letter-spacing: .12em; color: var(--gold2); font-weight: 500; }
.top-rule { position: absolute; top: 128px; left: 52px; right: 56px; height: 1px; background: linear-gradient(to right, var(--gold), rgba(201,168,76,.1)); }
.hero { position: absolute; top: 50%; left: 80px; right: 80px; transform: translateY(-52%); text-align: center; }
.hero .eyebrow { font-size: 9.5px; letter-spacing: .3em; text-transform: uppercase; color: var(--gold); margin-bottom: 28px; font-weight: 400; }
.hero .title { font-family: 'Cormorant Garamond', serif; font-size: 56px; font-weight: 300; line-height: 1.08; color: var(--white); letter-spacing: .01em; margin-bottom: 16px; }
.hero .title strong { font-weight: 700; color: var(--gold2); display: block; }
.hero .subtitle { font-size: 11px; letter-spacing: .18em; color: var(--muted); text-transform: uppercase; font-weight: 300; margin-bottom: 52px; }
.ornament { display: flex; align-items: center; gap: 16px; justify-content: center; margin-bottom: 52px; }
.ornament span { display: block; height: 1px; width: 70px; background: var(--line); }
.ornament i { width: 6px; height: 6px; border: 1.5px solid var(--gold); transform: rotate(45deg); }
.info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; border: 1px solid rgba(201,168,76,.15); border-radius: 3px; overflow: hidden; background: rgba(201,168,76,.1); }
.info-cell { background: rgba(13,17,23,.8); padding: 20px 18px; text-align: left; }
.info-cell .cell-label { font-size: 8.5px; letter-spacing: .22em; text-transform: uppercase; color: var(--gold); margin-bottom: 8px; font-weight: 400; }
.info-cell .cell-value { font-size: 13px; color: var(--white); font-weight: 500; line-height: 1.3; }
.bottom-strip { position: absolute; bottom: 0; left: 0; right: 0; height: 5px; background: linear-gradient(to right, var(--gold), var(--gold2), var(--gold)); }
.bottom-meta { position: absolute; bottom: 24px; left: 80px; right: 56px; display: flex; justify-content: space-between; align-items: flex-end; }
.bottom-meta .doc-ref { font-size: 9px; letter-spacing: .15em; color: var(--muted); font-weight: 300; }
.bottom-meta .revision { text-align: right; }
.bottom-meta .revision span { display: block; font-size: 9px; letter-spacing: .12em; color: var(--muted); font-weight: 300; }
.bottom-meta .revision strong { display: block; font-size: 10px; color: var(--gold2); letter-spacing: .1em; margin-top: 2px; }

/* ══ HEADER PADRÃO ══ */
.page-header { background: var(--navy); width: 100%; position: relative; overflow: hidden; }
.page-header::before { content: ''; display: block; height: 4px; background: linear-gradient(to right, var(--gold), var(--gold2), var(--gold)); }
.page-header::after { content: ''; position: absolute; top: 0; right: 0; width: 300px; height: 100%; background: radial-gradient(ellipse at 80% 50%, rgba(201,168,76,.06) 0%, transparent 70%); pointer-events: none; }
.header-inner { display: flex; align-items: stretch; min-height: 60px; }
.header-project { flex: 1; padding: 10px 16px; border-right: 1px solid var(--line); display: flex; flex-direction: column; justify-content: center; gap: 2px; }
.header-project .doc-code { font-size: 7.5px; letter-spacing: .2em; text-transform: uppercase; color: var(--gold); font-weight: 500; margin-bottom: 2px; }
.proj-row { display: flex; align-items: baseline; gap: 4px; }
.proj-row .lbl { font-size: 6.5px; letter-spacing: .15em; text-transform: uppercase; color: var(--muted); font-weight: 400; min-width: 60px; }
.proj-row .val { font-size: 9px; color: var(--white); font-weight: 400; }
.header-logo { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px 24px; border-right: 1px solid var(--line); gap: 2px; }
.header-logo .brand { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 700; letter-spacing: .1em; color: var(--white); line-height: 1; }
.header-logo .sub { font-size: 6px; letter-spacing: .28em; text-transform: uppercase; color: var(--gold); font-weight: 300; }
.header-meta { display: flex; flex-direction: column; justify-content: center; padding: 10px 16px; gap: 3px; }
.meta-row { display: flex; align-items: baseline; gap: 4px; }
.meta-row .lbl { font-size: 6.5px; letter-spacing: .15em; text-transform: uppercase; color: var(--muted); font-weight: 400; min-width: 40px; }
.meta-row .val { font-size: 9px; color: var(--white); font-weight: 500; }
.meta-row .val.highlight { color: var(--gold2); }
.header-rule { height: 1px; background: linear-gradient(to right, var(--gold), rgba(201,168,76,.1) 70%, transparent); }

/* ══ TABELAS RESUMO ══ */
.page-content { padding: 15px 12px; }
.resumo-title { text-align: center; margin: 15px 0 12px 0; }
.resumo-title h2 { font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 600; color: var(--navy); margin-bottom: 6px; }
.resumo-title h3 { font-size: 9px; letter-spacing: .12em; text-transform: uppercase; color: var(--gold); font-weight: 400; }
.table-resumo-clean { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 9px; table-layout: fixed; }
.table-resumo-clean th { background: var(--navy); color: var(--white); padding: 8px 8px; text-align: left; font-weight: 500; border: none; letter-spacing: .06em; font-size: 8px; }
.table-resumo-clean td { padding: 8px 8px; border: 1px solid #e0e0e0; background: white; font-size: 9px; }
.table-resumo-clean td[style*="text-align:right"] { padding-right: 12px; }
.table-resumo-clean .subtotal-row { background: var(--gold); color: var(--navy); font-weight: 600; }
.table-resumo-clean .subtotal-row td { padding: 10px 12px; }
.table-resumo-clean .total-row { background: var(--navy); color: var(--white); font-weight: 600; font-size: 10px; }
.table-resumo-clean .total-row td { padding: 12px 12px; }
.table-areas { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 9px; table-layout: fixed; }
.table-areas th { background: #f5f5f5; padding: 8px; border: 1px solid #ddd; font-weight: 500; text-align: center; font-size: 8px; }
.table-areas td { padding: 8px; border: 1px solid #ddd; text-align: center; font-size: 9px; }
.table-areas .total-row { background: var(--navy); color: var(--white); font-weight: 600; }
.table-areas .total-row td { padding: 10px; }
.page-number { position: absolute; bottom: 8mm; right: 8mm; font-size: 8px; color: #999; letter-spacing: .08em; }

/* ══ DETALHAMENTO ══ */
.page-detalhamento { background: var(--white); }
.section-title { padding: 12px 12px 8px; display: flex; align-items: center; gap: 10px; }
.section-title h2 { font-family: 'Cormorant Garamond', serif; font-size: 16px; font-weight: 400; color: var(--black); letter-spacing: .03em; }
.section-title .badge { font-size: 7px; letter-spacing: .15em; text-transform: uppercase; color: var(--gold); border: 1px solid var(--line); padding: 3px 8px; border-radius: 2px; }
.etapa-section { margin: 15px 0; page-break-inside: avoid; }
.etapa-header { padding: 10px 12px 6px; }
.etapa-title { font-family: 'Cormorant Garamond', serif; font-size: 14px; color: var(--gold); font-weight: 600; letter-spacing: .04em; }
.table-wrap { padding: 0 8px 12px; }
.table-itens { width: 100%; border-collapse: collapse; border: 1px solid #ddd; font-size: 8px; table-layout: fixed; }
.table-itens thead tr { background: var(--navy); }
.table-itens thead th { padding: 8px 6px; font-size: 7px; letter-spacing: .1em; text-transform: uppercase; color: var(--white); font-weight: 500; border-bottom: 1px solid var(--navy); white-space: nowrap; text-align: center; }
.table-itens thead th:first-child { text-align: center; width: 7%; }
.table-itens thead th:nth-child(2) { text-align: left; width: 28%; padding-left: 8px; }
.table-itens thead th:nth-child(3) { text-align: center; width: 7%; }
.table-itens thead th:nth-child(4) { text-align: center; width: 6%; }
.table-itens thead th:nth-child(5) { text-align: right; width: 12%; padding-right: 8px; }
.table-itens thead th:nth-child(6) { text-align: right; width: 13%; padding-right: 8px; }
.table-itens thead th:nth-child(7) { text-align: center; width: 8%; }
.table-itens thead th:nth-child(8) { text-align: center; width: 8%; }
.table-itens thead th:nth-child(9) { text-align: center; width: 11%; }
.table-itens tbody tr { border-bottom: 1px solid #e8e8e8; }
.table-itens tbody tr:nth-child(even) { background: var(--row-alt); }
.table-itens td { padding: 7px 6px; font-size: 8px; color: var(--black); vertical-align: middle; line-height: 1.3; }
.table-itens td:first-child { font-size: 7.5px; color: var(--muted); letter-spacing: .03em; font-weight: 500; text-align: center; }
.table-itens td:nth-child(2) { font-size: 8px; color: var(--black); font-weight: 400; padding-left: 8px; text-align: left; }
.table-itens td:nth-child(3) { text-align: center; font-variant-numeric: tabular-nums; color: var(--black); font-weight: 500; }
.table-itens td:nth-child(4) { text-align: center; color: var(--muted); font-size: 7.5px; }
.table-itens td:nth-child(5) { text-align: right; color: var(--black); font-variant-numeric: tabular-nums; padding-right: 8px; }
.table-itens td:nth-child(6) { text-align: right; font-weight: 600; font-size: 8.5px; color: var(--gold); font-variant-numeric: tabular-nums; padding-right: 8px; }
.table-itens td:nth-child(7) { text-align: center; font-size: 7.5px; color: var(--muted); font-variant-numeric: tabular-nums; }
.table-itens td:nth-child(8) { text-align: center; font-size: 7.5px; color: var(--muted); font-variant-numeric: tabular-nums; }
.table-itens td:nth-child(9) { text-align: center; }
.pct-pill { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 7px; font-weight: 600; background: rgba(76,175,122,.15); color: #2d8a54; letter-spacing: .03em; }
.pct-pill.zero { background: rgba(150,150,150,.12); color: #888; }
.resumo-etapa { background: var(--gold); color: var(--navy); padding: 10px 16px; display: flex; justify-content: space-between; align-items: center; font-size: 10px; font-weight: 600; margin: 12px 8px; border-radius: 2px; }
.total-final { background: var(--navy); color: var(--white); padding: 20px; text-align: center; margin: 20px 12px; border-radius: 3px; border: 1px solid var(--navy); }
.total-final h2 { font-family: 'Cormorant Garamond', serif; font-size: 18px; margin-bottom: 10px; letter-spacing: .04em; font-weight: 600; }
.total-final-value { font-size: 26px; font-weight: 700; color: var(--gold2); margin: 6px 0; }
.page-footer { background: var(--navy); border-top: 1px solid var(--navy); }
.footer-inner { display: flex; align-items: center; justify-content: space-between; padding: 8px 16px; }
.footer-inner .f-left { font-size: 6.5px; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); }
.footer-inner .f-center { font-family: 'Cormorant Garamond', serif; font-size: 11px; font-weight: 600; color: var(--gold); letter-spacing: .1em; }
.footer-inner .f-right { font-size: 6.5px; letter-spacing: .1em; color: var(--muted); }
.footer-gold-bar { height: 2px; background: linear-gradient(to right, var(--gold), var(--gold2), var(--gold)); }
</style>
</head>
<body>
HTML;
    }

    
    private static function gerarPaginaCapa(array $orcamento): string
    {
        $numeroProposta = (string)($orcamento['numero_proposta'] ?? '');
        $clienteNome = (string)($orcamento['cliente_nome'] ?? '');
        $endereco = (string)($orcamento['endereco_obra'] ?? '');
        $local = (string)($orcamento['local_obra'] ?? '');
        $area = (string)($orcamento['area_m2'] ?? '');
        $prazo = (string)($orcamento['prazo_dias'] ?? '');
        $prazomeses = $prazo ? round((int)$prazo / 30) : '';
        $rev = (string)($orcamento['rev'] ?? 'R00');
        $data = date('d/m/Y', strtotime($orcamento['data'] ?? 'now'));
        
        return <<<HTML
<div class="page page-cover">
<div class="accent-bar"></div>
<div class="corner-tl"></div>
<div class="corner-br"></div>
<div class="logo-block">
<div class="brand">BROOKS</div>
<div class="sub">Construtora</div>
</div>
<div class="top-meta">
<div class="label">Documento Técnico</div>
<div class="badge">{$numeroProposta}</div>
</div>
<div class="top-rule"></div>
<div class="hero">
<div class="eyebrow">Planilha de Orçamento Executivo</div>
<div class="title">Planilha<br/><strong>Resumo</strong></div>
<div class="subtitle">Etapa Cinza (Bruta) + Acabamentos &nbsp;|&nbsp; Administração</div>
<div class="ornament"><span></span><i></i><span></span></div>
<div class="info-grid">
<div class="info-cell">
<div class="cell-label">Cliente</div>
<div class="cell-value">{$clienteNome}</div>
</div>
<div class="info-cell">
<div class="cell-label">Endereço</div>
<div class="cell-value">{$endereco}<br/>{$local}</div>
</div>
<div class="info-cell">
<div class="cell-label">Prazo de Obra</div>
<div class="cell-value">{$prazomeses} meses</div>
</div>
</div>
</div>
<div class="bottom-meta">
<div class="doc-ref">DOCUMENTO CONFIDENCIAL — USO RESTRITO</div>
<div class="revision">
<span>Área total &nbsp;{$area} m²</span>
<strong>REV. {$rev} &nbsp;·&nbsp; {$data}</strong>
</div>
</div>
<div class="bottom-strip"></div>
</div>
HTML;
    }

    
    private static function gerarHeaderPadrao(array $orcamento): string
    {
        $numeroProposta = (string)($orcamento['numero_proposta'] ?? '');
        $clienteNome = (string)($orcamento['cliente_nome'] ?? '');
        $endereco = (string)($orcamento['endereco_obra'] ?? '');
        $local = (string)($orcamento['local_obra'] ?? '');
        $prazo = (string)($orcamento['prazo_dias'] ?? '');
        $prazomeses = $prazo ? round((int)$prazo / 30) : '';
        $area = (string)($orcamento['area_m2'] ?? '');
        $rev = (string)($orcamento['rev'] ?? 'R00');
        $data = date('d/m/Y', strtotime($orcamento['data'] ?? 'now'));
        
        return <<<HTML
<header class="page-header">
<div class="header-inner">
<div class="header-project">
<div class="doc-code">{$numeroProposta}</div>
<div class="proj-row"><span class="lbl">Cliente</span><span class="val">{$clienteNome}</span></div>
<div class="proj-row"><span class="lbl">Endereço</span><span class="val">{$endereco} – {$local}</span></div>
<div class="proj-row"><span class="lbl">Prazo</span><span class="val">{$prazomeses} meses</span></div>
</div>
<div class="header-logo">
<div class="brand">BROOKS</div>
<div class="sub">Construtora</div>
</div>
<div class="header-meta">
<div class="meta-row"><span class="lbl">Revisão</span><span class="val highlight">{$rev}</span></div>
<div class="meta-row"><span class="lbl">Área</span><span class="val">{$area} m²</span></div>
<div class="meta-row"><span class="lbl">Data</span><span class="val">{$data}</span></div>
</div>
</div>
<div class="header-rule"></div>
</header>
HTML;
    }

    
    private static function gerarPaginasResumo(int $orcamentoId, array $orcamento): string
    {
        $pdo = \App\Core\Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT codigo, grupo, SUM(valor_cobranca) as valor_total '
            . 'FROM orcamento_itens WHERE orcamento_id = :id '
            . 'GROUP BY CAST(SUBSTRING_INDEX(codigo, \'.\', 1) AS UNSIGNED), grupo '
            . 'ORDER BY CAST(SUBSTRING_INDEX(codigo, \'.\', 1) AS UNSIGNED)'
        );
        $stmt->execute([':id' => $orcamentoId]);
        $itensAgrupados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $totalCinza = 0; $totalAcabamentos = 0; $totalGerenciamento = 0; $totalAdm = 0;
        $itensCinza = []; $itensAcabamentos = []; $itensGerenciamento = []; $itensAdm = [];
        
        foreach ($itensAgrupados as $item) {
            $codigoNum = (int)explode('.', $item['codigo'])[0];
            $valor = (float)$item['valor_total'];
            if ($codigoNum >= 1 && $codigoNum <= 17) { $itensCinza[] = $item; $totalCinza += $valor; }
            elseif ($codigoNum >= 18 && $codigoNum <= 41) { $itensAcabamentos[] = $item; $totalAcabamentos += $valor; }
            elseif ($codigoNum === 42) { $itensGerenciamento[] = $item; $totalGerenciamento += $valor; }
            else { $itensAdm[] = $item; $totalAdm += $valor; }
        }
        
        $totalGeral = $totalCinza + $totalAcabamentos + $totalGerenciamento + $totalAdm;
        $area = (string)($orcamento['area_m2'] ?? '');
        
        $html .= '<div class="page">' . self::gerarHeaderPadrao($orcamento) . '<div class="page-content">';
        $html .= '<div class="resumo-title"><h2>Planilha Resumo</h2><h3>Etapa Cinza (Bruta) + Acabamentos | Administração</h3></div>';
        $html .= '<table class="table-resumo-clean"><thead><tr>';
        $html .= '<th style="width:5%;text-align:center;">#</th><th style="width:60%;text-align:left;padding-left:8px;">ETAPA CINZA</th>';
        $html .= '<th style="width:25%;text-align:right;padding-right:12px;">VALOR TOTAL</th><th style="width:10%;text-align:center;">%</th>';
        $html .= '</tr></thead><tbody>';
        
        $numero = 1;
        foreach ($itensCinza as $item) {
            $valor = (float)$item['valor_total'];
            $pct = $totalCinza > 0 ? ($valor / $totalCinza) * 100 : 0;
            $html .= sprintf('<tr><td style="text-align:center;">%d</td><td style="padding-left:8px;">%s</td><td style="text-align:right;padding-right:12px;">R$ %s</td><td style="text-align:center;">%s%%</td></tr>',
                $numero++, htmlspecialchars((string)$item['grupo']), self::formatarValor($valor), number_format($pct, 2, ',', '.'));
        }
        
        $pctCinza = $totalGeral > 0 ? ($totalCinza / $totalGeral) * 100 : 0;
        $html .= sprintf('<tr class="subtotal-row"><td colspan="2" style="padding-left:12px;font-weight:700;">SUBTOTAL - ETAPA CINZA</td><td style="text-align:right;padding-right:12px;font-weight:700;">R$ %s</td><td style="text-align:center;font-weight:700;">%s%%</td></tr>',
            self::formatarValor($totalCinza), number_format($pctCinza, 2, ',', '.'));
        
        $html .= '</tbody></table></div><div class="page-number">FOLHA: 1</div></div>';

        
        $html .= '<div class="page">' . self::gerarHeaderPadrao($orcamento) . '<div class="page-content">';
        $html .= '<div class="resumo-title"><h2>Planilha Resumo</h2><h3>Etapa Cinza (Bruta) + Acabamentos | Administração</h3></div>';
        $html .= '<table class="table-resumo-clean"><thead><tr>';
        $html .= '<th style="width:5%;text-align:center;">#</th><th style="width:60%;text-align:left;padding-left:8px;">ETAPA DE ACABAMENTOS</th>';
        $html .= '<th style="width:25%;text-align:right;padding-right:12px;">VALOR TOTAL</th><th style="width:10%;text-align:center;">%</th>';
        $html .= '</tr></thead><tbody>';
        
        $numero = 18;
        foreach ($itensAcabamentos as $item) {
            $valor = (float)$item['valor_total'];
            $pct = $totalAcabamentos > 0 ? ($valor / $totalAcabamentos) * 100 : 0;
            $html .= sprintf('<tr><td style="text-align:center;">%d</td><td style="padding-left:8px;">%s</td><td style="text-align:right;padding-right:12px;">R$ %s</td><td style="text-align:center;">%s%%</td></tr>',
                $numero++, htmlspecialchars((string)$item['grupo']), self::formatarValor($valor), number_format($pct, 2, ',', '.'));
        }
        
        $pctAcabamentos = $totalGeral > 0 ? ($totalAcabamentos / $totalGeral) * 100 : 0;
        $html .= sprintf('<tr class="subtotal-row"><td colspan="2" style="padding-left:12px;font-weight:700;">SUBTOTAL - ETAPA ACABAMENTOS</td><td style="text-align:right;padding-right:12px;font-weight:700;">R$ %s</td><td style="text-align:center;font-weight:700;">%s%%</td></tr>',
            self::formatarValor($totalAcabamentos), number_format($pctAcabamentos, 2, ',', '.'));
        
        $html .= '</tbody></table></div><div class="page-number">FOLHA: 2</div></div>';

        
        $pctGerenciamento = $totalGeral > 0 ? ($totalGerenciamento / $totalGeral) * 100 : 0;
        $pctAdm = $totalGeral > 0 ? ($totalAdm / $totalGeral) * 100 : 0;
        
        $html .= '<div class="page">' . self::gerarHeaderPadrao($orcamento) . '<div class="page-content">';
        $html .= '<div class="resumo-title"><h2>Planilha Resumo</h2><h3>Etapa Cinza (Bruta) + Acabamentos | Administração</h3></div>';
        
        $html .= '<table class="table-resumo-clean"><thead><tr>';
        $html .= '<th style="width:5%;text-align:center;">#</th><th style="width:60%;text-align:left;padding-left:8px;">ETAPA DE GERENCIAMENTO</th>';
        $html .= '<th style="width:25%;text-align:right;padding-right:12px;">VALOR TOTAL</th><th style="width:10%;text-align:center;">%</th>';
        $html .= '</tr></thead><tbody>';
        $html .= sprintf('<tr><td style="text-align:center;">42</td><td style="padding-left:8px;">EQUIPE DE OBRA</td><td style="text-align:right;padding-right:12px;">R$ %s</td><td style="text-align:center;">100,00%%</td></tr>',
            self::formatarValor($totalGerenciamento));
        $html .= sprintf('<tr class="subtotal-row"><td colspan="2" style="padding-left:12px;font-weight:700;">SUBTOTAL - ETAPA DE GERENCIAMENTO</td><td style="text-align:right;padding-right:12px;font-weight:700;">R$ %s</td><td style="text-align:center;font-weight:700;">%s%%</td></tr>',
            self::formatarValor($totalGerenciamento), number_format($pctGerenciamento, 2, ',', '.'));
        $html .= '</tbody></table>';
        
        $html .= '<table class="table-resumo-clean" style="margin-top:20px;"><thead><tr>';
        $html .= '<th style="width:5%;text-align:center;">#</th><th style="width:60%;text-align:left;padding-left:8px;">TAXA DE ADMINISTRAÇÃO + IMPOSTOS</th>';
        $html .= '<th style="width:25%;text-align:right;padding-right:12px;">VALOR TOTAL</th><th style="width:10%;text-align:center;">%</th>';
        $html .= '</tr></thead><tbody>';
        $html .= sprintf('<tr><td style="text-align:center;">43</td><td style="padding-left:8px;">TAXA DE ADMINISTRAÇÃO + IMPOSTOS</td><td style="text-align:right;padding-right:12px;">R$ %s</td><td style="text-align:center;">100,00%%</td></tr>',
            self::formatarValor($totalAdm));
        $html .= sprintf('<tr class="subtotal-row"><td colspan="2" style="padding-left:12px;font-weight:700;">SUBTOTAL - TAXA DE ADMINISTRAÇÃO + IMPOSTOS</td><td style="text-align:right;padding-right:12px;font-weight:700;">R$ %s</td><td style="text-align:center;font-weight:700;">%s%%</td></tr>',
            self::formatarValor($totalAdm), number_format($pctAdm, 2, ',', '.'));
        $html .= '</tbody></table>';
        
        $html .= '<table class="table-resumo-clean" style="margin-top:20px;"><tbody>';
        $html .= sprintf('<tr class="total-row"><td colspan="2" style="padding-left:12px;font-size:10px;font-weight:700;">VALOR TOTAL GERAL + TAXA DE ADMINISTRAÇÃO + IMPOSTOS:</td><td style="text-align:right;padding-right:12px;font-size:10px;font-weight:700;">R$ %s</td><td style="text-align:center;font-size:10px;font-weight:700;">100,00%%</td></tr>',
            self::formatarValor($totalGeral));
        $html .= '</tbody></table>';

        
        $html .= '<table class="table-areas"><thead><tr><th>ÁREAS</th><th>m2</th><th>FATOR</th><th>m2 x FATOR</th></tr></thead><tbody>';
        $html .= '<tr><td>ÁREA INTERNA</td><td>344,10</td><td>1</td><td>344,10</td></tr>';
        $html .= '<tr><td>VARANDA COBERTA</td><td>103,94</td><td>1</td><td>103,94</td></tr>';
        $html .= '<tr><td>ABRIGO AUTOS</td><td>47,52</td><td>1</td><td>47,52</td></tr>';
        $html .= '<tr><td>ÁREA DESCOBERTA</td><td>139,79</td><td>1</td><td>139,79</td></tr>';
        $html .= '<tr><td>PISCINA</td><td>87,62</td><td>1</td><td>87,62</td></tr>';
        $html .= sprintf('<tr class="total-row"><td colspan="3"><strong>ÁREA TOTAL:</strong></td><td><strong>%s</strong></td></tr>', $area);
        $html .= '</tbody></table>';
        
        $html .= '<table class="table-areas" style="margin-top:30px;"><thead><tr><th>ETAPAS</th><th>PREÇO</th><th>M2</th><th>PREÇO / m2</th></tr></thead><tbody>';
        $html .= sprintf('<tr><td>ETAPA BRUTA (CINZA)</td><td>R$ %s</td><td>%s</td><td>R$ %s</td></tr>',
            self::formatarValor($totalCinza), $area, self::formatarValor($totalCinza / (float)$area));
        $html .= sprintf('<tr><td>ETAPA ACABAMENTOS</td><td>R$ %s</td><td>%s</td><td>R$ %s</td></tr>',
            self::formatarValor($totalAcabamentos), $area, self::formatarValor($totalAcabamentos / (float)$area));
        $html .= sprintf('<tr><td>GERENCIAMENTO / INDIRETOS / IMPOSTOS</td><td>R$ %s</td><td>%s</td><td>R$ %s</td></tr>',
            self::formatarValor($totalGerenciamento + $totalAdm), $area, self::formatarValor(($totalGerenciamento + $totalAdm) / (float)$area));
        $html .= sprintf('<tr class="total-row"><td><strong>TOTAL GERAL:</strong></td><td><strong>R$ %s</strong></td><td><strong>%s</strong></td><td><strong>R$ %s</strong></td></tr>',
            self::formatarValor($totalGeral), $area, self::formatarValor($totalGeral / (float)$area));
        $html .= '</tbody></table>';
        
        $html .= '</div><div class="page-number">FOLHA: 3</div></div>';
        
        return $html;
    }

    
    private static function gerarPaginasInstitucionais(): string
    {
        return <<<'HTML'
<div class="page">
<header class="page-header">
<div class="header-inner">
<div class="header-project">
<div class="doc-code">BROOKS CONSTRUTORA</div>
<div class="proj-row"><span class="val">Engenharia e Construção Civil</span></div>
</div>
<div class="header-logo">
<div class="brand">BROOKS</div>
<div class="sub">Construtora</div>
</div>
<div class="header-meta">
<div class="meta-row"><span class="val">Tel: (11) 3063-2263</span></div>
<div class="meta-row"><span class="val">contato@brooks.com.br</span></div>
</div>
</div>
<div class="header-rule"></div>
</header>
<div class="page-content">
<h1 style="font-family:'Cormorant Garamond',serif;font-size:26px;color:var(--navy);margin-bottom:20px;border-bottom:2px solid var(--gold);padding-bottom:12px;">Prezados</h1>
<p style="font-size:11px;line-height:1.8;text-align:justify;margin-bottom:18px;color:#555;">
Agradecemos imensamente o seu contato! Temos certeza de que esta proposta trará soluções inovadoras e valor para o seu projeto. Nas próximas páginas, apresentamos nossa empresa, detalhamos os serviços oferecidos, nossos valores e observações importantes. Estamos à sua inteira disposição para qualquer esclarecimento ou para darmos o próximo passo.
</p>
<p style="font-weight:600;color:var(--navy);margin-top:15px;font-size:11px;">Atenciosamente, <strong>BROOKS CONSTRUTORA</strong>.</p>
<h2 style="font-family:'Cormorant Garamond',serif;font-size:22px;color:var(--navy);margin:35px 0 18px 0;border-bottom:2px solid var(--gold);padding-bottom:10px;">Apresentação Institucional</h2>
<p style="font-size:11px;line-height:1.8;text-align:justify;margin-bottom:14px;color:#555;">
A <strong>Brooks Construtora</strong> é uma empresa especializada na execução de obras civis, reformas integrais e retrofit de imóveis residenciais, corporativos e comerciais de médio porte.
</p>
<p style="font-size:11px;line-height:1.8;text-align:justify;margin-bottom:14px;color:#555;">
Atuamos no segmento de construção civil por meio da execução completa de sistemas construtivos, contemplando:
</p>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:18px;font-size:10px;">
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Demolições técnicas</div>
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Infraestrutura para automação</div>
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Alvenaria estrutural e de vedação</div>
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Infraestrutura para climatização</div>
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Impermeabilizações</div>
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Cálculo estrutural e reforços</div>
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Instalação de básicos e revestimentos</div>
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Coberturas e fundações</div>
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Execução de meia esquadria</div>
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Pinturas técnicas e acabamentos</div>
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Pisos e sistemas em drywall</div>
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Logística e descarte de entulho</div>
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Instalações elétricas e hidráulicas</div>
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Instalação de louças e metais</div>
<div style="padding-left:12px;position:relative;color:#666;"><span style="position:absolute;left:0;color:var(--gold);font-weight:bold;">•</span> Suprimentos e materiais de obra</div>
</div>
</div>
<div class="page-number">Página 4</div>
</div>
HTML;
        return $html;
    }

    
    private static function resolverGrupoEtapa(string $codigo): string
    {
        $numero = (int)explode('.', trim($codigo))[0];
        if ($numero >= 1 && $numero <= 17) return 'cinza';
        if ($numero >= 18 && $numero <= 41) return 'acabamentos';
        if ($numero === 42) return 'gerenciamento';
        return 'adm_impostos';
    }
    
    private static function gerarPaginaDetalhamento(int $orcamentoId, array $orcamento): string
    {
        $pdo = \App\Core\Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, codigo, descricao, quantidade, unidade, valor_unitario, valor_cobranca, percentual_realizado '
            . 'FROM orcamento_itens WHERE orcamento_id = :id '
            . 'ORDER BY CAST(SUBSTRING_INDEX(codigo, \'.\', 1) AS UNSIGNED), CAST(SUBSTRING_INDEX(codigo, \'.\', -1) AS UNSIGNED), id'
        );
        $stmt->execute([':id' => $orcamentoId]);
        $todosItens = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $grupos = [
            'cinza' => ['label' => 'ETAPA CINZA', 'itens' => [], 'subtotal' => 0.0],
            'acabamentos' => ['label' => 'ETAPA ACABAMENTOS', 'itens' => [], 'subtotal' => 0.0],
            'gerenciamento' => ['label' => 'ETAPA GERENCIAMENTO', 'itens' => [], 'subtotal' => 0.0],
            'adm_impostos' => ['label' => 'TAXA DE ADMINISTRAÇÃO + IMPOSTOS', 'itens' => [], 'subtotal' => 0.0],
        ];
        
        $idsVistos = [];
        foreach ($todosItens as $item) {
            if (isset($idsVistos[$item['id']])) continue;
            $idsVistos[$item['id']] = true;
            $chave = self::resolverGrupoEtapa((string)$item['codigo']);
            $grupos[$chave]['itens'][] = $item;
            $grupos[$chave]['subtotal'] += (float)$item['valor_cobranca'];
        }
        
        $totalGeral = array_sum(array_column($grupos, 'subtotal'));
        
        $html = '<div class="page page-detalhamento">' . self::gerarHeaderPadrao($orcamento);
        $html .= '<div class="section-title"><h2>Detalhamento Completo</h2><span class="badge">Planilha de Insumos</span></div>';
        
        foreach ($grupos as $grupo) {
            if (empty($grupo['itens'])) continue;
            $subtotal = $grupo['subtotal'];
            $pctDoTotal = $totalGeral > 0 ? ($subtotal / $totalGeral) * 100 : 0.0;
            
            $html .= '<div class="etapa-section">';
            $html .= sprintf('<div class="etapa-header"><h2 class="etapa-title">%s</h2></div>', htmlspecialchars($grupo['label']));
            $html .= '<div class="table-wrap">';
            $html .= self::gerarTabelaItens($grupo['itens'], $subtotal, $totalGeral);
            $html .= '</div>';
            $html .= sprintf('<div class="resumo-etapa"><span style="font-weight:700;">SUBTOTAL — %s</span><div><span style="margin-right:16px;font-weight:700;font-size:11px;">R$ %s</span><span style="font-size:9px;opacity:.85;">%s%% do total</span></div></div>',
                htmlspecialchars((string)$grupo['label']), self::formatarValor($subtotal), number_format($pctDoTotal, 2, ',', '.'));
            $html .= '</div>';
        }
        
        $html .= sprintf('<div class="total-final"><h2>VALOR TOTAL GERAL DO PROJETO</h2><div class="total-final-value">R$ %s</div><div style="font-size:10px;opacity:.9;margin-top:4px;">100,00%%</div></div>',
            self::formatarValor($totalGeral));
        
        $html .= '<footer class="page-footer"><div class="footer-inner">';
        $html .= '<span class="f-left">Documento Confidencial — Uso Restrito</span>';
        $html .= '<span class="f-center">BROOKS</span>';
        $rev = (string)($orcamento['rev'] ?? 'R00');
        $data = date('d/m/Y', strtotime($orcamento['data'] ?? 'now'));
        $html .= sprintf('<span class="f-right">Rev. %s · %s</span>', $rev, $data);
        $html .= '</div><div class="footer-gold-bar"></div></footer>';
        
        $html .= '<div class="page-number">Página 7</div></div>';
        return $html;
    }

    
    private static function gerarTabelaItens(array $itens, float $subtotal, float $totalGeral): string
    {
        $html = '<table class="table-itens"><thead><tr>';
        $html .= '<th>CÓDIGO</th><th>DESCRIÇÃO</th><th>QUANT.</th><th>UNID</th>';
        $html .= '<th>VALOR UNIT.</th><th>VALOR TOTAL</th><th>% ETAPA</th><th>% TOTAL</th><th>% REALIZADO</th>';
        $html .= '</tr></thead><tbody>';
        
        foreach ($itens as $item) {
            $quantidade = (float)$item['quantidade'];
            $valorTotal = (float)$item['valor_cobranca'];
            $valorUnitario = $quantidade > 0 ? $valorTotal / $quantidade : (float)$item['valor_unitario'];
            $pctEtapa = $subtotal > 0 ? ($valorTotal / $subtotal) * 100 : 0.0;
            $pctTotal = $totalGeral > 0 ? ($valorTotal / $totalGeral) * 100 : 0.0;
            $percentualRealizado = (float)($item['percentual_realizado'] ?? 0);
            $pctRealizadoEfetivo = ($percentualRealizado / 100) * $pctTotal;
            
            $pillClass = $pctRealizadoEfetivo > 0 ? 'pct-pill' : 'pct-pill zero';
            
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars((string)$item['codigo']) . '</td>';
            $html .= '<td>' . htmlspecialchars((string)$item['descricao']) . '</td>';
            $html .= '<td style="text-align:center;">' . number_format($quantidade, 2, ',', '.') . '</td>';
            $html .= '<td style="text-align:center;">' . htmlspecialchars((string)$item['unidade']) . '</td>';
            $html .= '<td style="text-align:right;padding-right:8px;">R$ ' . self::formatarValor($valorUnitario) . '</td>';
            $html .= '<td style="text-align:right;padding-right:8px;font-weight:600;color:var(--gold);">R$ ' . self::formatarValor($valorTotal) . '</td>';
            $html .= '<td style="text-align:center;">' . number_format($pctEtapa, 2, ',', '.') . '%</td>';
            $html .= '<td style="text-align:center;">' . number_format($pctTotal, 2, ',', '.') . '%</td>';
            $html .= '<td style="text-align:center;"><span class="' . $pillClass . '">' . number_format($pctRealizadoEfetivo, 2, ',', '.') . '%</span></td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        return $html;
    }
    
    private static function gerarRodapeHTML(): string
    {
        return '</body></html>';
    }
    
    private static function formatarValor(float $valor): string
    {
        return number_format($valor, 2, ',', '.');
    }
}
