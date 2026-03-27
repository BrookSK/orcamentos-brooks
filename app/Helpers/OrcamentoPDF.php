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
<title>Proposta Orçamentária - Brooks Construtora</title>
<style>
:root {
--navy: #2C3350;
--red: #CC1F2D;
--gold: #C9973A;
--white: #FFFFFF;
--gray-light: #F4F5F7;
--black: #1A1A2E;
--muted: #666666;
}
@page { margin: 18mm; size: A4; }
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Helvetica, Arial, sans-serif; color: var(--black); line-height: 1.4; background: var(--white); font-size: 8pt; }
.page { page-break-after: always; position: relative; width: 100%; background: white; }

/* ══ CAPA ══ */
.page-cover { background: var(--navy); color: var(--white); min-height: 277mm; position: relative; }
.logo-cover { position: absolute; top: 40px; left: 40px; }
.logo-cover .brand { font-size: 36pt; font-weight: bold; letter-spacing: .05em; color: var(--white); }
.logo-cover .sub { font-size: 10pt; letter-spacing: .2em; text-transform: uppercase; color: var(--white); margin-top: 6px; }
.cover-meta { position: absolute; top: 45px; right: 40px; text-align: right; }
.cover-meta .label { font-size: 8pt; letter-spacing: .12em; text-transform: uppercase; color: var(--gray-light); margin-bottom: 6px; }
.cover-meta .badge { display: inline-block; padding: 5px 14px; background: var(--gold); border-radius: 2px; font-size: 10pt; letter-spacing: .08em; color: var(--white); font-weight: bold; }
.cover-line { position: absolute; top: 120px; left: 40px; right: 40px; height: 2px; background: var(--gold); }
.cover-title { position: absolute; top: 180px; left: 40px; right: 40px; text-align: center; }
.cover-title h1 { font-size: 32pt; font-weight: normal; color: var(--white); margin-bottom: 8px; }
.cover-title h1 strong { font-weight: bold; color: var(--gold); }
.cover-title .subtitle { font-size: 9pt; letter-spacing: .12em; color: var(--gray-light); text-transform: uppercase; margin-top: 12px; }
.cover-info { position: absolute; top: 340px; left: 60px; right: 60px; }
.cover-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.cover-info-item { background: rgba(255,255,255,.08); padding: 14px 16px; border-left: 3px solid var(--gold); }
.cover-info-item .label { font-size: 7pt; letter-spacing: .15em; text-transform: uppercase; color: var(--gold); margin-bottom: 6px; font-weight: bold; }
.cover-info-item .value { font-size: 11pt; color: var(--white); line-height: 1.4; }
.cover-bottom { position: absolute; bottom: 30px; left: 40px; right: 40px; display: flex; justify-content: space-between; border-top: 1px solid var(--gold); padding-top: 12px; }
.cover-bottom .left { font-size: 7pt; color: var(--gray-light); }
.cover-bottom .right { font-size: 8pt; color: var(--gold); font-weight: bold; }

/* ══ CABEÇALHO PADRÃO ══ */
.page-header { background: var(--navy); width: 100%; height: 22mm; display: flex; align-items: center; padding: 0 20px; position: relative; }
.header-logo { position: absolute; left: 20px; }
.header-logo .brand { font-size: 16pt; font-weight: bold; color: var(--white); letter-spacing: .05em; }
.header-logo .sub { font-size: 6pt; letter-spacing: .18em; text-transform: uppercase; color: var(--white); margin-top: 2px; }
.header-title { position: absolute; left: 50%; transform: translateX(-50%); font-size: 12pt; font-weight: bold; color: var(--white); letter-spacing: .08em; text-transform: uppercase; }
.header-meta { position: absolute; right: 20px; text-align: right; }
.header-meta-row { font-size: 7pt; color: var(--white); margin-bottom: 2px; }
.header-meta-row .label { color: var(--gold); margin-right: 4px; font-weight: bold; }
.header-gold-line { position: absolute; bottom: 0; left: 0; right: 0; height: 1.5mm; background: var(--gold); }

