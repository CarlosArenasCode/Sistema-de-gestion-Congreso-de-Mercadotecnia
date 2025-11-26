# 🎓 Generación Automática de Constancias - Inicio Rápido

## 🚀 ¿Qué hace este sistema?

Genera **automáticamente** constancias en PDF cuando un evento finaliza, para todos los usuarios que registraron **asistencia completa** (entrada Y salida).

## ⚡ Inicio Rápido

### 1️⃣ Probar el Sistema

```powershell
.\probar-generacion-automatica.ps1
```

Este script:
- ✓ Verifica todos los componentes
- ✓ Muestra eventos finalizados
- ✓ Ejecuta la generación
- ✓ Muestra constancias generadas

### 2️⃣ Configurar Tarea Automática (Recomendado)

```powershell
.\configurar-tarea-constancias.ps1
```

Opciones:
- **Opción 1:** Cada 15 minutos ← Recomendado para producción
- **Opción 2:** Cada 30 minutos
- **Opción 3:** Cada hora
- **Opción 4:** Solo manual

### 3️⃣ Ejecutar Manualmente

**PowerShell:**
```powershell
.\ejecutar-constancias-auto.ps1
```

**Navegador:**
```
http://localhost/Proyecto_conectado/php/ejecutar_generacion_constancias.php
```

**PHP directo:**
```bash
php Proyecto_conectado/php/generar_constancias_automaticas.php
```

## 📋 Requisitos

El sistema genera constancias cuando:

1. ✅ Evento finalizado (hora_fin < ahora - 30 minutos)
2. ✅ Evento configurado para generar constancias (`genera_constancia = 1`)
3. ✅ Usuario con asistencia completa (`hora_entrada` Y `hora_salida`)
4. ✅ Sin constancia previa

## 📁 Archivos Importantes

| Archivo | Ubicación | Propósito |
|---------|-----------|-----------|
| Script automático | `Proyecto_conectado/php/generar_constancias_automaticas.php` | Motor de generación |
| Endpoint web | `Proyecto_conectado/php/ejecutar_generacion_constancias.php` | Interfaz web |
| Constancias PDF | `Proyecto_conectado/constancias_pdf/` | PDFs generados |
| Logs | `Proyecto_conectado/logs/` | Registros de ejecución |

## 🔍 Verificar Ejecución

### Ver Log del Día
```powershell
Get-Content "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log"
```

### Ver Últimas 20 Líneas
```powershell
Get-Content "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log" -Tail 20
```

### Verificar Tarea Programada
```powershell
Get-ScheduledTask -TaskName "Generar_Constancias_Automaticas"
```

### Ejecutar Tarea Ahora
```powershell
Start-ScheduledTask -TaskName "Generar_Constancias_Automaticas"
```

## 📊 Consultas Útiles (SQL)

### Eventos finalizados pendientes
```sql
SELECT 
    id_evento,
    nombre_evento,
    TO_CHAR(hora_fin, 'YYYY-MM-DD HH24:MI') as finalizo
FROM eventos
WHERE genera_constancia = 1
  AND hora_fin < SYSDATE - INTERVAL '30' MINUTE
  AND hora_fin > SYSDATE - INTERVAL '7' DAY
ORDER BY hora_fin DESC;
```

### Constancias generadas hoy
```sql
SELECT 
    COUNT(*) as total,
    COUNT(DISTINCT id_evento) as eventos,
    COUNT(DISTINCT id_usuario) as usuarios
FROM constancias
WHERE TO_CHAR(fecha_emision, 'YYYY-MM-DD') = TO_CHAR(SYSDATE, 'YYYY-MM-DD');
```

### Usuarios elegibles sin constancia
```sql
SELECT 
    u.nombre_completo,
    u.matricula,
    e.nombre_evento,
    TO_CHAR(e.hora_fin, 'YYYY-MM-DD HH24:MI') as finalizo
FROM asistencias a
JOIN usuarios u ON a.id_usuario = u.id_usuario
JOIN eventos e ON a.id_evento = e.id_evento
LEFT JOIN constancias c ON a.id_usuario = c.id_usuario AND a.id_evento = c.id_evento
WHERE e.genera_constancia = 1
  AND e.hora_fin < SYSDATE - INTERVAL '30' MINUTE
  AND a.hora_entrada IS NOT NULL
  AND a.hora_salida IS NOT NULL
  AND c.id_constancia IS NULL;
```

