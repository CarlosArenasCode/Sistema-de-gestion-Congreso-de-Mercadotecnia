<?php
require_once '../php/conexion.php';

header('Content-Type: application/json');

$response = [];
$params = [];
$searchTerm = $_GET['search'] ?? null;

$sql = "SELECT
            i.id_inscripcion, u.id_usuario, u.nombre_completo AS nombre_usuario,
            e.id_evento, e.nombre_evento, i.fecha_inscripcion, i.estado
        FROM inscripciones i
        JOIN usuarios u ON i.id_usuario = u.id_usuario
        JOIN eventos e ON i.id_evento = e.id_evento";


if (!empty($searchTerm)) {
    $sql .= " WHERE (
        u.nombre_completo LIKE ? OR 
        e.nombre_evento LIKE ? OR
        u.id_usuario = ?
    )";
    $search_like = '%' . $searchTerm . '%';
 
    $params = [$search_like, $search_like, $searchTerm];
}

$sql .= " ORDER BY i.fecha_inscripcion DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $response['inscripciones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error al obtener inscripciones: " . $e->getMessage());
    $response['error'] = 'Error al obtener datos de inscripciones: ' . $e->getMessage();
    $response['inscripciones'] = [];
}

echo json_encode($response);
?>