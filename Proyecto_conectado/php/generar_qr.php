<?php
/**
 * php/generar_qr.php
 * Genera código QR del usuario para asistencias
 */

header('Content-Type: application/json');
require_once 'conexion.php';

try {
    // Verificar autenticación
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Usuario no autenticado'
        ]);
        exit;
    }
    
    $id_usuario = $_SESSION['usuario_id'];
    
    // Obtener datos del usuario
    $sql = "SELECT id_usuario, matricula, nombre_completo, email 
            FROM usuarios 
            WHERE id_usuario = :id_usuario";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Usuario no encontrado'
        ]);
        exit;
    }
    
    // Generar datos para el QR (formato JSON con info del usuario)
    $qr_data = json_encode([
        'id' => $usuario['id_usuario'],
        'matricula' => $usuario['matricula'],
        'nombre' => $usuario['nombre_completo'],
        'timestamp' => time()
    ]);
    
    // Codificar en base64 para el QR
    $qr_content = base64_encode($qr_data);
    
    echo json_encode([
        'success' => true,
        'qr_data' => $qr_content,
        'usuario' => [
            'id' => $usuario['id_usuario'],
            'matricula' => $usuario['matricula'],
            'nombre' => $usuario['nombre_completo']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al generar QR',
        'message' => $e->getMessage()
    ]);
}
?>
