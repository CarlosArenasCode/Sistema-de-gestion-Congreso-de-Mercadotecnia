-- oracle/init/01_create_user.sql

-- 1. CRUCIAL: Moverse al contenedor de base de datos correcto
ALTER SESSION SET CONTAINER = FREEPDB1;

-- 2. Crear el usuario de forma segura (Idempotente)
DECLARE
    v_count NUMBER;
BEGIN
    SELECT count(*) INTO v_count FROM dba_users WHERE username = 'CONGRESO_USER';
    IF v_count = 0 THEN
        EXECUTE IMMEDIATE 'CREATE USER congreso_user IDENTIFIED BY congreso_pass';
    END IF;
END;
/

-- 3. Dar permisos necesarios
GRANT CONNECT, RESOURCE, DBA TO congreso_user;
GRANT UNLIMITED TABLESPACE TO congreso_user;
GRANT CREATE SESSION TO congreso_user;