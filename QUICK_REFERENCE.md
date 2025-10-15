# 🐳 Sistema Docker - Guía Rápida de Referencia

## ⚡ Comandos Esenciales

### Iniciar el proyecto
```powershell
.\start-docker.ps1
```

### Detener el proyecto
```powershell
.\stop-docker.ps1
# O simplemente:
docker-compose down
```

### Validar que todo funciona
```powershell
.\validate-docker.ps1
```

---

## 🌐 URLs de Acceso

| Servicio | URL | Descripción |
|----------|-----|-------------|
| **Registro** | http://localhost:8080/Front-end/registro_usuario.html | Registro de nuevos usuarios |
| **Login** | http://localhost:8080/Front-end/login.html | Inicio de sesión |
| **Admin** | http://localhost:8080/Front-end/login_admin.html | Panel de administración |
| **phpMyAdmin** | http://localhost:8081 | Gestión de base de datos |
| **Verificación Config** | http://localhost:8080/php/verificar_config.php | Estado de configuración 2FA |

---

## 🔑 Credenciales por Defecto

### Base de Datos (phpMyAdmin)
- **Usuario**: `congreso_user`
- **Contraseña**: `congreso_pass`
- **Base de datos**: `congreso_db`

### MySQL Root
- **Usuario**: `root`
- **Contraseña**: `rootpassword`

---

## 📊 Ver Logs

### Ver logs de todos los servicios
```powershell
docker-compose logs
```

### Ver logs en tiempo real
```powershell
docker-compose logs -f
```

### Ver logs de un servicio específico
```powershell
docker-compose logs -f web       # Aplicación web
docker-compose logs -f db        # Base de datos
docker-compose logs -f phpmyadmin # phpMyAdmin
```

### Ver últimas 50 líneas de log
```powershell
docker-compose logs --tail=50 web
```

---

## 🔄 Gestión de Servicios

### Ver estado de servicios
```powershell
docker-compose ps
```

### Reiniciar servicios
```powershell
docker-compose restart           # Todos los servicios
docker-compose restart web       # Solo web
docker-compose restart db        # Solo base de datos
```

### Detener servicios
```powershell
docker-compose stop              # Detener (mantiene datos)
docker-compose down              # Detener y eliminar contenedores
docker-compose down -v           # ⚠️ Detener y eliminar TODO (incluye BD)
```

### Iniciar servicios detenidos
```powershell
docker-compose start             # Iniciar servicios existentes
docker-compose up -d             # Crear e iniciar servicios
```

---

## 🛠️ Reconstruir Después de Cambios

### Reconstruir imagen web (después de cambios en Dockerfile)
```powershell
docker-compose build web
docker-compose up -d web
```

### Reconstruir todo desde cero
```powershell
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Reinicio rápido sin reconstruir
```powershell
docker-compose restart
```

---

## 💾 Gestión de Base de Datos

### Acceder a MySQL desde terminal
```powershell
docker exec -it congreso_db mysql -u congreso_user -p
# Contraseña: congreso_pass
```

### Ejecutar consultas SQL directas
```powershell
docker exec congreso_db mysql -u congreso_user -pcongreso_pass congreso_db -e "SELECT * FROM usuarios LIMIT 5;"
```

### Hacer backup de la base de datos
```powershell
docker exec congreso_db mysqldump -u congreso_user -pcongreso_pass congreso_db > backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql
```

### Restaurar backup
```powershell
Get-Content backup.sql | docker exec -i congreso_db mysql -u congreso_user -pcongreso_pass congreso_db
```

### Importar el script SQL inicial
```powershell
docker exec -i congreso_db mysql -u congreso_user -pcongreso_pass congreso_db < Proyecto_conectado/sql/congreso_db.sql
```

---

## 🐚 Acceder a Contenedores

### Entrar al contenedor web (Apache/PHP)
```powershell
docker exec -it congreso_web bash
```

### Entrar al contenedor de base de datos
```powershell
docker exec -it congreso_db bash
```

### Ejecutar comandos PHP en el contenedor
```powershell
docker exec congreso_web php -v
docker exec congreso_web php /var/www/html/Proyecto_conectado/php/test.php
```

---

## 📁 Archivos y Directorios Importantes

### Estructura del proyecto
```
.
├── docker-compose.yml          # Configuración de servicios
├── Dockerfile                  # Imagen PHP personalizada
├── .env                        # Variables de entorno
├── .env.example               # Plantilla de configuración
├── start-docker.ps1           # Script de inicio
├── stop-docker.ps1            # Script de detención
├── validate-docker.ps1        # Script de validación
├── DOCKER_SETUP.md            # Documentación completa
├── QUICK_REFERENCE.md         # Esta guía
└── Proyecto_conectado/
    ├── Front-end/            # HTML
    ├── php/                  # Backend
    ├── js/                   # JavaScript
    └── sql/                  # Scripts de BD
