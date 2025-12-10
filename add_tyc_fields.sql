-- Agregar campos faltantes a la tabla usuarios
ALTER TABLE usuarios ADD (
    acepta_tyc NUMBER(1) DEFAULT 0 NOT NULL,
    fecha_aceptacion TIMESTAMP
);

-- Agregar constraint
ALTER TABLE usuarios ADD CONSTRAINT ck_usuarios_tyc CHECK (acepta_tyc IN (0, 1));

-- Agregar comentarios
COMMENT ON COLUMN usuarios.acepta_tyc IS '0 = No aceptado, 1 = Aceptado';
COMMENT ON COLUMN usuarios.fecha_aceptacion IS 'Fecha y hora exacta de aceptación de términos';

-- Verificar que se agregaron correctamente
SELECT column_name, data_type, nullable 
FROM user_tab_columns 
WHERE table_name = 'USUARIOS' 
ORDER BY column_id;

EXIT;
