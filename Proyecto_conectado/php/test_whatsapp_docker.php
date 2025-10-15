<?php
/**
 * Dashboard de Prueba - Servicio WhatsApp Docker
 * Verifica la conexión y funcionalidad del servicio de WhatsApp
 */

require_once 'whatsapp_client.php';

header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba Servicio WhatsApp Docker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #E4007C 0%, #c70067 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 30px;
        }
        
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #E4007C;
        }
        
        .section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .status {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .status.ready {
            background: #d4edda;
            color: #155724;
        }
        
        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status.initializing {
            background: #fff3cd;
            color: #856404;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
        }
        
        .info-value {
            color: #333;
        }
        
        .test-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #E4007C;
        }
        
        .btn {
            background: linear-gradient(135deg, #E4007C 0%, #c70067 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        
        pre {
            background: #2d2d2d;
            color: #f8f8f8;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🐳 Servicio WhatsApp Docker</h1>
            <p>Panel de verificación y pruebas</p>
        </div>
        
        <div class="content">
            <!-- Estado del Servicio -->
            <div class="section">
                <h2>📊 Estado del Servicio</h2>
                <?php
                $client = new WhatsAppClient('http://whatsapp:3001');
                $health = $client->checkHealth();
                
                $statusClass = 'error';
                $statusText = 'Error';
                
                if (isset($health['status'])) {
                    $statusClass = $health['status'];
                    $statusText = ucfirst($health['status']);
                }
                ?>
                
                <div class="info-row">
                    <span class="info-label">Estado:</span>
                    <span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                </div>
                
                <?php if (isset($health['phoneNumber'])): ?>
                <div class="info-row">
                    <span class="info-label">Número Emisor:</span>
                    <span class="info-value">+<?php echo $health['phoneNumber']; ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (isset($health['timestamp'])): ?>
                <div class="info-row">
                    <span class="info-label">Última Verificación:</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($health['timestamp'])); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (isset($health['error'])): ?>
                <div class="alert error">
                    <strong>⚠️ Error:</strong> <?php echo htmlspecialchars($health['error']); ?>
                </div>
                <?php endif; ?>
                
                <details style="margin-top: 15px;">
                    <summary style="cursor: pointer; color: #666; font-size: 14px;">Ver respuesta completa</summary>
                    <pre><?php echo json_encode($health, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                </details>
            </div>
            
            <!-- Formulario de Prueba -->
            <?php if (isset($health['status']) && $health['status'] === 'ready'): ?>
            <div class="section">
                <h2>📱 Enviar Mensaje de Prueba</h2>
                <p style="color: #666; margin-bottom: 15px;">
                    Envía un mensaje de prueba a tu número de WhatsApp.
                </p>
                
                <form method="POST" class="test-form">
                    <div class="form-group">
                        <label for="test_phone">Número de Teléfono:</label>
                        <input 
                            type="tel" 
                            id="test_phone" 
                            name="test_phone" 
                            placeholder="+52 449 123 4567"
                            pattern="[\+0-9\s\-\(\)]+"
                            required
                        >
                        <small style="color: #666; font-size: 12px;">
                            Formatos: +524491234567, 4491234567, (449) 123-4567
                        </small>
                    </div>
                    
                    <button type="submit" name="send_test" class="btn">
                        Enviar Mensaje de Prueba
                    </button>
                </form>
                
                <?php
                if (isset($_POST['send_test']) && !empty($_POST['test_phone'])) {
                    $testPhone = $_POST['test_phone'];
                    $result = $client->sendTest($testPhone);
                    
                    if (isset($result['success']) && $result['success']) {
                        echo '<div class="alert success">';
                        echo '<strong>✅ Éxito!</strong> Mensaje enviado a ' . htmlspecialchars($result['phone']) . '<br>';
                        echo 'Código de prueba: <code>' . $result['testCode'] . '</code>';
                        echo '</div>';
                    } else {
                        echo '<div class="alert error">';
                        echo '<strong>❌ Error:</strong> ' . htmlspecialchars($result['error'] ?? 'Error desconocido');
                        echo '</div>';
                    }
                    
                    echo '<details style="margin-top: 15px;">';
                    echo '<summary style="cursor: pointer; color: #666; font-size: 14px;">Ver respuesta completa</summary>';
                    echo '<pre>' . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                    echo '</details>';
                }
                ?>
            </div>
            <?php else: ?>
            <div class="section">
                <div class="alert error">
                    <strong>⚠️ Servicio no disponible</strong><br>
                    El servicio no está listo. Pasos:
                    <ol style="margin-top: 10px; margin-left: 20px;">
                        <li>Verifica Docker: <code>docker ps</code></li>
                        <li>Inicia servicio: <code>docker-compose up -d whatsapp</code></li>
                        <li>Ver logs: <code>docker logs congreso_whatsapp</code></li>
                        <li>Escanea el código QR (primera vez)</li>
                    </ol>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Información de Integración -->
            <div class="section">
                <h2>🔗 Información de Integración</h2>
                
                <div class="info-row">
                    <span class="info-label">URL del Servicio:</span>
                    <span class="info-value"><code>http://whatsapp:3001</code></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Cliente PHP:</span>
                    <span class="info-value"><code>whatsapp_client.php</code></span>
                </div>
                
                <details style="margin-top: 15px;">
                    <summary style="cursor: pointer; color: #666; font-size: 14px;">Ver ejemplo de código</summary>
                    <pre>&lt;?php
require 'whatsapp_client.php';

$client = new WhatsAppClient('http://whatsapp:3001');
$result = $client->sendVerificationCode(
    '+524491234567',
    '123456',
    'Juan Pérez'
);
?&gt;</pre>
                </details>
            </div>
        </div>
    </div>
</body>
</html>