```

### Archivos de configuración que puedes editar
- `.env` - Variables de entorno (SMTP, Twilio, BD)
- `Proyecto_conectado/php/smtp_config.php` - Configuración SMTP
- `Proyecto_conectado/php/conexion.php` - Conexión a BD

---

## 🐛 Solución Rápida de Problemas

### El puerto 8080 está ocupado
```powershell
# Detener XAMPP u otro servidor web
# O cambiar puerto en docker-compose.yml:
# ports: "8090:80"  # Cambia 8080 por 8090
```

### La base de datos no se conecta
```powershell
# Esperar 30 segundos
Start-Sleep -Seconds 30

# Ver logs
docker-compose logs db

# Reiniciar BD
docker-compose restart db
```

### Cambios en el código no se reflejan
```powershell
# Los cambios son automáticos (hot reload)
# Si no funciona:
docker-compose restart web

# O limpia caché del navegador (Ctrl+Shift+R)
```

### Error: "Cannot find module"
```powershell
# Reconstruir imagen
docker-compose build --no-cache web
docker-compose up -d
```

### Limpiar todo y empezar de cero
```powershell
# ⚠️ ESTO BORRARÁ TODOS LOS DATOS
docker-compose down -v
docker-compose up -d --build
```

---

## 📈 Monitoreo de Recursos

### Ver uso de recursos
```powershell
docker stats
```

### Ver uso de espacio en disco
```powershell
docker system df
```

### Limpiar recursos no utilizados
```powershell
docker system prune              # Eliminar recursos no usados
docker system prune -a           # Limpieza agresiva
docker volume prune              # Eliminar volúmenes no usados
```

---

## 🔐 Configuración de 2FA (WhatsApp/SMS)

### Verificar configuración actual
```powershell
# Abrir en navegador:
Start-Process "http://localhost:8080/php/verificar_config.php"
```

### Ver SMS simulados (modo desarrollo)
```powershell
Get-Content Proyecto_conectado\php\sms_log.txt -Tail 20 -Wait
```

### Configurar Twilio para producción
1. Edita `.env`:
```env
TWILIO_ACCOUNT_SID=tu_sid_real
TWILIO_AUTH_TOKEN=tu_token_real
TWILIO_PHONE_NUMBER=+14155238886
```

2. Reinicia el servicio:
```powershell
docker-compose restart web
```

---

## ✅ Checklist de Validación

Ejecuta estos comandos para verificar que todo funciona:

```powershell
# 1. Validación automática
.\validate-docker.ps1

# 2. Verificar servicios
docker-compose ps

# 3. Probar aplicación web
Start-Process "http://localhost:8080/Front-end/registro_usuario.html"

# 4. Probar phpMyAdmin
Start-Process "http://localhost:8081"

# 5. Verificar logs (sin errores)
docker-compose logs --tail=50
```

---

## 📞 Ayuda Adicional

### Documentación completa
- Ver `DOCKER_SETUP.md` para guía detallada
- Ver `CONFIGURAR_WHATSAPP_PASO_A_PASO.md` para configuración de WhatsApp

### Ver versiones instaladas
```powershell
docker --version
docker-compose --version
docker exec congreso_web php -v
docker exec congreso_db mysql --version
```

### Contacto y soporte
- Revisa los logs: `docker-compose logs`
- Consulta la documentación en el repositorio
- Ejecuta el script de validación: `.\validate-docker.ps1`

---

**🎉 ¡Listo para desarrollar!**
