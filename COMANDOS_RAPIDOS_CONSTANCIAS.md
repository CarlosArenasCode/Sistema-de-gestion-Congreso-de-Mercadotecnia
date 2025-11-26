# ⚡ Comandos Rápidos - Constancias Automáticas

## 🚀 Ejecución

### Probar Todo el Sistema
```powershell
.\probar-generacion-automatica.ps1
```

### Ejecutar Generación Manualmente
```powershell
.\ejecutar-constancias-auto.ps1
```

### Configurar Tarea Automática
```powershell
.\configurar-tarea-constancias.ps1
```

### Desde Navegador
```
http://localhost/Proyecto_conectado/php/ejecutar_generacion_constancias.php
```

---

## 📊 Consultas SQL Útiles

### Eventos Finalizados Pendientes
```sql
SELECT 
    id_evento,
    nombre_evento,
    TO_CHAR(hora_fin, 'YYYY-MM-DD HH24:MI') as finalizo,
    ROUND((SYSDATE - hora_fin) * 24 * 60) as minutos_desde_fin
FROM eventos
WHERE genera_constancia = 1
  AND hora_fin < SYSDATE - INTERVAL '30' MINUTE
  AND hora_fin > SYSDATE - INTERVAL '7' DAY
ORDER BY hora_fin DESC;
```

### Constancias Generadas Hoy
```sql
SELECT 
    COUNT(*) as total_hoy,
    COUNT(DISTINCT id_evento) as eventos_procesados
FROM constancias
WHERE TRUNC(fecha_emision) = TRUNC(SYSDATE);
```

### Usuarios Elegibles Sin Constancia
```sql
SELECT 
    u.nombre_completo,
    u.matricula,
    e.nombre_evento,
    TO_CHAR(e.hora_fin, 'HH24:MI') as finalizo
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

---

## 🔧 Gestión de Tarea Programada

### Ver Estado
```powershell
Get-ScheduledTask -TaskName "Generar_Constancias_Automaticas"
```

### Ejecutar Ahora
```powershell
Start-ScheduledTask -TaskName "Generar_Constancias_Automaticas"
```

### Deshabilitar
```powershell
Disable-ScheduledTask -TaskName "Generar_Constancias_Automaticas"
```

### Habilitar
```powershell
Enable-ScheduledTask -TaskName "Generar_Constancias_Automaticas"
```

### Ver Historial
```powershell
Get-ScheduledTask -TaskName "Generar_Constancias_Automaticas" | Get-ScheduledTaskInfo
```

### Eliminar
```powershell
Unregister-ScheduledTask -TaskName "Generar_Constancias_Automaticas" -Confirm:$false
```

---

## 📝 Logs

### Ver Log del Día
```powershell
Get-Content "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log"
```

### Últimas 30 Líneas
```powershell
Get-Content "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log" -Tail 30
```

### Monitorear en Tiempo Real
```powershell
Get-Content "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log" -Wait
```

### Buscar Errores
```powershell
Get-Content "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log" | Select-String "ERROR"
```

---

## 📁 Archivos

### Ver PDFs Generados Hoy
```powershell
Get-ChildItem "Proyecto_conectado\constancias_pdf" | Where-Object { $_.LastWriteTime -gt (Get-Date).Date }
```

### Contar PDFs
```powershell
(Get-ChildItem "Proyecto_conectado\constancias_pdf" -Filter "*.pdf").Count
```

### Abrir Carpeta de Constancias
```powershell
explorer.exe "Proyecto_conectado\constancias_pdf"
```

### Espacio en Disco
```powershell
$size = (Get-ChildItem "Proyecto_conectado\constancias_pdf" -Recurse | Measure-Object -Property Length -Sum).Sum
[math]::Round($size / 1MB, 2)
```

---

## 🐛 Diagnóstico

### Verificar PHP
```powershell
& "C:\xampp\php\php.exe" -v
```

### Verificar Oracle Container
```powershell
docker ps | Select-String "congreso_oracle_db"
```

### Test de Conexión Oracle
```powershell
docker exec congreso_oracle_db sqlplus -s congreso_user/congreso_pass@FREEPDB1 <<EOF
SELECT 'OK' as status FROM dual;
EOF
```

### Ver Directorios del Sistema
```powershell
Get-ChildItem "Proyecto_conectado" -Directory | Select Name
```

---

## ⚙️ Configuración

### Editar Parámetros del Script
```powershell
notepad "Proyecto_conectado\php\generar_constancias_automaticas.php"
```

Parámetros clave:
- `$DEBUG_MODE = true;` (línea ~18)
- `$LIMITE_EVENTOS = 50;` (línea ~19)
- `$TIEMPO_ESPERA = 30;` (línea ~20)

### Cambiar Frecuencia de Tarea
```powershell
# Eliminar tarea existente
Unregister-ScheduledTask -TaskName "Generar_Constancias_Automaticas" -Confirm:$false

