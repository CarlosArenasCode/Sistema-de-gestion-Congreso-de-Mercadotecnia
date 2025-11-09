-- Base de datos: `congreso_db`
CREATE DATABASE IF NOT EXISTS `congreso_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `congreso_db`;

-- --------------------------------------------------------

--
-- Estructura de tabla para `administradores`
--
CREATE TABLE `administradores` (
  `id_admin` int(11) NOT NULL,
  `nombre_completo` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('superadmin','staff') NOT NULL DEFAULT 'staff',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para `eventos`
--
CREATE TABLE `eventos` (
  `id_evento` int(11) NOT NULL,
  `nombre_evento` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `fecha_fin` date NOT NULL,
  `hora_fin` time NOT NULL,
  `lugar` varchar(255) DEFAULT NULL,
  `ponente` varchar(255) DEFAULT NULL,
  `cupo_maximo` int(11) DEFAULT NULL,
  `cupo_actual` int(11) DEFAULT 0,
  `genera_constancia` tinyint(1) NOT NULL DEFAULT 0,
  `tipo_evento` enum('conferencia','taller') NOT NULL DEFAULT 'conferencia',
  `horas_para_constancia` decimal(4,2) NOT NULL DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para `usuarios`
--
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre_completo` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `matricula` varchar(50) DEFAULT NULL,
  `semestre` int(2) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol` enum('alumno','profesor') NOT NULL DEFAULT 'alumno',
  `codigo_qr` varchar(255) DEFAULT NULL,
  `codigo_verificacion` varchar(6) DEFAULT NULL,
  `fecha_codigo` datetime DEFAULT NULL,
  `verificado` tinyint(1) NOT NULL DEFAULT 0,
  `intentos_verificacion` int(11) DEFAULT 0,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para `asistencia`
--
CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_evento` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `duracion` time DEFAULT NULL,
  `estado_asistencia` enum('Completa','Incompleta','Ausente') DEFAULT NULL,
  `metodo_registro` enum('QR_SCAN','MANUAL_ADMIN','OTRO') DEFAULT 'QR_SCAN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para `constancias`
--
CREATE TABLE `constancias` (
  `id_constancia` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_evento` int(11) NOT NULL,
  `numero_serie` varchar(100) NOT NULL,
  `fecha_emision` timestamp NOT NULL DEFAULT current_timestamp(),
  `ruta_archivo_pdf` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para `justificaciones`
--
CREATE TABLE `justificaciones` (
  `id_justificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_evento` int(11) NOT NULL,
  `fecha_falta` date NOT NULL,
  `motivo` text NOT NULL,
  `archivo_adjunto_ruta` varchar(512) DEFAULT NULL,
  `estado` enum('PENDIENTE','APROBADA','RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `id_admin_revisor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para `tokens_reseteo_password`
--
CREATE TABLE `tokens_reseteo_password` (
  `id_token` int(11) NOT NULL,
  `selector` varchar(255) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `fecha_expiracion` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para `inscripciones`
--
CREATE TABLE `inscripciones` (
  `id_inscripcion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_evento` int(11) NOT NULL,
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('Inscrito','Cancelado') NOT NULL DEFAULT 'Inscrito'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--
ALTER TABLE `administradores` ADD PRIMARY KEY (`id_admin`), ADD UNIQUE KEY `email` (`email`);
ALTER TABLE `eventos` ADD PRIMARY KEY (`id_evento`);
ALTER TABLE `usuarios` ADD PRIMARY KEY (`id_usuario`), ADD UNIQUE KEY `email` (`email`), ADD UNIQUE KEY `matricula` (`matricula`), ADD UNIQUE KEY `codigo_qr` (`codigo_qr`);
ALTER TABLE `asistencia` ADD PRIMARY KEY (`id_asistencia`), ADD KEY `id_usuario` (`id_usuario`), ADD KEY `id_evento` (`id_evento`);
ALTER TABLE `constancias` ADD PRIMARY KEY (`id_constancia`), ADD UNIQUE KEY `numero_serie` (`numero_serie`), ADD KEY `fk_constancias_usuario` (`id_usuario`), ADD KEY `fk_constancias_evento` (`id_evento`);
ALTER TABLE `justificaciones` ADD PRIMARY KEY (`id_justificacion`), ADD KEY `id_usuario` (`id_usuario`), ADD KEY `id_evento` (`id_evento`), ADD KEY `id_admin_revisor` (`id_admin_revisor`);
ALTER TABLE `tokens_reseteo_password` ADD PRIMARY KEY (`id_token`), ADD KEY `id_usuario` (`id_usuario`), ADD KEY `id_admin` (`id_admin`);
ALTER TABLE `inscripciones` ADD PRIMARY KEY (`id_inscripcion`), ADD UNIQUE KEY `idx_usuario_evento_inscripcion` (`id_usuario`,`id_evento`), ADD KEY `id_evento` (`id_evento`);

--
-- AUTO_INCREMENT de las tablas volcadas
--
ALTER TABLE `administradores` MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `eventos` MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `usuarios` MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `asistencia` MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `constancias` MODIFY `id_constancia` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `justificaciones` MODIFY `id_justificacion` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tokens_reseteo_password` MODIFY `id_token` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `inscripciones` MODIFY `id_inscripcion` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asistencia_ibfk_2` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `constancias`
  ADD CONSTRAINT `constancias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `constancias_ibfk_2` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `justificaciones`
  ADD CONSTRAINT `justificaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `justificaciones_ibfk_2` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `justificaciones_ibfk_3` FOREIGN KEY (`id_admin_revisor`) REFERENCES `administradores` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `tokens_reseteo_password`
  ADD CONSTRAINT `tokens_reseteo_password_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tokens_reseteo_password_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `administradores` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `inscripciones`
  ADD CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inscripciones_ibfk_2` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE ON UPDATE CASCADE;
