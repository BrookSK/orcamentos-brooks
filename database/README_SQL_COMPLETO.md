# SQL Completo - Template CASA ANA E CAIO

## ✅ Arquivo Gerado

**`seed_casa_ana_caio_COMPLETO.sql`**

## 📊 Estatísticas

- **Total de Itens**: 398 itens
- **Projeto**: P.724-25 - CASA ANA E CAIO
- **Cliente**: ANA E CAIO
- **Valor Total**: R$ 4.747.344,51

## 🎯 Composição por Etapa

O SQL contém itens distribuídos em 4 etapas:

1. **ETAPA CINZA (BRUTA)** - Estrutura e fundações
2. **ETAPA ACABAMENTOS** - Revestimentos e finalizações
3. **ETAPA DE GERENCIAMENTO** - Gestão de obra
4. **TAXA DE ADMINISTRAÇÃO + IMPOSTOS** - Custos administrativos

## 🚀 Como Executar

### Opção 1: Via MySQL Command Line

```bash
mysql -u seu_usuario -p seu_banco < database/seed_casa_ana_caio_COMPLETO.sql
```

### Opção 2: Via phpMyAdmin

1. Acesse phpMyAdmin
2. Selecione seu banco de dados
3. Vá em "Importar"
4. Selecione o arquivo `seed_casa_ana_caio_COMPLETO.sql`
5. Clique em "Executar"

### Opção 3: Via HeidiSQL ou DBeaver

1. Abra a conexão com o banco
2. Abra o arquivo SQL
3. Execute o script completo

## 📋 O que o SQL faz

1. **Cria o orçamento principal**
   - Número da proposta: P.724-25
   - Cliente: ANA E CAIO
   - Obra: CASA ANA E CAIO
   - Status: ativo

2. **Insere todos os 398 itens** com:
   - Código do item
   - Descrição completa
   - Unidade de medida
   - Quantidade
   - Valor unitário
   - Valor total
   - Custo de material
   - Custo de mão de obra
   - Etapa classificada
   - Tipo de custo (material/mao_obra/misto)

3. **Configura cores das etapas**
   - ETAPA CINZA (BRUTA): #6c757d 🏗️
   - ETAPA ACABAMENTOS: #28a745 🎨
   - ETAPA DE GERENCIAMENTO: #17a2b8 📊
   - TAXA DE ADMINISTRAÇÃO + IMPOSTOS: #ffc107 💰

4. **Executa queries de verificação**
   - Confirma criação do orçamento
   - Conta total de itens inseridos
   - Resume valores por etapa
   - Resume custos por tipo

## ✅ Verificação Pós-Execução

Após executar o SQL, você verá no output:

```
✅ Template CASA ANA E CAIO criado com sucesso!
📋 Orçamento ID: [número]
📊 Total de itens inseridos: 398

Resumo por etapa:
- ETAPA CINZA (BRUTA): [qtd] itens - R$ [valor]
- ETAPA ACABAMENTOS: [qtd] itens - R$ [valor]
- ETAPA DE GERENCIAMENTO: [qtd] itens - R$ [valor]
- TAXA DE ADMINISTRAÇÃO + IMPOSTOS: [qtd] itens - R$ [valor]
```

## 🔄 Regenerar o SQL

Se precisar regenerar o SQL com dados atualizados:

```bash
cd database
powershell -ExecutionPolicy Bypass -File gerar_sql_completo.ps1
```

O script irá:
- Ler o JSON completo
- Processar todos os itens
- Classificar automaticamente por etapa
- Gerar novo SQL atualizado

## ⚠️ Importante

- **Backup**: Faça backup do banco antes de executar
- **Duplicatas**: Se o orçamento P.724-25 já existir, delete antes:
  ```sql
  DELETE FROM orcamento_itens WHERE orcamento_id IN 
    (SELECT id FROM orcamentos WHERE numero_proposta = 'P.724-25');
  DELETE FROM orcamentos WHERE numero_proposta = 'P.724-25';
  ```

## 📁 Estrutura dos Dados

Cada item inserido contém:

```sql
INSERT INTO orcamento_itens (
    orcamento_id,        -- ID do orçamento (auto)
    grupo,               -- Nome do grupo
    categoria,           -- Categoria do item
    codigo,              -- Código (ex: 1.1.1)
    descricao,           -- Descrição completa
    unidade,             -- Unidade (vb, m2, m3, kg, etc)
    quantidade,          -- Quantidade
    valor_unitario,      -- Valor por unidade
    valor_total,         -- Total do item
    custo_material,      -- Custo de material
    custo_mao_obra,      -- Custo de mão de obra
    ordem,               -- Ordem de exibição
    etapa,               -- Etapa do orçamento
    tipo_custo,          -- material | mao_obra | misto
    grupo_finalidade     -- Slug do grupo
) VALUES (...);
```

## 🎨 Tipos de Custo

- **material**: Apenas custo de material (custo_mao_obra = 0)
- **mao_obra**: Apenas mão de obra (custo_material = 0)
- **misto**: Material + mão de obra (ambos > 0)

## 📞 Suporte

Para problemas ou dúvidas:
1. Verifique se o banco de dados existe
2. Confirme permissões de escrita
3. Verifique se as tabelas existem (execute migrations primeiro)
4. Consulte os logs de erro do MySQL

## 🔗 Arquivos Relacionados

- `seed_casa_ana_caio_COMPLETO.sql` - SQL pronto para execução
- `gerar_sql_completo.ps1` - Script gerador
- `c:\Users\narci\Downloads\tableConvert.com_cbx68k.json` - JSON fonte
