<?php
session_start();
require '../php/conexion.php';

// Verificar que sea administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['tipo'] !== 'admin') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Acceso no autorizado. Se requiere sesión de administrador.'
    ]);
    exit;
}

header('Content-Type: application/json');

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true);
$id_usuario = $input['id_usuario'] ?? null;
$id_evento = $input['id_evento'] ?? null;
$qr_data = $input['qr_data'] ?? null;

// Si viene QR data, decodificar
if ($qr_data && !$id_usuario) {
    $decoded = json_decode(base64_decode($qr_data), true);
    if ($decoded && isset($decoded['id'])) {
        $id_usuario = $decoded['id'];
    }
}

if (!$id_usuario || !$id_evento) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Se requiere id_usuario e id_evento.'
    ]);
    exit;
}

try {
    // Verificar que el usuario esté inscrito al evento
    $sql_inscripcion = "SELECT i.id_inscripcion, i.estado, u.nombre_completo, u.matricula, e.nombre_evento
                        FROM inscripciones i
                        JOIN usuarios u ON i.id_usuario = u.id_usuario
                        JOIN eventos e ON i.id_evento = e.id_evento
                        WHERE i.id_usuario = :id_usuario AND i.id_evento = :id_evento";
    
    $stmt = $pdo->prepare($sql_inscripcion);
    $stmt->execute([
        ':id_usuario' => $id_usuario,
        ':id_evento' => $id_evento
    ]);
    
    $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$inscripcion) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró inscripción para este usuario en este evento.'
        ]);
        exit;
    }
    
    // Convertir CLOBs
    foreach (['nombre_completo', 'matricula', 'nombre_evento'] as $field) {
        if (isset($inscripcion[$field]) && is_resource($inscripcion[$field])) {
            $inscripcion[$field] = stream_get_contents($inscripcion[$field]);
        }
    }
    
    // Verificar si ya tiene asistencia registrada
    $sql_check = "SELECT id_asistencia FROM asistencias 
                  WHERE id_usuario = :id_usuario AND id_evento = :id_evento";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([
        ':id_usuario' => $id_usuario,
        ':id_evento' => $id_evento
    ]);
    
    if ($stmt_check->fetch()) {
        echo json_encode([
            'success' => true,
            'message' => 'Asistencia ya registrada previamente.',
            'already_registered' => true,
            'usuario' => $inscripcion['nombre_completo'],
            'matricula' => $inscripcion['matricula'],
            'evento' => $inscripcion['nombre_evento']
        ]);
        exit;
    }
    
    // Registrar asistencia
    $sql_insert = "INSERT INTO asistencias (id_usuario, id_evento, fecha_asistencia) 
                   VALUES (:id_usuario, :id_evento, SYSDATE)";
    
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([
        ':id_usuario' => $id_usuario,
        ':id_evento' => $id_evento
    ]);
    
    // Actualizar estado de inscripción a 'asistido'
    $sql_update = "UPDATE inscripciones 
                   SET estado = 'asistido' 
                   WHERE id_inscripcion = :id_inscripcion";
    
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([':id_inscripcion' => $inscripcion['id_inscripcion']]);
    
    // ============================================
    // NOTIFICAR VÍA WEBSOCKET
    // ============================================
    notifyWebSocket([
        'id_usuario' => $id_usuario,
        'id_evento' => $id_evento,
        'nombre_completo' => $inscripcion['nombre_completo'],
        'matricula' => $inscripcion['matricula'],
        'nombre_evento' => $inscripcion['nombre_evento'],
        'tipo_registro' => 'entrada',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Asistencia registrada exitosamente.',
        'usuario' => $inscripcion['nombre_completo'],
        'matricula' => $inscripcion['matricula'],
        'evento' => $inscripcion['nombre_evento'],
        'fecha' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al registrar asistencia: ' . $e->getMessage()
    ]);
}

/**
 * Función para notificar al servidor WebSocket
 */
function notifyWebSocket($data) {
    try {
        $websocket_url = 'http://whatsapp:3001/notify-attendance';
        
        // Si no estamos en Docker, usar localhost
        if (!gethostbyname('whatsapp')) {
            $websocket_url = 'http://localhost:3001/notify-attendance';
        }
        
        $ch = curl_init($websocket_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Timeout corto para no bloquear
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            error_log("✅ WebSocket notificado: {$data['nombre_completo']}");
        } else {
            error_log("⚠️ WebSocket no disponible (código {$http_code})");
        }
    } catch (Exception $e) {
        error_log("⚠️ Error al notificar WebSocket: " . $e->getMessage());
        // No lanzar error, el registro de asistencia ya se hizo
    }
}
?>
