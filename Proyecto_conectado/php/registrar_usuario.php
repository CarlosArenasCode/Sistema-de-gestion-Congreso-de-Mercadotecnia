<?php
// registrar_usuario.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'conexion.php';
require 'send_notifications.php'; // Para envío de emails
require 'sms_service.php'; // Para envío de SMS

// Datos recibidos del formulario
$nombre_completo = $_POST['nombre_completo'] ?? '';
$email = $_POST['email'] ?? '';
$matricula = $_POST['matricula'] ?? '';
$semestre = $_POST['Semestre'] ?? '';
$telefono = $_POST['telefono'] ?? ''; // Teléfono del USUARIO
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$rol = $_POST['rol'] ?? 'alumno'; 

// Validación básica
if (empty($nombre_completo) || empty($email) || empty($matricula) || empty($telefono) || empty($password)) {
    echo "Error: Todos los campos son obligatorios (excepto semestre para profesores).";
    exit;
}

if ($rol === 'alumno' && empty($semestre)) {
    echo "Error: El semestre es obligatorio para los alumnos.";
    exit;
}

if ($password !== $password_confirm) {
    echo "Error: Las contraseñas no coinciden.";
    exit;
}

// Formatear teléfono del usuario
$telefono = formatear_telefono($telefono);

$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Generar código de verificación de 6 dígitos
$codigo_verificacion = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
$fecha_codigo = date('Y-m-d H:i:s');

function guidv4($data = null) {
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
$qr_code_data = guidv4();

try {
    // Insertar usuario con verificado = 0 (no verificado)
    $sql = "INSERT INTO usuarios (nombre_completo, email, password_hash, matricula, semestre, telefono, rol, qr_code_data, codigo_verificacion, fecha_codigo, verificado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $nombre_completo,
        $email,
        $password_hash,
        $matricula,
        ($rol === 'alumno' ? $semestre : null),
        $telefono,
        $rol,
        $qr_code_data,
        $codigo_verificacion,
        $fecha_codigo
    ]);

    $id_usuario = $pdo->lastInsertId();

    // Enviar código por EMAIL
    $asunto = "Código de Verificación - Congreso de Mercadotecnia";
    $mensaje_email = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #E4007C; color: white; padding: 20px; text-align: center; }
            .content { background-color: #f9f9f9; padding: 30px; border-radius: 5px; margin-top: 20px; }
            .code { font-size: 32px; font-weight: bold; color: #E4007C; text-align: center; letter-spacing: 5px; padding: 20px; background-color: white; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Bienvenido al Congreso de Mercadotecnia</h1>
            </div>
            <div class='content'>
                <h2>Hola, {$nombre_completo}</h2>
                <p>Gracias por registrarte. Para activar tu cuenta, utiliza el siguiente código de verificación:</p>
                <div class='code'>{$codigo_verificacion}</div>
                <p><strong>Este código expira en 15 minutos.</strong></p>
                <p>Por seguridad, no compartas este código con nadie.</p>
                <p>Si no solicitaste este registro, puedes ignorar este correo.</p>
            </div>
            <div class='footer'>
                <p>Congreso de Mercadotecnia - UAA</p>
            </div>
        </div>
    </body>
    </html>
    ";

    send_email($email, $asunto, $mensaje_email);

    // Enviar código por SMS al número del USUARIO
    // FROM: +52 449 210 6893 (tu número emisor)
    // TO: $telefono (número del usuario)
    enviar_codigo_verificacion_sms($telefono, $codigo_verificacion, $nombre_completo);

    // Redirigir a página de verificación
    header("Location: ../Front-end/verificar_codigo.html?email=" . urlencode($email));
    exit;

} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
         echo "Error: El email o la matrícula ya están registrados.";
    } else {
         echo "Error al registrar el usuario: " . $e->getMessage();
    }
}
?>