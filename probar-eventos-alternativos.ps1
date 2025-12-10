# Script para Probar Sistema de Eventos Alternativos
# Ejecutar en PowerShell

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  PRUEBA: Sistema de Eventos Alternativos" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""

# Función para ejecutar SQL en Oracle
function Invoke-OracleSQL {
    param([string]$Query)
    docker exec congreso_oracle_db bash -c "echo `"$Query`" | sqlplus -S congreso_user/congreso_pass@FREEPDB1"
}

Write-Host "[1/5] Verificando eventos existentes..." -ForegroundColor Yellow
Write-Host ""

$query = @"
SELECT 
    id_evento, 
    nombre_evento, 
    tipo_evento,
    TO_CHAR(fecha_inicio, 'YYYY-MM-DD') as fecha,
    cupo_actual || '/' || cupo_maximo as cupos
FROM eventos 
WHERE fecha_inicio >= TRUNC(SYSDATE)
ORDER BY fecha_inicio;
"@

Invoke-OracleSQL -Query $query

Write-Host ""
Write-Host "[2/5] Creando eventos de prueba..." -ForegroundColor Yellow

$createEvents = @"
-- Evento que estará LLENO
INSERT INTO eventos (
    nombre_evento, descripcion, fecha_inicio, hora_inicio, 
    fecha_fin, hora_fin, lugar, ponente, cupo_maximo, cupo_actual,
    genera_constancia, tipo_evento, horas_para_constancia
) VALUES (
    'TALLER LLENO - Prueba',
    'Este taller está completo para pruebas',
    TO_DATE('2025-12-10', 'YYYY-MM-DD'),
    TO_TIMESTAMP('2025-12-10 10:00:00', 'YYYY-MM-DD HH24:MI:SS'),
    TO_DATE('2025-12-10', 'YYYY-MM-DD'),
    TO_TIMESTAMP('2025-12-10 12:00:00', 'YYYY-MM-DD HH24:MI:SS'),
    'Aula 999',
    'Dr. Test',
    10, 10, 1, 'taller', 2.0
);

-- Alternativa 1: Mismo ponente (mayor prioridad)
INSERT INTO eventos (
    nombre_evento, descripcion, fecha_inicio, hora_inicio, 
    fecha_fin, hora_fin, lugar, ponente, cupo_maximo, cupo_actual,
    genera_constancia, tipo_evento, horas_para_constancia
) VALUES (
    'TALLER ALTERNATIVO 1 - Mismo Ponente',
    'Primera alternativa disponible',
    TO_DATE('2025-12-11', 'YYYY-MM-DD'),
    TO_TIMESTAMP('2025-12-11 14:00:00', 'YYYY-MM-DD HH24:MI:SS'),
    TO_DATE('2025-12-11', 'YYYY-MM-DD'),
    TO_TIMESTAMP('2025-12-11 16:00:00', 'YYYY-MM-DD HH24:MI:SS'),
    'Aula 888',
    'Dr. Test',
    20, 5, 1, 'taller', 2.0
);

-- Alternativa 2: Otro ponente
INSERT INTO eventos (
    nombre_evento, descripcion, fecha_inicio, hora_inicio, 
    fecha_fin, hora_fin, lugar, ponente, cupo_maximo, cupo_actual,
    genera_constancia, tipo_evento, horas_para_constancia
) VALUES (
    'TALLER ALTERNATIVO 2 - Otro Ponente',
    'Segunda alternativa disponible',
    TO_DATE('2025-12-12', 'YYYY-MM-DD'),
    TO_TIMESTAMP('2025-12-12 09:00:00', 'YYYY-MM-DD HH24:MI:SS'),
    TO_DATE('2025-12-12', 'YYYY-MM-DD'),
    TO_TIMESTAMP('2025-12-12 11:00:00', 'YYYY-MM-DD HH24:MI:SS'),
    'Aula 777',
    'Ing. Alternativo',
    25, 8, 1, 'taller', 2.0
);

-- Conferencia (diferente tipo - NO debe aparecer como alternativa)
INSERT INTO eventos (
    nombre_evento, descripcion, fecha_inicio, hora_inicio, 
    fecha_fin, hora_fin, lugar, ponente, cupo_maximo, cupo_actual,
    genera_constancia, tipo_evento, horas_para_constancia
) VALUES (
    'CONFERENCIA - NO Alternativa',
    'Diferente tipo de evento',
    TO_DATE('2025-12-11', 'YYYY-MM-DD'),
    TO_TIMESTAMP('2025-12-11 10:00:00', 'YYYY-MM-DD HH24:MI:SS'),
    TO_DATE('2025-12-11', 'YYYY-MM-DD'),
    TO_TIMESTAMP('2025-12-11 12:00:00', 'YYYY-MM-DD HH24:MI:SS'),
    'Auditorio',
    'Dr. Test',
    100, 20, 1, 'conferencia', 1.0
);

COMMIT;
"@

Invoke-OracleSQL -Query $createEvents
Write-Host "✅ Eventos de prueba creados" -ForegroundColor Green

Write-Host ""
Write-Host "[3/5] Obteniendo ID del evento lleno..." -ForegroundColor Yellow

$getIdQuery = "SELECT id_evento FROM eventos WHERE nombre_evento = 'TALLER LLENO - Prueba';"
$resultado = Invoke-OracleSQL -Query $getIdQuery

# Extraer ID del evento (simplificado)
Write-Host $resultado
Write-Host ""

Write-Host "[4/5] Probando endpoint de eventos alternativos..." -ForegroundColor Yellow
Write-Host ""
Write-Host "⚠️  INSTRUCCIONES MANUALES:" -ForegroundColor Yellow
Write-Host "1. Abre: http://localhost:8081/Front-end/horario.html" -ForegroundColor White
Write-Host "2. Busca el evento: 'TALLER LLENO - Prueba'" -ForegroundColor White
Write-Host "3. Intenta inscribirte (el botón debería estar deshabilitado)" -ForegroundColor White
Write-Host "4. O usa la consola del navegador para forzar inscripción:" -ForegroundColor White
Write-Host ""
Write-Host "   // Reemplaza XXX con el ID del evento" -ForegroundColor Gray
Write-Host "   handleInscriptionAction(XXX, 'inscribir')" -ForegroundColor Cyan
Write-Host ""
Write-Host "5. Deberías ver un MODAL con 2 eventos alternativos:" -ForegroundColor White
Write-Host "   ✅ TALLER ALTERNATIVO 1 - Mismo Ponente (prioridad alta)" -ForegroundColor Green
Write-Host "   ✅ TALLER ALTERNATIVO 2 - Otro Ponente" -ForegroundColor Green
Write-Host "   ❌ NO debe aparecer: CONFERENCIA" -ForegroundColor Red
Write-Host ""

Write-Host "[5/5] Verificar estructura de eventos:" -ForegroundColor Yellow
Write-Host ""

$verifyQuery = @"
SELECT 
    nombre_evento,
    tipo_evento,
    TO_CHAR(fecha_inicio, 'DD-MON') as fecha,
    ponente,
    cupo_actual || '/' || cupo_maximo as cupos,
    CASE 
        WHEN cupo_actual >= cupo_maximo THEN 'LLENO ❌'
        ELSE 'Disponible ✅'
    END as estado
FROM eventos 
WHERE nombre_evento LIKE '%LLENO%' 
   OR nombre_evento LIKE '%ALTERNATIVO%'
ORDER BY fecha_inicio;
"@

Invoke-OracleSQL -Query $verifyQuery

Write-Host ""
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  PRUEBA LISTA" -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "📌 Próximos pasos:" -ForegroundColor Yellow
Write-Host "   1. Abre el navegador" -ForegroundColor White
Write-Host "   2. Ve a: http://localhost:8081/Front-end/horario.html" -ForegroundColor Cyan
Write-Host "   3. Intenta inscribirte al evento lleno" -ForegroundColor White
Write-Host "   4. Verifica que aparezca el modal con alternativas" -ForegroundColor White
Write-Host ""
Write-Host "🧹 Para limpiar después de las pruebas:" -ForegroundColor Yellow
Write-Host "   .\limpiar-eventos-prueba.ps1" -ForegroundColor Cyan
Write-Host ""
