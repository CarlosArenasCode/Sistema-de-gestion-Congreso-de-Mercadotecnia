-- =====================================================
-- Script: 06_reporte_division.sql
-- Descripción: Reportes usando el operador de DIVISIÓN relacional
-- Requisito: Tener por lo menos un reporte de división
-- Base de datos: Oracle Database 23ai Free
-- =====================================================

SET SERVEROUTPUT ON;
SET DEFINE OFF;
SET LINESIZE 200;
SET PAGESIZE 100;

-- =====================================================
-- REPORTE DE DIVISIÓN 1: 
-- Usuarios que han asistido a TODOS los eventos
-- =====================================================

PROMPT
PROMPT ========================================
PROMPT  REPORTE 1: División Relacional
PROMPT  Usuarios que asistieron a TODOS los eventos
PROMPT ========================================
PROMPT

-- Método 1: Usando NOT EXISTS (División por doble negación)
SELECT 
    u.id_usuario,
    u.nombre_completo,
    u.matricula,
    u.email,
    u.rol,
    COUNT(DISTINCT a.id_evento) AS total_eventos_asistidos,
    (SELECT COUNT(*) FROM eventos) AS total_eventos_sistema
FROM usuarios u
WHERE NOT EXISTS (
    -- No existe un evento...
    SELECT e.id_evento
    FROM eventos e
    WHERE NOT EXISTS (
        -- ...al que el usuario NO haya asistido
        SELECT 1
        FROM asistencias a
        WHERE a.id_usuario = u.id_usuario
        AND a.id_evento = e.id_evento
    )
)
GROUP BY u.id_usuario, u.nombre_completo, u.matricula, u.email, u.rol
ORDER BY u.nombre_completo;

PROMPT
PROMPT Nota: Si no aparecen resultados, significa que ningún usuario ha asistido a TODOS los eventos.
PROMPT

-- =====================================================
-- REPORTE DE DIVISIÓN 2:
-- Alumnos inscritos en TODOS los talleres
-- =====================================================

PROMPT
PROMPT ========================================
PROMPT  REPORTE 2: División Relacional
PROMPT  Alumnos inscritos en TODOS los talleres
PROMPT ========================================
PROMPT

SELECT 
    u.id_usuario,
    u.nombre_completo,
    u.matricula,
    u.semestre,
    COUNT(DISTINCT i.id_evento) AS talleres_inscritos,
    (SELECT COUNT(*) FROM eventos WHERE tipo_evento = 'taller') AS total_talleres
FROM usuarios u
INNER JOIN inscripciones i ON u.id_usuario = i.id_usuario
WHERE u.rol = 'alumno'
AND NOT EXISTS (
    -- No existe un taller...
    SELECT e.id_evento
    FROM eventos e
    WHERE e.tipo_evento = 'taller'
    AND NOT EXISTS (
        -- ...en el que el alumno NO esté inscrito
        SELECT 1
        FROM inscripciones ins
        WHERE ins.id_usuario = u.id_usuario
        AND ins.id_evento = e.id_evento
        AND ins.estado = 'Inscrito'
    )
)
GROUP BY u.id_usuario, u.nombre_completo, u.matricula, u.semestre
ORDER BY u.semestre, u.nombre_completo;

PROMPT
PROMPT Nota: Muestra alumnos que se inscribieron en el 100% de los talleres disponibles.
PROMPT

-- =====================================================
-- REPORTE DE DIVISIÓN 3:
-- Eventos con asistencia completa (TODOS los inscritos asistieron)
-- =====================================================

PROMPT
PROMPT ========================================
PROMPT  REPORTE 3: División Relacional Inversa
PROMPT  Eventos donde TODOS los inscritos asistieron
PROMPT ========================================
PROMPT

SELECT 
    e.id_evento,
    e.nombre_evento,
    e.tipo_evento,
    TO_CHAR(e.fecha_inicio, 'DD/MM/YYYY') AS fecha,
    e.cupo_actual AS total_inscritos,
    COUNT(DISTINCT a.id_usuario) AS total_asistencias,
    ROUND((COUNT(DISTINCT a.id_usuario) / NULLIF(e.cupo_actual, 0)) * 100, 2) AS porcentaje_asistencia
FROM eventos e
INNER JOIN inscripciones i ON e.id_evento = i.id_evento
WHERE i.estado = 'Inscrito'
AND NOT EXISTS (
    -- No existe un inscrito...
    SELECT ins.id_usuario
    FROM inscripciones ins
    WHERE ins.id_evento = e.id_evento
    AND ins.estado = 'Inscrito'
    AND NOT EXISTS (
        -- ...que NO haya asistido
        SELECT 1
        FROM asistencias ast
        WHERE ast.id_usuario = ins.id_usuario
        AND ast.id_evento = e.id_evento
    )
)
LEFT JOIN asistencias a ON e.id_evento = a.id_evento
GROUP BY e.id_evento, e.nombre_evento, e.tipo_evento, e.fecha_inicio, e.cupo_actual
HAVING e.cupo_actual > 0
ORDER BY e.fecha_inicio DESC;

