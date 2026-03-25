$file = "c:\Users\narci\OneDrive\Documentos\GitHub\orcamentos-brooks\database\seed_casa_ana_caio_completo.sql"
$content = Get-Content $file -Raw -Encoding UTF8

# Remover os valores de tipo_custo dos VALUES
$content = $content -replace ", 'material', '", ", '"
$content = $content -replace ", 'mao_obra', '", ", '"
$content = $content -replace ", 'misto', '", ", '"

# Salvar
$Utf8NoBomEncoding = New-Object System.Text.UTF8Encoding $False
[System.IO.File]::WriteAllText($file, $content, $Utf8NoBomEncoding)

Write-Host "Arquivo corrigido com sucesso!"
