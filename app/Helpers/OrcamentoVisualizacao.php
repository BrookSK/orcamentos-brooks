<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\OrcamentoItem;
use App\Models\OrcamentoCor;

final class OrcamentoVisualizacao
{
    /**
     * Renderiza o orçamento completo com cores e agrupamentos
     */
    public static function renderOrcamentoCompleto(int $orcamentoId, array $orcamento): string
    {
        $resumoEtapas = OrcamentoItem::getResumoEtapas($orcamentoId);
        $totaisGerais = OrcamentoItem::getTotaisGerais($orcamentoId);
        
        $html = self::renderCabecalho($orcamento);
        $html .= self::renderResumoFinanceiro($resumoEtapas, $totaisGerais);
        
        foreach ($resumoEtapas as $etapa) {
            $html .= self::renderEtapa($orcamentoId, $etapa);
        }
        
        $html .= self::renderTotalGeral($totaisGerais);
        
        return $html;
    }

    /**
     * Renderiza o cabeçalho do orçamento
     */
    private static function renderCabecalho(array $orcamento): string
    {
        return sprintf(
            '<div class="orcamento-cabecalho" style="background: linear-gradient(135deg, #667eea 0%%, #764ba2 100%%); color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px;">'
            . '<h1 style="margin: 0 0 20px 0; font-size: 28px;">PROPOSTA ORÇAMENTÁRIA | %s</h1>'
            . '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; font-size: 14px;">'
            . '<div><strong>Projeto:</strong> %s</div>'
            . '<div><strong>Revisão:</strong> %s</div>'
            . '<div><strong>Cliente:</strong> %s</div>'
            . '<div><strong>Data:</strong> %s</div>'
            . '<div><strong>Endereço:</strong> %s</div>'
            . '<div><strong>Área Total:</strong> %.2f m²</div>'
            . '<div><strong>Prazo de Obra:</strong> %s</div>'
            . '<div><strong>Fonte:</strong> %s</div>'
            . '</div>'
            . '</div>',
            htmlspecialchars($orcamento['obra_nome'] ?? ''),
            htmlspecialchars($orcamento['numero_proposta'] ?? ''),
            htmlspecialchars($orcamento['revisao'] ?? 'R00'),
            htmlspecialchars($orcamento['cliente_nome'] ?? ''),
            date('d/m/Y', strtotime($orcamento['data'] ?? 'now')),
            htmlspecialchars($orcamento['endereco_obra'] ?? ''),
            (float)($orcamento['area_m2'] ?? 0),
            htmlspecialchars($orcamento['prazo_obra'] ?? ''),
            htmlspecialchars($orcamento['fonte'] ?? '')
        );
    }

    /**
     * Renderiza o resumo financeiro
     */
    private static function renderResumoFinanceiro(array $resumoEtapas, array $totaisGerais): string
    {
        $totalGeral = (float)($totaisGerais['total_cobranca'] ?? 0);
        
        $html = '<div class="resumo-financeiro" style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">';
        $html .= '<h2 style="margin: 0 0 20px 0; color: #333; border-bottom: 3px solid #667eea; padding-bottom: 10px;">RESUMO FINANCEIRO</h2>';
        $html .= '<table style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr style="background: #f5f5f5;">';
        $html .= '<th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">#</th>';
        $html .= '<th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Bloco / Etapa</th>';
        $html .= '<th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">Custo Previsto (Mat.)</th>';
        $html .= '<th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">Custo Efetivo (M.O)</th>';
        $html .= '<th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">Valor (R$)</th>';
        $html .= '<th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">% do Total</th>';
        $html .= '</tr></thead><tbody>';
        
        $numero = 1;
        foreach ($resumoEtapas as $etapa) {
            $corInfo = OrcamentoCor::getCorPorEtapa($etapa['etapa']);
            $percentual = $totalGeral > 0 ? ((float)$etapa['total_cobranca'] / $totalGeral) * 100 : 0;
            
            $html .= sprintf(
                '<tr style="border-bottom: 1px solid #eee;">'
                . '<td style="padding: 12px; font-weight: bold;">%d</td>'
                . '<td style="padding: 12px;"><span style="display: inline-block; width: 12px; height: 12px; background: %s; border-radius: 50%%; margin-right: 8px;"></span>%s %s</td>'
                . '<td style="padding: 12px; text-align: right; color: #2196F3; font-weight: bold;">R$ %s</td>'
                . '<td style="padding: 12px; text-align: right; color: #4CAF50; font-weight: bold;">R$ %s</td>'
                . '<td style="padding: 12px; text-align: right; font-weight: bold;">R$ %s</td>'
                . '<td style="padding: 12px; text-align: right;">%.2f%%</td>'
                . '</tr>',
                $numero++,
                $corInfo['cor'],
                $corInfo['icone'] ?? '',
                htmlspecialchars($etapa['etapa']),
                number_format((float)$etapa['total_material'], 2, ',', '.'),
                number_format((float)$etapa['total_mao_obra'], 2, ',', '.'),
                number_format((float)$etapa['total_cobranca'], 2, ',', '.'),
                $percentual
            );
        }
        
        $html .= '<tr style="background: #f9f9f9; font-weight: bold; font-size: 16px;">';
        $html .= '<td colspan="2" style="padding: 15px;">TOTAL GERAL</td>';
        $html .= sprintf(
            '<td style="padding: 15px; text-align: right; color: #2196F3;">R$ %s</td>',
            number_format((float)$totaisGerais['total_material'], 2, ',', '.')
        );
        $html .= sprintf(
            '<td style="padding: 15px; text-align: right; color: #4CAF50;">R$ %s</td>',
            number_format((float)$totaisGerais['total_mao_obra'], 2, ',', '.')
        );
        $html .= sprintf(
            '<td style="padding: 15px; text-align: right;">R$ %s</td>',
            number_format($totalGeral, 2, ',', '.')
        );
        $html .= '<td style="padding: 15px; text-align: right;">100,00%</td>';
        $html .= '</tr>';
        
        $html .= '</tbody></table></div>';
        
        return $html;
    }

