<?php
/**
 * ejecutar_generacion_constancias.php
 * 
 * Endpoint para ejecutar manualmente la generación automática de constancias
 * También puede ser llamado desde navegador para pruebas
 */

// Solo permitir acceso desde admin (opcional - comentar para desarrollo)
// session_start();
// if (!isset($_SESSION['id_admin'])) {
//     http_response_code(403);
//     exit(json_encode(['success' => false, 'message' => 'Acceso denegado']));
// }

// Incluir el script de generación automática
require_once __DIR__ . '/generar_constancias_automaticas.php';

// El script ya se ejecutó al hacer require
// Solo retornar respuesta HTML o JSON según el contexto

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($is_ajax) {
    // Respuesta JSON para llamadas AJAX
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Generación de constancias ejecutada',
        'log_file' => 'logs/constancias_auto_' . date('Y-m-d') . '.log'
    ]);
} else {
    // Respuesta HTML para navegador
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Generación de Constancias Automática</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 900px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 {
                color: #2c3e50;
                border-bottom: 3px solid #3498db;
                padding-bottom: 10px;
            }
            .success {
                background: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .log {
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                padding: 15px;
                border-radius: 5px;
                max-height: 500px;
                overflow-y: auto;
                font-family: 'Courier New', monospace;
                font-size: 12px;
                white-space: pre-wrap;
            }
            .button {
                display: inline-block;
                padding: 10px 20px;
                background: #3498db;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
            .button:hover {
                background: #2980b9;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>✓ Generación de Constancias Ejecutada</h1>
            
            <div class="success">
                El proceso de generación automática de constancias se ha ejecutado correctamente.
            </div>
            
            <h2>Log de Ejecución</h2>
            <div class="log">
<?php
    $log_file = __DIR__ . '/../logs/constancias_auto_' . date('Y-m-d') . '.log';
    if (file_exists($log_file)) {
        echo htmlspecialchars(file_get_contents($log_file));
    } else {
        echo "No se encontró archivo de log.";
    }
?>
            </div>
            
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="button">Ejecutar Nuevamente</a>
            <a href="../Front-end/admin_constancias.html" class="button">Ver Constancias</a>
        </div>
    </body>
    </html>
    <?php
}
?>
