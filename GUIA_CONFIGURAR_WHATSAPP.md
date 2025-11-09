# 📱 Guía para Configurar WhatsApp con Twilio

## ⚠️ IMPORTANTE: Diferencia entre SMS y WhatsApp

Twilio ofrece DOS servicios diferentes:
1. **SMS tradicionales** - Envío de mensajes de texto simples
2. **WhatsApp Business API** - Envío de mensajes por WhatsApp

## 🎯 Opciones para enviar mensajes

### Opción 1: SMS Tradicionales (Más Rápido)
✅ Más fácil de configurar
✅ Funciona inmediatamente con número de prueba
✅ Créditos gratuitos para testing ($15 USD)
❌ Costo por mensaje (después de créditos gratuitos)
❌ No aparece en WhatsApp, llega como SMS

### Opción 2: WhatsApp Business API (Recomendado para producción)
✅ Aparece en WhatsApp del usuario
✅ Más económico a largo plazo
✅ Mejor experiencia de usuario
❌ Requiere aprobación de plantillas de mensajes
❌ Proceso de configuración más complejo
❌ Requiere cuenta de negocio verificada

---

## 🚀 OPCIÓN 1: Configurar SMS (Rápido - 10 minutos)

### Paso 1: Crear cuenta en Twilio
1. Ve a: https://www.twilio.com/try-twilio
2. Regístrate con tu email
3. Verifica tu número de teléfono
4. Twilio te dará **$15 USD en créditos gratuitos**

### Paso 2: Obtener credenciales
1. Ve al Dashboard: https://console.twilio.com/
2. Busca en la parte superior:
   - **Account SID** (ejemplo: ACxxxxxxxxxxxxxxxxxxxxx)
   - **Auth Token** (haz clic en "Show" para verlo)
3. Copia estos valores

### Paso 3: Obtener número de teléfono
1. En el Dashboard de Twilio, ve a:
   - **Phone Numbers** → **Manage** → **Buy a number**
2. Filtra por:
   - **Country**: Mexico (+52)
   - **Capabilities**: SMS
3. **IMPORTANTE**: Busca un número que soporte SMS
4. Haz clic en "Buy" (usará tus créditos gratuitos)

### Paso 4: Configurar en tu proyecto

Edita el archivo: `Proyecto_conectado/php/verificacion_config.php`

```php
<?php
// NÚMERO EMISOR - Usa el número que compraste en Twilio
define('TELEFONO_EMISOR', '+5215512345678'); // ⚠️ CAMBIA ESTO por tu número de Twilio

// Modo de desarrollo: Cambiar a false para envío real
define('SMS_MODE_DESARROLLO', false); // ⚠️ CAMBIA A false

// Configuración de Twilio
define('TWILIO_ACCOUNT_SID', 'ACxxxxxxxxxxxxxxxxxxxxx'); // ⚠️ Pega tu Account SID
define('TWILIO_AUTH_TOKEN', 'tu_auth_token_aqui');      // ⚠️ Pega tu Auth Token
?>
```

### Paso 5: Probar
1. Reinicia Docker: `docker compose restart`
2. Registra un usuario con tu número de teléfono
3. Deberías recibir un SMS con el código

---

## 📲 OPCIÓN 2: Configurar WhatsApp (Producción - Más complejo)

### Requisitos previos
- Cuenta de Facebook Business verificada
- Número de WhatsApp Business
- Aprobación de plantillas de mensajes (puede tomar días)

### Paso 1: Configurar WhatsApp en Twilio
1. Ve a: https://console.twilio.com/
2. En el menú lateral: **Messaging** → **Try it out** → **Send a WhatsApp message**
3. Sigue el proceso de conectar tu cuenta de WhatsApp Business

### Paso 2: Crear plantillas de mensajes
Twilio requiere que crees plantillas pre-aprobadas para WhatsApp:

**Plantilla de código de verificación:**
```
Nombre: codigo_verificacion
Contenido: 
Hola {{1}},

Tu código de verificación para el Congreso de Mercadotecnia es:

🔐 {{2}}

Este código expira en 15 minutos.
No compartas este código con nadie.
```

### Paso 3: Esperar aprobación
- Las plantillas deben ser aprobadas por WhatsApp (24-48 horas)
- Una vez aprobadas, puedes usarlas

### Paso 4: Modificar código para usar WhatsApp

Necesitarás modificar `sms_service.php` para usar la API de WhatsApp de Twilio:

```php
// Para WhatsApp, cambiar el formato del número:
$data = array(
    'To' => 'whatsapp:' . $to,              // Agregar prefijo whatsapp:
    'From' => 'whatsapp:' . TELEFONO_EMISOR, // Agregar prefijo whatsapp:
    'Body' => $message
);
```

---

## 🔧 Solución Alternativa: Usar tu número actual con Twilio Sandbox

### WhatsApp Sandbox (Solo para desarrollo/testing)

Twilio ofrece un "Sandbox" de WhatsApp que puedes usar sin aprobación:

1. Ve a: https://console.twilio.com/us1/develop/sms/try-it-out/whatsapp-learn
2. Únete al Sandbox siguiendo las instrucciones
3. Envía el mensaje que te indica a su número de WhatsApp

**Limitaciones del Sandbox:**
- Solo funciona con números que se "unieron" al sandbox
- Cada usuario debe enviar un mensaje inicial
- No recomendado para producción
- Bueno para testing

---

## 💡 RECOMENDACIÓN ACTUAL

### Para desarrollo/testing inmediato:
1. **Usa SMS tradicionales** (Opción 1) - Es lo más rápido
2. Los usuarios recibirán SMS normales (no WhatsApp)
3. Funciona perfectamente para validar el código

### Para producción:
1. Configura WhatsApp Business API (Opción 2)
2. Crea y aprueba plantillas
3. Migra cuando estés listo para lanzar

---

## 🎯 Configuración Recomendada AHORA

Edita: `Proyecto_conectado/php/verificacion_config.php`

```php
<?php
// Tu número de Twilio (cuando lo compres)
define('TELEFONO_EMISOR', '+5215512345678'); // Número de Twilio

// CAMBIAR A false cuando tengas Twilio configurado
define('SMS_MODE_DESARROLLO', false);

// Credenciales de Twilio (obtenerlas del dashboard)
define('TWILIO_ACCOUNT_SID', 'ACxxxxx'); // Tu Account SID
define('TWILIO_AUTH_TOKEN', 'tu_token');  // Tu Auth Token
?>
```

---

## ❓ ¿Necesitas ayuda?

### Costos aproximados (después de créditos gratuitos):
- SMS en México: ~$0.02-0.04 USD por mensaje
- WhatsApp: ~$0.005-0.01 USD por conversación
- Número mensual: ~$1-2 USD/mes

### Alternativas gratuitas/baratas:
1. **Mantener modo desarrollo** - Log en archivo (actual)
2. **Usar solo email** - Eliminar verificación SMS
3. **API de WhatsApp Web** (no oficial, puede ser bloqueado)

---

## 📝 Próximos pasos

1. **¿Quieres configurar SMS ahora?** → Sigue Opción 1
2. **¿Prefieres esperar y usar WhatsApp después?** → Déjalo en modo desarrollo
3. **¿Necesitas ayuda para configurar?** → Dime en qué paso estás

### Comandos útiles:

Ver log de SMS simulados:
```powershell
Get-Content Proyecto_conectado\php\sms_log.txt -Tail 20
```

Reiniciar Docker después de cambios:
```powershell
docker compose restart
```

Ver logs en tiempo real:
```powershell
docker compose logs -f web
```
