<?php
// php/constancias_usuario.php
session_start();
require_once 'conexion.php';

// Limpiar buffer
if (ob_get_level()) ob_clean();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado.']);
    exit;
}
$id_usuario = $_SESSION['usuario_id'];

try {
    // Oracle: Reescribir consulta usando WITH y EXTRACT para INTERVAL
    $sql = "
        WITH potenciales AS (
            -- Eventos tipo taller donde el usuario está inscrito
            SELECT e.id_evento, e.nombre_evento, e.tipo_evento, e.horas_para_constancia
            FROM eventos e
            JOIN inscripciones i ON e.id_evento = i.id_evento
            WHERE i.id_usuario = :id_usuario1 AND e.tipo_evento = 'taller'
            
            UNION
            
            -- Eventos tipo conferencia donde el usuario tiene asistencia
            SELECT e.id_evento, e.nombre_evento, e.tipo_evento, e.horas_para_constancia
            FROM eventos e
            JOIN (SELECT DISTINCT id_evento FROM asistencias WHERE id_usuario = :id_usuario2) a ON e.id_evento = a.id_evento
            WHERE e.tipo_evento = 'conferencia'
        ),
        att AS (
            -- Datos de asistencia del usuario
            SELECT
                id_evento,
                SUM(CASE WHEN hora_salida IS NOT NULL THEN 1 ELSE 0 END) as asistencia_completa_count,
                -- Calcular duración en segundos desde hora_entrada y hora_salida
                SUM(
                    CASE 
                        WHEN hora_entrada IS NOT NULL AND hora_salida IS NOT NULL THEN
                            (EXTRACT(DAY FROM (hora_salida - hora_entrada)) * 86400 +
                             EXTRACT(HOUR FROM (hora_salida - hora_entrada)) * 3600 +
                             EXTRACT(MINUTE FROM (hora_salida - hora_entrada)) * 60 +
                             EXTRACT(SECOND FROM (hora_salida - hora_entrada)))
                        ELSE 0
                    END
                ) as duracion_total_seg
            FROM asistencias
            WHERE id_usuario = :id_usuario3
            GROUP BY id_evento
        )
        SELECT
            potenciales.id_evento,
            potenciales.nombre_evento,
            potenciales.tipo_evento,
            potenciales.horas_para_constancia,
            NVL(att.asistencia_completa_count, 0) AS asistencia_completa,
            NVL(att.duracion_total_seg, 0) AS duracion_total_seg,
            c.ruta_archivo_pdf
        FROM potenciales
        LEFT JOIN att ON potenciales.id_evento = att.id_evento
        LEFT JOIN constancias c ON potenciales.id_evento = c.id_evento AND c.id_usuario = :id_usuario4
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_usuario1' => $id_usuario,
        ':id_usuario2' => $id_usuario,
        ':id_usuario3' => $id_usuario,
        ':id_usuario4' => $id_usuario
    ]);
    $eventos_con_datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $resultado = [];
    foreach ($eventos_con_datos as $evento) {
        $item = [
            'nombre_evento' => $evento['nombre_evento'],
            'estado' => 'Asistencia Incompleta',
            'url_descarga' => null
        ];

        $elegible = false;
        if ($evento['tipo_evento'] == 'conferencia') {
            if ($evento['asistencia_completa'] > 0) $elegible = true;
        } elseif ($evento['tipo_evento'] == 'taller') {
            if ($evento['duracion_total_seg']) {
                $duracion_requerida_seg = $evento['horas_para_constancia'] * 3600;
                if ($evento['duracion_total_seg'] >= $duracion_requerida_seg) $elegible = true;
            }
        }

        if ($elegible) {
            if ($evento['ruta_archivo_pdf']) {
                $item['estado'] = 'Disponible';
                $item['url_descarga'] = '../' . $evento['ruta_archivo_pdf'];
            } else {
                $item['estado'] = 'Pendiente de Emisión';
            }
        }
        
        $resultado[] = $item;
    }

    echo json_encode($resultado);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar las constancias: ' . $e->getMessage()]);
}
?>