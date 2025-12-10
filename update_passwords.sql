UPDATE usuarios 
SET password_hash = '$2y$10$KrWHG6ZNWaYUeIL0E6rykenPui9gvMRmdhKnF/fu.sQh6Uduqdz0y' 
WHERE matricula = 'A12345678';

UPDATE administradores 
SET password_hash = '$2y$10$KrWHG6ZNWaYUeIL0E6rykenPui9gvMRmdhKnF/fu.sQh6Uduqdz0y' 
WHERE email = 'admin@congreso.com';

COMMIT;

SELECT matricula, nombre_completo, SUBSTR(password_hash, 1, 20) as hash FROM usuarios;
SELECT email, SUBSTR(password_hash, 1, 20) as hash FROM administradores;
