# Script para convertir ENTREGABLE_PROYECTO_BD.md a PDF
# Sistema de Gestión - Congreso de Mercadotecnia

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  CONVERTIR MARKDOWN A PDF" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Cyan

$archivoMD = "ENTREGABLE_PROYECTO_BD.md"
$archivoPDF = "ENTREGABLE_PROYECTO_BD.pdf"

# Verificar que existe el archivo
if (-not (Test-Path $archivoMD)) {
    Write-Host "❌ Error: No se encontró el archivo $archivoMD" -ForegroundColor Red
    exit 1
}

Write-Host "📄 Archivo fuente: $archivoMD" -ForegroundColor Yellow

# Opción 1: Verificar si está instalado Pandoc
Write-Host "`n🔍 Verificando herramientas disponibles..." -ForegroundColor Yellow

$pandocInstalled = $false
try {
    $pandocVersion = pandoc --version 2>$null
    if ($LASTEXITCODE -eq 0) {
        $pandocInstalled = $true
        Write-Host "✅ Pandoc está instalado" -ForegroundColor Green
    }
} catch {
    Write-Host "⚠️  Pandoc no está instalado" -ForegroundColor Yellow
}

if ($pandocInstalled) {
    Write-Host "`n📝 Convirtiendo con Pandoc..." -ForegroundColor Cyan
    
    # Convertir con Pandoc (mejor calidad)
    pandoc $archivoMD -o $archivoPDF `
        --pdf-engine=xelatex `
        --variable geometry:margin=2cm `
        --variable fontsize=11pt `
        --variable documentclass=article `
        --variable lang=es `
        --toc `
        --toc-depth=3 `
        --highlight-style=tango `
        2>&1
    
    if ($LASTEXITCODE -eq 0 -and (Test-Path $archivoPDF)) {
        Write-Host "✅ PDF generado exitosamente: $archivoPDF" -ForegroundColor Green
        Write-Host "`n📊 Información del archivo:" -ForegroundColor Cyan
        Get-Item $archivoPDF | Select-Object Name, Length, LastWriteTime | Format-List
        
        # Abrir el PDF
        Write-Host "🔍 Abriendo PDF..." -ForegroundColor Yellow
        Start-Process $archivoPDF
    } else {
        Write-Host "❌ Error al generar el PDF con Pandoc" -ForegroundColor Red
        Write-Host "💡 Intenta instalar MiKTeX o TeX Live para soporte de LaTeX/XeLaTeX" -ForegroundColor Yellow
    }
} else {
    Write-Host "`n⚠️  Pandoc no está instalado" -ForegroundColor Yellow
    Write-Host "`n📥 Opciones para convertir a PDF:" -ForegroundColor Cyan
    Write-Host "`n1️⃣  INSTALAR PANDOC (Recomendado):" -ForegroundColor White
    Write-Host "   winget install --id JohnMacFarlane.Pandoc" -ForegroundColor Gray
    Write-Host "   Luego ejecuta este script de nuevo" -ForegroundColor Gray
    
    Write-Host "`n2️⃣  USAR MARKDOWN-PDF (VS Code Extension):" -ForegroundColor White
    Write-Host "   - Abre $archivoMD en VS Code" -ForegroundColor Gray
    Write-Host "   - Presiona Ctrl+Shift+P" -ForegroundColor Gray
    Write-Host "   - Busca 'Markdown PDF: Export (pdf)'" -ForegroundColor Gray
    Write-Host "   - Selecciona la opción" -ForegroundColor Gray
    
    Write-Host "`n3️⃣  USAR HERRAMIENTA ONLINE:" -ForegroundColor White
    Write-Host "   - https://www.markdowntopdf.com/" -ForegroundColor Gray
    Write-Host "   - https://md2pdf.netlify.app/" -ForegroundColor Gray
    Write-Host "   - https://cloudconvert.com/md-to-pdf" -ForegroundColor Gray
    
    Write-Host "`n4️⃣  ABRIR EN NAVEGADOR Y EXPORTAR:" -ForegroundColor White
    Write-Host "   Abriendo en navegador con vista previa..." -ForegroundColor Gray
    
    # Crear HTML temporal para vista previa
    $htmlContent = @"
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Entregable Proyecto BD</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.5.0/github-markdown.min.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        .markdown-body { box-sizing: border-box; min-width: 200px; max-width: 980px; margin: 0 auto; padding: 45px; }
        @media print { .markdown-body { max-width: 100%; padding: 20px; } .no-print { display: none; } }
        @page { margin: 2cm; }
    </style>
</head>
<body>
    <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #0366d6; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
            🖨️ Imprimir / Guardar como PDF
        </button>
    </div>
    <div class="markdown-body" id="content"></div>
    <script>
        fetch('$archivoMD')
            .then(response => response.text())
            .then(text => {
                document.getElementById('content').innerHTML = marked.parse(text);
            });
    </script>
</body>
</html>
"@
    
    $htmlFile = "preview_entregable.html"
    $htmlContent | Out-File -FilePath $htmlFile -Encoding UTF8
    
    Write-Host "   Archivo HTML creado: $htmlFile" -ForegroundColor Gray
    Write-Host "   Presiona Ctrl+P en el navegador y selecciona 'Guardar como PDF'" -ForegroundColor Gray
    
    Start-Process $htmlFile
}

Write-Host "`n========================================`n" -ForegroundColor Cyan
