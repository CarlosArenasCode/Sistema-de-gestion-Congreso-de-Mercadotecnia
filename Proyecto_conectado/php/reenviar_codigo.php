<?php
/**
 * reenviar_codigo.php
 * Genera y reenvía un nuevo código de verificación
 */

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

require 'conexion.php';
require 'send_notifications.php';
require 'sms_service.php';

try {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email es requerido'
        ]);
        exit;
    }

    // Buscar usuario
    $sql = "SELECT id_usuario, nombre_completo, telefono, verificado, fecha_codigo 
            FROM usuarios 
            WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ]);
        exit;
    }

    if ($usuario['verificado'] == 1) {
        echo json_encode([
            'success' => false,
            'message' => 'Esta cuenta ya está verificada'
        ]);
        exit;
    }

    // Verificar que no se esté reenviando muy frecuentemente (mínimo 1 minuto)
    if ($usuario['fecha_codigo']) {
        $fecha_codigo = new DateTime($usuario['fecha_codigo']);
        $ahora = new DateTime();
        $diferencia = $ahora->diff($fecha_codigo);
        $segundos = ($diferencia->i * 60) + $diferencia->s;

        if ($segundos < 60) {
            echo json_encode([
                'success' => false,
                'message' => 'Debes esperar al menos 1 minuto antes de solicitar un nuevo código'
            ]);
            exit;
        }
    }

    // Generar nuevo código
    $nuevo_codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $fecha_codigo = date('Y-m-d H:i:s');

    // Actualizar en BD
    $sql_update = "UPDATE usuarios 
                   SET codigo_verificacion = ?, 
                       fecha_codigo = ?,
                       intentos_verificacion = 0
                   WHERE id_usuario = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$nuevo_codigo, $fecha_codigo, $usuario['id_usuario']]);

    // Enviar por EMAIL
    $asunto = "Nuevo Código de Verificación - Congreso de Mercadotecnia";
    $mensaje_email = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #E4007C; color: white; padding: 20px; text-align: center; }
            .content { background-color: #f9f9f9; padding: 30px; border-radius: 5px; margin-top: 20px; }
            .code { font-size: 32px; font-weight: bold; color: #E4007C; text-align: center; letter-spacing: 5px; padding: 20px; background-color: white; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Nuevo Código de Verificación</h1>
            </div>
            <div class='content'>
                <h2>Hola, {$usuario['nombre_completo']}</h2>
                <p>Has solicitado un nuevo código de verificación:</p>
                <div class='code'>{$nuevo_codigo}</div>
                <p><strong>Este código expira en 15 minutos.</strong></p>
            </div>
        </div>
    </body>
    </html>
    ";

    send_email($email, $asunto, $mensaje_email);

    // Enviar por SMS al número del USUARIO
    // FROM: +52 449 210 6893 (tu número emisor)
    // TO: $usuario['telefono'] (número del usuario)
    enviar_codigo_verificacion_sms($usuario['telefono'], $nuevo_codigo, $usuario['nombre_completo']);

    error_log("Código reenviado a: " . $email);

    echo json_encode([
        'success' => true,
        'message' => 'Nuevo código enviado por email y SMS'
    ]);

} catch (PDOException $e) {
    error_log("Error al reenviar código: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor'
    ]);
} catch (Exception $e) {
    error_log("Error general al reenviar código: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado'
    ]);
}
?>
