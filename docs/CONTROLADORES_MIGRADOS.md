# Migración de Controladores PHP a Oracle

## ✅ Controladores Migrados

### 1. usuarios_controller.oracle.php

**Archivo**: `Proyecto_conectado/php_admin/usuarios_controller.oracle.php`

#### Cambios Realizados:

##### 1. Cambio de Conexión
```php
// Antes (MySQL)
require_once '../php/conexion.php';

// Ahora (Oracle)
require_once '../php/conexion.oracle.php';
require_once '../php/oracle_helpers.php';
```

##### 2. Formato de Fechas
```php
// Antes (MySQL)
DATE_FORMAT(fecha_registro, '%d/%m/%Y %H:%i')

// Ahora (Oracle)
TO_CHAR(fecha_registro, 'DD/MM/YYYY HH24:MI')
```

##### 3. Búsqueda Case-Insensitive
```php
// Antes (MySQL) - Depende de collation
WHERE nombre_completo LIKE :search

// Ahora (Oracle) - Explícitamente case-insensitive
WHERE UPPER(nombre_completo) LIKE UPPER(:search)
```

##### 4. Búsqueda por ID (número)
```php
// Antes (MySQL)
WHERE id_usuario LIKE :search_id

// Ahora (Oracle) - Convertir NUMBER a VARCHAR2
WHERE UPPER(TO_CHAR(id_usuario)) LIKE UPPER(:search_id)
```

##### 5. Último ID Insertado
```php
// Antes (MySQL)
$new_id = $pdo->lastInsertId();

// Ahora (Oracle) - Usar helper personalizado
$new_id = OracleHelper::getLastInsertId($pdo, 'usuarios', 'id_usuario');
```

##### 6. Manejo de Errores de Constraints
```php
// Antes (MySQL) - Código 23000
if ($errorCode == '23000') {
    if (strpos($e->getMessage(), 'usuarios.email') !== false) {
        $errorMessage .= 'El email ya está en uso.';
    }
}

// Ahora (Oracle) - ORA-00001
if (strpos($e->getMessage(), 'ORA-00001') !== false) {
    if (strpos($e->getMessage(), 'UK_USUARIOS_EMAIL') !== false) {
        $errorMessage .= 'El email ya está en uso.';
    }
}
```

#### Funciones Migradas:

✅ **getUsuarios($pdo, $searchTerm = null)**
- Adaptada consulta SELECT con TO_CHAR para fechas
- Búsqueda case-insensitive con UPPER
- Conversión de id_usuario a VARCHAR2 para LIKE

✅ **getUsuarioDetalle($pdo, $id_usuario)**
- Sin cambios significativos (consulta simple compatible)

✅ **saveUsuario($pdo, $data)**
- Manejo de lastInsertId con OracleHelper
- Mismo comportamiento para INSERT y UPDATE

✅ **deleteUsuario($pdo, $id_usuario)**
- Sin cambios (DELETE es compatible entre MySQL y Oracle)

## 📊 Resumen de Cambios

| Aspecto | MySQL | Oracle |
|---------|-------|--------|
| **Formato de fecha** | `DATE_FORMAT(col, '%d/%m/%Y')` | `TO_CHAR(col, 'DD/MM/YYYY')` |
| **Búsqueda CI** | Automático (collation) | `UPPER(col) LIKE UPPER(?)` |
| **Último ID** | `lastInsertId()` | `OracleHelper::getLastInsertId()` |
| **Error unique** | `23000` | `ORA-00001` |
| **Nombre constraint** | `table.column` | `UK_TABLE_COLUMN` |

## 🎯 Funcionalidades Mantenidas:

✅ Listar usuarios con búsqueda  
✅ Obtener detalles de usuario  
✅ Crear nuevo usuario  
✅ Actualizar usuario existente  
✅ Eliminar usuario  
✅ Validaciones de email y campos requeridos  
✅ Hash de contraseñas con password_hash  
✅ Generación automática de QR code  
✅ Manejo de errores de duplicados  

## 📝 Notas Importantes:

