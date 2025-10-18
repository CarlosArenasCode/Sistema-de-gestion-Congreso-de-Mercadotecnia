<?php
/**
 * Verificador de Sesión
 * Endpoint para verificar si el usuario tiene una sesión activa válida
 */

session_start();

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Función para enviar respuesta JSON
function sendResponse($loggedIn, $data = []) {
    $response = array_merge([
        'loggedIn' => $loggedIn,
        'timestamp' => date('Y-m-d H:i:s')
    ], $data);
    
    echo json_encode($response);
    exit;
}

// Verificar si existe una sesión activa
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    sendResponse(false, [
        'message' => 'No hay sesión activa'
    ]);
}

// Verificar si la sesión ha expirado
$sessionTimeout = 3600; // 1 hora en segundos
if (isset($_SESSION['last_activity'])) {
    $inactive = time() - $_SESSION['last_activity'];
    
    if ($inactive > $sessionTimeout) {
        // Sesión expirada
        session_unset();
        session_destroy();
        
        sendResponse(false, [
            'message' => 'Sesión expirada',
            'expired' => true
        ]);
    }
}

// Actualizar timestamp de última actividad
$_SESSION['last_activity'] = time();

// Sesión válida - enviar datos del usuario
$userData = [
    'id' => $_SESSION['usuario_id'],
    'nombre' => $_SESSION['nombre'] ?? '',
    'email' => $_SESSION['email'] ?? '',
    'rol' => $_SESSION['rol'] ?? $_SESSION['tipo'] ?? 'alumno',
    'matricula' => $_SESSION['matricula'] ?? null,
    'verificado' => $_SESSION['verificado'] ?? false
];

sendResponse(true, [
    'message' => 'Sesión activa',
    'user' => $userData,
    'rol' => $userData['rol'],
    'tipo' => $userData['rol']
]);
?>
