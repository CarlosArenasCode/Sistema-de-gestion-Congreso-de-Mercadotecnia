<?php
// php/ver_justificaciones.php
require_once 'conexion.php'; // Incluye la conexión y session_start()

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Usuario no autenticado.']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

try {
    // Consulta para obtener las justificaciones del usuario
    $sql = "SELECT 
                j.id_justificacion, 
                e.nombre_evento, 
                j.fecha_falta, 
                j.motivo, 
                j.archivo_adjunto_ruta, 
                j.estado, 
                j.fecha_solicitud, 
                j.fecha_revision
            FROM 
                justificaciones j
            INNER JOIN 
                eventos e ON j.id_evento = e.id_evento
            WHERE 
                j.id_usuario = :id_usuario
            ORDER BY 
                j.fecha_solicitud DESC"; // Ordenar por fecha de solicitud, descendente

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    
    $justificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Oracle: Convertir CLOBs a strings antes de json_encode
    foreach ($justificaciones as &$just) {
        if (isset($just['motivo']) && is_resource($just['motivo'])) {
            $just['motivo'] = stream_get_contents($just['motivo']);
        }
    }
    unset($just);

    if ($justificaciones) {
        echo json_encode($justificaciones);
    } else {
        echo json_encode(['message' => 'No hay justificaciones registradas.']);
    }

} catch (PDOException $e) {
    http_response_code(500); 
    echo json_encode(['error' => 'Error al obtener las justificaciones: ' . $e->getMessage()]);
}
?>
