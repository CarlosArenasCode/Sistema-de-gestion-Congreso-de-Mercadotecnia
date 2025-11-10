<?php
// php/test_email_debug.php
// Script de prueba detallada para envío de correos
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Diagnóstico de Configuración de Correo</h2>";

// 1. Verificar que smtp_config.php existe y se puede cargar
echo "<h3>1. Verificando smtp_config.php</h3>";
$smtpConfigPath = __DIR__ . '/smtp_config.php';
if (file_exists($smtpConfigPath)) {
    echo "✓ smtp_config.php existe<br>";
    require_once $smtpConfigPath;
    
    echo "✓ Configuración cargada:<br>";
    echo "&nbsp;&nbsp;- SMTP_HOST: " . (defined('SMTP_HOST') ? SMTP_HOST : 'NO DEFINIDO') . "<br>";
    echo "&nbsp;&nbsp;- SMTP_USER: " . (defined('SMTP_USER') ? SMTP_USER : 'NO DEFINIDO') . "<br>";
    echo "&nbsp;&nbsp;- SMTP_PASS: " . (defined('SMTP_PASS') ? str_repeat('*', strlen(SMTP_PASS)) : 'NO DEFINIDO') . "<br>";
    echo "&nbsp;&nbsp;- SMTP_PORT: " . (defined('SMTP_PORT') ? SMTP_PORT : 'NO DEFINIDO') . "<br>";
    echo "&nbsp;&nbsp;- SMTP_SECURE: " . (defined('SMTP_SECURE') ? SMTP_SECURE : 'NO DEFINIDO') . "<br>";
} else {
    echo "✗ smtp_config.php NO existe en: $smtpConfigPath<br>";
}

// 2. Verificar PHPMailer
echo "<h3>2. Verificando PHPMailer</h3>";

$phpMailerPaths = [
    __DIR__ . '/vendor/autoload.php' => 'Composer autoload',
    __DIR__ . '/PHPMailer-6.11.1/src/PHPMailer.php' => 'PHPMailer 6.11.1 (PHPMailer-6.11.1/src)',
    __DIR__ . '/PHPMailer/src/PHPMailer.php' => 'PHPMailer manual (PHPMailer/src)',
    __DIR__ . '/PHPMailer.php' => 'PHPMailer directo'
];

$phpMailerLoaded = false;
foreach ($phpMailerPaths as $path => $description) {
    if (file_exists($path)) {
        echo "✓ Encontrado: $description en $path<br>";
        
        if (strpos($path, 'autoload.php') !== false) {
            require_once $path;
            $phpMailerLoaded = true;
        } elseif (strpos($path, 'PHPMailer-6.11.1/src') !== false) {
            require_once __DIR__ . '/PHPMailer-6.11.1/src/Exception.php';
            require_once __DIR__ . '/PHPMailer-6.11.1/src/PHPMailer.php';
            require_once __DIR__ . '/PHPMailer-6.11.1/src/SMTP.php';
            $phpMailerLoaded = true;
        } elseif (strpos($path, 'PHPMailer/src') !== false) {
            require_once __DIR__ . '/PHPMailer/src/Exception.php';
            require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
            require_once __DIR__ . '/PHPMailer/src/SMTP.php';
            $phpMailerLoaded = true;
        } elseif (basename($path) === 'PHPMailer.php') {
            require_once __DIR__ . '/Exception.php';
            require_once __DIR__ . '/PHPMailer.php';
            require_once __DIR__ . '/SMTP.php';
            $phpMailerLoaded = true;
        }
        break;
    }
}

if (!$phpMailerLoaded) {
    echo "✗ PHPMailer NO encontrado en ninguna ubicación<br>";
    echo "<strong>SOLUCIÓN:</strong> Necesitas instalar PHPMailer<br>";
} else {
    echo "✓ PHPMailer cargado correctamente<br>";
}

// 3. Verificar extensión OpenSSL
echo "<h3>3. Verificando extensión OpenSSL</h3>";
if (extension_loaded('openssl')) {
    echo "✓ Extensión OpenSSL está habilitada<br>";
} else {
    echo "✗ Extensión OpenSSL NO está habilitada<br>";
    echo "<strong>SOLUCIÓN:</strong> Habilita extension=openssl en php.ini<br>";
}

// 4. Intentar enviar correo de prueba
echo "<h3>4. Prueba de Envío de Correo</h3>";

$emailDestino = $_GET['to'] ?? '';
if (empty($emailDestino)) {
    echo "⚠ Por favor, proporciona un email de destino: ?to=tu@correo.com<br>";
} else {
    echo "Intentando enviar a: <strong>$emailDestino</strong><br><br>";
    
    if ($phpMailerLoaded && class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
            
            // Debug detallado
            $mail->SMTPDebug = 3; // Muy verbose
            $mail->Debugoutput = function($str, $level) {
                echo "<div style='margin: 5px 0; padding: 5px; background: #f0f0f0; border-left: 3px solid #007bff;'>$str</div>";
            };
            
            // Configuración del correo
            $mail->setFrom(SMTP_USER, 'Congreso de Mercadotecnia');
            $mail->addAddress($emailDestino);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Prueba de envío - ' . date('Y-m-d H:i:s');
            $mail->Body = '<h2>Prueba de Correo</h2><p>Este es un correo de prueba desde el Sistema de Gestión del Congreso de Mercadotecnia.</p><p>Fecha: ' . date('Y-m-d H:i:s') . '</p>';
            
            echo "<div style='background: #e7f3ff; padding: 10px; margin: 10px 0;'>";
            echo "<strong>Iniciando envío...</strong><br>";
            
            if ($mail->send()) {
                echo "</div>";
                echo "<div style='background: #d4edda; color: #155724; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
                echo "✓ <strong>¡CORREO ENVIADO EXITOSAMENTE!</strong><br>";
                echo "Revisa la bandeja de entrada (y SPAM) de: $emailDestino";
                echo "</div>";
            } else {
                echo "</div>";
                echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
                echo "✗ Error al enviar<br>";
                echo "Info: " . $mail->ErrorInfo;
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
            echo "✗ <strong>EXCEPCIÓN CAPTURADA:</strong><br>";
            echo htmlspecialchars($e->getMessage());
            echo "</div>";
        }
    } else {
        echo "✗ No se puede enviar: PHPMailer no está disponible<br>";
    }
}

// 5. Verificar archivo de debug si existe
echo "<h3>5. Log de Debug SMTP</h3>";
$debugLog = __DIR__ . '/smtp_debug.log';
if (file_exists($debugLog)) {
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>";
    echo htmlspecialchars(file_get_contents($debugLog));
    echo "</pre>";
} else {
    echo "No hay archivo smtp_debug.log (se creará al enviar correos)<br>";
}

echo "<hr>";
echo "<p><small>Script ejecutado en: " . date('Y-m-d H:i:s') . "</small></p>";
?>
