# Script PowerShell para gerar SQL completo do orçamento CASA ANA E CAIO
# Lê o JSON e gera SQL com TODOS os itens

$jsonPath = "c:\Users\narci\Downloads\tableConvert.com_cbx68k.json"
$outputPath = "c:\Users\narci\OneDrive\Documentos\GitHub\orcamentos-brooks\database\seed_casa_ana_caio_COMPLETO.sql"

Write-Host "Lendo JSON..." -ForegroundColor Cyan
$json = Get-Content $jsonPath -Raw -Encoding UTF8 | ConvertFrom-Json

Write-Host "Processando itens..." -ForegroundColor Cyan

$grupoAtual = ""
$categoriaAtual = ""
$itens = @()

foreach ($row in $json) {
    $codigo = $row.'__EMPTY'
    $descricao = $row.'__EMPTY_1'
    $primeiraCol = $row.'PROPOSTA ORÇAMENTÁRIA | CASA ANA E CAIO'
    
    # Identificar mudanca de grupo (linhas sem codigo mas com descricao longa)
    if (-not $codigo -and $descricao -and $descricao.Length -gt 15) {
        if ($descricao -notmatch 'MATERIAL / CUSTO' -and $descricao -notmatch 'MÃO DE OBRA' -and $descricao -notmatch 'CUSTO EFETIVO' -and $descricao -notmatch 'CUSTO PREVISTO') {
            $grupoAtual = $descricao.Trim()
            Write-Host "Grupo detectado: $grupoAtual" -ForegroundColor Yellow
            continue
        }
    }
    
    # Identificar categoria
    if ($primeiraCol -match '^\d+$' -and $descricao) {
        $categoriaAtual = $descricao
        continue
    }
    
    # Pular linhas de separacao
    if ($descricao -match 'MATERIAL / CUSTO' -or $descricao -match 'MÃO DE OBRA') {
        continue
    }
    
    # Verificar se é um item válido
    if ($codigo -and $codigo -match '\d+\.\d+' -and $descricao) {
        $unidade = $row.'__EMPTY_2'
        $quantidade = $row.'__EMPTY_3' -replace ',', '.'
        $valorUnit = $row.'__EMPTY_4' -replace '[.,]', '' 
        $valorUnit = [double]$valorUnit / 100
        $valorTotal = $row.'__EMPTY_5' -replace '[.,]', ''
        $valorTotal = [double]$valorTotal / 100
        $custoMat = $row.'__EMPTY_6' -replace '[.,]', ''
        if ($custoMat) { $custoMat = [double]$custoMat / 100 } else { $custoMat = 0 }
        $custoMO = $row.'__EMPTY_7' -replace '[.,]', ''
        if ($custoMO) { $custoMO = [double]$custoMO / 100 } else { $custoMO = 0 }
        
        if ([double]$quantidade -gt 0 -and $valorTotal -gt 0) {
            # Determinar etapa baseado no grupo
            $etapa = "ETAPA CINZA (BRUTA)"
            if ($grupoAtual -match 'ACABAMENTO|PINTURA|PISO|REVESTIMENTO|LOUÇA|METAL|MÁRMORE|BOX|ESPELHO|SERRALHERIA|VIDRO|FORRO|GESSO|PORTA|JANELA|GRANITO|PORCELANATO') {
                $etapa = "ETAPA ACABAMENTOS"
            } elseif ($grupoAtual -match 'GERENCIAMENTO|EQUIPE|GESTÃO') {
                $etapa = "ETAPA DE GERENCIAMENTO"
            } elseif ($grupoAtual -match 'ADMINISTRAÇÃO|IMPOSTO|TAXA|BDI') {
                $etapa = "TAXA DE ADMINISTRAÇÃO + IMPOSTOS"
            }
            
            # Se grupo vazio, tentar determinar pela categoria ou codigo
            if (-not $grupoAtual -or $grupoAtual.Length -lt 3) {
                if ($codigo -match '^(1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20)\.' -and $codigo -notmatch '^(21|22|23|24|25|26|27|28|29|30|31|32|33|34|35|36|37|38|39|40|41|42|43)\.') {
                    $etapa = "ETAPA CINZA (BRUTA)"
                } elseif ($codigo -match '^(21|22|23|24|25|26|27|28|29|30|31|32|33|34|35|36|37|38|39|40|41)\.') {
                    $etapa = "ETAPA ACABAMENTOS"
                } elseif ($codigo -match '^42\.') {
                    $etapa = "ETAPA DE GERENCIAMENTO"
                } elseif ($codigo -match '^43\.') {
                    $etapa = "TAXA DE ADMINISTRAÇÃO + IMPOSTOS"
                }
            }
            
            # Determinar tipo de custo
            $tipoCusto = "material"
            if ($custoMat -gt 0 -and $custoMO -gt 0) {
                $tipoCusto = "misto"
            } elseif ($custoMO -gt 0) {
                $tipoCusto = "mao_obra"
            }
            
            $itens += [PSCustomObject]@{
                Grupo = $grupoAtual
                Categoria = $categoriaAtual
                Codigo = $codigo
                Descricao = $descricao
                Unidade = $unidade
                Quantidade = $quantidade
                ValorUnitario = $valorUnit
                ValorTotal = $valorTotal
                CustoMaterial = $custoMat
                CustoMaoObra = $custoMO
                Etapa = $etapa
                TipoCusto = $tipoCusto
            }
        }
    }
}

Write-Host "Total de itens encontrados: $($itens.Count)" -ForegroundColor Green

