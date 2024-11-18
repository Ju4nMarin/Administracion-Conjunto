-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-11-2024 a las 21:21:53
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `torre_erre_53`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `egresos`
--

CREATE TABLE `egresos` (
  `id_egreso` int(11) NOT NULL,
  `tipo_egreso` varchar(50) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha_egreso` date DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `egresos`
--

INSERT INTO `egresos` (`id_egreso`, `tipo_egreso`, `monto`, `fecha_egreso`, `descripcion`) VALUES
(1, 'Mantenimiento', 150000.00, '2024-11-13', 'Reparación ascensor torre 1'),
(2, 'Servicios', 90000.00, '2024-01-20', 'Pago de servicios públicos enero'),
(3, 'Salarios', 1200000.00, '2024-01-31', 'Pago mensual de vigilantes y personal de limpieza'),
(4, 'Mantenimiento', 600000.00, '2024-02-10', 'Pintura de fachada torre 2'),
(5, 'Mantenimiento', 190000.00, '2024-11-10', 'Reparación ascensor torre 1'),
(14, 'Mantenimiento', 22.00, '2024-11-10', '2'),
(16, 'Mantenimiento', 0.02, '2024-11-13', '123'),
(19, 'Salarios', 10000.00, '2024-10-31', 'Si cosas'),
(20, 'Mantenimiento', 123334.00, '2024-11-17', 'Cositas'),
(21, 'Mantenimiento', 1233.00, '2024-11-18', '123');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id_factura` int(11) NOT NULL,
  `id_propietario` int(11) DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `monto_total` decimal(10,2) DEFAULT NULL,
  `estado_pago` varchar(100) DEFAULT NULL,
  `numero_apartamento` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id_factura`, `id_propietario`, `fecha_emision`, `monto_total`, `estado_pago`, `numero_apartamento`) VALUES
(1, 1, '2024-01-10', 850000.00, 'pendiente', 202),
(2, 2, '2024-01-15', 950000.00, 'pendiente', 201),
(3, 3, '2024-01-20', 780000.00, 'pagado', 302),
(4, 4, '2024-01-25', 920000.00, 'pendiente', 401),
(5, 5, '2024-02-01', 880000.00, 'pagado', 504),
(6, 5, '2024-11-15', 10000.00, 'pagado', 504),
(7, 5, '2024-11-15', 1234566.00, 'pagado', 504),
(8, 5, '2024-11-15', 123213.00, 'pagado', 504),
(9, 5, '2024-11-14', 21321.00, 'pagado', 504),
(10, 5, '2024-11-15', 0.01, 'pagado', 504),
(11, 5, '2024-11-15', 0.01, 'pagado', 504),
(12, 5, '2024-11-15', 189833.00, 'pagado', 504),
(13, 1, '2024-11-15', 234324.00, 'pendiente', 202),
(14, 5, '2024-11-15', 2000000.00, 'pagado', 101),
(15, 5, '2024-11-16', 1234566.00, 'pagado', 101),
(18, 65, '2024-11-18', 231231.00, 'pagado', 402),
(19, 65, '2024-11-18', 9000.00, 'pendiente', 402),
(20, 65, '2024-11-18', 213123.00, 'pendiente', 402),
(21, 65, '2024-11-30', 433333.00, 'pendiente', 402),
(22, 65, '2024-11-18', 1000.00, 'pendiente', 504);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inmuebles`
--

CREATE TABLE `inmuebles` (
  `id_inmueble` int(11) NOT NULL,
  `numero_apartamento` int(11) DEFAULT NULL,
  `piso` int(11) DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inmuebles`
--

INSERT INTO `inmuebles` (`id_inmueble`, `numero_apartamento`, `piso`, `estado`) VALUES
(1, 103, 1, 'ocupado'),
(2, 102, 1, 'ocupado'),
(3, 201, 2, 'ocupado'),
(4, 202, 2, 'ocupado'),
(5, 301, 3, 'disponible'),
(6, 302, 3, 'ocupado'),
(7, 401, 4, 'ocupado'),
(8, 402, 4, 'disponible'),
(9, 501, 5, 'disponible'),
(10, 502, 5, 'disponible'),
(30, 504, 5, 'ocupado'),
(33, 404, 4, 'disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_factura` int(11) DEFAULT NULL,
  `fecha_pago` date DEFAULT NULL,
  `monto_pagado` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_factura`, `fecha_pago`, `monto_pagado`) VALUES
(1, 3, '2024-01-22', 780000.00),
(2, 5, '2024-02-05', 880000.00),
(3, 6, '2024-11-15', 10000.00),
(4, 7, '2024-11-15', 1234566.00),
(5, 8, '2024-11-15', 123213.00),
(6, 9, '2024-11-15', 21321.00),
(7, 10, '2024-11-15', 0.01),
(8, 14, '2024-11-15', 2000000.00),
(9, 12, '2024-11-15', 189833.00),
(10, 11, '2024-11-15', 0.01),
(11, 15, '2024-11-16', 1234566.00),
(12, 18, '2024-11-18', 231231.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propietarios`
--

CREATE TABLE `propietarios` (
  `id_propietario` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `NIT` varchar(20) DEFAULT NULL,
  `numero_contacto` varchar(50) DEFAULT NULL,
  `correo_electronico` varchar(100) DEFAULT NULL,
  `apartamento` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `propietarios`
--

INSERT INTO `propietarios` (`id_propietario`, `nombre`, `NIT`, `numero_contacto`, `correo_electronico`, `apartamento`) VALUES
(1, 'Camilo Andrés Pérez', '9201234567', '3101234567', 'carlos.perez@gmail.com', 4),
(2, 'Margarita Torres', '9020200571', '3117654321', 'margarita.torres@hotmail.com', 3),
(3, 'Alberto Rodríguez', '8009876543', '3029876543', 'Alberto.rodriguez@yahoo.com', 6),
(4, 'María Fernanda Gómez', '9004567891', '3131234567', 'maria.gomez@gmail.com', 7),
(5, 'Juan Camilo López', '9003216549', '3141234567', 'juan.lopez@gmail.com', 1),
(65, 'Steven Gomez', '1234567891', '3053177082', 'Steven@gmail.com', 30);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `egresos`
--
ALTER TABLE `egresos`
  ADD PRIMARY KEY (`id_egreso`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id_factura`),
  ADD KEY `id_propietario` (`id_propietario`),
  ADD KEY `numero_apartamento` (`numero_apartamento`);

--
-- Indices de la tabla `inmuebles`
--
ALTER TABLE `inmuebles`
  ADD PRIMARY KEY (`id_inmueble`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_factura` (`id_factura`);

--
-- Indices de la tabla `propietarios`
--
ALTER TABLE `propietarios`
  ADD PRIMARY KEY (`id_propietario`),
  ADD KEY `apartamento` (`apartamento`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `egresos`
--
ALTER TABLE `egresos`
  MODIFY `id_egreso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id_factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `inmuebles`
--
ALTER TABLE `inmuebles`
  MODIFY `id_inmueble` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `propietarios`
--
ALTER TABLE `propietarios`
  MODIFY `id_propietario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`id_propietario`) REFERENCES `propietarios` (`id_propietario`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_factura`) REFERENCES `facturas` (`id_factura`);

--
-- Filtros para la tabla `propietarios`
--
ALTER TABLE `propietarios`
  ADD CONSTRAINT `propietarios_ibfk_1` FOREIGN KEY (`apartamento`) REFERENCES `inmuebles` (`id_inmueble`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
