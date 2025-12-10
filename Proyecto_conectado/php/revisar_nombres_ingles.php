<?php
/**
 * Script para revisar nombres de tablas y columnas en inglés
 */
require_once 'conexion.php';

header('Content-Type: application/json');

try {
    $resultado = [
        'tablas' => [],
        'resumen' => []
    ];
    
    // Obtener todas las tablas
    $stmt = $pdo->query("SELECT table_name FROM user_tables ORDER BY table_name");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tablas as $tabla) {
        // Obtener columnas de cada tabla
        $stmt = $pdo->query("SELECT column_name, data_type FROM user_tab_columns WHERE table_name = '$tabla' ORDER BY column_id");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $columnas_ingles = [];
        
        foreach ($columnas as $col) {
            $nombre = $col['column_name'];
            
            // Detectar nombres en inglés (palabras comunes en inglés en bases de datos)
            $palabras_ingles = [
                'PASSWORD', 'HASH', 'EMAIL', 'TOKEN', 'SELECTOR', 
                'TIMESTAMP', 'RESET', 'ADMIN', 'USER', 'ROLE',
                'STAFF', 'SUPERADMIN', 'VERIFIED', 'CODE', 'DATE',
                'TIME', 'PHONE', 'QR', 'STATUS', 'TYPE', 'METHOD',
                'SCAN', 'MANUAL', 'COMPLETE', 'INCOMPLETE', 'PENDING',
                'APPROVED', 'REJECTED', 'SERIAL', 'NUMBER', 'FILE',
                'PATH', 'PDF', 'ATTACHMENT', 'REVIEW', 'REVIEWER'
            ];
            
            $es_ingles = false;
            foreach ($palabras_ingles as $palabra) {
                if (strpos($nombre, $palabra) !== false) {
                    $es_ingles = true;
                    break;
                }
            }
            
            if ($es_ingles) {
                $columnas_ingles[] = [
                    'nombre' => $nombre,
                    'tipo' => $col['data_type']
                ];
            }
        }
        
        if (!empty($columnas_ingles)) {
            $resultado['tablas'][$tabla] = $columnas_ingles;
        }
    }
    
    // Generar resumen
    foreach ($resultado['tablas'] as $tabla => $columnas) {
        $resultado['resumen'][] = [
            'tabla' => $tabla,
            'total_columnas_ingles' => count($columnas)
        ];
    }
    
    echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al consultar base de datos',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
