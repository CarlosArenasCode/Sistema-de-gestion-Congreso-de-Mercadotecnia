<?php
include 'conexion.php';

$usuario_id = $_POST['usuario_id'];
$fecha = date('Y-m-d');
$hora = date('H:i:s');

$sql = "INSERT INTO asistencias (usuario_id, fecha, hora) VALUES ('$usuario_id', '$fecha', '$hora')";
if ($conn->query($sql) === TRUE) {
    echo "Asistencia registrada";
} else {
    echo "Error: " . $conn->error;
}
?>
