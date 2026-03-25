# Instruções para Executar os SQLs

## 📋 Ordem de Execução

Execute os arquivos SQL na seguinte ordem:

### 1. Migration - Adicionar Novos Campos
```bash
mysql -u seu_usuario -p seu_banco < database/migration_002_add_cost_fields.sql
```

**O que faz:**
- Adiciona campos de custo, margem e desconto à tabela `orcamentos`
- Adiciona campos de custo, margem e desconto à tabela `orcamento_itens`
- Cria tabela `orcamento_descontos` para gerenciar descontos
- Cria tabela `orcamento_margens` para gerenciar margens de lucro

### 2. Seed Template - Popular Orçamento Exemplo
```bash
mysql -u seu_usuario -p seu_banco < database/seed_template_orcamento.sql
```

**O que faz:**
- Cria um orçamento completo baseado na planilha "Casa Ana e Caio"
- Popula com itens de exemplo organizados por etapas
- Adiciona opções de etapas ao sistema
- Retorna o ID do orçamento criado

## ✅ Verificação

Após executar os SQLs, verifique se tudo foi criado corretamente:

```sql
-- Verificar se os campos foram adicionados
DESCRIBE orcamentos;
DESCRIBE orcamento_itens;

-- Verificar se as novas tabelas existem
SHOW TABLES LIKE 'orcamento_%';

-- Verificar o orçamento template criado
SELECT * FROM orcamentos ORDER BY id DESC LIMIT 1;

-- Verificar itens do template
SELECT etapa, COUNT(*) as total_itens 
FROM orcamento_itens 
WHERE orcamento_id = (SELECT MAX(id) FROM orcamentos)
GROUP BY etapa;
```

## 🔄 Reverter (se necessário)

Se precisar reverter as alterações:

```sql
-- Reverter migration
ALTER TABLE orcamentos 
DROP COLUMN desconto_geral,
DROP COLUMN margem_lucro_padrao,
DROP COLUMN prazo_obra,
DROP COLUMN fonte,
DROP COLUMN revisao;

ALTER TABLE orcamento_itens
DROP COLUMN etapa,
DROP COLUMN custo_material,
DROP COLUMN custo_mao_obra,
DROP COLUMN valor_cobranca,
DROP COLUMN margem_lucro,
DROP COLUMN desconto_item;

DROP TABLE IF EXISTS orcamento_descontos;
DROP TABLE IF EXISTS orcamento_margens;

-- Deletar orçamento template (substitua X pelo ID)
DELETE FROM orcamentos WHERE id = X;
```

## 📊 Estrutura Criada

### Campos em `orcamentos`:
- `desconto_geral` DECIMAL(5,2) - Desconto geral em %
- `margem_lucro_padrao` DECIMAL(5,2) - Margem padrão em %
- `prazo_obra` VARCHAR(100) - Prazo da obra
- `fonte` VARCHAR(255) - Fonte/Referência
- `revisao` VARCHAR(50) - Revisão (ex: R00)

### Campos em `orcamento_itens`:
- `etapa` VARCHAR(100) - Etapa do orçamento
- `custo_material` DECIMAL(10,2) - Custo de material
- `custo_mao_obra` DECIMAL(10,2) - Custo de mão de obra
- `valor_cobranca` DECIMAL(10,2) - Valor de cobrança
- `margem_lucro` DECIMAL(5,2) - Margem de lucro em %
- `desconto_item` DECIMAL(5,2) - Desconto do item em %

### Tabela `orcamento_descontos`:
- Gerencia descontos por categoria/etapa/grupo
- Relacionada com `orcamentos` via FK

### Tabela `orcamento_margens`:
- Gerencia margens por categoria/etapa/grupo
- Relacionada com `orcamentos` via FK

## 💡 Próximos Passos

Após executar os SQLs:

1. Acesse o sistema de orçamentos
2. Visualize o orçamento template criado
3. Edite os itens conforme necessário
4. Configure margens e descontos
5. Exporte o orçamento em PDF

## 🎯 Funcionalidades Disponíveis

Com o sistema atualizado, você pode:

✅ Cadastrar itens com custos separados (Material + Mão de Obra)
✅ Configurar margens de lucro por categoria/etapa
✅ Aplicar descontos gerais ou específicos
✅ Visualizar totalizadores automáticos
✅ Exportar orçamentos organizados e profissionais
✅ Gerenciar múltiplas etapas de obra
✅ Calcular automaticamente valores de cobrança
