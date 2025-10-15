# 🌐 URLs CORRECTAS DEL SISTEMA

## ⚠️ IMPORTANTE: RUTAS SIN /Proyecto_conectado/

El Document Root de Apache está configurado en `/var/www/html/Proyecto_conectado`, por lo tanto, las URLs **NO** deben incluir `/Proyecto_conectado/`.

---

## ✅ URLs CORRECTAS

### Aplicación Principal

| Descripción | URL CORRECTA ✅ |
|------------|-----------------|
| **Página de Inicio** | http://localhost:8080/ |
| **Registro de Usuario** | http://localhost:8080/Front-end/registro_usuario.html |
| **Login Usuario** | http://localhost:8080/Front-end/login.html |
| **Login Admin** | http://localhost:8080/Front-end/login_admin.html |
| **Dashboard Alumno** | http://localhost:8080/Front-end/dashboard_alumno.html |
| **Dashboard Admin** | http://localhost:8080/Front-end/admin_dashboard.html |
| **Horario** | http://localhost:8080/Front-end/horario.html |
| **Mi QR** | http://localhost:8080/Front-end/mi_qr.html |
| **Mis Constancias** | http://localhost:8080/Front-end/mis_constancias.html |

### Servicios WhatsApp

| Descripción | URL CORRECTA ✅ |
|------------|-----------------|
| **Panel de Pruebas WhatsApp** | http://localhost:8080/php/test_whatsapp_docker.php |
| **API WhatsApp (Health)** | http://localhost:3001/health |
| **Servicio WhatsApp (QR)** | http://localhost:3001/qr |

### Base de Datos

| Descripción | URL CORRECTA ✅ |
|------------|-----------------|
| **phpMyAdmin** | http://localhost:8081 |

---

## ❌ URLs INCORRECTAS (NO USAR)

| ❌ INCORRECTO | ✅ CORRECTO |
|--------------|-------------|
| ~~http://localhost:8080/Proyecto_conectado/Front-end/registro_usuario.html~~ | http://localhost:8080/Front-end/registro_usuario.html |
| ~~http://localhost:8080/Proyecto_conectado/php/test_whatsapp_docker.php~~ | http://localhost:8080/php/test_whatsapp_docker.php |

---

## 🧪 Prueba Rápida

### 1. Verificar que Apache está corriendo

```powershell
curl -I http://localhost:8080/
```

Debe responder con `HTTP/1.1` (200, 403, etc.)

### 2. Probar Registro de Usuario

Abre en tu navegador:
```
http://localhost:8080/Front-end/registro_usuario.html
```

### 3. Probar Panel de WhatsApp

Abre en tu navegador:
```
http://localhost:8080/php/test_whatsapp_docker.php
```

### 4. Verificar Servicio WhatsApp

```powershell
curl http://localhost:3001/health
```

Debe responder con JSON:
```json
{
  "status": "qr_ready",
  "service": "whatsapp-verification",
  "phoneNumber": "524492106893",
  "qrAvailable": true
}
```

---

## 📁 Estructura de Carpetas

```
/var/www/html/Proyecto_conectado/  ← Document Root
├── Front-end/
│   ├── registro_usuario.html
│   ├── login.html
│   ├── dashboard_alumno.html
│   └── ...
├── php/
│   ├── test_whatsapp_docker.php
│   ├── whatsapp_client.php
│   ├── registrar_usuario.php
│   └── ...
├── CSS/
├── js/
├── Logos/
└── ...
```

**Cuando accedes a:** `http://localhost:8080/Front-end/registro_usuario.html`

**Apache busca en:** `/var/www/html/Proyecto_conectado/Front-end/registro_usuario.html`

---

## 🔧 Verificación de Configuración

### Ver configuración de Apache

```powershell
docker exec congreso_web cat /etc/apache2/sites-available/000-default.conf
```

