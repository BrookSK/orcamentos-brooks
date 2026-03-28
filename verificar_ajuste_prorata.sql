-- Verificar se a coluna ajuste_prorata_materiais existe
SHOW COLUMNS FROM orcamentos LIKE 'ajuste_prorata_materiais';

-- Ver os valores atuais
SELECT id, numero_proposta, ajuste_prorata_materiais, margem_materiais, margem_mao_obra, margem_equipamentos 
FROM orcamentos 
ORDER BY id DESC 
LIMIT 5;
