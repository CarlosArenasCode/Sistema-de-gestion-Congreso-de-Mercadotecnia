<?php
// Test para ver qué retorna ver_evento.php con sesión activa
session_start();
$_SESSION['id_usuario'] = 1; // Simular usuario logueado

// Capturar output
ob_start();
include 'ver_evento.php';
$output = ob_get_clean();

echo "=== OUTPUT CAPTURADO ===\n";
echo "Longitud: " . strlen($output) . " bytes\n";
echo "Primeros 200 caracteres:\n";
echo substr($output, 0, 200) . "\n\n";
echo "Últimos 200 caracteres:\n";
echo substr($output, -200) . "\n\n";

// Intentar decodificar JSON
$json = json_decode($output, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "ERROR JSON: " . json_last_error_msg() . "\n";
    echo "Output completo:\n";
    echo $output . "\n";
} else {
    echo "JSON válido, " . count($json) . " eventos encontrados\n";
}
?>
