<?php

declare(strict_types=1);

namespace App\Helpers;

final class OrcamentoPDF
{
    public static function gerarHTML(int $orcamentoId, array $orcamento): string
    {
        $html = self::gerarCabecalhoHTML();
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
@page { margin: 15mm; size: A4; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Helvetica, Arial, sans-serif; color: #000; line-height: 1.3; background: #FFF; font-size: 9pt; }
.page { page-break-after: always; background: #FFF; padding: 20px; }

/* CAPA */
.capa { background: #FFF; padding-top: 30px; }
.capa-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
.capa-logo { font-size: 28pt; font-weight: bold; color: #2C3350; }
.capa-logo-sub { font-size: 9pt; color: #666; letter-spacing: 0.1em; }
.capa-meta { text-align: right; font-size: 8pt; }
.capa-meta-label { color: #999; text-transform: uppercase; font-size: 7pt; }
.capa-meta-value { color: #2C3350; font-weight: bold; font-size: 10pt; margin-top: 2px; }
.capa-title { text-align: center; margin: 60px 0 40px 0; }
.capa-title h1 { font-size: 32pt; font-weight: normal; color: #2C3350; line-height: 1.2; }
.capa-title-sub { font-size: 9pt; color: #2C3350; margin-top: 10px; font-weight: bold; }
.capa-info { margin: 40px 0; }
.capa-info-item { margin-bottom: 20px; }
.capa-info-label { font-size: 7pt; color: #999; text-transform: uppercase; font-weight: bold; margin-bottom: 4px; }
.capa-info-value { font-size: 11pt; color: #2C3350; font-weight: bold; }
.capa-footer { margin-top: 100px; padding-top: 12px; border-top: 1px solid #CCC; font-size: 7pt; color: #666; display: flex; justify-content: space-between; }

/* HEADER PÁGINAS - SIMPLES COMO REFERÊNCIA */
.page-header { margin-bottom: 15px; margin-top: 20px; }
.page-header-info { font-size: 8pt; line-height: 1.5; margin-bottom: 10px; }
.page-header-info div { margin-bottom: 2px; }
.page-header-logo-container { text-align: center; margin: 15px 0; }
.page-header-logo { display: inline-block; padding: 10px 20px; }
.page-header-logo-text { font-size: 18pt; font-weight: bold; color: #2C3350; }
.page-header-logo-sub { font-size: 8pt; color: #666; }
.page-header-meta { position: absolute; top: 40px; right: 20px; text-align: right; font-size: 8pt; line-height: 1.6; }
.page-title { text-align: center; font-size: 14pt; font-weight: bold; color: #000; margin: 15px 0 5px 0; }
.page-subtitle { text-align: center; font-size: 8pt; color: #2C3350; font-weight: bold; margin-bottom: 15px; }

/* TABELAS RESUMO */
.etapa-header { background: #666; color: #FFF; padding: 8px 12px; font-weight: bold; font-size: 9pt; margin: 20px 0 0 0; text-align: center; }
.table-resumo { width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 8pt; margin-top: 10px; }
.table-resumo thead th { background: #666; color: #FFF; padding: 6px 8px; text-align: center; font-weight: bold; font-size: 8pt; border: 1px solid #666; }
.table-resumo thead th.left { text-align: left; }
.table-resumo tbody td { padding: 5px 8px; border: 1px solid #CCC; background: #FFF; }
.table-resumo tbody td.center { text-align: center; }
.table-resumo tbody td.right { text-align: right; }
.table-resumo .subtotal-row td { background: #2C3350 !important; color: #FFF; font-weight: bold; padding: 8px; border: 1px solid #2C3350; }
.table-resumo .total-row td { background: #000 !important; color: #FFF; font-weight: bold; padding: 10px 8px; font-size: 9pt; border: 1px solid #000; }

/* TABELAS ÁREAS */
.table-areas { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 8pt; margin-top: 20px; }
.table-areas thead th { background: #666; color: #FFF; padding: 6px 8px; font-weight: bold; text-align: center; font-size: 8pt; border: 1px solid #666; }
.table-areas tbody td { padding: 5px 8px; border: 1px solid #CCC; text-align: center; background: #FFF; }
.table-areas .total-row td { background: #666; color: #FFF; font-weight: bold; padding: 8px; border: 1px solid #666; }

/* TABELAS DETALHAMENTO */
.banner-etapa { background: #2C3350; color: #FFF; padding: 8px 12px; font-weight: bold; font-size: 9pt; margin: 20px 0 0 0; text-align: center; }
.table-detalhes { width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 7pt; margin-top: 10px; }
.table-detalhes thead th { background: #666; color: #FFF; padding: 5px 4px; text-align: center; font-weight: bold; font-size: 7pt; border: 1px solid #666; }
.table-detalhes thead th.left { text-align: left; padding-left: 6px; }
.table-detalhes thead th.right { text-align: right; padding-right: 6px; }
.table-detalhes tbody td { padding: 4px; border: 1px solid #CCC; vertical-align: middle; background: #FFF; font-size: 7pt; }
.table-detalhes tbody td.center { text-align: center; }
.table-detalhes tbody td.right { text-align: right; padding-right: 6px; }
.table-detalhes tbody td.left { text-align: left; padding-left: 6px; }
.subtotal-item { background: #2C3350; color: #FFF; padding: 6px 8px; font-weight: bold; font-size: 8pt; text-align: right; margin-top: 10px; }
.subtotal-etapa { background: #666; color: #FFF; padding: 8px 12px; font-weight: bold; font-size: 8pt; text-align: right; margin: 10px 0 0 0; }
.total-obra { background: #000; color: #FFF; padding: 10px 12px; font-weight: bold; font-size: 9pt; text-align: right; margin: 20px 0 10px 0; }

/* RODAPÉ */
.page-footer { margin-top: 20px; padding-top: 8px; border-top: 1px solid #2C3350; font-size: 7pt; color: #999; display: flex; justify-content: flex-end; }
</style>
</head>
<body>
HTML;
    }

    
    private static function gerarPaginaCapa(array $orcamento): string
    {
        $numeroProposta = htmlspecialchars((string)($orcamento['numero_proposta'] ?? ''));
        $clienteNome = htmlspecialchars((string)($orcamento['cliente_nome'] ?? ''));
        $endereco = htmlspecialchars((string)($orcamento['endereco_obra'] ?? ''));
        $local = htmlspecialchars((string)($orcamento['local_obra'] ?? ''));
        $area = htmlspecialchars((string)($orcamento['area_m2'] ?? ''));
        $prazo = (string)($orcamento['prazo_dias'] ?? '');
        $prazomeses = $prazo ? round((int)$prazo / 30) : '';
        $rev = htmlspecialchars((string)($orcamento['rev'] ?? 'R00'));
        $data = date('d/m/Y', strtotime($orcamento['data'] ?? 'now'));
        
        return <<<HTML
<div class="page capa">
    <div class="capa-header">
        <div>
            <div class="capa-logo">BROOKS</div>
            <div class="capa-logo-sub">CONSTRUTORA</div>
        </div>
        <div class="capa-meta">
            <div class="capa-meta-label">DOCUMENTO TÉCNICO</div>
            <div class="capa-meta-value">{$numeroProposta}</div>
        </div>
    </div>
    
    <div class="capa-title">
        <h1>PLANILHA<br/>ORÇAMENTÁRIA</h1>
        <div class="capa-title-sub">ETAPA CINZA (BRUTA) + ACABAMENTOS | ADMINISTRAÇÃO</div>
    </div>
    
    <div class="capa-info">
        <div class="capa-info-item">
            <div class="capa-info-label">CLIENTE</div>
            <div class="capa-info-value">{$clienteNome}</div>
        </div>
        <div class="capa-info-item">
            <div class="capa-info-label">REVISÃO</div>
            <div class="capa-info-value">{$rev}</div>
        </div>
        <div class="capa-info-item">
            <div class="capa-info-label">ENDEREÇO</div>
            <div class="capa-info-value">{$endereco}<br/>{$local}</div>
        </div>
        <div class="capa-info-item">
            <div class="capa-info-label">DATA</div>
            <div class="capa-info-value">{$data}</div>
        </div>
        <div class="capa-info-item">
            <div class="capa-info-label">PRAZO DE OBRA</div>
            <div class="capa-info-value">{$prazomeses} meses</div>
        </div>
        <div class="capa-info-item">
            <div class="capa-info-label">ÁREA TOTAL</div>
            <div class="capa-info-value">{$area} m²</div>
        </div>
    </div>
    
    <div class="capa-footer">
        <div>DOCUMENTO CONFIDENCIAL — USO RESTRITO</div>
        <div>REV. {$rev} · {$data}</div>
    </div>
</div>
HTML;
    }

    private static function gerarHeaderPadrao(array $orcamento, string $tituloSecao): string
    {
        $numeroProposta = htmlspecialchars((string)($orcamento['numero_proposta'] ?? ''));
        $clienteNome = htmlspecialchars((string)($orcamento['cliente_nome'] ?? ''));
        $endereco = htmlspecialchars((string)($orcamento['endereco_obra'] ?? ''));
        $local = htmlspecialchars((string)($orcamento['local_obra'] ?? ''));
        $area = htmlspecialchars((string)($orcamento['area_m2'] ?? ''));
        $prazo = (string)($orcamento['prazo_dias'] ?? '');
        $prazomeses = $prazo ? round((int)$prazo / 30) : '';
        $rev = htmlspecialchars((string)($orcamento['rev'] ?? 'R00'));
        $data = date('d/m/Y', strtotime($orcamento['data'] ?? 'now'));
        $logoPath = (string)($orcamento['logo_path'] ?? '');
        
        $logoHtml = '';
        if (!empty($logoPath)) {
            // Converter caminho relativo para absoluto
            $absolutePath = __DIR__ . '/../../' . ltrim($logoPath, '/');
            
            if (file_exists($absolutePath)) {
                // Converter imagem para base64
                $imageData = base64_encode(file_get_contents($absolutePath));
                $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
                $mimeType = $ext === 'png' ? 'image/png' : ($ext === 'jpg' || $ext === 'jpeg' ? 'image/jpeg' : 'image/png');
                $base64Src = 'data:' . $mimeType . ';base64,' . $imageData;
                $logoHtml = '<img src="' . $base64Src . '" style="max-width:180px;max-height:60px;" alt="Logo">';
            } else {
                // Fallback se arquivo não existir
                $logoHtml = '<div class="page-header-logo"><div class="page-header-logo-text">BROOKS</div><div class="page-header-logo-sub">CONSTRUTORA</div></div>';
            }
        } else {
            // Sem logo configurada
            $logoHtml = '<div class="page-header-logo"><div class="page-header-logo-text">BROOKS</div><div class="page-header-logo-sub">CONSTRUTORA</div></div>';
        }
        
        return <<<HTML
<div class="page-header">
    <div class="page-header-info">
        <div><strong>{$numeroProposta}</strong></div>
        <div><strong>CLIENTE:</strong> {$clienteNome}</div>
        <div><strong>ENDEREÇO:</strong> {$endereco} - {$local}</div>
        <div><strong>PRAZO DE OBRA:</strong> {$prazomeses} meses</div>
    </div>
    <div class="page-header-meta">
        <div><strong>REVISÃO:</strong> {$rev}</div>
        <div><strong>ÁREA:</strong> {$area} m²</div>
        <div><strong>DATA:</strong> {$data}</div>
    </div>
    <div class="page-header-logo-container">
        {$logoHtml}
    </div>
    <div class="page-title">{$tituloSecao}</div>
    <div class="page-subtitle">ETAPA CINZA (BRUTA) + ACABAMENTOS | ADMINISTRAÇÃO</div>
</div>
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
        
        // PÁGINA 1 - ETAPA CINZA
        $html = '<div class="page">' . self::gerarHeaderPadrao($orcamento, 'PLANILHA RESUMO');
        $html .= '<div class="etapa-header">ETAPA CINZA</div>';
        $html .= '<table class="table-resumo"><thead><tr>';
        $html .= '<th style="width:8%;">Nº</th><th style="width:60%;">DESCRIÇÃO</th>';
        $html .= '<th class="right" style="width:22%;">VALOR TOTAL</th><th class="center" style="width:10%;">%</th>';
        $html .= '</tr></thead><tbody>';
        
        $numero = 1;
        foreach ($itensCinza as $item) {
            $valor = (float)$item['valor_total'];
            $pct = $totalGeral > 0 ? ($valor / $totalGeral) * 100 : 0;
            $html .= sprintf(
                '<tr><td class="center">%d</td><td>%s</td><td class="right">R$ %s</td><td class="center">%s%%</td></tr>',
                $numero++,
                htmlspecialchars((string)$item['grupo']),
                self::formatarValor($valor),
                number_format($pct, 2, ',', '.')
            );
        }
        
        $pctCinza = $totalGeral > 0 ? ($totalCinza / $totalGeral) * 100 : 0;
        $html .= sprintf(
            '<tr class="subtotal-row"><td colspan="2">SUBTOTAL - ETAPA CIZA</td><td class="right">R$ %s</td><td class="center">%s%%</td></tr>',
            self::formatarValor($totalCinza),
            number_format($pctCinza, 2, ',', '.')
        );
        $html .= '</tbody></table>';
        $html .= '<div class="page-footer"><div style="text-align:right;padding:0 20px 8px;">FOLHA: 1</div></div>';
        $html .= '</div>';

        // PÁGINA 2 - ETAPA ACABAMENTOS
        $html .= '<div class="page">' . self::gerarHeaderPadrao($orcamento, 'PLANILHA RESUMO');
        $html .= '<div class="etapa-header">ETAPA ACABAMENTOS</div>';
        $html .= '<table class="table-resumo"><thead><tr>';
        $html .= '<th style="width:8%;">Nº</th><th style="width:60%;">DESCRIÇÃO</th>';
        $html .= '<th class="right" style="width:22%;">VALOR TOTAL</th><th class="center" style="width:10%;">%</th>';
        $html .= '</tr></thead><tbody>';
        
        $numero = 18;
        foreach ($itensAcabamentos as $item) {
            $valor = (float)$item['valor_total'];
            $pct = $totalGeral > 0 ? ($valor / $totalGeral) * 100 : 0;
            $html .= sprintf(
                '<tr><td class="center">%d</td><td>%s</td><td class="right">R$ %s</td><td class="center">%s%%</td></tr>',
                $numero++,
                htmlspecialchars((string)$item['grupo']),
                self::formatarValor($valor),
                number_format($pct, 2, ',', '.')
            );
        }
        
        $pctAcabamentos = $totalGeral > 0 ? ($totalAcabamentos / $totalGeral) * 100 : 0;
        $html .= sprintf(
            '<tr class="subtotal-row"><td colspan="2">SUBTOTAL - ETAPA ACABAMENTOS</td><td class="right">R$ %s</td><td class="center">%s%%</td></tr>',
            self::formatarValor($totalAcabamentos),
            number_format($pctAcabamentos, 2, ',', '.')
        );
        $html .= '</tbody></table>';
        $html .= '<div class="page-footer"><div style="text-align:right;padding:0 20px 8px;">FOLHA: 2</div></div>';
        $html .= '</div>';

        // PÁGINA 3 - GERENCIAMENTO + ADM + TOTAIS
        $html .= '<div class="page">' . self::gerarHeaderPadrao($orcamento, 'PLANILHA RESUMO');
        
        $pctGerenciamento = $totalGeral > 0 ? ($totalGerenciamento / $totalGeral) * 100 : 0;
        $html .= '<div class="etapa-header">ETAPA DE GERENCIAMENTO</div>';
        $html .= '<table class="table-resumo"><thead><tr>';
        $html .= '<th style="width:8%;">Nº</th><th class="left" style="width:60%;">DESCRIÇÃO</th>';
        $html .= '<th style="width:22%;">VALOR TOTAL</th><th style="width:10%;">%</th>';
        $html .= '</tr></thead><tbody>';
        $html .= sprintf(
            '<tr><td class="center">42</td><td>EQUIPE DE OBRA</td><td class="right">R$ %s</td><td class="center">100,00%%</td></tr>',
            self::formatarValor($totalGerenciamento)
        );
        $html .= sprintf(
            '<tr class="subtotal-row"><td colspan="2">SUBTOTAL - ETAPA DE GERENCIAMENTO</td><td class="right">R$ %s</td><td class="center">%s%%</td></tr>',
            self::formatarValor($totalGerenciamento),
            number_format($pctGerenciamento, 2, ',', '.')
        );
        $html .= '</tbody></table>';
        
        $pctAdm = $totalGeral > 0 ? ($totalAdm / $totalGeral) * 100 : 0;
        $html .= '<div class="etapa-header" style="margin-top:15px;">TAXA DE ADMINISTRAÇÃO + IMPOSTOS</div>';
        $html .= '<table class="table-resumo"><thead><tr>';
        $html .= '<th style="width:8%;">Nº</th><th class="left" style="width:60%;">DESCRIÇÃO</th>';
        $html .= '<th style="width:22%;">VALOR TOTAL</th><th style="width:10%;">%</th>';
        $html .= '</tr></thead><tbody>';
        $html .= sprintf(
            '<tr><td class="center">43</td><td>TAXA DE ADMINISTRAÇÃO + IMPOSTOS</td><td class="right">R$ %s</td><td class="center">100,00%%</td></tr>',
            self::formatarValor($totalAdm)
        );
        $html .= sprintf(
            '<tr class="subtotal-row"><td colspan="2">SUBTOTAL - TAXA DE ADMINISTRAÇÃO + IMPOSTOS</td><td class="right">R$ %s</td><td class="center">%s%%</td></tr>',
            self::formatarValor($totalAdm),
            number_format($pctAdm, 2, ',', '.')
        );
        $html .= '</tbody></table>';
        
        $html .= '<table class="table-resumo" style="margin-top:15px;"><tbody>';
        $html .= sprintf(
            '<tr class="total-row"><td colspan="2">VALOR TOTAL GERAL + TAXA DE ADMINISTRAÇÃO + IMPOSTOS:</td><td class="right">R$ %s</td><td class="center">100,00%%</td></tr>',
            self::formatarValor($totalGeral)
        );
        $html .= '</tbody></table>';

        // TABELAS DE ÁREAS
        $areaNum = (float)($orcamento['area_m2'] ?? 0);
        $html .= '<table class="table-areas" style="margin-top:20px;"><thead><tr><th>ÁREAS</th><th>m2</th><th>FATOR</th><th>m2 x FATOR</th></tr></thead><tbody>';
        $html .= '<tr><td>ÁREA INTERNA</td><td>344,10</td><td>1</td><td>344,10</td></tr>';
        $html .= '<tr><td>VARANDA COBERTA</td><td>103,94</td><td>1</td><td>103,94</td></tr>';
        $html .= '<tr><td>ABRIGO AUTOS</td><td>47,52</td><td>1</td><td>47,52</td></tr>';
        $html .= '<tr><td>ÁREA DESCOBERTA</td><td>139,79</td><td>1</td><td>139,79</td></tr>';
        $html .= '<tr><td>PISCINA</td><td>87,62</td><td>1</td><td>87,62</td></tr>';
        $html .= sprintf('<tr class="total-row"><td colspan="3">ÁREA TOTAL:</td><td>%s</td></tr>', number_format($areaNum, 2, ',', '.'));
        $html .= '</tbody></table>';
        
        $html .= '<table class="table-areas" style="margin-top:15px;"><thead><tr><th>ETAPAS</th><th>PREÇO</th><th>M2</th><th>PREÇO / m2</th></tr></thead><tbody>';
        $html .= sprintf(
            '<tr><td>ETAPA BRUTA (CINZA)</td><td>R$ %s</td><td>%s</td><td>R$ %s</td></tr>',
            self::formatarValor($totalCinza),
            number_format($areaNum, 2, ',', '.'),
            self::formatarValor($areaNum > 0 ? $totalCinza / $areaNum : 0)
        );
        $html .= sprintf(
            '<tr><td>ETAPA ACABAMENTOS</td><td>R$ %s</td><td>%s</td><td>R$ %s</td></tr>',
            self::formatarValor($totalAcabamentos),
            number_format($areaNum, 2, ',', '.'),
            self::formatarValor($areaNum > 0 ? $totalAcabamentos / $areaNum : 0)
        );
        $html .= sprintf(
            '<tr><td>GERENCIAMENTO / INDIRETOS / IMPOSTOS</td><td>R$ %s</td><td>%s</td><td>R$ %s</td></tr>',
            self::formatarValor($totalGerenciamento + $totalAdm),
            number_format($areaNum, 2, ',', '.'),
            self::formatarValor($areaNum > 0 ? ($totalGerenciamento + $totalAdm) / $areaNum : 0)
        );
        $html .= sprintf(
            '<tr class="total-row"><td>TOTAL GERAL:</td><td>R$ %s</td><td>%s</td><td>R$ %s</td></tr>',
            self::formatarValor($totalGeral),
            number_format($areaNum, 2, ',', '.'),
            self::formatarValor($areaNum > 0 ? $totalGeral / $areaNum : 0)
        );
        $html .= '</tbody></table>';
        
        $html .= '<div class="page-footer"><div>FOLHA: 3</div></div>';
        $html .= '</div>';
        
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
            'SELECT id, codigo, descricao, quantidade, unidade, valor_unitario, valor_cobranca, percentual_realizado, custo_material, custo_mao_obra '
            . 'FROM orcamento_itens WHERE orcamento_id = :id '
            . 'ORDER BY CAST(SUBSTRING_INDEX(codigo, \'.\', 1) AS UNSIGNED), CAST(SUBSTRING_INDEX(codigo, \'.\', -1) AS UNSIGNED), id'
        );
        $stmt->execute([':id' => $orcamentoId]);
        $todosItens = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $grupos = [
            'cinza' => ['label' => 'ETAPA CINZA', 'itens' => [], 'subtotal' => 0.0],
            'acabamentos' => ['label' => 'ETAPA ACABAMENTOS', 'itens' => [], 'subtotal' => 0.0],
            'gerenciamento' => ['label' => 'ETAPA DE GERENCIAMENTO', 'itens' => [], 'subtotal' => 0.0],
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
        
        $html = '<div class="page">' . self::gerarHeaderPadrao($orcamento, 'PLANILHA ORÇAMENTÁRIA');
        
        foreach ($grupos as $grupo) {
            if (empty($grupo['itens'])) continue;
            $subtotal = $grupo['subtotal'];
            $pctDoTotal = $totalGeral > 0 ? ($subtotal / $totalGeral) * 100 : 0.0;
            
            $html .= sprintf('<div class="banner-etapa">%s</div>', htmlspecialchars((string)$grupo['label']));
            $html .= self::gerarTabelaDetalhes($grupo['itens'], $subtotal, $totalGeral);
            $html .= sprintf(
                '<div class="subtotal-etapa">SUBTOTAL — %s: R$ %s</div>',
                htmlspecialchars((string)$grupo['label']),
                self::formatarValor($subtotal)
            );
        }
        
        $html .= sprintf(
            '<div class="total-obra">VALOR TOTAL DE OBRA [CUSTO TOTAL DE OBRA + EQUIPE DE OBRA]: R$ %s</div>',
            self::formatarValor($totalGeral)
        );
        
        $html .= '<div class="page-footer"><div>FOLHA: 4</div></div>';
        $html .= '</div>';
        
        return $html;
    }

    
    private static function gerarTabelaDetalhes(array $itens, float $subtotal, float $totalGeral): string
    {
        $html = '<table class="table-detalhes"><thead><tr>';
        $html .= '<th class="left" style="width:5%;">ITEM</th>';
        $html .= '<th class="left" style="width:25%;">DESCRIÇÃO</th>';
        $html .= '<th class="center" style="width:5%;">UNID</th>';
        $html .= '<th class="center" style="width:6%;">QUANT</th>';
        $html .= '<th class="right" style="width:10%;">VALOR UNIT MATERIAL</th>';
        $html .= '<th class="right" style="width:10%;">VALOR UNIT M.O</th>';
        $html .= '<th class="right" style="width:10%;">VALOR UNITÁRIO TOTAL</th>';
        $html .= '<th class="right" style="width:11%;">VALOR TOTAL</th>';
        $html .= '<th class="center" style="width:7%;">% ETAPA</th>';
        $html .= '<th class="center" style="width:7%;">% OBRA</th>';
        $html .= '<th class="center" style="width:4%;">% REALIZADO</th>';
        $html .= '</tr></thead><tbody>';
        
        foreach ($itens as $item) {
            $quantidade = (float)$item['quantidade'];
            $valorTotal = (float)$item['valor_cobranca'];
            $custoMaterial = (float)($item['custo_material'] ?? 0);
            $custoMaoObra = (float)($item['custo_mao_obra'] ?? 0);
            
            $valorUnitMaterial = $quantidade > 0 ? $custoMaterial / $quantidade : 0;
            $valorUnitMaoObra = $quantidade > 0 ? $custoMaoObra / $quantidade : 0;
            $valorUnitTotal = $quantidade > 0 ? $valorTotal / $quantidade : (float)$item['valor_unitario'];
            
            $pctEtapa = $subtotal > 0 ? ($valorTotal / $subtotal) * 100 : 0.0;
            $pctObra = $totalGeral > 0 ? ($valorTotal / $totalGeral) * 100 : 0.0;
            $percentualRealizado = (float)($item['percentual_realizado'] ?? 0);
            $pctRealizadoEfetivo = ($percentualRealizado / 100) * $pctObra;
            
            $html .= '<tr>';
            $html .= '<td class="left">' . htmlspecialchars((string)$item['codigo']) . '</td>';
            $html .= '<td class="left">' . htmlspecialchars((string)$item['descricao']) . '</td>';
            $html .= '<td class="center">' . htmlspecialchars((string)$item['unidade']) . '</td>';
            $html .= '<td class="center">' . number_format($quantidade, 2, ',', '.') . '</td>';
            $html .= '<td class="right">R$ ' . self::formatarValor($valorUnitMaterial) . '</td>';
            $html .= '<td class="right">R$ ' . self::formatarValor($valorUnitMaoObra) . '</td>';
            $html .= '<td class="right">R$ ' . self::formatarValor($valorUnitTotal) . '</td>';
            $html .= '<td class="right">R$ ' . self::formatarValor($valorTotal) . '</td>';
            $html .= '<td class="center">' . number_format($pctEtapa, 2, ',', '.') . '%</td>';
            $html .= '<td class="center">' . number_format($pctObra, 2, ',', '.') . '%</td>';
            $html .= '<td class="center">' . number_format($pctRealizadoEfetivo, 2, ',', '.') . '%</td>';
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
