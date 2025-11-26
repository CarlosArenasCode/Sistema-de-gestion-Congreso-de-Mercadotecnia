<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

// Verificar si hay sesión de usuario (alumno/profesor)
if (!isset($_SESSION['usuario_id'])) {
    // Si no hay sesión, devolvemos success=true para no bloquear el login, 
    // o error si queremos ser estrictos. Para el modal, asumimos que si no hay sesión no mostramos nada.
    echo json_encode(['status' => 'error', 'message' => 'No hay sesión activa']);
    exit;
}

$user_id = $_SESSION['usuario_id'];

try {
    // Consultamos el campo acepta_tyc de la tabla USUARIOS
    $query = "SELECT acepta_tyc FROM usuarios WHERE id_usuario = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        // Convertimos el '1' o '0' de Oracle a un booleano real para JS
        // Nota: PDO::CASE_LOWER está activo en conexion.php, por lo que las claves son minúsculas
        $aceptado = ($result['acepta_tyc'] == 1);
        
        echo json_encode([
            'status' => 'success', 
            'aceptado' => $aceptado
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>