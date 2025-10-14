<?php
/**
 * Verificador de Configuración de WhatsApp/SMS
 * Muestra el estado actual de la configuración
 */

require_once 'whatsapp_service.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Configuración - WhatsApp/SMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #E4007C;
            border-bottom: 3px solid #E4007C;
            padding-bottom: 10px;
        }
        .status-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 5px solid #ccc;
        }
        .status-ok {
            background-color: #d4edda;
            border-left-color: #28a745;
        }
        .status-warning {
            background-color: #fff3cd;
            border-left-color: #ffc107;
        }
        .status-error {
            background-color: #f8d7da;
            border-left-color: #dc3545;
        }
        .label {
            font-weight: bold;
            color: #333;
        }
        .value {
            color: #666;
            margin-left: 10px;
        }
        .icon {
            font-size: 20px;
            margin-right: 10px;
        }
        .next-steps {
            background-color: #e7f3ff;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            border-left: 5px solid #0066cc;
        }
        .next-steps h3 {
            color: #0066cc;
            margin-top: 0;
        }
        .next-steps ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin: 8px 0;
        }
        code {
            background-color: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📱 Estado de Configuración WhatsApp/SMS</h1>
        
        <?php
        $config = verificar_configuracion_twilio();
        ?>
        
        <!-- Estado General -->
        <div class="status-item <?php echo $config['configurado'] ? 'status-ok' : 'status-warning'; ?>">
            <span class="icon"><?php echo $config['configurado'] ? '✅' : '⚠️'; ?></span>
            <span class="label">Estado General:</span>
            <span class="value">
                <?php 
                if ($config['configurado']) {
                    echo "✅ Configurado y listo para producción";
                } elseif ($config['modo_desarrollo']) {
                    echo "🔧 Modo Desarrollo - Mensajes en log";
                } else {
                    echo "⚠️ Configuración incompleta";
                }
                ?>
            </span>
        </div>

        <!-- Modo de Operación -->
        <div class="status-item <?php echo $config['modo_desarrollo'] ? 'status-warning' : 'status-ok'; ?>">
            <span class="icon"><?php echo $config['modo_desarrollo'] ? '🔧' : '🚀'; ?></span>
            <span class="label">Modo de Operación:</span>
            <span class="value">
                <?php echo $config['modo_desarrollo'] ? 'DESARROLLO (simulado)' : 'PRODUCCIÓN (envío real)'; ?>
            </span>
        </div>

        <!-- Tipo de Mensaje -->
        <div class="status-item status-ok">
            <span class="icon">📤</span>
            <span class="label">Tipo de Mensaje:</span>
            <span class="value">
                <?php echo $config['usa_whatsapp'] ? 'WhatsApp' : 'SMS'; ?>
            </span>
        </div>

        <!-- Número Emisor -->
        <div class="status-item <?php echo !empty($config['numero_emisor']) ? 'status-ok' : 'status-error'; ?>">
            <span class="icon">📞</span>
            <span class="label">Número Emisor:</span>
            <span class="value">
                <?php echo !empty($config['numero_emisor']) ? $config['numero_emisor'] : 'No configurado'; ?>
            </span>
        </div>

        <!-- Credenciales Twilio -->
        <div class="status-item <?php echo empty($config['errores']) ? 'status-ok' : 'status-error'; ?>">
            <span class="icon"><?php echo empty($config['errores']) ? '🔑' : '❌'; ?></span>
            <span class="label">Credenciales Twilio:</span>
            <span class="value">
                <?php 
                if (empty($config['errores'])) {
                    echo "✅ Configuradas correctamente";
                } else {
                    echo "❌ " . implode(", ", $config['errores']);
                }
                ?>
            </span>
        </div>

        <!-- Archivo de Log -->
        <?php if ($config['modo_desarrollo']): ?>
        <div class="status-item status-ok">
            <span class="icon">📝</span>
            <span class="label">Log de Mensajes:</span>
            <span class="value">
                <code><?php echo __DIR__ . '/sms_log.txt'; ?></code>
            </span>
        </div>
        <?php endif; ?>

        <!-- Próximos Pasos -->
        <div class="next-steps">
            <h3>🎯 Próximos Pasos</h3>
            
            <?php if ($config['modo_desarrollo']): ?>
                <p><strong>Actualmente en modo DESARROLLO</strong></p>
                <p>Los mensajes se guardan en archivo de log, no se envían realmente.</p>
                
                <h4>Para activar envío real por WhatsApp:</h4>
                <ol>
                    <li>Crea cuenta en Twilio: <a href="https://www.twilio.com/try-twilio" target="_blank">https://www.twilio.com/try-twilio</a></li>
                    <li>Obtén tus credenciales (Account SID y Auth Token)</li>
                    <li>Configura WhatsApp Business con tu número <code>+52 449 210 6893</code></li>
                    <li>Crea y aprueba plantilla de mensajes (24-48 horas)</li>
                    <li>Edita <code>verificacion_config.php</code> con tus credenciales</li>
                    <li>Cambia <code>SMS_MODE_DESARROLLO = false</code></li>
                    <li>Reinicia Docker: <code>docker compose restart</code></li>
                </ol>
                
                <h4>📖 Guías disponibles:</h4>
                <ul>
                    <li><code>CONFIGURAR_WHATSAPP_PASO_A_PASO.md</code> - Guía completa</li>
                    <li><code>GUIA_CONFIGURAR_WHATSAPP.md</code> - Referencia rápida</li>
                </ul>
                
                <h4>🧪 Para probar con WhatsApp Sandbox (rápido):</h4>
                <ol>
                    <li>Ve a: <a href="https://console.twilio.com/us1/develop/sms/try-it-out/whatsapp-learn" target="_blank">WhatsApp Sandbox</a></li>
                    <li>Únete al sandbox desde tu WhatsApp</li>
                    <li>Configura credenciales en <code>verificacion_config.php</code></li>
                    <li>Cambia <code>USE_WHATSAPP_SANDBOX = true</code></li>
                    <li>Cambia <code>SMS_MODE_DESARROLLO = false</code></li>
                </ol>
            <?php else: ?>
                <p><strong>✅ Sistema configurado para producción</strong></p>
                <?php if (empty($config['errores'])): ?>
                    <p>Tu sistema está listo para enviar mensajes por <?php echo $config['usa_whatsapp'] ? 'WhatsApp' : 'SMS'; ?>.</p>
                    <h4>Verificación final:</h4>
                    <ol>
                        <li>Registra un usuario de prueba</li>
                        <li>Verifica que reciba el código por <?php echo $config['usa_whatsapp'] ? 'WhatsApp' : 'SMS'; ?></li>
                        <li>Monitorea los logs de Docker: <code>docker compose logs -f web</code></li>
                    </ol>
                <?php else: ?>
                    <p><strong>⚠️ Hay errores en la configuración</strong></p>
                    <p>Corrige los errores mostrados arriba antes de usar en producción.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Ver Log -->
        <?php if ($config['modo_desarrollo'] && file_exists(__DIR__ . '/sms_log.txt')): ?>
        <div class="next-steps">
            <h3>📄 Últimos Mensajes Simulados</h3>
            <pre style="background-color: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; max-height: 300px;"><?php 
                $log_content = file_get_contents(__DIR__ . '/sms_log.txt');
                $log_lines = explode("\n", $log_content);
                $last_lines = array_slice($log_lines, -50); // Últimas 50 líneas
                echo htmlspecialchars(implode("\n", $last_lines));
            ?></pre>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>
