-- Tabla para guardar la configuración de personalización del sitio
CREATE TABLE IF NOT EXISTS personalizacion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT NOT NULL,
    tipo ENUM('color', 'imagen', 'texto') DEFAULT 'texto',
    descripcion VARCHAR(255),
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    modificado_por INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para las imágenes del carrusel
CREATE TABLE IF NOT EXISTS carrusel_imagenes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    url_imagen TEXT NOT NULL,
    alt_texto VARCHAR(255),
    orden INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    tipo_fuente ENUM('url', 'archivo') DEFAULT 'url',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    creado_por INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar valores por defecto para los colores principales
INSERT INTO personalizacion (clave, valor, tipo, descripcion) VALUES
('color_primario', '#0056b3', 'color', 'Color principal del sitio'),
('color_secundario', '#28a745', 'color', 'Color secundario (botones de acción)'),
('color_header', '#4A4A4A', 'color', 'Color de fondo del header'),
('color_nav', '#333', 'color', 'Color de fondo del menú de navegación'),
('color_nav_hover', '#0056b3', 'color', 'Color de hover en navegación'),
('color_footer', '#333', 'color', 'Color de fondo del footer'),
('color_carrusel_fondo', '#6c757d', 'color', 'Color de fondo del carrusel')
ON DUPLICATE KEY UPDATE valor=VALUES(valor);

-- Insertar imágenes por defecto del carrusel (las 3 logos locales)
INSERT INTO carrusel_imagenes (url_imagen, alt_texto, orden, tipo_fuente) VALUES
('../Logos/UAA_LOGO.png', 'Logo Universidad Autónoma de Aguascalientes', 1, 'archivo'),
('../Logos/logo-ccea.png', 'Logo CCEA', 2, 'archivo'),
('../Logos/MKT_LOGO.png', 'Logo MKT', 3, 'archivo')
ON DUPLICATE KEY UPDATE url_imagen=VALUES(url_imagen);
