# 🧪 Guía de Pruebas - Sistema con Oracle Database

## 📋 Pasos para Probar el Sistema

### 1️⃣ Verificar que los Servicios Están Corriendo

```powershell
# Ver estado de todos los contenedores
docker ps

# Deberías ver 3 contenedores:
# - congreso_oracle_db (Oracle Database)
# - congreso_web_oracle (PHP + Apache)
# - adminer_oracle (Herramienta de administración)
```

**✅ Verificación esperada:**
- Los 3 contenedores deben estar en estado "Up" (healthy)
- Puertos: 1521 (Oracle), 8080 (Web), 8081 (Adminer)

---

### 2️⃣ Verificar la Conexión a Oracle

```powershell
# Probar conexión desde el contenedor web
docker exec congreso_web_oracle php /var/www/html/php/test_oracle_connection.php
```

**✅ Verificación esperada:**
- ✅ Conexión exitosa a Oracle Database
- ✅ Usuario conectado: CONGRESO_USER
- ✅ Todas las 8 tablas listadas
- ✅ Operaciones CRUD funcionando

---

### 3️⃣ Modificar los Front-ends para Usar Oracle

Los archivos HTML necesitan apuntar a los controladores Oracle. Tienes 2 opciones:

#### Opción A: Renombrar archivos Oracle (Recomendado)
```powershell
# Navegar a la carpeta
cd "Proyecto_conectado\php_admin"

# Renombrar los archivos Oracle para que sean los predeterminados
Move-Item usuarios_controller.php usuarios_controller.mysql.php -Force
Move-Item usuarios_controller.oracle.php usuarios_controller.php -Force

Move-Item eventos_controller.php eventos_controller.mysql.php -Force
Move-Item eventos_controller.oracle.php eventos_controller.php -Force

Move-Item dashboard_controller.php dashboard_controller.mysql.php -Force
Move-Item dashboard_controller.oracle.php dashboard_controller.php -Force

Move-Item asistencia_controller.php asistencia_controller.mysql.php -Force
Move-Item asistencia_controller.oracle.php asistencia_controller.php -Force

Move-Item justificaciones_controller.php justificaciones_controller.mysql.php -Force
Move-Item justificaciones_controller.oracle.php justificaciones_controller.php -Force

Move-Item constancias_controller.php constancias_controller.mysql.php -Force
Move-Item constancias_controller.oracle.php constancias_controller.php -Force

Move-Item reporte_asistencia_controller.php reporte_asistencia_controller.mysql.php -Force
Move-Item reporte_asistencia_controller.oracle.php reporte_asistencia_controller.php -Force

# También en la carpeta php/
cd "..\php"
Move-Item conexion.php conexion.mysql.php -Force
Move-Item conexion.oracle.php conexion.php -Force

Move-Item generar_constancia.php generar_constancia.mysql.php -Force
Move-Item generar_constancia.oracle.php generar_constancia.php -Force
```

#### Opción B: Modificar los archivos HTML (Más trabajo)
Editar cada archivo HTML para cambiar las rutas, ejemplo:
```javascript
// Cambiar en cada archivo .html en Front-end/
// De:
fetch('../php_admin/usuarios_controller.php?action=get_list')
// A:
fetch('../php_admin/usuarios_controller.oracle.php?action=get_list')
```

---

### 4️⃣ Acceder al Sistema

#### 🌐 Aplicación Web Principal
```
URL: http://localhost:8080
```

Archivos principales:
- `http://localhost:8080/Front-end/login_admin.html` - Login administrador
- `http://localhost:8080/Front-end/login.html` - Login usuario
- `http://localhost:8080/Front-end/admin_dashboard.html` - Dashboard admin

#### 🗄️ Adminer (Administrador de BD)
```
URL: http://localhost:8081
```

Credenciales de acceso:
- **Sistema**: Oracle
- **Servidor**: congreso_oracle_db:1521/FREEPDB1
- **Usuario**: congreso_user
- **Contraseña**: congreso_pass

