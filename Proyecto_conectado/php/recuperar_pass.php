<?php
// php/recuperar_pass.php
// Recibe `recovery_input` (matrícula o email) desde el formulario y envía un correo con enlace de restablecimiento.
require_once 'conexion.php';
require_once 'send_notifications.php';

header('Content-Type: application/json; charset=utf-8');
// Evitar que mensajes/avisos rompan el JSON: capturamos salida accidental
ob_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = trim($_POST['recovery_input'] ?? '');
if (empty($input)) {
    echo json_encode(['success' => false, 'message' => 'Ingrese su matrícula o correo.']);
    exit;
}

try {
    // Buscar usuario por matrícula o email
    $stmt = $pdo->prepare("SELECT id_usuario, nombre_completo, email FROM usuarios WHERE matricula = :m OR email = :e LIMIT 1");
    $stmt->execute([':m' => $input, ':e' => $input]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // No revelar demasiado por seguridad
        echo json_encode(['success' => true, 'message' => 'Si el usuario existe, se enviaron instrucciones al correo registrado.']);
        exit;
    }

    if (empty($user['email'])) {
        echo json_encode(['success' => false, 'message' => 'El usuario no tiene correo registrado. Contacte al administrador.']);
        exit;
    }

    // Generar token seguro y caducidad (1 hora)
    $token = bin2hex(random_bytes(16));
    $expires = date('Y-m-d H:i:s', time() + 3600);

    // Crear tabla password_resets si no existe (simple)
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        token VARCHAR(128) NOT NULL,
        expires DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX(token(64))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Insertar token
    $ins = $pdo->prepare("INSERT INTO password_resets (id_usuario, token, expires) VALUES (:id_usuario, :token, :expires)");
    $ins->execute([':id_usuario' => $user['id_usuario'], ':token' => password_hash($token, PASSWORD_DEFAULT), ':expires' => $expires]);

    // Construir enlace de restablecimiento con token en query.
    // Puedes definir RESET_URL_BASE en php/smtp_config.php (o en otro config) para controlar la URL completa.
    // Por defecto usamos la ruta solicitada por el usuario (ajustada a reset_password.html).
    $defaultBase = 'http://localhost/Proyecto/Sistema-de-gestion-Congreso-de-Mercadotecnia/Proyecto_conectado/Front-end';
    if (defined('RESET_URL_BASE') && !empty(RESET_URL_BASE)) {
        $base = RESET_URL_BASE;
    } elseif (defined('BASE_URL') && !empty(BASE_URL)) {
        $base = BASE_URL;
    } else {
        $base = $defaultBase;
    }
    $resetUrl = rtrim($base, '/') . '/reset_password.html?token=' . urlencode($token) . '&uid=' . urlencode($user['id_usuario']);

    $subject = 'Recuperación de contraseña';
    $html = '<p>Hola ' . htmlspecialchars($user['nombre_completo']) . ',</p>';
    $html .= '<p>Se ha solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para crear una nueva contraseña. El enlace caduca en 1 hora.</p>';
    $html .= '<p><a href="' . htmlspecialchars($resetUrl) . '">Restablecer contraseña</a></p>';
    $html .= '<p>Si no solicitaste esto, ignora este correo.</p>';

    // Intentar enviar y devolver resultado en 'sent' para depuración
    $sent = false;
    try {
        $sent = (bool) send_email($user['email'], $subject, $html);
        if (!$sent) {
            error_log('[recuperar_pass] send_email falló para: ' . $user['email']);
        }
    } catch (Exception $e) {
        error_log('[recuperar_pass] Exception en send_email: ' . $e->getMessage());
    }

    // Limpiar cualquier salida accidental y devolver JSON (manteniendo mensaje genérico)
    ob_clean();
    echo json_encode(['success' => true, 'message' => 'Si el usuario existe, se enviaron instrucciones al correo registrado.', 'sent' => $sent]);
    exit;

} catch (Exception $e) {
    error_log('[recuperar_pass] ' . $e->getMessage());
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Error interno.']);
    exit;
}

?>
