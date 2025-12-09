# 📋 CUMPLIMIENTO DE REQUISITOS - Sistema de Gestión Congreso de Mercadotecnia

## ✅ 1. Despliegue de Servidor Web Local usando Apache o Nginx (6.25%)

### Implementación
**Servidor:** Apache/2.4.62 (Debian)  
**Tecnología:** Docker + Apache  
**Puerto:** 8081

### Evidencia

#### Dockerfile.oracle (Configuración Apache)
```dockerfile
FROM php:8.2-apache

# Extensiones PHP
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip
RUN a2enmod rewrite headers

# Configuración Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/Proyecto_conectado
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
```

#### docker-compose.yml (Servicio Web)
```yaml
web:
  build:
    context: .
    dockerfile: Dockerfile.oracle
  container_name: congreso_web_oracle
  ports:
    - "8081:80"  # Apache escuchando en puerto 80 interno, expuesto en 8081
  volumes:
    - ./Proyecto_conectado:/var/www/html
  environment:
    APACHE_DOCUMENT_ROOT: /var/www/html
```

### Verificación del Servidor
```bash
# Verificar versión de Apache
$ docker exec congreso_web_oracle apache2 -v
Server version: Apache/2.4.62 (Debian)
Server built:   2024-10-04T15:21:08

# Estado del servidor
$ curl http://localhost:8081/
HTTP/1.1 200 OK
```

### Funcionalidades Desplegadas
- ✅ **Front-end:** HTML5, CSS3, JavaScript (ES6+)
  - Dashboard de Alumnos
  - Dashboard de Administradores
  - Panel de Registro y Login
  - Sistema de Verificación de Códigos
  
- ✅ **Back-end:** PHP 8.2.29
  - 161 archivos PHP funcionales
  - Controladores MVC
  - APIs REST
  - Conexión a Oracle Database

**⚠️ Nota:** No se utiliza XAMPP ni WAMP. Todo el proyecto funciona mediante Docker con Apache nativo.

---

## ✅ 2. Utilizar APIs - Mínimo 5 Endpoints Funcionales (6.25%)

### APIs Implementadas

El proyecto cuenta con **más de 30 endpoints funcionales**. A continuación los 5 principales:

### 📍 Endpoint 1: Autenticación de Usuarios
**Archivo:** `php/login.php`  
**Método:** POST  
**URL:** `http://localhost:8081/php/login.php`

**Descripción:** Autenticación de usuarios y administradores con validación de contraseña hasheada.

**Request:**
```http
POST /php/login.php
Content-Type: application/x-www-form-urlencoded

university_id=512456&password=contraseña123
```

**Response:**
```json
{
  "success": true,
  "message": "Inicio de sesión exitoso",
  "redirectUrl": "/Front-end/dashboard_alumno.html",
  "userData": {
    "id": 1,
    "nombre": "Angel Ivan",
    "email": "anelkauri@gmail.com",
    "matricula": "512456",
    "rol": "alumno"
  },
  "token": "a1b2c3d4e5f6..."
}
```

---

### 📍 Endpoint 2: Listar Eventos Disponibles
**Archivo:** `php/listar_eventos.php`  
**Método:** GET  
**URL:** `http://localhost:8081/php/listar_eventos.php`

**Descripción:** Obtiene lista de eventos activos del congreso con detalles completos.

**Response:**
```json
{
  "success": true,
  "eventos": [
    {
      "id_evento": 1,
      "nombre_evento": "Conferencia de Marketing Digital",
      "descripcion": "Tendencias actuales en marketing digital",
      "fecha": "2025-12-15",
      "hora_inicio": "09:00:00",
      "hora_fin": "11:00:00",
      "ubicacion": "Auditorio Principal",
      "cupo_maximo": 150,
      "inscritos": 87
    }
  ],
  "total": 2
}
```

---

### 📍 Endpoint 3: Inscripción a Eventos
**Archivo:** `php/inscribir_evento.php`  
**Método:** POST  
**URL:** `http://localhost:8081/php/inscribir_evento.php`

**Descripción:** Permite a un usuario inscribirse a un evento específico.

