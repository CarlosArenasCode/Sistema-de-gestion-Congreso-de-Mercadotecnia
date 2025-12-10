-- =====================================================
-- Script: 05_cursores_ejemplos.sql
-- Descripción: Implementación de 3 tipos diferentes de cursores en PL/SQL
-- Requisito: Tener por lo menos 3 cursores de distintos tipos
-- Base de datos: Oracle Database 23ai Free
-- =====================================================

SET SERVEROUTPUT ON;
SET DEFINE OFF;

-- =====================================================
-- CURSOR TIPO 1: CURSOR EXPLÍCITO
-- Descripción: Listar todas las asistencias con validación manual
-- =====================================================

CREATE OR REPLACE PROCEDURE proc_listar_asistencias_completas AS
    -- Declaración de cursor explícito
    CURSOR cur_asistencias IS
        SELECT 
            asist.id_asistencia,
            u.nombre_completo,
            e.nombre_evento,
            asist.fecha_asistencia,
            asist.constancia_generada,
            e.tipo_evento
        FROM asistencias asist
        INNER JOIN usuarios u ON asist.id_usuario = u.id_usuario
        INNER JOIN eventos e ON asist.id_evento = e.id_evento
        ORDER BY asist.fecha_asistencia DESC;
    
    -- Variable para almacenar cada registro
    v_asistencia cur_asistencias%ROWTYPE;
    v_contador NUMBER := 0;
    
BEGIN
    DBMS_OUTPUT.PUT_LINE('=================================================');
    DBMS_OUTPUT.PUT_LINE('REPORTE DE ASISTENCIAS (CURSOR EXPLÍCITO)');
    DBMS_OUTPUT.PUT_LINE('=================================================');
    DBMS_OUTPUT.PUT_LINE('');
    
    -- Abrir el cursor
    OPEN cur_asistencias;
    
    -- Ciclo de lectura manual
    LOOP
        -- Leer el siguiente registro
        FETCH cur_asistencias INTO v_asistencia;
        
        -- Salir si no hay más registros
        EXIT WHEN cur_asistencias%NOTFOUND;
        
        v_contador := v_contador + 1;
        
        -- Mostrar información del registro
        DBMS_OUTPUT.PUT_LINE('Asistencia #' || v_contador);
        DBMS_OUTPUT.PUT_LINE('  Usuario: ' || v_asistencia.nombre_completo);
        DBMS_OUTPUT.PUT_LINE('  Evento: ' || v_asistencia.nombre_evento);
        DBMS_OUTPUT.PUT_LINE('  Tipo: ' || v_asistencia.tipo_evento);
        DBMS_OUTPUT.PUT_LINE('  Fecha: ' || TO_CHAR(v_asistencia.fecha_asistencia, 'DD/MM/YYYY'));
        DBMS_OUTPUT.PUT_LINE('  Constancia: ' || CASE WHEN v_asistencia.constancia_generada = 1 THEN 'Generada' ELSE 'Pendiente' END);
        DBMS_OUTPUT.PUT_LINE('-------------------------------------------------');
    END LOOP;
    
    -- Cerrar el cursor
    CLOSE cur_asistencias;
    
    DBMS_OUTPUT.PUT_LINE('');
    DBMS_OUTPUT.PUT_LINE('Total de asistencias procesadas: ' || v_contador);
    DBMS_OUTPUT.PUT_LINE('=================================================');
    
EXCEPTION
    WHEN OTHERS THEN
        IF cur_asistencias%ISOPEN THEN
            CLOSE cur_asistencias;
        END IF;
        DBMS_OUTPUT.PUT_LINE('Error: ' || SQLERRM);
        RAISE;
END proc_listar_asistencias_completas;
/

-- =====================================================
-- CURSOR TIPO 2: CURSOR PARAMETRIZADO
-- Descripción: Obtener eventos de una fecha específica con parámetros
-- =====================================================

CREATE OR REPLACE PROCEDURE proc_eventos_por_fecha(
    p_fecha_inicio IN DATE DEFAULT SYSDATE
) AS
    -- Cursor parametrizado que acepta una fecha como parámetro
    CURSOR cur_eventos_fecha(cp_fecha DATE) IS
        SELECT 
            e.id_evento,
            e.nombre_evento,
            e.descripcion,
            e.fecha_inicio,
            e.hora_inicio,
            e.lugar,
            e.ponente,
            e.cupo_maximo,
            e.cupo_actual,
            (e.cupo_maximo - e.cupo_actual) AS cupos_disponibles,
            e.tipo_evento
        FROM eventos e
        WHERE TRUNC(e.fecha_inicio) = TRUNC(cp_fecha)
        ORDER BY e.hora_inicio;
    
    v_total_eventos NUMBER := 0;
    v_total_cupos NUMBER := 0;
    
