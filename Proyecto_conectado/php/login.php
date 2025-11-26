<?php
// Configurar headers para JSON PRIMERO
header('Content-Type: application/json');

// Configurar sesión antes de iniciarla
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_lifetime', '0'); // Expira al cerrar navegador
ini_set('session.gc_maxlifetime', '3600'); // 1 hora

session_start();

// Intentar conectar y manejar errores
try {
    require 'conexion.php';
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos. Intenta nuevamente.'
    ]);
    exit;
}

$matricula = $_POST['university_id'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($matricula) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Por favor completa todos los campos.'
    ]);
    exit;
}

try {
    // Primero intentar login como usuario (matrícula)
    $consulta = $pdo->prepare("SELECT * FROM usuarios WHERE matricula = :matricula");
    $consulta->execute([':matricula' => $matricula]);
    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        // Verificar si la cuenta está verificada
        if (isset($usuario['verificado']) && $usuario['verificado'] == 0) {
            echo json_encode([
                'success' => false,
                'verified' => false,
                'message' => 'Tu cuenta aún no está verificada. Revisa tu email y SMS para obtener el código de verificación.',
                'redirectUrl' => '../Front-end/verificar_codigo.html?email=' . urlencode($usuario['email'])
            ]);
            exit;
        }

        // Inicio de sesión exitoso - Guardar datos completos en sesión
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre_completo'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['matricula'] = $usuario['matricula'];
        // Usar el rol real de la base de datos, o 'alumno' por defecto si no está definido
        $_SESSION['rol'] = isset($usuario['rol']) ? $usuario['rol'] : 'alumno';
        $_SESSION['tipo'] = 'alumno'; // Tipo de usuario general (vs admin)
        $_SESSION['verificado'] = isset($usuario['verificado']) ? $usuario['verificado'] : 1;
        $_SESSION['last_activity'] = time();

        // Generar token de sesión
        $token = bin2hex(random_bytes(32));
        $_SESSION['session_token'] = $token;

        echo json_encode([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'redirectUrl' => '/Front-end/dashboard_alumno.html',
            'userData' => [
                'id' => $usuario['id_usuario'],
                'nombre' => $usuario['nombre_completo'],
                'email' => $usuario['email'],
                'matricula' => $usuario['matricula'],
                'rol' => isset($usuario['rol']) ? $usuario['rol'] : 'alumno',
                'tipo' => 'alumno',
                'verificado' => isset($usuario['verificado']) ? $usuario['verificado'] : 1
            ],
            'token' => $token
        ]);
        exit;
    }
    
    // Si no es usuario, intentar login como administrador (email)
    $consulta_admin = $pdo->prepare("SELECT * FROM administradores WHERE email = :email");
    $consulta_admin->execute([':email' => $matricula]); // Usando matrícula como email para admin
    $admin = $consulta_admin->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password_hash'])) {
        // Inicio de sesión exitoso como admin
        $_SESSION['admin_id'] = $admin['id_admin'];
        $_SESSION['nombre'] = $admin['nombre_completo'];
        $_SESSION['email'] = $admin['email'];
        $_SESSION['rol'] = $admin['rol'];
        $_SESSION['tipo'] = 'admin';
        $_SESSION['last_activity'] = time();

        // Generar token de sesión
        $token = bin2hex(random_bytes(32));
        $_SESSION['session_token'] = $token;

        echo json_encode([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'redirectUrl' => '/Front-end/admin_dashboard.html',
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
    }
    
    // Si no se encontró en ninguna tabla
    echo json_encode([
        'success' => false,
        'message' => 'Matrícula o contraseña incorrectos.'
    ]);


} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al iniciar sesión: ' . $e->getMessage()
    ]);
}
?>