PROMPT
PROMPT Nota: Estos eventos tuvieron 100% de asistencia de sus inscritos.
PROMPT

-- =====================================================
-- REPORTE DE DIVISIÓN 4:
-- Profesores que impartieron en TODAS las fechas del congreso
-- =====================================================

PROMPT
PROMPT ========================================
PROMPT  REPORTE 4: División Relacional
PROMPT  Profesores que participaron TODAS las fechas
PROMPT ========================================
PROMPT

WITH fechas_congreso AS (
    SELECT DISTINCT TRUNC(fecha_inicio) AS fecha
    FROM eventos
)
SELECT 
    u.id_usuario,
    u.nombre_completo,
    u.email,
    COUNT(DISTINCT TRUNC(e.fecha_inicio)) AS fechas_participadas,
    (SELECT COUNT(*) FROM fechas_congreso) AS total_fechas_congreso
FROM usuarios u
INNER JOIN asistencias a ON u.id_usuario = a.id_usuario
INNER JOIN eventos e ON a.id_evento = e.id_evento
WHERE u.rol = 'profesor'
AND NOT EXISTS (
    -- No existe una fecha del congreso...
    SELECT fc.fecha
    FROM fechas_congreso fc
    WHERE NOT EXISTS (
        -- ...en la que el profesor NO haya participado
        SELECT 1
        FROM asistencias ast
        INNER JOIN eventos evt ON ast.id_evento = evt.id_evento
        WHERE ast.id_usuario = u.id_usuario
        AND TRUNC(evt.fecha_inicio) = fc.fecha
    )
)
GROUP BY u.id_usuario, u.nombre_completo, u.email
ORDER BY u.nombre_completo;

PROMPT
PROMPT Nota: Profesores con participación en el 100% de las fechas del congreso.
PROMPT

-- =====================================================
-- PROCEDIMIENTO ALMACENADO: Reporte de División Completo
-- =====================================================

CREATE OR REPLACE PROCEDURE proc_reporte_division_completo AS
    v_total_usuarios NUMBER := 0;
    v_total_eventos NUMBER := 0;
    v_usuarios_perfectos NUMBER := 0;
    
BEGIN
    DBMS_OUTPUT.PUT_LINE('=================================================');
    DBMS_OUTPUT.PUT_LINE('REPORTE DE DIVISIÓN RELACIONAL - ANÁLISIS COMPLETO');
    DBMS_OUTPUT.PUT_LINE('=================================================');
    DBMS_OUTPUT.PUT_LINE('');
    
    -- Contar totales
    SELECT COUNT(*) INTO v_total_usuarios FROM usuarios;
    SELECT COUNT(*) INTO v_total_eventos FROM eventos;
    
    DBMS_OUTPUT.PUT_LINE('Total de usuarios en sistema: ' || v_total_usuarios);
    DBMS_OUTPUT.PUT_LINE('Total de eventos en sistema: ' || v_total_eventos);
    DBMS_OUTPUT.PUT_LINE('');
    DBMS_OUTPUT.PUT_LINE('-------------------------------------------------');
    DBMS_OUTPUT.PUT_LINE('ANÁLISIS 1: Usuarios con asistencia perfecta');
    DBMS_OUTPUT.PUT_LINE('-------------------------------------------------');
    
    -- Usuarios que asistieron a TODOS los eventos
    FOR rec IN (
        SELECT 
            u.nombre_completo,
            u.rol,
            COUNT(DISTINCT a.id_evento) AS eventos_asistidos
        FROM usuarios u
        INNER JOIN asistencias a ON u.id_usuario = a.id_usuario
        WHERE NOT EXISTS (
            SELECT e.id_evento
            FROM eventos e
            WHERE NOT EXISTS (
                SELECT 1
                FROM asistencias ast
                WHERE ast.id_usuario = u.id_usuario
                AND ast.id_evento = e.id_evento
            )
        )
        GROUP BY u.id_usuario, u.nombre_completo, u.rol
        ORDER BY u.nombre_completo
    ) LOOP
        v_usuarios_perfectos := v_usuarios_perfectos + 1;
        DBMS_OUTPUT.PUT_LINE('✓ ' || rec.nombre_completo || ' (' || rec.rol || ')');
        DBMS_OUTPUT.PUT_LINE('  Asistió a: ' || rec.eventos_asistidos || '/' || v_total_eventos || ' eventos');
    END LOOP;
    
    IF v_usuarios_perfectos = 0 THEN
        DBMS_OUTPUT.PUT_LINE('No hay usuarios con asistencia perfecta a todos los eventos.');
    ELSE
        DBMS_OUTPUT.PUT_LINE('');
        DBMS_OUTPUT.PUT_LINE('Total de usuarios con asistencia perfecta: ' || v_usuarios_perfectos);
    END IF;
    
    DBMS_OUTPUT.PUT_LINE('');
    DBMS_OUTPUT.PUT_LINE('-------------------------------------------------');
    DBMS_OUTPUT.PUT_LINE('ANÁLISIS 2: Eventos con asistencia completa');
    DBMS_OUTPUT.PUT_LINE('-------------------------------------------------');
    
    -- Eventos donde TODOS los inscritos asistieron
    FOR rec IN (
        SELECT 
            e.nombre_evento,
            e.tipo_evento,
            e.cupo_actual AS inscritos,
            COUNT(DISTINCT a.id_usuario) AS asistentes
        FROM eventos e
        INNER JOIN inscripciones i ON e.id_evento = i.id_evento
        LEFT JOIN asistencias a ON e.id_evento = a.id_evento
        WHERE i.estado = 'Inscrito'
        AND e.cupo_actual > 0
        AND NOT EXISTS (
            SELECT ins.id_usuario
            FROM inscripciones ins
            WHERE ins.id_evento = e.id_evento
            AND ins.estado = 'Inscrito'
            AND NOT EXISTS (
                SELECT 1
                FROM asistencias ast
                WHERE ast.id_usuario = ins.id_usuario
                AND ast.id_evento = e.id_evento
            )
        )
        GROUP BY e.id_evento, e.nombre_evento, e.tipo_evento, e.cupo_actual
        ORDER BY e.nombre_evento
    ) LOOP
        DBMS_OUTPUT.PUT_LINE('✓ ' || rec.nombre_evento || ' (' || rec.tipo_evento || ')');
        DBMS_OUTPUT.PUT_LINE('  Asistencia: ' || rec.asistentes || '/' || rec.inscritos || ' (100%)');
    END LOOP;
    
    DBMS_OUTPUT.PUT_LINE('');
    DBMS_OUTPUT.PUT_LINE('=================================================');
    DBMS_OUTPUT.PUT_LINE('Reporte generado exitosamente');
    DBMS_OUTPUT.PUT_LINE('=================================================');
    
