-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: localhost    Database: mor
-- ------------------------------------------------------
-- Server version	5.6.39

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `fs_file`
--

DROP TABLE IF EXISTS `fs_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `file_size` bigint(20) NOT NULL,
  `file_hash` varchar(128) NOT NULL,
  `server_id` int(11) NOT NULL DEFAULT '1',
  `use_count` int(11) NOT NULL,
  PRIMARY KEY (`file_id`),
  KEY `file_hash` (`file_hash`),
  KEY `file_hash_8` (`file_hash`(1)),
  KEY `file_hash_16` (`file_hash`(2))
) ENGINE=InnoDB AUTO_INCREMENT=26515 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_file`
--

LOCK TABLES `fs_file` WRITE;
/*!40000 ALTER TABLE `fs_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_file` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `add` AFTER INSERT ON `fs_file` FOR EACH ROW UPDATE fs_list SET files_count = files_count + 1 WHERE fs_id = NEW.server_id */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `del` AFTER DELETE ON `fs_file` FOR EACH ROW UPDATE fs_list SET files_count = files_count - 1 WHERE fs_id = OLD.server_id */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `fs_list`
--

DROP TABLE IF EXISTS `fs_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_list` (
  `fs_id` int(11) NOT NULL AUTO_INCREMENT,
  `is_online` tinyint(1) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL,
  `fs_host` varchar(255) NOT NULL,
  `files_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fs_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_list`
--

LOCK TABLES `fs_list` WRITE;
/*!40000 ALTER TABLE `fs_list` DISABLE KEYS */;
INSERT INTO `fs_list` VALUES (1,1,1,'fs1.myownradio.biz',4959);
/*!40000 ALTER TABLE `fs_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mor_access`
--

DROP TABLE IF EXISTS `mor_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_access` (
  `access` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_access`
--

LOCK TABLES `mor_access` WRITE;
/*!40000 ALTER TABLE `mor_access` DISABLE KEYS */;
INSERT INTO `mor_access` VALUES ('PRIVATE','Private - only you can listen to this radio channel'),('PUBLIC','Public - everyone can find and listen to this radio channel'),('UNLISTED','Unlisted - only those who has link can listen to this radio channel');
/*!40000 ALTER TABLE `mor_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mor_comment`
--

DROP TABLE IF EXISTS `mor_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_comment` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_stream` int(11) NOT NULL,
  `comment_user` int(11) NOT NULL,
  `comment_body` varchar(4096) NOT NULL,
  `comment_date` int(11) NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `comment_stream` (`comment_stream`),
  KEY `comment_user` (`comment_user`),
  CONSTRAINT `mor_comment_ibfk_1` FOREIGN KEY (`comment_stream`) REFERENCES `r_streams` (`sid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `mor_comment_ibfk_2` FOREIGN KEY (`comment_user`) REFERENCES `r_users` (`uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_comment`
--

LOCK TABLES `mor_comment` WRITE;
/*!40000 ALTER TABLE `mor_comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `mor_comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mor_countries`
--

DROP TABLE IF EXISTS `mor_countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_countries` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(2) NOT NULL DEFAULT '',
  `country_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`country_id`),
  FULLTEXT KEY `FT` (`country_name`)
) ENGINE=InnoDB AUTO_INCREMENT=243 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_countries`
--

LOCK TABLES `mor_countries` WRITE;
/*!40000 ALTER TABLE `mor_countries` DISABLE KEYS */;
INSERT INTO `mor_countries` VALUES (1,'US','United States'),(2,'CA','Canada'),(3,'AF','Afghanistan'),(4,'AL','Albania'),(5,'DZ','Algeria'),(6,'DS','American Samoa'),(7,'AD','Andorra'),(8,'AO','Angola'),(9,'AI','Anguilla'),(10,'AQ','Antarctica'),(11,'AG','Antigua and/or Barbuda'),(12,'AR','Argentina'),(13,'AM','Armenia'),(14,'AW','Aruba'),(15,'AU','Australia'),(16,'AT','Austria'),(17,'AZ','Azerbaijan'),(18,'BS','Bahamas'),(19,'BH','Bahrain'),(20,'BD','Bangladesh'),(21,'BB','Barbados'),(22,'BY','Belarus'),(23,'BE','Belgium'),(24,'BZ','Belize'),(25,'BJ','Benin'),(26,'BM','Bermuda'),(27,'BT','Bhutan'),(28,'BO','Bolivia'),(29,'BA','Bosnia and Herzegovina'),(30,'BW','Botswana'),(31,'BV','Bouvet Island'),(32,'BR','Brazil'),(33,'IO','British lndian Ocean Territory'),(34,'BN','Brunei Darussalam'),(35,'BG','Bulgaria'),(36,'BF','Burkina Faso'),(37,'BI','Burundi'),(38,'KH','Cambodia'),(39,'CM','Cameroon'),(40,'CV','Cape Verde'),(41,'KY','Cayman Islands'),(42,'CF','Central African Republic'),(43,'TD','Chad'),(44,'CL','Chile'),(45,'CN','China'),(46,'CX','Christmas Island'),(47,'CC','Cocos (Keeling) Islands'),(48,'CO','Colombia'),(49,'KM','Comoros'),(50,'CG','Congo'),(51,'CK','Cook Islands'),(52,'CR','Costa Rica'),(53,'HR','Croatia (Hrvatska)'),(54,'CU','Cuba'),(55,'CY','Cyprus'),(56,'CZ','Czech Republic'),(57,'DK','Denmark'),(58,'DJ','Djibouti'),(59,'DM','Dominica'),(60,'DO','Dominican Republic'),(61,'TP','East Timor'),(62,'EC','Ecuador'),(63,'EG','Egypt'),(64,'SV','El Salvador'),(65,'GQ','Equatorial Guinea'),(66,'ER','Eritrea'),(67,'EE','Estonia'),(68,'ET','Ethiopia'),(69,'FK','Falkland Islands (Malvinas)'),(70,'FO','Faroe Islands'),(71,'FJ','Fiji'),(72,'FI','Finland'),(73,'FR','France'),(74,'FX','France, Metropolitan'),(75,'GF','French Guiana'),(76,'PF','French Polynesia'),(77,'TF','French Southern Territories'),(78,'GA','Gabon'),(79,'GM','Gambia'),(80,'GE','Georgia'),(81,'DE','Germany'),(82,'GH','Ghana'),(83,'GI','Gibraltar'),(84,'GR','Greece'),(85,'GL','Greenland'),(86,'GD','Grenada'),(87,'GP','Guadeloupe'),(88,'GU','Guam'),(89,'GT','Guatemala'),(90,'GN','Guinea'),(91,'GW','Guinea-Bissau'),(92,'GY','Guyana'),(93,'HT','Haiti'),(94,'HM','Heard and Mc Donald Islands'),(95,'HN','Honduras'),(96,'HK','Hong Kong'),(97,'HU','Hungary'),(98,'IS','Iceland'),(99,'IN','India'),(100,'ID','Indonesia'),(101,'IR','Iran (Islamic Republic of)'),(102,'IQ','Iraq'),(103,'IE','Ireland'),(104,'IL','Israel'),(105,'IT','Italy'),(106,'CI','Ivory Coast'),(107,'JM','Jamaica'),(108,'JP','Japan'),(109,'JO','Jordan'),(110,'KZ','Kazakhstan'),(111,'KE','Kenya'),(112,'KI','Kiribati'),(113,'KP','Korea, Democratic People\'s Republic of'),(114,'KR','Korea, Republic of'),(115,'XK','Kosovo'),(116,'KW','Kuwait'),(117,'KG','Kyrgyzstan'),(118,'LA','Lao People\'s Democratic Republic'),(119,'LV','Latvia'),(120,'LB','Lebanon'),(121,'LS','Lesotho'),(122,'LR','Liberia'),(123,'LY','Libyan Arab Jamahiriya'),(124,'LI','Liechtenstein'),(125,'LT','Lithuania'),(126,'LU','Luxembourg'),(127,'MO','Macau'),(128,'MK','Macedonia'),(129,'MG','Madagascar'),(130,'MW','Malawi'),(131,'MY','Malaysia'),(132,'MV','Maldives'),(133,'ML','Mali'),(134,'MT','Malta'),(135,'MH','Marshall Islands'),(136,'MQ','Martinique'),(137,'MR','Mauritania'),(138,'MU','Mauritius'),(139,'TY','Mayotte'),(140,'MX','Mexico'),(141,'FM','Micronesia, Federated States of'),(142,'MD','Moldova, Republic of'),(143,'MC','Monaco'),(144,'MN','Mongolia'),(145,'ME','Montenegro'),(146,'MS','Montserrat'),(147,'MA','Morocco'),(148,'MZ','Mozambique'),(149,'MM','Myanmar'),(150,'NA','Namibia'),(151,'NR','Nauru'),(152,'NP','Nepal'),(153,'NL','Netherlands'),(154,'AN','Netherlands Antilles'),(155,'NC','New Caledonia'),(156,'NZ','New Zealand'),(157,'NI','Nicaragua'),(158,'NE','Niger'),(159,'NG','Nigeria'),(160,'NU','Niue'),(161,'NF','Norfork Island'),(162,'MP','Northern Mariana Islands'),(163,'NO','Norway'),(164,'OM','Oman'),(165,'PK','Pakistan'),(166,'PW','Palau'),(167,'PA','Panama'),(168,'PG','Papua New Guinea'),(169,'PY','Paraguay'),(170,'PE','Peru'),(171,'PH','Philippines'),(172,'PN','Pitcairn'),(173,'PL','Poland'),(174,'PT','Portugal'),(175,'PR','Puerto Rico'),(176,'QA','Qatar'),(177,'RE','Reunion'),(178,'RO','Romania'),(179,'RU','Russian Federation'),(180,'RW','Rwanda'),(181,'KN','Saint Kitts and Nevis'),(182,'LC','Saint Lucia'),(183,'VC','Saint Vincent and the Grenadines'),(184,'WS','Samoa'),(185,'SM','San Marino'),(186,'ST','Sao Tome and Principe'),(187,'SA','Saudi Arabia'),(188,'SN','Senegal'),(189,'RS','Serbia'),(190,'SC','Seychelles'),(191,'SL','Sierra Leone'),(192,'SG','Singapore'),(193,'SK','Slovakia'),(194,'SI','Slovenia'),(195,'SB','Solomon Islands'),(196,'SO','Somalia'),(197,'ZA','South Africa'),(198,'GS','South Georgia South Sandwich Islands'),(199,'ES','Spain'),(200,'LK','Sri Lanka'),(201,'SH','St. Helena'),(202,'PM','St. Pierre and Miquelon'),(203,'SD','Sudan'),(204,'SR','Suriname'),(205,'SJ','Svalbarn and Jan Mayen Islands'),(206,'SZ','Swaziland'),(207,'SE','Sweden'),(208,'CH','Switzerland'),(209,'SY','Syrian Arab Republic'),(210,'TW','Taiwan'),(211,'TJ','Tajikistan'),(212,'TZ','Tanzania, United Republic of'),(213,'TH','Thailand'),(214,'TG','Togo'),(215,'TK','Tokelau'),(216,'TO','Tonga'),(217,'TT','Trinidad and Tobago'),(218,'TN','Tunisia'),(219,'TR','Turkey'),(220,'TM','Turkmenistan'),(221,'TC','Turks and Caicos Islands'),(222,'TV','Tuvalu'),(223,'UG','Uganda'),(224,'UA','Ukraine'),(225,'AE','United Arab Emirates'),(226,'GB','United Kingdom'),(227,'UM','United States minor outlying islands'),(228,'UY','Uruguay'),(229,'UZ','Uzbekistan'),(230,'VU','Vanuatu'),(231,'VA','Vatican City State'),(232,'VE','Venezuela'),(233,'VN','Vietnam'),(234,'VG','Virgin Islands (British)'),(235,'VI','Virgin Islands (U.S.)'),(236,'WF','Wallis and Futuna Islands'),(237,'EH','Western Sahara'),(238,'YE','Yemen'),(239,'YU','Yugoslavia'),(240,'ZR','Zaire'),(241,'ZM','Zambia'),(242,'ZW','Zimbabwe');
/*!40000 ALTER TABLE `mor_countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mor_email_queue`
--

DROP TABLE IF EXISTS `mor_email_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_email_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `to` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `ip` varchar(15) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_email_queue`
--

LOCK TABLES `mor_email_queue` WRITE;
/*!40000 ALTER TABLE `mor_email_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `mor_email_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mor_genres`
--

DROP TABLE IF EXISTS `mor_genres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_genres` (
  `genre_id` int(11) NOT NULL AUTO_INCREMENT,
  `genre_name` varchar(255) NOT NULL,
  PRIMARY KEY (`genre_id`)
) ENGINE=InnoDB AUTO_INCREMENT=258 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_genres`
--

LOCK TABLES `mor_genres` WRITE;
/*!40000 ALTER TABLE `mor_genres` DISABLE KEYS */;
INSERT INTO `mor_genres` VALUES (1,'Acoustic'),(2,'Acoustic Folk'),(3,'Acoustic General'),(4,'Acoustic Guitar'),(5,'Acoustic Piano'),(6,'Acoustic Rock'),(7,'Acoustic Vocals'),(8,'Cover Songs'),(9,'Folk'),(10,'Alternative'),(11,'Alt Power Pop'),(12,'Alternative General'),(13,'Avant Rock'),(14,'Brit Pop'),(15,'Cover Songs'),(16,'Dance-Punk'),(17,'Emo'),(18,'Experimental'),(19,'Goth'),(20,'Grunge'),(21,'Indie'),(22,'Nu Metal'),(23,'Other Alternative'),(24,'Pop Punk'),(25,'Post Punk'),(26,'Shoegaze'),(27,'Ska'),(28,'Blues'),(29,'Acoustic Blues'),(30,'Blues General'),(31,'Blues Rock'),(32,'Country Blues'),(33,'Cover Songs'),(34,'Electric Blues'),(35,'Jump Blues'),(36,'Straight Ahead Blues'),(37,'Classical'),(38,'Baroque'),(39,'Chamber Music'),(40,'Choral'),(41,'Classical General'),(42,'Contemporary'),(43,'Ensembles'),(44,'Medieval'),(45,'Opera'),(46,'Renaissance'),(47,'Symphonic'),(48,'Comedy'),(49,'Adult Comedy'),(50,'General Comedy'),(51,'On Stage'),(52,'Parody'),(53,'Political Humor'),(54,'Prank Calls'),(55,'Country'),(56,'Alternative Country'),(57,'Americana'),(58,'Bluegrass'),(59,'Cajun/Zydeco'),(60,'Christian Country'),(61,'Country and Western'),(62,'Country General'),(63,'Country Swing'),(64,'Country-Pop'),(65,'Country-Rock'),(66,'Cover Songs'),(67,'Honky-Tonk'),(68,'Rockabilly'),(69,'Traditional Country'),(70,'Electronic'),(71,'Acid'),(72,'Ambient'),(73,'Big Beat'),(74,'Breakbeat'),(75,'Dance'),(76,'Drum n Bass'),(77,'Dubstep'),(78,'EDM'),(79,'Electro'),(80,'Electronica'),(81,'Euro'),(82,'Experimental Sounds'),(83,'Games Soundtrack'),(84,'Happy Hardcore'),(85,'House'),(86,'IDM'),(87,'Indietronic'),(88,'Industrial'),(89,'Jungle'),(90,'Mellow'),(91,'Minimal'),(92,'Noise'),(93,'Techno'),(94,'Techno Hardcore'),(95,'Trance'),(96,'Tribal'),(97,'Trip Hop'),(98,'HipHop'),(99,'Alternative Hip Hop'),(100,'Bass Rap'),(101,'Battles/Disses'),(102,'Christian Rap'),(103,'Freestyle'),(104,'Grime'),(105,'Hardcore Rap'),(106,'Hip Hop - Asian'),(107,'Hip Hop - Dutch'),(108,'Hip Hop - German'),(109,'Hip Hop General'),(110,'Hyphy'),(111,'Nerdcore'),(112,'New School'),(113,'Old School'),(114,'Positive Vibes'),(115,'Spoken Word'),(116,'Instrumentals'),(117,'Beats General'),(118,'Classical'),(119,'Club Bangas'),(120,'Cover Songs'),(121,'Crunk'),(122,'Dance & Electronic'),(123,'Dirty South'),(124,'East Coast'),(125,'EDM Instrumental'),(126,'Electro-hop'),(127,'Film Music'),(128,'Funk'),(129,'Game & Soundtrack'),(130,'Gangsta'),(131,'Hardcore'),(132,'Hip Hop'),(133,'Instrumentals with Hooks'),(134,'Jazzy Beats'),(135,'Latin'),(136,'Miami Bass'),(137,'Mid West'),(138,'New School'),(139,'Old School'),(140,'Pop'),(141,'R&B'),(142,'Reggae Beats'),(143,'Reggaeton'),(144,'Rock'),(145,'Scratch'),(146,'Smooth'),(147,'Trap'),(148,'West Coast'),(149,'Jazz'),(150,'Acid Jazz'),(151,'Bebop'),(152,'Cover Songs'),(153,'Dixieland'),(154,'Free Jazz'),(155,'Jazz Fusion'),(156,'Jazz General'),(157,'Jazz Vocals'),(158,'Lounge'),(159,'Modern Jazz'),(160,'Nu Jazz'),(161,'Smooth Jazz'),(162,'Swing'),(163,'Latin'),(164,'Bossa Nova'),(165,'Cover Songs'),(166,'Cuban'),(167,'Flamenco'),(168,'General Latin'),(169,'Latin Jazz'),(170,'Mariachi'),(171,'Merengue'),(172,'Pop/Balada'),(173,'Reggaeton'),(174,'Salsa'),(175,'Samba'),(176,'Tango'),(177,'Metal'),(178,'Alternative Metal'),(179,'Cover Songs'),(180,'Death/Black Metal'),(181,'Doom Metal'),(182,'Goth Metal'),(183,'Heavy Metal'),(184,'Industrial Metal'),(185,'Metal Riffs and Licks'),(186,'Power Metal'),(187,'Progressive Metal'),(188,'Rap-Metal'),(189,'Thrash Metal'),(190,'Pop'),(191,'Adult Contemporary'),(192,'Beach'),(193,'Christmas/Seasonal'),(194,'Contemporary Christian'),(195,'Contemporary Gospel'),(196,'Cover Songs'),(197,'Euro Pop'),(198,'J-Pop'),(199,'Musical'),(200,'Pop General'),(201,'Pop Rock'),(202,'Power Pop'),(203,'Rock'),(204,'Christian Rock'),(205,'Classic Rock'),(206,'Cover Songs'),(207,'Folk Rock'),(208,'Garage Rock'),(209,'Goth Rock'),(210,'Guitar Rock'),(211,'Hard Rock'),(212,'Instrumental Rock'),(213,'Progressive Rock'),(214,'Psychedelic Rock'),(215,'Punk'),(216,'Riffs and Licks'),(217,'Rock En Espanol'),(218,'Rock General'),(219,'Rock n Roll'),(220,'Rock Unplugged'),(221,'Southern Rock'),(222,'Surf Rock'),(223,'Talk'),(224,'Audio Blog'),(225,'Fictional Stories'),(226,'Music Talk'),(227,'Poetry'),(228,'Politics'),(229,'Religious'),(230,'Sports'),(231,'Talk'),(232,'Urban'),(233,'Cover Songs'),(234,'Funk'),(235,'Funky R&B'),(236,'Gospel'),(237,'Neo-Soul'),(238,'R&B/Soul/Pop'),(239,'Smooth R&B'),(240,'Soul'),(241,'World'),(242,'Dancehall'),(243,'Dub'),(244,'Native American'),(245,'New Age'),(246,'Reggae'),(247,'Traditional African'),(248,'Traditional Arabic'),(249,'Traditional Asian'),(250,'Traditional Celtic'),(251,'Traditional European'),(252,'Traditional Hawaiian'),(253,'Traditional Indian'),(254,'Traditional Irish'),(255,'Traditional Spanish'),(256,'World Fusion'),(257,'World General');
/*!40000 ALTER TABLE `mor_genres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mor_letter_event`
--

DROP TABLE IF EXISTS `mor_letter_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_letter_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `date` int(11) NOT NULL,
  `hash` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `user_id` (`to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_letter_event`
--

LOCK TABLES `mor_letter_event` WRITE;
/*!40000 ALTER TABLE `mor_letter_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `mor_letter_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mor_limits`
--

DROP TABLE IF EXISTS `mor_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_limits` (
  `limit_id` int(11) NOT NULL AUTO_INCREMENT,
  `streams_max` int(11) DEFAULT NULL,
  `time_max` bigint(20) NOT NULL,
  `min_track_length` int(11) NOT NULL DEFAULT '0',
  `max_listeners` int(11) DEFAULT '0',
  PRIMARY KEY (`limit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_limits`
--

LOCK TABLES `mor_limits` WRITE;
/*!40000 ALTER TABLE `mor_limits` DISABLE KEYS */;
INSERT INTO `mor_limits` VALUES (1,1,28800000,15000,20),(2,5,86400000,15000,100),(3,10,432000000,15000,500),(4,NULL,3629743000,15000,1000);
/*!40000 ALTER TABLE `mor_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mor_payment_order`
--

DROP TABLE IF EXISTS `mor_payment_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_payment_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `order_date` int(11) NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `mor_payment_order_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `r_users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mor_payment_order_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `mor_plans` (`plan_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_payment_order`
--

LOCK TABLES `mor_payment_order` WRITE;
/*!40000 ALTER TABLE `mor_payment_order` DISABLE KEYS */;
/*!40000 ALTER TABLE `mor_payment_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mor_payments`
--

DROP TABLE IF EXISTS `mor_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `expires` int(11) NOT NULL,
  `payment_comment` varchar(255) NOT NULL,
  `payment_source` enum('PROMO','PAYPAL','LIQPAY','OTHER') NOT NULL DEFAULT 'OTHER',
  `success` int(11) NOT NULL,
  `modified` int(11) NOT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `USER` (`user_id`),
  KEY `PLAN` (`plan_id`),
  CONSTRAINT `mor_payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `r_users` (`uid`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `mor_payments_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `mor_plans` (`plan_id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_payments`
--

LOCK TABLES `mor_payments` WRITE;
/*!40000 ALTER TABLE `mor_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `mor_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mor_plans`
--

DROP TABLE IF EXISTS `mor_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_plans` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(128) NOT NULL,
  `plan_duration` bigint(20) DEFAULT NULL,
  `plan_value` decimal(11,2) NOT NULL,
  `plan_period` varchar(32) DEFAULT NULL,
  `limit_id` int(11) NOT NULL,
  PRIMARY KEY (`plan_id`),
  KEY `LIMIT` (`limit_id`),
  CONSTRAINT `mor_plans_ibfk_1` FOREIGN KEY (`limit_id`) REFERENCES `mor_limits` (`limit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_plans`
--

LOCK TABLES `mor_plans` WRITE;
/*!40000 ALTER TABLE `mor_plans` DISABLE KEYS */;
INSERT INTO `mor_plans` VALUES (1,'Free',NULL,0.00,NULL,1),(2,'Extended Month',2592000,2.50,'month',2),(3,'Extended Year',31536000,20.00,'year',2),(4,'Premium Month',2592000,5.00,'month',3),(5,'Premium Year',31536000,45.00,'year',3),(6,'Infinity',NULL,100.00,'month',4);
/*!40000 ALTER TABLE `mor_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `mor_plans_view`
--

DROP TABLE IF EXISTS `mor_plans_view`;
/*!50001 DROP VIEW IF EXISTS `mor_plans_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `mor_plans_view` AS SELECT 
 1 AS `plan_id`,
 1 AS `plan_name`,
 1 AS `plan_duration`,
 1 AS `plan_period`,
 1 AS `plan_value`,
 1 AS `limit_id`,
 1 AS `streams_max`,
 1 AS `time_max`,
 1 AS `min_track_length`,
 1 AS `max_listeners`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `mor_playlists`
--

DROP TABLE IF EXISTS `mor_playlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_playlists` (
  `playlist_id` int(11) NOT NULL AUTO_INCREMENT,
  `used_id` int(11) NOT NULL,
  `playlist_name` varchar(255) NOT NULL,
  PRIMARY KEY (`playlist_id`),
  KEY `used_id` (`used_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_playlists`
--

LOCK TABLES `mor_playlists` WRITE;
/*!40000 ALTER TABLE `mor_playlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `mor_playlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mor_playlists_link`
--

DROP TABLE IF EXISTS `mor_playlists_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_playlists_link` (
  `link_id` int(11) NOT NULL AUTO_INCREMENT,
  `position_id` int(11) NOT NULL,
  `playlist_id` int(11) NOT NULL,
  `track_id` int(11) NOT NULL,
  PRIMARY KEY (`link_id`),
  KEY `playlist_id` (`playlist_id`),
  KEY `track_id` (`track_id`),
  CONSTRAINT `mor_playlists_link_ibfk_1` FOREIGN KEY (`playlist_id`) REFERENCES `mor_playlists` (`playlist_id`),
  CONSTRAINT `mor_playlists_link_ibfk_2` FOREIGN KEY (`track_id`) REFERENCES `r_tracks` (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_playlists_link`
--

LOCK TABLES `mor_playlists_link` WRITE;
/*!40000 ALTER TABLE `mor_playlists_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `mor_playlists_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `mor_playlists_view`
--

DROP TABLE IF EXISTS `mor_playlists_view`;
/*!50001 DROP VIEW IF EXISTS `mor_playlists_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `mor_playlists_view` AS SELECT 
 1 AS `link_id`,
 1 AS `playlist_id`,
 1 AS `position_id`,
 1 AS `track_id`,
 1 AS `user_id`,
 1 AS `filename`,
 1 AS `file_extension`,
 1 AS `artist`,
 1 AS `title`,
 1 AS `album`,
 1 AS `track_number`,
 1 AS `genre`,
 1 AS `date`,
 1 AS `cue`,
 1 AS `duration`,
 1 AS `filesize`,
 1 AS `color`,
 1 AS `uploaded`,
 1 AS `is_new`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `mor_promo_codes`
--

DROP TABLE IF EXISTS `mor_promo_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_promo_codes` (
  `promo_code` varchar(16) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `expires` int(11) NOT NULL,
  `use_left` int(11) NOT NULL,
  PRIMARY KEY (`promo_code`),
  KEY `PLAN` (`plan_id`),
  CONSTRAINT `mor_promo_codes_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `mor_plans` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_promo_codes`
--

LOCK TABLES `mor_promo_codes` WRITE;
/*!40000 ALTER TABLE `mor_promo_codes` DISABLE KEYS */;

/*!40000 ALTER TABLE `mor_promo_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mor_servers_list`
--

DROP TABLE IF EXISTS `mor_servers_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_servers_list` (
  `server_id` int(11) NOT NULL AUTO_INCREMENT,
  `server_kind` enum('FS','STREAMER') NOT NULL,
  `server_kind_id` int(11) NOT NULL,
  `is_enabled` int(11) NOT NULL,
  `is_online` int(11) NOT NULL,
  PRIMARY KEY (`server_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_servers_list`
--

LOCK TABLES `mor_servers_list` WRITE;
/*!40000 ALTER TABLE `mor_servers_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `mor_servers_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `mor_stream_stats_view`
--

DROP TABLE IF EXISTS `mor_stream_stats_view`;
/*!50001 DROP VIEW IF EXISTS `mor_stream_stats_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `mor_stream_stats_view` AS SELECT 
 1 AS `sid`,
 1 AS `permalink`,
 1 AS `uid`,
 1 AS `started`,
 1 AS `started_from`,
 1 AS `status`,
 1 AS `tracks_count`,
 1 AS `tracks_duration`,
 1 AS `listeners_count`,
 1 AS `bookmarks_count`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `mor_stream_tracklist_view`
--

DROP TABLE IF EXISTS `mor_stream_tracklist_view`;
/*!50001 DROP VIEW IF EXISTS `mor_stream_tracklist_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `mor_stream_tracklist_view` AS SELECT 
 1 AS `tid`,
 1 AS `file_id`,
 1 AS `uid`,
 1 AS `filename`,
 1 AS `ext`,
 1 AS `artist`,
 1 AS `title`,
 1 AS `album`,
 1 AS `track_number`,
 1 AS `genre`,
 1 AS `date`,
 1 AS `cue`,
 1 AS `buy`,
 1 AS `duration`,
 1 AS `filesize`,
 1 AS `color`,
 1 AS `uploaded`,
 1 AS `id`,
 1 AS `stream_id`,
 1 AS `track_id`,
 1 AS `t_order`,
 1 AS `unique_id`,
 1 AS `time_offset`,
 1 AS `is_new`,
 1 AS `can_be_shared`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `mor_tag_list`
--

DROP TABLE IF EXISTS `mor_tag_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_tag_list` (
  `tag_name` varchar(255) NOT NULL,
  `usage_count` int(11) NOT NULL,
  PRIMARY KEY (`tag_name`),
  KEY `first_letter` (`tag_name`(1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_tag_list`
--

LOCK TABLES `mor_tag_list` WRITE;
/*!40000 ALTER TABLE `mor_tag_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `mor_tag_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mor_track_like`
--

DROP TABLE IF EXISTS `mor_track_like`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_track_like` (
  `track_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `relation` enum('like','dislike') NOT NULL DEFAULT 'like',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`track_id`,`user_id`),
  KEY `by_user` (`user_id`),
  KEY `by_track` (`track_id`),
  KEY `by_relation` (`relation`),
  CONSTRAINT `mor_track_like_ibfk_1` FOREIGN KEY (`track_id`) REFERENCES `r_tracks` (`tid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mor_track_like_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `r_users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_track_like`
--

LOCK TABLES `mor_track_like` WRITE;
/*!40000 ALTER TABLE `mor_track_like` DISABLE KEYS */;
/*!40000 ALTER TABLE `mor_track_like` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `ADD` AFTER INSERT ON `mor_track_like` FOR EACH ROW IF (NEW.relation = "like") THEN

	UPDATE mor_track_stat SET likes = likes + 1 WHERE track_id = NEW.track_id;

ELSE

	UPDATE mor_track_stat SET dislikes = dislikes + 1 WHERE track_id = NEW.track_id;

END IF */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `DEL` AFTER DELETE ON `mor_track_like` FOR EACH ROW IF (OLD.relation = "like") THEN

	UPDATE mor_track_stat SET likes = likes - 1 WHERE track_id = OLD.track_id;

ELSE

	UPDATE mor_track_stat SET dislikes = dislikes - 1 WHERE track_id = OLD.track_id;

END IF */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `mor_track_stat`
--

DROP TABLE IF EXISTS `mor_track_stat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mor_track_stat` (
  `track_id` int(11) NOT NULL,
  `likes` int(11) NOT NULL,
  `dislikes` int(11) NOT NULL,
  PRIMARY KEY (`track_id`),
  CONSTRAINT `mor_track_stat_ibfk_1` FOREIGN KEY (`track_id`) REFERENCES `r_tracks` (`tid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mor_track_stat`
--

LOCK TABLES `mor_track_stat` WRITE;
/*!40000 ALTER TABLE `mor_track_stat` DISABLE KEYS */;
/*!40000 ALTER TABLE `mor_track_stat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `mor_users_view`
--

DROP TABLE IF EXISTS `mor_users_view`;
/*!50001 DROP VIEW IF EXISTS `mor_users_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `mor_users_view` AS SELECT 
 1 AS `uid`,
 1 AS `mail`,
 1 AS `login`,
 1 AS `password`,
 1 AS `name`,
 1 AS `info`,
 1 AS `rights`,
 1 AS `registration_date`,
 1 AS `last_visit_date`,
 1 AS `permalink`,
 1 AS `avatar`,
 1 AS `country_id`,
 1 AS `user_id`,
 1 AS `tracks_count`,
 1 AS `tracks_duration`,
 1 AS `tracks_size`,
 1 AS `streams_count`,
 1 AS `plan_id`,
 1 AS `plan_expires`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `opt_user_options`
--

DROP TABLE IF EXISTS `opt_user_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opt_user_options` (
  `user_id` int(11) NOT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `format_id` int(11) NOT NULL DEFAULT '6',
  `volume` int(11) NOT NULL DEFAULT '100',
  UNIQUE KEY `uid` (`user_id`),
  KEY `lang` (`lang_id`,`format_id`),
  KEY `format` (`format_id`),
  CONSTRAINT `opt_user_options_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `r_users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `opt_user_options_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `opt_valid_lang` (`lang_id`),
  CONSTRAINT `opt_user_options_ibfk_3` FOREIGN KEY (`format_id`) REFERENCES `opt_valid_format` (`format_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opt_user_options`
--

LOCK TABLES `opt_user_options` WRITE;
/*!40000 ALTER TABLE `opt_user_options` DISABLE KEYS */;
/*!40000 ALTER TABLE `opt_user_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opt_valid_format`
--

DROP TABLE IF EXISTS `opt_valid_format`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opt_valid_format` (
  `format_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_type` int(11) NOT NULL,
  `format_string` varchar(16) NOT NULL,
  `format_name` varchar(16) NOT NULL,
  `format_bitrate` int(11) NOT NULL,
  PRIMARY KEY (`format_id`),
  KEY `account_type` (`account_type`),
  CONSTRAINT `opt_valid_format_ibfk_1` FOREIGN KEY (`account_type`) REFERENCES `mor_limits` (`limit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opt_valid_format`
--

LOCK TABLES `opt_valid_format` WRITE;
/*!40000 ALTER TABLE `opt_valid_format` DISABLE KEYS */;
INSERT INTO `opt_valid_format` VALUES (1,1,'aacplus_24k','AAC+',24000),(2,1,'aacplus_32k','AAC+',32000),(3,1,'aacplus_64k','AAC+',64000),(4,1,'aacplus_128k','AAC+',128000),(5,1,'mp3_64k','MP3',64000),(6,1,'mp3_128k','MP3',128000),(7,2,'mp3_256k','MP3',256000),(8,2,'mp3_320k','MP3',320000);
/*!40000 ALTER TABLE `opt_valid_format` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opt_valid_lang`
--

DROP TABLE IF EXISTS `opt_valid_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opt_valid_lang` (
  `lang_id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_code` varchar(2) NOT NULL,
  `lang_locale` varchar(5) NOT NULL,
  `lang_name` varchar(32) NOT NULL,
  PRIMARY KEY (`lang_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opt_valid_lang`
--

LOCK TABLES `opt_valid_lang` WRITE;
/*!40000 ALTER TABLE `opt_valid_lang` DISABLE KEYS */;
INSERT INTO `opt_valid_lang` VALUES (1,'en','en_US','English'),(2,'uk','uk_UA','Українська');
/*!40000 ALTER TABLE `opt_valid_lang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_bookmarks`
--

DROP TABLE IF EXISTS `r_bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_bookmarks` (
  `user_id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`stream_id`),
  KEY `STREAM_idx` (`stream_id`),
  CONSTRAINT `STREAM` FOREIGN KEY (`stream_id`) REFERENCES `r_streams` (`sid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `USER` FOREIGN KEY (`user_id`) REFERENCES `r_users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_bookmarks`
--

LOCK TABLES `r_bookmarks` WRITE;
/*!40000 ALTER TABLE `r_bookmarks` DISABLE KEYS */;
/*!40000 ALTER TABLE `r_bookmarks` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `bookmark.when.added` AFTER INSERT ON `r_bookmarks` FOR EACH ROW UPDATE r_static_stream_vars SET bookmarks_count = bookmarks_count + 1 WHERE stream_id = NEW.stream_id */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `bookmark.when.removed` AFTER DELETE ON `r_bookmarks` FOR EACH ROW UPDATE r_static_stream_vars SET bookmarks_count = bookmarks_count - 1 WHERE stream_id = OLD.stream_id */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `r_categories`
--

DROP TABLE IF EXISTS `r_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `category_permalink` varchar(255) NOT NULL,
  `streams_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_categories`
--

LOCK TABLES `r_categories` WRITE;
/*!40000 ALTER TABLE `r_categories` DISABLE KEYS */;
INSERT INTO `r_categories` VALUES (1,'Electronic (House, Trance, Breakbeat)','electronic',12),(2,'Easy Electronic (Chillout, Ambient, Space Music)','relax-electronic',6),(3,'Jazz (Jazz, Blues, Country)','jazz',1),(4,'Lounge (Acid Jazz, Lounge, Smooth Jazz)','lounge',4),(5,'Indie (Indie Rock, Chillwave, Synthpop, Indie)','indie',1),(6,'Pop (International Pop, ex-USSR Pop, Britpop)','pop',12),(7,'Talks','talks',5),(8,'New Age (Ethno, Celtic, Enigmatic, World, Folk)','new-age',5),(9,'Classical & Neoclassical','classical',1),(10,'Instrumental Music','instrumental',0),(11,'Reggae (Early, Roots, Dub)','reggae',0),(12,'Rap (Soul, R\'n\'B)','rap',9),(13,'Rock (Classic Rock, Metal, Alternative, Prog)','rock',7),(14,'Oldies','oldies',3),(15,'Psy (Psybient, Psychill, Psytrance)','psy',4),(16,'Uncategorized','uncategorized',38);
/*!40000 ALTER TABLE `r_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_colors`
--

DROP TABLE IF EXISTS `r_colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_colors` (
  `color_id` int(11) NOT NULL,
  `color_name` varchar(32) NOT NULL,
  `color_code` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`color_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_colors`
--

LOCK TABLES `r_colors` WRITE;
/*!40000 ALTER TABLE `r_colors` DISABLE KEYS */;
INSERT INTO `r_colors` VALUES (0,'None',NULL),(1,'Dark Green','#008800'),(2,'Yellow','#d9d900'),(3,'Orange','#d97200'),(4,'Red','#d90000'),(5,'Pink','#d953ab'),(6,'Purple','#6855d9'),(7,'Dark Blue','#0001d9'),(8,'Blue','#0382d9'),(9,'Cyan?','#00ced9'),(10,'Green','#36d979'),(11,'Light Green','#8dd987'),(12,'Brown','#b9968f');
/*!40000 ALTER TABLE `r_colors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_link`
--

DROP TABLE IF EXISTS `r_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_link` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL,
  `track_id` int(11) NOT NULL,
  `t_order` int(11) NOT NULL,
  `unique_id` varchar(8) NOT NULL,
  `time_offset` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ID` (`unique_id`),
  KEY `STREAM` (`stream_id`),
  KEY `TRACK` (`track_id`),
  CONSTRAINT `r_link_ibfk_1` FOREIGN KEY (`stream_id`) REFERENCES `r_streams` (`sid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `r_link_ibfk_2` FOREIGN KEY (`track_id`) REFERENCES `r_tracks` (`tid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20142 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_link`
--

LOCK TABLES `r_link` WRITE;
/*!40000 ALTER TABLE `r_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `r_link` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `STREAM_ADD_TRACK` AFTER INSERT ON `r_link` FOR EACH ROW BEGIN



SELECT duration INTO @duration FROM r_tracks WHERE tid = NEW.track_id;

UPDATE r_tracks SET used_count = used_count + 1 WHERE tid = NEW.track_id;



INSERT INTO `r_static_stream_vars` SET `stream_id` = NEW.`stream_id`, `tracks_count` = 1, `tracks_duration` = @duration ON DUPLICATE KEY UPDATE  `tracks_count` = `tracks_count` + 1, `tracks_duration` = `tracks_duration` + @duration;



END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `STREAM_MODIFY_TRACK` AFTER UPDATE ON `r_link` FOR EACH ROW BEGIN

IF (OLD.track_id != NEW.track_id) THEN

UPDATE r_tracks SET used_count = used_count - 1 WHERE tid = OLD.track_id;

UPDATE r_tracks SET used_count = used_count + 1 WHERE tid = NEW.track_id;

END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `STREAM_REMOVE_TRACK` BEFORE DELETE ON `r_link` FOR EACH ROW BEGIN


SELECT duration 
	INTO @duration 
	FROM r_tracks 
	WHERE tid = OLD.track_id;


UPDATE r_tracks 
	SET used_count = GREATEST(used_count - 1, 0) 
	WHERE tid = OLD.track_id;


UPDATE `r_static_stream_vars` 
	SET `tracks_count` = GREATEST(`tracks_count` - 1, 0), 
    	`tracks_duration` = GREATEST(`tracks_duration` - @duration, 0) 
	WHERE `stream_id` = OLD.`stream_id`;



END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `r_listener`
--

DROP TABLE IF EXISTS `r_listener`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_listener` (
  `client_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_ip` varchar(32) NOT NULL,
  `client_ua` varchar(255) NOT NULL,
  `stream` int(11) DEFAULT NULL,
  `quality` varchar(32) NOT NULL,
  `started` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finished` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`client_id`),
  KEY `STREAM` (`stream`),
  KEY `QUALITY` (`quality`),
  CONSTRAINT `r_listener_ibfk_1` FOREIGN KEY (`stream`) REFERENCES `r_streams` (`sid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45140 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_listener`
--

LOCK TABLES `r_listener` WRITE;
/*!40000 ALTER TABLE `r_listener` DISABLE KEYS */;
/*!40000 ALTER TABLE `r_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_static_stream_vars`
--

DROP TABLE IF EXISTS `r_static_stream_vars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_static_stream_vars` (
  `stream_id` int(11) NOT NULL,
  `tracks_count` int(11) NOT NULL DEFAULT '0',
  `tracks_duration` bigint(20) NOT NULL DEFAULT '0',
  `listeners_count` int(11) NOT NULL DEFAULT '0',
  `bookmarks_count` int(11) NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `playbacks` int(11) NOT NULL,
  `summary_played` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`stream_id`),
  CONSTRAINT `r_static_stream_vars_ibfk_1` FOREIGN KEY (`stream_id`) REFERENCES `r_streams` (`sid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_static_stream_vars`
--

LOCK TABLES `r_static_stream_vars` WRITE;
/*!40000 ALTER TABLE `r_static_stream_vars` DISABLE KEYS */;
/*!40000 ALTER TABLE `r_static_stream_vars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_static_user_vars`
--

DROP TABLE IF EXISTS `r_static_user_vars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_static_user_vars` (
  `user_id` int(11) NOT NULL,
  `tracks_count` int(11) NOT NULL DEFAULT '0',
  `tracks_duration` bigint(20) NOT NULL DEFAULT '0',
  `tracks_size` bigint(20) NOT NULL DEFAULT '0',
  `streams_count` int(11) NOT NULL DEFAULT '0',
  `listeners_count` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_static_user_vars`
--

LOCK TABLES `r_static_user_vars` WRITE;
/*!40000 ALTER TABLE `r_static_user_vars` DISABLE KEYS */;
/*!40000 ALTER TABLE `r_static_user_vars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_stats_memory`
--

DROP TABLE IF EXISTS `r_stats_memory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_stats_memory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `uid` int(11) NOT NULL,
  `uri` varchar(4096) NOT NULL,
  `referer` varchar(4096) NOT NULL,
  `useragent` varchar(4096) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_stats_memory`
--

LOCK TABLES `r_stats_memory` WRITE;
/*!40000 ALTER TABLE `r_stats_memory` DISABLE KEYS */;
/*!40000 ALTER TABLE `r_stats_memory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_streams`
--

DROP TABLE IF EXISTS `r_streams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_streams` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `permalink` varchar(255) DEFAULT NULL,
  `info` varchar(4096) NOT NULL DEFAULT '',
  `jingle_interval` int(11) NOT NULL DEFAULT '4',
  `status` int(11) NOT NULL DEFAULT '0',
  `started` bigint(20) DEFAULT NULL,
  `started_from` bigint(20) DEFAULT NULL,
  `access` varchar(255) NOT NULL DEFAULT 'PUBLIC',
  `category` int(11) DEFAULT NULL,
  `hashtags` varchar(4096) NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `cover_background` varchar(7) DEFAULT NULL,
  `created` bigint(20) NOT NULL,
  PRIMARY KEY (`sid`),
  KEY `UID` (`uid`),
  KEY `CATEGORY` (`category`),
  KEY `access_key` (`access`),
  FULLTEXT KEY `TAGS` (`hashtags`),
  FULLTEXT KEY `FT` (`name`,`permalink`,`hashtags`),
  CONSTRAINT `r_streams_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `r_users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `r_streams_ibfk_2` FOREIGN KEY (`category`) REFERENCES `r_categories` (`category_id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `r_streams_ibfk_3` FOREIGN KEY (`access`) REFERENCES `mor_access` (`access`)
) ENGINE=InnoDB AUTO_INCREMENT=357 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_streams`
--

LOCK TABLES `r_streams` WRITE;
/*!40000 ALTER TABLE `r_streams` DISABLE KEYS */;

/*!40000 ALTER TABLE `r_streams` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `stream.when.added` AFTER INSERT ON `r_streams` FOR EACH ROW BEGIN

INSERT INTO r_static_stream_vars SET stream_id = NEW.sid;

UPDATE r_static_user_vars SET streams_count = streams_count + 1 WHERE user_id = NEW.uid;

IF NEW.access = "PUBLIC" THEN

	CALL increase_streams_in_category(NEW.category);

END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `stream.when.changed` AFTER UPDATE ON `r_streams` FOR EACH ROW BEGIN

IF OLD.access = "PUBLIC" THEN

	CALL decrease_streams_in_category(OLD.category);

END IF;

IF NEW.access = "PUBLIC" THEN

	CALL increase_streams_in_category(NEW.category);

END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `stream.when.removed` AFTER DELETE ON `r_streams` FOR EACH ROW BEGIN

DELETE FROM r_static_stream_vars WHERE stream_id = OLD.sid;

UPDATE r_static_user_vars SET streams_count = streams_count - 1 WHERE user_id = OLD.uid;



IF OLD.access = "PUBLIC" THEN

	CALL decrease_streams_in_category(OLD.category);

END IF;



END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `r_tracks`
--

DROP TABLE IF EXISTS `r_tracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_tracks` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) DEFAULT NULL,
  `uid` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `hash` varchar(128) NOT NULL,
  `ext` varchar(32) NOT NULL,
  `artist` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `album` varchar(255) NOT NULL,
  `track_number` varchar(11) NOT NULL,
  `genre` varchar(255) NOT NULL,
  `date` varchar(64) NOT NULL,
  `cue` text,
  `buy` varchar(255) DEFAULT NULL,
  `duration` int(11) NOT NULL,
  `filesize` int(11) NOT NULL,
  `color` int(11) NOT NULL DEFAULT '0',
  `uploaded` int(11) NOT NULL,
  `copy_of` int(11) DEFAULT NULL,
  `used_count` int(11) NOT NULL DEFAULT '0',
  `is_new` tinyint(1) NOT NULL DEFAULT '1',
  `can_be_shared` tinyint(1) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` int(11) DEFAULT NULL,
  PRIMARY KEY (`tid`),
  KEY `UID` (`uid`),
  KEY `KEYWORD` (`artist`(2)),
  KEY `COLOR` (`color`),
  KEY `COPY_OF` (`copy_of`),
  KEY `USED` (`used_count`),
  KEY `GENRE` (`genre`),
  KEY `file_id` (`file_id`),
  KEY `HASH_1` (`hash`(1)),
  KEY `HASH_2` (`hash`(2)),
  KEY `HASH_ALL` (`hash`),
  FULLTEXT KEY `FT` (`artist`,`title`,`genre`),
  CONSTRAINT `r_tracks_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `r_users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `r_tracks_ibfk_2` FOREIGN KEY (`color`) REFERENCES `r_colors` (`color_id`),
  CONSTRAINT `r_tracks_ibfk_3` FOREIGN KEY (`copy_of`) REFERENCES `r_tracks` (`tid`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `r_tracks_ibfk_4` FOREIGN KEY (`file_id`) REFERENCES `fs_file` (`file_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22864 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_tracks`
--

LOCK TABLES `r_tracks` WRITE;
/*!40000 ALTER TABLE `r_tracks` DISABLE KEYS */;

/*!40000 ALTER TABLE `r_tracks` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `ADD_TRACK` AFTER INSERT ON `r_tracks` FOR EACH ROW BEGIN

INSERT INTO `r_static_user_vars` SET `user_id` = NEW.`uid`, `tracks_count` = 1, `tracks_duration` = NEW.`duration`, `tracks_size` = NEW.`filesize`

ON DUPLICATE KEY UPDATE `tracks_count` = `tracks_count` + 1, `tracks_duration` = `tracks_duration` + NEW.`duration`, `tracks_size` = `tracks_size` + NEW.`filesize`;

INSERT INTO mor_track_stat SET track_id = NEW.tid;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `r_users`
--

DROP TABLE IF EXISTS `r_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `mail` varchar(128) NOT NULL,
  `login` varchar(32) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `info` varchar(4096) DEFAULT NULL,
  `rights` int(11) DEFAULT NULL,
  `registration_date` int(10) unsigned NOT NULL,
  `last_visit_date` int(10) unsigned DEFAULT NULL,
  `permalink` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `UNIQUE_EMAIL` (`mail`),
  UNIQUE KEY `UNIQUE_LOGIN` (`login`),
  KEY `COUNTRY` (`country_id`),
  FULLTEXT KEY `NAME_FT` (`name`),
  CONSTRAINT `r_users_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `mor_countries` (`country_id`)
) ENGINE=InnoDB AUTO_INCREMENT=318 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_users`
--

LOCK TABLES `r_users` WRITE;
/*!40000 ALTER TABLE `r_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `r_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `when.user.added` AFTER INSERT ON `r_users` FOR EACH ROW BEGIN

INSERT INTO r_static_user_vars SET user_id = NEW.uid;

INSERT INTO opt_user_options SET user_id = NEW.uid;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `when.user.removed` AFTER DELETE ON `r_users` FOR EACH ROW DELETE FROM r_static_user_vars WHERE user_id = OLD.uid */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Dumping events for database 'mor'
--

--
-- Dumping routines for database 'mor'
--
/*!50003 DROP FUNCTION IF EXISTS `TODAY` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`mor`@`%` FUNCTION `TODAY`(`PARAM` TIMESTAMP) RETURNS tinyint(1)
    NO SQL
RETURN CAST(NOW() AS DATE) = CAST(PARAM AS DATE) ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `decrease_streams_in_category` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`mor`@`%` PROCEDURE `decrease_streams_in_category`(IN `id` INT)
    NO SQL
UPDATE r_categories

SET streams_count = GREATEST(streams_count - 1, 0)

WHERE category_id = id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `increase_streams_in_category` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`mor`@`%` PROCEDURE `increase_streams_in_category`(IN `id` INT)
    NO SQL
UPDATE r_categories

SET streams_count = streams_count + 1

WHERE category_id = id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `move_track_channel` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`mor`@`%` PROCEDURE `move_track_channel`(IN `s_id` INT, IN `s_target` VARCHAR(16), IN `s_index` INT)
    NO SQL
proc:BEGIN



SELECT `t_order`,`track_id` INTO @order, @id FROM `r_link` WHERE `unique_id` = s_target;



IF(@order = s_index) THEN

	LEAVE proc;

END IF;



SELECT `duration` INTO @duration FROM `r_tracks` WHERE `tid` = @id;



IF(s_index > @order) THEN

	UPDATE `r_link` SET `t_order` = `t_order` - 1, `time_offset` = `time_offset` - @duration

        WHERE `t_order` > @order AND `t_order` <= s_index AND `stream_id` = s_id

        ORDER BY `t_order` ASC;



    SELECT `time_offset`, `track_id` INTO @tmpOffset, @tmpTrackId FROM `r_link`

    	WHERE `t_order` = s_index - 1 AND `stream_id` = s_id LIMIT 1;



    SELECT `duration` INTO @tmpDuration FROM `r_tracks`

        WHERE `tid` = @tmpTrackId;



	SET @newOffset := @tmpOffset + @tmpDuration;

ELSE

	SELECT `time_offset` INTO @newOffset FROM `r_link` WHERE `t_order` = s_index AND `stream_id` = s_id;

	UPDATE `r_link` SET `t_order` = `t_order` + 1, `time_offset` = `time_offset` + @duration

        WHERE `t_order` >= s_index AND `t_order` < @order AND `stream_id` = s_id

        ORDER BY `t_order` ASC;

END IF;



UPDATE `r_link` SET `t_order` = s_index, `time_offset` = @newOffset WHERE `unique_id` = s_target;



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `optimize_channel` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`mor`@`%` PROCEDURE `optimize_channel`(IN `s_id` INT)
    NO SQL
BEGIN

SET @col := 0;

SET @acc := 0;



UPDATE r_link AS target

  INNER JOIN (

      SELECT @col := @col + 1 AS col, @acc AS acc, @acc := @acc + t.duration, t.id FROM

        (

               SELECT

                 r_link.*,

                 r_tracks.*

               FROM r_link

                 INNER JOIN r_tracks ON r_link.track_id = r_tracks.tid

               WHERE r_link.stream_id = s_id ORDER BY r_link.t_order ASC

        ) AS t

             ) AS source ON target.id = source.id



SET

  target.t_order     = source.col,

  target.time_offset = source.acc;



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `serverListenersRotate` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`mor`@`%` PROCEDURE `serverListenersRotate`()
    NO SQL
BEGIN



INSERT INTO `r_listener_stats` (`date`, `stream_id`, `listeners`, `average`, `ips`)

SELECT CAST(NOW() AS DATE), `stream`, COUNT(DISTINCT client_id), AVG(TIMESTAMPDIFF(MINUTE, started, finished)), COUNT(DISTINCT client_ip)

FROM `r_listener`

WHERE TODAY(`started`) AND `finished` IS NOT NULL

GROUP BY `stream`;



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `shuffle_channel` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`mor`@`%` PROCEDURE `shuffle_channel`(IN `s_id` INT)
    NO SQL
BEGIN

SET @col := 0;

SET @acc := 0;



UPDATE r_link AS target

  INNER JOIN (

      SELECT @col := @col + 1 AS col, @acc AS acc, @acc := @acc + t.duration, t.id FROM

        (

               SELECT

                 r_link.*,

                 r_tracks.*

               FROM r_link

                 INNER JOIN r_tracks ON r_link.track_id = r_tracks.tid

               WHERE r_link.stream_id = s_id ORDER BY RAND()

        ) AS t

             ) AS source ON target.id = source.id



SET

  target.t_order     = source.col,

  target.time_offset = source.acc;



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `mor_plans_view`
--

/*!50001 DROP VIEW IF EXISTS `mor_plans_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mor`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `mor_plans_view` AS select `p`.`plan_id` AS `plan_id`,`p`.`plan_name` AS `plan_name`,`p`.`plan_duration` AS `plan_duration`,`p`.`plan_period` AS `plan_period`,`p`.`plan_value` AS `plan_value`,`p`.`limit_id` AS `limit_id`,`l`.`streams_max` AS `streams_max`,`l`.`time_max` AS `time_max`,`l`.`min_track_length` AS `min_track_length`,`l`.`max_listeners` AS `max_listeners` from (`mor_plans` `p` join `mor_limits` `l` on((`p`.`limit_id` = `l`.`limit_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `mor_playlists_view`
--

/*!50001 DROP VIEW IF EXISTS `mor_playlists_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mor`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `mor_playlists_view` AS select `mor_playlists_link`.`link_id` AS `link_id`,`mor_playlists_link`.`playlist_id` AS `playlist_id`,`mor_playlists_link`.`position_id` AS `position_id`,`r_tracks`.`tid` AS `track_id`,`r_tracks`.`uid` AS `user_id`,`r_tracks`.`filename` AS `filename`,`r_tracks`.`ext` AS `file_extension`,`r_tracks`.`artist` AS `artist`,`r_tracks`.`title` AS `title`,`r_tracks`.`album` AS `album`,`r_tracks`.`track_number` AS `track_number`,`r_tracks`.`genre` AS `genre`,`r_tracks`.`date` AS `date`,`r_tracks`.`cue` AS `cue`,`r_tracks`.`duration` AS `duration`,`r_tracks`.`filesize` AS `filesize`,`r_tracks`.`color` AS `color`,`r_tracks`.`uploaded` AS `uploaded`,`r_tracks`.`is_new` AS `is_new` from (`r_tracks` join `mor_playlists_link` on((`r_tracks`.`tid` = `mor_playlists_link`.`track_id`))) order by `mor_playlists_link`.`position_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `mor_stream_stats_view`
--

/*!50001 DROP VIEW IF EXISTS `mor_stream_stats_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mor`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `mor_stream_stats_view` AS select `a`.`sid` AS `sid`,`a`.`permalink` AS `permalink`,`a`.`uid` AS `uid`,`a`.`started` AS `started`,`a`.`started_from` AS `started_from`,`a`.`status` AS `status`,`b`.`tracks_count` AS `tracks_count`,`b`.`tracks_duration` AS `tracks_duration`,`b`.`listeners_count` AS `listeners_count`,`b`.`bookmarks_count` AS `bookmarks_count` from (`r_streams` `a` join `r_static_stream_vars` `b` on((`a`.`sid` = `b`.`stream_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `mor_stream_tracklist_view`
--

/*!50001 DROP VIEW IF EXISTS `mor_stream_tracklist_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mor`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `mor_stream_tracklist_view` AS select `r_tracks`.`tid` AS `tid`,`r_tracks`.`file_id` AS `file_id`,`r_tracks`.`uid` AS `uid`,`r_tracks`.`filename` AS `filename`,`r_tracks`.`ext` AS `ext`,`r_tracks`.`artist` AS `artist`,`r_tracks`.`title` AS `title`,`r_tracks`.`album` AS `album`,`r_tracks`.`track_number` AS `track_number`,`r_tracks`.`genre` AS `genre`,`r_tracks`.`date` AS `date`,`r_tracks`.`cue` AS `cue`,`r_tracks`.`buy` AS `buy`,`r_tracks`.`duration` AS `duration`,`r_tracks`.`filesize` AS `filesize`,`r_tracks`.`color` AS `color`,`r_tracks`.`uploaded` AS `uploaded`,`r_link`.`id` AS `id`,`r_link`.`stream_id` AS `stream_id`,`r_link`.`track_id` AS `track_id`,`r_link`.`t_order` AS `t_order`,`r_link`.`unique_id` AS `unique_id`,`r_link`.`time_offset` AS `time_offset`,`r_tracks`.`is_new` AS `is_new`,`r_tracks`.`can_be_shared` AS `can_be_shared` from (`r_tracks` join `r_link` on((`r_tracks`.`tid` = `r_link`.`track_id`))) order by `r_link`.`stream_id`,`r_link`.`t_order` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `mor_users_view`
--

/*!50001 DROP VIEW IF EXISTS `mor_users_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mor`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `mor_users_view` AS select `r_users`.`uid` AS `uid`,`r_users`.`mail` AS `mail`,`r_users`.`login` AS `login`,`r_users`.`password` AS `password`,`r_users`.`name` AS `name`,`r_users`.`info` AS `info`,`r_users`.`rights` AS `rights`,`r_users`.`registration_date` AS `registration_date`,`r_users`.`last_visit_date` AS `last_visit_date`,`r_users`.`permalink` AS `permalink`,`r_users`.`avatar` AS `avatar`,`r_users`.`country_id` AS `country_id`,`r_static_user_vars`.`user_id` AS `user_id`,`r_static_user_vars`.`tracks_count` AS `tracks_count`,`r_static_user_vars`.`tracks_duration` AS `tracks_duration`,`r_static_user_vars`.`tracks_size` AS `tracks_size`,`r_static_user_vars`.`streams_count` AS `streams_count`,ifnull((select `mor_payments`.`plan_id` from `mor_payments` where ((`mor_payments`.`user_id` = `r_users`.`uid`) and `mor_payments`.`success`) order by `mor_payments`.`payment_id` desc limit 1),1) AS `plan_id`,(select `mor_payments`.`expires` from `mor_payments` where ((`mor_payments`.`user_id` = `r_users`.`uid`) and `mor_payments`.`success`) order by `mor_payments`.`expires` desc limit 1) AS `plan_expires` from (`r_users` join `r_static_user_vars` on((`r_users`.`uid` = `r_static_user_vars`.`user_id`))) */;
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

-- Dump completed on 2018-02-28  9:35:41
