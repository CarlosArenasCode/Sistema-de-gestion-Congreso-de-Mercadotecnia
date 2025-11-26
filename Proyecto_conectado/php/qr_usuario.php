<?php
// Configuración de sesión mejorada
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

session_start();
header('Content-Type: application/json');
require 'conexion.php';

// Verificar sesión con las variables correctas
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['nombre'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$nombre = $_SESSION['nombre'];
$usuario_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("SELECT qr_code_data FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    // Eliminar cualquier timestamp anterior
    $qr_base = preg_replace('/&timestamp=\d+/', '', $usuario['qr_code_data']);

    // Generar nuevo código con timestamp
    $nuevo_qr = $qr_base . '&timestamp=' . time();

    // Guardar el nuevo código en la base de datos usando el ID del usuario
    $update = $pdo->prepare("UPDATE usuarios SET qr_code_data = ? WHERE id_usuario = ?");
    $update->execute([$nuevo_qr, $usuario_id]);

    echo json_encode(['qr_code_data' => $nuevo_qr]);
} else {
    echo json_encode(['error' => 'Usuario no encontrado']);
}
?>
