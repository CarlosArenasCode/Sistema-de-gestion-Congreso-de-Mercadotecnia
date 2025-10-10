# 🐳 Guía de Deployment con Docker

Esta guía te ayudará a levantar el **Sistema de Gestión - Congreso de Mercadotecnia** usando Docker y Docker Compose.

---

## 📋 Requisitos Previos

Antes de comenzar, asegúrate de tener instalado:

1. **Docker Desktop** (Windows/Mac) o **Docker Engine** (Linux)
   - Descargar: https://www.docker.com/products/docker-desktop
   - Verificar instalación:
     ```powershell
     docker --version
     docker-compose --version
     ```

2. **Git** (opcional, para clonar el repositorio)

---

## 🚀 Pasos para Levantar el Proyecto

### **Paso 1: Preparar la Configuración**

1. Copia el archivo de ejemplo de variables de entorno:
   ```powershell
   Copy-Item .env.example .env
   ```

2. **(Opcional)** Edita el archivo `.env` si deseas cambiar las credenciales de la base de datos:
   ```env
   MYSQL_ROOT_PASSWORD=rootpassword
   MYSQL_DATABASE=congreso_db
   MYSQL_USER=congreso_user
   MYSQL_PASSWORD=congreso_pass
   ```

3. Copia el archivo de conexión para Docker:
   ```powershell
   Copy-Item Proyecto_conectado\php\conexion.docker.php Proyecto_conectado\php\conexion.php
   ```
   > **Nota:** Este archivo está configurado para usar el servicio `db` de Docker como host.

---

### **Paso 2: Construir las Imágenes Docker**

Desde la raíz del proyecto, ejecuta:

```powershell
docker-compose build
```

Este comando:
- Descarga la imagen base de PHP 8.2 con Apache
- Instala las extensiones necesarias (PDO, MySQLi, GD, Zip)
- Copia el código del proyecto al contenedor

⏱️ **Tiempo estimado:** 3-5 minutos (primera vez)

---

### **Paso 3: Levantar los Servicios**

Ejecuta el siguiente comando para iniciar todos los servicios:

```powershell
docker-compose up -d
```

Esto iniciará:
- ✅ **Servicio Web** (PHP + Apache) en el puerto `8080`
- ✅ **Base de Datos MySQL** en el puerto `3306`
- ✅ **phpMyAdmin** en el puerto `8081`

El flag `-d` ejecuta los contenedores en segundo plano (detached mode).

---

### **Paso 4: Verificar que los Servicios Estén Corriendo**

Verifica el estado de los contenedores:

```powershell
docker-compose ps
```

Deberías ver algo como:

```
NAME                   STATUS        PORTS
congreso_web           Up            0.0.0.0:8080->80/tcp
congreso_db            Up            0.0.0.0:3306->3306/tcp
congreso_phpmyadmin    Up            0.0.0.0:8081->80/tcp
```

---

### **Paso 5: Acceder a la Aplicación**

Una vez que los servicios estén corriendo:

| Servicio | URL | Credenciales |
|----------|-----|--------------|
| **Aplicación Web** | http://localhost:8080/Front-end/login.html | (según BD) |
| **phpMyAdmin** | http://localhost:8081 | User: `congreso_user`<br>Pass: `congreso_pass` |

---

## 🔧 Comandos Útiles

### Ver logs en tiempo real
```powershell
docker-compose logs -f
```

### Ver logs solo del servicio web
```powershell
docker-compose logs -f web
```

### Detener los servicios (sin eliminar datos)
```powershell
docker-compose stop
```

### Iniciar servicios detenidos
```powershell
docker-compose start
```

### Detener y eliminar contenedores (mantiene los datos de BD)
```powershell
docker-compose down
```

### Detener y eliminar TODO (incluyendo datos de BD)
```powershell
docker-compose down -v
```

### Reconstruir imagen tras cambios en Dockerfile
```powershell
docker-compose build --no-cache
docker-compose up -d
```

### Entrar al contenedor web para debugging
```powershell
docker exec -it congreso_web bash
```

### Entrar al contenedor de MySQL
```powershell
docker exec -it congreso_db mysql -u congreso_user -p
```
(Contraseña: `congreso_pass`)

---

## 🗄️ Base de Datos

### Inicialización Automática

La base de datos se inicializa automáticamente la **primera vez** que se levanta el servicio, usando el archivo:
```
Proyecto_conectado/sql/congreso_db.sql
```

### Reiniciar la Base de Datos

Si necesitas reiniciar la BD desde cero:

1. Detén los servicios:
   ```powershell
   docker-compose down -v
   ```

2. Levántalos nuevamente:
   ```powershell
   docker-compose up -d
   ```

---

## 📂 Estructura de Volúmenes

El proyecto usa volúmenes para persistir datos:

| Volumen | Propósito |
|---------|-----------|
| `./Proyecto_conectado` | Código fuente (hot reload) |
| `./data/uploads` | Archivos subidos por usuarios |
| `./data/constancias_pdf` | Constancias generadas en PDF |
| `db_data` | Datos de MySQL (persistentes) |

---

## 🐛 Troubleshooting

### Problema: "Port already in use"

Si ves un error como `bind: address already in use`, significa que otro proceso está usando el puerto.

**Solución:** Cambia los puertos en `docker-compose.yml`:
```yaml
ports:
  - "8090:80"  # Cambiar 8080 por 8090
```

### Problema: "Connection refused" al acceder a la BD

Espera unos segundos más. MySQL puede tardar 10-30 segundos en inicializarse completamente.

Verifica que esté listo:
```powershell
docker-compose logs db | Select-String "ready for connections"
```

### Problema: Cambios en PHP no se reflejan

Por el hot reload con volúmenes, los cambios deberían reflejarse inmediatamente. Si no:
```powershell
docker-compose restart web
```

---

## 🔐 Configuración SMTP (Opcional)

Para habilitar el envío de emails (recuperación de contraseñas):

1. Edita `.env` y descomenta las líneas SMTP:
   ```env
   SMTP_HOST=smtp.office365.com
   SMTP_USER=tu_email@ejemplo.com
   SMTP_PASS=tu_contraseña_app
   SMTP_PORT=587
   SMTP_SECURE=tls
   ```

2. Edita `Proyecto_conectado/php/smtp_config.php` con las mismas credenciales.

3. Reinicia el servicio web:
   ```powershell
   docker-compose restart web
   ```

---

## 📊 Monitoreo de Recursos

Ver uso de recursos de los contenedores:
```powershell
docker stats
```

---

## 🎯 Próximos Pasos

1. **Producción:** Para desplegar en producción, considera:
   - Usar variables de entorno seguras
   - Configurar HTTPS con certificados SSL
   - Usar imágenes optimizadas
   - Configurar backups automáticos de la BD

2. **Desarrollo:** Para desarrollo activo:
   - Los cambios en archivos PHP se reflejan automáticamente
   - Usa `docker-compose logs -f` para debugging en tiempo real

---

## 📞 Soporte

Si encuentras problemas, verifica:
1. Los logs de Docker: `docker-compose logs -f`
2. Que Docker Desktop esté corriendo
3. Que los puertos no estén en uso por otros servicios

---

## 📝 Resumen de URLs

| Servicio | URL |
|----------|-----|
| Login Alumnos | http://localhost:8080/Front-end/login.html |
| Login Admin | http://localhost:8080/Front-end/login_admin.html |
| Registro | http://localhost:8080/Front-end/registro_usuario.html |
| phpMyAdmin | http://localhost:8081 |

---

¡Listo! Tu Sistema de Gestión del Congreso de Mercadotecnia está corriendo en Docker. 🎉
