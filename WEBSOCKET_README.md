# 🔌 Sistema de WebSocket en Tiempo Real

## 📋 Descripción General

Este proyecto implementa un **sistema de notificaciones en tiempo real** utilizando **WebSocket (Socket.IO)** para transmitir actualizaciones instantáneas de asistencia a eventos sin necesidad de refrescar la página.

---

## 🏗️ Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────────┐
│                     FLUJO DE ASISTENCIA                         │
└─────────────────────────────────────────────────────────────────┘

1. Admin escanea QR → admin_scan_qr.html
         ↓
2. POST → /php_admin/registrar_asistencia.php
         ↓
3. Se guarda en Oracle DB
         ↓
4. PHP notifica → POST /notify-attendance (Node.js:3001)
         ↓
5. Socket.IO emite eventos → WebSocket
         ↓
    ┌────────────────┬──────────────────┬──────────────────┐
    ↓                ↓                  ↓                  ↓
Admin Dashboard  Admin Scanner    Dashboard Alumno   Otros clientes
(estadísticas)   (confirmación)   (notificación)     (broadcast)
```

---

## 🚀 Componentes Implementados

### 1. **Servidor WebSocket** (`whatsapp-service/index.js`)

**Puerto:** 3001  
**Tecnología:** Node.js + Express + Socket.IO

#### Endpoints HTTP:
- `POST /notify-attendance` - Recibe notificaciones desde PHP
- `GET /health` - Estado del servicio (incluye info WebSocket)
- `GET /stats` - Estadísticas en tiempo real

#### Eventos WebSocket:
**Cliente → Servidor:**
- `join:admin` - Unirse a sala de administradores
- `join:user` - Unirse a sala de usuario específico
- `join:event` - Unirse a sala de evento específico
- `request:stats` - Solicitar estadísticas actuales

**Servidor → Cliente:**
- `connection:established` - Confirmación de conexión
- `attendance:registered` - Nueva asistencia (broadcast a todos)
- `attendance:confirmed` - Confirmación para usuario específico
- `attendance:event:update` - Actualización para evento específico
- `attendance:admin:update` - Actualización para administradores
- `stats:update` - Actualización de estadísticas

---

### 2. **Cliente WebSocket** (`js/websocket-client.js`)

Clase JavaScript reutilizable que maneja:
- ✅ Conexión/reconexión automática
- ✅ Manejo de eventos personalizados
- ✅ Notificaciones visuales en el navegador
- ✅ Sistema de salas (rooms) para eventos/usuarios
- ✅ Estadísticas en tiempo real
- ✅ Manejo de errores y timeouts

**Uso básico:**
```javascript
const ws = new AttendanceWebSocket({
    serverUrl: 'http://localhost:3001',
    debug: true
});

ws.connect();
ws.on('connected', () => {
    ws.joinAdmin(); // o ws.joinUser(userId)
});

