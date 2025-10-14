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
        // Verificar si la cuenta está verificada
        if ($usuario['verificado'] == 0) {
            // Cuenta no verificada, redirigir a página de verificación
            echo "<script>
                alert('Tu cuenta aún no está verificada. Revisa tu email y SMS para obtener el código de verificación.');
                window.location.href = '../Front-end/verificar_codigo.html?email=" . urlencode($usuario['email']) . "';
            </script>";
            exit;
        }

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
