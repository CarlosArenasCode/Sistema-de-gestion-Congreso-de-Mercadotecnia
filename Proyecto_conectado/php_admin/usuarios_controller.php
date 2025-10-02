<?php
require_once '../php/conexion.php'; // Ajusta la ruta

header('Content-Type: application/json');
// session_start(); // Descomentar para protección de sesión
// if (!isset($_SESSION['id_admin'])) {
//     http_response_code(403);
//     echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
//     exit;
// }

$method = $_SERVER['REQUEST_METHOD'];
$action = '';

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'get_usuarios';
} elseif ($method === 'POST') {
    $action = $_POST['action'] ?? 'save_usuario'; // 'save_usuario' o 'delete_usuario'
}

try {
    switch ($action) {
        case 'get_usuarios':
            getUsuarios($pdo, $_GET['search'] ?? null);
            break;
        case 'get_usuario_detalle': // Para poblar el formulario de edición
            $id_usuario = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : 0;
            if ($id_usuario > 0) {
                getUsuarioDetalle($pdo, $id_usuario);
            } else {
                throw new Exception("ID de usuario no válido para obtener detalles.");
            }
            break;
        case 'save_usuario':
            saveUsuario($pdo, $_POST);
            break;
        case 'delete_usuario':
            $id_usuario_to_delete = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
            if ($id_usuario_to_delete > 0) {
                deleteUsuario($pdo, $id_usuario_to_delete);
            } else {
                throw new Exception("ID de usuario no válido para eliminar.");
            }
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida para usuarios.']);
            http_response_code(400);
            break;
    }
} catch (PDOException $e) {
    error_log("PDOException en usuarios_controller: " . $e->getMessage());
    $errorCode = $e->getCode();
    $errorMessage = 'Error de base de datos. ';
    if ($errorCode == '23000') { // Código de error para violación de integridad (ej. UNIQUE constraint)
        if (strpos($e->getMessage(), 'usuarios.email') !== false) {
            $errorMessage .= 'El email proporcionado ya está en uso.';
        } elseif (strpos($e->getMessage(), 'usuarios.matricula') !== false) {
            $errorMessage .= 'La matrícula proporcionada ya está en uso.';
        } else {
            $errorMessage .= 'Violación de restricción de unicidad.';
        }
    } else {
        $errorMessage .= $e->getMessage();
    }
    echo json_encode(['success' => false, 'message' => $errorMessage]);
    http_response_code(500); // Error de servidor
} catch (Exception $e) {
    error_log("Exception en usuarios_controller: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    http_response_code(400); // Generalmente error del cliente si es una excepción que lanzamos
}

function getUsuarios($pdo, $searchTerm = null) {
    $sql = "SELECT id_usuario, matricula, nombre_completo, email, semestre, DATE_FORMAT(fecha_registro, '%d/%m/%Y %H:%i') as fecha_registro_formateada FROM usuarios";
    $params = [];
    if ($searchTerm) {
        $sql .= " WHERE id_usuario LIKE :search_id 
                  OR nombre_completo LIKE :search_nombre 
                  OR email LIKE :search_email
                  OR matricula LIKE :search_matricula";
        $params[':search_id'] = '%' . $searchTerm . '%';
        $params[':search_nombre'] = '%' . $searchTerm . '%';
        $params[':search_email'] = '%' . $searchTerm . '%';
        $params[':search_matricula'] = '%' . $searchTerm . '%';
    }
    $sql .= " ORDER BY nombre_completo ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'usuarios' => $usuarios]);
}

function getUsuarioDetalle($pdo, $id_usuario) {
    $stmt = $pdo->prepare("SELECT id_usuario, matricula, nombre_completo, email, semestre, qr_code_data FROM usuarios WHERE id_usuario = :id_usuario");
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario) {
        echo json_encode(['success' => true, 'usuario' => $usuario]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
        http_response_code(404);
    }
}

function saveUsuario($pdo, $data) {
    $id_usuario = (isset($data['id_usuario_hidden']) && !empty($data['id_usuario_hidden'])) ? (int)$data['id_usuario_hidden'] : null;

    // Validaciones
    if (empty($data['nombre_completo'])) throw new Exception("El nombre completo es obligatorio.");
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El email no es válido o está vacío.");
    }
    

    $params = [
        ':nombre_completo' => $data['nombre_completo'],
        ':email' => $data['email'],
        ':matricula' => !empty($data['matricula']) ? $data['matricula'] : null,
        ':semestre' => (isset($data['semestre']) && $data['semestre'] !== '') ? (int)$data['semestre'] : null,
        // ':qr_code_data' => $data['qr_code_data'] ?? uniqid('qr_'), // Generar si está vacío, o tomar del form
    ];
     // Generar qr_code_data si es un nuevo usuario y no se provee explícitamente
    if (!$id_usuario) { // Nuevo usuario
        $params[':qr_code_data'] = $data['qr_code_data'] ?? uniqid('userqr_'); //  Genera un QR único
    } elseif (isset($data['qr_code_data']) && !empty($data['qr_code_data'])) { // Actualizar QR si se provee
         $params[':qr_code_data'] = $data['qr_code_data'];
    }


    if ($id_usuario) { // Actualizar
        if (!empty($data['password'])) {
            $params[':password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $sql_password_part = ", password_hash = :password_hash";
        } else {
            $sql_password_part = ""; // No actualizar contraseña si está vacía
        }
        // Si qr_code_data no está en params (porque no se actualiza), no incluirlo en SQL
        $sql_qr_part = isset($params[':qr_code_data']) ? ", qr_code_data = :qr_code_data" : "";

        $sql = "UPDATE usuarios SET 
                    nombre_completo = :nombre_completo, 
                    email = :email, 
                    matricula = :matricula, 
                    semestre = :semestre
                    {$sql_password_part}
                    {$sql_qr_part}
                WHERE id_usuario = :id_usuario";
        $params[':id_usuario'] = $id_usuario;
        $message = "Usuario actualizado correctamente.";

    } else { // Insertar nuevo usuario
        if (empty($data['password'])) throw new Exception("La contraseña es obligatoria para nuevos usuarios.");
        $params[':password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuarios (nombre_completo, email, matricula, semestre, password_hash, qr_code_data) 
                VALUES (:nombre_completo, :email, :matricula, :semestre, :password_hash, :qr_code_data)";
        $message = "Usuario creado correctamente.";
    }

    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        $new_id = $id_usuario ? $id_usuario : $pdo->lastInsertId();
        echo json_encode(['success' => true, 'message' => $message, 'id_usuario' => $new_id]);
    } else {
        // El bloque catch PDOException manejará errores de BD como duplicados
        throw new Exception("Error al guardar el usuario.");
    }
}

function deleteUsuario($pdo, $id_usuario) {
    // La BD tiene ON DELETE CASCADE para algunas, lo que podría ser suficiente.
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el usuario para eliminar o ya fue eliminado.']);
            http_response_code(404);
        }
    } else {
        throw new Exception("Error al eliminar el usuario.");
    }
}
?>