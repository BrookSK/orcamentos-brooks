<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\OrcamentoItem;
use App\Models\OrcamentoCor;

final class OrcamentoPDF
{
    /**
     * Gera HTML completo para exportação em PDF
     * Estrutura: Capa → Resumo (3 páginas) → Apresentação → Expertise → Objeto → Detalhamento Completo
     */
    public static function gerarHTML(int $orcamentoId, array $orcamento): string
    {
        $html = self::gerarCabecalhoHTML();
        $html .= self::gerarPaginaApresentacao($orcamento);
        $html .= self::gerarPaginaResumoExecutivo($orcamentoId, $orcamento);
        $html .= self::gerarPaginaApresentacaoInstitucional();
        $html .= self::gerarPaginaExpertise();
        $html .= self::gerarPaginaObjetoProposta();
        $html .= self::gerarPaginaDetalhamento($orcamentoId, $orcamento);
        $html .= self::gerarRodapeHTML();
        
        return $html;
    }
    
    /**
     * Gera cabeçalho HTML com estilos CSS modernos - Layout Brooks Construtora
     */
    private static function gerarCabecalhoHTML(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposta Orçamentária - Brooks Construtora</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            color: #333;
            line-height: 1.4;
        }
        
        .page {
            page-break-after: always;
            position: relative;
            width: 210mm;
            min-height: 297mm;
            background: white;
        }
        
