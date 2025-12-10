<?php
// Test de lectura de codigo_qr con PDO
header('Content-Type: application/json');

require_once '../php/conexion.php';

try {
    $stmt = $pdo->prepare("
        SELECT 
            u.id_usuario,
            u.matricula,
            u.nombre_completo,
            u.email,
            u.codigo_qr
        FROM usuarios u 
        WHERE u.id_usuario = :id_usuario
    ");
    
    $stmt->execute([':id_usuario' => 1]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($datos) {
        // Convertir CLOBs si es necesario
        foreach ($datos as $key => $value) {
            if (is_resource($value)) {
                $datos[$key] = stream_get_contents($value);
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'datos' => $datos,
        'columnas' => array_keys($datos ?: [])
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