**Request:**
```json
{
  "id_evento": 1,
  "id_usuario": 5
}
```

**Response:**
```json
{
  "success": true,
  "message": "Inscripción exitosa al evento",
  "inscripcion_id": 42,
  "evento": "Conferencia de Marketing Digital"
}
```

---

### 📍 Endpoint 4: Verificar Código de Verificación
**Archivo:** `php/verificar_codigo.php`  
**Método:** POST  
**URL:** `http://localhost:8081/php/verificar_codigo.php`

**Descripción:** Verifica el código de 6 dígitos enviado por WhatsApp/Email.

**Request:**
```http
POST /php/verificar_codigo.php
Content-Type: application/x-www-form-urlencoded

email=usuario@ejemplo.com&digit1=3&digit2=0&digit3=4&digit4=1&digit5=7&digit6=6
```

**Response:**
```json
{
  "success": true,
  "verified": true,
  "message": "Cuenta verificada exitosamente",
  "redirectUrl": "/Front-end/login.html"
}
```

---

### 📍 Endpoint 5: Registro de Asistencia
**Archivo:** `php_admin/registrar_asistencia.php`  
**Método:** POST  
**URL:** `http://localhost:8081/php_admin/registrar_asistencia.php`

**Descripción:** Registra la asistencia de un alumno mediante código QR.

**Request:**
```http
POST /php_admin/registrar_asistencia.php
Content-Type: application/x-www-form-urlencoded

evento=1&qr_data=4bfb40d7-b43f-4308-88f9-883d620532f5&tipo_registro=entrada
```

**Response:**
```json
{
  "success": true,
  "message": "Asistencia de entrada registrada correctamente",
  "usuario": {
    "nombre": "Angel Ivan",
    "matricula": "345",
    "evento": "Conferencia de Marketing Digital"
  },
  "timestamp": "2025-12-09T16:45:32.000Z"
}
```

---

### 🔗 Endpoints Adicionales (30+ en total)

#### Gestión de Usuarios
- `GET /php/usuario.php` - Obtener datos del usuario en sesión
- `POST /php/registrar_usuario.php` - Registro de nuevos usuarios
- `POST /php/recuperar_pass.php` - Recuperación de contraseña
- `GET /php/verificar_sesion.php` - Validar sesión activa

#### Gestión de Eventos (Admin)
- `GET /php_admin/eventos_controller.php?action=listar` - Listar todos los eventos
- `POST /php_admin/eventos_controller.php?action=crear` - Crear nuevo evento
- `POST /php_admin/eventos_controller.php?action=editar` - Editar evento
- `DELETE /php_admin/eventos_controller.php?action=eliminar` - Eliminar evento

#### Constancias
- `GET /php/constancias_usuario.php` - Constancias del usuario
- `POST /php_admin/generar_constancia.php` - Generar constancia PDF
- `POST /php/generar_constancias_automaticas.php` - Generación masiva

#### Personalización
- `GET /php/obtener_personalizacion.php?action=get_all` - Obtener configuración
- `POST /php_admin/personalizacion_controller.php` - Actualizar personalización

---

## ✅ 3. Conexión a Sockets - Mínimo 1 Socket Funcional (6.25%)

### Implementación WebSocket

**Tecnología:** Socket.IO v4  
**Servidor:** Node.js + Express (Puerto 3001)  
**Archivo Principal:** `whatsapp-service/index.js`

### Arquitectura WebSocket

```
┌──────────────────────────────────────────────────┐
│          SERVIDOR WEBSOCKET (Node.js)            │
│              Puerto: 3001                        │
└──────────────────────────────────────────────────┘
                      │
        ┌─────────────┼─────────────┐
        ▼             ▼             ▼
   Dashboard      Scanner QR    Dashboard
    Admin          Admin         Alumno
```

### Configuración del Servidor

