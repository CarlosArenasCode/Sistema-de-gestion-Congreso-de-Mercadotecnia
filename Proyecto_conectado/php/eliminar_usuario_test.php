<?php
/**
 * Script para eliminar usuario de prueba
 */
require_once 'conexion.php';

header('Content-Type: application/json');

try {
    $matricula = 'TEST123456';
    
    // Buscar el usuario
    $stmt = $pdo->prepare("SELECT ID_USUARIO, NOMBRE_COMPLETO, EMAIL FROM USUARIOS WHERE MATRICULA = :matricula");
    $stmt->execute([':matricula' => $matricula]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        // Eliminar el usuario (las foreign keys con ON DELETE CASCADE eliminarán registros relacionados)
        $stmt = $pdo->prepare("DELETE FROM USUARIOS WHERE MATRICULA = :matricula");
        $stmt->execute([':matricula' => $matricula]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuario eliminado exitosamente',
            'usuario_eliminado' => $usuario
        ], JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró usuario con matrícula ' . $matricula
        ], JSON_PRETTY_PRINT);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar usuario',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
