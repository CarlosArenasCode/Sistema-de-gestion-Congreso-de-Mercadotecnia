# ✅ Sistema de Generación Automática de Constancias - IMPLEMENTADO

## 🎯 Objetivo Cumplido

Se ha implementado un **sistema completamente automático** que genera constancias en formato PDF cuando los eventos finalizan, eliminando la necesidad de generación manual desde HTML.

---

## 📦 Componentes Implementados

### 1. Motor de Generación Automática
**Archivo:** `Proyecto_conectado/php/generar_constancias_automaticas.php`

**Características:**
- ✅ Detecta eventos finalizados automáticamente
- ✅ Espera 30 minutos después de `hora_fin` antes de generar
- ✅ Busca usuarios con asistencia completa (entrada + salida)
- ✅ Genera PDFs individuales con QR code único
- ✅ Evita duplicados verificando constancias existentes
- ✅ Crea logs detallados de cada ejecución
- ✅ Procesa hasta 50 eventos por ejecución
- ✅ Solo procesa eventos de los últimos 7 días

**Formato del PDF generado:**
- Nombre completo (3 ubicaciones)
- Matrícula (3 ubicaciones)
- QR code con datos completos + hash SHA256
- Código QR visible en texto
- Información del evento y ponente
- Fecha y duración

### 2. Endpoint Web
**Archivo:** `Proyecto_conectado/php/ejecutar_generacion_constancias.php`  
**URL:** `http://localhost/Proyecto_conectado/php/ejecutar_generacion_constancias.php`

**Funcionalidad:**
- ✅ Interfaz HTML visual
- ✅ Muestra log en tiempo real
- ✅ Permite re-ejecutar con un clic
- ✅ Accesible desde navegador

### 3. Scripts de PowerShell

#### Script de Configuración
**Archivo:** `configurar-tarea-constancias.ps1`

**Funcionalidad:**
- ✅ Configura Windows Task Scheduler automáticamente
- ✅ Opciones de frecuencia: 15 min / 30 min / 1 hora / manual
- ✅ Ejecuta con permisos del usuario actual
- ✅ Incluye prueba inmediata después de configurar
- ✅ Muestra comandos útiles de gestión

#### Script de Ejecución Manual
**Archivo:** `ejecutar-constancias-auto.ps1`

**Funcionalidad:**
- ✅ Ejecuta generación manualmente
- ✅ Muestra log automáticamente
- ✅ Interfaz simple y directa

#### Script de Prueba Completa
**Archivo:** `probar-generacion-automatica.ps1`

**Funcionalidad:**
- ✅ Verifica todos los componentes del sistema
- ✅ Comprueba PHP, librerías, directorios
- ✅ Consulta eventos en base de datos
- ✅ Ejecuta generación de prueba
- ✅ Muestra PDFs generados
- ✅ Abre carpeta de constancias

### 4. Documentación Completa

#### Guía Técnica Completa
**Archivo:** `GENERACION_AUTOMATICA_CONSTANCIAS.md`

**Contenido:**
- Descripción del sistema
- Instalación paso a paso
- Configuración de tarea automática
- Consultas SQL útiles
- Solución de problemas
- Ejemplos de uso
- Monitoreo de producción

#### Guía de Inicio Rápido
**Archivo:** `INICIO_RAPIDO_CONSTANCIAS_AUTO.md`

**Contenido:**
- Comandos rápidos
- Checklist de instalación
- Verificaciones básicas
- Solución rápida de problemas

---

## 🚀 ¿Cómo Funciona?

### Flujo Automático

```
1. Evento finaliza (ejemplo: 14:00 hrs)
   ↓
2. Sistema espera 30 minutos (14:30 hrs)
   ↓
3. Tarea programada ejecuta cada 15 minutos
   ↓
4. Script detecta evento finalizado
   ↓
5. Busca usuarios con asistencia completa
   ↓
6. Genera PDF para cada usuario elegible
   ↓
7. Guarda en: constancias_pdf/
   ↓
8. Registra en base de datos (tabla: constancias)
   ↓
9. Usuario puede descargar desde su panel
```

### Criterios de Elegibilidad

Una constancia se genera automáticamente cuando:

