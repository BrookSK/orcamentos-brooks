-- ========================================
-- Migration 015: Adicionar classificacao_custo
-- ========================================
-- Objetivo: Adicionar nova coluna classificacao_custo
--           para aplicar margens corretas nos cálculos
--           SEM alterar a coluna tipo_custo existente
-- Data: 2026-03-29
-- ========================================

-- Adicionar nova coluna classificacao_custo
ALTER TABLE `orcamento_itens`
ADD COLUMN `classificacao_custo` ENUM('material', 'mao_obra', 'equipamento') NULL DEFAULT NULL 
COMMENT 'Classificação de custo para aplicar margem correta: material (20%), mao_obra (50%), equipamento (20%)'
AFTER `tipo_custo`;

-- Criar índice para otimização
ALTER TABLE `orcamento_itens`
ADD INDEX `idx_itens_classificacao_custo` (`orcamento_id`, `classificacao_custo`);
