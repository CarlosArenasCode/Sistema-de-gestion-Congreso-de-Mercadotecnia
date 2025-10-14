# 🔐 Sistema de Verificación por Código de 6 Dígitos

Este documento explica la nueva funcionalidad de verificación de cuentas mediante código de 6 dígitos enviado por **Email** y **SMS**.

---

## 📋 Cambios Implementados

### 1. **Registro de Usuario**
- ✅ Nuevo campo: **Número de Teléfono** (requerido)
- ✅ Usuario se registra pero la cuenta queda **NO VERIFICADA**
- ✅ Se genera automáticamente un código de 6 dígitos aleatorio
- ✅ El código se envía por:
  - 📧 **Email** (con diseño HTML atractivo)
  - 📱 **SMS** (usando Twilio API)
- ✅ Código válido por **15 minutos**

### 2. **Verificación de Código**
- ✅ Página dedicada para ingresar el código de 6 dígitos
- ✅ Auto-focus entre campos para mejor UX
- ✅ Validación en tiempo real
- ✅ Máximo **5 intentos** fallidos
- ✅ Opción para **reenviar código** (con límite de 1 minuto entre reenvíos)
- ✅ Una vez verificado, el usuario puede iniciar sesión

### 3. **Login**
- ✅ Verifica si el usuario está verificado antes de permitir acceso
- ✅ Si NO está verificado, redirige a página de verificación

---

## 🗄️ Cambios en Base de Datos

Se agregaron los siguientes campos a la tabla `usuarios`:

```sql
telefono VARCHAR(20)                  -- Número de teléfono
codigo_verificacion VARCHAR(6)        -- Código de 6 dígitos
fecha_codigo DATETIME                 -- Fecha de generación del código
verificado TINYINT(1) DEFAULT 0       -- 0 = no verificado, 1 = verificado
intentos_verificacion INT DEFAULT 0   -- Contador de intentos fallidos
```

### **Ejecutar Script SQL**

Para aplicar los cambios en la base de datos, ejecuta:

```bash
# Opción 1: Desde Docker
docker exec -i congreso_db mysql -u congreso_user -pcongreso_pass congreso_db < Proyecto_conectado/sql/add_verification_fields.sql

# Opción 2: Desde phpMyAdmin
# 1. Accede a http://localhost:8081
# 2. Selecciona la base de datos congreso_db
# 3. Ve a la pestaña "SQL"
# 4. Copia y pega el contenido de add_verification_fields.sql
# 5. Ejecuta
```

---

## 📱 Configuración de SMS (Twilio)

### **Paso 1: Crear Cuenta en Twilio**

1. Regístrate en https://www.twilio.com/
2. Obtén un número de teléfono de prueba (gratis)
3. Copia tus credenciales:
   - Account SID
   - Auth Token
   - Tu número de Twilio

### **Paso 2: Configurar en el Proyecto**

Edita el archivo `Proyecto_conectado/php/sms_service.php`:

```php
define('TWILIO_ACCOUNT_SID', 'TU_ACCOUNT_SID_AQUI');
define('TWILIO_AUTH_TOKEN', 'TU_AUTH_TOKEN_AQUI');
define('TWILIO_PHONE_NUMBER', '+1234567890'); // Tu número de Twilio
```

### **Modo de Desarrollo (Sin Twilio)**

Si no quieres configurar Twilio aún, el sistema usa **modo simulado**:
- Los SMS se guardan en `php/sms_log.txt`
- El código sigue funcionando para pruebas
- Los emails SÍ se envían normalmente

---

## 📧 Configuración de Email

El envío de emails ya está configurado con SMTP. Verifica que `smtp_config.php` tenga las credenciales correctas.

---

## 🚀 Flujo de Usuario

### **1. Registro**
1. Usuario completa formulario (incluyendo teléfono)
2. Click en "Registrarse"
3. Sistema:
   - Guarda usuario con `verificado = 0`
   - Genera código de 6 dígitos
   - Envía código por email y SMS
   - Redirige a página de verificación

### **2. Verificación**
1. Usuario recibe código en email y SMS
2. Ingresa los 6 dígitos en la página
3. Sistema valida:
   - ✅ Código correcto → Cuenta activada
   - ❌ Código incorrecto → Mostrar intentos restantes
   - ⏱️ Código expirado → Opción para reenviar

### **3. Login**
1. Usuario intenta iniciar sesión
2. Sistema verifica:
   - ✅ Cuenta verificada → Acceso permitido
   - ❌ Cuenta NO verificada → Redirige a verificación

---

## 🧪 Pruebas

### **Probar Registro y Verificación**

```powershell
# 1. Accede al formulario de registro
http://localhost:8080/Front-end/registro_usuario.html

# 2. Completa el formulario con datos de prueba
Nombre: Juan Pérez
Email: juan@ejemplo.com
Matrícula: A12345
Teléfono: +521234567890
Contraseña: Test123

# 3. Revisa el email y el archivo sms_log.txt para obtener el código

# 4. Ingresa el código en la página de verificación
http://localhost:8080/Front-end/verificar_codigo.html?email=juan@ejemplo.com
```

### **Ver SMS Simulados**

```powershell
# Ver el archivo de log de SMS
Get-Content Proyecto_conectado\php\sms_log.txt -Tail 20
```

---

## 📁 Archivos Modificados/Creados

### **Creados:**
- ✅ `Front-end/verificar_codigo.html` - Página de verificación
- ✅ `php/verificar_codigo.php` - Procesa verificación
- ✅ `php/reenviar_codigo.php` - Reenvía código
- ✅ `php/sms_service.php` - Servicio de envío de SMS
- ✅ `sql/add_verification_fields.sql` - Script de BD
- ✅ `VERIFICACION_README.md` - Esta documentación

### **Modificados:**
- ✅ `Front-end/registro_usuario.html` - Campo de teléfono
- ✅ `php/registrar_usuario.php` - Envío de código
- ✅ `php/login.php` - Validación de verificación

---

## 🔒 Seguridad

- ✅ Código de 6 dígitos aleatorio
- ✅ Expiración de 15 minutos
- ✅ Máximo 5 intentos fallidos
- ✅ Límite de reenvío (1 minuto)
- ✅ Código se elimina después de verificación exitosa
- ✅ Validación de formato de teléfono
- ✅ Protección contra ataques de fuerza bruta

---

## ⚠️ Notas Importantes

1. **Twilio en producción**: Necesitarás una cuenta de pago para enviar SMS a números reales
2. **Costos**: Twilio cobra por SMS (aprox $0.01 USD por mensaje)
3. **Alternativas**: Puedes usar otros proveedores como:
   - Vonage (Nexmo)
   - AWS SNS
   - MessageBird
   - Sinch

4. **Solo Email**: Si prefieres solo verificación por email, puedes deshabilitar SMS comentando la línea en `registrar_usuario.php`:
   ```php
   // enviar_codigo_verificacion_sms($telefono, $codigo_verificacion, $nombre_completo);
   ```

---

## 📞 Soporte

Para más información o problemas, consulta:
- Documentación de Twilio: https://www.twilio.com/docs
- Logs en: `php/sms_log.txt`
- Errores PHP: Revisa error_log del servidor

---

¡Sistema de verificación implementado exitosamente! 🎉
