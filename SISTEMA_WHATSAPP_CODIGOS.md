# 📱 Sistema de Envío de Códigos por WhatsApp

## 🎯 Funcionalidad

Este sistema envía códigos de verificación de 6 dígitos a los usuarios por WhatsApp cuando se registran en el congreso.

## 🔧 Configuración Actual

### Número Emisor (FROM)
- **Número:** +52 449 210 6893
- **Formato Twilio:** `whatsapp:+5244492106893`
- **Uso:** Número desde el cual se envían todos los códigos

### Número Receptor (TO)
- **Origen:** Capturado en el formulario de registro
- **Campo:** `telefono` en la tabla `usuarios`
- **Formato:** +52 449 XXX XXXX (el usuario ingresa su número)

## 📋 Flujo de Envío

```
Usuario se registra → 
  Se genera código de 6 dígitos →
    Se guarda en BD →
      Se envía por EMAIL →
        Se envía por WHATSAPP →
          Usuario recibe código
```

## 🚀 Archivos Creados

### 1. `whatsapp_sender.php`
Servicio principal que maneja el envío de códigos de verificación.

**Funciones principales:**
- `enviar_codigo_verificacion_whatsapp()` - Envía código de 6 dígitos
- `generar_codigo_verificacion()` - Genera código aleatorio
- `validar_formato_telefono()` - Valida formato +52XXXXXXXXXX
- `registrar_envio_codigo()` - Guarda log de envíos

**Ejemplo de uso:**
```php
require 'whatsapp_sender.php';

$resultado = enviar_codigo_verificacion_whatsapp(
    '+5244912345678',  // Número del usuario
    '123456',          // Código de 6 dígitos
    'Juan Pérez'       // Nombre (opcional)
);

if ($resultado['success']) {
    echo "Código enviado correctamente";
}
```

### 2. `test_whatsapp.php`
Archivo de prueba para verificar el envío de WhatsApp.

**Uso:**
1. Abre en navegador: `http://localhost:8080/php/test_whatsapp.php`
2. Cambia el número de prueba en el código
3. Ejecuta para ver el resultado

### 3. `registrar_usuario.php` (Actualizado)
Integra el envío de WhatsApp en el proceso de registro.

**Cambios realizados:**
```php
// Línea 12: Ahora usa whatsapp_sender.php
require 'whatsapp_sender.php';

// Línea 139: Envía código por WhatsApp después del email
enviar_codigo_verificacion_whatsapp($telefono, $codigo_verificacion, $nombre_completo);
```

## 📝 Formato del Mensaje WhatsApp

Cuando un usuario se registra, recibe este mensaje:

```
Hola [Nombre], 🎓

Tu código de verificación es:

🔐 *123456*

Este código es válido por 10 minutos.
Si no solicitaste este código, ignora este mensaje.

Congreso de Mercadotecnia UAA 📚
```

## 🛠️ Configuración en `verificacion_config.php`

```php
// Tu número emisor (FROM)
define('TELEFONO_EMISOR', '+5244492106893');

// Modo desarrollo (guarda en logs, no envía realmente)
define('SMS_MODE_DESARROLLO', true);

// Usar WhatsApp en lugar de SMS
define('USE_WHATSAPP', true);

// Credenciales de Twilio (para producción)
define('TWILIO_ACCOUNT_SID', 'your_account_sid_here');
define('TWILIO_AUTH_TOKEN', 'your_auth_token_here');
```

## 🔄 Modos de Operación

### Modo Desarrollo (Actual)
- `SMS_MODE_DESARROLLO = true`
- Los mensajes NO se envían realmente
- Se guardan en: `php/logs/whatsapp_codigos.log`
- Útil para pruebas sin gastar créditos de Twilio

### Modo Producción
- `SMS_MODE_DESARROLLO = false`
- Los mensajes SÍ se envían por Twilio
- Requiere credenciales de Twilio configuradas
- Se consume crédito por cada mensaje

## 🧪 Probar el Sistema

### Prueba 1: Desde el navegador
```
http://localhost:8080/php/test_whatsapp.php
```

### Prueba 2: Registro completo
1. Ir a registro: `http://localhost:8080/Front-end/registro_usuario.html`
2. Llenar el formulario con tu número
3. Revisar log: `php/logs/whatsapp_codigos.log`

### Prueba 3: Ver configuración
```
http://localhost:8080/php/verificar_config.php
```

## 📊 Logs y Depuración

Los envíos se registran en:
```
php/logs/whatsapp_codigos.log
```

Formato del log:
```
[2025-10-14 10:30:15] WhatsApp | exitoso | Destino: +5244912345678 | Código: 123456
[2025-10-14 10:31:20] WhatsApp | exitoso | Destino: +5244998765432 | Código: 789012
```

## 🔐 Seguridad

### Validaciones implementadas:
- ✅ Formato de teléfono: `+5244912345678`
- ✅ Código de 6 dígitos numéricos
- ✅ Expiración en 10 minutos
- ✅ Logs de todos los envíos
- ✅ Sanitización de inputs

### Recomendaciones:
- No compartir códigos
- Verificar número antes de enviar
- Monitorear logs de envío
- Configurar rate limiting en producción

## 🚀 Pasar a Producción

### Paso 1: Obtener Credenciales Twilio
1. Crear cuenta en: https://www.twilio.com
2. Ir a Console: https://console.twilio.com/
3. Copiar Account SID y Auth Token

### Paso 2: Configurar WhatsApp Business
1. Ir a: https://console.twilio.com/us1/develop/sms/whatsapp/senders
2. Seguir proceso de verificación de número
3. Aprobar plantilla de mensaje
4. Obtener WhatsApp Template SID

### Paso 3: Actualizar Configuración
Editar `verificacion_config.php`:
```php
define('SMS_MODE_DESARROLLO', false);
define('TWILIO_ACCOUNT_SID', 'ACxxxxxxxxxxxxx');
define('TWILIO_AUTH_TOKEN', 'tu_token_aqui');
define('WHATSAPP_TEMPLATE_SID', 'HXxxxxxxxxxxxxx');
```

### Paso 4: Probar en Producción
```
http://localhost:8080/php/test_whatsapp.php
```

## ❓ Preguntas Frecuentes

### ¿Por qué no recibo el WhatsApp?
- Verifica que `SMS_MODE_DESARROLLO = true` (modo desarrollo, solo logs)
- Revisa el archivo `php/logs/whatsapp_codigos.log`
- En producción, verifica credenciales de Twilio

### ¿Puedo cambiar el número emisor?
Sí, edita `TELEFONO_EMISOR` en `verificacion_config.php`

### ¿Cuánto cuesta cada mensaje?
Depende de Twilio. WhatsApp es más barato que SMS tradicional.

### ¿Cómo veo los mensajes enviados?
En modo desarrollo: `php/logs/whatsapp_codigos.log`
En producción: Dashboard de Twilio

## 📚 Referencias

- Documentación Twilio WhatsApp: https://www.twilio.com/docs/whatsapp
- API de Twilio: https://www.twilio.com/docs/usage/api
- Guía completa: Ver `CONFIGURAR_WHATSAPP_PASO_A_PASO.md`

## 🆘 Soporte

Si tienes problemas:
1. Revisa `php/logs/whatsapp_codigos.log`
2. Ejecuta `test_whatsapp.php`
3. Verifica `verificar_config.php`
4. Consulta documentación de Twilio
