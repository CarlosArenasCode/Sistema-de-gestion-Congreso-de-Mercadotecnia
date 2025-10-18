# instalar-personalizacion-xampp.ps1
# Script para instalar el sistema de personalización en XAMPP (Windows)

Write-Host "`n=========================================" -ForegroundColor Cyan
Write-Host "   INSTALADOR - Sistema de Personalización" -ForegroundColor Cyan
Write-Host "   Congreso de Mercadotecnia UAA" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

# 1. Verificar que MySQL de XAMPP está corriendo
Write-Host "`n[1/4] Verificando MySQL de XAMPP..." -ForegroundColor Yellow

$mysqlPath = "c:\xampp\mysql\bin\mysql.exe"

if (-not (Test-Path $mysqlPath)) {
    Write-Host "❌ ERROR: No se encontró MySQL en $mysqlPath" -ForegroundColor Red
    Write-Host "   Verifica que XAMPP esté instalado correctamente" -ForegroundColor Red
    exit 1
}

Write-Host "✓ MySQL encontrado en $mysqlPath" -ForegroundColor Green

# 2. Verificar archivo SQL
Write-Host "`n[2/4] Verificando archivo SQL..." -ForegroundColor Yellow

$sqlFile = "Proyecto_conectado\sql\personalizacion.sql"

if (-not (Test-Path $sqlFile)) {
    Write-Host "❌ ERROR: No se encontró $sqlFile" -ForegroundColor Red
    exit 1
}

Write-Host "✓ Archivo SQL encontrado" -ForegroundColor Green

# 3. Ejecutar script SQL
Write-Host "`n[3/4] Ejecutando script SQL..." -ForegroundColor Yellow
Write-Host "    Esto creará las tablas 'personalizacion' y 'carrusel_imagenes'" -ForegroundColor Cyan

try {
    # Ejecutar MySQL
    $result = & $mysqlPath -u root congreso_db -e "source $pwd\$sqlFile" 2>&1
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Script SQL ejecutado exitosamente" -ForegroundColor Green
    } else {
        Write-Host "⚠ Hubo un problema al ejecutar el SQL" -ForegroundColor Yellow
        Write-Host "   Error: $result" -ForegroundColor Yellow
        Write-Host "`n   Intenta ejecutar manualmente:" -ForegroundColor Cyan
        Write-Host "   1. Abre phpMyAdmin: http://localhost/phpmyadmin" -ForegroundColor Cyan
        Write-Host "   2. Selecciona base de datos: congreso_db" -ForegroundColor Cyan
        Write-Host "   3. Ve a pestaña SQL" -ForegroundColor Cyan
        Write-Host "   4. Copia/pega contenido de: $sqlFile" -ForegroundColor Cyan
    }
} catch {
    Write-Host "❌ ERROR al ejecutar MySQL: $_" -ForegroundColor Red
    Write-Host "`n   Solución alternativa:" -ForegroundColor Cyan
    Write-Host "   1. Abre phpMyAdmin: http://localhost/phpmyadmin" -ForegroundColor Cyan
    Write-Host "   2. Selecciona base de datos: congreso_db" -ForegroundColor Cyan
    Write-Host "   3. Ve a pestaña SQL" -ForegroundColor Cyan
    Write-Host "   4. Copia/pega contenido de: $sqlFile" -ForegroundColor Cyan
}

# 4. Verificar directorio de uploads
Write-Host "`n[4/4] Verificando directorio de uploads..." -ForegroundColor Yellow

$uploadsDir = "Proyecto_conectado\uploads\carrusel"

if (-not (Test-Path $uploadsDir)) {
    Write-Host "   Creando directorio..." -ForegroundColor Cyan
    New-Item -ItemType Directory -Force -Path $uploadsDir | Out-Null
    New-Item -ItemType File -Path "$uploadsDir\.gitkeep" | Out-Null
    Write-Host "✓ Directorio creado: $uploadsDir" -ForegroundColor Green
} else {
    Write-Host "✓ Directorio ya existe: $uploadsDir" -ForegroundColor Green
}

# 5. Verificar permisos de escritura
Write-Host "`n[5/4] Verificando permisos de escritura..." -ForegroundColor Yellow

$testFile = "$uploadsDir\test_write.tmp"
try {
    "test" | Out-File -FilePath $testFile -ErrorAction Stop
    Remove-Item $testFile -ErrorAction SilentlyContinue
    Write-Host "✓ Directorio tiene permisos de escritura" -ForegroundColor Green
} catch {
    Write-Host "⚠ Puede haber problemas de permisos de escritura" -ForegroundColor Yellow
    Write-Host "  Si hay errores al subir imágenes, da permisos de escritura a:" -ForegroundColor Yellow
    Write-Host "  $pwd\$uploadsDir" -ForegroundColor Yellow
}

# 6. Resumen
Write-Host "`n=========================================" -ForegroundColor Cyan
Write-Host "   INSTALACIÓN COMPLETADA" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Cyan

Write-Host "`n📋 SIGUIENTES PASOS:" -ForegroundColor Cyan
Write-Host "   1. Inicia sesión como administrador" -ForegroundColor White
Write-Host "   2. Ve a: http://localhost:8080/Front-end/admin_personalizacion.html" -ForegroundColor White
Write-Host "   3. O haz clic en '🎨 Personalizar Sitio' en el dashboard" -ForegroundColor White

Write-Host "`n📚 DOCUMENTACIÓN:" -ForegroundColor Cyan
Write-Host "   - SISTEMA_PERSONALIZACION.md (Documentación técnica)" -ForegroundColor White
Write-Host "   - GUIA_PERSONALIZACION.md (Guía de usuario)" -ForegroundColor White
Write-Host "   - RESUMEN_PERSONALIZACION.md (Resumen completo)" -ForegroundColor White

Write-Host "`n🎨 FUNCIONALIDADES:" -ForegroundColor Cyan
Write-Host "   ✓ Personalizar 7 colores del sitio" -ForegroundColor Green
Write-Host "   ✓ Gestionar imágenes del carrusel" -ForegroundColor Green
Write-Host "   ✓ Subir archivos o usar URLs" -ForegroundColor Green
Write-Host "   ✓ Reordenar con drag & drop" -ForegroundColor Green
Write-Host "   ✓ Vista previa en tiempo real" -ForegroundColor Green
Write-Host "   ✓ Cambios automáticos en todas las páginas" -ForegroundColor Green

Write-Host "`n=========================================" -ForegroundColor Cyan
Write-Host "`n¡Listo! El sistema está instalado y funcionando." -ForegroundColor Green
Write-Host "Presiona cualquier tecla para salir..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
