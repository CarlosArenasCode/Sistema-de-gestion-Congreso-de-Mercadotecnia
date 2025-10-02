<?php

require_once '../php/conexion.php';

if (!isset($_SESSION['id_admin'])) { http_response_code(403); exit('Acceso denegado'); }

header('Content-Type: application/json');

try {
    $stats = [];

    // Contar usuarios registrados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $stats['usuarios_registrados'] = $stmt->fetchColumn();

    // Contar eventos programados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM eventos");
    $stats['eventos_programados'] = $stmt->fetchColumn();
    
    // Contar justificaciones pendientes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM justificaciones WHERE estado = 'PENDIENTE'");
    $stats['justificaciones_pendientes'] = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'stats' => $stats]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener estadísticas.']);
}
?>