## 🛠️ Gestión de Tarea Programada

| Acción | Comando PowerShell |
|--------|-------------------|
| Ver estado | `Get-ScheduledTask -TaskName "Generar_Constancias_Automaticas"` |
| Ejecutar ahora | `Start-ScheduledTask -TaskName "Generar_Constancias_Automaticas"` |
| Deshabilitar | `Disable-ScheduledTask -TaskName "Generar_Constancias_Automaticas"` |
| Habilitar | `Enable-ScheduledTask -TaskName "Generar_Constancias_Automaticas"` |
| Eliminar | `Unregister-ScheduledTask -TaskName "Generar_Constancias_Automaticas" -Confirm:$false` |
| Ver historial | `Get-ScheduledTask -TaskName "Generar_Constancias_Automaticas" \| Get-ScheduledTaskInfo` |

## 🐛 Solución de Problemas

### No se generan constancias

1. **Verificar que el evento finalizó:**
   ```sql
   SELECT nombre_evento, 
          TO_CHAR(hora_fin, 'YYYY-MM-DD HH24:MI') as hora_fin,
          CASE WHEN hora_fin < SYSDATE - INTERVAL '30' MINUTE 
               THEN 'Puede generar' 
               ELSE 'Aún no' 
          END as estado
   FROM eventos 
   WHERE genera_constancia = 1;
   ```

2. **Verificar asistencias:**
   ```sql
   SELECT COUNT(*) as usuarios_elegibles
   FROM asistencias 
   WHERE id_evento = 5  -- Cambiar por ID del evento
     AND hora_entrada IS NOT NULL 
     AND hora_salida IS NOT NULL;
   ```

3. **Revisar logs:**
   ```powershell
   Get-Content "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log" | Select-String "ERROR"
   ```

### PHP no encontrado

Editar ruta en scripts:
```powershell
# En: configurar-tarea-constancias.ps1
# O en: ejecutar-constancias-auto.ps1
$phpPath = "C:\xampp\php\php.exe"  # Ajustar tu ruta
```

### Permisos de escritura

```powershell
icacls "Proyecto_conectado\constancias_pdf" /grant Users:F /T
icacls "Proyecto_conectado\logs" /grant Users:F /T
icacls "Proyecto_conectado\temp_qr" /grant Users:F /T
```

## 📖 Documentación Completa

Ver: [GENERACION_AUTOMATICA_CONSTANCIAS.md](GENERACION_AUTOMATICA_CONSTANCIAS.md)

## 🎯 Flujo del Sistema

```
┌─────────────────────┐
│ Evento Finaliza     │
│ (hora_fin < ahora)  │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Espera 30 minutos   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Tarea Automática    │
│ Se ejecuta          │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Busca usuarios con  │
│ asistencia completa │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Genera PDFs con QR  │
│ en constancias_pdf/ │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Registra en BD      │
│ tabla: constancias  │
└─────────────────────┘
```

## ✅ Checklist de Instalación

- [ ] Ejecutar `.\probar-generacion-automatica.ps1`
- [ ] Verificar que se crean directorios (constancias_pdf, logs, temp_qr)
- [ ] Comprobar conexión a base de datos Oracle
- [ ] Ejecutar `.\configurar-tarea-constancias.ps1`
- [ ] Seleccionar frecuencia (opción 1 recomendada)
- [ ] Ejecutar tarea manualmente para probar
- [ ] Verificar log generado
- [ ] Abrir ejemplo de constancia PDF
- [ ] Confirmar tarea en Task Scheduler

## 📞 Soporte

1. Revisar logs: `Proyecto_conectado/logs/`
2. Ejecutar en modo DEBUG (editar script, `$DEBUG_MODE = true`)
3. Consultar documentación completa

---

**Última actualización:** Noviembre 2025
