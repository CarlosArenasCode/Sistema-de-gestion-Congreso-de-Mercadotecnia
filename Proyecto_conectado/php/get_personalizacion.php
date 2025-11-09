<?php
// php/get_personalizacion.php
// Endpoint público para obtener configuración de personalización
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT * FROM personalizacion WHERE id_personalizacion = 1";
    $stmt = $pdo->query($sql);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config) {
        // Si no existe, crear registro con valores por defecto
        $sql = "INSERT INTO personalizacion (
                    color_primario, color_secundario, color_texto,
                    nombre_institucion, nombre_evento, fecha_actualizacion
                ) VALUES (
                    '#0056b3', '#28a745', '#333333',
                    'Universidad Tecnológica', 'Congreso de Mercadotecnia', SYSDATE
                )";
        $pdo->exec($sql);
        
        // Volver a consultar
        $stmt = $pdo->query("SELECT * FROM personalizacion WHERE id_personalizacion = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Convertir CLOBs a strings
    foreach ($config as $key => $value) {
        if (is_resource($value)) {
            $config[$key] = stream_get_contents($value);
        }
    }
    
    echo json_encode([
        'success' => true,
        'personalizacion' => $config
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener personalización: ' . $e->getMessage()
    ]);
}
?>
