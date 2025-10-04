<?php
// php/send_notifications.php
// Funciones simples para enviar notificaciones por correo cuando se crean eventos o cuando un usuario se inscribe.
// Nota: usa la función mail() de PHP. En entornos locales (XAMPP en Windows) puede requerir configuración de SMTP.

function send_email($to, $subject, $htmlBody, $fromName = 'Congreso Universitario', $fromEmail = 'no-reply@localhost') {
    // Cabeceras
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: {$fromName} <{$fromEmail}>" . "\r\n";

    // Intentar enviar
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