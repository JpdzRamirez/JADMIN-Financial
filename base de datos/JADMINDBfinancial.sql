-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-07-2021 a las 03:03:32
-- Versión del servidor: 10.4.11-MariaDB
-- Versión de PHP: 7.3.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `JADMINDBFINANCIAL`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `causales`
--

CREATE TABLE `causales` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `costos`
--

CREATE TABLE `costos` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(45) NOT NULL,
  `valor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `costos`
--

INSERT INTO `costos` (`id`, `descripcion`, `valor`) VALUES
(1, 'Seguro', 20000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `creditos`
--

CREATE TABLE `creditos` (
  `id` int(11) NOT NULL,
  `monto` int(11) NOT NULL,
  `plazo` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('Solicitado','Validando','Rechazado','Aprobado','En Cobro','Finalizado') NOT NULL,
  `tasa` decimal(4,2) DEFAULT NULL,
  `cuotas` int(11) DEFAULT NULL,
  `saldo` int(11) DEFAULT NULL,
  `fecha_resultado` date DEFAULT NULL,
  `users_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `creditos`
--

INSERT INTO `creditos` (`id`, `monto`, `plazo`, `fecha`, `estado`, `tasa`, `cuotas`, `saldo`, `fecha_resultado`, `users_id`) VALUES
(1, 300000, 5, '2021-06-24', 'Solicitado', NULL, NULL, NULL, NULL, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `creditos_causales`
--

CREATE TABLE `creditos_causales` (
  `id` int(11) NOT NULL,
  `creditos_id` int(11) NOT NULL,
  `causales_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `creditos_costos`
--

CREATE TABLE `creditos_costos` (
  `id` int(11) NOT NULL,
  `costos_id` int(11) NOT NULL,
  `creditos_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` int(11) NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `abono_capital` int(11) NOT NULL,
  `interes` int(11) NOT NULL,
  `mora` int(11) DEFAULT NULL,
  `estado` enum('Pendiente','Validando','Pagada','Vencida') NOT NULL,
  `creditos_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `valor` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('Pendiente','Acreditado') NOT NULL,
  `facturas_id` int(11) DEFAULT NULL,
  `users_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `referencias`
--

CREATE TABLE `referencias` (
  `id` int(11) NOT NULL,
  `tipo` enum('Personal','Familiar','Laboral') NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `celular` varchar(20) NOT NULL,
  `email` varchar(45) DEFAULT NULL,
  `users_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `scoring`
--

CREATE TABLE `scoring` (
  `id` int(11) NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `tipo` enum('Valores fijos','Rangos') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `scoring`
--

INSERT INTO `scoring` (`id`, `nombre`, `tipo`) VALUES
(1, 'Vivienda', 'Valores fijos'),
(2, 'Salarios minimos', 'Rangos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `segmentos`
--

CREATE TABLE `segmentos` (
  `id` int(11) NOT NULL,
  `valor` varchar(45) DEFAULT NULL,
  `inicio` varchar(45) DEFAULT NULL,
  `fin` varchar(45) DEFAULT NULL,
  `score` int(11) NOT NULL,
  `scoring_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `segmentos`
--

INSERT INTO `segmentos` (`id`, `valor`, `inicio`, `fin`, `score`, `scoring_id`) VALUES
(1, 'Propia', NULL, NULL, 50, 1),
(2, 'Arrendada', NULL, NULL, 40, 1),
(3, 'Familiar', NULL, NULL, 30, 1),
(4, NULL, '0', '1', 50, 2),
(5, NULL, '1', '3', 70, 2),
(6, NULL, '3', '20', 100, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `segmentos_users`
--

CREATE TABLE `segmentos_users` (
  `id` int(11) NOT NULL,
  `segmentos_id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tasas`
--

CREATE TABLE `tasas` (
  `id` int(11) NOT NULL,
  `tipo` enum('Interés','Mora') NOT NULL,
  `valor` decimal(4,2) NOT NULL,
  `mes` int(11) NOT NULL,
  `year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tasas`
--

INSERT INTO `tasas` (`id`, `tipo`, `valor`, `mes`, `year`) VALUES
(1, 'Interés', '1.89', 7, 2021),
(2, 'Mora', '0.59', 7, 2021);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `usuario` varchar(25) NOT NULL,
  `primer_apellido` varchar(45) NOT NULL,
  `segundo_apellido` varchar(45) DEFAULT NULL,
  `primer_nombre` varchar(45) NOT NULL,
  `segundo_nombre` varchar(45) DEFAULT NULL,
  `tipo_identificacion` enum('Cédula de ciudadanía','Cédula de extranjería','Pasaporte') NOT NULL,
  `nro_identificacion` varchar(20) NOT NULL,
  `email` varchar(45) DEFAULT NULL,
  `celular` varchar(45) DEFAULT NULL,
  `password` varchar(120) NOT NULL,
  `condicion` enum('Conductor','Propietario','Particular') NOT NULL,
  `estado` tinyint(1) NOT NULL,
  `rol` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `usuario`, `primer_apellido`, `segundo_apellido`, `primer_nombre`, `segundo_nombre`, `tipo_identificacion`, `nro_identificacion`, `email`, `celular`, `password`, `condicion`, `estado`, `rol`) VALUES
(1, 'jeremy.pedraza', 'hernandez', 'Pedraza', 'Jeremy', 'Ivan', 'Cédula de ciudadanía', '123416546', 'email', 'TEL', '$2y$10$jKsuoS0rEVpNZEOvj2oZz.uyhLXIHXU8RsspTju8ILUBvgfK5QBJy', 'Particular', 1, '1'),
(2, '123456', 'PRUEBA', '', 'PRUEBA', '', 'Cédula de ciudadanía', '123456', '123456@gmail.com', '6339215', '$2y$10$AZoCx67rGggfDr19.CwsZeXj/xEvpS/oRP1lzt/jUF5WsXWsDv3EK', 'Conductor', 1, '2');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `causales`
--
ALTER TABLE `causales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `costos`
--
ALTER TABLE `costos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `creditos`
--
ALTER TABLE `creditos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_solicitudes_users1_idx` (`users_id`);

--
-- Indices de la tabla `creditos_causales`
--
ALTER TABLE `creditos_causales`
  ADD PRIMARY KEY (`id`,`creditos_id`,`causales_id`),
  ADD KEY `fk_creditos_has_causales_causales1_idx` (`causales_id`),
  ADD KEY `fk_creditos_has_causales_creditos1_idx` (`creditos_id`);

--
-- Indices de la tabla `creditos_costos`
--
ALTER TABLE `creditos_costos`
  ADD PRIMARY KEY (`id`,`costos_id`,`creditos_id`),
  ADD KEY `fk_costos_has_creditos_creditos1_idx` (`creditos_id`),
  ADD KEY `fk_costos_has_creditos_costos1_idx` (`costos_id`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_facturas_creditos1_idx` (`creditos_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pagos_facturas1_idx` (`facturas_id`),
  ADD KEY `users_id` (`users_id`);

--
-- Indices de la tabla `referencias`
--
ALTER TABLE `referencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_referencias_users1_idx` (`users_id`);

--
-- Indices de la tabla `scoring`
--
ALTER TABLE `scoring`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `segmentos`
--
ALTER TABLE `segmentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_segmentos_scoring_idx` (`scoring_id`);

--
-- Indices de la tabla `segmentos_users`
--
ALTER TABLE `segmentos_users`
  ADD PRIMARY KEY (`id`,`segmentos_id`,`users_id`),
  ADD KEY `fk_segmentos_has_users_users1_idx` (`users_id`),
  ADD KEY `fk_segmentos_has_users_segmentos1_idx` (`segmentos_id`);

--
-- Indices de la tabla `tasas`
--
ALTER TABLE `tasas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `causales`
--
ALTER TABLE `causales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `costos`
--
ALTER TABLE `costos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `creditos`
--
ALTER TABLE `creditos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `creditos_causales`
--
ALTER TABLE `creditos_causales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `creditos_costos`
--
ALTER TABLE `creditos_costos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `referencias`
--
ALTER TABLE `referencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `scoring`
--
ALTER TABLE `scoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `segmentos`
--
ALTER TABLE `segmentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `segmentos_users`
--
ALTER TABLE `segmentos_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tasas`
--
ALTER TABLE `tasas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `creditos`
--
ALTER TABLE `creditos`
  ADD CONSTRAINT `fk_solicitudes_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `creditos_causales`
--
ALTER TABLE `creditos_causales`
  ADD CONSTRAINT `fk_creditos_has_causales_causales1` FOREIGN KEY (`causales_id`) REFERENCES `causales` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_creditos_has_causales_creditos1` FOREIGN KEY (`creditos_id`) REFERENCES `creditos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `creditos_costos`
--
ALTER TABLE `creditos_costos`
  ADD CONSTRAINT `fk_costos_has_creditos_costos1` FOREIGN KEY (`costos_id`) REFERENCES `costos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_costos_has_creditos_creditos1` FOREIGN KEY (`creditos_id`) REFERENCES `creditos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `fk_facturas_creditos1` FOREIGN KEY (`creditos_id`) REFERENCES `creditos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pagos_facturas1` FOREIGN KEY (`facturas_id`) REFERENCES `facturas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `referencias`
--
ALTER TABLE `referencias`
  ADD CONSTRAINT `fk_referencias_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `segmentos`
--
ALTER TABLE `segmentos`
  ADD CONSTRAINT `fk_segmentos_scoring` FOREIGN KEY (`scoring_id`) REFERENCES `scoring` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `segmentos_users`
--
ALTER TABLE `segmentos_users`
  ADD CONSTRAINT `fk_segmentos_has_users_segmentos1` FOREIGN KEY (`segmentos_id`) REFERENCES `segmentos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_segmentos_has_users_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
