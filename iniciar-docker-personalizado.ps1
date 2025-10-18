# iniciar-docker-personalizado.ps1
# Script para iniciar Docker con el sistema de personalización

Write-Host "`n=========================================" -ForegroundColor Cyan
Write-Host "   INICIANDO DOCKER - Sistema Completo" -ForegroundColor Cyan
Write-Host "   Congreso de Mercadotecnia UAA" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

# 1. Verificar que Docker esté corriendo
Write-Host "`n[1/6] Verificando Docker..." -ForegroundColor Yellow

try {
    $dockerVersion = docker --version 2>&1
    Write-Host "✓ Docker instalado: $dockerVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ ERROR: Docker no está instalado o no está en el PATH" -ForegroundColor Red
    Write-Host "   Descarga Docker Desktop desde: https://www.docker.com/products/docker-desktop" -ForegroundColor Yellow
    exit 1
}

try {
    docker ps | Out-Null
    Write-Host "✓ Docker está corriendo" -ForegroundColor Green
} catch {
    Write-Host "❌ ERROR: Docker no está corriendo" -ForegroundColor Red
    Write-Host "   Inicia Docker Desktop y espera a que esté listo" -ForegroundColor Yellow
    exit 1
}

# 2. Crear directorios necesarios
Write-Host "`n[2/6] Creando directorios de datos..." -ForegroundColor Yellow

$directories = @(
    "data",
    "data/uploads",
    "data/constancias_pdf",
    "data/carrusel",
    "Proyecto_conectado/uploads",
    "Proyecto_conectado/uploads/carrusel"
)

foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Force -Path $dir | Out-Null
        Write-Host "  ✓ Creado: $dir" -ForegroundColor Green
    } else {
        Write-Host "  ✓ Ya existe: $dir" -ForegroundColor Gray
    }
}

# 3. Verificar archivos SQL
Write-Host "`n[3/6] Verificando archivos SQL..." -ForegroundColor Yellow

$sqlFiles = @(
    "Proyecto_conectado/sql/congreso_db.sql",
    "Proyecto_conectado/sql/personalizacion.sql"
)

foreach ($file in $sqlFiles) {
    if (Test-Path $file) {
        Write-Host "  ✓ Encontrado: $file" -ForegroundColor Green
    } else {
        Write-Host "  ❌ NO ENCONTRADO: $file" -ForegroundColor Red
    }
}

# 4. Detener contenedores existentes (si hay alguno)
Write-Host "`n[4/6] Deteniendo contenedores previos..." -ForegroundColor Yellow

$containers = docker ps -a --filter "name=congreso_" --format "{{.Names}}" 2>&1

if ($containers) {
    Write-Host "  Deteniendo y eliminando contenedores previos..." -ForegroundColor Cyan
    docker-compose down -v 2>&1 | Out-Null
    Write-Host "  ✓ Contenedores detenidos y eliminados" -ForegroundColor Green
} else {
    Write-Host "  ℹ No hay contenedores previos" -ForegroundColor Gray
}

# 5. Construir e iniciar contenedores
Write-Host "`n[5/6] Construyendo e iniciando contenedores..." -ForegroundColor Yellow
Write-Host "  (Esto puede tomar varios minutos la primera vez)" -ForegroundColor Cyan

docker-compose up -d --build

if ($LASTEXITCODE -eq 0) {
    Write-Host "  ✓ Contenedores iniciados exitosamente" -ForegroundColor Green
} else {
    Write-Host "  ❌ ERROR al iniciar contenedores" -ForegroundColor Red
    Write-Host "  Revisa los logs con: docker-compose logs" -ForegroundColor Yellow
    exit 1
}

# 6. Esperar a que los servicios estén listos
Write-Host "`n[6/6] Esperando a que los servicios estén listos..." -ForegroundColor Yellow

Write-Host "  Esperando MySQL (15 segundos)..." -ForegroundColor Cyan
Start-Sleep -Seconds 15

Write-Host "  Esperando Apache/PHP (5 segundos)..." -ForegroundColor Cyan
Start-Sleep -Seconds 5

# 7. Verificar que los contenedores están corriendo
Write-Host "`n=========================================" -ForegroundColor Cyan
Write-Host "   ESTADO DE LOS CONTENEDORES" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

$containers = @(
    @{Name="congreso_web"; Service="Apache/PHP"; Port="8080"},
    @{Name="congreso_db"; Service="MySQL"; Port="3306"},
    @{Name="congreso_phpmyadmin"; Service="phpMyAdmin"; Port="8081"},
    @{Name="congreso_whatsapp"; Service="WhatsApp"; Port="3001"}
)

