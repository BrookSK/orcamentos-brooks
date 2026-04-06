-- Migration 017: Adicionar campo de controle de pagamento
-- Data: 2024

-- Adicionar coluna pagamento_realizado na tabela orcamento_itens
ALTER TABLE orcamento_itens 
ADD COLUMN pagamento_realizado TINYINT(1) NOT NULL DEFAULT 0 
COMMENT '0 = Pagamento Pendente, 1 = Pagamento Realizado'
AFTER percentual_realizado;

-- Criar índice para melhorar performance de consultas
CREATE INDEX idx_pagamento_realizado ON orcamento_itens(pagamento_realizado);
