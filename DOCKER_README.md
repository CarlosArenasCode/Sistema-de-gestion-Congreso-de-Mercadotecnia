# 🐳 Docker - Sistema de Gestión Congreso de Mercadotecnia

## 🚀 Inicio Rápido

### Opción 1: Script Automático (Recomendado)

```powershell
# Ejecutar desde la raíz del proyecto
.\iniciar-docker-personalizado.ps1
```

Este script hace TODO automáticamente:
- ✅ Verifica Docker
- ✅ Crea directorios necesarios
- ✅ Construye e inicia contenedores
- ✅ Inicializa la base de datos con el sistema de personalización
- ✅ Configura permisos
- ✅ Abre el navegador

### Opción 2: Manual

```powershell
# 1. Crear directorios
mkdir -p data/uploads data/constancias_pdf data/carrusel

# 2. Iniciar contenedores
docker-compose up -d --build

# 3. Esperar 20 segundos
Start-Sleep -Seconds 20

# 4. Configurar permisos
docker exec congreso_web chmod -R 777 /var/www/html/Proyecto_conectado/uploads

# 5. Abrir navegador
Start-Process "http://localhost:8080"
```

---

## 📦 Contenedores Incluidos

| Contenedor | Servicio | Puerto | Descripción |
|------------|----------|--------|-------------|
| `congreso_web` | Apache/PHP 8.2 | 8080 | Servidor web principal |
| `congreso_db` | MySQL 8.0 | 3306 | Base de datos |
| `congreso_phpmyadmin` | phpMyAdmin | 8081 | Gestión de BD |
| `congreso_whatsapp` | Node.js 18 | 3001 | Servicio WhatsApp |

---

## 🌐 URLs de Acceso

- **Sitio Web**: http://localhost:8080
- **Login**: http://localhost:8080/Front-end/login.html
- **Admin Dashboard**: http://localhost:8080/Front-end/admin_dashboard.html
- **Personalización**: http://localhost:8080/Front-end/admin_personalizacion.html
- **phpMyAdmin**: http://localhost:8081
- **WhatsApp API**: http://localhost:3001

---

## 🎨 Sistema de Personalización

### Inicialización Automática

El docker-compose.yml está configurado para inicializar automáticamente:

1. **Base de datos principal** (`congreso_db.sql`)
2. **Tablas de personalización** (`personalizacion.sql`)
   - Tabla `personalizacion` (7 colores)
   - Tabla `carrusel_imagenes` (imágenes del carrusel)

### Volúmenes Persistentes

```yaml
volumes:
  # Código en tiempo real (hot reload)
  - ./Proyecto_conectado:/var/www/html/Proyecto_conectado
  
  # Archivos generados (persistentes)
  - ./data/uploads:/var/www/html/Proyecto_conectado/uploads
  - ./data/constancias_pdf:/var/www/html/Proyecto_conectado/constancias_pdf
  - ./data/carrusel:/var/www/html/Proyecto_conectado/uploads/carrusel
```

### Características Habilitadas

✅ **Personalización de Colores**
- 7 colores configurables
- Aplicación automática a todas las páginas

✅ **Gestión del Carrusel**
- Subida de archivos locales → `data/carrusel/`
- URLs externas
- Persistencia entre reinicios

---

## 🔧 Comandos Útiles

### Gestión de Contenedores

```powershell
# Ver estado de contenedores
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f web
docker-compose logs -f db
docker-compose logs -f whatsapp

# Detener contenedores (sin eliminar datos)
docker-compose stop

# Iniciar contenedores detenidos
docker-compose start

# Reiniciar contenedores
docker-compose restart

# Detener y eliminar contenedores (mantiene volúmenes)
docker-compose down

# Detener y eliminar TODO (incluyendo volúmenes)
docker-compose down -v
```

### Gestión de la Base de Datos

