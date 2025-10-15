# 🚀 Guía Rápida: WhatsApp en Docker

## 📋 Resumen

Sistema de verificación por WhatsApp usando **bot-whatsapp** en un contenedor Docker independiente que se comunica con tu aplicación PHP.

## 🏗️ Arquitectura

```
┌─────────────────┐      HTTP POST      ┌──────────────────┐
│   PHP (Apache)  │ ─────────────────>  │ WhatsApp Service │
│   (Puerto 8080) │                     │   (Puerto 3001)  │
└─────────────────┘                     └──────────────────┘
        │                                         │
        │                                         │
        v                                         v
┌─────────────────┐                     ┌──────────────────┐
│   MySQL DB      │                     │ WhatsApp Web API │
│   (Puerto 3306) │                     │    (Baileys)     │
└─────────────────┘                     └──────────────────┘
```

## 🎯 Flujo de Verificación

1. **Usuario se registra** → PHP genera código de 6 dígitos
2. **PHP guarda en BD** → Usuario no verificado
3. **PHP envía email** → Código por correo
4. **PHP llama API WhatsApp** → `POST http://whatsapp:3001/send-verification-code`
5. **Servicio WhatsApp envía** → Mensaje al usuario
6. **Usuario ingresa código** → Sistema verifica y activa cuenta

## 🐳 Comandos Docker

### Iniciar Todo el Sistema

```powershell
# Iniciar todos los servicios
docker-compose up -d

# Ver estado
docker ps

# Ver logs de WhatsApp
docker logs -f congreso_whatsapp
```

### Iniciar Solo WhatsApp

```powershell
# Iniciar servicio WhatsApp
docker-compose up -d whatsapp

# Reiniciar si hay cambios
docker-compose restart whatsapp

# Detener
docker-compose stop whatsapp
```

### Primera Vez: Escanear QR

```powershell
# Ver logs en tiempo real
docker logs -f congreso_whatsapp

# Busca el código QR en ASCII art
# Ábrelo con WhatsApp → Dispositivos Vinculados → Vincular dispositivo
# Escanea el QR

# Una vez vinculado, verás:
# ✅ Bot de WhatsApp iniciado correctamente
```

### Solución de Problemas

```powershell
# Eliminar sesión y reiniciar (si hay problemas de conexión)
docker-compose down
docker volume rm sistema-de-gestion-congreso-de-mercadotecnia_whatsapp_sessions
docker volume rm sistema-de-gestion-congreso-de-mercadotecnia_whatsapp_cache
docker-compose up -d whatsapp

# Ver logs detallados
docker logs --tail 100 congreso_whatsapp

# Verificar salud del servicio
docker exec congreso_web curl http://whatsapp:3001/health
```

## 📁 Archivos Creados

### Servicio WhatsApp (Node.js)

```
whatsapp-service/
├── index.js              # Servidor Express + bot-whatsapp
├── package.json          # Dependencias Node.js
├── Dockerfile           # Imagen Docker del servicio
├── .env                 # Variables de entorno
├── .dockerignore        # Archivos a ignorar
└── README.md            # Documentación completa
```

### Cliente PHP

```
Proyecto_conectado/php/
├── whatsapp_client.php        # Cliente para comunicarse con servicio
└── test_whatsapp_docker.php   # Dashboard de pruebas
```

### Docker

```
docker-compose.yml        # Configuración actualizada con servicio WhatsApp
```

## 🔌 Uso en PHP

### Ejemplo Básico

```php
<?php
require 'whatsapp_client.php';

// Crear cliente
$client = new WhatsAppClient('http://whatsapp:3001');

// Verificar estado
$health = $client->checkHealth();

if ($health['status'] === 'ready') {
    // Enviar código de verificación
    $result = $client->sendVerificationCode(
        '+524491234567',  // Teléfono del usuario
        '123456',         // Código de 6 dígitos
        'Juan Pérez'      // Nombre (opcional)
    );
    
    if ($result['success']) {
        echo "✅ Código enviado correctamente";
    } else {
        echo "❌ Error: " . $result['error'];
    }
} else {
    echo "⚠️ Servicio no disponible: " . $health['status'];
}
?>
```

### Ya Integrado en `registrar_usuario.php`

El código ya está integrado automáticamente:

```php
// Crear cliente WhatsApp
$whatsappClient = new WhatsAppClient('http://whatsapp:3001');

// Enviar código
$resultWhatsApp = $whatsappClient->sendVerificationCode(
    $telefono,
    $codigo_verificacion,
    $nombre_completo
);
```

## 🧪 Probar el Sistema

### 1. Panel de Pruebas Web

Abre en tu navegador:

```
http://localhost:8080/Proyecto_conectado/php/test_whatsapp_docker.php
```