---

### 5️⃣ Probar Funcionalidades Principales

#### ✅ Módulo de Usuarios
1. Ir a `http://localhost:8080/Front-end/admin_usuarios.html`
2. **Crear usuario**:
   - Nombre completo: "Juan Pérez Test"
   - Email: "juan.test@example.com"
   - Matrícula: "123456"
   - Contraseña: "Test123"
3. **Buscar usuario**: Escribir "Juan" en el buscador
4. **Editar usuario**: Cambiar algún dato
5. **Verificar**: Los datos deben guardarse correctamente

**🔍 Verificar en Adminer**:
```sql
SELECT * FROM usuarios WHERE email = 'juan.test@example.com';
```

---

#### ✅ Módulo de Eventos
1. Ir a `http://localhost:8080/Front-end/admin_eventos.html`
2. **Crear evento**:
   - Nombre: "Conferencia de Prueba Oracle"
   - Tipo: Conferencia
   - Fecha inicio: (hoy)
   - Fecha fin: (mañana)
   - Hora inicio: 10:00
   - Hora fin: 12:00
   - Cupo máximo: 50
   - Ponente: "Dr. Test Oracle"
3. **Verificar**: El evento debe aparecer en la lista

**🔍 Verificar en Adminer**:
```sql
SELECT id_evento, nombre_evento, 
       TO_CHAR(hora_inicio, 'HH24:MI') as hora_inicio,
       TO_CHAR(hora_fin, 'HH24:MI') as hora_fin
FROM eventos
WHERE nombre_evento LIKE '%Oracle%';
```

---

#### ✅ Módulo de Dashboard
1. Ir a `http://localhost:8080/Front-end/admin_dashboard.html`
2. **Verificar estadísticas**:
   - Total de usuarios registrados
   - Total de eventos programados
   - Justificaciones pendientes

**🔍 Verificar en Adminer**:
```sql
SELECT COUNT(*) as total_usuarios FROM usuarios;
SELECT COUNT(*) as total_eventos FROM eventos;
SELECT COUNT(*) as justificaciones_pendientes FROM justificaciones WHERE estado = 'PENDIENTE';
```

---

#### ✅ Módulo de Asistencia (Más Complejo)
1. Ir a `http://localhost:8080/Front-end/admin_scan_qr.html`
2. **Registrar entrada**:
   - Seleccionar evento activo
   - Escanear QR del usuario (o ingresar manualmente el qr_code_data)
   - Hacer clic en "Registrar Entrada"
3. **Registrar salida**:
   - Mismo usuario y evento
   - Hacer clic en "Registrar Salida"

**🔍 Verificar duración en Adminer**:
```sql
SELECT 
    u.nombre_completo,
    e.nombre_evento,
    TO_CHAR(a.fecha, 'DD/MM/YYYY') as fecha,
    TO_CHAR(a.hora_entrada, 'HH24:MI:SS') as entrada,
    TO_CHAR(a.hora_salida, 'HH24:MI:SS') as salida,
    EXTRACT(HOUR FROM a.duracion) || 'h ' || 
    EXTRACT(MINUTE FROM a.duracion) || 'm' as duracion
FROM asistencia a
JOIN usuarios u ON a.id_usuario = u.id_usuario
JOIN eventos e ON a.id_evento = e.id_evento
ORDER BY a.fecha DESC;
```

---

#### ✅ Módulo de Justificaciones
1. Ir a `http://localhost:8080/Front-end/admin_justificacion.html`
2. **Ver justificaciones pendientes**
3. **Aprobar/Rechazar una justificación**