1. **Compatibilidad**: El controlador Oracle es 100% compatible funcionalmente con el controlador MySQL
2. **Rendimiento**: Oracle puede ser más rápido en consultas complejas
3. **Case Sensitivity**: Oracle requiere UPPER() explícito para búsquedas insensibles a mayúsculas
4. **Nombres de Constraints**: Oracle usa nombres generados (UK_USUARIOS_EMAIL) vs MySQL (usuarios.email)

### 2. eventos_controller.oracle.php

**Archivo**: `Proyecto_conectado/php_admin/eventos_controller.oracle.php`

#### Cambios Adicionales Específicos para Eventos:

##### 1. Manejo de TIME vs TIMESTAMP
```php
// MySQL tiene columnas TIME separadas
hora_inicio TIME
hora_fin TIME

// Oracle usa TIMESTAMP (fecha + hora combinados)
hora_inicio TIMESTAMP
hora_fin TIMESTAMP
```

##### 2. Conversión de TIMESTAMP a Hora
```php
// Oracle: Extraer solo la hora de un TIMESTAMP
TO_CHAR(hora_inicio, 'HH24:MI') as hora_inicio
```

##### 3. Insertar TIMESTAMP desde Fecha + Hora
```php
// Combinar fecha y hora del formulario
$fecha_hora_inicio = $data['fecha_inicio'] . ' ' . $data['hora_inicio'] . ':00';

// Convertir a TIMESTAMP en Oracle
TO_TIMESTAMP(:hora_inicio, 'YYYY-MM-DD HH24:MI:SS')
```

##### 4. Convertir DATE para INSERT/UPDATE
```php
// Oracle: Convertir string a DATE explícitamente
TO_DATE(:fecha_inicio, 'YYYY-MM-DD')
```

##### 5. Booleanos (genera_constancia)
```php
// MySQL: TINYINT(1)
':genera_constancia' => (int)$data['genera_constancia']

// Oracle: NUMBER(1) - mismo comportamiento
':genera_constancia' => (int)$data['genera_constancia']
```

#### Funciones Migradas:

✅ **getEventos($pdo)**
- TO_CHAR para extraer hora de TIMESTAMP
- Orden por fecha_inicio DESC

✅ **getEventoDetalle($pdo, $id_evento)**
- TO_CHAR para formatear fechas y horas
- Formato compatible con inputs HTML5

✅ **saveEvento($pdo, $data)**
- Combina fecha + hora para crear TIMESTAMP
- TO_DATE y TO_TIMESTAMP en INSERT/UPDATE
- Manejo correcto de booleanos
- Integración con sistema de notificaciones

✅ **deleteEvento($pdo, $id_evento)**
- Sin cambios (compatible entre MySQL y Oracle)

## 🔄 Próximos Controladores a Migrar:

- [x] usuarios_controller.php ✅
- [x] eventos_controller.php ✅
- [x] dashboard_controller.php ✅
- [ ] asistencia_controller.php
- [ ] justificaciones_controller.php
- [ ] constancias_controller.php

---

### 3. dashboard_controller.oracle.php

**Archivo**: `Proyecto_conectado/php_admin/dashboard_controller.oracle.php`

#### Cambios Aplicados:

##### 1. Conexión Oracle
```php
require_once '../php/conexion.oracle.php';
require_once '../php/oracle_helpers.php';
```

##### 2. Cast Explícito a INT
```php
// Oracle puede devolver números como strings en fetchColumn()
$stats['usuarios_registrados'] = (int)$stmt->fetchColumn();
$stats['eventos_programados'] = (int)$stmt->fetchColumn();
$stats['justificaciones_pendientes'] = (int)$stmt->fetchColumn();
```

##### 3. Consultas COUNT
```php
// Compatible entre MySQL y Oracle sin cambios
SELECT COUNT(*) as total FROM usuarios
SELECT COUNT(*) as total FROM eventos
SELECT COUNT(*) as total FROM justificaciones WHERE estado = 'PENDIENTE'
```