Este panel te permite:
- ✅ Ver estado del servicio
- ✅ Enviar mensajes de prueba
- ✅ Ver información de integración
- ✅ Verificar configuración

### 2. Prueba desde Terminal

```powershell
# Verificar salud
curl http://localhost:3001/health

# Enviar mensaje de prueba
curl -X POST http://localhost:3001/test-send `
  -H "Content-Type: application/json" `
  -d '{\"phone\":\"+524491234567\"}'
```

### 3. Prueba Completa de Registro

1. Ve a: `http://localhost:8080/Proyecto_conectado/Front-end/registro_usuario.html`
2. Llena el formulario con tu número
3. Envía el formulario
4. Deberías recibir:
   - ✉️ Email con código
   - 📱 WhatsApp con código

## 📊 Verificar Estado

### Estado de Servicios

```powershell
# Ver todos los contenedores
docker ps

# Deberías ver:
# - congreso_web (PHP)
# - congreso_db (MySQL)
# - congreso_phpmyadmin
# - congreso_whatsapp (NUEVO!)
```

### Logs en Tiempo Real

```powershell
# WhatsApp
docker logs -f congreso_whatsapp

# PHP/Apache
docker logs -f congreso_web

# MySQL
docker logs -f congreso_db
```

### Verificar Comunicación

```powershell
# Desde contenedor PHP, probar conexión a WhatsApp
docker exec congreso_web curl http://whatsapp:3001/health
```

## 🌐 URLs del Sistema

| Servicio | URL | Descripción |
|----------|-----|-------------|
| Web App | http://localhost:8080 | Aplicación principal |
| phpMyAdmin | http://localhost:8081 | Gestión de BD |
| WhatsApp API | http://localhost:3001 | Servicio WhatsApp |
| Health Check | http://localhost:3001/health | Estado del servicio |
| Panel Pruebas | http://localhost:8080/Proyecto_conectado/php/test_whatsapp_docker.php | Dashboard |

## 📱 Configuración del Número

El número **+52 449 210 6893** está configurado como **emisor** de mensajes.

**Importante:**
- Debe tener WhatsApp activo
- Primera vez: escanear código QR
- La sesión se guarda en volumen Docker
- No necesitas re-escanear después

## ⚙️ Variables de Entorno

En `whatsapp-service/.env`:

```env
PORT=3001
WHATSAPP_NUMBER=524492106893
NODE_ENV=production
```

## 🔐 Seguridad

- ✅ Sesión de WhatsApp en volumen persistente
- ✅ Servicio solo accesible dentro de red Docker
- ✅ Variables sensibles en `.env`
- ✅ No expone número en código fuente

## 🆘 Solución de Problemas Comunes

### 1. "El servicio no está listo"

**Causa:** Servicio iniciando o no vinculado a WhatsApp

**Solución:**
```powershell
docker logs congreso_whatsapp
# Busca el código QR y escanealo
```

### 2. "Error de conexión"

**Causa:** Docker no está corriendo o servicio caído

**Solución:**
```powershell
docker ps
docker-compose up -d whatsapp
```

### 3. "Sesión expirada"

**Causa:** Sesión de WhatsApp desvinculada

**Solución:**
```powershell
# Eliminar sesión antigua
docker-compose down
docker volume rm sistema-de-gestion-congreso-de-mercadotecnia_whatsapp_sessions
docker-compose up -d whatsapp
# Escanear nuevo QR
```

### 4. "No llegan los mensajes"

**Causas posibles:**
- Número de destino sin WhatsApp
- Número mal formateado
- Servicio no vinculado

**Solución:**
```powershell
# Verificar estado
curl http://localhost:3001/health

# Probar con panel de pruebas
# http://localhost:8080/Proyecto_conectado/php/test_whatsapp_docker.php
```

## 📚 Documentación Adicional

- **README completo del servicio:** `whatsapp-service/README.md`
- **Documentación del cliente PHP:** Ver comentarios en `whatsapp_client.php`
- **Panel de pruebas:** `test_whatsapp_docker.php`

## ✅ Checklist de Verificación

Antes de usar en producción:

- [ ] Docker Desktop instalado y corriendo
- [ ] Servicios iniciados: `docker-compose up -d`
- [ ] Código QR escaneado (primera vez)
- [ ] Estado "ready": `curl http://localhost:3001/health`
- [ ] Mensaje de prueba enviado exitosamente
- [ ] Registro de usuario funciona completamente
- [ ] Logs sin errores: `docker logs congreso_whatsapp`

## 🎉 ¡Listo!

Ahora tienes un sistema completo de verificación por WhatsApp integrado con Docker.

**Siguiente paso:** Probar el registro completo de un usuario y verificar que reciba el código por WhatsApp.

---

**Creado por:** GJA Team  
**Fecha:** Octubre 2025  
**Stack:** Docker + Node.js + bot-whatsapp + PHP + MySQL
