# 📧📱 Sistema de Verificación por Email y WhatsApp

## Descripción

El sistema de gestión del Congreso de Mercadotecnia ahora cuenta con un **sistema dual de verificación** que envía códigos de verificación tanto por **correo electrónico (Gmail)** como por **WhatsApp**.

---

## 🔄 Flujo de Verificación

### 1. Registro de Usuario
Cuando un usuario se registra en el sistema:

1. **Se genera un código de 6 dígitos** aleatorio
2. **Se almacena en la base de datos** junto con la fecha/hora de creación
3. **Se envía automáticamente por dos canales:**
   - ✉️ **Email** a través de Gmail SMTP
   - 📱 **WhatsApp** a través del servicio Node.js en Docker

### 2. Reenvío de Código
Si el usuario no recibe el código, puede solicitar un reenvío:

- El sistema verifica que haya pasado al menos **1 minuto** desde el último envío
- Genera un **nuevo código** (el anterior se invalida)
- Envía nuevamente por **Email y WhatsApp**
- Resetea el contador de **intentos fallidos**

### 3. Verificación
El usuario ingresa el código recibido:
- Máximo **3 intentos** permitidos
- Código válido por **15 minutos**
- Después de 3 intentos fallidos, debe solicitar un nuevo código

---

## 📁 Archivos Modificados/Creados

### Archivos PHP Principales

#### 1. `registrar_usuario.php`
**Función:** Maneja el registro inicial y primer envío de código

**Cambios realizados:**
- ✅ Integración con Gmail SMTP para envío de emails
- ✅ Integración con servicio WhatsApp Docker
- ✅ Logs detallados de cada envío
- ✅ Manejo de errores robusto
- ✅ Verificación de estado del servicio WhatsApp antes de enviar

**Fragmento clave:**
```php
// Envío por Email
$emailEnviado = send_email($email, $asunto, $mensaje_email, 'Congreso de Mercadotecnia UAA');

// Envío por WhatsApp
$whatsappClient = new WhatsAppClient('http://whatsapp:3001');
$healthCheck = $whatsappClient->checkHealth();

if ($healthCheck['status'] === 'ready') {
    $resultWhatsApp = $whatsappClient->sendVerificationCode(
        $telefono, 
        $codigo_verificacion, 
        $nombre_completo
    );
}
```

#### 2. `reenviar_codigo.php`
**Función:** Reenvía el código de verificación

**Cambios realizados:**
- ✅ Reemplazó servicio SMS por WhatsApp
- ✅ Validación de límite de tiempo (1 minuto)
- ✅ Generación de nuevo código
- ✅ Envío dual (Email + WhatsApp)
- ✅ Respuesta JSON con métodos exitosos

#### 3. `whatsapp_client.php`
**Función:** Cliente PHP para comunicarse con el servicio WhatsApp

**Métodos principales:**
- `sendVerificationCode($phone, $code, $name)` - Enviar código
- `checkHealth()` - Verificar estado del servicio
- `sendTest($phone)` - Enviar mensaje de prueba

#### 4. `send_notifications.php`
**Función:** Maneja el envío de emails con PHPMailer

**Configuración actual:**
- Host: `smtp.gmail.com`
- Puerto: `587`
- Seguridad: `TLS`
- Usuario: `mercadotecnia.congreso@gmail.com`

---

## ⚙️ Configuración

### Configuración de Email (Gmail)

Archivo: `smtp_config.php`

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'mercadotecnia.congreso@gmail.com');
define('SMTP_PASS', 'dodjeovfvscljvly'); // App Password
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

### Configuración de WhatsApp (Docker)

El servicio WhatsApp corre en un contenedor Docker separado:

**docker-compose.yml:**
```yaml
whatsapp:
  build: ./whatsapp-service
  container_name: congreso_whatsapp
  ports:
    - "3001:3001"
  environment:
    - PORT=3001
    - WHATSAPP_NUMBER=524492106893
```

**Acceso al servicio:**
- URL interna: `http://whatsapp:3001`
- URL externa: `http://localhost:3001`

---

## 🚀 Uso

### Iniciar Servicios

```bash
# Iniciar todos los contenedores
docker-compose up -d

# Verificar estado
docker-compose ps

# Ver logs del servicio WhatsApp
docker-compose logs whatsapp
```

