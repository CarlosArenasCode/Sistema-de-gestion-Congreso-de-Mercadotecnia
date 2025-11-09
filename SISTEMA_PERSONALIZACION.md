# Sistema de Personalización del Sitio - Instrucciones de Instalación

## 📋 Descripción

Este sistema permite al administrador personalizar los colores e imágenes del carrusel del sitio web del Congreso Universitario. Los cambios se aplican automáticamente a todas las páginas que ven los usuarios/alumnos.

## ✨ Características

- 🎨 **Personalización de Colores**: Cambia 7 colores principales del sitio
- 🖼️ **Gestión del Carrusel**: Agrega, edita, elimina y reordena imágenes
- 🌐 **URLs Externas**: Carga imágenes desde URLs
- 💾 **Subida de Archivos**: Sube imágenes desde tu computadora
- 🔄 **Aplicación Automática**: Los cambios se aplican instantáneamente a todas las páginas
- 📱 **Vista Previa en Tiempo Real**: Ve los cambios antes de guardar

## 📦 Archivos Creados

### Frontend
- `Front-end/admin_personalizacion.html` - Página de administración de personalización
- `js/personalizacion-loader.js` - Script que carga y aplica la personalización en las páginas de usuarios

### Backend
- `php/obtener_personalizacion.php` - API pública para obtener configuración
- `php_admin/personalizacion_controller.php` - Controlador CRUD para admin

### JavaScript Admin
- `js_admin/admin_personalizacion.js` - Lógica de la interfaz de administración

### Base de Datos
- `sql/personalizacion.sql` - Script SQL para crear las tablas necesarias

### Scripts de Instalación
- `agregar-personalizacion-loader.ps1` - Script PowerShell para agregar loader a páginas

## 🔧 Instalación

### 1. Ejecutar Script SQL

**Opción A: Usando phpMyAdmin**
1. Abre phpMyAdmin en `http://localhost:8081` (o `http://localhost/phpmyadmin`)
2. Selecciona la base de datos `congreso_db`
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido de `sql/personalizacion.sql`
5. Haz clic en "Continuar"

**Opción B: Usando MySQL Command Line**
```bash
# Si estás usando XAMPP en Windows
cd c:\xampp\mysql\bin
mysql -u root -p congreso_db < "c:\xampp\htdocs\Proyecto\Sistema-de-gestion-Congreso-de-Mercadotecnia\Proyecto_conectado\sql\personalizacion.sql"
```

**Opción C: Usando Docker (si los contenedores están corriendo)**
```powershell
Get-Content Proyecto_conectado/sql/personalizacion.sql | docker exec -i congreso_db mysql -uroot -prootpassword congreso_db
```

### 2. Crear Directorio para Uploads

El sistema necesita un directorio para guardar las imágenes subidas:

```powershell
# Crear directorio uploads/carrusel
New-Item -ItemType Directory -Force -Path "Proyecto_conectado/uploads/carrusel"
```

**Para Docker:**
```powershell
docker exec congreso_web mkdir -p /var/www/html/uploads/carrusel
docker exec congreso_web chmod 777 /var/www/html/uploads/carrusel
```

### 3. Verificar Permisos

Asegúrate de que PHP tenga permisos de escritura en el directorio uploads:

**Windows (XAMPP):**
- Clic derecho en `Proyecto_conectado/uploads/carrusel`
- Propiedades → Seguridad
- Dar permisos de "Control total" al usuario IUSR o Everyone

**Linux/Docker:**
```bash
chmod -R 777 Proyecto_conectado/uploads/carrusel
```

### 4. Actualizar Páginas Existentes (Ya realizado)

El script `agregar-personalizacion-loader.ps1` ya agregó el loader a estas páginas:
- ✅ `dashboard_alumno.html`
- ✅ `horario.html`
- ✅ `mi_qr.html`
- ✅ `mis_constancias.html`
- ✅ `justificar_falta.html`

### 5. Acceder al Panel de Personalización

1. Inicia sesión como administrador
2. Ve al Panel de Administración
3. Haz clic en "🎨 Personalizar Sitio" en el menú o en los accesos rápidos
4. URL directa: `http://localhost:8080/Front-end/admin_personalizacion.html`

## 🎨 Uso del Sistema

### Personalizar Colores

1. En la sección "🎨 Colores del Sitio":
   - Usa los selectores de color o ingresa códigos hexadecimales
   - Haz clic en "👁️ Vista Previa" para ver los cambios
   - Haz clic en "💾 Guardar Colores" para aplicar
   - Opción: "🔄 Restablecer Valores por Defecto"

### Gestionar Imágenes del Carrusel

#### Agregar Imagen desde URL
1. Selecciona "Desde URL"
2. Ingresa la URL completa de la imagen
3. Ingresa un texto alternativo (Alt)
4. Haz clic en "➕ Agregar Imagen"