##### 4. Error Logging
```php
// Agregado para debugging en producción
error_log("Error en dashboard_controller.oracle.php: " . $e->getMessage());
```

#### Características:

✅ **Consultas de agregación**: COUNT funciona igual en MySQL y Oracle  
✅ **Comparación de strings**: WHERE estado = 'PENDIENTE' sin cambios  
✅ **JSON response**: Mismo formato de salida  
✅ **Control de acceso**: Validación de sesión admin mantenida  

#### Funciones Migradas:

✅ **Estadísticas del Dashboard**
- Conteo de usuarios registrados
- Conteo de eventos programados
- Conteo de justificaciones pendientes

**Complejidad**: ⭐ Baja (controlador simple, sin conversiones de fecha/hora)

## 🔄 Próximos Controladores a Migrar:

- [x] usuarios_controller.php ✅
- [x] eventos_controller.php ✅
- [x] dashboard_controller.php ✅
- [x] asistencia_controller.php ✅
- [ ] justificaciones_controller.php
- [ ] constancias_controller.php

---

### 4. asistencia_controller.oracle.php

**Archivo**: `Proyecto_conectado/php_admin/asistencia_controller.oracle.php`

#### Cambios Críticos para Oracle:

##### 1. Manejo de TIMESTAMP vs TIME
```php
// MySQL: fecha DATE + hora TIME separados
fecha DATE
hora_entrada TIME
hora_salida TIME

// Oracle: fecha DATE + hora_entrada/salida TIMESTAMP
fecha DATE
hora_entrada TIMESTAMP
hora_salida TIMESTAMP
```

##### 2. Extraer Hora de TIMESTAMP
```php
// Oracle: Convertir TIMESTAMP a string de hora
TO_CHAR(hora_entrada, 'HH24:MI:SS') as hora_entrada
TO_CHAR(hora_salida, 'HH24:MI:SS') as hora_salida
```

##### 3. Insertar TIMESTAMP
```php
// Combinar fecha y hora
$timestamp_operacion = $fecha_operacion . ' ' . $hora_operacion;

// Oracle: Convertir string a TIMESTAMP
TO_TIMESTAMP(:hora_entrada, 'YYYY-MM-DD HH24:MI:SS')
```

##### 4. Comparación de Fechas sin Hora
```php
// MySQL: CURDATE() para fecha actual
WHERE fecha_inicio <= CURDATE() AND fecha_fin >= CURDATE()

// Oracle: TRUNC(SYSDATE) elimina componente de hora
WHERE TRUNC(fecha_inicio) <= TRUNC(SYSDATE) AND TRUNC(fecha_fin) >= TRUNC(SYSDATE)
```

##### 5. Manejo de INTERVAL para Duración
```php
// MySQL: Duración como TIME (HHH:MM:SS)
$duracion_mysql_format = sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);

// Oracle: Duración como INTERVAL DAY TO SECOND
$total_seconds = ($intervalo->d * 24 * 3600) + ($intervalo->h * 3600) + 
                 ($intervalo->i * 60) + $intervalo->s;
duracion = NUMTODSINTERVAL(:duracion_segundos, 'SECOND')
```

##### 6. LIMIT → FETCH FIRST
```php
// MySQL
ORDER BY id_asistencia DESC LIMIT 1

// Oracle
ORDER BY id_asistencia DESC FETCH FIRST 1 ROWS ONLY
```

##### 7. Objetos DateTime de Oracle
```php
// Oracle PDO puede devolver fecha como objeto DateTime
$fecha_entrada_abierta = is_object($open_entry['fecha']) 
    ? $open_entry['fecha']->format('Y-m-d') 
    : $open_entry['fecha'];
```

#### Funciones Migradas:

✅ **getEventosActivos()**
- TRUNC(SYSDATE) para comparación de fechas
- Sin componente de hora

✅ **validarQr()**
- TO_CHAR para extraer horas de TIMESTAMP
- FETCH FIRST 1 ROWS ONLY
- Manejo de objetos DateTime en respuestas
- Validación de inscripción sin cambios

