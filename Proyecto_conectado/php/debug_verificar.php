<?php
/**
 * Debug script para verificar qué datos se están enviando
 */
header('Content-Type: application/json');

// Mostrar todos los datos POST recibidos
$debug_info = [
    'POST_data' => $_POST,
    'email' => $_POST['email'] ?? 'NO RECIBIDO',
    'digits' => []
];

for ($i = 1; $i <= 6; $i++) {
    $debug_info['digits']['digit' . $i] = $_POST['digit' . $i] ?? 'NO RECIBIDO';
}

// Concatenar código
$codigo = '';
for ($i = 1; $i <= 6; $i++) {
    $codigo .= $_POST['digit' . $i] ?? '';
}

$debug_info['codigo_concatenado'] = $codigo;
$debug_info['codigo_length'] = strlen($codigo);
$debug_info['email_empty'] = empty($_POST['email']);
$debug_info['codigo_empty'] = empty($codigo);

echo json_encode($debug_info, JSON_PRETTY_PRINT);
?>