**🔍 Verificar en Adminer**:
```sql
SELECT 
    j.id_justificacion,
    u.nombre_completo,
    e.nombre_evento,
    j.estado,
    TO_CHAR(j.fecha_solicitud, 'DD/MM/YYYY HH24:MI:SS') as fecha_solicitud,
    TO_CHAR(j.fecha_revision, 'DD/MM/YYYY HH24:MI:SS') as fecha_revision
FROM justificaciones j
JOIN usuarios u ON j.id_usuario = u.id_usuario
JOIN eventos e ON j.id_evento = e.id_evento
ORDER BY j.fecha_solicitud DESC;
```

---

#### ✅ Módulo de Constancias (Genera PDFs)
1. Ir a `http://localhost:8080/Front-end/admin_constancias.html`
2. **Seleccionar un evento**
3. **Ver usuarios elegibles**
4. **Generar constancia** para un usuario

**🔍 Verificar en Adminer**:
```sql
SELECT 
    c.numero_serie,
    u.nombre_completo,
    e.nombre_evento,
    c.ruta_archivo_pdf,
    TO_CHAR(c.fecha_emision, 'DD/MM/YYYY HH24:MI:SS') as fecha_emision
FROM constancias c
JOIN usuarios u ON c.id_usuario = u.id_usuario
JOIN eventos e ON c.id_evento = e.id_evento
ORDER BY c.fecha_emision DESC;
```

**📄 Verificar PDF generado**:
El PDF debe estar en: `Proyecto_conectado/constancias_generadas/`

---

#### ✅ Reporte de Asistencia
1. Ir a `http://localhost:8080/Front-end/admin_asistencia.html`
2. **Ver lista de asistencias**
3. **Buscar por nombre, evento, fecha**
4. **Exportar a CSV**

---

### 6️⃣ Verificar Logs de Errores

```powershell
# Ver logs del contenedor web (PHP)
docker logs congreso_web_oracle --tail 50

# Ver logs del contenedor Oracle
docker logs congreso_oracle_db --tail 50

# Ver logs en tiempo real
docker logs -f congreso_web_oracle
```

---

### 7️⃣ Consultas SQL Útiles para Verificación

#### Ver todas las tablas y su contenido
```sql
-- En Adminer o sqlplus
SELECT table_name FROM user_tables ORDER BY table_name;

-- Contar registros en cada tabla
SELECT 'usuarios' as tabla, COUNT(*) as registros FROM usuarios
UNION ALL
SELECT 'eventos', COUNT(*) FROM eventos
UNION ALL
SELECT 'inscripciones', COUNT(*) FROM inscripciones
UNION ALL
SELECT 'asistencia', COUNT(*) FROM asistencia
UNION ALL
SELECT 'justificaciones', COUNT(*) FROM justificaciones
UNION ALL
SELECT 'constancias', COUNT(*) FROM constancias
UNION ALL
SELECT 'administradores', COUNT(*) FROM administradores
UNION ALL
SELECT 'password_reset_tokens', COUNT(*) FROM password_reset_tokens;
```

#### Verificar formato de fechas/horas Oracle
```sql
-- Ver configuración de formato de fecha
SELECT * FROM nls_session_parameters 
WHERE parameter IN ('NLS_DATE_FORMAT', 'NLS_TIMESTAMP_FORMAT');

-- Ejemplos de conversión
SELECT 
    SYSDATE as fecha_actual,
    TO_CHAR(SYSDATE, 'DD/MM/YYYY HH24:MI:SS') as formato_texto,
    TRUNC(SYSDATE) as solo_fecha
FROM dual;
```

---

### 8️⃣ Troubleshooting (Solución de Problemas)

#### ❌ Error: "No se puede conectar a Oracle"
```powershell
# Verificar que Oracle esté corriendo
docker exec congreso_oracle_db sqlplus congreso_user/congreso_pass@//localhost:1521/FREEPDB1

# Si no funciona, reiniciar contenedor
docker restart congreso_oracle_db
docker restart congreso_web_oracle
```

