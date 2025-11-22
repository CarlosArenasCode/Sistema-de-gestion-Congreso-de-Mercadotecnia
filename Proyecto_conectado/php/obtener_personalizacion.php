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
    // Adaptado para el esquema de Oracle que usa columnas específicas
    $sql = "SELECT color_primario, color_secundario, color_texto, logo_header, logo_footer, nombre_institucion, nombre_evento, pie_constancia FROM personalizacion WHERE id_personalizacion = 1";
    $stmt = $pdo->query($sql);
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        // Convertir CLOBs si es necesario (aunque estos campos son VARCHAR2 en el esquema actual)
        $colores = [];
        foreach ($row as $key => $value) {
            $colores[$key] = $value;
        }
        
        // Mapear nombres de columnas a lo que espera el frontend si es necesario
        // El frontend usa las claves para generar variables CSS: --color-primario, etc.
        // Las columnas ya se llaman color_primario, etc., así que debería funcionar directo.
        
        echo json_encode(['success' => true, 'colores' => $colores]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró configuración']);
    }
}

function getImagenesCarrusel($pdo) {
    // TODO: La tabla carrusel_imagenes no existe en el esquema actual de Oracle.
    // Se devuelve un array vacío para evitar errores en el frontend.
    /*
    $sql = "SELECT url_imagen, alt_texto, orden FROM carrusel_imagenes WHERE activo = 1 ORDER BY orden ASC";
    $stmt = $pdo->query($sql);
    
    $imagenes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $imagenes[] = $row;
    }
    */
    $imagenes = [];
    
    echo json_encode(['success' => true, 'imagenes' => $imagenes]);
}

function getAll($pdo) {
    // Combinar colores e imágenes
    // Reutilizamos la lógica de getColores
    $sql = "SELECT color_primario, color_secundario, color_texto, logo_header, logo_footer, nombre_institucion, nombre_evento, pie_constancia FROM personalizacion WHERE id_personalizacion = 1";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $colores = $row ? $row : [];
    
    // Imágenes vacías por ahora
    $imagenes = [];
    
    echo json_encode([
        'success' => true, 
        'colores' => $colores,
        'imagenes' => $imagenes
    ]);
}
    
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
?>
