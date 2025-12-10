# 🎯 GUÍA DE INSTALACIÓN RÁPIDA
## Sistema de Validación de Alumnos Universitarios

---

## ✅ ¿Qué se ha implementado?

Se ha creado un **sistema completo de validación de matrículas** que verifica que los alumnos existen en la base de datos oficial de la universidad antes de permitirles inscribirse a eventos.

### 📦 Archivos creados:

1. **`Proyecto_conectado/sql/alumnos_universidad.sql`**
   - Script SQL con la tabla simulada de alumnos universitarios
   - 15 alumnos de prueba (13 activos, 1 inactivo, 1 egresado)

2. **`oracle/init/04_install_validacion_alumnos.sql`**
   - Script de instalación automatizado para Oracle
   - Incluye verificación y mensajes informativos

3. **`Proyecto_conectado/php/validar_alumno_universidad.php`**
   - API REST para validar matrículas
   - Soporta GET y POST
   - Retorna JSON con información del alumno

4. **`Proyecto_conectado/php/inscribir_evento.php` (modificado)**
   - Ahora valida la matrícula antes de permitir inscripción
   - Verifica que el alumno exista y esté ACTIVO

5. **`Proyecto_conectado/Front-end/test_validacion_alumnos.html`**
   - Interfaz web para probar el sistema de validación
   - Incluye casos de prueba predefinidos

6. **`instalar-validacion-alumnos.ps1`**
   - Script PowerShell para instalación automática

7. **`VALIDACION_ALUMNOS_README.md`**
   - Documentación completa del sistema

---

## 🚀 INSTALACIÓN (3 PASOS)

### **Paso 1: Asegúrate que Docker esté corriendo**

```powershell
.\start-docker.ps1
```

### **Paso 2: Instala el sistema de validación**

**Opción A - Script automático (si hay problemas con el contenedor):**
```powershell
docker cp "install_alumnos_simple.sql" congreso_oracle_db:/tmp/install_simple.sql
docker exec congreso_oracle_db bash -c "sqlplus congreso_user/congreso_pass@FREEPDB1 @/tmp/install_simple.sql"
```

**Opción B - Script PowerShell (actualizado):**
```powershell
.\instalar-validacion-alumnos.ps1
```

Este script:
- ✅ Verifica que Oracle esté corriendo
- ✅ Crea la tabla `alumnos_universidad`
- ✅ Inserta 15 alumnos de prueba
- ✅ Muestra un resumen de la instalación

### **Paso 3: Prueba el sistema**

#### Opción A: Interfaz Web (Recomendado)
Abre en tu navegador:
```
http://localhost:8081/Front-end/test_validacion_alumnos.html
```

#### Opción B: Usando cURL
```bash
# Alumno válido (ACTIVO)
curl "http://localhost:8081/php/validar_alumno_universidad.php?matricula=A12345678"

# Alumno inactivo
curl "http://localhost:8081/php/validar_alumno_universidad.php?matricula=A99998888"

# Matrícula no existe
curl "http://localhost:8081/php/validar_alumno_universidad.php?matricula=A00000000"
```

---

## 🧪 CASOS DE PRUEBA

### ✅ Alumnos que PUEDEN inscribirse (ACTIVO):

| Matrícula | Nombre | Carrera |
|-----------|--------|---------|
| **A12345678** | Juan Pérez García | Ingeniería en Sistemas |
| **A87654321** | María López Hernández | Mercadotecnia |
| **A11223344** | Carlos Ramírez Torres | Administración |
| **A55667788** | Ana Martínez Ruiz | Diseño Gráfico |
| **A99887766** | Luis González Vega | Contaduría Pública |

### ❌ Alumnos que NO pueden inscribirse:

| Matrícula | Nombre | Razón |
|-----------|--------|-------|
| **A99998888** | Roberto Torres Díaz | Status: INACTIVO |
| **A77776666** | Diana Ortiz Jiménez | Status: EGRESADO |
| **A00000000** | (No existe) | No está en la BD universitaria |

---

## 📋 CÓMO FUNCIONA

### 1. **Cuando un alumno intenta inscribirse a un evento:**

```
Usuario autenticado → Obtener matrícula → Validar en BD universitaria
                                                    ↓
                                        ¿Existe y está ACTIVO?
                                                    ↓
                                    SÍ → Permitir inscripción
                                    NO → Mostrar error
```

### 2. **Validaciones que se realizan:**

✅ Usuario está autenticado  
✅ Matrícula existe en tabla `usuarios`  
✅ **Matrícula existe en tabla `alumnos_universidad`** ← NUEVO  
✅ **Status del alumno es "ACTIVO"** ← NUEVO  
✅ No está ya inscrito en el evento  
✅ Hay cupo disponible  

### 3. **Respuestas del sistema:**

#### ✅ Alumno válido:
```json
{
  "success": true,
  "valid": true,
  "message": "Alumno validado correctamente.",
  "data": {
    "matricula": "A12345678",
    "nombre_completo": "Juan Pérez García",
    "carrera": "Ingeniería en Sistemas",
    "semestre": 5,
    "status": "ACTIVO"
  }
}
```