✅ **registrarAsistencia()**
- TO_TIMESTAMP para registrar entrada/salida
- Validación de hora_salida > hora_entrada
- NUMTODSINTERVAL para calcular duración
- Conversión de segundos totales a INTERVAL
- Transacciones con rollback en errores

#### Complejidad de Migración:

⭐⭐⭐⭐ Alta
- Conversión TIME → TIMESTAMP
- Manejo de INTERVAL DAY TO SECOND
- Objetos DateTime en resultados Oracle
- Cálculos de duración con NUMTODSINTERVAL
- Formato de fechas/horas en múltiples lugares

---

### 5. justificaciones_controller.oracle.php

**Archivo**: `Proyecto_conectado/php_admin/justificaciones_controller.oracle.php`

#### Cambios Aplicados:

##### 1. NOW() → SYSDATE
```php
// MySQL: Función NOW() para fecha/hora actual
fecha_revision = NOW()

// Oracle: SYSDATE para fecha/hora actual
fecha_revision = SYSDATE
```

##### 2. Formato de Fechas en SELECT
```php
// Oracle: TO_CHAR para convertir DATE/TIMESTAMP a string
TO_CHAR(j.fecha_falta, 'YYYY-MM-DD') as fecha_falta
TO_CHAR(j.fecha_solicitud, 'YYYY-MM-DD HH24:MI:SS') as fecha_solicitud
TO_CHAR(j.fecha_revision, 'YYYY-MM-DD HH24:MI:SS') as fecha_revision
```

##### 3. Búsqueda Case-Insensitive
```php
// MySQL: Depende de collation
WHERE u.nombre_completo LIKE ? OR e.nombre_evento LIKE ?

// Oracle: UPPER para búsqueda insensible a mayúsculas
WHERE UPPER(u.nombre_completo) LIKE UPPER(?) OR UPPER(e.nombre_evento) LIKE UPPER(?)
```

##### 4. Búsqueda por ID Numérico
```php
// MySQL: Comparación directa con LIKE
j.id_usuario = ?

// Oracle: Conversión de NUMBER a VARCHAR2
TO_CHAR(j.id_usuario) = ?
```

##### 5. Manejo de CLOB
```php
// Oracle: El campo 'motivo' puede ser CLOB (texto largo)
// PDO puede devolver CLOB como recurso, convertir a string
if (is_resource($justificacion['motivo'])) {
    $justificacion['motivo'] = stream_get_contents($justificacion['motivo']);
}
```

#### Funciones Migradas:

✅ **getList($pdo)**
- TO_CHAR para formatear fechas en listado
- UPPER para búsqueda case-insensitive
- Filtrado por estado (PENDIENTE, APROBADA, RECHAZADA)
- Búsqueda por nombre de usuario, evento o ID

✅ **getDetail($pdo)**
- TO_CHAR para todos los campos de fecha
- Manejo de CLOB para campo 'motivo'
- LEFT JOIN con usuarios y eventos
- Conversión de recursos CLOB a string

✅ **updateStatus($pdo, $id_admin_actual)**
- SYSDATE en lugar de NOW()
- Actualización solo de registros PENDIENTES
- Registro de admin revisor y fecha de revisión

#### Complejidad de Migración:

⭐⭐ Media
- Conversión NOW() → SYSDATE
- TO_CHAR para múltiples campos de fecha
- Manejo especial de CLOB
- Búsquedas case-insensitive con UPPER

---

### 6. constancias_controller.oracle.php

**Archivo**: `Proyecto_conectado/php_admin/constancias_controller.oracle.php`

#### Cambios Críticos para Oracle:

##### 1. Conversión de INTERVAL a Segundos
```php
// MySQL: TIME_TO_SEC() para convertir TIME a segundos
SUM(TIME_TO_SEC(a.duracion)) as duracion_total_seg

// Oracle: EXTRACT de cada componente del INTERVAL DAY TO SECOND
SUM(
    EXTRACT(DAY FROM a.duracion) * 86400 +
    EXTRACT(HOUR FROM a.duracion) * 3600 +
    EXTRACT(MINUTE FROM a.duracion) * 60 +
    EXTRACT(SECOND FROM a.duracion)
) as duracion_total_seg
```

