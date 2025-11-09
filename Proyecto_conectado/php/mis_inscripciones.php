<?php
/**
 * php/mis_inscripciones.php
 * Obtiene las inscripciones del usuario autenticado
 */

header('Content-Type: application/json');
require_once 'conexion.php';

try {
    // Verificar autenticación
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Usuario no autenticado'
        ]);
        exit;
    }
    
    $id_usuario = $_SESSION['usuario_id'];
    
    // Consulta para obtener inscripciones
    $sql = "SELECT 
                i.id_inscripcion,
                i.id_evento,
                i.id_usuario,
                i.fecha_inscripcion,
                i.estado,
                e.nombre_evento,
                e.descripcion,
                e.fecha_inicio,
                e.hora_inicio,
                e.lugar,
                e.ponente,
                TO_CHAR(i.fecha_inscripcion, 'YYYY-MM-DD HH24:MI:SS') as fecha_inscripcion_format,
                TO_CHAR(e.fecha_inicio, 'YYYY-MM-DD') as fecha_evento_format
            FROM inscripciones i
            JOIN eventos e ON i.id_evento = e.id_evento
            WHERE i.id_usuario = :id_usuario
            ORDER BY i.fecha_inscripcion DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir CLOBs
    foreach ($inscripciones as &$insc) {
        if (isset($insc['descripcion']) && is_resource($insc['descripcion'])) {
            $insc['descripcion'] = stream_get_contents($insc['descripcion']);
        }
    }
    unset($insc);
    
    echo json_encode([
        'success' => true,
        'inscripciones' => $inscripciones,
        'total' => count($inscripciones)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener inscripciones',
        'message' => $e->getMessage()
    ]);
}
?>
