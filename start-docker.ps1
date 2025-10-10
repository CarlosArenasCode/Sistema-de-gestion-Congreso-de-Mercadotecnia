# Script de inicio rápido para Docker
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

# Verificar si el archivo .env existe, si no, copiarlo del ejemplo
if (-not (Test-Path ".env")) {
    Write-Host "📋 Creando archivo .env desde .env.example..." -ForegroundColor Yellow
    Copy-Item ".env.example" ".env"
    Write-Host "✅ Archivo .env creado" -ForegroundColor Green
} else {
    Write-Host "✅ Archivo .env ya existe" -ForegroundColor Green
}

# Verificar si el archivo conexion.php existe, si no, copiarlo de conexion.docker.php
if (-not (Test-Path "Proyecto_conectado\php\conexion.php")) {
    Write-Host "📋 Creando archivo conexion.php para Docker..." -ForegroundColor Yellow
    Copy-Item "Proyecto_conectado\php\conexion.docker.php" "Proyecto_conectado\php\conexion.php"
    Write-Host "✅ Archivo conexion.php creado" -ForegroundColor Green
} else {
    Write-Host "✅ Archivo conexion.php ya existe" -ForegroundColor Green
}

# Crear directorios necesarios
Write-Host "📁 Creando directorios para datos..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path "data\uploads" | Out-Null
New-Item -ItemType Directory -Force -Path "data\constancias_pdf" | Out-Null
Write-Host "✅ Directorios creados" -ForegroundColor Green

Write-Host ""
Write-Host "🔨 Construyendo imágenes Docker..." -ForegroundColor Cyan
docker-compose build

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Error al construir las imágenes" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "🚀 Levantando servicios..." -ForegroundColor Cyan
docker-compose up -d

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Error al levantar los servicios" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "⏳ Esperando a que la base de datos esté lista..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

Write-Host ""
Write-Host "✅ ¡Sistema levantado exitosamente!" -ForegroundColor Green
Write-Host ""
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "📱 URLs disponibles:" -ForegroundColor White
Write-Host "   • Aplicación Web:  http://localhost:8080/Front-end/login.html" -ForegroundColor Yellow
Write-Host "   • phpMyAdmin:      http://localhost:8081" -ForegroundColor Yellow
Write-Host ""
Write-Host "🔑 Credenciales de phpMyAdmin:" -ForegroundColor White
Write-Host "   • Usuario: congreso_user" -ForegroundColor Yellow
Write-Host "   • Contraseña: congreso_pass" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "💡 Comandos útiles:" -ForegroundColor White
Write-Host "   docker-compose logs -f       # Ver logs en tiempo real" -ForegroundColor Gray
Write-Host "   docker-compose stop          # Detener servicios" -ForegroundColor Gray
Write-Host "   docker-compose down          # Detener y eliminar contenedores" -ForegroundColor Gray
Write-Host "   docker-compose ps            # Ver estado de servicios" -ForegroundColor Gray
Write-Host ""
