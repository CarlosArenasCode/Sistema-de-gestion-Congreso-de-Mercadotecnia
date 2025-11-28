<?php
/**
 * Script para resetear la contraseña del administrador
 */
require_once 'conexion.php';

header('Content-Type: application/json');

try {
    $nueva_password = 'Admin123!'; // Contraseña por defecto
    $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE ADMINISTRADORES SET PASSWORD_HASH = :hash WHERE EMAIL = 'admin@congreso.com'");
    $stmt->execute([':hash' => $password_hash]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Contraseña actualizada exitosamente',
        'credenciales' => [
            'email' => 'admin@congreso.com',
            'password' => $nueva_password,
            'url_login' => 'http://localhost:8081/Front-end/login_admin.html'
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar contraseña',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
