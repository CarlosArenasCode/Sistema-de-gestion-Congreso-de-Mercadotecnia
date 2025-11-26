# ejecutar-constancias-auto.ps1
# Script simple para ejecutar la generación automática de constancias manualmente

Write-Host "=== Generador Automático de Constancias ===" -ForegroundColor Cyan
Write-Host ""

$scriptPath = Join-Path $PSScriptRoot "Proyecto_conectado\php\generar_constancias_automaticas.php"
$phpPath = "C:\xampp\php\php.exe"

# Verificar archivos
if (-not (Test-Path $scriptPath)) {
    Write-Host "ERROR: Script no encontrado: $scriptPath" -ForegroundColor Red
    exit 1
}

if (-not (Test-Path $phpPath)) {
    Write-Host "ERROR: PHP no encontrado: $phpPath" -ForegroundColor Red
    Write-Host "Ajusta la ruta en el script o instala XAMPP" -ForegroundColor Yellow
    exit 1
}

Write-Host "Ejecutando generación automática de constancias..." -ForegroundColor Yellow
Write-Host ""

# Ejecutar script PHP
& $phpPath $scriptPath

Write-Host ""
Write-Host "=== Ejecución completada ===" -ForegroundColor Green
Write-Host ""

# Mostrar log
$logFile = Join-Path $PSScriptRoot "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log"

if (Test-Path $logFile) {
    Write-Host "Log guardado en: $logFile" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "=== ÚLTIMAS 20 LÍNEAS DEL LOG ===" -ForegroundColor Cyan
    Get-Content $logFile -Tail 20
} else {
    Write-Host "No se generó archivo de log" -ForegroundColor Yellow
}

Write-Host ""
Read-Host "Presiona ENTER para salir"
