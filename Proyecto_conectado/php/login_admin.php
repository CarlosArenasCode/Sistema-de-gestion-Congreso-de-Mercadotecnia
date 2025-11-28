<?php
/**
 * login_admin.php
 * Login específico para administradores usando EMAIL
 */

header('Content-Type: application/json');

// Configurar sesión
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_lifetime', '0');
ini_set('session.gc_maxlifetime', '3600');

session_start();

try {
    require 'conexion.php';
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos.'
    ]);
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Log para debugging
error_log("Login Admin - Email recibido: " . $email);

if (empty($email) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Por favor completa todos los campos.'
    ]);
    exit;
}

try {
    // Buscar administrador por email
    $consulta = $pdo->prepare("SELECT * FROM ADMINISTRADORES WHERE EMAIL = :email");
    $consulta->execute([':email' => $email]);
    $admin = $consulta->fetch(PDO::FETCH_ASSOC);
    
    error_log("Login Admin - Admin encontrado: " . ($admin ? 'SÍ' : 'NO'));
    
    if ($admin) {
        error_log("Login Admin - Verificando password...");
        
        if (password_verify($password, $admin['password_hash'])) {
            // Inicio de sesión exitoso
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['nombre'] = $admin['nombre_completo'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['rol'] = $admin['rol'];
            $_SESSION['tipo'] = 'admin';
            $_SESSION['last_activity'] = time();
            
            // Generar token de sesión
            $token = bin2hex(random_bytes(32));
            $_SESSION['session_token'] = $token;
            
            error_log("Login Admin - Sesión iniciada exitosamente para: " . $admin['nombre_completo']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'redirectUrl' => '../Front-end/admin_dashboard.html',
                'userData' => [
                    'id' => $admin['id_admin'],
                    'nombre' => $admin['nombre_completo'],
                    'email' => $admin['email'],
                    'rol' => $admin['rol'],
                    'tipo' => 'admin'
                ],
                'token' => $token
            ]);
            exit;
        } else {
            error_log("Login Admin - Password incorrecto");
        }
    }
    
    // Credenciales incorrectas
    echo json_encode([
        'success' => false,
        'message' => 'Email o contraseña incorrectos.'
    ]);

} catch (PDOException $e) {
    error_log("Login Admin - Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al iniciar sesión.',
        'error' => $e->getMessage()
    ]);
}
?>
