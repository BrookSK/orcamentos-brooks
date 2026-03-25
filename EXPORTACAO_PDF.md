# 📄 Exportação PDF - Proposta Orçamentária Moderna

## 🎨 Visão Geral

Sistema completo de exportação de orçamentos em PDF com design moderno, elegante e profissional, incluindo apresentação institucional da Brooks Construtora.

## 📋 Estrutura do PDF

O PDF exportado possui a seguinte estrutura em páginas:

### **Página 1: Capa**
- Logo Brooks Construtora
- Título "Proposta Orçamentária"
- Nome do projeto
- Dados do cliente
- Data da proposta
- Design: Gradiente azul escuro elegante

### **Página 2: Prezados + Apresentação Institucional**
- Texto de apresentação cordial
- Descrição da empresa
- Lista de serviços oferecidos em grid 2 colunas
- Especialidades da construtora

### **Página 3: Nossa Expertise**
- Experiência da empresa
- Reconhecimento em publicações
- Compromisso com excelência
- Boxes destacados com informações

### **Página 4: Objeto da Proposta**
- Descrição dos serviços incluídos
- Grid com 4 boxes: Execução Técnica, Documentação, Proteção, Gestão
- Notas técnicas importantes
- Alertas sobre sistemas especiais

### **Página 5: Resumo Executivo** ⭐
- **Visão Geral de Custos** em cards coloridos:
  - 📦 Custo Previsto (Material)
  - 👷 Custo Efetivo (Mão de Obra)
  - 💰 Valor Total do Projeto
- **Tabela de Resumo por Etapa**:
  - Todas as etapas com cores identificadoras
  - Valores separados (Material / M.O / Total)
  - Percentual de cada etapa
  - Total geral

### **Páginas 6+: Detalhamento Completo**
- **Por Etapa** (com cor identificadora):
  - Cabeçalho colorido da etapa
  - **Por Grupo/Finalidade**:
    - 📦 Material / Custo Previsto (tabela completa)
    - 👷 Mão de Obra / Custo Efetivo (tabela completa)
    - Resumo do grupo
  - Subtotal da etapa
- **Última Página**:
  - Total final destacado em vermelho
  - Valores separados de Material e M.O
  - Valor total geral em destaque

## 🎨 Design e Estilo

### Paleta de Cores

```
Primária:     #1a1a2e (Azul escuro)
Secundária:   #e94560 (Vermelho/Rosa)
Gradientes:   #667eea → #764ba2 (Roxo)
              #1a1a2e → #0f3460 (Azul)
Material:     #2196F3 (Azul claro)
Mão de Obra:  #4CAF50 (Verde)
```

### Tipografia

- **Fonte**: Segoe UI, Tahoma, Geneva, Verdana, sans-serif
- **Títulos**: 32-48px, peso 700
- **Subtítulos**: 18-28px, peso 600
- **Texto**: 13-14px, peso 400
- **Tabelas**: 11-13px

### Elementos Visuais

- ✅ Gradientes modernos
- ✅ Bordas arredondadas (8-10px)
- ✅ Sombras suaves
- ✅ Ícones emoji para identificação
- ✅ Cards com backdrop-filter
- ✅ Tabelas zebradas
- ✅ Cores por categoria

## 💻 Uso

### Gerar PDF via Código

```php
use App\Helpers\OrcamentoPDF;

// Obter dados do orçamento
$orcamento = Orcamento::find($orcamentoId);

// Gerar HTML completo
$html = OrcamentoPDF::gerarHTML($orcamentoId, $orcamento);

// Salvar em arquivo HTML (para preview)
file_put_contents('orcamento.html', $html);

// Ou converter para PDF usando biblioteca
// Exemplo com DomPDF:
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Download
$dompdf->stream('orcamento.pdf', ['Attachment' => true]);

// Ou salvar em arquivo
file_put_contents('orcamento.pdf', $dompdf->output());
```

### Integração com Controller

```php
// No OrcamentoController.php

public function exportarPDF(int $id): void
{
    $orcamento = Orcamento::find($id);
    
    if (!$orcamento) {
        http_response_code(404);
        echo "Orçamento não encontrado";
        return;
    }
    
    // Gerar HTML
    $html = OrcamentoPDF::gerarHTML($id, $orcamento);
    
    // Configurar DomPDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Nome do arquivo
    $filename = sprintf(
        'Orcamento_%s_%s.pdf',
        $orcamento['numero_proposta'],
        date('Y-m-d')
    );
    
    // Download
    $dompdf->stream($filename, ['Attachment' => true]);
}
```

### Rota

```php
// routes.php
$router->get('/orcamentos/{id}/pdf', 'OrcamentoController@exportarPDF');
```

## 📊 Estrutura das Páginas

### 1. Página de Capa

```
┌─────────────────────────────────────┐
│                                     │
│         BROOKS CONSTRUTORA          │
│                                     │
│    PROPOSTA ORÇAMENTÁRIA            │
│    CASA ANA E CAIO                  │
│                                     │
│    Proposta: P724-25                │
│    Cliente: Ana e Caio              │
│    Endereço: Porto Fino             │
│    Data: 25/03/2026                 │
│                                     │
└─────────────────────────────────────┘
```

### 2. Resumo Executivo

```
┌─────────────────────────────────────┐
│  RESUMO EXECUTIVO                   │
│                                     │
│  ┌──────┐  ┌──────┐  ┌──────┐      │
│  │📦    │  │👷    │  │💰    │      │
│  │Mat.  │  │M.O   │  │Total │      │
│  │2.0M  │  │1.5M  │  │3.5M  │      │
│  └──────┘  └──────┘  └──────┘      │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Etapa          │ Valor │ %  │   │
│  ├─────────────────────────────┤   │
│  │ 🏗️ Cinza      │ 2.0M  │50% │   │
│  │ 🎨 Acabamentos │ 1.0M  │25% │   │
│  │ 👷 Gerenc.     │ 0.5M  │15% │   │
│  └─────────────────────────────┘   │
└─────────────────────────────────────┘
```

