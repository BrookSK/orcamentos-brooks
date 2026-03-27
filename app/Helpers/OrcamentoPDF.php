<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\OrcamentoItem;
use App\Models\OrcamentoCor;

final class OrcamentoPDF
{
    /**
     * Gera HTML completo para exportação em PDF
     * Estrutura: Apresentação → Resumo Executivo → Detalhamento Completo
     */
    public static function gerarHTML(int $orcamentoId, array $orcamento): string
    {
        $html = self::gerarCabecalhoHTML();
        $html .= self::gerarPaginaApresentacao($orcamento);
        $html .= self::gerarPaginaApresentacaoInstitucional();
        $html .= self::gerarPaginaExpertise();
        $html .= self::gerarPaginaObjetoProposta();
        $html .= self::gerarPaginaResumoExecutivo($orcamentoId, $orcamento);
        $html .= self::gerarPaginaDetalhamento($orcamentoId, $orcamento);
        $html .= self::gerarRodapeHTML();
        
        return $html;
    }
    
    /**
     * Gera cabeçalho HTML com estilos CSS modernos
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        .page {
            page-break-after: always;
            position: relative;
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            background: white;
        }
        
        .page-cover {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .logo-area {
            margin-bottom: 60px;
        }
        
        .logo-text {
            font-size: 48px;
            font-weight: 700;
            letter-spacing: 3px;
            color: #fff;
            text-transform: uppercase;
        }
        
        .logo-subtitle {
            font-size: 16px;
            letter-spacing: 2px;
            color: #e94560;
            margin-top: 10px;
        }
        
        .cover-title {
            font-size: 42px;
            font-weight: 300;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }
        
        .cover-subtitle {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 40px;
            color: #e94560;
        }
        
        .cover-info {
            font-size: 18px;
            margin: 10px 0;
            opacity: 0.9;
        }
        
        .section-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 4px solid #e94560;
        }
        
        .intro-text {
            font-size: 14px;
            line-height: 1.8;
            text-align: justify;
            margin-bottom: 30px;
            color: #444;
        }
        
        .intro-signature {
            font-weight: 600;
            color: #1a1a2e;
            margin-top: 20px;
        }
        
        .institutional-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .institutional-item {
            font-size: 13px;
            padding-left: 20px;
            position: relative;
            color: #555;
        }
        
        .institutional-item:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #e94560;
            font-weight: bold;
            font-size: 18px;
        }
        
        .expertise-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        
        .expertise-box {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            border-left: 4px solid #e94560;
        }
        
        .expertise-box h3 {
            font-size: 18px;
            color: #1a1a2e;
            margin-bottom: 15px;
        }
        
        .expertise-box p {
            font-size: 13px;
            line-height: 1.7;
            color: #555;
        }
        
        .proposta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }
        
        .proposta-box {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
        }
        
        .proposta-box h3 {
            font-size: 16px;
            color: #1a1a2e;
            margin-bottom: 10px;
        }
        
        .proposta-box p {
            font-size: 13px;
            color: #666;
        }
        
        .nota-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 15px 0;
            border-radius: 4px;
        }
        
        .nota-box h4 {
            font-size: 14px;
            color: #856404;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .nota-box p {
            font-size: 12px;
            color: #856404;
            line-height: 1.6;
        }
        
        .resumo-financeiro {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .resumo-financeiro h2 {
            font-size: 28px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .resumo-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .resumo-card {
            background: rgba(255, 255, 255, 0.15);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            backdrop-filter: blur(10px);
        }
        
        .resumo-card-label {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .resumo-card-value {
            font-size: 24px;
            font-weight: 700;
        }
        
        .table-resumo {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table-resumo th {
            background: #1a1a2e;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }
        
        .table-resumo td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }
        
        .table-resumo tr:hover {
            background: #f8f9fa;
        }
        
        .table-resumo .total-row {
            background: #f8f9fa;
            font-weight: 700;
            font-size: 15px;
        }
        
        .etapa-section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        
        .etapa-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
        }
        
        .etapa-title {
            font-size: 22px;
            font-weight: 600;
            margin: 0;
        }
        
        .grupo-section {
            background: white;
            border: 1px solid #e0e0e0;
            border-top: none;
            padding: 25px;
        }
        
        .grupo-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e94560;
        }
        
        .categoria-title {
            font-size: 14px;
            font-weight: 600;
            color: #2196F3;
            margin: 20px 0 10px 0;
            display: flex;
            align-items: center;
        }
        
        .categoria-title.mao-obra {
            color: #4CAF50;
        }
        
        .table-itens {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        .table-itens th {
            background: #f8f9fa;
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #dee2e6;
            font-size: 11px;
        }
        
        .table-itens td {
            padding: 8px;
            border: 1px solid #dee2e6;
        }
        
        .table-itens tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .resumo-grupo {
            background: #e3f2fd;
            padding: 12px;
            border-radius: 5px;
            display: flex;
            justify-content: flex-end;
            gap: 25px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .resumo-etapa {
            background: #1a1a2e;
            color: white;
            padding: 15px 25px;
            border-radius: 0 0 8px 8px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 600;
        }
        
        .total-final {
            background: linear-gradient(135deg, #e94560 0%, #d63447 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-top: 40px;
        }
        
        .total-final h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        .total-final-value {
            font-size: 48px;
            font-weight: 700;
            margin: 20px 0;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .color-material {
            color: #2196F3;
        }
        
        .color-mao-obra {
            color: #4CAF50;
        }
        
        .page-number {
            position: absolute;
            bottom: 15mm;
            right: 20mm;
            font-size: 11px;
            color: #999;
        }
    </style>
</head>
<body>
HTML;
    }
    
    /**
     * Gera página de capa/apresentação
     */
    private static function gerarPaginaApresentacao(array $orcamento): string
    {
        $numeroProposta = htmlspecialchars($orcamento['numero_proposta'] ?? '');
        $obraNome = htmlspecialchars($orcamento['obra_nome'] ?? '');
        $clienteNome = htmlspecialchars($orcamento['cliente_nome'] ?? '');
        $endereco = htmlspecialchars($orcamento['endereco_obra'] ?? '');
        $data = date('d/m/Y', strtotime($orcamento['data'] ?? 'now'));
        
        return <<<HTML
<div class="page page-cover">
    <div class="logo-area">
        <div class="logo-text">BROOKS</div>
        <div class="logo-subtitle">CONSTRUTORA</div>
    </div>
    
    <div class="cover-title">PROPOSTA ORÇAMENTÁRIA</div>
    <div class="cover-subtitle">{$obraNome}</div>
    
    <div style="margin-top: 60px;">
        <div class="cover-info"><strong>Proposta:</strong> {$numeroProposta}</div>
        <div class="cover-info"><strong>Cliente:</strong> {$clienteNome}</div>
        <div class="cover-info"><strong>Endereço:</strong> {$endereco}</div>
        <div class="cover-info"><strong>Data:</strong> {$data}</div>
    </div>
</div>
HTML;
    }
    
