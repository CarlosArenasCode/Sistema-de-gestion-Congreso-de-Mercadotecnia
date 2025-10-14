<?php
/**
 * Servicio de envío de WhatsApp
 * Utiliza Twilio WhatsApp API para enviar mensajes
 * 
 * IMPORTANTE: Este archivo maneja tanto SMS como WhatsApp
 * Configuración en verificacion_config.php
 */

require_once __DIR__ . '/verificacion_config.php';

/**
 * Envía mensaje por WhatsApp o SMS según configuración
 * 
 * @param string $to Número de teléfono destino (formato: +5244912345678)
 * @param string $message Mensaje a enviar
 * @param bool $use_whatsapp Si es true, usa WhatsApp. Si es false, usa SMS
 * @return bool True si se envió correctamente
 */
function enviar_mensaje($to, $message, $use_whatsapp = true) {
    // Si está en modo desarrollo, simular envío
    if (defined('SMS_MODE_DESARROLLO') && SMS_MODE_DESARROLLO === true) {
        return enviar_mensaje_simulado($to, $message, $use_whatsapp ? 'WhatsApp' : 'SMS');
    }
    
    // Validar credenciales de Twilio
    if (!defined('TWILIO_ACCOUNT_SID') || !defined('TWILIO_AUTH_TOKEN') ||
        TWILIO_ACCOUNT_SID === 'your_account_sid_here' || 
        TWILIO_AUTH_TOKEN === 'your_auth_token_here') {
        error_log("Twilio no está configurado. Usando modo simulado.");
        return enviar_mensaje_simulado($to, $message, $use_whatsapp ? 'WhatsApp' : 'SMS');
    }

    // Decidir qué método usar
    if ($use_whatsapp) {
        return enviar_whatsapp($to, $message);
    } else {
        return enviar_sms_twilio($to, $message);
    }
}

/**
 * Envía mensaje por WhatsApp usando Twilio
 * 
 * @param string $to Número destino
 * @param string $message Mensaje
 * @return bool
 */
function enviar_whatsapp($to, $message) {
    $url = "https://api.twilio.com/2010-04-01/Accounts/" . TWILIO_ACCOUNT_SID . "/Messages.json";

    // Para WhatsApp, agregar prefijo "whatsapp:"
    $to_whatsapp = 'whatsapp:' . $to;
    $from_whatsapp = 'whatsapp:' . TELEFONO_EMISOR;

    $data = array(
        'To' => $to_whatsapp,
        'From' => $from_whatsapp,
        'Body' => $message
    );

    return enviar_twilio_request($url, $data, 'WhatsApp');
}

/**
 * Envía SMS tradicional usando Twilio
 * 
 * @param string $to Número destino
 * @param string $message Mensaje
 * @return bool
 */
function enviar_sms_twilio($to, $message) {
    $url = "https://api.twilio.com/2010-04-01/Accounts/" . TWILIO_ACCOUNT_SID . "/Messages.json";

    $data = array(
        'To' => $to,
        'From' => TELEFONO_EMISOR,
        'Body' => $message
    );

    return enviar_twilio_request($url, $data, 'SMS');
}

/**
 * Realiza la petición HTTP a Twilio
 * 
 * @param string $url Endpoint de Twilio
 * @param array $data Datos del mensaje
 * @param string $tipo Tipo de mensaje (WhatsApp o SMS)
 * @return bool
 */
function enviar_twilio_request($url, $data, $tipo = 'Mensaje') {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, TWILIO_ACCOUNT_SID . ':' . TWILIO_AUTH_TOKEN);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        error_log("{$tipo} - Error en cURL: " . $error);
        return false;
    }
    
    curl_close($ch);

    // Verificar respuesta
    if ($http_code >= 200 && $http_code < 300) {
        error_log("{$tipo} enviado exitosamente a: " . $data['To']);
        return true;
    } else {
        error_log("{$tipo} - Error HTTP {$http_code}: " . $response);
        
        // Intentar decodificar respuesta JSON para más detalles
        $response_data = json_decode($response, true);
        if (isset($response_data['message'])) {
            error_log("{$tipo} - Mensaje de error: " . $response_data['message']);
        }
        
        return false;
    }
}

