-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mundogamer_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `administradores`
--

DROP TABLE IF EXISTS `administradores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `administradores` (
  `id_admin` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `usuario_admin` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `ultimo_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `usuario_admin` (`usuario_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administradores`
--

LOCK TABLES `administradores` WRITE;
/*!40000 ALTER TABLE `administradores` DISABLE KEYS */;
INSERT INTO `administradores` VALUES (5,'Fernando Antonio','Gutierrez Sernaque','Darkmafia','fernandogutierrezsernaque@gmail.com','948525533','$2y$10$ryuxKcbj.EK2a6RhYmIuNOMzney7JgCczU8BFu8zbDLiPIAVs.d86','2025-10-22 01:52:50','activo','2025-12-02 08:00:24');
/*!40000 ALTER TABLE `administradores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `almacen_productos`
--

DROP TABLE IF EXISTS `almacen_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `almacen_productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `almacen_id` int(11) NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_almacen` (`almacen_id`),
  KEY `fk_producto` (`producto_id`),
  CONSTRAINT `fk_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `almacen_productos`
--

LOCK TABLES `almacen_productos` WRITE;
/*!40000 ALTER TABLE `almacen_productos` DISABLE KEYS */;
INSERT INTO `almacen_productos` VALUES (16,4,15),(17,4,16),(18,4,17),(19,4,18),(20,4,19),(21,4,20),(22,4,21),(23,5,22),(24,5,23),(25,5,24),(26,5,25),(27,5,26),(28,5,27),(29,5,28),(30,6,29),(31,6,30),(32,6,31),(33,6,32),(34,6,33),(35,6,34),(36,6,35),(37,7,36),(38,7,37),(39,7,38),(40,7,39),(41,7,40),(42,7,41),(43,7,42),(44,8,43),(45,8,44),(46,8,45),(47,8,46),(48,8,47),(49,8,48),(50,8,49),(65,3,8),(66,3,9),(67,3,10),(68,3,11),(69,3,12),(70,3,13),(71,3,14),(79,9,50),(80,9,51),(81,9,52),(82,9,53),(83,9,54),(84,9,55),(85,9,56),(93,2,1),(94,2,2),(95,2,3),(96,2,4),(97,2,5),(98,2,6),(99,2,7);
/*!40000 ALTER TABLE `almacen_productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `almacenes`
--

DROP TABLE IF EXISTS `almacenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `almacenes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trabajador_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ubicacion` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `stock` int(11) NOT NULL,
  `fecha_registro` date NOT NULL,
  `fecha_actualizacion` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_trabajador` (`trabajador_id`),
  CONSTRAINT `fk_trabajador` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `almacenes`
--

LOCK TABLES `almacenes` WRITE;
/*!40000 ALTER TABLE `almacenes` DISABLE KEYS */;
INSERT INTO `almacenes` VALUES (2,9,'Paradise Gamer&amp;amp;#039;s','Av. Alfredo Benavides 334, Miraflores','(01) 612-3456',100000,'2019-09-03','2025-11-26'),(3,15,'Pixel Play','C.C. Mega Plaza, Av. Alfredo Mendiola 3698, Independencia','(01) 723-4567',100000,'2019-09-08','2025-11-18'),(4,30,'Arequipa Arcade','Calle Mercaderes 210, Arequipa','(054) 43-2109',100000,'2019-09-13','2025-10-24'),(5,40,'Chiclayo Gamer Center','Av. Balta 230, Chiclayo','(074) 98-7654',100000,'2019-09-18','2025-10-24'),(6,47,'Tacna T-Game','Av. San Martín 650, Tacna','(052) 78-9012',100000,'2019-09-23','2025-10-24'),(7,48,'Ica Impulse','Av. San Martín 892, Ica','(056) 65-4321',100000,'2019-09-30','2025-10-24'),(8,49,'Ayacucho Advance','Jr. Ayacucho 568, Ayacucho','(066) 12-3456',100000,'2019-10-04','2025-10-24'),(9,50,'El Dorado Gaming','Avenida de los Héroes 1337, distrito de Miraflores','(01) 456 - 7890',100000,'2019-09-30','2025-11-18');
/*!40000 ALTER TABLE `almacenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calificaciones`
--

DROP TABLE IF EXISTS `calificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calificaciones` (
  `id_calificacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_producto` int(10) unsigned NOT NULL,
  `puntuacion` tinyint(4) NOT NULL CHECK (`puntuacion` between 1 and 5),
  `comentario` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_calificacion`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `calificaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `calificaciones_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calificaciones`
--

LOCK TABLES `calificaciones` WRITE;
/*!40000 ALTER TABLE `calificaciones` DISABLE KEYS */;
INSERT INTO `calificaciones` VALUES (1,1,1,5,'Me encanta este juego esta bueno para pasarla solo y con amigos','2025-10-22 15:01:37'),(2,1,55,2,'Este juego es poronga','2025-10-28 03:50:31');
/*!40000 ALTER TABLE `calificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrito`
--

DROP TABLE IF EXISTS `carrito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_producto` int(10) unsigned NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_carrito`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrito`
--

LOCK TABLES `carrito` WRITE;
/*!40000 ALTER TABLE `carrito` DISABLE KEYS */;
/*!40000 ALTER TABLE `carrito` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_venta`
--

DROP TABLE IF EXISTS `detalle_venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_venta` (
  `id_detalle` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_venta` int(10) unsigned NOT NULL,
  `id_producto` int(10) unsigned NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS (`cantidad` * `precio_unitario`) STORED,
  PRIMARY KEY (`id_detalle`),
  KEY `id_venta` (`id_venta`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `detalle_venta_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON DELETE CASCADE,
  CONSTRAINT `detalle_venta_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_venta`
--

LOCK TABLES `detalle_venta` WRITE;
/*!40000 ALTER TABLE `detalle_venta` DISABLE KEYS */;
INSERT INTO `detalle_venta` VALUES (12,12,56,2,76.31,152.62);
/*!40000 ALTER TABLE `detalle_venta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gestion_clientes`
--

DROP TABLE IF EXISTS `gestion_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gestion_clientes` (
  `id_gestion` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `estado` enum('Activo','Inactivo','Suspendido','Baneado') DEFAULT 'Activo',
  `fecha_suspension_inicio` date DEFAULT NULL,
  `fecha_suspension_fin` date DEFAULT NULL,
  PRIMARY KEY (`id_gestion`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `gestion_clientes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gestion_clientes`
--

LOCK TABLES `gestion_clientes` WRITE;
/*!40000 ALTER TABLE `gestion_clientes` DISABLE KEYS */;
INSERT INTO `gestion_clientes` VALUES (2,1,'Activo','2025-10-24','2025-10-29'),(3,2,'Activo',NULL,NULL),(4,3,'Activo',NULL,NULL),(5,4,'Activo',NULL,NULL),(6,5,'Activo',NULL,NULL),(7,6,'Activo',NULL,NULL),(8,7,'Activo',NULL,NULL),(9,8,'Activo',NULL,NULL),(10,9,'Activo',NULL,NULL),(11,10,'Activo',NULL,NULL),(12,11,'Activo',NULL,NULL),(13,12,'Activo',NULL,NULL),(14,13,'Activo',NULL,NULL),(15,14,'Activo',NULL,NULL),(16,15,'Activo',NULL,NULL),(17,16,'Activo',NULL,NULL),(18,17,'Activo',NULL,NULL),(19,18,'Activo',NULL,NULL),(20,19,'Activo',NULL,NULL),(21,20,'Activo',NULL,NULL),(22,32,'Activo',NULL,NULL);
/*!40000 ALTER TABLE `gestion_clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos` (
  `id_pago` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_venta` int(10) unsigned NOT NULL,
  `metodo` enum('Tarjeta','Yape','Plin','Efectivo') NOT NULL,
  `codigo_transaccion` varchar(100) DEFAULT NULL,
  `monto_pagado` decimal(10,2) NOT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pago`),
  KEY `id_venta` (`id_venta`),
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
INSERT INTO `pagos` VALUES (11,12,'','TRX691b525681777',129.73,'2025-11-17 16:50:30');
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productos` (
  `id_producto` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `genero` varchar(100) NOT NULL,
  `id_proveedor` int(10) unsigned NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `plataforma` varchar(100) NOT NULL,
  `fecha_lanzamiento` date NOT NULL,
  `rating_promedio` decimal(2,1) NOT NULL DEFAULT 0.0,
  `imagen` varchar(500) DEFAULT NULL,
  `estado` enum('activo','descontinuado') NOT NULL DEFAULT 'activo',
  `vip` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_producto`),
  KEY `id_proveedor` (`id_proveedor`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'Terraria','Acción y Aventura',1,'Explora, construye y lucha en un mundo 2D lleno de secretos, jefes y aventuras infinitas. Terraria combina supervivencia, construcción y RPG de manera muy adictiva.',9.00,'PC','2022-01-01',5.0,'https://cdn.cloudflare.steamstatic.com/steam/apps/105600/header.jpg','activo',0,'2025-10-15 20:38:49'),(2,'Counter Strike 1.6','Shooter',1,'Es un videojuego de disparos en primera persona (FPS) multijugador, muy popular y de culto, desarrollado por Valve',6.28,'PC','2022-01-20',4.8,'https://shared.fastly.steamstatic.com/store_item_assets/steam/subs/7/header_586x192.jpg?t=1447444801','activo',0,'2025-10-23 19:23:24'),(3,'Counter Strike: Codition-Zero','Shooter',1,'Es un videojuego de disparos en primera persona. Es una nueva versión de Counter Strike con gráficos mejorados, mapas retocados, añade dos modelos nuevos y un modo para un solo jugador',6.28,'PC','2022-02-08',3.9,'https://m.media-amazon.com/images/M/MV5BZjNmMzk2ZDQtMWZhMi00MGNjLTg0N2QtNGRlMWJhN2EyOTE0XkEyXkFqcGc@._V1_.jpg','activo',0,'2025-10-23 19:24:58'),(4,'Counter-Strike: Source','Shooter',1,'Es una versión mejorada del clásico Counter-Strike 1.6 al motor gráfico Source de Valve. Esto le dio gráficos modernos y un motor de físicas que permitía una mayor interactividad con el entorno',11.52,'PC','2022-02-22',4.9,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/240/header.jpg?t=1745368575','activo',0,'2025-10-23 19:27:39'),(5,'Counter-Strike 2','Shooter',1,'Es la última entrega de la serie. No se trata de un juego completamente nuevo, sino de una actualización gratuita y masiva que reemplazó a Counter-Strike: Global Offensive',0.00,'PC','2023-09-07',4.2,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/730/header.jpg?t=1749053861','activo',0,'2025-10-23 19:29:00'),(6,'Half-Life','Shooter, Acción y Aventura',1,'Juega como Gordon Freeman y sobrevive a un desastre científico en Black Mesa enfrentando alienígenas y fuerzas militares.',6.28,'PC','2022-03-05',5.0,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/70/header.jpg?t=1745368462','activo',0,'2025-10-23 19:31:41'),(7,'Black Mesa','Shooter, Acción y Aventura',1,'Remake moderno de Half-Life con gráficos actualizados y mejoras en la jugabilidad',12.28,'PC','2022-03-30',4.5,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/362890/674b9a0e7b31996efd5032dc15e695dbf457d71d/header.jpg?t=1747193649','activo',1,'2025-10-23 19:32:47'),(8,'Half-Life: Blue Shift','Shooter, Acción y Aventura',1,'Juega como Barney Calhoun, un guardia de seguridad en Black Mesa, y experimenta los eventos desde una perspectiva diferente',3.43,'PC','2022-04-12',3.8,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/130/header.jpg?t=1745368541','activo',0,'2025-10-23 19:34:20'),(9,'Half-Life: Opposing Force','Shooter, Acción y Aventura',1,'Asume el papel de Adrian Shephard, un soldado enviado a Black Mesa para eliminar a Gordon Freeman, y enfrenta nuevas amenazas alienígenas',3.43,'PC','2022-04-30',4.0,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/50/header.jpg?t=1745368539','activo',0,'2025-10-23 19:35:39'),(10,'Half-Life: Alyx','Shooter, Acción, Aventura y VR',1,'Experiencia inmersiva en realidad virtual como Alyx Vance luchando contra la ocupación de los Combine en City 17',32.83,'PC','2022-05-06',5.0,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/546560/header.jpg?t=1673391297','activo',0,'2025-10-23 19:36:47'),(11,'Half-Life 2','Shooter, Acción y Aventura',1,'Gordon Freeman regresa en un mundo controlado por la opresiva Combine, con física avanzada y combates intensos',6.57,'PC','2022-05-29',4.8,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/220/header.jpg?t=1745368545','activo',0,'2025-10-23 19:37:56'),(12,'Portal','Lógica y Plataformas',1,'Es un videojuego de puzles en primera persona para un jugador, donde el jugador controla a Chell para resolver desafíos en las instalaciones de Aperture Science usando una \"pistola de portales\" para crear portales interconectados en paredes',6.57,'PC','2022-06-05',4.2,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/400/header.jpg?t=1745368554','activo',0,'2025-10-23 19:38:51'),(13,'Portal 2','Lógica y Plataformas',1,'Es un videojuego de puzles en primera persona que expande la mecánica de su predecesor, con una pistola que crea portales para resolver pruebas en instalaciones abandonadas, incluyendo una historia para un jugador y un modo cooperativo con robots que deben colaborar',6.57,'PC','2022-06-15',4.9,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/620/header.jpg?t=1745363004','activo',0,'2025-10-23 19:40:53'),(14,'Half-Life 2: Episode One','Shooter, Acción y Aventura',1,'Continúa la historia de Half-Life 2 con nuevos desafíos y enemigos tras la caída de City 17',12.28,'PC','2022-06-30',4.1,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/380/header.jpg?t=1745368553','activo',0,'2025-10-23 19:41:46'),(15,'Half-Life 2: Episode Two','Shooter, Acción y Aventura',1,'Concluye la saga de episodios de Half-Life 2 con emocionantes escenarios y enemigos',12.28,'PC','2022-07-14',4.0,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/420/header.jpg?t=1745368556','activo',0,'2025-10-23 19:42:39'),(16,'Fornite','Supervivencia, Mundo abierto, Battle Royale',5,'Es un videojuego en línea multijugador s que incluye un modo \"batalla real\" donde 100 jugadores compiten en una isla hasta que solo uno o un equipo permanezca en pie',0.00,'PC, PlayStation','2022-07-31',5.0,'https://cdn1.epicgames.com/offer/fn/FNBR_37-00_C6S4_EGS_Launcher_KeyArt_FNLogo_Carousel_PDP_2560x1440_logo_2560x1440-04348f5d3d52391f572e8c1050ddc737','activo',0,'2025-10-23 19:51:23'),(17,'Red Dead Redemption','Acción-Aventura, Mundo abierto, Disparos en tercera persona',2,'Es un videojuego western de mundo abierto que sigue a John Marston, un exforajido, mientras es forzado por el gobierno a cazar y eliminar a miembros de su antigua banda en el año 1911, un periodo de transición entre el Viejo Oeste y el México post-revolucionario',52.82,'PC, PlayStation','2022-05-18',4.9,'https://gaming-cdn.com/images/products/16200/616x353/red-dead-redemption-playstation-4-juego-playstation-store-cover.jpg?v=1730298578','activo',1,'2025-10-23 19:52:34'),(18,'Red Dead Redemption 2','Acción-Aventura, Mundo abierto, Disparos en tercera persona',2,'Es una precuela de acción-aventura ambientada en 1899, donde Arthur Morgan y la banda de Van der Linde huyen tras un atraco fallido, enfrentándose a agentes federales y cazarrecompensas mientras la banda se desintegra',102.76,'PC, PlayStation','2022-08-31',5.0,'https://cdn2.unrealengine.com/Diesel%2Fproductv2%2Fheather%2Fhome%2FEGS_RockstarGames_RedDeadRedemption2_G1A_00-1920x1080-308f101576da37225c889173094f373f2afc56c1.jpg','activo',1,'2025-10-23 19:55:00'),(19,'Grand Theft Auto: The Trilogy – The Definitive Edition','Acción y Aventura, Mundo abierto y Disparos en tercera persona',2,'Tres ciudades famosas, tres historias épicas. Juega a los clásicos que definieron un género de la trilogía original: Grand Theft Auto: III, Grand Theft Auto: Vice City y Grand Theft Auto: San Andreas, actualizados para la nueva generación, ahora con mejoras a todos los niveles, como nueva y deslumbrante iluminación, retoques en el entorno, texturas de alta resolución, mayores distancias visuales, controles y muchas cosas más que harán que cobren vida con niveles de detalle inéditos',58.22,'PC, PlayStation','2022-09-08',5.0,'https://images.ctfassets.net/wn7ipiv9ue5v/3ITuNHfrIBvbxkE3ukAUbJ/6e173dfc97d2ccbe5f8f42910dbdcf96/GTAT_RockstarStore_HeroNoLogo_3840x2160_Deliverable.jpg?w=1920&h=&fm=avif&q=75','activo',1,'2025-10-23 19:57:00'),(20,'Grand Theft Auto IV: Complete Edition','Acción y Aventura, Mundo abierto y Disparos en tercera persona',2,'Incluye Grand Theft Auto IV y las expansiones The Lost and Damned y The Ballad of Gay Tony. Vive tres historias entrelazadas en Liberty City',19.39,'PC','2022-09-23',4.4,'https://cdn.cloudflare.steamstatic.com/steam/apps/12210/header.jpg?t=1686151350','activo',1,'2025-10-23 19:57:55'),(21,'Grand Theft Auto V','Acción y Aventura, Mundo abierto y Disparos en tercera persona',2,'Juego de acción y mundo abierto donde puedes explorar Los Santos, cumplir misiones y participar en el modo online con amigos',31.66,'PC, PlayStation','2022-10-19',4.5,'https://media.rockstargames.com/rockstargames-newsite/uploads/e4e67668228df3eb050e64232a664f454ab7b030.jpg','activo',1,'2025-10-23 19:58:48'),(22,'Zenless Zone Zero','Rol de acción, Ciencia ficción, Fantasía urbana',3,'RPG de acción futurista ambientado en New Eridu, donde los jugadores asumen el rol de un Proxy guiando a agentes a través de los peligrosos Hollows',0.00,'PC, PlayStation','2022-11-10',4.8,'https://i0.wp.com/www.pcmrace.com/wp-content/uploads/2024/06/Zenless-Zone-Zero_2024_06-28-24_011.jpg?zoom=2&resize=750%2C400&ssl=1','activo',0,'2025-10-23 20:00:03'),(23,'Dying Light Definitive Edition','Aventura, Pelea, Disparos y Plataformas',5,'Sobrevive en una ciudad infestada de zombis usando parkour, combate cuerpo a cuerpo y armas improvisadas',45.40,'PC, PlayStation','2022-11-29',4.3,'https://cdn1.epicgames.com/offer/2c42520d342a46d7a6e0cfa77b4715de/EGS_DyingLightDefinitiveEdition_Techland_Editions_S1_2560x1440-17c03be0bf68fc0f1b4a212fcdd469fd','activo',1,'2025-10-23 20:01:03'),(24,'Dying Light 2: Stay Human Edition Veraniega','Aventura, Pelea, Disparos y Plataformas',5,'Es una versión del juego que incluye extras digitales, el lote del traje del SAI y 2800 puntos de DL para desbloquear armas, aspectos y otros contenidos, todo esto ambientado en el trepidante mundo postapocalíptico del juego',94.22,'PC, PlayStation','2022-12-13',4.2,'https://cdn2.unrealengine.com/egs-dyinglight2stayhumansummeredition-techland-editions-g1a-00-1920x1080-9fe07939c5dd.jpg','activo',1,'2025-10-23 20:02:02'),(25,'Hollow Knight','Acción, Aventura, Metroidvania y Pelea',6,'Es un videojuego Metroidvania de acción y aventura en 2D donde un silencioso caballero insecto explora un vasto reino subterráneo en ruinas, Hallownest, luchando contra criaturas corrompidas y descubriendo antiguos secretos',14.99,'PC, PlayStation, Nintendo Switch, Xbox','2023-11-05',4.9,'https://cdn.cloudflare.steamstatic.com/steam/apps/367520/header.jpg','activo',1,'2025-10-23 20:02:58'),(26,'Hollow Knight: Silksong','Acción, Aventura, Metroidvania y Pelea',6,'Es un Metroidvania de acción y aventura que se desarrolla en un reino nuevo y vibrante, donde los jugadores controlan a Hornet, una princesa cautiva que debe ascender una brillante ciudadela para descubrir la fuente de una misteriosa amenaza',19.99,'PC, PlayStation, Nintendo Switch, Xbox','2025-09-04',5.0,'https://assets.nintendo.com/image/upload/ar_16:9,c_lpad,w_1240/b_white/f_auto/q_auto/ncom/software/switch/70010000020840/60eebc8f7133f685eddbffbe43c8da617ba0a5d699f2008f9c31c6119d1792af','activo',1,'2025-10-23 20:05:16'),(27,'Control','Disparos en tercera persona, Acción',5,'Sumérgete en la Agencia Federal de Control, llena de fenómenos paranormales. Usa poderes sobrenaturales y armas transformables',29.99,'PC','2025-04-15',4.5,'https://cdn.cloudflare.steamstatic.com/steam/apps/870780/header.jpg','activo',0,'2025-10-23 20:19:16'),(28,'God of War','Acción-Aventura Hack and Slash',7,'Un juego de acción y aventura donde Kratos, un guerrero espartano, busca venganza contra los dioses del Olimpo',25.37,'PlayStation','2025-04-20',4.9,'https://i.pinimg.com/736x/f8/c9/12/f8c912cb0adda053b2dbb89ac6b9bc09.jpg','activo',0,'2025-10-23 20:38:32'),(29,'God of War 2','Acción-Aventura Hack and Slash',7,'La continuación de la historia de Kratos mientras desafía a los dioses del Olimpo en busca de venganza',49.99,'PlayStation','2025-04-26',4.9,'https://gmedia.playstation.com/is/image/SIEPDC/god-of-war-hub-thumbnail-gow-ii-en-29jul21?$2400px$','activo',0,'2025-10-23 20:51:40'),(30,'God of War: Chains of Olympus','Acción-Aventura Hack and Slash',7,'Una precuela que sigue a Kratos mientras lucha contra dioses y monstruos en el inframundo',20.99,'PlayStation','2025-04-30',4.4,'https://i.pinimg.com/736x/b4/13/d7/b413d7ffdaf77b8cd944119026c06a0b.jpg','activo',0,'2025-10-23 21:02:33'),(31,'God of War 3','Acción-Aventura Hack and Slash',7,'Kratos lleva su batalla final contra los dioses del Olimpo, enfrentando desafíos aún mayores y buscando una nueva vida',19.99,'PlayStation','2025-05-05',5.0,'https://gmedia.playstation.com/is/image/SIEPDC/god-of-war-3-remastered-two-column-01-en-ps4-2Dec20?$2400px$','activo',0,'2025-10-23 21:11:35'),(32,'God of War: Ghost of Sparta','Acción-Aventura Hack and Slash',7,'Kratos continúa su viaje por el mundo de los dioses mientras enfrenta sus propios demonios personales',23.50,'PlayStation','2025-05-10',4.2,'https://gmedia.playstation.com/is/image/SIEPDC/god-of-war-hub-thumbnail-ghost-of-sparta-en-29jul21?$2400px$','activo',0,'2025-10-23 21:16:01'),(33,'God of War: Ascension','Acción-Aventura Hack and Slash',7,'Una precuela que explora los eventos que llevaron a Kratos a desafiar a los dioses del Olimpo',59.99,'PlayStation','2025-05-16',4.0,'https://gmedia.playstation.com/is/image/SIEPDC/god-of-war-hub-thumbnail-ascension-en-29jul21?$2400px$','activo',0,'2025-10-23 21:18:44'),(34,'God of War','Acción-Aventura Hack and Slash',7,'Kratos regresa en un nuevo mundo mitológico, enfrentando nuevos desafíos y buscando proteger a su hijo Atreus',29.99,'PC, PlayStation','2025-05-20',5.0,'https://image.api.playstation.com/vulcan/img/rnd/202010/2217/ax0V5TYMax06mLzmkWeQMiwH.jpg?w=780&thumb=false','activo',1,'2025-10-23 21:22:06'),(35,'God of War: Ragnarok','Acción-Aventura Hack and Slash',7,'La continuación de la épica historia de Kratos y Atreus, luchando contra los dioses del norte y enfrentando el Ragnarok',79.99,'PC, PlayStation','2025-05-26',5.0,'https://image.api.playstation.com/vulcan/ap/rnd/202207/1117/qpCTTb74VvcbqDjxkBmY4i1G.jpg?w=780&thumb=false','activo',1,'2025-10-23 21:25:23'),(36,'Call of Duty','Disparos en primera persona,  Aventura, Acción',4,'El inicio de una de las sagas más grandes, ambientado en la Segunda Guerra Mundial',21.51,'PC','2025-05-30',4.8,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2620/header.jpg?t=1731699308','activo',0,'2025-10-24 08:25:09'),(37,'Call of Duty 2','Disparos en primera persona, Aventura, Acción',4,'Expande la experiencia bélica con múltiples campañas en distintos frentes',21.51,'PC','2025-06-04',4.5,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2630/header.jpg?t=1731698337','activo',0,'2025-10-24 08:26:52'),(38,'Call of Duty 3','Disparos en primera persona, Aventura, Acción',4,'Continúa la acción de la Segunda Guerra Mundial centrada en consolas',21.51,'PC, PlayStation','2025-06-12',4.0,'https://media.vandal.net/ivandal/12/63/1200x630/56/5655/20081821443_1.jpg','activo',0,'2025-10-24 08:39:17'),(39,'Call of Duty: World at War','Disparos en primera persona, Aventura, Acción',4,'Combates brutales en el Pacífico y Europa del Este durante la Segunda Guerra Mundial',21.51,'PC, PlayStation','2025-06-18',4.8,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/10090/header.jpg?t=1654830025','activo',0,'2025-10-24 08:44:56'),(40,'Call of Duty: Modern Warfare','Disparos en primera persona, Aventura, Acción',4,'Reinvención moderna con campaña intensa y multijugador competitivo',26.52,'PC, PlayStation','2025-06-20',4.8,'https://blizzstoreperu.com/cdn/shop/products/1784886-box_cod4mwds.png?v=1672877638','activo',0,'2025-10-24 08:48:52'),(41,'Call of Duty: Modern Warfare 2','Disparos en primera persona, Aventura, Acción',4,'Una entrega clásica con misiones memorables y acción intensa',42.72,'PC, PlayStation','2025-06-25',5.0,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/10180/header.jpg?t=1748044299','activo',0,'2025-10-24 08:51:59'),(42,'Call of Duty: Black Ops','Disparos en primera persona, Aventura, Acción',4,'Espionaje en la Guerra Fría y el inicio del modo zombis clásico',42.72,'PC, PlayStation','2025-06-28',5.0,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/42700/header.jpg?t=1748040520','activo',1,'2025-10-24 08:53:49'),(43,'Call of Duty: Modern Warfare 3','Disparos en primera persona, Aventura, Acción',4,'El final épico de la trilogía MW con una campaña explosiva',39.99,'PC, PlayStation','2025-06-30',4.5,'https://zeroxgames.gg/wp-content/uploads/2025/08/imgi_95_imgi_6_cod_mw3.webp','activo',0,'2025-10-24 08:56:31'),(44,'Call of Duty: Black Ops 2','Disparos en primera persona, Aventura, Acción',4,'Combina escenarios futuristas con elecciones narrativas',63.93,'PC, PlayStation','2025-07-02',4.8,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/202970/header.jpg?t=1748037715','activo',1,'2025-10-24 08:58:03'),(45,'Call of Duty: Ghosts','Disparos en primera persona, Aventura, Acción',4,'Una historia postapocalíptica con mecánicas de sigilo y acción táctica',63.93,'PC, PlayStation','2025-07-06',4.0,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/209160/header.jpg?t=1748043672','activo',0,'2025-10-24 08:59:44'),(46,'Call of Duty: Advanced Warfare','Disparos en primera persona, Aventura, Acción',4,'Explora el combate futurista con trajes exoesqueleto y narrativa impactante',39.99,'PC, PlayStation','2025-07-10',4.0,'https://upload.wikimedia.org/wikipedia/en/3/3b/Advanced_Warfare.jpg','activo',0,'2025-10-24 09:02:13'),(47,'Call of Duty: Black Ops 3','Disparos en primera persona, Aventura, Acción',4,'Introduce mejoras cibernéticas, zombis y una historia intensa',63.93,'PC, PlayStation','2025-07-14',4.8,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/311210/header.jpg?t=1748022663','activo',1,'2025-10-24 09:04:08'),(48,'Call of Duty: Infinite Warfare','Disparos en primera persona, Aventura, Acción',4,'Viaja al espacio en esta entrega con combate futurista y batallas estelares',63.93,'PC, PlayStation','2025-07-18',3.8,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/292730/header.jpg?t=1755558170','activo',0,'2025-10-24 09:05:36'),(49,'Call of Duty: WWII','Disparos en primera persona, Aventura, Acción',4,'Regresa a los orígenes con un enfoque realista de la Segunda Guerra Mundial',63.93,'PC, PlayStation','2025-07-22',4.0,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/476600/header.jpg?t=1751302904','activo',0,'2025-10-24 09:07:07'),(50,'Call of Duty: Black Ops Cold War','	Disparos en primera persona, Aventura, Acción',4,'Misiones encubiertas durante la Guerra Fría, conectando con Warzone y zombis',63.93,'PC, PlayStation','2025-07-25',4.2,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1985810/header.jpg?t=1731607699','activo',0,'2025-10-24 09:12:56'),(51,'Call of Duty: Black Ops 4','Disparos en primera persona, Aventura, Acción',4,'Ofrece intensos modos multijugador, zombies y el battle royale Blackout',59.99,'PC, PlayStation','2025-07-30',4.7,'https://image.api.playstation.com/vulcan/img/cfn/11307CjjUZ9rA_whmJUghJsG9Hl1-rmnOUTk3-nccj01ZpYMCHrJ8k8kzBrVyp-p-iCPej73TEJAs88ZBeiZ1uirtj0fsa16.png?w=780&thumb=false','activo',1,'2025-10-24 09:17:24'),(52,'Call of Duty: Warzone','Disparos en primera persona, Battle Royale',4,'Modo battle royale gratuito de la saga Call of Duty, con mapas enormes y tiroteos intensos',0.00,'PC, PlayStation','2025-08-03',3.2,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1962663/54bd6a40eb3759aca46966aadd4c4d0d84b2713e/header.jpg?t=1760458556','activo',0,'2025-10-24 09:19:23'),(53,'Call of Duty: Modern Warfare 2','Disparos en primera persona, Aventura, Acción',4,'Una historia épica con acción moderna, mapas multijugador icónicos y emocionantes modos cooperativos',75.13,'PC, PlayStation','2025-08-06',3.2,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3595230/ce4d5e53b36cb9d3c4309d1df72bf8663bbbc7ef/header.jpg?t=1755227025','activo',1,'2025-10-24 09:20:56'),(54,'Call of Duty: Vanguard','Disparos en primera persona, Aventura, Acción',4,'Ambientado en distintos frentes de la Segunda Guerra Mundial, con multijugador dinámico',63.93,'PC, PlayStation','2025-08-10',4.0,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1985820/header.jpg?t=1731607702','activo',1,'2025-10-24 09:22:45'),(55,'Call of Duty: Modern Warfare 3','Disparos en primera persona, Aventura, Acción',4,'Intensa continuación de la saga MW, con combates explosivos y narrativa cinematográfica',75.13,'PC, PlayStation','2025-08-14',3.5,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3595270/7d0f21912a075c33bbb5ea558100e187ceb234ac/header.jpg?t=1758060267','activo',1,'2025-10-24 09:24:52'),(56,'Call of Duty: Black Ops 6','Disparos en primera persona, Aventura, Acción',4,'Un thriller de espionaje lleno de intriga y desconfianza, con un enfoque en la conspiración política, espionaje ambientado a principios de la década de 1990',76.31,'PC, PlayStation','2025-08-20',3.0,'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2933620/header.jpg?t=1760466943','activo',1,'2025-10-24 09:28:14');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proveedores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa` varchar(255) NOT NULL,
  `ruc` varchar(50) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `paga` varchar(100) DEFAULT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'Steam','208889656','+1 (800) 123-4568','soporte@steampowered.com','EEUU, Florida','70000','2025-10-15 19:10:24'),(2,'Rockstar Games','20512345678','1-800-456-7890','soporte@rockstargames.com','EEUU, Nueva York','80000','2025-10-23 19:45:14'),(3,'Hoyoverse','20601234567','400-666-6312','aetherlumine@hoyoverse.com','China, Shanghái','60000','2025-10-23 19:46:11'),(4,'Activision Blizzard','20608765432','+1 (800) 987-6543','callofdutygamer@activisionblizzard.com','EEUU, Santa Monica','100000','2025-10-23 19:47:02'),(5,'Epic Games','10487654321','1-800-987-6543','contacto@epicgames.com','EEUU, Weston Parkway','90000','2025-10-23 19:47:53'),(6,'Team Cherry','20600000001','+61 8 8231 4333','info@teamcherry.com','Australia, Adelaida','50000','2025-10-23 19:48:28'),(7,'Santa Monica Studio','90123456789','+1 (310) 555-1234','contacto@omegagamelabs.com','Stewart St, USA','90000','2025-10-23 20:09:23');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `soporte_cliente`
--

DROP TABLE IF EXISTS `soporte_cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `soporte_cliente` (
  `id_soporte` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `asunto` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp(),
  `estado` enum('Pendiente','En revisión','Resuelto') DEFAULT 'Pendiente',
  `respuesta_admin` text DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT NULL,
  PRIMARY KEY (`id_soporte`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `soporte_cliente_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `soporte_cliente`
--

LOCK TABLES `soporte_cliente` WRITE;
/*!40000 ALTER TABLE `soporte_cliente` DISABLE KEYS */;
INSERT INTO `soporte_cliente` VALUES (1,1,'Daniel Santiago','Gutierrez Sernaque','danielsantiago@gmail.com','948526655','Problemas con el login','Puse mal mi contraseña y username y pos no me deja entrar','2025-10-22 10:55:42','Resuelto','Ponlo en un blog de notas para que no te olvides','2025-10-22 11:56:36');
/*!40000 ALTER TABLE `soporte_cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trabajadores`
--

DROP TABLE IF EXISTS `trabajadores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trabajadores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `dni` varchar(8) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `fechaNacimiento` date NOT NULL,
  `puesto` varchar(100) NOT NULL,
  `fechaContratacion` date NOT NULL,
  `sueldo` decimal(10,2) NOT NULL,
  `estado` enum('activo','suspendido','vacaciones','despedido') NOT NULL DEFAULT 'activo',
  `fechaDespido` date DEFAULT NULL,
  `fechaInicioVacaciones` date DEFAULT NULL,
  `fechaFinVacaciones` date DEFAULT NULL,
  `liquidacion` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fechaInicioSuspension` date DEFAULT NULL,
  `fechaFinSuspension` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni` (`dni`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trabajadores`
--

LOCK TABLES `trabajadores` WRITE;
/*!40000 ALTER TABLE `trabajadores` DISABLE KEYS */;
INSERT INTO `trabajadores` VALUES (8,'Luis Alberto','Castro Mendoza','87456321','luiscastromendoza@gmail.com','1982-04-06','Administrador de Sistemas','2019-01-12',1544.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:13:07','2025-10-25 00:13:07','0000-00-00','0000-00-00'),(9,'Ana Sofía','Torres Vargas','80234567','anatorresvargas@gmail.com','1991-05-24','Trabajador de Almacén','2019-01-06',428.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:16:27','2025-10-25 00:16:27','0000-00-00','0000-00-00'),(10,'Fernando Antonio','Gutierrez Sernaque','77275373','fernandogutierrezsernaque@gmail.com','1985-02-01','Gerente','2019-02-01',2900.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:33:37','2025-10-25 00:33:37','0000-00-00','0000-00-00'),(11,'Sofía Belén','Vargas Sánchez','82456789','sofiavargassanchez@gmail.com','1985-07-22','Administrador de la Empresa','2019-01-20',1284.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:36:24','2025-10-25 00:36:24','0000-00-00','0000-00-00'),(12,'Carlos Andrés','Gómez Ruiz','83567890','carlosgomezruiz@gmail.com','1986-05-12','Marketing - Gestor de Comunidad','2019-01-24',950.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:38:17','2025-10-25 00:38:17','0000-00-00','0000-00-00'),(13,'María Fernanda','López Pérez','84678901','marialopezperez@gmail.com','1997-04-10','Gerente de Contenido/Productos','2019-01-31',2100.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:40:28','2025-10-25 00:40:28','0000-00-00','0000-00-00'),(14,'Pedro José','Sánchez Navarro','85789012','pedrosanchezs@gmail.com','2000-09-09','Recursos Humanos','2019-02-05',931.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:41:51','2025-10-25 00:41:51','0000-00-00','0000-00-00'),(15,'Laura Camila','Ríos Morales','86890123','laurariosmorales@gmail.com','1992-03-19','Trabajador de Almacén','2019-02-09',428.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:43:27','2025-10-25 00:43:27','0000-00-00','0000-00-00'),(16,'José Daniel','Pérez García','87901234','joseperezgarcia@gmail.com','1982-08-07','Gerente','2019-02-13',3500.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:44:48','2025-10-25 00:44:48','0000-00-00','0000-00-00'),(17,'Isabel Cristina','Mendoza Herrera','88012345','isabelmendozaherrera@gmail.com','1985-05-13','Administrador de Sistemas','2019-02-20',1600.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:46:48','2025-10-25 00:46:48','0000-00-00','0000-00-00'),(18,'David Alejandro','García Romero','89123456','davidgarciaromero@gmail.com','1989-02-28','Administrador de la Empresa','2019-02-25',1300.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:48:17','2025-10-25 00:48:17','0000-00-00','0000-00-00'),(21,'Andrea Carolina','Silva Ortiz','81357904','andreasilvaortiz@gmail.com','1978-06-26','Marketing - Gestor de Comunidad','2019-03-02',950.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:51:48','2025-10-25 00:51:48','0000-00-00','0000-00-00'),(22,'Javier Ernesto','Ruiz Castro','81345678','javierruizcastro@gmail.com','1989-12-11','Marketing - Especialista en Marketing Digital','2019-03-07',1215.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:53:10','2025-10-25 00:53:10','0000-00-00','0000-00-00'),(24,'Elena Patricia','Morales Díaz','83579102','elenamoralesdiaz@gmail.com','2000-04-08','Gerente de Contenido/Productos','2019-03-12',1900.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:55:53','2025-10-25 00:55:53','0000-00-00','0000-00-00'),(26,'Fernando Gabriel','Díaz López','84680213','fernandodiazlopez@gmail.com','1995-10-16','Recursos Humanos','2019-03-18',1000.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 00:57:14','2025-10-25 00:57:14','0000-00-00','0000-00-00'),(28,'Camila Andrea','Rojas Torres','85791350','camilarojastorres@gmail.com','1994-02-25','Gerente','2019-03-24',2100.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:19:47','2025-10-25 01:19:47','0000-00-00','0000-00-00'),(30,'Miguel Ángel','Fernández Vargas','86802461','miguelfernandezvargas@gmail.com','1996-04-04','Trabajador de Almacén','2019-04-01',428.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:21:30','2025-10-25 01:21:30','0000-00-00','0000-00-00'),(32,'Valeria Sofía','Herrera Silva','87913572','valeriaherrerasi@gmail.com','1990-10-13','Administrador de Sistemas','2019-04-06',1300.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:24:16','2025-10-25 01:24:16','0000-00-00','0000-00-00'),(34,'Pablo Enrique','Ortiz Gómez','88024683','pabloortizgomez@gmail.com','1983-01-31','Administrador de la Empresa','2019-04-13',1500.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:26:25','2025-10-25 01:26:25','0000-00-00','0000-00-00'),(36,'Gabriela Alejandra','Romero Sánchez','89135794','gabrielaromerosanchez@gmail.com','1994-08-25','Marketing - Gestor de Comunidad','2019-04-19',950.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:27:56','2025-10-25 01:27:56','0000-00-00','0000-00-00'),(38,'Ricardo Javier','Navarro Mendoza','80246805','ricardonavarromendoza@gmail.com','1998-04-06','Marketing - Especialista en Marketing Digital','2019-04-25',1190.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:33:24','2025-10-25 01:33:24','0000-00-00','0000-00-00'),(39,'Ricardo Andrés','Silva Herrera','70123456','ricardosilvaherrera@gmail.com','1994-06-12','Recursos Humanos','2019-04-30',930.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:36:24','2025-10-25 01:36:24','0000-00-00','0000-00-00'),(40,'Sofía Alejandra','López Castro','71234567','sofialopezcastro@gmail.com','1990-06-13','Trabajador de Almacén','2019-05-06',430.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:38:52','2025-10-25 01:39:47','0000-00-00','0000-00-00'),(41,'Miguel Ángel','Morales Navarro','72345678','miguelmoralesn@gmail.com','1986-05-08','Administrador de Sistemas','2019-05-13',1550.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:41:04','2025-10-25 01:41:04','0000-00-00','0000-00-00'),(42,'Laura Cristina','Vargas Mendoza','73456789','lauravargasm@gmail.com','1999-05-18','Administrador de la Empresa','2019-05-19',1300.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:42:31','2025-10-25 01:42:31','0000-00-00','0000-00-00'),(43,'Daniel Ernesto','García Torres','74567890','danielgarciat@gmail.com','1988-11-02','Marketing - Gestor de Comunidad','2019-05-25',950.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:43:36','2025-10-25 01:43:36','0000-00-00','0000-00-00'),(44,'Carolina Isabel','Pérez Gómez','75678901','carolinaperezg@gmail.com','2001-07-20','Marketing - Especialista en Marketing Digital','2019-05-31',1200.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:45:17','2025-10-25 01:45:17','0000-00-00','0000-00-00'),(45,'Juan José','Ruiz Herrera','76789012','juanruizherrera@gmail.com','1996-09-01','Gerente de Contenido/Productos','2019-06-05',1800.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:46:45','2025-10-25 01:46:45','0000-00-00','0000-00-00'),(46,'Andrea Victoria','Sánchez Silva','77890123','andreasanchezsilva@gmail.com','1987-08-10','Recursos Humanos','2019-06-11',900.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:48:26','2025-10-25 01:48:26','0000-00-00','0000-00-00'),(47,'Pablo Enrique','Rojas Ortiz','78901234','pablorojaso@gmail.com','1986-05-31','Trabajador de Almacén','2019-06-17',420.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:49:39','2025-10-25 01:49:39','0000-00-00','0000-00-00'),(48,'Valeria Fernanda','Castro Díaz','79012345','valeriacastrod@gmail.com','1989-01-24','Trabajador de Almacén','2019-06-22',420.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:51:03','2025-10-25 01:51:03','0000-00-00','0000-00-00'),(49,'Matías Nicolás','Morales Ruiz','80123456','matiasmoralesr@gmail.com','1987-07-21','Trabajador de Almacén','2019-06-26',430.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:52:58','2025-10-25 01:52:58','0000-00-00','0000-00-00'),(50,'Felipe Andrés','Castillo Gómez','82345678','felipeandresc@gmail.com','1985-06-14','Trabajador de Almacén','2019-06-30',430.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:55:46','2025-10-25 01:55:46','0000-00-00','0000-00-00'),(51,'Valentina Belén','Herrera Torres','83456789','valentinabh@gmail.com','2001-05-03','Recursos Humanos','2019-07-01',1100.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 01:59:18','2025-10-25 01:59:18','0000-00-00','0000-00-00'),(52,'Paulina Andrea','Soto Mendoza','85678901','psotomendoza@gmail.com','1993-06-20','Recursos Humanos','2019-07-06',1000.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 02:00:45','2025-10-25 02:00:45','0000-00-00','0000-00-00'),(53,'Benjamín Antonio','Godoy Lara','86789012','benjamingodoyl@gmail.com','1997-02-07','Marketing - Gestor de Comunidad','2019-07-11',950.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 02:01:51','2025-10-25 02:01:51','0000-00-00','0000-00-00'),(54,'Luis Fernando','Ramírez Soto','84561290','luisframirez91@gmail.com','1993-03-15','Economista/Financiero','2019-07-15',5000.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 02:03:08','2025-10-25 02:03:08','0000-00-00','0000-00-00'),(55,'Andrea Nicole','Cárdenas	Quispe','71094328','andreanicolec@gmail.com','1995-07-22','Economista/Financiero','2019-07-20',4500.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 02:06:11','2025-10-25 02:06:11','0000-00-00','0000-00-00'),(56,'Javier Antonio','Mendoza	Flores','90375814','javiermendoza2024@gmail.com','1979-11-10','Economista/Financiero','2019-07-25',4500.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 02:07:21','2025-10-25 02:07:21','0000-00-00','0000-00-00'),(57,'María Isabel','Huamán Pérez','68257001','mariahperez@gmail.com','1996-01-05','Economista/Financiero','2019-07-30',3000.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 02:08:31','2025-10-25 02:08:31','0000-00-00','0000-00-00'),(58,'Carlos Daniel','Gutiérrez	Vargas','79410653','gutierrezcarlosd@gmail.com','1994-04-28','Economista/Financiero','2019-08-05',3500.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 02:10:06','2025-10-25 02:10:06','0000-00-00','0000-00-00'),(59,'Laura Valeria','Castro Salas','92138764','lauravaleriasc@gmail.com','2000-09-19','Economista/Financiero','2019-08-08',3000.00,'activo',NULL,'0000-00-00','0000-00-00',0.00,'2025-10-25 02:10:53','2025-10-25 02:10:53','0000-00-00','0000-00-00');
/*!40000 ALTER TABLE `trabajadores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) NOT NULL,
  `apellido` varchar(30) NOT NULL,
  `username` varchar(20) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `fechaNacimiento` date NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `fechaRegistro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Daniel Santiago','Gutierrez Sernaque','Papa Molida','danielsantiago@gmail.com','948525577','2001-07-01','Francisco Gonzales Burga 420','$2y$10$eanQtNzC87ze8ThuJSTqt.M1FMtb4JSOVNj6mM7J9QzefXcmMICrS','Activo','2025-10-18 14:04:17'),(2,'Elena Sofía','Ramírez Pérez','eramirperez','eramirperez@gmail.com','912345678','1992-07-15','San Isidro, Lima','$2y$10$SXHSthFO1GeSBRIsRVwKO.Y./sBryabt6SGSoegQgJzJChiG60oTe','Activo','2025-10-23 23:39:51'),(3,'Jorge Luis','García López','jgarcialopez','jgarcialopez@gmail.com','923456789','1985-11-23','Vicente de la Vega 423','$2y$10$ftxEuyDS0rJ2yTZfdbnsNuy7CdWQZtPLF3cyGejLzXsMt3kWA/PiS','Activo','2025-10-23 23:42:58'),(4,'María Fernanda','Martínez Soto','mfmartinezsoto','mfmartinezsoto@gmail.com','934567890','2003-01-09','Av. Chiclayo 350','$2y$10$ku6m6KecfQ4ubuqRhAY3AejO3mIgyu955IZfBBVMrwbOgxAlAlM9u','Activo','2025-10-23 23:48:02'),(5,'Carlos Alberto','Díaz Castro','cadiazcastro','cadiazcastro@gmail.com','945678901','1977-09-04','Av. Fernando Belaúnde','$2y$10$fCbeEHf0kqXv5LesT.sUmu5EsktTS9Hd1j1Zv93AQEAPX/auNVb8K','Activo','2025-10-23 23:51:35'),(6,'Ana Victoria','Sánchez Ruiz','avsanchezruiz','avsanchezruiz@gmail.com','956789012','1998-05-28','Av. Sáenz Peña','$2y$10$sAsq6LSUJoIS8MIXkhgmaekI/dySNlGCA.szCcIYpwqNDANMJif6q','Activo','2025-10-24 00:09:31'),(7,'Roberto Andrés','Hernández Gil','rahernandezgil','rahernandezgil@gmail.com','967890123','1963-03-08','Av. Jorge Chávez','$2y$10$m1dSWzHbjpIZ5YOBjgdOn.rXBpurWr3golOQ3fwM.sytwhHLTEgwS','Activo','2025-10-24 00:11:42'),(8,'Laura Patricia','Gómez Morales','lpgomezmorales','lpgomezmorales@gmail.com','978901234','1972-02-17','Enrique Baca Matos','$2y$10$rHSc3BRFuIdZkXoF98jSBuLgSxnCfRK4CaR/jJfUHALvMAoRSbKfG','Activo','2025-10-24 00:14:11'),(9,'Daniel Ricardo','Vargas Flores','drvargasflores','drvargasflores@gmail.com','989012345','1994-06-30','Fernando de Montesinos','$2y$10$JhQ1ttG1HQtebJvXLWavfeOHV7As04ogm/C6IpWYNO8xQQBi3D3YC','Activo','2025-10-24 00:15:36'),(10,'Silvia Beatriz','Torres Núñez','sbtorresnunez','sbtorresnunez@gmail.com','990123456','1973-10-12','Jirón Carlos de los Heros','$2y$10$pov93T2rI.AvSVxjso.4jeQ1ZF3zs/nV5lky1yEQQdFFWnuIkLpm6','Activo','2025-10-24 00:17:28'),(11,'Javier Antonio','Herrera Vega','jaherreravega','jaherreravega@gmail.com','901234567','2000-04-03','Santa Rosa 453','$2y$10$p9T3dvJIfmXCcTSKU.gX8O07v04hTWmIFGAI1Ax2DJsvFgxgHTBty','Activo','2025-10-24 00:18:58'),(12,'Alejandra Ruiz','Rojas Castro','arojascruz','arojascruz@gmail.com','987654321','1995-11-20','Av. Las Flores 123','$2y$10$rRRgP5dDNzhFXdxudjyE4O2JmFVXgBgVV1RY1UR85TmUQpmQZ.UJO','Activo','2025-10-25 03:12:16'),(13,'Javier Juárez','Mendoza Espinoza','jmendozasilva','jmendozasilva@gmail.com','910234567','1988-03-15','Calle Los Pinos 456','$2y$10$wxw6g/.labANEkW9.C8.D.OKo0p3PLEa7gOj6DmhcpRFdIWK5dY76','Activo','2025-10-25 03:19:41'),(14,'Emilia Sofia','De la Vega','svegalara','svegalara@gmail.com','932109876','2000-07-28','Jr. San Martín 789','$2y$10$AYnA8rRtFCqZUv8ybFvZju6EJs1UcDlkam/2ERwCyYurKTu3CNb26','Activo','2025-10-25 03:30:03'),(15,'Miguel Armando','Segura Castañeda','mcastanedap','mcastanedap@gmail.com','954321098','1975-01-10','Prolong. Cusco 101','$2y$10$sm2saERIJ.EZZgKzyTm.uODFpDjiQ58G1kKxz3JFM5wEMglHrTQT.','Activo','2025-10-25 03:33:11'),(16,'Valeria Berenice','Paredes Larrea','vparedesruiz','vparedesruiz@gmail.com','976543210','1999-05-03','Urb. El Sol Mz A Lote 5','$2y$10$mawgEkQ/frGeomVw5CvJ6.xjElLcKlbuyIZAGHVH49JEnfZXID2eC','Activo','2025-10-25 04:00:57'),(17,'Luis Andrés','Lopez Huamán','ahuamantito','ahuamantito@gmail.com','980123456','1982-12-07','Ca. Grau 300 Dpto 4A','$2y$10$5.T2VkhJAQIdGuSG0Skh8eKMYeuyQEy9Hc3SYu6IOrY9GPLuySxYG','Activo','2025-10-25 16:22:17'),(18,'Ana Lucía','Garcia Flores','lfloressoto','lfloressoto@gmail.com','943210987','1990-09-22','Pasaje Italia 50','$2y$10$3F4lDt9oXWwqkoZW6r3/zeV2NNaDCjywfkrYW6/1OuqTKTox.bndq','Activo','2025-10-25 16:27:52'),(19,'José Roberto','Quispe Vega','rquispevega','rquispevega@gmail.com','965432109','1968-04-18','Av. Garcilaso 202 B','$2y$10$Hs39erNT0fA9q.skhpqNSugbpCD9Hh0t8PdlRXKUai0A7iFAjnnKC','Activo','2025-10-25 16:30:51'),(20,'Daniela Isabel','Benítez Santisteban','dbenitezcano','dbenitezcano@gmail.com','921098765','2003-06-01','Carretera Central Km 10','$2y$10$jRza2BKFxlnyFH2oR5Fd4OgUWJR9UQ09dvrOaZvMMZfgakjc1pWzW','Activo','2025-10-25 16:38:29'),(32,'Carla Sofía','Juares Montalvo','ladymontalvo','carlasjuaresmontalvo@gmail.com','920481735','1996-05-18','Jirón Los Rosales 455, Surco, Lima','$2y$10$tCMnR/e6oyYAUeExRVtHOOJR5FRx64cwPuRiXPDPUsUBBE1hknP9y','Activo','2025-11-17 18:50:18');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios_vip`
--

DROP TABLE IF EXISTS `usuarios_vip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios_vip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `tipo_membresia` varchar(50) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('Activa','Cancelada','Expirada') DEFAULT 'Activa',
  `fecha_cancelacion` date DEFAULT NULL,
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `usuarios_vip_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios_vip`
--

LOCK TABLES `usuarios_vip` WRITE;
/*!40000 ALTER TABLE `usuarios_vip` DISABLE KEYS */;
INSERT INTO `usuarios_vip` VALUES (1,1,'Anual',99.99,'2025-10-19','2028-10-19','Cancelada','2025-10-19','2025-10-19 15:27:47'),(2,1,'Anual',99.99,'2025-10-19','2028-10-19','Activa',NULL,'2025-10-19 15:28:17'),(3,2,'Anual',69.99,'2025-10-25','2027-10-25','Activa',NULL,'2025-10-25 16:43:43'),(4,3,'Mensual',24.99,'2025-10-25','2026-04-25','Activa',NULL,'2025-10-25 16:44:41'),(5,4,'Anual',39.99,'2025-10-25','2026-10-25','Activa',NULL,'2025-10-25 16:51:01'),(6,5,'Mensual',24.99,'2025-10-25','2026-04-25','Activa',NULL,'2025-10-25 16:52:10'),(7,6,'Mensual',17.99,'2025-10-25','2026-02-25','Activa',NULL,'2025-10-25 16:54:15'),(8,7,'Anual',39.99,'2025-10-25','2026-10-25','Activa',NULL,'2025-10-25 17:08:33'),(9,8,'Mensual',24.99,'2025-10-25','2026-04-25','Activa',NULL,'2025-10-25 17:09:16'),(10,9,'Anual',39.99,'2025-10-25','2026-10-25','Activa',NULL,'2025-10-25 17:10:02'),(11,10,'Mensual',17.99,'2025-10-25','2026-02-25','Activa',NULL,'2025-10-25 17:10:58'),(12,32,'Anual',39.99,'2025-11-26','2026-11-26','Cancelada','2025-12-02','2025-12-02 12:52:32'),(13,32,'Anual',69.99,'2025-12-02','2027-12-02','Activa',NULL,'2025-12-02 12:53:41');
/*!40000 ALTER TABLE `usuarios_vip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ventas` (
  `id_venta` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `total_final` decimal(10,2) NOT NULL,
  `metodo_pago` enum('Tarjeta','Yape','Plin','Efectivo') NOT NULL,
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('Pendiente','Pagado','Cancelado') DEFAULT 'Pendiente',
  PRIMARY KEY (`id_venta`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES (12,1,152.62,22.89,129.73,'','2025-11-17 16:50:30','');
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-04 16:30:33
