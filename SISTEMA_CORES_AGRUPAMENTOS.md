# 🎨 Sistema de Cores e Agrupamentos - Orçamentos

## 📋 Visão Geral

O sistema foi aprimorado para incluir:
- ✅ **Categorização por cores** para cada etapa
- ✅ **Separação clara** entre custos previstos (Material) e efetivos (Mão de Obra)
- ✅ **Agrupamento inteligente** de materiais e mão de obra pela mesma finalidade
- ✅ **Resumos automáticos** de valores por categoria e tipo

## 🎨 Sistema de Cores

### Cores Padrão por Etapa

| Etapa | Cor | Hexadecimal | Ícone |
|-------|-----|-------------|-------|
| ETAPA CINZA (BRUTA) | Cinza | #607D8B | 🏗️ |
| ETAPA ACABAMENTOS | Verde | #4CAF50 | 🎨 |
| ETAPA DE GERENCIAMENTO | Azul | #2196F3 | 👷 |
| TAXA DE ADMINISTRAÇÃO + IMPOSTOS | Laranja | #FF9800 | 💰 |

### Personalizar Cores

```php
// Definir cor personalizada para uma etapa
OrcamentoCor::setCorEtapa(
    'ETAPA CINZA (BRUTA)',
    '#607D8B',
    'Cinza',
    '🏗️',
    1
);

// Obter cor de uma etapa
$corInfo = OrcamentoCor::getCorPorEtapa('ETAPA CINZA (BRUTA)');
// Retorna: ['cor' => '#607D8B', 'cor_nome' => 'Cinza', 'icone' => '🏗️']
```

## 📊 Separação de Custos

### Tipos de Custo

