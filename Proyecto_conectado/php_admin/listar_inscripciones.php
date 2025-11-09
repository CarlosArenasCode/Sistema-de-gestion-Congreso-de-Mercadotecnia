<?php
session_start();
require '../php/conexion.php';

// Verificar que sea administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['tipo'] !== 'admin') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Acceso no autorizado. Se requiere sesión de administrador.'
    ]);
    exit;
}

header('Content-Type: application/json');

// Obtener parámetros opcionales
$id_evento = $_GET['id_evento'] ?? null;
$estado = $_GET['estado'] ?? null;

try {
    // Construir query base
    $sql = "SELECT 
                i.id_inscripcion,
                i.id_usuario,
                i.id_evento,
                i.estado,
                TO_CHAR(i.fecha_inscripcion, 'YYYY-MM-DD HH24:MI:SS') as fecha_inscripcion,
                u.nombre_completo,
                u.matricula,
                u.email,
                u.carrera,
                e.nombre_evento,
                TO_CHAR(e.fecha_inicio, 'YYYY-MM-DD') as fecha_evento,
                (SELECT COUNT(*) FROM asistencias WHERE id_usuario = i.id_usuario AND id_evento = i.id_evento) as tiene_asistencia
            FROM inscripciones i
            JOIN usuarios u ON i.id_usuario = u.id_usuario
            JOIN eventos e ON i.id_evento = e.id_evento
            WHERE 1=1";
    
    $params = [];
    
    // Filtrar por evento si se especifica
    if ($id_evento) {
        $sql .= " AND i.id_evento = :id_evento";
        $params[':id_evento'] = $id_evento;
    }
    
    // Filtrar por estado si se especifica
    if ($estado) {
        $sql .= " AND i.estado = :estado";
        $params[':estado'] = $estado;
    }
    
    $sql .= " ORDER BY i.fecha_inscripcion DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $inscripciones = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Convertir CLOBs
        foreach (['nombre_completo', 'matricula', 'email', 'carrera', 'nombre_evento', 'estado'] as $field) {
            if (isset($row[$field]) && is_resource($row[$field])) {
                $row[$field] = stream_get_contents($row[$field]);
            }
        }
        
        // Convertir números
        $row['id_inscripcion'] = (int)$row['id_inscripcion'];
        $row['id_usuario'] = (int)$row['id_usuario'];
        $row['id_evento'] = (int)$row['id_evento'];
        $row['tiene_asistencia'] = (int)$row['tiene_asistencia'];
        
        $inscripciones[] = $row;
    }
    
    // Obtener estadísticas
    $sql_stats = "SELECT 
                      COUNT(*) as total,
                      SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activas,
                      SUM(CASE WHEN estado = 'asistido' THEN 1 ELSE 0 END) as asistidas,
                      SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as canceladas
                  FROM inscripciones";
    
    if ($id_evento) {
        $sql_stats .= " WHERE id_evento = :id_evento";
    }
    
    $stmt_stats = $pdo->prepare($sql_stats);
    if ($id_evento) {
        $stmt_stats->execute([':id_evento' => $id_evento]);
    } else {
        $stmt_stats->execute();
    }
    
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'total' => count($inscripciones),
        'estadisticas' => [
            'total' => (int)$stats['total'],
            'activas' => (int)$stats['activas'],
            'asistidas' => (int)$stats['asistidas'],
            'canceladas' => (int)$stats['canceladas']
        ],
        'inscripciones' => $inscripciones
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener inscripciones: ' . $e->getMessage()
    ]);
}
?>
