<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión no válida']);
    exit;
}

$user_id = $_SESSION['usuario_id'];

try {
    // Actualizamos el estado a 1 y guardamos la fecha/hora exacta (SYSTIMESTAMP en Oracle)
    $query = "UPDATE usuarios SET acepta_tyc = 1, fecha_aceptacion = SYSTIMESTAMP WHERE id_usuario = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Términos aceptados y registrados correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar la base de datos']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>