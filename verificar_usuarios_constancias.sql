-- verificar_usuarios_constancias.sql
-- Script para verificar el estado de usuarios para constancias

SET PAGESIZE 100
SET LINESIZE 200

PROMPT ================================================================
PROMPT   VERIFICACIÓN DE USUARIOS PARA CONSTANCIAS
PROMPT ================================================================

PROMPT
PROMPT 1. EVENTOS QUE GENERAN CONSTANCIAS:
PROMPT ----------------------------------------------------------------
SELECT 
    e.id_evento,
    e.nombre_evento,
    e.tipo_evento,
    e.genera_constancia,
    TO_CHAR(e.hora_fin, 'YYYY-MM-DD HH24:MI') as hora_fin,
    e.cupo_actual as inscritos,
    CASE 
        WHEN e.hora_fin < SYSDATE - INTERVAL '30' MINUTE THEN 'Puede generar'
        WHEN e.hora_fin < SYSDATE THEN 'Esperando 30 min'
        ELSE 'Aún no finaliza'
    END as estado_generacion
FROM eventos e
WHERE e.genera_constancia = 1
ORDER BY e.hora_fin DESC;

PROMPT
PROMPT 2. USUARIOS INSCRITOS POR EVENTO:
PROMPT ----------------------------------------------------------------
SELECT 
    e.id_evento,
    e.nombre_evento,
    COUNT(i.id_inscripcion) as total_inscritos,
    SUM(CASE WHEN a.hora_entrada IS NOT NULL THEN 1 ELSE 0 END) as con_entrada,
    SUM(CASE WHEN a.hora_salida IS NOT NULL THEN 1 ELSE 0 END) as con_salida,
    SUM(CASE WHEN a.hora_entrada IS NOT NULL AND a.hora_salida IS NOT NULL THEN 1 ELSE 0 END) as asistencia_completa
FROM eventos e
LEFT JOIN inscripciones i ON e.id_evento = i.id_evento AND i.estado = 'Inscrito'
LEFT JOIN asistencias a ON i.id_usuario = a.id_usuario AND i.id_evento = a.id_evento
WHERE e.genera_constancia = 1
GROUP BY e.id_evento, e.nombre_evento
ORDER BY e.id_evento;

PROMPT
PROMPT 3. DETALLE DE USUARIOS CON/SIN ASISTENCIA COMPLETA:
PROMPT ----------------------------------------------------------------
SELECT 
    e.nombre_evento,
    u.nombre_completo,
    u.matricula,
    i.estado as estado_inscripcion,
    CASE WHEN a.hora_entrada IS NOT NULL THEN 'Sí' ELSE 'No' END as registro_entrada,
    CASE WHEN a.hora_salida IS NOT NULL THEN 'Sí' ELSE 'No' END as registro_salida,
    CASE 
        WHEN a.hora_entrada IS NOT NULL AND a.hora_salida IS NOT NULL THEN 'ELEGIBLE'
        WHEN a.hora_entrada IS NOT NULL THEN 'Falta salida'
        WHEN i.id_inscripcion IS NOT NULL THEN 'Sin asistencia'
        ELSE 'No inscrito'
    END as estado_para_constancia,
    CASE WHEN c.id_constancia IS NOT NULL THEN 'Sí' ELSE 'No' END as constancia_generada
FROM eventos e
LEFT JOIN inscripciones i ON e.id_evento = i.id_evento
LEFT JOIN usuarios u ON i.id_usuario = u.id_usuario
LEFT JOIN asistencias a ON i.id_usuario = a.id_usuario AND i.id_evento = a.id_evento
LEFT JOIN constancias c ON i.id_usuario = c.id_usuario AND i.id_evento = c.id_evento
WHERE e.genera_constancia = 1
  AND e.id_evento = &evento_id
ORDER BY estado_para_constancia DESC, u.nombre_completo;

PROMPT
PROMPT 4. CONSTANCIAS YA GENERADAS:
PROMPT ----------------------------------------------------------------
SELECT 
    e.nombre_evento,
    u.nombre_completo,
    u.matricula,
    TO_CHAR(c.fecha_emision, 'YYYY-MM-DD HH24:MI:SS') as fecha_generacion,
    c.ruta_archivo_pdf
FROM constancias c
JOIN usuarios u ON c.id_usuario = u.id_usuario
JOIN eventos e ON c.id_evento = e.id_evento
WHERE e.genera_constancia = 1
ORDER BY c.fecha_emision DESC;

PROMPT
PROMPT ================================================================
PROMPT   FIN DE VERIFICACIÓN
PROMPT ================================================================
PROMPT
PROMPT Para ejecutar este script:
PROMPT   @verificar_usuarios_constancias.sql
PROMPT   Ingresa el ID del evento cuando se solicite
PROMPT
