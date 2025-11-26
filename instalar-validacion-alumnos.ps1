# =====================================================
# Script de Instalación - Validación de Alumnos
# =====================================================
# Este script instala automáticamente el sistema de
# validación de alumnos en Oracle Database
# =====================================================

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  INSTALADOR - Validación de Alumnos  " -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Verificar si Docker está corriendo
Write-Host "🔍 Verificando contenedor de Oracle..." -ForegroundColor Yellow
$container = docker ps --filter "name=congreso_oracle_db" --format "{{.Names}}" 2>$null

if (-not $container) {
    Write-Host "❌ ERROR: El contenedor 'congreso_oracle_db' no está corriendo" -ForegroundColor Red
    Write-Host ""
    Write-Host "Por favor inicia el contenedor primero:" -ForegroundColor Yellow
    Write-Host "  .\start-docker.ps1" -ForegroundColor White
    Write-Host ""
    exit 1
}

Write-Host "✓ Contenedor Oracle encontrado" -ForegroundColor Green
Write-Host ""

# Ruta del script SQL
$scriptPath = "install_alumnos_simple.sql"

if (-not (Test-Path $scriptPath)) {
    Write-Host "❌ ERROR: No se encuentra el archivo:" -ForegroundColor Red
    Write-Host "  $scriptPath" -ForegroundColor White
    exit 1
}

Write-Host "📄 Script encontrado: $scriptPath" -ForegroundColor Green
Write-Host ""

# Ejecutar instalación
Write-Host "🚀 Ejecutando instalación en Oracle..." -ForegroundColor Yellow
Write-Host ""

try {
    # Copiar el archivo al contenedor
    docker cp $scriptPath congreso_oracle_db:/tmp/install_validacion.sql
    
    # Ejecutar el script
    $result = docker exec congreso_oracle_db bash -c "sqlplus -S congreso_user/congreso_pass@FREEPDB1 @/tmp/install_validacion.sql" 2>&1
    
    Write-Host $result
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "========================================" -ForegroundColor Green
        Write-Host "  ✓ INSTALACIÓN COMPLETADA" -ForegroundColor Green
        Write-Host "========================================" -ForegroundColor Green
        Write-Host ""
        Write-Host "📊 Próximos pasos:" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "1. Probar el endpoint de validación:" -ForegroundColor White
        Write-Host "   curl http://localhost:8081/php/validar_alumno_universidad.php?matricula=A12345678" -ForegroundColor Gray
        Write-Host ""
        Write-Host "2. Ver la documentación completa:" -ForegroundColor White
        Write-Host "   VALIDACION_ALUMNOS_README.md" -ForegroundColor Gray
        Write-Host ""
        Write-Host "3. Probar inscripción a eventos:" -ForegroundColor White
        Write-Host "   - Inicia sesión con matrícula: A12345678" -ForegroundColor Gray
        Write-Host "   - Intenta inscribirte a un evento" -ForegroundColor Gray
        Write-Host ""
        Write-Host "📋 Matrículas de prueba disponibles:" -ForegroundColor Cyan
        Write-Host "   ✓ A12345678 (ACTIVO) - Juan Pérez García" -ForegroundColor Green
        Write-Host "   ✓ A87654321 (ACTIVO) - María López Hernández" -ForegroundColor Green
        Write-Host "   ✗ A99998888 (INACTIVO) - Roberto Torres Díaz" -ForegroundColor Yellow
        Write-Host "   ✗ A77776666 (EGRESADO) - Diana Ortiz Jiménez" -ForegroundColor Yellow
        Write-Host ""
        
    } else {
        Write-Host ""
        Write-Host "❌ ERROR durante la instalación" -ForegroundColor Red
        Write-Host "Revisa los mensajes anteriores para más detalles" -ForegroundColor Yellow
        Write-Host ""
        exit 1
    }
    
} catch {
    Write-Host ""
    Write-Host "❌ ERROR: $_" -ForegroundColor Red
    Write-Host ""
    exit 1
}

# Limpiar archivo temporal
docker exec congreso_oracle_db rm -f /tmp/install_validacion.sql 2>$null

Write-Host "✓ Instalación finalizada" -ForegroundColor Green
Write-Host ""
