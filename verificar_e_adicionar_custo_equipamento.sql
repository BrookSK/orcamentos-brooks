-- Script para adicionar coluna custo_equipamento
-- Execute este script no banco de dados

-- Adicionar coluna custo_equipamento (ignora erro se já existir)
ALTER TABLE orcamento_itens 
ADD COLUMN custo_equipamento DECIMAL(15,2) DEFAULT 0.00 AFTER custo_mao_obra;

-- Se der erro "Duplicate column name", significa que a coluna já existe e está tudo OK!
-- Caso contrário, a coluna foi adicionada com sucesso.

-- Verificar se funcionou (deve mostrar a coluna custo_equipamento)
DESCRIBE orcamento_itens;