**whatsapp-service/index.js:**
```javascript
const { Server } = require('socket.io');
const http = require('http');

const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

// Eventos del WebSocket
io.on('connection', (socket) => {
    console.log(`🔌 Cliente conectado: ${socket.id}`);
    
    // Evento: Cliente se conecta
    socket.emit('connection:established', {
        socketId: socket.id,
        timestamp: new Date().toISOString()
    });

    // Evento: Unirse a sala de admin
    socket.on('join:admin', () => {
        socket.join('admins');
        console.log(`👤 Admin ${socket.id} conectado`);
    });

    // Evento: Unirse a sala de evento
    socket.on('join:event', (eventId) => {
        socket.join(`event_${eventId}`);
        console.log(`📍 Cliente ${socket.id} se unió al evento ${eventId}`);
    });

    // Evento: Desconexión
    socket.on('disconnect', () => {
        console.log(`🔌 Cliente desconectado: ${socket.id}`);
    });
});
```

### Endpoint para Notificaciones en Tiempo Real

**POST /notify-attendance:**
```javascript
app.post('/notify-attendance', (req, res) => {
    const { id_usuario, id_evento, nombre_completo, matricula, nombre_evento } = req.body;

    const attendanceData = {
        id_usuario,
        id_evento,
        nombre_completo,
        matricula,
        nombre_evento,
        timestamp: new Date().toISOString()
    };

    // Emitir a TODOS los clientes conectados
    io.emit('attendance:registered', attendanceData);

    // Emitir solo al evento específico
    io.to(`event_${id_evento}`).emit('attendance:event:update', attendanceData);

    // Emitir solo a administradores
    io.to('admins').emit('attendance:admin:update', {
        ...attendanceData,
        stats: realtimeStats
    });

    res.json({ success: true, message: 'Notificación enviada vía WebSocket' });
});
```

### Cliente WebSocket

**Proyecto_conectado/js/websocket-client.js:**
```javascript
class AttendanceWebSocket {
    constructor(options = {}) {
        this.serverUrl = options.serverUrl || 'http://localhost:3001';
        this.socket = null;
        this.connected = false;
    }

    connect() {
        this.socket = io(this.serverUrl, {
            transports: ['websocket', 'polling'],
            reconnection: true,
            reconnectionDelay: 1000,
            reconnectionAttempts: 5
        });

        // Eventos del cliente
        this.socket.on('connect', () => {
            console.log('✅ Conectado al servidor WebSocket');
            this.connected = true;
        });

        this.socket.on('attendance:registered', (data) => {
            console.log('📢 Nueva asistencia registrada:', data);
            this.showNotification(data);
        });

        this.socket.on('attendance:admin:update', (data) => {
            console.log('📊 Actualización para admin:', data);
            this.updateDashboard(data);
        });
    }

    joinAdmin() {
        this.socket.emit('join:admin');
    }

    joinEvent(eventId) {
        this.socket.emit('join:event', eventId);
    }
}
```

### Funcionamiento en la Práctica

#### Flujo de Asistencia en Tiempo Real:

1. **Admin escanea QR** en `admin_scan_qr.html`
2. **PHP registra en Oracle:** `php_admin/registrar_asistencia.php`
3. **PHP notifica WebSocket:**
   ```php
   $ch = curl_init('http://whatsapp:3001/notify-attendance');
   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
   $response = curl_exec($ch);
   ```
4. **WebSocket emite eventos** a todos los clientes conectados
5. **Dashboards se actualizan automáticamente** sin refrescar la página

### Eventos Implementados

**Del Cliente al Servidor:**
- `join:admin` - Unirse a sala de administradores
- `join:user` - Unirse a sala de usuario específico
- `join:event` - Unirse a sala de evento
- `request:stats` - Solicitar estadísticas

**Del Servidor al Cliente:**
- `connection:established` - Confirmación de conexión
- `attendance:registered` - Nueva asistencia (broadcast)
- `attendance:confirmed` - Confirmación individual
- `attendance:event:update` - Actualización por evento
- `attendance:admin:update` - Actualización para admins
- `stats:update` - Estadísticas en tiempo real

### Prueba del WebSocket

**Verificar estado:**
```bash
curl http://localhost:3001/health
```

**Respuesta:**
```json
{
  "status": "ready",
  "authenticated": true,
  "service": "whatsapp-verification",
  "websocket": {
    "enabled": true,
    "connectedClients": 3
  }
}
```

