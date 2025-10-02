<?php
// php/constancias_usuario.php
require_once 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado.']);
    exit;
}
$id_usuario = $_SESSION['id_usuario'];

try {
    
    $sql = "
        SELECT
            potenciales.id_evento,
            potenciales.nombre_evento,
            potenciales.tipo_evento,
            potenciales.horas_para_constancia,
            COALESCE(att.asistencia_completa_count, 0) AS asistencia_completa,
            COALESCE(att.duracion_total_seg, 0) AS duracion_total_seg,
            c.ruta_archivo_pdf
        FROM
            (
                -- Subconsulta para obtener todos los eventos potenciales para el usuario
                SELECT e.id_evento, e.nombre_evento, e.tipo_evento, e.horas_para_constancia
                FROM eventos e
                JOIN inscripciones i ON e.id_evento = i.id_evento
                WHERE i.id_usuario = ? AND e.tipo_evento = 'taller'
                
                UNION
                
                SELECT e.id_evento, e.nombre_evento, e.tipo_evento, e.horas_para_constancia
                FROM eventos e
                JOIN (SELECT DISTINCT id_evento FROM asistencia WHERE id_usuario = ?) a ON e.id_evento = a.id_evento
                WHERE e.tipo_evento = 'conferencia'
            ) AS potenciales
        LEFT JOIN
            (
                -- Subconsulta para agregar los datos de asistencia del usuario
                SELECT
                    id_evento,
                    SUM(CASE WHEN hora_salida IS NOT NULL THEN 1 ELSE 0 END) as asistencia_completa_count,
                    SUM(TIME_TO_SEC(duracion)) as duracion_total_seg
                FROM asistencia
                WHERE id_usuario = ?
                GROUP BY id_evento
            ) AS att ON potenciales.id_evento = att.id_evento
        LEFT JOIN
            constancias c ON potenciales.id_evento = c.id_evento AND c.id_usuario = ?
    ";

    $stmt = $pdo->prepare($sql);
    // Pasamos el ID del usuario para cada '?' en la consulta
    $stmt->execute([$id_usuario, $id_usuario, $id_usuario, $id_usuario]);
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