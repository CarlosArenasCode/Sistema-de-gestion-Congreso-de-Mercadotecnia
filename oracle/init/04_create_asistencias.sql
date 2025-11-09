-- Crear tabla asistencias
CREATE TABLE asistencias (
    id_asistencia NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_usuario NUMBER NOT NULL,
    id_evento NUMBER NOT NULL,
    fecha_asistencia DATE DEFAULT SYSDATE,
    constancia_generada NUMBER(1) DEFAULT 0,
    ruta_constancia VARCHAR2(500),
    CONSTRAINT fk_asist_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    CONSTRAINT fk_asist_evento FOREIGN KEY (id_evento) REFERENCES eventos(id_evento),
    CONSTRAINT uk_asistencia UNIQUE (id_usuario, id_evento)
);

-- Índices para mejor rendimiento
CREATE INDEX idx_asist_usuario ON asistencias(id_usuario);
CREATE INDEX idx_asist_evento ON asistencias(id_evento);
CREATE INDEX idx_asist_fecha ON asistencias(fecha_asistencia);

-- Comentarios
COMMENT ON TABLE asistencias IS 'Registros de asistencia de usuarios a eventos';
COMMENT ON COLUMN asistencias.constancia_generada IS '0=No generada, 1=Generada';

COMMIT;