**Página de prueba:**
```
http://localhost:8081/Front-end/test_websocket.html
```

### Documentación Completa
- `WEBSOCKET_README.md` - Documentación técnica detallada
- `PRUEBAS_WEBSOCKET.md` - Guía de pruebas paso a paso

---

## ✅ 4. Respaldo con Cron - Cada 5 Minutos (6.25%)

### Implementación del Cron

**Tecnología:** Cron (Linux Cron Daemon)  
**Frecuencia:** Cada 5 minutos  
**Script:** `php/cron_backup.php`

### Configuración del Crontab

**Archivo:** `crontab`
```cron
# Ejecutar script cada 5 minutos (y guardar log en carpeta visible)
*/5 * * * * root LD_LIBRARY_PATH=/opt/oracle/instantclient ORACLE_HOME=/opt/oracle/instantclient /usr/local/bin/php /var/www/html/php/cron_backup.php >> /var/www/html/php/logs/cron_activity.log 2>&1
```

**Explicación de la sintaxis:**
- `*/5` = Cada 5 minutos
- `* * * *` = Cualquier hora, día, mes, día de semana
- `root` = Usuario que ejecuta el comando
- Variables de entorno Oracle requeridas
- `>>` = Redirigir salida al log (append)
- `2>&1` = Capturar errores también

### Instalación en Docker

**Dockerfile.oracle:**
```dockerfile
# Instalar CRON
RUN apt-get update && apt-get install -y \
    cron \
    && rm -rf /var/lib/apt/lists/*

# Copiar archivo crontab
COPY crontab /etc/cron.d/my-cron

# Dar permisos y registrar
RUN chmod 0644 /etc/cron.d/my-cron && \
    crontab /etc/cron.d/my-cron && \
    touch /var/log/cron.log

# Comando para iniciar cron junto con Apache
CMD cron && apache2-foreground
```

### Script de Respaldo

**php/cron_backup.php:**
```php
<?php
// Script para generar Copia de Seguridad de la tabla usuarios
require_once __DIR__ . '/conexion.php';

$backupDir = __DIR__ . '/backups';
$logFile = __DIR__ . '/logs/cron_activity.log';

// Crear carpetas si no existen
if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);
if (!is_dir(dirname($logFile))) mkdir(dirname($logFile), 0777, true);

try {
    // 1. Obtener todos los datos de usuarios desde Oracle
    $sql = "SELECT * FROM usuarios";
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Crear archivo de respaldo con timestamp
    $fecha = date('Y-m-d_H-i-s');
    $archivoSalida = $backupDir . '/respaldo_usuarios_' . $fecha . '.json';
    
    // 3. Guardar datos en formato JSON
    $datosJson = json_encode($usuarios, JSON_PRETTY_PRINT);     
    file_put_contents($archivoSalida, $datosJson);

    // 4. Registrar en LOG
    $count = count($usuarios);
    $mensaje = "[$fecha] BACKUP ÉXITO: Se guardaron $count usuarios en $archivoSalida\n";
    
    file_put_contents($logFile, $mensaje, FILE_APPEND);
    echo $mensaje;

} catch (Exception $e) {
    // Registrar error en log
    $error = date('Y-m-d H:i:s') . " ERROR BACKUP: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $error, FILE_APPEND);
    echo $error;
}
?>
```

### Verificación del Cron

**1. Verificar que cron está corriendo:**
```bash
$ docker exec congreso_web_oracle service cron status
cron is running.
```

**2. Verificar crontab instalado:**
```bash
$ docker exec congreso_web_oracle crontab -l
*/5 * * * * LD_LIBRARY_PATH=/opt/oracle/instantclient ORACLE_HOME=/opt/oracle/instantclient /usr/local/bin/php /var/www/html/php/cron_backup.php >> /var/www/html/php/logs/cron_activity.log 2>&1
```

**3. Ver archivos de respaldo generados:**
```bash
$ ls -lh Proyecto_conectado/php/backups/
-rw-r--r-- 1 www-data www-data 1.2K Nov 26 03:15 respaldo_usuarios_2025-11-26_03-15-02.json
-rw-r--r-- 1 www-data www-data 1.2K Nov 26 03:20 respaldo_usuarios_2025-11-26_03-20-01.json
-rw-r--r-- 1 www-data www-data 1.1K Nov 22 02:35 respaldo_usuarios_2025-11-22_02-35-01.json
```

