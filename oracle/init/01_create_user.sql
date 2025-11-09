-- =====================================================
-- Script: 01_create_user.sql
-- Descripción: Crear usuario y tablespace para el sistema
-- Base de datos: Oracle Database 23ai Free
-- =====================================================

-- Conectar a la PDB (Pluggable Database)
ALTER SESSION SET CONTAINER = FREEPDB1;

-- Mostrar información de la sesión
SELECT 'Conectado a: ' || SYS_CONTEXT('USERENV', 'CON_NAME') AS info FROM DUAL;

-- =====================================================
-- CREAR TABLESPACE PARA DATOS DEL CONGRESO
-- =====================================================

-- Crear tablespace para almacenar los datos de la aplicación
CREATE TABLESPACE congreso_data
  DATAFILE 'congreso_data01.dbf' 
  SIZE 100M 
  AUTOEXTEND ON 
  NEXT 10M 
  MAXSIZE UNLIMITED
  EXTENT MANAGEMENT LOCAL 
  SEGMENT SPACE MANAGEMENT AUTO;

SELECT 'Tablespace congreso_data creado exitosamente' AS info FROM DUAL;

-- =====================================================
-- CREAR USUARIO DE LA APLICACIÓN
-- =====================================================

-- Crear usuario para la aplicación
CREATE USER congreso_user IDENTIFIED BY congreso_pass
  DEFAULT TABLESPACE congreso_data
  TEMPORARY TABLESPACE TEMP
  QUOTA UNLIMITED ON congreso_data;

SELECT 'Usuario congreso_user creado exitosamente' AS info FROM DUAL;

-- =====================================================
-- OTORGAR PRIVILEGIOS
-- =====================================================

-- Privilegios básicos de conexión y recursos
GRANT CONNECT TO congreso_user;
GRANT RESOURCE TO congreso_user;
GRANT CREATE SESSION TO congreso_user;

-- Privilegios para crear objetos
GRANT CREATE TABLE TO congreso_user;
GRANT CREATE VIEW TO congreso_user;
GRANT CREATE SEQUENCE TO congreso_user;
GRANT CREATE TRIGGER TO congreso_user;
GRANT CREATE PROCEDURE TO congreso_user;
GRANT CREATE SYNONYM TO congreso_user;

-- Privilegio para usar tablespace sin límite
GRANT UNLIMITED TABLESPACE TO congreso_user;

SELECT 'Privilegios otorgados exitosamente' AS info FROM DUAL;

-- =====================================================
-- VERIFICAR CREACIÓN
-- =====================================================

-- Mostrar información del usuario creado
SELECT 
    username AS "Usuario",
    default_tablespace AS "Tablespace por Defecto",
    temporary_tablespace AS "Tablespace Temporal",
    created AS "Fecha de Creación"
FROM dba_users 
WHERE username = 'CONGRESO_USER';

-- Mostrar privilegios del sistema otorgados
SELECT 
    privilege AS "Privilegio"
FROM dba_sys_privs
WHERE grantee = 'CONGRESO_USER'
ORDER BY privilege;

-- Confirmar cambios
COMMIT;

SELECT 'Configuración inicial completada exitosamente' AS info FROM DUAL;

EXIT;
