# 🛑 DETENER Y LIMPIAR ENTORNO DOCKER
# Ejecuta este script si necesitas reiniciar completamente el proyecto

Write-Host "========================================" -ForegroundColor Red
Write-Host "  ⚠️  DETENER SISTEMA DOCKER" -ForegroundColor Red
Write-Host "========================================`n" -ForegroundColor Red

Write-Host "[1/3] Deteniendo contenedores..." -ForegroundColor Yellow
docker-compose down

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Contenedores detenidos exitosamente`n" -ForegroundColor Green
} else {
    Write-Host "✗ Error al detener contenedores`n" -ForegroundColor Red
}

Write-Host "[2/3] Verificando contenedores activos..." -ForegroundColor Yellow
$containers = docker ps -a --filter "name=congreso" --format "{{.Names}}"

if ($containers) {
    Write-Host "⚠️  Contenedores aún existentes:" -ForegroundColor Yellow
    docker ps -a --filter "name=congreso"
    
    $remove = Read-Host "`n¿Deseas eliminar estos contenedores? (s/n)"
    if ($remove -eq "s") {
        docker rm -f $containers
        Write-Host "✓ Contenedores eliminados" -ForegroundColor Green
    }
} else {
    Write-Host "✓ No hay contenedores del proyecto activos`n" -ForegroundColor Green
}

Write-Host "[3/3] Verificando volúmenes..." -ForegroundColor Yellow
$volumes = docker volume ls -q --filter "name=congreso"

if ($volumes) {
    Write-Host "⚠️  Volúmenes existentes (contienen datos de la BD):" -ForegroundColor Yellow
    docker volume ls --filter "name=congreso"
    
    Write-Host "`n⚠️  ¡ADVERTENCIA! Eliminar volúmenes borrará todos los datos de la base de datos" -ForegroundColor Red
    $removeVolumes = Read-Host "¿Deseas eliminar los volúmenes? (s/n)"
    
    if ($removeVolumes -eq "s") {
        docker volume rm $volumes
        Write-Host "✓ Volúmenes eliminados`n" -ForegroundColor Green
    }
} else {
    Write-Host "✓ No hay volúmenes del proyecto`n" -ForegroundColor Green
}

Write-Host "========================================" -ForegroundColor Green
Write-Host "  ✓ LIMPIEZA COMPLETADA" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Green

Write-Host "Para volver a iniciar el proyecto, ejecuta:" -ForegroundColor Cyan
Write-Host "  .\start-docker.ps1`n" -ForegroundColor White
