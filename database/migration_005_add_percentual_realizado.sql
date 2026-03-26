-- Migration 005: Adicionar campo de medição/percentual realizado por item
-- Data: 2026-03-26

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Adicionar coluna apenas se não existir
SET @col_exists := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'orcamento_itens'
      AND column_name = 'percentual_realizado'
);

SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE `orcamento_itens` ADD COLUMN `percentual_realizado` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT \'Percentual realizado do item (0-100)\';',
    'SELECT \'Column percentual_realizado already exists\' as info;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
