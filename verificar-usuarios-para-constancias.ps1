# verificar-usuarios-para-constancias.ps1
# Script para verificar el estado de usuarios y constancias

Write-Host "`n╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║     VERIFICACIÓN DE USUARIOS PARA CONSTANCIAS             ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

# Verificar Docker
$dockerStatus = docker ps --filter "name=congreso_oracle_db" --format "{{.Status}}" 2>$null
if (-not $dockerStatus -or $dockerStatus -notmatch "Up") {
    Write-Host "✗ Contenedor Oracle no está corriendo" -ForegroundColor Red
    Write-Host "  Ejecuta: docker-compose up -d`n" -ForegroundColor Yellow
    exit 1
}

Write-Host "✓ Contenedor Oracle activo`n" -ForegroundColor Green

# Query 1: Eventos que generan constancias
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "1. EVENTOS QUE GENERAN CONSTANCIAS" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════════`n" -ForegroundColor Cyan

$query1 = @"
SET PAGESIZE 50
SET LINESIZE 200
COLUMN nombre_evento FORMAT A30
COLUMN tipo_evento FORMAT A12
COLUMN hora_fin FORMAT A16
COLUMN estado_generacion FORMAT A20

SELECT 
    e.id_evento,
    e.nombre_evento,
    e.tipo_evento,
    TO_CHAR(e.hora_fin, 'YYYY-MM-DD HH24:MI') as hora_fin,
    e.cupo_actual as inscritos,
    CASE 
        WHEN e.hora_fin < SYSDATE - INTERVAL '30' MINUTE THEN 'Puede generar'
        WHEN e.hora_fin < SYSDATE THEN 'Esperando 30 min'
        ELSE 'Aun no finaliza'
    END as estado_generacion
FROM eventos e
WHERE e.genera_constancia = 1
ORDER BY e.hora_fin DESC;
EXIT;
"@

docker exec congreso_oracle_db bash -c "echo '$query1' | sqlplus -s congreso_user/congreso_pass@FREEPDB1"

Write-Host "`n"

# Query 2: Resumen por evento
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "2. RESUMEN DE INSCRITOS Y ASISTENCIA" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════════`n" -ForegroundColor Cyan

$query2 = @"
SET PAGESIZE 50
SET LINESIZE 200
COLUMN nombre_evento FORMAT A30

SELECT 
    e.id_evento,
    e.nombre_evento,
    COUNT(DISTINCT i.id_inscripcion) as inscritos,
    COUNT(DISTINCT CASE WHEN a.hora_entrada IS NOT NULL THEN a.id_usuario END) as con_entrada,
    COUNT(DISTINCT CASE WHEN a.hora_salida IS NOT NULL THEN a.id_usuario END) as con_salida,
    COUNT(DISTINCT CASE WHEN a.hora_entrada IS NOT NULL AND a.hora_salida IS NOT NULL THEN a.id_usuario END) as completa
FROM eventos e
LEFT JOIN inscripciones i ON e.id_evento = i.id_evento AND i.estado = 'Inscrito'
LEFT JOIN asistencias a ON i.id_usuario = a.id_usuario AND i.id_evento = a.id_evento
WHERE e.genera_constancia = 1
GROUP BY e.id_evento, e.nombre_evento
ORDER BY e.id_evento;
EXIT;
"@

docker exec congreso_oracle_db bash -c "echo '$query2' | sqlplus -s congreso_user/congreso_pass@FREEPDB1"

Write-Host "`n"

# Query 3: Constancias generadas
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "3. CONSTANCIAS YA GENERADAS" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════════`n" -ForegroundColor Cyan

$query3 = @"
SET PAGESIZE 50
SET LINESIZE 200
COLUMN nombre_evento FORMAT A30
COLUMN nombre_completo FORMAT A25
COLUMN fecha_generacion FORMAT A20

SELECT 
    e.nombre_evento,
    u.nombre_completo,
    u.matricula,
    TO_CHAR(c.fecha_emision, 'YYYY-MM-DD HH24:MI') as fecha_generacion
FROM constancias c
JOIN usuarios u ON c.id_usuario = u.id_usuario
JOIN eventos e ON c.id_evento = e.id_evento
WHERE e.genera_constancia = 1
ORDER BY c.fecha_emision DESC;
EXIT;
"@

docker exec congreso_oracle_db bash -c "echo '$query3' | sqlplus -s congreso_user/congreso_pass@FREEPDB1"

Write-Host "`n"

# Preguntar si ver detalle de un evento
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "¿Deseas ver el detalle de usuarios de un evento específico?" -ForegroundColor Yellow
$verDetalle = Read-Host "Ingresa el ID del evento (o Enter para salir)"

if ($verDetalle -match '^\d+$') {
    Write-Host "`n═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
    Write-Host "4. DETALLE DE USUARIOS - EVENTO ID: $verDetalle" -ForegroundColor Yellow
    Write-Host "═══════════════════════════════════════════════════════════`n" -ForegroundColor Cyan
    
    $query4 = @"
SET PAGESIZE 100
SET LINESIZE 250
COLUMN nombre_evento FORMAT A25
COLUMN nombre_completo FORMAT A25
COLUMN estado_inscripcion FORMAT A12
COLUMN registro_entrada FORMAT A10
COLUMN registro_salida FORMAT A10
COLUMN estado_constancia FORMAT A20

SELECT 
    e.nombre_evento,
    u.nombre_completo,
    u.matricula,
    i.estado as estado_inscripcion,
    CASE WHEN a.hora_entrada IS NOT NULL THEN 'Si' ELSE 'No' END as entrada,
    CASE WHEN a.hora_salida IS NOT NULL THEN 'Si' ELSE 'No' END as salida,
    CASE 
        WHEN a.hora_entrada IS NOT NULL AND a.hora_salida IS NOT NULL THEN 'ELEGIBLE'
        WHEN a.hora_entrada IS NOT NULL THEN 'Falta salida'
        WHEN i.id_inscripcion IS NOT NULL THEN 'Sin asistencia'
        ELSE 'No inscrito'
    END as estado_constancia,
    CASE WHEN c.id_constancia IS NOT NULL THEN 'Generada' ELSE '-' END as constancia
FROM eventos e
LEFT JOIN inscripciones i ON e.id_evento = i.id_evento
LEFT JOIN usuarios u ON i.id_usuario = u.id_usuario
LEFT JOIN asistencias a ON i.id_usuario = a.id_usuario AND i.id_evento = a.id_evento
LEFT JOIN constancias c ON i.id_usuario = c.id_usuario AND i.id_evento = c.id_evento
WHERE e.id_evento = $verDetalle
ORDER BY estado_constancia DESC, u.nombre_completo;
EXIT;
"@
    
    docker exec congreso_oracle_db bash -c "echo '$query4' | sqlplus -s congreso_user/congreso_pass@FREEPDB1"
}

Write-Host "`n═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "VERIFICACIÓN COMPLETADA" -ForegroundColor Green
Write-Host "═══════════════════════════════════════════════════════════`n" -ForegroundColor Cyan

Write-Host "💡 INTERPRETACIÓN:" -ForegroundColor Yellow
Write-Host "   • ELEGIBLE = Usuario inscrito con entrada Y salida completa" -ForegroundColor White
Write-Host "   • Falta salida = Usuario registró entrada pero no salida" -ForegroundColor White
Write-Host "   • Sin asistencia = Usuario inscrito pero no ha registrado asistencia" -ForegroundColor White
Write-Host ""

Read-Host "Presiona ENTER para salir"
