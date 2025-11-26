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

    // ========================================================
    // VALIDACIÓN 1: Verificar que el usuario existe y está verificado
    // ========================================================
    $stmt_usuario = $pdo->prepare("
        SELECT matricula, nombre_completo, verificado 
        FROM usuarios 
        WHERE id_usuario = :id_usuario
    ");
    $stmt_usuario->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt_usuario->execute();
    $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || empty($usuario['matricula'])) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Usuario no encontrado o sin matrícula registrada.'
        ]);
        exit;
    }

    // Verificar que el usuario esté verificado
    if ($usuario['verificado'] != 1) {
        $pdo->rollBack();
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Debes verificar tu cuenta antes de inscribirte a eventos. Revisa tu email para el código de verificación.',
            'error_code' => 'USUARIO_NO_VERIFICADO'
        ]);
        exit;
    }

    // ========================================================
    // VALIDACIÓN 2: Verificar que el evento existe
    // ========================================================
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

    // ========================================================
    // VALIDACIÓN 3: Verificar inscripción existente
    // ========================================================
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

    // ========================================================
    // VALIDACIÓN 4: Verificar cupo disponible
    // ========================================================
    if ($evento['cupo_actual'] >= $evento['cupo_maximo']) {
        $pdo->rollBack();
        
        // Buscar eventos alternativos
        $stmt_alt = $pdo->prepare("
            SELECT 
                e.id_evento,
                e.nombre_evento,
                e.tipo_evento,
                e.ponente,
                TO_CHAR(e.fecha_inicio, 'YYYY-MM-DD') as fecha_inicio,
                TO_CHAR(e.hora_inicio, 'HH24:MI') as hora_inicio,
                e.lugar,
                e.cupo_maximo,
                e.cupo_actual,
                (e.cupo_maximo - e.cupo_actual) as cupos_disponibles,
                CASE 
                    WHEN UPPER(e.ponente) = UPPER(:ponente) THEN 1
                    ELSE 2
                END as prioridad
            FROM eventos e
            WHERE e.id_evento != :id_evento
                AND e.tipo_evento = :tipo_evento
                AND e.cupo_actual < e.cupo_maximo
                AND e.fecha_inicio >= TRUNC(SYSDATE)
            ORDER BY prioridad ASC, e.fecha_inicio ASC
            FETCH FIRST 3 ROWS ONLY
        ");
        
        $stmt_alt->execute([
            ':id_evento' => $id_evento,
            ':tipo_evento' => $evento['tipo_evento'],
            ':ponente' => $evento['ponente'] ?? ''
        ]);
        
        $eventos_alternativos = $stmt_alt->fetchAll(PDO::FETCH_ASSOC);
        
        // Limpiar campo prioridad de la respuesta
        foreach ($eventos_alternativos as &$alt) {
            unset($alt['prioridad']);
        }
        unset($alt);
        
        http_response_code(409); 
        echo json_encode([
            'success' => false, 
            'error_code' => 'CUPO_LLENO',
            'message' => 'El cupo para este evento está lleno.',
            'eventos_alternativos' => $eventos_alternativos,
            'tiene_alternativas' => count($eventos_alternativos) > 0
        ]);
        exit;
    }

    // ========================================================
    // PROCESO: Registrar inscripción
    // ========================================================
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

    // ========================================================
    // PROCESO: Actualizar cupo del evento
    // ========================================================
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