BEGIN
    DBMS_OUTPUT.PUT_LINE('=================================================');
    DBMS_OUTPUT.PUT_LINE('EVENTOS PROGRAMADOS (CURSOR PARAMETRIZADO)');
    DBMS_OUTPUT.PUT_LINE('Fecha: ' || TO_CHAR(p_fecha_inicio, 'DD/MM/YYYY'));
    DBMS_OUTPUT.PUT_LINE('=================================================');
    DBMS_OUTPUT.PUT_LINE('');
    
    -- Uso del cursor parametrizado con FOR LOOP
    FOR v_evento IN cur_eventos_fecha(p_fecha_inicio) LOOP
        v_total_eventos := v_total_eventos + 1;
        v_total_cupos := v_total_cupos + v_evento.cupos_disponibles;
        
        DBMS_OUTPUT.PUT_LINE('Evento: ' || v_evento.nombre_evento);
        DBMS_OUTPUT.PUT_LINE('  Tipo: ' || v_evento.tipo_evento);
        DBMS_OUTPUT.PUT_LINE('  Hora: ' || TO_CHAR(v_evento.hora_inicio, 'HH24:MI'));
        DBMS_OUTPUT.PUT_LINE('  Lugar: ' || NVL(v_evento.lugar, 'No especificado'));
        DBMS_OUTPUT.PUT_LINE('  Ponente: ' || NVL(v_evento.ponente, 'Por definir'));
        DBMS_OUTPUT.PUT_LINE('  Cupos: ' || v_evento.cupo_actual || '/' || v_evento.cupo_maximo);
        DBMS_OUTPUT.PUT_LINE('  Disponibles: ' || v_evento.cupos_disponibles);
        DBMS_OUTPUT.PUT_LINE('-------------------------------------------------');
    END LOOP;
    
    DBMS_OUTPUT.PUT_LINE('');
    DBMS_OUTPUT.PUT_LINE('Total de eventos: ' || v_total_eventos);
    DBMS_OUTPUT.PUT_LINE('Total de cupos disponibles: ' || v_total_cupos);
    DBMS_OUTPUT.PUT_LINE('=================================================');
    
    -- Si no hay eventos
    IF v_total_eventos = 0 THEN
        DBMS_OUTPUT.PUT_LINE('No hay eventos programados para esta fecha.');
    END IF;
    
EXCEPTION
    WHEN OTHERS THEN
        DBMS_OUTPUT.PUT_LINE('Error: ' || SQLERRM);
        RAISE;
END proc_eventos_por_fecha;
/

-- =====================================================
-- CURSOR TIPO 3: CURSOR FOR UPDATE (con bloqueo de registros)
-- Descripción: Actualizar eventos con cupos llenos y bloquear registros
-- =====================================================

CREATE OR REPLACE PROCEDURE proc_actualizar_eventos_llenos AS
    -- Cursor con FOR UPDATE para bloquear registros durante actualización
    CURSOR cur_eventos_llenos IS
        SELECT 
            id_evento,
            nombre_evento,
            cupo_maximo,
            cupo_actual,
            fecha_inicio
        FROM eventos 
        WHERE cupo_actual >= cupo_maximo
        AND fecha_inicio >= SYSDATE
        FOR UPDATE NOWAIT;  -- Bloqueo inmediato sin espera
    
    v_eventos_procesados NUMBER := 0;
    v_total_ajustados NUMBER := 0;
    