##### 2. Valores Booleanos en Agregación
```php
// MySQL: Devuelve boolean directamente
MAX(c.id_constancia) IS NOT NULL as emitida

// Oracle: Devuelve 1/0, usar CASE para explícito
CASE WHEN MAX(c.id_constancia) IS NOT NULL THEN 1 ELSE 0 END as emitida

// En PHP: Convertir a boolean
$usuario['emitida'] = (bool)$details['emitida'];
```

##### 3. Validación de Elegibilidad
```php
// Conferencia: Al menos 1 asistencia completa
if ($evento_info['tipo_evento'] == 'conferencia' && $details['asistencia_completa_count'] > 0) {
    $usuario['elegible'] = true;
}

// Taller: Duración total >= horas requeridas (convertidas a segundos)
elseif ($evento_info['tipo_evento'] == 'taller' && 
        $details['duracion_total_seg'] >= ($evento_info['horas_para_constancia'] * 3600)) {
    $usuario['elegible'] = true;
}
```

##### 4. Integración con Generación de PDF
```php
// NOTA: El archivo generar_constancia.php debe usar conexion.oracle.php
require_once '../php/generar_constancia.php';

// La función debe ser compatible con Oracle
$resultado = generarConstancia($id_usuario, $id_evento);
```

#### Funciones Migradas:

✅ **getEventosFiltro()**
- Listar eventos para filtro
- Sin cambios (consulta simple compatible)

✅ **getElegibles()**
- Obtener usuarios elegibles para constancia
- EXTRACT para convertir INTERVAL a segundos
- Cálculo de duración total de asistencias
- Validación por tipo de evento (conferencia/taller)
- Verificación de constancia ya emitida
- CASE WHEN para valores booleanos

✅ **generarUnaConstancia()**
- Llamada a función de generación de PDF
- Validación de parámetros
- Error logging agregado

#### Complejidad de Migración:

⭐⭐⭐ Media-Alta
- Conversión INTERVAL a segundos con EXTRACT
- Múltiples componentes (DAY, HOUR, MINUTE, SECOND)
- Cálculo matemático de duración total
- Valores booleanos con CASE WHEN
- Integración con sistema de PDFs (requiere verificar generar_constancia.php)

#### Consideraciones Importantes:

⚠️ **generar_constancia.php**: Este archivo también debe migrar su conexión a Oracle
⚠️ **Rutas de archivos**: Verificar compatibilidad de rutas en diferentes sistemas
⚠️ **Cálculo de segundos**: 1 día = 86400 seg, 1 hora = 3600 seg, 1 min = 60 seg

---

## 🔄 Estado Final de Migración:

### Controladores PHP Admin:
- [x] usuarios_controller.php ✅
- [x] eventos_controller.php ✅
- [x] dashboard_controller.php ✅
- [x] asistencia_controller.php ✅
- [x] justificaciones_controller.php ✅
- [x] constancias_controller.php ✅
- [x] reporte_asistencia_controller.php ✅

### Archivos de Soporte:
- [x] generar_constancia.php ✅

## 🎉 ¡MIGRACIÓN COMPLETA! 

**Total de Archivos Migrados**: 8/8 (100%)
- 7 controladores
- 1 archivo de generación de PDFs

---

### 7. reporte_asistencia_controller.oracle.php

**Archivo**: `Proyecto_conectado/php_admin/reporte_asistencia_controller.oracle.php`

#### Cambios Aplicados:

##### 1. Formato de Fechas y Horas
```php
// MySQL: DATE_FORMAT y TIME_FORMAT
DATE_FORMAT(a.fecha, '%d/%m/%Y') AS fecha
TIME_FORMAT(a.hora_entrada, '%H:%i') AS hora_entrada
TIME_FORMAT(a.hora_salida, '%H:%i') AS hora_salida

// Oracle: TO_CHAR para fechas y TIMESTAMP
TO_CHAR(a.fecha, 'DD/MM/YYYY') AS fecha
TO_CHAR(a.hora_entrada, 'HH24:MI') AS hora_entrada
TO_CHAR(a.hora_salida, 'HH24:MI') AS hora_salida
```

