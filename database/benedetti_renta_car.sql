-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-05-2026 a las 22:29:19
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
-- Base de datos: `benedetti_renta_car`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id`, `nombre`, `correo`, `password`, `creado_en`) VALUES
(1, 'Administrador Principal', 'benedettirentacar@gmail.com', '$2y$10$m21fvtG04cRyUjkHDLKbjuw0pAli7KHJ7QuwM9kYgLCIQKhJit1Eu', '2026-03-13 17:38:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id_auditoria` int(11) NOT NULL,
  `tabla_afectada` varchar(100) NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `id_registro_afectado` int(11) NOT NULL,
  `usuario_responsable` int(11) DEFAULT NULL,
  `fecha_evento` timestamp NOT NULL DEFAULT current_timestamp(),
  `detalle` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `tipo_documento` varchar(20) NOT NULL,
  `numero_documento` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `licencia_conduccion` varchar(50) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `codigo_cliente` varchar(50) DEFAULT NULL,
  `total_alquileres_confirmados` int(11) NOT NULL DEFAULT 0,
  `nombres` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `estado_cliente` enum('prospecto','activo') DEFAULT 'prospecto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `tipo_documento`, `numero_documento`, `nombre`, `apellido`, `telefono`, `correo`, `direccion`, `licencia_conduccion`, `fecha_registro`, `codigo_cliente`, `total_alquileres_confirmados`, `nombres`, `apellidos`, `estado_cliente`) VALUES
