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
@page { margin: 0; size: A4; }
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Outfit', sans-serif; color: var(--black); line-height: 1.4; background: var(--white); }
.page { page-break-after: always; position: relative; width: 210mm; min-height: 297mm; background: white; }

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
.header-inner { display: flex; align-items: stretch; min-height: 80px; }
.header-project { flex: 1; padding: 18px 28px; border-right: 1px solid var(--line); display: flex; flex-direction: column; justify-content: center; gap: 4px; }
.header-project .doc-code { font-size: 8.5px; letter-spacing: .25em; text-transform: uppercase; color: var(--gold); font-weight: 500; margin-bottom: 4px; }
.proj-row { display: flex; align-items: baseline; gap: 6px; }
.proj-row .lbl { font-size: 7.5px; letter-spacing: .18em; text-transform: uppercase; color: var(--muted); font-weight: 400; min-width: 76px; }
.proj-row .val { font-size: 10.5px; color: var(--white); font-weight: 400; }
.header-logo { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 18px 40px; border-right: 1px solid var(--line); gap: 4px; }
.header-logo .brand { font-family: 'Cormorant Garamond', serif; font-size: 30px; font-weight: 700; letter-spacing: .1em; color: var(--white); line-height: 1; }
.header-logo .sub { font-size: 7px; letter-spacing: .32em; text-transform: uppercase; color: var(--gold); font-weight: 300; }
.header-meta { display: flex; flex-direction: column; justify-content: center; padding: 18px 28px; gap: 5px; }
.meta-row { display: flex; align-items: baseline; gap: 6px; }
.meta-row .lbl { font-size: 7.5px; letter-spacing: .18em; text-transform: uppercase; color: var(--muted); font-weight: 400; min-width: 48px; }
.meta-row .val { font-size: 10.5px; color: var(--white); font-weight: 500; }
.meta-row .val.highlight { color: var(--gold2); }
.header-rule { height: 1px; background: linear-gradient(to right, var(--gold), rgba(201,168,76,.1) 70%, transparent); }

