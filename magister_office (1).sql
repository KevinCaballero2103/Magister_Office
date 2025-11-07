-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20251018.6d3d61fe5f
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 07, 2025 at 01:17 AM
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
  `movimiento_relacionado` int DEFAULT NULL,
  `usuario_modificacion` varchar(100) DEFAULT NULL,
  `fecha_modificacion` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `usuario_registro` varchar(100) DEFAULT NULL,
  `estado_cliente` tinyint NOT NULL DEFAULT '1',
  `usuario_modificacion` varchar(100) DEFAULT NULL,
  `fecha_modificacion` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_registro` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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

-- --------------------------------------------------------

--
-- Table structure for table `log_actividades`
--

CREATE TABLE `log_actividades` (
  `id` int NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `accion` varchar(100) NOT NULL COMMENT 'Ej: CREAR, EDITAR, ELIMINAR, LOGIN, LOGOUT',
  `modulo` varchar(50) NOT NULL COMMENT 'Ej: CLIENTES, PRODUCTOS, VENTAS, CAJA',
  `descripcion` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `datos_anteriores` text COMMENT 'JSON con datos antes del cambio',
  `datos_nuevos` text COMMENT 'JSON con datos después del cambio',
  `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_registro` varchar(100) DEFAULT NULL,
  `usuario_modificacion` varchar(100) DEFAULT NULL,
  `fecha_modificacion` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_registro` varchar(100) DEFAULT NULL,
  `usuario_modificacion` varchar(100) DEFAULT NULL,
  `fecha_modificacion` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `fecha_ingreso` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_registro` varchar(100) DEFAULT NULL,
  `usuario_modificacion` varchar(100) DEFAULT NULL,
  `fecha_modificacion` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `intentos_fallidos` int DEFAULT '0',
  `bloqueado_hasta` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `usuario`, `password`, `rol`, `estado`, `fecha_registro`, `ultimo_acceso`, `intentos_fallidos`, `bloqueado_hasta`) VALUES
(1, 'Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMINISTRADOR', 1, '2025-11-02 17:16:46', '2025-11-05 16:18:50', 0, NULL),
(2, 'KEVIN CABALLERO', 'kevin', '$2y$10$PqxbCX.36728TxpciRKMI.9w4w7lvpXjZYZVjNeMrmSE3FbwVlnBK', 'ADMINISTRADOR', 1, '2025-11-05 01:14:18', '2025-11-05 20:19:59', 0, NULL),
(3, 'KEVIN CABALLERO 2', 'kevinsec', '$2y$10$2Ypzb54lDJUG5l71AVn8lezWL5lvV2JmmzTn3MYataua8GdOBrsiW', 'ADMINISTRADOR', 1, '2025-11-05 01:14:35', '2025-11-05 16:08:33', 0, NULL),
(4, 'KEVIN CABALLERO 3', 'kevin3', '$2y$10$KhhKFJ63yjP6bZLvc9eqJekujIDPwyVVWsEZ61qEoqw1Oeg1ayogS', 'ADMINISTRADOR', 1, '2025-11-05 01:14:47', NULL, 0, NULL),
(5, 'KEVIN CABALLERO 4', 'admin2', '$2y$10$fXnEE5KuPaxev4ATeBDkDuPxC6a0Yc1cNr9RmLyT/04c4LBzNPWI2', 'VENDEDOR', 1, '2025-11-05 01:15:29', NULL, 0, NULL),
(6, 'VENDEDOR', 'vendedor', '$2y$10$ZQSxSJ7z0M4vctkrWCeV0u1IFVPo2CtEVcxqBXW5NPsVIIfXgIg5S', 'VENDEDOR', 1, '2025-11-05 01:19:52', '2025-11-05 01:20:03', 0, NULL);

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
-- Indexes for table `log_actividades`
--
ALTER TABLE `log_actividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario`),
  ADD KEY `idx_modulo` (`modulo`),
  ADD KEY `idx_fecha` (`fecha_hora`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cierres_caja`
--
ALTER TABLE `cierres_caja`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detalle_compras`
--
ALTER TABLE `detalle_compras`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historial_anulaciones`
--
ALTER TABLE `historial_anulaciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historial_stock`
--
ALTER TABLE `historial_stock`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log_actividades`
--
ALTER TABLE `log_actividades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notas_credito`
--
ALTER TABLE `notas_credito`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pagos_compra`
--
ALTER TABLE `pagos_compra`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pagos_venta`
--
ALTER TABLE `pagos_venta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proveedor_producto`
--
ALTER TABLE `proveedor_producto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `series_comprobantes`
--
ALTER TABLE `series_comprobantes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

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
