# Guía de Configuración del Sistema de Notificaciones

## Estado Actual del Sistema

El sistema de registro de usuarios intenta enviar notificaciones por **dos canales**:

1. **📧 Email (SMTP)**
2. **📱 WhatsApp**

### ⚠️ Problemas Detectados

#### 1. Configuración SMTP
- **Estado**: Configurado pero con error de autenticación
- **Cuenta configurada**: `al529633@edu.uaa.mx`
- **Error**: No puede autenticarse con `smtp.office365.com`

**Solución**:
```
La cuenta de Office 365 requiere una "App Password" cuando tiene 
autenticación de dos factores (MFA) habilitada.

Pasos para generar App Password:
1. Ve a https://account.microsoft.com/security
2. Busca "Contraseñas de aplicación" o "App Passwords"
3. Genera una nueva contraseña para la aplicación
4. Actualiza el archivo php/smtp_config.php con la nueva contraseña
```

#### 2. Servicio de WhatsApp
- **Estado**: ⏳ Esperando escanear código QR
- **Error actual**: "El servicio de WhatsApp no está listo"

**Solución**:
```
El servicio de WhatsApp necesita ser vinculado con una cuenta de WhatsApp:

1. Ve a los logs del contenedor:
   docker logs congreso_whatsapp

2. Verás un código QR en formato ASCII

3. Abre WhatsApp en tu teléfono:
   - Android: Menú (⋮) > Dispositivos vinculados
   - iPhone: Configuración > Dispositivos vinculados

4. Toca "Vincular un dispositivo"

5. Escanea el código QR que aparece en los logs

6. Una vez vinculado, el servicio estará listo para enviar mensajes

Nota: El número +52 449 210 6893 debe estar registrado en WhatsApp
```

## 🔧 Cómo Funciona Actualmente

El sistema ahora es **tolerante a fallos**:

1. ✅ **El registro continúa** aunque fallen las notificaciones
2. 📝 **Se registra en logs** cuando hay problemas
3. 🔐 **El usuario puede usar el código de verificación** que se guarda en la base de datos

### Ver el código de verificación manualmente

Si los emails/WhatsApp no funcionan, puedes ver el código directamente en la base de datos:

```sql
-- Conectar a la base de datos
docker exec -it congreso_db mysql -ucongreso_user -pcongreso_pass congreso_db

-- Ver el código de verificación de un usuario
SELECT email, codigo_verificacion, fecha_codigo 
FROM usuarios 
WHERE email = 'usuario@example.com';
```

## 📋 Estado de Columnas de Base de Datos

✅ Columnas agregadas a la tabla `usuarios`:
- `telefono` VARCHAR(20) - Número de teléfono formateado
- `codigo_verificacion` VARCHAR(6) - Código de 6 dígitos
- `fecha_codigo` DATETIME - Fecha de generación del código
- `verificado` TINYINT(1) - Estado de verificación (0=no, 1=sí)
- `intentos_verificacion` INT - Contador de intentos

## 🚀 Próximos Pasos Recomendados

### Opción 1: Arreglar SMTP (Recomendado)
1. Generar App Password en Microsoft
2. Actualizar `php/smtp_config.php`
3. Probar envío de email

### Opción 2: Usar Gmail
Editar `php/smtp_config.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'tu-email@gmail.com');
define('SMTP_PASS', 'tu-app-password'); // Generar en seguridad de Google
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

### Opción 3: Usar Mailtrap (Para pruebas)
```php
define('SMTP_HOST', 'smtp.mailtrap.io');
define('SMTP_USER', 'tu-usuario-mailtrap');
define('SMTP_PASS', 'tu-password-mailtrap');
define('SMTP_PORT', 2525);
define('SMTP_SECURE', '');
```

### Opción 4: Vincular WhatsApp
1. Ver logs: `docker logs congreso_whatsapp`
2. Escanear código QR
3. Esperar confirmación de vinculación

## 🧪 Probar el Sistema

```powershell
# 1. Verificar que los contenedores están corriendo
docker ps --filter "name=congreso"

# 2. Ver logs del servicio web
docker logs congreso_web --tail 50

# 3. Ver logs del servicio WhatsApp
docker logs congreso_whatsapp --tail 50

# 4. Probar registro de usuario
# Ir a: http://localhost:8080/Front-end/registro_usuario.html

# 5. Ver códigos en la base de datos
docker exec -it congreso_db mysql -ucongreso_user -pcongreso_pass -e "SELECT email, codigo_verificacion, verificado FROM congreso_db.usuarios ORDER BY fecha_registro DESC LIMIT 5;"
```

## 📊 Verificar Estructura de Base de Datos

```powershell
# Ver estructura completa de la tabla usuarios
docker exec -it congreso_db mysql -ucongreso_user -pcongreso_pass congreso_db -e "DESCRIBE usuarios;"
```

## 🛠️ Solución Temporal

Mientras configuras SMTP y WhatsApp, puedes:

1. **Registrar usuario normalmente**
2. **Obtener el código manualmente** de la base de datos
3. **Ingresar el código** en la página de verificación

El sistema está diseñado para ser resiliente y continuar funcionando aunque fallen los servicios de notificación.
