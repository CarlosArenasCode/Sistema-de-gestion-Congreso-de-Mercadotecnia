<?php
/**
 * Script de prueba de conexión a Oracle
 */

// Configurar salida JSON
header('Content-Type: application/json');

try {
    require 'conexion.php';
    
    // Probar una consulta simple
    $stmt = $pdo->query("SELECT 'Conexión exitosa' as mensaje, SYSDATE as fecha FROM DUAL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Contar usuarios
    $stmtUsers = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $users = $stmtUsers->fetch(PDO::FETCH_ASSOC);
    
    // Contar administradores
    $stmtAdmins = $pdo->query("SELECT COUNT(*) as total FROM administradores");
    $admins = $stmtAdmins->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Conexión a Oracle exitosa',
        'data' => [
            'conexion' => $result,
            'usuarios' => $users['total'],
            'administradores' => $admins['total']
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
