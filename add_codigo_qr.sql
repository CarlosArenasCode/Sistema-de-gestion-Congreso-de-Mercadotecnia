-- Agregar columna codigo_qr a usuarios si no existe
DECLARE
    v_count NUMBER;
BEGIN
    SELECT COUNT(*) INTO v_count
    FROM user_tab_columns
    WHERE table_name = 'USUARIOS' AND column_name = 'CODIGO_QR';
    
    IF v_count = 0 THEN
        EXECUTE IMMEDIATE 'ALTER TABLE usuarios ADD (codigo_qr VARCHAR2(500))';
        DBMS_OUTPUT.PUT_LINE('Columna codigo_qr agregada exitosamente');
    ELSE
        DBMS_OUTPUT.PUT_LINE('La columna codigo_qr ya existe');
    END IF;
END;
/

COMMIT;

-- Verificar
SELECT column_name, data_type, data_length 
FROM user_tab_columns 
WHERE table_name = 'USUARIOS' AND column_name = 'CODIGO_QR';
