# 🚀 Guía Rápida - Probar WebSocket en 5 Minutos

## ✅ Pre-requisitos

- Docker Desktop ejecutándose
- Proyecto corriendo con `docker-compose up -d`

---

## 📝 Paso 1: Verificar que el servicio WebSocket está activo

Abre tu navegador en:
```
http://localhost:3001/health
```

Deberías ver algo como:
```json
{
  "status": "ready",
  "service": "whatsapp-verification",
  "websocket": {
    "enabled": true,
    "connectedClients": 0
  }
}
```

✅ Si ves esto, el WebSocket está activo y listo.

---

## 🧪 Paso 2: Probar con la página de test

1. Abre en tu navegador:
```
http://localhost:8081/Front-end/test_websocket.html
```

2. Haz clic en el botón **"Conectar"**

3. Deberías ver:
   - Estado cambia a "Conectado" 🟢
   - Socket ID aparece
   - Logs muestran conexión exitosa

4. Haz clic en **"Unirse a Admin"**

5. Haz clic en **"Simular Asistencia"**

6. Deberías ver:
   - Notificación en la esquina superior derecha
   - Logs muestran la asistencia registrada
   - Estadísticas se actualizan

---

## 🎯 Paso 3: Probar en escenario real

### Opción A: Dashboard de Admin

1. Abre:
```
http://localhost:8081/Front-end/admin_dashboard.html
```

2. Abre la consola del navegador (F12)

3. Deberías ver:
```
[WebSocket] Conectando a http://localhost:3001...
[WebSocket] ✅ Conectado al servidor WebSocket
✅ Admin conectado a WebSocket
✅ Unido a sala de admin
```

4. Las estadísticas en tiempo real deberían mostrar "0" inicialmente

### Opción B: Escáner de QR + Dashboard

**Preparación:**
1. Abre dos ventanas/pestañas del navegador

**Ventana 1 - Admin Dashboard:**
```
http://localhost:8081/Front-end/admin_dashboard.html
```

**Ventana 2 - Admin Scanner:**
```
http://localhost:8081/Front-end/admin_scan_qr.html
```

**Ahora prueba:**

1. En la ventana 2 (Scanner):
   - Selecciona un evento
   - Ingresa un código QR manualmente o escanea uno
   - Registra la asistencia

2. En la ventana 1 (Dashboard):
   - **SIN REFRESCAR LA PÁGINA**
   - Verás las estadísticas actualizarse automáticamente
   - Aparecerá una notificación en la esquina superior derecha
   - Los contadores incrementarán

---

## 👤 Paso 4: Probar notificación de alumno

1. Abre sesión como alumno:
```
http://localhost:8081/Front-end/dashboard_alumno.html
```

2. Abre la consola (F12)

3. Verás:
```
✅ Conectado a notificaciones en tiempo real (Usuario: X)
```

4. En otra ventana, escanea el QR de ese alumno en `admin_scan_qr.html`

5. El alumno recibirá:
   - Alerta en pantalla
   - Notificación visual
   - Log en consola

---

## 🔍 Verificar que funciona correctamente

### ✅ Checklist de funcionamiento:

- [ ] Servidor responde en `http://localhost:3001/health`
- [ ] `test_websocket.html` conecta exitosamente
- [ ] `admin_dashboard.html` muestra estadísticas en tiempo real
- [ ] Al registrar asistencia, el dashboard se actualiza SIN refrescar
- [ ] Alumno recibe notificación cuando se escanea su QR
- [ ] Logs en consola muestran eventos WebSocket

---

## 🐛 Problemas Comunes

### ❌ "Cannot connect to localhost:3001"

**Solución:**
```bash
# Verifica que el contenedor whatsapp esté corriendo
docker ps | grep whatsapp

# Si no está corriendo, reinicia Docker
docker-compose restart whatsapp

# Verifica los logs
docker logs congreso_whatsapp
```

### ❌ "Socket.IO client not loaded"

**Solución:**
Verifica que tienes conexión a internet. El CDN de Socket.IO debe cargar:
```html
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
```

### ❌ "CORS error"

**Solución:**
Asegúrate de acceder desde `http://localhost:8081` (no desde file://)

### ❌ "PHP no notifica al WebSocket"

**Solución:**
```bash
# Verifica que curl esté habilitado en PHP
docker exec congreso_web_oracle php -m | grep curl

# Verifica logs de PHP
docker exec congreso_web_oracle tail -f /var/log/apache2/error.log
```

---

## 📊 Monitorear eventos en tiempo real

### Ver logs del servidor WebSocket:

```bash
docker logs -f congreso_whatsapp
```

Deberías ver algo como:
```
🌐 Servidor corriendo en http://localhost:3001
🔌 WebSocket Server activo
🔌 Cliente conectado: abc123xyz
👤 Admin abc123xyz conectado
✅ Asistencia notificada vía WebSocket: Juan Pérez (AL123456)
```

---

## 🎉 ¡Listo!

Si completaste todos los pasos y ves las actualizaciones en tiempo real, **¡el WebSocket está funcionando perfectamente!**

### Ahora puedes:
- Demostrar el funcionamiento en vivo
- Explicar cómo funciona cada componente
- Mostrar las notificaciones en tiempo real
- Cumplir con el requisito académico (6.25%)

---

## 📸 Capturas para Documentación

Para tu reporte, captura pantalla de:

1. `test_websocket.html` conectado mostrando logs
2. `admin_dashboard.html` con estadísticas actualizándose
3. Consola del navegador mostrando eventos WebSocket
4. `docker logs congreso_whatsapp` mostrando eventos
5. Dos ventanas lado a lado (scanner + dashboard) mostrando actualización simultánea

---

**¿Necesitas ayuda?** Revisa `WEBSOCKET_README.md` para documentación completa.
