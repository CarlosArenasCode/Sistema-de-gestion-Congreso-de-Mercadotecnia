<?php
// obtener_personalizacion.php - API para obtener la configuración de personalización
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once 'conexion.php';

$action = $_GET['action'] ?? 'get_all';

try {
    switch ($action) {
        case 'get_colores':
            getColores($conn);
            break;
        
        case 'get_imagenes':
            getImagenesCarrusel($conn);
            break;
        
        case 'get_all':
        default:
            getAll($conn);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getColores($conn) {
    $sql = "SELECT clave, valor FROM personalizacion WHERE tipo = 'color'";
    $result = $conn->query($sql);
    
    $colores = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $colores[$row['clave']] = $row['valor'];
        }
    }
    
    echo json_encode(['success' => true, 'colores' => $colores]);
}

function getImagenesCarrusel($conn) {
    $sql = "SELECT url_imagen, alt_texto, orden FROM carrusel_imagenes WHERE activo = 1 ORDER BY orden ASC";
    $result = $conn->query($sql);
    
    $imagenes = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $imagenes[] = $row;
        }
    }
    
    echo json_encode(['success' => true, 'imagenes' => $imagenes]);
}

function getAll($conn) {
    // Obtener colores
    $sql = "SELECT clave, valor FROM personalizacion WHERE tipo = 'color'";
    $result = $conn->query($sql);
    
    $colores = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $colores[$row['clave']] = $row['valor'];
        }
    }
    
    // Obtener imágenes del carrusel
    $sql = "SELECT url_imagen, alt_texto, orden FROM carrusel_imagenes WHERE activo = 1 ORDER BY orden ASC";
    $result = $conn->query($sql);
    
    $imagenes = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $imagenes[] = $row;
        }
    }
    
    echo json_encode([
        'success' => true,
        'colores' => $colores,
        'imagenes' => $imagenes
    ]);
}

$conn->close();
?>