**4. Ver logs de actividad:**
```bash
$ docker exec congreso_web_oracle tail -f /var/www/html/php/logs/cron_activity.log
[2025-11-26_03-15-02] BACKUP ÉXITO: Se guardaron 3 usuarios en /var/www/html/php/backups/respaldo_usuarios_2025-11-26_03-15-02.json
[2025-11-26_03-20-01] BACKUP ÉXITO: Se guardaron 3 usuarios en /var/www/html/php/backups/respaldo_usuarios_2025-11-26_03-20-01.json
```

### Formato de Respaldo

**Ejemplo de archivo JSON generado:**
```json
[
    {
        "id_usuario": "1",
        "nombre_completo": "Angel Ivan",
        "email": "anelkauri@gmail.com",
        "password_hash": "$2y$10$Os0TAeHtnOkGMFUM1itsd.R9/2clxmaEp3WxA976NDWOvLcF5YHS6",
        "matricula": "512456",
        "semestre": "5",
        "telefono": "+5214491197007",
        "rol": "alumno",
        "codigo_qr": "4bfb40d7-b43f-4308-88f9-883d620532f5",
        "verificado": "1",
        "fecha_registro": "2025-11-21 17:46:49"
    }
]
```

### Características del Sistema de Respaldo

✅ **Ejecución automática:** Cada 5 minutos sin intervención manual  
✅ **Persistencia:** Los backups se guardan en volumen Docker  
✅ **Logging:** Todos los respaldos se registran en log  
✅ **Timestamp:** Cada archivo tiene fecha y hora única  
✅ **Formato JSON:** Fácil de leer y restaurar  
✅ **Manejo de errores:** Los errores se registran en el log  
✅ **Variables de entorno Oracle:** Correctamente configuradas para PDO_OCI

---

## 📊 Resumen de Cumplimiento

| Requisito | Porcentaje | Estado | Evidencia |
|-----------|------------|--------|-----------|
| Servidor Web (Apache) | 6.25% | ✅ Cumplido | Apache 2.4.62 en Docker, puerto 8081 |
| APIs (5+ endpoints) | 6.25% | ✅ Cumplido | 30+ endpoints funcionales documentados |
| WebSocket (1+ socket) | 6.25% | ✅ Cumplido | Socket.IO con 8+ eventos en tiempo real |
| Cron Backup (cada 5 min) | 6.25% | ✅ Cumplido | Crontab activo con logs verificables |
| **TOTAL** | **25%** | **✅ 100%** | **Todos los requisitos implementados** |

---

## 🔍 Verificación Rápida

### Comando 1: Verificar servicios activos
```bash
docker-compose ps
```

**Resultado esperado:**
```
congreso_web_oracle    Running    0.0.0.0:8081->80/tcp
congreso_oracle_db     Running    0.0.0.0:1521->1521/tcp
congreso_whatsapp      Running    0.0.0.0:3001->3001/tcp
```

### Comando 2: Probar API
```bash
curl http://localhost:8081/php/listar_eventos.php
```

### Comando 3: Probar WebSocket
```bash
curl http://localhost:3001/health
```

### Comando 4: Verificar Cron
```bash
docker exec congreso_web_oracle crontab -l
docker exec congreso_web_oracle ls -lh /var/www/html/php/backups/
```

---

## 📚 Documentación Adicional

- `README.md` - Guía principal del proyecto
- `DOCKER_README.md` - Documentación de Docker
- `WEBSOCKET_README.md` - Documentación WebSocket completa
- `PRUEBAS_WEBSOCKET.md` - Guía de pruebas WebSocket
- `MAPA_SERVICIOS.md` - Arquitectura de servicios

---

**Fecha de Elaboración:** 9 de diciembre de 2025  
**Versión:** 1.0  
**Equipo:** GJA - Sistema de Gestión Congreso de Mercadotecnia UAA
