<?php
// Proyecto_conectado/php/eventos_inscrito.php

// 1. Incluir conexión 
require_once 'conexion.php'; // $pdo estará disponible aquí

// 2. Establecer cabecera de respuesta
header('Content-Type: application/json');

// 3. Verificar autenticación (usando la sesión iniciada en conexion.php)
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Usuario no autenticado.']);
    exit;
}

// 4. Obtener ID de usuario
$id_usuario = $_SESSION['usuario_id'];

// 5. Preparar la consulta SQL
// Seleccionamos solo los eventos donde el usuario tiene una inscripción activa.
$sql = "SELECT e.nombre_evento, e.descripcion, e.fecha_inicio, e.hora_inicio, e.lugar, e.ponente
        FROM eventos e
        JOIN inscripciones i ON e.id_evento = i.id_evento
        WHERE i.id_usuario = :id_usuario  
          AND i.estado = 'Inscrito'
        ORDER BY e.fecha_inicio, e.hora_inicio";

// 6. Ejecutar la consulta usando PDO
try {
    // Preparar la sentencia
    $stmt = $pdo->prepare($sql);

    // Vincular el parámetro (más seguro)
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

    // Ejecutar la sentencia
    $stmt->execute();

    // Obtener todos los resultados
    $eventos = $stmt->fetchAll(); // Usa fetchAll() ya que esperamos múltiples filas

    // 7. Devolver los resultados
    echo json_encode($eventos);

} catch (\PDOException $e) {
    // Manejar errores de la base de datos
    http_response_code(500); // Internal Server Error
    // En producción, es mejor registrar este error que mostrarlo.
    echo json_encode(['error' => 'Error al consultar los eventos: ' . $e->getMessage()]);
}

?>