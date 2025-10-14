<?php
// php/send_notifications.php
// Funciones simples para enviar notificaciones por correo cuando se crean eventos o cuando un usuario se inscribe.
// Nota: usa la función mail() de PHP. En entornos locales (XAMPP en Windows) puede requerir configuración de SMTP.

function send_email($to, $subject, $htmlBody, $fromName = 'Congreso Universitario', $fromEmail = 'no-reply@localhost') {
    // Intent: usar PHPMailer vía SMTP si está disponible (composer/autoload o librería incluida),
    // de lo contrario, caer en mail() (suponiendo que PHP está configurado para SMTP en php.ini/sendmail).

    // Opcional: cargar configuración SMTP si existe en php/smtp_config.php
    $smtpConfig = __DIR__ . '/smtp_config.php';
    if (file_exists($smtpConfig)) {
        require_once $smtpConfig;
    }

    // Primero, intentar PHPMailer si fue instalado con Composer o agregado en /php/PHPMailer
    $composerAutoload = __DIR__ . '/vendor/autoload.php';
    $manualPHPMailer = __DIR__ . '/PHPMailer/src/PHPMailer.php';
    // Try loading PHPMailer via Composer first, then via manual include; if neither present, fall back to mail().
    $mail = null;
    if (file_exists($composerAutoload)) {
        require_once $composerAutoload;
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        } catch (\Exception $e) {
            error_log('[send_notifications] Error instantiating PHPMailer via Composer: ' . $e->getMessage());
            $mail = null;
        }
    } elseif (file_exists($manualPHPMailer)) {
        // Composer not available but PHPMailer downloaded manually into php/PHPMailer
        // Require the three core files if they exist, then attempt to instantiate
        $srcDir = __DIR__ . '/PHPMailer/src';
        if (file_exists($srcDir . '/Exception.php')) require_once $srcDir . '/Exception.php';
        if (file_exists($srcDir . '/PHPMailer.php')) require_once $srcDir . '/PHPMailer.php';
        if (file_exists($srcDir . '/SMTP.php')) require_once $srcDir . '/SMTP.php';
        // If class is available, instantiate
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            } catch (\Exception $e) {
                error_log('[send_notifications] Error instantiating PHPMailer (PHPMailer/src): ' . $e->getMessage());
                $mail = null;
            }
        } else {
            error_log('[send_notifications] PHPMailer class not found after including PHPMailer/src files');
        }
    } elseif (file_exists(__DIR__ . '/PHPMailer.php')) {
        // PHPMailer files placed directly in php/ (PHPMailer.php, SMTP.php, Exception.php)
        // Attempt to require them
        if (file_exists(__DIR__ . '/Exception.php')) require_once __DIR__ . '/Exception.php';
        require_once __DIR__ . '/PHPMailer.php';
        if (file_exists(__DIR__ . '/SMTP.php')) require_once __DIR__ . '/SMTP.php';
        // Check that the namespaced class exists and instantiate
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            } catch (\Exception $e) {
                error_log('[send_notifications] Error instantiating manual PHPMailer: ' . $e->getMessage());
                $mail = null;
            }
        } else {
            // If class not found, log helpful debug information when enabled
            error_log('[send_notifications] PHPMailer.php was included but class PHPMailer\\PHPMailer\\PHPMailer does not exist');
            if (defined('SMTP_DEBUG') && SMTP_DEBUG) {
                $dbg = __DIR__ . '/smtp_debug.log';
                @file_put_contents($dbg, date('c') . " [send_notifications] Included files in " . __DIR__ . PHP_EOL, FILE_APPEND);
                $files = glob(__DIR__ . DIRECTORY_SEPARATOR . '*.php');
                foreach ($files as $f) {
                    @file_put_contents($dbg, basename($f) . "\n", FILE_APPEND);
                }
            }
        }
    }

    // If PHPMailer is available, attempt to send via SMTP (using smtp_config.php constants or env vars)
    if ($mail) {
        try {
            // Configuración SMTP por defecto. Puedes sobreescribir estas constantes en un archivo separado
            $smtpHost = defined('SMTP_HOST') ? SMTP_HOST : getenv('SMTP_HOST');
            $smtpUser = defined('SMTP_USER') ? SMTP_USER : getenv('SMTP_USER');
            $smtpPass = defined('SMTP_PASS') ? SMTP_PASS : getenv('SMTP_PASS');
            // Evitar expresiones ambiguas: asignar puerto explícitamente
            if (defined('SMTP_PORT')) {
                $smtpPort = SMTP_PORT;
            } else {
                $envPort = getenv('SMTP_PORT');
                $smtpPort = $envPort && $envPort !== false ? $envPort : 587;
            }
            $smtpSecure = defined('SMTP_SECURE') ? SMTP_SECURE : getenv('SMTP_SECURE'); // 'tls' or 'ssl'

            if ($smtpHost) {
                $mail->isSMTP();
                $mail->Host = $smtpHost;
                if (!empty($smtpUser)) {
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtpUser;
                    $mail->Password = $smtpPass;
                }
                if (!empty($smtpSecure)) $mail->SMTPSecure = $smtpSecure;
                $mail->Port = $smtpPort;
            }

            // Optional SMTP debug: define('SMTP_DEBUG', true) in smtp_config.php to enable
            $debugEnabled = defined('SMTP_DEBUG') ? (bool) SMTP_DEBUG : false;
            if ($debugEnabled) {
                $debugFile = __DIR__ . '/smtp_debug.log';
                // clear previous debug log
                @file_put_contents($debugFile, "[smtp debug start] " . date('c') . PHP_EOL);
                $mail->SMTPDebug = 3; // verbose
                $mail->Debugoutput = function($str, $level) use ($debugFile) {
                    @file_put_contents($debugFile, date('c') . " [level={$level}] " . $str . PHP_EOL, FILE_APPEND);
                };
            }

            // If the provided from email looks invalid or is the default local placeholder,
            // prefer using the SMTP user as the From address (many providers require the From to match the authenticated account).
            $effectiveFrom = $fromEmail;
            if (empty($effectiveFrom) || strpos($effectiveFrom, '@') === false || preg_match('/@localhost$/i', $effectiveFrom)) {
                if (!empty($smtpUser)) {
                    $effectiveFrom = $smtpUser;
                    $fromName = $fromName ?: $smtpUser;
                }
            }
            $mail->setFrom($effectiveFrom, $fromName);
            // Ensure the SMTP envelope sender (Return-Path) uses the authenticated user when possible
            if (!empty($smtpUser)) {
                $mail->Sender = $smtpUser;
            }
            // Enable AutoTLS where supported
            if (property_exists($mail, 'SMTPAutoTLS')) {
                $mail->SMTPAutoTLS = true;
            }
            if (defined('SMTP_DEBUG') && SMTP_DEBUG) {
                $dbg = isset($debugFile) ? $debugFile : __DIR__ . '/smtp_debug.log';
                @file_put_contents($dbg, date('c') . " [send_notifications] effectiveFrom={$effectiveFrom} smtpUser={$smtpUser} smtpHost={$smtpHost}\n", FILE_APPEND);
            }
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8'; // Asegurar codificación UTF-8
            $mail->Encoding = 'base64'; // Codificación segura para caracteres especiales
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log('[send_notifications] PHPMailer error: ' . $e->getMessage());
            // fallback a mail()
        }
    }

    // Cabeceras para mail() como fallback
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: {$fromName} <{$fromEmail}>" . "\r\n";

    // Intentar enviar mediante mail() (depende de la configuración de PHP/sendmail)
    try {
        $sent = @mail($to, $subject, $htmlBody, $headers);
        if (!$sent) {
            error_log("[send_notifications] mail() falló para: {$to} | asunto: {$subject}");
            return false;
        }
        return true;
    } catch (Exception $e) {
        error_log("[send_notifications] Exception al enviar mail a {$to}: " . $e->getMessage());
        return false;
    }
}

