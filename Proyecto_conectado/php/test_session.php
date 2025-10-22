<?php
/**
 * Test de sesión - Para diagnosticar problemas de sesión
 */
session_start();

header('Content-Type: application/json');

$response = [
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'session_save_path' => session_save_path(),
    'session_cookie_params' => session_get_cookie_params(),
    'headers_sent' => headers_sent(),
    'php_session_active' => session_status() === PHP_SESSION_ACTIVE
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
