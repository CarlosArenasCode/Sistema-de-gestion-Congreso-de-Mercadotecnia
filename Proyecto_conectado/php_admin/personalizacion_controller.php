<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

// Verificar que el usuario esté autenticado y sea admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_colores':
            getColores($conn);
            break;
        
        case 'save_colores':
            saveColores($conn);
            break;
        
        case 'get_imagenes':
            getImagenes($conn);
            break;
        
        case 'add_imagen':
            addImagen($conn);
            break;
        
        case 'update_imagen':
            updateImagen($conn);
            break;
        
        case 'delete_imagen':
            deleteImagen($conn);
            break;
        
        case 'update_orden':
            updateOrden($conn);
            break;
        
        case 'reset_colores':
            resetColores($conn);
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Obtener todos los colores
function getColores($conn) {
    $sql = "SELECT clave, valor FROM personalizacion WHERE tipo = 'color'";
    $result = $conn->query($sql);
    
    $colores = [];
    while ($row = $result->fetch_assoc()) {
        $colores[$row['clave']] = $row['valor'];
    }
    
    echo json_encode(['success' => true, 'colores' => $colores]);
}

// Guardar colores
function saveColores($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $usuario_id = $_SESSION['usuario_id'];
    
    $colores = [
        'color_primario',
        'color_secundario',
        'color_header',
        'color_nav',
        'color_nav_hover',
        'color_footer',
        'color_carrusel_fondo'
    ];
    
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("INSERT INTO personalizacion (clave, valor, tipo, modificado_por) 
                               VALUES (?, ?, 'color', ?) 
                               ON DUPLICATE KEY UPDATE valor = VALUES(valor), modificado_por = VALUES(modificado_por)");
        
        foreach ($colores as $clave) {
            if (isset($data[$clave])) {
                $valor = $data[$clave];
                // Validar formato hexadecimal
                if (preg_match('/^#[0-9A-Fa-f]{6}$/', $valor)) {
                    $stmt->bind_param("ssi", $clave, $valor, $usuario_id);
                    $stmt->execute();
                }
            }
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Colores guardados exitosamente']);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

// Restablecer colores a valores por defecto
function resetColores($conn) {
    $usuario_id = $_SESSION['usuario_id'];
    
    $coloresDefault = [
        'color_primario' => '#0056b3',
        'color_secundario' => '#28a745',
        'color_header' => '#4A4A4A',
        'color_nav' => '#333',
        'color_nav_hover' => '#0056b3',
        'color_footer' => '#333',
        'color_carrusel_fondo' => '#6c757d'
    ];
    
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("UPDATE personalizacion SET valor = ?, modificado_por = ? WHERE clave = ?");
        
        foreach ($coloresDefault as $clave => $valor) {
            $stmt->bind_param("sis", $valor, $usuario_id, $clave);
            $stmt->execute();
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Colores restablecidos a valores por defecto', 'colores' => $coloresDefault]);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

// Obtener todas las imágenes del carrusel
function getImagenes($conn) {
    $sql = "SELECT * FROM carrusel_imagenes WHERE activo = 1 ORDER BY orden ASC";
    $result = $conn->query($sql);
    
    $imagenes = [];
    while ($row = $result->fetch_assoc()) {
        $imagenes[] = $row;
    }
    
    echo json_encode(['success' => true, 'imagenes' => $imagenes]);
}

// Agregar nueva imagen al carrusel
function addImagen($conn) {
    $usuario_id = $_SESSION['usuario_id'];
    
    // Verificar si es archivo subido o URL
    if (isset($_FILES['archivo_imagen']) && $_FILES['archivo_imagen']['error'] === UPLOAD_ERR_OK) {
        // Manejar subida de archivo
        $upload_dir = '../uploads/carrusel/';
        
        // Crear directorio si no existe
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['archivo_imagen']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido']);
            return;
        }
        
        // Generar nombre único
        $new_filename = uniqid('carrusel_') . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['archivo_imagen']['tmp_name'], $upload_path)) {
            $url_imagen = $upload_path;
            $tipo_fuente = 'archivo';
            $alt_texto = $_POST['alt_texto'] ?? 'Imagen del carrusel';
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
            return;
        }
    } else {
        // Es una URL externa
        $data = json_decode(file_get_contents('php://input'), true);
        $url_imagen = $data['url_imagen'] ?? '';
        $alt_texto = $data['alt_texto'] ?? 'Imagen del carrusel';
        $tipo_fuente = 'url';
        
        if (empty($url_imagen)) {
            echo json_encode(['success' => false, 'message' => 'URL de imagen no proporcionada']);
            return;
        }
    }
    
    // Obtener el orden máximo actual
    $result = $conn->query("SELECT MAX(orden) as max_orden FROM carrusel_imagenes");
    $row = $result->fetch_assoc();
    $nuevo_orden = ($row['max_orden'] ?? 0) + 1;
    
    // Insertar en base de datos
    $stmt = $conn->prepare("INSERT INTO carrusel_imagenes (url_imagen, alt_texto, orden, tipo_fuente, creado_por) 
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisi", $url_imagen, $alt_texto, $nuevo_orden, $tipo_fuente, $usuario_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Imagen agregada exitosamente',
            'imagen' => [
                'id' => $conn->insert_id,
                'url_imagen' => $url_imagen,
                'alt_texto' => $alt_texto,
                'orden' => $nuevo_orden,
                'tipo_fuente' => $tipo_fuente
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la imagen']);
    }
}

// Actualizar información de una imagen
function updateImagen($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? 0;
    $alt_texto = $data['alt_texto'] ?? '';
    $activo = $data['activo'] ?? 1;
    
    $stmt = $conn->prepare("UPDATE carrusel_imagenes SET alt_texto = ?, activo = ? WHERE id = ?");
    $stmt->bind_param("sii", $alt_texto, $activo, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Imagen actualizada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la imagen']);
    }
}

// Eliminar (desactivar) una imagen
function deleteImagen($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? 0;
    
    // Primero, obtener la información de la imagen
    $stmt = $conn->query("SELECT url_imagen, tipo_fuente FROM carrusel_imagenes WHERE id = $id");
    $imagen = $stmt->fetch_assoc();
    
    // Si es un archivo local, eliminarlo físicamente
    if ($imagen && $imagen['tipo_fuente'] === 'archivo' && file_exists($imagen['url_imagen'])) {
        unlink($imagen['url_imagen']);
    }
    
    // Eliminar de base de datos (o marcar como inactivo)
    $stmt = $conn->prepare("DELETE FROM carrusel_imagenes WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Imagen eliminada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la imagen']);
    }
}

// Actualizar orden de las imágenes
function updateOrden($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $imagenes = $data['imagenes'] ?? [];
    
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("UPDATE carrusel_imagenes SET orden = ? WHERE id = ?");
        
        foreach ($imagenes as $index => $id) {
            $orden = $index + 1;
            $stmt->bind_param("ii", $orden, $id);
            $stmt->execute();
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Orden actualizado exitosamente']);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

$conn->close();
?>
