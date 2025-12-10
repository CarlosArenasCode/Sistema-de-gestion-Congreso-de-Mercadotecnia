# Sistema de Generación Automática de Constancias

## 📋 Descripción

Sistema automático que genera constancias en formato PDF para usuarios que completaron su asistencia a eventos finalizados. El sistema se ejecuta periódicamente y procesa eventos cuya `hora_fin` ya pasó.

## 🎯 Funcionalidad Principal

### ¿Cuándo se generan las constancias?

Las constancias se generan **automáticamente** cuando:

1. ✅ El evento ha finalizado (`hora_fin < SYSDATE - 30 minutos`)
2. ✅ El evento está configurado para generar constancias (`genera_constancia = 1`)
3. ✅ El usuario tiene asistencia completa (`hora_entrada` Y `hora_salida` registradas)
4. ✅ No existe constancia previa para ese usuario/evento

### Proceso Automático

```
Evento Finaliza → Espera 30 min → Script Automático Ejecuta → Busca Usuarios Elegibles → Genera PDFs
```

## 📁 Archivos del Sistema

### 1. Script Principal
**Ubicación:** `Proyecto_conectado/php/generar_constancias_automaticas.php`

- Busca eventos finalizados en los últimos 7 días
- Identifica usuarios con asistencia completa
- Genera constancias en PDF con QR code
- Registra todo en la base de datos
- Crea logs detallados de cada ejecución

**Parámetros configurables:**
```php
$DEBUG_MODE = true;              // Mostrar mensajes en consola
$LIMITE_EVENTOS = 50;            // Máximo de eventos a procesar
$TIEMPO_ESPERA = 30;             // Minutos después de hora_fin
```

### 2. Endpoint Web
**Ubicación:** `Proyecto_conectado/php/ejecutar_generacion_constancias.php`  
**URL:** `http://localhost/Proyecto_conectado/php/ejecutar_generacion_constancias.php`

Permite ejecutar la generación desde el navegador con interfaz visual que muestra:
- Estado de la ejecución
- Log completo en tiempo real
- Botones para re-ejecutar

### 3. Scripts de PowerShell

#### `ejecutar-constancias-auto.ps1`
Script simple para ejecutar manualmente la generación:

```powershell
.\ejecutar-constancias-auto.ps1
```

#### `configurar-tarea-constancias.ps1`
Configura Windows Task Scheduler para ejecución automática:

```powershell
.\configurar-tarea-constancias.ps1
```

Opciones de frecuencia:
- Cada 15 minutos (recomendado para producción)
- Cada 30 minutos
- Cada hora
- Manual (sin automatización)

## 🚀 Instalación y Configuración

### Opción 1: Tarea Automática (Recomendado)

1. **Abrir PowerShell como Administrador**

2. **Ejecutar configurador:**
   ```powershell
   cd "C:\Users\JOSHUA\Desktop\Proyecto\Sistema-de-gestion-Congreso-de-Mercadotecnia"
   .\configurar-tarea-constancias.ps1
   ```

3. **Seleccionar frecuencia** (ejemplo: opción 1 = cada 15 minutos)

4. **Verificar tarea creada:**
   ```powershell
   Get-ScheduledTask -TaskName "Generar_Constancias_Automaticas"
   ```

### Opción 2: Ejecución Manual

**Desde PowerShell:**
```powershell
.\ejecutar-constancias-auto.ps1
```

**Desde Navegador:**
```
http://localhost/Proyecto_conectado/php/ejecutar_generacion_constancias.php
```

**Desde PHP directo:**
```bash
php Proyecto_conectado/php/generar_constancias_automaticas.php
```

### Opción 3: Integración con Cron (Linux/Mac)

```bash
# Editar crontab
crontab -e

# Agregar línea (ejecutar cada 15 minutos)
*/15 * * * * /usr/bin/php /ruta/completa/generar_constancias_automaticas.php
```

## 📊 Logs y Monitoreo

### Ubicación de Logs
```
Proyecto_conectado/logs/constancias_auto_YYYY-MM-DD.log
```

### Formato del Log
```
[2025-11-25 14:30:00] === INICIO DE GENERACIÓN AUTOMÁTICA DE CONSTANCIAS ===
[2025-11-25 14:30:01] Eventos finalizados encontrados: 3
[2025-11-25 14:30:01] 
--- Procesando Evento ID 5: Taller de Marketing Digital ---
[2025-11-25 14:30:01]    Hora fin: 2025-11-25 14:00:00
[2025-11-25 14:30:02]    Usuarios elegibles: 12
[2025-11-25 14:30:03]    ✓ Juan Pérez García (A12345) - Constancia generada: constancia_1_5_1732555803.pdf
[2025-11-25 14:30:04]    ✓ María López Torres (A12346) - Constancia generada: constancia_2_5_1732555804.pdf
...
[2025-11-25 14:30:15] === RESUMEN DE EJECUCIÓN ===
[2025-11-25 14:30:15] Eventos procesados: 3
[2025-11-25 14:30:15] Constancias generadas: 12
[2025-11-25 14:30:15] Constancias ya existentes: 3
[2025-11-25 14:30:15] Errores: 0
```

