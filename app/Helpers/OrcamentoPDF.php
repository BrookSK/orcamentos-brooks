<?php

declare(strict_types=1);

namespace App\Helpers;

final class OrcamentoPDF
{
    /**
     * Calcula áreas do orçamento separando terreno, construída térrea e construída superior
     * @return array ['terreno' => float, 'terrea' => float, 'superior' => float, 'total' => float, 'areas' => array]
     */
    private static function calcularAreas(array $orcamento): array
    {
        $areasPersonalizadas = [];
        $areaTerrea = 0;      // Áreas construídas térreo/pavimento superior
        $areaSuperior = 0;    // Áreas construídas superiores
        
        // ÁREA TOTAL DO TERRENO sempre vem do campo area_m2 do cabeçalho
        $areaTerreno = (float)($orcamento['area_m2'] ?? 0);
        
        if (!empty($orcamento['areas_personalizadas'])) {
            $areasPersonalizadas = json_decode($orcamento['areas_personalizadas'], true);
            if (is_array($areasPersonalizadas)) {
                foreach ($areasPersonalizadas as $area) {
                    $m2 = (float)($area['m2'] ?? 0);
                    $fator = (float)($area['fator'] ?? 1);
                    $tipoArea = (string)($area['tipo_area'] ?? 'terreno');
                    $m2xFator = $m2 * $fator;
                    
                    // Não somar áreas marcadas como "nao_somar"
                    if ($tipoArea === 'nao_somar') {
                        continue;
                    }
                    
                    // Somar apenas áreas construídas (térrea e superior)
                    if ($tipoArea === 'terrea') {
                        $areaTerrea += $m2xFator;
                    } elseif ($tipoArea === 'superior') {
                        $areaSuperior += $m2xFator;
                    }
                    // Áreas tipo 'terreno' não somam (já estão no area_m2 do cabeçalho)
                }
            }
        }
        
        // Área total construída = térrea + superior (não incluir terreno do cabeçalho)
        $areaTotal = $areaTerrea + $areaSuperior;
        
        return [
            'terreno' => $areaTerreno,
            'terrea' => $areaTerrea,
            'superior' => $areaSuperior,
            'total' => $areaTotal,
            'areas' => $areasPersonalizadas
        ];
    }
    
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
        
        $html .= self::gerarPaginaResumoPorFase($orcamentoId, $orcamento);
        $html .= self::gerarPaginaDetalhamento($orcamentoId, $orcamento);
        $html .= self::gerarResumoFinal($orcamentoId, $orcamento); // Adicionar página de resumo final com impostos
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
        $dadosAreas = self::calcularAreas($orcamento);
        $areaTerreno = $dadosAreas['terreno'];
        $areaTerrea = $dadosAreas['terrea'];
        $areaSuperior = $dadosAreas['superior'];
        $areaTotal = $dadosAreas['total'];
        $areasPersonalizadas = $dadosAreas['areas'];
        
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
        
        // Gerar tabela de áreas (separada, abaixo) - wrapper para evitar quebra de página
        $html .= '<div style="page-break-inside: avoid;">';
        $html .= '<table class="table-areas" style="margin-top:20px;"><thead><tr><th>ÁREAS</th><th>m2</th><th>FATOR</th><th>m2 x FATOR</th></tr></thead><tbody>';
        
