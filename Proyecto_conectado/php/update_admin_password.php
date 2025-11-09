<?php
require 'conexion.php';

$email = 'admin@congreso.com';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Generando nuevo hash para: $email\n";
echo "Password: $password\n";
echo "Hash generado: $hash\n\n";

try {
    $stmt = $pdo->prepare("UPDATE administradores SET password_hash = ? WHERE email = ?");
    $stmt->execute([$hash, $email]);
    
    echo "✓ Password actualizado exitosamente\n\n";
    
    // Verificar
    $stmt = $pdo->prepare("SELECT id_admin, nombre_completo, email, SUBSTR(password_hash, 1, 30) as hash_preview FROM administradores WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Verificación:\n";
    print_r($admin);
    
    // Test password verify
    $stmt = $pdo->prepare("SELECT password_hash FROM administradores WHERE email = ?");
    $stmt->execute([$email]);
    $stored_hash = $stmt->fetchColumn();
    
    echo "\nTest password_verify: ";
    echo password_verify($password, $stored_hash) ? "✓ OK" : "✗ FAIL";
    echo "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