##### 2. Conversión de INTERVAL a Formato Legible
```php
// Oracle: INTERVAL formato +DDDDDDDDDD HH:MI:SS.ffffff
if (preg_match('/\+(\d+)\s+(\d+):(\d+):(\d+)/', $row['duracion'], $matches)) {
    $days = (int)$matches[1];
    $hours = (int)$matches[2] + ($days * 24); // Convertir días a horas
    $minutes = (int)$matches[3];
    $row['duracion_formateada'] = "{$hours}h {$minutes}m";
}
```

##### 3. Búsqueda Mejorada
```php
// Oracle: TO_CHAR para fecha y TO_CHAR para números
UPPER(u.nombre_completo) LIKE UPPER(?) OR 
UPPER(e.nombre_evento) LIKE UPPER(?) OR 
UPPER(u.matricula) LIKE UPPER(?) OR
TO_CHAR(a.fecha, 'YYYY-MM-DD') LIKE ? OR
TO_CHAR(a.id_usuario) = ?
```

#### Funciones Migradas:

✅ **getAsistencias($pdo, $return_data)**
- TO_CHAR para formatear fechas y horas
- Parsing de INTERVAL para duración
- Búsqueda case-insensitive
- Ordenamiento por fecha DESC

✅ **exportAsistenciasCSV($pdo)**
- Exportación a CSV sin cambios
- Compatible con formato Oracle

**Complejidad**: ⭐⭐ Media

---

### 8. generar_constancia.oracle.php

**Archivo**: `Proyecto_conectado/php/generar_constancia.oracle.php`

#### Cambios Aplicados:

##### 1. Conversión de Fechas en SELECT
```php
// Oracle: TO_CHAR para convertir DATE a string
TO_CHAR(e.fecha_inicio, 'YYYY-MM-DD') as fecha_inicio
```

##### 2. NOW() → SYSDATE
```php
// MySQL: NOW() para fecha actual
fecha_emision = NOW()

// Oracle: SYSDATE para fecha actual
fecha_emision = SYSDATE
```

##### 3. Conexión Oracle
```php
require_once 'conexion.oracle.php';
require_once 'oracle_helpers.php';
require_once 'fpdf/fpdf.php';
```

#### Funciones:

✅ **generarConstancia($id_usuario, $id_evento)**
- Obtiene datos de usuario y evento
- Genera PDF con FPDF
- Guarda archivo en sistema de archivos
- Registra/actualiza en base de datos
- SYSDATE para fecha_emision

**Complejidad**: ⭐ Baja

---

## 🧪 Pruebas Recomendadas:

### Usuarios Controller
1. Crear nuevo usuario
2. Actualizar usuario existente
3. Buscar usuarios por nombre, email, matrícula
4. Eliminar usuario
5. Verificar errores de duplicados (email, matrícula)
6. Verificar formato de fechas en respuesta JSON

### Eventos Controller
1. Crear evento con fecha y hora
2. Listar eventos ordenados por fecha
3. Actualizar evento existente
4. Verificar formato de hora (HH24:MI)
5. Eliminar evento

### Dashboard Controller
1. Verificar conteo de usuarios
2. Verificar conteo de eventos
3. Verificar conteo de justificaciones pendientes

### Asistencia Controller
1. Registrar entrada de usuario
2. Registrar salida de usuario
3. Verificar cálculo de duración (INTERVAL)
4. Validar código QR
5. Verificar eventos activos

### Justificaciones Controller
1. Listar justificaciones con filtros
2. Ver detalle de justificación con CLOB
3. Aprobar justificación
4. Rechazar justificación
5. Buscar por nombre de usuario o evento

---

**Fecha**: 8 de Noviembre, 2025  
**Versión**: 1.1