```powershell
# Acceder a MySQL desde la terminal
docker exec -it congreso_db mysql -uroot -prootpassword congreso_db

# Ejecutar query SQL
echo "SELECT * FROM personalizacion;" | docker exec -i congreso_db mysql -uroot -prootpassword congreso_db

# Exportar base de datos
docker exec congreso_db mysqldump -uroot -prootpassword congreso_db > backup.sql

# Importar base de datos
Get-Content backup.sql | docker exec -i congreso_db mysql -uroot -prootpassword congreso_db

# Reinicializar base de datos (CUIDADO: Borra todos los datos)
docker-compose down -v
docker-compose up -d
```

### Gestión de Archivos

```powershell
# Copiar archivos AL contenedor
docker cp Proyecto_conectado/Front-end/mi_archivo.html congreso_web:/var/www/html/Proyecto_conectado/Front-end/

# Copiar archivos DESDE el contenedor
docker cp congreso_web:/var/www/html/Proyecto_conectado/uploads/carrusel/ ./backup_carrusel/

# Ver archivos en el contenedor
docker exec congreso_web ls -la /var/www/html/Proyecto_conectado/uploads/carrusel

# Limpiar caché de PHP
docker exec congreso_web rm -rf /tmp/*
```

### Configurar Permisos

```powershell
# Dar permisos de escritura a uploads
docker exec congreso_web chmod -R 777 /var/www/html/Proyecto_conectado/uploads

# Verificar permisos
docker exec congreso_web ls -la /var/www/html/Proyecto_conectado/uploads
```

---

## 🐛 Solución de Problemas

### Error: Puerto 3306 ya en uso

**Problema:** XAMPP MySQL está corriendo

**Solución:**
```powershell
# Opción 1: Detener MySQL de XAMPP
# Abre XAMPP Control Panel y detén MySQL

# Opción 2: Cambiar puerto en docker-compose.yml
# Cambia "3306:3306" a "3307:3306"
```

### Error: Puerto 8080 ya en uso

**Problema:** Otro servicio usa el puerto 8080

**Solución:**
```yaml
# En docker-compose.yml, cambiar:
ports:
  - "8081:80"  # Cambia 8080 a 8081
```

### Error: No se crean las tablas de personalización

**Problema:** La base de datos ya existía

**Solución:**
```powershell
# 1. Eliminar volumen de BD
docker-compose down -v

# 2. Reiniciar
docker-compose up -d

# 3. Esperar 30 segundos
Start-Sleep -Seconds 30

# 4. Verificar en phpMyAdmin (http://localhost:8081)
```

### Error: No se pueden subir imágenes

**Problema:** Permisos incorrectos

**Solución:**
```powershell
# Configurar permisos
docker exec congreso_web chmod -R 777 /var/www/html/Proyecto_conectado/uploads

# Verificar directorio existe
docker exec congreso_web ls -la /var/www/html/Proyecto_conectado/uploads/carrusel
```

### Los cambios de código no se reflejan

**Problema:** Hot reload no funciona

**Solución:**
```powershell
# 1. Verificar que el volumen está montado
docker-compose ps

# 2. Reiniciar el contenedor web
docker-compose restart web

# 3. Limpiar caché del navegador (Ctrl+Shift+R)
```

### Error de conexión a la base de datos

**Problema:** `conexion.php` tiene credenciales incorrectas

**Solución:**
```php
// Verificar en Proyecto_conectado/php/conexion.php
$servername = "congreso_db";  // NO "localhost"
$username = "congreso_user";
$password = "congreso_pass";
$dbname = "congreso_db";
```

---

## 📊 Monitoreo

### Ver uso de recursos

```powershell
# Ver CPU y memoria de contenedores
docker stats

# Ver solo estadísticas de congreso
docker stats congreso_web congreso_db congreso_whatsapp
```

### Ver logs detallados

```powershell
# Logs de inicialización de MySQL
docker logs congreso_db

# Logs de Apache/PHP
docker logs congreso_web

# Logs de WhatsApp
docker logs congreso_whatsapp

# Ver últimas 50 líneas
docker logs --tail 50 congreso_web
```

---

## 🔐 Credenciales por Defecto

### Base de Datos

```
Host:     localhost:3306 (desde host) o congreso_db:3306 (desde contenedor)
Database: congreso_db
User:     congreso_user
Password: congreso_pass
Root:     rootpassword
```

