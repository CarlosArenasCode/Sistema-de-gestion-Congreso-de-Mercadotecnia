# 📱 Servicio de Verificación por WhatsApp

Servicio Node.js con bot-whatsapp que envía códigos de verificación de 6 dígitos por WhatsApp.

## 🚀 Características

- ✅ Envío de códigos de verificación por WhatsApp
- ✅ Formato automático de números de teléfono
- ✅ API REST para integración con PHP
- ✅ Persistencia de sesión de WhatsApp (no requiere escanear QR repetidamente)
- ✅ Logs de envío
- ✅ Contenedor Docker independiente

## 📋 Requisitos Previos

1. **Docker Desktop** instalado y corriendo
2. **Cuenta de WhatsApp** dedicada para el servicio (recomendado: número comercial)
3. **Puerto 3001** disponible

## 🔧 Configuración

### 1. Variables de Entorno

El servicio usa las siguientes variables (configuradas en `.env`):

```env
PORT=3001
WHATSAPP_NUMBER=524492106893  # Tu número de WhatsApp (sin +)
NODE_ENV=production
```

### 2. Número de WhatsApp

El número `+52 449 210 6893` está configurado como **emisor** de los mensajes.

**Importante:** 
- Este número debe tener WhatsApp activo
- Se recomienda usar un número comercial dedicado
- No usar tu número personal

## 🐳 Uso con Docker

### Iniciar el Servicio

El servicio se inicia automáticamente con Docker Compose:

```powershell
docker-compose up -d whatsapp
```

O iniciar todos los servicios:

```powershell
docker-compose up -d
```

### Primera Vez: Escanear Código QR

La **primera vez** que inicies el servicio, necesitas vincular WhatsApp:

1. Ver los logs del contenedor:
```powershell
docker logs -f congreso_whatsapp
```

2. Busca el **código QR** en la consola (aparecerá en formato ASCII)

3. Abre WhatsApp en tu teléfono → **Dispositivos Vinculados** → **Vincular dispositivo**

4. Escanea el código QR de la consola

5. Una vez vinculado, la sesión se guarda y **no necesitarás escanear QR nuevamente**

### Ver Estado del Servicio

```powershell
# Ver logs
docker logs congreso_whatsapp

# Ver logs en tiempo real
docker logs -f congreso_whatsapp

# Verificar que esté corriendo
docker ps | findstr whatsapp
```

## 📡 API Endpoints

### 1. Enviar Código de Verificación

**POST** `/send-verification-code`

Envía un código de verificación por WhatsApp.

**Body (JSON):**
```json
{
  "phone": "+524491234567",
  "code": "123456",
  "name": "Juan Pérez"
}
```

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Código enviado correctamente",
  "phone": "+524491234567",
  "normalized": "524491234567@s.whatsapp.net"
}
```

**Respuesta de error:**
```json
{
  "success": false,
  "error": "Descripción del error"
}
```

### 2. Verificar Estado del Servicio

**GET** `/health`

Verifica si el servicio está funcionando.

**Respuesta:**
```json
{
  "status": "ready",
  "service": "whatsapp-verification",
  "timestamp": "2025-10-14T12:00:00.000Z",
  "phoneNumber": "524492106893"
}
```

Posibles estados:
- `initializing` - Servicio iniciando
- `ready` - Listo para enviar mensajes
- `error` - Error en el servicio

### 3. Enviar Mensaje de Prueba

**POST** `/test-send`

Envía un mensaje de prueba a un número.

**Body (JSON):**
```json
{
  "phone": "+524491234567"
}
```

## 🔗 Integración con PHP

### Uso Básico

```php
<?php
require 'whatsapp_client.php';

// Crear cliente
$client = new WhatsAppClient('http://whatsapp:3001');

// Verificar estado
$health = $client->checkHealth();
if ($health['status'] === 'ready') {
    // Enviar código
    $result = $client->sendVerificationCode(
        '+524491234567',  // Teléfono del usuario
        '123456',         // Código de 6 dígitos
        'Juan Pérez'      // Nombre del usuario
    );
    
    if ($result['success']) {
        echo "✅ Código enviado correctamente";
    } else {
        echo "❌ Error: " . $result['error'];
    }
}
?>
```

### Integración en Registro de Usuario

Ya está integrado en `registrar_usuario.php`:

```php
// Crear cliente WhatsApp
$whatsappClient = new WhatsAppClient('http://whatsapp:3001');

