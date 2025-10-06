<?php
// php/reset_password.php
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$uid = intval($_POST['uid'] ?? 0);
$token = $_POST['token'] ?? '';
$newPass = $_POST['new_password'] ?? '';

if ($uid <= 0 || empty($token) || empty($newPass)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    // Buscar tokens vigentes
    $stmt = $pdo->prepare("SELECT id, token, expires FROM password_resets WHERE id_usuario = :uid ORDER BY created_at DESC");
    $stmt->execute([':uid' => $uid]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) {
        echo json_encode(['success' => false, 'message' => 'Token inválido o expirado']);
        exit;
    }

    $valid = false;
    $rowIdToDelete = null;
    foreach ($rows as $r) {
        if (new DateTime() > new DateTime($r['expires'])) continue;
        if (password_verify($token, $r['token'])) {
            $valid = true;
            $rowIdToDelete = $r['id'];
            break;
        }
    }

    if (!$valid) {
        echo json_encode(['success' => false, 'message' => 'Token inválido o expirado']);
        exit;
    }

    // Actualizar contraseña del usuario
    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    $upd = $pdo->prepare("UPDATE usuarios SET password_hash = :hash WHERE id_usuario = :uid");
    $upd->execute([':hash' => $hash, ':uid' => $uid]);

    // Eliminar tokens para este usuario
    $del = $pdo->prepare("DELETE FROM password_resets WHERE id_usuario = :uid");
    $del->execute([':uid' => $uid]);

    echo json_encode(['success' => true, 'message' => 'Contraseña restablecida correctamente']);
    exit;

} catch (Exception $e) {
    error_log('[reset_password] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno']);
    exit;
}

?>
