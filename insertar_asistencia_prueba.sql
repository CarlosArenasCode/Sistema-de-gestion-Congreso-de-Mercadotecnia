-- Script de prueba: Insertar asistencia para el usuario Joshua al evento 25
-- Ejecutar este script si quieres probar la generación de constancias

INSERT INTO asistencias (id_usuario, id_evento, fecha_asistencia)
VALUES (1, 25, SYSDATE);

COMMIT;

-- Verificar inserción
SELECT a.*, u.nombre_completo, e.nombre_evento
FROM asistencias a
JOIN usuarios u ON a.id_usuario = u.id_usuario
JOIN eventos e ON a.id_evento = e.id_evento
WHERE a.id_usuario = 1 AND a.id_evento = 25;
