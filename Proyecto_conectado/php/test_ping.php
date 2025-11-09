<?php
// php/test_ping.php
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true, 'time' => date('c'), 'path' => __FILE__]);
?>
