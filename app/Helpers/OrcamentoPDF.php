<?php

declare(strict_types=1);

namespace App\Helpers;

final class OrcamentoPDF
{
    public static function gerarHTML(int $orcamentoId, array $orcamento): string
    {
        $html = self::gerarCabecalhoHTML();
        
        // Exibir até 4 capas personalizadas
        for ($i = 1; $i <= 4; $i++) {
            $capaPath = (string)($orcamento['capa_path_' . $i] ?? '');
            if (!empty($capaPath)) {
                $html .= self::gerarCapaPersonalizada($capaPath);
            }
        }
        
        $html .= self::gerarPaginasResumo($orcamentoId, $orcamento);
        $html .= self::gerarPaginaDetalhamento($orcamentoId, $orcamento);
        $html .= self::gerarRodapeHTML();
        return $html;
    }

    public static function gerarHTMLAdmin(int $orcamentoId, array $orcamento): string
    {
        $html = self::gerarCabecalhoHTMLAdmin();
        
        // Exibir até 4 capas personalizadas
        for ($i = 1; $i <= 4; $i++) {
            $capaPath = (string)($orcamento['capa_path_' . $i] ?? '');
            if (!empty($capaPath)) {
                $html .= self::gerarCapaPersonalizada($capaPath);
            }
        }
        
        // Gerar apenas RESUMO GERAL (página 4) - não duplicar páginas 1-3
        $html .= self::gerarPaginaResumoGeral($orcamentoId, $orcamento);
        $html .= self::gerarPaginaDetalhamentoAdmin($orcamentoId, $orcamento);
        $html .= self::gerarResumoFinal($orcamentoId, $orcamento);
        $html .= self::gerarRodapeHTML();
        return $html;
    }
    
    private static function gerarPaginaResumoGeral(int $orcamentoId, array $orcamento): string
    {
        // Buscar itens agrupados por CATEGORIA
        $pdo = \App\Core\Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT codigo, descricao, grupo, categoria, (quantidade * valor_cobranca) as valor_total '
            . 'FROM orcamento_itens WHERE orcamento_id = :id '
            . 'ORDER BY CAST(SUBSTRING_INDEX(codigo, \'.\', 1) AS UNSIGNED), CAST(SUBSTRING_INDEX(codigo, \'.\', -1) AS UNSIGNED)'
        );
        $stmt->execute([':id' => $orcamentoId]);
        $itensAgrupados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Agrupar por categoria (mantém subcategorias para detalhamento)
        $categorias = [];
        $totalGeral = 0;
        
        foreach ($itensAgrupados as $item) {
            $categoria = trim((string)($item['categoria'] ?? 'SEM CATEGORIA'));
            $valor = (float)$item['valor_total'];
            
            if (!isset($categorias[$categoria])) {
                $categorias[$categoria] = [
                    'itens' => [],
                    'total' => 0
                ];
            }
            
            $categorias[$categoria]['itens'][] = $item;
            $categorias[$categoria]['total'] += $valor;
            $totalGeral += $valor;
        }
        
        // Agrupar categorias por nome principal (remover sufixos como " - MATERIAIS", " - MÃO DE OBRA", etc)
        $categoriasAgrupadas = [];
        foreach ($categorias as $categoriaNome => $categoriaData) {
            // Extrair categoria principal (antes do " - ")
            $categoriaPrincipal = $categoriaNome;
            if (strpos($categoriaNome, ' - ') !== false) {
                $categoriaPrincipal = trim(explode(' - ', $categoriaNome)[0]);
            }
            
            if (!isset($categoriasAgrupadas[$categoriaPrincipal])) {
                $categoriasAgrupadas[$categoriaPrincipal] = 0;
            }
            
            $categoriasAgrupadas[$categoriaPrincipal] += $categoriaData['total'];
        }
        
        // Gerar página de RESUMO GERAL com áreas e totais por categoria
        $html = '<div class="page">' . self::gerarHeaderPadrao($orcamento, 'PLANILHA RESUMO');
        $html .= '<div class="etapa-header">RESUMO GERAL</div>';
        
        // TABELAS DE ÁREAS
        // Processar áreas personalizadas
        $areasPersonalizadas = [];
        $areaTotal = 0;
        
        if (!empty($orcamento['areas_personalizadas'])) {
            $areasPersonalizadas = json_decode($orcamento['areas_personalizadas'], true);
            if (is_array($areasPersonalizadas)) {
                foreach ($areasPersonalizadas as $area) {
                    $m2 = (float)($area['m2'] ?? 0);
                    $fator = (float)($area['fator'] ?? 1);
                    $areaTotal += $m2 * $fator;
                }
            }
        }
        
        // Se não tiver áreas personalizadas ou área total for zero, usar área do orçamento
        if ($areaTotal == 0) {
            $areaTotal = (float)($orcamento['area_m2'] ?? 0);
        }
        
        // Tabela ÚNICA com TODAS as informações: CATEGORIA, VALOR TOTAL, % DA OBRA, M2, PREÇO/m2
        $html .= '<table class="table-resumo" style="margin-top:15px;">';
        $html .= '<thead><tr>';
        $html .= '<th class="left" style="width:30%;">CATEGORIA</th>';
        $html .= '<th class="right" style="width:20%;">VALOR TOTAL</th>';
        $html .= '<th class="center" style="width:12%;">% DA OBRA</th>';
        $html .= '<th class="center" style="width:15%;">M2</th>';
        $html .= '<th class="right" style="width:23%;">PREÇO / m2</th>';
        $html .= '</tr></thead><tbody>';
        
