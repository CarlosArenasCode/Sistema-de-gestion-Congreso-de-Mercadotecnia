<?php
/**
 * Script para verificar administradores en la base de datos
 */
require_once 'conexion.php';

header('Content-Type: application/json');

try {
    // Listar todos los administradores
    $stmt = $pdo->query("SELECT ID_ADMIN, NOMBRE_COMPLETO, EMAIL, ROL FROM ADMINISTRADORES");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'total_admins' => count($admins),
        'administradores' => $admins
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al consultar administradores',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
