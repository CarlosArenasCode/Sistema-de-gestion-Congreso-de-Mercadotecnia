<?php
/**
 * Script para crear/actualizar el administrador con contraseña válida
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Configuración de Administrador</h2>";
echo "<pre>";

// Intentar conectar
try {
    require_once '../php/conexion.php';
    echo "✅ Conexión a base de datos: OK\n\n";
} catch (Exception $e) {
    echo "❌ Error de conexión a base de datos:\n";
    echo $e->getMessage() . "\n\n";
    echo "Verifica que Docker esté corriendo y Oracle esté disponible.\n";
    echo "</pre>";
    exit;
}

echo "<h2>Configuración de Administrador</h2>";
echo "<pre>";

// Credenciales del administrador
$email_admin = 'admin@congreso.com';
$password_admin = 'admin123'; // Contraseña por defecto
$nombre_admin = 'Administrador Principal';
$rol = 'superadmin';

// Generar hash de la contraseña
$password_hash = password_hash($password_admin, PASSWORD_DEFAULT);

echo "=== CONFIGURACIÓN ===\n";
echo "Email: {$email_admin}\n";
echo "Contraseña: {$password_admin}\n";
echo "Hash generado: {$password_hash}\n\n";

try {
    // Verificar si el administrador ya existe
    $check = $pdo->prepare("SELECT id_admin, nombre_completo FROM administradores WHERE email = ?");
    $check->execute([$email_admin]);
    $admin_existe = $check->fetch();
    
    if ($admin_existe) {
        // Actualizar contraseña del administrador existente
        echo "=== ADMINISTRADOR EXISTENTE ===\n";
        echo "ID: {$admin_existe['id_admin']}\n";
        echo "Nombre: {$admin_existe['nombre_completo']}\n\n";
        
        $update = $pdo->prepare("UPDATE administradores SET password_hash = ? WHERE email = ?");
        $update->execute([$password_hash, $email_admin]);
        
        echo "✅ Contraseña actualizada exitosamente\n\n";
    } else {
        // Insertar nuevo administrador
        echo "=== CREANDO NUEVO ADMINISTRADOR ===\n";
        
        $insert = $pdo->prepare(
            "INSERT INTO administradores (nombre_completo, email, password_hash, rol) 
             VALUES (?, ?, ?, ?)"
        );
        $insert->execute([$nombre_admin, $email_admin, $password_hash, $rol]);
        
        echo "✅ Administrador creado exitosamente\n\n";
    }
    
    // Verificar que el login funciona
    echo "=== VERIFICACIÓN DE LOGIN ===\n";
    $verify = $pdo->prepare("SELECT id_admin, nombre_completo, email, password_hash, rol FROM administradores WHERE email = ?");
    $verify->execute([$email_admin]);
    $admin = $verify->fetch();
    
    if ($admin) {
        echo "Admin encontrado:\n";
        echo "  ID: {$admin['id_admin']}\n";
        echo "  Nombre: {$admin['nombre_completo']}\n";
        echo "  Email: {$admin['email']}\n";
        echo "  Rol: {$admin['rol']}\n\n";
        
        // Probar verificación de contraseña
        if (password_verify($password_admin, $admin['password_hash'])) {
            echo "✅ Verificación de contraseña: CORRECTA\n";
        } else {
            echo "❌ Verificación de contraseña: INCORRECTA\n";
        }
    }
    
    echo "\n=== RESUMEN ===\n";
    echo "Puedes iniciar sesión en el panel de administración con:\n";
    echo "  URL: http://localhost:8080/Proyecto_conectado/Front-end/login_admin.html\n";
    echo "  Email: {$email_admin}\n";
    echo "  Contraseña: {$password_admin}\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
