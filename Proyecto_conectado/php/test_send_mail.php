<?php
// php/test_send_mail.php
// Uso: abrir en navegador: php/test_send_mail.php?to=tu@correo.com
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/send_notifications.php';

$to = trim($_GET['to'] ?? '');
if (empty($to)) {
    echo json_encode(['success' => false, 'message' => 'Parámetro ?to= requerido']);
    exit;
}

$subject = 'Prueba de envío desde Sistema de Gestión - ' . date('Y-m-d H:i:s');
$body = '<p>Este es un correo de prueba para verificar la configuración de envío desde el servidor local.</p>';

$result = false;
try {
    $result = send_email($to, $subject, $body, 'Congreso Test', 'no-reply@localhost');
} catch (Exception $e) {
    $err = $e->getMessage();
    // continuar
}

$out = ['success' => (bool)$result];
if ($result) {
    $out['message'] = 'Correo enviado (send_email returned true). Revisa la bandeja de entrada y SPAM.';
} else {
    $out['message'] = 'send_email devolvió false o lanzó excepción.';
    if (isset($err)) $out['error_exception'] = $err;
}

// Intentar leer el log de sendmail si existe (ruta típica c:\xampp\sendmail\error.log)
$sendmailLog = 'C:\\xampp\\sendmail\\error.log';
if (file_exists($sendmailLog) && is_readable($sendmailLog)) {
    $contents = file($sendmailLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $tail = array_slice($contents, -40);
    $out['sendmail_log_tail'] = $tail;
}

echo json_encode($out);
exit;
