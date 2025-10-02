<?php
// php/conexion.php

$host = '127.0.0.1'; // o 'localhost'
$db   = 'congreso_db';
$user = 'root'; // Tu usuario de BD
$pass = '';     // Tu contraseña de BD
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // En producción, loguea el error en lugar de mostrarlo
     // error_log($e->getMessage());
     // exit('Error de conexión a la base de datos.');
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Iniciar sesión en todos los scripts que lo necesiten
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>