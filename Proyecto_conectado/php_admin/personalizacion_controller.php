<?php<?php<?php

session_start();

header('Content-Type: application/json');session_start();session_start();

require_once '../php/conexion.php';

header('Content-Type: application/json');header('Content-Type: application/json');

// Verificar que el usuario esté autenticado y sea admin

if (!isset($_SESSION['admin_id']) || $_SESSION['tipo'] !== 'admin') {require_once '../php/conexion.php';require_once '../php/conexion.php';

    echo json_encode(['success' => false, 'message' => 'No autorizado']);

    exit;

}

// Verificar que el usuario esté autenticado y sea admin// Verificar que el usuario esté autenticado y sea admin

$method = $_SERVER['REQUEST_METHOD'];

$action = $_GET['action'] ?? '';if (!isset($_SESSION['admin_id']) || $_SESSION['tipo'] !== 'admin') {if (!isset($_SESSION['admin_id']) || $_SESSION['tipo'] !== 'admin') {



try {    echo json_encode(['success' => false, 'message' => 'No autorizado']);    echo json_encode(['success' => false, 'message' => 'No autorizado']);

    switch ($action) {

        case 'get_colores':    exit;    exit;

            getColores($pdo);

            break;}}

        

        case 'save_colores':

            saveColores($pdo);

            break;$method = $_SERVER['REQUEST_METHOD'];$method = $_SERVER['REQUEST_METHOD'];

        

        case 'get_config':$action = $_GET['action'] ?? '';$action = $_GET['action'] ?? '';

            getConfig($pdo);

            break;

        

        case 'save_config':try {try {

            saveConfig($pdo);

            break;    switch ($action) {    switch ($action) {

        

        default:        case 'get_colores':        case 'get_colores':

            // Si no hay acción, devolver configuración

            getConfig($pdo);            getColores($pdo);            getColores($pdo);

    }

} catch (Exception $e) {            break;            break;

    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);

}                



// Obtener configuración completa de personalización        case 'save_colores':        case 'save_colores':

function getConfig($pdo) {

    $sql = "SELECT * FROM personalizacion WHERE id_personalizacion = 1";            saveColores($pdo);            saveColores($pdo);

    $stmt = $pdo->query($sql);

    $config = $stmt->fetch(PDO::FETCH_ASSOC);            break;            break;

    

    if (!$config) {                

        // Crear registro inicial si no existe

        $sql = "INSERT INTO personalizacion (        case 'reset_colores':        case 'get_imagenes':

                    color_primario, color_secundario, color_texto,

                    nombre_institucion, nombre_evento, fecha_actualizacion            resetColores($pdo);            getImagenes($pdo);

                ) VALUES (

                    '#0056b3', '#28a745', '#333333',            break;            break;

                    'Universidad Tecnológica', 'Congreso de Mercadotecnia', SYSDATE

                )";                

        $pdo->exec($sql);

                default:        case 'add_imagen':

        $stmt = $pdo->query("SELECT * FROM personalizacion WHERE id_personalizacion = 1");

        $config = $stmt->fetch(PDO::FETCH_ASSOC);            echo json_encode(['success' => false, 'message' => 'Acción no válida']);            addImagen($pdo);

    }

        }            break;

    // Convertir CLOBs si existen

    foreach ($config as $key => $value) {} catch (Exception $e) {        

        if (is_resource($value)) {

            $config[$key] = stream_get_contents($value);    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);        case 'update_imagen':

        }

    }}            updateImagen($pdo);

    

    echo json_encode(['success' => true, 'config' => $config]);            break;

}

// Obtener todos los colores        

// Obtener solo colores (compatible con versión anterior)