    /**
     * Renderiza uma etapa completa com seus grupos
     */
    private static function renderEtapa(int $orcamentoId, array $etapa): string
    {
        $corInfo = OrcamentoCor::getCorPorEtapa($etapa['etapa']);
        $itensAgrupados = OrcamentoItem::getItensAgrupadosPorFinalidade($orcamentoId, $etapa['etapa']);
        
        $html = sprintf(
            '<div class="etapa" style="margin-bottom: 30px; border-left: 5px solid %s;">'
            . '<div style="background: %s; color: white; padding: 15px 20px; border-radius: 5px 5px 0 0;">'
            . '<h2 style="margin: 0; font-size: 20px;">%s %s</h2>'
            . '</div>',
            $corInfo['cor'],
            $corInfo['cor'],
            $corInfo['icone'] ?? '',
            htmlspecialchars($etapa['etapa'])
        );
        
        foreach ($itensAgrupados as $grupoNome => $grupo) {
            $html .= self::renderGrupo($grupoNome, $grupo, $corInfo['cor']);
        }
        
        $html .= sprintf(
            '<div style="background: #f5f5f5; padding: 15px 20px; border-radius: 0 0 5px 5px; display: flex; justify-content: space-between; font-weight: bold;">'
            . '<span>SUBTOTAL — %s</span>'
            . '<div>'
            . '<span style="color: #2196F3; margin-right: 20px;">📦 Material: R$ %s</span>'
            . '<span style="color: #4CAF50; margin-right: 20px;">👷 M.O: R$ %s</span>'
            . '<span style="font-size: 18px;">Total: R$ %s</span>'
            . '</div>'
            . '</div>',
            htmlspecialchars($etapa['etapa']),
            number_format((float)$etapa['total_material'], 2, ',', '.'),
            number_format((float)$etapa['total_mao_obra'], 2, ',', '.'),
            number_format((float)$etapa['total_cobranca'], 2, ',', '.')
        );
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Renderiza um grupo (finalidade) com materiais e mão de obra separados
     */
    private static function renderGrupo(string $grupoNome, array $grupo, string $corEtapa): string
    {
        $html = sprintf(
            '<div class="grupo" style="background: white; margin: 0; padding: 20px; border-bottom: 1px solid #eee;">'
            . '<h3 style="margin: 0 0 15px 0; color: %s; font-size: 16px;">▶ %s</h3>',
            $corEtapa,
            htmlspecialchars($grupoNome)
        );
        
        // Renderizar MATERIAL / CUSTO PREVISTO
        if (!empty($grupo['material'])) {
            $html .= '<div style="margin-bottom: 20px;">';
            $html .= '<h4 style="color: #2196F3; margin: 0 0 10px 0; font-size: 14px;">📦 MATERIAL / CUSTO PREVISTO</h4>';
            $html .= self::renderTabelaItens($grupo['material'], 'material');
            $html .= '</div>';
        }
        
        // Renderizar MÃO DE OBRA / CUSTO EFETIVO
        if (!empty($grupo['mao_obra'])) {
            $html .= '<div style="margin-bottom: 20px;">';
            $html .= '<h4 style="color: #4CAF50; margin: 0 0 10px 0; font-size: 14px;">👷 MÃO DE OBRA / CUSTO EFETIVO</h4>';
            $html .= self::renderTabelaItens($grupo['mao_obra'], 'mao_obra');
            $html .= '</div>';
        }
        
        // Resumo do grupo
        $html .= sprintf(
            '<div style="background: #f9f9f9; padding: 10px; border-radius: 5px; display: flex; justify-content: flex-end; gap: 20px; font-size: 13px;">'
            . '<span style="color: #2196F3;">📦 Material: R$ %s</span>'
            . '<span style="color: #4CAF50;">👷 M.O: R$ %s</span>'
            . '<span style="font-weight: bold;">Total: R$ %s</span>'
            . '</div>',
            number_format($grupo['total_material'], 2, ',', '.'),
            number_format($grupo['total_mao_obra'], 2, ',', '.'),
            number_format($grupo['total_geral'], 2, ',', '.')
        );
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Renderiza tabela de itens (material ou mão de obra)
     */
    private static function renderTabelaItens(array $itens, string $tipo): string
    {
        $corTipo = $tipo === 'material' ? '#E3F2FD' : '#E8F5E9';
        
        $html = '<table style="width: 100%; border-collapse: collapse; font-size: 12px;">';
        $html .= sprintf(
            '<thead><tr style="background: %s;">'
            . '<th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Código</th>'
            . '<th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Descrição</th>'
            . '<th style="padding: 8px; text-align: center; border: 1px solid #ddd;">Qtd</th>'
            . '<th style="padding: 8px; text-align: center; border: 1px solid #ddd;">Un.</th>'
            . '<th style="padding: 8px; text-align: right; border: 1px solid #ddd;">Vr. Unit.</th>'
            . '<th style="padding: 8px; text-align: right; border: 1px solid #ddd;">Vr. Total</th>'
            . '</tr></thead><tbody>',
            $corTipo
        );
        
        foreach ($itens as $item) {
            $valorUnitario = $tipo === 'material' ? (float)$item['custo_material'] / max((float)$item['quantidade'], 1) : (float)$item['custo_mao_obra'] / max((float)$item['quantidade'], 1);
            $valorTotal = $tipo === 'material' ? (float)$item['custo_material'] : (float)$item['custo_mao_obra'];
            
            $html .= sprintf(
                '<tr style="border-bottom: 1px solid #eee;">'
                . '<td style="padding: 8px; border: 1px solid #ddd;">%s</td>'
                . '<td style="padding: 8px; border: 1px solid #ddd;">%s</td>'
                . '<td style="padding: 8px; text-align: center; border: 1px solid #ddd;">%s</td>'
                . '<td style="padding: 8px; text-align: center; border: 1px solid #ddd;">%s</td>'
                . '<td style="padding: 8px; text-align: right; border: 1px solid #ddd;">R$ %s</td>'
                . '<td style="padding: 8px; text-align: right; border: 1px solid #ddd; font-weight: bold;">R$ %s</td>'
                . '</tr>',
                htmlspecialchars($item['codigo']),
                htmlspecialchars($item['descricao']),
                number_format((float)$item['quantidade'], 3, ',', '.'),
                htmlspecialchars($item['unidade']),
                number_format($valorUnitario, 2, ',', '.'),
                number_format($valorTotal, 2, ',', '.')
            );
        }
        
        $html .= '</tbody></table>';
        
        return $html;
    }

    /**
     * Renderiza o total geral final
     */
    private static function renderTotalGeral(array $totaisGerais): string
    {
        return sprintf(
            '<div style="background: linear-gradient(135deg, #667eea 0%%, #764ba2 100%%); color: white; padding: 25px; border-radius: 10px; text-align: center; margin-top: 30px;">'
            . '<h2 style="margin: 0 0 15px 0; font-size: 24px;">VALOR TOTAL GERAL</h2>'
            . '<div style="display: flex; justify-content: center; gap: 40px; margin-bottom: 15px;">'
            . '<div><div style="font-size: 12px; opacity: 0.9;">Custo Previsto (Material)</div><div style="font-size: 20px; font-weight: bold;">R$ %s</div></div>'
            . '<div><div style="font-size: 12px; opacity: 0.9;">Custo Efetivo (M.O)</div><div style="font-size: 20px; font-weight: bold;">R$ %s</div></div>'
            . '</div>'
            . '<div style="font-size: 32px; font-weight: bold; padding: 20px; background: rgba(255,255,255,0.2); border-radius: 10px;">R$ %s</div>'
            . '</div>',
            number_format((float)$totaisGerais['total_material'], 2, ',', '.'),
            number_format((float)$totaisGerais['total_mao_obra'], 2, ',', '.'),
            number_format((float)$totaisGerais['total_cobranca'], 2, ',', '.')
        );
    }
}
