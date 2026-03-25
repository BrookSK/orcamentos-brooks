# Sistema de Orçamentos Automatizado - Guia de Instalação

## 📋 Visão Geral

Sistema completo de orçamentos com:
- ✅ Separação de custos (Material e Mão de Obra)
- ✅ Cálculo automático de valores de cobrança
- ✅ Margens de lucro configuráveis
- ✅ Descontos gerais, por categoria e por etapa
- ✅ Organização por etapas (ETAPA CINZA, ETAPA ACABAMENTOS, etc.)
- ✅ Totalizadores automáticos
- ✅ Exportação moderna e organizada

## 🚀 Instalação

### Passo 1: Executar Migration

Execute o arquivo de migration para adicionar os novos campos ao banco de dados:

```bash
mysql -u seu_usuario -p seu_banco < database/migration_002_add_cost_fields.sql
```

### Passo 2: Popular Template (Opcional)

Para criar um orçamento template baseado na planilha "Casa Ana e Caio":

```bash
mysql -u seu_usuario -p seu_banco < database/seed_template_orcamento.sql
```

Este comando criará um orçamento completo com todos os itens da planilha de exemplo.

## 📊 Estrutura de Dados

### Tabela: `orcamentos`

**Novos campos adicionados:**
- `desconto_geral` - Desconto geral em % (0-100)
- `margem_lucro_padrao` - Margem de lucro padrão em % (0-100)
- `prazo_obra` - Prazo da obra (ex: "18 meses")
- `fonte` - Fonte/Referência do orçamento
- `revisao` - Revisão do orçamento (ex: "R00")

### Tabela: `orcamento_itens`

**Novos campos adicionados:**
- `etapa` - Etapa do orçamento (ETAPA CINZA, ETAPA ACABAMENTOS, etc.)
- `custo_material` - Custo previsto de material
- `custo_mao_obra` - Custo efetivo de mão de obra
- `valor_cobranca` - Valor de cobrança ao cliente (calculado automaticamente)
- `margem_lucro` - Margem de lucro em % (0-100)
- `desconto_item` - Desconto específico do item em % (0-100)

### Tabela: `orcamento_descontos` (Nova)

Gerencia descontos por categoria, etapa ou grupo:
- `tipo` - 'categoria', 'etapa' ou 'grupo'
- `referencia` - Nome da categoria/etapa/grupo
- `desconto` - Desconto em % (0-100)

### Tabela: `orcamento_margens` (Nova)

Gerencia margens de lucro por categoria, etapa ou grupo:
- `tipo` - 'categoria', 'etapa' ou 'grupo'
- `referencia` - Nome da categoria/etapa/grupo
- `margem` - Margem em % (0-100)

## 🧮 Cálculos Automáticos

### Valor de Cobrança

O sistema calcula automaticamente o valor de cobrança usando a fórmula:

```
Custo Total = Custo Material + Custo Mão de Obra
Valor com Margem = Custo Total × (1 + Margem Lucro / 100)
Valor Final = Valor com Margem × (1 - Desconto Item / 100)
```

### Aplicação de Descontos

Os descontos são aplicados em cascata:
1. **Desconto do Item** - Aplicado primeiro
2. **Desconto da Categoria** - Aplicado ao grupo de itens
3. **Desconto da Etapa** - Aplicado à etapa completa
4. **Desconto Geral** - Aplicado ao orçamento todo

## 📁 Estrutura de Etapas

O sistema organiza os itens em etapas:

1. **ETAPA CINZA (BRUTA)**
   - Serviços Iniciais
   - Demolições
   - Locação/Demarcação
   - Terraplanagem
   - Fundações
   - Estrutura
   - Etc.

2. **ETAPA ACABAMENTOS**
   - Cobertura
   - Revestimentos
   - Pinturas
   - Instalações finais
   - Etc.

3. **ETAPA DE GERENCIAMENTO**
   - Equipe de obra
   - Administração
   - Etc.

4. **TAXA DE ADMINISTRAÇÃO + IMPOSTOS**

## 💡 Uso dos Models

### OrcamentoItem

```php
// Criar item com custos separados
$data = [
    'etapa' => 'ETAPA CINZA (BRUTA)',
    'grupo' => 'SERVIÇOS INICIAIS',
    'categoria' => 'MATERIAL / CUSTO PREVISTO',
    'codigo' => '1.1.1',
    'descricao' => 'PAGAMENTO TAXA DE ART',
    'quantidade' => 1.0,
    'unidade' => 'vb',
    'custo_material' => 297.00,
    'custo_mao_obra' => 0.00,
    'margem_lucro' => 0.00,
    'desconto_item' => 0.00,
];

$itemId = OrcamentoItem::create($orcamentoId, $data);

// Obter totais por etapa
$totais = OrcamentoItem::getTotaisPorEtapa($orcamentoId);

// Obter totais gerais
$totaisGerais = OrcamentoItem::getTotaisGerais($orcamentoId);
```

### OrcamentoDesconto

```php
// Definir desconto para uma categoria
OrcamentoDesconto::setDesconto($orcamentoId, 'categoria', 'COBERTURA', 5.0);

// Obter desconto
$desconto = OrcamentoDesconto::getDesconto($orcamentoId, 'categoria', 'COBERTURA');

// Remover desconto
OrcamentoDesconto::deleteDesconto($orcamentoId, 'categoria', 'COBERTURA');
```

### OrcamentoMargem

```php
// Definir margem para uma etapa
OrcamentoMargem::setMargem($orcamentoId, 'etapa', 'ETAPA CINZA (BRUTA)', 15.0);

// Obter margem
$margem = OrcamentoMargem::getMargem($orcamentoId, 'etapa', 'ETAPA CINZA (BRUTA)');
```

## 📈 Totalizadores

O sistema calcula automaticamente:

- **Total por Item** - Custo Material + Custo Mão de Obra
- **Total por Categoria** - Soma de todos os itens da categoria
- **Total por Etapa** - Soma de todas as categorias da etapa
- **Total Geral** - Soma de todas as etapas

Todos os totais consideram:
- Margens de lucro aplicadas
- Descontos aplicados
- Valores de cobrança finais

## 🎨 Formato de Exportação

O orçamento exportado segue o padrão da planilha:

```
PROPOSTA ORÇAMENTÁRIA | PROJETO
Projeto: [Nome do Projeto]
Cliente: [Nome do Cliente]
Endereço: [Endereço da Obra]
Prazo de Obra: [Prazo]

RESUMO FINANCEIRO
#  | Bloco / Etapa              | Valor (R$)    | % do Total
1  | ETAPA CINZA (BRUTA)        | 2.371.963,06  | 49,96%
2  | ETAPA ACABAMENTOS          | 1.548.753,77  | 32,62%
3  | ETAPA DE GERENCIAMENTO     | 356.400,00    | 7,51%
4  | TAXA DE ADMINISTRAÇÃO      | 470.197,68    | 9,90%
   | TOTAL GERAL                | 4.747.314,51  | 100,00%

[Detalhamento por etapa com itens...]
```

## 🔧 Manutenção

### Adicionar Nova Etapa

1. Adicione a etapa em `orcamento_opcoes`:
```sql
INSERT INTO orcamento_opcoes (tipo, nome, created_at) 
VALUES ('etapa', 'NOVA ETAPA', NOW());
```

2. Crie itens associados à nova etapa

### Recalcular Valores

Os valores são recalculados automaticamente ao:
- Criar/editar um item
- Alterar margem de lucro
- Alterar desconto

## 📞 Suporte

Para dúvidas ou problemas, consulte a documentação completa ou entre em contato com o desenvolvedor.