#### ❌ Error: "Call to undefined function oci_connect()"
```powershell
# Verificar extensiones PHP
docker exec congreso_web_oracle php -m | findstr /i "oci pdo"

# Debe mostrar:
# oci8
# PDO
# pdo_oci
```

#### ❌ Error: "ORA-12154: TNS:could not resolve the connect identifier"
- Verificar que la conexión use el formato correcto
- En `conexion.oracle.php` debe ser: `host:1521/FREEPDB1`

#### ❌ Error en generación de PDF
```powershell
# Verificar permisos de carpeta
docker exec congreso_web_oracle ls -la /var/www/html/constancias_generadas/

# Crear carpeta si no existe
docker exec congreso_web_oracle mkdir -p /var/www/html/constancias_generadas
docker exec congreso_web_oracle chown -R www-data:www-data /var/www/html/constancias_generadas
docker exec congreso_web_oracle chmod -R 775 /var/www/html/constancias_generadas
```

---

### 9️⃣ Pruebas de Rendimiento Oracle

#### Comparar velocidad de consultas
```sql
-- Habilitar timing en sqlplus
SET TIMING ON

-- Consulta compleja
SELECT 
    u.nombre_completo,
    COUNT(DISTINCT e.id_evento) as eventos_asistidos,
    COUNT(a.id_asistencia) as total_asistencias,
    SUM(
        EXTRACT(DAY FROM a.duracion) * 86400 +
        EXTRACT(HOUR FROM a.duracion) * 3600 +
        EXTRACT(MINUTE FROM a.duracion) * 60
    ) / 3600 as horas_totales
FROM usuarios u
LEFT JOIN asistencia a ON u.id_usuario = a.id_usuario
LEFT JOIN eventos e ON a.id_evento = e.id_evento
GROUP BY u.nombre_completo
ORDER BY horas_totales DESC NULLS LAST;
```

---

### 🔟 Comandos Útiles de Docker

```powershell
# Detener todo
docker-compose -f docker-compose.oracle.yml down

# Iniciar todo
docker-compose -f docker-compose.oracle.yml up -d

# Ver uso de recursos
docker stats

# Limpiar volúmenes (⚠️ BORRA TODOS LOS DATOS)
docker-compose -f docker-compose.oracle.yml down -v

# Reconstruir imagen si cambias el Dockerfile
docker-compose -f docker-compose.oracle.yml build --no-cache
docker-compose -f docker-compose.oracle.yml up -d
```

---

## ✅ Checklist de Pruebas Completas

- [ ] Servicios corriendo (Docker ps)
- [ ] Conexión Oracle exitosa (test_oracle_connection.php)
- [ ] Controladores renombrados o HTML modificados
- [ ] Login de administrador funciona
- [ ] Crear usuario en Oracle
- [ ] Buscar usuario (case-insensitive)
- [ ] Editar y eliminar usuario
- [ ] Crear evento con fechas y horas
- [ ] Dashboard muestra estadísticas
- [ ] Registrar entrada de asistencia
- [ ] Registrar salida de asistencia (calcula duración)
- [ ] Ver justificaciones
- [ ] Aprobar/rechazar justificación
- [ ] Generar constancia PDF
- [ ] Descargar constancia generada
- [ ] Exportar reporte de asistencia a CSV
- [ ] Verificar datos en Adminer
- [ ] Sin errores en logs de Docker

---

## 🎯 Próximos Pasos Después de las Pruebas

1. **Si todo funciona**: Documentar cualquier configuración adicional necesaria
2. **Si hay errores**: Revisar logs y ajustar código según sea necesario
3. **Optimización**: Agregar índices en Oracle si las consultas son lentas
4. **Seguridad**: Cambiar contraseñas predeterminadas en producción
5. **Backup**: Configurar respaldo automático de la base de datos Oracle

---

**Fecha de creación**: 8 de Noviembre, 2025  
**Versión del sistema**: Oracle Database 23ai Free  
**Autor**: Sistema de Gestión de Congresos - Migración Oracle
