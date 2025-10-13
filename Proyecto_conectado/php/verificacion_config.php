<?php
/**
 * Configuración de Verificación por SMS
 * Configura el número EMISOR desde el cual se enviarán los códigos de verificación
 */

// NÚMERO EMISOR (FROM) - Tu número de WhatsApp/SMS desde donde se enviarán los códigos
// Este es el número que aparecerá como remitente en los SMS de los usuarios
define('TELEFONO_EMISOR', '+5244921068393'); // Tu número: +52 449 210 6893

// Modo de desarrollo: Si es true, los SMS se guardan en log en lugar de enviarse
define('SMS_MODE_DESARROLLO', true); // Cambiar a false cuando configures Twilio para envío real

// Configuración de Twilio (necesario para envío real de SMS)
// Obtén estas credenciales en: https://console.twilio.com/
define('TWILIO_ACCOUNT_SID', 'your_account_sid_here'); // Tu Account SID de Twilio
define('TWILIO_AUTH_TOKEN', 'your_auth_token_here');   // Tu Auth Token de Twilio

?>