#### ❌ Matrícula no existe:
```json
{
  "success": false,
  "valid": false,
  "message": "La matrícula no está registrada en la BD universitaria.",
  "error_code": "MATRICULA_NO_ENCONTRADA"
}
```

#### ⚠️ Alumno inactivo:
```json
{
  "success": false,
  "valid": false,
  "message": "Tu status en la universidad es: INACTIVO",
  "error_code": "ALUMNO_NO_ACTIVO"
}
```

---

## 🔧 ADMINISTRACIÓN

### Agregar un nuevo alumno:

```sql
INSERT INTO alumnos_universidad 
(matricula, nombre_completo, carrera, semestre, status, email_institucional, fecha_ingreso) 
VALUES 
('A60606060', 'Nuevo Alumno', 'Mercadotecnia', 4, 'ACTIVO', 
 'nuevo.alumno@universidad.edu.mx', SYSDATE);
COMMIT;
```

### Cambiar status de un alumno:

```sql
-- Desactivar
UPDATE alumnos_universidad SET status = 'INACTIVO' WHERE matricula = 'A12345678';

-- Reactivar
UPDATE alumnos_universidad SET status = 'ACTIVO' WHERE matricula = 'A12345678';

COMMIT;
```

### Ver todos los alumnos:

```sql
SELECT matricula, nombre_completo, carrera, status 
FROM alumnos_universidad 
ORDER BY status DESC, nombre_completo;
```

---

## 📊 ESTRUCTURA DE LA BASE DE DATOS

```
┌─────────────────────────────────────────┐
│      ALUMNOS_UNIVERSIDAD (Nueva)        │
├─────────────────────────────────────────┤
│ • id_alumno (PK, auto-increment)        │
│ • matricula (UNIQUE, NOT NULL)          │
│ • nombre_completo                       │
│ • carrera                               │
│ • semestre (1-12)                       │
│ • status (ACTIVO/INACTIVO/EGRESADO/BAJA)│
│ • email_institucional                   │
│ • fecha_ingreso                         │
│ • fecha_registro                        │
└─────────────────────────────────────────┘
              ↑
              │ validación
              │
┌─────────────────────────────────────────┐
│           USUARIOS (Existente)          │
├─────────────────────────────────────────┤
│ • id_usuario (PK)                       │
│ • matricula ← debe existir arriba       │
│ • nombre_completo                       │
│ • email                                 │
│ • ...                                   │
└─────────────────────────────────────────┘
```

---

## 🎯 FLUJO COMPLETO DE INSCRIPCIÓN

```
1. Alumno hace login con matrícula A12345678
   ↓
2. Ve la lista de eventos disponibles
   ↓
3. Hace clic en "Inscribirse" en un evento
   ↓
4. Sistema obtiene su id_usuario de la sesión
   ↓
5. Busca la matrícula en tabla USUARIOS
   ↓
6. 🆕 VALIDA la matrícula en ALUMNOS_UNIVERSIDAD
   ↓
7. 🆕 VERIFICA que status = 'ACTIVO'
   ↓
8. Verifica que no esté ya inscrito
   ↓
9. Verifica que haya cupo
   ↓
10. Registra la inscripción
    ↓
11. Envía notificación por email
    ↓
12. ✅ Muestra mensaje de éxito
```

---

## ⚙️ ARCHIVOS DE CONFIGURACIÓN

No requiere configuración adicional. El sistema usa:
- La misma conexión PDO (`php/conexion.php`)
- La misma base de datos Oracle
- Las mismas sesiones de usuario

---

## 📞 SOPORTE Y TROUBLESHOOTING

### Problema: "Tabla no existe"

```powershell
# Re-ejecutar instalación
.\instalar-validacion-alumnos.ps1
```

### Problema: "Matrícula no encontrada" (pero debería existir)

```sql
-- Verificar la tabla
SELECT * FROM alumnos_universidad WHERE matricula = 'A12345678';

-- Si no existe, insertar manualmente o reinstalar
```

### Problema: El endpoint no responde

```powershell
# Verificar que los servicios estén corriendo
docker ps

# Revisar logs
docker logs congreso-php
```

---

## 📚 DOCUMENTACIÓN COMPLETA

Para más detalles, consulta:
- **`VALIDACION_ALUMNOS_README.md`** - Guía completa
- **`Proyecto_conectado/sql/alumnos_universidad.sql`** - Script SQL
- **`Proyecto_conectado/php/validar_alumno_universidad.php`** - Código del endpoint

---

## ✨ RESUMEN

**Antes:**
- ❌ Cualquier usuario autenticado podía inscribirse a eventos
- ❌ No se validaba contra la base de datos universitaria

**Ahora:**
- ✅ Se valida que la matrícula existe en la BD universitaria
- ✅ Solo alumnos con status "ACTIVO" pueden inscribirse
- ✅ Mensajes de error claros y específicos
- ✅ Sistema listo para pruebas locales

---

**¡Listo para usar! 🚀**
