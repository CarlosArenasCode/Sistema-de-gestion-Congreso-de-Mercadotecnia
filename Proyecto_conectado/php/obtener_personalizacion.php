<?php
// obtener_personalizacion.php - API para obtener la configuración de personalización
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once 'conexion.php';

$action = $_GET['action'] ?? 'get_all';

try {
    switch ($action) {
        case 'get_colores':
            getColores($pdo);
            break;
        
        case 'get_imagenes':
            getImagenesCarrusel($pdo);
            break;
        
        case 'get_all':
        default:
            getAll($pdo);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getColores($pdo) {
    $sql = "SELECT clave, valor FROM personalizacion WHERE tipo = 'color'";
    $stmt = $pdo->query($sql);
    
    $colores = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $colores[$row['clave']] = $row['valor'];
    }
    
    echo json_encode(['success' => true, 'colores' => $colores]);
}

function getImagenesCarrusel($pdo) {
    $sql = "SELECT url_imagen, alt_texto, orden FROM carrusel_imagenes WHERE activo = 1 ORDER BY orden ASC";
    $stmt = $pdo->query($sql);
    
    $imagenes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $imagenes[] = $row;
    }
    
    echo json_encode(['success' => true, 'imagenes' => $imagenes]);
}

function getAll($pdo) {
    // Obtener colores
    $sql = "SELECT clave, valor FROM personalizacion WHERE tipo = 'color'";
    $stmt = $pdo->query($sql);
    
    $colores = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $colores[$row['clave']] = $row['valor'];
    }
    
    // Obtener imágenes del carrusel
    $sql = "SELECT url_imagen, alt_texto, orden FROM carrusel_imagenes WHERE activo = 1 ORDER BY orden ASC";
    $stmt = $pdo->query($sql);
    
    $imagenes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $imagenes[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'colores' => $colores,
        'imagenes' => $imagenes
    ]);
}

$conn->close();
?>