### 3. Detalhamento

```
┌─────────────────────────────────────┐
│  🏗️ ETAPA CINZA (BRUTA)            │
├─────────────────────────────────────┤
│  ▶ FUNDAÇÃO PROFUNDA                │
│                                     │
│  📦 MATERIAL / CUSTO PREVISTO       │
│  ┌────────────────────────────┐    │
│  │ Cód │ Desc │ Qtd │ Vr.Total│    │
│  ├────────────────────────────┤    │
│  │ 6.1 │ Est. │ 40  │ 12.144 │    │
│  └────────────────────────────┘    │
│                                     │
│  👷 MÃO DE OBRA / CUSTO EFETIVO     │
│  ┌────────────────────────────┐    │
│  │ Cód │ Desc │ Qtd │ Vr.Total│    │
│  ├────────────────────────────┤    │
│  │ 6.2 │ Exec.│ 40  │ 8.800  │    │
│  └────────────────────────────┘    │
│                                     │
│  Resumo: 📦 19K │ 👷 11K │ 30K     │
├─────────────────────────────────────┤
│  SUBTOTAL: 2.000.000,00             │
└─────────────────────────────────────┘
```

## 🎯 Características

### ✅ Apresentação Profissional
- Capa moderna com gradiente
- Logo e identidade visual
- Informações organizadas

### ✅ Conteúdo Institucional
- Texto "Prezados" personalizado
- Apresentação da empresa
- Lista de serviços
- Expertise e reconhecimento
- Objeto da proposta
- Notas técnicas

### ✅ Resumo Executivo
- Cards visuais com totais
- Tabela resumida por etapa
- Separação Material vs M.O
- Percentuais calculados

### ✅ Detalhamento Completo
- Todas as etapas com cores
- Agrupamento por finalidade
- Materiais e M.O separados
- Tabelas completas de itens
- Resumos parciais e totais

### ✅ Design Moderno
- Gradientes e cores vibrantes
- Tipografia hierárquica
- Espaçamento adequado
- Ícones emoji para identificação
- Layout responsivo para impressão

## 📐 Especificações Técnicas

### Formato
- **Tamanho**: A4 (210mm × 297mm)
- **Orientação**: Retrato
- **Margens**: 20mm em todos os lados
- **Resolução**: 300 DPI (para impressão)

### Compatibilidade
- ✅ Navegadores modernos
- ✅ DomPDF
- ✅ TCPDF
- ✅ wkhtmltopdf
- ✅ Impressão direta

### Otimizações
- CSS inline para compatibilidade
- Fontes web-safe
- Cores em hexadecimal
- Quebras de página controladas
- Numeração automática

## 🔧 Personalização

### Alterar Cores

```php
// No arquivo OrcamentoPDF.php, seção de estilos CSS

// Cor primária (azul escuro)
background: #1a1a2e;

// Cor secundária (vermelho/rosa)
color: #e94560;

// Gradiente de capa
background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
```

### Adicionar Logo

```php
// Na função gerarPaginaApresentacao()

<div class="logo-area">
    <img src="caminho/para/logo.png" alt="Brooks Construtora" style="max-width: 200px;">
    <div class="logo-subtitle">CONSTRUTORA</div>
</div>
```

### Customizar Textos

Edite as funções:
- `gerarPaginaApresentacaoInstitucional()` - Texto "Prezados"
- `gerarPaginaExpertise()` - Nossa Expertise
- `gerarPaginaObjetoProposta()` - Objeto da Proposta

## 📝 Exemplo Completo

```php
<?php
// exemplo_exportacao.php

require_once 'vendor/autoload.php';

use App\Models\Orcamento;
use App\Helpers\OrcamentoPDF;
use Dompdf\Dompdf;
use Dompdf\Options;

// ID do orçamento
$orcamentoId = 1;

// Buscar orçamento
$orcamento = Orcamento::find($orcamentoId);

if (!$orcamento) {
    die('Orçamento não encontrado');
}

// Gerar HTML
$html = OrcamentoPDF::gerarHTML($orcamentoId, $orcamento);

// Configurar PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Nome do arquivo
$filename = sprintf(
    'Proposta_Orcamentaria_%s_%s.pdf',
    str_replace(' ', '_', $orcamento['numero_proposta']),
    date('Y-m-d')
);

// Download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo $dompdf->output();
```

## ✨ Resultado Final

O PDF exportado contém:

1. ✅ **Capa elegante** com gradiente e informações do projeto
2. ✅ **Apresentação institucional** completa da Brooks Construtora
3. ✅ **Resumo executivo** com visão geral de custos (Material + M.O)
4. ✅ **Detalhamento completo** de todas as atividades por etapa
5. ✅ **Design moderno** e profissional
6. ✅ **Organização clara** com cores e ícones
7. ✅ **Pronto para impressão** ou envio digital

## 🎯 Benefícios

- **Profissionalismo**: Apresentação comercial de alto nível
- **Clareza**: Informações organizadas e fáceis de entender
- **Completude**: Todos os dados necessários em um único documento
- **Modernidade**: Design atual e elegante
- **Praticidade**: Exportação em um clique
- **Personalização**: Fácil de customizar cores e textos

---

**O sistema de exportação PDF está completo e pronto para uso!** 🚀
