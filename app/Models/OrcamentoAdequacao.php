<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class OrcamentoAdequacao
{
    /**
     * Aplica adequação de valores no orçamento
     * Ajusta todos os itens proporcionalmente para atingir o valor desejado
     */
    public static function aplicarAdequacao(int $orcamentoId, float $valorDesejado, string $observacao = '', string $usuario = ''): array
    {
        $pdo = Database::pdo();
        
        // Iniciar transação
        $pdo->beginTransaction();
        
        try {
            // Obter valor atual do orçamento
            $totaisAtuais = OrcamentoItem::getTotaisGerais($orcamentoId);
            $valorAtual = (float)$totaisAtuais['total_cobranca'];
            
            if ($valorAtual <= 0) {
                throw new \Exception('Orçamento não possui itens ou valor total é zero.');
            }
            
            // Calcular fator de adequação
            $fatorAdequacao = $valorDesejado / $valorAtual;
            $percentualAjuste = (($fatorAdequacao - 1) * 100);
            
            // Buscar todos os itens do orçamento
            $stmt = $pdo->prepare('SELECT * FROM orcamento_itens WHERE orcamento_id = :id');
            $stmt->execute([':id' => $orcamentoId]);
            $itens = $stmt->fetchAll();
            
            // Aplicar fator de adequação em cada item
            $stmtUpdate = $pdo->prepare(
                'UPDATE orcamento_itens SET '
                . 'custo_material = :custo_material, '
                . 'custo_mao_obra = :custo_mao_obra, '
                . 'valor_cobranca = :valor_cobranca, '
                . 'valor_unitario = :valor_unitario, '
                . 'valor_total = :valor_total '
                . 'WHERE id = :id'
            );
            
            foreach ($itens as $item) {
                $novoCustoMaterial = (float)$item['custo_material'] * $fatorAdequacao;
                $novoCustoMaoObra = (float)$item['custo_mao_obra'] * $fatorAdequacao;
                $novoValorCobranca = (float)$item['valor_cobranca'] * $fatorAdequacao;
                $novoValorUnitario = (float)$item['valor_unitario'] * $fatorAdequacao;
                $novoValorTotal = (float)$item['valor_total'] * $fatorAdequacao;
                
                $stmtUpdate->execute([
                    ':id' => $item['id'],
                    ':custo_material' => round($novoCustoMaterial, 2),
                    ':custo_mao_obra' => round($novoCustoMaoObra, 2),
                    ':valor_cobranca' => round($novoValorCobranca, 2),
                    ':valor_unitario' => round($novoValorUnitario, 2),
                    ':valor_total' => round($novoValorTotal, 2),
                ]);
            }
            
            // Atualizar informações de adequação no orçamento
            $stmtOrcamento = $pdo->prepare(
                'UPDATE orcamentos SET '
                . 'valor_original = :valor_original, '
                . 'valor_adequado = :valor_adequado, '
                . 'fator_adequacao = :fator_adequacao, '
                . 'data_adequacao = :data_adequacao, '
                . 'adequacao_aplicada = 1 '
                . 'WHERE id = :id'
            );
            
            $stmtOrcamento->execute([
                ':id' => $orcamentoId,
                ':valor_original' => $valorAtual,
                ':valor_adequado' => $valorDesejado,
                ':fator_adequacao' => $fatorAdequacao,
                ':data_adequacao' => date('Y-m-d H:i:s'),
            ]);
            
            // Registrar no histórico
            self::registrarHistorico(
                $orcamentoId,
                $valorAtual,
                $valorDesejado,
                $fatorAdequacao,
                $percentualAjuste,
                $usuario,
                $observacao
            );
            
            // Commit da transação
            $pdo->commit();
            
            // Retornar resultado
            return [
                'sucesso' => true,
                'valor_anterior' => $valorAtual,
                'valor_novo' => $valorDesejado,
                'fator_adequacao' => $fatorAdequacao,
                'percentual_ajuste' => $percentualAjuste,
                'itens_atualizados' => count($itens),
                'mensagem' => sprintf(
                    'Adequação aplicada com sucesso! Valor ajustado de R$ %s para R$ %s (%.2f%%)',
                    number_format($valorAtual, 2, ',', '.'),
                    number_format($valorDesejado, 2, ',', '.'),
                    $percentualAjuste
                ),
            ];
            
        } catch (\Exception $e) {
            $pdo->rollBack();
            return [
                'sucesso' => false,
                'erro' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Registra adequação no histórico
     */
    private static function registrarHistorico(
        int $orcamentoId,
        float $valorAnterior,
        float $valorDesejado,
        float $fatorAplicado,
        float $percentualAjuste,
        string $usuario,
        string $observacao
    ): void {
        $pdo = Database::pdo();
        
        $stmt = $pdo->prepare(
            'INSERT INTO orcamento_adequacoes '
            . '(orcamento_id, valor_anterior, valor_desejado, fator_aplicado, percentual_ajuste, usuario, observacao, created_at) '
            . 'VALUES (:orcamento_id, :valor_anterior, :valor_desejado, :fator_aplicado, :percentual_ajuste, :usuario, :observacao, :created_at)'
        );
        
        $stmt->execute([
            ':orcamento_id' => $orcamentoId,
            ':valor_anterior' => $valorAnterior,
            ':valor_desejado' => $valorDesejado,
            ':fator_aplicado' => $fatorAplicado,
            ':percentual_ajuste' => $percentualAjuste,
            ':usuario' => $usuario,
            ':observacao' => $observacao,
            ':created_at' => date('Y-m-d H:i:s'),
        ]);
    }
    
    /**
     * Obtém histórico de adequações de um orçamento
     */
    public static function getHistorico(int $orcamentoId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT * FROM orcamento_adequacoes '
            . 'WHERE orcamento_id = :id '
            . 'ORDER BY created_at DESC'
        );
        $stmt->execute([':id' => $orcamentoId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Calcula preview da adequação sem aplicar
     */
    public static function calcularPreview(int $orcamentoId, float $valorDesejado): array
    {
        $totaisAtuais = OrcamentoItem::getTotaisGerais($orcamentoId);
        $valorAtual = (float)$totaisAtuais['total_cobranca'];
        
        if ($valorAtual <= 0) {
            return [
                'erro' => 'Orçamento não possui itens ou valor total é zero.',
            ];
        }
        
        $fatorAdequacao = $valorDesejado / $valorAtual;
        $percentualAjuste = (($fatorAdequacao - 1) * 100);
        $diferenca = $valorDesejado - $valorAtual;
        
        // Calcular preview por etapa
        $resumoEtapas = OrcamentoItem::getResumoEtapas($orcamentoId);
        $etapasPreview = [];
        
        foreach ($resumoEtapas as $etapa) {
            $valorAtualEtapa = (float)$etapa['total_cobranca'];
            $valorNovoEtapa = $valorAtualEtapa * $fatorAdequacao;
            
            $etapasPreview[] = [
                'etapa' => $etapa['etapa'],
                'valor_atual' => $valorAtualEtapa,
                'valor_novo' => $valorNovoEtapa,
                'diferenca' => $valorNovoEtapa - $valorAtualEtapa,
                'percentual' => ($valorNovoEtapa / $valorDesejado) * 100,
            ];
        }
        
        return [
            'valor_atual' => $valorAtual,
            'valor_desejado' => $valorDesejado,
            'diferenca' => $diferenca,
            'fator_adequacao' => $fatorAdequacao,
            'percentual_ajuste' => $percentualAjuste,
            'tipo_ajuste' => $diferenca > 0 ? 'aumento' : 'reducao',
            'etapas' => $etapasPreview,
        ];
    }
    
    /**
     * Reverte última adequação aplicada
     */
    public static function reverterAdequacao(int $orcamentoId): array
    {
        $pdo = Database::pdo();
        
        // Buscar última adequação
        $stmt = $pdo->prepare(
            'SELECT * FROM orcamento_adequacoes '
            . 'WHERE orcamento_id = :id '
            . 'ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([':id' => $orcamentoId]);
        $ultimaAdequacao = $stmt->fetch();
        
        if (!$ultimaAdequacao) {
            return [
                'sucesso' => false,
                'erro' => 'Nenhuma adequação encontrada para reverter.',
            ];
        }
        
        // Aplicar adequação reversa
        $fatorReverso = 1 / (float)$ultimaAdequacao['fator_aplicado'];
        $valorOriginal = (float)$ultimaAdequacao['valor_anterior'];
        
        return self::aplicarAdequacao(
            $orcamentoId,
            $valorOriginal,
            'Reversão de adequação anterior',
            'Sistema'
        );
    }
}
