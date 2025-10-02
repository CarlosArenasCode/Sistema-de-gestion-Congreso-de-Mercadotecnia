<?php
session_start();
header('Content-Type: application/json');
require 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$nombre = $_SESSION['usuario'];

// Obtener el qr_code_data actual
$stmt = $pdo->prepare("SELECT qr_code_data FROM usuarios WHERE nombre_completo = ?");
$stmt->execute([$nombre]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    // Eliminar cualquier timestamp anterior
    $qr_base = preg_replace('/&timestamp=\d+/', '', $usuario['qr_code_data']);

    // Generar nuevo código con timestamp
    $nuevo_qr = $qr_base . '&timestamp=' . time();

    // Guardar el nuevo código en la base de datos
    $update = $pdo->prepare("UPDATE usuarios SET qr_code_data = ? WHERE nombre_completo = ?");
    $update->execute([$nuevo_qr, $nombre]);

    echo json_encode(['qr_code_data' => $nuevo_qr]);
} else {
    echo json_encode(['error' => 'Usuario no encontrado']);
}
?>
