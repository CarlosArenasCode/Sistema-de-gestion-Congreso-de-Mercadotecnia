<?php
/**
 * Servicio de envío de códigos de verificación por WhatsApp
 * Envía códigos de 6 dígitos al número del usuario
 * Usa el número emisor: +524492106893
 */

require_once __DIR__ . '/whatsapp_service.php';

/**
 * Envía código de verificación de 6 dígitos por WhatsApp
 * 
 * @param string $telefono_destino Número de teléfono del usuario (formato: +5244912345678)
 * @param string $codigo_verificacion Código de 6 dígitos
 * @param string $nombre_usuario Nombre del usuario (opcional)
 * @return array ['success' => bool, 'message' => string]
 */
function enviar_codigo_verificacion_whatsapp($telefono_destino, $codigo_verificacion, $nombre_usuario = '') {
    // Validar formato del teléfono
    if (!validar_formato_telefono($telefono_destino)) {
        return [
            'success' => false,
            'message' => 'Formato de teléfono inválido. Use formato: +5244912345678'
        ];
    }

    // Validar código de 6 dígitos
    if (!preg_match('/^\d{6}$/', $codigo_verificacion)) {
        return [
            'success' => false,
            'message' => 'Código de verificación debe ser de 6 dígitos'
        ];
    }

    // Construir mensaje personalizado
    $saludo = $nombre_usuario ? "Hola $nombre_usuario" : "Hola";
    
    $mensaje = "$saludo, 🎓\n\n";
    $mensaje .= "Tu código de verificación es:\n\n";
    $mensaje .= "🔐 *$codigo_verificacion*\n\n";
    $mensaje .= "Este código es válido por 10 minutos.\n";
    $mensaje .= "Si no solicitaste este código, ignora este mensaje.\n\n";
    $mensaje .= "Congreso de Mercadotecnia UAA 📚";

    // Enviar por WhatsApp
    try {
        $enviado = enviar_mensaje($telefono_destino, $mensaje, true);
        
        if ($enviado) {
            // Registrar envío exitoso
            registrar_envio_codigo($telefono_destino, $codigo_verificacion, 'WhatsApp', 'exitoso');
            
            return [
                'success' => true,
                'message' => 'Código enviado correctamente por WhatsApp',
                'metodo' => 'WhatsApp',
                'numero_emisor' => TELEFONO_EMISOR
            ];
        } else {
            // Registrar fallo
            registrar_envio_codigo($telefono_destino, $codigo_verificacion, 'WhatsApp', 'fallido');
            
            return [
                'success' => false,
                'message' => 'Error al enviar código por WhatsApp'
            ];
        }
    } catch (Exception $e) {
        error_log("Error al enviar código WhatsApp: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error en el servicio de WhatsApp: ' . $e->getMessage()
        ];
    }
}

/**
 * Valida el formato del número de teléfono
 * 
 * @param string $telefono Número a validar
 * @return bool
 */
function validar_formato_telefono($telefono) {
    // Formato esperado: +5244912345678 (código país + número)
    // Mínimo 10 dígitos, máximo 15
    return preg_match('/^\+\d{10,15}$/', $telefono);
}

/**
 * Registra el envío del código en log
 * 
 * @param string $telefono Número destino
 * @param string $codigo Código enviado
 * @param string $metodo WhatsApp o SMS
 * @param string $estado exitoso o fallido
 */
function registrar_envio_codigo($telefono, $codigo, $metodo, $estado) {
    $log_file = __DIR__ . '/logs/whatsapp_codigos.log';
    $log_dir = dirname($log_file);
    
    // Crear directorio si no existe
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $metodo | $estado | Destino: $telefono | Código: $codigo\n";
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

/**
 * Genera un código de verificación de 6 dígitos
 * 
 * @return string Código de 6 dígitos
 */
function generar_codigo_verificacion() {
    return sprintf("%06d", mt_rand(0, 999999));
}

/**
 * Envía mensaje simple por WhatsApp (sin formato de código)
 * 
 * @param string $telefono_destino Número destino
 * @param string $mensaje Mensaje a enviar
 * @return array ['success' => bool, 'message' => string]
 */
function enviar_mensaje_whatsapp($telefono_destino, $mensaje) {
    if (!validar_formato_telefono($telefono_destino)) {
        return [
            'success' => false,
            'message' => 'Formato de teléfono inválido'
        ];
    }

    try {
        $enviado = enviar_mensaje($telefono_destino, $mensaje, true);
        
        return [
            'success' => $enviado,
            'message' => $enviado ? 'Mensaje enviado' : 'Error al enviar mensaje'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

/**
 * Obtiene información del estado del servicio WhatsApp
 * 
 * @return array Información del servicio
 */
function obtener_estado_servicio_whatsapp() {
    return [
        'numero_emisor' => TELEFONO_EMISOR,
        'modo_desarrollo' => SMS_MODE_DESARROLLO,
        'usa_whatsapp' => USE_WHATSAPP,
        'twilio_configurado' => (
            defined('TWILIO_ACCOUNT_SID') && 
            TWILIO_ACCOUNT_SID !== 'your_account_sid_here'
        ),
        'estado' => SMS_MODE_DESARROLLO ? 'Modo Desarrollo (logs)' : 'Producción (envíos reales)'
    ];
}
