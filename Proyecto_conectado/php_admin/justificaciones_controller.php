<?php
require_once '../php/conexion.php';

header('Content-Type: application/json');
$id_admin_actual = 1; // ID de admin de prueba. En producción, obténlo de $_SESSION.

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get_list':
            getList($pdo);
            break;
        case 'get_detail':
            getDetail($pdo);
            break;
        case 'update_status':
            updateStatus($pdo, $id_admin_actual);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida.']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error en justificaciones_controller: " . $e->getMessage());
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}



function getList($pdo) {
    $estado = $_GET['estado'] ?? null;
    $searchTerm = $_GET['search'] ?? null;

    $sql = "SELECT 
                j.id_justificacion, 
                u.nombre_completo AS nombre_usuario, 
                e.nombre_evento, 
                j.fecha_falta, 
                j.fecha_solicitud, 
                j.estado
            FROM justificaciones j
            LEFT JOIN usuarios u ON j.id_usuario = u.id_usuario
            LEFT JOIN eventos e ON j.id_evento = e.id_evento
            WHERE 1=1";

    $params = [];
    
    if (!empty($estado)) {
        $sql .= " AND j.estado = ?";
        $params[] = $estado;
    }

    if (!empty($searchTerm)) {
        $sql .= " AND (
            u.nombre_completo LIKE ? OR 
            e.nombre_evento LIKE ? OR 
            j.id_usuario = ?
        )";
        $search_like = '%' . $searchTerm . '%';
        $params[] = $search_like;
        $params[] = $search_like;
        $params[] = $searchTerm;
    }

    $sql .= " ORDER BY j.fecha_solicitud DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $justificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['justificaciones' => $justificaciones]);
}


function getDetail($pdo) {
    $id_justificacion = $_GET['id_justificacion'] ?? 0;
    if(!$id_justificacion) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de justificación no proporcionado.']);
        return;
    }

    $stmt = $pdo->prepare("SELECT j.*, u.nombre_completo AS nombre_usuario, e.nombre_evento FROM justificaciones j LEFT JOIN usuarios u ON j.id_usuario=u.id_usuario LEFT JOIN eventos e ON j.id_evento=e.id_evento WHERE j.id_justificacion = ?");
    $stmt->execute([$id_justificacion]);
    $justificacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if($justificacion){
        echo json_encode($justificacion);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Justificación no encontrada.']);
    }
}

function updateStatus($pdo, $id_admin_actual) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id_justificacion = $data['id_justificacion'] ?? 0;
    $nuevo_estado = $data['nuevo_estado'] ?? '';

    if ($id_justificacion && in_array($nuevo_estado, ['APROBADA', 'RECHAZADA'])) {
        $sql = "UPDATE justificaciones SET estado = ?, id_admin_revisor = ?, fecha_revision = NOW() WHERE id_justificacion = ? AND estado = 'PENDIENTE'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nuevo_estado, $id_admin_actual, $id_justificacion]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Justificación ' . strtolower($nuevo_estado) . ' correctamente.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo actualizar. Puede que ya estuviera revisada.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
    }
}
?>