EXCEPTION
    WHEN OTHERS THEN
        DBMS_OUTPUT.PUT_LINE('Error al generar reporte: ' || SQLERRM);
        RAISE;
END proc_reporte_division_completo;
/

-- =====================================================
-- VISTA: Usuarios con asistencia perfecta
-- =====================================================

CREATE OR REPLACE VIEW v_usuarios_asistencia_perfecta AS
SELECT 
    u.id_usuario,
    u.nombre_completo,
    u.matricula,
    u.rol,
    COUNT(DISTINCT a.id_evento) AS total_eventos_asistidos,
    (SELECT COUNT(*) FROM eventos) AS total_eventos_sistema,
    ROUND((COUNT(DISTINCT a.id_evento) / (SELECT COUNT(*) FROM eventos)) * 100, 2) AS porcentaje_asistencia
FROM usuarios u
INNER JOIN asistencias a ON u.id_usuario = a.id_usuario
WHERE NOT EXISTS (
    SELECT e.id_evento
    FROM eventos e
    WHERE NOT EXISTS (
        SELECT 1
        FROM asistencias ast
        WHERE ast.id_usuario = u.id_usuario
        AND ast.id_evento = e.id_evento
    )
)
GROUP BY u.id_usuario, u.nombre_completo, u.matricula, u.rol;

COMMENT ON VIEW v_usuarios_asistencia_perfecta IS 'Vista de división: Usuarios que asistieron a TODOS los eventos del sistema';

-- =====================================================
-- PRUEBAS Y VERIFICACIÓN
-- =====================================================

PROMPT
PROMPT ========================================
PROMPT  Ejecutando reporte de división completo
PROMPT ========================================
PROMPT

BEGIN
    proc_reporte_division_completo;
END;
/

PROMPT
PROMPT ========================================
PROMPT  Consultando vista de asistencia perfecta
PROMPT ========================================
PROMPT

SELECT * FROM v_usuarios_asistencia_perfecta;

PROMPT
PROMPT ========================================
PROMPT  Script de división completado
PROMPT ========================================
PROMPT
PROMPT Los siguientes objetos fueron creados:
PROMPT
PROMPT 1. proc_reporte_division_completo
PROMPT    - Procedimiento que genera análisis completo de división
PROMPT    - Uso: EXEC proc_reporte_division_completo;
PROMPT
PROMPT 2. v_usuarios_asistencia_perfecta
PROMPT    - Vista de usuarios con asistencia a TODOS los eventos
PROMPT    - Uso: SELECT * FROM v_usuarios_asistencia_perfecta;
PROMPT
PROMPT ========================================

-- Fin del script