#### Agregar Imagen desde Archivo
1. Selecciona "Subir Archivo"
2. Haz clic en "Seleccionar Archivo"
3. Elige una imagen (JPG, PNG, GIF, WEBP)
4. Ingresa un texto alternativo (Alt)
5. Haz clic en "➕ Agregar Imagen"

#### Reordenar Imágenes
1. Arrastra las imágenes por el icono ☰
2. Suéltalas en la posición deseada
3. Haz clic en "🔄 Guardar Orden Actual"

#### Editar/Eliminar Imágenes
- **Editar texto Alt**: Cambia el texto y haz clic en 💾
- **Eliminar**: Haz clic en 🗑️ y confirma

## 🗄️ Estructura de Base de Datos

### Tabla: `personalizacion`
Almacena la configuración de colores y otras personalizaciones.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| clave | VARCHAR(100) | Nombre de la configuración (ej: `color_primario`) |
| valor | TEXT | Valor de la configuración (ej: `#0056b3`) |
| tipo | ENUM | Tipo: `color`, `imagen`, `texto` |
| descripcion | VARCHAR(255) | Descripción legible |
| fecha_modificacion | TIMESTAMP | Última modificación |
| modificado_por | INT | ID del usuario que modificó |

### Tabla: `carrusel_imagenes`
Almacena las imágenes del carrusel.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| url_imagen | TEXT | URL o ruta de la imagen |
| alt_texto | VARCHAR(255) | Texto alternativo |
| orden | INT | Orden de visualización |
| activo | BOOLEAN | Si la imagen está activa |
| tipo_fuente | ENUM | `url` o `archivo` |
| fecha_creacion | TIMESTAMP | Fecha de creación |
| fecha_modificacion | TIMESTAMP | Última modificación |
| creado_por | INT | ID del usuario que creó |

## 🔒 Seguridad

- ✅ Solo administradores pueden acceder al panel de personalización
- ✅ Validación de sesión en PHP (`$_SESSION['tipo'] === 'admin'`)
- ✅ Validación de tipos de archivo permitidos (JPG, PNG, GIF, WEBP)
- ✅ Nombres únicos para archivos subidos (previene sobrescritura)
- ✅ Validación de formato hexadecimal para colores
- ✅ Protección contra inyección SQL (prepared statements)

## 📋 Colores Personalizables

| Color | Uso |
|-------|-----|
| `color_primario` | Enlaces, títulos, navegación activa |
| `color_secundario` | Botones de acción (registrarse, guardar) |
| `color_header` | Fondo del encabezado |
| `color_nav` | Fondo del menú de navegación |
| `color_nav_hover` | Color al pasar el mouse sobre menú |
| `color_footer` | Fondo del pie de página |
| `color_carrusel_fondo` | Fondo del carrusel de imágenes |

## 🐛 Solución de Problemas

### Las imágenes subidas no se guardan
- Verifica que el directorio `uploads/carrusel` existe
- Verifica permisos de escritura en el directorio
- Revisa los logs de PHP para errores

### Los colores no se aplican
- Verifica que `personalizacion-loader.js` está incluido en las páginas
- Abre la consola del navegador (F12) y busca errores
- Verifica que la API `obtener_personalizacion.php` responde correctamente

### Error de base de datos
- Verifica que las tablas existen: `personalizacion` y `carrusel_imagenes`
- Ejecuta el script SQL nuevamente si es necesario
- Verifica la conexión a la base de datos en `conexion.php`

## 🔄 Cómo Funciona

1. **Admin cambia colores/imágenes** → Guarda en BD
2. **Usuario carga una página** → `personalizacion-loader.js` ejecuta
3. **Loader consulta** → `obtener_personalizacion.php` (API)
4. **API responde** → JSON con colores e imágenes
5. **Loader aplica** → CSS dinámico + actualiza carrusel
6. **Usuario ve** → Sitio personalizado

## 📝 Notas

- Los cambios son **inmediatos** para todos los usuarios
- Las imágenes subidas se guardan en `uploads/carrusel/`
- Las URLs externas se guardan directamente (no se descargan)
- El carrusel duplica las imágenes para crear efecto de bucle infinito
- Máximo recomendado: 10-15 imágenes en el carrusel para rendimiento óptimo

## 🚀 Próximas Mejoras

- [ ] Caché de configuración para mejor rendimiento
- [ ] Historial de cambios (audit log)
- [ ] Previsualización de cambios antes de aplicar globalmente
- [ ] Múltiples temas predefinidos
- [ ] Programación de cambios (ej: colores especiales para eventos)
- [ ] Optimización automática de imágenes subidas
- [ ] Soporte para videos en el carrusel

## 📞 Soporte

Si encuentras algún problema o tienes sugerencias:
1. Revisa esta documentación
2. Verifica los logs en `php/logs/`
3. Contacta al equipo de desarrollo

---

**Versión:** 1.0  
**Fecha:** 18 de Octubre, 2025  
**Desarrollador:** Sistema de Gestión Congreso de Mercadotecnia
