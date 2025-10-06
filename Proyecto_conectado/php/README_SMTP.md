PHPMailer / SMTP configuration

This project supports sending email via PHPMailer (recommended) or falls back to PHP's mail() function.

To enable PHPMailer (recommended):

1. Install Composer (https://getcomposer.org) if not installed.
2. From the `Proyecto_conectado/php` folder run:
   composer require phpmailer/phpmailer

3. Set SMTP configuration via environment variables or define constants in your Apache/PHP config:
   - SMTP_HOST (e.g., smtp.gmail.com)
   - SMTP_USER
   - SMTP_PASS
   - SMTP_PORT (e.g., 587)
   - SMTP_SECURE (tls or ssl)

PHPMailer will be automatically used if `vendor/autoload.php` exists.

Fallback: If PHPMailer is not installed, the code will use `mail()` which requires correct SMTP/sendmail settings in php.ini (Windows XAMPP: configure [mail function] or use fake sendmail). For local testing it's fine; for production use PHPMailer + authenticated SMTP.

Windows / Composer quick install
1. Download and run Composer-Setup from https://getcomposer.org/Composer-Setup.exe (instalador para Windows).
2. Durante la instalación, acepta añadir Composer al PATH para poder usarlo desde PowerShell.
3. Abre PowerShell, navega a la carpeta `Proyecto_conectado\php` y ejecuta:

```powershell
composer require phpmailer/phpmailer
```

Alternativa (sin Composer): descarga PHPMailer manualmente
1. Descarga el ZIP desde https://github.com/PHPMailer/PHPMailer/releases (elige la última versión estable).
2. Extrae la carpeta `src` dentro de `Proyecto_conectado/php/PHPMailer/` (debe existir `Proyecto_conectado/php/PHPMailer/src/PHPMailer.php`).
3. Nuestro `send_notifications.php` detectará la presencia de esta carpeta y usará PHPMailer automáticamente.

Después de instalar PHPMailer (por Composer o manual), asegúrate de definir `php/smtp_config.php` con tus credenciales SMTP (ejemplo incluido en el repo) y reinicia Apache.