<?php
session_start();
require 'conexion.php';

// Configurar headers para JSON
header('Content-Type: application/json');

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
    // Buscar usuario por matrícula
    $consulta = $pdo->prepare("SELECT * FROM usuarios WHERE matricula = ?");
    $consulta->execute([$matricula]);
    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        // Verificar si la cuenta está verificada
        if ($usuario['verificado'] == 0) {
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
        $_SESSION['rol'] = 'alumno';
        $_SESSION['tipo'] = 'alumno';
        $_SESSION['verificado'] = $usuario['verificado'];
        $_SESSION['last_activity'] = time();

        // Generar token de sesión
        $token = bin2hex(random_bytes(32));
        $_SESSION['session_token'] = $token;

        echo json_encode([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'redirectUrl' => '../Front-end/dashboard_alumno.html',
            'userData' => [
                'id' => $usuario['id_usuario'],
                'nombre' => $usuario['nombre_completo'],
                'email' => $usuario['email'],
                'matricula' => $usuario['matricula'],
                'rol' => 'alumno',
                'tipo' => 'alumno',
                'verificado' => $usuario['verificado']
            ],
            'token' => $token
        ]);
        exit;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Matrícula o contraseña incorrectos.'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al iniciar sesión: ' . $e->getMessage()
    ]);
}
?>
