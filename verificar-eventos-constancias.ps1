# verificar-eventos-constancias.ps1
# Script simple para verificar eventos configurados

Write-Host "`n╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║       EVENTOS CONFIGURADOS PARA CONSTANCIAS                ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

# Verificar Docker
$dockerStatus = docker ps --filter "name=congreso_oracle_db" --format "{{.Status}}" 2>$null
if (-not $dockerStatus -or $dockerStatus -notmatch "Up") {
    Write-Host "✗ Contenedor Oracle no está corriendo" -ForegroundColor Red
    Write-Host "  Ejecuta: docker-compose up -d`n" -ForegroundColor Yellow
    exit 1
}

Write-Host "✓ Conectando a Oracle...`n" -ForegroundColor Green

# Crear archivo SQL temporal
$sqlFile = Join-Path $env:TEMP "check_eventos.sql"
$sqlContent = @"
SET PAGESIZE 100
SET LINESIZE 200
COLUMN nombre_evento FORMAT A45
COLUMN tipo_evento FORMAT A12
COLUMN fecha FORMAT A12
COLUMN genera FORMAT A8

PROMPT ================================================================
PROMPT   TODOS LOS EVENTOS
PROMPT ================================================================

SELECT 
    id_evento as ID,
    nombre_evento,
    tipo_evento,
    TO_CHAR(fecha_inicio, 'YYYY-MM-DD') as fecha,
    CASE WHEN genera_constancia = 1 THEN 'SI' ELSE 'NO' END as genera
FROM eventos 
ORDER BY fecha_inicio DESC;

PROMPT
PROMPT ================================================================
PROMPT   SOLO EVENTOS QUE GENERAN CONSTANCIAS (genera_constancia = 1)
PROMPT ================================================================

SELECT 
    id_evento as ID,
    nombre_evento,
    tipo_evento,
    TO_CHAR(fecha_inicio, 'YYYY-MM-DD') as fecha,
    cupo_actual as inscritos
FROM eventos 
WHERE genera_constancia = 1
ORDER BY fecha_inicio DESC;

EXIT;
"@

$sqlContent | Out-File -FilePath $sqlFile -Encoding ASCII

# Ejecutar query
Get-Content $sqlFile | docker exec -i congreso_oracle_db sqlplus -s congreso_user/congreso_pass@FREEPDB1

# Limpiar archivo temporal
Remove-Item $sqlFile -ErrorAction SilentlyContinue

Write-Host "`n╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                    INSTRUCCIONES                           ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

Write-Host "Si no ves eventos arriba, significa que:" -ForegroundColor Yellow
Write-Host "  1. No hay eventos creados en la base de datos" -ForegroundColor White
Write-Host "  2. Los eventos tienen genera_constancia = 0" -ForegroundColor White
Write-Host ""
Write-Host "Para que un evento aparezca en el panel de constancias:" -ForegroundColor Yellow
Write-Host "  • Debe tener: " -ForegroundColor White -NoNewline
Write-Host "genera_constancia = 1" -ForegroundColor Green
Write-Host ""
Write-Host "Para actualizar un evento existente:" -ForegroundColor Cyan
Write-Host @"
  UPDATE eventos 
  SET genera_constancia = 1 
  WHERE id_evento = X;
  COMMIT;
"@ -ForegroundColor Gray

Write-Host "`n"
Read-Host "Presiona ENTER para salir"
