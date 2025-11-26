<?php
require_once '../php/conexion.php';
require_once '../php/oracle_helpers.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_asistencias':
            getAsistencias($pdo);
            break;
        case 'export_asistencias_csv':
            exportAsistenciasCSV($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            http_response_code(400);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error en reporte_asistencia_controller: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
}

function getAsistencias($pdo, $return_data = false) {
    $searchTerm = $_GET['search'] ?? null;

    // Oracle: TO_CHAR para formatear fechas y horas desde TIMESTAMP
    $sql = "SELECT
                a.id_usuario, 
                u.nombre_completo AS nombre_usuario, 
                e.nombre_evento,
                TO_CHAR(a.fecha_asistencia, 'DD/MM/YYYY') AS fecha,
                TO_CHAR(a.hora_entrada, 'HH24:MI') AS hora_entrada,
                TO_CHAR(a.hora_salida, 'HH24:MI') AS hora_salida,
                a.duracion
            FROM asistencias a
            LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
            LEFT JOIN eventos e ON a.id_evento = e.id_evento
            WHERE 1=1";

    $params = [];
    
    if (!empty($searchTerm)) {
        // Oracle: UPPER para búsqueda case-insensitive, TO_CHAR para fecha y números
        $sql .= " AND (
            UPPER(u.nombre_completo) LIKE UPPER(?) OR 
            UPPER(e.nombre_evento) LIKE UPPER(?) OR 
            UPPER(u.matricula) LIKE UPPER(?) OR
            TO_CHAR(a.fecha, 'YYYY-MM-DD') LIKE ? OR
            TO_CHAR(a.id_usuario) = ?
        )";
        $search_like = '%' . $searchTerm . '%';
        $params = [$search_like, $search_like, $search_like, $search_like, $searchTerm];
    }

    $sql .= " ORDER BY a.fecha DESC, a.hora_entrada DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($asistencias as &$row) {
        // Oracle: duracion es INTERVAL, convertir a formato legible
        if ($row['duracion']) {
            // Oracle PDO puede devolver INTERVAL como string o recurso
            if (is_string($row['duracion'])) {
                // Formato: +000000000 HH:MI:SS.ffffff
                if (preg_match('/\+(\d+)\s+(\d+):(\d+):(\d+)/', $row['duracion'], $matches)) {
                    $days = (int)$matches[1];
                    $hours = (int)$matches[2] + ($days * 24); // Convertir días a horas
                    $minutes = (int)$matches[3];
                    $row['duracion_formateada'] = "{$hours}h {$minutes}m";
                } else {
                    $row['duracion_formateada'] = '-';
                }
            } else {
                // Si es un objeto, intentar obtener el valor
                $row['duracion_formateada'] = '-';
            }
        } else {
            $row['duracion_formateada'] = '-';
        }
    }
    unset($row);

    if ($return_data) {
        return $asistencias;
    }

    echo json_encode(['success' => true, 'asistencias' => $asistencias]);
}

function exportAsistenciasCSV($pdo) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reporte_asistencia_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID Usuario', 'Nombre Usuario', 'Evento', 'Fecha', 'Entrada', 'Salida', 'Duracion']);

    $asistencias = getAsistencias($pdo, true);

    foreach ($asistencias as $asistencia) {
        unset($asistencia['duracion_formateada']);
        fputcsv($output, $asistencia);
    }
    fclose($output);
    exit;
}
?>
