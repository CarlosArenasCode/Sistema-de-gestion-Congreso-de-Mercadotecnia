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

// La configuración de Twilio ahora está en verificacion_config.php
// Este archivo solo contiene las funciones de envío

/**
 * Envía SMS usando Twilio API o modo simulado
 * 
 * @param string $to Número de teléfono DESTINO del usuario (formato: +521234567890)
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
    if (!defined('TWILIO_ACCOUNT_SID') || !defined('TWILIO_AUTH_TOKEN') ||
        TWILIO_ACCOUNT_SID === 'your_account_sid_here' || 
        TWILIO_AUTH_TOKEN === 'your_auth_token_here') {
        error_log("SMS Service: Twilio no está configurado. Usando modo simulado.");
        return enviar_sms_simulado($to, $message);
    }

    // Endpoint de Twilio
    $url = "https://api.twilio.com/2010-04-01/Accounts/" . TWILIO_ACCOUNT_SID . "/Messages.json";

    // Datos del mensaje
    // FROM = Tu número (+52 449 210 6893) - el EMISOR
    // TO = Número del usuario - el RECEPTOR
    $data = array(
        'To' => $to,                    // Número del USUARIO (destino)
        'From' => TELEFONO_EMISOR,      // TU número (emisor)
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
 * Envía código de verificación por SMS al número del USUARIO
 * 
 * @param string $telefono_usuario Número de teléfono del USUARIO (destino)
 * @param string $codigo Código de verificación de 6 dígitos
 * @param string $nombre_usuario Nombre del usuario (opcional)
 * @return bool
 */
function enviar_codigo_verificacion_sms($telefono_usuario, $codigo, $nombre_usuario = '') {
    $saludo = $nombre_usuario ? "Hola {$nombre_usuario}," : "Hola,";
    
    $mensaje = "{$saludo}\n\n"
             . "Tu código de verificación para el Congreso de Mercadotecnia es:\n\n"
             . "🔐 {$codigo}\n\n"
             . "Este código expira en 15 minutos.\n"
             . "No compartas este código con nadie.\n\n"
             . "Si no solicitaste este código, ignora este mensaje.";
    
    // Enviar SMS al número del usuario
    // FROM: +52 449 210 6893 (tu número emisor)
    // TO: $telefono_usuario (número del usuario)
    return enviar_sms($telefono_usuario, $mensaje);
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