        if (!empty($areasPersonalizadas)) {
            foreach ($areasPersonalizadas as $area) {
                $nome = htmlspecialchars((string)($area['nome'] ?? ''));
                $m2 = (float)($area['m2'] ?? 0);
                $fator = (float)($area['fator'] ?? 1);
                $tipoArea = (string)($area['tipo_area'] ?? 'terreno');
                $m2xFator = $m2 * $fator;
                
                // Adicionar indicador conforme tipo de área
                $nomeExibicao = $nome;
                if ($tipoArea === 'terrea') {
                    $nomeExibicao = $nome . ' *';
                } elseif ($tipoArea === 'superior') {

                    $nomeExibicao = $nome . ' **';

                } elseif ($tipoArea === 'nao_somar') {

                    $nomeExibicao = $nome . '<br><span style="font-size:8px;">Não utilizado para cálculo</span>';

                }
                
                $html .= sprintf(
                    '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                    $nomeExibicao,
                    number_format($m2, 2, ',', '.'),
                    number_format($fator, 2, ',', '.'),
                    number_format($m2xFator, 2, ',', '.')
                );
            }
            
            // Linha de subtotal do terreno
            $html .= sprintf(
                '<tr style="background:#e0e0e0;"><td colspan="3"><strong>ÁREA TOTAL DO TERRENO:</strong></td><td><strong>%s</strong></td></tr>',
                number_format($areaTerreno, 2, ',', '.')
            );
            
            // Linha de área construída térrea (se houver)
            if ($areaTerrea > 0) {
                $html .= sprintf(
                    '<tr style="background:#d0d0d0;"><td colspan="3"><strong>ÁREA CONSTRUÍDA TÉRREA (*):</strong></td><td><strong>%s</strong></td></tr>',
                    number_format($areaTerrea, 2, ',', '.')
                );
            }
            
            // Linha de área construída superiores (se houver)
            if ($areaSuperior > 0) {
                $html .= sprintf(
                    '<tr style="background:#c0c0c0;"><td colspan="3"><strong>ÁREA CONSTRUÍDA SUPERIORES (**):</strong></td><td><strong>%s</strong></td></tr>',
                    number_format($areaSuperior, 2, ',', '.')
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
        
        $html .= sprintf('<tr class="total-row"><td colspan="3">ÁREA TOTAL CONSTRUÍDA:</td><td>%s</td></tr>', number_format($areaTotal, 2, ',', '.'));
        
        // Adicionar nota explicativa
        if ($areaTerrea > 0 || $areaSuperior > 0) {
            $html .= '<tr><td colspan="4" style="font-size:9px;color:#666;padding:8px;text-align:left;">';
            if ($areaTerrea > 0) {
                $html .= '(*) Área construída térrea - pavimentos superiores<br>';
            }
            if ($areaSuperior > 0) {
                $html .= '(**) Área construída superiores';
            }
            $html .= '</td></tr>';
        }
        
        $html .= '</tbody></table>';
        $html .= '</div>'; // Fecha wrapper page-break-inside: avoid
        
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
.page { page-break-after: always; background: #FFF; padding: 10mm; }
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
.table-resumo thead th { background: #666; color: #FFF; padding: 8px; text-align: center; font-weight: bold; font-size: 8pt; border: 1px solid #666; vertical-align: middle; height: 35px; line-height: 1.2; }
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
.table-areas thead th { background: #666; color: #FFF; padding: 8px; font-weight: bold; text-align: center; font-size: 8pt; border: 1px solid #666; vertical-align: middle; height: 35px; line-height: 1.2; }
.table-areas tbody td { padding: 5px 8px; border: 1px solid #CCC; text-align: center; background: #FFF; }
.table-areas .total-row td { background: #666; color: #FFF; font-weight: bold; padding: 8px; border: 1px solid #666; }

/* TABELAS DETALHAMENTO */
.banner-etapa { background: #2C3350; color: #FFF; padding: 8px 12px; font-weight: bold; font-size: 9pt; margin: 40px 0 0 0; text-align: center; page-break-after: avoid; }
.banner-etapa:first-of-type { margin-top: 20px; }
.table-detalhes { width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 7pt; margin-top: 10px; }
.table-detalhes thead { display: table-header-group; }
.table-detalhes thead th { background: #666; color: #FFF; padding: 8px 4px; text-align: center; font-weight: bold; font-size: 7pt; border: 1px solid #666; vertical-align: middle; height: 35px; line-height: 1.2; }
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
        
        // Se não houver categorias, gerar apenas página de áreas
        if (empty($categorias)) {
            $html = '<div class="page">' . self::gerarHeaderPadrao($orcamento, 'PLANILHA RESUMO');
            $html .= '<div class="etapa-header">RESUMO GERAL</div>';
            
            // TABELAS DE ÁREAS
            $dadosAreas = self::calcularAreas($orcamento);
            $areaTerreno = $dadosAreas['terreno'];
            $areaTerrea = $dadosAreas['terrea'];
            $areaSuperior = $dadosAreas['superior'];
            $areaTotal = $dadosAreas['total'];
            $areasPersonalizadas = $dadosAreas['areas'];
            
            // Gerar tabela de áreas
            $html .= '<div style="page-break-inside: avoid;">';
            $html .= '<table class="table-areas" style="margin-top:20px;"><thead><tr><th>ÁREAS</th><th>m2</th><th>FATOR</th><th>m2 x FATOR</th></tr></thead><tbody>';
            
            if (!empty($areasPersonalizadas)) {
                foreach ($areasPersonalizadas as $area) {
                    $nome = htmlspecialchars((string)($area['nome'] ?? ''));
                    $m2 = (float)($area['m2'] ?? 0);
                    $fator = (float)($area['fator'] ?? 1);
                    $tipoArea = (string)($area['tipo_area'] ?? 'terreno');
                    $m2xFator = $m2 * $fator;
                    
                    $nomeExibicao = $nome;
                    if ($tipoArea === 'terrea') {
                        $nomeExibicao = $nome . ' *';
                    } elseif ($tipoArea === 'superior') {

                        $nomeExibicao = $nome . ' **';

                    } elseif ($tipoArea === 'nao_somar') {

                        $nomeExibicao = $nome . '<br><span style="font-size:8px;">Não utilizado para cálculo</span>';

                    }
                    
                    $html .= sprintf(
                        '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                        $nomeExibicao,
                        number_format($m2, 2, ',', '.'),
                        number_format($fator, 2, ',', '.'),
                        number_format($m2xFator, 2, ',', '.')
                    );
                }
                
                $html .= sprintf(
                    '<tr style="background:#e0e0e0;"><td colspan="3"><strong>ÁREA TOTAL DO TERRENO:</strong></td><td><strong>%s</strong></td></tr>',
                    number_format($areaTerreno, 2, ',', '.')
                );
                
                if ($areaTerrea > 0) {
                    $html .= sprintf(
                        '<tr style="background:#d0d0d0;"><td colspan="3"><strong>ÁREA CONSTRUÍDA TÉRREA (*):</strong></td><td><strong>%s</strong></td></tr>',
                        number_format($areaTerrea, 2, ',', '.')
                    );
                }
                
                if ($areaSuperior > 0) {
                    $html .= sprintf(
                        '<tr style="background:#c0c0c0;"><td colspan="3"><strong>ÁREA CONSTRUÍDA SUPERIORES (**):</strong></td><td><strong>%s</strong></td></tr>',
                        number_format($areaSuperior, 2, ',', '.')
                    );
                }
            } else {
                $html .= sprintf(
                    '<tr><td>ÁREA TOTAL</td><td>%s</td><td>1</td><td>%s</td></tr>',
                    number_format($areaTotal, 2, ',', '.'),
                    number_format($areaTotal, 2, ',', '.')
                );
            }
            
            $html .= sprintf('<tr class="total-row"><td colspan="3">ÁREA TOTAL CONSTRUÍDA:</td><td>%s</td></tr>', number_format($areaTotal, 2, ',', '.'));
            
            if ($areaTerrea > 0 || $areaSuperior > 0) {
                $html .= '<tr><td colspan="4" style="font-size:9px;color:#666;padding:8px;text-align:left;">';
                if ($areaTerrea > 0) {
                    $html .= '(*) Área construída térrea - pavimentos superiores<br>';
                }
                if ($areaSuperior > 0) {
                    $html .= '(**) Área construída superiores';
                }
                $html .= '</td></tr>';
            }
            
            $html .= '</tbody></table>';
            $html .= '</div>';
            
            $html .= '<div class="page-footer"><div>FOLHA: 1</div></div>';
            $html .= '</div>';
            
            return $html;
        }
        
        // PRIMEIRO: Gerar página de RESUMO GERAL (logo após as capas)
        $html = '';
        
        // TABELAS DE ÁREAS
        // Processar áreas personalizadas
        $dadosAreas = self::calcularAreas($orcamento);
        $areaTerreno = $dadosAreas['terreno'];
        $areaTerrea = $dadosAreas['terrea'];
        $areaSuperior = $dadosAreas['superior'];
        $areaTotal = $dadosAreas['total'];
        $areasPersonalizadas = $dadosAreas['areas'];
        
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
        
        $html .= '<div class="page">' . self::gerarHeaderPadrao($orcamento, 'PLANILHA RESUMO');
        $html .= '<div class="etapa-header">RESUMO GERAL</div>';
        
        // Calcular custos administrativos e impostos
        $subtotal = $totalGeral;
        $percentualCustosAdm = (float)($orcamento['percentual_custos_adm'] ?? 0);
        $percentualImpostos = (float)($orcamento['percentual_impostos'] ?? 0);
        
        $valorCustosAdm = $subtotal * ($percentualCustosAdm / 100);
        $valorImpostos = $subtotal * ($percentualImpostos / 100);
        $totalFinal = $subtotal + $valorCustosAdm + $valorImpostos;
        
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
        
        // Linha de SUBTOTAL DA OBRA
        $html .= sprintf(
            '<tr class="subtotal-row"><td class="left">SUBTOTAL DA OBRA:</td><td class="right">R$ %s</td><td class="center">100,00%%</td><td class="center">%s</td><td class="right">R$ %s</td></tr>',
            self::formatarValor($subtotal),
            number_format($areaTotal, 2, ',', '.'),
            self::formatarValor($areaTotal > 0 ? $subtotal / $areaTotal : 0)
        );
        
        // Linha de Custos Administrativos (se houver)
        if ($percentualCustosAdm > 0) {
            $html .= sprintf(
                '<tr><td class="left">Custos Administrativos (%s%%)</td><td class="right">R$ %s</td><td class="center" colspan="3">—</td></tr>',
                number_format($percentualCustosAdm, 2, ',', '.'),
                self::formatarValor($valorCustosAdm)
            );
        }
        
        // Linha de Impostos (se houver)
        if ($percentualImpostos > 0) {
            $html .= sprintf(
                '<tr><td class="left">Impostos (%s%%)</td><td class="right">R$ %s</td><td class="center" colspan="3">—</td></tr>',
                number_format($percentualImpostos, 2, ',', '.'),
                self::formatarValor($valorImpostos)
            );
        }
        
        // Linha de TOTAL GERAL
        $html .= sprintf(
            '<tr class="total-row"><td class="left">TOTAL GERAL:</td><td class="right">R$ %s</td><td class="center" colspan="3">—</td></tr>',
            self::formatarValor($totalFinal)
        );
        
        $html .= '</tbody></table>';
        
        // Gerar tabela de áreas (separada, abaixo) - wrapper para evitar quebra de página
        $html .= '<div style="page-break-inside: avoid;">';
        $html .= '<table class="table-areas" style="margin-top:20px;"><thead><tr><th>ÁREAS</th><th>m2</th><th>FATOR</th><th>m2 x FATOR</th></tr></thead><tbody>';
        
        if (!empty($areasPersonalizadas)) {
            foreach ($areasPersonalizadas as $area) {
                $nome = htmlspecialchars((string)($area['nome'] ?? ''));
                $m2 = (float)($area['m2'] ?? 0);
                $fator = (float)($area['fator'] ?? 1);
                $tipoArea = (string)($area['tipo_area'] ?? 'terreno');
                $m2xFator = $m2 * $fator;
                
                // Adicionar indicador conforme tipo de área
                $nomeExibicao = $nome;
                if ($tipoArea === 'terrea') {
                    $nomeExibicao = $nome . ' *';
                } elseif ($tipoArea === 'superior') {

                    $nomeExibicao = $nome . ' **';

                } elseif ($tipoArea === 'nao_somar') {

                    $nomeExibicao = $nome . '<br><span style="font-size:8px;">Não utilizado para cálculo</span>';

                }
                
                $html .= sprintf(
                    '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                    $nomeExibicao,
                    number_format($m2, 2, ',', '.'),
                    number_format($fator, 2, ',', '.'),
                    number_format($m2xFator, 2, ',', '.')
                );
            }
            
            // Linha de subtotal do terreno
            $html .= sprintf(
                '<tr style="background:#e0e0e0;"><td colspan="3"><strong>ÁREA TOTAL DO TERRENO:</strong></td><td><strong>%s</strong></td></tr>',
                number_format($areaTerreno, 2, ',', '.')
            );
            
            // Linha de área construída térrea (se houver)
            if ($areaTerrea > 0) {
                $html .= sprintf(
                    '<tr style="background:#d0d0d0;"><td colspan="3"><strong>ÁREA CONSTRUÍDA TÉRREA (*):</strong></td><td><strong>%s</strong></td></tr>',
                    number_format($areaTerrea, 2, ',', '.')
                );
            }
            
            // Linha de área construída superiores (se houver)
            if ($areaSuperior > 0) {
                $html .= sprintf(
                    '<tr style="background:#c0c0c0;"><td colspan="3"><strong>ÁREA CONSTRUÍDA SUPERIORES (**):</strong></td><td><strong>%s</strong></td></tr>',
                    number_format($areaSuperior, 2, ',', '.')
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
        
        $html .= sprintf('<tr class="total-row"><td colspan="3">ÁREA TOTAL CONSTRUÍDA:</td><td>%s</td></tr>', number_format($areaTotal, 2, ',', '.'));
        
        // Adicionar nota explicativa
        if ($areaTerrea > 0 || $areaSuperior > 0) {
            $html .= '<tr><td colspan="4" style="font-size:9px;color:#666;padding:8px;text-align:left;">';
            if ($areaTerrea > 0) {
                $html .= '(*) Área construída térrea - pavimentos superiores<br>';
            }
            if ($areaSuperior > 0) {
                $html .= '(**) Área construída superiores';
            }
            $html .= '</td></tr>';
        }
        
        $html .= '</tbody></table>';
        $html .= '</div>'; // Fecha wrapper page-break-inside: avoid
        
        $html .= '<div class="page-footer"><div>FOLHA: 1</div></div>';
        $html .= '</div>';
        
        // DEPOIS: Gerar páginas de resumo por categoria
        $paginaNum = 2;
        
        // Obter valor de entrada para cálculos
        $valorEntrada = (float)($orcamento['valor_entrada'] ?? 0);
        $saldoAPagar = $totalGeral - $valorEntrada;
        
        foreach ($categorias as $categoriaNome => $categoriaData) {
            $html .= '<div class="page">' . self::gerarHeaderPadrao($orcamento, 'PLANILHA RESUMO');
            $html .= '<div class="etapa-header">' . htmlspecialchars(strtoupper($categoriaNome)) . '</div>';
            $html .= '<table class="table-resumo"><thead><tr>';
            $html .= '<th style="width:6%;">Nº</th><th style="width:28%;">DESCRIÇÃO</th>';
            $html .= '<th class="right" style="width:11%;">VALOR TOTAL</th>';
            $html .= '<th class="center" style="width:7%;">% Etapa</th>';
            $html .= '<th class="right" style="width:11%;">VLR CONCLUÍDO</th>';
            $html .= '<th class="center" style="width:7%;">% Obra</th>';
            $html .= '<th class="right" style="width:11%;">VLR A PAGAR</th>';
            $html .= '<th class="center" style="width:7%;">% Saldo</th>';
            $html .= '<th class="center" style="width:12%;">Status</th>';
            $html .= '</tr></thead><tbody>';
            
            foreach ($categoriaData['itens'] as $item) {
                $valorTotal = (float)$item['valor_total'];
                $pctEtapa = $categoriaData['total'] > 0 ? ($valorTotal / $categoriaData['total']) * 100 : 0;
                
                // Buscar percentual_realizado do item
                $stmtItem = $pdo->prepare('SELECT percentual_realizado FROM orcamento_itens WHERE orcamento_id = :orcamento_id AND codigo = :codigo LIMIT 1');
                $stmtItem->execute([':orcamento_id' => $orcamentoId, ':codigo' => $item['codigo']]);
                $itemData = $stmtItem->fetch(\PDO::FETCH_ASSOC);
                $percentualRealizado = (float)($itemData['percentual_realizado'] ?? 0);
                
                // Calcular valores concluídos
                $valorConcluido = $valorTotal * ($percentualRealizado / 100);
                $pctObra = $totalGeral > 0 ? ($valorConcluido / $totalGeral) * 100 : 0;
                
                // Calcular valores a pagar
                $valorAPagar = $valorConcluido;
                $pctSaldo = $saldoAPagar > 0 ? ($valorConcluido / $saldoAPagar) * 100 : 0;
                
                // Determinar status
                $status = '';
                $statusColor = '';
                if ($percentualRealizado >= 100) {
                    $status = 'Concluído';
                    $statusColor = 'color:#4CAF50;font-weight:bold;';
                } elseif ($percentualRealizado > 0) {
                    $status = number_format($percentualRealizado, 0) . '%';
                    $statusColor = 'color:#FF9800;';
                } else {
                    $status = 'Pendente';
                    $statusColor = 'color:#999;';
                }
                
                $html .= sprintf(
                    '<tr><td>%s</td><td>%s</td><td class="right">R$ %s</td><td class="center">%s%%</td><td class="right">R$ %s</td><td class="center">%s%%</td><td class="right">R$ %s</td><td class="center">%s%%</td><td class="center" style="%s">%s</td></tr>',
                    htmlspecialchars((string)$item['codigo']),
                    htmlspecialchars((string)$item['descricao']),
                    self::formatarValor($valorTotal),
                    number_format($pctEtapa, 2, ',', '.'),
                    self::formatarValor($valorConcluido),
                    number_format($pctObra, 2, ',', '.'),
                    self::formatarValor($valorAPagar),
                    number_format($pctSaldo, 2, ',', '.'),
                    $statusColor,
                    $status
                );
            }
            
            // Calcular totais da categoria
            $totalConcluidoCategoria = 0;
            foreach ($categoriaData['itens'] as $item) {
                $stmtItem = $pdo->prepare('SELECT percentual_realizado FROM orcamento_itens WHERE orcamento_id = :orcamento_id AND codigo = :codigo LIMIT 1');
                $stmtItem->execute([':orcamento_id' => $orcamentoId, ':codigo' => $item['codigo']]);
                $itemData = $stmtItem->fetch(\PDO::FETCH_ASSOC);
                $percentualRealizado = (float)($itemData['percentual_realizado'] ?? 0);
                $totalConcluidoCategoria += (float)$item['valor_total'] * ($percentualRealizado / 100);
            }
            
            $pctCategoriaObra = $totalGeral > 0 ? ($totalConcluidoCategoria / $totalGeral) * 100 : 0;
            $pctCategoriaSaldo = $saldoAPagar > 0 ? ($totalConcluidoCategoria / $saldoAPagar) * 100 : 0;
            
            $html .= sprintf(
                '<tr class="subtotal-row"><td colspan="2">SUBTOTAL - %s</td><td class="right">R$ %s</td><td class="center">100,00%%</td><td class="right">R$ %s</td><td class="center">%s%%</td><td class="right">R$ %s</td><td class="center">%s%%</td><td class="center">—</td></tr>',
                htmlspecialchars(strtoupper($categoriaNome)),
                self::formatarValor($categoriaData['total']),
                self::formatarValor($totalConcluidoCategoria),
                number_format($pctCategoriaObra, 2, ',', '.'),
                self::formatarValor($totalConcluidoCategoria),
                number_format($pctCategoriaSaldo, 2, ',', '.')
            );
            
            $html .= '</tbody></table>';
            $html .= sprintf('<div class="page-footer"><div>FOLHA: %d</div></div>', $paginaNum);
            $html .= '</div>';
            
            $paginaNum++;
        }
        
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
    
    private static function gerarPaginaResumoPorFase(int $orcamentoId, array $orcamento): string
    {
        $itens = \App\Models\OrcamentoItem::allByOrcamento($orcamentoId);
        
        // BDI global padrão
        $bdiGlobal = (float)($orcamento['margem_global'] ?? 18.0);
        
        // Agrupar por ETAPA > GRUPO > CATEGORIA (3 níveis)
        $itensProcessados = [];
        foreach ($itens as $item) {
            $etapa = (string)($item['etapa'] ?? 'SEM ETAPA');
            $grupo = (string)($item['grupo'] ?? 'SEM GRUPO');
            $categoria = (string)($item['categoria'] ?? 'SEM CATEGORIA');
            
            // Calcular valor do item
            $quantidade = (float)($item['quantidade'] ?? 0);
            $custoMat = (float)($item['custo_material'] ?? 0);
            $custoMo = (float)($item['custo_mao_obra'] ?? 0);
            $custoEquip = (float)($item['custo_equipamento'] ?? 0);
            
            $usaMargemPersonalizada = (int)($item['usa_margem_personalizada'] ?? 0);
            $margemPersonalizada = (float)($item['margem_personalizada'] ?? 0);
            
            $bdi = $usaMargemPersonalizada && $margemPersonalizada > 0 
                ? $margemPersonalizada 
                : $bdiGlobal;
            
            $fatorBDI = 1 + ($bdi / 100);
            
            $vlrUnitMat = ($custoMat + $custoEquip) * $fatorBDI;
            $vlrUnitMo = $custoMo * $fatorBDI;
            $vlrUnitTotal = $vlrUnitMat + $vlrUnitMo;
            $vlrTotal = $vlrUnitTotal * $quantidade;
            
            $percentualRealizado = (float)($item['percentual_realizado'] ?? 0);
            
            if (!isset($itensProcessados[$etapa])) {
                $itensProcessados[$etapa] = [];
            }
            if (!isset($itensProcessados[$etapa][$grupo])) {
                $itensProcessados[$etapa][$grupo] = [];
            }
            if (!isset($itensProcessados[$etapa][$grupo][$categoria])) {
                $itensProcessados[$etapa][$grupo][$categoria] = [
                    'total' => 0.0,
                    'concluido' => 0.0
                ];
            }
            
            $itensProcessados[$etapa][$grupo][$categoria]['total'] += $vlrTotal;
            $itensProcessados[$etapa][$grupo][$categoria]['concluido'] += $vlrTotal * ($percentualRealizado / 100);
        }
        
        // Calcular totais por etapa
        $totaisPorEtapa = [];
        $totaisConcluidosPorEtapa = [];
        
        foreach ($itensProcessados as $etapa => $grupos) {
            $totalEtapa = 0.0;
            $totalConcluidoEtapa = 0.0;
            
            foreach ($grupos as $grupo => $categorias) {
                foreach ($categorias as $categoria => $dados) {
                    $totalEtapa += $dados['total'];
                    $totalConcluidoEtapa += $dados['concluido'];
                }
            }
            
            $totaisPorEtapa[$etapa] = $totalEtapa;
            $totaisConcluidosPorEtapa[$etapa] = $totalConcluidoEtapa;
        }
        
        // Calcular total geral da obra
        $totalGeralObra = array_sum($totaisPorEtapa);
        $totalConcluidoObra = array_sum($totaisConcluidosPorEtapa);
        
        // Iniciar HTML
        $html = '<div class="page"><div class="page-title">PLANILHA RESUMO</div>';
        $html .= '<div class="page-subtitle">Resumo por Fase, Grupo e Categoria</div>';
        
        // Processar cada ETAPA
        foreach ($itensProcessados as $nomeEtapa => $grupos) {
            // Banner da etapa
            $html .= '<div class="banner-etapa" style="background:#1a237e;color:#FFF;padding:12px;margin:20px 0 10px 0;font-weight:bold;font-size:16px;">' . htmlspecialchars(strtoupper($nomeEtapa)) . '</div>';
            
            $totalEtapa = $totaisPorEtapa[$nomeEtapa];
            $totalConcluidoEtapa = $totaisConcluidosPorEtapa[$nomeEtapa];
            
            // Processar cada GRUPO dentro da etapa
            foreach ($grupos as $nomeGrupo => $categorias) {
                // Banner do grupo
                $html .= '<div class="banner-etapa" style="background:#2C3350;color:#FFF;padding:10px;margin:15px 0 8px 0;font-weight:bold;font-size:14px;">' . htmlspecialchars(strtoupper($nomeGrupo)) . '</div>';
                
                // Calcular total do grupo
                $totalGrupo = 0.0;
                $totalConcluidoGrupo = 0.0;
                foreach ($categorias as $categoria => $dados) {
                    $totalGrupo += $dados['total'];
                    $totalConcluidoGrupo += $dados['concluido'];
                }
                
                // Tabela de categorias dentro do grupo
                $html .= '<table class="table-detalhes">';
                $html .= '<thead><tr>';
                $html .= '<th class="left" style="width:22%;">CATEGORIA</th>';
                $html .= '<th class="right" style="width:12%;">VALOR TOTAL</th>';
                $html .= '<th class="center" style="width:8%;">% ETAPA</th>';
                $html .= '<th class="right" style="width:12%;">VLR CONCLUÍDO</th>';
                $html .= '<th class="center" style="width:8%;">% OBRA</th>';
                $html .= '<th class="right" style="width:12%;">VLR A PAGAR</th>';
                $html .= '<th class="center" style="width:8%;">% SALDO</th>';
                $html .= '</tr></thead><tbody>';
                
                // Listar categorias do grupo
                foreach ($categorias as $nomeCategoria => $dados) {
                    $totalCategoria = $dados['total'];
                    $totalConcluidoCategoria = $dados['concluido'];
                    
                    $percentualNaEtapa = $totalEtapa > 0 
                        ? ($totalCategoria / $totalEtapa) * 100 
                        : 0.0;
                    
                    // % OBRA = quanto da OBRA TOTAL foi concluído desta categoria
                    $percentualConcluidoNaObra = $totalGeralObra > 0 
                        ? ($totalConcluidoCategoria / $totalGeralObra) * 100 
                        : 0.0;
                    
                    // % SALDO = quanto representa do saldo restante (após entrada)
                    $valorEntrada = (float)($orcamento['valor_entrada'] ?? 0);
                    $saldoAPagar = $totalGeralObra - $valorEntrada;
                    $percentualAPagar = $saldoAPagar > 0 
                        ? ($totalConcluidoCategoria / $saldoAPagar) * 100 
                        : 0.0;
                    
                    // VALOR A PAGAR = valor concluído que ainda precisa ser pago
                    $valorAPagar = $totalConcluidoCategoria;
                    
                    $html .= '<tr>';
                    $html .= '<td class="left">' . htmlspecialchars($nomeCategoria) . '</td>';
                    $html .= '<td class="right">R$ ' . self::formatarValor($totalCategoria) . '</td>';
                    $html .= '<td class="center">' . number_format($percentualNaEtapa, 2, ',', '.') . '%</td>';
                    $html .= '<td class="right">R$ ' . self::formatarValor($totalConcluidoCategoria) . '</td>';
                    $html .= '<td class="center">' . number_format($percentualConcluidoNaObra, 2, ',', '.') . '%</td>';
                    $html .= '<td class="right">R$ ' . self::formatarValor($valorAPagar) . '</td>';
                    $html .= '<td class="center">' . number_format($percentualAPagar, 2, ',', '.') . '%</td>';
                    $html .= '</tr>';
                }
                
                // Subtotal do grupo
                $percentualGrupoNaEtapa = $totalEtapa > 0 
                    ? ($totalGrupo / $totalEtapa) * 100 
                    : 0.0;
                
                // % OBRA do grupo = quanto da OBRA TOTAL o grupo representa considerando o que foi concluído
                $percentualConcluidoGrupoNaObra = $totalGeralObra > 0 
                    ? ($totalConcluidoGrupo / $totalGeralObra) * 100 
                    : 0.0;
                
                // % SALDO do grupo
                $valorEntrada = (float)($orcamento['valor_entrada'] ?? 0);
                $saldoAPagar = $totalGeralObra - $valorEntrada;
                $percentualAPagarGrupo = $saldoAPagar > 0 
                    ? ($totalConcluidoGrupo / $saldoAPagar) * 100 
                    : 0.0;
                
                // VALOR A PAGAR do grupo
                $valorAPagarGrupo = $totalConcluidoGrupo;
                
                $html .= '<tr style="background:#2C3E50 !important;color:#FFF !important;font-weight:bold;">';
                $html .= '<td class="left" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;">SUBTOTAL — ' . htmlspecialchars(strtoupper($nomeGrupo)) . '</td>';
                $html .= '<td class="right" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;">R$ ' . self::formatarValor($totalGrupo) . '</td>';
                $html .= '<td class="center" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;">' . number_format($percentualGrupoNaEtapa, 2, ',', '.') . '%</td>';
                $html .= '<td class="right" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;">R$ ' . self::formatarValor($totalConcluidoGrupo) . '</td>';
                $html .= '<td class="center" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;">' . number_format($percentualConcluidoGrupoNaObra, 2, ',', '.') . '%</td>';
                $html .= '<td class="right" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;">R$ ' . self::formatarValor($valorAPagarGrupo) . '</td>';
                $html .= '<td class="center" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;">' . number_format($percentualAPagarGrupo, 2, ',', '.') . '%</td>';
                $html .= '</tr>';
                
                $html .= '</tbody></table>';
            }
            
            // Subtotal da etapa
            $percentualEtapaNaObra = $totalGeralObra > 0 
                ? ($totalEtapa / $totalGeralObra) * 100 
                : 0.0;
            
            $percentualConcluidoEtapa = $totalEtapa > 0 
                ? ($totalConcluidoEtapa / $totalEtapa) * 100 
                : 0.0;
            
            $html .= '<div style="background:#1a237e;color:#FFF;padding:10px;margin:10px 0;font-weight:bold;font-size:14px;">';
            $html .= 'SUBTOTAL — ' . htmlspecialchars(strtoupper($nomeEtapa));
            $html .= ' | Valor: R$ ' . self::formatarValor($totalEtapa);
            $html .= ' | % na Obra: ' . number_format($percentualEtapaNaObra, 2, ',', '.') . '%';
            $html .= ' | % Concluído: ' . number_format($percentualConcluidoEtapa, 2, ',', '.') . '%';
            $html .= '</div>';
        }
        
        // Rodapé geral consolidado
        $html .= '<div style="margin-top:40px;">';
        $html .= '<div style="font-weight:700; font-size:16px; margin-bottom:15px; text-align:center;">RESUMO GERAL</div>';
        
        // Tabela de resumo por etapa usando a classe table-detalhes
        $html .= '<table class="table-detalhes">';
        $html .= '<thead><tr>';
        $html .= '<th class="left" style="width:30%;">ETAPA</th>';
        $html .= '<th class="right" style="width:20%;">VLR TOTAL</th>';
        $html .= '<th class="center" style="width:15%;">% OBRA</th>';
        $html .= '<th class="right" style="width:20%;">VLR CONCLUÍDO</th>';
        $html .= '<th class="center" style="width:15%;">% CONCLUÍDO</th>';
        $html .= '</tr></thead><tbody>';
        
        foreach ($totaisPorEtapa as $nomeEtapa => $valorEtapa) {
            $percentualEtapa = $totalGeralObra > 0 
                ? ($valorEtapa / $totalGeralObra) * 100 
                : 0.0;
            
            $valorConcluidoEtapa = $totaisConcluidosPorEtapa[$nomeEtapa];
            $percentualConcluidoEtapa = $valorEtapa > 0 
                ? ($valorConcluidoEtapa / $valorEtapa) * 100 
                : 0.0;
            
            $html .= '<tr>';
            $html .= '<td class="left">' . htmlspecialchars(strtoupper($nomeEtapa)) . '</td>';
            $html .= '<td class="right">R$ ' . self::formatarValor($valorEtapa) . '</td>';
            $html .= '<td class="center">' . number_format($percentualEtapa, 2, ',', '.') . '%</td>';
            $html .= '<td class="right">R$ ' . self::formatarValor($valorConcluidoEtapa) . '</td>';
            $html .= '<td class="center">' . number_format($percentualConcluidoEtapa, 2, ',', '.') . '%</td>';
            $html .= '</tr>';
        }
        
        // Subtotal da obra
        $percentualConcluidoObra = $totalGeralObra > 0 
            ? ($totalConcluidoObra / $totalGeralObra) * 100 
            : 0.0;
        
        $html .= '<tr style="background:#E2E8F0 !important;font-weight:bold;">';
        $html .= '<td class="left" style="padding:8px;background:#E2E8F0 !important;border:1px solid #CCC;">SUBTOTAL DA OBRA</td>';
        $html .= '<td class="right" style="padding:8px;background:#E2E8F0 !important;border:1px solid #CCC;">R$ ' . self::formatarValor($totalGeralObra) . '</td>';
        $html .= '<td class="center" style="padding:8px;background:#E2E8F0 !important;border:1px solid #CCC;">100%</td>';
        $html .= '<td class="right" style="padding:8px;background:#E2E8F0 !important;border:1px solid #CCC;">R$ ' . self::formatarValor($totalConcluidoObra) . '</td>';
        $html .= '<td class="center" style="padding:8px;background:#E2E8F0 !important;border:1px solid #CCC;">' . number_format($percentualConcluidoObra, 2, ',', '.') . '%</td>';
        $html .= '</tr>';
        
        // Impostos
        $percentualImpostos = (float)($orcamento['percentual_impostos'] ?? 18.0);
        $valorImpostos = $totalGeralObra * ($percentualImpostos / 100);
        
        $html .= '<tr>';
        $html .= '<td class="left">IMPOSTOS (' . number_format($percentualImpostos, 0) . '%)</td>';
        $html .= '<td class="right">R$ ' . self::formatarValor($valorImpostos) . '</td>';
        $html .= '<td class="center">—</td>';
        $html .= '<td class="right">—</td>';
        $html .= '<td class="center">—</td>';
        $html .= '</tr>';
        
        // Total geral
        $totalComImpostos = $totalGeralObra + $valorImpostos;
        
        $html .= '<tr style="background:#2C3E50 !important;color:#FFF !important;font-weight:bold;font-size:16px;">';
        $html .= '<td class="left" style="padding:12px;background:#2C3E50 !important;color:#FFF !important;border:1px solid #2C3E50 !important;">TOTAL GERAL</td>';
        $html .= '<td class="right" style="padding:12px;background:#2C3E50 !important;color:#FFF !important;border:1px solid #2C3E50 !important;">R$ ' . self::formatarValor($totalComImpostos) . '</td>';
        $html .= '<td class="center" style="padding:12px;background:#2C3E50 !important;color:#FFF !important;border:1px solid #2C3E50 !important;">—</td>';
        $html .= '<td class="right" style="padding:12px;background:#2C3E50 !important;color:#FFF !important;border:1px solid #2C3E50 !important;">—</td>';
        $html .= '<td class="center" style="padding:12px;background:#2C3E50 !important;color:#FFF !important;border:1px solid #2C3E50 !important;">—</td>';
        $html .= '</tr>';
        
        $html .= '</tbody></table>';
        $html .= '</div>';
        
        $html .= '</div>';
        return $html;
    }
    
    private static function gerarPaginaDetalhamento(int $orcamentoId, array $orcamento): string
    {
        $itens = \App\Models\OrcamentoItem::allByOrcamento($orcamentoId);
        
        // BDI global padrão
        $bdiGlobal = (float)($orcamento['margem_global'] ?? 18.0);
        
        // Agrupar por ETAPA > GRUPO > CATEGORIA (3 níveis hierárquicos)
        $grouped = [];
        foreach ($itens as $item) {
            $etapa = (string)($item['etapa'] ?? 'SEM ETAPA');
            $grupo = (string)($item['grupo'] ?? 'SEM GRUPO');
            $categoria = (string)($item['categoria'] ?? 'SEM CATEGORIA');
            
            if (!isset($grouped[$etapa])) {
                $grouped[$etapa] = [];
            }
            if (!isset($grouped[$etapa][$grupo])) {
                $grouped[$etapa][$grupo] = [];
            }
            if (!isset($grouped[$etapa][$grupo][$categoria])) {
                $grouped[$etapa][$grupo][$categoria] = [];
            }
            
            $grouped[$etapa][$grupo][$categoria][] = $item;
        }

        $html = '<div class="page"><div class="page-title">PLANILHA ORÇAMENTÁRIA</div>';
        $html .= '<div class="page-subtitle">Detalhamento por Item</div>';

        // PRIMEIRO: Pré-calcular total geral da obra
        $totalGeralObra = 0.0;
        foreach ($grouped as $etapa => $grupos) {
            foreach ($grupos as $grupo => $categorias) {
                foreach ($categorias as $categoria => $itensCategoria) {
                    foreach ($itensCategoria as $item) {
                        // Calcular valor usando custos + BDI
                        $quantidade = (float)($item['quantidade'] ?? 0);
                        $custoMat = (float)($item['custo_material'] ?? 0);
                        $custoMo = (float)($item['custo_mao_obra'] ?? 0);
                        $custoEquip = (float)($item['custo_equipamento'] ?? 0);
                        
                        $usaMargemPersonalizada = (int)($item['usa_margem_personalizada'] ?? 0);
                        $margemPersonalizada = (float)($item['margem_personalizada'] ?? 0);
                        
                        $bdi = $usaMargemPersonalizada && $margemPersonalizada > 0 
                            ? $margemPersonalizada 
                            : $bdiGlobal;
                        
                        $fatorBDI = 1 + ($bdi / 100);
                        
                        $vlrUnitMat = ($custoMat + $custoEquip) * $fatorBDI;
                        $vlrUnitMo = $custoMo * $fatorBDI;
                        $vlrUnitTotal = $vlrUnitMat + $vlrUnitMo;
                        $vlrTotal = $vlrUnitTotal * $quantidade;
                        
                        $totalGeralObra += $vlrTotal;
                    }
                }
            }
        }

        // Processar cada ETAPA (fase)
        foreach ($grouped as $nomeEtapa => $grupos) {
            $html .= '<!-- PROCESSANDO ETAPA: ' . htmlspecialchars($nomeEtapa) . ' com ' . count($grupos) . ' grupos -->';
            
            // Banner da ETAPA (azul escuro)
            $html .= '<div class="banner-etapa" style="background:#1a237e;color:#FFF;padding:12px;margin:20px 0 10px 0;font-weight:bold;font-size:16px;">' . htmlspecialchars(strtoupper($nomeEtapa)) . '</div>';
            
            $subtotalEtapa = 0.0;
            $totalConcluidoEtapa = 0.0;
            
            // Processar cada GRUPO dentro da etapa
            foreach ($grupos as $nomeGrupo => $categorias) {
                $html .= '<!-- PROCESSANDO GRUPO: ' . htmlspecialchars($nomeGrupo) . ' com ' . count($categorias) . ' categorias -->';
                
                // Banner do GRUPO (azul médio)
                $html .= '<div class="banner-etapa" style="background:#2C3350;color:#FFF;padding:10px;margin:15px 0 8px 0;font-weight:bold;font-size:14px;">' . htmlspecialchars(strtoupper($nomeGrupo)) . '</div>';
                
                $subtotalGrupo = 0.0;
                
                // Tabela com 14 colunas (adicionando VLR CONCLUÍDO, % OBRA, VLR A PAGAR, % SALDO)
                $html .= '<table class="table-detalhes">';
                $html .= '<thead><tr>';
                $html .= '<th class="left" style="width:5%;">Cód.</th>';
                $html .= '<th class="left" style="width:16%;">Descrição</th>';
                $html .= '<th class="center" style="width:3%;">Un</th>';
                $html .= '<th class="center" style="width:4%;">Qtd</th>';
                $html .= '<th class="right" style="width:7%;">Vlr Unit. Mat.</th>';
                $html .= '<th class="right" style="width:7%;">Vlr Unit. M.O.</th>';
                $html .= '<th class="right" style="width:7%;">Vlr Unit.Total</th>';
                $html .= '<th class="right" style="width:8%;">Vlr Total</th>';
                $html .= '<th class="center" style="width:5%;">% Etapa</th>';
                $html .= '<th class="right" style="width:7%;">Vlr Concluído</th>';
                $html .= '<th class="center" style="width:5%;">% Obra</th>';
                $html .= '<th class="right" style="width:7%;">Vlr A Pagar</th>';
                $html .= '<th class="center" style="width:5%;">% Saldo</th>';
                $html .= '<th class="center" style="width:7%;">Status</th>';
                $html .= '</tr></thead><tbody>';

                // Calcular subtotal do grupo primeiro e obter valor de entrada
                $valorEntrada = (float)($orcamento['valor_entrada'] ?? 0);
                $saldoAPagar = $totalGeralObra - $valorEntrada;
                foreach ($categorias as $categoria => $itensCategoria) {
                    foreach ($itensCategoria as $item) {
                        $quantidade = (float)($item['quantidade'] ?? 0);
                        $custoMat = (float)($item['custo_material'] ?? 0);
                        $custoMo = (float)($item['custo_mao_obra'] ?? 0);
                        $custoEquip = (float)($item['custo_equipamento'] ?? 0);
                        
                        $usaMargemPersonalizada = (int)($item['usa_margem_personalizada'] ?? 0);
                        $margemPersonalizada = (float)($item['margem_personalizada'] ?? 0);
                        
                        $bdi = $usaMargemPersonalizada && $margemPersonalizada > 0 
                            ? $margemPersonalizada 
                            : $bdiGlobal;
                        
                        $fatorBDI = 1 + ($bdi / 100);
                        
                        $vlrUnitMat = ($custoMat + $custoEquip) * $fatorBDI;
                        $vlrUnitMo = $custoMo * $fatorBDI;
                        $vlrUnitTotal = $vlrUnitMat + $vlrUnitMo;
                        $vlrTotal = $vlrUnitTotal * $quantidade;
                        
                        $subtotalGrupo += $vlrTotal;
                    }
                }

                // Agora gerar as linhas por categoria
                foreach ($categorias as $nomeCategoria => $itensCategoria) {
                    $html .= '<!-- CATEGORIA: ' . htmlspecialchars($nomeCategoria) . ' com ' . count($itensCategoria) . ' itens -->';
                    
                    // Linha de cabeçalho da CATEGORIA (cinza)
                    $html .= '<tr>';
                    $html .= '<td colspan="14" class="left" style="padding:8px;background:#4A5568;color:#FFF;font-weight:bold;border:1px solid #4A5568;">' . htmlspecialchars(strtoupper($nomeCategoria)) . '</td>';
                    $html .= '</tr>';
                    
                    $subtotalCategoria = 0.0;
                    $totalConcluidoCategoria = 0.0;
                    
                    // Itens da categoria
                    foreach ($itensCategoria as $item) {
                        $quantidade = (float)($item['quantidade'] ?? 0);
                        $custoMat = (float)($item['custo_material'] ?? 0);
                        $custoMo = (float)($item['custo_mao_obra'] ?? 0);
                        $custoEquip = (float)($item['custo_equipamento'] ?? 0);
                        $percentualRealizado = (float)($item['percentual_realizado'] ?? 0);
                        
                        $usaMargemPersonalizada = (int)($item['usa_margem_personalizada'] ?? 0);
                        $margemPersonalizada = (float)($item['margem_personalizada'] ?? 0);
                        
                        $bdi = $usaMargemPersonalizada && $margemPersonalizada > 0 
                            ? $margemPersonalizada 
                            : $bdiGlobal;
                        
                        $fatorBDI = 1 + ($bdi / 100);
                        
                        $vlrUnitMat = ($custoMat + $custoEquip) * $fatorBDI;
                        $vlrUnitMo = $custoMo * $fatorBDI;
                        $vlrUnitTotal = $vlrUnitMat + $vlrUnitMo;
                        $vlrTotal = $vlrUnitTotal * $quantidade;
                        
                        $subtotalCategoria += $vlrTotal;
                        $totalConcluidoCategoria += $vlrTotal * ($percentualRealizado / 100);
                        
                        $pctEtapa = $subtotalGrupo > 0 ? ($vlrTotal / $subtotalGrupo) * 100 : 0.0;
                        
                        // Calcular valores concluídos e a pagar
                        $vlrConcluido = $vlrTotal * ($percentualRealizado / 100);
                        $pctObra = $totalGeralObra > 0 ? ($vlrConcluido / $totalGeralObra) * 100 : 0.0;
                        $vlrAPagar = $vlrConcluido;
                        $pctSaldo = $saldoAPagar > 0 ? ($vlrConcluido / $saldoAPagar) * 100 : 0.0;
                        
                        // Determinar status baseado no percentual realizado
                        $status = '';
                        $statusColor = '';
                        if ($percentualRealizado >= 100) {
                            $status = 'Concluído';
                            $statusColor = 'color:#4CAF50;font-weight:bold;';
                        } elseif ($percentualRealizado > 0) {
                            $status = number_format($percentualRealizado, 0) . '%';
                            $statusColor = 'color:#FF9800;';
                        } else {
                            $status = 'Pendente';
                            $statusColor = 'color:#999;';
                        }
                        
                        // Formatar valores (exibir "—" quando zero)
                        $displayMatUnit = $vlrUnitMat > 0.001 
                            ? 'R$ ' . self::formatarValor($vlrUnitMat) 
                            : '—';
                        
                        $displayMoUnit = $vlrUnitMo > 0.001 
                            ? 'R$ ' . self::formatarValor($vlrUnitMo) 
                            : '—';
                        
                        $html .= '<tr>';
                        $html .= '<td class="left">' . htmlspecialchars((string)($item['codigo'] ?? '')) . '</td>';
                        $html .= '<td class="left">' . nl2br(htmlspecialchars((string)($item['descricao'] ?? ''))) . '</td>';
                        $html .= '<td class="center">' . htmlspecialchars((string)($item['unidade'] ?? '')) . '</td>';
                        $html .= '<td class="center">' . number_format($quantidade, 2, ',', '.') . '</td>';
                        $html .= '<td class="right">' . $displayMatUnit . '</td>';
                        $html .= '<td class="right">' . $displayMoUnit . '</td>';
                        $html .= '<td class="right">R$ ' . self::formatarValor($vlrUnitTotal) . '</td>';
                        $html .= '<td class="right">R$ ' . self::formatarValor($vlrTotal) . '</td>';
                        $html .= '<td class="center">' . number_format($pctEtapa, 2, ',', '.') . '%</td>';
                        $html .= '<td class="right">R$ ' . self::formatarValor($vlrConcluido) . '</td>';
                        $html .= '<td class="center">' . number_format($pctObra, 2, ',', '.') . '%</td>';
                        $html .= '<td class="right">R$ ' . self::formatarValor($vlrAPagar) . '</td>';
                        $html .= '<td class="center">' . number_format($pctSaldo, 2, ',', '.') . '%</td>';
                        $html .= '<td class="center" style="' . $statusColor . '">' . $status . '</td>';
                        $html .= '</tr>';
                    }
                    
                    // Subtotal da categoria
                    $pctObraCategoria = $totalGeralObra > 0 ? ($totalConcluidoCategoria / $totalGeralObra) * 100 : 0.0;
                    $pctSaldoCategoria = $saldoAPagar > 0 ? ($totalConcluidoCategoria / $saldoAPagar) * 100 : 0.0;
                    
                    $html .= '<tr style="background:#E2E8F0 !important;font-weight:bold;">';
                    $html .= '<td colspan="7" class="left" style="padding:6px;background:#E2E8F0 !important;border:1px solid #CCC;">SUBTOTAL — ' . htmlspecialchars(strtoupper($nomeCategoria)) . '</td>';
                    $html .= '<td class="right" style="padding:6px;background:#E2E8F0 !important;border:1px solid #CCC;">R$ ' . self::formatarValor($subtotalCategoria) . '</td>';
                    $html .= '<td class="center" style="padding:6px;background:#E2E8F0 !important;border:1px solid #CCC;">—</td>';
                    $html .= '<td class="right" style="padding:6px;background:#E2E8F0 !important;border:1px solid #CCC;">R$ ' . self::formatarValor($totalConcluidoCategoria) . '</td>';
                    $html .= '<td class="center" style="padding:6px;background:#E2E8F0 !important;border:1px solid #CCC;">' . number_format($pctObraCategoria, 2, ',', '.') . '%</td>';
                    $html .= '<td class="right" style="padding:6px;background:#E2E8F0 !important;border:1px solid #CCC;">R$ ' . self::formatarValor($totalConcluidoCategoria) . '</td>';
                    $html .= '<td class="center" style="padding:6px;background:#E2E8F0 !important;border:1px solid #CCC;">' . number_format($pctSaldoCategoria, 2, ',', '.') . '%</td>';
                    $html .= '<td class="center" style="padding:6px;background:#E2E8F0 !important;border:1px solid #CCC;">—</td>';
                    $html .= '</tr>';
                }
                
                // Calcular totais do grupo para o subtotal
                $totalConcluidoGrupo = 0.0;
                foreach ($categorias as $categoria => $itensCategoria) {
                    foreach ($itensCategoria as $item) {
                        $quantidade = (float)($item['quantidade'] ?? 0);
                        $custoMat = (float)($item['custo_material'] ?? 0);
                        $custoMo = (float)($item['custo_mao_obra'] ?? 0);
                        $custoEquip = (float)($item['custo_equipamento'] ?? 0);
                        $percentualRealizado = (float)($item['percentual_realizado'] ?? 0);
                        
                        $usaMargemPersonalizada = (int)($item['usa_margem_personalizada'] ?? 0);
                        $margemPersonalizada = (float)($item['margem_personalizada'] ?? 0);
                        
                        $bdi = $usaMargemPersonalizada && $margemPersonalizada > 0 
                            ? $margemPersonalizada 
                            : $bdiGlobal;
                        
                        $fatorBDI = 1 + ($bdi / 100);
                        
                        $vlrUnitMat = ($custoMat + $custoEquip) * $fatorBDI;
                        $vlrUnitMo = $custoMo * $fatorBDI;
                        $vlrUnitTotal = $vlrUnitMat + $vlrUnitMo;
                        $vlrTotal = $vlrUnitTotal * $quantidade;
                        
                        $totalConcluidoGrupo += $vlrTotal * ($percentualRealizado / 100);
                    }
                }
                
                $percentualConcluidoGrupo = $subtotalGrupo > 0 
                    ? ($totalConcluidoGrupo / $subtotalGrupo) * 100 
                    : 0.0;
                
                $pctObraGrupo = $totalGeralObra > 0 ? ($totalConcluidoGrupo / $totalGeralObra) * 100 : 0.0;
                $pctSaldoGrupo = $saldoAPagar > 0 ? ($totalConcluidoGrupo / $saldoAPagar) * 100 : 0.0;
                
                // Linha de subtotal do GRUPO dentro da tabela
                $html .= '<tr style="background:#2C3E50 !important;color:#FFF !important;font-weight:bold;">';
                $html .= '<td colspan="7" class="left" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;border:1px solid #2C3E50 !important;">SUBTOTAL — ' . htmlspecialchars(strtoupper($nomeGrupo)) . '</td>';
                $html .= '<td class="right" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;border:1px solid #2C3E50 !important;">R$ ' . self::formatarValor($subtotalGrupo) . '</td>';
                $html .= '<td class="center" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;border:1px solid #2C3E50 !important;">—</td>';
                $html .= '<td class="right" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;border:1px solid #2C3E50 !important;">R$ ' . self::formatarValor($totalConcluidoGrupo) . '</td>';
                $html .= '<td class="center" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;border:1px solid #2C3E50 !important;">' . number_format($pctObraGrupo, 2, ',', '.') . '%</td>';
                $html .= '<td class="right" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;border:1px solid #2C3E50 !important;">R$ ' . self::formatarValor($totalConcluidoGrupo) . '</td>';
                $html .= '<td class="center" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;border:1px solid #2C3E50 !important;">' . number_format($pctSaldoGrupo, 2, ',', '.') . '%</td>';
                $html .= '<td class="center" style="padding:8px;background:#2C3E50 !important;color:#FFF !important;border:1px solid #2C3E50 !important;">—</td>';
                $html .= '</tr>';
                
                $html .= '</tbody></table>';
                
                $subtotalEtapa += $subtotalGrupo;
                $totalConcluidoEtapa += $totalConcluidoGrupo;
            }
            
            // Subtotal da ETAPA (após todos os grupos)
            $percentualConcluidoEtapa = $subtotalEtapa > 0 
                ? ($totalConcluidoEtapa / $subtotalEtapa) * 100 
                : 0.0;
            
            $pctObraEtapa = $totalGeralObra > 0 ? ($totalConcluidoEtapa / $totalGeralObra) * 100 : 0.0;
            $pctSaldoEtapa = ($totalGeralObra - $valorEntrada) > 0 ? ($totalConcluidoEtapa / ($totalGeralObra - $valorEntrada)) * 100 : 0.0;
            
            $html .= '<div style="background:#1a237e;color:#FFF;padding:10px;margin:10px 0;font-weight:bold;font-size:14px;">';
            $html .= 'SUBTOTAL — ' . htmlspecialchars(strtoupper($nomeEtapa));
            $html .= ' | Valor: R$ ' . self::formatarValor($subtotalEtapa);
            $html .= ' | Vlr Concluído: R$ ' . self::formatarValor($totalConcluidoEtapa);
            $html .= ' | % Obra: ' . number_format($pctObraEtapa, 2, ',', '.') . '%';
            $html .= ' | Vlr A Pagar: R$ ' . self::formatarValor($totalConcluidoEtapa);
            $html .= ' | % Saldo: ' . number_format($pctSaldoEtapa, 2, ',', '.') . '%';
            $html .= '</div>';
        }
        
        // Calcular totais gerais de todos os itens executados
        $totalConcluidoGeral = 0.0;
        foreach ($grouped as $etapa => $grupos) {
            foreach ($grupos as $grupo => $categorias) {
                foreach ($categorias as $categoria => $itensCategoria) {
                    foreach ($itensCategoria as $item) {
                        $quantidade = (float)($item['quantidade'] ?? 0);
                        $custoMat = (float)($item['custo_material'] ?? 0);
                        $custoMo = (float)($item['custo_mao_obra'] ?? 0);
                        $custoEquip = (float)($item['custo_equipamento'] ?? 0);
                        $percentualRealizado = (float)($item['percentual_realizado'] ?? 0);
                        
                        $usaMargemPersonalizada = (int)($item['usa_margem_personalizada'] ?? 0);
                        $margemPersonalizada = (float)($item['margem_personalizada'] ?? 0);
                        
                        $bdi = $usaMargemPersonalizada && $margemPersonalizada > 0 
                            ? $margemPersonalizada 
                            : $bdiGlobal;
                        
                        $fatorBDI = 1 + ($bdi / 100);
                        
                        $vlrUnitMat = ($custoMat + $custoEquip) * $fatorBDI;
                        $vlrUnitMo = $custoMo * $fatorBDI;
                        $vlrUnitTotal = $vlrUnitMat + $vlrUnitMo;
                        $vlrTotal = $vlrUnitTotal * $quantidade;
                        
                        $totalConcluidoGeral += $vlrTotal * ($percentualRealizado / 100);
                    }
                }
            }
        }
        
        $valorEntrada = (float)($orcamento['valor_entrada'] ?? 0);
        $saldoAPagar = $totalGeralObra - $valorEntrada;
        $pctObraGeral = $totalGeralObra > 0 ? ($totalConcluidoGeral / $totalGeralObra) * 100 : 0.0;
        $pctSaldoGeral = $saldoAPagar > 0 ? ($totalConcluidoGeral / $saldoAPagar) * 100 : 0.0;
        
        $html .= '<div style="margin-top:30px;padding:20px;background:#f8f9fa;border:2px solid #1a237e;">';
        $html .= '<div style="font-weight:700;font-size:18px;margin-bottom:15px;text-align:center;color:#1a237e;">RESUMO GERAL DE EXECUÇÃO</div>';
        $html .= '<table style="width:100%;border-collapse:collapse;">';
        $html .= '<tr style="background:#1a237e;color:#FFF;font-weight:bold;">';
        $html .= '<td style="padding:10px;border:1px solid #1a237e;">VALOR TOTAL DA OBRA</td>';
        $html .= '<td style="padding:10px;border:1px solid #1a237e;text-align:right;">R$ ' . self::formatarValor($totalGeralObra) . '</td>';
        $html .= '</tr>';
        $html .= '<tr style="background:#e3f2fd;">';
        $html .= '<td style="padding:10px;border:1px solid #ccc;">VALOR DE ENTRADA</td>';
        $html .= '<td style="padding:10px;border:1px solid #ccc;text-align:right;">R$ ' . self::formatarValor($valorEntrada) . '</td>';
        $html .= '</tr>';
        $html .= '<tr style="background:#fff;">';
        $html .= '<td style="padding:10px;border:1px solid #ccc;">SALDO A PAGAR</td>';
        $html .= '<td style="padding:10px;border:1px solid #ccc;text-align:right;">R$ ' . self::formatarValor($saldoAPagar) . '</td>';
        $html .= '</tr>';
        $html .= '<tr style="background:#c8e6c9;">';
        $html .= '<td style="padding:10px;border:1px solid #ccc;font-weight:bold;">VALOR TOTAL EXECUTADO</td>';
        $html .= '<td style="padding:10px;border:1px solid #ccc;text-align:right;font-weight:bold;">R$ ' . self::formatarValor($totalConcluidoGeral) . '</td>';
        $html .= '</tr>';
        $html .= '<tr style="background:#fff;">';
        $html .= '<td style="padding:10px;border:1px solid #ccc;">% DA OBRA EXECUTADA</td>';
        $html .= '<td style="padding:10px;border:1px solid #ccc;text-align:right;">' . number_format($pctObraGeral, 2, ',', '.') . '%</td>';
        $html .= '</tr>';
        $html .= '<tr style="background:#fff3cd;font-weight:bold;">';
        $html .= '<td style="padding:10px;border:1px solid #ccc;">VALOR A PAGAR (EXECUTADO)</td>';
        $html .= '<td style="padding:10px;border:1px solid #ccc;text-align:right;">R$ ' . self::formatarValor($totalConcluidoGeral) . '</td>';
        $html .= '</tr>';
        $html .= '<tr style="background:#fff;">';
        $html .= '<td style="padding:10px;border:1px solid #ccc;">% DO SALDO</td>';
        $html .= '<td style="padding:10px;border:1px solid #ccc;text-align:right;">' . number_format($pctSaldoGeral, 2, ',', '.') . '%</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</div>';
        
        $html .= '</div>';
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
        // Buscar BDI/margem global do orçamento (padrão 18%)
        $bdiGlobal = (float)($orcamento['margem_global'] ?? 18.0);
        
        $itens = \App\Models\OrcamentoItem::allByOrcamento($orcamentoId);
        
        // CORREÇÃO 3: Prazo de obra - buscar da fonte correta
        $prazo = $orcamento['prazo_meses'] ?? $orcamento['prazo'] ?? $orcamento['duracao'] ?? '';
        
        // Agrupar itens por GRUPO (usar os nomes reais do banco)
        $itensPorGrupo = [];
        foreach ($itens as $item) {
            $grupo = (string)($item['grupo'] ?? 'SEM GRUPO');
            if (!isset($itensPorGrupo[$grupo])) {
                $itensPorGrupo[$grupo] = [];
            }
            $itensPorGrupo[$grupo][] = $item;
        }

        $html = '<div class="page">';
        $html .= self::gerarHeaderPadrao($orcamento, 'PLANILHA ORÇAMENTÁRIA');

        $totalGeralObra = 0.0;
        $totaisPorGrupo = [];
        
        // CORREÇÃO 1: Controle de itens processados para evitar duplicação
        $itensProcessados = [];

        // Processar cada grupo na ordem em que aparecem
        foreach ($itensPorGrupo as $nomeGrupo => $itensGrupo) {
            if (empty($itensGrupo)) {
                continue;
            }
            
            // Separador de grupo (ETAPA)
            $html .= '<div class="banner-etapa">' . htmlspecialchars(strtoupper($nomeGrupo)) . '</div>';
            
            $subtotalGrupo = 0.0;
            
            // Tabela com 8 colunas
            $html .= '<table class="table-detalhes">';
            $html .= '<thead><tr>';
            $html .= '<th class="left" style="width:8%;">ITEM</th>';
            $html .= '<th class="left" style="width:30%;">DESCRIÇÃO</th>';
            $html .= '<th class="center" style="width:6%;">UNID.</th>';
            $html .= '<th class="center" style="width:8%;">QUANT.</th>';
            $html .= '<th class="right" style="width:12%;">VALOR UNIT. MAT.</th>';
            $html .= '<th class="right" style="width:12%;">VALOR UNIT. M.O.</th>';
            $html .= '<th class="right" style="width:12%;">VALOR UNITÁRIO TOTAL</th>';
            $html .= '<th class="right" style="width:12%;">VALOR TOTAL</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($itensGrupo as $item) {
                // CORREÇÃO 1: Verificar se item já foi processado
                $chaveItem = ($item['codigo'] ?? '') . '_' . $nomeGrupo;
                if (isset($itensProcessados[$chaveItem])) {
                    continue;
                }
                $itensProcessados[$chaveItem] = true;
                
                // ── PASSO 1: buscar custos unitários do banco (já unitários)
                $custoMatUnit = (float)($item['custo_material'] ?? 0);
                $custoMoUnit = (float)($item['custo_mao_obra'] ?? 0);
                $custoEquipUnit = (float)($item['custo_equipamento'] ?? 0);
                $quantidade = (float)($item['quantidade'] ?? 0);
                $valorCobranca = (float)($item['valor_cobranca'] ?? 0);
                
                // CORREÇÃO 4: Validar se custos estão inflados (totais em vez de unitários)
                // Se custo unitário > valor de cobrança, provavelmente é total, então dividir
                if ($valorCobranca > 0 && $quantidade > 0) {
                    if ($custoMatUnit > $valorCobranca) {
                        \App\Core\Logger::warning('orcamentos.pdf.custo_material_inflado', [
                            'item_id' => $item['id'] ?? 0,
                            'codigo' => $item['codigo'] ?? '',
                            'custo_original' => $custoMatUnit,
                            'valor_cobranca' => $valorCobranca,
                            'quantidade' => $quantidade
                        ]);
                        $custoMatUnit = $custoMatUnit / $quantidade;
                    }
                    
                    if ($custoMoUnit > $valorCobranca) {
                        \App\Core\Logger::warning('orcamentos.pdf.custo_mo_inflado', [
                            'item_id' => $item['id'] ?? 0,
                            'codigo' => $item['codigo'] ?? '',
                            'custo_original' => $custoMoUnit,
                            'valor_cobranca' => $valorCobranca,
                            'quantidade' => $quantidade
                        ]);
                        $custoMoUnit = $custoMoUnit / $quantidade;
                    }
                    
                    if ($custoEquipUnit > $valorCobranca) {
                        \App\Core\Logger::warning('orcamentos.pdf.custo_equip_inflado', [
                            'item_id' => $item['id'] ?? 0,
                            'codigo' => $item['codigo'] ?? '',
                            'custo_original' => $custoEquipUnit,
                            'valor_cobranca' => $valorCobranca,
                            'quantidade' => $quantidade
                        ]);
                        $custoEquipUnit = $custoEquipUnit / $quantidade;
                    }
                }
                
                // ── PASSO 2: definir BDI (margem personalizada ou global)
                $usaMargemPersonalizada = (int)($item['usa_margem_personalizada'] ?? 0);
                $margemPersonalizada = (float)($item['margem_personalizada'] ?? 0);
                
                $bdi = $usaMargemPersonalizada && $margemPersonalizada > 0 
                    ? $margemPersonalizada 
                    : $bdiGlobal;
                
                $fatorBDI = 1 + ($bdi / 100); // ex: 1.18
                
                // ── PASSO 3: calcular preço de venda unitário por tipo
                // Equipamento incorporado ao material
                $vlrUnitMaterial = ($custoMatUnit + $custoEquipUnit) * $fatorBDI;
                $vlrUnitMO = $custoMoUnit * $fatorBDI;
                
                // ── PASSO 4: total unitário e total da linha
                $vlrUnitTotal = $vlrUnitMaterial + $vlrUnitMO;
                $vlrTotal = $vlrUnitTotal * $quantidade;
                
                // Validação: não permitir valores negativos
                if ($vlrUnitMaterial < 0 || $vlrUnitMO < 0 || $vlrUnitTotal < 0 || $vlrTotal < 0) {
                    \App\Core\Logger::warning('orcamentos.pdf.valor_negativo', [
                        'item_id' => $item['id'] ?? 0,
                        'codigo' => $item['codigo'] ?? '',
                        'vlrUnitMaterial' => $vlrUnitMaterial,
                        'vlrUnitMO' => $vlrUnitMO,
                        'vlrTotal' => $vlrTotal
                    ]);
                    // Forçar zero se negativo
                    $vlrUnitMaterial = max(0, $vlrUnitMaterial);
                    $vlrUnitMO = max(0, $vlrUnitMO);
                    $vlrUnitTotal = max(0, $vlrUnitTotal);
                    $vlrTotal = max(0, $vlrTotal);
                }
                
                // ── PASSO 5: acumular subtotal do grupo
                $subtotalGrupo += $vlrTotal;
                
                // ── PASSO 6: formatar valores para exibição
                // Regra: se valor == 0, exibir "—" (traço)
                $displayMatUnit = $vlrUnitMaterial > 0.001 
                    ? number_format($vlrUnitMaterial, 2, ',', '.') 
                    : '—';
                
                $displayMoUnit = $vlrUnitMO > 0.001 
                    ? number_format($vlrUnitMO, 2, ',', '.') 
                    : '—';
                
                $displayUnitTotal = $vlrUnitTotal > 0.001 
                    ? number_format($vlrUnitTotal, 2, ',', '.') 
                    : '—';
                
                $displayTotal = $vlrTotal > 0.001 
                    ? number_format($vlrTotal, 2, ',', '.') 
                    : '—';
                
                // ── PASSO 7: renderizar linha
                $html .= '<tr>';
                $html .= '<td class="left">' . htmlspecialchars((string)($item['codigo'] ?? '')) . '</td>';
                $html .= '<td class="left">' . nl2br(htmlspecialchars((string)($item['descricao'] ?? ''))) . '</td>';
                $html .= '<td class="center">' . htmlspecialchars((string)($item['unidade'] ?? '')) . '</td>';
                $html .= '<td class="center">' . number_format($quantidade, 2, ',', '.') . '</td>';
                $html .= '<td class="right">' . $displayMatUnit . '</td>';
                $html .= '<td class="right">' . $displayMoUnit . '</td>';
                $html .= '<td class="right">' . $displayUnitTotal . '</td>';
                $html .= '<td class="right">' . $displayTotal . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
            
            // Linha de subtotal do grupo
            $html .= '<div class="subtotal-item">';
            $html .= 'SUBTOTAL — ' . htmlspecialchars(strtoupper($nomeGrupo)) . ': ';
            $html .= 'R$ ' . self::formatarValor($subtotalGrupo);
            $html .= '</div>';
            
            $totalGeralObra += $subtotalGrupo;
            $totaisPorGrupo[$nomeGrupo] = $subtotalGrupo;
        }

        // Rodapé com totais por grupo
        $html .= '<div style="margin-top:30px; padding:20px; background:rgba(255,255,255,0.02); border-radius:8px;">';
        
        foreach ($totaisPorGrupo as $nomeGrupo => $valorGrupo) {
            $html .= '<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.1);">';
            $html .= '<span>SUBTOTAL - ' . htmlspecialchars(strtoupper($nomeGrupo)) . '</span>';
            $html .= '<span>R$ ' . self::formatarValor($valorGrupo) . '</span>';
            $html .= '</div>';
        }
        
        // Custos administrativos e impostos
        $percentualCustosAdm = (float)($orcamento['percentual_custos_adm'] ?? 0);
        $percentualImpostos = (float)($orcamento['percentual_impostos'] ?? 0);
        
        if ($percentualCustosAdm > 0 || $percentualImpostos > 0) {
            $valorCustosAdm = $totalGeralObra * ($percentualCustosAdm / 100);
            $valorImpostos = $totalGeralObra * ($percentualImpostos / 100);
            $valorTaxas = $valorCustosAdm + $valorImpostos;
            
            $html .= '<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.1);">';
            $html .= '<span>SUBTOTAL - TAXA DE ADMINISTRAÇÃO + IMPOSTOS</span>';
            $html .= '<span>R$ ' . self::formatarValor($valorTaxas) . '</span>';
            $html .= '</div>';
            
            $totalGeralObra += $valorTaxas;
        }
        
        // Total geral
        $html .= '<div style="display:flex; justify-content:space-between; padding:12px 0; margin-top:8px; border-top:2px solid rgba(255,255,255,0.3); font-weight:800; font-size:16px;">';
        $html .= '<span>VALOR TOTAL GERAL</span>';
        $html .= '<span>R$ ' . self::formatarValor($totalGeralObra) . '</span>';
        $html .= '</div>';
        
        $html .= '</div>';
        
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
            $custoEquipamento = (float)($item['custo_equipamento'] ?? 0);
            $valorCobranca = (float)($item['valor_cobranca'] ?? 0);
            
            $totalCobranca += $quantidade * $valorCobranca;
            
            // Somar TODOS os custos de TODOS os itens
            $totalMateriais += $quantidade * $custoMaterial;
            $totalMaoObra += $quantidade * $custoMaoObra;
            $totalEquipamentos += $quantidade * $custoEquipamento;
        }
        
        // Soma dos custos (base para calcular percentuais)
        $somaCustos = $totalMateriais + $totalMaoObra + $totalEquipamentos;
        
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
        $html .= '<td class="left">Total em Materiais</td>';
        $html .= '<td class="right">R$ ' . self::formatarValor($totalMateriais) . '</td>';
        $html .= '<td class="center">' . number_format($somaCustos > 0 ? ($totalMateriais/$somaCustos)*100 : 0, 2, ',', '.') . '%</td>';
        $html .= '</tr>';
        
        $html .= '<tr>';
        $html .= '<td class="left">Total em Mão de Obra</td>';
        $html .= '<td class="right">R$ ' . self::formatarValor($totalMaoObra) . '</td>';
        $html .= '<td class="center">' . number_format($somaCustos > 0 ? ($totalMaoObra/$somaCustos)*100 : 0, 2, ',', '.') . '%</td>';
        $html .= '</tr>';
        
        $html .= '<tr>';
        $html .= '<td class="left">Total em Equipamentos</td>';
        $html .= '<td class="right">R$ ' . self::formatarValor($totalEquipamentos) . '</td>';
        $html .= '<td class="center">' . number_format($somaCustos > 0 ? ($totalEquipamentos/$somaCustos)*100 : 0, 2, ',', '.') . '%</td>';
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
        
        $html .= '</div>';
        return $html;
    }
}
