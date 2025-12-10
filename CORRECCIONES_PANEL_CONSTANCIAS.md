# 🔧 CORRECCIONES APLICADAS - Panel de Constancias Admin

## ✅ Problema Resuelto

El panel de administración de constancias ahora está corregido. Los problemas eran:

### 1. **Query incorrecta en generación automática**
- ❌ Antes: Buscaba directamente en tabla `asistencias`
- ✅ Ahora: Busca usuarios INSCRITOS y verifica asistencia completa

### 2. **Nombre incorrecto de tabla**
- ❌ Antes: `asistencia` (singular)
- ✅ Ahora: `asistencias` (plural)

### 3. **Filtro de eventos no aplicado**
- ❌ Antes: Mostraba TODOS los eventos
- ✅ Ahora: Solo muestra eventos con `genera_constancia = 1`

---

## 📊 Estado Actual de la Base de Datos

### Eventos Configurados:
```
ID: 25
Nombre: Conferencia Magistral
Tipo: conferencia
Fecha: 2025-11-25
Genera constancia: SÍ ✓
Inscritos: 1
```

---

## 🚀 Para Probar el Panel Admin

### Paso 1: Iniciar Apache
```
1. Abre XAMPP Control Panel
2. Click en "Start" en Apache
3. Espera a que aparezca verde
```

O ejecuta:
```powershell
Start-Process "C:\xampp\xampp-control.exe"
```

### Paso 2: Abrir Panel de Constancias
```
http://localhost/Proyecto_conectado/Front-end/admin_constancias.html
```

### Paso 3: Verificar Dropdown de Eventos
Deberías ver:
- ✓ Dropdown con eventos disponibles
- ✓ "Conferencia Magistral" debe aparecer en la lista

---

## 🧪 Flujo Completo de Prueba

### A. Verificar que aparezcan eventos
1. Abre panel admin de constancias
2. Mira el dropdown "Seleccione un Evento"
3. Debe mostrar: **Conferencia Magistral**

### B. Verificar usuarios inscritos
1. Selecciona el evento del dropdown
2. Debe mostrar tabla con usuarios inscritos
3. Columnas:
   - Nombre del Participante
   - Elegible para Constancia (Sí/No)
   - Estado de Emisión (Emitida/No emitida)
   - Acciones (Generar/Ver PDF)

### C. Estados posibles por usuario

| Estado | Significado | Acción Disponible |
|--------|-------------|-------------------|
| **Elegible: Sí** + No emitida | Usuario completó asistencia | Botón "Generar" |
| **Elegible: Sí** + Emitida | Constancia ya generada | Botones "Ver PDF" y "Regenerar" |
| **Elegible: No** | Falta entrada o salida | Botón deshabilitado |

---

## 📋 Requisitos para que un Usuario sea Elegible

### Para Conferencias:
```
✓ Usuario INSCRITO al evento
✓ Registró ENTRADA (escaneó QR al llegar)
✓ Registró SALIDA (escaneó QR al irse)
```

### Para Talleres:
```
✓ Usuario INSCRITO al evento
✓ Registró ENTRADA y SALIDA
✓ Duración total ≥ horas_para_constancia
```

---

## 🔍 Verificación de Datos

### Ver estado de usuarios del evento 25:
```sql
SELECT 
    u.nombre_completo,
    u.matricula,
    i.estado as inscripcion,
    CASE WHEN a.hora_entrada IS NOT NULL THEN 'Sí' ELSE 'No' END as entrada,
    CASE WHEN a.hora_salida IS NOT NULL THEN 'Sí' ELSE 'No' END as salida,
    CASE 
        WHEN a.hora_entrada IS NOT NULL AND a.hora_salida IS NOT NULL THEN 'ELEGIBLE'
        WHEN a.hora_entrada IS NOT NULL THEN 'Falta salida'
        ELSE 'Sin asistencia'
    END as estado
FROM inscripciones i
JOIN usuarios u ON i.id_usuario = u.id_usuario
LEFT JOIN asistencias a ON i.id_usuario = a.id_usuario AND i.id_evento = a.id_evento
WHERE i.id_evento = 25;
```

Ejecuta con:
```powershell
.\verificar-usuarios-para-constancias.ps1
```

---

## 🛠️ Si No Aparecen Eventos

### Causa 1: Evento no tiene genera_constancia = 1
**Solución:**
```sql
UPDATE eventos 
SET genera_constancia = 1 
WHERE id_evento = 25;
COMMIT;
```

### Causa 2: Apache no está corriendo
**Solución:**
- Inicia XAMPP Apache

### Causa 3: Error en el controlador
**Verificar logs:**
```powershell
Get-Content "C:\xampp\apache\logs\error.log" -Tail 20
```

---

## 📄 Archivos Modificados

1. **php/generar_constancias_automaticas.php**
   - Cambiado query para buscar desde `inscripciones`
   - Agregada validación de elegibilidad por tipo de evento
   - Agregado cálculo de duración total

2. **php_admin/constancias_controller.php**
   - Función `getEventosFiltro()`: Ahora filtra por `genera_constancia = 1`
   - Función `getElegibles()`: Corregido nombre de tabla a `asistencias`
   - Mejorado manejo de CLOBs

---

## ✅ Checklist Final

- [x] Query de generación automática corregida
- [x] Nombre de tabla corregido (asistencias)
- [x] Filtro de eventos aplicado (solo genera_constancia = 1)
- [x] Manejo de CLOBs implementado
- [x] Validación de elegibilidad por tipo de evento
- [ ] **PENDIENTE: Iniciar Apache en XAMPP**
- [ ] **PENDIENTE: Probar panel admin**

---

## 🎯 Siguiente Paso

**INICIA APACHE Y PRUEBA:**

```powershell
# 1. Inicia XAMPP
Start-Process "C:\xampp\xampp-control.exe"

# 2. Abre navegador en:
# http://localhost/Proyecto_conectado/Front-end/admin_constancias.html

# 3. Verifica que aparezca "Conferencia Magistral" en el dropdown
```

---

## 📞 Si Persisten Problemas

1. **Verifica Apache:**
   ```powershell
   Get-Process -Name "httpd" -ErrorAction SilentlyContinue
   ```

2. **Verifica datos:**
   ```powershell
   .\verificar-usuarios-para-constancias.ps1
   ```

3. **Revisa logs de PHP:**
   ```powershell
   Get-Content "C:\xampp\apache\logs\error.log" -Tail 50
   ```

---

**Última actualización:** Noviembre 25, 2025  
**Estado:** ✅ Correcciones aplicadas - Listo para probar