function sendEventCreatedToAll($pdo, $id_evento) {
    // Obtener detalles del evento
    $stmt = $pdo->prepare("SELECT nombre_evento, descripcion, fecha_inicio, hora_inicio, lugar FROM eventos WHERE id_evento = :id_evento");
    $stmt->execute([':id_evento' => $id_evento]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$evento) return false;

    $subject = "Nuevo evento: " . $evento['nombre_evento'];
    $date = isset($evento['fecha_inicio']) ? $evento['fecha_inicio'] : '';
    $time = isset($evento['hora_inicio']) ? $evento['hora_inicio'] : '';
    $place = $evento['lugar'] ?? '';

    $html = "<p>Se ha creado un nuevo evento en el sistema:</p>";
    $html .= "<h2>" . htmlspecialchars($evento['nombre_evento']) . "</h2>";
    if (!empty($evento['descripcion'])) $html .= "<p>" . nl2br(htmlspecialchars($evento['descripcion'])) . "</p>";
    $html .= "<p><strong>Fecha:</strong> " . htmlspecialchars($date) . " <strong>Hora:</strong> " . htmlspecialchars($time) . "</p>";
    if (!empty($place)) $html .= "<p><strong>Lugar:</strong> " . htmlspecialchars($place) . "</p>";
    $html .= "<p>Ingresa al sistema para ver más detalles.</p>";

    // Obtener usuarios con email
    $stmt2 = $pdo->query("SELECT email, nombre_completo FROM usuarios WHERE email IS NOT NULL AND email <> ''");
    $users = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $u) {
        $to = $u['email'];
        $personalized = "<p>Hola " . htmlspecialchars($u['nombre_completo']) . ",</p>" . $html;
        // Enviar (nota: para muchos usuarios, esto puede ser lento)
        send_email($to, $subject, $personalized);
        // Pequeña pausa para no saturar el servidor de correo local
        usleep(50000); // 50ms
    }
    return true;
}

function sendRegistrationToUser($pdo, $id_usuario, $id_evento) {
    // Obtener usuario y evento
    $stmtU = $pdo->prepare("SELECT nombre_completo, email FROM usuarios WHERE id_usuario = :id_usuario");
    $stmtU->execute([':id_usuario' => $id_usuario]);
    $user = $stmtU->fetch(PDO::FETCH_ASSOC);
    if (!$user || empty($user['email'])) return false;

    $stmtE = $pdo->prepare("SELECT nombre_evento, fecha_inicio, hora_inicio, lugar FROM eventos WHERE id_evento = :id_evento");
    $stmtE->execute([':id_evento' => $id_evento]);
    $evento = $stmtE->fetch(PDO::FETCH_ASSOC);
    if (!$evento) return false;

    $subject = "Confirmación de inscripción: " . $evento['nombre_evento'];
    $html = "<p>Hola " . htmlspecialchars($user['nombre_completo']) . ",</p>";
    $html .= "<p>Tu inscripción al evento <strong>" . htmlspecialchars($evento['nombre_evento']) . "</strong> fue procesada correctamente.</p>";
    $html .= "<p><strong>Fecha:</strong> " . htmlspecialchars($evento['fecha_inicio']) . " <strong>Hora:</strong> " . htmlspecialchars($evento['hora_inicio']) . "</p>";
    if (!empty($evento['lugar'])) $html .= "<p><strong>Lugar:</strong> " . htmlspecialchars($evento['lugar']) . "</p>";
    $html .= "<p>Gracias por inscribirte. Te esperamos.</p>";

    return send_email($user['email'], $subject, $html);
}

?>