/**
 * Envía código de verificación por WhatsApp
 * 
 * @param string $telefono_usuario Número del usuario
 * @param string $codigo Código de 6 dígitos
 * @param string $nombre_usuario Nombre del usuario
 * @return bool
 */
function enviar_codigo_verificacion_whatsapp($telefono_usuario, $codigo, $nombre_usuario = '') {
    $saludo = $nombre_usuario ? "Hola {$nombre_usuario}," : "Hola,";
    
    $mensaje = "{$saludo}\n\n"
             . "Tu código de verificación para el Congreso de Mercadotecnia es:\n\n"
             . "🔐 {$codigo}\n\n"
             . "Este código expira en 15 minutos.\n"
             . "No compartas este código con nadie.\n\n"
             . "Si no solicitaste este código, ignora este mensaje.\n\n"
             . "Congreso MKT - UAA";
    
    // Determinar si usar WhatsApp
    $use_whatsapp = defined('USE_WHATSAPP') && USE_WHATSAPP === true;
    
    return enviar_mensaje($telefono_usuario, $mensaje, $use_whatsapp);
}

/**
 * Alias para compatibilidad con código anterior
 * Envía por WhatsApp o SMS según configuración
 */
function enviar_codigo_verificacion_sms($telefono_usuario, $codigo, $nombre_usuario = '') {
    return enviar_codigo_verificacion_whatsapp($telefono_usuario, $codigo, $nombre_usuario);
}

/**
 * Simula envío de mensaje (modo desarrollo)
 * 
 * @param string $to Número destino
 * @param string $message Mensaje
 * @param string $tipo Tipo (WhatsApp o SMS)
 * @return bool
 */
function enviar_mensaje_simulado($to, $message, $tipo = 'WhatsApp') {
    $log_file = __DIR__ . '/sms_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    
    $log_entry = "\n========================================\n"
               . "Timestamp: {$timestamp}\n"
               . "Tipo: {$tipo}\n"
               . "From: " . (defined('TELEFONO_EMISOR') ? TELEFONO_EMISOR : 'No configurado') . "\n"
               . "To: {$to}\n"
               . "Message:\n{$message}\n"
               . "========================================\n";
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    error_log("{$tipo} simulado guardado en log para: {$to}");
    
    return true;
}

/**
 * Función auxiliar para formatear número de teléfono
 * 
 * @param string $telefono Número sin formato
 * @return string Número formateado con código de país
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

/**
 * Verifica si Twilio está configurado correctamente
 * 
 * @return array Estado de configuración
 */
function verificar_configuracion_twilio() {
    $estado = [
        'configurado' => false,
        'modo_desarrollo' => true,
        'usa_whatsapp' => false,
        'numero_emisor' => '',
        'errores' => []
    ];

    // Verificar modo desarrollo
    if (defined('SMS_MODE_DESARROLLO')) {
        $estado['modo_desarrollo'] = SMS_MODE_DESARROLLO;
    }

    // Verificar número emisor
    if (defined('TELEFONO_EMISOR')) {
        $estado['numero_emisor'] = TELEFONO_EMISOR;
    } else {
        $estado['errores'][] = 'TELEFONO_EMISOR no definido';
    }

    // Verificar WhatsApp
    if (defined('USE_WHATSAPP')) {
        $estado['usa_whatsapp'] = USE_WHATSAPP;
    }

    // Verificar credenciales Twilio
    if (!defined('TWILIO_ACCOUNT_SID') || TWILIO_ACCOUNT_SID === 'your_account_sid_here') {
        $estado['errores'][] = 'TWILIO_ACCOUNT_SID no configurado';
    }

    if (!defined('TWILIO_AUTH_TOKEN') || TWILIO_AUTH_TOKEN === 'your_auth_token_here') {
        $estado['errores'][] = 'TWILIO_AUTH_TOKEN no configurado';
    }

    // Si no hay errores y no está en modo desarrollo, está configurado
    if (empty($estado['errores']) && !$estado['modo_desarrollo']) {
        $estado['configurado'] = true;
    }

    return $estado;
}
?>