/* ══ TABELAS RESUMO ══ */
.page-content { padding: 10px 0; }
.table-resumo { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 8pt; }
.table-resumo thead th { background: var(--navy); color: var(--white); padding: 6px 8px; text-align: left; font-weight: bold; border: none; text-transform: uppercase; font-size: 7pt; letter-spacing: .05em; }
.table-resumo thead th.col-right { text-align: right; padding-right: 12px; }
.table-resumo thead th.col-center { text-align: center; }
.table-resumo tbody td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; }
.table-resumo tbody tr:nth-child(even) td { background: var(--gray-light); }
.table-resumo .subtotal-row td { background: var(--gold) !important; color: var(--white); font-weight: bold; padding: 8px; border-top: 2px solid var(--gold); }
.table-resumo .total-row td { background: var(--red) !important; color: var(--white); font-weight: bold; padding: 10px 8px; font-size: 9pt; }
.table-areas { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 8pt; }
.table-areas thead th { background: var(--navy); color: var(--white); padding: 6px 8px; font-weight: bold; text-align: center; font-size: 7pt; text-transform: uppercase; }
.table-areas tbody td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; text-align: center; }
.table-areas tbody tr:nth-child(even) td { background: var(--gray-light); }
.table-areas .total-row td { background: var(--gold); color: var(--white); font-weight: bold; padding: 8px; }

