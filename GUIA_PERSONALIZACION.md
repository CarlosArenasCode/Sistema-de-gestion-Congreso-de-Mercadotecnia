# 🎨 Guía Rápida: Sistema de Personalización

## 📍 Acceso al Panel

1. **Iniciar sesión como Administrador**
2. **Ir al Panel de Administración**
3. **Opción 1**: Clic en menú "**Personalización**"
4. **Opción 2**: Clic en botón morado "**🎨 Personalizar Sitio**"

---

## 🎨 SECCIÓN 1: Colores del Sitio

### Colores Disponibles

| # | Color | Dónde se Aplica |
|---|-------|-----------------|
| 1️⃣ | **Color Primario** | Enlaces, títulos principales, navegación activa |
| 2️⃣ | **Color Secundario** | Botones de acción (Registrarse, Guardar, etc.) |
| 3️⃣ | **Color del Header** | Fondo del encabezado superior |
| 4️⃣ | **Color del Menú** | Fondo de la barra de navegación |
| 5️⃣ | **Color Hover Menú** | Color al pasar el mouse sobre el menú |
| 6️⃣ | **Color del Footer** | Fondo del pie de página |
| 7️⃣ | **Color Carrusel** | Fondo detrás de las imágenes del carrusel |

### 🖱️ Cómo Cambiar Colores

**Método 1: Selector Visual**
```
1. Clic en el cuadro de color 🎨
2. Selecciona el color deseado
3. El código hexadecimal se actualiza automáticamente
```

**Método 2: Código Hexadecimal**
```
1. Escribe el código en el campo de texto (ej: #FF5733)
2. El selector de color se actualiza automáticamente
3. Formato requerido: #RRGGBB (6 caracteres)
```

### 💾 Guardar Cambios

```
Botón "💾 Guardar Colores"
  ↓
Los colores se guardan en la base de datos
  ↓
Se aplican AUTOMÁTICAMENTE a todas las páginas
  ↓
Los usuarios ven los cambios INMEDIATAMENTE
```

### 👁️ Vista Previa

```
Botón "👁️ Vista Previa"
  ↓
Muestra cómo se verán los colores
  ↓
SIN GUARDAR en la base de datos
```

### 🔄 Restablecer

```
Botón "🔄 Restablecer Valores por Defecto"
  ↓
Pregunta confirmación
  ↓
Restaura colores originales del sistema
```

---

## 🖼️ SECCIÓN 2: Carrusel de Imágenes

### 📋 Lista de Imágenes Actual

Cada imagen muestra:
- **☰** = Arrastrar para reordenar
- **Miniatura** = Vista previa de 100x60px
- **Campo de texto** = Texto alternativo (Alt)
- **💾** = Guardar cambios del texto Alt
- **🗑️** = Eliminar imagen

### ➕ Agregar Nueva Imagen

#### Opción A: Desde URL 🌐

```
1. Seleccionar radio button "Desde URL"
2. Pegar URL completa de la imagen
   Ejemplo: https://ejemplo.com/mi-imagen.jpg
3. Escribir texto alternativo (descripción)
4. Clic en "➕ Agregar Imagen"
5. ✅ La imagen aparece en la lista
```

**Ventajas:**
- ✅ No usa espacio en el servidor
- ✅ Actualización automática si cambia la imagen externa
- ✅ Ideal para imágenes de redes sociales o CDNs

**Desventajas:**
- ❌ Si el sitio externo cae, la imagen no se muestra
- ❌ Puede ser más lenta de cargar

#### Opción B: Subir Archivo 💾

```
1. Seleccionar radio button "Subir Archivo"
2. Clic en "Seleccionar Archivo"
3. Elegir imagen de tu computadora
   Formatos: JPG, JPEG, PNG, GIF, WEBP
4. Escribir texto alternativo (descripción)
5. Clic en "➕ Agregar Imagen"
6. 📤 El archivo se sube al servidor
7. ✅ La imagen aparece en la lista
```

**Ventajas:**
- ✅ Imagen siempre disponible
- ✅ Carga más rápida
- ✅ Control total sobre la imagen

**Desventajas:**
- ❌ Usa espacio en el servidor
- ❌ Si cambias la imagen, debes subirla de nuevo

**⚠️ Importante:** Las imágenes subidas se guardan en `uploads/carrusel/` con nombres únicos

