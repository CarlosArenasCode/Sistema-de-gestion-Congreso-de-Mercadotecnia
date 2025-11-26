# 🚀 feat: Migración Oracle, Backups Automáticos y Fixes QR/JS

## 📱 Resumen
Este PR finaliza la migración del backend a Oracle Database 23ai, implementa un sistema robusto de backups automáticos con CRON, y corrige la lógica de generación de QRs y validaciones en el Frontend.

## ✨ Cambios Principales

### 🗄️ Backend & Base de Datos (Oracle)
- **Migración PDO OCI:** Cambio total de drivers MySQL a Oracle en `php/conexion.php`.
- **Init Scripts:** Actualización de scripts de inicialización en `oracle/init/` para estructura de tablas y usuarios.
- **Backups:** Implementación de `php/cron_backup.php` para exportar datos JSON periódicamente.

### ⚙️ DevOps & Infraestructura
- **Docker:** Actualización de `Dockerfile.oracle` instalando librerías `libaio1` y `cron`.
- **CRON:** Configuración de `crontab` para ejecutar respaldos cada 5 minutos.

### 🐛 Frontend & Fixes
- **QR System:** Corrección en `js/qr.js` y `php/qr_usuario.php` para lectura correcta de JSON (`qr_code_data`).
- **Constancias:** Manejo de errores mejorado en `js/certificates.js`.