Debe mostrar:
```apache
DocumentRoot /var/www/html/Proyecto_conectado
```

### Listar archivos disponibles

```powershell
# Ver archivos HTML
docker exec congreso_web ls /var/www/html/Proyecto_conectado/Front-end/

# Ver archivos PHP
docker exec congreso_web ls /var/www/html/Proyecto_conectado/php/
```

---

## 🎯 Flujo de Prueba Completo

### Paso 1: Abrir Registro
```
http://localhost:8080/Front-end/registro_usuario.html
```

### Paso 2: Llenar formulario
- Nombre Completo: Tu nombre
- Email: tu@email.com
- Matrícula: 123456
- Teléfono: **tu número de WhatsApp real**
- Semestre: 1 (si eres alumno)
- Rol: alumno
- Contraseña: tu_contraseña

### Paso 3: Enviar
El sistema debe:
1. ✉️ Enviarte un email con el código
2. 📱 Enviarte un WhatsApp con el código (después de escanear QR)

### Paso 4: Verificar código
- Ingresa el código de 6 dígitos
- Tu cuenta se activa

---

## 🐳 Comandos Docker Útiles

### Ver logs de Apache

```powershell
docker logs congreso_web
```

### Ver logs de WhatsApp

```powershell
docker logs congreso_whatsapp
```

### Acceder al contenedor

```powershell
# Entrar al contenedor web
docker exec -it congreso_web bash

# Dentro del contenedor, ver archivos:
ls -la /var/www/html/Proyecto_conectado/Front-end/
ls -la /var/www/html/Proyecto_conectado/php/
```

### Reiniciar servicios

```powershell
# Reiniciar Apache (contenedor web)
docker-compose restart web

# Reiniciar WhatsApp
docker-compose restart whatsapp

# Reiniciar todo
docker-compose restart
```

---

## 📱 Ver Código QR de WhatsApp

```powershell
docker logs congreso_whatsapp
```

Busca el código QR en formato ASCII y escanealo con WhatsApp:
1. Abre WhatsApp en tu teléfono
2. Ve a **Dispositivos Vinculados**
3. **Vincular dispositivo**
4. Escanea el QR de la consola

---

## ✅ Checklist de Verificación

- [ ] Apache responde en http://localhost:8080/
- [ ] Registro abre correctamente: http://localhost:8080/Front-end/registro_usuario.html
- [ ] Panel WhatsApp abre: http://localhost:8080/php/test_whatsapp_docker.php
- [ ] API WhatsApp responde: http://localhost:3001/health
- [ ] Código QR visible en logs
- [ ] Código QR escaneado con WhatsApp
- [ ] Estado "ready" verificado

---

## 🆘 Solución de Problemas

### Error 404: Not Found

**Causa:** Estás usando la URL incorrecta con `/Proyecto_conectado/`

**Solución:** Elimina `/Proyecto_conectado/` de la URL

❌ INCORRECTO: `http://localhost:8080/Proyecto_conectado/Front-end/registro_usuario.html`
✅ CORRECTO: `http://localhost:8080/Front-end/registro_usuario.html`

### Error 403: Forbidden

**Causa:** No hay archivo index en la raíz

**Solución:** Accede directamente a los archivos HTML o PHP

### Página en blanco

**Causa:** Error de PHP

**Solución:** Ver logs de Apache
```powershell
docker logs congreso_web
```

---

## 🎊 ¡Listo!

Ahora puedes acceder correctamente a todas las páginas del sistema.

**URLs principales para probar:**
1. 📝 Registro: http://localhost:8080/Front-end/registro_usuario.html
2. 🧪 Test WhatsApp: http://localhost:8080/php/test_whatsapp_docker.php
3. 🔐 Login: http://localhost:8080/Front-end/login.html

---

**Actualizado:** Octubre 2025  
**Branch:** feature/gja-proposal  
**Document Root:** `/var/www/html/Proyecto_conectado`
