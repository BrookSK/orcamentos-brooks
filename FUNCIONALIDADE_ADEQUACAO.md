# 💰 Funcionalidade de Adequação de Valores

## 📋 Visão Geral

A funcionalidade de **Adequação de Valores** permite ajustar automaticamente todos os itens de um orçamento de forma proporcional para atingir um valor total desejado.

## 🎯 Objetivo

Quando você precisa que o orçamento atinja um valor específico (por exemplo, o valor disponível do cliente), o sistema ajusta **todos os itens proporcionalmente**, mantendo as proporções relativas entre as etapas.

## 🔧 Como Funciona

### Conceito

1. **Você informa**: Valor total desejado (ex: R$ 5.000.000,00)
2. **Sistema calcula**: Fator de adequação = Valor Desejado ÷ Valor Atual
3. **Sistema aplica**: Multiplica todos os valores por esse fator
4. **Resultado**: Orçamento ajustado mantendo proporções

### Exemplo Prático

**Situação Atual:**
```
ETAPA CINZA (BRUTA)          R$ 2.000.000,00  (50%)
ETAPA ACABAMENTOS            R$ 1.000.000,00  (25%)
ETAPA DE GERENCIAMENTO       R$   800.000,00  (20%)
TAXA DE ADMINISTRAÇÃO        R$   200.000,00  (5%)
─────────────────────────────────────────────
TOTAL ATUAL                  R$ 4.000.000,00  (100%)
```

**Valor Desejado:** R$ 5.000.000,00

**Cálculo:**
- Fator de adequação = 5.000.000 ÷ 4.000.000 = 1.25
- Percentual de ajuste = (1.25 - 1) × 100 = +25%

**Resultado Após Adequação:**
```
ETAPA CINZA (BRUTA)          R$ 2.500.000,00  (50%) ← mantém proporção
ETAPA ACABAMENTOS            R$ 1.250.000,00  (25%) ← mantém proporção
ETAPA DE GERENCIAMENTO       R$ 1.000.000,00  (20%) ← mantém proporção
TAXA DE ADMINISTRAÇÃO        R$   250.000,00  (5%)  ← mantém proporção
─────────────────────────────────────────────
TOTAL NOVO                   R$ 5.000.000,00  (100%)
```

## 📊 Estrutura de Dados

### Campos Adicionados em `orcamentos`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `valor_original` | DECIMAL(15,2) | Valor total antes da adequação |
| `valor_adequado` | DECIMAL(15,2) | Valor total após adequação |
| `fator_adequacao` | DECIMAL(10,6) | Fator aplicado (ex: 1.250000) |
| `data_adequacao` | DATETIME | Data/hora da última adequação |
| `adequacao_aplicada` | TINYINT(1) | Se foi aplicada (0=não, 1=sim) |

### Tabela `orcamento_adequacoes` (Histórico)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | INT | ID do registro |
| `orcamento_id` | INT | ID do orçamento |
| `valor_anterior` | DECIMAL(15,2) | Valor antes da adequação |
| `valor_desejado` | DECIMAL(15,2) | Valor desejado |
| `fator_aplicado` | DECIMAL(10,6) | Fator aplicado |
| `percentual_ajuste` | DECIMAL(5,2) | Percentual de ajuste (%) |
| `usuario` | VARCHAR(100) | Usuário que aplicou |
| `observacao` | TEXT | Observações |
| `created_at` | DATETIME | Data/hora do registro |

## 🚀 Instalação

### Passo 1: Executar Migration

```bash
mysql -u usuario -p sql_orcamento_onsolutionsbrasil_com_br < database/migration_004_add_adequacao.sql
```

### Passo 2: Verificar

```sql
-- Verificar campos adicionados
DESCRIBE orcamentos;

-- Verificar tabela de histórico
DESCRIBE orcamento_adequacoes;
```

## 💻 Uso via Código

### Aplicar Adequação

```php
use App\Models\OrcamentoAdequacao;

// Aplicar adequação
$resultado = OrcamentoAdequacao::aplicarAdequacao(
    $orcamentoId,
    5000000.00,  // Valor desejado
    'Ajuste solicitado pelo cliente',  // Observação
    'João Silva'  // Usuário
);

if ($resultado['sucesso']) {
    echo $resultado['mensagem'];
    echo "Itens atualizados: " . $resultado['itens_atualizados'];
    echo "Fator aplicado: " . $resultado['fator_adequacao'];
    echo "Ajuste: " . $resultado['percentual_ajuste'] . "%";
} else {
    echo "Erro: " . $resultado['erro'];
}
```

