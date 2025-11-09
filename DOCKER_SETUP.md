# 🐳 Guía de Instalación y Uso con Docker

## 📋 Requisitos Previos

1. **Docker Desktop** instalado y ejecutándose
   - Descargar desde: https://www.docker.com/products/docker-desktop
   - Verificar que Docker está corriendo (ícono de ballena en la barra de tareas)

2. **Git** (para clonar el repositorio)

---

## 🚀 Inicio Rápido (3 pasos)

### 1️⃣ Configurar variables de entorno

Copia el archivo de ejemplo y configúralo:

```powershell
# En PowerShell
Copy-Item .env.example .env
notepad .env
```

Edita el archivo `.env` con tus credenciales:
- **SMTP**: Para envío de emails (Gmail, Outlook, etc.)
- **Twilio**: Para WhatsApp/SMS (opcional en desarrollo)

### 2️⃣ Iniciar el proyecto

Ejecuta el script de inicio automático:

```powershell
# En PowerShell
.\start-docker.ps1
```

Este script automáticamente:
- ✅ Verifica que Docker esté instalado y corriendo
- ✅ Crea el archivo `.env` si no existe
- ✅ Construye las imágenes Docker
- ✅ Inicia todos los servicios
- ✅ Muestra las URLs de acceso

### 3️⃣ Acceder a la aplicación

Una vez iniciado, abre en tu navegador:

- **Aplicación Web**: http://localhost:8080/Front-end/login.html
- **Registro**: http://localhost:8080/Front-end/registro_usuario.html
- **phpMyAdmin**: http://localhost:8081
- **Panel de verificación 2FA**: http://localhost:8080/php/verificar_config.php

---

## 🛠️ Comandos Útiles

### Ver logs en tiempo real
```powershell
docker-compose logs -f web
docker-compose logs -f db
```

### Detener los servicios
```powershell
docker-compose down
```

### Reiniciar los servicios
```powershell
docker-compose restart
```

### Reconstruir las imágenes (después de cambios)
```powershell
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Acceder al contenedor web
```powershell
docker exec -it congreso_web bash
```

### Acceder al contenedor de base de datos
```powershell
docker exec -it congreso_db mysql -u congreso_user -p
# Contraseña: congreso_pass (por defecto)
```

### Ver estado de los contenedores
```powershell
docker-compose ps
```

---

## 📦 Servicios Incluidos

| Servicio | Puerto | Descripción |
|----------|--------|-------------|
| **web** | 8080 | PHP 8.2 + Apache con el código de la aplicación |
| **db** | 3306 | MySQL 8.0 con la base de datos `congreso_db` |
| **phpmyadmin** | 8081 | Interfaz web para gestionar la base de datos |

---

## 🔧 Configuración Avanzada

### Variables de entorno importantes

Edita el archivo `.env` para configurar:

```env
# Base de datos
MYSQL_DATABASE=congreso_db
MYSQL_USER=congreso_user
MYSQL_PASSWORD=congreso_pass

# SMTP (para emails)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=tu-email@gmail.com
SMTP_PASSWORD=tu-contraseña-de-aplicacion

# Twilio (para WhatsApp/SMS)
TWILIO_ACCOUNT_SID=tu_account_sid
TWILIO_AUTH_TOKEN=tu_auth_token
TWILIO_PHONE_NUMBER=+14155238886
```

### Persistencia de datos

Los datos se guardan en:
- **Base de datos**: Volumen Docker `db_data`
- **Uploads**: `./data/uploads`
- **Constancias PDF**: `./data/constancias_pdf`

---

## 🐛 Solución de Problemas

### Error: "Docker no está instalado"
- Instala Docker Desktop desde https://www.docker.com/products/docker-desktop
- Reinicia la terminal después de instalar

### Error: "Docker no está corriendo"
- Abre Docker Desktop y espera a que el ícono de ballena esté estable
- En Windows, verifica que el servicio de Docker esté iniciado

### Error: "Puerto 8080 ya está en uso"
- Detén XAMPP o cualquier otro servidor web local
- O cambia el puerto en `docker-compose.yml`: `"8081:80"`

### Error: "No se puede conectar a la base de datos"
- Espera 30 segundos para que MySQL termine de inicializarse
- Verifica los logs: `docker-compose logs db`
- Reinicia el servicio: `docker-compose restart db`

### La base de datos está vacía
- La primera vez, MySQL importa automáticamente `congreso_db.sql`
- Si no funciona, importa manualmente:
  ```powershell
  docker exec -i congreso_db mysql -u congreso_user -pcongreso_pass congreso_db < Proyecto_conectado/sql/congreso_db.sql
  ```

### Limpiar todo y empezar de cero
```powershell
# ⚠️ Esto eliminará todos los datos
docker-compose down -v
docker-compose up -d
```

---

## 📚 Estructura del Proyecto

```
.
├── docker-compose.yml          # Configuración de servicios Docker
├── Dockerfile                  # Imagen personalizada PHP 8.2 + Apache
├── .env                        # Variables de entorno (NO subir a Git)
├── .env.example               # Plantilla de variables de entorno
├── start-docker.ps1           # Script de inicio automático
├── Proyecto_conectado/        # Código fuente de la aplicación
│   ├── Front-end/            # Interfaces de usuario (HTML)
│   ├── php/                  # Backend PHP
│   ├── js/                   # JavaScript (usuarios)
│   ├── js_admin/            # JavaScript (administradores)
│   ├── CSS/                 # Estilos
│   └── sql/                 # Scripts de base de datos
└── data/                     # Datos persistentes
    ├── uploads/             # Archivos subidos
    └── constancias_pdf/     # PDFs generados
```

---

## ✅ Checklist Post-Instalación

- [ ] Docker Desktop instalado y corriendo
- [ ] Archivo `.env` configurado con credenciales SMTP
- [ ] Script `start-docker.ps1` ejecutado sin errores
- [ ] Acceso exitoso a http://localhost:8080
- [ ] phpMyAdmin accesible en http://localhost:8081
- [ ] Base de datos `congreso_db` con tablas creadas
- [ ] Registro de usuario funcional (con verificación 2FA)

---

## 🆘 Soporte

Si encuentras problemas:
1. Revisa los logs: `docker-compose logs`
2. Consulta la sección "Solución de Problemas" arriba
3. Verifica la configuración en: http://localhost:8080/php/verificar_config.php

---

## 📖 Documentación Adicional

- [Configuración de WhatsApp/SMS](./CONFIGURAR_WHATSAPP_PASO_A_PASO.md)
- [Guía rápida WhatsApp](./GUIA_CONFIGURAR_WHATSAPP.md)
- [Descripción del Pull Request](./PR_DESCRIPTION.md)

---

**¡Feliz desarrollo! 🚀**