    /**
     * Gera página "Prezados" com texto de apresentação
     */
    private static function gerarPaginaApresentacaoInstitucional(): string
    {
        return <<<'HTML'
<div class="page">
    <h1 class="section-title">Prezados</h1>
    
    <p class="intro-text">
        Agradecemos imensamente o seu contato! Temos certeza de que esta proposta trará soluções inovadoras e valor para o 
        seu projeto. Nas próximas páginas, apresentamos nossa empresa, detalhamos os serviços oferecidos, nossos valores e 
        observações importantes. Estamos à sua inteira disposição para qualquer esclarecimento ou para darmos o próximo passo.
    </p>
    
    <p class="intro-signature">Atenciosamente, <strong>BROOKS CONSTRUTORA</strong>.</p>
    
    <h2 class="section-title" style="margin-top: 60px;">Apresentação Institucional</h2>
    
    <p class="intro-text">
        A <strong>Brooks Construtora</strong> é uma empresa especializada na execução de obras civis, reformas integrais e retrofit de imóveis 
        residenciais, corporativos e comerciais de médio porte.
    </p>
    
    <p class="intro-text">
        Atuamos no segmento de construção civil por meio da execução completa de sistemas construtivos, contemplando:
    </p>
    
    <div class="institutional-grid">
        <div class="institutional-item">Demolições técnicas</div>
        <div class="institutional-item">Infraestrutura para automação</div>
        <div class="institutional-item">Alvenaria estrutural e de vedação</div>
        <div class="institutional-item">Infraestrutura para climatização e ar condicionado</div>
        <div class="institutional-item">Impermeabilizações</div>
        <div class="institutional-item">Cálculo estrutural e reforços</div>
        <div class="institutional-item">Instalação de básicos e revestimentos</div>
        <div class="institutional-item">Coberturas e fundações</div>
        <div class="institutional-item">Execução de meia esquadria</div>
        <div class="institutional-item">Pinturas técnicas e acabamentos especiais</div>
        <div class="institutional-item">Pisos e sistemas em drywall</div>
        <div class="institutional-item">Enxaguamento, logística e descarte de entulho</div>
        <div class="institutional-item">Instalações elétricas, luminotécnicas e hidráulicas</div>
        <div class="institutional-item">Instalação de louças, metais e acessórios</div>
        <div class="institutional-item">Estrutura metálica e madeira (PEX)</div>
        <div class="institutional-item">Suprimentos e frotas de materiais básicos de obra</div>
        <div class="institutional-item"></div>
        <div class="institutional-item">Demais serviços correlatos à engenharia civil</div>
    </div>
    
    <div class="page-number">Página 2</div>
</div>
HTML;
    }
    