1. **Previsto (Material)** - `tipo_custo = 'previsto'`
   - Custos de materiais
   - Cor de destaque: Azul (#2196F3)
   - Ícone: 📦

2. **Efetivo (Mão de Obra)** - `tipo_custo = 'efetivo'`
   - Custos de mão de obra
   - Cor de destaque: Verde (#4CAF50)
   - Ícone: 👷

### Identificação Automática

O sistema identifica automaticamente o tipo baseado na categoria:
- Se contém "MATERIAL" ou "CUSTO PREVISTO" → Material
- Se contém "MÃO DE OBRA" ou "CUSTO EFETIVO" → Mão de Obra

## 🔗 Agrupamento por Finalidade

### Conceito

Itens com a mesma finalidade são agrupados juntos, mas **materiais e mão de obra são apresentados separadamente** dentro do mesmo bloco.

### Exemplo de Estrutura

```
▶ FUNDAÇÃO PROFUNDA
  
  📦 MATERIAL / CUSTO PREVISTO
  ┌─────────────────────────────────────────────────────┐
  │ Código │ Descrição              │ Qtd │ Vr. Total  │
  ├─────────────────────────────────────────────────────┤
  │ 6.1.1  │ Estacas de concreto    │ 40  │ 12.144,00  │
  │ 6.1.2  │ Argamassa              │ 20  │ 6.899,20   │
  └─────────────────────────────────────────────────────┘
  
  👷 MÃO DE OBRA / CUSTO EFETIVO
  ┌─────────────────────────────────────────────────────┐
  │ Código │ Descrição              │ Qtd │ Vr. Total  │
  ├─────────────────────────────────────────────────────┤
  │ 6.2.1  │ Execução de estacas    │ 40  │ 8.800,00   │
  │ 6.2.2  │ Montagem de formas     │ 20  │ 3.080,00   │
  └─────────────────────────────────────────────────────┘
  
  Resumo: 📦 Material: R$ 19.043,20 | 👷 M.O: R$ 11.880,00 | Total: R$ 30.923,20
```

### Usar Agrupamento

```php
// Obter itens agrupados por finalidade
$itensAgrupados = OrcamentoItem::getItensAgrupadosPorFinalidade($orcamentoId, 'ETAPA CINZA (BRUTA)');

// Estrutura retornada:
[
    'FUNDAÇÃO PROFUNDA' => [
        'material' => [...itens de material...],
        'mao_obra' => [...itens de mão de obra...],
        'total_material' => 19043.20,
        'total_mao_obra' => 11880.00,
        'total_geral' => 30923.20
    ],
    'SERVIÇOS INICIAIS' => [
        'material' => [...],
        'mao_obra' => [...],
        'total_material' => ...,
        'total_mao_obra' => ...,
        'total_geral' => ...
    ]
]
```

## 📈 Resumos Automáticos

### Resumo por Etapa

```php
$resumoEtapas = OrcamentoItem::getResumoEtapas($orcamentoId);

// Retorna:
[
    [
        'etapa' => 'ETAPA CINZA (BRUTA)',
        'total_material' => 500000.00,
        'total_mao_obra' => 300000.00,
        'total_cobranca' => 800000.00,
        'custo_total' => 800000.00,
        'total_itens' => 150
    ],
    ...
]
```

### Resumo por Grupo dentro de Etapa

```php
$resumoGrupos = OrcamentoItem::getResumoGruposPorEtapa($orcamentoId, 'ETAPA CINZA (BRUTA)');

// Retorna:
[
    [
        'grupo_nome' => 'FUNDAÇÃO PROFUNDA',
        'total_material' => 19043.20,
        'total_mao_obra' => 11880.00,
        'total_cobranca' => 30923.20,
        'total_itens' => 8
    ],
    ...
]
```

## 🎯 Visualização Completa

### Usar Helper de Visualização

```php
use App\Helpers\OrcamentoVisualizacao;

// Renderizar orçamento completo com cores e agrupamentos
$html = OrcamentoVisualizacao::renderOrcamentoCompleto($orcamentoId, $orcamento);

echo $html;
```

### Estrutura da Visualização

1. **Cabeçalho** - Informações do projeto com gradiente colorido
2. **Resumo Financeiro** - Tabela com todas as etapas e cores
3. **Detalhamento por Etapa** - Cada etapa com sua cor
4. **Agrupamento por Finalidade** - Materiais e M.O separados
5. **Resumos Parciais** - Totais por grupo e por etapa
6. **Total Geral** - Resumo final destacado

## 📊 Formato de Exportação

### Resumo Financeiro

```
RESUMO FINANCEIRO
┌───┬──────────────────────────────┬─────────────────┬─────────────────┬──────────────┬──────────┐
│ # │ Bloco / Etapa                │ Custo Previsto  │ Custo Efetivo   │ Valor (R$)   │ % Total  │
│   │                              │ (Material)      │ (M.O)           │              │          │
├───┼──────────────────────────────┼─────────────────┼─────────────────┼──────────────┼──────────┤
│ 1 │ 🏗️ ETAPA CINZA (BRUTA)       │ R$ 1.200.000,00 │ R$ 800.000,00   │ 2.000.000,00 │ 50,00%   │
│ 2 │ 🎨 ETAPA ACABAMENTOS          │ R$ 600.000,00   │ R$ 400.000,00   │ 1.000.000,00 │ 25,00%   │
│ 3 │ 👷 ETAPA DE GERENCIAMENTO     │ R$ 0,00         │ R$ 800.000,00   │ 800.000,00   │ 20,00%   │
│ 4 │ 💰 TAXA DE ADMINISTRAÇÃO      │ R$ 200.000,00   │ R$ 0,00         │ 200.000,00   │ 5,00%    │
├───┼──────────────────────────────┼─────────────────┼─────────────────┼──────────────┼──────────┤
│   │ TOTAL GERAL                  │ R$ 2.000.000,00 │ R$ 2.000.000,00 │ 4.000.000,00 │ 100,00%  │
└───┴──────────────────────────────┴─────────────────┴─────────────────┴──────────────┴──────────┘
```

### Detalhamento de Etapa

```
🏗️ ETAPA CINZA (BRUTA)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

▶ FUNDAÇÃO PROFUNDA

  📦 MATERIAL / CUSTO PREVISTO
  ┌──────────┬─────────────────────────────────┬──────┬─────┬────────────┬──────────────┐
  │ Código   │ Descrição                       │ Qtd  │ Un. │ Vr. Unit.  │ Vr. Total    │
  ├──────────┼─────────────────────────────────┼──────┼─────┼────────────┼──────────────┤
  │ 6.1.1    │ Estacas de concreto             │ 40   │ und │ 303,60     │ 12.144,00    │
  │ 6.1.2    │ Argamassa de assentamento       │ 20   │ sc  │ 344,96     │ 6.899,20     │
  └──────────┴─────────────────────────────────┴──────┴─────┴────────────┴──────────────┘

  👷 MÃO DE OBRA / CUSTO EFETIVO
  ┌──────────┬─────────────────────────────────┬──────┬─────┬────────────┬──────────────┐
  │ Código   │ Descrição                       │ Qtd  │ Un. │ Vr. Unit.  │ Vr. Total    │
  ├──────────┼─────────────────────────────────┼──────┼─────┼────────────┼──────────────┤
  │ 6.2.1    │ Execução de estacas             │ 40   │ und │ 220,00     │ 8.800,00     │
  │ 6.2.2    │ Montagem de formas              │ 20   │ m²  │ 154,00     │ 3.080,00     │
  └──────────┴─────────────────────────────────┴──────┴─────┴────────────┴──────────────┘

  Resumo: 📦 Material: R$ 19.043,20 | 👷 M.O: R$ 11.880,00 | Total: R$ 30.923,20

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
SUBTOTAL — ETAPA CINZA (BRUTA)
📦 Material: R$ 1.200.000,00 | 👷 M.O: R$ 800.000,00 | Total: R$ 2.000.000,00
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

## 🔧 Instalação

### Passo 1: Executar Migration

```bash
mysql -u usuario -p banco < database/migration_003_add_colors_and_types.sql
```

### Passo 2: Verificar Cores

```sql
SELECT * FROM orcamento_cores_etapas;
```

## 💡 Exemplos de Uso

### Atualizar Tipo e Grupo de um Item

```php
// Definir que um item é de material e pertence ao grupo FUNDAÇÃO
OrcamentoItem::updateTipoEGrupo(
    $itemId,
    'previsto',
    'FUNDAÇÃO PROFUNDA'
);
```

### Obter Cor de uma Etapa

```php
$corInfo = OrcamentoCor::getCorPorEtapa('ETAPA CINZA (BRUTA)');
echo "Cor: " . $corInfo['cor']; // #607D8B
echo "Ícone: " . $corInfo['icone']; // 🏗️
```

### Renderizar Orçamento com Cores

```php
use App\Helpers\OrcamentoVisualizacao;

$orcamento = Orcamento::find($orcamentoId);
$html = OrcamentoVisualizacao::renderOrcamentoCompleto($orcamentoId, $orcamento);

// Salvar em arquivo HTML
file_put_contents('orcamento.html', $html);
```

## 🎨 Personalização de Cores

### Adicionar Nova Etapa com Cor

```php
OrcamentoCor::setCorEtapa(
    'ETAPA ESPECIAL',
    '#E91E63',  // Rosa
    'Rosa',
    '⭐',
    5
);
```

### Cores Sugeridas

| Tipo | Cor | Hexadecimal |
|------|-----|-------------|
| Estrutura | Cinza | #607D8B |
| Acabamentos | Verde | #4CAF50 |
| Instalações | Azul | #2196F3 |
| Gerenciamento | Roxo | #9C27B0 |
| Financeiro | Laranja | #FF9800 |
| Especial | Rosa | #E91E63 |

## ✅ Benefícios

1. **Visual Organizado** - Cores facilitam identificação rápida
2. **Separação Clara** - Material e M.O sempre separados
3. **Agrupamento Inteligente** - Itens relacionados juntos
4. **Resumos Automáticos** - Totais calculados automaticamente
5. **Exportação Profissional** - Layout moderno e organizado

## 🎯 Resultado Final

O sistema garante que:
- ✅ Cada etapa tem sua cor identificadora
- ✅ Materiais (previsto) e Mão de Obra (efetivo) são claramente separados
- ✅ Itens da mesma finalidade são agrupados no mesmo bloco
- ✅ Resumos de valores são apresentados por categoria
- ✅ Exportação é moderna, colorida e profissional
