# 🎓 Sistema de Validación de Alumnos Universitarios

## 📋 Descripción

Este módulo implementa un sistema de **validación de matrículas** contra una base de datos simulada de alumnos de la universidad. El sistema verifica que solo los alumnos registrados oficialmente en la universidad puedan inscribirse a eventos del congreso.

---

## 🗂️ Archivos Creados

### 1. **Base de Datos**
- **📄 `sql/alumnos_universidad.sql`**
  - Tabla `alumnos_universidad` que simula la base de datos oficial de la universidad
  - Incluye 15 alumnos de prueba con diferentes estados (ACTIVO, INACTIVO, EGRESADO)
  - Campos: matrícula, nombre, carrera, semestre, status, email institucional

### 2. **API de Validación**
- **📄 `php/validar_alumno_universidad.php`**
  - Endpoint para validar matrículas contra la base de datos universitaria
  - Soporta métodos GET y POST
  - Retorna información completa del alumno si es válido

### 3. **Integración en Inscripción**
- **📝 `php/inscribir_evento.php` (modificado)**
  - Ahora valida la matrícula antes de permitir inscripción a eventos
  - Verifica que el alumno exista en la BD universitaria
  - Verifica que el status del alumno sea "ACTIVO"

---

## 🚀 Instalación

### Paso 1: Crear la tabla en Oracle

Ejecuta el script SQL en tu base de datos Oracle:

```bash
sqlplus congreso_user/password@//localhost:1521/FREEPDB1
```

```sql
@sql/alumnos_universidad.sql
```

O desde Docker:

```bash
docker exec -i congreso-oracle sqlplus congreso_user/password@FREEPDB1 < Proyecto_conectado/sql/alumnos_universidad.sql
```

### Paso 2: Verificar la instalación

Conecta a tu base de datos y verifica:

```sql
-- Ver todos los alumnos
SELECT * FROM alumnos_universidad;

-- Ver cantidad por status
SELECT status, COUNT(*) as cantidad 
FROM alumnos_universidad 
GROUP BY status;
```

Deberías ver:
- **13 alumnos ACTIVOS**
- **1 alumno INACTIVO**
- **1 alumno EGRESADO**

---

## 📖 Uso

### **1️⃣ Validar Matrícula (API Standalone)**

#### Usando POST:
```bash
curl -X POST http://localhost:8081/php/validar_alumno_universidad.php \
  -H "Content-Type: application/json" \
  -d '{"matricula": "A12345678"}'
```

#### Usando GET:
```bash
curl "http://localhost:8081/php/validar_alumno_universidad.php?matricula=A12345678"
```

#### Respuesta exitosa (Alumno ACTIVO):
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
    "status": "ACTIVO",
    "email_institucional": "juan.perez@universidad.edu.mx",
    "fecha_ingreso": "2023-08-15"
  }
}
```

#### Respuesta de error (Matrícula no encontrada):
```json
{
  "success": false,
  "valid": false,
  "message": "La matrícula no se encuentra registrada en la base de datos de la universidad.",
  "error_code": "MATRICULA_NO_ENCONTRADA"
}
```

#### Respuesta de error (Alumno NO ACTIVO):
```json
{
  "success": false,
  "valid": false,
  "message": "El alumno no puede inscribirse. Status actual: INACTIVO",
  "error_code": "ALUMNO_NO_ACTIVO",
  "data": {
    "matricula": "A99998888",
    "nombre_completo": "Roberto Torres Díaz",
    "status": "INACTIVO"
  }
}
```

---

### **2️⃣ Inscripción a Eventos (Automática)**

El proceso de inscripción ahora incluye validación automática:

1. **Usuario autenticado** intenta inscribirse a un evento
2. El sistema **obtiene su matrícula** de la tabla `usuarios`
3. **Valida** que la matrícula existe en `alumnos_universidad`
4. **Verifica** que el status sea "ACTIVO"
5. Si todo es correcto, **procede con la inscripción**

#### Ejemplo de uso desde frontend:
```javascript
// El usuario ya autenticado intenta inscribirse
fetch('../php/inscribir_evento.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id_evento: 5 })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        alert('¡Inscripción exitosa!');
    } else {
        alert('Error: ' + data.message);
    }
});
```

---

## 🧪 Casos de Prueba

### ✅ **Casos Exitosos (Alumnos ACTIVOS)**

| Matrícula | Nombre | Carrera | Resultado Esperado |
|-----------|--------|---------|-------------------|
| A12345678 | Juan Pérez García | Ingeniería en Sistemas | ✅ Puede inscribirse |
| A87654321 | María López Hernández | Mercadotecnia | ✅ Puede inscribirse |
| A11223344 | Carlos Ramírez Torres | Administración | ✅ Puede inscribirse |
| A55667788 | Ana Martínez Ruiz | Diseño Gráfico | ✅ Puede inscribirse |

### ❌ **Casos de Error**

| Matrícula | Problema | Error Esperado |
|-----------|----------|----------------|
| A99998888 | Alumno INACTIVO | "Tu status en la universidad es: INACTIVO" |
| A77776666 | Alumno EGRESADO | "Tu status en la universidad es: EGRESADO" |
| A00000000 | No existe | "La matrícula no está registrada" |
| (vacío) | Sin matrícula | "Usuario no encontrado o sin matrícula" |

---

## 🔍 Flujo de Validación

```
┌─────────────────────────────────────────┐
│  Usuario intenta inscribirse a evento  │
└────────────────┬────────────────────────┘
                 │
                 ▼
    ┌────────────────────────────┐
    │ ¿Usuario autenticado?      │
    └────┬──────────────────┬────┘
         │ NO               │ SÍ
         ▼                  ▼
    ❌ Error 401    ┌──────────────────┐
                    │ Obtener matrícula│
                    └────────┬─────────┘
                             │
                             ▼
            ┌────────────────────────────────┐
            │ ¿Matrícula existe en usuarios? │
            └────┬──────────────────────┬────┘
                 │ NO                   │ SÍ
                 ▼                      ▼
            ❌ Error         ┌─────────────────────────┐
                             │ Validar en BD Universidad│
                             └────────┬────────────────┘
                                      │
                                      ▼
                    ┌──────────────────────────────────┐
                    │ ¿Existe en alumnos_universidad?  │
                    └────┬────────────────────────┬────┘
                         │ NO                     │ SÍ
                         ▼                        ▼
                    ❌ "Matrícula no    ┌────────────────┐
                       registrada"      │ Verificar status│
                                        └────┬───────────┘
                                             │
                                             ▼
                            ┌────────────────────────────┐
                            │ ¿Status = ACTIVO?          │
                            └────┬──────────────────┬────┘
                                 │ NO               │ SÍ
                                 ▼                  ▼
                            ❌ "Alumno no      ✅ Continuar
                               activo"            inscripción
