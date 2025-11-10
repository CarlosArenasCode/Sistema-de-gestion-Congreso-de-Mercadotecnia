#!/bin/bash
# Script para ejecutar todos los scripts de inicialización de Oracle

echo "Ejecutando scripts de inicialización..."

# Ejecutar script de creación de esquema como congreso_user
echo "Creando esquema..."
sqlplus congreso_user/congreso_pass@//oracle_db:1521/FREEPDB1 @/opt/oracle/scripts/setup/02_create_schema.sql

# Ejecutar script de personalización
echo "Creando tabla de personalización..."
sqlplus congreso_user/congreso_pass@//oracle_db:1521/FREEPDB1 @/opt/oracle/scripts/setup/03_create_personalizacion.sql

# Ejecutar script de asistencias
echo "Creando tabla de asistencias..."
sqlplus congreso_user/congreso_pass@//oracle_db:1521/FREEPDB1 @/opt/oracle/scripts/setup/04_create_asistencias.sql

echo "Scripts ejecutados exitosamente"
