-- Migration 009: Adicionar campos de margem de lucro global no cabeçalho do orçamento
-- Data: 2026-03-28

-- Adicionar campos de margem global para mão de obra e materiais
ALTER TABLE `orcamentos` 
ADD COLUMN `margem_mao_obra` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Margem de lucro padrão para mão de obra (%)' AFTER `percentual_impostos`,
ADD COLUMN `margem_materiais` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Margem de lucro padrão para materiais (%)' AFTER `margem_mao_obra`;

-- Adicionar campo para indicar se o item usa margem personalizada
ALTER TABLE `orcamento_itens`
ADD COLUMN `usa_margem_personalizada` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se 1, usa percentual_bdi do item; se 0, usa margem global do orçamento' AFTER `percentual_bdi`;

-- Renomear percentual_bdi para margem_personalizada para deixar mais claro
ALTER TABLE `orcamento_itens`
CHANGE COLUMN `percentual_bdi` `margem_personalizada` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Margem de lucro personalizada do item (%)';
