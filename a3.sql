-- MySQL dump 10.13  Distrib 8.0.13, for Linux (x86_64)
--
-- Host: localhost    Database: a3
-- ------------------------------------------------------
-- Server version	8.0.13

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
 SET NAMES utf8mb4 ;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Admin`
--

DROP TABLE IF EXISTS `Admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `Admin` (
  `id` int(11) NOT NULL,
  `Player_id` int(11) NOT NULL,
  `Password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Admin_Player_idx` (`Player_id`),
  CONSTRAINT `fk_Admin_Player` FOREIGN KEY (`Player_id`) REFERENCES `Player` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Admin`
--

LOCK TABLES `Admin` WRITE;
/*!40000 ALTER TABLE `Admin` DISABLE KEYS */;
INSERT INTO `Admin` VALUES (1,2,'$2y$10$TGpoQ76boCcIR9zxIk/b8.CdJOUS6xcCZeuWMuRvSeURIZQl7Et6.');
/*!40000 ALTER TABLE `Admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Inventory`
--

DROP TABLE IF EXISTS `Inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `Inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `squadID` int(11) NOT NULL,
  `status` enum('open','closed','pending') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Squad_has_Firearm_Squad1_idx` (`squadID`),
  CONSTRAINT `fk_Squad_has_Firearm_Squad1` FOREIGN KEY (`squadID`) REFERENCES `Squad` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Inventory`
--

LOCK TABLES `Inventory` WRITE;
/*!40000 ALTER TABLE `Inventory` DISABLE KEYS */;
INSERT INTO `Inventory` VALUES (21,39,'open'),(23,34,'open'),(24,40,'open'),(25,41,'open');
/*!40000 ALTER TABLE `Inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `InventoryItem`
--

DROP TABLE IF EXISTS `InventoryItem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `InventoryItem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventoryID` int(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  `status` enum('inStore','inGame','destroyed') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_InventoryItem_Inventory1_idx` (`inventoryID`),
  KEY `fk_InventoryItem_InventoryType1_idx` (`typeID`),
  CONSTRAINT `fk_InventoryItem_Inventory1` FOREIGN KEY (`inventoryID`) REFERENCES `Inventory` (`id`),
  CONSTRAINT `fk_InventoryItem_InventoryType1` FOREIGN KEY (`typeID`) REFERENCES `ItemType` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=164 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `InventoryItem`
--

LOCK TABLES `InventoryItem` WRITE;
/*!40000 ALTER TABLE `InventoryItem` DISABLE KEYS */;
INSERT INTO `InventoryItem` VALUES (132,21,6,'inStore'),(133,21,6,'inGame'),(136,21,6,'inGame'),(141,23,6,'inGame'),(143,23,7,'inStore'),(145,23,12,'inStore'),(149,23,6,'inStore'),(150,23,10,'inStore'),(151,23,10,'inStore'),(157,21,6,'inStore'),(158,21,6,'inStore'),(159,21,6,'inStore'),(160,21,6,'inStore'),(161,21,6,'inStore'),(162,21,10,'inGame'),(163,21,7,'inStore');
/*!40000 ALTER TABLE `InventoryItem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ItemClass`
--

DROP TABLE IF EXISTS `ItemClass`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `ItemClass` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ItemClass`
--

LOCK TABLES `ItemClass` WRITE;
/*!40000 ALTER TABLE `ItemClass` DISABLE KEYS */;
INSERT INTO `ItemClass` VALUES (2,'Assault Rifle'),(3,'Machine Gun'),(4,'Marksman Rifle'),(5,'Tool');
/*!40000 ALTER TABLE `ItemClass` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ItemType`
--

DROP TABLE IF EXISTS `ItemType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `ItemType` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `image` varchar(20) DEFAULT NULL,
  `scriptName` varchar(20) NOT NULL,
  `classID` int(11) NOT NULL,
  `sideID` int(11) DEFAULT NULL,
  `Price` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ItemType_table11_idx` (`classID`),
  KEY `fk_sideID` (`sideID`),
  CONSTRAINT `fk_ItemType_table11` FOREIGN KEY (`classID`) REFERENCES `ItemClass` (`id`),
  CONSTRAINT `fk_sideID` FOREIGN KEY (`sideID`) REFERENCES `Side` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ItemType`
--

LOCK TABLES `ItemType` WRITE;
/*!40000 ALTER TABLE `ItemType` DISABLE KEYS */;
INSERT INTO `ItemType` VALUES (3,'Rahim 7.62mm','Rahim 7.62mm','srifle_DMR_01_F',2,2,1200),(5,'Mk18 ABR 7.62 mm','Mk18 ABR 7.62 mm','srifle_EBR_F',2,1,1200),(6,'Map','Map','ItemMap',5,NULL,20),(7,'Compass','Compass','ItemCompass',5,NULL,50),(8,'GPS','GPS','ItemGPS',5,NULL,400),(9,'Radio','Radio','ItemRadio',5,NULL,400),(10,'Binoculars','Binoculars','Binocular',5,NULL,80),(11,'Rangefinder','Rangefinder','RangeFinder',5,NULL,400),(12,'Night Vision Goggles','Night Vision Goggles','NVGoggles',5,NULL,400),(13,'GM6 Lynx 12.7 mm','GM6 Lynx 12.7 mm','srifle_GM6_F',4,2,2500),(14,'M320 LRR .408','M320 LRR .408','srifle_LRR_F',4,1,2500),(15,'LMG_Mk200_F','LMG_Mk200_F','Mk200 6.5 mm',3,1,2000);
/*!40000 ALTER TABLE `ItemType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Player`
--

DROP TABLE IF EXISTS `Player`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `Player` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `firstName` varchar(30) NOT NULL,
  `email` varchar(40) NOT NULL,
  `username` varchar(20) NOT NULL,
  `squadID` int(11) DEFAULT NULL,
  `password` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Player_Squad_idx` (`squadID`),
  CONSTRAINT `fk_Player_Squad` FOREIGN KEY (`squadID`) REFERENCES `Squad` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Player`
--

LOCK TABLES `Player` WRITE;
/*!40000 ALTER TABLE `Player` DISABLE KEYS */;
INSERT INTO `Player` VALUES (2,'Villiger','Titus','titus.villiger@bluewin.ch','Momo',34,'$2y$10$JwQn80ZJzcyL9Fyx71Wi6.rZlP6UgJZ2ueW98dPTWA.FG1ud4GYbK'),(3,'Kant','Immanuel','immanuel.kant@jenseits.all','DerKritische',35,'$2y$10$uG5RE2ygo2QKs4boi0cl3eNlI6VwuoghJCyBrbW4nrkJ2UlYfN0V2'),(4,'Hans','Muster','h.m@h.m','Hanswurscht',35,'$2y$10$aKW6a54yaFhVXopQMyZ0wODSmBhwdmGQwhFs6He.4fRuEXfxRpBQ.'),(5,'Max','Müllser','m.m@gmx.com','Maxx',34,'$2y$10$NA5yPiYBbZnkTspxyu52eOzRQKJh0CVAg3SRtTJYZqtzMSMS7KlUK'),(6,'Knecht','Ueli','ueli.knecht@knechtschaft.com','UeliDerKnecht',34,'$2y$10$oUnt0n9F0WTdKUw/NYDq8eNUnP7NxYrk12QduxYyzXJfdYnxNTJTa'),(7,'Diesseits','Jenseits','dieses@jenes.ch','Player1',35,'$2y$10$p8LQIZ3zx0jYnDc8pvNBLOsFiuBGuJjRqfBiZSbPcTh4hNiNzhxl2'),(8,'Rauhmann','Raphael','ra.rauh@gmx.com','Red',39,'$2y$10$Jz7PZYVb5ez6zoh6DabEdOqLBjz30uordJbKcjGD5/PVtxmX8HRpO'),(9,'Hennig','Herrmann','henning@hartmann.ch','Blue',39,'$2y$10$E/o9GRd.N8slu0eubEuWJOq9w4UihUnyp.5g67Q9QB5Zn5fnbTBo6'),(10,'Grünbaum','Gabi','gabi.gruenbaum@unibas.ch','Green',39,'$2y$10$1aFMp.53QQZNrjnI9XZO4.Xr6NiZWq.NDnCzhdcDi26swni7GNJku'),(11,'&lt;Vil&gt;','Tit','villiger.titus@bluewin.ch','testuser',40,'$2y$10$7SIDRLlgm6wUNqDxJ.7ykuXwH/G4xtdh.WKf8sZsUT4ugtkAD8Ca6'),(12,'user','test','test.user@gmx.com','testuser2',40,'$2y$10$8mGJ4iuuBvPZ5NE9ql3Vge/kTmkvgJexlOTqHGbFKaydmx67xnYoW'),(14,'test','user','test@user.com','testuser3',NULL,'$2y$10$JwhlXGBgtJJY55N6RoOVV.9DkW6yR0taNYTpetw.80URZ6El/mPam'),(15,'poi','dau','dau@poi.ch','poidau',40,'$2y$10$TUZXsOVGMZ.EmDO.FXBHIe47ltyaHNazFVYS/Wpvroe2oDX.IO94q'),(16,'Gabi','Gibmit','gabi@gibmit.ch','Godot',NULL,'$2y$10$RV6hlZwjUN0r1zzXSknumeJj727VLqbwpKXlMHdcKFIKK0VjaMH.O'),(17,'Rechthaber','Ralf','recht.haber@habrecht.ch','rechthaber',41,'$2y$10$nmQI63li/G8l2zsjut2mDuirvq/pC6s1jlWOjTlZrr1Lfc6PsbyGC'),(18,'Liebaher','Lukas','hab.lieb@love.com','liebhaber',41,'$2y$10$SQE5GdlaFkUZgFxLzIPuyOQ3oJJ3mVO3HOvyWeJA4DOdIWjlubfPq'),(19,'Sorg','Eugen','sorg.eugen@sorgen.ch','sorgenträger',41,'$2y$10$wv9j1GX4o8t2uwImED5.F.YqHZTrNNSCmN1P.qyCtsf.VhHYD4c8C'),(20,'OR TRUE;','Böse','boesermensch@evil.com','evil',NULL,'$2y$10$sAySWE9ZTjbePWuMrJ.Lsule4fd8c5DMMPSM8BVemt8UIKFHiw.XG');
/*!40000 ALTER TABLE `Player` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Side`
--

DROP TABLE IF EXISTS `Side`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `Side` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(12) DEFAULT NULL,
  `credits` int(11) NOT NULL,
  `leaderID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Side_Player1_idx` (`leaderID`),
  CONSTRAINT `fk_Side_Player1` FOREIGN KEY (`leaderID`) REFERENCES `Player` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Side`
--

LOCK TABLES `Side` WRITE;
/*!40000 ALTER TABLE `Side` DISABLE KEYS */;
INSERT INTO `Side` VALUES (1,'BLUEFOR',0,NULL),(2,'OPFOR',0,NULL);
/*!40000 ALTER TABLE `Side` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Squad`
--

DROP TABLE IF EXISTS `Squad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `Squad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `image` varchar(20) DEFAULT NULL,
  `url` varchar(20) DEFAULT NULL,
  `credits` int(11) NOT NULL,
  `sideID` int(11) DEFAULT NULL,
  `leaderID` int(11) NOT NULL,
  `status` enum('pending','active','inactive') NOT NULL,
  `inventoryID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Squad_Side1_idx` (`sideID`),
  KEY `fk_Squad_Player1_idx` (`leaderID`),
  KEY `fk_Squad_Inventory1` (`inventoryID`),
  CONSTRAINT `fk_Squad_Inventory1` FOREIGN KEY (`inventoryID`) REFERENCES `Inventory` (`id`),
  CONSTRAINT `fk_Squad_Player1` FOREIGN KEY (`leaderID`) REFERENCES `Player` (`id`),
  CONSTRAINT `fk_Squad_Side1` FOREIGN KEY (`sideID`) REFERENCES `Side` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Squad`
--

LOCK TABLES `Squad` WRITE;
/*!40000 ALTER TABLE `Squad` DISABLE KEYS */;
INSERT INTO `Squad` VALUES (34,'Sieben Zwerge',NULL,'viltit.ch',150,1,2,'active',23),(35,'Zahnlose Haie',NULL,NULL,0,1,7,'inactive',NULL),(39,'Rainbow',NULL,'www.example.com',200,2,10,'active',21),(40,'TheTesters',NULL,'www,test.com',0,NULL,15,'pending',24),(41,'Habmenschen',NULL,'www.habwas.com',0,1,19,'active',25);
/*!40000 ALTER TABLE `Squad` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-01-02 23:22:42