    /**
     * Gera página "Nossa Expertise"
     */
    private static function gerarPaginaExpertise(): string
    {
        return <<<'HTML'
<div class="page">
    <h1 class="section-title">Nossa Expertise</h1>
    
    <p class="intro-text">
        Contamos com equipes especializadas de alta performance, coordenadas por engenheiro civil responsável técnico, 
        garantindo conformidade normativa, controle de qualidade de processos e cumprimento rigoroso de prazos.
    </p>
    
    <p class="intro-text">
        Possuímos centenas de obras entregues ao longo de quase uma década, mantendo parcerias consolidadas com escritórios 
        de arquitetura e fornecedores premium na cidade de São Paulo.
    </p>
    
    <div class="expertise-grid">
        <div class="expertise-box">
            <h3>Reconhecimento</h3>
            <p>
                Nossos empreendimentos já foram publicados em veículos como <strong>Casa Vogue, Casa e Jardim, Diário do 
                Arquiteto, De.cor.ar</strong>, entre outras mídias especializadas.
            </p>
        </div>
        
        <div class="expertise-box">
            <h3>Compromisso</h3>
            <p>
                Nossa prioridade permanece focada na excelência técnica, ética profissional, transparência contratual e 
                satisfação integral do cliente.
            </p>
        </div>
    </div>
    
    <div class="page-number">Página 3</div>
</div>
HTML;
    }
    
