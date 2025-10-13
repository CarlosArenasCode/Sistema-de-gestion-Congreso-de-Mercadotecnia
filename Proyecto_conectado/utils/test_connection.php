<?php
// test_connection.php - Script para probar la conexión a la base de datos
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Prueba de Conexión a Base de Datos</h1>";

try {
    require '../php/conexion.php';
    echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos!</p>";
    
    // Probar una consulta simple
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Consulta de prueba exitosa: " . $result['test'] . "</p>";
    
    // Verificar que existe la tabla usuarios
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    $table_exists = $stmt->fetch();
    
    if ($table_exists) {
        echo "<p style='color: green;'>✅ La tabla 'usuarios' existe en la base de datos</p>";
        
        // Contar registros en la tabla usuarios
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios");
        $count = $stmt->fetch();
        echo "<p style='color: blue;'>ℹ️ Número de usuarios registrados: " . $count['count'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ La tabla 'usuarios' NO existe en la base de datos</p>";
        echo "<p style='color: orange;'>⚠️ Puede que la base de datos no se haya inicializado correctamente</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p style='color: orange;'>Código de error: " . $e->getCode() . "</p>";
}
?>