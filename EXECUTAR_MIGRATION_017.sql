-- ============================================
-- MIGRATION 017: Adicionar controle de pagamento
-- ============================================
-- IMPORTANTE: Execute este SQL no seu banco de dados MySQL
-- para adicionar o campo de controle de pagamento aos itens do orçamento
-- ============================================

-- Adicionar coluna pagamento_realizado na tabela orcamento_itens
ALTER TABLE orcamento_itens 
ADD COLUMN pagamento_realizado TINYINT(1) NOT NULL DEFAULT 0 
COMMENT '0 = Pagamento Pendente, 1 = Pagamento Realizado'
AFTER percentual_realizado;

-- Criar índice para melhorar performance de consultas
CREATE INDEX idx_pagamento_realizado ON orcamento_itens(pagamento_realizado);

-- ============================================
-- Verificar se a coluna foi criada corretamente
-- ============================================
-- Execute este comando para confirmar:
-- DESCRIBE orcamento_itens;
-- 
-- Você deve ver a coluna "pagamento_realizado" na lista
-- ============================================