ws.on('attendance:registered', (data) => {
    console.log('Nueva asistencia:', data);
});
```

---

### 3. **Integración PHP** (`php_admin/registrar_asistencia.php`)

Función que notifica al WebSocket después de registrar asistencia:

```php
function notifyWebSocket($data) {
    $websocket_url = 'http://whatsapp:3001/notify-attendance';
    
    $ch = curl_init($websocket_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    
    curl_exec($ch);
    curl_close($ch);
}
```

---

### 4. **Páginas con WebSocket Integrado**

#### 📱 **admin_dashboard.html**
- Muestra estadísticas en tiempo real
- Actualiza contadores automáticamente
- Indicador de conexión WebSocket

#### 📸 **admin_scan_qr.html**
- Recibe confirmación inmediata al escanear QR
- Animaciones visuales de éxito/error
- Feedback en tiempo real

#### 👤 **dashboard_alumno.html**
- Recibe notificación cuando su QR es escaneado
- Alerta visual + notificación del navegador
- Confirmación de asistencia instantánea

---

## 🔧 Instalación y Configuración

### 1. Instalar dependencias de Node.js

```bash
cd whatsapp-service
npm install
```

Esto instalará:
- `socket.io` (v4.7.2) - Servidor WebSocket
- `express` - Servidor HTTP
- Otras dependencias existentes

### 2. Iniciar el servidor WebSocket

**Opción A: Docker (Recomendado)**
```bash
docker-compose up -d
```

El servicio `whatsapp` ya incluye Socket.IO y se inicia automáticamente.

**Opción B: Manual (para desarrollo)**
```bash
cd whatsapp-service
node index.js
```

### 3. Verificar que está funcionando

Abre el navegador en:
```
http://localhost:3001/health
```

Deberías ver:
```json
{
  "status": "ready",
  "websocket": {
    "enabled": true,
    "connectedClients": 0
  }
}
```

---

## 📊 Pruebas del Sistema

### Prueba 1: Conexión WebSocket

1. Abre `admin_dashboard.html`
2. Abre la consola del navegador (F12)
3. Deberías ver:
```
[WebSocket] Conectando a http://localhost:3001...
[WebSocket] ✅ Conectado al servidor WebSocket
[WebSocket] ✅ Unido a sala de administradores
```

### Prueba 2: Asistencia en Tiempo Real

1. Abre dos ventanas del navegador:
   - Ventana 1: `admin_scan_qr.html`
   - Ventana 2: `admin_dashboard.html`

2. En ventana 1, escanea un QR o ingresa código manual

3. En ventana 2, verás:
   - Estadísticas actualizándose automáticamente
   - Notificación visual en esquina superior derecha
   - Animación de actualización

### Prueba 3: Notificación de Alumno

1. Abre `dashboard_alumno.html` (con sesión de alumno)
2. Escanea el QR de ese alumno en `admin_scan_qr.html`
3. El alumno recibirá:
   - Alerta en pantalla
   - Notificación del navegador (si dio permiso)
   - Mensaje en consola

---

## 🔍 Debugging

### Ver logs del servidor WebSocket

**Docker:**
```bash
docker logs -f congreso_whatsapp
```

**Manual:**
Verás los logs directamente en la terminal donde ejecutaste `node index.js`

### Logs esperados al registrar asistencia:

```
🔌 Cliente conectado: abc123xyz
📍 Cliente abc123xyz se unió al evento 5
✅ Asistencia notificada vía WebSocket: Juan Pérez (AL123456) - Conferencia Marketing
```

### Problemas comunes:

#### ❌ "WebSocket no conecta"
- Verifica que el servidor Node.js esté corriendo en puerto 3001
- Revisa que no haya bloqueadores de CORS
- Comprueba la URL en `websocket-client.js`

#### ❌ "PHP no notifica al WebSocket"
- Verifica que el servicio `whatsapp` esté corriendo
- Revisa que `curl` esté habilitado en PHP
- Comprueba los logs de PHP: `tail -f php_error.log`

#### ❌ "Cliente conecta pero no recibe eventos"
- Verifica que se haya unido a la sala correcta (`joinAdmin`, `joinUser`)
- Comprueba que el evento esté siendo emitido desde el servidor
- Revisa la consola del navegador

---

## 📈 Estadísticas en Tiempo Real

El sistema mantiene las siguientes estadísticas:

```javascript
{
  totalAttendance: 0,      // Total de asistencias registradas
  activeEvents: 0,         // Eventos activos
  connectedClients: 0,     // Clientes WebSocket conectados
  lastUpdate: "2025-11-18T..."  // Última actualización
}
```

Estas se actualizan automáticamente en `admin_dashboard.html`.

---

## 🔒 Seguridad

### CORS (Cross-Origin Resource Sharing)
Actualmente configurado para aceptar todas las conexiones (`origin: "*"`).

**Para producción, cambiar a:**
```javascript
io = new Server(server, {
    cors: {
        origin: ["http://tudominio.com", "http://localhost:8081"],
        methods: ["GET", "POST"]
    }
});
```

### Autenticación
Actualmente no hay autenticación en WebSocket. Para mejorar:
1. Enviar token de sesión al conectar
2. Validar token en el servidor
3. Solo permitir unirse a salas autorizadas

---

## 🚀 Características Futuras (Opcional)

1. **Persistencia de eventos** - Guardar eventos en Redis/MongoDB
2. **Historial de asistencias** - Ver asistencias pasadas en tiempo real
3. **Chat en vivo** - Comunicación admin ↔ alumno
4. **Notificaciones push** - Integrar con service workers
5. **Gráficas en tiempo real** - Chart.js con datos live
6. **Lista de asistentes en vivo** - Ver quién entró al evento

---

## 📚 Referencias

- [Socket.IO Documentation](https://socket.io/docs/v4/)
- [WebSocket MDN](https://developer.mozilla.org/en-US/docs/Web/API/WebSocket)
- [Notification API](https://developer.mozilla.org/en-US/docs/Web/API/Notification)

---

## ✅ Cumplimiento del Requisito Académico

**Requisito:** Conexión a sockets (6.25%) - Utilizar al menos 1 socket y explicar su funcionamiento.

**Implementación:**
✅ **Socket.IO implementado** en puerto 3001  
✅ **Múltiples eventos bidireccionales** (8+ eventos)  
✅ **Salas (rooms)** para segmentación de usuarios  
✅ **Integración completa** PHP ↔ Node.js ↔ HTML  
✅ **Documentación detallada** de funcionamiento  

**Funcionamiento:**
El sistema usa WebSocket para notificaciones en tiempo real. Cuando un administrador escanea un código QR para registrar asistencia, el servidor PHP guarda en la base de datos y notifica al servidor Node.js vía HTTP POST. El servidor Node.js emite eventos WebSocket que son recibidos instantáneamente por todos los clientes conectados (admins y alumnos), actualizando la interfaz sin necesidad de refrescar la página.

---

**Autor:** Sistema de Gestión - Congreso de Mercadotecnia  
**Fecha:** Noviembre 2025  
**Versión:** 1.0.0
