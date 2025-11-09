-- Agregar columnas para verificación de usuarios y WhatsApp
-- Script de actualización de la tabla usuarios

USE congreso_db;

-- Agregar columna telefono
ALTER TABLE usuarios 
ADD COLUMN telefono VARCHAR(20) NULL AFTER semestre;

-- Agregar columna codigo_verificacion
ALTER TABLE usuarios 
ADD COLUMN codigo_verificacion VARCHAR(6) NULL AFTER qr_code_data;

-- Agregar columna fecha_codigo
ALTER TABLE usuarios 
ADD COLUMN fecha_codigo DATETIME NULL AFTER codigo_verificacion;

-- Agregar columna verificado
ALTER TABLE usuarios 
ADD COLUMN verificado TINYINT(1) DEFAULT 0 NOT NULL AFTER fecha_codigo;

-- Agregar columna intentos_verificacion
ALTER TABLE usuarios
ADD COLUMN intentos_verificacion INT DEFAULT 0 AFTER verificado;

-- Mostrar la estructura actualizada
DESCRIBE usuarios;
