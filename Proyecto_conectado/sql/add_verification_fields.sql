-- Script para agregar campos de verificación a la tabla usuarios
-- Ejecutar este script en la base de datos congreso_db

USE congreso_db;

-- Agregar columnas para verificación de cuenta
ALTER TABLE `usuarios` 
ADD COLUMN `telefono` VARCHAR(20) DEFAULT NULL AFTER `semestre`,
ADD COLUMN `codigo_verificacion` VARCHAR(6) DEFAULT NULL AFTER `telefono`,
ADD COLUMN `fecha_codigo` DATETIME DEFAULT NULL AFTER `codigo_verificacion`,
ADD COLUMN `verificado` TINYINT(1) NOT NULL DEFAULT 0 AFTER `fecha_codigo`,
ADD COLUMN `intentos_verificacion` INT(11) DEFAULT 0 AFTER `verificado`;

-- Crear índice para búsqueda rápida por código
CREATE INDEX idx_codigo_verificacion ON `usuarios` (`codigo_verificacion`);

-- Crear índice para búsqueda por teléfono
CREATE INDEX idx_telefono ON `usuarios` (`telefono`);