### Calcular Preview (Sem Aplicar)

```php
// Calcular preview sem aplicar
$preview = OrcamentoAdequacao::calcularPreview($orcamentoId, 5000000.00);

echo "Valor Atual: R$ " . number_format($preview['valor_atual'], 2);
echo "Valor Desejado: R$ " . number_format($preview['valor_desejado'], 2);
echo "Diferença: R$ " . number_format($preview['diferenca'], 2);
echo "Ajuste: " . number_format($preview['percentual_ajuste'], 2) . "%";

// Preview por etapa
foreach ($preview['etapas'] as $etapa) {
    echo $etapa['etapa'] . ": ";
    echo "R$ " . number_format($etapa['valor_atual'], 2);
    echo " → R$ " . number_format($etapa['valor_novo'], 2);
    echo " (" . number_format($etapa['percentual'], 2) . "%)";
}
```

### Obter Histórico

```php
// Obter histórico de adequações
$historico = OrcamentoAdequacao::getHistorico($orcamentoId);

foreach ($historico as $item) {
    echo date('d/m/Y H:i', strtotime($item['created_at']));
    echo " - Ajuste: " . $item['percentual_ajuste'] . "%";
    echo " - R$ " . number_format($item['valor_anterior'], 2);
    echo " → R$ " . number_format($item['valor_desejado'], 2);
}
```

### Reverter Adequação

```php
// Reverter última adequação
$resultado = OrcamentoAdequacao::reverterAdequacao($orcamentoId);

if ($resultado['sucesso']) {
    echo "Adequação revertida com sucesso!";
} else {
    echo "Erro: " . $resultado['erro'];
}
```

## 🎨 Interface Web

### Acessar Interface

```
/orcamentos/{id}/adequacao
```

### Funcionalidades da Interface

1. **Visualização de Valores Atuais**
   - Valor total atual
   - Custo de material
   - Custo de mão de obra
   - Informações da última adequação (se houver)

2. **Formulário de Adequação**
   - Campo para valor desejado
   - Campo para observação (opcional)
   - Botão "Visualizar Preview"
   - Botão "Aplicar Adequação"

3. **Preview Interativo**
   - Mostra ajuste por etapa
   - Valores antes e depois
   - Diferenças e percentuais
   - Confirmação antes de aplicar

4. **Histórico de Adequações**
   - Lista todas as adequações anteriores
   - Data, usuário, valores e observações
   - Percentual de ajuste aplicado

## 📈 O Que é Ajustado

Quando você aplica uma adequação, o sistema atualiza **proporcionalmente**:

### Em Cada Item do Orçamento:

- ✅ `custo_material` × fator
- ✅ `custo_mao_obra` × fator
- ✅ `valor_cobranca` × fator
- ✅ `valor_unitario` × fator
- ✅ `valor_total` × fator

### Mantém Inalterado:

- ❌ `quantidade` (não muda)
- ❌ `margem_lucro` (não muda)
- ❌ `desconto_item` (não muda)
- ❌ `etapa` (não muda)
- ❌ `categoria` (não muda)

## ⚠️ Importante

### Antes de Aplicar

1. **Faça um backup** do orçamento
2. **Use o preview** para verificar os valores
3. **Confirme** se o ajuste está correto
4. **Documente** a razão da adequação

### Após Aplicar

1. A adequação é **irreversível** (exceto via reversão)
2. Todos os itens são atualizados
3. O histórico é registrado
4. As proporções são mantidas

### Limitações

- Não é possível adequar orçamento sem itens
- Valor desejado deve ser maior que zero
- A adequação afeta TODOS os itens
- Não é possível adequar etapas individualmente

## 🔄 Fluxo de Trabalho Recomendado

```
1. Criar orçamento normalmente
   ↓
2. Adicionar todos os itens
   ↓
3. Verificar valor total atual
   ↓
4. Acessar função de adequação
   ↓
5. Informar valor desejado
   ↓
6. Visualizar preview
   ↓
7. Confirmar e aplicar
   ↓
8. Verificar resultado
   ↓
9. Exportar orçamento final
```

