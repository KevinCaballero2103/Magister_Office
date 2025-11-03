-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20251018.6d3d61fe5f
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 03, 2025 at 01:39 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `magister_office`
--

-- --------------------------------------------------------

--
-- Table structure for table `caja`
--

CREATE TABLE `caja` (
  `id` int NOT NULL,
  `tipo_movimiento` enum('INGRESO','EGRESO') NOT NULL,
  `categoria` enum('VENTA','COMPRA','OTRO') NOT NULL,
  `id_referencia` int DEFAULT NULL COMMENT 'ID de venta/compra relacionada',
  `concepto` varchar(200) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_movimiento` date NOT NULL,
  `observaciones` text,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_registro` varchar(100) DEFAULT NULL,
  `movimiento_relacionado` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `caja`
--

INSERT INTO `caja` (`id`, `tipo_movimiento`, `categoria`, `id_referencia`, `concepto`, `monto`, `fecha_movimiento`, `observaciones`, `fecha_registro`, `usuario_registro`, `movimiento_relacionado`) VALUES
(1, 'EGRESO', 'COMPRA', 1, 'COMPRA #1 - OFIMARKET', 1400000.00, '2025-10-08', NULL, '2025-10-08 20:07:39', NULL, NULL),
(2, 'EGRESO', 'COMPRA', 2, 'COMPRA #2 - ALAMO ORIGI', 60000.00, '2025-10-08', NULL, '2025-10-08 20:10:31', NULL, NULL),
(3, 'EGRESO', 'COMPRA', 3, 'COMPRA #3 - SANTEI', 100000.00, '2025-10-08', NULL, '2025-10-08 20:13:02', NULL, NULL),
(4, 'EGRESO', 'COMPRA', 4, 'COMPRA #4 - ALAMON\'T', 19000.00, '2025-10-08', NULL, '2025-10-08 20:15:32', NULL, NULL),
(5, 'EGRESO', 'COMPRA', 5, 'COMPRA #5 - OFIMARKET', 350000.00, '2025-10-08', NULL, '2025-10-08 20:18:04', NULL, NULL),
(6, 'EGRESO', 'COMPRA', 6, 'COMPRA #6 - OFIMARKET', 515000.00, '2025-10-08', NULL, '2025-10-08 20:20:15', NULL, NULL),
(7, 'EGRESO', 'COMPRA', 7, 'COMPRA #7 - OFIMARKET', 3250000.00, '2025-10-09', NULL, '2025-10-09 19:53:54', NULL, NULL),
(9, 'EGRESO', 'COMPRA', 9, 'COMPRA #9 - ALAMO2', 3040000.00, '2025-10-12', NULL, '2025-10-12 00:44:06', NULL, NULL),
(13, 'EGRESO', 'COMPRA', 18, 'COMPRA #18 - ALAMO ORIGI', 20000.00, '2025-10-15', NULL, '2025-10-15 00:49:36', NULL, NULL),
(15, 'INGRESO', 'VENTA', 4, 'VENTA #4 - KEVIN SEBASTIAN CABALLERO GODOY', 3000.00, '2025-10-16', NULL, '2025-10-15 19:57:08', NULL, NULL),
(17, 'EGRESO', 'COMPRA', 20, 'COMPRA #20 - ALAMO ORIGI', 200000.00, '2025-10-15', NULL, '2025-10-15 20:07:45', NULL, NULL),
(18, 'INGRESO', 'VENTA', 5, 'VENTA #5 - KEVIN SEBASTIAN CABALLERO GODOY (Ticket: 0000001)', 275000.00, '2025-10-19', NULL, '2025-10-19 23:15:33', NULL, NULL),
(19, 'INGRESO', 'VENTA', 6, 'VENTA #6 - KEVIN SEBASTIAN CABALLERO GODOY', 66000.00, '2025-10-22', NULL, '2025-10-22 00:39:29', NULL, NULL),
(20, 'INGRESO', 'VENTA', 7, 'VENTA #7 - KEVIN SEBASTIAN CABALLERO GODOY (Ticket: 0000007)', 746075.00, '2025-10-22', NULL, '2025-10-22 21:15:59', NULL, NULL),
(21, 'INGRESO', 'VENTA', 8, 'VENTA #8 - KEVIN SEBASTIAN CABALLERO GODOY', 550000.00, '2025-10-22', NULL, '2025-10-22 21:17:48', NULL, NULL),
(23, 'INGRESO', 'VENTA', 10, 'VENTA #10 - KEVIN SEBASTIAN CABALLERO GODOY (FACTURA: 001-001-0000835)', 3000.00, '2025-10-23', NULL, '2025-10-23 22:56:26', NULL, NULL),
(24, 'INGRESO', 'VENTA', 11, 'VENTA #11 - MAGNA ADELINA GODOY OLMEDO (FACTURA: 001-001-0000836)', 1130000.00, '2025-10-24', NULL, '2025-10-24 00:09:48', NULL, NULL),
(25, 'INGRESO', 'VENTA', 12, 'VENTA #12 - MAGNA ADELINA GODOY OLMEDO (FACTURA: 001-001-0000837)', 250000.00, '2025-10-24', NULL, '2025-10-24 21:42:36', NULL, NULL),
(26, 'INGRESO', 'VENTA', 13, 'VENTA #13 - MAGNA ADELINA GODOY OLMEDO (FACTURA: 001-001-0000838)', 1575000.00, '2025-10-24', NULL, '2025-10-24 22:17:40', NULL, NULL),
(27, 'INGRESO', 'VENTA', 14, 'VENTA #14 - MAGNA ADELINA GODOY OLMEDO (FACTURA: 001-001-0000839)', 27000.00, '2025-10-24', NULL, '2025-10-24 22:43:22', NULL, NULL),
(29, 'EGRESO', 'OTRO', 12, 'ANULACIÓN FACTURA #12 - MAGNA ADELINA GODOY OLMEDO (001-001-0000837)', 250000.00, '2025-11-02', 'Anulado por: ADMIN\nMotivo: Prueba 1', '2025-11-02 17:21:45', 'ADMIN', 25),
(30, 'EGRESO', 'OTRO', 14, 'ANULACIÓN FACTURA #14 - MAGNA ADELINA GODOY OLMEDO (001-001-0000839)', 27000.00, '2025-11-02', 'Anulado por: ADMIN\nMotivo: wery', '2025-11-02 17:23:03', 'ADMIN', 27),
(31, 'EGRESO', 'OTRO', 11, 'ANULACIÓN FACTURA #11 - MAGNA ADELINA GODOY OLMEDO (001-001-0000836)', 1130000.00, '2025-11-02', 'Anulado por: ADMIN\nMotivo: prueba23', '2025-11-02 20:09:29', 'ADMIN', 24),
(32, 'EGRESO', 'OTRO', 13, 'ANULACIÓN FACTURA #13 - MAGNA ADELINA GODOY OLMEDO (001-001-0000838)', 1575000.00, '2025-11-02', 'Anulado por: ADMIN\nMotivo: wdsfghkjl', '2025-11-02 20:13:10', 'ADMIN', 26),
(33, 'EGRESO', 'OTRO', 10, 'ANULACIÓN FACTURA #10 - KEVIN SEBASTIAN CABALLERO GODOY (001-001-0000835)', 3000.00, '2025-11-02', 'Anulado por: ADMIN\nMotivo: prueba444', '2025-11-03 01:21:03', 'ADMIN', 23),
(34, 'EGRESO', 'OTRO', 6, 'ANULACIÓN FACTURA #6 - KEVIN SEBASTIAN CABALLERO GODOY (N/A)', 66000.00, '2025-11-02', 'Anulado por: ADMIN\nMotivo: wergh', '2025-11-03 01:21:49', 'ADMIN', 19);

-- --------------------------------------------------------

--
-- Table structure for table `cierres_caja`
--

CREATE TABLE `cierres_caja` (
  `id` int NOT NULL,
  `fecha_apertura` datetime NOT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `saldo_inicial` decimal(12,2) NOT NULL DEFAULT '0.00',
  `saldo_final` decimal(12,2) DEFAULT NULL,
  `total_ingresos` decimal(12,2) DEFAULT '0.00',
  `total_egresos` decimal(12,2) DEFAULT '0.00',
  `saldo_sistema` decimal(12,2) DEFAULT NULL COMMENT 'Lo que debería haber según el sistema',
  `saldo_fisico` decimal(12,2) DEFAULT NULL COMMENT 'Lo que realmente hay (conteo físico)',
  `diferencia` decimal(12,2) DEFAULT NULL COMMENT 'saldo_fisico - saldo_sistema',
  `observaciones_apertura` text,
  `observaciones_cierre` text,
  `estado` enum('ABIERTA','CERRADA') NOT NULL DEFAULT 'ABIERTA',
  `usuario_apertura` varchar(100) DEFAULT NULL,
  `usuario_cierre` varchar(100) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cierres_caja`
--

INSERT INTO `cierres_caja` (`id`, `fecha_apertura`, `fecha_cierre`, `saldo_inicial`, `saldo_final`, `total_ingresos`, `total_egresos`, `saldo_sistema`, `saldo_fisico`, `diferencia`, `observaciones_apertura`, `observaciones_cierre`, `estado`, `usuario_apertura`, `usuario_cierre`, `fecha_registro`) VALUES
(1, '2025-10-26 18:47:00', '2025-10-26 18:47:00', 10000.00, 10000.00, 0.00, 0.00, 10000.00, 10000.00, 0.00, NULL, NULL, 'CERRADA', 'Kevin', 'Kevin', '2025-10-27 00:47:36');

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `id` int NOT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `apellido_cliente` varchar(100) NOT NULL,
  `ci_ruc_cliente` varchar(20) NOT NULL,
  `telefono_cliente` varchar(20) DEFAULT NULL,
  `correo_cliente` varchar(150) DEFAULT NULL,
  `direccion_cliente` varchar(150) DEFAULT NULL,
  `fecha_cliente` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado_cliente` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id`, `nombre_cliente`, `apellido_cliente`, `ci_ruc_cliente`, `telefono_cliente`, `correo_cliente`, `direccion_cliente`, `fecha_cliente`, `estado_cliente`) VALUES
(1, 'KEVIN SEBASTIAN', 'CABALLERO GODOY', '123423', '1000000', 'ejemplo@gmail.com', 'CALLE 1 C/ CALLE 3', '2025-09-17 22:42:30', 1),
(2, 'SDADS', 'ADASD', '3423423', '', '', '', '2025-09-21 01:31:03', 1),
(3, 'KEVIN SEBASTIAN', 'CABALLERO GODOY', '3455545', '0909090909', 'ejemplo@gmail.com', 'CALLE 1 C/ CALLE 2', '2025-09-21 03:46:17', 1),
(4, 'PRUEBA', 'PP', '3243', '', '', '', '2025-09-21 06:21:17', 1),
(5, 'PRUEBA4', 'DFFSFDSS', '123456789', '', '', '', '2025-09-21 13:43:29', 1),
(6, 'GHDFB', 'SDFSDFGDSDFSDF', '3564', '', 'sdfsdfsdf@gmail.com', '', '2025-09-24 19:49:17', 1),
(7, 'MAGNA ADELINA', 'GODOY OLMEDO', '4305336', '0972617447', 'magnitagodoy2016@gmail.com', 'SAN JUAN', '2025-10-24 00:08:15', 1);

-- --------------------------------------------------------

--
-- Table structure for table `compras`
--

CREATE TABLE `compras` (
  `id` int NOT NULL,
  `id_proveedor` int NOT NULL,
  `numero_compra` varchar(50) DEFAULT NULL COMMENT 'Número de factura o recibo',
  `fecha_compra` date NOT NULL,
  `total_compra` decimal(10,2) NOT NULL DEFAULT '0.00',
  `observaciones` text,
  `estado_compra` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Activa, 0=Anulada',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `compras`
--

INSERT INTO `compras` (`id`, `id_proveedor`, `numero_compra`, `fecha_compra`, `total_compra`, `observaciones`, `estado_compra`, `fecha_registro`) VALUES
(1, 4, NULL, '2025-10-08', 1400000.00, NULL, 1, '2025-10-08 20:07:39'),
(2, 2, NULL, '2025-10-08', 60000.00, NULL, 1, '2025-10-08 20:10:31'),
(3, 6, NULL, '2025-10-08', 100000.00, NULL, 1, '2025-10-08 20:13:02'),
(4, 1, NULL, '2025-10-08', 19000.00, '', 1, '2025-10-08 20:15:32'),
(5, 4, NULL, '2025-10-08', 350000.00, '', 1, '2025-10-08 20:18:04'),
(6, 4, NULL, '2025-10-08', 515000.00, '', 1, '2025-10-08 20:20:15'),
(7, 4, NULL, '2025-10-09', 3250000.00, '', 1, '2025-10-09 19:53:54'),
(9, 3, NULL, '2025-10-12', 3040000.00, '', 1, '2025-10-12 00:44:06'),
(13, 3, NULL, '2025-10-15', 19000.00, '', 1, '2025-10-15 00:24:46'),
(14, 1, NULL, '2025-10-15', 1000.00, '', 1, '2025-10-15 00:42:53'),
(15, 1, '4567', '2025-10-13', 100000.00, '', 1, '2025-10-15 00:43:32'),
(16, 3, NULL, '2025-10-15', 19000.00, '', 1, '2025-10-15 00:45:46'),
(17, 3, NULL, '2025-10-15', 19000.00, '', 1, '2025-10-15 00:48:49'),
(18, 2, NULL, '2025-10-15', 20000.00, '', 1, '2025-10-15 00:49:36'),
(20, 2, NULL, '2025-10-15', 200000.00, '', 1, '2025-10-15 20:07:45');

-- --------------------------------------------------------

--
-- Table structure for table `configuracion_sistema`
--

CREATE TABLE `configuracion_sistema` (
  `id` int NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `tipo` enum('TEXTO','NUMERO','BOOLEAN','JSON') DEFAULT 'TEXTO',
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `configuracion_sistema`
--

INSERT INTO `configuracion_sistema` (`id`, `clave`, `valor`, `descripcion`, `tipo`, `fecha_actualizacion`) VALUES
(1, 'dias_limite_anulacion', '30', 'Días máximos para anular una venta', 'NUMERO', '2025-11-02 17:16:46'),
(2, 'requiere_motivo_anulacion', '1', 'Si se requiere motivo obligatorio para anular', 'BOOLEAN', '2025-11-02 17:16:46'),
(3, 'permitir_anular_factura', '1', 'Permitir anular facturas (genera nota de crédito)', 'BOOLEAN', '2025-11-02 17:16:46'),
(4, 'generar_nota_credito_auto', '0', 'Generar nota de crédito automáticamente', 'BOOLEAN', '2025-11-02 17:16:46');

-- --------------------------------------------------------

--
-- Table structure for table `cuentas_corrientes`
--

CREATE TABLE `cuentas_corrientes` (
  `id` int NOT NULL,
  `id_cliente` int NOT NULL,
  `id_venta` int DEFAULT NULL,
  `tipo_movimiento` enum('DEBITO','CREDITO') NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `saldo_actual` decimal(12,2) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cuotas_venta`
--

CREATE TABLE `cuotas_venta` (
  `id` int NOT NULL,
  `id_venta` int NOT NULL,
  `numero` int NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('PENDIENTE','PAGADA') DEFAULT 'PENDIENTE',
  `fecha_pago` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cuotas_venta`
--

INSERT INTO `cuotas_venta` (`id`, `id_venta`, `numero`, `monto`, `fecha_vencimiento`, `estado`, `fecha_pago`) VALUES
(1, 5, 1, 55000.00, '2026-02-28', 'PENDIENTE', NULL),
(2, 5, 2, 55000.00, '2026-03-30', 'PENDIENTE', NULL),
(3, 5, 3, 55000.00, '2026-04-29', 'PENDIENTE', NULL),
(4, 5, 4, 55000.00, '2026-05-29', 'PENDIENTE', NULL),
(5, 5, 5, 55000.00, '2026-06-28', 'PENDIENTE', NULL),
(6, 7, 1, 62172.92, '2025-10-26', 'PENDIENTE', NULL),
(7, 7, 2, 62172.92, '2025-11-25', 'PENDIENTE', NULL),
(8, 7, 3, 62172.92, '2025-12-25', 'PENDIENTE', NULL),
(9, 7, 4, 62172.92, '2026-01-24', 'PENDIENTE', NULL),
(10, 7, 5, 62172.92, '2026-02-23', 'PENDIENTE', NULL),
(11, 7, 6, 62172.92, '2026-03-25', 'PENDIENTE', NULL),
(12, 7, 7, 62172.92, '2026-04-24', 'PENDIENTE', NULL),
(13, 7, 8, 62172.92, '2026-05-24', 'PENDIENTE', NULL),
(14, 7, 9, 62172.92, '2026-06-23', 'PENDIENTE', NULL),
(15, 7, 10, 62172.92, '2026-07-23', 'PENDIENTE', NULL),
(16, 7, 11, 62172.92, '2026-08-22', 'PENDIENTE', NULL),
(17, 7, 12, 62172.92, '2026-09-21', 'PENDIENTE', NULL),
(18, 8, 1, 183333.33, '2025-10-23', 'PENDIENTE', NULL),
(19, 8, 2, 183333.33, '2025-11-22', 'PENDIENTE', NULL),
(20, 8, 3, 183333.33, '2025-12-22', 'PENDIENTE', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `detalle_compras`
--

CREATE TABLE `detalle_compras` (
  `id` int NOT NULL,
  `id_compra` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL COMMENT 'Precio de compra',
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `detalle_compras`
--

INSERT INTO `detalle_compras` (`id`, `id_compra`, `id_producto`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 3, 56, 25000.00, 1400000.00),
(2, 2, 2, 20, 3000.00, 60000.00),
(3, 3, 2, 20, 5000.00, 100000.00),
(4, 4, 1, 19, 1000.00, 19000.00),
(5, 5, 3, 14, 25000.00, 350000.00),
(6, 6, 3, 20, 25000.00, 500000.00),
(7, 6, 1, 10, 1500.00, 15000.00),
(8, 7, 3, 130, 25000.00, 3250000.00),
(10, 9, 3, 160, 19000.00, 3040000.00),
(13, 18, 3, 1, 20000.00, 20000.00),
(15, 20, 3, 10, 20000.00, 200000.00);

-- --------------------------------------------------------

--
-- Table structure for table `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `id` int NOT NULL,
  `id_venta` int NOT NULL,
  `tipo_item` enum('PRODUCTO','SERVICIO') NOT NULL,
  `id_item` int NOT NULL COMMENT 'ID del producto o servicio',
  `descripcion` varchar(200) NOT NULL COMMENT 'Nombre guardado al momento de la venta',
  `codigo` varchar(100) DEFAULT NULL,
  `cantidad` int NOT NULL DEFAULT '1',
  `precio_unitario` decimal(10,2) NOT NULL COMMENT 'Precio al momento de la venta (productos: automático, servicios: manual)',
  `porcentaje_iva` decimal(5,2) NOT NULL DEFAULT '10.00',
  `monto_iva` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_linea` decimal(12,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `detalle_ventas`
--

INSERT INTO `detalle_ventas` (`id`, `id_venta`, `tipo_item`, `id_item`, `descripcion`, `codigo`, `cantidad`, `precio_unitario`, `porcentaje_iva`, `monto_iva`, `total_linea`, `subtotal`) VALUES
(1, 1, 'PRODUCTO', 1, 'BIROME BIC AZUL', NULL, 6, 3000.00, 10.00, 0.00, 0.00, 18000.00),
(2, 1, 'SERVICIO', 13, 'MANTENIMIENTO DE IMPRESORA', NULL, 1, 30000.00, 10.00, 0.00, 0.00, 30000.00),
(3, 2, 'PRODUCTO', 2, 'RESMA A4', NULL, 2, 50000.00, 10.00, 0.00, 0.00, 100000.00),
(4, 2, 'SERVICIO', 13, 'MANTENIMIENTO DE IMPRESORA', NULL, 1, 30000.00, 10.00, 0.00, 0.00, 30000.00),
(5, 2, 'PRODUCTO', 3, 'RESMA A5+', NULL, 10, 30000.00, 10.00, 0.00, 0.00, 300000.00),
(6, 3, 'PRODUCTO', 2, 'RESMA A4', NULL, 10, 50000.00, 10.00, 0.00, 0.00, 500000.00),
(7, 3, 'PRODUCTO', 3, 'RESMA A5+', NULL, 1, 30000.00, 10.00, 0.00, 0.00, 30000.00),
(8, 3, 'SERVICIO', 14, 'MANTENIMIENTO DE LAPTOP', NULL, 1, 30000.00, 10.00, 0.00, 0.00, 30000.00),
(9, 4, 'PRODUCTO', 5, 'BORRADOR', NULL, 1, 3000.00, 10.00, 0.00, 0.00, 3000.00),
(10, 5, 'PRODUCTO', 6, 'REGLA 30CM', NULL, 50, 5000.00, 10.00, 0.00, 0.00, 250000.00),
(11, 6, 'PRODUCTO', 6, 'REGLA 30CM', NULL, 10, 5000.00, 10.00, 0.00, 0.00, 50000.00),
(12, 6, 'SERVICIO', 16, 'IMPRESIóN A COLOR', NULL, 10, 1000.00, 10.00, 0.00, 0.00, 10000.00),
(13, 7, 'PRODUCTO', 1, 'BIROME BIC AZUL', NULL, 10, 3000.00, 10.00, 0.00, 0.00, 30000.00),
(14, 7, 'PRODUCTO', 6, 'REGLA 30CM', NULL, 10, 5000.00, 10.00, 0.00, 0.00, 50000.00),
(15, 7, 'PRODUCTO', 5, 'BORRADOR', NULL, 9, 3000.00, 10.00, 0.00, 0.00, 27000.00),
(16, 7, 'PRODUCTO', 2, 'RESMA A4', NULL, 10, 50000.00, 10.00, 0.00, 0.00, 500000.00),
(17, 7, 'SERVICIO', 17, 'FOTOCOPIAS', NULL, 165, 250.00, 10.00, 0.00, 0.00, 41250.00),
(18, 7, 'SERVICIO', 16, 'IMPRESIóN A COLOR', NULL, 15, 2000.00, 10.00, 0.00, 0.00, 30000.00),
(19, 8, 'SERVICIO', 15, 'MANTENIMIENTO DE PC DE ESCRITORIO', NULL, 1, 500000.00, 10.00, 0.00, 0.00, 500000.00),
(20, 9, 'PRODUCTO', 6, 'REGLA 30CM', NULL, 10, 5000.00, 10.00, 0.00, 0.00, 50000.00),
(21, 9, 'SERVICIO', 16, 'IMPRESIóN A COLOR', NULL, 1, 1000.00, 10.00, 0.00, 0.00, 1000.00),
(22, 10, 'PRODUCTO', 1, 'BIROME BIC AZUL', NULL, 1, 3000.00, 10.00, 0.00, 0.00, 3000.00),
(23, 11, 'PRODUCTO', 2, 'RESMA A4', NULL, 10, 50000.00, 10.00, 0.00, 0.00, 500000.00),
(24, 11, 'PRODUCTO', 3, 'RESMA A5+', NULL, 21, 30000.00, 10.00, 0.00, 0.00, 630000.00),
(25, 12, 'SERVICIO', 15, 'MANTENIMIENTO DE PC DE ESCRITORIO', NULL, 1, 100000.00, 10.00, 0.00, 0.00, 100000.00),
(26, 12, 'PRODUCTO', 6, 'REGLA 30CM', NULL, 30, 5000.00, 10.00, 0.00, 0.00, 150000.00),
(27, 13, 'PRODUCTO', 6, 'REGLA 30CM', NULL, 10, 5000.00, 10.00, 0.00, 0.00, 50000.00),
(28, 13, 'SERVICIO', 17, 'FOTOCOPIAS', NULL, 100, 250.00, 10.00, 0.00, 0.00, 25000.00),
(29, 13, 'PRODUCTO', 2, 'RESMA A4', NULL, 30, 50000.00, 10.00, 0.00, 0.00, 1500000.00),
(30, 14, 'PRODUCTO', 1, 'BIROME BIC AZUL', NULL, 9, 3000.00, 10.00, 0.00, 0.00, 27000.00),
(31, 15, 'PRODUCTO', 1, 'BIROME BIC AZUL', NULL, 10, 3000.00, 10.00, 0.00, 0.00, 30000.00),
(32, 15, 'SERVICIO', 17, 'FOTOCOPIAS', NULL, 100, 250.00, 10.00, 0.00, 0.00, 25000.00);

-- --------------------------------------------------------

--
-- Table structure for table `historial_anulaciones`
--

CREATE TABLE `historial_anulaciones` (
  `id` int NOT NULL,
  `id_venta` int NOT NULL,
  `tipo_documento` varchar(50) DEFAULT NULL,
  `numero_documento` varchar(100) DEFAULT NULL,
  `monto_anulado` decimal(12,2) NOT NULL,
  `motivo` text NOT NULL,
  `usuario_anula` varchar(100) NOT NULL,
  `fecha_anulacion` datetime NOT NULL,
  `detalles_json` text,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `historial_anulaciones`
--

INSERT INTO `historial_anulaciones` (`id`, `id_venta`, `tipo_documento`, `numero_documento`, `monto_anulado`, `motivo`, `usuario_anula`, `fecha_anulacion`, `detalles_json`, `fecha_registro`) VALUES
(1, 12, 'FACTURA', '001-001-0000837', 250000.00, 'Prueba 1', 'ADMIN', '2025-11-02 13:21:45', '{\"productos_revertidos\":1,\"servicios\":1,\"items_totales\":2,\"cliente\":\"MAGNA ADELINA GODOY OLMEDO\",\"movimiento_caja_id\":\"29\"}', '2025-11-02 17:21:45'),
(2, 14, 'FACTURA', '001-001-0000839', 27000.00, 'wery', 'ADMIN', '2025-11-02 13:23:03', '{\"productos_revertidos\":1,\"servicios\":0,\"items_totales\":1,\"cliente\":\"MAGNA ADELINA GODOY OLMEDO\",\"movimiento_caja_id\":\"30\"}', '2025-11-02 17:23:03'),
(3, 11, 'FACTURA', '001-001-0000836', 1130000.00, 'prueba23', 'ADMIN', '2025-11-02 16:09:29', '{\"productos_revertidos\":2,\"servicios\":0,\"items_totales\":2,\"cliente\":\"MAGNA ADELINA GODOY OLMEDO\",\"movimiento_caja_id\":\"31\"}', '2025-11-02 20:09:29'),
(4, 13, 'FACTURA', '001-001-0000838', 1575000.00, 'wdsfghkjl', 'ADMIN', '2025-11-02 16:13:10', '{\"productos_revertidos\":2,\"servicios\":1,\"items_totales\":3,\"cliente\":\"MAGNA ADELINA GODOY OLMEDO\",\"movimiento_caja_id\":\"32\"}', '2025-11-02 20:13:10'),
(5, 10, 'FACTURA', '001-001-0000835', 3000.00, 'prueba444', 'ADMIN', '2025-11-02 21:21:03', '{\"productos_revertidos\":1,\"servicios\":0,\"items_totales\":1,\"cliente\":\"KEVIN SEBASTIAN CABALLERO GODOY\",\"movimiento_caja_id\":\"33\"}', '2025-11-03 01:21:03'),
(6, 6, 'FACTURA', 'N/A', 66000.00, 'wergh', 'ADMIN', '2025-11-02 21:21:49', '{\"productos_revertidos\":1,\"servicios\":1,\"items_totales\":2,\"cliente\":\"KEVIN SEBASTIAN CABALLERO GODOY\",\"movimiento_caja_id\":\"34\"}', '2025-11-03 01:21:49');

-- --------------------------------------------------------

--
-- Table structure for table `historial_stock`
--

CREATE TABLE `historial_stock` (
  `id` int NOT NULL,
  `id_producto` int NOT NULL,
  `tipo_movimiento` enum('ENTRADA','SALIDA','AJUSTE') NOT NULL,
  `cantidad` int NOT NULL,
  `stock_anterior` int NOT NULL,
  `stock_nuevo` int NOT NULL,
  `motivo` varchar(200) NOT NULL,
  `id_referencia` int DEFAULT NULL COMMENT 'ID de compra/venta/ajuste',
  `fecha_movimiento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `historial_stock`
--

INSERT INTO `historial_stock` (`id`, `id_producto`, `tipo_movimiento`, `cantidad`, `stock_anterior`, `stock_nuevo`, `motivo`, `id_referencia`, `fecha_movimiento`) VALUES
(1, 3, 'ENTRADA', 56, 30, 86, 'COMPRA #1 - Proveedor ID: 4', 1, '2025-10-08 20:07:39'),
(2, 2, 'ENTRADA', 20, 310, 330, 'COMPRA #2 - Proveedor ID: 2', 2, '2025-10-08 20:10:31'),
(3, 2, 'ENTRADA', 20, 330, 350, 'COMPRA #3 - Proveedor ID: 6', 3, '2025-10-08 20:13:02'),
(4, 1, 'ENTRADA', 19, 11, 30, 'COMPRA #4 - Proveedor ID: 1', 4, '2025-10-08 20:15:32'),
(5, 3, 'ENTRADA', 14, 86, 100, 'COMPRA #5 - Proveedor ID: 4', 5, '2025-10-08 20:18:04'),
(6, 3, 'ENTRADA', 20, 100, 120, 'COMPRA #6 - Proveedor ID: 4', 6, '2025-10-08 20:20:15'),
(7, 1, 'ENTRADA', 10, 30, 40, 'COMPRA #6 - Proveedor ID: 4', 6, '2025-10-08 20:20:15'),
(8, 3, 'ENTRADA', 130, 120, 250, 'COMPRA #7 - Proveedor ID: 4', 7, '2025-10-09 19:53:54'),
(9, 3, 'ENTRADA', 10, 250, 260, 'COMPRA #8 - Proveedor ID: 4', 8, '2025-10-12 00:43:13'),
(10, 3, 'ENTRADA', 160, 260, 420, 'COMPRA #9 - Proveedor ID: 3', 9, '2025-10-12 00:44:06'),
(11, 1, 'SALIDA', 6, 40, 34, 'VENTA #1', 1, '2025-10-12 18:39:35'),
(12, 3, 'ENTRADA', 80, 420, 500, 'COMPRA #10 - Proveedor ID: 4', 10, '2025-10-14 01:57:50'),
(13, 2, 'ENTRADA', 50, 350, 400, 'COMPRA #10 - Proveedor ID: 4', 10, '2025-10-14 01:57:50'),
(14, 3, 'SALIDA', 80, 500, 420, 'ELIMINACIÓN COMPRA #10', 10, '2025-10-14 01:58:16'),
(15, 2, 'SALIDA', 50, 400, 350, 'ELIMINACIÓN COMPRA #10', 10, '2025-10-14 01:58:16'),
(16, 3, 'SALIDA', 10, 420, 410, 'ELIMINACIÓN COMPRA #8', 8, '2025-10-14 02:01:38'),
(17, 2, 'SALIDA', 2, 350, 348, 'VENTA #2', 2, '2025-10-14 02:03:30'),
(18, 3, 'SALIDA', 10, 410, 400, 'VENTA #2', 2, '2025-10-14 02:03:30'),
(19, 1, 'ENTRADA', 6, 34, 40, 'ANULACIÓN VENTA #1', 1, '2025-10-14 02:04:04'),
(20, 2, 'ENTRADA', 2, 348, 350, 'ANULACIÓN VENTA #2', 2, '2025-10-14 02:04:27'),
(21, 3, 'ENTRADA', 10, 400, 410, 'ANULACIÓN VENTA #2', 2, '2025-10-14 02:04:27'),
(22, 3, 'ENTRADA', 1, 410, 411, 'COMPRA #18 - Proveedor ID: 2', 18, '2025-10-15 00:49:36'),
(23, 2, 'SALIDA', 10, 350, 340, 'VENTA #3', 3, '2025-10-15 19:56:31'),
(24, 3, 'SALIDA', 1, 411, 410, 'VENTA #3', 3, '2025-10-15 19:56:31'),
(25, 5, 'SALIDA', 1, 100, 99, 'VENTA #4', 4, '2025-10-15 19:57:08'),
(26, 5, 'ENTRADA', 10, 99, 109, 'COMPRA #19 - Proveedor ID: 7', 19, '2025-10-15 19:57:48'),
(27, 3, 'ENTRADA', 10, 410, 420, 'COMPRA #20 - Proveedor ID: 2', 20, '2025-10-15 20:07:45'),
(28, 5, 'AJUSTE', 5, 109, 104, 'AJUSTE COMPRA #19: -5 unidades', 19, '2025-10-19 14:06:35'),
(29, 5, 'AJUSTE', 15, 104, 119, 'AJUSTE COMPRA #19: +15 unidades', 19, '2025-10-19 14:08:15'),
(30, 5, 'AJUSTE', 2, 119, 121, 'AJUSTE COMPRA #19: +2 unidades', 19, '2025-10-19 19:45:24'),
(31, 6, 'AJUSTE', 2, 200, 202, 'AJUSTE COMPRA #19: +2 unidades (nuevo)', 19, '2025-10-19 19:45:24'),
(32, 5, 'AJUSTE', 2, 121, 119, 'AJUSTE COMPRA #19: -2 unidades', 19, '2025-10-19 19:46:12'),
(33, 6, 'AJUSTE', 2, 202, 200, 'AJUSTE COMPRA #19: -2 unidades (eliminado)', 19, '2025-10-19 19:46:12'),
(34, 5, 'SALIDA', 20, 119, 99, 'ELIMINACIÓN COMPRA #19', 19, '2025-10-19 19:46:20'),
(35, 6, 'SALIDA', 50, 200, 150, 'VENTA #5', 5, '2025-10-19 23:15:33'),
(36, 6, 'SALIDA', 10, 150, 140, 'VENTA #6', 6, '2025-10-22 00:39:29'),
(37, 2, 'ENTRADA', 10, 340, 350, 'ANULACIÓN VENTA #3', 3, '2025-10-22 21:11:21'),
(38, 3, 'ENTRADA', 1, 420, 421, 'ANULACIÓN VENTA #3', 3, '2025-10-22 21:11:21'),
(39, 1, 'SALIDA', 10, 40, 30, 'VENTA #7', 7, '2025-10-22 21:15:59'),
(40, 6, 'SALIDA', 10, 140, 130, 'VENTA #7', 7, '2025-10-22 21:15:59'),
(41, 5, 'SALIDA', 9, 99, 90, 'VENTA #7', 7, '2025-10-22 21:15:59'),
(42, 2, 'SALIDA', 10, 350, 340, 'VENTA #7', 7, '2025-10-22 21:15:59'),
(43, 6, 'SALIDA', 10, 130, 120, 'VENTA #9', 9, '2025-10-23 22:55:29'),
(44, 1, 'SALIDA', 1, 30, 29, 'VENTA #10', 10, '2025-10-23 22:56:26'),
(45, 6, 'ENTRADA', 10, 120, 130, 'ANULACIÓN VENTA #9', 9, '2025-10-23 23:05:20'),
(46, 2, 'SALIDA', 10, 340, 330, 'VENTA #11', 11, '2025-10-24 00:09:48'),
(47, 3, 'SALIDA', 21, 421, 400, 'VENTA #11', 11, '2025-10-24 00:09:48'),
(48, 6, 'SALIDA', 30, 130, 100, 'VENTA #12', 12, '2025-10-24 21:42:36'),
(49, 6, 'SALIDA', 10, 100, 90, 'VENTA #13', 13, '2025-10-24 22:17:40'),
(50, 2, 'SALIDA', 30, 330, 300, 'VENTA #13', 13, '2025-10-24 22:17:40'),
(51, 1, 'SALIDA', 9, 29, 20, 'VENTA #14', 14, '2025-10-24 22:43:22'),
(52, 1, 'SALIDA', 10, 20, 10, 'VENTA #15', 15, '2025-10-29 21:35:27'),
(53, 1, 'ENTRADA', 10, 10, 20, 'ANULACIÓN VENTA #15', 15, '2025-10-29 22:07:13'),
(54, 6, 'ENTRADA', 30, 90, 120, 'ANULACIÓN FACTURA #12 - Usuario: ADMIN - Motivo: Prueba 1', 12, '2025-11-02 17:21:45'),
(55, 1, 'ENTRADA', 9, 20, 29, 'ANULACIÓN FACTURA #14 - Usuario: ADMIN - Motivo: wery', 14, '2025-11-02 17:23:03'),
(56, 2, 'ENTRADA', 10, 300, 310, 'ANULACIÓN FACTURA #11 - Usuario: ADMIN - Motivo: prueba23', 11, '2025-11-02 20:09:29'),
(57, 3, 'ENTRADA', 21, 400, 421, 'ANULACIÓN FACTURA #11 - Usuario: ADMIN - Motivo: prueba23', 11, '2025-11-02 20:09:29'),
(58, 6, 'ENTRADA', 10, 120, 130, 'ANULACIÓN FACTURA #13 - Usuario: ADMIN - Motivo: wdsfghkjl', 13, '2025-11-02 20:13:10'),
(59, 2, 'ENTRADA', 30, 310, 340, 'ANULACIÓN FACTURA #13 - Usuario: ADMIN - Motivo: wdsfghkjl', 13, '2025-11-02 20:13:10'),
(60, 1, 'ENTRADA', 1, 29, 30, 'ANULACIÓN FACTURA #10 - Usuario: ADMIN - Motivo: prueba444', 10, '2025-11-03 01:21:03'),
(61, 6, 'ENTRADA', 10, 130, 140, 'ANULACIÓN FACTURA #6 - Usuario: ADMIN - Motivo: wergh', 6, '2025-11-03 01:21:49');

-- --------------------------------------------------------

--
-- Table structure for table `notas_credito`
--

CREATE TABLE `notas_credito` (
  `id` int NOT NULL,
  `id_venta_original` int NOT NULL,
  `numero_nota` varchar(100) DEFAULT NULL,
  `serie` varchar(50) DEFAULT NULL,
  `fecha_emision` datetime NOT NULL,
  `monto_total` decimal(12,2) NOT NULL,
  `motivo` text NOT NULL,
  `estado` enum('PENDIENTE','EMITIDA','ANULADA') DEFAULT 'PENDIENTE',
  `usuario_genera` varchar(100) NOT NULL,
  `observaciones` text,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pagos_compra`
--

CREATE TABLE `pagos_compra` (
  `id` int NOT NULL,
  `id_compra` int NOT NULL,
  `forma_pago` enum('CONTADO','CREDITO') NOT NULL,
  `cuotas` int DEFAULT NULL,
  `monto_cuota` decimal(10,2) DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pagos_compra`
--

INSERT INTO `pagos_compra` (`id`, `id_compra`, `forma_pago`, `cuotas`, `monto_cuota`, `fecha_vencimiento`, `fecha_registro`) VALUES
(2, 18, 'CONTADO', NULL, NULL, NULL, '2025-10-15 00:49:36'),
(4, 20, 'CREDITO', 10, 20000.00, '2025-10-14', '2025-10-15 20:07:45');

-- --------------------------------------------------------

--
-- Table structure for table `pagos_venta`
--

CREATE TABLE `pagos_venta` (
  `id` int NOT NULL,
  `id_venta` int NOT NULL,
  `tipo_pago` varchar(50) NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `referencia` varchar(200) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id` int NOT NULL,
  `nombre_producto` varchar(150) NOT NULL,
  `codigo_producto` varchar(50) DEFAULT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `stock_actual` int DEFAULT '0',
  `stock_minimo` int DEFAULT '5',
  `estado_producto` tinyint(1) DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`id`, `nombre_producto`, `codigo_producto`, `precio_venta`, `stock_actual`, `stock_minimo`, `estado_producto`, `fecha_registro`) VALUES
(1, 'BIROME BIC AZUL', '', 3000.00, 30, 10, 1, '2025-09-24 20:39:01'),
(2, 'RESMA A4', '2343342432', 50000.00, 340, 10, 1, '2025-09-25 21:51:26'),
(3, 'RESMA A5+', '23456787654', 30000.00, 421, 20, 1, '2025-09-30 21:42:56'),
(5, 'BORRADOR', '4353456789', 3000.00, 90, 5, 1, '2025-10-15 00:20:00'),
(6, 'REGLA 30CM', '3456734567', 5000.00, 140, 5, 1, '2025-10-19 15:19:28');

-- --------------------------------------------------------

--
-- Table structure for table `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int NOT NULL,
  `nombre_proveedor` varchar(150) NOT NULL,
  `telefono_proveedor` varchar(20) DEFAULT NULL,
  `direccion_proveedor` varchar(200) DEFAULT NULL,
  `estado_proveedor` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre_proveedor`, `telefono_proveedor`, `direccion_proveedor`, `estado_proveedor`, `fecha_registro`) VALUES
(1, 'ALAMON\'T', '13231', 'K', 1, '2025-09-19 00:27:15'),
(2, 'ALAMO ORIGI', '13231', '', 1, '2025-09-19 00:27:52'),
(3, 'ALAMO2', '34223', NULL, 1, '2025-09-21 01:30:47'),
(4, 'OFIMARKET', '1323133', '', 1, '2025-09-21 05:48:58'),
(6, 'SANTEI', '', 'ECARNAYORK', 1, '2025-09-30 00:21:07'),
(7, 'OOOOOO', '032032023', 'DSDSFLDSLKÑSDFKLÑ', 1, '2025-10-15 00:21:19');

-- --------------------------------------------------------

--
-- Table structure for table `proveedor_producto`
--

CREATE TABLE `proveedor_producto` (
  `id` int NOT NULL,
  `id_proveedor` int NOT NULL,
  `id_producto` int NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `proveedor_producto`
--

INSERT INTO `proveedor_producto` (`id`, `id_proveedor`, `id_producto`, `precio_compra`, `fecha_registro`) VALUES
(16, 2, 3, 20000.00, '2025-09-30 21:42:56'),
(17, 3, 3, 19000.00, '2025-09-30 21:42:56'),
(21, 1, 1, 1000.00, '2025-09-30 21:44:05'),
(22, 2, 2, 3000.00, '2025-09-30 21:44:34'),
(23, 6, 2, 5000.00, '2025-09-30 21:44:34'),
(26, 4, 3, 25000.00, '2025-10-07 23:38:48'),
(27, 4, 1, 1500.00, '2025-10-07 23:38:48'),
(28, 4, 2, 40000.00, '2025-10-07 23:38:48'),
(29, 2, 5, 0.00, '2025-10-15 00:20:00'),
(30, 3, 5, 0.00, '2025-10-15 00:20:00'),
(31, 1, 5, 0.00, '2025-10-15 00:20:00'),
(36, 7, 5, 2000.00, '2025-10-19 14:25:01'),
(37, 7, 2, 20000.00, '2025-10-19 14:25:01'),
(38, 7, 3, 20000.00, '2025-10-19 14:25:01'),
(39, 7, 1, 2000.00, '2025-10-19 14:25:01'),
(40, 2, 6, 3000.00, '2025-10-19 15:19:28'),
(41, 3, 6, 3000.00, '2025-10-19 15:19:28'),
(42, 1, 6, 2000.00, '2025-10-19 15:19:28'),
(43, 7, 6, 3000.00, '2025-10-19 15:19:28');

-- --------------------------------------------------------

--
-- Table structure for table `series_comprobantes`
--

CREATE TABLE `series_comprobantes` (
  `id` int NOT NULL,
  `tipo_comprobante` varchar(50) NOT NULL,
  `serie` varchar(50) NOT NULL,
  `ultimo_numero` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servicios`
--

CREATE TABLE `servicios` (
  `id` int NOT NULL,
  `nombre_servicio` varchar(200) NOT NULL,
  `categoria_servicio` varchar(200) NOT NULL,
  `precio_sugerido` decimal(10,2) DEFAULT '0.00',
  `estado_servicio` tinyint(1) DEFAULT '1',
  `fecha_ingreso` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `servicios`
--

INSERT INTO `servicios` (`id`, `nombre_servicio`, `categoria_servicio`, `precio_sugerido`, `estado_servicio`, `fecha_ingreso`) VALUES
(13, 'MANTENIMIENTO DE IMPRESORA', 'MATEMATICA', 0.00, 1, '2025-10-08 18:56:54'),
(14, 'MANTENIMIENTO DE LAPTOP', 'MATEMATICA', 0.00, 1, '2025-10-08 20:17:21'),
(15, 'MANTENIMIENTO DE PC DE ESCRITORIO', 'MANTENIMIENTO', 0.00, 1, '2025-10-19 18:52:54'),
(16, 'IMPRESIóN A COLOR', 'IMPRESIONES', 1000.00, 1, '2025-10-20 22:17:56'),
(17, 'FOTOCOPIAS', 'IMPRESION', 250.00, 1, '2025-10-20 22:23:14');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('ADMINISTRADOR','CAJERO','VENDEDOR') DEFAULT 'VENDEDOR',
  `estado` tinyint(1) DEFAULT '1',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `usuario`, `password`, `rol`, `estado`, `fecha_registro`) VALUES
(1, 'Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMINISTRADOR', 1, '2025-11-02 17:16:46');

-- --------------------------------------------------------

--
-- Table structure for table `ventas`
--

CREATE TABLE `ventas` (
  `id` int NOT NULL,
  `id_cliente` int DEFAULT NULL COMMENT 'NULL = Venta sin cliente registrado',
  `es_factura_legal` tinyint(1) NOT NULL DEFAULT '0',
  `tipo_comprobante` varchar(50) DEFAULT NULL,
  `serie` varchar(50) DEFAULT NULL,
  `numero_venta` varchar(50) DEFAULT NULL COMMENT 'Número de factura/ticket',
  `condicion_venta` enum('CONTADO','CREDITO') NOT NULL DEFAULT 'CONTADO',
  `forma_pago` varchar(50) DEFAULT 'CONTADO',
  `cuotas` int DEFAULT '1',
  `fecha_vencimiento_primera` date DEFAULT NULL,
  `fecha_venta` datetime NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `iva_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `usuario_id` int DEFAULT NULL,
  `total_venta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `observaciones` text,
  `estado_venta` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Activa, 0=Anulada',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_anulacion` datetime DEFAULT NULL,
  `motivo_anulacion` text,
  `usuario_anula` varchar(100) DEFAULT NULL,
  `usuario_registro` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ventas`
--

INSERT INTO `ventas` (`id`, `id_cliente`, `es_factura_legal`, `tipo_comprobante`, `serie`, `numero_venta`, `condicion_venta`, `forma_pago`, `cuotas`, `fecha_vencimiento_primera`, `fecha_venta`, `subtotal`, `descuento`, `iva_total`, `usuario_id`, `total_venta`, `observaciones`, `estado_venta`, `fecha_registro`, `fecha_anulacion`, `motivo_anulacion`, `usuario_anula`, `usuario_registro`) VALUES
(1, 3, 0, NULL, NULL, '234567234', 'CONTADO', 'CONTADO', 1, NULL, '2025-10-12 00:00:00', 48000.00, 0.00, 0.00, NULL, 48000.00, '', 0, '2025-10-12 18:39:35', NULL, NULL, NULL, NULL),
(2, 3, 0, NULL, NULL, '324456778965', 'CONTADO', 'CONTADO', 1, NULL, '2025-10-13 00:00:00', 430000.00, 0.00, 0.00, NULL, 430000.00, '', 0, '2025-10-14 02:03:30', NULL, NULL, NULL, NULL),
(3, 5, 0, NULL, NULL, '4356879', 'CONTADO', 'CONTADO', 1, NULL, '2025-10-13 00:00:00', 560000.00, 0.00, 0.00, NULL, 560000.00, '', 0, '2025-10-15 19:56:31', NULL, NULL, NULL, NULL),
(4, 1, 0, NULL, NULL, NULL, 'CONTADO', 'CONTADO', 1, NULL, '2025-10-16 00:00:00', 3000.00, 0.00, 0.00, NULL, 3000.00, '', 1, '2025-10-15 19:57:08', NULL, NULL, NULL, NULL),
(5, 1, 0, 'TICKET', NULL, '0000001', 'CREDITO', 'CONTADO', 5, '2026-02-28', '2025-10-19 00:00:00', 250000.00, 0.00, 0.00, NULL, 275000.00, '', 1, '2025-10-19 23:15:33', NULL, NULL, NULL, NULL),
(6, 3, 1, 'FACTURA', NULL, NULL, 'CONTADO', 'CONTADO', NULL, NULL, '2025-10-22 00:00:00', 60000.00, 0.00, 0.00, NULL, 66000.00, '', 0, '2025-10-22 00:39:29', '2025-11-02 21:21:49', 'wergh', 'ADMIN', NULL),
(7, 1, 0, 'TICKET', NULL, '0000007', 'CREDITO', 'TARJETA', 12, '2025-10-26', '2025-10-22 00:00:00', 678250.00, 0.00, 0.00, NULL, 746075.00, '', 1, '2025-10-22 21:15:59', NULL, NULL, NULL, NULL),
(8, 1, 1, 'FACTURA', NULL, NULL, 'CREDITO', 'TRANSFERENCIA', 3, '2025-10-23', '2025-10-22 00:00:00', 500000.00, 0.00, 0.00, NULL, 550000.00, '', 1, '2025-10-22 21:17:48', NULL, NULL, NULL, NULL),
(9, 3, 0, 'TICKET', NULL, '0000009', 'CONTADO', 'CONTADO', 1, NULL, '2025-10-23 00:00:00', 51000.00, 0.00, 0.00, NULL, 51000.00, '', 0, '2025-10-23 22:55:29', NULL, NULL, NULL, NULL),
(10, 3, 0, 'FACTURA', NULL, '001-001-0000835', 'CONTADO', 'CONTADO', 1, NULL, '2025-10-23 00:00:00', 3000.00, 0.00, 0.00, NULL, 3000.00, '', 0, '2025-10-23 22:56:26', '2025-11-02 21:21:03', 'prueba444', 'ADMIN', NULL),
(11, 7, 0, 'FACTURA', NULL, '001-001-0000836', 'CONTADO', 'CONTADO', 1, NULL, '2025-10-24 00:08:00', 1130000.00, 0.00, 0.00, NULL, 1130000.00, '', 0, '2025-10-24 00:09:48', '2025-11-02 16:09:29', 'prueba23', 'ADMIN', NULL),
(12, 7, 0, 'FACTURA', NULL, '001-001-0000837', 'CONTADO', 'CONTADO', 1, NULL, '2025-10-24 21:41:00', 250000.00, 0.00, 0.00, NULL, 250000.00, '', 0, '2025-10-24 21:42:36', '2025-11-02 13:21:45', 'Prueba 1', 'ADMIN', NULL),
(13, 7, 0, 'FACTURA', NULL, '001-001-0000838', 'CONTADO', 'CONTADO', 1, NULL, '2025-10-24 19:15:00', 1575000.00, 0.00, 0.00, NULL, 1575000.00, '', 0, '2025-10-24 22:17:40', '2025-11-02 16:13:10', 'wdsfghkjl', 'ADMIN', NULL),
(14, 7, 0, 'FACTURA', NULL, '001-001-0000839', 'CONTADO', 'CONTADO', 1, NULL, '2025-10-24 19:42:00', 27000.00, 0.00, 0.00, NULL, 27000.00, '', 0, '2025-10-24 22:43:22', '2025-11-02 13:23:03', 'wery', 'ADMIN', NULL),
(15, 7, 0, 'FACTURA', NULL, '001-001-0000840', 'CONTADO', 'CONTADO', 1, NULL, '2025-10-29 18:34:00', 55000.00, 0.00, 0.00, NULL, 55000.00, '', 0, '2025-10-29 21:35:27', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_productos_bajo_stock`
-- (See below for the actual view)
--
CREATE TABLE `v_productos_bajo_stock` (
`id` int
,`nombre_producto` varchar(150)
,`codigo_producto` varchar(50)
,`stock_actual` int
,`stock_minimo` int
,`faltante` bigint
,`nivel_alerta` varchar(8)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_productos_mas_vendidos`
-- (See below for the actual view)
--
CREATE TABLE `v_productos_mas_vendidos` (
`id_producto` int
,`nombre_producto` varchar(200)
,`total_vendido` decimal(32,0)
,`num_ventas` bigint
,`ingresos_generados` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_resumen_caja`
-- (See below for the actual view)
--
CREATE TABLE `v_resumen_caja` (
`fecha` date
,`total_ingresos` decimal(32,2)
,`total_egresos` decimal(32,2)
,`saldo_dia` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_servicios_mas_solicitados`
-- (See below for the actual view)
--
CREATE TABLE `v_servicios_mas_solicitados` (
`id_servicio` int
,`nombre_servicio` varchar(200)
,`veces_solicitado` bigint
,`cantidad_total` decimal(32,0)
,`precio_promedio` decimal(14,6)
,`ingresos_generados` decimal(32,2)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `caja`
--
ALTER TABLE `caja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tipo` (`tipo_movimiento`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_fecha` (`fecha_movimiento`);

--
-- Indexes for table `cierres_caja`
--
ALTER TABLE `cierres_caja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_apertura` (`fecha_apertura`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ci_ruc_cliente_u` (`ci_ruc_cliente`);

--
-- Indexes for table `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proveedor` (`id_proveedor`),
  ADD KEY `idx_fecha` (`fecha_compra`);

--
-- Indexes for table `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indexes for table `cuentas_corrientes`
--
ALTER TABLE `cuentas_corrientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cc_cliente` (`id_cliente`),
  ADD KEY `idx_cc_venta` (`id_venta`);

--
-- Indexes for table `cuotas_venta`
--
ALTER TABLE `cuotas_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cuotas_venta` (`id_venta`);

--
-- Indexes for table `detalle_compras`
--
ALTER TABLE `detalle_compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_compra` (`id_compra`),
  ADD KEY `idx_producto` (`id_producto`);

--
-- Indexes for table `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_venta` (`id_venta`),
  ADD KEY `idx_tipo_item` (`tipo_item`,`id_item`);

--
-- Indexes for table `historial_anulaciones`
--
ALTER TABLE `historial_anulaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_venta` (`id_venta`),
  ADD KEY `idx_fecha` (`fecha_anulacion`),
  ADD KEY `idx_usuario` (`usuario_anula`);

--
-- Indexes for table `historial_stock`
--
ALTER TABLE `historial_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_producto` (`id_producto`),
  ADD KEY `idx_tipo` (`tipo_movimiento`),
  ADD KEY `idx_fecha` (`fecha_movimiento`);

--
-- Indexes for table `notas_credito`
--
ALTER TABLE `notas_credito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_venta` (`id_venta_original`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indexes for table `pagos_compra`
--
ALTER TABLE `pagos_compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_compra` (`id_compra`);

--
-- Indexes for table `pagos_venta`
--
ALTER TABLE `pagos_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pv_venta` (`id_venta`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_producto` (`codigo_producto`);

--
-- Indexes for table `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `proveedor_producto`
--
ALTER TABLE `proveedor_producto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_proveedor_producto` (`id_proveedor`,`id_producto`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indexes for table `series_comprobantes`
--
ALTER TABLE `series_comprobantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_tipo_serie` (`tipo_comprobante`,`serie`);

--
-- Indexes for table `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indexes for table `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ventas_tipo_serie_num` (`tipo_comprobante`,`serie`,`numero_venta`),
  ADD KEY `idx_cliente` (`id_cliente`),
  ADD KEY `idx_fecha` (`fecha_venta`),
  ADD KEY `idx_estado` (`estado_venta`),
  ADD KEY `idx_fecha_anulacion` (`fecha_anulacion`),
  ADD KEY `idx_usuario_anula` (`usuario_anula`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `caja`
--
ALTER TABLE `caja`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `cierres_caja`
--
ALTER TABLE `cierres_caja`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cuentas_corrientes`
--
ALTER TABLE `cuentas_corrientes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cuotas_venta`
--
ALTER TABLE `cuotas_venta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `detalle_compras`
--
ALTER TABLE `detalle_compras`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `historial_anulaciones`
--
ALTER TABLE `historial_anulaciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `historial_stock`
--
ALTER TABLE `historial_stock`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `notas_credito`
--
ALTER TABLE `notas_credito`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pagos_compra`
--
ALTER TABLE `pagos_compra`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pagos_venta`
--
ALTER TABLE `pagos_venta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `proveedor_producto`
--
ALTER TABLE `proveedor_producto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `series_comprobantes`
--
ALTER TABLE `series_comprobantes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

-- --------------------------------------------------------

--
-- Structure for view `v_productos_bajo_stock`
--
DROP TABLE IF EXISTS `v_productos_bajo_stock`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_productos_bajo_stock`  AS SELECT `p`.`id` AS `id`, `p`.`nombre_producto` AS `nombre_producto`, `p`.`codigo_producto` AS `codigo_producto`, `p`.`stock_actual` AS `stock_actual`, `p`.`stock_minimo` AS `stock_minimo`, (`p`.`stock_minimo` - `p`.`stock_actual`) AS `faltante`, (case when (`p`.`stock_actual` = 0) then 'CRÍTICO' when (`p`.`stock_actual` <= (`p`.`stock_minimo` * 0.5)) then 'MUY BAJO' when (`p`.`stock_actual` <= `p`.`stock_minimo`) then 'BAJO' else 'NORMAL' end) AS `nivel_alerta` FROM `productos` AS `p` WHERE ((`p`.`stock_actual` <= `p`.`stock_minimo`) AND (`p`.`estado_producto` = 1)) ORDER BY (case when (`p`.`stock_actual` = 0) then 1 when (`p`.`stock_actual` <= (`p`.`stock_minimo` * 0.5)) then 2 when (`p`.`stock_actual` <= `p`.`stock_minimo`) then 3 else 4 end) ASC, `p`.`stock_actual` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `v_productos_mas_vendidos`
--
DROP TABLE IF EXISTS `v_productos_mas_vendidos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_productos_mas_vendidos`  AS SELECT `dv`.`id_item` AS `id_producto`, `dv`.`descripcion` AS `nombre_producto`, sum(`dv`.`cantidad`) AS `total_vendido`, count(distinct `dv`.`id_venta`) AS `num_ventas`, sum(`dv`.`subtotal`) AS `ingresos_generados` FROM (`detalle_ventas` `dv` join `ventas` `v` on((`dv`.`id_venta` = `v`.`id`))) WHERE ((`dv`.`tipo_item` = 'PRODUCTO') AND (`v`.`estado_venta` = 1)) GROUP BY `dv`.`id_item`, `dv`.`descripcion` ORDER BY `total_vendido` DESC LIMIT 0, 10 ;

-- --------------------------------------------------------

--
-- Structure for view `v_resumen_caja`
--
DROP TABLE IF EXISTS `v_resumen_caja`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_resumen_caja`  AS SELECT cast(`caja`.`fecha_movimiento` as date) AS `fecha`, sum((case when (`caja`.`tipo_movimiento` = 'INGRESO') then `caja`.`monto` else 0 end)) AS `total_ingresos`, sum((case when (`caja`.`tipo_movimiento` = 'EGRESO') then `caja`.`monto` else 0 end)) AS `total_egresos`, sum((case when (`caja`.`tipo_movimiento` = 'INGRESO') then `caja`.`monto` else -(`caja`.`monto`) end)) AS `saldo_dia` FROM `caja` GROUP BY cast(`caja`.`fecha_movimiento` as date) ORDER BY `fecha` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_servicios_mas_solicitados`
--
DROP TABLE IF EXISTS `v_servicios_mas_solicitados`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_servicios_mas_solicitados`  AS SELECT `dv`.`id_item` AS `id_servicio`, `dv`.`descripcion` AS `nombre_servicio`, count(0) AS `veces_solicitado`, sum(`dv`.`cantidad`) AS `cantidad_total`, avg(`dv`.`precio_unitario`) AS `precio_promedio`, sum(`dv`.`subtotal`) AS `ingresos_generados` FROM (`detalle_ventas` `dv` join `ventas` `v` on((`dv`.`id_venta` = `v`.`id`))) WHERE ((`dv`.`tipo_item` = 'SERVICIO') AND (`v`.`estado_venta` = 1)) GROUP BY `dv`.`id_item`, `dv`.`descripcion` ORDER BY `veces_solicitado` DESC LIMIT 0, 10 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cuentas_corrientes`
--
ALTER TABLE `cuentas_corrientes`
  ADD CONSTRAINT `fk_cc_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cuotas_venta`
--
ALTER TABLE `cuotas_venta`
  ADD CONSTRAINT `fk_cuotas_venta_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detalle_compras`
--
ALTER TABLE `detalle_compras`
  ADD CONSTRAINT `fk_detallecompras_compra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `fk_detalleventas_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `historial_anulaciones`
--
ALTER TABLE `historial_anulaciones`
  ADD CONSTRAINT `historial_anulaciones_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `historial_stock`
--
ALTER TABLE `historial_stock`
  ADD CONSTRAINT `fk_historial_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notas_credito`
--
ALTER TABLE `notas_credito`
  ADD CONSTRAINT `notas_credito_ibfk_1` FOREIGN KEY (`id_venta_original`) REFERENCES `ventas` (`id`);

--
-- Constraints for table `pagos_venta`
--
ALTER TABLE `pagos_venta`
  ADD CONSTRAINT `fk_pagosventa_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `proveedor_producto`
--
ALTER TABLE `proveedor_producto`
  ADD CONSTRAINT `fk_pp_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pp_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
