<?php
// php/inscribir_evento.php
require_once 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit;
}
$id_usuario = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); 
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id_evento']) || !is_numeric($data['id_evento'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de evento no válido.']);
    exit;
}
$id_evento = (int)$data['id_evento'];

try {
    $pdo->beginTransaction();

    
    $stmt = $pdo->prepare("SELECT cupo_maximo, cupo_actual FROM eventos WHERE id_evento = :id_evento FOR UPDATE");
    $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
    $stmt->execute();
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$evento) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Evento no encontrado.']);
        exit;
    }

   
    $stmt_inscripcion = $pdo->prepare("SELECT id_inscripcion, estado FROM inscripciones WHERE id_usuario = :id_usuario AND id_evento = :id_evento");
    $stmt_inscripcion->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt_inscripcion->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
    $stmt_inscripcion->execute();
    $inscripcion_existente = $stmt_inscripcion->fetch(PDO::FETCH_ASSOC);

    if ($inscripcion_existente && $inscripcion_existente['estado'] === 'Inscrito') {
        $pdo->rollBack(); 
        http_response_code(409); 
        echo json_encode(['success' => false, 'message' => 'Ya estás inscrito en este evento.']);
        exit;
    }


    if ($evento['cupo_actual'] >= $evento['cupo_maximo']) {
        $pdo->rollBack();
        http_response_code(409); 
        echo json_encode(['success' => false, 'message' => 'El cupo para este evento está lleno.']);
        exit;
    }

    
    if ($inscripcion_existente) { 
        // Oracle: SYSDATE en lugar de NOW()
        $stmt = $pdo->prepare("UPDATE inscripciones SET estado = 'Inscrito', fecha_inscripcion = SYSDATE WHERE id_inscripcion = :id_inscripcion");
        $stmt->bindParam(':id_inscripcion', $inscripcion_existente['id_inscripcion'], PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Oracle: SYSDATE en lugar de NOW()
        $stmt = $pdo->prepare("INSERT INTO inscripciones (id_usuario, id_evento, fecha_inscripcion, estado) VALUES (:id_usuario, :id_evento, SYSDATE, 'Inscrito')");
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        $stmt->execute();
    }


    $stmt = $pdo->prepare("UPDATE eventos SET cupo_actual = cupo_actual + 1 WHERE id_evento = :id_evento");
    $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
    $stmt->execute();

    $pdo->commit();
    // Enviar notificación por correo al usuario sobre la inscripción
    try {
        require_once __DIR__ . '/send_notifications.php';
        // enviar (no detener el flujo si falla)
        sendRegistrationToUser($pdo, $id_usuario, $id_evento);
    } catch (Exception $e) {
        error_log('Error enviando notificación de inscripción: ' . $e->getMessage());
    }

    echo json_encode(['success' => true, 'message' => 'Inscripción exitosa.']);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    // error_log("Error inscribing event: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al procesar la inscripción: ' . $e->getMessage()]);
}
?>