### Comandos para Revisar Logs

**Ver log del día:**
```powershell
Get-Content "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log"
```

**Ver últimas 30 líneas:**
```powershell
Get-Content "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log" -Tail 30
```

**Monitorear en tiempo real:**
```powershell
Get-Content "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log" -Wait
```

## 🗄️ Estructura de Base de Datos

### Consulta SQL para Eventos Finalizados

```sql
SELECT 
    e.id_evento,
    e.nombre_evento,
    TO_CHAR(e.hora_fin, 'YYYY-MM-DD HH24:MI:SS') as hora_fin,
    COUNT(DISTINCT a.id_usuario) as usuarios_con_asistencia
FROM eventos e
LEFT JOIN asistencias a ON e.id_evento = a.id_evento
    AND a.hora_entrada IS NOT NULL
    AND a.hora_salida IS NOT NULL
WHERE e.genera_constancia = 1
  AND e.hora_fin < SYSDATE - INTERVAL '30' MINUTE
GROUP BY e.id_evento, e.nombre_evento, e.hora_fin
ORDER BY e.hora_fin DESC;
```

### Verificar Constancias Generadas

```sql
SELECT 
    c.id_constancia,
    u.nombre_completo,
    u.matricula,
    e.nombre_evento,
    c.fecha_emision,
    c.ruta_archivo_pdf
FROM constancias c
JOIN usuarios u ON c.id_usuario = u.id_usuario
JOIN eventos e ON c.id_evento = e.id_evento
WHERE TO_CHAR(c.fecha_emision, 'YYYY-MM-DD') = TO_CHAR(SYSDATE, 'YYYY-MM-DD')
ORDER BY c.fecha_emision DESC;
```

## 🔧 Gestión de la Tarea Programada

### Ver Estado de la Tarea
```powershell
Get-ScheduledTask -TaskName "Generar_Constancias_Automaticas" | Format-List
```

### Ejecutar Tarea Manualmente
```powershell
Start-ScheduledTask -TaskName "Generar_Constancias_Automaticas"
```

### Deshabilitar Tarea
```powershell
Disable-ScheduledTask -TaskName "Generar_Constancias_Automaticas"
```

### Habilitar Tarea
```powershell
Enable-ScheduledTask -TaskName "Generar_Constancias_Automaticas"
```

### Ver Historial de Ejecuciones
```powershell
Get-ScheduledTask -TaskName "Generar_Constancias_Automaticas" | Get-ScheduledTaskInfo
```

### Eliminar Tarea
```powershell
Unregister-ScheduledTask -TaskName "Generar_Constancias_Automaticas" -Confirm:$false
```

## 📝 Formato de Constancia Generada

Cada constancia PDF incluye:

1. **Encabezado:** "CONSTANCIA DE ASISTENCIA"
2. **Datos del Usuario:**
   - Nombre completo (3 ubicaciones: centro grande, centro pequeño, footer)
   - Matrícula (3 ubicaciones: centro, footer, footer)
3. **Datos del Evento:**
   - Nombre del evento
   - Ponente
   - Fecha de realización
   - Duración en horas
4. **Código QR de Verificación:**
   - Posición: esquina inferior derecha
   - Contiene: JSON con todos los datos + hash SHA256
   - Texto del código visible (truncado a 30 caracteres)
5. **Footer:**
   - Información del usuario
   - Código QR de verificación
6. **Firma:** Espacio para firma del rector

### Datos del QR Code

```json
{
  "tipo": "CONSTANCIA",
  "id_usuario": 123,
  "matricula": "A12345",
  "nombre": "Juan Pérez García",
  "email": "juan.perez@universidad.edu.mx",
  "evento_id": 5,
  "evento": "Taller de Marketing Digital",
  "fecha_evento": "2025-11-25",
  "codigo_qr_usuario": "QR-USER-123-XYZ",
  "fecha_emision": "2025-11-25 14:30:05",
  "verificacion": "a1b2c3d4e5f6..."
}
```

## ⚙️ Requisitos del Sistema

- **PHP:** 7.4 o superior
- **Oracle Database:** 23ai Free
- **Extensiones PHP:**
  - PDO
  - PDO_OCI
  - GD (para QR codes)
- **Librerías:**
  - FPDF (PDF generation)
  - phpqrcode (QR code generation)
- **Sistema Operativo:**
  - Windows: Task Scheduler
  - Linux/Mac: Cron

## 🐛 Solución de Problemas

### Problema: No se generan constancias

**Verificar:**
1. ¿El evento ya finalizó + 30 minutos?
   ```sql
   SELECT nombre_evento, hora_fin, 
          CASE WHEN hora_fin < SYSDATE - INTERVAL '30' MINUTE 
               THEN 'SÍ' ELSE 'NO' END as puede_generar
   FROM eventos WHERE genera_constancia = 1;
   ```

