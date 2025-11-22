# 🎉 feat: Migración Oracle + Sistema de Backups Automáticos

## 📱 Resumen
Migración completa del sistema a Oracle Database 23ai e integración de Copias de Seguridad Automáticas (Backups) mediante tareas CRON.

## ✨ Cambios Principales
- 🗄️ BD: Migración de MySQL a Oracle (PDO OCI), corrección de credenciales y creación de tabla 'usuarios'.
- ⏰ CRON: Implementación de tarea programada cada 5 mins en Docker para generar respaldos JSON.
- 🐳 Docker: Actualización de imagen base a 'bullseye' en Dockerfile.oracle para compatibilidad con librerías libaio1.
- 🔧 Config: Ajuste de variables de entorno (LD_LIBRARY_PATH) en crontab para ejecución automática.