# Re-configurar con nueva frecuencia
.\configurar-tarea-constancias.ps1
```

---

## 📊 Estadísticas Rápidas

### Resumen General
```sql
SELECT 
    (SELECT COUNT(*) FROM constancias WHERE TRUNC(fecha_emision) = TRUNC(SYSDATE)) as hoy,
    (SELECT COUNT(*) FROM constancias WHERE fecha_emision > SYSDATE - INTERVAL '7' DAY) as semana,
    (SELECT COUNT(*) FROM constancias) as total
FROM dual;
```

### Por Evento
```sql
SELECT 
    e.nombre_evento,
    COUNT(c.id_constancia) as constancias,
    e.cupo_actual as inscritos,
    ROUND(COUNT(c.id_constancia) * 100.0 / NULLIF(e.cupo_actual, 0), 2) as porcentaje
FROM eventos e
LEFT JOIN constancias c ON e.id_evento = c.id_evento
WHERE e.genera_constancia = 1
GROUP BY e.id_evento, e.nombre_evento, e.cupo_actual
ORDER BY constancias DESC;
```

---

## 🔄 Operaciones Comunes

### Re-generar Constancias de un Evento
```sql
-- 1. Eliminar constancias existentes
DELETE FROM constancias WHERE id_evento = 5;

-- 2. Ejecutar generación
Start-ScheduledTask -TaskName "Generar_Constancias_Automaticas"
```

### Limpiar Constancias con Errores
```sql
DELETE FROM constancias 
WHERE ruta_archivo_pdf IS NULL 
   OR LENGTH(ruta_archivo_pdf) < 10;
```

### Backup de Constancias
```powershell
$fecha = Get-Date -Format "yyyy-MM-dd"
Compress-Archive -Path "Proyecto_conectado\constancias_pdf\*" -DestinationPath "backup_constancias_$fecha.zip"
```

---

## 📖 Documentación

- **GENERACION_AUTOMATICA_CONSTANCIAS.md** → Guía completa técnica
- **INICIO_RAPIDO_CONSTANCIAS_AUTO.md** → Inicio rápido
- **RESUMEN_CONSTANCIAS_AUTOMATICAS.md** → Resumen ejecutivo
- **vista_sistema_constancias_auto.html** → Vista general visual

---

## 🆘 Ayuda Rápida

### Sistema no genera constancias
```powershell
# 1. Verificar tarea
Get-ScheduledTask -TaskName "Generar_Constancias_Automaticas"

# 2. Ejecutar manualmente
.\ejecutar-constancias-auto.ps1

# 3. Revisar log
Get-Content "Proyecto_conectado\logs\constancias_auto_$(Get-Date -Format 'yyyy-MM-dd').log" -Tail 50
```

### Permisos de archivos
```powershell
icacls "Proyecto_conectado\constancias_pdf" /grant Users:F /T
icacls "Proyecto_conectado\logs" /grant Users:F /T
```

### Re-instalar tarea
```powershell
Unregister-ScheduledTask -TaskName "Generar_Constancias_Automaticas" -Confirm:$false
.\configurar-tarea-constancias.ps1
```

---

**Versión:** 1.0  
**Última actualización:** Noviembre 2025
