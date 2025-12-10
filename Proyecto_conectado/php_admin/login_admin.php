<?php
/**
 * login_admin.php
 * Procesa el login de administradores
 */

session_start();

// Configuración de errores
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Headers para JSON
header('Content-Type: application/json');

require_once '../php/conexion.php';

try {
    // Obtener datos del formulario
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validaciones básicas
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email y contraseña son requeridos'
        ]);
        exit;
    }

    // Buscar administrador por email
    $sql = "SELECT id_admin, nombre_completo, email, password_hash, rol 
            FROM administradores 
            WHERE LOWER(email) = LOWER(?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        error_log("[LOGIN_ADMIN] Intento de login fallido - Email no encontrado: {$email}");
        echo json_encode([
            'success' => false,
            'message' => 'Credenciales incorrectas'
        ]);
        exit;
    }

    // Verificar contraseña
    if (!password_verify($password, $admin['password_hash'])) {
        error_log("[LOGIN_ADMIN] Intento de login fallido - Contraseña incorrecta para: {$email}");
        echo json_encode([
            'success' => false,
            'message' => 'Credenciales incorrectas'
        ]);
        exit;
    }

    // Login exitoso - Crear sesión
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $admin['id_admin'];
    $_SESSION['admin_nombre'] = $admin['nombre_completo'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_rol'] = $admin['rol'];
    $_SESSION['login_time'] = time();

    error_log("[LOGIN_ADMIN] Login exitoso - Admin: {$admin['nombre_completo']} ({$email})");

    echo json_encode([
        'success' => true,
        'message' => 'Login exitoso',
        'admin' => [
            'id' => $admin['id_admin'],
            'nombre' => $admin['nombre_completo'],
            'email' => $admin['email'],
            'rol' => $admin['rol']
        ],
        'redirect' => '../Front-end/admin_dashboard.html'
    ]);

} catch (PDOException $e) {
    error_log("[LOGIN_ADMIN] Error PDO: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor. Intenta nuevamente.'
    ]);
} catch (Exception $e) {
    error_log("[LOGIN_ADMIN] Error general: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado. Intenta nuevamente.'
    ]);
}
?>