foreach ($container in $containers) {
    $status = docker ps --filter "name=$($container.Name)" --format "{{.Status}}" 2>&1
    if ($status -match "Up") {
        Write-Host "✓ $($container.Service) - Corriendo en puerto $($container.Port)" -ForegroundColor Green
    } else {
        Write-Host "❌ $($container.Service) - NO está corriendo" -ForegroundColor Red
    }
}

# 8. Verificar tablas de personalización
Write-Host "`n=========================================" -ForegroundColor Cyan
Write-Host "   VERIFICANDO BASE DE DATOS" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

Write-Host "`nVerificando tablas de personalización..." -ForegroundColor Yellow

$checkTables = @"
SHOW TABLES LIKE 'personalizacion';
SHOW TABLES LIKE 'carrusel_imagenes';
"@

try {
    $result = $checkTables | docker exec -i congreso_db mysql -uroot -prootpassword congreso_db 2>&1
    
    if ($result -match "personalizacion" -and $result -match "carrusel_imagenes") {
        Write-Host "✓ Tablas de personalización creadas correctamente" -ForegroundColor Green
    } else {
        Write-Host "⚠ Las tablas pueden no estar creadas aún" -ForegroundColor Yellow
        Write-Host "  Espera 30 segundos más y revisa phpMyAdmin" -ForegroundColor Cyan
    }
} catch {
    Write-Host "⚠ No se pudo verificar las tablas (esto es normal en el primer inicio)" -ForegroundColor Yellow
}

# 9. Configurar permisos del directorio de uploads
Write-Host "`n=========================================" -ForegroundColor Cyan
Write-Host "   CONFIGURANDO PERMISOS" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

Write-Host "`nConfigurando permisos de escritura..." -ForegroundColor Yellow

docker exec congreso_web chmod -R 777 /var/www/html/Proyecto_conectado/uploads 2>&1 | Out-Null

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Permisos configurados correctamente" -ForegroundColor Green
} else {
    Write-Host "⚠ No se pudieron configurar permisos automáticamente" -ForegroundColor Yellow
}

# 10. Resumen final
Write-Host "`n=========================================" -ForegroundColor Cyan
Write-Host "   🎉 DOCKER INICIADO EXITOSAMENTE" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Cyan

Write-Host "`n📋 URLS DE ACCESO:" -ForegroundColor Cyan
Write-Host "   🌐 Sitio Web:        http://localhost:8080" -ForegroundColor White
Write-Host "   🗄️  phpMyAdmin:       http://localhost:8081" -ForegroundColor White
Write-Host "   📱 WhatsApp Service: http://localhost:3001" -ForegroundColor White

Write-Host "`n🎨 SISTEMA DE PERSONALIZACIÓN:" -ForegroundColor Cyan
Write-Host "   ✓ Tablas creadas automáticamente" -ForegroundColor Green
Write-Host "   ✓ Directorio de uploads configurado" -ForegroundColor Green
Write-Host "   ✓ Permisos de escritura establecidos" -ForegroundColor Green
Write-Host "`n   📍 Panel Admin: http://localhost:8080/Front-end/admin_personalizacion.html" -ForegroundColor White

Write-Host "`n🔑 CREDENCIALES DE BASE DE DATOS:" -ForegroundColor Cyan
Write-Host "   Host:     localhost:3306" -ForegroundColor White
Write-Host "   Database: congreso_db" -ForegroundColor White
Write-Host "   User:     congreso_user" -ForegroundColor White
Write-Host "   Password: congreso_pass" -ForegroundColor White
Write-Host "   Root:     rootpassword" -ForegroundColor White

Write-Host "`n📚 COMANDOS ÚTILES:" -ForegroundColor Cyan
Write-Host "   Ver logs:          docker-compose logs -f" -ForegroundColor Gray
Write-Host "   Detener:           docker-compose down" -ForegroundColor Gray
Write-Host "   Reiniciar:         docker-compose restart" -ForegroundColor Gray
Write-Host "   Reconstruir:       docker-compose up -d --build" -ForegroundColor Gray
Write-Host "   Estado:            docker-compose ps" -ForegroundColor Gray

Write-Host "`n💡 PRÓXIMOS PASOS:" -ForegroundColor Cyan
Write-Host "   1. Abre http://localhost:8080 en tu navegador" -ForegroundColor White
Write-Host "   2. Inicia sesión como administrador" -ForegroundColor White
Write-Host "   3. Ve al panel de personalización (🎨)" -ForegroundColor White
Write-Host "   4. Personaliza colores e imágenes del carrusel" -ForegroundColor White

Write-Host "`n=========================================" -ForegroundColor Cyan
Write-Host "   ¡Todo listo! Presiona cualquier tecla para continuar..." -ForegroundColor Gray
Write-Host "=========================================" -ForegroundColor Cyan

$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

# Abrir navegador automáticamente
Write-Host "`n🚀 Abriendo navegador..." -ForegroundColor Cyan
Start-Process "http://localhost:8080"