// Enviar código de verificación
$resultWhatsApp = $whatsappClient->sendVerificationCode(
    $telefono,              // Del formulario
    $codigo_verificacion,   // Generado (6 dígitos)
    $nombre_completo        // Del formulario
);
```

## 📱 Formato de Números de Teléfono

El servicio acepta números en diferentes formatos y los normaliza automáticamente:

| Formato Entrada | Formato Normalizado | Para WhatsApp |
|----------------|---------------------|---------------|
| `4491234567` | `524491234567` | `524491234567@s.whatsapp.net` |
| `+524491234567` | `524491234567` | `524491234567@s.whatsapp.net` |
| `52 449 123 4567` | `524491234567` | `524491234567@s.whatsapp.net` |
| `(449) 123-4567` | `524491234567` | `524491234567@s.whatsapp.net` |

**Nota:** Todos los números se normalizan al código de país México (+52).

## 🔍 Solución de Problemas

### El servicio no inicia

```powershell
# Ver logs detallados
docker logs congreso_whatsapp

# Reiniciar el servicio
docker-compose restart whatsapp
```

### No aparece el código QR

```powershell
# Eliminar sesión antigua y reiniciar
docker-compose down
docker volume rm sistema-de-gestion-congreso-de-mercadotecnia_whatsapp_sessions
docker-compose up -d whatsapp
docker logs -f congreso_whatsapp
```

### Los mensajes no se envían

1. **Verificar que WhatsApp esté vinculado:**
   ```powershell
   docker logs congreso_whatsapp | findstr "ready"
   ```
   Debe mostrar: `✅ Bot de WhatsApp iniciado correctamente`

2. **Verificar el número de teléfono del destinatario:**
   - Debe tener WhatsApp activo
   - Debe estar en formato correcto

3. **Verificar conectividad:**
   ```powershell
   docker exec congreso_whatsapp wget -O- http://localhost:3001/health
   ```

### Error "El servicio no está listo"

El servicio está iniciando. Espera 1-2 minutos y verifica el estado:

```powershell
docker logs congreso_whatsapp
```

## 📊 Logs

### Ubicación de Logs

**En el contenedor:**
- `/app/logs/` - Logs de la aplicación

**En PHP:**
- `Proyecto_conectado/php/logs/whatsapp_client.log` - Logs del cliente PHP

### Ver Logs

```powershell
# Logs del contenedor Docker
docker logs congreso_whatsapp

# Logs en tiempo real
docker logs -f congreso_whatsapp

# Últimas 50 líneas
docker logs --tail 50 congreso_whatsapp
```

## 🔐 Seguridad

1. **Sesión de WhatsApp**: Se guarda en un volumen Docker persistente
2. **Número privado**: El número emisor no se expone en el código fuente
3. **Variables de entorno**: Configuración sensible en `.env`
4. **Red Docker**: El servicio solo es accesible dentro de la red Docker

## 🛠️ Desarrollo

### Modo Desarrollo

Para desarrollo con hot-reload, edita `docker-compose.yml`:

```yaml
whatsapp:
  volumes:
    - ./whatsapp-service:/app
    - /app/node_modules  # Excluir node_modules del volumen
```

Luego reinicia:

```powershell
docker-compose restart whatsapp
```

### Estructura del Proyecto

```
whatsapp-service/
├── index.js              # Servidor principal
├── package.json          # Dependencias Node.js
├── Dockerfile           # Imagen Docker
├── .env                 # Variables de entorno
├── .dockerignore        # Archivos a ignorar
└── README.md            # Esta documentación
```

## 📦 Dependencias

- **@bot-whatsapp/bot** - Framework de bot WhatsApp
- **@bot-whatsapp/provider-baileys** - Proveedor para WhatsApp Web
- **express** - Servidor HTTP
- **body-parser** - Parser de JSON
- **cors** - Manejo de CORS
- **dotenv** - Variables de entorno

## 🎯 Flujo Completo

1. Usuario se registra en el formulario web
2. PHP genera código de 6 dígitos
3. PHP guarda usuario en base de datos (no verificado)
4. PHP envía email con el código
5. **PHP envía petición HTTP al servicio WhatsApp** ⬅️
6. Servicio WhatsApp envía mensaje al usuario
7. Usuario ingresa código en el sitio web
8. Sistema verifica el código y activa la cuenta

## 🌐 URLs del Sistema

- **Web App**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **Servicio WhatsApp**: http://localhost:3001
  - Health check: http://localhost:3001/health

## ✅ Checklist de Verificación

Antes de usar en producción:

- [ ] Número de WhatsApp comercial configurado
- [ ] Código QR escaneado y sesión guardada
- [ ] Servicio muestra estado "ready"
- [ ] Mensaje de prueba enviado exitosamente
- [ ] Logs de envío funcionando correctamente
- [ ] Variables de entorno configuradas
- [ ] Red Docker funcionando (comunicación PHP ↔ WhatsApp)

## 📞 Soporte

Si tienes problemas:

1. Revisa los logs: `docker logs congreso_whatsapp`
2. Verifica el estado: `curl http://localhost:3001/health`
3. Revisa esta documentación
4. Contacta al equipo de desarrollo

---

**Autor:** GJA Team  
**Versión:** 1.0.0  
**Fecha:** Octubre 2025  
**Licencia:** MIT