2. ¿Hay usuarios con asistencia completa?
   ```sql
   SELECT COUNT(*) FROM asistencias 
   WHERE id_evento = 5 
     AND hora_entrada IS NOT NULL 
     AND hora_salida IS NOT NULL;
   ```

3. ¿La tarea está habilitada?
   ```powershell
   Get-ScheduledTask -TaskName "Generar_Constancias_Automaticas" | 
   Select TaskName, State
   ```

### Problema: Error de permisos en archivos

**Solución:**
```powershell
# Dar permisos de escritura a directorios
icacls "Proyecto_conectado\constancias_pdf" /grant Users:F /T
icacls "Proyecto_conectado\logs" /grant Users:F /T
icacls "Proyecto_conectado\temp_qr" /grant Users:F /T
```

### Problema: PHP no encontrado

**Solución:**
1. Verificar instalación de XAMPP
2. Actualizar ruta en scripts:
   ```powershell
   # Editar configurar-tarea-constancias.ps1 o ejecutar-constancias-auto.ps1
   $phpPath = "C:\xampp\php\php.exe"  # Ajustar según tu instalación
   ```

### Problema: Errores de conexión a Oracle

**Verificar:**
```powershell
# Estado del contenedor Docker
docker ps | Select-String "oracle"

# Logs de conexión
Get-Content "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log" | 
Select-String "ERROR"
```

## 📈 Monitoreo de Producción

### Dashboard de Estadísticas (SQL)

```sql
-- Constancias generadas hoy
SELECT COUNT(*) as total_hoy
FROM constancias
WHERE TO_CHAR(fecha_emision, 'YYYY-MM-DD') = TO_CHAR(SYSDATE, 'YYYY-MM-DD');

-- Constancias por evento (últimos 7 días)
SELECT 
    e.nombre_evento,
    COUNT(c.id_constancia) as constancias_generadas,
    e.cupo_actual as total_inscritos,
    ROUND(COUNT(c.id_constancia) * 100.0 / NULLIF(e.cupo_actual, 0), 2) as porcentaje
FROM eventos e
LEFT JOIN constancias c ON e.id_evento = c.id_evento
    AND c.fecha_emision > SYSDATE - INTERVAL '7' DAY
WHERE e.genera_constancia = 1
GROUP BY e.id_evento, e.nombre_evento, e.cupo_actual
ORDER BY constancias_generadas DESC;

-- Próximos eventos a finalizar
SELECT 
    nombre_evento,
    TO_CHAR(hora_fin, 'YYYY-MM-DD HH24:MI') as hora_fin,
    ROUND((CAST(hora_fin AS DATE) - SYSDATE) * 24 * 60) as minutos_restantes
FROM eventos
WHERE genera_constancia = 1
  AND hora_fin > SYSDATE
  AND hora_fin < SYSDATE + INTERVAL '2' HOUR
ORDER BY hora_fin ASC;
```

## 🎓 Ejemplos de Uso

### Ejemplo 1: Ejecutar generación tras finalizar un evento

```powershell
# El evento "Taller de SEO" finalizó a las 14:00
# Esperar 30 minutos y ejecutar:

Start-ScheduledTask -TaskName "Generar_Constancias_Automaticas"

# O manualmente:
.\ejecutar-constancias-auto.ps1
```

### Ejemplo 2: Verificar constancias de un evento específico

```sql
-- Ver constancias del evento ID 5
SELECT 
    u.nombre_completo,
    u.matricula,
    c.numero_serie,
    c.ruta_archivo_pdf,
    TO_CHAR(c.fecha_emision, 'DD/MM/YYYY HH24:MI') as emitida_el
FROM constancias c
JOIN usuarios u ON c.id_usuario = u.id_usuario
WHERE c.id_evento = 5
ORDER BY c.fecha_emision DESC;
```

### Ejemplo 3: Re-generar constancias con errores

```sql
-- Eliminar constancias con errores (PDF no generado)
DELETE FROM constancias 
WHERE ruta_archivo_pdf IS NULL 
   OR LENGTH(ruta_archivo_pdf) < 10;

-- Ejecutar generación nuevamente
```

## 📞 Soporte

Para problemas o dudas:
1. Revisar logs en `Proyecto_conectado/logs/`
2. Verificar base de datos con queries de diagnóstico
3. Ejecutar en modo DEBUG para mensajes detallados

## 🔄 Actualización del Sistema

Si se modifican las plantillas de constancia:

1. Editar `generar_constancias_automaticas.php` función `generarConstanciaPDF()`
2. Probar con ejecución manual
3. Verificar formato del PDF generado
4. Re-ejecutar para eventos ya procesados si es necesario

---

**Versión:** 1.0  
**Última actualización:** Noviembre 2025  
**Autor:** Sistema de Gestión - Congreso de Mercadotecnia
