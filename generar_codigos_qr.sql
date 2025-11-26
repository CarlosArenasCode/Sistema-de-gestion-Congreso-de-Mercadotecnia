-- Generar códigos QR únicos para usuarios que no los tienen
DECLARE
    v_codigo_qr VARCHAR2(500);
    v_count NUMBER := 0;
BEGIN
    FOR user_rec IN (SELECT id_usuario, matricula FROM usuarios WHERE codigo_qr IS NULL) LOOP
        -- Generar código QR único basado en id_usuario y timestamp
        v_codigo_qr := 'QR-' || user_rec.matricula || '-' || TO_CHAR(SYSTIMESTAMP, 'YYYYMMDDHH24MISSFF6');
        
        UPDATE usuarios 
        SET codigo_qr = v_codigo_qr 
        WHERE id_usuario = user_rec.id_usuario;
        
        v_count := v_count + 1;
    END LOOP;
    
    COMMIT;
    
    DBMS_OUTPUT.PUT_LINE('Códigos QR generados para ' || v_count || ' usuarios');
END;
/

-- Verificar usuarios con código QR
SELECT id_usuario, matricula, SUBSTR(codigo_qr, 1, 50) as codigo_qr_preview
FROM usuarios;
