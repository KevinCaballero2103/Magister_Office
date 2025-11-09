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
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `caja` WRITE;
/*!40000 ALTER TABLE `caja` DISABLE KEYS */;
INSERT INTO `caja` VALUES (1,'EGRESO','COMPRA',1,'COMPRA #1 - OFIMARKET',1400000.00,'2025-10-08',NULL,'2025-10-08 20:07:39',NULL,NULL,NULL,NULL),(2,'EGRESO','COMPRA',2,'COMPRA #2 - ALAMO ORIGI',60000.00,'2025-10-08',NULL,'2025-10-08 20:10:31',NULL,NULL,NULL,NULL),(3,'EGRESO','COMPRA',3,'COMPRA #3 - SANTEI',100000.00,'2025-10-08',NULL,'2025-10-08 20:13:02',NULL,NULL,NULL,NULL),(4,'EGRESO','COMPRA',4,'COMPRA #4 - ALAMON\'T',19000.00,'2025-10-08',NULL,'2025-10-08 20:15:32',NULL,NULL,NULL,NULL),(5,'EGRESO','COMPRA',5,'COMPRA #5 - OFIMARKET',350000.00,'2025-10-08',NULL,'2025-10-08 20:18:04',NULL,NULL,NULL,NULL),(6,'EGRESO','COMPRA',6,'COMPRA #6 - OFIMARKET',515000.00,'2025-10-08',NULL,'2025-10-08 20:20:15',NULL,NULL,NULL,NULL),(9,'EGRESO','COMPRA',9,'COMPRA #9 - ALAMO2',3040000.00,'2025-10-12',NULL,'2025-10-12 00:44:06',NULL,NULL,NULL,NULL),(13,'EGRESO','COMPRA',18,'COMPRA #18 - ALAMO ORIGI',20000.00,'2025-10-15',NULL,'2025-10-15 00:49:36',NULL,NULL,NULL,NULL),(15,'INGRESO','VENTA',4,'VENTA #4 - KEVIN SEBASTIAN CABALLERO GODOY',3000.00,'2025-10-16',NULL,'2025-10-15 19:57:08',NULL,NULL,NULL,NULL),(17,'EGRESO','COMPRA',20,'COMPRA #20 - ALAMO ORIGI',200000.00,'2025-10-15',NULL,'2025-10-15 20:07:45',NULL,NULL,NULL,NULL),(18,'INGRESO','VENTA',5,'VENTA #5 - KEVIN SEBASTIAN CABALLERO GODOY (Ticket: 0000001)',275000.00,'2025-10-19',NULL,'2025-10-19 23:15:33',NULL,NULL,NULL,NULL),(19,'INGRESO','VENTA',6,'VENTA #6 - KEVIN SEBASTIAN CABALLERO GODOY',66000.00,'2025-10-22',NULL,'2025-10-22 00:39:29',NULL,NULL,NULL,NULL),(20,'INGRESO','VENTA',7,'VENTA #7 - KEVIN SEBASTIAN CABALLERO GODOY (Ticket: 0000007)',746075.00,'2025-10-22',NULL,'2025-10-22 21:15:59',NULL,NULL,NULL,NULL),(21,'INGRESO','VENTA',8,'VENTA #8 - KEVIN SEBASTIAN CABALLERO GODOY',550000.00,'2025-10-22',NULL,'2025-10-22 21:17:48',NULL,NULL,NULL,NULL),(23,'INGRESO','VENTA',10,'VENTA #10 - KEVIN SEBASTIAN CABALLERO GODOY (FACTURA: 001-001-0000835)',3000.00,'2025-10-23',NULL,'2025-10-23 22:56:26',NULL,NULL,NULL,NULL),(24,'INGRESO','VENTA',11,'VENTA #11 - MAGNA ADELINA GODOY OLMEDO (FACTURA: 001-001-0000836)',1130000.00,'2025-10-24',NULL,'2025-10-24 00:09:48',NULL,NULL,NULL,NULL),(25,'INGRESO','VENTA',12,'VENTA #12 - MAGNA ADELINA GODOY OLMEDO (FACTURA: 001-001-0000837)',250000.00,'2025-10-24',NULL,'2025-10-24 21:42:36',NULL,NULL,NULL,NULL),(26,'INGRESO','VENTA',13,'VENTA #13 - MAGNA ADELINA GODOY OLMEDO (FACTURA: 001-001-0000838)',1575000.00,'2025-10-24',NULL,'2025-10-24 22:17:40',NULL,NULL,NULL,NULL),(27,'INGRESO','VENTA',14,'VENTA #14 - MAGNA ADELINA GODOY OLMEDO (FACTURA: 001-001-0000839)',27000.00,'2025-10-24',NULL,'2025-10-24 22:43:22',NULL,NULL,NULL,NULL),(29,'EGRESO','OTRO',12,'ANULACIÓN FACTURA #12 - MAGNA ADELINA GODOY OLMEDO (001-001-0000837)',250000.00,'2025-11-02','Anulado por: ADMIN\nMotivo: Prueba 1','2025-11-02 17:21:45','ADMIN',25,NULL,NULL),(30,'EGRESO','OTRO',14,'ANULACIÓN FACTURA #14 - MAGNA ADELINA GODOY OLMEDO (001-001-0000839)',27000.00,'2025-11-02','Anulado por: ADMIN\nMotivo: wery','2025-11-02 17:23:03','ADMIN',27,NULL,NULL),(31,'EGRESO','OTRO',11,'ANULACIÓN FACTURA #11 - MAGNA ADELINA GODOY OLMEDO (001-001-0000836)',1130000.00,'2025-11-02','Anulado por: ADMIN\nMotivo: prueba23','2025-11-02 20:09:29','ADMIN',24,NULL,NULL),(32,'EGRESO','OTRO',13,'ANULACIÓN FACTURA #13 - MAGNA ADELINA GODOY OLMEDO (001-001-0000838)',1575000.00,'2025-11-02','Anulado por: ADMIN\nMotivo: wdsfghkjl','2025-11-02 20:13:10','ADMIN',26,NULL,NULL),(33,'EGRESO','OTRO',10,'ANULACIÓN FACTURA #10 - KEVIN SEBASTIAN CABALLERO GODOY (001-001-0000835)',3000.00,'2025-11-02','Anulado por: ADMIN\nMotivo: prueba444','2025-11-03 01:21:03','ADMIN',23,NULL,NULL),(34,'EGRESO','OTRO',6,'ANULACIÓN FACTURA #6 - KEVIN SEBASTIAN CABALLERO GODOY (N/A)',66000.00,'2025-11-02','Anulado por: ADMIN\nMotivo: wergh','2025-11-03 01:21:49','ADMIN',19,NULL,NULL),(35,'EGRESO','COMPRA',21,'COMPRA #21 - ALAMO ORIGI (Factura: 4567887654)',180000.00,'2025-11-05',NULL,'2025-11-05 01:01:54',NULL,NULL,NULL,NULL),(36,'INGRESO','VENTA',16,'VENTA #16 - KEVIN SEBASTIAN CABALLERO GODOY (FACTURA: 001-001-0000841)',30000.00,'2025-11-05',NULL,'2025-11-05 19:48:19',NULL,NULL,NULL,NULL),(37,'INGRESO','VENTA',17,'VENTA #17 - Cliente Genérico',20000.00,'2025-11-05',NULL,'2025-11-05 19:48:50',NULL,NULL,NULL,NULL),(38,'INGRESO','VENTA',18,'VENTA #18 - Cliente Genérico (TICKET: 0000018)',12500.00,'2025-11-05',NULL,'2025-11-05 19:49:31',NULL,NULL,NULL,NULL),(39,'EGRESO','OTRO',16,'ANULACIÓN FACTURA #16 - KEVIN SEBASTIAN CABALLERO GODOY (001-001-0000841)',30000.00,'2025-11-05','Anulado por: ADMIN\nMotivo: Anulación de prueba','2025-11-05 19:51:11','ADMIN',36,NULL,NULL),(40,'INGRESO','VENTA',19,'VENTA #19 - KEVIN SEBASTIAN CABALLERO GODOY (FACTURA: 001-001-0000842)',3000.00,'2025-11-05',NULL,'2025-11-05 20:05:35',NULL,NULL,NULL,NULL),(41,'EGRESO','OTRO',19,'ANULACIÓN FACTURA #19 - KEVIN SEBASTIAN CABALLERO GODOY (001-001-0000842)',3000.00,'2025-11-05','Anulado por: ADMIN\nMotivo: a','2025-11-05 20:05:58','ADMIN',40,NULL,NULL),(42,'INGRESO','VENTA',20,'VENTA #20 - MAGNA ADELINA GODOY OLMEDO (FACTURA: 001-001-0000843)',3000.00,'2025-11-05',NULL,'2025-11-05 20:06:24',NULL,NULL,NULL,NULL),(43,'INGRESO','VENTA',21,'VENTA #21 - GHDFB SDFSDFGDSDFSDF (FACTURA: 001-001-0000844)',3000.00,'2025-11-05',NULL,'2025-11-05 20:09:54',NULL,NULL,NULL,NULL),(44,'EGRESO','OTRO',21,'ANULACIÓN FACTURA #21 - GHDFB SDFSDFGDSDFSDF (001-001-0000844)',3000.00,'2025-11-05','Anulado por: KEVIN CABALLERO\nMotivo: asdfg','2025-11-05 20:13:15','KEVIN CABALLERO',43,NULL,NULL),(45,'EGRESO','OTRO',20,'ANULACIÓN FACTURA #20 - MAGNA ADELINA GODOY OLMEDO (001-001-0000843)',3000.00,'2025-11-05','Anulado por: KEVIN CABALLERO\nMotivo: dsfgh','2025-11-05 20:21:02','KEVIN CABALLERO',42,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cierres_caja` WRITE;
/*!40000 ALTER TABLE `cierres_caja` DISABLE KEYS */;
INSERT INTO `cierres_caja` VALUES (1,'2025-10-26 18:47:00','2025-10-26 18:47:00',10000.00,10000.00,0.00,0.00,10000.00,10000.00,0.00,NULL,NULL,'CERRADA','Kevin','Kevin','2025-10-27 00:47:36'),(2,'2025-11-04 10:39:00','2025-11-04 10:40:00',0.00,0.00,0.00,0.00,0.00,0.00,0.00,NULL,NULL,'CERRADA','Administrador','Administrador','2025-11-04 16:39:47'),(3,'2025-11-04 19:01:00','2025-11-04 19:02:00',500000.00,320000.00,0.00,180000.00,320000.00,320000.00,0.00,NULL,NULL,'CERRADA','Administrador','Administrador','2025-11-05 01:01:27'),(4,'2025-11-04 19:02:00','2025-11-04 19:03:00',100000.00,100000.00,0.00,180000.00,-80000.00,100000.00,180000.00,NULL,NULL,'CERRADA','Administrador','Administrador','2025-11-05 01:02:47'),(5,'2025-11-04 19:05:00','2025-11-04 19:05:00',0.00,0.00,0.00,180000.00,-180000.00,0.00,180000.00,NULL,NULL,'CERRADA','Administrador','Administrador','2025-11-05 01:05:41'),(6,'2025-11-05 13:47:00','2025-11-05 13:58:00',100000.00,100000.00,0.00,0.00,100000.00,100000.00,0.00,NULL,NULL,'CERRADA','KEVIN CABALLERO','KEVIN CABALLERO','2025-11-05 19:47:12'),(7,'2025-11-05 14:18:00','2025-11-05 14:19:00',0.00,0.00,0.00,0.00,0.00,0.00,0.00,NULL,NULL,'CERRADA','KEVIN CABALLERO','KEVIN CABALLERO','2025-11-05 20:18:44'),(8,'2025-11-05 14:20:00','2025-11-08 20:00:00',0.00,0.00,0.00,0.00,0.00,0.00,0.00,NULL,NULL,'CERRADA','KEVIN CABALLERO','KEVIN CABALLERO','2025-11-05 20:20:08');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'KEVIN SEBASTIAN','CABALLERO GODOY','123423','1000000','ejemplo@gmail.com','CALLE 1 C/ CALLE 3','2025-09-17 22:42:30',NULL,1,NULL,NULL),(2,'SDADS','ADASD','3423423','','','','2025-09-21 01:31:03',NULL,1,NULL,NULL),(3,'KEVIN SEBASTIAN','CABALLERO GODOY','3455545','0909090909','ejemplo@gmail.com','CALLE 1 C/ CALLE 2','2025-09-21 03:46:17',NULL,1,NULL,NULL),(4,'PRUEBA','PP','3243','','','','2025-09-21 06:21:17',NULL,1,NULL,NULL),(5,'PRUEBA4','DFFSFDSS','123456789','','','','2025-09-21 13:43:29',NULL,1,NULL,NULL),(6,'GHDFB','SDFSDFGDSDFSDF','3564','','sdfsdfsdf@gmail.com','','2025-09-24 19:49:17',NULL,1,NULL,NULL),(7,'MAGNA ADELINA','GODOY OLMEDO','4305336','0972617447','magnitagodoy2016@gmail.com','SAN JUAN','2025-10-24 00:08:15',NULL,1,NULL,NULL),(8,'LUNA YOMAIRA','CABALLERO GODOY','4444444','45678976443','lunananana@gmail.com','SDASDASDADS','2025-11-04 00:21:04',NULL,1,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
INSERT INTO `compras` VALUES (1,4,NULL,'2025-10-08',1400000.00,NULL,1,'2025-10-08 20:07:39',NULL),(2,2,NULL,'2025-10-08',60000.00,NULL,1,'2025-10-08 20:10:31',NULL),(3,6,NULL,'2025-10-08',100000.00,NULL,1,'2025-10-08 20:13:02',NULL),(4,1,NULL,'2025-10-08',19000.00,'',1,'2025-10-08 20:15:32',NULL),(5,4,NULL,'2025-10-08',350000.00,'',1,'2025-10-08 20:18:04',NULL),(6,4,NULL,'2025-10-08',515000.00,'',1,'2025-10-08 20:20:15',NULL),(9,3,NULL,'2025-10-12',3040000.00,'',1,'2025-10-12 00:44:06',NULL),(13,3,NULL,'2025-10-15',19000.00,'',1,'2025-10-15 00:24:46',NULL),(14,1,NULL,'2025-10-15',1000.00,'',1,'2025-10-15 00:42:53',NULL),(15,1,'4567','2025-10-13',100000.00,'',1,'2025-10-15 00:43:32',NULL),(16,3,NULL,'2025-10-15',19000.00,'',1,'2025-10-15 00:45:46',NULL),(17,3,NULL,'2025-10-15',19000.00,'',1,'2025-10-15 00:48:49',NULL),(18,2,NULL,'2025-10-15',20000.00,'',1,'2025-10-15 00:49:36',NULL),(20,2,NULL,'2025-10-15',200000.00,'',1,'2025-10-15 20:07:45',NULL),(21,2,'4567887654','2025-11-05',180000.00,'',1,'2025-11-05 01:01:54',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cuotas_venta` WRITE;
/*!40000 ALTER TABLE `cuotas_venta` DISABLE KEYS */;
INSERT INTO `cuotas_venta` VALUES (1,5,1,55000.00,'2026-02-28','PENDIENTE',NULL),(2,5,2,55000.00,'2026-03-30','PENDIENTE',NULL),(3,5,3,55000.00,'2026-04-29','PENDIENTE',NULL),(4,5,4,55000.00,'2026-05-29','PENDIENTE',NULL),(5,5,5,55000.00,'2026-06-28','PENDIENTE',NULL),(6,7,1,62172.92,'2025-10-26','PENDIENTE',NULL),(7,7,2,62172.92,'2025-11-25','PENDIENTE',NULL),(8,7,3,62172.92,'2025-12-25','PENDIENTE',NULL),(9,7,4,62172.92,'2026-01-24','PENDIENTE',NULL),(10,7,5,62172.92,'2026-02-23','PENDIENTE',NULL),(11,7,6,62172.92,'2026-03-25','PENDIENTE',NULL),(12,7,7,62172.92,'2026-04-24','PENDIENTE',NULL),(13,7,8,62172.92,'2026-05-24','PENDIENTE',NULL),(14,7,9,62172.92,'2026-06-23','PENDIENTE',NULL),(15,7,10,62172.92,'2026-07-23','PENDIENTE',NULL),(16,7,11,62172.92,'2026-08-22','PENDIENTE',NULL),(17,7,12,62172.92,'2026-09-21','PENDIENTE',NULL),(18,8,1,183333.33,'2025-10-23','PENDIENTE',NULL),(19,8,2,183333.33,'2025-11-22','PENDIENTE',NULL),(20,8,3,183333.33,'2025-12-22','PENDIENTE',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `detalle_compras` WRITE;
/*!40000 ALTER TABLE `detalle_compras` DISABLE KEYS */;
INSERT INTO `detalle_compras` VALUES (1,1,3,56,25000.00,1400000.00),(2,2,2,20,3000.00,60000.00),(3,3,2,20,5000.00,100000.00),(4,4,1,19,1000.00,19000.00),(5,5,3,14,25000.00,350000.00),(6,6,3,20,25000.00,500000.00),(7,6,1,10,1500.00,15000.00),(10,9,3,160,19000.00,3040000.00),(13,18,3,1,20000.00,20000.00),(15,20,3,10,20000.00,200000.00),(17,21,3,9,20000.00,180000.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `detalle_ventas` WRITE;
/*!40000 ALTER TABLE `detalle_ventas` DISABLE KEYS */;
INSERT INTO `detalle_ventas` VALUES (1,1,'PRODUCTO',1,'BIROME BIC AZUL',NULL,6,3000.00,10.00,0.00,0.00,18000.00),(2,1,'SERVICIO',13,'MANTENIMIENTO DE IMPRESORA',NULL,1,30000.00,10.00,0.00,0.00,30000.00),(3,2,'PRODUCTO',2,'RESMA A4',NULL,2,50000.00,10.00,0.00,0.00,100000.00),(4,2,'SERVICIO',13,'MANTENIMIENTO DE IMPRESORA',NULL,1,30000.00,10.00,0.00,0.00,30000.00),(5,2,'PRODUCTO',3,'RESMA A5+',NULL,10,30000.00,10.00,0.00,0.00,300000.00),(6,3,'PRODUCTO',2,'RESMA A4',NULL,10,50000.00,10.00,0.00,0.00,500000.00),(7,3,'PRODUCTO',3,'RESMA A5+',NULL,1,30000.00,10.00,0.00,0.00,30000.00),(8,3,'SERVICIO',14,'MANTENIMIENTO DE LAPTOP',NULL,1,30000.00,10.00,0.00,0.00,30000.00),(9,4,'PRODUCTO',5,'BORRADOR',NULL,1,3000.00,10.00,0.00,0.00,3000.00),(10,5,'PRODUCTO',6,'REGLA 30CM',NULL,50,5000.00,10.00,0.00,0.00,250000.00),(11,6,'PRODUCTO',6,'REGLA 30CM',NULL,10,5000.00,10.00,0.00,0.00,50000.00),(12,6,'SERVICIO',16,'IMPRESIóN A COLOR',NULL,10,1000.00,10.00,0.00,0.00,10000.00),(13,7,'PRODUCTO',1,'BIROME BIC AZUL',NULL,10,3000.00,10.00,0.00,0.00,30000.00),(14,7,'PRODUCTO',6,'REGLA 30CM',NULL,10,5000.00,10.00,0.00,0.00,50000.00),(15,7,'PRODUCTO',5,'BORRADOR',NULL,9,3000.00,10.00,0.00,0.00,27000.00),(16,7,'PRODUCTO',2,'RESMA A4',NULL,10,50000.00,10.00,0.00,0.00,500000.00),(17,7,'SERVICIO',17,'FOTOCOPIAS',NULL,165,250.00,10.00,0.00,0.00,41250.00),(18,7,'SERVICIO',16,'IMPRESIóN A COLOR',NULL,15,2000.00,10.00,0.00,0.00,30000.00),(19,8,'SERVICIO',15,'MANTENIMIENTO DE PC DE ESCRITORIO',NULL,1,500000.00,10.00,0.00,0.00,500000.00),(20,9,'PRODUCTO',6,'REGLA 30CM',NULL,10,5000.00,10.00,0.00,0.00,50000.00),(21,9,'SERVICIO',16,'IMPRESIóN A COLOR',NULL,1,1000.00,10.00,0.00,0.00,1000.00),(22,10,'PRODUCTO',1,'BIROME BIC AZUL',NULL,1,3000.00,10.00,0.00,0.00,3000.00),(23,11,'PRODUCTO',2,'RESMA A4',NULL,10,50000.00,10.00,0.00,0.00,500000.00),(24,11,'PRODUCTO',3,'RESMA A5+',NULL,21,30000.00,10.00,0.00,0.00,630000.00),(25,12,'SERVICIO',15,'MANTENIMIENTO DE PC DE ESCRITORIO',NULL,1,100000.00,10.00,0.00,0.00,100000.00),(26,12,'PRODUCTO',6,'REGLA 30CM',NULL,30,5000.00,10.00,0.00,0.00,150000.00),(27,13,'PRODUCTO',6,'REGLA 30CM',NULL,10,5000.00,10.00,0.00,0.00,50000.00),(28,13,'SERVICIO',17,'FOTOCOPIAS',NULL,100,250.00,10.00,0.00,0.00,25000.00),(29,13,'PRODUCTO',2,'RESMA A4',NULL,30,50000.00,10.00,0.00,0.00,1500000.00),(30,14,'PRODUCTO',1,'BIROME BIC AZUL',NULL,9,3000.00,10.00,0.00,0.00,27000.00),(31,15,'PRODUCTO',1,'BIROME BIC AZUL',NULL,10,3000.00,10.00,0.00,0.00,30000.00),(32,15,'SERVICIO',17,'FOTOCOPIAS',NULL,100,250.00,10.00,0.00,0.00,25000.00),(33,16,'PRODUCTO',1,'BIROME BIC AZUL',NULL,10,3000.00,10.00,0.00,0.00,30000.00),(34,17,'SERVICIO',16,'IMPRESIóN A COLOR',NULL,10,2000.00,10.00,0.00,0.00,20000.00),(35,18,'SERVICIO',17,'FOTOCOPIAS',NULL,50,250.00,10.00,0.00,0.00,12500.00),(36,19,'PRODUCTO',1,'BIROME BIC AZUL',NULL,1,3000.00,10.00,0.00,0.00,3000.00),(37,20,'PRODUCTO',1,'BIROME BIC AZUL',NULL,1,3000.00,10.00,0.00,0.00,3000.00),(38,21,'PRODUCTO',1,'BIROME BIC AZUL',NULL,1,3000.00,10.00,0.00,0.00,3000.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `historial_anulaciones` WRITE;
/*!40000 ALTER TABLE `historial_anulaciones` DISABLE KEYS */;
INSERT INTO `historial_anulaciones` VALUES (1,12,'FACTURA','001-001-0000837',250000.00,'Prueba 1','ADMIN','2025-11-02 13:21:45','{\"productos_revertidos\":1,\"servicios\":1,\"items_totales\":2,\"cliente\":\"MAGNA ADELINA GODOY OLMEDO\",\"movimiento_caja_id\":\"29\"}','2025-11-02 17:21:45'),(2,14,'FACTURA','001-001-0000839',27000.00,'wery','ADMIN','2025-11-02 13:23:03','{\"productos_revertidos\":1,\"servicios\":0,\"items_totales\":1,\"cliente\":\"MAGNA ADELINA GODOY OLMEDO\",\"movimiento_caja_id\":\"30\"}','2025-11-02 17:23:03'),(3,11,'FACTURA','001-001-0000836',1130000.00,'prueba23','ADMIN','2025-11-02 16:09:29','{\"productos_revertidos\":2,\"servicios\":0,\"items_totales\":2,\"cliente\":\"MAGNA ADELINA GODOY OLMEDO\",\"movimiento_caja_id\":\"31\"}','2025-11-02 20:09:29'),(4,13,'FACTURA','001-001-0000838',1575000.00,'wdsfghkjl','ADMIN','2025-11-02 16:13:10','{\"productos_revertidos\":2,\"servicios\":1,\"items_totales\":3,\"cliente\":\"MAGNA ADELINA GODOY OLMEDO\",\"movimiento_caja_id\":\"32\"}','2025-11-02 20:13:10'),(5,10,'FACTURA','001-001-0000835',3000.00,'prueba444','ADMIN','2025-11-02 21:21:03','{\"productos_revertidos\":1,\"servicios\":0,\"items_totales\":1,\"cliente\":\"KEVIN SEBASTIAN CABALLERO GODOY\",\"movimiento_caja_id\":\"33\"}','2025-11-03 01:21:03'),(6,6,'FACTURA','N/A',66000.00,'wergh','ADMIN','2025-11-02 21:21:49','{\"productos_revertidos\":1,\"servicios\":1,\"items_totales\":2,\"cliente\":\"KEVIN SEBASTIAN CABALLERO GODOY\",\"movimiento_caja_id\":\"34\"}','2025-11-03 01:21:49'),(7,16,'FACTURA','001-001-0000841',30000.00,'Anulación de prueba','ADMIN','2025-11-05 16:51:11','{\"productos_revertidos\":1,\"servicios\":0,\"items_totales\":1,\"cliente\":\"KEVIN SEBASTIAN CABALLERO GODOY\",\"movimiento_caja_id\":\"39\"}','2025-11-05 19:51:11'),(8,19,'FACTURA','001-001-0000842',3000.00,'a','ADMIN','2025-11-05 17:05:58','{\"productos_revertidos\":1,\"servicios\":0,\"items_totales\":1,\"cliente\":\"KEVIN SEBASTIAN CABALLERO GODOY\",\"movimiento_caja_id\":\"41\"}','2025-11-05 20:05:58'),(9,21,'FACTURA','001-001-0000844',3000.00,'asdfg','KEVIN CABALLERO','2025-11-05 17:13:15','{\"productos_revertidos\":1,\"servicios\":0,\"items_totales\":1,\"cliente\":\"GHDFB SDFSDFGDSDFSDF\",\"movimiento_caja_id\":\"44\"}','2025-11-05 20:13:15'),(10,20,'FACTURA','001-001-0000843',3000.00,'dsfgh','KEVIN CABALLERO','2025-11-05 17:21:02','{\"productos_revertidos\":1,\"servicios\":0,\"items_totales\":1,\"cliente\":\"MAGNA ADELINA GODOY OLMEDO\",\"movimiento_caja_id\":\"45\"}','2025-11-05 20:21:02');
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
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `historial_stock` WRITE;
/*!40000 ALTER TABLE `historial_stock` DISABLE KEYS */;
INSERT INTO `historial_stock` VALUES (1,3,'ENTRADA',56,30,86,'COMPRA #1 - Proveedor ID: 4',1,'2025-10-08 20:07:39'),(2,2,'ENTRADA',20,310,330,'COMPRA #2 - Proveedor ID: 2',2,'2025-10-08 20:10:31'),(3,2,'ENTRADA',20,330,350,'COMPRA #3 - Proveedor ID: 6',3,'2025-10-08 20:13:02'),(4,1,'ENTRADA',19,11,30,'COMPRA #4 - Proveedor ID: 1',4,'2025-10-08 20:15:32'),(5,3,'ENTRADA',14,86,100,'COMPRA #5 - Proveedor ID: 4',5,'2025-10-08 20:18:04'),(6,3,'ENTRADA',20,100,120,'COMPRA #6 - Proveedor ID: 4',6,'2025-10-08 20:20:15'),(7,1,'ENTRADA',10,30,40,'COMPRA #6 - Proveedor ID: 4',6,'2025-10-08 20:20:15'),(8,3,'ENTRADA',130,120,250,'COMPRA #7 - Proveedor ID: 4',7,'2025-10-09 19:53:54'),(9,3,'ENTRADA',10,250,260,'COMPRA #8 - Proveedor ID: 4',8,'2025-10-12 00:43:13'),(10,3,'ENTRADA',160,260,420,'COMPRA #9 - Proveedor ID: 3',9,'2025-10-12 00:44:06'),(11,1,'SALIDA',6,40,34,'VENTA #1',1,'2025-10-12 18:39:35'),(12,3,'ENTRADA',80,420,500,'COMPRA #10 - Proveedor ID: 4',10,'2025-10-14 01:57:50'),(13,2,'ENTRADA',50,350,400,'COMPRA #10 - Proveedor ID: 4',10,'2025-10-14 01:57:50'),(14,3,'SALIDA',80,500,420,'ELIMINACIÓN COMPRA #10',10,'2025-10-14 01:58:16'),(15,2,'SALIDA',50,400,350,'ELIMINACIÓN COMPRA #10',10,'2025-10-14 01:58:16'),(16,3,'SALIDA',10,420,410,'ELIMINACIÓN COMPRA #8',8,'2025-10-14 02:01:38'),(17,2,'SALIDA',2,350,348,'VENTA #2',2,'2025-10-14 02:03:30'),(18,3,'SALIDA',10,410,400,'VENTA #2',2,'2025-10-14 02:03:30'),(19,1,'ENTRADA',6,34,40,'ANULACIÓN VENTA #1',1,'2025-10-14 02:04:04'),(20,2,'ENTRADA',2,348,350,'ANULACIÓN VENTA #2',2,'2025-10-14 02:04:27'),(21,3,'ENTRADA',10,400,410,'ANULACIÓN VENTA #2',2,'2025-10-14 02:04:27'),(22,3,'ENTRADA',1,410,411,'COMPRA #18 - Proveedor ID: 2',18,'2025-10-15 00:49:36'),(23,2,'SALIDA',10,350,340,'VENTA #3',3,'2025-10-15 19:56:31'),(24,3,'SALIDA',1,411,410,'VENTA #3',3,'2025-10-15 19:56:31'),(25,5,'SALIDA',1,100,99,'VENTA #4',4,'2025-10-15 19:57:08'),(26,5,'ENTRADA',10,99,109,'COMPRA #19 - Proveedor ID: 7',19,'2025-10-15 19:57:48'),(27,3,'ENTRADA',10,410,420,'COMPRA #20 - Proveedor ID: 2',20,'2025-10-15 20:07:45'),(28,5,'AJUSTE',5,109,104,'AJUSTE COMPRA #19: -5 unidades',19,'2025-10-19 14:06:35'),(29,5,'AJUSTE',15,104,119,'AJUSTE COMPRA #19: +15 unidades',19,'2025-10-19 14:08:15'),(30,5,'AJUSTE',2,119,121,'AJUSTE COMPRA #19: +2 unidades',19,'2025-10-19 19:45:24'),(31,6,'AJUSTE',2,200,202,'AJUSTE COMPRA #19: +2 unidades (nuevo)',19,'2025-10-19 19:45:24'),(32,5,'AJUSTE',2,121,119,'AJUSTE COMPRA #19: -2 unidades',19,'2025-10-19 19:46:12'),(33,6,'AJUSTE',2,202,200,'AJUSTE COMPRA #19: -2 unidades (eliminado)',19,'2025-10-19 19:46:12'),(34,5,'SALIDA',20,119,99,'ELIMINACIÓN COMPRA #19',19,'2025-10-19 19:46:20'),(35,6,'SALIDA',50,200,150,'VENTA #5',5,'2025-10-19 23:15:33'),(36,6,'SALIDA',10,150,140,'VENTA #6',6,'2025-10-22 00:39:29'),(37,2,'ENTRADA',10,340,350,'ANULACIÓN VENTA #3',3,'2025-10-22 21:11:21'),(38,3,'ENTRADA',1,420,421,'ANULACIÓN VENTA #3',3,'2025-10-22 21:11:21'),(39,1,'SALIDA',10,40,30,'VENTA #7',7,'2025-10-22 21:15:59'),(40,6,'SALIDA',10,140,130,'VENTA #7',7,'2025-10-22 21:15:59'),(41,5,'SALIDA',9,99,90,'VENTA #7',7,'2025-10-22 21:15:59'),(42,2,'SALIDA',10,350,340,'VENTA #7',7,'2025-10-22 21:15:59'),(43,6,'SALIDA',10,130,120,'VENTA #9',9,'2025-10-23 22:55:29'),(44,1,'SALIDA',1,30,29,'VENTA #10',10,'2025-10-23 22:56:26'),(45,6,'ENTRADA',10,120,130,'ANULACIÓN VENTA #9',9,'2025-10-23 23:05:20'),(46,2,'SALIDA',10,340,330,'VENTA #11',11,'2025-10-24 00:09:48'),(47,3,'SALIDA',21,421,400,'VENTA #11',11,'2025-10-24 00:09:48'),(48,6,'SALIDA',30,130,100,'VENTA #12',12,'2025-10-24 21:42:36'),(49,6,'SALIDA',10,100,90,'VENTA #13',13,'2025-10-24 22:17:40'),(50,2,'SALIDA',30,330,300,'VENTA #13',13,'2025-10-24 22:17:40'),(51,1,'SALIDA',9,29,20,'VENTA #14',14,'2025-10-24 22:43:22'),(52,1,'SALIDA',10,20,10,'VENTA #15',15,'2025-10-29 21:35:27'),(53,1,'ENTRADA',10,10,20,'ANULACIÓN VENTA #15',15,'2025-10-29 22:07:13'),(54,6,'ENTRADA',30,90,120,'ANULACIÓN FACTURA #12 - Usuario: ADMIN - Motivo: Prueba 1',12,'2025-11-02 17:21:45'),(55,1,'ENTRADA',9,20,29,'ANULACIÓN FACTURA #14 - Usuario: ADMIN - Motivo: wery',14,'2025-11-02 17:23:03'),(56,2,'ENTRADA',10,300,310,'ANULACIÓN FACTURA #11 - Usuario: ADMIN - Motivo: prueba23',11,'2025-11-02 20:09:29'),(57,3,'ENTRADA',21,400,421,'ANULACIÓN FACTURA #11 - Usuario: ADMIN - Motivo: prueba23',11,'2025-11-02 20:09:29'),(58,6,'ENTRADA',10,120,130,'ANULACIÓN FACTURA #13 - Usuario: ADMIN - Motivo: wdsfghkjl',13,'2025-11-02 20:13:10'),(59,2,'ENTRADA',30,310,340,'ANULACIÓN FACTURA #13 - Usuario: ADMIN - Motivo: wdsfghkjl',13,'2025-11-02 20:13:10'),(60,1,'ENTRADA',1,29,30,'ANULACIÓN FACTURA #10 - Usuario: ADMIN - Motivo: prueba444',10,'2025-11-03 01:21:03'),(61,6,'ENTRADA',10,130,140,'ANULACIÓN FACTURA #6 - Usuario: ADMIN - Motivo: wergh',6,'2025-11-03 01:21:49'),(62,3,'ENTRADA',9,421,430,'COMPRA #21 - Proveedor ID: 2',21,'2025-11-05 01:01:54'),(63,1,'SALIDA',10,30,20,'VENTA #16',16,'2025-11-05 19:48:19'),(64,1,'ENTRADA',10,20,30,'ANULACIÓN FACTURA #16 - Usuario: ADMIN - Motivo: Anulación de prueba',16,'2025-11-05 19:51:11'),(65,1,'SALIDA',1,30,29,'VENTA #19',19,'2025-11-05 20:05:35'),(66,1,'ENTRADA',1,29,30,'ANULACIÓN FACTURA #19 - Usuario: ADMIN - Motivo: a',19,'2025-11-05 20:05:58'),(67,1,'SALIDA',1,30,29,'VENTA #20',20,'2025-11-05 20:06:24'),(68,1,'SALIDA',1,29,28,'VENTA #21',21,'2025-11-05 20:09:54'),(69,1,'ENTRADA',1,28,29,'ANULACIÓN FACTURA #21 - Usuario: KEVIN CABALLERO - Motivo: asdfg',21,'2025-11-05 20:13:15'),(70,1,'ENTRADA',1,29,30,'ANULACIÓN FACTURA #20 - Usuario: KEVIN CABALLERO - Motivo: dsfgh',20,'2025-11-05 20:21:02');
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
) ENGINE=InnoDB AUTO_INCREMENT=168 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `log_actividades` WRITE;
/*!40000 ALTER TABLE `log_actividades` DISABLE KEYS */;
INSERT INTO `log_actividades` VALUES (1,'Administrador','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-04 00:12:58'),(2,'Administrador','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-04 16:36:20'),(3,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-04 16:36:20'),(4,'Administrador','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-04 16:36:51'),(5,'Administrador','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-04 16:36:55'),(6,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-04 16:36:55'),(7,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-04 16:37:15'),(8,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-04 16:37:26'),(9,'Administrador','ACCESO','CAJA','Acceso a apertura de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-04 16:39:35'),(10,'Administrador','APERTURA_CAJA','CAJA','Apertura de caja #2 - Saldo inicial: ₲ 0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"id_cierre\":\"2\",\"saldo_inicial\":0,\"fecha_apertura\":\"2025-11-04 10:39:00\",\"observaciones\":null}','2025-11-04 16:39:47'),(11,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-04 16:39:55'),(12,'Administrador','ACCESO','CAJA','Acceso a cierre de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-04 16:40:02'),(13,'Administrador','CIERRE_CAJA','CAJA','Cierre de caja #2 - Diferencia: ₲ 0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','{\"saldo_inicial\":0,\"estado_anterior\":\"ABIERTA\"}','{\"id_cierre\":2,\"saldo_sistema\":0,\"saldo_fisico\":0,\"diferencia\":0,\"total_ingresos\":0,\"total_egresos\":0,\"estado_nuevo\":\"CERRADA\"}','2025-11-04 16:40:13'),(14,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-04 16:40:19'),(15,'Administrador','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-04 21:15:22'),(16,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-04 21:15:23'),(17,'Administrador','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 00:59:10'),(18,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 00:59:10'),(19,'Administrador','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-05 00:59:58'),(20,'Administrador','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 01:00:04'),(21,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:00:04'),(22,'Administrador','ACCESO','CAJA','Acceso a apertura de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:01:21'),(23,'Administrador','APERTURA_CAJA','CAJA','Apertura de caja #3 - Saldo inicial: ₲ 500.000','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"id_cierre\":\"3\",\"saldo_inicial\":500000,\"fecha_apertura\":\"2025-11-04 19:01:00\",\"observaciones\":null}','2025-11-05 01:01:27'),(24,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:01:28'),(25,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:01:56'),(26,'Administrador','ACCESO','CAJA','Acceso a cierre de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:02:23'),(27,'Administrador','CIERRE_CAJA','CAJA','Cierre de caja #3 - Diferencia: ₲ 0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','{\"saldo_inicial\":500000,\"estado_anterior\":\"ABIERTA\"}','{\"id_cierre\":3,\"saldo_sistema\":320000,\"saldo_fisico\":320000,\"diferencia\":0,\"total_ingresos\":0,\"total_egresos\":180000,\"estado_nuevo\":\"CERRADA\"}','2025-11-05 01:02:38'),(28,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:02:40'),(29,'Administrador','ACCESO','CAJA','Acceso a apertura de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:02:40'),(30,'Administrador','APERTURA_CAJA','CAJA','Apertura de caja #4 - Saldo inicial: ₲ 100.000','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"id_cierre\":\"4\",\"saldo_inicial\":100000,\"fecha_apertura\":\"2025-11-04 19:02:00\",\"observaciones\":null}','2025-11-05 01:02:47'),(31,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:02:48'),(32,'Administrador','ACCESO','CAJA','Acceso a cierre de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:03:04'),(33,'Administrador','CIERRE_CAJA','CAJA','Cierre de caja #4 - Diferencia: ₲ 180.000','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','{\"saldo_inicial\":100000,\"estado_anterior\":\"ABIERTA\"}','{\"id_cierre\":4,\"saldo_sistema\":-80000,\"saldo_fisico\":100000,\"diferencia\":180000,\"total_ingresos\":0,\"total_egresos\":180000,\"estado_nuevo\":\"CERRADA\"}','2025-11-05 01:03:29'),(34,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:03:31'),(35,'Administrador','ACCESO','CAJA','Acceso a apertura de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:05:35'),(36,'Administrador','APERTURA_CAJA','CAJA','Apertura de caja #5 - Saldo inicial: ₲ 0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"id_cierre\":\"5\",\"saldo_inicial\":0,\"fecha_apertura\":\"2025-11-04 19:05:00\",\"observaciones\":null}','2025-11-05 01:05:41'),(37,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:05:42'),(38,'Administrador','ACCESO','CAJA','Acceso a cierre de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:05:50'),(39,'Administrador','CIERRE_CAJA','CAJA','Cierre de caja #5 - Diferencia: ₲ 180.000','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','{\"saldo_inicial\":0,\"estado_anterior\":\"ABIERTA\"}','{\"id_cierre\":5,\"saldo_sistema\":-180000,\"saldo_fisico\":0,\"diferencia\":180000,\"total_ingresos\":0,\"total_egresos\":180000,\"estado_nuevo\":\"CERRADA\"}','2025-11-05 01:05:57'),(40,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:06:01'),(41,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:06:38'),(42,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:06:39'),(43,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:06:39'),(44,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:06:41'),(45,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:07:03'),(46,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:11:01'),(47,'Administrador','ACCESO','USUARIOS','Acceso al formulario de creación de usuario','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:11:05'),(48,'Administrador','CREAR','USUARIOS','Usuario creado: KEVIN CABALLERO (Login: kevin, Rol: ADMINISTRADOR)','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"id\":\"2\",\"nombre\":\"KEVIN CABALLERO\",\"usuario\":\"kevin\",\"rol\":\"ADMINISTRADOR\",\"estado\":1}','2025-11-05 01:14:18'),(49,'Administrador','CREAR','USUARIOS','Usuario creado: KEVIN CABALLERO 2 (Login: kevin2, Rol: ADMINISTRADOR)','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"id\":\"3\",\"nombre\":\"KEVIN CABALLERO 2\",\"usuario\":\"kevin2\",\"rol\":\"ADMINISTRADOR\",\"estado\":1}','2025-11-05 01:14:35'),(50,'Administrador','CREAR','USUARIOS','Usuario creado: KEVIN CABALLERO 3 (Login: kevin3, Rol: ADMINISTRADOR)','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"id\":\"4\",\"nombre\":\"KEVIN CABALLERO 3\",\"usuario\":\"kevin3\",\"rol\":\"ADMINISTRADOR\",\"estado\":1}','2025-11-05 01:14:47'),(51,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:14:50'),(52,'Administrador','ACCESO','USUARIOS','Acceso al formulario de creación de usuario','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:15:00'),(53,'Administrador','CREAR','USUARIOS','Usuario creado: KEVIN CABALLERO 4 (Login: admin2, Rol: VENDEDOR)','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"id\":\"5\",\"nombre\":\"KEVIN CABALLERO 4\",\"usuario\":\"admin2\",\"rol\":\"VENDEDOR\",\"estado\":1}','2025-11-05 01:15:29'),(54,'Administrador','ACCESO','USUARIOS','Acceso al listado de usuarios','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:15:32'),(55,'Administrador','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-05 01:15:43'),(56,'KEVIN CABALLERO','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 01:15:49'),(57,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:15:49'),(58,'KEVIN CABALLERO','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-05 01:15:58'),(59,'KEVIN CABALLERO','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 01:16:35'),(60,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:16:35'),(61,'KEVIN CABALLERO','ACCESO','CAJA','Acceso a apertura de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:16:39'),(62,'KEVIN CABALLERO','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-05 01:16:52'),(63,'KEVIN CABALLERO','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 01:17:35'),(64,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:17:35'),(65,'KEVIN CABALLERO','ACCESO','USUARIOS','Acceso al listado de usuarios','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:17:40'),(66,'KEVIN CABALLERO','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-05 01:19:14'),(67,'KEVIN CABALLERO','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 01:19:34'),(68,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:19:34'),(69,'KEVIN CABALLERO','ACCESO','USUARIOS','Acceso al formulario de creación de usuario','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:19:37'),(70,'KEVIN CABALLERO','CREAR','USUARIOS','Usuario creado: VENDEDOR (Login: vendedor, Rol: VENDEDOR)','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"id\":\"6\",\"nombre\":\"VENDEDOR\",\"usuario\":\"vendedor\",\"rol\":\"VENDEDOR\",\"estado\":1}','2025-11-05 01:19:52'),(71,'KEVIN CABALLERO','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-05 01:19:55'),(72,'VENDEDOR','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 01:20:03'),(73,'VENDEDOR','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:20:03'),(74,'VENDEDOR','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-05 01:20:15'),(75,'KEVIN CABALLERO','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 01:32:24'),(76,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 01:32:24'),(77,'KEVIN CABALLERO','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-05 01:32:32'),(78,'KEVIN CABALLERO','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 16:06:24'),(79,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:06:24'),(80,'KEVIN CABALLERO','ACCESO','USUARIOS','Acceso al listado de usuarios','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:06:37'),(81,'KEVIN CABALLERO','ACCESO','USUARIOS','Acceso a edición de usuario: KEVIN CABALLERO 4','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:06:54'),(82,'KEVIN CABALLERO','ACCESO','USUARIOS','Acceso al listado de usuarios','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:07:07'),(83,'KEVIN CABALLERO','ACCESO','USUARIOS','Acceso a edición de usuario: KEVIN CABALLERO 4','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:07:12'),(84,'KEVIN CABALLERO','ACCESO','USUARIOS','Acceso a edición de usuario: KEVIN CABALLERO 3','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:07:15'),(85,'KEVIN CABALLERO','ACCESO','USUARIOS','Acceso al listado de usuarios','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:07:53'),(86,'KEVIN CABALLERO','ACCESO','USUARIOS','Acceso a edición de usuario: KEVIN CABALLERO 2','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:07:59'),(87,'KEVIN CABALLERO','EDITAR','USUARIOS','Usuario editado: KEVIN CABALLERO 2 (ID: 3) - Cambios: Usuario: \'kevin2\' → \'kevinsec\', Contraseña actualizada','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','{\"nombre\":\"KEVIN CABALLERO 2\",\"usuario\":\"kevin2\",\"rol\":\"ADMINISTRADOR\",\"estado\":1}','{\"nombre\":\"KEVIN CABALLERO 2\",\"usuario\":\"kevinsec\",\"rol\":\"ADMINISTRADOR\",\"estado\":1,\"password_cambiado\":true}','2025-11-05 16:08:17'),(88,'KEVIN CABALLERO','ACCESO','USUARIOS','Acceso al listado de usuarios','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:08:19'),(89,'KEVIN CABALLERO','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-05 16:08:25'),(90,'KEVIN CABALLERO 2','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 16:08:33'),(91,'KEVIN CABALLERO 2','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:08:33'),(92,'KEVIN CABALLERO 2','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-05 16:08:38'),(93,'Administrador','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 16:18:50'),(94,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:18:50'),(95,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:19:49'),(96,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:23:52'),(97,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:24:52'),(98,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:25:59'),(99,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:26:00'),(100,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:26:32'),(101,'Administrador','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:27:05'),(102,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:27:09'),(103,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:27:27'),(104,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:29:39'),(105,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:29:50'),(106,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:30:16'),(107,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:30:20'),(108,'Administrador','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 16:30:48'),(109,'KEVIN CABALLERO','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 19:47:01'),(110,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 19:47:01'),(111,'KEVIN CABALLERO','ACCESO','CAJA','Acceso a apertura de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 19:47:05'),(112,'KEVIN CABALLERO','APERTURA_CAJA','CAJA','Apertura de caja #6 - Saldo inicial: ₲ 100.000','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"id_cierre\":\"6\",\"saldo_inicial\":100000,\"fecha_apertura\":\"2025-11-05 13:47:00\",\"observaciones\":null}','2025-11-05 19:47:12'),(113,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 19:47:23'),(114,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 19:57:23'),(115,'KEVIN CABALLERO','ACCESO','CAJA','Acceso a cierre de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 19:58:24'),(116,'KEVIN CABALLERO','CIERRE_CAJA','CAJA','Cierre de caja #6 - Diferencia: ₲ 0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','{\"saldo_inicial\":100000,\"estado_anterior\":\"ABIERTA\"}','{\"id_cierre\":6,\"saldo_sistema\":100000,\"saldo_fisico\":100000,\"diferencia\":0,\"total_ingresos\":0,\"total_egresos\":0,\"estado_nuevo\":\"CERRADA\"}','2025-11-05 19:58:40'),(117,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 19:58:42'),(118,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 20:13:57'),(119,'KEVIN CABALLERO','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-05 20:14:04'),(120,'KEVIN CABALLERO','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 20:18:19'),(121,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 20:18:19'),(122,'KEVIN CABALLERO','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-05 20:18:24'),(123,'KEVIN CABALLERO','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 20:18:34'),(124,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 20:18:34'),(125,'KEVIN CABALLERO','ACCESO','CAJA','Acceso a apertura de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 20:18:37'),(126,'KEVIN CABALLERO','APERTURA_CAJA','CAJA','Apertura de caja #7 - Saldo inicial: ₲ 0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"id_cierre\":\"7\",\"saldo_inicial\":0,\"fecha_apertura\":\"2025-11-05 14:18:00\",\"observaciones\":null}','2025-11-05 20:18:44'),(127,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 20:18:46'),(128,'KEVIN CABALLERO','ACCESO','CAJA','Acceso a cierre de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 20:19:05'),(129,'KEVIN CABALLERO','CIERRE_CAJA','CAJA','Cierre de caja #7 - Diferencia: ₲ 0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','{\"saldo_inicial\":0,\"estado_anterior\":\"ABIERTA\"}','{\"id_cierre\":7,\"saldo_sistema\":0,\"saldo_fisico\":0,\"diferencia\":0,\"total_ingresos\":0,\"total_egresos\":0,\"estado_nuevo\":\"CERRADA\"}','2025-11-05 20:19:13'),(130,'KEVIN CABALLERO','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-05 20:19:18'),(131,'KEVIN CABALLERO','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-05 20:19:59'),(132,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 20:19:59'),(133,'KEVIN CABALLERO','ACCESO','CAJA','Acceso a apertura de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 20:20:02'),(134,'KEVIN CABALLERO','APERTURA_CAJA','CAJA','Apertura de caja #8 - Saldo inicial: ₲ 0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"id_cierre\":\"8\",\"saldo_inicial\":0,\"fecha_apertura\":\"2025-11-05 14:20:00\",\"observaciones\":null}','2025-11-05 20:20:08'),(135,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 20:20:10'),(136,'KEVIN CABALLERO','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 20:21:51'),(137,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 20:22:20'),(138,'KEVIN CABALLERO','ACCESO','AUDITORIA','Acceso al log de actividades','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 20:22:25'),(139,'KEVIN CABALLERO','ACCESO','USUARIOS','Acceso al formulario de creación de usuario','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 20:23:10'),(140,'KEVIN CABALLERO','ACCESO','USUARIOS','Acceso al listado de usuarios','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-05 20:23:15'),(141,'KEVIN CABALLERO','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-07 21:21:59'),(142,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-07 21:21:59'),(143,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-07 21:22:26'),(144,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-07 21:22:53'),(145,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-07 21:24:37'),(146,'KEVIN CABALLERO','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-07 21:42:58'),(147,'KEVIN CABALLERO','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-07 21:43:05'),(148,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-07 21:43:05'),(149,'KEVIN CABALLERO','BACKUP','SISTEMA','Backup descargado: backup_automatico_2025-11-07_18-43-05.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-07 21:43:31'),(150,'KEVIN CABALLERO','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-09 01:52:08'),(151,'KEVIN CABALLERO','BACKUP','SISTEMA','Backup descargado: backup_automatico_2025-11-08_22-52-08.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-09 01:52:12'),(152,'KEVIN CABALLERO','BACKUP','SISTEMA','Backup descargado: backup_automatico_2025-11-08_22-52-08.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-09 01:53:36'),(153,'KEVIN CABALLERO','BACKUP','SISTEMA','Backup manual generado: backup_manual_2025-11-08_22-56-24.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"archivo\":\"backup_manual_2025-11-08_22-56-24.sql\",\"tamano\":\"92.66 KB\"}','2025-11-09 01:56:25'),(154,'KEVIN CABALLERO','BACKUP','SISTEMA','Backup descargado: backup_manual_2025-11-08_22-56-24.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-09 01:56:28'),(155,'KEVIN CABALLERO','BACKUP','SISTEMA','Backup manual generado: backup_manual_2025-11-08_22-58-12.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"archivo\":\"backup_manual_2025-11-08_22-58-12.sql\",\"tamano\":\"93.24 KB\"}','2025-11-09 01:58:13'),(156,'KEVIN CABALLERO','BACKUP','SISTEMA','Backup manual generado: backup_manual_2025-11-08_22-58-13.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"archivo\":\"backup_manual_2025-11-08_22-58-13.sql\",\"tamano\":\"93.56 KB\"}','2025-11-09 01:58:14'),(157,'KEVIN CABALLERO','BACKUP','SISTEMA','Backup manual generado: backup_manual_2025-11-08_22-58-14.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"archivo\":\"backup_manual_2025-11-08_22-58-14.sql\",\"tamano\":\"93.89 KB\"}','2025-11-09 01:58:14'),(158,'KEVIN CABALLERO','BACKUP','SISTEMA','Backup manual generado: backup_manual_2025-11-08_22-58-15.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,'{\"archivo\":\"backup_manual_2025-11-08_22-58-15.sql\",\"tamano\":\"94.22 KB\"}','2025-11-09 01:58:15'),(159,'KEVIN CABALLERO','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-09 01:58:28'),(160,'KEVIN CABALLERO','LOGIN','SISTEMA','Inicio de sesión exitoso','::1',NULL,NULL,NULL,'2025-11-09 01:58:35'),(161,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-09 01:58:35'),(162,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-09 02:00:22'),(163,'KEVIN CABALLERO','ACCESO','CAJA','Acceso a cierre de caja','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-09 02:00:28'),(164,'KEVIN CABALLERO','CIERRE_CAJA','CAJA','Cierre de caja #8 - Diferencia: ₲ 0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','{\"saldo_inicial\":0,\"estado_anterior\":\"ABIERTA\"}','{\"id_cierre\":8,\"saldo_sistema\":0,\"saldo_fisico\":0,\"diferencia\":0,\"total_ingresos\":0,\"total_egresos\":0,\"estado_nuevo\":\"CERRADA\"}','2025-11-09 02:00:34'),(165,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-09 02:00:36'),(166,'KEVIN CABALLERO','ACCESO','DASHBOARD','Acceso al panel principal','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',NULL,NULL,'2025-11-09 02:03:09'),(167,'KEVIN CABALLERO','LOGOUT','SISTEMA','Cierre de sesión','::1',NULL,NULL,NULL,'2025-11-09 02:03:15');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pagos_compra` WRITE;
/*!40000 ALTER TABLE `pagos_compra` DISABLE KEYS */;
INSERT INTO `pagos_compra` VALUES (2,18,'CONTADO',NULL,NULL,NULL,'2025-10-15 00:49:36'),(4,20,'CREDITO',10,20000.00,'2025-10-14','2025-10-15 20:07:45'),(5,21,'CONTADO',NULL,NULL,NULL,'2025-11-05 01:01:54');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'BIROME BIC AZUL','',3000.00,30,10,1,'2025-09-24 20:39:01',NULL,NULL,'2025-11-05 20:21:02'),(2,'RESMA A4','2343342432',50000.00,340,10,1,'2025-09-25 21:51:26',NULL,NULL,NULL),(3,'RESMA A5+','23456787654',30000.00,430,20,1,'2025-09-30 21:42:56',NULL,NULL,'2025-11-05 01:01:54'),(5,'BORRADOR','4353456789',3000.00,90,5,1,'2025-10-15 00:20:00',NULL,NULL,NULL),(6,'REGLA 30CM','3456734567',5000.00,140,5,1,'2025-10-19 15:19:28',NULL,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `proveedor_producto` WRITE;
/*!40000 ALTER TABLE `proveedor_producto` DISABLE KEYS */;
INSERT INTO `proveedor_producto` VALUES (16,2,3,20000.00,'2025-09-30 21:42:56'),(17,3,3,19000.00,'2025-09-30 21:42:56'),(21,1,1,1000.00,'2025-09-30 21:44:05'),(22,2,2,3000.00,'2025-09-30 21:44:34'),(23,6,2,5000.00,'2025-09-30 21:44:34'),(26,4,3,25000.00,'2025-10-07 23:38:48'),(27,4,1,1500.00,'2025-10-07 23:38:48'),(28,4,2,40000.00,'2025-10-07 23:38:48'),(29,2,5,0.00,'2025-10-15 00:20:00'),(30,3,5,0.00,'2025-10-15 00:20:00'),(31,1,5,0.00,'2025-10-15 00:20:00'),(36,7,5,2000.00,'2025-10-19 14:25:01'),(37,7,2,20000.00,'2025-10-19 14:25:01'),(38,7,3,20000.00,'2025-10-19 14:25:01'),(39,7,1,2000.00,'2025-10-19 14:25:01'),(40,2,6,3000.00,'2025-10-19 15:19:28'),(41,3,6,3000.00,'2025-10-19 15:19:28'),(42,1,6,2000.00,'2025-10-19 15:19:28'),(43,7,6,3000.00,'2025-10-19 15:19:28');
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'ALAMON\'T','13231','K',1,'2025-09-19 00:27:15',NULL,NULL,NULL),(2,'ALAMO ORIGI','13231','',1,'2025-09-19 00:27:52',NULL,NULL,NULL),(3,'ALAMO2','34223',NULL,1,'2025-09-21 01:30:47',NULL,NULL,NULL),(4,'OFIMARKET','1323133','',1,'2025-09-21 05:48:58',NULL,NULL,NULL),(6,'SANTEI','','ECARNAYORK',1,'2025-09-30 00:21:07',NULL,NULL,NULL),(7,'OOOOOO','032032023','DSDSFLDSLKÑSDFKLÑ',1,'2025-10-15 00:21:19',NULL,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `servicios` WRITE;
/*!40000 ALTER TABLE `servicios` DISABLE KEYS */;
INSERT INTO `servicios` VALUES (13,'MANTENIMIENTO DE IMPRESORA','MATEMATICA',0.00,1,'2025-10-08 18:56:54',NULL,NULL,NULL),(14,'MANTENIMIENTO DE LAPTOP','MATEMATICA',0.00,1,'2025-10-08 20:17:21',NULL,NULL,NULL),(15,'MANTENIMIENTO DE PC DE ESCRITORIO','MANTENIMIENTO',0.00,1,'2025-10-19 18:52:54',NULL,NULL,NULL),(16,'IMPRESIóN A COLOR','IMPRESIONES',1000.00,1,'2025-10-20 22:17:56',NULL,NULL,NULL),(17,'FOTOCOPIAS','IMPRESION',250.00,1,'2025-10-20 22:23:14',NULL,NULL,NULL);
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
INSERT INTO `usuarios` VALUES (1,'Administrador','admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','ADMINISTRADOR',1,'2025-11-02 17:16:46','2025-11-05 16:18:50',0,NULL),(2,'KEVIN CABALLERO','kevin','$2y$10$PqxbCX.36728TxpciRKMI.9w4w7lvpXjZYZVjNeMrmSE3FbwVlnBK','ADMINISTRADOR',1,'2025-11-05 01:14:18','2025-11-09 01:58:35',0,NULL),(3,'KEVIN CABALLERO 2','kevinsec','$2y$10$2Ypzb54lDJUG5l71AVn8lezWL5lvV2JmmzTn3MYataua8GdOBrsiW','ADMINISTRADOR',1,'2025-11-05 01:14:35','2025-11-05 16:08:33',0,NULL),(4,'KEVIN CABALLERO 3','kevin3','$2y$10$KhhKFJ63yjP6bZLvc9eqJekujIDPwyVVWsEZ61qEoqw1Oeg1ayogS','ADMINISTRADOR',1,'2025-11-05 01:14:47',NULL,0,NULL),(5,'KEVIN CABALLERO 4','admin2','$2y$10$fXnEE5KuPaxev4ATeBDkDuPxC6a0Yc1cNr9RmLyT/04c4LBzNPWI2','VENDEDOR',1,'2025-11-05 01:15:29',NULL,0,NULL),(6,'VENDEDOR','vendedor','$2y$10$ZQSxSJ7z0M4vctkrWCeV0u1IFVPo2CtEVcxqBXW5NPsVIIfXgIg5S','VENDEDOR',1,'2025-11-05 01:19:52','2025-11-05 01:20:03',0,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES (1,3,0,NULL,NULL,'234567234','CONTADO','CONTADO',1,NULL,'2025-10-12 00:00:00',48000.00,0.00,0.00,NULL,48000.00,'',0,'2025-10-12 18:39:35',NULL,NULL,NULL,NULL),(2,3,0,NULL,NULL,'324456778965','CONTADO','CONTADO',1,NULL,'2025-10-13 00:00:00',430000.00,0.00,0.00,NULL,430000.00,'',0,'2025-10-14 02:03:30',NULL,NULL,NULL,NULL),(3,5,0,NULL,NULL,'4356879','CONTADO','CONTADO',1,NULL,'2025-10-13 00:00:00',560000.00,0.00,0.00,NULL,560000.00,'',0,'2025-10-15 19:56:31',NULL,NULL,NULL,NULL),(4,1,0,NULL,NULL,NULL,'CONTADO','CONTADO',1,NULL,'2025-10-16 00:00:00',3000.00,0.00,0.00,NULL,3000.00,'',1,'2025-10-15 19:57:08',NULL,NULL,NULL,NULL),(5,1,0,'TICKET',NULL,'0000001','CREDITO','CONTADO',5,'2026-02-28','2025-10-19 00:00:00',250000.00,0.00,0.00,NULL,275000.00,'',1,'2025-10-19 23:15:33',NULL,NULL,NULL,NULL),(6,3,1,'FACTURA',NULL,NULL,'CONTADO','CONTADO',NULL,NULL,'2025-10-22 00:00:00',60000.00,0.00,0.00,NULL,66000.00,'',0,'2025-10-22 00:39:29','2025-11-02 21:21:49','wergh','ADMIN',NULL),(7,1,0,'TICKET',NULL,'0000007','CREDITO','TARJETA',12,'2025-10-26','2025-10-22 00:00:00',678250.00,0.00,0.00,NULL,746075.00,'',1,'2025-10-22 21:15:59',NULL,NULL,NULL,NULL),(8,1,1,'FACTURA',NULL,NULL,'CREDITO','TRANSFERENCIA',3,'2025-10-23','2025-10-22 00:00:00',500000.00,0.00,0.00,NULL,550000.00,'',1,'2025-10-22 21:17:48',NULL,NULL,NULL,NULL),(9,3,0,'TICKET',NULL,'0000009','CONTADO','CONTADO',1,NULL,'2025-10-23 00:00:00',51000.00,0.00,0.00,NULL,51000.00,'',0,'2025-10-23 22:55:29',NULL,NULL,NULL,NULL),(10,3,0,'FACTURA',NULL,'001-001-0000835','CONTADO','CONTADO',1,NULL,'2025-10-23 00:00:00',3000.00,0.00,0.00,NULL,3000.00,'',0,'2025-10-23 22:56:26','2025-11-02 21:21:03','prueba444','ADMIN',NULL),(11,7,0,'FACTURA',NULL,'001-001-0000836','CONTADO','CONTADO',1,NULL,'2025-10-24 00:08:00',1130000.00,0.00,0.00,NULL,1130000.00,'',0,'2025-10-24 00:09:48','2025-11-02 16:09:29','prueba23','ADMIN',NULL),(12,7,0,'FACTURA',NULL,'001-001-0000837','CONTADO','CONTADO',1,NULL,'2025-10-24 21:41:00',250000.00,0.00,0.00,NULL,250000.00,'',0,'2025-10-24 21:42:36','2025-11-02 13:21:45','Prueba 1','ADMIN',NULL),(13,7,0,'FACTURA',NULL,'001-001-0000838','CONTADO','CONTADO',1,NULL,'2025-10-24 19:15:00',1575000.00,0.00,0.00,NULL,1575000.00,'',0,'2025-10-24 22:17:40','2025-11-02 16:13:10','wdsfghkjl','ADMIN',NULL),(14,7,0,'FACTURA',NULL,'001-001-0000839','CONTADO','CONTADO',1,NULL,'2025-10-24 19:42:00',27000.00,0.00,0.00,NULL,27000.00,'',0,'2025-10-24 22:43:22','2025-11-02 13:23:03','wery','ADMIN',NULL),(15,7,0,'FACTURA',NULL,'001-001-0000840','CONTADO','CONTADO',1,NULL,'2025-10-29 18:34:00',55000.00,0.00,0.00,NULL,55000.00,'',0,'2025-10-29 21:35:27',NULL,NULL,NULL,NULL),(16,1,0,'FACTURA',NULL,'001-001-0000841','CREDITO','CONTADO',1,NULL,'2025-11-05 16:47:00',30000.00,0.00,0.00,NULL,30000.00,'',0,'2025-11-05 19:48:19','2025-11-05 16:51:11','Anulación de prueba','ADMIN',NULL),(17,NULL,0,NULL,NULL,NULL,'CONTADO','CONTADO',1,NULL,'2025-11-05 16:48:00',20000.00,0.00,0.00,NULL,20000.00,'',1,'2025-11-05 19:48:50',NULL,NULL,NULL,NULL),(18,NULL,0,'TICKET',NULL,'0000018','CONTADO','CONTADO',1,NULL,'2025-11-05 16:49:00',12500.00,0.00,0.00,NULL,12500.00,'',1,'2025-11-05 19:49:31',NULL,NULL,NULL,NULL),(19,1,0,'FACTURA',NULL,'001-001-0000842','CONTADO','CONTADO',1,NULL,'2025-11-05 17:03:00',3000.00,0.00,0.00,NULL,3000.00,'',0,'2025-11-05 20:05:35','2025-11-05 17:05:58','a','ADMIN',NULL),(20,7,0,'FACTURA',NULL,'001-001-0000843','CONTADO','CONTADO',1,NULL,'2025-11-05 17:06:00',3000.00,0.00,0.00,NULL,3000.00,'',0,'2025-11-05 20:06:24','2025-11-05 17:21:02','dsfgh','KEVIN CABALLERO',NULL),(21,6,0,'FACTURA',NULL,'001-001-0000844','CONTADO','CONTADO',1,NULL,'2025-11-05 17:09:00',3000.00,0.00,0.00,NULL,3000.00,'',0,'2025-11-05 20:09:54','2025-11-05 17:13:15','asdfg','KEVIN CABALLERO',NULL);
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

