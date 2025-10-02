<?php
// php/ver_evento.php
require_once 'conexion.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401); 
    echo json_encode(['error' => 'Usuario no autenticado.']);
    exit;
}
$id_usuario = $_SESSION['id_usuario'];

try {
    
    $sql = "SELECT
                e.*,
                IF(i.id_usuario IS NOT NULL AND i.estado = 'Inscrito', 1, 0) AS is_inscrito
            FROM
                eventos e
            LEFT JOIN
                inscripciones i ON e.id_evento = i.id_evento AND i.id_usuario = :id_usuario
            ORDER BY
                e.fecha_inicio, e.hora_inicio";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($eventos);

} catch (PDOException $e) {
    http_response_code(500); 
    // error_log("Error fetching events: " . $e->getMessage());
    echo json_encode(['error' => 'Error al obtener los eventos: ' . $e->getMessage()]);
}
?>