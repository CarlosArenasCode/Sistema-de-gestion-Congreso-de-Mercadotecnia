<?php
require_once '../php/conexion.php';
require_once '../php/oracle_helpers.php';

header('Content-Type: application/json');
$id_admin_actual = 1; // ID de admin de prueba. En producción, obténlo de $_SESSION.

// Leer JSON body una sola vez y hacerlo global
$json_data = null;
$json_input = file_get_contents('php://input');
if (!empty($json_input)) {
    $json_data = json_decode($json_input, true);
}

// Leer action desde REQUEST o desde JSON body
$action = $_REQUEST['action'] ?? ($json_data['action'] ?? '');

try {
    switch ($action) {
        case 'get_list':
            getList($pdo);
            break;
        case 'get_detail':
            getDetail($pdo);
            break;
        case 'update_status':
            updateStatus($pdo, $id_admin_actual, $json_data);
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

    // Oracle: TO_CHAR para convertir fechas a formato legible
    $sql = "SELECT 
                j.id_justificacion, 
                u.nombre_completo AS nombre_usuario, 
                e.nombre_evento, 
                TO_CHAR(j.fecha_falta, 'YYYY-MM-DD') as fecha_falta,
                TO_CHAR(j.fecha_solicitud, 'YYYY-MM-DD HH24:MI:SS') as fecha_solicitud,
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
        // Oracle: UPPER para búsqueda case-insensitive
        // Convertir id_usuario a VARCHAR2 para LIKE
        $sql .= " AND (
            UPPER(u.nombre_completo) LIKE UPPER(?) OR 
            UPPER(e.nombre_evento) LIKE UPPER(?) OR 
            TO_CHAR(j.id_usuario) = ?
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

    // Oracle: TO_CHAR para formatear fechas
    // DBMS_LOB.SUBSTR para leer CLOB si motivo es muy largo
    $stmt = $pdo->prepare("
        SELECT 
            j.id_justificacion,
            j.id_usuario,
            j.id_evento,
            TO_CHAR(j.fecha_falta, 'YYYY-MM-DD') as fecha_falta,
            TO_CHAR(j.fecha_solicitud, 'YYYY-MM-DD HH24:MI:SS') as fecha_solicitud,
            j.motivo,
            j.archivo_adjunto_ruta,
            j.estado,
            j.id_admin_revisor,
            TO_CHAR(j.fecha_revision, 'YYYY-MM-DD HH24:MI:SS') as fecha_revision,
            u.nombre_completo AS nombre_usuario, 
            e.nombre_evento 
        FROM justificaciones j 
        LEFT JOIN usuarios u ON j.id_usuario = u.id_usuario 
        LEFT JOIN eventos e ON j.id_evento = e.id_evento 
        WHERE j.id_justificacion = ?
    ");
    $stmt->execute([$id_justificacion]);
    $justificacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if($justificacion){
        // Oracle puede devolver CLOB como recurso, convertir a string si es necesario
        if (isset($justificacion['motivo']) && is_resource($justificacion['motivo'])) {
            $justificacion['motivo'] = stream_get_contents($justificacion['motivo']);
        }
        echo json_encode($justificacion);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Justificación no encontrada.']);
    }
}

function updateStatus($pdo, $id_admin_actual, $data = null) {
    if ($data === null) {
        $data = json_decode(file_get_contents('php://input'), true);
    }
    $id_justificacion = $data['id_justificacion'] ?? 0;
    $nuevo_estado = $data['nuevo_estado'] ?? '';

    if ($id_justificacion && in_array($nuevo_estado, ['APROBADA', 'RECHAZADA'])) {
        // Oracle: SYSDATE en lugar de NOW()
        $sql = "UPDATE justificaciones 
                SET estado = ?, 
                    id_admin_revisor = ?, 
                    fecha_revision = SYSDATE 
                WHERE id_justificacion = ? 
                AND estado = 'PENDIENTE'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nuevo_estado, $id_admin_actual, $id_justificacion]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Justificación ' . strtolower($nuevo_estado) . ' correctamente.'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'error' => 'No se pudo actualizar. Puede que ya estuviera revisada.'
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
    }
}
?>