        foreach ($categoriasAgrupadas as $categoriaNome => $totalCategoria) {
            $pctObra = $totalGeral > 0 ? ($totalCategoria / $totalGeral) * 100 : 0;
            $precoM2 = $areaTotal > 0 ? $totalCategoria / $areaTotal : 0;
            
            $html .= sprintf(
                '<tr><td class="left">%s</td><td class="right">R$ %s</td><td class="center">%s%%</td><td class="center">%s</td><td class="right">R$ %s</td></tr>',
                htmlspecialchars(strtoupper($categoriaNome)),
                self::formatarValor($totalCategoria),
                number_format($pctObra, 2, ',', '.'),
                number_format($areaTotal, 2, ',', '.'),
                self::formatarValor($precoM2)
            );
        }
        
        $html .= sprintf(
            '<tr class="total-row"><td class="left">VALOR TOTAL GERAL:</td><td class="right">R$ %s</td><td class="center">100,00%%</td><td class="center">%s</td><td class="right">R$ %s</td></tr>',
            self::formatarValor($totalGeral),
            number_format($areaTotal, 2, ',', '.'),
            self::formatarValor($areaTotal > 0 ? $totalGeral / $areaTotal : 0)
        );
        $html .= '</tbody></table>';
        
        // Gerar tabela de áreas (separada, abaixo)
        $html .= '<table class="table-areas" style="margin-top:20px;"><thead><tr><th>ÁREAS</th><th>m2</th><th>FATOR</th><th>m2 x FATOR</th></tr></thead><tbody>';
        
        if (!empty($areasPersonalizadas)) {
            foreach ($areasPersonalizadas as $area) {
                $nome = htmlspecialchars((string)($area['nome'] ?? ''));
                $m2 = (float)($area['m2'] ?? 0);
                $fator = (float)($area['fator'] ?? 1);
                $m2xFator = $m2 * $fator;
                
                $html .= sprintf(
                    '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                    $nome,
                    number_format($m2, 2, ',', '.'),
                    number_format($fator, 2, ',', '.'),
                    number_format($m2xFator, 2, ',', '.')
                );
            }
        } else {
            // Fallback: mostrar apenas área total
            $html .= sprintf(
                '<tr><td>ÁREA TOTAL</td><td>%s</td><td>1</td><td>%s</td></tr>',
                number_format($areaTotal, 2, ',', '.'),
                number_format($areaTotal, 2, ',', '.')
            );
        }
        
        $html .= sprintf('<tr class="total-row"><td colspan="3">ÁREA TOTAL:</td><td>%s</td></tr>', number_format($areaTotal, 2, ',', '.'));
        $html .= '</tbody></table>';
        
