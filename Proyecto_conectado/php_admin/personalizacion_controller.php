<?php
session_start();
require_once '../php/conexion.php';

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado y sea admin
if (!isset($_SESSION['admin_id']) || $_SESSION['tipo'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_config':
            getConfig($pdo);
            break;
        
        case 'save_config':
            saveConfig($pdo);
            break;
            
        case 'reset_colores':
            resetColores($pdo);
            break;
            
        default:
            getConfig($pdo);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getConfig($pdo) {
    $sql = "SELECT * FROM personalizacion WHERE id_personalizacion = 1";
    $stmt = $pdo->query($sql);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config) {
        // Insertar por defecto si no existe
        $sql = "INSERT INTO personalizacion (
                    color_primario, color_secundario, color_texto,
                    nombre_institucion, nombre_evento
                ) VALUES (
                    '#E4007C', '#FFFFFF', '#333333',
                    'Universidad Autónoma de Aguascalientes', 'Congreso de Mercadotecnia 2025'
                )";
        $pdo->exec($sql);
        
        $stmt = $pdo->query("SELECT * FROM personalizacion WHERE id_personalizacion = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo json_encode(['success' => true, 'config' => $config]);
}

function saveConfig($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $allowed = [
        'color_primario', 'color_secundario', 'color_texto',
        'logo_header', 'logo_footer', 'nombre_institucion', 
        'nombre_evento', 'pie_constancia'
    ];
    
    $updates = [];
    $params = [];
    
    foreach ($allowed as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = :$field";
            $params[":$field"] = $data[$field];
        }
    }
    
    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => 'No hay datos para actualizar']);
        return;
    }
    
    $sql = "UPDATE personalizacion SET " . implode(', ', $updates) . " WHERE id_personalizacion = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode(['success' => true, 'message' => 'Configuración guardada']);
}

function resetColores($pdo) {
    $defaults = [
        'color_primario' => '#E4007C',
        'color_secundario' => '#FFFFFF',
        'color_texto' => '#333333'
    ];
    
    $sql = "UPDATE personalizacion SET 
            color_primario = :color_primario,
            color_secundario = :color_secundario,
            color_texto = :color_texto
            WHERE id_personalizacion = 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($defaults);
    
    echo json_encode(['success' => true, 'message' => 'Colores restablecidos', 'colores' => $defaults]);
}
?>