1. ✅ `eventos.hora_fin < SYSDATE - 30 minutos`
2. ✅ `eventos.genera_constancia = 1`
3. ✅ `asistencias.hora_entrada IS NOT NULL`
4. ✅ `asistencias.hora_salida IS NOT NULL`
5. ✅ No existe constancia previa

---

## 📊 Estructura de Base de Datos

### Tabla: eventos
```sql
- hora_fin TIMESTAMP       -- Hora de finalización del evento
- genera_constancia NUMBER -- 0 = No, 1 = Sí
- horas_para_constancia    -- Duración a mostrar en PDF
```

### Tabla: asistencias
```sql
- hora_entrada TIMESTAMP   -- Registro de entrada
- hora_salida TIMESTAMP    -- Registro de salida
- constancia_generada      -- Flag de control
- ruta_constancia          -- Ruta al PDF
```

### Tabla: constancias
```sql
- id_usuario
- id_evento
- numero_serie             -- Identificador único (AUTO-CONST-...)
- ruta_archivo_pdf         -- constancias_pdf/constancia_X_Y_Z.pdf
- fecha_emision            -- Timestamp de generación
```

---

## 💻 Instalación (3 Pasos)

### Paso 1: Probar el Sistema
```powershell
.\probar-generacion-automatica.ps1
```

### Paso 2: Configurar Tarea Automática
```powershell
.\configurar-tarea-constancias.ps1
# Seleccionar: Opción 1 (cada 15 minutos)
```

### Paso 3: Verificar
```powershell
Get-ScheduledTask -TaskName "Generar_Constancias_Automaticas"
```

---

## 🎓 Ejemplos de Uso

### Escenario 1: Evento Taller de SEO

```
Evento: "Taller de SEO Avanzado"
Hora inicio: 10:00
Hora fin: 14:00
Inscritos: 50 alumnos
Asistencia completa: 42 alumnos

Timeline:
14:00 - Evento finaliza
14:30 - Sistema espera 30 minutos
14:45 - Tarea programada ejecuta (cada 15 min)
14:45 - Se generan 42 PDFs automáticamente
14:46 - Constancias disponibles para descarga
```

### Escenario 2: Verificación Manual

```powershell
# Ver eventos finalizados hoy
docker exec congreso_oracle_db sqlplus -s congreso_user/congreso_pass@FREEPDB1 <<EOF
SELECT nombre_evento, 
       TO_CHAR(hora_fin, 'HH24:MI') as finalizo
FROM eventos 
WHERE TRUNC(hora_fin) = TRUNC(SYSDATE)
  AND genera_constancia = 1;
EOF

# Ejecutar generación manualmente
.\ejecutar-constancias-auto.ps1

# Ver constancias generadas
Get-ChildItem "Proyecto_conectado\constancias_pdf" | 
Where-Object { $_.LastWriteTime -gt (Get-Date).AddHours(-1) }
```

---

## 📁 Archivos Generados

### Ubicaciones Importantes

```
Proyecto_conectado/
├── constancias_pdf/           ← PDFs generados
│   ├── constancia_1_5_1732555803.pdf
│   ├── constancia_2_5_1732555804.pdf
│   └── ...
│
├── logs/                      ← Logs de ejecución
│   ├── constancias_auto_2025-11-25.log
│   ├── constancias_auto_2025-11-26.log
│   └── ...
│
└── temp_qr/                   ← QR temporales (se auto-eliminan)
    └── (vacío - archivos temporales)
```

### Ejemplo de Nombre de PDF
```
constancia_123_45_1732555803.pdf
           ↑   ↑   ↑
           │   │   └─ Timestamp (Unix)
           │   └───── ID del evento
           └───────── ID del usuario
```

---

## 📈 Monitoreo y Logs

### Ver Log del Día
```powershell
Get-Content "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log"
```

### Ejemplo de Log
```
[2025-11-25 14:45:00] === INICIO DE GENERACIÓN AUTOMÁTICA DE CONSTANCIAS ===
[2025-11-25 14:45:01] Eventos finalizados encontrados: 2

--- Procesando Evento ID 5: Taller de SEO Avanzado ---
[2025-11-25 14:45:01]    Hora fin: 2025-11-25 14:00:00
[2025-11-25 14:45:02]    Usuarios elegibles: 42
[2025-11-25 14:45:03]    ✓ Juan Pérez (A12345) - Constancia generada: constancia_1_5_1732555803.pdf
[2025-11-25 14:45:04]    ✓ María López (A12346) - Constancia generada: constancia_2_5_1732555804.pdf
...
[2025-11-25 14:46:15] === RESUMEN DE EJECUCIÓN ===
[2025-11-25 14:46:15] Eventos procesados: 2
[2025-11-25 14:46:15] Constancias generadas: 42
[2025-11-25 14:46:15] Constancias ya existentes: 0
[2025-11-25 14:46:15] Errores: 0
```

