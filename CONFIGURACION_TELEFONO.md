# 📱 Configuración de Número Emisor para SMS

## 🎯 Objetivo

Tu número **+52 449 210 6893** será el número EMISOR (FROM) desde el cual se enviarán TODOS los códigos de verificación. Cada usuario recibirá el código en SU PROPIO número de teléfono que registre.

---

## ⚙️ Configuración

### **Tu número ya está configurado:**

**Archivo:** `Proyecto_conectado/php/verificacion_config.php`

```php
<?php
// TU NÚMERO EMISOR (FROM) - Desde donde se envían los SMS
define('TELEFONO_EMISOR', '+5244921068393'); // +52 449 210 6893

// Modo desarrollo: SMS se guardan en log (no se envían realmente)
define('SMS_MODE_DESARROLLO', true);

// Credenciales de Twilio (necesario para envío real)
define('TWILIO_ACCOUNT_SID', 'your_account_sid_here');
define('TWILIO_AUTH_TOKEN', 'your_auth_token_here');
?>
```

### **Para envío REAL de SMS:**

1. **Crea cuenta en Twilio:** https://www.twilio.com/
2. **Verifica tu número:** +52 449 210 6893 en Twilio Console
3. **Obtén credenciales:** Account SID y Auth Token
4. **Actualiza verificacion_config.php:**
   ```php
   define('SMS_MODE_DESARROLLO', false); // ← Cambiar a false
   define('TWILIO_ACCOUNT_SID', 'AC1234...'); // Tu SID
   define('TWILIO_AUTH_TOKEN', 'abc123...'); // Tu Token
   ```

---

## 🔄 Flujo del Sistema

```
1. USUARIO SE REGISTRA
   ├─ Nombre: Juan Pérez
   ├─ Email: juan@ejemplo.com
   ├─ Teléfono: +52 449 123 4567  ← SU número
   └─ Click en "Registrarse"

2. SISTEMA GENERA CÓDIGO
   └─ Código: 123456

3. SISTEMA ENVÍA SMS
   ├─ FROM (Emisor): +52 449 210 6893 (TU número)
   └─ TO (Destino): +52 449 123 4567 (número del usuario)

4. USUARIO RECIBE SMS
   ┌──────────────────────────────┐
   │ De: +52 449 210 6893         │
   │                               │
   │ Hola Juan Pérez,              │
   │                               │
   │ Tu código de verificación:    │
   │ 🔐 123456                     │
   │                               │
   │ Expira en 15 minutos.         │
   └──────────────────────────────┘

5. USUARIO INGRESA CÓDIGO
   └─ Va a verificar_codigo.html e ingresa 123456
   └─ ¡Cuenta activada! ✅
```

---

## 🧪 Probar el Sistema

### **Modo Desarrollo (Por Defecto)**

Los SMS NO se envían realmente, se guardan en un archivo:

```powershell
# Ver los SMS simulados
Get-Content Proyecto_conectado\php\sms_log.txt -Tail 20
```

**Ejemplo de log:**
```
========================================
Timestamp: 2025-10-13 14:30:45
To: +521234567890
Message: 🔐 CÓDIGO DE VERIFICACIÓN

Usuario: Juan Pérez
Email: juan@ejemplo.com

Código: 123456

Expira en 15 minutos.
========================================
```

### **Modo Producción (Con Twilio)**

1. Edita `verificacion_config.php`:
   ```php
   define('SMS_MODE_DESARROLLO', false); // ← Cambiar a false
   ```

2. Configura Twilio en `sms_service.php`:
   ```php
   define('TWILIO_ACCOUNT_SID', 'tu_sid_aqui');
   define('TWILIO_AUTH_TOKEN', 'tu_token_aqui');
   define('TWILIO_PHONE_NUMBER', '+1234567890');
   ```

3. Los SMS se enviarán realmente a tu teléfono

---

## 📝 Ejemplo de Uso

### **1. Usuario se registra:**
```
http://localhost:8080/Front-end/registro_usuario.html

Nombre: Test User
Email: test@ejemplo.com
Matrícula: A12345
Semestre: 5
Contraseña: Test123
```

### **2. Tú recibes SMS (o ves en log):**
```powershell
# Ver último SMS
Get-Content Proyecto_conectado\php\sms_log.txt -Tail 10
```

```
Código: 456789
Usuario: Test User
Email: test@ejemplo.com
```

### **3. Le das el código al usuario:**
- Usuario va a: http://localhost:8080/Front-end/verificar_codigo.html?email=test@ejemplo.com
- Ingresa: `456789`
- ¡Cuenta verificada! ✅

---

## 🔒 Seguridad

- ✅ Solo TÚ recibes los códigos
- ✅ Código expira en 15 minutos
- ✅ Máximo 5 intentos por código
- ✅ El usuario también recibe el código por email
- ✅ Cooldown de 1 minuto para reenviar

---

## 🚀 Migración Futura

Cuando quieras que cada usuario tenga su propio teléfono:

1. **Agregar campo teléfono al formulario:**
   ```html
   <input type="tel" name="telefono" required>
   ```

2. **Cambiar función en registrar_usuario.php:**
   ```php
   // De:
   $telefono = TELEFONO_VERIFICACION_ADMIN;
   
   // A:
   $telefono = $_POST['telefono'] ?? '';
   ```

3. **Actualizar llamada a SMS:**
   ```php
   // De:
   enviar_codigo_verificacion_sms($codigo, $nombre, $email);
   
   // A:
   enviar_codigo_verificacion_sms_usuario($telefono, $codigo, $nombre);
   ```

---

## ⚠️ Importante

1. **Costos Twilio:**
   - Modo desarrollo: GRATIS (solo logs)
   - Modo producción: ~$0.01 USD por SMS

2. **Números de prueba Twilio:**
   - Puedes enviar a números verificados GRATIS
   - Verifica tu número en https://console.twilio.com/

3. **Alternativas:**
   - **Solo Email:** Comenta la línea SMS en `registrar_usuario.php`
   - **WhatsApp:** Twilio también soporta WhatsApp

---

## 📞 Resumen

✅ **AHORA:** Un solo número (el tuyo) recibe TODOS los códigos
✅ **Usuarios:** Solo ingresan el código que tú les das
✅ **Modo desarrollo:** SMS se guardan en `sms_log.txt`
✅ **Fácil de cambiar:** Cuando quieras, cada usuario puede tener su teléfono

---

¡Sistema configurado para usar tu número fijo! 🎉