/* ══ DETALHAMENTO ══ */
.banner-secao { background: var(--navy); color: var(--white); padding: 10px 20px; font-weight: bold; font-size: 10pt; text-transform: uppercase; letter-spacing: .06em; border-left: 4px solid var(--gold); margin: 16px 0 8px 0; }
.table-detalhes { width: 100%; border-collapse: collapse; font-size: 7pt; margin-bottom: 12px; }
.table-detalhes thead th { background: var(--navy); color: var(--white); padding: 6px 5px; font-weight: bold; text-transform: uppercase; font-size: 6.5pt; letter-spacing: .04em; border-bottom: 1px solid var(--navy); }
.table-detalhes thead th.col-left { text-align: left; padding-left: 8px; }
.table-detalhes thead th.col-right { text-align: right; padding-right: 8px; }
.table-detalhes thead th.col-center { text-align: center; }
.table-detalhes tbody tr { border-bottom: 1px solid #e8e8e8; }
.table-detalhes tbody tr:nth-child(even) { background: var(--gray-light); }
.table-detalhes tbody td { padding: 5px 5px; vertical-align: middle; }
.table-detalhes tbody td.col-codigo { text-align: left; padding-left: 8px; color: var(--muted); font-size: 7pt; }
.table-detalhes tbody td.col-desc { text-align: left; color: var(--black); }
.table-detalhes tbody td.col-num { text-align: right; padding-right: 8px; font-variant-numeric: tabular-nums; }
.table-detalhes tbody td.col-center { text-align: center; }
.table-detalhes tbody td.col-total { text-align: right; padding-right: 8px; font-weight: bold; color: var(--gold); font-variant-numeric: tabular-nums; }
.subtotal-etapa { background: var(--gold); color: var(--white); padding: 8px 12px; font-weight: bold; font-size: 9pt; text-align: right; margin: 8px 0; }
.total-geral-box { background: var(--red); color: var(--white); padding: 16px; text-align: center; margin: 20px 0; }
.total-geral-box h2 { font-size: 12pt; font-weight: bold; margin-bottom: 8px; }
.total-geral-box .valor { font-size: 24pt; font-weight: bold; margin: 8px 0; }

/* ══ RODAPÉ ══ */
.page-footer { position: absolute; bottom: 0; left: 0; right: 0; border-top: 1px solid var(--gold); padding-top: 6px; }
.footer-content { display: flex; justify-content: space-between; align-items: center; padding: 0 20px 8px; }
.footer-left { font-size: 7pt; color: var(--muted); }
.footer-right { font-size: 8pt; color: var(--black); font-weight: bold; }
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
<div class="logo-cover">
<div class="brand">BROOKS</div>
<div class="sub">CONSTRUTORA</div>
</div>
<div class="cover-meta">
<div class="label">DOCUMENTO TÉCNICO</div>
<div class="badge">{$numeroProposta}</div>
</div>
<div class="cover-line"></div>
<div class="cover-title">
<h1>PLANILHA<br/><strong>RESUMO</strong></h1>
<div class="subtitle">ETAPA CINZA (BRUTA) + ACABAMENTOS | ADMINISTRAÇÃO</div>
</div>
<div class="cover-info">
<div class="cover-info-grid">
<div class="cover-info-item">
<div class="label">CLIENTE</div>
<div class="value">{$clienteNome}</div>
</div>
<div class="cover-info-item">
<div class="label">ENDEREÇO</div>
<div class="value">{$endereco}<br/>{$local}</div>
</div>
<div class="cover-info-item">
<div class="label">PRAZO DE OBRA</div>
<div class="value">{$prazomeses} meses</div>
</div>
<div class="cover-info-item">
<div class="label">ÁREA TOTAL</div>
<div class="value">{$area} m²</div>
</div>
</div>
</div>
<div class="cover-bottom">
<div class="left">DOCUMENTO CONFIDENCIAL — USO RESTRITO</div>
<div class="right">REV. {$rev} · {$data}</div>
</div>
</div>
HTML;
    }

    
    private static function gerarHeaderPadrao(array $orcamento, string $tituloSecao): string
    {
        $area = (string)($orcamento['area_m2'] ?? '');
        $rev = (string)($orcamento['rev'] ?? 'R00');
        $data = date('d/m/Y', strtotime($orcamento['data'] ?? 'now'));
        
        return <<<HTML
<header class="page-header">
<div class="header-logo">
<div class="brand">BROOKS</div>
<div class="sub">CONSTRUTORA</div>
</div>
<div class="header-title">{$tituloSecao}</div>
<div class="header-meta">
<div class="header-meta-row"><span class="label">REVISÃO:</span> {$rev}</div>
<div class="header-meta-row"><span class="label">ÁREA:</span> {$area} m²</div>
<div class="header-meta-row"><span class="label">DATA:</span> {$data}</div>
</div>
<div class="header-gold-line"></div>
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
        
        // FOLHA 1 - ETAPA CINZA
        $html = '<div class="page">' . self::gerarHeaderPadrao($orcamento, 'PLANILHA RESUMO') . '<div class="page-content">';
        $html .= '<table class="table-resumo"><thead><tr>';
        $html .= '<th style="width:8%;">Nº</th><th style="width:62%;">DESCRIÇÃO</th>';
        $html .= '<th class="col-right" style="width:20%;">VALOR TOTAL</th><th class="col-center" style="width:10%;">%</th>';
        $html .= '</tr></thead><tbody>';
        
        $numero = 1;
        foreach ($itensCinza as $item) {
            $valor = (float)$item['valor_total'];
            $pct = $totalCinza > 0 ? ($valor / $totalCinza) * 100 : 0;
            $html .= sprintf('<tr><td style="text-align:center;">%d</td><td style="padding-left:8px;">%s</td><td style="text-align:right;padding-right:12px;">R$ %s</td><td style="text-align:center;">%s%%</td></tr>',
                $numero++, htmlspecialchars((string)$item['grupo']), self::formatarValor($valor), number_format($pct, 2, ',', '.'));
        }
        
        $pctCinza = $totalGeral > 0 ? ($totalCinza / $totalGeral) * 100 : 0;
        $html .= sprintf('<tr class="subtotal-row"><td colspan="2" style="padding-left:12px;">SUBTOTAL - ETAPA CINZA</td><td style="text-align:right;padding-right:12px;">R$ %s</td><td style="text-align:center;">%s%%</td></tr>',
            self::formatarValor($totalCinza), number_format($pctCinza, 2, ',', '.'));
        
        $html .= '</tbody></table></div>';
        $html .= '<div class="page-footer"><div class="footer-content"><span class="footer-left"></span><span class="footer-right">FOLHA: 1</span></div></div>';
        $html .= '</div>';

        // FOLHA 2 - ETAPA ACABAMENTOS
        $html .= '<div class="page">' . self::gerarHeaderPadrao($orcamento, 'PLANILHA RESUMO') . '<div class="page-content">';
        $html .= '<table class="table-resumo"><thead><tr>';
        $html .= '<th style="width:8%;">Nº</th><th style="width:62%;">DESCRIÇÃO</th>';
        $html .= '<th class="col-right" style="width:20%;">VALOR TOTAL</th><th class="col-center" style="width:10%;">%</th>';
        $html .= '</tr></thead><tbody>';
        
        $numero = 18;
        foreach ($itensAcabamentos as $item) {
            $valor = (float)$item['valor_total'];
            $pct = $totalAcabamentos > 0 ? ($valor / $totalAcabamentos) * 100 : 0;
            $html .= sprintf('<tr><td style="text-align:center;">%d</td><td style="padding-left:8px;">%s</td><td style="text-align:right;padding-right:12px;">R$ %s</td><td style="text-align:center;">%s%%</td></tr>',
                $numero++, htmlspecialchars((string)$item['grupo']), self::formatarValor($valor), number_format($pct, 2, ',', '.'));
        }
        
        $pctAcabamentos = $totalGeral > 0 ? ($totalAcabamentos / $totalGeral) * 100 : 0;
        $html .= sprintf('<tr class="subtotal-row"><td colspan="2" style="padding-left:12px;">SUBTOTAL - ETAPA ACABAMENTOS</td><td style="text-align:right;padding-right:12px;">R$ %s</td><td style="text-align:center;">%s%%</td></tr>',
            self::formatarValor($totalAcabamentos), number_format($pctAcabamentos, 2, ',', '.'));
        
        $html .= '</tbody></table></div>';
        $html .= '<div class="page-footer"><div class="footer-content"><span class="footer-left"></span><span class="footer-right">FOLHA: 2</span></div></div>';
        $html .= '</div>';

        // FOLHA 3 - GERENCIAMENTO + ADM + TOTAIS
        $pctGerenciamento = $totalGeral > 0 ? ($totalGerenciamento / $totalGeral) * 100 : 0;
        $pctAdm = $totalGeral > 0 ? ($totalAdm / $totalGeral) * 100 : 0;
        
        $html .= '<div class="page">' . self::gerarHeaderPadrao($orcamento, 'PLANILHA RESUMO') . '<div class="page-content">';
        
        $html .= '<table class="table-resumo"><thead><tr>';
        $html .= '<th style="width:8%;">Nº</th><th style="width:62%;">DESCRIÇÃO</th>';
        $html .= '<th class="col-right" style="width:20%;">VALOR TOTAL</th><th class="col-center" style="width:10%;">%</th>';
        $html .= '</tr></thead><tbody>';
        $html .= sprintf('<tr><td style="text-align:center;">42</td><td style="padding-left:8px;">EQUIPE DE OBRA</td><td style="text-align:right;padding-right:12px;">R$ %s</td><td style="text-align:center;">100,00%%</td></tr>',
            self::formatarValor($totalGerenciamento));
        $html .= sprintf('<tr class="subtotal-row"><td colspan="2" style="padding-left:12px;">SUBTOTAL - ETAPA DE GERENCIAMENTO</td><td style="text-align:right;padding-right:12px;">R$ %s</td><td style="text-align:center;">%s%%</td></tr>',
            self::formatarValor($totalGerenciamento), number_format($pctGerenciamento, 2, ',', '.'));
        $html .= '</tbody></table>';
        
        $html .= '<table class="table-resumo" style="margin-top:16px;"><thead><tr>';
        $html .= '<th style="width:8%;">Nº</th><th style="width:62%;">DESCRIÇÃO</th>';
        $html .= '<th class="col-right" style="width:20%;">VALOR TOTAL</th><th class="col-center" style="width:10%;">%</th>';
        $html .= '</tr></thead><tbody>';
        $html .= sprintf('<tr><td style="text-align:center;">43</td><td style="padding-left:8px;">TAXA DE ADMINISTRAÇÃO + IMPOSTOS</td><td style="text-align:right;padding-right:12px;">R$ %s</td><td style="text-align:center;">100,00%%</td></tr>',
            self::formatarValor($totalAdm));
        $html .= sprintf('<tr class="subtotal-row"><td colspan="2" style="padding-left:12px;">SUBTOTAL - TAXA DE ADMINISTRAÇÃO + IMPOSTOS</td><td style="text-align:right;padding-right:12px;">R$ %s</td><td style="text-align:center;">%s%%</td></tr>',
            self::formatarValor($totalAdm), number_format($pctAdm, 2, ',', '.'));
        $html .= '</tbody></table>';
        
        $html .= '<table class="table-resumo" style="margin-top:16px;"><tbody>';
        $html .= sprintf('<tr class="total-row"><td colspan="2" style="padding-left:12px;">VALOR TOTAL GERAL + TAXA DE ADMINISTRAÇÃO + IMPOSTOS:</td><td style="text-align:right;padding-right:12px;">R$ %s</td><td style="text-align:center;">100,00%%</td></tr>',
            self::formatarValor($totalGeral));
        $html .= '</tbody></table>';

        // TABELAS DE ÁREAS
        $html .= '<table class="table-areas" style="margin-top:24px;"><thead><tr><th>ÁREAS</th><th>m²</th><th>FATOR</th><th>m² x FATOR</th></tr></thead><tbody>';
        $html .= '<tr><td>ÁREA INTERNA</td><td>344,10</td><td>1</td><td>344,10</td></tr>';
        $html .= '<tr><td>VARANDA COBERTA</td><td>103,94</td><td>1</td><td>103,94</td></tr>';
        $html .= '<tr><td>ABRIGO AUTOS</td><td>47,52</td><td>1</td><td>47,52</td></tr>';
        $html .= '<tr><td>ÁREA DESCOBERTA</td><td>139,79</td><td>1</td><td>139,79</td></tr>';
        $html .= '<tr><td>PISCINA</td><td>87,62</td><td>1</td><td>87,62</td></tr>';
        $html .= sprintf('<tr class="total-row"><td colspan="3">ÁREA TOTAL:</td><td>%s</td></tr>', $area);
        $html .= '</tbody></table>';
        
        $html .= '<table class="table-areas" style="margin-top:20px;"><thead><tr><th>ETAPAS</th><th>PREÇO</th><th>M²</th><th>PREÇO / m²</th></tr></thead><tbody>';
        $html .= sprintf('<tr><td>ETAPA BRUTA (CINZA)</td><td>R$ %s</td><td>%s</td><td>R$ %s</td></tr>',
            self::formatarValor($totalCinza), $area, self::formatarValor($totalCinza / (float)$area));
        $html .= sprintf('<tr><td>ETAPA ACABAMENTOS</td><td>R$ %s</td><td>%s</td><td>R$ %s</td></tr>',
            self::formatarValor($totalAcabamentos), $area, self::formatarValor($totalAcabamentos / (float)$area));
        $html .= sprintf('<tr><td>GERENCIAMENTO / INDIRETOS / IMPOSTOS</td><td>R$ %s</td><td>%s</td><td>R$ %s</td></tr>',
            self::formatarValor($totalGerenciamento + $totalAdm), $area, self::formatarValor(($totalGerenciamento + $totalAdm) / (float)$area));
        $html .= sprintf('<tr class="total-row"><td>TOTAL GERAL:</td><td>R$ %s</td><td>%s</td><td>R$ %s</td></tr>',
            self::formatarValor($totalGeral), $area, self::formatarValor($totalGeral / (float)$area));
        $html .= '</tbody></table>';
        
        $html .= '</div>';
        $html .= '<div class="page-footer"><div class="footer-content"><span class="footer-left"></span><span class="footer-right">FOLHA: 3</span></div></div>';
        $html .= '</div>';
        
        return $html;
    }