        /* HEADER PADRÃO - Todas as páginas exceto capa */
        .page-header {
            background: #1a1a2e;
            padding: 15px 20mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        
        .header-left {
            font-size: 10px;
            line-height: 1.4;
        }
        
        .header-logo {
            text-align: center;
        }
        
        .header-logo-text {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #fff;
        }
        
        .header-logo-sub {
            font-size: 9px;
            letter-spacing: 1px;
            color: #c92a2a;
        }
        
        .header-right {
            font-size: 10px;
            text-align: right;
            line-height: 1.4;
        }
        
        .page-content {
            padding: 20mm;
        }
        
        /* PÁGINA DE CAPA */
        .page-cover {
            background: #1a1a2e;
            color: white;
            padding: 0;
        }
        
        .cover-header {
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .cover-logo {
            flex: 1;
        }
        
        .cover-logo-text {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 3px;
            color: #fff;
        }
        
        .cover-logo-sub {
            font-size: 11px;
            letter-spacing: 1.5px;
            color: #c92a2a;
            margin-top: 5px;
        }
        
        .cover-info-box {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px 20px;
            border-radius: 5px;
            font-size: 11px;
            line-height: 1.6;
            text-align: right;
        }
        
        .cover-body {
            padding: 80px 40px;
            text-align: center;
        }
        
        .cover-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 30px;
            letter-spacing: 1px;
            color: #c92a2a;
        }
        
        .cover-subtitle {
            font-size: 20px;
            font-weight: 400;
            margin-bottom: 60px;
            color: #fff;
        }
        
        /* PLANILHA RESUMO - Estilo dos prints */
        .resumo-title {
            text-align: center;
            margin: 30px 0 10px 0;
        }
        
        .resumo-title h2 {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 5px;
        }
        
        .resumo-title h3 {
            font-size: 14px;
            font-weight: 400;
            color: #c92a2a;
        }
        
        .table-resumo-clean {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 11px;
        }
        
        .table-resumo-clean th {
            background: #4a4a4a;
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #333;
        }
        
        .table-resumo-clean td {
            padding: 10px;
            border: 1px solid #ddd;
            background: white;
        }
        
        .table-resumo-clean .subtotal-row {
            background: #c92a2a;
            color: white;
            font-weight: 700;
        }
        
        .table-resumo-clean .total-row {
            background: #1a1a2e;
            color: white;
            font-weight: 700;
            font-size: 12px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .page-number {
            position: absolute;
            bottom: 15mm;
            right: 20mm;
            font-size: 10px;
            color: #666;
        }
        
        /* TABELAS DE ÁREAS */
        .table-areas {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            font-size: 11px;
        }
        
        .table-areas th {
            background: #f0f0f0;
            padding: 10px;
            border: 1px solid #ccc;
            font-weight: 600;
            text-align: center;
        }
        
        .table-areas td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        
        .table-areas .total-row {
            background: #e0e0e0;
            font-weight: 700;
        }
        
        /* DETALHAMENTO COMPLETO */
        .section-title {
            font-size: 28px;
            color: #1a1a2e;
            text-align: center;
            margin: 40px 0 30px 0;
            padding: 20px;
            border-bottom: 4px solid #c92a2a;
        }
        
        .etapa-section {
            margin: 30px 0;
            page-break-inside: avoid;
        }
        
        .etapa-header {
            background: #1a1a2e;
            padding: 15px 20px;
            margin-bottom: 15px;
        }
        
        .etapa-title {
            font-size: 18px;
            color: white;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .table-itens {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 15px;
        }
        
        .table-itens th {
            background: #4a4a4a;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #333;
        }
        
        .table-itens td {
            padding: 8px;
            border: 1px solid #ddd;
            background: white;
        }
        
        .table-itens tr:nth-child(even) td {
            background: #f9f9f9;
        }
        
        .resumo-etapa {
            background: #c92a2a;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .total-final {
            background: #1a1a2e;
            color: white;
            padding: 30px;
            text-align: center;
            margin: 40px 0;
            border-radius: 5px;
        }
        
        .total-final h2 {
            font-size: 20px;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }
        
        .total-final-value {
            font-size: 32px;
            font-weight: 700;
            color: #c92a2a;
            margin: 10px 0;
        }
    </style>
</head>
<body>
HTML;
    }
    
    /**
     * Gera página de capa/apresentação - Estilo Brooks Construtora
     */
    private static function gerarPaginaApresentacao(array $orcamento): string
    {
        $numeroProposta = htmlspecialchars($orcamento['numero_proposta'] ?? '');
        $clienteNome = htmlspecialchars($orcamento['cliente_nome'] ?? '');
        $endereco = htmlspecialchars($orcamento['endereco_obra'] ?? '');
        $area = htmlspecialchars($orcamento['area_m2'] ?? '');
        $prazo = htmlspecialchars($orcamento['prazo_dias'] ?? '');
        $prazomeses = $prazo ? round((int)$prazo / 30) : '';
        $rev = htmlspecialchars($orcamento['rev'] ?? 'R00');
        $data = date('d/m/Y', strtotime($orcamento['data'] ?? 'now'));
        
        return <<<HTML
<div class="page page-cover">
    <div class="cover-header">
        <div class="cover-logo">
            <div class="cover-logo-text">BROOKS</div>
            <div class="cover-logo-sub">CONSTRUTORA</div>
        </div>
        <div class="cover-info-box">
            <div><strong>REVISÃO:</strong> {$rev}</div>
            <div><strong>ÁREA:</strong> {$area}m²</div>
            <div><strong>DATA:</strong> {$data}</div>
        </div>
    </div>
    
    <div class="cover-body">
        <div class="cover-title">PLANILHA RESUMO</div>
        <div class="cover-subtitle">ETAPA CINZA (BRUTA) + ACABAMENTOS | ADMINISTRAÇÃO</div>
        
        <div style="margin-top: 80px; font-size: 12px; line-height: 1.8;">
            <div><strong>{$numeroProposta}</strong></div>
            <div><strong>CLIENTE:</strong> {$clienteNome}</div>
            <div><strong>ENDEREÇO:</strong> {$endereco}</div>
            <div><strong>PRAZO DE OBRA:</strong> {$prazomeses} meses</div>
        </div>
    </div>
</div>
HTML;
    }
    
    /**
     * Gera página de Resumo Executivo - Estilo Planilha Resumo (como nos prints)
     */
    private static function gerarPaginaResumoExecutivo(int $orcamentoId, array $orcamento): string
    {
        $pdo = \App\Core\Database::pdo();
        
        // Buscar itens agrupados por código principal
        $stmt = $pdo->prepare(
            'SELECT codigo, grupo, SUM(valor_cobranca) as valor_total '
            . 'FROM orcamento_itens '
            . 'WHERE orcamento_id = :id '
            . 'GROUP BY CAST(SUBSTRING_INDEX(codigo, \'.\', 1) AS UNSIGNED), grupo '
            . 'ORDER BY CAST(SUBSTRING_INDEX(codigo, \'.\', 1) AS UNSIGNED)'
        );
        $stmt->execute([':id' => $orcamentoId]);
        $itensAgrupados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Calcular totais por etapa
        $totalCinza = 0;
        $totalAcabamentos = 0;
        $totalGerenciamento = 0;
        $totalAdm = 0;
        
        $itensCinza = [];
        $itensAcabamentos = [];
        $itensGerenciamento = [];
        $itensAdm = [];
        
        foreach ($itensAgrupados as $item) {
            $codigoNum = (int)explode('.', $item['codigo'])[0];
            $valor = (float)$item['valor_total'];
            
            if ($codigoNum >= 1 && $codigoNum <= 17) {
                $itensCinza[] = $item;
                $totalCinza += $valor;
            } elseif ($codigoNum >= 18 && $codigoNum <= 41) {
                $itensAcabamentos[] = $item;
                $totalAcabamentos += $valor;
            } elseif ($codigoNum === 42) {
                $itensGerenciamento[] = $item;
                $totalGerenciamento += $valor;
            } else {
                $itensAdm[] = $item;
                $totalAdm += $valor;
            }
        }
        
        $totalGeral = $totalCinza + $totalAcabamentos + $totalGerenciamento + $totalAdm;
        
        $numeroProposta = htmlspecialchars($orcamento['numero_proposta'] ?? '');
        $clienteNome = htmlspecialchars($orcamento['cliente_nome'] ?? '');
        $endereco = htmlspecialchars($orcamento['endereco_obra'] ?? '');
        $prazo = htmlspecialchars($orcamento['prazo_dias'] ?? '');
        $prazomeses = $prazo ? round((int)$prazo / 30) : '';
        $area = htmlspecialchars($orcamento['area_m2'] ?? '');
        $rev = htmlspecialchars($orcamento['rev'] ?? 'R00');
        $data = date('d/m/Y', strtotime($orcamento['data'] ?? 'now'));
        
        // PÁGINA 1 - ETAPA CINZA
        $html = <<<HTML
<div class="page">
    <div class="page-header">
        <div class="header-left">
            <div><strong>{$numeroProposta}</strong></div>
            <div><strong>CLIENTE:</strong> {$clienteNome}</div>
            <div><strong>ENDEREÇO:</strong> {$endereco}</div>
            <div><strong>PRAZO DE OBRA:</strong> {$prazomeses} meses</div>
        </div>
        <div class="header-logo">
            <div class="header-logo-text">BROOKS</div>
            <div class="header-logo-sub">CONSTRUTORA</div>
        </div>
        <div class="header-right">
            <div><strong>REVISÃO:</strong> {$rev}</div>
            <div><strong>ÁREA:</strong> {$area}m²</div>
            <div><strong>DATA:</strong> {$data}</div>
        </div>
    </div>
    
    <div class="page-content">
        <div class="resumo-title">
            <h2>PLANILHA RESUMO</h2>
            <h3>ETAPA CINZA (BRUTA) + ACABAMENTOS | ADMINISTRAÇÃO</h3>
        </div>
        
        <table class="table-resumo-clean">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 65%;">ETAPA CINZA</th>
                    <th style="width: 20%; text-align: right;">VALOR TOTAL</th>
                    <th style="width: 10%; text-align: right;">%</th>
                </tr>
            </thead>
            <tbody>
HTML;
        
        $numero = 1;
        foreach ($itensCinza as $item) {
            $valor = (float)$item['valor_total'];
            $pct = $totalCinza > 0 ? ($valor / $totalCinza) * 100 : 0;
            
            $html .= sprintf(
                '<tr>'
                . '<td style="text-align: center;">%d</td>'
                . '<td>%s</td>'
                . '<td style="text-align: right;">%s</td>'
                . '<td style="text-align: right;">%.2f%%</td>'
                . '</tr>',
                $numero++,
                htmlspecialchars($item['grupo']),
                self::formatarValor($valor),
                $pct
            );
        }
        
        $pctCinza = $totalGeral > 0 ? ($totalCinza / $totalGeral) * 100 : 0;
        
        $html .= sprintf(
            '<tr class="subtotal-row">'
            . '<td colspan="2" style="text-align: left; padding-left: 20px;">SUBTOTAL - ETAPA CINZA</td>'
            . '<td style="text-align: right;">R$ %s</td>'
            . '<td style="text-align: right;">%.2f%%</td>'
            . '</tr>',
            self::formatarValor($totalCinza),
            $pctCinza
        );
        
        $html .= <<<HTML
            </tbody>
        </table>
    </div>
    
    <div class="page-number">FOLHA: 1</div>
</div>
HTML;
        
        // PÁGINA 2 - ETAPA ACABAMENTOS
        $html .= <<<HTML
<div class="page">
    <div class="page-header">
        <div class="header-left">
            <div><strong>{$numeroProposta}</strong></div>
            <div><strong>CLIENTE:</strong> {$clienteNome}</div>
            <div><strong>ENDEREÇO:</strong> {$endereco}</div>
            <div><strong>PRAZO DE OBRA:</strong> {$prazomeses} meses</div>
        </div>
        <div class="header-logo">
            <div class="header-logo-text">BROOKS</div>
            <div class="header-logo-sub">CONSTRUTORA</div>
        </div>
        <div class="header-right">
            <div><strong>REVISÃO:</strong> {$rev}</div>
            <div><strong>ÁREA:</strong> {$area}m²</div>
            <div><strong>DATA:</strong> {$data}</div>
        </div>
    </div>
    
    <div class="page-content">
        <div class="resumo-title">
            <h2>PLANILHA RESUMO</h2>
            <h3>ETAPA CINZA (BRUTA) + ACABAMENTOS | ADMINISTRAÇÃO</h3>
        </div>
        
        <table class="table-resumo-clean">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 65%;">ETAPA DE ACABAMENTOS</th>
                    <th style="width: 20%; text-align: right;">VALOR TOTAL</th>
                    <th style="width: 10%; text-align: right;">%</th>
                </tr>
            </thead>
            <tbody>
HTML;
        
        $numero = 18;
        foreach ($itensAcabamentos as $item) {
            $valor = (float)$item['valor_total'];
            $pct = $totalAcabamentos > 0 ? ($valor / $totalAcabamentos) * 100 : 0;
            
            $html .= sprintf(
                '<tr>'
                . '<td style="text-align: center;">%d</td>'
                . '<td>%s</td>'
                . '<td style="text-align: right;">%s</td>'
                . '<td style="text-align: right;">%.2f%%</td>'
                . '</tr>',
                $numero++,
                htmlspecialchars($item['grupo']),
                self::formatarValor($valor),
                $pct
            );
        }
        
        $pctAcabamentos = $totalGeral > 0 ? ($totalAcabamentos / $totalGeral) * 100 : 0;
        
        $html .= sprintf(
            '<tr class="subtotal-row">'
            . '<td colspan="2" style="text-align: left; padding-left: 20px;">SUBTOTAL - ETAPA ACABAMENTOS</td>'
            . '<td style="text-align: right;">R$ %s</td>'
            . '<td style="text-align: right;">%.2f%%</td>'
            . '</tr>',
            self::formatarValor($totalAcabamentos),
            $pctAcabamentos
        );
        
        $html .= <<<HTML
            </tbody>
        </table>
    </div>
    
    <div class="page-number">FOLHA: 2</div>
</div>
HTML;
        
        // PÁGINA 3 - GERENCIAMENTO + ADM + TOTAL + ÁREAS
        $pctGerenciamento = $totalGeral > 0 ? ($totalGerenciamento / $totalGeral) * 100 : 0;
        $pctAdm = $totalGeral > 0 ? ($totalAdm / $totalGeral) * 100 : 0;
        
        $html .= <<<HTML
<div class="page">
    <div class="page-header">
        <div class="header-left">
            <div><strong>{$numeroProposta}</strong></div>
            <div><strong>CLIENTE:</strong> {$clienteNome}</div>
            <div><strong>ENDEREÇO:</strong> {$endereco}</div>
            <div><strong>PRAZO DE OBRA:</strong> {$prazomeses} meses</div>
        </div>
        <div class="header-logo">
            <div class="header-logo-text">BROOKS</div>
            <div class="header-logo-sub">CONSTRUTORA</div>
        </div>
        <div class="header-right">
            <div><strong>REVISÃO:</strong> {$rev}</div>
            <div><strong>ÁREA:</strong> {$area}m²</div>
            <div><strong>DATA:</strong> {$data}</div>
        </div>
    </div>
    
    <div class="page-content">
        <div class="resumo-title">
            <h2>PLANILHA RESUMO</h2>
            <h3>ETAPA CINZA (BRUTA) + ACABAMENTOS | ADMINISTRAÇÃO</h3>
        </div>
        
        <table class="table-resumo-clean">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 65%;">ETAPA DE GERENCIAMENTO</th>
                    <th style="width: 20%; text-align: right;">VALOR TOTAL</th>
                    <th style="width: 10%; text-align: right;">%</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">42</td>
                    <td>EQUIPE DE OBRA</td>
                    <td style="text-align: right;">{self::formatarValor($totalGerenciamento)}</td>
                    <td style="text-align: right;">100,00%</td>
                </tr>
                <tr class="subtotal-row">
                    <td colspan="2" style="text-align: left; padding-left: 20px;">SUBTOTAL - ETAPA DE GERENCIAMENTO</td>
                    <td style="text-align: right;">R$ {self::formatarValor($totalGerenciamento)}</td>
                    <td style="text-align: right;">{sprintf('%.2f%%', $pctGerenciamento)}</td>
                </tr>
            </tbody>
        </table>
        
        <table class="table-resumo-clean" style="margin-top: 30px;">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 65%;">TAXA DE ADMINISTRAÇÃO + IMPOSTOS</th>
                    <th style="width: 20%; text-align: right;">VALOR TOTAL</th>
                    <th style="width: 10%; text-align: right;">%</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">43</td>
                    <td>TAXA DE ADMINISTRAÇÃO + IMPOSTOS</td>
                    <td style="text-align: right;">{self::formatarValor($totalAdm)}</td>
                    <td style="text-align: right;">100,00%</td>
                </tr>
                <tr class="subtotal-row">
                    <td colspan="2" style="text-align: left; padding-left: 20px;">SUBTOTAL - TAXA DE ADMINISTRAÇÃO + IMPOSTOS</td>
                    <td style="text-align: right;">R$ {self::formatarValor($totalAdm)}</td>
                    <td style="text-align: right;">{sprintf('%.2f%%', $pctAdm)}</td>
                </tr>
            </tbody>
        </table>
        
        <table class="table-resumo-clean" style="margin-top: 30px;">
            <tbody>
                <tr class="total-row">
                    <td colspan="2" style="text-align: left; padding-left: 20px; font-size: 14px;">VALOR TOTAL GERAL + TAXA DE ADMINISTRAÇÃO + IMPOSTOS:</td>
                    <td style="text-align: right; font-size: 14px;">R$ {self::formatarValor($totalGeral)}</td>
                    <td style="text-align: right; font-size: 14px;">100,00%</td>
                </tr>
            </tbody>
        </table>
        
        <table class="table-areas">
            <thead>
                <tr>
                    <th>ÁREAS</th>
                    <th>m2</th>
                    <th>FATOR</th>
                    <th>m2 x FATOR</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>ÁREA INTERNA</td>
                    <td>344,10</td>
                    <td>1</td>
                    <td>344,10</td>
                </tr>
                <tr>
                    <td>VARANDA COBERTA</td>
                    <td>103,94</td>
                    <td>1</td>
                    <td>103,94</td>
                </tr>
                <tr>
                    <td>ABRIGO AUTOS</td>
                    <td>47,52</td>
                    <td>1</td>
                    <td>47,52</td>
                </tr>
                <tr>
                    <td>ÁREA DESCOBERTA</td>
                    <td>139,79</td>
                    <td>1</td>
                    <td>139,79</td>
                </tr>
                <tr>
                    <td>PISCINA</td>
                    <td>87,62</td>
                    <td>1</td>
                    <td>87,62</td>
                </tr>
                <tr class="total-row">
                    <td colspan="3"><strong>ÁREA TOTAL:</strong></td>
                    <td><strong>{$area}</strong></td>
                </tr>
            </tbody>
        </table>
        
        <table class="table-areas" style="margin-top: 30px;">
            <thead>
                <tr>
                    <th>ETAPAS</th>
                    <th>PREÇO</th>
                    <th>M2</th>
                    <th>PREÇO / m2</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>ETAPA BRUTA (CINZA)</td>
                    <td>R$ {self::formatarValor($totalCinza)}</td>
                    <td>{$area}</td>
                    <td>R$ {self::formatarValor($totalCinza / (float)$area)}</td>
                </tr>
                <tr>
                    <td>ETAPA ACABAMENTOS</td>
                    <td>R$ {self::formatarValor($totalAcabamentos)}</td>
                    <td>{$area}</td>
                    <td>R$ {self::formatarValor($totalAcabamentos / (float)$area)}</td>
                </tr>
                <tr>
                    <td>GERENCIAMENTO / INDIRETOS / IMPOSTOS</td>
                    <td>R$ {self::formatarValor($totalGerenciamento + $totalAdm)}</td>
                    <td>{$area}</td>
                    <td>R$ {self::formatarValor(($totalGerenciamento + $totalAdm) / (float)$area)}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>TOTAL GERAL:</strong></td>
                    <td><strong>R$ {self::formatarValor($totalGeral)}</strong></td>
                    <td><strong>{$area}</strong></td>
                    <td><strong>R$ {self::formatarValor($totalGeral / (float)$area)}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="page-number">FOLHA: 3</div>
</div>
HTML;
        
        return $html;
    }
    
    /**
     * Gera página "Prezados" com texto de apresentação - Brooks Construtora
     */
    private static function gerarPaginaApresentacaoInstitucional(): string
    {
        return <<<'HTML'
<div class="page">
    <div class="page-header">
        <div class="header-left">
            <div><strong>BROOKS CONSTRUTORA</strong></div>
            <div>Engenharia e Construção Civil</div>
        </div>
        <div class="header-logo">
            <div class="header-logo-text">BROOKS</div>
            <div class="header-logo-sub">CONSTRUTORA</div>
        </div>
        <div class="header-right">
            <div>Tel: (11) 3063-2263</div>
            <div>contato@brooks.com.br</div>
        </div>
    </div>
    
    <div class="page-content">
        <h1 style="font-size: 24px; color: #1a1a2e; margin-bottom: 20px; border-bottom: 3px solid #c92a2a; padding-bottom: 10px;">Prezados</h1>
        
        <p style="font-size: 12px; line-height: 1.8; text-align: justify; margin-bottom: 20px; color: #444;">
            Agradecemos imensamente o seu contato! Temos certeza de que esta proposta trará soluções inovadoras e valor para o 
            seu projeto. Nas próximas páginas, apresentamos nossa empresa, detalhamos os serviços oferecidos, nossos valores e 
            observações importantes. Estamos à sua inteira disposição para qualquer esclarecimento ou para darmos o próximo passo.
        </p>
        
        <p style="font-weight: 600; color: #1a1a2e; margin-top: 15px; font-size: 12px;">Atenciosamente, <strong>BROOKS CONSTRUTORA</strong>.</p>
        
        <h2 style="font-size: 20px; color: #1a1a2e; margin: 40px 0 20px 0; border-bottom: 3px solid #c92a2a; padding-bottom: 10px;">Apresentação Institucional</h2>
        
        <p style="font-size: 12px; line-height: 1.8; text-align: justify; margin-bottom: 15px; color: #444;">
            A <strong>Brooks Construtora</strong> é uma empresa especializada na execução de obras civis, reformas integrais e retrofit de imóveis 
            residenciais, corporativos e comerciais de médio porte.
        </p>
        
        <p style="font-size: 12px; line-height: 1.8; text-align: justify; margin-bottom: 15px; color: #444;">
            Atuamos no segmento de construção civil por meio da execução completa de sistemas construtivos, contemplando:
        </p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; font-size: 11px;">
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Demolições técnicas</div>
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Infraestrutura para automação</div>
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Alvenaria estrutural e de vedação</div>
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Infraestrutura para climatização</div>
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Impermeabilizações</div>
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Cálculo estrutural e reforços</div>
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Instalação de básicos e revestimentos</div>
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Coberturas e fundações</div>
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Execução de meia esquadria</div>
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Pinturas técnicas e acabamentos</div>
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Pisos e sistemas em drywall</div>
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Logística e descarte de entulho</div>
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Instalações elétricas e hidráulicas</div>
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Instalação de louças e metais</div>
            <div style="padding-left: 15px; position: relative; color: #555;"><span style="position: absolute; left: 0; color: #c92a2a; font-weight: bold;">•</span> Suprimentos e materiais de obra</div>
        </div>
    </div>
    
    <div class="page-number">Página 4</div>
</div>
HTML;
    }
    
    /**
     * Gera página "Nossa Expertise" - Brooks Construtora
     */
    private static function gerarPaginaExpertise(): string
    {
        return <<<'HTML'
<div class="page">
    <div class="page-header">
        <div class="header-left">
            <div><strong>BROOKS CONSTRUTORA</strong></div>
            <div>Engenharia e Construção Civil</div>
        </div>
        <div class="header-logo">
            <div class="header-logo-text">BROOKS</div>
            <div class="header-logo-sub">CONSTRUTORA</div>
        </div>
        <div class="header-right">
            <div>Tel: (11) 3063-2263</div>
            <div>contato@brooks.com.br</div>
        </div>
    </div>
    
    <div class="page-content">
        <h1 style="font-size: 24px; color: #1a1a2e; margin-bottom: 20px; border-bottom: 3px solid #c92a2a; padding-bottom: 10px;">Nossa Expertise</h1>
        
        <p style="font-size: 12px; line-height: 1.8; text-align: justify; margin-bottom: 15px; color: #444;">
            Contamos com equipes especializadas de alta performance, coordenadas por engenheiro civil responsável técnico, 
            garantindo conformidade normativa, controle de qualidade de processos e cumprimento rigoroso de prazos.
        </p>
        
        <p style="font-size: 12px; line-height: 1.8; text-align: justify; margin-bottom: 25px; color: #444;">
            Possuímos centenas de obras entregues ao longo de quase uma década, mantendo parcerias consolidadas com escritórios 
            de arquitetura e fornecedores premium na cidade de São Paulo.
        </p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 25px;">
            <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; border-left: 4px solid #c92a2a;">
                <h3 style="font-size: 16px; color: #1a1a2e; margin-bottom: 12px;">Reconhecimento</h3>
                <p style="font-size: 11px; line-height: 1.6; color: #555;">
                    Nossos empreendimentos já foram publicados em veículos como <strong>Casa Vogue, Casa e Jardim, Diário do 
                    Arquiteto, De.cor.ar</strong>, entre outras mídias especializadas.
                </p>
            </div>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; border-left: 4px solid #c92a2a;">
                <h3 style="font-size: 16px; color: #1a1a2e; margin-bottom: 12px;">Compromisso</h3>
                <p style="font-size: 11px; line-height: 1.6; color: #555;">
                    Nossa prioridade permanece focada na excelência técnica, ética profissional, transparência contratual e 
                    satisfação integral do cliente.
                </p>
            </div>
        </div>
    </div>
    
    <div class="page-number">Página 5</div>
</div>
HTML;
    }
    
    /**
     * Gera página "Objeto da Proposta" - Brooks Construtora
     */
    private static function gerarPaginaObjetoProposta(): string
    {
        return <<<'HTML'
<div class="page">
    <div class="page-header">
        <div class="header-left">
            <div><strong>BROOKS CONSTRUTORA</strong></div>
            <div>Engenharia e Construção Civil</div>
        </div>
        <div class="header-logo">
            <div class="header-logo-text">BROOKS</div>
            <div class="header-logo-sub">CONSTRUTORA</div>
        </div>
        <div class="header-right">
            <div>Tel: (11) 3063-2263</div>
            <div>contato@brooks.com.br</div>
        </div>
    </div>
    
    <div class="page-content">
        <h1 style="font-size: 24px; color: #1a1a2e; margin-bottom: 20px; border-bottom: 3px solid #c92a2a; padding-bottom: 10px;">Objeto da Proposta</h1>
        
        <p style="font-size: 12px; line-height: 1.8; text-align: justify; margin-bottom: 15px; color: #444;">
            A presente proposta tem como objeto a prestação de serviços de obra civil, incluindo:
        </p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 25px 0;">
            <div style="background: white; border: 2px solid #e0e0e0; border-radius: 5px; padding: 15px;">
                <h3 style="font-size: 14px; color: #1a1a2e; margin-bottom: 8px;">Execução Técnica</h3>
                <p style="font-size: 11px; color: #666;">Execução técnica integral dos serviços descritos nas etapas seguintes.</p>
            </div>
            
            <div style="background: white; border: 2px solid #e0e0e0; border-radius: 5px; padding: 15px;">
                <h3 style="font-size: 14px; color: #1a1a2e; margin-bottom: 8px;">Documentação</h3>
                <p style="font-size: 11px; color: #666;">Emissão de ART de execução</p>
            </div>
            
            <div style="background: white; border: 2px solid #e0e0e0; border-radius: 5px; padding: 15px;">
                <h3 style="font-size: 14px; color: #1a1a2e; margin-bottom: 8px;">Proteção</h3>
                <p style="font-size: 11px; color: #666;">Seguro de obra</p>
            </div>
            
            <div style="background: white; border: 2px solid #e0e0e0; border-radius: 5px; padding: 15px;">
                <h3 style="font-size: 14px; color: #1a1a2e; margin-bottom: 8px;">Gestão</h3>
                <p style="font-size: 11px; color: #666;">Coordenação operacional</p>
            </div>
        </div>
        
        <p style="font-size: 12px; line-height: 1.8; text-align: justify; margin: 25px 0 15px 0; color: #444;">
            Todos os serviços serão executados exclusivamente conforme os descritivos técnicos desta proposta.
        </p>
        
        <h2 style="font-size: 20px; color: #1a1a2e; margin: 35px 0 20px 0; border-bottom: 3px solid #c92a2a; padding-bottom: 10px;">Notas Técnicas Gerais</h2>
        
        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 12px 0; border-radius: 3px;">
            <h4 style="font-size: 12px; color: #856404; margin-bottom: 8px; font-weight: 600;">⚠️ Nota 1 – Sistemas especiais</h4>
            <p style="font-size: 11px; color: #856404; line-height: 1.5;">
                Não estão inclusos: <strong>Pressurização, Aquecimento, Automação, Energia Fotovoltaica, Bombeamento, Piscinas, 
                Sistemas gerais, Cisternas, Sistemas complexos correlatos</strong>.
            </p>
            <p style="font-size: 11px; color: #856404; line-height: 1.5; margin-top: 8px;">
                Todos os itens não relacionados aos sistemas propostos de engenharia especializada contratados pelo cliente.
            </p>
        </div>
        
        <div style="background: #d1ecf1; border-left: 4px solid #0c5460; padding: 15px; margin: 12px 0; border-radius: 3px;">
            <h4 style="font-size: 12px; color: #0c5460; margin-bottom: 8px; font-weight: 600;">ℹ️ Nota 2 – Compatibilização</h4>
            <p style="font-size: 11px; color: #0c5460; line-height: 1.5;">
                O orçamento foi elaborado com base no projeto arquitetônico fornecido. Limitações físicas, centurianas, 
                condominiais ou pré-existências poderão gerar imobilidades técnicas, adaptações executivas, revisões 
                orçamentárias.
            </p>
            <p style="font-size: 11px; color: #0c5460; line-height: 1.5; margin-top: 8px;">
                A compatibilização entre arquitetura, engenharia e execução é indispensável.
            </p>
        </div>
    </div>
    
    <div class="page-number">Página 6</div>
</div>
HTML;
    }
    
    /**
     * Resolve o grupo de etapa de um item pelo prefixo numérico do código.
     * Retorna uma das 4 chaves fixas: 'cinza', 'acabamentos', 'gerenciamento', 'adm_impostos'
     */
    private static function resolverGrupoEtapa(string $codigo): string
    {
        $numero = (int)explode('.', trim($codigo))[0];

        if ($numero >= 1 && $numero <= 17) {
            return 'cinza';
        }
        if ($numero >= 18 && $numero <= 41) {
            return 'acabamentos';
        }
        if ($numero === 42) {
            return 'gerenciamento';
        }
        return 'adm_impostos';
    }

    /**
     * Gera páginas de detalhamento completo — planilha orçamentária agrupada por etapa.
     *
     * Regras:
     * - Cada item aparece UMA única vez (deduplicado por id).
     * - Agrupamento fixo: Cinza (1–17) | Acabamentos (18–41) | Gerenciamento (42) | Adm+Impostos (43+)
     * - % etapa  = valor_item / subtotal_da_etapa
     * - % total  = valor_item / total_geral
     * - total_geral = soma dos 4 subtotais
     */
    private static function gerarPaginaDetalhamento(int $orcamentoId, array $orcamento): string
    {
        $pdo = \App\Core\Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, codigo, descricao, quantidade, unidade, valor_unitario, valor_cobranca, percentual_realizado '
            . 'FROM orcamento_itens '
            . 'WHERE orcamento_id = :id '
            . 'ORDER BY CAST(SUBSTRING_INDEX(codigo, \'.\', 1) AS UNSIGNED), '
            . 'CAST(SUBSTRING_INDEX(codigo, \'.\', -1) AS UNSIGNED), id'
        );
        $stmt->execute([':id' => $orcamentoId]);
        $todosItens = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Agrupa por etapa, deduplicando por id
        $grupos = [
            'cinza'         => ['label' => 'ETAPA CINZA',                     'itens' => [], 'subtotal' => 0.0],
            'acabamentos'   => ['label' => 'ETAPA ACABAMENTOS',               'itens' => [], 'subtotal' => 0.0],
            'gerenciamento' => ['label' => 'ETAPA GERENCIAMENTO',             'itens' => [], 'subtotal' => 0.0],
            'adm_impostos'  => ['label' => 'TAXA DE ADMINISTRAÇÃO + IMPOSTOS', 'itens' => [], 'subtotal' => 0.0],
        ];


        $idsVistos = [];
        foreach ($todosItens as $item) {
            if (isset($idsVistos[$item['id']])) {
                continue; // garante que cada item aparece só uma vez
            }
            $idsVistos[$item['id']] = true;

            $chave = self::resolverGrupoEtapa((string)$item['codigo']);
            $grupos[$chave]['itens'][]   = $item;
            $grupos[$chave]['subtotal'] += (float)$item['valor_cobranca'];
        }

        // total_geral = soma dos 4 subtotais (nunca soma direta de itens)
        $totalGeral = array_sum(array_column($grupos, 'subtotal'));

        // Validação: soma dos subtotais == total_geral
        assert(abs(array_sum(array_column($grupos, 'subtotal')) - $totalGeral) < 0.01,
            'Soma dos subtotais difere do total geral');

        $html  = '<div class="page">';
        $html .= '<div class="page-header">';
        $html .= '<div class="header-left">';
        $html .= '<div><strong>BROOKS CONSTRUTORA</strong></div>';
        $html .= '<div>Engenharia e Construção Civil</div>';
        $html .= '</div>';
        $html .= '<div class="header-logo">';
        $html .= '<div class="header-logo-text">BROOKS</div>';
        $html .= '<div class="header-logo-sub">CONSTRUTORA</div>';
        $html .= '</div>';
        $html .= '<div class="header-right">';
        $html .= '<div>Tel: (11) 3063-2263</div>';
        $html .= '<div>contato@brooks.com.br</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="page-content">';
        $html .= '<h1 class="section-title">Detalhamento Completo</h1>';

        $paginaAtual = 7;

        foreach ($grupos as $grupo) {
            if (empty($grupo['itens'])) {
                continue;
            }

            $subtotal        = $grupo['subtotal'];
            $pctDoTotal      = $totalGeral > 0 ? ($subtotal / $totalGeral) * 100 : 0.0;

            $html .= '<div class="etapa-section">';
            $html .= sprintf(
                '<div class="etapa-header"><h2 class="etapa-title">%s</h2></div>',
                htmlspecialchars($grupo['label'])
            );

            $html .= self::gerarTabelaItens($grupo['itens'], $subtotal, $totalGeral);


            $html .= sprintf(
                '<div class="resumo-etapa">'
                . '<span>SUBTOTAL — %s</span>'
                . '<div>'
                . '<span style="margin-right: 20px;"><strong>R$ %s</strong></span>'
                . '<span style="font-size: 13px; opacity: 0.85;">%.2f%% do total</span>'
                . '</div>'
                . '</div>',
                htmlspecialchars($grupo['label']),
                self::formatarValor($subtotal),
                $pctDoTotal
            );

            $html .= '</div>'; // etapa-section
        }

        // Total Final
        $html .= sprintf(
            '<div class="total-final">'
            . '<h2>VALOR TOTAL GERAL DO PROJETO</h2>'
            . '<div class="total-final-value">R$ %s</div>'
            . '<div style="font-size: 14px; opacity: 0.9;">100,00%%</div>'
            . '</div>',
            self::formatarValor($totalGeral)
        );

        $html .= '</div>'; // page-content
        $html .= sprintf('<div class="page-number">Página %d</div>', $paginaAtual);
        $html .= '</div>'; // page

        return $html;
    }

    /**
     * Gera tabela de itens de uma etapa.
     * Colunas: CÓDIGO | DESCRIÇÃO | QUANT. | UNID | VALOR UNIT. | VALOR TOTAL | % ETAPA | % TOTAL | % REALIZADO
     *
     * @param array $itens      Itens da etapa (sem duplicatas)
     * @param float $subtotal   Subtotal da etapa — denominador de % etapa
     * @param float $totalGeral Total geral do orçamento — denominador de % total
     */
    private static function gerarTabelaItens(array $itens, float $subtotal, float $totalGeral): string
    {
        $html  = '<table class="table-itens">';
        $html .= '<thead><tr>';
        $html .= '<th style="width: 6%;">Código</th>';
        $html .= '<th style="width: 32%;">Descrição</th>';
        $html .= '<th style="width: 7%; text-align: center;">Quant.</th>';
        $html .= '<th style="width: 5%; text-align: center;">Unid</th>';
        $html .= '<th style="width: 12%; text-align: right;">Valor Unit.</th>';
        $html .= '<th style="width: 12%; text-align: right;">Valor Total</th>';
        $html .= '<th style="width: 8%; text-align: right;">% Etapa</th>';
        $html .= '<th style="width: 8%; text-align: right;">% Total</th>';
        $html .= '<th style="width: 10%; text-align: right;">% Realizado</th>';
        $html .= '</tr></thead><tbody>';

        $somaPctEtapa = 0.0;

        foreach ($itens as $item) {
            $quantidade    = (float)$item['quantidade'];
            $valorTotal    = (float)$item['valor_cobranca'];
            $valorUnitario = $quantidade > 0 ? $valorTotal / $quantidade : (float)$item['valor_unitario'];

            // % etapa = valor_item / subtotal_da_etapa (denominador correto)
            $pctEtapa = $subtotal > 0 ? ($valorTotal / $subtotal) * 100 : 0.0;
            // % total = valor_item / total_geral
            $pctTotal = $totalGeral > 0 ? ($valorTotal / $totalGeral) * 100 : 0.0;
            
            // % realizado: 0-100% onde 100% = % total do item
            $percentualRealizado = (float)($item['percentual_realizado'] ?? 0);
            $pctRealizadoEfetivo = ($percentualRealizado / 100) * $pctTotal;

            $somaPctEtapa += $pctEtapa;

            $html .= sprintf(
                '<tr>'
                . '<td>%s</td>'
                . '<td>%s</td>'
                . '<td style="text-align: center;">%s</td>'
                . '<td style="text-align: center;">%s</td>'
                . '<td style="text-align: right;">R$ %s</td>'
                . '<td style="text-align: right;"><strong>R$ %s</strong></td>'
                . '<td style="text-align: right;">%.2f%%</td>'
                . '<td style="text-align: right;">%.2f%%</td>'
                . '<td style="text-align: right;">%.2f%%</td>'
                . '</tr>',
                htmlspecialchars($item['codigo']),
                htmlspecialchars($item['descricao']),
                number_format($quantidade, 3, ',', '.'),
                htmlspecialchars($item['unidade']),
                self::formatarValor($valorUnitario),
                self::formatarValor($valorTotal),
                $pctEtapa,
                $pctTotal,
                $pctRealizadoEfetivo
            );
        }


        // Validação: soma de % etapa deve ser ~100%
        assert(
            $subtotal == 0.0 || abs($somaPctEtapa - 100.0) < 0.1,
            sprintf('Soma de %% etapa = %.4f%% (esperado ~100%%)', $somaPctEtapa)
        );

        $html .= '</tbody></table>';

        return $html;
    }
    
    /**
     * Gera rodapé HTML
     */
    private static function gerarRodapeHTML(): string
    {
        return '</body></html>';
    }
    
    /**
     * Formata valor monetário
     */
    private static function formatarValor(float $valor): string
    {
        return number_format($valor, 2, ',', '.');
    }
}
