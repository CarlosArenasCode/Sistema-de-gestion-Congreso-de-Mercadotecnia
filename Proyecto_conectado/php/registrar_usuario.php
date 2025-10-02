<?php
// registrar_usuario.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'conexion.php';

// Datos recibidos del formulario
$nombre_completo = $_POST['nombre_completo'] ?? '';
$email = $_POST['email'] ?? '';
$matricula = $_POST['matricula'] ?? '';
$semestre = $_POST['Semestre'] ?? '';
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$rol = $_POST['rol'] ?? 'alumno'; 

// Validación básica (semestre no es obligatorio si es profesor)
if (empty($nombre_completo) || empty($email) || empty($matricula) || empty($password)) {
    echo "Error: Todos los campos son obligatorios (excepto semestre para profesores).";
    exit;
}

if ($rol === 'alumno' && empty($semestre)) {
    echo "Error: El semestre es obligatorio para los alumnos.";
    exit;
}

if ($password !== $password_confirm) {
    echo "Error: Las contraseñas no coinciden.";
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

function guidv4($data = null) {
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
$qr_code_data = guidv4();

try {
    
    $sql = "INSERT INTO usuarios (nombre_completo, email, password_hash, matricula, semestre, rol, qr_code_data)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);


    $stmt->execute([
        $nombre_completo,
        $email,
        $password_hash,
        $matricula,
        ($rol === 'alumno' ? $semestre : null), // Guardar semestre solo si es alumno
        $rol,
        $qr_code_data
    ]);

     header("Location: ../Front-end/login.html?registro=exitoso");
     exit;

} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
         echo "Error: El email o la matrícula ya están registrados.";
    } else {
         echo "Error al registrar el usuario: " . $e->getMessage();
    }
}
?>