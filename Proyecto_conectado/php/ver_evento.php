<?php
// php/ver_evento.php
session_start(); // Iniciar sesión antes de requerir conexion
require_once 'conexion.php'; 

// Limpiar cualquier output buffer previo
if (ob_get_level()) ob_clean();

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401); 
    echo json_encode(['error' => 'Usuario no autenticado.']);
    exit;
}
$id_usuario = $_SESSION['id_usuario'];

try {
    // Oracle: Usar CASE en lugar de IF, y convertir booleano a número
    $sql = "SELECT
                e.*,
                CASE WHEN i.id_usuario IS NOT NULL AND i.estado = 'Inscrito' THEN 1 ELSE 0 END AS is_inscrito
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

    // Oracle: Convertir CLOBs a strings antes de json_encode
    foreach ($eventos as &$evento) {
        if (isset($evento['descripcion']) && is_resource($evento['descripcion'])) {
            $evento['descripcion'] = stream_get_contents($evento['descripcion']);
        }
    }
    unset($evento); // Romper referencia

    echo json_encode($eventos);

} catch (PDOException $e) {
    http_response_code(500); 
    // error_log("Error fetching events: " . $e->getMessage());
    echo json_encode(['error' => 'Error al obtener los eventos: ' . $e->getMessage()]);
}
?>