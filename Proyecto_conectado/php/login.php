<?php
session_start();  // Esto debe estar al principio de todo el código
require 'conexion.php';

$matricula = $_POST['university_id'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($matricula) || empty($password)) {
    echo "Por favor completa todos los campos.";
    exit;
}

try {
    // Buscar usuario por matrícula
    $consulta = $pdo->prepare("SELECT * FROM usuarios WHERE matricula = ?");
    $consulta->execute([$matricula]);
    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        // Inicio de sesión exitoso
        $_SESSION['id_usuario'] = $usuario['id_usuario']; // Guarda el ID de usuario en la sesión
        $_SESSION['usuario'] = $usuario['nombre_completo']; // O puedes almacenar otro dato, como el nombre

        header("Location: ../Front-end/dashboard_alumno.html");
        exit;
    } else {
        echo "Matrícula o contraseña incorrectos.";
    }

} catch (PDOException $e) {
    echo "Error al iniciar sesión: " . $e->getMessage();
}