## 📊 Casos de Uso

### Caso 1: Cliente tem Orçamento Limitado

**Situação:** Cliente tem R$ 4.500.000 disponível, mas orçamento está em R$ 5.000.000

**Solução:**
```php
OrcamentoAdequacao::aplicarAdequacao(
    $orcamentoId,
    4500000.00,
    'Adequação ao orçamento disponível do cliente'
);
```

**Resultado:** Redução de 10% em todos os itens

### Caso 2: Aumento de Escopo

**Situação:** Cliente solicitou itens adicionais, aumentando de R$ 4.000.000 para R$ 5.000.000

**Solução:**
```php
OrcamentoAdequacao::aplicarAdequacao(
    $orcamentoId,
    5000000.00,
    'Aumento de escopo conforme solicitação do cliente'
);
```

**Resultado:** Aumento de 25% em todos os itens

### Caso 3: Ajuste de Mercado

**Situação:** Reajuste de preços de mercado de 8%

**Solução:**
```php
$valorAtual = OrcamentoItem::getTotaisGerais($orcamentoId)['total_cobranca'];
$valorNovo = $valorAtual * 1.08;

OrcamentoAdequacao::aplicarAdequacao(
    $orcamentoId,
    $valorNovo,
    'Reajuste de mercado - 8%'
);
```

**Resultado:** Aumento de 8% em todos os itens

## 🎯 Benefícios

1. **Rapidez** - Ajusta todo orçamento em segundos
2. **Precisão** - Mantém proporções exatas
3. **Rastreabilidade** - Histórico completo de alterações
4. **Flexibilidade** - Aumenta ou reduz valores
5. **Transparência** - Preview antes de aplicar
6. **Segurança** - Confirmação obrigatória

## 📝 Exemplo Completo

```php
// 1. Calcular preview
$preview = OrcamentoAdequacao::calcularPreview($orcamentoId, 5500000.00);

echo "Preview da Adequação:\n";
echo "Valor Atual: R$ " . number_format($preview['valor_atual'], 2) . "\n";
echo "Valor Desejado: R$ " . number_format($preview['valor_desejado'], 2) . "\n";
echo "Ajuste: " . number_format($preview['percentual_ajuste'], 2) . "%\n\n";

echo "Detalhamento por Etapa:\n";
foreach ($preview['etapas'] as $etapa) {
    echo sprintf(
        "%s: R$ %s → R$ %s (%.2f%%)\n",
        $etapa['etapa'],
        number_format($etapa['valor_atual'], 2),
        number_format($etapa['valor_novo'], 2),
        $etapa['percentual']
    );
}

// 2. Aplicar adequação
$resultado = OrcamentoAdequacao::aplicarAdequacao(
    $orcamentoId,
    5500000.00,
    'Adequação conforme negociação com cliente',
    'Maria Santos'
);

if ($resultado['sucesso']) {
    echo "\n✅ " . $resultado['mensagem'] . "\n";
    echo "Itens atualizados: " . $resultado['itens_atualizados'] . "\n";
} else {
    echo "\n❌ Erro: " . $resultado['erro'] . "\n";
}

// 3. Verificar histórico
$historico = OrcamentoAdequacao::getHistorico($orcamentoId);
echo "\nHistórico de Adequações:\n";
foreach ($historico as $item) {
    echo sprintf(
        "[%s] %s: R$ %s → R$ %s (%+.2f%%)\n",
        date('d/m/Y H:i', strtotime($item['created_at'])),
        $item['usuario'],
        number_format($item['valor_anterior'], 2),
        number_format($item['valor_desejado'], 2),
        $item['percentual_ajuste']
    );
}
```

## ✅ Conclusão

A funcionalidade de adequação permite ajustar rapidamente o valor total do orçamento mantendo as proporções entre etapas, facilitando negociações e ajustes de escopo.

**Características principais:**
- ✅ Ajuste proporcional automático
- ✅ Preview antes de aplicar
- ✅ Histórico completo
- ✅ Interface web intuitiva
- ✅ Reversão possível
- ✅ Rastreabilidade total