    /**
     * Gera página "Objeto da Proposta"
     */
    private static function gerarPaginaObjetoProposta(): string
    {
        return <<<'HTML'
<div class="page">
    <h1 class="section-title">Objeto da Proposta</h1>
    
    <p class="intro-text">
        A presente proposta tem como objeto a prestação de serviços de obra civil, incluindo:
    </p>
    
    <div class="proposta-grid">
        <div class="proposta-box">
            <h3>Execução Técnica</h3>
            <p>Execução técnica integral dos serviços descritos nas etapas seguintes.</p>
        </div>
        
        <div class="proposta-box">
            <h3>Documentação</h3>
            <p>Emissão de ART de execução</p>
        </div>
        
        <div class="proposta-box">
            <h3>Proteção</h3>
            <p>Seguro de obra</p>
        </div>
        
        <div class="proposta-box">
            <h3>Gestão</h3>
            <p>Coordenação operacional</p>
        </div>
    </div>
    
    <p class="intro-text" style="margin-top: 30px;">
        Todos os serviços serão executados exclusivamente conforme os descritivos técnicos desta proposta.
    </p>
    
    <p class="intro-text">
        Nesta são previstas como objeto de orçamento complementar:
    </p>
    
    <ul style="margin-left: 30px; margin-bottom: 20px;">
        <li style="margin-bottom: 8px;">Projetos complementares e executivos</li>
        <li style="margin-bottom: 8px;">Cumprir legislação trabalhista e sanitária</li>
        <li style="margin-bottom: 8px;">Adotar práticas de segurança do trabalho</li>
        <li style="margin-bottom: 8px;">Manter equipe técnica qualificada</li>
    </ul>
    
    <h2 class="section-title" style="margin-top: 40px;">Notas Técnicas Gerais</h2>
    
    <div class="nota-box">
        <h4>⚠️ Nota 1 – Sistemas especiais</h4>
        <p>
            Não estão inclusos: <strong>Pressurização, Aquecimento, Automação, Energia Fotovoltaica, Bombeamento, Piscinas, 
            Sistemas gerais, Cisternas, Sistemas complexos correlatos</strong>.
        </p>
        <p style="margin-top: 10px;">
            Todos os itens não relacionados aos sistemas propostos de engenharia especializada contratados pelo cliente.
        </p>
    </div>
    
    <div class="nota-box" style="background: #d1ecf1; border-left-color: #0c5460;">
        <h4 style="color: #0c5460;">ℹ️ Nota 2 – Sistemas especiais</h4>
        <p style="color: #0c5460;">
            O orçamento foi elaborado com base no projeto arquitetônico fornecido. Limitações físicas, centurianas, 
            condominiais ou pré-existências poderão gerar imobilidades técnicas, adaptações executivas, revisões 
            orçamentárias.
        </p>
        <p style="margin-top: 10px; color: #0c5460;">
            A compatibilização entre arquitetura, engenharia e execução é indispensável.
        </p>
    </div>
    
    <div class="page-number">Página 4</div>
</div>
HTML;
    }
    