### 🔄 Reordenar Imágenes

```
1. Haz clic en el icono ☰ de una imagen
2. Arrastra hacia arriba o abajo
3. Suelta en la posición deseada
4. Repite para todas las imágenes
5. Clic en "🔄 Guardar Orden Actual"
6. ✅ El nuevo orden se guarda en la BD
```

**💡 Tip:** El orden de arriba a abajo es el mismo orden de izquierda a derecha en el carrusel

### ✏️ Editar Texto Alternativo

```
1. Haz clic en el campo de texto de la imagen
2. Modifica el texto
3. Clic en el botón 💾 junto al campo
4. ✅ El texto se actualiza en la BD
```

**❓ ¿Para qué sirve el texto Alt?**
- Accesibilidad para personas con discapacidad visual
- Se muestra si la imagen no carga
- Mejora el SEO del sitio

### 🗑️ Eliminar Imagen

```
1. Clic en el botón 🗑️ de la imagen
2. Confirmar eliminación
3. ✅ Imagen eliminada de la BD
4. 💾 Si era archivo local, se elimina del servidor
```

### 👁️ Vista Previa del Carrusel

La sección inferior muestra una **mini vista previa** del carrusel:
- Se actualiza automáticamente al agregar/eliminar imágenes
- Muestra el efecto de desplazamiento infinito
- Altura: 50px (proporcional)

---

## 🔍 Flujo de Trabajo Recomendado

### Personalización de Colores

```
1. 👁️ Abrir Vista Previa
2. 🎨 Probar diferentes colores
3. ✅ Cuando estés satisfecho, guardar
4. 🌐 Abrir una página de usuario en otra pestaña
5. 🔄 Recargar la página para ver cambios
```

### Gestión del Carrusel

```
1. 📋 Revisar imágenes actuales
2. ➕ Agregar nuevas imágenes (URL o archivo)
3. 🔄 Reordenar arrastrando
4. ✏️ Actualizar textos Alt si es necesario
5. 💾 Guardar orden
6. 🗑️ Eliminar imágenes no deseadas
7. 👁️ Verificar en vista previa
8. 🌐 Abrir página de usuario para confirmar
```

---

## ⚡ Aplicación de Cambios

### ¿Cuándo se aplican los cambios?

| Acción | ¿Cuándo se ve? |
|--------|----------------|
| Guardar colores | **Inmediatamente** en todas las páginas |
| Agregar imagen | Al **recargar** cualquier página con carrusel |
| Eliminar imagen | Al **recargar** cualquier página con carrusel |
| Reordenar imágenes | Al **recargar** cualquier página con carrusel |

### ¿Dónde se aplican?

**Colores:**
- ✅ Todas las páginas de estudiantes
- ✅ Todas las páginas de administrador
- ✅ Login, registro, recuperar contraseña

**Carrusel:**
- ✅ dashboard_alumno.html
- ✅ horario.html
- ✅ mi_qr.html
- ✅ mis_constancias.html
- ✅ justificar_falta.html
- ✅ admin_asistencia.html
- ✅ admin_inscripciones.html
- ✅ admin_justificacion.html

---

## 💾 Persistencia de Datos

### Base de Datos

**Tabla: `personalizacion`**
```
Almacena los 7 colores personalizables
  ↓
Cada color es una fila con:
  - clave (ej: color_primario)
  - valor (ej: #0056b3)
  - fecha de última modificación
  - usuario que modificó
```

**Tabla: `carrusel_imagenes`**
```
Almacena todas las imágenes del carrusel
  ↓
Cada imagen es una fila con:
  - url_imagen (ruta o URL)
  - alt_texto
  - orden (1, 2, 3...)
  - tipo_fuente (url o archivo)
  - activo (sí/no)
  - fecha de creación
  - usuario que creó
```

### Archivos Subidos

```
Proyecto_conectado/uploads/carrusel/
  ├── carrusel_abc123.jpg
  ├── carrusel_def456.png
  └── carrusel_ghi789.webp
```

**Nombre de archivo:**
- Formato: `carrusel_[id único].[extensión]`
- Generado automáticamente con `uniqid()`
- Previene sobrescritura de archivos

---

## 🔒 Seguridad

### Validaciones Implementadas

