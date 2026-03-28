-- Verificar as etapas cadastradas nos itens do orçamento
SELECT 
    orcamento_id,
    etapa,
    COUNT(*) as qtd_itens,
    SUM(quantidade * valor_cobranca) as total_etapa
FROM orcamento_itens
GROUP BY orcamento_id, etapa
ORDER BY orcamento_id, etapa;

-- Ver detalhes dos itens por etapa
SELECT 
    id,
    orcamento_id,
    codigo,
    descricao,
    etapa,
    grupo,
    categoria,
    quantidade,
    valor_cobranca,
    (quantidade * valor_cobranca) as valor_total
FROM orcamento_itens
ORDER BY orcamento_id, codigo;