/* ══ TABELAS RESUMO ══ */
.resumo-title { text-align: center; margin: 30px 0 20px 0; }
.resumo-title h2 { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 600; color: var(--navy); margin-bottom: 8px; }
.resumo-title h3 { font-size: 11px; letter-spacing: .15em; text-transform: uppercase; color: var(--gold); font-weight: 400; }
.table-resumo-clean { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 10px; }
.table-resumo-clean th { background: var(--navy); color: var(--white); padding: 12px 10px; text-align: left; font-weight: 500; border: none; letter-spacing: .08em; }
.table-resumo-clean td { padding: 10px; border: 1px solid #e0e0e0; background: white; }
.table-resumo-clean .subtotal-row { background: var(--gold); color: var(--navy); font-weight: 600; }
.table-resumo-clean .total-row { background: var(--navy); color: var(--white); font-weight: 600; font-size: 11px; }
.table-areas { width: 100%; border-collapse: collapse; margin: 30px 0; font-size: 10px; }
.table-areas th { background: #f5f5f5; padding: 10px; border: 1px solid #ddd; font-weight: 500; text-align: center; }
.table-areas td { padding: 10px; border: 1px solid #ddd; text-align: center; }
.table-areas .total-row { background: var(--navy); color: var(--white); font-weight: 600; }
.page-number { position: absolute; bottom: 15mm; right: 20mm; font-size: 9px; color: #999; letter-spacing: .1em; }

/* ══ DETALHAMENTO ══ */
.page-detalhamento { background: var(--white); }
.section-title { padding: 22px 28px 14px; display: flex; align-items: center; gap: 16px; }
.section-title h2 { font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 400; color: var(--black); letter-spacing: .04em; }
.section-title .badge { font-size: 8px; letter-spacing: .18em; text-transform: uppercase; color: var(--gold); border: 1px solid var(--line); padding: 4px 10px; border-radius: 2px; }
.etapa-section { margin: 25px 0; page-break-inside: avoid; }
.etapa-header { padding: 18px 28px 12px; }
.etapa-title { font-family: 'Cormorant Garamond', serif; font-size: 18px; color: var(--gold); font-weight: 600; letter-spacing: .06em; }
.table-wrap { padding: 0 20px 28px; }
.table-itens { width: 100%; border-collapse: collapse; border: 1px solid #e0e0e0; border-radius: 4px; overflow: hidden; }
.table-itens thead tr { background: var(--navy); }
.table-itens thead th { padding: 11px 12px; font-size: 7.5px; letter-spacing: .18em; text-transform: uppercase; color: var(--white); font-weight: 500; border-bottom: 1px solid var(--navy); white-space: nowrap; }
.table-itens thead th:first-child { text-align: left; padding-left: 16px; }
.table-itens thead th:nth-child(2) { text-align: left; min-width: 200px; }
.table-itens thead th:nth-child(n+3) { text-align: right; }
.table-itens tbody tr { border-bottom: 1px solid #e0e0e0; transition: background .15s; }
.table-itens tbody tr:nth-child(even) { background: var(--row-alt); }
.table-itens tbody tr:hover { background: rgba(201,168,76,.08); }
.table-itens td { padding: 10px 12px; font-size: 10px; color: var(--black); vertical-align: middle; line-height: 1.4; }
.table-itens td:first-child { font-size: 9px; color: var(--muted); letter-spacing: .06em; font-weight: 500; padding-left: 16px; }
.table-itens td:nth-child(2) { font-size: 10px; color: var(--black); font-weight: 400; }
.table-itens td:nth-child(3), .table-itens td:nth-child(4) { text-align: right; font-variant-numeric: tabular-nums; color: var(--muted); }
.table-itens td:nth-child(5) { text-align: right; color: var(--muted); }
.col-total { text-align: right; font-weight: 600; font-size: 10.5px; color: var(--gold) !important; font-variant-numeric: tabular-nums; }
.col-pct { text-align: right; font-size: 9px; color: var(--muted); }
.col-realizado { text-align: right; }
.pct-pill { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 8.5px; font-weight: 600; background: rgba(76,175,122,.12); color: #4caf7a; letter-spacing: .05em; }
.pct-pill.zero { background: rgba(122,138,154,.1); color: var(--muted); }
.resumo-etapa { background: var(--gold); color: var(--navy); padding: 14px 28px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 600; margin: 20px 0; }
.total-final { background: var(--navy); color: var(--white); padding: 30px; text-align: center; margin: 30px 20px; border-radius: 4px; border: 1px solid var(--navy); }
.total-final h2 { font-family: 'Cormorant Garamond', serif; font-size: 22px; margin-bottom: 12px; letter-spacing: .05em; font-weight: 600; }
.total-final-value { font-size: 32px; font-weight: 700; color: var(--gold2); margin: 8px 0; }
.page-footer { background: var(--navy); border-top: 1px solid var(--navy); }
.footer-inner { display: flex; align-items: center; justify-content: space-between; padding: 11px 28px; }
.footer-inner .f-left { font-size: 7.5px; letter-spacing: .15em; text-transform: uppercase; color: var(--muted); }
.footer-inner .f-center { font-family: 'Cormorant Garamond', serif; font-size: 13px; font-weight: 600; color: var(--gold); letter-spacing: .12em; }
.footer-inner .f-right { font-size: 7.5px; letter-spacing: .12em; color: var(--muted); }
.footer-gold-bar { height: 3px; background: linear-gradient(to right, var(--gold), var(--gold2), var(--gold)); }
.page-footer { background: var(--navy); border-top: 1px solid var(--line); }
.footer-inner { display: flex; align-items: center; justify-content: space-between; padding: 11px 28px; }
.footer-inner .f-left { font-size: 7.5px; letter-spacing: .15em; text-transform: uppercase; color: var(--muted); }
.footer-inner .f-center { font-family: 'Cormorant Garamond', serif; font-size: 13px; font-weight: 600; color: var(--gold); letter-spacing: .12em; }
.footer-inner .f-right { font-size: 7.5px; letter-spacing: .12em; color: var(--muted); }
.footer-gold-bar { height: 3px; background: linear-gradient(to right, var(--gold), var(--gold2), var(--gold)); }
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
        
        $html = '<div class="page">' . self::gerarHeaderPadrao($orcamento) . '<div class="page-content">';
        $html .= '<div class="resumo-title"><h2>Planilha Resumo</h2><h3>Etapa Cinza (Bruta) + Acabamentos | Administração</h3></div>';
        $html .= '<table class="table-resumo-clean"><thead><tr>';
        $html .= '<th style="width:5%;">#</th><th style="width:65%;">ETAPA CINZA</th>';
        $html .= '<th style="width:20%;text-align:right;">VALOR TOTAL</th><th style="width:10%;text-align:right;">%</th>';
        $html .= '</tr></thead><tbody>';
        
        $numero = 1;
        foreach ($itensCinza as $item) {
            $valor = (float)$item['valor_total'];
            $pct = $totalCinza > 0 ? ($valor / $totalCinza) * 100 : 0;
            $html .= sprintf('<tr><td style="text-align:center;">%d</td><td>%s</td><td style="text-align:right;">%s</td><td style="text-align:right;">%.2f%%</td></tr>',
                $numero++, htmlspecialchars($item['grupo']), self::formatarValor($valor), $pct);
        }
        
        $pctCinza = $totalGeral > 0 ? ($totalCinza / $totalGeral) * 100 : 0;
        $html .= sprintf('<tr class="subtotal-row"><td colspan="2" style="padding-left:20px;">SUBTOTAL - ETAPA CINZA</td><td style="text-align:right;">R$ %s</td><td style="text-align:right;">%.2f%%</td></tr>',
            self::formatarValor($totalCinza), $pctCinza);
        
        $html .= '</tbody></table></div><div class="page-number">FOLHA: 1</div></div>';

        
        $html .= '<div class="page">' . self::gerarHeaderPadrao($orcamento) . '<div class="page-content">';
        $html .= '<div class="resumo-title"><h2>Planilha Resumo</h2><h3>Etapa Cinza (Bruta) + Acabamentos | Administração</h3></div>';
        $html .= '<table class="table-resumo-clean"><thead><tr>';
        $html .= '<th style="width:5%;">#</th><th style="width:65%;">ETAPA DE ACABAMENTOS</th>';
        $html .= '<th style="width:20%;text-align:right;">VALOR TOTAL</th><th style="width:10%;text-align:right;">%</th>';
        $html .= '</tr></thead><tbody>';
        
        $numero = 18;
        foreach ($itensAcabamentos as $item) {
            $valor = (float)$item['valor_total'];
            $pct = $totalAcabamentos > 0 ? ($valor / $totalAcabamentos) * 100 : 0;
            $html .= sprintf('<tr><td style="text-align:center;">%d</td><td>%s</td><td style="text-align:right;">%s</td><td style="text-align:right;">%.2f%%</td></tr>',
                $numero++, htmlspecialchars($item['grupo']), self::formatarValor($valor), $pct);
        }
        
        $pctAcabamentos = $totalGeral > 0 ? ($totalAcabamentos / $totalGeral) * 100 : 0;
        $html .= sprintf('<tr class="subtotal-row"><td colspan="2" style="padding-left:20px;">SUBTOTAL - ETAPA ACABAMENTOS</td><td style="text-align:right;">R$ %s</td><td style="text-align:right;">%.2f%%</td></tr>',
            self::formatarValor($totalAcabamentos), $pctAcabamentos);
        
        $html .= '</tbody></table></div><div class="page-number">FOLHA: 2</div></div>';

        
        $pctGerenciamento = $totalGeral > 0 ? ($totalGerenciamento / $totalGeral) * 100 : 0;
        $pctAdm = $totalGeral > 0 ? ($totalAdm / $totalGeral) * 100 : 0;
        
        $html .= '<div class="page">' . self::gerarHeaderPadrao($orcamento) . '<div class="page-content">';
        $html .= '<div class="resumo-title"><h2>Planilha Resumo</h2><h3>Etapa Cinza (Bruta) + Acabamentos | Administração</h3></div>';
        
        $html .= '<table class="table-resumo-clean"><thead><tr>';
        $html .= '<th style="width:5%;">#</th><th style="width:65%;">ETAPA DE GERENCIAMENTO</th>';
        $html .= '<th style="width:20%;text-align:right;">VALOR TOTAL</th><th style="width:10%;text-align:right;">%</th>';
        $html .= '</tr></thead><tbody>';
        $html .= sprintf('<tr><td style="text-align:center;">42</td><td>EQUIPE DE OBRA</td><td style="text-align:right;">%s</td><td style="text-align:right;">100,00%%</td></tr>',
            self::formatarValor($totalGerenciamento));
        $html .= sprintf('<tr class="subtotal-row"><td colspan="2" style="padding-left:20px;">SUBTOTAL - ETAPA DE GERENCIAMENTO</td><td style="text-align:right;">R$ %s</td><td style="text-align:right;">%.2f%%</td></tr>',
            self::formatarValor($totalGerenciamento), $pctGerenciamento);
        $html .= '</tbody></table>';
        
        $html .= '<table class="table-resumo-clean" style="margin-top:30px;"><thead><tr>';
        $html .= '<th style="width:5%;">#</th><th style="width:65%;">TAXA DE ADMINISTRAÇÃO + IMPOSTOS</th>';
        $html .= '<th style="width:20%;text-align:right;">VALOR TOTAL</th><th style="width:10%;text-align:right;">%</th>';
        $html .= '</tr></thead><tbody>';
        $html .= sprintf('<tr><td style="text-align:center;">43</td><td>TAXA DE ADMINISTRAÇÃO + IMPOSTOS</td><td style="text-align:right;">%s</td><td style="text-align:right;">100,00%%</td></tr>',
            self::formatarValor($totalAdm));
        $html .= sprintf('<tr class="subtotal-row"><td colspan="2" style="padding-left:20px;">SUBTOTAL - TAXA DE ADMINISTRAÇÃO + IMPOSTOS</td><td style="text-align:right;">R$ %s</td><td style="text-align:right;">%.2f%%</td></tr>',
            self::formatarValor($totalAdm), $pctAdm);
        $html .= '</tbody></table>';
        
        $html .= '<table class="table-resumo-clean" style="margin-top:30px;"><tbody>';
        $html .= sprintf('<tr class="total-row"><td colspan="2" style="padding-left:20px;font-size:13px;">VALOR TOTAL GERAL + TAXA DE ADMINISTRAÇÃO + IMPOSTOS:</td><td style="text-align:right;font-size:13px;">R$ %s</td><td style="text-align:right;font-size:13px;">100,00%%</td></tr>',
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
            $html .= sprintf('<div class="resumo-etapa"><span>SUBTOTAL — %s</span><div><span style="margin-right:20px;"><strong>R$ %s</strong></span><span style="font-size:10px;opacity:.85;">%.2f%% do total</span></div></div>',
                htmlspecialchars($grupo['label']), self::formatarValor($subtotal), $pctDoTotal);
            $html .= '</div>';
        }
        
        $html .= sprintf('<div class="total-final"><h2>VALOR TOTAL GERAL DO PROJETO</h2><div class="total-final-value">R$ %s</div><div style="font-size:11px;opacity:.9;">100,00%%</div></div>',
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
        $html .= '<th>Código</th><th>Descrição</th><th>Quant.</th><th>Unid</th>';
        $html .= '<th>Valor Unit.</th><th>Valor Total</th><th>% Etapa</th><th>% Total</th><th>% Realizado</th>';
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
            
            $html .= sprintf(
                '<tr><td>%s</td><td>%s</td><td class="col-pct">%s</td><td class="col-pct">%s</td>'
                . '<td class="col-pct">R$ %s</td><td class="col-total">R$ %s</td>'
                . '<td class="col-pct">%.2f%%</td><td class="col-pct">%.2f%%</td>'
                . '<td class="col-realizado"><span class="%s">%.2f%%</span></td></tr>',
                htmlspecialchars($item['codigo']), htmlspecialchars($item['descricao']),
                number_format($quantidade, 3, ',', '.'), htmlspecialchars($item['unidade']),
                self::formatarValor($valorUnitario), self::formatarValor($valorTotal),
                $pctEtapa, $pctTotal, $pillClass, $pctRealizadoEfetivo
            );
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
