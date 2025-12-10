<?php
require_once '../php/conexion.php';
header('Content-Type: application/json');

try {
    // Verificar columnas de la tabla asistencias
    $stmt = $pdo->query("SELECT column_name, data_type FROM user_tab_columns WHERE table_name = 'ASISTENCIAS' ORDER BY column_id");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verificar datos de muestra
    $stmt2 = $pdo->query("SELECT * FROM asistencias WHERE ROWNUM <= 1");
    $sample = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'columns' => $columns,
        'sample_data' => $sample
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
