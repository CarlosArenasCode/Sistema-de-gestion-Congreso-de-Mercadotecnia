<?php

require_once '../php/conexion.php';
require_once '../php/oracle_helpers.php';

// Comentado temporalmente para pruebas
// if (!isset($_SESSION['id_admin'])) { 
//     http_response_code(403); 
//     exit('Acceso denegado'); 
// }

header('Content-Type: application/json');

try {
    $stats = [];

    // Contar usuarios registrados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $stats['usuarios_registrados'] = (int)$stmt->fetchColumn();

    // Contar eventos programados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM eventos");
    $stats['eventos_programados'] = (int)$stmt->fetchColumn();
    
    // Contar justificaciones pendientes
    // En Oracle, los valores de enumeración se almacenan en VARCHAR2
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM justificaciones WHERE estado = 'PENDIENTE'");
    $stats['justificaciones_pendientes'] = (int)$stmt->fetchColumn();

    // Obtener nombre del admin (si está en sesión)
    $admin_nombre = 'Administrador';
    if (isset($_SESSION['id_admin'])) {
        $stmt = $pdo->prepare("SELECT nombre_completo FROM administradores WHERE id_admin = :id");
        $stmt->execute([':id' => $_SESSION['id_admin']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            $admin_nombre = $admin['nombre_completo'];
        }
    }

    echo json_encode(['success' => true, 'stats' => $stats, 'admin_nombre' => $admin_nombre]);

} catch (PDOException $e) {
    http_response_code(500);
    // Log del error para debugging (opcional)
    error_log("Error en dashboard_controller.oracle.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al obtener estadísticas.']);
}
?>
