<?php
/**
 * Script de Prueba Simple - Verificar Tablas Oracle
 */
require_once 'conexion.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 Verificación de Tablas Oracle</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .success { color: green; padding: 10px; background: #d4edda; margin: 5px 0; border-radius: 5px; }
    .error { color: red; padding: 10px; background: #f8d7da; margin: 5px 0; border-radius: 5px; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; background: white; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #007bff; color: white; }
</style>";

try {
    // Listar todas las tablas del usuario
    $stmt = $pdo->query("SELECT table_name FROM user_tables ORDER BY table_name");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<div class='success'>✅ Conexión exitosa a Oracle Database</div>";
    echo "<h2>📊 Tablas encontradas (" . count($tablas) . "):</h2>";
    echo "<table>";
    echo "<tr><th>#</th><th>Nombre de Tabla</th><th>Registros</th><th>Estado</th></tr>";
    
    $contador = 1;
    foreach ($tablas as $tabla) {
        try {
            $countStmt = $pdo->query("SELECT COUNT(*) as total FROM $tabla");
            $count = $countStmt->fetch(PDO::FETCH_ASSOC);
            $registros = $count['total'];
            $estado = "✅ OK";
            $clase = "success";
        } catch (Exception $e) {
            $registros = "Error";
            $estado = "❌ " . $e->getMessage();
            $clase = "error";
        }
        
        echo "<tr>";
        echo "<td>$contador</td>";
        echo "<td><strong>$tabla</strong></td>";
        echo "<td>$registros</td>";
        echo "<td class='$clase'>$estado</td>";
        echo "</tr>";
        $contador++;
    }
    
    echo "</table>";
    
    // Mostrar estructura de tablas principales
    $tablasImportantes = ['USUARIOS', 'EVENTOS', 'ADMINISTRADORES', 'INSCRIPCIONES', 'ASISTENCIA', 'CONSTANCIAS'];
    
    foreach ($tablasImportantes as $tabla) {
        if (in_array($tabla, $tablas)) {
            echo "<h3>📋 Estructura de $tabla:</h3>";
            echo "<table>";
            echo "<tr><th>Columna</th><th>Tipo</th><th>Nullable</th></tr>";
            
            $stmt = $pdo->query("SELECT column_name, data_type, nullable FROM user_tab_columns WHERE table_name = '$tabla' ORDER BY column_id");
            $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($columnas as $col) {
                echo "<tr>";
                echo "<td>" . $col['column_name'] . "</td>";
                echo "<td>" . $col['data_type'] . "</td>";
                echo "<td>" . ($col['nullable'] == 'Y' ? 'Sí' : 'No') . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}
?>
