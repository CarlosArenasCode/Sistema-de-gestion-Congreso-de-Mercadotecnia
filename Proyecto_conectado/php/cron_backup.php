<?php
// cron_backup.php
// Script para generar una Copia de Seguridad de la tabla usuarios
require_once __DIR__ . '/conexion.php';

// Carpetas para guardar los respaldos y logs
$backupDir = __DIR__ . '/backups';
$logFile = __DIR__ . '/logs/cron_activity.log';

// Asegurar que existan las carpetas
if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);
if (!is_dir(dirname($logFile))) mkdir(dirname($logFile), 0777, true);

try {
    // 1. Obtener todos los datos de usuarios
    $sql = "SELECT * FROM usuarios";
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Preparar el archivo de respaldo (formato JSON)
    $fecha = date('Y-m-d_H-i-s');
    $archivoSalida = $backupDir . '/respaldo_usuarios_' . $fecha . '.json';
    
    // Guardar los datos en el archivo
    $datosJson = json_encode($usuarios, JSON_PRETTY_PRINT);     
    file_put_contents($archivoSalida, $datosJson);

    // 3. Registrar en el LOG   
    $count = count($usuarios);
    $mensaje = "[$fecha] BACKUP ÉXITO: Se guardaron $count usuarios en $archivoSalida\n";
    
    file_put_contents($logFile, $mensaje, FILE_APPEND);
    echo $mensaje;

} catch (Exception $e) {
    $error = date('Y-m-d H:i:s') . " ERROR BACKUP: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $error, FILE_APPEND);
    echo $error;
}
?>