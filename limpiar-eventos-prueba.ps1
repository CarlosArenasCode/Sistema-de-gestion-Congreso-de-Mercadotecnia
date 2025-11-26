# Script para Limpiar Eventos de Prueba
# Ejecutar después de probar la funcionalidad

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  LIMPIEZA: Eventos de Prueba" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Eliminando eventos de prueba..." -ForegroundColor Yellow

$deleteQuery = @"
DELETE FROM inscripciones 
WHERE id_evento IN (
    SELECT id_evento FROM eventos 
    WHERE nombre_evento LIKE '%LLENO - Prueba%' 
       OR nombre_evento LIKE '%ALTERNATIVO%'
);

DELETE FROM eventos 
WHERE nombre_evento LIKE '%LLENO - Prueba%' 
   OR nombre_evento LIKE '%ALTERNATIVO%'
   OR nombre_evento = 'CONFERENCIA - NO Alternativa';

COMMIT;
"@

docker exec congreso_oracle_db bash -c "echo `"$deleteQuery`" | sqlplus -S congreso_user/congreso_pass@FREEPDB1"

Write-Host "✅ Eventos de prueba eliminados" -ForegroundColor Green
Write-Host ""

# Verificar
Write-Host "Verificando limpieza..." -ForegroundColor Yellow
$verifyQuery = "SELECT COUNT(*) as total FROM eventos WHERE nombre_evento LIKE '%Prueba%' OR nombre_evento LIKE '%ALTERNATIVO%';"
docker exec congreso_oracle_db bash -c "echo `"$verifyQuery`" | sqlplus -S congreso_user/congreso_pass@FREEPDB1"

Write-Host ""
Write-Host "✅ Limpieza completada" -ForegroundColor Green
Write-Host ""