### Vincular WhatsApp

1. Accede a `http://localhost:3001`
2. Escanea el código QR con WhatsApp en tu teléfono
3. El servicio quedará vinculado y listo para enviar mensajes

### Probar Envío

#### Probar Email:
```
http://localhost:8080/php/test_send_mail.php?to=tucorreo@gmail.com
```

#### Probar WhatsApp:
```
http://localhost:3001
```
(Usa el formulario en la interfaz)

---

## 📊 Logs y Monitoreo

### Logs de Registro
Los logs se escriben en el error log de PHP:

```bash
# Ver logs del contenedor web
docker-compose logs web

# Filtrar solo registros
docker-compose logs web | grep "\[REGISTRO\]"

# Ver logs en tiempo real
docker-compose logs -f web
```

### Formato de Logs

```
[REGISTRO] Intentando enviar código por email a: usuario@ejemplo.com
[REGISTRO] ✅ Código enviado exitosamente por email a: usuario@ejemplo.com
[REGISTRO] Intentando enviar código por WhatsApp a: +524491234567
[REGISTRO] ✅ Código enviado exitosamente por WhatsApp a: +524491234567
[REGISTRO] 📧 Código 123456 enviado a Juan Pérez por: Email y WhatsApp
```

```
[REENVIO] Nuevo código generado para usuario@ejemplo.com: 654321
[REENVIO] ✅ Código enviado por email a: usuario@ejemplo.com
[REENVIO] ✅ Código enviado por WhatsApp a: +524491234567
```

---

## 🔧 Solución de Problemas

### Email no se envía

1. **Verificar configuración SMTP:**
   ```bash
   docker exec congreso_web_oracle php /var/www/html/php/test_email_debug.php?to=test@gmail.com
   ```

2. **Verificar contraseña de aplicación:**
   - Debe ser generada desde: https://myaccount.google.com/apppasswords
   - NO es la contraseña normal de Gmail

3. **Verificar logs:**
   ```bash
   docker exec congreso_web_oracle cat /var/www/html/php/smtp_debug.log
   ```

### WhatsApp no se envía

1. **Verificar estado del servicio:**
   ```bash
   curl http://localhost:3001/health
   ```

2. **Verificar que esté vinculado:**
   - Accede a `http://localhost:3001`
   - Debe mostrar "WhatsApp Conectado"

3. **Revisar logs:**
   ```bash
   docker-compose logs whatsapp
   ```

4. **Reiniciar servicio:**
   ```bash
   docker-compose restart whatsapp
   ```

### El código no llega

1. **Verificar que el número tenga formato correcto:**
   - Debe incluir código de país: `+524491234567`
   - Para México: `+52` + `1` + 10 dígitos

2. **Verificar en logs que se haya enviado:**
   ```bash
   docker-compose logs web | grep "Código.*enviado"
   ```

3. **Solicitar reenvío:**
   - Esperar 1 minuto
   - Usar el botón "Reenviar código" en la interfaz

---

## 📝 Notas Importantes

### Seguridad
- ✅ Códigos expiran en **15 minutos**
- ✅ Máximo **3 intentos** de verificación
- ✅ Límite de **reenvíos** (1 por minuto)
- ✅ Contraseñas de aplicación (no contraseñas reales)

### Límites
- **Email:** Sin límite específico (Gmail permite ~500 por día)
- **WhatsApp:** Depende de la cuenta vinculada
- **Reenvíos:** 1 por minuto por usuario

### Fallback
Si un método falla, el otro sigue funcionando:
- Si WhatsApp falla → el código se envía por Email
- Si Email falla → el código se envía por WhatsApp
- El registro NO falla si algún método no está disponible

---

## 🎯 Próximas Mejoras

- [ ] Implementar cola de mensajes para envíos masivos
- [ ] Dashboard de monitoreo de envíos
- [ ] Estadísticas de tasa de entrega
- [ ] Plantillas personalizables para mensajes
- [ ] Soporte para múltiples idiomas
- [ ] Notificaciones push como tercer canal

---

## 👥 Contacto

Para soporte técnico:
- Email: mercadotecnia.congreso@gmail.com
- WhatsApp: +52 449 210 6893

---

**Última actualización:** 10 de noviembre de 2025
