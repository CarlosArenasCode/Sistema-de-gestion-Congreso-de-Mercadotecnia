-- Script para verificar y recrear el usuario congreso_user
ALTER SESSION SET CONTAINER = FREEPDB1;

-- Verificar si el usuario existe
SELECT 'Verificando usuario...' FROM DUAL;

-- Intentar eliminar el usuario si existe (ignorar errores)
BEGIN
   EXECUTE IMMEDIATE 'DROP USER congreso_user CASCADE';
   DBMS_OUTPUT.PUT_LINE('Usuario anterior eliminado');
EXCEPTION
   WHEN OTHERS THEN
      DBMS_OUTPUT.PUT_LINE('Usuario no existía');
END;
/

-- Crear el usuario nuevamente
CREATE USER congreso_user IDENTIFIED BY congreso_pass
  DEFAULT TABLESPACE USERS
  TEMPORARY TABLESPACE TEMP
  QUOTA UNLIMITED ON USERS;

-- Otorgar privilegios
GRANT CONNECT TO congreso_user;
GRANT RESOURCE TO congreso_user;
GRANT CREATE SESSION TO congreso_user;
GRANT CREATE TABLE TO congreso_user;
GRANT CREATE VIEW TO congreso_user;
GRANT CREATE SEQUENCE TO congreso_user;
GRANT CREATE TRIGGER TO congreso_user;
GRANT CREATE PROCEDURE TO congreso_user;
GRANT UNLIMITED TABLESPACE TO congreso_user;

-- Verificar que el usuario fue creado
SELECT 'Usuario: ' || username || ' - Estado: ' || account_status 
FROM dba_users 
WHERE username = 'CONGRESO_USER';

-- Mostrar privilegios
SELECT 'Privilegios otorgados' FROM DUAL;

EXIT;
