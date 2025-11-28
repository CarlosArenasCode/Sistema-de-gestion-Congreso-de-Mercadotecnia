# 🔐 Credenciales de Acceso - Sistema de Gestión

## 👨‍💼 Administrador

**Email**: `admin@congreso.com`  
**Contraseña**: *Necesitas verificar/crear la contraseña*

**URL de Login**: http://localhost:8081/Front-end/login_admin.html

### ⚠️ Nota Importante
Si no conoces la contraseña del administrador, necesitarás:
1. Resetearla en la base de datos, o
2. Crear un nuevo administrador

---

## 👨‍🎓 Usuarios de Prueba

### Usuario 1 (Ejemplo)
**Email**: `test@ejemplo.com`  
**Matrícula**: `TEST123456`  
**Contraseña**: La que usaste al registrarte

**URL de Login**: http://localhost:8081/Front-end/login.html

---

## 🔧 Cómo Crear/Resetear Contraseña de Administrador

### Opción 1: Crear Nueva Contraseña para Admin Existente

Ejecuta este script PHP:

```php
<?php
require_once 'conexion.php';

$nueva_password = 'Admin123!'; // Cambia esto por la contraseña que quieras
$password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE ADMINISTRADORES SET PASSWORD_HASH = :hash WHERE EMAIL = 'admin@congreso.com'");
$stmt->execute([':hash' => $password_hash]);

echo "Contraseña actualizada exitosamente para admin@congreso.com\n";
echo "Nueva contraseña: " . $nueva_password . "\n";
?>
```

### Opción 2: Crear Nuevo Administrador

```php
<?php
require_once 'conexion.php';

$email = 'miadmin@congreso.com';
$nombre = 'Mi Administrador';
$password = 'MiPassword123!';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO ADMINISTRADORES (NOMBRE_COMPLETO, EMAIL, PASSWORD_HASH, ROL) VALUES (?, ?, ?, ?)");
$stmt->execute([$nombre, $email, $password_hash, 'superadmin']);

echo "Administrador creado exitosamente\n";
echo "Email: " . $email . "\n";
echo "Contraseña: " . $password . "\n";
?>
```

---

## ✅ Mejoras Realizadas

### Login de Usuarios (login.php)
- ✅ Ahora acepta **email O matrícula** para iniciar sesión
- ✅ Búsqueda flexible en la base de datos
- ✅ Mensaje de error actualizado

### Login de Administradores (login_admin.php)
- ✅ Nuevo archivo dedicado para administradores
- ✅ Usa **email** como identificador
- ✅ Logging detallado para debugging
- ✅ Formulario actualizado (login_admin.html)

---

## 🧪 Cómo Probar

### Probar Login de Usuario
1. Ve a: http://localhost:8081/Front-end/login.html
2. Puedes usar:
   - **Matrícula**: TEST123456
   - **O Email**: test@ejemplo.com
3. Ingresa tu contraseña
4. Deberías acceder al dashboard de alumno

### Probar Login de Administrador
1. Ve a: http://localhost:8081/Front-end/login_admin.html
2. Usa:
   - **Email**: admin@congreso.com
   - **Contraseña**: (la que establezcas con el script)
3. Deberías acceder al dashboard de administrador

---

## 📝 Archivos Modificados

1. ✅ `php/login.php` - Acepta email O matrícula para usuarios
2. ✅ `php/login_admin.php` - Nuevo archivo para login de admins
3. ✅ `Front-end/login_admin.html` - Actualizado para usar login_admin.php
4. ✅ `php/verificar_admins.php` - Script para ver admins existentes

---

**Última actualización**: 27 de Noviembre, 2025
