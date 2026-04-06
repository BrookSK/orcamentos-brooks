-- ============================================================================
-- MIGRATIONS PENDENTES - Executar no banco de dados
-- ============================================================================
-- Este arquivo contém todas as alterações necessárias para corrigir os erros
-- de produção (500 no store e warnings no PDF)
-- ============================================================================

-- MIGRATION 016: Adicionar campo valor_entrada
-- Verifica se a coluna já existe antes de adicionar
SET @dbname = DATABASE();
SET @tablename = 'orcamentos';
SET @columnname = 'valor_entrada';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT ''Valor de entrada pago pelo cliente'' AFTER percentual_impostos')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- MIGRATION 017: Adicionar campo pagamento_realizado
-- Verifica se a coluna já existe antes de adicionar
SET @tablename = 'orcamento_itens';
SET @columnname = 'pagamento_realizado';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''0 = Pagamento Pendente, 1 = Pagamento Realizado'' AFTER percentual_realizado')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Criar índice para pagamento_realizado (se não existir)
SET @indexname = 'idx_pagamento_realizado';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX ', @indexname, ' ON ', @tablename, '(pagamento_realizado)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- VERIFICAÇÃO: Confirmar que as colunas foram adicionadas
-- ============================================================================
SELECT 
    'orcamentos' as tabela,
    'valor_entrada' as coluna,
    IF(COUNT(*) > 0, '✓ EXISTE', '✗ FALTANDO') as status
FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_schema = DATABASE()
  AND table_name = 'orcamentos'
  AND column_name = 'valor_entrada'

UNION ALL

SELECT 
    'orcamento_itens' as tabela,
    'pagamento_realizado' as coluna,
    IF(COUNT(*) > 0, '✓ EXISTE', '✗ FALTANDO') as status
FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_schema = DATABASE()
  AND table_name = 'orcamento_itens'
  AND column_name = 'pagamento_realizado';

-- ============================================================================
-- FIM DAS MIGRATIONS
-- ============================================================================
