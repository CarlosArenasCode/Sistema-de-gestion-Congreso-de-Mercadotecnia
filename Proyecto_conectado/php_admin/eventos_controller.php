<?php
/**
 * Controlador de Eventos - Versión Oracle
 * Migrado para usar Oracle Database 23ai Free
 */

// Cambiar a conexión Oracle
require_once '../php/conexion.php';
require_once '../php/oracle_helpers.php';

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/*
if (!isset($_SESSION['id_admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}
*/

$method = $_SERVER['REQUEST_METHOD'];
$action = '';
$id_evento = null;

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'get_eventos';
    $id_evento = isset($_GET['id_evento']) ? (int)$_GET['id_evento'] : null;
} elseif ($method === 'POST') {
    $action = $_POST['action'] ?? 'save_evento';
    $id_evento = isset($_POST['id_evento']) ? ((!empty($_POST['id_evento'])) ? (int)$_POST['id_evento'] : null) : null;
}

try {
    switch ($action) {
        case 'get_eventos':
            getEventos($pdo);
            break;
        case 'get_evento_detalle':
            if ($id_evento) {
                getEventoDetalle($pdo, $id_evento);
            } else {
                throw new Exception("ID de evento no proporcionado.");
            }
            break;
        case 'save_evento':
            saveEvento($pdo, $_POST);
            break;
        case 'delete_evento':
             $id_evento_to_delete = isset($_POST['id_evento']) ? (int)$_POST['id_evento'] : 0;
             if ($id_evento_to_delete > 0) {
                deleteEvento($pdo, $id_evento_to_delete);
            } else {
                throw new Exception("ID de evento no válido para eliminar.");
            }
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

/**
 * Obtener lista de eventos
 * Oracle: hora_inicio y hora_fin son TIMESTAMP, no TIME
 */
function getEventos($pdo) {
    $stmt = $pdo->query("
        SELECT 
            id_evento, 
            nombre_evento, 
            ponente, 
            fecha_inicio, 
            TO_CHAR(hora_inicio, 'HH24:MI') as hora_inicio,
            lugar, 
            cupo_maximo, 
            cupo_actual, 
            genera_constancia, 
            tipo_evento, 
            horas_para_constancia
        FROM eventos 
        ORDER BY fecha_inicio DESC, nombre_evento ASC
    ");
    
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'eventos' => $eventos]);
}

/**
 * Obtener detalles de un evento específico
 * Oracle: Convertir TIMESTAMP a formato compatible con HTML inputs
 */
function getEventoDetalle($pdo, $id_evento) {
    $stmt = $pdo->prepare("
        SELECT 
            id_evento, 
            nombre_evento, 
            descripcion, 
            TO_CHAR(fecha_inicio, 'YYYY-MM-DD') as fecha_inicio,
            TO_CHAR(hora_inicio, 'HH24:MI') as hora_inicio,
            TO_CHAR(fecha_fin, 'YYYY-MM-DD') as fecha_fin,
            TO_CHAR(hora_fin, 'HH24:MI') as hora_fin,
            lugar, 
            ponente, 
            cupo_maximo, 
            genera_constancia, 
            tipo_evento, 
            horas_para_constancia
        FROM eventos 
        WHERE id_evento = :id_evento
    ");
    
    $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
    $stmt->execute();
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($evento) {
        // Oracle: Convertir CLOB descripcion a string
        if (isset($evento['descripcion']) && is_resource($evento['descripcion'])) {
            $evento['descripcion'] = stream_get_contents($evento['descripcion']);
        }
        echo json_encode(['success' => true, 'evento' => $evento]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Evento no encontrado.']);
    }
}

/**
 * Guardar (crear o actualizar) un evento
 * Oracle: Manejo especial para TIMESTAMP (fecha + hora)
 */
function saveEvento($pdo, $data) {
    $id_evento_form = (isset($data['id_evento']) && !empty($data['id_evento'])) 
                      ? (int)$data['id_evento'] 
                      : null;

    // Validaciones
    if (empty($data['nombre_evento']) || empty($data['fecha_inicio']) || empty($data['hora_inicio'])) {
        throw new Exception("Los campos Título, Fecha de Inicio y Hora de Inicio son obligatorios.");
    }

    // Oracle: Combinar fecha y hora para crear TIMESTAMP
    // Formato esperado del form: fecha_inicio = 'YYYY-MM-DD', hora_inicio = 'HH:MM'
    $fecha_hora_inicio = $data['fecha_inicio'] . ' ' . $data['hora_inicio'] . ':00';
    
    $fecha_fin_val = !empty($data['fecha_fin']) ? $data['fecha_fin'] : $data['fecha_inicio'];
    $hora_fin_val = !empty($data['hora_fin']) ? $data['hora_fin'] : $data['hora_inicio'];
    $fecha_hora_fin = $fecha_fin_val . ' ' . $hora_fin_val . ':00';

    $params = [
        ':nombre_evento' => $data['nombre_evento'],
        ':descripcion' => $data['descripcion'] ?? null,
        ':fecha_inicio' => $data['fecha_inicio'], // DATE
        ':hora_inicio' => $fecha_hora_inicio,     // TIMESTAMP
        ':fecha_fin' => $fecha_fin_val,           // DATE
        ':hora_fin' => $fecha_hora_fin,           // TIMESTAMP
        ':lugar' => $data['lugar'] ?? null,
        ':ponente' => $data['ponente'] ?? null,
        ':cupo_maximo' => (isset($data['cupo_maximo']) && $data['cupo_maximo'] !== '') 
                          ? (int)$data['cupo_maximo'] 
                          : null,
        // Oracle: genera_constancia es NUMBER(1) (0 o 1)
        ':genera_constancia' => isset($data['genera_constancia']) 
                                 ? (int)$data['genera_constancia'] 
                                 : 0,
        ':tipo_evento' => $data['tipo_evento'] ?? 'conferencia',
        ':horas_para_constancia' => (isset($data['horas_para_constancia']) && $data['horas_para_constancia'] !== '') 
                                     ? (float)$data['horas_para_constancia'] 
                                     : 1.0,
    ];

    if ($id_evento_form) {
        // ACTUALIZAR evento existente
        $params[':id_evento'] = $id_evento_form;
        
        $sql = "UPDATE eventos SET
                    nombre_evento = :nombre_evento, 
                    descripcion = :descripcion, 
                    fecha_inicio = TO_DATE(:fecha_inicio, 'YYYY-MM-DD'),
                    hora_inicio = TO_TIMESTAMP(:hora_inicio, 'YYYY-MM-DD HH24:MI:SS'),
                    fecha_fin = TO_DATE(:fecha_fin, 'YYYY-MM-DD'),
                    hora_fin = TO_TIMESTAMP(:hora_fin, 'YYYY-MM-DD HH24:MI:SS'),
                    lugar = :lugar, 
                    ponente = :ponente, 
                    cupo_maximo = :cupo_maximo,
                    genera_constancia = :genera_constancia, 
                    tipo_evento = :tipo_evento,
                    horas_para_constancia = :horas_para_constancia
                WHERE id_evento = :id_evento";
        
        $message = "Evento actualizado correctamente.";
        
    } else {
        // INSERTAR nuevo evento
        $sql = "INSERT INTO eventos (
                    nombre_evento, descripcion, fecha_inicio, hora_inicio, 
                    fecha_fin, hora_fin, lugar, ponente, cupo_maximo, 
                    genera_constancia, tipo_evento, horas_para_constancia, cupo_actual
                ) VALUES (
                    :nombre_evento, 
                    :descripcion, 
                    TO_DATE(:fecha_inicio, 'YYYY-MM-DD'),
                    TO_TIMESTAMP(:hora_inicio, 'YYYY-MM-DD HH24:MI:SS'),
                    TO_DATE(:fecha_fin, 'YYYY-MM-DD'),
                    TO_TIMESTAMP(:hora_fin, 'YYYY-MM-DD HH24:MI:SS'),
                    :lugar, 
                    :ponente, 
                    :cupo_maximo,
                    :genera_constancia, 
                    :tipo_evento, 
                    :horas_para_constancia, 
                    0
                )";
        
        $message = "Evento creado correctamente.";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Oracle: Obtener ID del evento
    if ($id_evento_form) {
        $new_id = $id_evento_form;
    } else {
        $new_id = OracleHelper::getLastInsertId($pdo, 'eventos', 'id_evento');
    }
    
    // Si se creó un nuevo evento, enviar notificación a usuarios
    if (!$id_evento_form) {
        require_once __DIR__ . '/../php/send_notifications.php';
        try {
            sendEventCreatedToAll($pdo, $new_id);
        } catch (Exception $e) {
            error_log("Error enviando notificaciones de evento creado: " . $e->getMessage());
        }
    }

    echo json_encode([
        'success' => true, 
        'message' => $message, 
        'id_evento' => $new_id
    ]);
}

/**
 * Eliminar un evento
 * Oracle: ON DELETE CASCADE manejará registros relacionados
 */
function deleteEvento($pdo, $id_evento) {
    $stmt = $pdo->prepare("DELETE FROM eventos WHERE id_evento = :id_evento");
    $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Evento eliminado correctamente.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No se encontró el evento para eliminar.']);
    }
}
?>