    /**
     * Gera página de Resumo Executivo
     */
    private static function gerarPaginaResumoExecutivo(int $orcamentoId, array $orcamento): string
    {
        $totaisGerais = OrcamentoItem::getTotaisGerais($orcamentoId);
        $resumoEtapas = OrcamentoItem::getResumoEtapas($orcamentoId);
        
        $totalMaterial = (float)$totaisGerais['total_material'];
        $totalMaoObra = (float)$totaisGerais['total_mao_obra'];
        $totalGeral = (float)$totaisGerais['total_cobranca'];
        
        // Informações de adequação
        $adequacaoAplicada = (bool)($orcamento['adequacao_aplicada'] ?? false);
        $valorOriginal = (float)($orcamento['valor_original'] ?? 0);
        $valorAdequado = (float)($orcamento['valor_adequado'] ?? 0);
        $fatorAdequacao = (float)($orcamento['fator_adequacao'] ?? 1.0);
        
        $html = <<<HTML
<div class="page">
    <h1 class="section-title">Resumo Executivo</h1>
    
    <div class="resumo-financeiro">
        <h2>Visão Geral de Custos</h2>
        
        <div class="resumo-cards">
            <div class="resumo-card">
                <div class="resumo-card-label">📦 Custo Previsto (Material)</div>
                <div class="resumo-card-value">R$ {self::formatarValor($totalMaterial)}</div>
            </div>
            <div class="resumo-card">
                <div class="resumo-card-label">👷 Custo Efetivo (Mão de Obra)</div>
                <div class="resumo-card-value">R$ {self::formatarValor($totalMaoObra)}</div>
            </div>
            <div class="resumo-card">
                <div class="resumo-card-label">💰 Valor Total do Projeto</div>
                <div class="resumo-card-value">R$ {self::formatarValor($totalGeral)}</div>
            </div>
        </div>
HTML;
        
        // Adicionar informações de adequação se aplicada
        if ($adequacaoAplicada && $valorOriginal > 0) {
            $percentualAjuste = (($fatorAdequacao - 1) * 100);
            $tipoAjuste = $percentualAjuste >= 0 ? 'aumento' : 'redução';
            $corAjuste = $percentualAjuste >= 0 ? '#28a745' : '#dc3545';
            
            $html .= sprintf(
                '<div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 5px;">'
                . '<h3 style="font-size: 16px; color: #856404; margin-bottom: 15px;">💰 Adequação de Valores Aplicada</h3>'
                . '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; font-size: 13px; color: #856404;">'
                . '<div><strong>Valor Original:</strong><br>R$ %s</div>'
                . '<div><strong>Valor Adequado:</strong><br>R$ %s</div>'
                . '<div><strong>Ajuste:</strong><br><span style="color: %s; font-weight: bold;">%+.2f%% (%s)</span></div>'
                . '</div>'
                . '</div>',
                self::formatarValor($valorOriginal),
                self::formatarValor($valorAdequado),
                $corAjuste,
                $percentualAjuste,
                $tipoAjuste
            );
        }
        
        $html .= <<<HTML
        
        <table class="table-resumo">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Etapa</th>
                    <th class="text-right">Custo Previsto</th>
                    <th class="text-right">Custo Efetivo</th>
                    <th class="text-right">Valor Total</th>
                    <th class="text-right">% do Total</th>
                </tr>
            </thead>
            <tbody>
HTML;
        
        $numero = 1;
        foreach ($resumoEtapas as $etapa) {
            $valorEtapa = (float)$etapa['total_cobranca'];
            $percentual = $totalGeral > 0 ? ($valorEtapa / $totalGeral) * 100 : 0;
            $corInfo = OrcamentoCor::getCorPorEtapa($etapa['etapa']);
            
            $html .= sprintf(
                '<tr>'
                . '<td>%d</td>'
                . '<td><span style="color: %s;">%s</span> %s</td>'
                . '<td class="text-right color-material">R$ %s</td>'
                . '<td class="text-right color-mao-obra">R$ %s</td>'
                . '<td class="text-right"><strong>R$ %s</strong></td>'
                . '<td class="text-right">%.2f%%</td>'
                . '</tr>',
                $numero++,
                $corInfo['cor'],
                $corInfo['icone'] ?? '●',
                htmlspecialchars($etapa['etapa']),
                self::formatarValor((float)$etapa['total_material']),
                self::formatarValor((float)$etapa['total_mao_obra']),
                self::formatarValor($valorEtapa),
                $percentual
            );
        }
        
        $html .= sprintf(
            '<tr class="total-row">'
            . '<td colspan="2">TOTAL GERAL</td>'
            . '<td class="text-right color-material">R$ %s</td>'
            . '<td class="text-right color-mao-obra">R$ %s</td>'
            . '<td class="text-right">R$ %s</td>'
            . '<td class="text-right">100,00%%</td>'
            . '</tr>',
            self::formatarValor($totalMaterial),
            self::formatarValor($totalMaoObra),
            self::formatarValor($totalGeral)
        );
        
        $html .= <<<HTML
            </tbody>
        </table>
    </div>
    
    <div class="page-number">Página 5</div>
</div>
HTML;
        
        return $html;
    }
    
    /**
     * Gera páginas de detalhamento completo
     */
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
        $html .= '<h1 class="section-title">Detalhamento Completo</h1>';

        $paginaAtual = 6;

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

            $html .= sprintf('</div><div class="page"><div class="page-number">Página %d</div>', $paginaAtual++);
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

        $html .= '</div>';

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
            // Se item tem 5% do total e está 100% realizado, contribui 5% para o total realizado
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
