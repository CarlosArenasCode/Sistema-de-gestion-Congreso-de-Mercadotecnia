# probar-generacion-automatica.ps1
# Script para probar el sistema de generación automática de constancias

Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   Prueba de Sistema de Generación Automática              ║" -ForegroundColor Cyan
Write-Host "║              de Constancias                                ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

$baseDir = $PSScriptRoot
$phpPath = "C:\xampp\php\php.exe"

# Verificar PHP
if (-not (Test-Path $phpPath)) {
    Write-Host "✗ PHP no encontrado en: $phpPath" -ForegroundColor Red
    Write-Host "  Ajusta la ruta en el script" -ForegroundColor Yellow
    exit 1
}

Write-Host "✓ PHP encontrado: $phpPath" -ForegroundColor Green

# Verificar archivos del sistema
$archivos = @{
    "Script automático" = "Proyecto_conectado\php\generar_constancias_automaticas.php"
    "Endpoint web" = "Proyecto_conectado\php\ejecutar_generacion_constancias.php"
    "FPDF" = "Proyecto_conectado\php\fpdf\fpdf.php"
    "phpqrcode" = "Proyecto_conectado\php\phpqrcode\qrlib.php"
}

$todoOk = $true
foreach ($nombre in $archivos.Keys) {
    $ruta = Join-Path $baseDir $archivos[$nombre]
    if (Test-Path $ruta) {
        Write-Host "✓ $nombre" -ForegroundColor Green
    } else {
        Write-Host "✗ $nombre - NO ENCONTRADO" -ForegroundColor Red
        Write-Host "  Ruta: $ruta" -ForegroundColor Yellow
        $todoOk = $false
    }
}

if (-not $todoOk) {
    Write-Host ""
    Write-Host "Faltan archivos necesarios. Verifica la instalación." -ForegroundColor Red
    exit 1
}

# Verificar directorios
Write-Host ""
Write-Host "Verificando directorios..." -ForegroundColor Cyan

$directorios = @(
    "Proyecto_conectado\constancias_pdf",
    "Proyecto_conectado\temp_qr",
    "Proyecto_conectado\logs"
)

foreach ($dir in $directorios) {
    $rutaDir = Join-Path $baseDir $dir
    if (-not (Test-Path $rutaDir)) {
        Write-Host "  Creando: $dir" -ForegroundColor Yellow
        New-Item -ItemType Directory -Path $rutaDir -Force | Out-Null
    }
    Write-Host "  ✓ $dir" -ForegroundColor Green
}

# Verificar Docker Oracle
Write-Host ""
Write-Host "Verificando base de datos Oracle..." -ForegroundColor Cyan
$dockerPs = docker ps --filter "name=congreso_oracle_db" --format "{{.Status}}" 2>$null

if ($dockerPs -match "Up") {
    Write-Host "✓ Contenedor Oracle activo" -ForegroundColor Green
} else {
    Write-Host "✗ Contenedor Oracle no está corriendo" -ForegroundColor Red
    Write-Host "  Ejecuta: docker-compose up -d" -ForegroundColor Yellow
    
    $iniciar = Read-Host "¿Deseas iniciar el contenedor ahora? (S/N)"
    if ($iniciar -eq 'S' -or $iniciar -eq 's') {
        Write-Host "Iniciando Docker..." -ForegroundColor Cyan
        docker-compose up -d
        Start-Sleep -Seconds 10
    } else {
        exit 1
    }
}

# Verificar eventos finalizados
Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "Consultando eventos finalizados en la base de datos..." -ForegroundColor Cyan
Write-Host ""

$sqlQuery = @"
SELECT 
    e.id_evento,
    e.nombre_evento,
    TO_CHAR(e.hora_fin, 'YYYY-MM-DD HH24:MI') as hora_fin,
    e.genera_constancia,
    COUNT(a.id_usuario) as usuarios_con_asistencia
FROM eventos e
LEFT JOIN asistencias a ON e.id_evento = a.id_evento
    AND a.hora_entrada IS NOT NULL
    AND a.hora_salida IS NOT NULL
