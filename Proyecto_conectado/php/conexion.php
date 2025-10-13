<?php
// php/conexion.php
// Archivo de configuración para conexión a base de datos en Docker

// Configuración para Docker (usa el nombre del servicio 'db' como host)
$host = 'db'; // Nombre del servicio de MySQL en docker-compose.yml
$db   = getenv('MYSQL_DATABASE') ?: 'congreso_db';
$user = getenv('MYSQL_USER') ?: 'congreso_user';
$pass = getenv('MYSQL_PASSWORD') ?: 'congreso_pass';
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

// Iniciar sesión en todos los scripts que lo necesiten
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>