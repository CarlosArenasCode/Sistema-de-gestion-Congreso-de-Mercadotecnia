<?php
// Debug de sesión simple
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');

session_start();

header('Content-Type: text/html; charset=utf-8');

echo "<h1>DEBUG DE SESIÓN PHP</h1>";
echo "<hr>";

echo "<h2>Estado de la Sesión:</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVA' : 'INACTIVA') . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";
echo "<br>";

echo "<h2>Datos en \$_SESSION:</h2>";
if (empty($_SESSION)) {
    echo "<strong>⚠️ LA SESIÓN ESTÁ VACÍA</strong><br>";
} else {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}
echo "<br>";

echo "<h2>Cookies Recibidas:</h2>";
if (empty($_COOKIE)) {
    echo "<strong>⚠️ NO HAY COOKIES</strong><br>";
} else {
    echo "<pre>";
    print_r($_COOKIE);
    echo "</pre>";
}
echo "<br>";

echo "<h2>Headers de Cookie:</h2>";
echo "<pre>";
foreach (headers_list() as $header) {
    if (stripos($header, 'Set-Cookie') !== false) {
        echo $header . "\n";
    }
}
echo "</pre>";
echo "<br>";

echo "<hr>";
echo "<h3>Opciones:</h3>";
echo "<a href='?test=set'>1. Establecer sesión de prueba</a> | ";
echo "<a href='?test=clear'>2. Limpiar sesión</a> | ";
echo "<a href='?test=refresh'>3. Refrescar</a>";
echo "<br><br>";

if (isset($_GET['test'])) {
    if ($_GET['test'] === 'set') {
        $_SESSION['test_user'] = 'Usuario de Prueba';
        $_SESSION['test_time'] = time();
        $_SESSION['usuario_id'] = 999;
        $_SESSION['nombre'] = 'Test User';
        $_SESSION['rol'] = 'alumno';
        echo "<strong style='color: green;'>✅ Sesión de prueba establecida</strong><br>";
        echo "<a href='debug_session.php'>Ver sesión</a>";
    } elseif ($_GET['test'] === 'clear') {
        session_unset();
        session_destroy();
        echo "<strong style='color: red;'>🗑️ Sesión limpiada</strong><br>";
        echo "<a href='debug_session.php'>Ver sesión</a>";
    }
}
?>
