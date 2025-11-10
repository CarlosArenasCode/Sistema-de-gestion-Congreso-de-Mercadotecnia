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
    // ===========================================
    // VERIFICACIÓN DE DUPLICADOS (antes de insertar)
    // ===========================================
    
    // Verificar si el email ya existe
    $checkEmail = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE LOWER(email) = LOWER(?)");
    $checkEmail->execute([$email]);
    if ($checkEmail->fetchColumn() > 0) {
        ob_end_clean();
        echo "Error: Ya existe una cuenta con el email '{$email}'. Por favor usa otro email o <a href='../Front-end/login.html'>inicia sesión</a>.";
        exit;
    }
    
    // Verificar si la matrícula ya existe
    $checkMatricula = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE matricula = ?");
    $checkMatricula->execute([$matricula]);
    if ($checkMatricula->fetchColumn() > 0) {
        ob_end_clean();
        echo "Error: La matrícula '{$matricula}' ya está registrada. Por favor verifica tu matrícula o <a href='../Front-end/login.html'>inicia sesión</a>.";
        exit;
    }
    
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
                <p>También recibirás este código por WhatsApp en el número: {$telefono}</p>
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

    // ===========================================
    // ENVÍO DE CÓDIGO POR EMAIL
    // ===========================================
    $emailEnviado = false;
    try {
        error_log("[REGISTRO] Intentando enviar código por email a: {$email}");
        $emailEnviado = send_email($email, $asunto, $mensaje_email, 'Congreso de Mercadotecnia UAA');
        
        if ($emailEnviado) {
            error_log("[REGISTRO] ✅ Código enviado exitosamente por email a: {$email}");
        } else {
            error_log("[REGISTRO] ⚠️ No se pudo enviar código por email a: {$email}");
        }
    } catch (Exception $e) {
        error_log("[REGISTRO] ❌ Error al enviar email a {$email}: " . $e->getMessage());
    }

    // ===========================================
    // ENVÍO DE CÓDIGO POR WHATSAPP
    // ===========================================
    $whatsappEnviado = false;
    try {
        error_log("[REGISTRO] Intentando enviar código por WhatsApp a: {$telefono}");
        
        // Crear cliente WhatsApp (servicio en Docker)
        $whatsappClient = new WhatsAppClient('http://whatsapp:3001');
        
        // Verificar que el servicio esté disponible
        $healthCheck = $whatsappClient->checkHealth();
        
        if (isset($healthCheck['status']) && ($healthCheck['status'] === 'ready' || $healthCheck['status'] === 'authenticated')) {
            // Servicio disponible, enviar código
            $resultWhatsApp = $whatsappClient->sendVerificationCode($telefono, $codigo_verificacion, $nombre_completo);
            
            if (isset($resultWhatsApp['success']) && $resultWhatsApp['success']) {
                $whatsappEnviado = true;
                error_log("[REGISTRO] ✅ Código enviado exitosamente por WhatsApp a: {$telefono}");
            } else {
                $errorMsg = $resultWhatsApp['error'] ?? $resultWhatsApp['message'] ?? 'Error desconocido';
                error_log("[REGISTRO] ⚠️ No se pudo enviar código por WhatsApp a {$telefono}: {$errorMsg}");
            }
        } else {
            $serviceStatus = $healthCheck['status'] ?? 'unknown';
            error_log("[REGISTRO] ⚠️ Servicio WhatsApp no disponible. Estado: {$serviceStatus}");
        }
        
    } catch (Exception $e) {
        error_log("[REGISTRO] ❌ Error al enviar WhatsApp a {$telefono}: " . $e->getMessage());
    }

    // ===========================================
    // RESUMEN DEL ENVÍO
    // ===========================================
    $metodos_exitosos = [];
    if ($emailEnviado) $metodos_exitosos[] = "Email";
    if ($whatsappEnviado) $metodos_exitosos[] = "WhatsApp";
    
    if (count($metodos_exitosos) > 0) {
        error_log("[REGISTRO] 📧 Código {$codigo_verificacion} enviado a {$nombre_completo} por: " . implode(" y ", $metodos_exitosos));
    } else {
        error_log("[REGISTRO] ⚠️ Código {$codigo_verificacion} generado para {$nombre_completo}, pero no se pudo enviar por ningún medio");
    }

    // Limpiar el buffer y redirigir a página de verificación
    ob_end_clean();
    header("Location: ../Front-end/verificar_codigo.html?email=" . urlencode($email));
    exit;

} catch (PDOException $e) {
    ob_end_clean(); // Limpiar buffer antes de mostrar error
    
    // Log del error para debugging
    error_log("[REGISTRO] ❌ Error PDO: " . $e->getMessage());
    
    // Detectar tipo de violación de constraint
    $errorMsg = $e->getMessage();
    
    if ($e->getCode() == '23000' || strpos($errorMsg, 'ORA-00001') !== false) {
        // Constraint de unicidad violado
        if (strpos($errorMsg, 'UK_USUARIOS_EMAIL') !== false || strpos($errorMsg, 'EMAIL') !== false) {
            echo "Error: Ya existe una cuenta con el email '{$email}'. Por favor usa otro email o inicia sesión.";
        } elseif (strpos($errorMsg, 'UK_USUARIOS_MATRICULA') !== false || strpos($errorMsg, 'MATRICULA') !== false) {
            echo "Error: La matrícula '{$matricula}' ya está registrada. Por favor verifica tu matrícula o inicia sesión.";
        } else {
            echo "Error: El email o la matrícula ya están registrados. Por favor verifica tus datos.";
        }
    } else {
        // Otro tipo de error
        echo "Error al registrar el usuario. Por favor intenta nuevamente o contacta al administrador.";
    }
}
?>