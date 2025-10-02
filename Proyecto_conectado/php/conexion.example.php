<?php
// php/conexion.example.php
// Archivo de ejemplo para configuración de base de datos
// Copia este archivo como 'conexion.php' y actualiza con tus credenciales reales

$host = '127.0.0.1'; // o 'localhost'
$db   = 'congreso_db';
$user = 'tu_usuario_de_bd'; // Tu usuario de BD
$pass = 'tu_contraseña_de_bd';     // Tu contraseña de BD
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
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>