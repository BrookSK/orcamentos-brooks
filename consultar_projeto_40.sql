-- Consulta simples de todas as informações do projeto ID 40
-- P 0078-26 | DAVI DAMAZIO | GÊNESIS II

-- Dados do orçamento
SELECT * FROM orcamentos WHERE id = 40;

-- Todos os itens do orçamento
SELECT * FROM orcamento_itens WHERE orcamento_id = 40 ORDER BY ordem, id;

-- Margens (se existir tabela)
SELECT * FROM orcamento_margens WHERE orcamento_id = 40;

-- Descontos (se existir tabela)
SELECT * FROM orcamento_descontos WHERE orcamento_id = 40;

-- Cores (se existir tabela)
SELECT * FROM orcamento_cores WHERE orcamento_id = 40;

-- Adequações (se existir tabela)
SELECT * FROM orcamento_adequacoes WHERE orcamento_id = 40;
