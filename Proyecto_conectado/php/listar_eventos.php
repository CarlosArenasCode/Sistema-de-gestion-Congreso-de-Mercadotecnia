<?php
/**
 * php/listar_eventos.php
 * Lista todos los eventos disponibles (futuros) para inscripción
 */

header('Content-Type: application/json');
require_once 'conexion.php';

try {
    // Consulta para obtener todos los eventos futuros
    $sql = "SELECT 
                e.id_evento,
                e.nombre_evento,
                e.descripcion,
                e.fecha_inicio,
                e.hora_inicio,
                e.fecha_fin,
                e.hora_fin,
                e.lugar,
                e.ponente,
                e.cupo_maximo,
                e.cupo_actual,
                e.genera_constancia,
                e.tipo_evento,
                e.horas_para_constancia,
                TO_CHAR(e.fecha_inicio, 'YYYY-MM-DD') as fecha_inicio_format,
                TO_CHAR(e.hora_inicio, 'HH24:MI') as hora_inicio_format
            FROM eventos e
            WHERE e.fecha_inicio >= TRUNC(SYSDATE)
            ORDER BY e.fecha_inicio, e.hora_inicio";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir CLOBs a strings
    foreach ($eventos as &$evento) {
        if (isset($evento['descripcion']) && is_resource($evento['descripcion'])) {
            $evento['descripcion'] = stream_get_contents($evento['descripcion']);
        }
        
        // Calcular cupos disponibles
        $evento['cupos_disponibles'] = $evento['cupo_maximo'] - $evento['cupo_actual'];
        $evento['esta_lleno'] = $evento['cupo_actual'] >= $evento['cupo_maximo'];
    }
    unset($evento);
    
    echo json_encode([
        'success' => true,
        'eventos' => $eventos,
        'total' => count($eventos)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener eventos',
        'message' => $e->getMessage()
    ]);
}
?>
