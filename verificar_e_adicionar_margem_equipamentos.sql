-- Script para adicionar coluna margem_equipamentos
-- Execute este script no banco de dados

-- Adicionar coluna margem_equipamentos (ignora erro se já existir)
ALTER TABLE orcamentos 
ADD COLUMN margem_equipamentos DECIMAL(5,2) DEFAULT 20.00 AFTER margem_materiais;

-- Se der erro "Duplicate column name", significa que a coluna já existe e está tudo OK!
-- Caso contrário, a coluna foi adicionada com sucesso.

-- Verificar se funcionou (deve mostrar a coluna margem_equipamentos)
DESCRIBE orcamentos;