WHERE e.genera_constancia = 1
GROUP BY e.id_evento, e.nombre_evento, e.hora_fin, e.genera_constancia
ORDER BY e.hora_fin DESC
FETCH FIRST 10 ROWS ONLY;
"@

# Guardar query temporal
$tempSql = Join-Path $env:TEMP "query_eventos.sql"
$sqlQuery | Out-File -FilePath $tempSql -Encoding UTF8

Write-Host "Eventos que generan constancia:" -ForegroundColor Yellow
Write-Host "(Mostrando últimos 10 eventos)" -ForegroundColor Gray
Write-Host ""

# Ejecutar query con docker exec
docker exec congreso_oracle_db sqlplus -s congreso_user/congreso_pass@FREEPDB1 @<(echo "SET PAGESIZE 50; SET LINESIZE 150; $sqlQuery") 2>$null

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Preguntar si ejecutar generación
$ejecutar = Read-Host "¿Deseas ejecutar la generación automática ahora? (S/N)"

if ($ejecutar -ne 'S' -and $ejecutar -ne 's') {
    Write-Host ""
    Write-Host "Prueba cancelada." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Para ejecutar manualmente:" -ForegroundColor Cyan
    Write-Host "  .\ejecutar-constancias-auto.ps1" -ForegroundColor White
    Write-Host ""
    Write-Host "O desde navegador:" -ForegroundColor Cyan
    Write-Host "  http://localhost/Proyecto_conectado/php/ejecutar_generacion_constancias.php" -ForegroundColor White
    exit 0
}

# Ejecutar generación
Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Green
Write-Host "Ejecutando generación automática de constancias..." -ForegroundColor Green
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Green
Write-Host ""

$scriptPath = Join-Path $baseDir "Proyecto_conectado\php\generar_constancias_automaticas.php"
& $phpPath $scriptPath

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Green
Write-Host "Ejecución completada" -ForegroundColor Green
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Green
Write-Host ""

# Mostrar archivos generados
$pdfDir = Join-Path $baseDir "Proyecto_conectado\constancias_pdf"
$pdfsHoy = Get-ChildItem -Path $pdfDir -Filter "*.pdf" | 
           Where-Object { $_.LastWriteTime -gt (Get-Date).AddHours(-1) }

if ($pdfsHoy.Count -gt 0) {
    Write-Host "PDFs generados en la última hora: $($pdfsHoy.Count)" -ForegroundColor Green
    Write-Host ""
    Write-Host "Archivos:" -ForegroundColor Cyan
    $pdfsHoy | ForEach-Object {
        $size = [math]::Round($_.Length / 1KB, 2)
        Write-Host "  - $($_.Name) (${size} KB)" -ForegroundColor White
    }
    Write-Host ""
    
    $abrirCarpeta = Read-Host "¿Deseas abrir la carpeta de constancias? (S/N)"
    if ($abrirCarpeta -eq 'S' -or $abrirCarpeta -eq 's') {
        Start-Process explorer.exe $pdfDir
    }
} else {
    Write-Host "No se generaron nuevos PDFs" -ForegroundColor Yellow
    Write-Host "Esto puede ser normal si:" -ForegroundColor Gray
    Write-Host "  - No hay eventos finalizados" -ForegroundColor Gray
    Write-Host "  - No hay usuarios con asistencia completa" -ForegroundColor Gray
    Write-Host "  - Las constancias ya fueron generadas" -ForegroundColor Gray
}

# Mostrar log
Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
$logFile = Join-Path $baseDir "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log"

if (Test-Path $logFile) {
    Write-Host "Últimas 25 líneas del log:" -ForegroundColor Cyan
    Write-Host ""
    Get-Content $logFile -Tail 25
    Write-Host ""
    Write-Host "Log completo: $logFile" -ForegroundColor Gray
} else {
    Write-Host "No se encontró archivo de log" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "Prueba completada" -ForegroundColor Green
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

Read-Host "Presiona ENTER para salir"
