# agregar-personalizacion-loader.ps1
# Script para agregar personalizacion-loader.js a todas las páginas de estudiantes

$workspaceRoot = "c:\xampp\htdocs\Proyecto\Sistema-de-gestion-Congreso-de-Mercadotecnia"
$frontendPath = Join-Path $workspaceRoot "Proyecto_conectado\Front-end"

# Páginas de estudiantes que deben cargar la personalización
$paginasEstudiantes = @(
    "horario.html",
    "mi_qr.html",
    "mis_constancias.html",
    "justificar_falta.html"
)

$scriptTag = '    <!-- Cargar personalización dinámica -->' + "`n" + '    <script src="../js/personalizacion-loader.js"></script>'

$modificados = 0
$yaIncluidos = 0
$noEncontrados = 0

foreach ($pagina in $paginasEstudiantes) {
    $filePath = Join-Path $frontendPath $pagina
    
    if (Test-Path $filePath) {
        $content = Get-Content $filePath -Raw -Encoding UTF8
        
        # Verificar si ya tiene el script
        if ($content -match 'personalizacion-loader\.js') {
            Write-Host "✓ $pagina ya tiene personalizacion-loader.js" -ForegroundColor Yellow
            $yaIncluidos++
            continue
        }
        
        # Buscar la línea de session-guard.js y agregar después
        if ($content -match '(<script src="\.\./js/session-guard\.js"></script>)') {
            $replacement = '$1' + "`n" + $scriptTag
            $newContent = $content -replace '(<script src="\.\./js/session-guard\.js"></script>)', $replacement
            
            # Guardar el archivo
            [System.IO.File]::WriteAllText($filePath, $newContent, [System.Text.UTF8Encoding]::new($false))
            Write-Host "✓ Modificado: $pagina" -ForegroundColor Green
            $modificados++
        } else {
            Write-Host "⚠ No se encontró session-guard.js en $pagina" -ForegroundColor Yellow
        }
    } else {
        Write-Host "✗ No encontrado: $pagina" -ForegroundColor Red
        $noEncontrados++
    }
}

Write-Host "`n========== RESUMEN ==========" -ForegroundColor Cyan
Write-Host "Archivos modificados: $modificados" -ForegroundColor Green
Write-Host "Archivos ya protegidos: $yaIncluidos" -ForegroundColor Yellow
Write-Host "Archivos no encontrados: $noEncontrados" -ForegroundColor Red
Write-Host "=============================" -ForegroundColor Cyan

# Copiar archivos al contenedor Docker si está corriendo
Write-Host "`n¿Deseas copiar los archivos al contenedor Docker? (S/N): " -NoNewline
$respuesta = Read-Host

if ($respuesta -eq 'S' -or $respuesta -eq 's') {
    Write-Host "`nCopiando archivos al contenedor congreso_web..." -ForegroundColor Cyan
    
    # Copiar personalizacion-loader.js
    docker cp "Proyecto_conectado/js/personalizacion-loader.js" congreso_web:/var/www/html/js/
    
    # Copiar páginas modificadas
    foreach ($pagina in $paginasEstudiantes) {
        docker cp "Proyecto_conectado/Front-end/$pagina" congreso_web:/var/www/html/Front-end/
    }
    
    Write-Host "✓ Archivos copiados exitosamente al contenedor" -ForegroundColor Green
}
