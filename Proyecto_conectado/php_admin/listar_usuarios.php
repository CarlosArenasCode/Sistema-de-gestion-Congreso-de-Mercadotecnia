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

try {
    // Obtener todos los usuarios con información adicional
    $sql = "SELECT 
                u.id_usuario,
                u.nombre_completo,
                u.email,
                u.telefono,
                u.matricula,
                u.semestre,
                u.verificado,
                TO_CHAR(u.fecha_registro, 'YYYY-MM-DD HH24:MI:SS') as fecha_registro,
                (SELECT COUNT(*) FROM inscripciones WHERE id_usuario = u.id_usuario) as total_inscripciones,
                (SELECT COUNT(*) FROM asistencias WHERE id_usuario = u.id_usuario) as total_asistencias
            FROM usuarios u
            ORDER BY u.fecha_registro DESC";
    
    $stmt = $pdo->query($sql);
    $usuarios = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Convertir CLOBs a strings si existen
        foreach (['nombre_completo', 'email', 'telefono', 'matricula'] as $field) {
            if (isset($row[$field]) && is_resource($row[$field])) {
                $row[$field] = stream_get_contents($row[$field]);
            }
        }
        
        // Convertir números a enteros
        $row['id_usuario'] = (int)$row['id_usuario'];
        $row['semestre'] = isset($row['semestre']) ? (int)$row['semestre'] : null;
        $row['verificado'] = (int)$row['verificado'];
        $row['total_inscripciones'] = (int)$row['total_inscripciones'];
        $row['total_asistencias'] = (int)$row['total_asistencias'];
        
        $usuarios[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'total' => count($usuarios),
        'usuarios' => $usuarios
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener usuarios: ' . $e->getMessage()
    ]);
}
?>
