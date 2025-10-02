<?php
require_once '../php/conexion.php';

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

    $sql = "SELECT
                a.id_usuario, u.nombre_completo AS nombre_usuario, e.nombre_evento,
                DATE_FORMAT(a.fecha, '%d/%m/%Y') AS fecha,
                TIME_FORMAT(a.hora_entrada, '%H:%i') AS hora_entrada,
                TIME_FORMAT(a.hora_salida, '%H:%i') AS hora_salida,
                a.duracion
            FROM asistencia a
            LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
            LEFT JOIN eventos e ON a.id_evento = e.id_evento
            WHERE 1=1";

    $params = [];
    
    // --- LÓGICA DE FILTRO CORREGIDA CON PARÁMETROS POSICIONALES (?) ---
    if (!empty($searchTerm)) {
        $sql .= " AND (
            u.nombre_completo LIKE ? OR 
            e.nombre_evento LIKE ? OR 
            u.matricula LIKE ? OR
            a.fecha LIKE ? OR
            a.id_usuario = ?
        )";
        $search_like = '%' . $searchTerm . '%';
        // Se crea un array con los valores en el orden de los '?'
        $params = [$search_like, $search_like, $search_like, $search_like, $searchTerm];
    }

    $sql .= " ORDER BY a.fecha DESC, a.hora_entrada DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($asistencias as &$row) {
        if ($row['duracion'] && preg_match('/^(\d{2,}):(\d{2}):(\d{2})$/', $row['duracion'], $matches)) {
            $h = (int)$matches[1]; $m = (int)$matches[2];
            $row['duracion_formateada'] = "{$h}h {$m}m";
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