function getColores($pdo) {function getColores($pdo) {        case 'delete_imagen':

    $sql = "SELECT color_primario, color_secundario, color_texto FROM personalizacion WHERE id_personalizacion = 1";

    $stmt = $pdo->query($sql);    $sql = "SELECT clave, valor FROM personalizacion WHERE tipo = 'color'";            deleteImagen($pdo);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->query($sql);            break;

    if (!$row) {

        echo json_encode(['success' => false, 'message' => 'No se encontró configuración']);            

        return;

    }    $colores = [];        case 'update_orden':

    

    $colores = [];    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {            updateOrden($pdo);

    foreach ($row as $key => $value) {

        if (is_resource($value)) {        $clave = is_resource($row['clave']) ? stream_get_contents($row['clave']) : $row['clave'];            break;

            $value = stream_get_contents($value);

        }        $valor = is_resource($row['valor']) ? stream_get_contents($row['valor']) : $row['valor'];        

        $colores[$key] = $value;

    }        $colores[$clave] = $valor;        case 'reset_colores':

    

    echo json_encode(['success' => true, 'colores' => $colores]);    }            resetColores($pdo);

}

                break;

// Guardar configuración completa

function saveConfig($pdo) {    echo json_encode(['success' => true, 'colores' => $colores]);        

    $data = json_decode(file_get_contents('php://input'), true);

    }        default:

    $updates = [];

    $params = [];            echo json_encode(['success' => false, 'message' => 'Acción no válida']);

    

    $allowed_fields = ['color_primario', 'color_secundario', 'color_texto', // Guardar colores    }

                       'logo_header', 'logo_footer', 'nombre_institucion', 

                       'nombre_evento', 'pie_constancia'];function saveColores($pdo) {} catch (Exception $e) {

    

    foreach ($allowed_fields as $field) {    $data = json_decode(file_get_contents('php://input'), true);    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);

        if (isset($data[$field])) {

            $updates[] = "$field = :$field";    $admin_id = $_SESSION['admin_id'];}

            $params[":$field"] = $data[$field];

        }    

    }

        $colores = [// Obtener todos los colores

    if (empty($updates)) {

        echo json_encode(['success' => false, 'message' => 'No hay datos para actualizar']);        'color_primario',function getColores($pdo) {

        return;

    }        'color_secundario',    $sql = "SELECT clave, valor FROM personalizacion WHERE tipo = 'color'";

    

    $sql = "UPDATE personalizacion SET " . implode(', ', $updates) .         'color_header',    $stmt = $pdo->query($sql);

           ", fecha_actualizacion = SYSDATE WHERE id_personalizacion = 1";

            'color_nav',    

    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);        'color_nav_hover',    $colores = [];

    

    echo json_encode(['success' => true, 'message' => 'Configuración guardada exitosamente']);        'color_footer',    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

}

        'color_carrusel_fondo'        $clave = is_resource($row['clave']) ? stream_get_contents($row['clave']) : $row['clave'];

// Guardar solo colores (compatible con versión anterior)

function saveColores($pdo) {    ];        $valor = is_resource($row['valor']) ? stream_get_contents($row['valor']) : $row['valor'];

    $data = json_decode(file_get_contents('php://input'), true);

                $colores[$clave] = $valor;

    $params = [];

    if (isset($data['color_primario'])) $params[':color_primario'] = $data['color_primario'];    $pdo->beginTransaction();    }

    if (isset($data['color_secundario'])) $params[':color_secundario'] = $data['color_secundario'];

    if (isset($data['color_texto'])) $params[':color_texto'] = $data['color_texto'];        

    

    if (empty($params)) {    try {    echo json_encode(['success' => true, 'colores' => $colores]);

        echo json_encode(['success' => false, 'message' => 'No hay colores para actualizar']);

        return;        foreach ($colores as $clave) {}

    }

                if (isset($data[$clave])) {

    $updates = [];

    foreach ($params as $key => $value) {                $valor = $data[$clave];// Guardar colores

        $field = substr($key, 1); // Remover ':'

        $updates[] = "$field = $key";                // Validar formato hexadecimalfunction saveColores($conn) {

    }

                    if (preg_match('/^#[0-9A-Fa-f]{6}$/', $valor)) {    $data = json_decode(file_get_contents('php://input'), true);

    $sql = "UPDATE personalizacion SET " . implode(', ', $updates) . 

           ", fecha_actualizacion = SYSDATE WHERE id_personalizacion = 1";                    // Verificar si existe    $usuario_id = $_SESSION['usuario_id'];

    

    $stmt = $pdo->prepare($sql);                    $check = $pdo->prepare("SELECT id FROM personalizacion WHERE clave = :clave");    

    $stmt->execute($params);

                        $check->execute([':clave' => $clave]);    $colores = [

    echo json_encode(['success' => true, 'message' => 'Colores guardados exitosamente']);

}                            'color_primario',

?>

                    if ($check->fetch()) {        'color_secundario',

                        // UPDATE        'color_header',

                        $stmt = $pdo->prepare("UPDATE personalizacion         'color_nav',

                                             SET valor = :valor, modificado_por = :admin_id         'color_nav_hover',

                                             WHERE clave = :clave");        'color_footer',

                        $stmt->execute([        'color_carrusel_fondo'

                            ':valor' => $valor,    ];

                            ':admin_id' => $admin_id,    

                            ':clave' => $clave    $conn->begin_transaction();

                        ]);    

                    } else {    try {

                        // INSERT        $stmt = $conn->prepare("INSERT INTO personalizacion (clave, valor, tipo, modificado_por) 

                        $stmt = $pdo->prepare("INSERT INTO personalizacion (clave, valor, tipo, modificado_por)                                VALUES (?, ?, 'color', ?) 

                                             VALUES (:clave, :valor, 'color', :admin_id)");                               ON DUPLICATE KEY UPDATE valor = VALUES(valor), modificado_por = VALUES(modificado_por)");

                        $stmt->execute([        

                            ':clave' => $clave,        foreach ($colores as $clave) {

                            ':valor' => $valor,            if (isset($data[$clave])) {

                            ':admin_id' => $admin_id                $valor = $data[$clave];

                        ]);                // Validar formato hexadecimal

                    }                if (preg_match('/^#[0-9A-Fa-f]{6}$/', $valor)) {

                }                    $stmt->bind_param("ssi", $clave, $valor, $usuario_id);

            }                    $stmt->execute();

        }                }

                    }

        $pdo->commit();        }

        echo json_encode(['success' => true, 'message' => 'Colores guardados exitosamente']);        

    } catch (Exception $e) {        $conn->commit();

        $pdo->rollback();        echo json_encode(['success' => true, 'message' => 'Colores guardados exitosamente']);

        throw $e;    } catch (Exception $e) {

    }        $conn->rollback();

}        throw $e;

    }

