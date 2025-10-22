<?php
// Configuración de sesión mejorada
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

session_start();

header('Content-Type: application/json');

// Verificar si hay sesión activa con los datos correctos
if (isset($_SESSION['usuario_id']) && isset($_SESSION['nombre'])) {
    echo json_encode([
        'nombre' => $_SESSION['nombre'],
        'id' => $_SESSION['usuario_id'],
        'email' => $_SESSION['email'] ?? null,
        'matricula' => $_SESSION['matricula'] ?? null,
        'rol' => $_SESSION['rol'] ?? 'alumno'
    ]);
} else {
    echo json_encode(['nombre' => null]);
}
