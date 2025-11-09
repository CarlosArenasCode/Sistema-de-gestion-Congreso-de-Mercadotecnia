<?php
session_start();
require 'conexion.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo "Por favor completa todos los campos.";
    exit;
}

try {
    // Buscar administrador por email
    $consulta = $pdo->prepare("SELECT * FROM administradores WHERE email = ?");
    $consulta->execute([$email]);
    $admin = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password_hash'])) {
        // Inicio de sesión exitoso
        $_SESSION['id_admin'] = $admin['id_admin'];
        $_SESSION['admin_nombre'] = $admin['nombre_completo'];
        $_SESSION['admin_rol'] = $admin['rol'];
        
        header("Location: ../Front-end/admin_dashboard.html");
        exit;
    } else {
        echo "Email o contraseña incorrectos.";
    }

} catch (PDOException $e) {
    echo "Error al iniciar sesión: " . $e->getMessage();
}
?>