        $html .= '<div class="page-footer"><div>FOLHA: 1</div></div>';
        $html .= '</div>';
        
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
table { page-break-inside: auto; }
tr { page-break-inside: avoid; page-break-after: auto; }
thead { display: table-header-group; }
tfoot { display: table-footer-group; }
.page { page-break-after: always; background: #FFF; padding: 20px; }
.no-page-break { page-break-after: avoid; }

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
.page-header-info { font-size: 8pt; line-height: 1.5; }
.page-header-info div { margin-bottom: 2px; }
.page-header-logo-container { text-align: center; margin: 0 0 15px 0; }
.page-header-logo { display: inline-block; padding: 10px 20px; }
.page-header-logo-text { font-size: 18pt; font-weight: bold; color: #2C3350; }
.page-header-logo-sub { font-size: 8pt; color: #666; }
.page-header-meta { text-align: right; font-size: 8pt; line-height: 1.6; }
.page-title { text-align: center; font-size: 14pt; font-weight: bold; color: #000; margin: 15px 0 5px 0; }
.page-subtitle { text-align: center; font-size: 8pt; color: #2C3350; font-weight: bold; margin-bottom: 15px; }

/* TABELAS RESUMO */
.etapa-header { background: #666; color: #FFF; padding: 8px 12px; font-weight: bold; font-size: 9pt; margin: 20px 0 0 0; text-align: center; page-break-after: avoid; }
.table-resumo { width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 8pt; margin-top: 10px; }
.table-resumo thead th { background: #666; color: #FFF; padding: 6px 8px; text-align: center; font-weight: bold; font-size: 8pt; border: 1px solid #666; }
.table-resumo thead th.left { text-align: left; }
.table-resumo tbody tr { page-break-inside: avoid; }
.table-resumo tbody tr:nth-last-child(-n+2) { page-break-after: avoid; }
.table-resumo tbody td { padding: 5px 8px; border: 1px solid #CCC; background: #FFF; }
.table-resumo tbody td.center { text-align: center; }
.table-resumo tbody td.right { text-align: right; }
.table-resumo .subtotal-row { page-break-before: avoid; page-break-inside: avoid; }
.table-resumo .subtotal-row td { background: #2C3350 !important; color: #FFF; font-weight: bold; padding: 8px; border: 1px solid #2C3350; }
.table-resumo .total-row { page-break-before: avoid; page-break-inside: avoid; }
.table-resumo .total-row td { background: #000 !important; color: #FFF; font-weight: bold; padding: 10px 8px; font-size: 9pt; border: 1px solid #000; }

/* TABELAS ÁREAS */
.table-areas { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 8pt; margin-top: 20px; }
.table-areas thead th { background: #666; color: #FFF; padding: 6px 8px; font-weight: bold; text-align: center; font-size: 8pt; border: 1px solid #666; }
.table-areas tbody td { padding: 5px 8px; border: 1px solid #CCC; text-align: center; background: #FFF; }
.table-areas .total-row td { background: #666; color: #FFF; font-weight: bold; padding: 8px; border: 1px solid #666; }

/* TABELAS DETALHAMENTO */
.banner-etapa { background: #2C3350; color: #FFF; padding: 8px 12px; font-weight: bold; font-size: 9pt; margin: 40px 0 0 0; text-align: center; page-break-after: avoid; }
.banner-etapa:first-of-type { margin-top: 20px; }
.table-detalhes { width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 7pt; margin-top: 10px; }
.table-detalhes thead { display: table-header-group; }
.table-detalhes thead th { background: #666; color: #FFF; padding: 5px 4px; text-align: center; font-weight: bold; font-size: 7pt; border: 1px solid #666; }
.table-detalhes thead th.left { text-align: left; padding-left: 6px; }
.table-detalhes thead th.right { text-align: right; padding-right: 6px; }
.table-detalhes tbody td { padding: 4px; border: 1px solid #CCC; vertical-align: middle; background: #FFF; font-size: 7pt; }
.table-detalhes tbody td.center { text-align: center; }
.table-detalhes tbody td.right { text-align: right; padding-right: 6px; }
.table-detalhes tbody td.left { text-align: left; padding-left: 6px; }
.table-detalhes tbody tr { page-break-inside: avoid; }
.table-detalhes tbody tr:nth-last-child(-n+3) { page-break-after: avoid; }
.subtotal-item { background: #2C3350; color: #FFF; padding: 6px 8px; font-weight: bold; font-size: 8pt; text-align: right; margin-top: 10px; page-break-inside: avoid; page-break-before: avoid; }
.subtotal-etapa { background: #666; color: #FFF; padding: 8px 12px; font-weight: bold; font-size: 8pt; text-align: right; margin: 10px 0 0 0; page-break-inside: avoid; page-break-before: avoid; }
.total-obra { background: #000; color: #FFF; padding: 10px 12px; font-weight: bold; font-size: 9pt; text-align: right; margin: 20px 0 10px 0; page-break-inside: avoid; page-break-before: avoid; }

/* RODAPÉ */
.page-footer { margin-top: 20px; padding-top: 8px; border-top: 1px solid #2C3350; font-size: 7pt; color: #999; display: flex; justify-content: flex-end; }
</style>
</head>
<body>
HTML;
    }

    private static function gerarCapaPersonalizada(string $capaPath): string
    {
        // Converter caminho relativo para absoluto
        $absolutePath = __DIR__ . '/../../' . ltrim($capaPath, '/');
        
        if (!file_exists($absolutePath)) {
            return '';
        }
        
        // Converter imagem para base64
        $imageData = base64_encode(file_get_contents($absolutePath));
        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $mimeType = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
        $base64Src = 'data:' . $mimeType . ';base64,' . $imageData;
        
        return <<<HTML
<div class="page" style="padding:0;margin:0;display:flex;align-items:center;justify-content:center;">
    <img src="{$base64Src}" style="width:100%;height:100%;object-fit:contain;" alt="Capa">
</div>
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
    <div class="page-header-logo-container">
        {$logoHtml}
    </div>
    <div class="page-title">{$tituloSecao}</div>
    <div class="page-subtitle">ETAPA CINZA (BRUTA) + ACABAMENTOS | ADMINISTRAÇÃO</div>
    <div style="display:flex;justify-content:space-between;margin-top:15px;font-size:8pt;">
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
    </div>
</div>
HTML;
    }

    private static function gerarPaginasResumo(int $orcamentoId, array $orcamento): string
    {
        // Buscar itens agrupados por CATEGORIA (não por etapa hardcoded)
        $pdo = \App\Core\Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT codigo, descricao, grupo, categoria, (quantidade * valor_cobranca) as valor_total '
            . 'FROM orcamento_itens WHERE orcamento_id = :id '
            . 'ORDER BY CAST(SUBSTRING_INDEX(codigo, \'.\', 1) AS UNSIGNED), CAST(SUBSTRING_INDEX(codigo, \'.\', -1) AS UNSIGNED)'
        );
        $stmt->execute([':id' => $orcamentoId]);
        $itensAgrupados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Agrupar por categoria
        $categorias = [];
        $totalGeral = 0;
        
        foreach ($itensAgrupados as $item) {
            $categoria = trim((string)($item['categoria'] ?? 'SEM CATEGORIA'));
            $valor = (float)$item['valor_total'];
            
            if (!isset($categorias[$categoria])) {
                $categorias[$categoria] = [
                    'itens' => [],
                    'total' => 0
                ];
            }
            
            $categorias[$categoria]['itens'][] = $item;
            $categorias[$categoria]['total'] += $valor;
            $totalGeral += $valor;
        }
        
        // Se não houver categorias, retornar vazio
        if (empty($categorias)) {
            return '';
        }
        
        // Gerar páginas de resumo por categoria
        $html = '';
        $paginaNum = 1;
        
        foreach ($categorias as $categoriaNome => $categoriaData) {
            $html .= '<div class="page">' . self::gerarHeaderPadrao($orcamento, 'PLANILHA RESUMO');
            $html .= '<div class="etapa-header">' . htmlspecialchars(strtoupper($categoriaNome)) . '</div>';
            $html .= '<table class="table-resumo"><thead><tr>';
            $html .= '<th style="width:8%;">Nº</th><th style="width:60%;">DESCRIÇÃO</th>';
            $html .= '<th class="right" style="width:22%;">VALOR TOTAL</th><th class="center" style="width:10%;">%</th>';
            $html .= '</tr></thead><tbody>';
            
            foreach ($categoriaData['itens'] as $item) {
                $pct = $totalGeral > 0 ? ((float)$item['valor_total'] / $totalGeral) * 100 : 0;
                $html .= sprintf(
                    '<tr><td>%s</td><td>%s</td><td class="right">R$ %s</td><td class="center">%s%%</td></tr>',
                    htmlspecialchars((string)$item['codigo']),
                    htmlspecialchars((string)$item['descricao']),
                    self::formatarValor((float)$item['valor_total']),
                    number_format($pct, 2, ',', '.')
                );
            }
            
            $pctCategoria = $totalGeral > 0 ? ($categoriaData['total'] / $totalGeral) * 100 : 0;
            $html .= sprintf(
                '<tr class="subtotal-row"><td colspan="2">SUBTOTAL - %s</td><td class="right">R$ %s</td><td class="center">%s%%</td></tr>',
                htmlspecialchars(strtoupper($categoriaNome)),
                self::formatarValor($categoriaData['total']),
                number_format($pctCategoria, 2, ',', '.')
            );
            
            $html .= '</tbody></table>';
            $html .= sprintf('<div class="page-footer"><div>FOLHA: %d</div></div>', $paginaNum);
            $html .= '</div>';
            
            $paginaNum++;
        }
        
        // Página final com áreas e totais por categoria
        $html .= '<div class="page">' . self::gerarHeaderPadrao($orcamento, 'PLANILHA RESUMO');
        $html .= '<div class="etapa-header">RESUMO GERAL</div>';
        
        // Tabela de total geral
        $html .= '<table class="table-resumo" style="margin-top:15px;"><tbody>';
        $html .= sprintf(
            '<tr class="total-row"><td colspan="2">VALOR TOTAL GERAL:</td><td class="right">R$ %s</td><td class="center">100,00%%</td></tr>',
            self::formatarValor($totalGeral)
        );
        $html .= '</tbody></table>';

        // TABELAS DE ÁREAS
        // Processar áreas personalizadas
        $areasPersonalizadas = [];
        $areaTotal = 0;
        
        if (!empty($orcamento['areas_personalizadas'])) {
            $areasPersonalizadas = json_decode($orcamento['areas_personalizadas'], true);
            if (is_array($areasPersonalizadas)) {
                foreach ($areasPersonalizadas as $area) {
                    $m2 = (float)($area['m2'] ?? 0);
                    $fator = (float)($area['fator'] ?? 1);
                    $areaTotal += $m2 * $fator;
                }
            }
        }
        
        // Se não tiver áreas personalizadas ou área total for zero, usar área do orçamento
        if ($areaTotal == 0) {
            $areaTotal = (float)($orcamento['area_m2'] ?? 0);
        }
        
        // Gerar tabela de áreas
        $html .= '<table class="table-areas" style="margin-top:20px;"><thead><tr><th>ÁREAS</th><th>m2</th><th>FATOR</th><th>m2 x FATOR</th></tr></thead><tbody>';
        
        if (!empty($areasPersonalizadas)) {
            foreach ($areasPersonalizadas as $area) {
                $nome = htmlspecialchars((string)($area['nome'] ?? ''));
                $m2 = (float)($area['m2'] ?? 0);
                $fator = (float)($area['fator'] ?? 1);
                $m2xFator = $m2 * $fator;
                
                $html .= sprintf(
                    '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                    $nome,
                    number_format($m2, 2, ',', '.'),
                    number_format($fator, 2, ',', '.'),
                    number_format($m2xFator, 2, ',', '.')
                );
            }
        } else {
            // Fallback: mostrar apenas área total
            $html .= sprintf(
                '<tr><td>ÁREA TOTAL</td><td>%s</td><td>1</td><td>%s</td></tr>',
                number_format($areaTotal, 2, ',', '.'),
                number_format($areaTotal, 2, ',', '.')
            );
        }
        
        $html .= sprintf('<tr class="total-row"><td colspan="3">ÁREA TOTAL:</td><td>%s</td></tr>', number_format($areaTotal, 2, ',', '.'));
        $html .= '</tbody></table>';
        
        // Gerar tabela de CATEGORIAS (usando dados reais do orçamento)
        $html .= '<table class="table-areas" style="margin-top:15px;"><thead><tr><th>CATEGORIAS</th><th>PREÇO</th><th>M2</th><th>PREÇO / m2</th></tr></thead><tbody>';
        
        foreach ($categorias as $categoriaNome => $categoriaData) {
            $html .= sprintf(
                '<tr><td>%s</td><td>R$ %s</td><td>%s</td><td>R$ %s</td></tr>',
                htmlspecialchars(strtoupper($categoriaNome)),
                self::formatarValor($categoriaData['total']),
                number_format($areaTotal, 2, ',', '.'),
                self::formatarValor($areaTotal > 0 ? $categoriaData['total'] / $areaTotal : 0)
            );
        }
        
        $html .= sprintf(
            '<tr class="total-row"><td>TOTAL GERAL:</td><td>R$ %s</td><td>%s</td><td>R$ %s</td></tr>',
            self::formatarValor($totalGeral),
            number_format($areaTotal, 2, ',', '.'),
            self::formatarValor($areaTotal > 0 ? $totalGeral / $areaTotal : 0)
        );
        $html .= '</tbody></table>';
        
        $html .= sprintf('<div class="page-footer"><div>FOLHA: %d</div></div>', $paginaNum);
        $html .= '</div>';
        
        return $html;
    }

    
    private static function resolverGrupoEtapa(array $item): string
    {
        // Usar o campo etapa do item ao invés de lógica hardcoded
        $etapa = strtoupper(trim((string)($item['etapa'] ?? '')));
        
        // Mapear etapas para grupos do PDF
        if (empty($etapa) || $etapa === 'SEM ETAPA') {
            // Fallback para lógica antiga se não tiver etapa definida
            $numero = (int)explode('.', trim((string)$item['codigo']))[0];
            if ($numero >= 1 && $numero <= 17) return 'cinza';
            if ($numero >= 18 && $numero <= 41) return 'acabamentos';
            if ($numero === 42) return 'gerenciamento';
            return 'adm_impostos';
        }
        
        // Mapear nomes de etapas para grupos
        if (strpos($etapa, 'CINZA') !== false || strpos($etapa, 'BRUTA') !== false) {
            return 'cinza';
        }
        if (strpos($etapa, 'ACABAMENTO') !== false) {
            return 'acabamentos';
        }
        if (strpos($etapa, 'GERENCIAMENTO') !== false) {
            return 'gerenciamento';
        }
        if (strpos($etapa, 'ADMINISTRA') !== false || strpos($etapa, 'IMPOSTO') !== false || strpos($etapa, 'INDIRETO') !== false) {
            return 'adm_impostos';
        }
        
        // Fallback: usar lógica antiga baseada no código
        $numero = (int)explode('.', trim((string)$item['codigo']))[0];
        if ($numero >= 1 && $numero <= 17) return 'cinza';
        if ($numero >= 18 && $numero <= 41) return 'acabamentos';
        if ($numero === 42) return 'gerenciamento';
        return 'adm_impostos';
    }
    
    private static function gerarPaginaDetalhamento(int $orcamentoId, array $orcamento): string
    {
        $pdo = \App\Core\Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, codigo, descricao, quantidade, unidade, valor_unitario, valor_cobranca, percentual_realizado, custo_material, custo_mao_obra, categoria '
            . 'FROM orcamento_itens WHERE orcamento_id = :id '
            . 'ORDER BY CAST(SUBSTRING_INDEX(codigo, \'.\', 1) AS UNSIGNED), CAST(SUBSTRING_INDEX(codigo, \'.\', -1) AS UNSIGNED), id'
        );
        $stmt->execute([':id' => $orcamentoId]);
        $todosItens = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Agrupar por CATEGORIA
        $categorias = [];
        $totalGeral = 0.0;
        
        foreach ($todosItens as $item) {
            $categoria = (string)($item['categoria'] ?? 'SEM CATEGORIA');
            if (!isset($categorias[$categoria])) {
                $categorias[$categoria] = [
                    'label' => strtoupper($categoria),
                    'itens' => [],
                    'subtotal' => 0.0
                ];
            }
            $valorTotal = (float)$item['quantidade'] * (float)$item['valor_cobranca'];
            $categorias[$categoria]['itens'][] = $item;
            $categorias[$categoria]['subtotal'] += $valorTotal;
            $totalGeral += $valorTotal;
        }
        
        $html = '<div class="no-page-break">' . self::gerarHeaderPadrao($orcamento, 'PLANILHA ORÇAMENTÁRIA') . '</div>';
        
        foreach ($categorias as $categoria) {
            if (empty($categoria['itens'])) continue;
            $subtotal = $categoria['subtotal'];
            
            $html .= sprintf('<div class="banner-etapa">%s</div>', htmlspecialchars((string)$categoria['label']));
            $html .= self::gerarTabelaDetalhes($categoria['itens'], $subtotal, $totalGeral);
            $html .= sprintf(
                '<div class="subtotal-etapa">SUBTOTAL — %s: R$ %s</div>',
                htmlspecialchars((string)$categoria['label']),
                self::formatarValor($subtotal)
            );
        }
        
        $html .= sprintf(
            '<div class="total-obra">VALOR TOTAL DE OBRA: R$ %s</div>',
            self::formatarValor($totalGeral)
        );
        
        $html .= '<div class="page-footer"><div>FOLHA: 4</div></div>';
        
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
            $valorCobrancaUnitario = (float)$item['valor_cobranca'];
            $valorTotal = $quantidade * $valorCobrancaUnitario;
            $custoMaterial = (float)($item['custo_material'] ?? 0);
            $custoMaoObra = (float)($item['custo_mao_obra'] ?? 0);
            
            $valorUnitMaterial = $quantidade > 0 ? $custoMaterial / $quantidade : 0;
            $valorUnitMaoObra = $quantidade > 0 ? $custoMaoObra / $quantidade : 0;
            $valorUnitTotal = $valorCobrancaUnitario;
            
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

    // ══════════════════════════════════════════════
    //  MÉTODOS PARA PDF ADMINISTRATIVO
    // ══════════════════════════════════════════════

    private static function gerarCabecalhoHTMLAdmin(): string
    {
        $baseCSS = self::gerarCabecalhoHTML();
        // Adicionar CSS específico para colunas administrativas
        $adminCSS = <<<'CSS'
<style>
.table-detalhes-admin thead th { font-size: 6pt; padding: 4px 2px; }
.table-detalhes-admin tbody td { font-size: 6pt; padding: 3px 2px; }
.col-custo { background: #FFF8DC !important; }
.col-bdi { background: #E6F3FF !important; }
.col-margem { background: #E8F5E9 !important; }
</style>
</head>
<body>
CSS;
        return str_replace('</head><body>', $adminCSS, $baseCSS);
    }

    private static function gerarPaginaDetalhamentoAdmin(int $orcamentoId, array $orcamento): string
    {
        // Buscar margens globais do orçamento
        $margemMaoObraGlobal = (float)($orcamento['margem_mao_obra'] ?? 0);
        $margemMateriaisGlobal = (float)($orcamento['margem_materiais'] ?? 0);
        $margemEquipamentosGlobal = (float)($orcamento['margem_equipamentos'] ?? 20);
        
        $itens = \App\Models\OrcamentoItem::allByOrcamento($orcamentoId);
        
        // Agrupar por CATEGORIA (não por etapa)
        $grouped = [];
        foreach ($itens as $item) {
            $categoria = (string)($item['categoria'] ?? 'SEM CATEGORIA');
            $grouped[$categoria][] = $item;
        }

        $html = '<div class="page"><div class="page-title">DETALHAMENTO ADMINISTRATIVO</div>';
        $html .= '<div class="page-subtitle">Custos, Margens e BDI por Item</div>';

        $totalGeralObra = 0.0;
        $lucroGeralObra = 0.0;

        foreach ($grouped as $categoria => $itensCategoria) {
            $html .= '<div class="banner-etapa">' . htmlspecialchars(strtoupper($categoria)) . '</div>';
            
            $subtotalCategoria = 0.0;
            $lucroCategoria = 0.0;
            
            $html .= '<table class="table-detalhes table-detalhes-admin">';
            $html .= '<thead><tr>';
            $html .= '<th class="left" style="width:6%;">Cód.</th>';
            $html .= '<th class="left" style="width:20%;">Descrição</th>';
            $html .= '<th class="center" style="width:4%;">Un</th>';
            $html .= '<th class="center" style="width:5%;">Qtd</th>';
            $html .= '<th class="right col-custo" style="width:7%;">Custo Mat.</th>';
            $html .= '<th class="right col-custo" style="width:7%;">Custo M.O.</th>';
            $html .= '<th class="right col-custo" style="width:7%;">Custo Equip.</th>';
            $html .= '<th class="right col-bdi" style="width:5%;">% BDI</th>';
            $html .= '<th class="right col-margem" style="width:7%;">Margem Un.</th>';
            $html .= '<th class="right" style="width:8%;">Vlr Unit.</th>';
            $html .= '<th class="right col-lucro" style="width:10%;">Lucro Total</th>';
            $html .= '<th class="right" style="width:14%;">Vlr Total</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($itensCategoria as $item) {
                    $quantidade = (float)($item['quantidade'] ?? 0);
                    $custoMaterialTotal = (float)($item['custo_material'] ?? 0);
                    $custoMaoObraTotal = (float)($item['custo_mao_obra'] ?? 0);
                    $custoEquipamentoTotal = (float)($item['custo_equipamento'] ?? 0);
                    $margemPersonalizada = (float)($item['margem_personalizada'] ?? 0);
                    $usaMargemPersonalizada = (int)($item['usa_margem_personalizada'] ?? 0);
                    $valorUnitario = (float)($item['valor_unitario'] ?? 0);
                    $valorCobrancaUnitario = (float)($item['valor_cobranca'] ?? 0);
                    
                    // Calcular valores unitários
                    // IMPORTANTE: custos podem estar salvos como UNITÁRIOS ou TOTAIS
                    // Para detectar, verificamos se custo ≈ valor_unitario (indica que é unitário)
                    
                    $custoMaterialUnit = 0;
                    $custoMaoObraUnit = 0;
                    $custoEquipamentoUnit = 0;
                    
                    if ($custoMaterialTotal > 0 && $quantidade > 0) {
                        // Se custo é próximo do valor_unitario, provavelmente já é unitário
                        if (abs($custoMaterialTotal - $valorUnitario) < 0.01) {
                            $custoMaterialUnit = $custoMaterialTotal;
                        } else {
                            // Custo é total, dividir pela quantidade
                            $custoMaterialUnit = $custoMaterialTotal / $quantidade;
                        }
                    }
                    
                    if ($custoMaoObraTotal > 0 && $quantidade > 0) {
                        // Se custo é próximo do valor_unitario, provavelmente já é unitário
                        if (abs($custoMaoObraTotal - $valorUnitario) < 0.01) {
                            $custoMaoObraUnit = $custoMaoObraTotal;
                        } else {
                            // Custo é total, dividir pela quantidade
                            $custoMaoObraUnit = $custoMaoObraTotal / $quantidade;
                        }
                    }
                    
                    if ($custoEquipamentoTotal > 0 && $quantidade > 0) {
                        // Se custo é próximo do valor_unitario, provavelmente já é unitário
                        if (abs($custoEquipamentoTotal - $valorUnitario) < 0.01) {
                            $custoEquipamentoUnit = $custoEquipamentoTotal;
                        } else {
                            // Custo é total, dividir pela quantidade
                            $custoEquipamentoUnit = $custoEquipamentoTotal / $quantidade;
                        }
                    }
                    
                    $custoUnitTotal = $custoMaterialUnit + $custoMaoObraUnit + $custoEquipamentoUnit;
                    
                    // Se não há custo detalhado, usar valor_unitario como base de custo
                    $custoBase = $custoUnitTotal > 0 ? $custoUnitTotal : $valorUnitario;
                    
                    $margemUnit = $valorCobrancaUnitario - $custoBase;
                    $valorTotal = $quantidade * $valorCobrancaUnitario;
                    
                    // Calcular % de margem aplicada
                    $percentualMargemAplicada = 0;
                    
                    // PRIORIDADE 1: Se usa margem personalizada, mostrar ela diretamente
                    if ($usaMargemPersonalizada && $margemPersonalizada > 0) {
                        $percentualMargemAplicada = $margemPersonalizada;
                    }
                    // PRIORIDADE 2: Se usa margem global, usar classificacao_custo do item
                    elseif (!$usaMargemPersonalizada) {
                        $classificacaoCusto = (string)($item['classificacao_custo'] ?? '');
                        if ($classificacaoCusto === 'mao_obra') {
                            $percentualMargemAplicada = $margemMaoObraGlobal;
                        } elseif ($classificacaoCusto === 'equipamento') {
                            $percentualMargemAplicada = $margemEquipamentosGlobal;
                        } elseif ($classificacaoCusto === 'material') {
                            $percentualMargemAplicada = $margemMateriaisGlobal;
                        } else {
                            // Se não tem classificacao_custo, usar 0
                            $percentualMargemAplicada = 0;
                        }
                    }
                    // PRIORIDADE 3: Calcular baseado em custo vs valor de venda (fallback)
                    elseif ($custoBase > 0.01 && $valorCobrancaUnitario > $custoBase) {
                        $percentualMargemAplicada = (($valorCobrancaUnitario - $custoBase) / $custoBase) * 100;
                        // Limitar a 999% para evitar valores absurdos
                        if ($percentualMargemAplicada > 999) {
                            $percentualMargemAplicada = 0;
                        }
                    }
                    
                    $subtotalCategoria += $valorTotal;
                    
                    // Calcular lucro total (margem unitária × quantidade)
                    $lucroTotal = $margemUnit * $quantidade;
                    $lucroCategoria += $lucroTotal;
                    
                    $html .= '<tr>';
                    $html .= '<td class="left">' . htmlspecialchars((string)$item['codigo']) . '</td>';
                    $html .= '<td class="left">' . nl2br(htmlspecialchars((string)$item['descricao'])) . '</td>';
                    $html .= '<td class="center">' . htmlspecialchars((string)$item['unidade']) . '</td>';
                    $html .= '<td class="center">' . number_format($quantidade, 2, ',', '.') . '</td>';
                    $html .= '<td class="right col-custo">R$ ' . self::formatarValor($custoMaterialUnit) . '</td>';
                    $html .= '<td class="right col-custo">R$ ' . self::formatarValor($custoMaoObraUnit) . '</td>';
                    $html .= '<td class="right col-custo">R$ ' . self::formatarValor($custoEquipamentoUnit) . '</td>';
                    $html .= '<td class="center col-bdi">' . number_format($percentualMargemAplicada, 1, ',', '.') . '%</td>';
                    $html .= '<td class="right col-margem">R$ ' . self::formatarValor($margemUnit) . '</td>';
                    $html .= '<td class="right">R$ ' . self::formatarValor($valorCobrancaUnitario) . '</td>';
                    $html .= '<td class="right col-lucro">R$ ' . self::formatarValor($lucroTotal) . '</td>';
                    $html .= '<td class="right">R$ ' . self::formatarValor($valorTotal) . '</td>';
                    $html .= '</tr>';
                }

                $html .= '</tbody></table>';
                $html .= '<div class="subtotal-item">Subtotal ' . htmlspecialchars(strtoupper($categoria)) . ': R$ ' . self::formatarValor($subtotalCategoria) . ' | Lucro: R$ ' . self::formatarValor($lucroCategoria) . '</div>';
                
                $totalGeralObra += $subtotalCategoria;
                $lucroGeralObra += $lucroCategoria;
        }

        $html .= '<div class="total-obra">TOTAL DA OBRA: R$ ' . self::formatarValor($totalGeralObra) . ' | LUCRO TOTAL: R$ ' . self::formatarValor($lucroGeralObra) . '</div>';
        $html .= '</div>';

        return $html;
    }

    private static function gerarResumoFinal(int $orcamentoId, array $orcamento): string
    {
        $itens = \App\Models\OrcamentoItem::allByOrcamento($orcamentoId);
        
        $totalMateriais = 0.0;
        $totalMaoObra = 0.0;
        $totalEquipamentos = 0.0;
        $totalCobranca = 0.0;
        
        foreach ($itens as $item) {
            $quantidade = (float)($item['quantidade'] ?? 0);
            $custoMaterial = (float)($item['custo_material'] ?? 0);
            $custoMaoObra = (float)($item['custo_mao_obra'] ?? 0);
            $valorCobranca = (float)($item['valor_cobranca'] ?? 0);
            $categoria = (string)($item['categoria'] ?? '');
            
            $totalCobranca += $quantidade * $valorCobranca;
            
            if (stripos($categoria, 'EQUIPAMENTO') !== false) {
                $totalEquipamentos += $quantidade * $custoMaterial;
            } else {
                $totalMateriais += $quantidade * $custoMaterial;
                $totalMaoObra += $quantidade * $custoMaoObra;
            }
        }
        
        $subtotal = $totalCobranca;
        $percentualCustosAdm = (float)($orcamento['percentual_custos_adm'] ?? 0);
        $percentualImpostos = (float)($orcamento['percentual_impostos'] ?? 0);
        
        $valorCustosAdm = $subtotal * ($percentualCustosAdm / 100);
        $valorImpostos = $subtotal * ($percentualImpostos / 100);
        $totalFinal = $subtotal + $valorCustosAdm + $valorImpostos;
        
        $html = '<div class="page" style="page-break-before: always;">';
        $html .= '<div class="page-title">RESUMO DE CUSTOS</div>';
        $html .= '<div class="page-subtitle">Análise Financeira Detalhada</div>';
        
        $html .= '<table class="table-resumo" style="margin-top:30px;">';
        $html .= '<thead><tr>';
        $html .= '<th class="left">Descrição</th>';
        $html .= '<th class="right" style="width:20%;">Valor (R$)</th>';
        $html .= '<th class="center" style="width:15%;">% Obra</th>';
        $html .= '</tr></thead><tbody>';
        
        $html .= '<tr>';
        $html .= '<td class="left">🧱 Total em Materiais</td>';
        $html .= '<td class="right">R$ ' . self::formatarValor($totalMateriais) . '</td>';
        $html .= '<td class="center">' . number_format($subtotal > 0 ? ($totalMateriais/$subtotal)*100 : 0, 2, ',', '.') . '%</td>';
        $html .= '</tr>';
        
        $html .= '<tr>';
        $html .= '<td class="left">👷 Total em Mão de Obra</td>';
        $html .= '<td class="right">R$ ' . self::formatarValor($totalMaoObra) . '</td>';
        $html .= '<td class="center">' . number_format($subtotal > 0 ? ($totalMaoObra/$subtotal)*100 : 0, 2, ',', '.') . '%</td>';
        $html .= '</tr>';
        
        $html .= '<tr>';
        $html .= '<td class="left">⚙️ Total em Equipamentos</td>';
        $html .= '<td class="right">R$ ' . self::formatarValor($totalEquipamentos) . '</td>';
        $html .= '<td class="center">' . number_format($subtotal > 0 ? ($totalEquipamentos/$subtotal)*100 : 0, 2, ',', '.') . '%</td>';
        $html .= '</tr>';
        
        $html .= '<tr class="subtotal-row">';
        $html .= '<td class="left">SUBTOTAL DA OBRA</td>';
        $html .= '<td class="right">R$ ' . self::formatarValor($subtotal) . '</td>';
        $html .= '<td class="center">100,00%</td>';
        $html .= '</tr>';
        
        if ($percentualCustosAdm > 0) {
            $html .= '<tr>';
            $html .= '<td class="left">Custos Administrativos (' . number_format($percentualCustosAdm, 2, ',', '.') . '%)</td>';
            $html .= '<td class="right">R$ ' . self::formatarValor($valorCustosAdm) . '</td>';
            $html .= '<td class="center">—</td>';
            $html .= '</tr>';
        }
        
        if ($percentualImpostos > 0) {
            $html .= '<tr>';
            $html .= '<td class="left">Impostos (' . number_format($percentualImpostos, 2, ',', '.') . '%)</td>';
            $html .= '<td class="right">R$ ' . self::formatarValor($valorImpostos) . '</td>';
            $html .= '<td class="center">—</td>';
            $html .= '</tr>';
        }
        
        $html .= '<tr class="total-row">';
        $html .= '<td class="left">TOTAL GERAL</td>';
        $html .= '<td class="right">R$ ' . self::formatarValor($totalFinal) . '</td>';
        $html .= '<td class="center">—</td>';
        $html .= '</tr>';
        
        $html .= '</tbody></table>';
        
        $html .= '<div style="margin-top:30px; padding:15px; background:#F5F5F5; border-left:4px solid #C9973A; font-size:8pt;">';
        $html .= '<strong>Observações:</strong><br>';
        $html .= '• Valores incluem BDI (Benefícios e Despesas Indiretas) conforme especificado por item<br>';
        $html .= '• Custos administrativos e impostos calculados sobre o subtotal da obra<br>';
        $html .= '• Este documento é confidencial e destinado apenas para uso administrativo interno';
        $html .= '</div>';
        
        $html .= '</div>';
        return $html;
    }
}
