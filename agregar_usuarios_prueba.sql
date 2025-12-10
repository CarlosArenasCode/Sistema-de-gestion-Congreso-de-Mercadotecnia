-- Script para agregar usuarios de prueba
-- Estos usuarios tendrán diferentes estados de verificación para probar el sistema

-- Usuario verificado (puede inscribirse)
INSERT INTO usuarios (nombre_completo, email, password_hash, matricula, semestre, telefono, rol, codigo_qr, verificado) 
VALUES ('María López García', 'maria.lopez@universidad.edu.mx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024001', 3, '+525512345678', 'alumno', 'QR_2024001', 1);

-- Usuario no verificado (NO puede inscribirse)
INSERT INTO usuarios (nombre_completo, email, password_hash, matricula, semestre, telefono, rol, codigo_qr, codigo_verificacion, fecha_codigo, verificado) 
VALUES ('Carlos Ramírez Torres', 'carlos.ramirez@universidad.edu.mx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024002', 5, '+525587654321', 'alumno', 'QR_2024002', '123456', SYSTIMESTAMP, 0);

-- Usuario verificado (puede inscribirse)
INSERT INTO usuarios (nombre_completo, email, password_hash, matricula, semestre, telefono, rol, codigo_qr, verificado) 
VALUES ('Ana Martínez Ruiz', 'ana.martinez@universidad.edu.mx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024003', 4, '+525598765432', 'alumno', 'QR_2024003', 1);

-- Usuario profesor verificado
INSERT INTO usuarios (nombre_completo, email, password_hash, matricula, telefono, rol, codigo_qr, verificado) 
VALUES ('Dr. Luis González Pérez', 'luis.gonzalez@universidad.edu.mx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'PROF001', '+525511112222', 'profesor', 'QR_PROF001', 1);

-- Usuario verificado reciente
INSERT INTO usuarios (nombre_completo, email, password_hash, matricula, semestre, telefono, rol, codigo_qr, verificado) 
VALUES ('Laura Sánchez Flores', 'laura.sanchez@universidad.edu.mx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024004', 2, '+525533334444', 'alumno', 'QR_2024004', 1);

COMMIT;

-- Verificar
SELECT matricula, nombre_completo, rol, verificado 
FROM usuarios 
ORDER BY id_usuario;

EXIT;