// Restablecer colores a valores por defecto}

function resetColores($pdo) {

    $admin_id = $_SESSION['admin_id'];// Restablecer colores a valores por defecto

    function resetColores($conn) {

    $coloresDefault = [    $usuario_id = $_SESSION['usuario_id'];

        'color_primario' => '#0056b3',    

        'color_secundario' => '#28a745',    $coloresDefault = [

        'color_header' => '#4A4A4A',        'color_primario' => '#0056b3',

        'color_nav' => '#333',        'color_secundario' => '#28a745',

        'color_nav_hover' => '#0056b3',        'color_header' => '#4A4A4A',

        'color_footer' => '#333',        'color_nav' => '#333',

        'color_carrusel_fondo' => '#6c757d'        'color_nav_hover' => '#0056b3',

    ];        'color_footer' => '#333',

            'color_carrusel_fondo' => '#6c757d'

    $pdo->beginTransaction();    ];

        

    try {    $conn->begin_transaction();

        $stmt = $pdo->prepare("UPDATE personalizacion     

                              SET valor = :valor, modificado_por = :admin_id     try {

                              WHERE clave = :clave");        $stmt = $conn->prepare("UPDATE personalizacion SET valor = ?, modificado_por = ? WHERE clave = ?");

                

        foreach ($coloresDefault as $clave => $valor) {        foreach ($coloresDefault as $clave => $valor) {

            $stmt->execute([            $stmt->bind_param("sis", $valor, $usuario_id, $clave);

                ':valor' => $valor,            $stmt->execute();

                ':admin_id' => $admin_id,        }

                ':clave' => $clave        

            ]);        $conn->commit();

        }        echo json_encode(['success' => true, 'message' => 'Colores restablecidos a valores por defecto', 'colores' => $coloresDefault]);

            } catch (Exception $e) {

        $pdo->commit();        $conn->rollback();

        echo json_encode([        throw $e;

            'success' => true,     }

            'message' => 'Colores restablecidos a valores por defecto', }

            'colores' => $coloresDefault

        ]);// Obtener todas las imágenes del carrusel

    } catch (Exception $e) {function getImagenes($conn) {

        $pdo->rollback();    $sql = "SELECT * FROM carrusel_imagenes WHERE activo = 1 ORDER BY orden ASC";

        throw $e;    $result = $conn->query($sql);

    }    

}    $imagenes = [];

?>    while ($row = $result->fetch_assoc()) {

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