✅ **Sesión Admin**: Solo administradores pueden acceder  
✅ **Tipos de Archivo**: Solo JPG, JPEG, PNG, GIF, WEBP  
✅ **Formato de Color**: Validación de código hexadecimal  
✅ **SQL Injection**: Uso de prepared statements  
✅ **Nombres Únicos**: Previene sobrescritura de archivos  

---

## 🐛 Solución de Problemas

### "No se guardan los colores"

```
❌ Problema: Clic en guardar pero no cambian
✅ Solución:
  1. Abre consola del navegador (F12)
  2. Ve a pestaña "Network"
  3. Haz clic en "Guardar Colores"
  4. Busca error en la respuesta
  5. Verifica que estés logueado como admin
```

### "Las imágenes no se cargan"

```
❌ Problema: Imagen con icono roto
✅ Solución para URLs:
  1. Verifica que la URL sea accesible
  2. Copia y pega la URL en nueva pestaña
  3. Si no carga, la URL es incorrecta

✅ Solución para archivos:
  1. Verifica que existe: uploads/carrusel/
  2. Verifica permisos de escritura
  3. Intenta subir imagen más pequeña
```

### "No puedo reordenar imágenes"

```
❌ Problema: No se pueden arrastrar
✅ Solución:
  1. Asegúrate de arrastrar por el icono ☰
  2. Mantén presionado el clic
  3. Arrastra lentamente
  4. Suelta sobre otra imagen
  5. Clic en "Guardar Orden Actual"
```

### "Los cambios no se ven en las páginas"

```
❌ Problema: Guardé pero no se aplican
✅ Solución:
  1. Recarga la página con Ctrl+F5
  2. Verifica que personalizacion-loader.js existe
  3. Abre consola (F12) y busca errores
  4. Verifica archivo php/obtener_personalizacion.php
```

---

## 📱 Responsive Design

El sistema funciona en:
- ✅ Desktop (1920px+)
- ✅ Laptop (1366px - 1920px)
- ✅ Tablet (768px - 1365px)
- ✅ Mobile (320px - 767px)

El carrusel se adapta automáticamente:
- Desktop: Altura 60px
- Mobile: Altura 40px
- Velocidad ajustada para mejor experiencia

---

## 🎯 Mejores Prácticas

### Colores

1. **Contraste**: Asegura buen contraste entre texto y fondo
2. **Consistencia**: Usa colores de la paleta institucional
3. **Accesibilidad**: Verifica que personas con daltonismo puedan distinguir
4. **Prueba**: Siempre usa vista previa antes de guardar

### Imágenes

1. **Tamaño**: Usa imágenes de 800x600px o similar (no muy pesadas)
2. **Formato**: Prefiere WEBP o JPG para menor tamaño
3. **Alt Text**: Escribe descripciones significativas
4. **Cantidad**: Máximo 10-15 imágenes para buen rendimiento
5. **URLs**: Verifica que las URLs sean permanentes

---

## 📊 Monitoreo

### Ver qué cambios se hicieron

```sql
-- Ver últimos colores modificados
SELECT * FROM personalizacion 
ORDER BY fecha_modificacion DESC;

-- Ver quién modificó los colores
SELECT p.*, u.nombre, u.email 
FROM personalizacion p
LEFT JOIN usuarios u ON p.modificado_por = u.id
ORDER BY p.fecha_modificacion DESC;

-- Ver imágenes del carrusel activas
SELECT * FROM carrusel_imagenes 
WHERE activo = 1 
ORDER BY orden ASC;
```

---

## 🎓 Preguntas Frecuentes

**P: ¿Puedo tener diferentes colores para admins y estudiantes?**  
R: No actualmente. Los colores se aplican globalmente a todo el sitio.

**P: ¿Cuántas imágenes puedo tener en el carrusel?**  
R: Sin límite técnico, pero se recomienda 10-15 para mejor rendimiento.

**P: ¿Se pueden usar GIFs animados?**  
R: Sí, pero pueden afectar el rendimiento si son muy pesados.

**P: ¿Los cambios afectan a usuarios actualmente navegando?**  
R: Los colores se aplican al recargar la página. Las imágenes también.

**P: ¿Puedo revertir cambios?**  
R: Los colores sí (botón restablecer). Las imágenes eliminadas no se pueden recuperar.

**P: ¿Hay un historial de cambios?**  
R: Actualmente no, pero está en las mejoras futuras.

---

**🎨 ¡Personaliza tu sitio y hazlo único!**
