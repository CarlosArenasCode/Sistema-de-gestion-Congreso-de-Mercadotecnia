# Script de validación completa del entorno Docker
# Ejecuta este script para verificar que todo funcione correctamente

Write-Host "🔍 Validando entorno Docker..." -ForegroundColor Cyan
Write-Host ""

# Verificar Docker
Write-Host "1. Verificando Docker..." -ForegroundColor Yellow
if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Docker no está instalado" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Docker está disponible" -ForegroundColor Green

# Verificar servicios
Write-Host ""
Write-Host "2. Verificando servicios..." -ForegroundColor Yellow
$services = docker compose ps --format json | ConvertFrom-Json
$expected = @("congreso_web", "congreso_db", "congreso_phpmyadmin")

foreach ($service in $expected) {
    $running = $services | Where-Object { $_.Name -eq $service -and $_.State -eq "running" }
    if ($running) {
        Write-Host "✅ $service está corriendo" -ForegroundColor Green
    } else {
        Write-Host "❌ $service no está corriendo" -ForegroundColor Red
    }
}

# Verificar conectividad web
Write-Host ""
Write-Host "3. Verificando conectividad web..." -ForegroundColor Yellow

# Aplicación principal
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8080/Front-end/login.html" -UseBasicParsing -TimeoutSec 10
    if ($response.StatusCode -eq 200) {
        Write-Host "✅ Aplicación web accesible" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ Aplicación web no accesible" -ForegroundColor Red
}

# phpMyAdmin
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8081" -UseBasicParsing -TimeoutSec 10
    if ($response.StatusCode -eq 200) {
        Write-Host "✅ phpMyAdmin accesible" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ phpMyAdmin no accesible" -ForegroundColor Red
}

# API Backend
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8080/php/test_ping.php" -UseBasicParsing -TimeoutSec 10
    if ($response.StatusCode -eq 200) {
        Write-Host "✅ Backend PHP funcionando" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ Backend PHP no responde" -ForegroundColor Red
}

Write-Host ""
Write-Host "🎉 Validación completada!" -ForegroundColor Cyan
Write-Host ""
Write-Host "📱 URLs disponibles:" -ForegroundColor White
Write-Host "   • Aplicación: http://localhost:8080/Front-end/login.html" -ForegroundColor Yellow
Write-Host "   • phpMyAdmin: http://localhost:8081" -ForegroundColor Yellow
Write-Host ""