---

## 🔧 Gestión de Tarea Programada

### Comandos Útiles

```powershell
# Ver estado
Get-ScheduledTask -TaskName "Generar_Constancias_Automaticas"

# Ejecutar ahora
Start-ScheduledTask -TaskName "Generar_Constancias_Automaticas"

# Deshabilitar
Disable-ScheduledTask -TaskName "Generar_Constancias_Automaticas"

# Habilitar
Enable-ScheduledTask -TaskName "Generar_Constancias_Automaticas"

# Ver historial
Get-ScheduledTask -TaskName "Generar_Constancias_Automaticas" | 
Get-ScheduledTaskInfo

# Eliminar
Unregister-ScheduledTask -TaskName "Generar_Constancias_Automaticas" -Confirm:$false
```

---

## ✅ Checklist de Verificación

- [x] Script PHP de generación automática creado
- [x] Endpoint web para ejecución manual creado
- [x] Scripts PowerShell para configuración creados
- [x] Script de prueba completa creado
- [x] Documentación técnica completa
- [x] Guía de inicio rápido
- [x] Sistema de logs implementado
- [x] Gestión de archivos temporales (QR)
- [x] Validación de duplicados
- [x] Formato de PDF con QR code
- [x] Registro en base de datos
- [x] Sin errores de sintaxis

---

## 📞 Comandos de Diagnóstico

### Verificar Sistema Completo
```powershell
.\probar-generacion-automatica.ps1
```

### Ver Eventos Pendientes
```sql
SELECT 
    id_evento,
    nombre_evento,
    TO_CHAR(hora_fin, 'YYYY-MM-DD HH24:MI') as finalizo,
    ROUND((SYSDATE - hora_fin) * 24 * 60) as minutos_desde_fin
FROM eventos
WHERE genera_constancia = 1
  AND hora_fin < SYSDATE
  AND hora_fin > SYSDATE - INTERVAL '7' DAY
ORDER BY hora_fin DESC;
```

### Ver Constancias Generadas Hoy
```sql
SELECT 
    u.nombre_completo,
    u.matricula,
    e.nombre_evento,
    TO_CHAR(c.fecha_emision, 'HH24:MI:SS') as hora
FROM constancias c
JOIN usuarios u ON c.id_usuario = u.id_usuario
JOIN eventos e ON c.id_evento = e.id_evento
WHERE TRUNC(c.fecha_emision) = TRUNC(SYSDATE)
ORDER BY c.fecha_emision DESC;
```

---

## 🎉 Resumen de Implementación

### ✅ Lo que se logró:

1. **Automatización Completa:**
   - Las constancias se generan SIN intervención manual
   - El sistema se ejecuta automáticamente cada 15 minutos
   - Procesa eventos finalizados en tiempo real

2. **Formato Profesional:**
   - PDFs en formato horizontal (Landscape A4)
   - QR code con todos los datos del usuario
   - Información visible del código QR
   - Nombre y matrícula en múltiples ubicaciones
   - Footer con información completa

3. **Robustez:**
   - Validación de duplicados
   - Manejo de errores
   - Logs detallados
   - Archivos temporales auto-eliminados

4. **Facilidad de Uso:**
   - Configuración en 3 pasos
   - Scripts de prueba incluidos
   - Documentación completa
   - Interfaz web para ejecución manual

5. **Monitoreo:**
   - Logs diarios automáticos
   - Consultas SQL para verificación
   - Comandos PowerShell para gestión

### 🚀 Siguiente Paso: Probar

```powershell
# Ejecutar este comando para probar todo:
.\probar-generacion-automatica.ps1
```

---

**Sistema:** Generación Automática de Constancias  
**Versión:** 1.0  
**Estado:** ✅ Completamente Implementado  
**Fecha:** Noviembre 2025  
**Documentación:** GENERACION_AUTOMATICA_CONSTANCIAS.md
