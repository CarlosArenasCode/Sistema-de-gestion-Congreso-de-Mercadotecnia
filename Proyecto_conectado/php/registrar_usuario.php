<?php
// registrar_usuario.php

// Iniciar output buffering para prevenir problemas con headers
ob_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'conexion.php';
require 'send_notifications.php'; // Para envío de emails
require 'whatsapp_client.php'; // Cliente para servicio WhatsApp en Docker

/**
 * Formatea un número de teléfono al formato internacional
 * Agrega el código de país +52 si no está presente
 */
function formatear_telefono($telefono) {
    // Remover espacios y caracteres especiales (excepto +)
    $telefono = preg_replace('/[^0-9+]/', '', $telefono);
    
    // Si no tiene código de país, agregar +52 (México)
    if (!str_starts_with($telefono, '+')) {
        // Remover 0 inicial si existe
        $telefono = ltrim($telefono, '0');
        $telefono = '+52' . $telefono;
    }
    
    return $telefono;
}

// Datos recibidos del formulario
$nombre_completo = $_POST['nombre_completo'] ?? '';
$email = $_POST['email'] ?? '';
$matricula = $_POST['matricula'] ?? '';
$semestre = $_POST['Semestre'] ?? '';
// Usar telefono_completo que ya viene formateado desde el frontend
$telefono = $_POST['telefono_completo'] ?? $_POST['telefono'] ?? ''; 
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$rol = $_POST['rol'] ?? 'alumno'; 

// Validación básica con mensajes específicos
$campos_faltantes = [];
if (empty($nombre_completo)) $campos_faltantes[] = "Nombre Completo";
if (empty($email)) $campos_faltantes[] = "Email";
if (empty($matricula)) $campos_faltantes[] = "Matrícula";
if (empty($telefono)) $campos_faltantes[] = "Teléfono";
if (empty($password)) $campos_faltantes[] = "Contraseña";

if (!empty($campos_faltantes)) {
    ob_end_clean();
    echo "Error: Los siguientes campos son obligatorios: " . implode(", ", $campos_faltantes);
    exit;
}

if ($rol === 'alumno' && empty($semestre)) {
    ob_end_clean();
    echo "Error: El semestre es obligatorio para los alumnos.";
    exit;
}

if ($password !== $password_confirm) {
    ob_end_clean();
    echo "Error: Las contraseñas no coinciden.";
    exit;
}

// Formatear teléfono del usuario
// Formatear teléfono solo si no viene del campo telefono_completo
// Si viene de telefono_completo, ya está en formato +521XXXXXXXXXX
if (!isset($_POST['telefono_completo']) || empty($_POST['telefono_completo'])) {
    $telefono = formatear_telefono($telefono);
}
// Si ya viene formateado, solo asegurarse que tenga el formato correcto
$telefono = preg_replace('/[^0-9+]/', '', $telefono);

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
$codigo_qr = guidv4();

try {
    // Insertar usuario con verificado = 0 (no verificado)
    $sql = "INSERT INTO usuarios (nombre_completo, email, password_hash, matricula, semestre, telefono, rol, codigo_qr, codigo_verificacion, fecha_codigo, verificado)
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
        $codigo_qr,
        $codigo_verificacion,
        $fecha_codigo
    ]);

    // Oracle: Obtener el último ID insertado usando helper
    require_once 'oracle_helpers.php';
    $id_usuario = OracleHelper::getLastInsertId($pdo, 'usuarios', 'id_usuario');

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

    // Intentar enviar código por EMAIL
    $emailEnviado = false;
    try {
        $emailEnviado = send_email($email, $asunto, $mensaje_email);
        if (!$emailEnviado) {
            error_log("Advertencia: No se pudo enviar código por email a {$email}");
        }
    } catch (Exception $e) {
        error_log("Error al enviar email: " . $e->getMessage());
    }

    // Enviar código por WhatsApp usando el servicio Docker
    // FROM: +52 449 210 6893 (configurado en el servicio WhatsApp)
    // TO: $telefono (número del usuario)
    $whatsappClient = new WhatsAppClient('http://whatsapp:3001');
    $resultWhatsApp = $whatsappClient->sendVerificationCode($telefono, $codigo_verificacion, $nombre_completo);
    
    // Log del resultado (opcional)
    if (!isset($resultWhatsApp['success']) || !$resultWhatsApp['success']) {
        error_log("Advertencia: No se pudo enviar código por WhatsApp a {$telefono}: " . 
                 ($resultWhatsApp['error'] ?? 'Error desconocido'));
        // Nota: No detenemos el registro, el usuario puede verificar por email
    }

    // Limpiar el buffer y redirigir a página de verificación
    ob_end_clean();
    header("Location: ../Front-end/verificar_codigo.html?email=" . urlencode($email));
    exit;

} catch (PDOException $e) {
    ob_end_clean(); // Limpiar buffer antes de mostrar error
    if ($e->getCode() == '23000') {
         echo "Error: El email o la matrícula ya están registrados.";
    } else {
         echo "Error al registrar el usuario: " . $e->getMessage();
    }
}
?>