# Gerar SQL
$sql = @"
-- ============================================================================
-- TEMPLATE COMPLETO DE ORÇAMENTO: CASA ANA E CAIO
-- Projeto: P.724-25 - CASA ANA E CAIO
-- Cliente: ANA E CAIO
-- Obra: CASA ANA E CAIO
-- Total de Itens: $($itens.Count)
-- Valor Total: R$ 4.747.344,51
-- ============================================================================
-- GERADO AUTOMATICAMENTE EM: $(Get-Date -Format 'dd/MM/yyyy HH:mm:ss')
-- ============================================================================

-- 1. Criar o orçamento principal
INSERT INTO orcamentos (
    numero_proposta,
    cliente_nome,
    obra_nome,
    data_criacao,
    status
) VALUES (
    'P.724-25',
    'ANA E CAIO',
    'CASA ANA E CAIO',
    NOW(),
    'ativo'
);

-- Obter o ID do orçamento criado
SET @orcamento_id = LAST_INSERT_ID();

-- ============================================================================
-- INSERÇÃO DE TODOS OS ITENS DO ORÇAMENTO
-- ============================================================================

"@

# Agrupar por grupo para melhor organização
$gruposUnicos = $itens | Group-Object -Property Grupo

foreach ($grupo in $gruposUnicos) {
    $nomeGrupo = $grupo.Name
    $sql += "`n-- ============================================================================`n"
    $sql += "-- $nomeGrupo`n"
    $sql += "-- ============================================================================`n`n"
    
    $sql += "INSERT INTO orcamento_itens (`n"
    $sql += "    orcamento_id, grupo, categoria, codigo, descricao, unidade,`n"
    $sql += "    quantidade, valor_unitario, valor_total, custo_material, custo_mao_obra,`n"
    $sql += "    ordem, etapa, tipo_custo, grupo_finalidade`n"
    $sql += ") VALUES`n"
    
    $itensGrupo = $grupo.Group
    for ($i = 0; $i -lt $itensGrupo.Count; $i++) {
        $item = $itensGrupo[$i]
        
        # Escapar aspas simples
        $grupoEsc = $item.Grupo -replace "'", "''"
        $categoriaEsc = $item.Categoria -replace "'", "''"
        $descricaoEsc = $item.Descricao -replace "'", "''"
        $grupoFinalidade = ($item.Grupo -replace '[^a-zA-Z0-9]', '_').ToLower().Substring(0, [Math]::Min(30, $item.Grupo.Length))
        
        $virgula = if ($i -eq $itensGrupo.Count - 1) { ";" } else { "," }
        
        $sql += "(@orcamento_id, '$grupoEsc', '$categoriaEsc', "
        $sql += "'$($item.Codigo)', '$descricaoEsc', '$($item.Unidade)', "
        $sql += "$($item.Quantidade), $($item.ValorUnitario), $($item.ValorTotal), "
        $sql += "$($item.CustoMaterial), $($item.CustoMaoObra), "
        $sql += "0, '$($item.Etapa)', '$($item.TipoCusto)', '$grupoFinalidade')$virgula`n"
    }
    
    $sql += "`n"
}

# Adicionar configuração de cores e verificação
$sql += @"

-- ============================================================================
-- CONFIGURAÇÃO DE CORES DAS ETAPAS
-- ============================================================================

INSERT INTO orcamento_cores_etapas (etapa, cor, icone) VALUES
('ETAPA CINZA (BRUTA)', '#6c757d', '🏗️'),
('ETAPA ACABAMENTOS', '#28a745', '🎨'),
('ETAPA DE GERENCIAMENTO', '#17a2b8', '📊'),
('TAXA DE ADMINISTRAÇÃO + IMPOSTOS', '#ffc107', '💰')
ON DUPLICATE KEY UPDATE cor=VALUES(cor), icone=VALUES(icone);

-- ============================================================================
-- VERIFICAÇÃO E RESUMO
-- ============================================================================

SELECT '✅ Template CASA ANA E CAIO criado com sucesso!' as mensagem;
SELECT CONCAT('📋 Orçamento ID: ', @orcamento_id) as info;
SELECT CONCAT('📊 Total de itens inseridos: ', COUNT(*)) as total_itens 
FROM orcamento_itens 
WHERE orcamento_id = @orcamento_id;

SELECT 
    etapa,
    COUNT(*) as qtd_itens,
    CONCAT('R$ ', FORMAT(SUM(valor_total), 2, 'de_DE')) as valor_total
FROM orcamento_itens 
WHERE orcamento_id = @orcamento_id 
GROUP BY etapa
ORDER BY etapa;

SELECT 
    tipo_custo,
    COUNT(*) as qtd_itens,
    CONCAT('R$ ', FORMAT(SUM(custo_material), 2, 'de_DE')) as total_material,
    CONCAT('R$ ', FORMAT(SUM(custo_mao_obra), 2, 'de_DE')) as total_mao_obra
FROM orcamento_itens 
WHERE orcamento_id = @orcamento_id 
GROUP BY tipo_custo;
"@

# Salvar SQL com encoding correto
$Utf8NoBomEncoding = New-Object System.Text.UTF8Encoding $False
[System.IO.File]::WriteAllLines($outputPath, $sql, $Utf8NoBomEncoding)

Write-Host "`n✅ SQL gerado com sucesso!" -ForegroundColor Green
Write-Host "📄 Arquivo: $outputPath" -ForegroundColor Cyan
Write-Host "📊 Total de itens: $($itens.Count)" -ForegroundColor Cyan
Write-Host "📁 Total de grupos: $($gruposUnicos.Count)" -ForegroundColor Cyan
