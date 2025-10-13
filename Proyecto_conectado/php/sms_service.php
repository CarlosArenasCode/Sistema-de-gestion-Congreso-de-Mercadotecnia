<?php
/**
 * Servicio de envío de SMS
 * Utiliza Twilio API para enviar mensajes de texto
 * 
 * IMPORTANTE: Necesitas una cuenta de Twilio para usar este servicio
 * 1. Crea una cuenta en https://www.twilio.com/
 * 2. Obtén tu Account SID, Auth Token y número de teléfono
 * 3. Configura las constantes abajo
 */

// Configuración de Twilio (¡NO SUBIR A REPOSITORIO PÚBLICO!)
// Mejor práctica: usar variables de entorno o archivo de configuración separado
define('TWILIO_ACCOUNT_SID', 'your_account_sid_here');
define('TWILIO_AUTH_TOKEN', 'your_auth_token_here');
define('TWILIO_PHONE_NUMBER', '+1234567890'); // Tu número de Twilio

/**
 * Envía SMS usando Twilio API o modo simulado
 * 
 * @param string $to Número de teléfono destino (formato: +521234567890)
 * @param string $message Mensaje a enviar
 * @return bool True si se envió correctamente, False en caso contrario
 */
function enviar_sms($to, $message) {
    // Cargar configuración
    require_once __DIR__ . '/verificacion_config.php';
    
    // Si está en modo desarrollo, usar SMS simulado
    if (defined('SMS_MODE_DESARROLLO') && SMS_MODE_DESARROLLO === true) {
        return enviar_sms_simulado($to, $message);
    }
    
    // Validar que Twilio esté configurado
    if (TWILIO_ACCOUNT_SID === 'your_account_sid_here' || 
        TWILIO_AUTH_TOKEN === 'your_auth_token_here') {
        error_log("SMS Service: Twilio no está configurado. Usando modo simulado.");
        return enviar_sms_simulado($to, $message);
    }

    // Endpoint de Twilio
    $url = "https://api.twilio.com/2010-04-01/Accounts/" . TWILIO_ACCOUNT_SID . "/Messages.json";

    // Datos del mensaje
    $data = array(
        'To' => $to,
        'From' => TWILIO_PHONE_NUMBER,
        'Body' => $message
    );

    // Inicializar cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, TWILIO_ACCOUNT_SID . ':' . TWILIO_AUTH_TOKEN);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

    // Ejecutar petición
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Verificar respuesta
    if ($http_code >= 200 && $http_code < 300) {
        error_log("SMS enviado exitosamente a: " . $to);
        return true;
    } else {
        error_log("Error al enviar SMS. HTTP Code: " . $http_code . " Response: " . $response);
        return false;
    }
}

/**
 * Envía código de verificación por SMS al número configurado del administrador
 * 
 * @param string $codigo Código de verificación de 6 dígitos
 * @param string $nombre_usuario Nombre del usuario que se está registrando
 * @param string $email Email del usuario que se está registrando
 * @return bool
 */
function enviar_codigo_verificacion_sms($codigo, $nombre_usuario = '', $email = '') {
    require_once __DIR__ . '/verificacion_config.php';
    
    // Usar el teléfono del administrador configurado
    $telefono_destino = TELEFONO_VERIFICACION_ADMIN;
    
    // Si está configurado, incluir información del usuario
    if (defined('SMS_ADMIN_PREFIX') && SMS_ADMIN_PREFIX === true && $nombre_usuario) {
        $mensaje = "🔐 CÓDIGO DE VERIFICACIÓN\n\n"
                 . "Usuario: {$nombre_usuario}\n"
                 . "Email: {$email}\n\n"
                 . "Código: {$codigo}\n\n"
                 . "Expira en 15 minutos.";
    } else {
        $mensaje = "🔐 Código de verificación:\n\n"
                 . "{$codigo}\n\n"
                 . "Expira en 15 minutos.";
    }
    
    return enviar_sms($telefono_destino, $mensaje);
}

/**
 * ALTERNATIVA: Simulación de envío de SMS para desarrollo/testing
 * Guarda el SMS en un archivo log en lugar de enviarlo realmente
 */
function enviar_sms_simulado($to, $message) {
    $log_file = __DIR__ . '/sms_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "\n========================================\n"
               . "Timestamp: {$timestamp}\n"
               . "To: {$to}\n"
               . "Message: {$message}\n"
               . "========================================\n";
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    error_log("SMS simulado guardado en log para: " . $to);
    
    return true;
}

/**
 * Función auxiliar para formatear número de teléfono
 * 
 * @param string $telefono Número sin formato
 * @return string Número formateado con +52 (México)
 */
function formatear_telefono($telefono) {
    // Remover espacios y caracteres especiales
    $telefono = preg_replace('/[^0-9+]/', '', $telefono);
    
    // Si no tiene código de país, agregar +52 (México)
    if (!str_starts_with($telefono, '+')) {
        $telefono = '+52' . $telefono;
    }
    
    return $telefono;
}
?>
