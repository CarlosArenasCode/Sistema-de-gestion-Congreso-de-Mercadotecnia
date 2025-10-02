<?php
// php/cancelar_inscripcion.php
require_once 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado.']);
    exit;
}
$id_usuario = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id_evento']) || !is_numeric($data['id_evento'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de evento no válido.']);
    exit;
}
$id_evento = (int)$data['id_evento'];

try {
    $pdo->beginTransaction();

   // Verificar si el usuario tiene una inscripción activa para este evento
    $stmt = $pdo->prepare("SELECT id_inscripcion FROM inscripciones WHERE id_usuario = :id_usuario AND id_evento = :id_evento AND estado = 'Inscrito'");
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
    $stmt->execute();
    $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscripcion) {
        $pdo->rollBack();
        http_response_code(404); 
        echo json_encode(['error' => 'No se encontró una inscripción activa para este evento.']);
        exit;
    }

    // Actualizar el estado de la inscripción a 'Cancelado' y decrementar cupo_actual
    $stmt_update_inscripcion = $pdo->prepare("UPDATE inscripciones SET estado = 'Cancelado' WHERE id_inscripcion = :id_inscripcion");
    $stmt_update_inscripcion->bindParam(':id_inscripcion', $inscripcion['id_inscripcion'], PDO::PARAM_INT);
    $stmt_update_inscripcion->execute();

    // Decrementar cupo_actual del evento, asegurando que no sea negativo
    $stmt_update_evento = $pdo->prepare("UPDATE eventos SET cupo_actual = GREATEST(0, cupo_actual - 1) WHERE id_evento = :id_evento");
    $stmt_update_evento->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
    $stmt_update_evento->execute();

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Inscripción cancelada exitosamente.']);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    
    echo json_encode(['error' => 'Error al procesar la cancelación: ' . $e->getMessage()]);
}
?>