(1, 'CC', '1129533673', 'Carolay Paola', 'Medina Osorio', '3127318763', 'karito_612@hotmail.com', 'Calle 64 #26-60 Mirador de los Andes', '1129533673-1', '2026-03-13 21:57:29', NULL, 0, 'Carolay Paola', 'Medina Osorio', 'activo'),
(2, 'CC', '1143427461', 'Joelys', 'Benedetti Hernández', '3045523060', 'joelysbenedetti@gmail.com', 'Carrera 24 #63B-78 Apto 1', '1143427461-B1', '2026-03-13 22:06:30', NULL, 0, 'Joelys', 'Benedetti Hernández', 'activo'),
(3, 'CC', '32648547', 'Evangelina', 'Hernandez', '3013982978', 'evitahernandezfrias@gmail.com', 'Carrera 32 #60-19', '32648547-B1', '2026-03-17 00:16:41', NULL, 0, 'Evangelina', 'Hernandez', 'activo'),
(4, 'CC', '72341970', 'Sergio', 'Benedetti Hernández', '3142182917', 'sergiobenedetti@gmail.com', 'Carrera 24 #63B-78 Apto 1', '72341970-B1', '2026-03-17 01:13:26', NULL, 0, 'Sergio', 'Benedetti Hernández', 'activo'),
(5, 'CC', '72276632', 'Kevin', 'Benedetti Hernández', '3004628366', 'kevinbenedettihernandez@gmail.com', 'Calle 64 #26-60 Mirador de los Andes', '72276632-B1', '2026-03-17 21:30:33', NULL, 0, 'Kevin', 'Benedetti Hernández', 'activo'),
(6, 'CC', '72345678', 'Pierre', 'Quintero', '3005678909', 'pierrequintero@gmail.com', 'Av Siempre Viva, Rancagua, Chile', '72345678-B1', '2026-03-19 15:36:06', NULL, 0, 'Pierre', 'Quintero', 'activo'),
(7, 'cedula_ciudadania', '1046709162', 'Evita', 'Benedetti', '3012345432', 'evitabenedetti@gmail.com', NULL, '', '2026-03-24 17:34:48', NULL, 0, 'Evita', 'Benedetti', 'prospecto'),
(8, 'cedula_ciudadania', '72276634', 'Alessandro', 'Benedetti', '3456789008', 'Alessandrobenedetti@gmail.com', NULL, '', '2026-03-24 17:43:01', NULL, 0, 'Alessandro', 'Benedetti', 'prospecto'),
(9, 'cedula_ciudadania', '32648765', 'Eva', 'Benedetti', '3012345634', 'evabenedetti@gmail.com', NULL, '', '2026-03-28 20:35:25', NULL, 0, 'Eva', 'Benedetti', 'prospecto'),
(10, 'cedula_ciudadania', '72276635', 'Alejandro', 'Benedetti', '3214567890', 'alejandrobenedetti@prueba.com', NULL, '', '2026-03-28 20:37:06', NULL, 0, 'Alejandro', 'Benedetti', 'prospecto'),
(12, 'cedula_ciudadania', '23432765', 'Rogelio', 'Patequiva', '211456789', 'rpatequiva@prueba.com', NULL, '', '2026-04-07 21:15:13', NULL, 0, 'Rogelio', 'Patequiva', 'prospecto'),
(13, 'cedula_ciudadania', '1046709161', 'Leandro', 'Benedetti Medina', '3205553627', 'leandrobenedetti@gmail.com', NULL, '', '2026-04-14 21:33:42', NULL, 0, 'Leandro', 'Benedetti Medina', 'prospecto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id_documento` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_documento` enum('cedula','licencia_conduccion','pasaporte','otro') NOT NULL,
  `url_archivo` text NOT NULL,
  `estado_revision` enum('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
  `comentario_revision` text DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_reserva` int(11) NOT NULL,
  `metodo_pago` enum('paypal','stripe','payu','wompi','mercadopago','efectivo','transferencia') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `estado_pago` enum('pendiente','aprobado','rechazado','reembolsado') NOT NULL DEFAULT 'pendiente',
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `referencia_pago` varchar(100) DEFAULT NULL,
  `transaccion_id` varchar(150) DEFAULT NULL,
  `pasarela` varchar(50) DEFAULT NULL,
  `comprobante_url` text DEFAULT NULL,
  `respuesta_pasarela` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_reserva`, `metodo_pago`, `monto`, `estado_pago`, `fecha_pago`, `referencia_pago`, `transaccion_id`, `pasarela`, `comprobante_url`, `respuesta_pasarela`) VALUES
(1, 29, '', 960000.00, 'pendiente', '2026-04-07 16:53:01', 'PAY-RES-20260407185301-9922', NULL, 'pendiente_gateway', NULL, NULL),
(2, 33, '', 200000.00, 'pendiente', '2026-04-07 21:15:13', 'PAY-RES-20260407231513-6287', NULL, 'pendiente_gateway', NULL, NULL),
(3, 34, '', 200000.00, 'pendiente', '2026-04-07 21:15:13', 'PAY-RES-20260407231513-4823', NULL, 'wompi', NULL, NULL),
(4, 35, '', 720000.00, 'pendiente', '2026-04-07 21:16:29', 'PAY-RES-20260407231629-2034', NULL, 'manual_qr', NULL, NULL),
(5, 36, '', 400000.00, 'pendiente', '2026-04-07 21:50:13', 'PAY-RES-20260407235013-7804', NULL, 'pendiente_gateway', NULL, NULL),
(6, 37, '', 500000.00, 'pendiente', '2026-04-07 21:55:52', 'PAY-RES-20260407235552-5546', NULL, 'pendiente_gateway', NULL, NULL),
(7, 38, '', 720000.00, 'pendiente', '2026-04-14 21:33:42', 'PAY-RES-20260414233342-9872', NULL, 'wompi', NULL, NULL),
(8, 39, '', 1000000.00, '', '2026-04-15 18:45:33', 'PAY-RES-20260415204533-6810', NULL, 'wompi_simulado', NULL, NULL),
(9, 40, '', 1000000.00, 'pendiente', '2026-04-15 18:49:16', 'PAY-RES-20260415204916-5405', NULL, 'manual_qr', NULL, NULL),
(10, 41, '', 720000.00, '', '2026-04-22 19:16:38', 'PAY-RES-20260422211638-2976', NULL, 'wompi_simulado', NULL, NULL),
(11, 42, '', 1200000.00, 'pendiente', '2026-04-22 19:22:08', 'PAY-RES-20260422212208-7466', NULL, 'manual_qr', NULL, NULL),
(12, 43, '', 720000.00, '', '2026-04-23 23:39:52', 'PAY-RES-20260424013952-2254', NULL, 'wompi_simulado', NULL, NULL),
(13, 44, '', 1000000.00, '', '2026-04-25 19:47:53', 'PAY-RES-20260425214753-1086', NULL, 'wompi_simulado', NULL, NULL),
(14, 45, '', 200000.00, 'pendiente', '2026-04-28 00:07:10', 'PAY-RES-20260428020710-6719', NULL, 'pendiente_gateway', NULL, NULL),
(15, 46, '', 200000.00, 'pendiente', '2026-04-28 00:08:40', 'PAY-RES-20260428020840-5215', NULL, 'wompi_pse_simulado', NULL, NULL),
(16, 47, '', 1660000.00, 'pendiente', '2026-04-28 18:30:34', 'PAY-RES-20260428203034-8998', NULL, 'wompi_pse_simulado', NULL, NULL),
(17, 48, '', 400000.00, '', '2026-04-29 17:04:39', 'PAY-RES-20260429190439-5081', NULL, 'wompi_simulado', NULL, NULL),
(18, 49, '', 720000.00, 'pendiente', '2026-04-30 22:34:02', 'PAY-RES-20260501003402-4378', NULL, 'pendiente_gateway', NULL, NULL),
(19, 50, '', 1200000.00, 'pendiente', '2026-05-04 20:39:35', 'PAY-RES-20260504223935-6184', NULL, 'pendiente_gateway', NULL, NULL),
(20, 51, '', 1000000.00, 'pendiente', '2026-05-04 21:14:31', 'PAY-RES-20260504231431-9047', NULL, 'pendiente_gateway', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id_reserva` int(11) NOT NULL,
  `codigo_reserva` varchar(20) DEFAULT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_vehiculo` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `horas_gracia` int(11) NOT NULL DEFAULT 1,
  `tarifa_hora_extra` decimal(10,2) NOT NULL DEFAULT 30000.00,
  `fecha_entrega_real` datetime DEFAULT NULL,
  `horas_extra` int(11) NOT NULL DEFAULT 0,
  `cargo_extra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `modalidad_entrega` enum('sitio','domicilio') NOT NULL DEFAULT 'sitio',
  `lugar_entrega` varchar(150) DEFAULT NULL,
  `costo_entrega` decimal(10,2) NOT NULL DEFAULT 0.00,
  `modalidad_devolucion` enum('sitio','domicilio') NOT NULL DEFAULT 'sitio',
  `lugar_devolucion` varchar(150) DEFAULT NULL,
  `costo_devolucion` decimal(10,2) NOT NULL DEFAULT 0.00,
  `servicio_lavado` enum('si','no') NOT NULL DEFAULT 'no',
  `costo_lavado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado_limpieza_entrega` enum('limpio','muy_limpio') NOT NULL DEFAULT 'limpio',
  `estado_limpieza_devolucion` enum('limpio','sucio','muy_sucio') DEFAULT NULL,
  `estado_reserva` enum('pendiente','confirmada','cancelada','finalizada') NOT NULL DEFAULT 'pendiente',
  `total_pago` decimal(10,2) NOT NULL DEFAULT 0.00,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `modo_pago` varchar(30) DEFAULT NULL,
  `codigo_referido_usado` varchar(50) DEFAULT NULL,
  `anticipo_requerido` decimal(10,2) NOT NULL DEFAULT 0.00,
  `anticipo_pagado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bloquea_disponibilidad` tinyint(1) NOT NULL DEFAULT 0,
  `total_estimado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `horas_extra_cobradas` int(11) NOT NULL DEFAULT 0,
  `recargo_horas_extra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_final` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_hora_devolucion_real` datetime DEFAULT NULL,
  `lat_entrega` decimal(10,7) DEFAULT NULL,
  `lng_entrega` decimal(10,7) DEFAULT NULL,
  `lat_devolucion` decimal(10,7) DEFAULT NULL,
  `lng_devolucion` decimal(10,7) DEFAULT NULL,
  `distancia_km` decimal(10,2) DEFAULT NULL,
  `costo_km` decimal(10,2) DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id_reserva`, `codigo_reserva`, `id_cliente`, `id_usuario`, `id_vehiculo`, `fecha_inicio`, `fecha_fin`, `horas_gracia`, `tarifa_hora_extra`, `fecha_entrega_real`, `horas_extra`, `cargo_extra`, `modalidad_entrega`, `lugar_entrega`, `costo_entrega`, `modalidad_devolucion`, `lugar_devolucion`, `costo_devolucion`, `servicio_lavado`, `costo_lavado`, `estado_limpieza_entrega`, `estado_limpieza_devolucion`, `estado_reserva`, `total_pago`, `observaciones`, `fecha_creacion`, `modo_pago`, `codigo_referido_usado`, `anticipo_requerido`, `anticipo_pagado`, `bloquea_disponibilidad`, `total_estimado`, `horas_extra_cobradas`, `recargo_horas_extra`, `total_final`, `fecha_hora_devolucion_real`, `lat_entrega`, `lng_entrega`, `lat_devolucion`, `lng_devolucion`, `distancia_km`, `costo_km`) VALUES
(15, 'RES-1774036993', 1, NULL, 2, '2026-03-21 11:00:00', '2026-03-23 18:00:00', 1, 30000.00, '2026-03-23 18:00:00', 0, 0.00, 'sitio', NULL, 0.00, 'sitio', NULL, 0.00, 'no', 0.00, 'limpio', NULL, 'finalizada', 580000.00, '', '2026-03-20 20:03:13', NULL, NULL, 0.00, 0.00, 0, 0.00, 0, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'RES-1774042057', 5, NULL, 3, '2026-03-24 09:00:00', '2026-03-26 09:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto', 0.00, 'sitio', 'Aeropuerto', 0.00, 'no', 0.00, 'limpio', NULL, '', 480000.00, '', '2026-03-20 21:27:37', 'qr', '', 0.00, 0.00, 0, 0.00, 0, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'RES-1774044049', 5, NULL, 2, '2026-03-24 08:00:00', '2026-03-25 08:30:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto', 0.00, 'sitio', 'Aeropuerto', 0.00, 'no', 0.00, 'limpio', NULL, '', 400000.00, '', '2026-03-20 22:00:49', 'debito', '', 0.00, 0.00, 0, 0.00, 0, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'RES-1774373767', 7, NULL, 3, '2026-03-28 08:00:00', '2026-03-30 08:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto', 0.00, 'sitio', 'Aeropuerto', 0.00, 'no', 0.00, 'limpio', NULL, '', 480000.00, '', '2026-03-24 17:36:07', 'credito', '', 0.00, 0.00, 0, 0.00, 0, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'RES-1774374181', 8, NULL, 3, '2026-04-04 08:00:00', '2026-04-10 08:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto', 0.00, 'sitio', 'Aeropuerto', 0.00, 'no', 0.00, 'limpio', NULL, '', 1440000.00, '', '2026-03-24 17:43:01', 'credito', '', 0.00, 0.00, 0, 0.00, 0, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'RES-1774384562', 5, NULL, 3, '2026-03-26 08:00:00', '2026-03-28 09:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto', 0.00, 'sitio', 'Aeropuerto', 0.00, 'no', 0.00, 'limpio', NULL, '', 720000.00, '', '2026-03-24 20:36:02', 'debito', '', 0.00, 0.00, 0, 720000.00, 0, 0.00, 720000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'RES-1774384642', 5, NULL, 3, '2026-03-26 08:00:00', '2026-03-28 08:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto', 0.00, 'sitio', 'Aeropuerto', 0.00, 'no', 0.00, 'limpio', NULL, '', 480000.00, '', '2026-03-24 20:37:22', 'debito', '', 0.00, 0.00, 0, 480000.00, 0, 0.00, 480000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'RES-1774384657', 5, NULL, 3, '2026-03-26 08:00:00', '2026-03-28 09:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto', 0.00, 'sitio', 'Aeropuerto', 0.00, 'no', 0.00, 'limpio', NULL, '', 720000.00, '', '2026-03-24 20:37:37', 'debito', '', 0.00, 0.00, 0, 720000.00, 0, 0.00, 720000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 'RES-1774387774', 5, NULL, 1, '2026-03-26 07:00:00', '2026-03-30 12:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto', 0.00, 'sitio', 'Aeropuerto', 0.00, 'no', 0.00, 'limpio', NULL, '', 920000.00, '', '2026-03-24 21:29:34', 'debito', '', 0.00, 0.00, 0, 920000.00, 4, 120000.00, 920000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 'RES-1774388436', 5, NULL, 3, '2026-03-26 08:00:00', '2026-03-30 10:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto', 0.00, 'sitio', 'Aeropuerto', 0.00, 'no', 0.00, 'limpio', NULL, '', 960000.00, '', '2026-03-24 21:40:36', 'debito', '', 0.00, 0.00, 0, 960000.00, 1, 0.00, 960000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 'RES-1774389099', 5, NULL, 3, '2026-03-26 08:00:00', '2026-03-27 09:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto', 0.00, 'sitio', 'Aeropuerto', 0.00, 'no', 0.00, 'limpio', NULL, '', 240000.00, '', '2026-03-24 21:51:39', 'debito', '', 0.00, 0.00, 0, 240000.00, 1, 0.00, 240000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 'RES-1774729835', 7, NULL, 3, '2026-03-29 09:00:00', '2026-03-30 08:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'no', 0.00, 'limpio', NULL, '', 240000.00, '', '2026-03-28 20:30:35', 'credito', '', 0.00, 0.00, 0, 240000.00, 0, 0.00, 240000.00, NULL, 0.0000000, 0.0000000, 0.0000000, 0.0000000, 0.00, 0.00),
(27, 'RES-1774730125', 9, NULL, 3, '2026-03-29 08:30:00', '2026-03-31 09:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'no', 0.00, 'limpio', NULL, '', 480000.00, '', '2026-03-28 20:35:25', 'credito', '', 0.00, 0.00, 0, 480000.00, 1, 0.00, 480000.00, NULL, 0.0000000, 0.0000000, 0.0000000, 0.0000000, 0.00, 0.00),
(28, 'RES-1774730226', 10, NULL, 3, '2026-03-30 07:30:00', '2026-04-02 09:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'no', 0.00, 'limpio', NULL, '', 800000.00, '', '2026-03-28 20:37:06', 'credito', '', 0.00, 0.00, 0, 800000.00, 2, 80000.00, 800000.00, NULL, 0.0000000, 0.0000000, 0.0000000, 0.0000000, 0.00, 0.00),
(29, 'RES-20260407185301-9', 5, NULL, 3, '2026-05-01 08:00:00', '2026-05-05 08:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'no', 0.00, 'limpio', NULL, 'pendiente', 960000.00, 'Prueba de reserva guardada', '2026-04-07 16:53:01', 'debito', NULL, 0.00, 0.00, 0, 960000.00, 0, 0.00, 960000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(33, 'RES-20260407231513-6', 12, NULL, 1, '2026-04-09 08:00:00', '2026-04-10 08:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'no', 0.00, 'limpio', NULL, '', 200000.00, '', '2026-04-07 21:15:13', 'pendiente', NULL, 0.00, 0.00, 0, 200000.00, 0, 0.00, 200000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(34, 'RES-20260407231513-4', 12, NULL, 1, '2026-04-09 08:00:00', '2026-04-10 08:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'no', 0.00, 'limpio', NULL, '', 200000.00, '', '2026-04-07 21:15:13', 'tarjeta', NULL, 0.00, 0.00, 0, 200000.00, 0, 0.00, 200000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(35, 'RES-20260407231629-2', 5, NULL, 3, '2026-04-16 08:30:00', '2026-04-18 14:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'no', 0.00, 'limpio', NULL, '', 720000.00, '', '2026-04-07 21:16:29', 'qr', NULL, 0.00, 0.00, 0, 720000.00, 6, 0.00, 720000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(36, 'RES-20260407235013-7', 5, NULL, 2, '2026-04-15 11:00:00', '2026-04-17 10:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'no', 0.00, 'limpio', NULL, '', 400000.00, '', '2026-04-07 21:50:13', 'pendiente', NULL, 0.00, 0.00, 0, 400000.00, 23, 0.00, 400000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(37, 'RES-20260407235552-5', 5, NULL, 5, '2026-04-09 07:00:00', '2026-04-11 07:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'no', 0.00, 'limpio', NULL, '', 500000.00, '', '2026-04-07 21:55:52', 'pendiente', NULL, 0.00, 0.00, 0, 500000.00, 0, 0.00, 500000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(38, 'RES-20260414233342-9', 13, NULL, 3, '2026-04-22 08:00:00', '2026-04-25 08:30:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'no', 0.00, 'limpio', NULL, '', 720000.00, '', '2026-04-14 21:33:42', 'tarjeta', NULL, 0.00, 0.00, 0, 720000.00, 1, 0.00, 720000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(39, 'RES-20260415204533-6', 14, NULL, 5, '2026-04-17 10:00:00', '2026-04-21 07:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'no', 0.00, 'limpio', NULL, '', 1000000.00, '', '2026-04-15 18:45:33', 'tarjeta', NULL, 300000.00, 0.00, 0, 1000000.00, 21, 0.00, 1000000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(40, 'RES-20260415204916-5', 5, NULL, 5, '2026-04-25 11:00:00', '2026-04-28 19:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'no', 0.00, 'limpio', NULL, '', 1000000.00, '', '2026-04-15 18:49:16', 'qr', NULL, 300000.00, 0.00, 0, 1000000.00, 8, 0.00, 1000000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(41, 'RES-20260422211638-2', 15, NULL, 3, '2026-04-24 09:00:00', '2026-04-27 09:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'no', 0.00, 'limpio', NULL, '', 720000.00, 'Prueba de Reserva', '2026-04-22 19:16:38', 'tarjeta', NULL, 216000.00, 0.00, 0, 720000.00, 0, 0.00, 720000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(42, 'RES-20260422212208-7', 3, NULL, 3, '2026-04-25 09:00:00', '2026-04-30 09:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'no', 0.00, 'limpio', NULL, '', 1200000.00, 'Reserva Sandra', '2026-04-22 19:22:08', 'qr', NULL, 360000.00, 0.00, 0, 1200000.00, 0, 0.00, 1200000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(43, 'RES-20260424013952-2', 5, NULL, 3, '2026-04-24 08:30:00', '2026-04-27 09:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'no', 0.00, 'limpio', NULL, '', 720000.00, 'Prueba de reserva', '2026-04-23 23:39:52', 'tarjeta', NULL, 216000.00, 0.00, 0, 720000.00, 1, 0.00, 720000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(44, 'RES-20260425214753-1', 15, NULL, 1, '2026-05-01 09:00:00', '2026-05-06 09:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'no', 0.00, 'limpio', NULL, '', 1000000.00, 'Prueba de Reserva cliente nuevo', '2026-04-25 19:47:53', 'tarjeta', NULL, 300000.00, 0.00, 0, 1000000.00, 0, 0.00, 1000000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(45, 'RES-20260428020710-6', 5, NULL, 2, '2026-05-05 07:00:00', '2026-05-06 07:30:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'no', 0.00, 'limpio', NULL, '', 200000.00, 'Prueba', '2026-04-28 00:07:10', 'pendiente', NULL, 60000.00, 0.00, 0, 200000.00, 1, 0.00, 200000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(46, 'RES-20260428020840-5', 5, NULL, 2, '2026-05-05 07:00:00', '2026-05-06 07:30:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'no', 0.00, 'limpio', NULL, '', 200000.00, 'Prueba', '2026-04-28 00:08:40', 'pse', NULL, 60000.00, 0.00, 0, 200000.00, 1, 0.00, 200000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(47, 'RES-20260428203034-8', 5, NULL, 2, '2026-04-30 06:00:00', '2026-05-08 08:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Aeropuerto Ernesto Cortissoz', 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'no', 0.00, 'limpio', NULL, '', 1660000.00, '', '2026-04-28 18:30:34', 'pse', NULL, 498000.00, 0.00, 0, 1660000.00, 2, 60000.00, 1660000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(48, 'RES-20260429190439-5', 5, NULL, 2, '2026-05-14 14:00:00', '2026-05-16 14:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'no', 0.00, 'limpio', NULL, '', 400000.00, 'Prueba', '2026-04-29 17:04:39', 'tarjeta', NULL, 120000.00, 0.00, 0, 400000.00, 0, 0.00, 400000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(49, 'RES-20260501003402-4', 5, NULL, 3, '2026-06-01 09:00:00', '2026-06-04 08:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'no', 0.00, 'limpio', NULL, '', 720000.00, 'prueba', '2026-04-30 22:34:02', 'pendiente', NULL, 216000.00, 0.00, 0, 720000.00, 23, 0.00, 720000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(50, 'RES-20260504223935-6', 16, NULL, 2, '2026-06-01 14:00:00', '2026-06-06 20:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'no', 0.00, 'limpio', NULL, '', 1200000.00, 'PRueba de reserva cliente nuevo', '2026-05-04 20:39:35', 'pendiente', NULL, 360000.00, 0.00, 0, 1200000.00, 6, 0.00, 1200000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00),
(51, 'RES-20260504231431-9', 5, NULL, 2, '2026-05-06 09:00:00', '2026-05-11 09:00:00', 1, 30000.00, NULL, 0, 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'sitio', 'Oficina Benedetti Rent a Car', 0.00, 'no', 0.00, 'limpio', NULL, '', 1000000.00, 'Prueba de reserva', '2026-05-04 21:14:31', 'pendiente', NULL, 300000.00, 0.00, 0, 1000000.00, 0, 0.00, 1000000.00, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`) VALUES
(1, 'administrador', 'Control total del sistema'),
(2, 'cliente', 'Usuario que alquila vehículos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `estado` enum('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
  `rol_id` int(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `codigo_usuario` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `password_hash`, `telefono`, `estado`, `rol_id`, `fecha_registro`, `codigo_usuario`, `updated_at`) VALUES
(1000, 'Kevin Andrés', 'Benedetti Hernández', 'benedettirentacar@gmail.com', 'temporal', '3004628366', 'activo', 1, '2026-03-12 20:44:14', 'BRC-1000', '2026-03-12 21:22:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id_vehiculo` int(11) NOT NULL,
  `codigo_vehiculo` varchar(20) DEFAULT NULL,
  `marca` varchar(100) NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `capacidad` int(11) DEFAULT NULL,
  `transmision` enum('manual','automatica') DEFAULT NULL,
  `categoria` enum('sedan','hatchback','suv','pickup','coupe','van','camioneta') DEFAULT NULL,
  `anio` int(11) DEFAULT NULL,
  `placa` varchar(20) NOT NULL,
  `precio_dia` decimal(10,2) NOT NULL,
  `estado` enum('disponible','reservado','mantenimiento','inactivo') NOT NULL DEFAULT 'disponible',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `precio_especial_3_dias` decimal(10,2) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `precio_hora_extra` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id_vehiculo`, `codigo_vehiculo`, `marca`, `modelo`, `color`, `capacidad`, `transmision`, `categoria`, `anio`, `placa`, `precio_dia`, `estado`, `created_at`, `updated_at`, `precio_especial_3_dias`, `imagen`, `precio_hora_extra`) VALUES
(1, 'VEH-2000', 'Kia', 'Picanto', 'Gris Plata', 5, 'manual', 'sedan', 2026, 'POS119', 200000.00, 'disponible', '2026-03-12 21:42:38', '2026-03-24 21:06:59', 180000.00, 'kia_picanto.jpeg', 30000.00),
(2, 'VEH-2001', 'Renault', 'Kwid', 'Blanco', 5, 'manual', 'hatchback', 2026, 'POS379', 200000.00, 'disponible', '2026-03-13 19:16:52', '2026-03-24 21:06:50', 180000.00, 'Renault_Kwid_1.jpeg', 30000.00),
(3, 'VEH-2002', 'Suzuki', 'Swift Dzire', 'Gris Fuerte', 5, 'automatica', 'sedan', 2026, 'ABC123', 240000.00, 'disponible', '2026-03-13 20:11:09', '2026-03-24 21:06:33', 220000.00, 'Suzuki_Swift_Dzire.jpeg', 40000.00),
(5, 'VEH-2003', 'Mini Cooper', 'RS', 'Negro Córcega', 4, 'automatica', 'hatchback', 2025, 'MDZ098', 250000.00, 'disponible', '2026-03-16 15:42:26', '2026-05-14 20:10:38', 220000.00, 'vehiculo_1778789438_2329.png', NULL),
(6, 'VEH-2004', 'Mini Cooper', 'DLX', 'Azul Córcega', 4, 'automatica', 'coupe', 2018, 'KYA178', 250000.00, 'disponible', '2026-04-30 16:22:10', '2026-05-14 20:20:17', 220000.00, 'vehiculo_1777566130_9556.png', NULL),
(7, 'VEH-2005', 'Mini Cooper', 'Cabriolet', 'Azul Eléctrico', 4, 'automatica', 'hatchback', 2017, 'KBH809', 270000.00, 'disponible', '2026-05-14 20:18:44', '2026-05-14 20:19:22', 250000.00, 'vehiculo_1778789924_3961.png', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id_auditoria`),
  ADD KEY `fk_auditoria_usuario` (`usuario_responsable`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `idx_documentos_usuario` (`id_usuario`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD UNIQUE KEY `referencia_pago` (`referencia_pago`),
  ADD KEY `idx_pagos_reserva` (`id_reserva`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id_reserva`),
  ADD UNIQUE KEY `codigo_reserva` (`codigo_reserva`),
  ADD KEY `idx_reservas_usuario` (`id_usuario`),
  ADD KEY `idx_reservas_vehiculo` (`id_vehiculo`),
  ADD KEY `idx_reservas_estado` (`estado_reserva`),
  ADD KEY `idx_reservas_fechas` (`fecha_inicio`,`fecha_fin`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `uq_roles_nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `codigo_usuario` (`codigo_usuario`),
  ADD KEY `idx_usuarios_correo` (`correo`),
  ADD KEY `idx_usuarios_rol_id` (`rol_id`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id_vehiculo`),
  ADD UNIQUE KEY `placa` (`placa`),
  ADD UNIQUE KEY `codigo_vehiculo` (`codigo_vehiculo`),
  ADD KEY `idx_vehiculos_estado` (`estado`),
  ADD KEY `idx_vehiculos_placa` (`placa`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1001;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id_vehiculo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`usuario_responsable`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `fk_documento_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pago_reserva` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`);

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_reserva_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_reserva_vehiculo` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculos` (`id_vehiculo`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
