-- Migration 004: Adicionar campos de adequação de valores
-- Data: 2026-03-25

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Adicionar campos à tabela orcamentos para controle de adequação
SET @db_name = DATABASE();

SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'orcamentos' AND column_name = 'valor_original'
);
SET @sql = IF(
  @col_exists = 0,
  "ALTER TABLE `orcamentos` ADD COLUMN `valor_original` DECIMAL(15,2) NULL DEFAULT 0.00 COMMENT 'Valor total original do orçamento'",
  "SELECT 1"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'orcamentos' AND column_name = 'valor_adequado'
);
SET @sql = IF(
  @col_exists = 0,
  "ALTER TABLE `orcamentos` ADD COLUMN `valor_adequado` DECIMAL(15,2) NULL DEFAULT 0.00 COMMENT 'Valor total após adequação'",
  "SELECT 1"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'orcamentos' AND column_name = 'fator_adequacao'
);
SET @sql = IF(
  @col_exists = 0,
  "ALTER TABLE `orcamentos` ADD COLUMN `fator_adequacao` DECIMAL(10,6) NULL DEFAULT 1.000000 COMMENT 'Fator de adequação aplicado'",
  "SELECT 1"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'orcamentos' AND column_name = 'data_adequacao'
);
SET @sql = IF(
  @col_exists = 0,
  "ALTER TABLE `orcamentos` ADD COLUMN `data_adequacao` DATETIME NULL COMMENT 'Data da última adequação'",
  "SELECT 1"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'orcamentos' AND column_name = 'adequacao_aplicada'
);
SET @sql = IF(
  @col_exists = 0,
  "ALTER TABLE `orcamentos` ADD COLUMN `adequacao_aplicada` TINYINT(1) NULL DEFAULT 0 COMMENT 'Se adequação foi aplicada (0=não, 1=sim)'",
  "SELECT 1"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Criar tabela de histórico de adequações
CREATE TABLE IF NOT EXISTS `orcamento_adequacoes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `orcamento_id` INT NOT NULL,
  `valor_anterior` DECIMAL(15,2) NOT NULL COMMENT 'Valor total antes da adequação',
  `valor_desejado` DECIMAL(15,2) NOT NULL COMMENT 'Valor total desejado',
  `fator_aplicado` DECIMAL(12,8) NOT NULL COMMENT 'Fator de adequação aplicado',
  `percentual_ajuste` DECIMAL(8,2) NOT NULL COMMENT 'Percentual de ajuste (%)',
  `usuario` VARCHAR(100) NULL COMMENT 'Usuário que aplicou a adequação',
  `observacao` TEXT NULL COMMENT 'Observações sobre a adequação',
  `created_at` DATETIME NOT NULL,
  CONSTRAINT `fk_adequacoes_orcamento` FOREIGN KEY (`orcamento_id`) REFERENCES `orcamentos`(`id`) ON DELETE CASCADE,
  INDEX `idx_adequacoes_orcamento` (`orcamento_id`),
  INDEX `idx_adequacoes_data` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