### phpMyAdmin

```
URL:      http://localhost:8081
User:     congreso_user
Password: congreso_pass
```

---

## 🚀 Desarrollo

### Hot Reload Habilitado

El código en `Proyecto_conectado/` está montado como volumen:
- ✅ Cambios en PHP/HTML/JS/CSS se reflejan inmediatamente
- ✅ No necesitas reconstruir el contenedor
- ✅ Solo reinicia el navegador

### Agregar Nueva Funcionalidad

```powershell
# 1. Edita archivos en Proyecto_conectado/
# 2. Recarga el navegador (Ctrl+R o F5)
# 3. Si agregaste archivos PHP nuevos, puede necesitar:
docker-compose restart web
```

### Instalar Paquetes PHP Adicionales

```dockerfile
# Edita Dockerfile y agrega:
RUN docker-php-ext-install nuevo_paquete

# Reconstruye:
docker-compose up -d --build
```

---

## 📦 Backup y Restauración

### Backup Completo

```powershell
# 1. Backup de base de datos
docker exec congreso_db mysqldump -uroot -prootpassword congreso_db > backup_$(Get-Date -Format 'yyyyMMdd').sql

# 2. Backup de archivos subidos
Copy-Item -Recurse data/carrusel backup_carrusel_$(Get-Date -Format 'yyyyMMdd')
Copy-Item -Recurse data/uploads backup_uploads_$(Get-Date -Format 'yyyyMMdd')
```

### Restauración

```powershell
# 1. Restaurar base de datos
Get-Content backup_20251018.sql | docker exec -i congreso_db mysql -uroot -prootpassword congreso_db

# 2. Restaurar archivos
Copy-Item -Recurse backup_carrusel_20251018/* data/carrusel/
Copy-Item -Recurse backup_uploads_20251018/* data/uploads/
```

---

## 🔄 Actualizar Sistema

### Actualizar Código

```powershell
# 1. Pull cambios de git
git pull origin feature/gja-proposal

# 2. Reiniciar contenedor web
docker-compose restart web
```

### Actualizar Base de Datos

```powershell
# Si hay nuevo script SQL
Get-Content Proyecto_conectado/sql/nueva_tabla.sql | docker exec -i congreso_db mysql -uroot -prootpassword congreso_db
```

### Actualizar Contenedores

```powershell
# Reconstruir desde cero
docker-compose down
docker-compose up -d --build
```

---

## 📝 Estructura de Volúmenes

```
Sistema-de-gestion-Congreso-de-Mercadotecnia/
├── data/                           # Datos persistentes
│   ├── uploads/                    # Archivos subidos generales
│   ├── constancias_pdf/           # PDFs de constancias
│   └── carrusel/                  # Imágenes del carrusel
│
├── Proyecto_conectado/            # Código fuente (montado en tiempo real)
│   ├── Front-end/
│   ├── php/
│   ├── js/
│   └── uploads/                   # También accesible aquí
│       └── carrusel/
│
└── docker-compose.yml
```

---

## 🎯 Variables de Entorno

### Archivo `.env` (opcional)

Crea un archivo `.env` en la raíz:

```env
MYSQL_ROOT_PASSWORD=rootpassword
MYSQL_DATABASE=congreso_db
MYSQL_USER=congreso_user
MYSQL_PASSWORD=congreso_pass
WHATSAPP_NUMBER=524492106893
```

---

## 🌟 Características Docker

- ✅ **Hot Reload**: Cambios de código en tiempo real
- ✅ **Persistencia**: Datos no se pierden al reiniciar
- ✅ **Aislamiento**: No interfiere con XAMPP u otros servicios
- ✅ **Portabilidad**: Funciona igual en Windows, Mac, Linux
- ✅ **Escalabilidad**: Fácil agregar más servicios
- ✅ **Reproducibilidad**: Mismo entorno para todo el equipo

---

## 📚 Recursos Adicionales

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [PHP Docker Image](https://hub.docker.com/_/php)
- [MySQL Docker Image](https://hub.docker.com/_/mysql)

---

**🎉 ¡Docker configurado y listo para usar!**
