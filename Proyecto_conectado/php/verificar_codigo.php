<?php
/**
 * verificar_codigo.php
 * Procesa la verificación del código de 6 dígitos
 */

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

require 'conexion.php';

try {
    // Obtener datos del formulario
    $email = $_POST['email'] ?? '';
    $codigo = '';
    
    // Concatenar los 6 dígitos
    for ($i = 1; $i <= 6; $i++) {
        $codigo .= $_POST['digit' . $i] ?? '';
    }

    // Log para debugging
    error_log("Verificación - Email recibido: " . ($email ?: 'VACÍO'));
    error_log("Verificación - Código recibido: " . ($codigo ?: 'VACÍO'));
    error_log("Verificación - POST data: " . json_encode($_POST));

    // Validaciones básicas
    if (empty($email) || empty($codigo)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email y código son requeridos',
            'debug' => [
                'email_received' => !empty($email),
                'codigo_received' => !empty($codigo),
                'codigo_length' => strlen($codigo)
            ]
        ]);
        exit;
    }

    if (strlen($codigo) !== 6 || !ctype_digit($codigo)) {
        echo json_encode([
            'success' => false,
            'message' => 'El código debe tener 6 dígitos numéricos'
        ]);
        exit;
    }

    // Buscar usuario por email
    $sql = "SELECT id_usuario, codigo_verificacion, fecha_codigo, verificado, intentos_verificacion, nombre_completo 
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

    // Verificar si ya está verificado
    if ($usuario['verificado'] == 1) {
        echo json_encode([
            'success' => false,
            'message' => 'Esta cuenta ya está verificada. Puedes iniciar sesión.'
        ]);
        exit;
    }

    // Verificar límite de intentos
    if ($usuario['intentos_verificacion'] >= 5) {
        echo json_encode([
            'success' => false,
            'message' => 'Has superado el límite de intentos. Solicita un nuevo código.'
        ]);
        exit;
    }

    // Verificar si el código ha expirado (15 minutos)
    $fecha_codigo = new DateTime($usuario['fecha_codigo']);
    $ahora = new DateTime();
    $diferencia = $ahora->diff($fecha_codigo);
    $minutos = ($diferencia->days * 24 * 60) + ($diferencia->h * 60) + $diferencia->i;

    if ($minutos > 15) {
        echo json_encode([
            'success' => false,
            'message' => 'El código ha expirado. Solicita uno nuevo.'
        ]);
        exit;
    }

    // Verificar si el código es correcto
    if ($codigo !== $usuario['codigo_verificacion']) {
        // Incrementar intentos fallidos
        $sql_update = "UPDATE usuarios SET intentos_verificacion = intentos_verificacion + 1 WHERE id_usuario = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$usuario['id_usuario']]);

        $intentos_restantes = 5 - ($usuario['intentos_verificacion'] + 1);
        
        echo json_encode([
            'success' => false,
            'message' => "Código incorrecto. Te quedan {$intentos_restantes} intentos."
        ]);
        exit;
    }

    // CÓDIGO CORRECTO - Activar cuenta
    $sql_verificar = "UPDATE usuarios 
                      SET verificado = 1, 
                          codigo_verificacion = NULL, 
                          fecha_codigo = NULL,
                          intentos_verificacion = 0
                      WHERE id_usuario = ?";
    $stmt_verificar = $pdo->prepare($sql_verificar);
    $stmt_verificar->execute([$usuario['id_usuario']]);

    // Log de éxito
    error_log("Usuario verificado exitosamente: " . $usuario['nombre_completo'] . " (" . $email . ")");

    echo json_encode([
        'success' => true,
        'message' => '¡Cuenta verificada exitosamente! Redirigiendo al login...'
    ]);

} catch (PDOException $e) {
    error_log("Error en verificación: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor. Inténtalo de nuevo.'
    ]);
} catch (Exception $e) {
    error_log("Error general en verificación: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado. Inténtalo de nuevo.'
    ]);
}
?>
