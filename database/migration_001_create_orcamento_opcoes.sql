-- Migration 001: criar tabela de opções (grupos/categorias/unidades)

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `orcamento_opcoes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tipo` VARCHAR(30) NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL,
  UNIQUE KEY `uq_orcamento_opcoes_tipo_nome` (`tipo`, `nome`),
  INDEX `idx_orcamento_opcoes_tipo` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
