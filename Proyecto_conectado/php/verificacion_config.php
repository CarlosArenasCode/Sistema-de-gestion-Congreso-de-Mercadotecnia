<?php
/**
 * Configuración de Verificación
 * Configura el número de teléfono fijo para recibir todos los códigos de verificación
 */

// NÚMERO DE TELÉFONO FIJO PARA PRUEBAS
// Todos los códigos de verificación se enviarán a este número
define('TELEFONO_VERIFICACION_ADMIN', '+521234567890'); // ⚠️ CAMBIAR POR TU NÚMERO REAL

// Modo de desarrollo: Si es true, los SMS se guardan en log en lugar de enviarse
define('SMS_MODE_DESARROLLO', true); // Cambiar a false cuando configures Twilio

// Mensaje personalizado para el administrador
define('SMS_ADMIN_PREFIX', true); // Incluir nombre del usuario en el SMS

?>