BEGIN
    DBMS_OUTPUT.PUT_LINE('=================================================');
    DBMS_OUTPUT.PUT_LINE('ACTUALIZACIÓN DE EVENTOS LLENOS (CURSOR FOR UPDATE)');
    DBMS_OUTPUT.PUT_LINE('=================================================');
    DBMS_OUTPUT.PUT_LINE('');
    
    -- Procesar cada evento con cupo lleno
    FOR v_evento IN cur_eventos_llenos LOOP
        v_eventos_procesados := v_eventos_procesados + 1;
        
        DBMS_OUTPUT.PUT_LINE('Procesando: ' || v_evento.nombre_evento);
        DBMS_OUTPUT.PUT_LINE('  Cupo actual: ' || v_evento.cupo_actual);
        DBMS_OUTPUT.PUT_LINE('  Cupo máximo: ' || v_evento.cupo_maximo);
        
        -- Si el cupo actual excede el máximo, ajustarlo
        IF v_evento.cupo_actual > v_evento.cupo_maximo THEN
            UPDATE eventos 
            SET cupo_actual = cupo_maximo 
            WHERE CURRENT OF cur_eventos_llenos;  -- Actualizar el registro actual bloqueado
            
            v_total_ajustados := v_total_ajustados + 1;
            DBMS_OUTPUT.PUT_LINE('  ✓ Cupo ajustado de ' || v_evento.cupo_actual || ' a ' || v_evento.cupo_maximo);
        ELSE
            DBMS_OUTPUT.PUT_LINE('  ✓ Cupo correcto, no requiere ajuste');
        END IF;
        
        DBMS_OUTPUT.PUT_LINE('-------------------------------------------------');
    END LOOP;
    
    -- Confirmar cambios
    COMMIT;
    
    DBMS_OUTPUT.PUT_LINE('');
    DBMS_OUTPUT.PUT_LINE('Resumen:');
    DBMS_OUTPUT.PUT_LINE('  Eventos revisados: ' || v_eventos_procesados);
    DBMS_OUTPUT.PUT_LINE('  Eventos ajustados: ' || v_total_ajustados);
    DBMS_OUTPUT.PUT_LINE('  Cambios confirmados: ✓');
    DBMS_OUTPUT.PUT_LINE('=================================================');
    
    IF v_eventos_procesados = 0 THEN
        DBMS_OUTPUT.PUT_LINE('No hay eventos con cupo lleno pendientes de revisar.');
    END IF;
    
EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        DBMS_OUTPUT.PUT_LINE('Error durante la actualización: ' || SQLERRM);
        RAISE;
END proc_actualizar_eventos_llenos;
/

-- =====================================================
-- SCRIPT DE VERIFICACIÓN Y PRUEBA
-- =====================================================

PROMPT
PROMPT ========================================
PROMPT  Procedimientos creados exitosamente
PROMPT ========================================
PROMPT
PROMPT Los siguientes procedimientos están disponibles:
PROMPT
PROMPT 1. proc_listar_asistencias_completas
PROMPT    - Tipo: CURSOR EXPLÍCITO
PROMPT    - Función: Lista todas las asistencias con detalles
PROMPT    - Uso: EXEC proc_listar_asistencias_completas;
PROMPT
PROMPT 2. proc_eventos_por_fecha(fecha)
PROMPT    - Tipo: CURSOR PARAMETRIZADO
PROMPT    - Función: Obtiene eventos de una fecha específica
PROMPT    - Uso: EXEC proc_eventos_por_fecha(SYSDATE);
PROMPT    - Uso: EXEC proc_eventos_por_fecha(TO_DATE('2025-12-01', 'YYYY-MM-DD'));
PROMPT
PROMPT 3. proc_actualizar_eventos_llenos
PROMPT    - Tipo: CURSOR FOR UPDATE
PROMPT    - Función: Actualiza y ajusta cupos de eventos llenos
PROMPT    - Uso: EXEC proc_actualizar_eventos_llenos;
PROMPT
PROMPT ========================================
PROMPT  Ejecutando pruebas de ejemplo...
PROMPT ========================================
PROMPT

-- Prueba 1: Listar asistencias
PROMPT === PRUEBA 1: Listar asistencias ===
BEGIN
    proc_listar_asistencias_completas;
END;
/

-- Prueba 2: Eventos de hoy
PROMPT
PROMPT === PRUEBA 2: Eventos programados para hoy ===
BEGIN
    proc_eventos_por_fecha(SYSDATE);
END;
/

-- Prueba 3: Actualizar eventos llenos
PROMPT
PROMPT === PRUEBA 3: Actualizar eventos con cupo lleno ===
BEGIN
    proc_actualizar_eventos_llenos;
END;
/

PROMPT
PROMPT ========================================
PROMPT  Todas las pruebas completadas
PROMPT ========================================
PROMPT

-- =====================================================
-- DOCUMENTACIÓN ADICIONAL
-- =====================================================

COMMENT ON PROCEDURE proc_listar_asistencias_completas IS 'Cursor explícito: Lista todas las asistencias con detalles de usuario y evento';
COMMENT ON PROCEDURE proc_eventos_por_fecha IS 'Cursor parametrizado: Obtiene eventos filtrados por fecha';
COMMENT ON PROCEDURE proc_actualizar_eventos_llenos IS 'Cursor FOR UPDATE: Actualiza eventos con cupo lleno bloqueando registros';

-- Fin del script
