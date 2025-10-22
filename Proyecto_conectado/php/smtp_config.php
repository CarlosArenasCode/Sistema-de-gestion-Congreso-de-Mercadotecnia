<?php
/**
 * php/smtp_config.php
 * Configuración SMTP opcional. Si existe, `send_notifications.php` la cargará
 * y `send_email()` usará estas constantes para PHPMailer.
 *
 * Rellena las constantes según tu proveedor. NO subas este archivo a repositorios públicos.
 */

// Ejemplo para Outlook / Office365
// Si usas Outlook.com (hotmail/outlook) o Office365, las opciones típicas son:
// Host: smtp.office365.com
// Port: 587
// Secure: tls

define('SMTP_HOST', 'smtp.office365.com');
define('SMTP_USER', 'al529633@edu.uaa.mx'); // tu cuenta completa
define('SMTP_PASS', 'Rodriguez05');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // 'tls' o 'ssl' o ''
define('SMTP_DEBUG', true);
define('RESET_URL_BASE', 'http://localhost/Proyecto/Sistema-de-gestion-Congreso-de-Mercadotecnia/Proyecto_conectado/Front-end/reset_password.html');


/* Notas importantes:
 - Si tu cuenta tiene MFA (verificación en dos pasos), necesitas generar
   una App Password desde la cuenta Microsoft o usar un método de autenticación
   moderno (OAuth2). Las App Passwords se crean en https://account.microsoft.com/security
   dependiendo del tipo de cuenta (personal vs empresarial).
 - Para cuentas Office365 empresariales, el administrador puede necesitar habilitar
   "SMTP AUTH" para la cuenta/mailbox.
 - Asegúrate de que la extensión OpenSSL esté activada en php.ini (extension=openssl)
   y que Apache se reinicie tras cualquier cambio.

Uso:
 - Después de guardar este archivo con tus credenciales, instala PHPMailer en
   la carpeta `php/` con Composer para que `send_notifications.php` use SMTP:
     composer require phpmailer/phpmailer
 - Reinicia Apache y prueba:
     http://localhost/Proyecto_conectado/php/test_send_mail.php?to=tu@correo.com

Seguridad:
 - No dejes credenciales en repositorios públicos.
 - Considera usar cuentas de envío dedicadas o proveedores (SendGrid, Mailgun)
   para producción.
*/

?>