```

---

## 🛠️ Administración

### Agregar más alumnos de prueba:

```sql
INSERT INTO alumnos_universidad 
(matricula, nombre_completo, carrera, semestre, status, email_institucional, fecha_ingreso) 
VALUES 
('A60606060', 'Nuevo Alumno Test', 'Mercadotecnia', 4, 'ACTIVO', 
 'nuevo.alumno@universidad.edu.mx', TO_DATE('2024-01-20', 'YYYY-MM-DD'));
COMMIT;
```

### Cambiar status de un alumno:

```sql
-- Desactivar alumno
UPDATE alumnos_universidad 
SET status = 'INACTIVO' 
WHERE matricula = 'A12345678';

-- Reactivar alumno
UPDATE alumnos_universidad 
SET status = 'ACTIVO' 
WHERE matricula = 'A12345678';

COMMIT;
```

### Ver alumnos por status:

```sql
-- Ver solo alumnos activos
SELECT matricula, nombre_completo, carrera 
FROM alumnos_universidad 
WHERE status = 'ACTIVO' 
ORDER BY nombre_completo;

-- Ver alumnos inactivos
SELECT matricula, nombre_completo, status 
FROM alumnos_universidad 
WHERE status != 'ACTIVO';
```

---

## 🔐 Seguridad

- ✅ Validación obligatoria antes de inscripción
- ✅ Transacciones atómicas (rollback en caso de error)
- ✅ Normalización de matrículas (mayúsculas)
- ✅ Mensajes de error descriptivos sin exponer información sensible
- ✅ Logging de errores para debugging

---

## 📊 Estructura de la Base de Datos

### Tabla: `alumnos_universidad`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_alumno` | NUMBER (PK) | ID auto-incremental |
| `matricula` | VARCHAR2(50) UNIQUE | Matrícula del alumno |
| `nombre_completo` | VARCHAR2(255) | Nombre completo |
| `carrera` | VARCHAR2(100) | Carrera que estudia |
| `semestre` | NUMBER(2) | Semestre actual (1-12) |
| `status` | VARCHAR2(20) | ACTIVO, INACTIVO, EGRESADO, BAJA |
| `email_institucional` | VARCHAR2(255) | Email universitario |
| `fecha_ingreso` | DATE | Fecha de ingreso a la universidad |
| `fecha_registro` | TIMESTAMP | Fecha de registro en el sistema |

---

## 🐛 Troubleshooting

### Error: "Tabla no existe"
```sql
-- Verificar si la tabla existe
SELECT table_name FROM user_tables WHERE table_name = 'ALUMNOS_UNIVERSIDAD';

-- Si no existe, ejecutar el script
@sql/alumnos_universidad.sql
```

### Error: "Matrícula no está registrada" (pero debería estar)
```sql
-- Verificar datos
SELECT * FROM alumnos_universidad WHERE UPPER(matricula) = 'A12345678';

-- Verificar la matrícula del usuario
SELECT id_usuario, matricula FROM usuarios WHERE id_usuario = 1;
```

### Los alumnos INACTIVOS no pueden inscribirse (comportamiento esperado)
```sql
-- Ver status de un alumno específico
SELECT matricula, nombre_completo, status 
FROM alumnos_universidad 
WHERE matricula = 'A99998888';

-- Para permitir inscripción, cambiar a ACTIVO
UPDATE alumnos_universidad SET status = 'ACTIVO' WHERE matricula = 'A99998888';
COMMIT;
```

---

## 📞 Soporte

Si encuentras problemas:

1. **Verifica que la tabla existe**: `SELECT * FROM alumnos_universidad;`
2. **Revisa los logs**: `error_log` de PHP
3. **Comprueba las matrículas**: Deben coincidir entre `usuarios` y `alumnos_universidad`
4. **Verifica el status**: Solo alumnos con status "ACTIVO" pueden inscribirse

---

## 🎯 Resumen

Este sistema proporciona:

✅ **Validación robusta** de alumnos contra BD universitaria simulada  
✅ **15 alumnos de prueba** con diferentes estados  
✅ **API RESTful** para validación independiente  
✅ **Integración automática** en el proceso de inscripción  
✅ **Mensajes de error claros** para debugging  
✅ **Fácil administración** mediante SQL  

---

**Autor**: Sistema de Gestión - Congreso de Mercadotecnia  
**Fecha**: Noviembre 2025  
**Versión**: 1.0.0
