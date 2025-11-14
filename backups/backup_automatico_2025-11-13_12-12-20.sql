mysqldump: [Warning] Using a password on the command line interface can be insecure.

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `caja` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `fecha_modificacion` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tipo` (`tipo_movimiento`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_fecha` (`fecha_movimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `caja` WRITE;
/*!40000 ALTER TABLE `caja` DISABLE KEYS */;
/*!40000 ALTER TABLE `caja` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cierres_caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cierres_caja` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha_apertura` (`fecha_apertura`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cierres_caja` WRITE;
/*!40000 ALTER TABLE `cierres_caja` DISABLE KEYS */;
INSERT INTO `cierres_caja` VALUES (1,'2025-11-12 05:52:00','2025-11-12 05:53:00',0.00,0.00,0.00,0.00,0.00,0.00,0.00,NULL,NULL,'CERRADA','Administrador','Administrador','2025-11-12 11:52:32'),(2,'2025-11-12 06:46:00',NULL,0.00,NULL,0.00,0.00,NULL,NULL,NULL,NULL,NULL,'ABIERTA','Administrador',NULL,'2025-11-12 12:46:38');
/*!40000 ALTER TABLE `cierres_caja` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `fecha_modificacion` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ci_ruc_cliente_u` (`ci_ruc_cliente`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'KEVIN SEBASTIáN','CABALLERO GODOY','5446195','0975607217','a@yrt.al','A','2025-11-12 12:46:28',NULL,1,NULL,NULL);
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_proveedor` int NOT NULL,
  `numero_compra` varchar(50) DEFAULT NULL COMMENT 'Número de factura o recibo',
  `fecha_compra` date NOT NULL,
  `total_compra` decimal(10,2) NOT NULL DEFAULT '0.00',
  `observaciones` text,
  `estado_compra` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Activa, 0=Anulada',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_registro` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_proveedor` (`id_proveedor`),
  KEY `idx_fecha` (`fecha_compra`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
/*!40000 ALTER TABLE `compras` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `configuracion_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion_sistema` (
  `id` int NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `tipo` enum('TEXTO','NUMERO','BOOLEAN','JSON') DEFAULT 'TEXTO',
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `configuracion_sistema` WRITE;
/*!40000 ALTER TABLE `configuracion_sistema` DISABLE KEYS */;
INSERT INTO `configuracion_sistema` VALUES (1,'dias_limite_anulacion','30','Días máximos para anular una venta','NUMERO','2025-11-02 17:16:46'),(2,'requiere_motivo_anulacion','1','Si se requiere motivo obligatorio para anular','BOOLEAN','2025-11-02 17:16:46'),(3,'permitir_anular_factura','1','Permitir anular facturas (genera nota de crédito)','BOOLEAN','2025-11-02 17:16:46'),(4,'generar_nota_credito_auto','0','Generar nota de crédito automáticamente','BOOLEAN','2025-11-02 17:16:46');
/*!40000 ALTER TABLE `configuracion_sistema` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cuentas_corrientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuentas_corrientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `id_venta` int DEFAULT NULL,
  `tipo_movimiento` enum('DEBITO','CREDITO') NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `saldo_actual` decimal(12,2) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cc_cliente` (`id_cliente`),
  KEY `idx_cc_venta` (`id_venta`),
  CONSTRAINT `fk_cc_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cuentas_corrientes` WRITE;
/*!40000 ALTER TABLE `cuentas_corrientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuentas_corrientes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cuotas_venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuotas_venta` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_venta` int NOT NULL,
  `numero` int NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('PENDIENTE','PAGADA') DEFAULT 'PENDIENTE',
  `fecha_pago` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cuotas_venta` (`id_venta`),
  CONSTRAINT `fk_cuotas_venta_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cuotas_venta` WRITE;
/*!40000 ALTER TABLE `cuotas_venta` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuotas_venta` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `detalle_compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_compras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_compra` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL COMMENT 'Precio de compra',
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_compra` (`id_compra`),
  KEY `idx_producto` (`id_producto`),
  CONSTRAINT `fk_detallecompras_compra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `detalle_compras` WRITE;
/*!40000 ALTER TABLE `detalle_compras` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_compras` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `detalle_ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_ventas` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_venta` (`id_venta`),
  KEY `idx_tipo_item` (`tipo_item`,`id_item`),
  CONSTRAINT `fk_detalleventas_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `detalle_ventas` WRITE;
/*!40000 ALTER TABLE `detalle_ventas` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_ventas` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `historial_anulaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial_anulaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_venta` int NOT NULL,
  `tipo_documento` varchar(50) DEFAULT NULL,
  `numero_documento` varchar(100) DEFAULT NULL,
  `monto_anulado` decimal(12,2) NOT NULL,
  `motivo` text NOT NULL,
  `usuario_anula` varchar(100) NOT NULL,
  `fecha_anulacion` datetime NOT NULL,
  `detalles_json` text,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_venta` (`id_venta`),
  KEY `idx_fecha` (`fecha_anulacion`),
  KEY `idx_usuario` (`usuario_anula`),
  CONSTRAINT `historial_anulaciones_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `historial_anulaciones` WRITE;
/*!40000 ALTER TABLE `historial_anulaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `historial_anulaciones` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `historial_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial_stock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `tipo_movimiento` enum('ENTRADA','SALIDA','AJUSTE') NOT NULL,
  `cantidad` int NOT NULL,
  `stock_anterior` int NOT NULL,
  `stock_nuevo` int NOT NULL,
  `motivo` varchar(200) NOT NULL,
  `id_referencia` int DEFAULT NULL COMMENT 'ID de compra/venta/ajuste',
  `fecha_movimiento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_producto` (`id_producto`),
  KEY `idx_tipo` (`tipo_movimiento`),
  KEY `idx_fecha` (`fecha_movimiento`),
  CONSTRAINT `fk_historial_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `historial_stock` WRITE;
/*!40000 ALTER TABLE `historial_stock` DISABLE KEYS */;
/*!40000 ALTER TABLE `historial_stock` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `log_actividades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_actividades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(100) NOT NULL,
  `accion` varchar(100) NOT NULL COMMENT 'Ej: CREAR, EDITAR, ELIMINAR, LOGIN, LOGOUT',
  `modulo` varchar(50) NOT NULL COMMENT 'Ej: CLIENTES, PRODUCTOS, VENTAS, CAJA',
  `descripcion` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `datos_anteriores` text COMMENT 'JSON con datos antes del cambio',
  `datos_nuevos` text COMMENT 'JSON con datos después del cambio',
  `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario`),
  KEY `idx_modulo` (`modulo`),
  KEY `idx_fecha` (`fecha_hora`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `log_actividades` WRITE;
/*!40000 ALTER TABLE `log_actividades` DISABLE KEYS */;
INSERT INTO `log_actividades` VALUES (1,'Administrador','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-12 11:52:07'),(2,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-12 11:52:07'),(3,'Administrador','ACCESO','CAJA','Acceso a apertura de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-12 11:52:28'),(4,'Administrador','APERTURA_CAJA','CAJA','Apertura de caja #1 - Saldo inicial: ₲ 0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"id_cierre\":\"1\",\"saldo_inicial\":0,\"fecha_apertura\":\"2025-11-12 05:52:00\",\"observaciones\":null}','2025-11-12 11:52:32'),(5,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-12 11:52:33'),(6,'Administrador','ACCESO','USUARIOS','Acceso al listado de usuarios','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-12 11:52:51'),(7,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-12 11:52:57'),(8,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-12 11:53:08'),(9,'Administrador','ACCESO','CAJA','Acceso a cierre de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-12 11:53:12'),(10,'Administrador','CIERRE_CAJA','CAJA','Cierre de caja #1 - Diferencia: ₲ 0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','{\"saldo_inicial\":0,\"estado_anterior\":\"ABIERTA\"}','{\"id_cierre\":1,\"saldo_sistema\":0,\"saldo_fisico\":0,\"diferencia\":0,\"total_ingresos\":0,\"total_egresos\":0,\"estado_nuevo\":\"CERRADA\"}','2025-11-12 11:53:18'),(11,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-12 12:05:23'),(12,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-12 12:05:30'),(13,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-12 12:43:17'),(14,'Administrador','ACCESO','CAJA','Acceso a apertura de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-12 12:46:35'),(15,'Administrador','APERTURA_CAJA','CAJA','Apertura de caja #2 - Saldo inicial: ₲ 0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"id_cierre\":\"2\",\"saldo_inicial\":0,\"fecha_apertura\":\"2025-11-12 06:46:00\",\"observaciones\":null}','2025-11-12 12:46:38'),(16,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-12 12:46:39');
/*!40000 ALTER TABLE `log_actividades` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `notas_credito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notas_credito` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_venta_original` int NOT NULL,
  `numero_nota` varchar(100) DEFAULT NULL,
  `serie` varchar(50) DEFAULT NULL,
  `fecha_emision` datetime NOT NULL,
  `monto_total` decimal(12,2) NOT NULL,
  `motivo` text NOT NULL,
  `estado` enum('PENDIENTE','EMITIDA','ANULADA') DEFAULT 'PENDIENTE',
  `usuario_genera` varchar(100) NOT NULL,
  `observaciones` text,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_venta` (`id_venta_original`),
  KEY `idx_estado` (`estado`),
  CONSTRAINT `notas_credito_ibfk_1` FOREIGN KEY (`id_venta_original`) REFERENCES `ventas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `notas_credito` WRITE;
/*!40000 ALTER TABLE `notas_credito` DISABLE KEYS */;
/*!40000 ALTER TABLE `notas_credito` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `pagos_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_compra` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_compra` int NOT NULL,
  `forma_pago` enum('CONTADO','CREDITO') NOT NULL,
  `cuotas` int DEFAULT NULL,
  `monto_cuota` decimal(10,2) DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_compra` (`id_compra`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pagos_compra` WRITE;
/*!40000 ALTER TABLE `pagos_compra` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagos_compra` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `pagos_venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_venta` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_venta` int NOT NULL,
  `tipo_pago` varchar(50) NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `referencia` varchar(200) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pv_venta` (`id_venta`),
  CONSTRAINT `fk_pagosventa_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pagos_venta` WRITE;
/*!40000 ALTER TABLE `pagos_venta` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagos_venta` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_producto` varchar(150) NOT NULL,
  `codigo_producto` varchar(50) DEFAULT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `stock_actual` int DEFAULT '0',
  `stock_minimo` int DEFAULT '5',
  `estado_producto` tinyint(1) DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_registro` varchar(100) DEFAULT NULL,
  `usuario_modificacion` varchar(100) DEFAULT NULL,
  `fecha_modificacion` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_producto` (`codigo_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `proveedor_producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedor_producto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_proveedor` int NOT NULL,
  `id_producto` int NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_proveedor_producto` (`id_proveedor`,`id_producto`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `fk_pp_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pp_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `proveedor_producto` WRITE;
/*!40000 ALTER TABLE `proveedor_producto` DISABLE KEYS */;
/*!40000 ALTER TABLE `proveedor_producto` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_proveedor` varchar(150) NOT NULL,
  `telefono_proveedor` varchar(20) DEFAULT NULL,
  `direccion_proveedor` varchar(200) DEFAULT NULL,
  `estado_proveedor` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_registro` varchar(100) DEFAULT NULL,
  `usuario_modificacion` varchar(100) DEFAULT NULL,
  `fecha_modificacion` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `series_comprobantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `series_comprobantes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_comprobante` varchar(50) NOT NULL,
  `serie` varchar(50) NOT NULL,
  `ultimo_numero` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tipo_serie` (`tipo_comprobante`,`serie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `series_comprobantes` WRITE;
/*!40000 ALTER TABLE `series_comprobantes` DISABLE KEYS */;
/*!40000 ALTER TABLE `series_comprobantes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `servicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `servicios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_servicio` varchar(200) NOT NULL,
  `categoria_servicio` varchar(200) NOT NULL,
  `precio_sugerido` decimal(10,2) DEFAULT '0.00',
  `estado_servicio` tinyint(1) DEFAULT '1',
  `fecha_ingreso` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_registro` varchar(100) DEFAULT NULL,
  `usuario_modificacion` varchar(100) DEFAULT NULL,
  `fecha_modificacion` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `servicios` WRITE;
/*!40000 ALTER TABLE `servicios` DISABLE KEYS */;
INSERT INTO `servicios` VALUES (1,'Fotocopia normal','Fotocopia',250.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(2,'Fotocopia a color','Fotocopia',2000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(3,'Fotocopia de cedula B/N','Fotocopia',1000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(4,'Fotocopia de cedula Color','Fotocopia',2000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(5,'Impresión normal','Impresión',1000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(6,'Impresión Full Color','Impresión',2000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(7,'Impresión en Fotográfico Normal y Mate','Impresión',10000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(8,'Impresión en Adhesivo Foto y Normal','Impresión',10000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(9,'Impresión Foto + Diseño','Impresión',15000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(10,'Stickers Personalizado','Impresión',10000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(11,'Certificado de Estudios desde AprendizajeMEC','Impresión',5000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(12,'Papel Ilustrativo 260g Oficio/A4','Impresión',5000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(13,'Papel Ilustrativo 260g A5','Impresión',2500.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(14,'Foto Tipo Carnet C/U','Impresión',5000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(15,'Foto Tipo Carnet 5+','Impresión',4000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(16,'Impresión en Cartulina','Impresión',5000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(17,'Tipeo x Hoja','Trabajos',3000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(18,'Modificación de Documento','Trabajos',3000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(19,'Powerpoint x Diapositiva','Trabajos',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(20,'Trabajo Práctico 1','Trabajos',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(21,'Trabajo Práctico 2','Trabajos',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(22,'Trabajo Práctico 3','Trabajos',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(23,'Trabajo Práctico 4','Trabajos',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(24,'Trabajo Práctico 5','Trabajos',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(25,'Trabajo Práctico 6','Trabajos',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(26,'Curriculum Vitae','Trabajos',5000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(27,'Nota de Pedido','Trabajos',3000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(28,'Declaración Jurada','Trabajos',25000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(29,'Censo de Jubilados','Trabajos',10000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(30,'Certificado de Trabajo','Trabajos',5000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(31,'Escaneo x Hoja','Escaneo',2000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(32,'Carga de Documento C/U','Sigmec',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(33,'Impresión de Sigmec','Sigmec',5000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(34,'Impresión de Sigmec 5+ hojas','Sigmec',10000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(35,'Modificación de Datos','Sigmec',5000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(36,'Bonificación familiar x niño','Sigmec',5000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(37,'Bonificación familiar x niño + reconocimiento','Sigmec',10000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(38,'Sublimación en remera','Sublimación',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(39,'Sublimación + Remera','Sublimación',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(40,'Sublimación taza de cerámica','Sublimación',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(41,'Sublimación Hoppie','Sublimación',40000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(42,'Sublimación Hoppie grande','Sublimación',50000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(43,'Sublimación Chopera','Sublimación',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(44,'Sublimación Llavero','Sublimación',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(45,'Impresión de RUE','RUE',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(46,'Carga de Alumnos','RUE',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(47,'Carga de Notas','RUE',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(48,'Modificación de Datos','RUE',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(49,'Creación Identidad Electrónica','Trámites',10000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(50,'Certificado de no ser Agresor Sexual','Trámites',10000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(51,'Antecedente Policial','Trámites',35000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(52,'Antecedente Judicial','Trámites',55000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(53,'Consulta Informcof','Trámites',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(54,'Certificado de Estudios MEC','Trámites',17000.00,1,'2025-11-12 12:00:00','Kevin',NULL,NULL),(55,'Llavero con foto','Varios',NULL,1,'2025-11-12 12:00:00','Kevin',NULL,NULL);
/*!40000 ALTER TABLE `servicios` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('ADMINISTRADOR','CAJERO','VENDEDOR') DEFAULT 'VENDEDOR',
  `estado` tinyint(1) DEFAULT '1',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `intentos_fallidos` int DEFAULT '0',
  `bloqueado_hasta` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador','admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','ADMINISTRADOR',1,'2025-11-02 17:16:46','2025-11-12 11:52:07',0,NULL),(2,'KEVIN CABALLERO','kevin','$2y$10$PqxbCX.36728TxpciRKMI.9w4w7lvpXjZYZVjNeMrmSE3FbwVlnBK','ADMINISTRADOR',1,'2025-11-05 01:14:18','2025-11-05 20:19:59',0,NULL),(3,'KEVIN CABALLERO 2','kevinsec','$2y$10$2Ypzb54lDJUG5l71AVn8lezWL5lvV2JmmzTn3MYataua8GdOBrsiW','ADMINISTRADOR',1,'2025-11-05 01:14:35','2025-11-05 16:08:33',0,NULL),(4,'KEVIN CABALLERO 3','kevin3','$2y$10$KhhKFJ63yjP6bZLvc9eqJekujIDPwyVVWsEZ61qEoqw1Oeg1ayogS','ADMINISTRADOR',1,'2025-11-05 01:14:47',NULL,0,NULL),(5,'KEVIN CABALLERO 4','admin2','$2y$10$fXnEE5KuPaxev4ATeBDkDuPxC6a0Yc1cNr9RmLyT/04c4LBzNPWI2','VENDEDOR',1,'2025-11-05 01:15:29',NULL,0,NULL),(6,'VENDEDOR','vendedor','$2y$10$ZQSxSJ7z0M4vctkrWCeV0u1IFVPo2CtEVcxqBXW5NPsVIIfXgIg5S','VENDEDOR',1,'2025-11-05 01:19:52','2025-11-05 01:20:03',0,NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `v_productos_bajo_stock`;
/*!50001 DROP VIEW IF EXISTS `v_productos_bajo_stock`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_productos_bajo_stock` AS SELECT 
 1 AS `id`,
 1 AS `nombre_producto`,
 1 AS `codigo_producto`,
 1 AS `stock_actual`,
 1 AS `stock_minimo`,
 1 AS `faltante`,
 1 AS `nivel_alerta`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_productos_mas_vendidos`;
/*!50001 DROP VIEW IF EXISTS `v_productos_mas_vendidos`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_productos_mas_vendidos` AS SELECT 
 1 AS `id_producto`,
 1 AS `nombre_producto`,
 1 AS `total_vendido`,
 1 AS `num_ventas`,
 1 AS `ingresos_generados`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_resumen_caja`;
/*!50001 DROP VIEW IF EXISTS `v_resumen_caja`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_resumen_caja` AS SELECT 
 1 AS `fecha`,
 1 AS `total_ingresos`,
 1 AS `total_egresos`,
 1 AS `saldo_dia`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_servicios_mas_solicitados`;
/*!50001 DROP VIEW IF EXISTS `v_servicios_mas_solicitados`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_servicios_mas_solicitados` AS SELECT 
 1 AS `id_servicio`,
 1 AS `nombre_servicio`,
 1 AS `veces_solicitado`,
 1 AS `cantidad_total`,
 1 AS `precio_promedio`,
 1 AS `ingresos_generados`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `usuario_registro` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ventas_tipo_serie_num` (`tipo_comprobante`,`serie`,`numero_venta`),
  KEY `idx_cliente` (`id_cliente`),
  KEY `idx_fecha` (`fecha_venta`),
  KEY `idx_estado` (`estado_venta`),
  KEY `idx_fecha_anulacion` (`fecha_anulacion`),
  KEY `idx_usuario_anula` (`usuario_anula`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;
/*!50001 DROP VIEW IF EXISTS `v_productos_bajo_stock`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_productos_bajo_stock` AS select `p`.`id` AS `id`,`p`.`nombre_producto` AS `nombre_producto`,`p`.`codigo_producto` AS `codigo_producto`,`p`.`stock_actual` AS `stock_actual`,`p`.`stock_minimo` AS `stock_minimo`,(`p`.`stock_minimo` - `p`.`stock_actual`) AS `faltante`,(case when (`p`.`stock_actual` = 0) then 'CRÍTICO' when (`p`.`stock_actual` <= (`p`.`stock_minimo` * 0.5)) then 'MUY BAJO' when (`p`.`stock_actual` <= `p`.`stock_minimo`) then 'BAJO' else 'NORMAL' end) AS `nivel_alerta` from `productos` `p` where ((`p`.`stock_actual` <= `p`.`stock_minimo`) and (`p`.`estado_producto` = 1)) order by (case when (`p`.`stock_actual` = 0) then 1 when (`p`.`stock_actual` <= (`p`.`stock_minimo` * 0.5)) then 2 when (`p`.`stock_actual` <= `p`.`stock_minimo`) then 3 else 4 end),`p`.`stock_actual` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_productos_mas_vendidos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_productos_mas_vendidos` AS select `dv`.`id_item` AS `id_producto`,`dv`.`descripcion` AS `nombre_producto`,sum(`dv`.`cantidad`) AS `total_vendido`,count(distinct `dv`.`id_venta`) AS `num_ventas`,sum(`dv`.`subtotal`) AS `ingresos_generados` from (`detalle_ventas` `dv` join `ventas` `v` on((`dv`.`id_venta` = `v`.`id`))) where ((`dv`.`tipo_item` = 'PRODUCTO') and (`v`.`estado_venta` = 1)) group by `dv`.`id_item`,`dv`.`descripcion` order by `total_vendido` desc limit 0,10 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_resumen_caja`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_resumen_caja` AS select cast(`caja`.`fecha_movimiento` as date) AS `fecha`,sum((case when (`caja`.`tipo_movimiento` = 'INGRESO') then `caja`.`monto` else 0 end)) AS `total_ingresos`,sum((case when (`caja`.`tipo_movimiento` = 'EGRESO') then `caja`.`monto` else 0 end)) AS `total_egresos`,sum((case when (`caja`.`tipo_movimiento` = 'INGRESO') then `caja`.`monto` else -(`caja`.`monto`) end)) AS `saldo_dia` from `caja` group by cast(`caja`.`fecha_movimiento` as date) order by `fecha` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_servicios_mas_solicitados`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_servicios_mas_solicitados` AS select `dv`.`id_item` AS `id_servicio`,`dv`.`descripcion` AS `nombre_servicio`,count(0) AS `veces_solicitado`,sum(`dv`.`cantidad`) AS `cantidad_total`,avg(`dv`.`precio_unitario`) AS `precio_promedio`,sum(`dv`.`subtotal`) AS `ingresos_generados` from (`detalle_ventas` `dv` join `ventas` `v` on((`dv`.`id_venta` = `v`.`id`))) where ((`dv`.`tipo_item` = 'SERVICIO') and (`v`.`estado_venta` = 1)) group by `dv`.`id_item`,`dv`.`descripcion` order by `veces_solicitado` desc limit 0,10 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

