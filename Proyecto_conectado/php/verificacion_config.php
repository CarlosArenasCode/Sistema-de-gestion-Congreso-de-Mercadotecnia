<?php
/**
 * Configuración de Verificación por WhatsApp/SMS
 * Configura el número EMISOR desde el cual se enviarán los códigos de verificación
 */

// ============================================
// CONFIGURACIÓN BÁSICA
// ============================================

// NÚMERO EMISOR (FROM) - Tu número de WhatsApp Business desde donde se enviarán los códigos
// Este es el número que aparecerá como remitente
define('TELEFONO_EMISOR', '+5244921068393'); // Tu número: +52 449 210 6893

// Modo de desarrollo: Si es true, los mensajes se guardan en log en lugar de enviarse
// ⚠️ CAMBIAR A false cuando tengas Twilio configurado y plantillas aprobadas
define('SMS_MODE_DESARROLLO', true);

// ============================================
// CREDENCIALES DE TWILIO
// ============================================

// Obtén estas credenciales en: https://console.twilio.com/
// Account SID (comienza con "AC")
define('TWILIO_ACCOUNT_SID', 'your_account_sid_here');

// Auth Token (haz clic en "Show" en el dashboard para verlo)
define('TWILIO_AUTH_TOKEN', 'your_auth_token_here');

// ============================================
// CONFIGURACIÓN DE WHATSAPP
// ============================================

// Usar WhatsApp en lugar de SMS tradicional
// true = WhatsApp | false = SMS
define('USE_WHATSAPP', true);

// SID de la plantilla de WhatsApp aprobada (comienza con "HX")
// Obténlo después de que tu plantilla sea aprobada
// Ve a: https://console.twilio.com/us1/develop/sms/content-editor
define('WHATSAPP_TEMPLATE_SID', ''); // Vacío hasta que tengas plantilla aprobada

// Usar WhatsApp Sandbox para pruebas (solo desarrollo)
// true = Usa sandbox | false = Usa producción
define('USE_WHATSAPP_SANDBOX', false);

// ============================================
// INSTRUCCIONES RÁPIDAS
// ============================================

/*
FASE 1: DESARROLLO (ACTUAL)
- SMS_MODE_DESARROLLO = true
- Los mensajes se guardan en sms_log.txt
- No necesitas configurar nada más

FASE 2: TESTING CON SANDBOX
- Crea cuenta en Twilio
- Activa WhatsApp Sandbox
- Configura TWILIO_ACCOUNT_SID y TWILIO_AUTH_TOKEN
- Cambia USE_WHATSAPP_SANDBOX = true
- Cambia SMS_MODE_DESARROLLO = false
- Prueba enviando mensajes

FASE 3: PRODUCCIÓN CON WHATSAPP
- Verifica tu número +52 449 210 6893 en Twilio
- Crea y aprueba plantilla de WhatsApp
- Configura WHATSAPP_TEMPLATE_SID
- Cambia USE_WHATSAPP_SANDBOX = false
- Cambia SMS_MODE_DESARROLLO = false
- ¡Listo para producción!

📖 Ver guía completa: CONFIGURAR_WHATSAPP_PASO_A_PASO.md
*/

?>
