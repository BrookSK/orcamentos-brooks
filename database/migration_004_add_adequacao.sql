-- Migration 004: Adicionar campos de adequação de valores
-- Data: 2026-03-25

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Adicionar campos à tabela orcamentos para controle de adequação
ALTER TABLE `orcamentos`
ADD COLUMN `valor_original` DECIMAL(15,2) NULL DEFAULT 0.00 COMMENT 'Valor total original do orçamento',
ADD COLUMN `valor_adequado` DECIMAL(15,2) NULL DEFAULT 0.00 COMMENT 'Valor total após adequação',
ADD COLUMN `fator_adequacao` DECIMAL(10,6) NULL DEFAULT 1.000000 COMMENT 'Fator de adequação aplicado',
ADD COLUMN `data_adequacao` DATETIME NULL COMMENT 'Data da última adequação',
ADD COLUMN `adequacao_aplicada` TINYINT(1) NULL DEFAULT 0 COMMENT 'Se adequação foi aplicada (0=não, 1=sim)';

-- Criar tabela de histórico de adequações
CREATE TABLE IF NOT EXISTS `orcamento_adequacoes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `orcamento_id` INT NOT NULL,
  `valor_anterior` DECIMAL(15,2) NOT NULL COMMENT 'Valor total antes da adequação',
  `valor_desejado` DECIMAL(15,2) NOT NULL COMMENT 'Valor total desejado',
  `fator_aplicado` DECIMAL(10,6) NOT NULL COMMENT 'Fator de adequação aplicado',
  `percentual_ajuste` DECIMAL(5,2) NOT NULL COMMENT 'Percentual de ajuste (%)',
  `usuario` VARCHAR(100) NULL COMMENT 'Usuário que aplicou a adequação',
  `observacao` TEXT NULL COMMENT 'Observações sobre a adequação',
  `created_at` DATETIME NOT NULL,
  CONSTRAINT `fk_adequacoes_orcamento` FOREIGN KEY (`orcamento_id`) REFERENCES `orcamentos`(`id`) ON DELETE CASCADE,
  INDEX `idx_adequacoes_orcamento` (`orcamento_id`),
  INDEX `idx_adequacoes_data` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
