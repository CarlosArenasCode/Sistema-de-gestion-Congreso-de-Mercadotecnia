# Script de inicio rápido para Docker - Sistema de Verificación SMS
# Ejecuta este script para levantar el proyecto automáticamente

Write-Host "🐳 Iniciando Sistema de Gestión - Congreso de Mercadotecnia..." -ForegroundColor Cyan
Write-Host ""

# Verificar si Docker está instalado
if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Error: Docker no está instalado o no está en el PATH" -ForegroundColor Red
    Write-Host "Descarga Docker Desktop desde: https://www.docker.com/products/docker-desktop" -ForegroundColor Yellow
    exit 1
}

Write-Host "✅ Docker detectado" -ForegroundColor Green

# Verificar si Docker Desktop está corriendo
try {
    docker ps | Out-Null
    Write-Host "✅ Docker Desktop está corriendo" -ForegroundColor Green
} catch {
    Write-Host "❌ Docker Desktop no está corriendo" -ForegroundColor Red
    Write-Host "Inicia Docker Desktop y espera a que esté listo (ícono de ballena en la barra de tareas)" -ForegroundColor Yellow
    exit 1
}

# Verificar si el archivo .env existe
if (-not (Test-Path ".env")) {
    Write-Host "📋 Creando archivo .env desde .env.example..." -ForegroundColor Yellow
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Write-Host "✅ Archivo .env creado" -ForegroundColor Green
    } else {
        Write-Host "⚠️ No se encontró .env.example" -ForegroundColor Yellow
    }
} else {
    Write-Host "✅ Archivo .env ya existe" -ForegroundColor Green
}

# Crear directorios necesarios
Write-Host "📁 Creando directorios para datos..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path "data\uploads" | Out-Null
New-Item -ItemType Directory -Force -Path "data\constancias_pdf" | Out-Null
Write-Host "✅ Directorios creados" -ForegroundColor Green

Write-Host ""
Write-Host "🚀 Levantando servicios..." -ForegroundColor Cyan
docker compose up -d

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Error al levantar los servicios" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "⏳ Esperando a que los servicios estén listos..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

Write-Host ""
Write-Host "✅ ¡Sistema levantado exitosamente!" -ForegroundColor Green
Write-Host ""
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "📱 URLs disponibles:" -ForegroundColor White
Write-Host "   • Registro:        http://localhost:8080/Front-end/registro_usuario.html" -ForegroundColor Yellow
Write-Host "   • Login:           http://localhost:8080/Front-end/login.html" -ForegroundColor Yellow
Write-Host "   • phpMyAdmin:      http://localhost:8081" -ForegroundColor Yellow
Write-Host ""
Write-Host "🔐 Sistema de Verificación SMS:" -ForegroundColor White
Write-Host "   • Emisor SMS: +52 449 210 6893" -ForegroundColor Yellow
Write-Host "   • Modo: Desarrollo (SMS en log)" -ForegroundColor Yellow
Write-Host ""
Write-Host "🔑 Credenciales de phpMyAdmin:" -ForegroundColor White
Write-Host "   • Usuario: congreso_user" -ForegroundColor Yellow
Write-Host "   • Contraseña: congreso_pass" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "💡 Ver SMS simulados:" -ForegroundColor White
Write-Host "   Get-Content Proyecto_conectado\php\sms_log.txt -Tail 20" -ForegroundColor Gray
Write-Host ""
Write-Host "💡 Comandos útiles:" -ForegroundColor White
Write-Host "   docker compose logs -f       # Ver logs en tiempo real" -ForegroundColor Gray
Write-Host "   docker compose stop          # Detener servicios" -ForegroundColor Gray
Write-Host "   docker compose restart       # Reiniciar servicios" -ForegroundColor Gray
Write-Host "   docker compose ps            # Ver estado de servicios" -ForegroundColor Gray
Write-Host "   .\validate-docker.ps1        # Validar servicios" -ForegroundColor Gray
Write-Host ""
