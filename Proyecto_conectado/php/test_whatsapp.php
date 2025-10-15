<?php
/**
 * Archivo de prueba para envío de WhatsApp
 * Prueba el envío de código de verificación
 */

require_once 'whatsapp_sender.php';

// Configuración de prueba
$telefono_prueba = '+5244912345678'; // CAMBIA ESTO por el número de prueba
$codigo_prueba = '123456';
$nombre_prueba = 'Usuario de Prueba';

echo "<h1>🧪 Prueba de WhatsApp - Código de Verificación</h1>";
echo "<hr>";

// Obtener estado del servicio
echo "<h2>📊 Estado del Servicio</h2>";
$estado = obtener_estado_servicio_whatsapp();
echo "<pre>";
print_r($estado);
echo "</pre>";

echo "<hr>";

// Probar envío de código
echo "<h2>📤 Enviando código de verificación...</h2>";
echo "<p><strong>Número emisor (FROM):</strong> " . TELEFONO_EMISOR . "</p>";
echo "<p><strong>Número destino (TO):</strong> $telefono_prueba</p>";
echo "<p><strong>Código:</strong> $codigo_prueba</p>";
echo "<p><strong>Método:</strong> " . (USE_WHATSAPP ? 'WhatsApp' : 'SMS') . "</p>";

$resultado = enviar_codigo_verificacion_whatsapp($telefono_prueba, $codigo_prueba, $nombre_prueba);

echo "<hr>";
echo "<h2>📋 Resultado del Envío</h2>";
echo "<pre>";
print_r($resultado);
echo "</pre>";

if ($resultado['success']) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;'>";
    echo "✅ " . $resultado['message'];
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "❌ " . $resultado['message'];
    echo "</div>";
}

// Si está en modo desarrollo, mostrar dónde se guardó el log
if (SMS_MODE_DESARROLLO) {
    $log_file = __DIR__ . '/logs/whatsapp_codigos.log';
    echo "<hr>";
    echo "<h2>📝 Modo Desarrollo</h2>";
    echo "<p>Los mensajes se guardan en: <code>$log_file</code></p>";
    
    if (file_exists($log_file)) {
        echo "<h3>Últimos 5 envíos:</h3>";
        $logs = file($log_file);
        $ultimos = array_slice($logs, -5);
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        echo implode("", $ultimos);
        echo "</pre>";
    }
}

echo "<hr>";
echo "<h2>📚 Instrucciones</h2>";
echo "<ol>";
echo "<li>Cambia <code>\$telefono_prueba</code> por el número que quieres probar</li>";
echo "<li>Asegúrate de que el formato sea: +5244912345678 (código país + número)</li>";
echo "<li>Si <code>SMS_MODE_DESARROLLO = true</code>, los mensajes se guardarán en logs</li>";
echo "<li>Para envíos reales, configura Twilio en <code>verificacion_config.php</code></li>";
echo "</ol>";

echo "<hr>";
echo "<h2>🔧 Configuración Actual</h2>";
echo "<pre>";
echo "Número Emisor: " . TELEFONO_EMISOR . "\n";
echo "Modo Desarrollo: " . (SMS_MODE_DESARROLLO ? 'SÍ (logs)' : 'NO (envíos reales)') . "\n";
echo "Usa WhatsApp: " . (USE_WHATSAPP ? 'SÍ' : 'NO (usa SMS)') . "\n";
echo "Twilio configurado: " . (TWILIO_ACCOUNT_SID !== 'your_account_sid_here' ? 'SÍ' : 'NO') . "\n";
echo "</pre>";

echo "<hr>";
echo "<p><a href='verificar_config.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Ver Configuración Completa</a></p>";
?>
