-- MySQL dump 10.13  Distrib 5.6.51, for Linux (x86_64)
--
-- Host: localhost    Database: mor
-- ------------------------------------------------------
-- Server version	5.6.51

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
) ENGINE=InnoDB AUTO_INCREMENT=28218 DEFAULT CHARSET=utf8;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `file.when.added` AFTER INSERT ON `fs_file` FOR EACH ROW UPDATE fs_list SET files_count = files_count + 1 WHERE fs_id = NEW.server_id */;;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `file.when.deleted` AFTER DELETE ON `fs_file` FOR EACH ROW UPDATE fs_list SET files_count = files_count - 1 WHERE fs_id = OLD.server_id */;;
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
INSERT INTO `fs_list` VALUES (1,1,1,'fs1.myownradio.biz',5784);
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
INSERT INTO `mor_comment` VALUES (1,88,1,'Perfect stream! Thanks!',1425140899);
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
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8;
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
-- Temporary table structure for view `mor_plans_view`
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
INSERT INTO `mor_playlists` VALUES (1,1,'My Test Playlist');
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
-- Temporary table structure for view `mor_playlists_view`
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
INSERT INTO `mor_promo_codes` VALUES ('3758649255931190',3,1533116800,989);
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
INSERT INTO `mor_servers_list` VALUES (1,'FS',1,1,1),(2,'STREAMER',1,1,1),(3,'FS',2,0,0);
/*!40000 ALTER TABLE `mor_servers_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `mor_stream_stats_view`
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
-- Temporary table structure for view `mor_stream_tracklist_view`
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
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `like.when.added` AFTER INSERT ON `mor_track_like` FOR EACH ROW IF (NEW.relation = "like") THEN
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
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `like.when.deleted` AFTER DELETE ON `mor_track_like` FOR EACH ROW IF (OLD.relation = "like") THEN
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
-- Temporary table structure for view `mor_users_view`
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
INSERT INTO `r_categories` VALUES (1,'Electronic (House, Trance, Breakbeat)','electronic',13),(2,'Easy Electronic (Chillout, Ambient, Space Music)','relax-electronic',6),(3,'Jazz (Jazz, Blues, Country)','jazz',1),(4,'Lounge (Acid Jazz, Lounge, Smooth Jazz)','lounge',4),(5,'Indie (Indie Rock, Chillwave, Synthpop, Indie)','indie',1),(6,'Pop (International Pop, ex-USSR Pop, Britpop)','pop',13),(7,'Talks','talks',5),(8,'New Age (Ethno, Celtic, Enigmatic, World, Folk)','new-age',5),(9,'Classical & Neoclassical','classical',2),(10,'Instrumental Music','instrumental',1),(11,'Reggae (Early, Roots, Dub)','reggae',0),(12,'Rap (Soul, R\'n\'B)','rap',10),(13,'Rock (Classic Rock, Metal, Alternative, Prog)','rock',7),(14,'Oldies','oldies',4),(15,'Psy (Psybient, Psychill, Psytrance)','psy',4),(16,'Uncategorized','uncategorized',41);
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
) ENGINE=InnoDB AUTO_INCREMENT=22101 DEFAULT CHARSET=utf8;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `rlink.when.added` AFTER INSERT ON `r_link` FOR EACH ROW BEGIN

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
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `rlink.when.modified` AFTER UPDATE ON `r_link` FOR EACH ROW BEGIN
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
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `rlink.when.deleted` BEFORE DELETE ON `r_link` FOR EACH ROW BEGIN

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
) ENGINE=InnoDB AUTO_INCREMENT=48380 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_listener`
--

LOCK TABLES `r_listener` WRITE;
/*!40000 ALTER TABLE `r_listener` DISABLE KEYS */;
/*!40000 ALTER TABLE `r_listener` ENABLE KEYS */;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `when.listener.added` AFTER INSERT ON `r_listener` FOR EACH ROW BEGIN
    UPDATE r_static_stream_vars set listeners_count = listeners_count + 1, playbacks = playbacks + 1 WHERE stream_id = NEW.stream;
    UPDATE r_static_user_vars set listeners_count = listeners_count + 1 WHERE user_id = (SELECT uid FROM r_streams WHERE sid = NEW.stream);
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
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `when.listener.finished` AFTER UPDATE ON `r_listener` FOR EACH ROW IF NEW.finished IS NOT NULL THEN
    UPDATE r_static_stream_vars
    SET listeners_count = listeners_count - 1, summary_played = summary_played + TIMESTAMPDIFF(SECOND, NEW.started, NEW.finished)
    WHERE stream_id = NEW.stream;
    UPDATE r_static_user_vars set listeners_count = listeners_count - 1 WHERE user_id = (SELECT uid FROM r_streams WHERE sid = NEW.stream);
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
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `when.listener.deleted` AFTER DELETE ON `r_listener` FOR EACH ROW IF OLD.finished IS NULL THEN
    UPDATE r_static_stream_vars SET listeners_count = listeners_count - 1 WHERE stream_id = OLD.stream AND listeners_count > 0;
    UPDATE r_static_user_vars set listeners_count = listeners_count - 1 WHERE user_id = (SELECT uid FROM r_streams WHERE sid = OLD.stream);
END IF */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `r_listener_stats`
--

DROP TABLE IF EXISTS `r_listener_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_listener_stats` (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `date` date NOT NULL,
                                    `stream_id` int(11) NOT NULL,
                                    `listeners` int(11) NOT NULL,
                                    `ips` int(11) NOT NULL,
                                    `average` int(11) NOT NULL,
                                    PRIMARY KEY (`id`),
                                    KEY `stream_id` (`stream_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5178 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_listener_stats`
--

LOCK TABLES `r_listener_stats` WRITE;
/*!40000 ALTER TABLE `r_listener_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `r_listener_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_modules`
--

DROP TABLE IF EXISTS `r_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_modules` (
                             `name` varchar(64) NOT NULL,
                             `html` text,
                             `css` text,
                             `js` text,
                             `tmpl` text,
                             `post` text NOT NULL,
                             `uid` int(11) NOT NULL DEFAULT '1',
                             `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                             `alias` varchar(255) NOT NULL,
                             PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_modules`
--

LOCK TABLES `r_modules` WRITE;
/*!40000 ALTER TABLE `r_modules` DISABLE KEYS */;
INSERT INTO `r_modules` VALUES ('larize','<!DOCTYPE html>\n<html>\n    <head>\n        <title>Larize Module Editor</title>\n        <meta charset=\"UTF-8\">\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n        <link rel=\"stylesheet\" href=\"/css/reset.css\" />\n        <link rel=\"stylesheet\" href=\"/icomoon/style.css\" />\n        <link rel=\"icon\" type=\"image/png\" href=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAAEEfUpiAAAACXBIWXMAAC4jAAAuIwF4pT92AAABNmlDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjarY6xSsNQFEDPi6LiUCsEcXB4kygotupgxqQtRRCs1SHJ1qShSmkSXl7VfoSjWwcXd7/AyVFwUPwC/0Bx6uAQIYODCJ7p3MPlcsGo2HWnYZRhEGvVbjrS9Xw5+8QMUwDQCbPUbrUOAOIkjvjB5ysC4HnTrjsN/sZ8mCoNTIDtbpSFICpA/0KnGsQYMIN+qkHcAaY6addAPAClXu4vQCnI/Q0oKdfzQXwAZs/1fDDmADPIfQUwdXSpAWpJOlJnvVMtq5ZlSbubBJE8HmU6GmRyPw4TlSaqo6MukP8HwGK+2G46cq1qWXvr/DOu58vc3o8QgFh6LFpBOFTn3yqMnd/n4sZ4GQ5vYXpStN0ruNmAheuirVahvAX34y/Axk/96FpPYgAAACBjSFJNAAB6JQAAgIMAAPn/AACA6AAAUggAARVYAAA6lwAAF2/XWh+QAAACX0lEQVR42mL8//8/AzJgYWBgYEha9QguysSABmACJjABAAAAAP//YkQ3gwlmBswcxsSVD1GUMM0Lk2NE4ocBAAAA///CMAMdoLgLWTdU7CzMWWehgruRNCszMDCcJWgFAAAA//8iqICJgQCAOfIuAwODEprcvXlhcsowE5QYGBjezwuTY0TyiRLcBCgQRI4GFCtgAEs4MMB9gU33vDA5RoLeBAAAAP//IqiAqGBABkmrHrkwMDDsxqOnYl6YXCeGC5JWPVrFwMAQiqRQaF6Y3Hskg9HlXeeFye1BdkEomk3vklY9wuf63QwMDIwsuGTREi5xgZi06tE7BgYGQSLDb9a8MLl0imOBiYFCAAAAAP//GngXUGwAtpS4CkuawBm9yClRiYGB4S6RFivPC5O7h+4CZM1n54XJmaC57D+aWkbkwrQDzZkmWGx9jy8MyvHYRp9YWI0tq5KUErE4Gx7SaN6bycDAkAaLTuRYMGFgYDiDHNIEygPUMJgXJncWauosIlzeOYwyEwAAAP//7Fa7EcMgDH0rZAGKrMAKzghmAIqsYK/gEZKCAWAFj0BWcMEkaVTojBHx5+IU0R0dd3ogvc/uJ5z+gsPlRCrrU8csSBeuBcoskzMqbB6B9ekC4CFp24p6ATBLxMoAUOO4kHi4Kg1ka9y5WwB3AI0AZHRG3SRfreWKqsZYn/RMETISO6P6EoAozPaw4rY0Z4E+mwWTMPuP89IeHegr841bG1mftPUpkp2ILLjSEtVC3pO4PhQWsWEnC4Xf1oH1AAqgWrYjXeWXRjoAEJZE6G9GPwHgPQB3hQShcxkghwAAAABJRU5ErkJggg==\" />\n        <!-- include:css -->\n        <script src=\"/js/jquery-1.11.0.min.js.gz\"></script>\n        <script src=\"/js/jquery-migrate-1.2.1.min.js.gz\"></script>\n        <script src=\"/js/jquery-ui.min.js.gz\"></script>\n        <script src=\"/js/jquery.livequery.js.gz\"></script>\n        <script src=\"/js/jquery.tmpl.js\"></script>\n        <script src=\"/js/jquery.change.js\"></script>\n        <!-- include:js -->\n        <script src=\"/includes/ace/ace.js\"></script>\n        <script src=\"/includes/ace/ext-language_tools.js\"></script>\n    </head>\n    <body\">\n        <div id=\"ajax\">Working...</div>\n        <div class=\"page_wrap\">\n            <!-- include:tmpl -->\n            <div class=\"page_content\">\n                <table class=\"editor\">\n                    <tr>\n                        <td id=\"list\">\n                            <div id=\"lrz-resizer\"></div>\n                            <h1 id=\"title\">.larize</h1>\n                            Modules:\n                            <ul id=\"ml\"></ul>\n                            <input id=\"create\" type=\"button\" value=\"New module...\" />\n                        </td>\n                        <td id=\"editor\">\n                            <div id=\"tabs\">\n                                <ul class=\"hz mlist\"></ul>\n                            </div>\n                            <div class=\"editor\">\n                                <input class=\"saveModule\" type=\"button\" value=\"Save Module\" />\n                                <input class=\"saveSection\" type=\"button\" value=\"Save Section\" />\n                                <ul class=\"sections hz\">\n                                    <li data-section=\"html\" class=\"html\">code</li>\n                                    <li data-section=\"css\" class=\"css\">stylesheet</li>\n                                    <li data-section=\"js\" class=\"js\">javascript</li>\n                                    <li data-section=\"tmpl\" class=\"tmpl\">templates</li>\n                                    <li data-section=\"post\" class=\"post\">post</li>\n                                </ul>\n                                <div class=\"switchers\">\n                                    \n                                </div>\n                            </div>\n                        </td>\n                    </tr>\n                </table>\n            </div>\n        </div>\n    </body>\n</html>\n','body, html {\r\n    width: 100%;\r\n    min-height: 100%;\r\n    font-family: tahoma;\r\n    font-size: 10pt;\r\n}\r\n\r\ndiv#ajax {\r\n    position: absolute;\r\n    left: 50%;\r\n    top: 0;\r\n    padding: 4px 8px;\r\n    background-color: #00f;\r\n    color: #fff;\r\n    cursor: default;\r\n}\r\n\r\ndiv#ajax:not(.visible) {\r\n    display: none;\r\n}\r\n\r\nh1#title {\r\n    font-size: 28pt;\r\n    padding-bottom: 16px;\r\n    color: #39f;\r\n}\r\n\r\n.page_wrap {\r\n    width: 100%;\r\n    min-height: 100%;\r\n}\r\n\r\n.page_content {\r\n    min-height: 100%;\r\n}\r\n\r\nul#ml {\r\n    box-sizing: border-box;\r\n    width: 100%;\r\n    margin-top: 8px;\r\n    overflow-x: hidden;\r\n    overflow-y: auto;\r\n}\r\n\r\nul#ml > li {\r\n    padding: 0px 8px;\r\n    margin-bottom: 4px;\r\n}\r\n\r\nul#ml > li:not(.delimiter) {\r\n    cursor: pointer;\r\n    font-size: 9pt;\r\n}\r\n\r\nul#ml > li.delimiter {\r\n    padding-top: 8px;\r\n    border-bottom: 1px solid #eee;\r\n    font-weight: bold;\r\n    color: #39f;\r\n}\r\n\r\nul#ml > li.active {\r\n    color: #39c;\r\n}\r\n\r\nul.mlaction {\r\n    float: right;\r\n    overflow: hidden;\r\n}\r\n\r\nul.mlaction > li {\r\n    float: left;\r\n    opacity: 0.3;\r\n}\r\n\r\nul.mlaction > li.active {\r\n    opacity: 1;\r\n}\r\n\r\ntable.editor {\r\n    width: 100%;\r\n    height: 100%;\r\n}\r\n\r\ntable.editor td {\r\n    vertical-align: top;\r\n    height: 100%;\r\n}\r\n\r\ntable.editor td#list {\r\n    width: 200px;\r\n    padding: 8px;\r\n    box-sizing: border-box;\r\n    border-right: 1px solid #eee;\r\n    height: 100%;\r\n    position: relative;\r\n}\r\n\r\ntable.editor td#editor {\r\n}\r\n\r\ndiv#tabs {\r\n    padding: 8px;\r\n    box-sizing: border-box;\r\n}\r\n\r\nul.hz {\r\n    overflow: auto;\r\n}\r\n\r\nul.hz > li {\r\n    float: left;\r\n}\r\n\r\nul.mlist > li,\r\nul.sections > li {\r\n    padding: 8px 16px;\r\n    border: 1px solid #eee;\r\n    margin-right: 4px;\r\n    margin-bottom: 4px;\r\n    cursor: pointer;\r\n    transition: border-color linear 250ms;\r\n}\r\n\r\nul.mlist > li:hover,\r\nul.sections > li:hover {\r\n    padding: 8px 16px;\r\n    border: 1px solid #eee;\r\n    margin-right: 4px;\r\n    cursor: pointer;\r\n    border-color: #39f;\r\n}\r\n\r\n\r\nul.mlist > li.modified:before,\r\nul.sections > li.modified:before {\r\n    content : \"*\";\r\n    padding-left: 4px;\r\n}\r\n\r\nul.mlist > li.active,\r\nul.sections > li.active {\r\n    border: 1px solid #39f;\r\n    background: #08f; \r\n    color: #fff;\r\n}\r\n\r\ndiv.editor {\r\n    border-top: 1px solid #eee;\r\n    box-sizing: border-box;\r\n    padding: 8px;\r\n    display: none;\r\n}\r\n\r\ndiv.editor.visible {\r\n    display: block;\r\n}\r\n\r\n.switchers {\r\n    box-sizing: border-box;\r\n}\r\n\r\ndiv.editor .textarea {\r\n    width: 100%;\r\n    height: 100%;\r\n    margin-top: 8px;\r\n    resize: none;\r\n    outline: none;\r\n    border: 1px solid #eee;\r\n    position: relative;\r\n}\r\n\r\ndiv.editor .textarea.modified {\r\n    border-color: #f66;\r\n}\r\n\r\nul.mlist:empty {\r\n    display: none;\r\n}\r\n\r\ndiv.cls {\r\n    width: 8px;\r\n    height: 8px;\r\n    background: url(\"/images/iconCloseWhite.png\") no-repeat center center;\r\n    display: inline-block;\r\n    margin-left: 8px;\r\n    vertical-align: middle;\r\n}\r\n\r\ninput[type=\"button\"] {\r\n    padding: 8px 16px;\r\n    border: 1px solid #eee;\r\n    cursor: pointer;\r\n    background: #fff;\r\n    color: #000;\r\n    outline: none;\r\n    transition: border-color linear 250ms;\r\n    margin: 0 0 0 8px;\r\n}\r\n\r\ninput[type=\"button\"]:hover {\r\n    border-color: #39f;\r\n}\r\n\r\n.saveModule, .saveSection {\r\n    float: right;\r\n}\r\n\r\n.saveModule:not(.visible),\r\n.saveSection:not(.visible) {\r\n    display: none;\r\n}\r\n\r\ninput[type=\"button\"]#create {\r\n    margin-top: 8px;\r\n}\r\n\r\nspan[class*=\"icon-\"] {\r\n    margin-left: 8px;\r\n    font-size: 7pt;\r\n}\r\n\r\n#lrz-resizer {\r\n    width:3px;\r\n    height: 100%;\r\n    top: 0;\r\n    right: 0;\r\n    background: transparent;\r\n    position: absolute;\r\n    cursor: col-resize;\r\n}','(function(){\n    var settings = {\n        \'title\' : \"Larize Module Editor\"\n    };\n    var openedModules = [];\n    var activeModule = null\n    var userToken = null;\n    var editor = [];\n    \n    var oldX = 0;\n    var resize = false;\n    var savedWidth = 0;\n    \n    $(document).ready(function(){\n        userToken = $(\"body\").attr(\"token\");\n        $.ajaxSetup({\n            headers: { \'My-Own-Token\': userToken }\n        });\n        loadModules();\n        resizeWindow();\n        $(\"td#list\").resizable({\n            handles: \'e, w\'\n        });\n    })\n    .ajaxSend(function(){$(\"#ajax\").addClass(\"visible\");})\n    .ajaxComplete(function(){$(\"#ajax\").removeClass(\"visible\");})\n    .ajaxError(function(){$(\"#ajax\").removeClass(\"visible\");})\n    .bind(\"mousedown\", function(event){\n        if($(event.target).is(\"div#lrz-resizer\"))\n        {\n            oldX = event.pageX;\n            savedWidth = $(\"td#list\").width();\n            resize = true;\n            event.stopPropagation();\n        }\n    })\n    .bind(\"mouseup\", function(event){\n        if(resize)\n        {\n            resize = false;\n            event.stopPropagation();\n        }\n    })\n    .bind(\"mousemove\", function(event){\n        if(resize) {\n            event.stopPropagation();\n            var delta = event.pageX - oldX;\n            $(\"td#list\").width(savedWidth + delta);\n            return false;\n        }\n    });\n    \n    $(window).bind(\'resize\', function(){\n        resizeWindow();\n    });\n    \n    function loadModules()\n    {\n        $.post(\"\", { action : \"list\", authtoken : userToken }, function(p){\n            $(\"ul#ml\").empty();\n            var pattern = $(\"#moduleList\");\n            if(p.error !== undefined)\n            {\n                alert(p.error);\n                return;\n            }\n            var lastPrefix = \"\";\n            for(var m in p)\n            {\n                var prefix = (p[m].name.indexOf(\'.\') > -1) ? (p[m].name.substr(0, p[m].name.indexOf(\'.\'))) : p[m].name;\n                if(prefix !== lastPrefix)\n                {\n                    $(\"<li>\").addClass(\"delimiter\").text(prefix).appendTo(\"ul#ml\");\n                }\n                if(p[m].alias.length > 0)\n                {\n                    p[m].active = \"active\";\n                }\n                pattern.tmpl(p[m]).appendTo(\"ul#ml\");\n                lastPrefix = prefix;\n            }\n        });        \n    }\n    \n    function addNewModule(data)\n    {\n        $(\"#moduleList\").tmpl(data).appendTo(\"ul#ml\");\n    }\n    \n    function openModule(name)\n    {\n        $.post(\"\", { action : \"get\", module : name, authtoken : userToken }, function(p){\n            try {\n                if(p.error !== undefined)\n                {\n                    alert(p.error);\n                    return;\n                }\n                openedModules[name] = {\n                    html : { modified : false, data : p.html },\n                    css  : { modified : false, data : p.css },\n                    js   : { modified : false, data : p.js },\n                    tmpl : { modified : false, data : p.tmpl },\n                    post : { modified : false, data : p.post },\n                    _loc : \'html\'\n                };\n            }\n            catch(e)\n            {\n                console.log(e);\n            }\n            openEditorTab(name, \"html\");\n            loadEditorContents();\n        });\n    }\n    \n    function loadEditorContents()\n    {\n        var uid = \"ea-\" + activeModule.module + \"-\" + activeModule.section;\n        if(editor[uid].getSession().getValue() !== openedModules[activeModule.module][activeModule.section].data)\n        {\n            editor[uid].getSession().setValue(openedModules[activeModule.module][activeModule.section].data);\n            openedModules[activeModule.module][activeModule.section].modified = false;\n            updateModifiers();\n        }\n    }\n    \n    function openEditorTab(module, section)\n    {\n        if(openedModules[module] === undefined)\n        {\n            console.log(\"No module\");\n            return;\n        }\n        \n        activeModule = { module : module, section : section, modified : false };\n\n        var uid = \"ea-\" + module + \"-\" + section;\n        \n        $(\".switchable\").hide();\n        $(\".editor\").addClass(\"visible\");\n        document.title = module + \" \\u2014 \" + settings.title;\n        \n        var selector = $(\".switchable\").filter(function(){ return $(this).attr(\"id\") === uid; });\n\n        console.log(\"Open Editor\", editor[uid] !== undefined, selector.length);\n\n        if(editor[uid] !== undefined)\n        {\n            selector.show();\n        }\n        else\n        {\n            selector.remove();\n            $(\"<pre>\")\n                .addClass(\"switchable textarea\")\n                .attr({ \'id\' : uid })\n                .attr({ \'data-module\' : module, \'data-section\' : section })\n                .text(openedModules[activeModule.module][activeModule.section].data)\n                .appendTo($(\"div.switchers\"));\n        \n            editor[uid] = ace.edit(uid);\n            \n            if(section === \"html\")\n                editor[uid].session.setMode(\"ace/mode/php\");\n            else if(section === \"js\")\n                editor[uid].session.setMode(\"ace/mode/javascript\");\n            else if(section === \"css\")\n                editor[uid].session.setMode(\"ace/mode/css\");\n            else if(section === \"tmpl\")\n                editor[uid].session.setMode(\"ace/mode/html\");\n            else if(section === \"post\")\n                editor[uid].session.setMode(\"ace/mode/php\");\n            \n            editor[uid].setTheme(\"ace/theme/chrome\");\n            editor[uid].setFontSize(\"10pt\");\n            editor[uid].setOptions({\n                enableBasicAutocompletion: false,\n                enableSnippets: true,\n                enableLiveAutocompletion: false\n            }); \n            \n            editor[uid].getSession().on(\"change\", function(element, target){\n                    $(\"ul.sections > li[data-section=\'\"+activeModule.section+\"\']\").addClass(\"modified\");\n                    $(\"ul.mlist > li[data-module=\'\"+activeModule.module+\"\']\").addClass(\"modified\");\n                    openedModules[activeModule.module][activeModule.section].modified = true;\n                    openedModules[activeModule.module][activeModule.section].data = editor[uid].getSession().getValue();\n                    updateModifiers();\n            });\n            \n            editor[uid].commands.addCommand({\n                name: \'saveCommand\',\n                    bindKey: {\n                        win: \'Ctrl-S\',\n                        mac: \'Command-S\',\n                        sender: \'editor\'\n                    }, exec: function(env, args, request) { \n                        if(openedModules[activeModule.module][activeModule.section].modified)\n                        {\n                            saveCurrentSection();\n                        }\n                    }\n            });\n            \n            editor[uid].commands.addCommand({\n                name: \'saveAllCommand\',\n                    bindKey: {\n                        win: \'Ctrl-M\',\n                        mac: \'Command-M\',\n                        sender: \'editor\'\n                    }, exec: function(env, args, request) { \n                        if(openedModules[activeModule.module][activeModule.section].modified)\n                        {\n                            saveCurrentModule();\n                        }\n                    }\n            });\n            \n\n        }\n        \n        openedModules[module]._loc = section;\n        \n        updateSelectors();\n        updateModifiers();\n        resizeWindow();\n        \n        editor[uid].focus();\n\n    }\n    \n    function resizeWindow()\n    {\n        $(\".switchers\").height($(window).height() - $(\".switchers\").position().top - 8);\n        $(\"ul#ml\").height($(window).height() - $(\"ul#ml\").position().top - $(\"#create\").outerHeight() - 28);\n    }\n    \n    function updateModifiers()\n    {\n\n        $(\".textarea\").removeClass(\"modified\");\n        $(\"ul.sections > li\").removeClass(\"modified\");\n        $(\"ul.mlist > li\").removeClass(\"modified\");\n        $(\".saveModule.visible\").removeClass(\"visible\");\n        $(\".saveSection.visible\").removeClass(\"visible\");\n        \n        if(activeModule !== null && openedModules[activeModule.module] !== undefined)\n        {\n            if(openedModules[activeModule.module][activeModule.section].modified)\n            {\n                $(\".saveSection\").addClass(\"visible\");\n            }\n\n            for(var sec in openedModules[activeModule.module])\n            {\n                if(openedModules[activeModule.module][sec].modified)\n                {\n                    $(\"ul.sections > li[data-section=\'\"+sec+\"\']\").addClass(\"modified\");\n                    $(\".saveModule\").addClass(\"visible\");\n                }\n            }\n        }\n        \n        for(var i in openedModules)\n        {\n            var data = openedModules[i];\n            \n            for(var sec in data)\n            {\n                if(data[sec].modified)\n                {\n                    $(\".textarea[data-module=\'\"+i+\"\']\").addClass(\"modified\");\n                    $(\"ul.mlist > li[data-module=\'\"+i+\"\']\").addClass(\"modified\");\n                }\n                \n            }\n        }\n        \n    }\n    \n    function updateSelectors()\n    {\n        $(\"ul.mlist\").empty();\n                \n        for(var i in openedModules)\n        {\n            var data = openedModules[i];\n            $(\"#openedList\").tmpl({name:i}).appendTo($(\"ul.mlist\"));\n        }\n\n        $(\"ul#ml > li.active\").removeClass(\"active\");\n        $(\"ul.mlist > li.active\").removeClass(\"active\");\n        $(\"ul.sections > li.active\").removeClass(\"active\");\n\n        if(activeModule)\n        {\n            $(\"ul#ml > li[data-module=\'\"+activeModule.module+\"\']\").addClass(\"active\");\n            $(\"ul.mlist > li\").filter(\"[data-module=\\\"\"+activeModule.module+\"\\\"]\").addClass(\"active\");\n            $(\"ul.sections > li\").filter(\".\"+activeModule.section).addClass(\"active\");\n        }\n        \n        documentHider();\n        \n    }\n    \n    function documentHider()\n    {\n        if($(\"ul.mlist > li.active\").length > 0)\n        {\n            $(\".editor\").addClass(\"visible\");\n        }\n        else\n        {\n            $(\".editor\").removeClass(\"visible\");\n            document.title = settings.title;\n        }\n    }\n    \n    function saveCurrentModule()\n    {\n        var post = {\n            action    : \"save\",\n            module    : activeModule.module, \n            authtoken : userToken\n        };\n        \n        for (var i in openedModules[activeModule.module])\n        {\n            if(openedModules[activeModule.module][i].modified)\n                post[i] = openedModules[activeModule.module][i].data;\n        }\n       \n        $.post(\"\", post, function(p){\n            try {\n                if(p.error !== undefined)\n                {\n                    alert(p.error);\n                    return;\n                }\n                if(p.save !== undefined)\n                {\n                    openedModules[activeModule.module].html.modified = false;\n                    openedModules[activeModule.module].js.modified = false;\n                    openedModules[activeModule.module].css.modified = false;\n                    openedModules[activeModule.module].tmpl.modified = false;\n                    openedModules[activeModule.module].post.modified = false;\n                    loadModules();\n                    updateModifiers();\n                }\n            }\n            catch(e)\n            {\n                \n            }\n\n        });\n    }\n\n    function saveCurrentSection()\n    {\n        var post = {\n            action    : \"save\",\n            module    : activeModule.module,\n            authtoken : userToken\n        };\n        \n        if(openedModules[activeModule.module][activeModule.section].modified)\n        {\n            post[activeModule.section] = openedModules[activeModule.module][activeModule.section].data;\n        }\n       \n        $.post(\"\", post, function(p){\n            try {\n                if(p.error !== undefined)\n                {\n                    alert(p.error);\n                    return;\n                }\n                if(p.save !== undefined)\n                {\n                    openedModules[activeModule.module][activeModule.section].modified = false;\n                    loadModules();\n                    updateModifiers();\n                }\n            }\n            catch(e)\n            {\n                \n            }\n\n            console.log(data);\n        });\n    }\n    \n    function closeModule(module)\n    {\n        var uid = \"ea-\" + module;\n        for(var i in editor)\n        {\n            if(i.indexOf(uid) === 0)\n            {\n                console.log(\"Closed: \" + i);\n                delete(editor[i]);\n                $(\".switchable\").filter(function(){return $(this).attr(\"id\") === i;}).remove();\n            }\n        }\n        delete(openedModules[module]);\n        $(\"ul#ml > li[data-module=\'\"+module+\"\'].active\").removeClass(\"active\");\n        $(\"ul.mlist > li[data-module=\'\"+module+\"\']\").remove();\n        if(activeModule !== null && module === activeModule.module)\n        { \n            activeModule = null; \n        }\n        updateSelectors()\n        updateModifiers();\n    }\n    \n    function createModule()\n    {\n        var name = prompt(\"Please enter the name for new module\", \"\");\n        if (name !== null) {\n            if(openedModules[name] !== undefined)\n            {\n                alert(\"Module already exists!\");\n            }\n            else\n            {\n                openedModules[name] = {\n                    html : { data : \"\", modified : true },\n                    css  : { data : \"\", modified : true },\n                    js   : { data : \"\", modified : true },\n                    tmpl : { data : \"\", modified : true },\n                    post : { data : \"\", modified : true },\n                    _loc : \"html\"\n                };\n                openEditorTab(name, \"html\");\n            }\n        }\n    }\n    \n    /* Bingings */\n    $(\"ul#ml > li:not(.delimiter)\").live(\"click\", function(){\n        openModule($(this).attr(\"data-module\"));\n    });\n    \n    $(\"ul.sections > li\").live(\"click\", function(){\n        var section = $(this).attr(\'data-section\');\n        openEditorTab(activeModule.module, section);\n    });\n    \n    $(\"ul.mlist > li\").live(\"click\", function(){\n        var module = $(this).attr(\'data-module\');\n        console.log(\"Open\");\n        if(openedModules[module] === undefined)\n        {\n            openEditorTab(module, \"html\");\n        }\n        else\n        {\n            openEditorTab(module, openedModules[module]._loc);\n        }\n    });\n    \n    $(\"#create\").live(\"click\", function(){\n        createModule();\n    });\n    \n    $(\".saveModule\").live(\"click\", function(){\n        saveCurrentModule();\n    });\n\n    $(\".saveSection\").live(\"click\", function(){\n        saveCurrentSection();\n    });\n    \n    $(\".closer\").live(\"click\", function(event){\n        event.stopPropagation();\n        var doc = $(this).attr(\"data-module\");\n        closeModule(doc);\n    });\n    \n    $(\".ml-alias\").live(\"click\", function(event){\n        event.stopPropagation();\n        var parent = $(this).parents(\".listModulesElement\");\n        var name = prompt(\"Please enter the alias for this module\", parent.attr(\"data-module-alias\"));\n        if (name !== null) {\n            $.post(\"\", {\n                action : \"alias\",\n                module : parent.attr(\"data-module\"),\n                alias  : name,\n                authtoken : userToken\n            }, function(data){\n                console.log(data);\n                loadModules();\n                updateSelectors();\n            });\n        }\n    });\n\n})();\n','<script type=\"text/x-jquery-tmpl\" id=\"moduleList\">\r\n    <li class=\"listModulesElement\" data-module=\"${name}\" data-module-alias=\"${alias}\">\r\n        <ul class=\"mlaction\">\r\n            <li class=\"ml-alias ${active}\" title=\"${alias}\">a</li>\r\n        </ul>\r\n        ${name}\r\n    </li>\r\n</script>\r\n<script type=\"text/x-jquery-tmpl\" id=\"openedList\">\r\n    <li data-module=\"${name}\">${name}<span title=\"Close module\" data-module=\"${name}\" class=\"icon-close closer\"></span></li>\r\n</script>','<?php\n\nuse Framework\\Services\\Database;\nuse Framework\\Services\\DB\\Query\\InsertQuery;\nuse Framework\\Services\\DB\\Query\\SelectQuery;\nuse Framework\\Services\\DB\\Query\\UpdateQuery;\nuse Framework\\Services\\HttpPost;\n\n$request = Framework\\Services\\HttpPost::getInstance();\n$post = HttpPost::getInstance();\n\n$action = $request->getParameter(\"action\")->getOrElseNull();\n\n$db = Database::getInstance()->connect();\n\nheader(\"Content-Type: application/json\");\n\nswitch($action) {\n    case \'list\':\n        $query = new SelectQuery(\"r_modules\");\n        $query->select(\"name\", \"alias\");\n        $query->orderBy(\"name\");\n        echo json_encode($query->fetchAll());\n        break;\n\n    case \'get\':\n        $module = $post->getParameter(\"module\")->getOrElseNull();\n        $query = new SelectQuery(\"r_modules\");\n        $query->where(\"name\", [$module]);\n        echo json_encode($query->fetchOneRow()->get());\n        break;\n\n    case \'alias\':\n        $module = $post->getParameter(\"module\")->getOrElseNull();\n        $alias = $post->getParameter(\"alias\")->getOrElseNull();\n\n        $query = new UpdateQuery(\"r_modules\");\n        $query->set(\"alias\", $alias)->where(\"name\", $module);\n\n        $resp = $query->executeUpdate();\n\n        echo json_encode(array(\"result\" => $resp));\n\n        break;\n\n    case \'save\':\n\n        $module = $post->getParameter(\"module\")->getOrElseNull();\n\n        $html = $post->getParameter(\"html\")->getOrElseNull();\n        $css = $post->getParameter(\"css\")->getOrElseNull();\n        $js = $post->getParameter(\"js\")->getOrElseNull();\n        $tmpl = $post->getParameter(\"tmpl\")->getOrElseNull();\n        $pst = $post->getParameter(\"post\")->getOrElseNull();\n\n        $args = [];\n\n        if(!is_null($html)) {\n            $args[\"html\"] = $html;\n        }\n\n        if(!is_null($css)) {\n            $args[\"css\"] = $css;\n        }\n\n        if(!is_null($js)) {\n            $args[\"js\"] = $js;\n        }\n\n        if(!is_null($tmpl)) {\n            $args[\"tmpl\"] = $tmpl;\n        }\n\n        if(!is_null($pst)) {\n            $args[\"post\"] = $pst;\n        }\n\n        $test = count((new SelectQuery(\"r_modules\"))->where(\"name\", $module));\n\n\n        $test = count((new SelectQuery(\"r_modules\"))->where(\"name\", $module));\n\n        if ($test > 0) {\n            $query = new UpdateQuery(\"r_modules\");\n            $query->set($args)->where(\"name\", $module);\n            $result = $db->executeUpdate($query);\n        } else {\n            $query = new InsertQuery(\"r_modules\");\n            $query->values($args)->values(\"name\", $module);\n            $result = $db->executeInsert($query);\n        }\n\n        echo json_encode([\"save\" => $result]);\n        break;\n}\n',1,'2014-12-23 20:37:25','larize'),('page.rm.charts','<!DOCTYPE html>\r\n<html>\r\n    <head>\r\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n        <title>Statistics @ My Own Radio Manager</title>\r\n        <!-- module:rm.common -->\r\n        <!-- include:css -->\r\n        <script type=\"text/javascript\" src=\"/includes/amcharts/amcharts.js.gz\"></script>\r\n        <script type=\"text/javascript\" src=\"/includes/amcharts/serial.js.gz\"></script>\r\n        <script type=\"text/javascript\" src=\"/includes/amcharts/light.js\"></script>\r\n        <script>\r\n        var stats = \"<?= base64_encode(json_encode(stats::getStatsGlobal(user::getCurrentUserId()))) ?>\";\r\n        </script>\r\n        <!-- include:js -->\r\n    </head>\r\n    <body class=\"partial library\">\r\n        <div id=\"jplayer\"></div>\r\n        <!-- module:rm.header -->\r\n        <div class=\"rm_body_wrap rm_max_width\">\r\n            <!-- module:rm.sidebar -->\r\n            <div class=\"rm_content_wrap\">\r\n                <div class=\"rm-common-wrapper\">\r\n                    <div class=\"rm-common-wrapper-header\">\r\n                        <h1><i class=\"icon-tools\"></i>Listening Statistics</h1>\r\n                        <div class=\"rm-chart-title\">Listeners per day</div>\r\n                        <div class=\"chart\" id=\"chartdiv1\"></div>\r\n                        <div class=\"rm-chart-title\">Average listening duration</div>\r\n                        <div class=\"chart\" id=\"chartdiv2\"></div>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </body>\r\n</html>','.chart {\n    width: 100%;\n    height: 400px;\n    margin-bottom: 16px;\n}\n\n.rm-chart-title {\n    padding: 4px;\n    text-align: center;\n    font-size: 14pt;\n}','$(document).ready(function(){\n    var chartData = generateChartData();\n    var chart1 = AmCharts.makeChart(\"chartdiv1\", {\n        \"type\": \"serial\",\n        \"theme\": \"light\",\n        \"pathToImages\": \"http://www.amcharts.com/lib/3/images/\",\n        \"dataProvider\": chartData,\n        \"valueAxes\": [{\n            \"axisAlpha\": 0.2,\n            \"dashLength\": 1,\n            \"position\": \"left\"\n        }],\n        \"graphs\": [{\n            \"id\":\"g1\",\n            \"balloonText\": \"[[value]]\",\n            \"bullet\": \"round\",\n            \"bulletBorderAlpha\": 1,\n         \"bulletColor\":\"#FFFFFF\",\n            \"hideBulletsCount\": 50,\n            \"title\": \"red line\",\n            \"valueField\": \"listeners\",\n      \"useLineColorForBulletBorder\":true\n        }],\n        \"chartScrollbar\": {\n            \"autoGridCount\": true,\n            \"graph\": \"g1\",\n            \"scrollbarHeight\": 40\n        },\n        \"chartCursor\": {\n            \"cursorPosition\": \"mouse\"\n        },\n        \"categoryField\": \"date\",\n        \"categoryAxis\": {\n            \"parseDates\": true,\n            \"axisColor\": \"#DADADA\",\n            \"dashLength\": 1,\n            \"minorGridEnabled\": true\n        },\n     \"exportConfig\":{\n       menuRight: \'20px\',\n          menuBottom: \'30px\',\n          menuItems: [{\n          icon: \'http://www.amcharts.com/lib/3/images/export.png\',\n          format: \'png\'   \n          }]  \n     }\n    });\n    \n    var chart2 = AmCharts.makeChart(\"chartdiv2\", {\n        \"type\": \"serial\",\n        \"theme\": \"light\",\n        \"pathToImages\": \"http://www.amcharts.com/lib/3/images/\",\n        \"dataProvider\": chartData,\n        \"valueAxes\": [{\n            \"axisAlpha\": 0.2,\n            \"dashLength\": 1,\n            \"position\": \"left\"\n        }],\n        \"graphs\": [{\n            \"id\":\"g1\",\n            \"balloonText\": \"[[value]]\",\n            \"bullet\": \"round\",\n            \"bulletBorderAlpha\": 1,\n         \"bulletColor\":\"#FFFFFF\",\n            \"hideBulletsCount\": 50,\n            \"title\": \"red line\",\n            \"valueField\": \"duration\",\n      \"useLineColorForBulletBorder\":true\n        }],\n        \"chartScrollbar\": {\n            \"autoGridCount\": true,\n            \"graph\": \"g1\",\n            \"scrollbarHeight\": 40\n        },\n        \"chartCursor\": {\n            \"cursorPosition\": \"mouse\"\n        },\n        \"categoryField\": \"date\",\n        \"categoryAxis\": {\n            \"parseDates\": true,\n            \"axisColor\": \"#DADADA\",\n            \"dashLength\": 1,\n            \"minorGridEnabled\": true\n        },\n     \"exportConfig\":{\n       menuRight: \'20px\',\n          menuBottom: \'30px\',\n          menuItems: [{\n          icon: \'http://www.amcharts.com/lib/3/images/export.png\',\n          format: \'png\'   \n          }]  \n     }\n    });\n    \n    chart1.addListener(\"rendered\", zoomChart);\n    chart2.addListener(\"rendered\", zoomChart);\n    zoomChart();\n    \n    // this method is called when chart is first inited as we listen for \"dataUpdated\" event\n    function zoomChart() {\n        // different zoom methods can be used - zoomToIndexes, zoomToDates, zoomToCategoryValues\n        chart1.zoomToIndexes(chartData.length - 40, chartData.length - 1);\n        chart2.zoomToIndexes(chartData.length - 40, chartData.length - 1);\n    }\n    \n    \n    // generate some random data, quite different range\n    function generateChartData() {\n    \n        \n        var chartData = [];\n        var firstDate = new Date();\n        firstDate.setDate(firstDate.getDate() - 5);\n        \n        var myStats = JSON.parse(atob(stats));\n        \n        myStats.forEach(function(el, i) {\n            chartData.push({\n                date: new Date(el.date_unix * 1000),\n                duration: el.average_listening,\n                listeners: el.listeners\n            });\n        });\n\n        return chartData;\n    }\n});\n\n\n','','',1,'2014-10-10 13:53:24','radiomanager/statistics'),('page.rm.home','<!DOCTYPE html>\r\n<html>\r\n    <head>\r\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n        <title>Audio Library @ My Own Radio Manager</title>\r\n        <!-- module:rm.common -->\r\n        <!-- include:css -->\r\n        <!-- include:js -->\r\n    </head>\r\n    <body class=\"partial library\">\r\n        <div id=\"jplayer\"></div>\r\n        <!-- module:rm.header -->\r\n        <div class=\"rm_body_wrap rm_max_width\">\r\n            <!-- module:rm.sidebar -->\r\n            <div class=\"rm_content_wrap\">\r\n                <!-- module:rm.home.status -->\r\n                <div class=\"rm_tracks_data\" content=\"<?= base64_encode(json_encode(track::getTracks(user::getCurrentUserId(), 0, config::getSetting(\"json\", \"tracks_per_query\")))) ?>\"></div>\r\n                <!-- module:rm.tracklist -->\r\n            </div>\r\n        </div>\r\n    </body>\r\n</html>','','','','<?php\r\n\r\n$from   = application::post(\"from\", 0, REQ_INT);\r\n$filter = application::post(\"filter\", \"\", REQ_STRING);\r\n\r\nif(strlen($filter)>0)\r\n{\r\n    $tracks = track::getFilteredTracks(user::getCurrentUserId(), misc::searchQueryFilter($filter), $from, config::getSetting(\"json\", \"tracks_per_query\"));\r\n} \r\nelse\r\n{\r\n    $tracks = track::getTracks(user::getCurrentUserId(), $from, config::getSetting(\"json\", \"tracks_per_query\"));\r\n}\r\n\r\necho json_encode($tracks);\r\n\r\n\r\n',1,'2014-10-20 19:13:06','radiomanager'),('page.rm.preferences','<?php\r\n    $userProfile = user::getUserByUid(user::getCurrentUserId());\r\n    $userAvatar = user::getUserAvatar(user::getCurrentUserId());\r\n    if(file_exists(folders::getUserPicturePath(user::getCurrentUserId())))\r\n    {\r\n        $profileAvatar = folders::getUserPicturePath(user::getCurrentUserId(), false) . \"?s=150&c=1\";\r\n    }\r\n    else\r\n    {\r\n        $profileAvatar = \"\";\r\n    }\r\n?>\r\n<!DOCTYPE html>\r\n<html>\r\n    <head>\r\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n        <title>Preferences @ My Own Radio Manager</title>\r\n        <!-- module:rm.common -->\r\n        <!-- include:css -->\r\n        <!-- include:js -->\r\n    </head>\r\n    <body class=\"partial library\">\r\n        <div id=\"jplayer\"></div>\r\n        <!-- module:rm.header -->\r\n        <div class=\"rm_body_wrap rm_max_width\">\r\n            <!-- module:rm.sidebar -->\r\n            <div class=\"rm_content_wrap\">\r\n                <div class=\"rm-misc-wrapper\">\r\n                    <form id=\"modifyPreferences\" method=\"POST\">\r\n                        <input type=\"hidden\" name=\"authtoken\" value=\"<?= user::getCurrentUserToken() ?>\" />\r\n                        <div class=\"rm-misc-wrapper-header\">\r\n                            <h1><i class=\"icon-tools\"></i>Preferences</h1>\r\n                        </div>\r\n                    </form>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </body>\r\n</html>','.rm-misc-wrapper {\n    box-sizing: border-box;\n    padding: 16px;\n    font-family: \"Open Sans\";\n    font-size: 10pt;\n}\n\n\n.rm-misc-wrapper-header {\n    border-bottom: 1px solid #ddd;\n    margin-bottom: 10px;\n}\n\n\n','','','',1,'2014-09-09 12:18:07','radiomanager/preferences'),('page.rm.profile','<?php\r\n    $userProfile = user::getUserByUid(user::getCurrentUserId());\r\n    $userAvatar = user::getUserAvatar(user::getCurrentUserId());\r\n    if(file_exists(folders::getUserPicturePath(user::getCurrentUserId())))\r\n    {\r\n        $profileAvatar = folders::getUserPicturePath(user::getCurrentUserId(), false) . \"?s=150&c=1\";\r\n    }\r\n    else\r\n    {\r\n        $profileAvatar = \"\";\r\n    }\r\n?>\r\n<!DOCTYPE html>\r\n<html>\r\n    <head>\r\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n        <title>Profile Settings - My Own Radio Manager</title>\r\n        <!-- module:rm.common -->\r\n        <!-- include:css -->\r\n        <!-- include:js -->\r\n    </head>\r\n    <body class=\"partial library\">\r\n        <div id=\"jplayer\"></div>\r\n        <!-- module:rm.header -->\r\n        <div class=\"rm_body_wrap rm_max_width\">\r\n            <!-- module:rm.sidebar -->\r\n            <div class=\"rm_content_wrap\">\r\n                <div class=\"rm-profile-wrapper\">\r\n                    <input type=\"file\" style=\"display: none;\" accept=\"image/*\" />\r\n                    <form id=\"modifyProfile\" method=\"POST\">\r\n                        <input type=\"hidden\" name=\"authtoken\" value=\"<?= user::getCurrentUserToken() ?>\" />\r\n                        <div class=\"rm-profile-wrapper-header\">\r\n                            <tools>\r\n                                <a href=\'#\' id=\'changePwd\'>Change password</a>\r\n                            </tools>\r\n                            <h1><i class=\"icon-profile\"></i>User Profile</h1>\r\n                        </div>\r\n                        <div class=\"rm-profile-wrapper-base\">\r\n                            <div class=\"rm-profile-wrapper-cover\">\r\n                                <div class=\"rm-profile-wrapper-cover-image\">\r\n                                    <img src=\"<?= $profileAvatar ?>\" />\r\n                                    <span id=\"changeImage\" class=\"rmButton\">Change image</span>\r\n                                </div>\r\n                            </div>\r\n                            <div class=\"rm-profile-wrapper-common\">\r\n                                <div class=\"rm-profile-wrapper-common-outsize\">\r\n                                    <div class=\"rm-profile-wrapper-common-field\">\r\n                                        <label for=\"username\">Username:</label>\r\n                                        <input id=\"username\" autocomplete=\"off\" class=\"rmBigTextInput rm-profile-wrapper-common-input\" type=\"text\" name=\"name\" placeholder=\"Your name\" value=\"<?= htmlspecialchars($userProfile[\'name\']) ?>\" />\r\n                                    </div>\r\n                                    <div class=\"rm-profile-wrapper-common-field\">\r\n                                        <label for=\"permalink\">Permanent link:</label>\r\n                                        <input \\\r\n                                            style=\"padding-left: 154px;\" \\\r\n                                            id=\"permalink\" \\\r\n                                            autocomplete=\"off\" \\\r\n                                            class=\"rmBigTextInput rm-profile-wrapper-common-input\" \\\r\n                                            type=\"text\" \\\r\n                                            name=\"permalink\" \\\r\n                                            placeholder=\"id<?= user::getCurrentUserId() ?>\" \\\r\n                                            value=\"<?= htmlspecialchars($userProfile[\'permalink\']) ?>\" \\\r\n                                        />\r\n                                        <div class=\"rm-profile-wrapper-permalink-validate\"></div>\r\n                                        <label for=\"permalink\">\r\n                                            <div class=\"rm-profile-wrapper-permalink-prefix\">http://myownradio.biz/</div>\r\n                                        </label>\r\n                                    </div>\r\n                                </div>\r\n                            </div>\r\n                        </div>\r\n                        <div class=\"rm-profile-wrapper-additional\">\r\n                            <div class=\"rm-profile-wrapper-common-field\">\r\n                                <label for=\"info\">Additional info:</label>\r\n                                <textarea id=\"info\" autocomplete=\"off\" class=\"rmBigTextInput rm-profile-wrapper-common-input\" name=\"info\" placeholder=\"\"><?= htmlspecialchars($userProfile[\'info\']) ?></textarea>\r\n                            </div>\r\n                        </div>\r\n                        <div class=\"rm-profile-wrapper-buttons\">\r\n                            <input id=\"#submit\" type=\"submit\" class=\"rm-profile-wrapper-common-submit rmButton\" value=\"Save\" />\r\n                        </div>\r\n                    </form>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </body>\r\n</html>','.rm-profile-wrapper {\n    box-sizing: border-box;\n    padding: 16px;\n    font-family: \"Open Sans\";\n    font-size: 10pt;\n}\n\n\n.rm-profile-wrapper-header {\n    border-bottom: 1px solid #ddd;\n    margin-bottom: 10px;\n}\n\n.rm-profile-wrapper label {\n    font-size: 10pt;\n    display: block;\n    margin-bottom: 8px;\n}\n\n.rm-profile-wrapper-base {\n    overflow: hidden;\n}\n\n.rm-profile-wrapper-cover {\n    float: left;\n    margin-right: 15px;\n}\n\n.rm-profile-wrapper-cover-image {\n    width: 150px;\n    height: 150px;\n    text-align: center;\n    position: relative;\n}\n\n.rm-profile-wrapper-cover-image > img {\n    display: block;\n    width: 100%;\n    height: 100%;\n    opacity: 1;\n}\n\n.rm-profile-wrapper-cover-image > span {\n    position: absolute;\n    font-size: 8pt;\n    z-index: 10;\n    bottom: 12px;\n    left: 12px;\n    right: 12px;\n    opacity: 0;\n    transition: opacity linear 150ms;\n    cursor: pointer;\n}\n\n.rm-profile-wrapper-cover-image:hover > span {\n    opacity: 1;\n}\n\n.rm-profile-wrapper-common {\n    position: relative;\n    overflow: hidden;\n}\n\n.rm-profile-wrapper-common-outsize {\n    \n}\n\n.rm-profile-wrapper-common-field {\n    position: relative;\n}\n\n.rm-profile-wrapper-common-input {\n    box-sizing: border-box;\n    position: relative;\n    width: 100%;\n}\n\n.rm-profile-wrapper-permalink-prefix {\n    position: absolute;\n    z-index: 10;\n    bottom: 26px;\n    margin-left: 8px;\n    color: #888;\n}\n\n.rm-profile-wrapper-permalink-validate {\n    position:absolute;\n    right: 8px;\n    bottom: 26px;\n    z-index: 10;\n    color: #f00;\n    display: none;\n}\n\n.rm-profile-input-permalink-wrap {\n    \n}\n\n.rm-profile-input-permalink-prefix {\n    float: left;\n}\n\n.rm-profile-input-permalink-content {\n    width: 100%;\n    display: block;\n    overflow: hidden;\n}\n\n.rm-profile-wrapper-additional {\n    margin-top: 8px;\n}\n\n.rm-profile-wrapper-common-input#info {\n    height: 200px;\n    resize: none;\n}\n\n.rm-profile-wrapper-buttons {\n    text-align: right;\n}\n\n\n.rm-profile-wrapper-header tools {\n    float: right;\n    font-size: 9pt;\n}\n\n.rm-profile-wrapper-header tools > a {\n    color: #39c;\n}','(function(){\n    \n    if($(\"#modifyProfile\").children().find(\"input\").filter(\".error\").length > 0)\n    {\n        alert(\"Not validated\");\n    }\n    \n    $(\"#modifyProfile\").live(\'submit\', function(event){\n        $.post(\"\", $(this).serialize(), function(json){\n            try\n            {\n                if(json.code === \"UPDATED\")\n                {\n                    console.log(\"Information updated!\");\n                    showPopup(\"Profile updated\")\n                }\n                else if(json.code === \"PERMALINK_USED\")\n                {\n                    $(\"#permalink\").addClass(\"error\");\n                    $(\".rm-profile-wrapper-permalink-validate\").text(\"This permalink already in use. Please try another.\").show();\n                }\n                else if(json.code === \"PERMALINK_LENGTH_UNACCEPTABLE\")\n                {\n                    $(\"#permalink\").addClass(\"error\");\n                    $(\".rm-profile-wrapper-permalink-validate\").text(\"Permalink must be between 3 to 255 characters.\").show();\n                }\n                else\n                {\n                    showPopup(\"No changes to profile were made\");\n                }\n            }\n            catch(e){}\n        });\n        return false;\n    });\n    \n    var tmr = false;\n    \n    $(\"#permalink\").livequery(\'textchange\', function(event){\n        if(tmr)\n        {\n            window.clearTimeout(tmr);\n        }\n        \n        tmr = window.setTimeout(function(){\n            tmr = false;\n            $.post(\"\", {\n                permalink: $(\"#permalink\").val(),\n                action: \"checkPermalink\"\n            }, function(json){\n                try\n                {\n                    if(json.code === \"PERMALINK_FREE\")\n                    {\n                        $(\"#permalink\").removeClass(\"error\");\n                        $(\".rm-profile-wrapper-permalink-validate\").hide().text(\"\");\n                    }\n                    else if(json.code === \"PERMALINK_USED\")\n                    {\n                        $(\"#permalink\").addClass(\"error\");\n                        $(\".rm-profile-wrapper-permalink-validate\").text(\"This permalink is unavailable. Please try another.\").show();\n                    }\n                    else if(json.code === \"PERMALINK_LENGTH_UNACCEPTABLE\")\n                    {\n                        $(\"#permalink\").addClass(\"error\");\n                        $(\".rm-profile-wrapper-permalink-validate\").text(\"Permalink must be between 3 to 255 characters\").show();\n                    }\n                    else if(json.code === \"PERMALINK_WRONG_CHARS\")\n                    {\n                        $(\"#permalink\").addClass(\"error\");\n                        $(\".rm-profile-wrapper-permalink-validate\").text(\"Permalink must contain only a-z, 0-9 and \\\"-\\\" characters\").show();\n                    }\n                }\n                catch(e){}\n            });\n        }, 250);\n\n    });\n    \n    // Upload cover\n    $(\".rmButton#changeImage\").live(\"click\", function(event){\n        $(\"input[type=\'file\']\").click();\n    });\n    \n    $(\"input[type=\'file\']\").livequery(function(){\n        $(this).on(\'change\', function(event)\n        {\n \n            var data = new FormData();\n            \n            data.append(\'file\', event.target.files[0]);\n            data.append(\'authtoken\', mor.user_token);\n            data.append(\'action\', \'avatar\');\n    \n            uploadHandle = $.ajax({\n                type: \"POST\",\n                url: \"\",\n                data: data,\n                processData: false,\n                contentType: false,\n                cache: false,\n                success: function(data) {\n                    try\n                    {\n                        var json = JSON.parse(data);\n                        if(json.code === \"SUCCESS\")\n                        {\n                            $(\".rm-profile-wrapper-cover-image > img\").appendTo($(\".rm-profile-wrapper-cover-image\"));\n                            var newImage = $(\".rm-profile-wrapper-cover-image > img\").attr(\"src\").replace(/(&rnd\\=([\\d.]+))/g, \'\') + \"&rnd=\" + Math.random();\n                            $(\".rm-profile-wrapper-cover-image > img\").attr(\"src\", newImage);\n                            showPopup(\"Image updated successfully\");\n                        }\n                    }\n                    catch(e) { }\n                },\n                error: function() {\n                    console.error(\"Upload error!\");\n                }\n            });\n            \n        });\n    });\n\n    // Tools\n    $(\"#changePwd\").live(\"click\", function() {\n        callModuleFunction(\"dialogs.changePassword\");\n        return false;\n    });\n    \n})();','','<?php\n$new_name       = application::post(\"name\", \"\", REQ_STRING);\n$new_permalink  = application::post(\"permalink\", \"\", REQ_STRING); \n$new_info       = application::post(\"info\", \"\", REQ_STRING);\n$new_action     = application::post(\"action\", null, REQ_STRING);\n\nif($new_action === \"checkPermalink\")\n{\n    if(strlen($new_permalink) === 0)\n    {\n        echo misc::outputJSON(\"PERMALINK_FREE\");\n        exit();\n    }\n    \n    if(strlen($new_permalink) > 255 || strlen($new_permalink) < 3)\n    {\n        echo misc::outputJSON(\"PERMALINK_LENGTH_UNACCEPTABLE\");\n        exit();\n    }\n\n    if(!preg_match(\"/^[a-z0-9\\-]+$/\", $new_permalink))\n    {\n        echo misc::outputJSON(\"PERMALINK_WRONG_CHARS\");\n        exit();\n    }\n    \n    $used = misc::pageExists(application::getSite() . \"/\" . $new_permalink);\n    if($used)\n    {\n        echo misc::outputJSON(\"PERMALINK_USED\");\n    }\n    else\n    {\n        echo misc::outputJSON(\"PERMALINK_FREE\");\n    }\n    exit();\n}\nelseif($new_action === \"avatar\")\n{\n        if( !isset($_FILES[\'file\']) || $_FILES[\'file\'][\'error\'] != 0)\n        {\n            return misc::outputJSON(\"NO_FILE\") ;\n        }\n        $img = new acResizeImage($_FILES[\'file\'][\'tmp_name\'], $_FILES[\'file\'][\'name\']);\n        \n        $pathinfo = pathinfo(user::getUserAvatar(user::getCurrentUserId()));\n        \n        $file = $img\n            ->cropSquare()\n            ->resize(500)\n            ->interlace()\n            ->save($pathinfo[\'dirname\'] . \"/\", $pathinfo[\'filename\'], \'png\', true, 100);\n\n        if(file_exists($file))\n        {\n            db::query_update(\"UPDATE `r_users` SET `hasavatar` = 1 WHERE `uid` = ?\", array(user::getCurrentUserId()));\n            echo misc::outputJSON(\"SUCCESS\", array($pathinfo[\'dirname\'], $pathinfo[\'basename\']));\n        }\n        else\n        {\n            echo misc::outputJSON(\"ERROR\");\n        }\n        exit();\n}\nelse\n{\n    if((strlen($new_permalink) > 255 || strlen($new_permalink) < 3) && strlen($new_permalink) > 0)\n    {\n        echo misc::outputJSON(\"PERMALINK_LENGTH_UNACCEPTABLE\");\n        exit();\n    }\n\n    $used = (int) db::query_single_col(\"SELECT COUNT(*) FROM `r_users` WHERE `permalink` = ? AND `uid` != ? AND LENGTH(`permalink`) > 0\", array($new_permalink, user::getCurrentUserId()));\n    if($used)\n    {\n        echo misc::outputJSON(\"PERMALINK_USED\");\n        exit();\n    }\n    \n    $res = db::query_update(\"UPDATE `r_users` SET `name` = ?, `permalink` = ?, `info` = ? WHERE `uid` = ?\", array(\n        $new_name, \n        $new_permalink, \n        $new_info, \n        user::getCurrentUserId()\n    ));\n    if($res > 0)\n    {\n        echo misc::outputJSON(\"UPDATED\");\n    }\n    else\n    {\n        echo misc::outputJSON(\"NOT_UPDATED\");\n    }\n}\n\n?>',1,'2014-09-25 19:38:35','radiomanager/profile'),('page.rm.stream','<?php\r\nif (!isset($_MODULE[\'stream_id\'])) \r\n{ \r\n    header(\"Location: /radiomanager\"); \r\n    return; \r\n}\r\n\r\ntry\r\n{\r\n    $stream = application::singular(\"stream\", $_MODULE[\'stream_id\']);\r\n}\r\ncatch(Exception $e)\r\n{\r\n    header(\"Location: /radiomanager\"); \r\n    return;\r\n}\r\n\r\nif ($stream->getOwner() != user::getCurrentUserId()) \r\n{ \r\n    header(\"Location: /radiomanager\"); \r\n    return; \r\n}\r\n?>\r\n<!DOCTYPE html>\r\n<html>\r\n    <head>\r\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n        <title><?= htmlspecialchars($stream->getStreamName()); ?> @ My Own Radio Manager</title>\r\n        <!-- module:rm.common -->\r\n        <!-- include:css -->\r\n        <!-- include:js -->\r\n        <script type=\"text/javascript\">var active_stream = <?= json_encode(array(\r\n            \'stream_id\'             => (int) $stream->getStreamId(),\r\n            \'tracks_count\'          => (int) $stream->getTracksCount(),\r\n            \'tracks_duration\'       => (int) $stream->getDuration(),\r\n            \'stream_link\'           => $stream->getStreamLink()\r\n        )); ?></script>\r\n    </head>\r\n    <body class=\"partial stream\">\r\n        <div id=\"jplayer\"></div>\r\n        <!-- module:rm.header -->\r\n        <div class=\"rm_body_wrap rm_max_width\">\r\n            <!-- module:rm.sidebar -->\r\n            <div class=\"rm_content_wrap\">\r\n                <!-- module:rm.stream.status -->\r\n                <div class=\"rm_tracks_data\" content=\"<?= base64_encode(json_encode($stream->dbGetStreamTracks(0, config::getSetting(\"json\", \"tracks_per_query\"))))?>\"></div>\r\n                <!-- module:rm.tracklist -->\r\n            </div>\r\n        </div>\r\n    </body>\r\n</html>','','','','<?php\r\n\r\n$stream_id  = application::get(\"stream_id\", NULL, REQ_INT);\r\n$from       = application::post(\"from\", 0, REQ_INT);\r\n$filter     = application::post(\"filter\", 0, REQ_STRING);\r\n\r\n$stream     = application::singular(\'stream\', $stream_id);\r\n\r\nif(!$stream->exists())\r\n{\r\n    misc::errorJSON(\"STREAM_NOT_EXISTS\");\r\n}\r\n\r\nif($stream->getOwner() != user::getCurrentUserId())\r\n{\r\n    misc::errorJSON(\"NO_ACCESS\");\r\n}\r\n\r\n$tracks = $stream->dbGetStreamTracks($from, config::getSetting(\"json\", \"tracks_per_query\"));\r\n\r\necho json_encode($tracks);\r\n\r\n',1,'2014-09-22 11:22:39','radiomanager/stream'),('page.rm.unused','<!DOCTYPE html>\r\n<html>\r\n    <head>\r\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n        <title>Unused Tracks @ My Own Radio Manager</title>\r\n        <!-- module:rm.common -->\r\n        <!-- include:css -->\r\n        <!-- include:js -->\r\n    </head>\r\n    <body class=\"partial unused\">\r\n        <div id=\"jplayer\"></div>\r\n        <!-- module:rm.header -->\r\n        <div class=\"rm_body_wrap rm_max_width\">\r\n            <!-- module:rm.sidebar -->\r\n            <div class=\"rm_content_wrap\">\r\n                <!-- module:rm.home.status -->\r\n                <!-- module:rm.home.filter -->\r\n                <div class=\"rm_tracks_data\" content=\"<?= base64_encode(json_encode(track::getUnusedTracks(user::getCurrentUserId(), 0, config::getSetting(\"json\", \"tracks_per_query\")))) ?>\"></div>\r\n                <!-- module:rm.tracklist -->\r\n            </div>\r\n        </div>\r\n    </body>\r\n</html>','','','','<?php\r\n\r\n$from   = application::post(\"from\", 0, REQ_INT);\r\n$filter = application::post(\"filter\", \"\", REQ_STRING);\r\n\r\nif(strlen($filter)>0)\r\n{\r\n    $tracks = track::getFilteredUnusedTracks(user::getCurrentUserId(), misc::searchQueryFilter($filter), $from, config::getSetting(\"json\", \"tracks_per_query\"));\r\n} \r\nelse\r\n{\r\n    $tracks = track::getUnusedTracks(user::getCurrentUserId(), $from, config::getSetting(\"json\", \"tracks_per_query\"));\r\n}\r\n\r\necho json_encode($tracks);\r\n\r\n\r\n',1,'2014-09-09 12:18:34','radiomanager/unused'),('page.us.browse','<!DOCTYPE html>\n<html>\n    <head>\n        <title>myownradio.biz - create your own web radio station</title>\n        <!-- module:us.common -->\n        <!-- module:us.common.streams -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body class=\"partial\">\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-body\">\n                <!-- module:us.allstreams -->\n            </div>\n        </div>\n        <!-- module:us.footer -->\n    </body>\n</html>','','','','',1,'2014-09-09 09:49:00','browse'),('page.us.categories','<!DOCTYPE html>\n<html>\n    <head>\n        <title>categories @ myownradio.biz</title>\n        <!-- module:us.common -->\n        <!-- module:us.common.streams -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body>\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-body\">\n                <div class=\"us-categories-wrap -limit-width\">\n                    <h1 class=\"us-section-label\">STREAM CATEGORIES</h1>\n                    <ul class=\"us-categories-list\">\n                    <?php foreach(category::getAllCategories() as $category): ?>\n                        <li><a href=\"/category/<?= $category[\'permalink\']; ?>\"><?= htmlspecialchars($category[\'name\']); ?></a></li>\n                    <?php endforeach; ?>\n                    </ul>\n                </div>\n            </div>\n        </div>\n        <!-- module:us.footer -->\n    </body>\n</html>','.us-categories-list {\n    overflow: hidden;\n}\n\n.us-categories-list > li {\n    float: left;\n    width: 240px;\n    height: 75px;\n    margin-right: 10px;\n    margin-bottom: 10px;\n    text-align: center;\n    border: 1px solid #000000;\n    box-sizing: border-box;\n    line-height: 75px;\n}\n\n.us-categories-list > li a {\n    color: #ffffff;\n}\n','','','',1,'2014-09-09 11:29:54','categories'),('page.us.category_view','<?php\n    $c_handle = application::singular(\"category\", application::get(\"permalink\", \"uncategorized\", REQ_STRING));\n    if($c_handle->exists() === false)\n    {\n        echo application::getModule(\"page.us.static.nocategory\");\n        exit();\n    }\n?><!DOCTYPE html>\n<html>\n    <head>\n        <title><?= htmlspecialchars($c_handle->getName()) ?> @ myownradio.biz - create your own web radio station</title>\n        <!-- module:us.common -->\n        <!-- module:us.common.streams -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body class=\"partial\">\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-body\">\n                <!-- module:us.catstreams -->\n            </div>\n        </div>\n        <!-- module:us.footer -->\n    </body>\n</html>','','','','',1,'2014-09-09 11:43:22','category_view'),('page.us.home','<!DOCTYPE html>\n<html>\n    <head>\n        <title>myownradio.biz - create your own web radio station</title>\n        <!-- module:us.common -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body>\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-body\">\n                <div class=\"message\"><h1>WELCOME TO MYOWNRADIO</h1><br>\nThe <b>myownradio.biz</b> brings you opportunity to make your own internet radio stream.\nJust create an account, upload some your favourite sound recordings, send it to your newly created stream and it will be streamed\nas fast as you turn your stream on!<br><br>\nProject is in pre-alfa, unstable and actively developing state, \nso if you wanna try it anyway or interested to be a beta-tester, please <nobr><a href=\"/signup\">sign up</a></nobr> and welcome!\n<br><br>\nFor any comments, bugs or feature requests please email to <nobr><a href=\"mailto:info@myownradio.biz\">info@myownradio.biz</a></nobr>.\n                </div>\n            </div>\n        </div>\n        <!-- module:us.footer -->\n    </body>\n</html>','div.message {\n    background-color: #ffffff;\n    padding: 16px;\n    border-radius: 10px;\n    width: 600px;\n    left: 50%;\n    margin-left:-300px;\n    position: relative;\n    color: #666;\n    line-height: 150%;\n    margin-top: 32px;\n    -font-size: 12pt;\n}\n\ndiv.message h1 {\n    font-family: \"Myriad Pro\";\n    font-size: 22pt;\n\n}','','','',1,'2014-09-13 21:33:54','index'),('page.us.listen','<?php\r\n$stream = application::singular(\'stream\', $_MODULE[\'stream_id\']);\r\n?><!DOCTYPE html>\r\n<html>\r\n    <head>\r\n        <title><?= application::getDocTitle($stream->getStreamName()); ?></title>\r\n        <!-- module:us.common -->\r\n        <script src=\"/js/jquery.jplayer.min.js\"></script>\r\n        <script src=\"/js/radioplayer.js\"></script>\r\n        <script>var myRadio = <?= json_encode($stream->getStreamStatus()); ?></script>\r\n    </head>\r\n    <body>\r\n        <div id=\"jplayer\"></div>\r\n        <div class=\"rh_wrap\">\r\n            <!-- module:us.header -->\r\n            <!-- module:us.player -->\r\n            <!-- module:us.footer -->\r\n        </div>\r\n    </body>\r\n</html>','','','','',1,'2014-09-01 09:36:01',''),('page.us.login','<!DOCTYPE html>\n<html>\n    <head>\n        <title><?= application::getDocTitle(\"Sign In\"); ?></title>\n        <!-- module:us.common -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body>\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-form-frame\">\n                <h1>SIGN IN</h1>\n                <form action=\"/api/v2/auth/login\" method=\"post\" class=\"page-user-form login\">\n                    <table class=\"page-form-table\">\n                        <tr>\n                            <td>Login:</td>\n                            <td><input type=\"text\"  data-type=\"login\" value=\"\" name=\"login\" required /></td>\n                            <td><div class=\"page-form-input-status login\">This field must be filled</div></td>\n                        </tr>\n                        <tr>\n                            <td>Password:</td>\n                            <td><input type=\"password\"  data-type=\"password\" value=\"\" name=\"password\" required /></td>\n                            <td><div class=\"page-form-input-status password\">This field must be filled</div></td>\n                        </tr>\n                    </table>\n                    <div class=\"page-form-additional\"><input type=\"checkbox\" id=\"remember\" name=\"remember\" /><label for=\"remember\"> Remember me on this computer</label></div>\n                    <div class=\"page-form-description\">\n                        Don\'t have an account yet? <a href=\"/signup\">Create it!</a><br>\n                        Forgot password? <a href=\"/recover\">Recover it!</a>\n                    </div>\n                    <div class=\"page-form-buttons\">\n                        <input type=\"submit\" value=\"Login\" />\n                    </div>\n                </form>\n            </div>\n        </div>\n        <!-- module:us.footer -->\n        </body>\n</html>','','$(\".page-user-form.login\").jsubmit(\"/\");','','',1,'2014-10-09 13:51:50',''),('page.us.password','<!DOCTYPE html>\n<html>\n    <head>\n        <title><?= application::getDocTitle(\"Password Recover\"); ?></title>\n        <!-- module:us.common -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body>\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-form-frame\">\n                <h1>RESET PASSWORD</h1>\n                <form action=\"\" method=\"post\" class=\"page-user-form passwordReset\">\n                    <table class=\"page-form-table\">\n                        <tr>\n                            <td>New password:</td>\n                            <td><input type=\"password\"  data-type=\"password\" value=\"\" name=\"password1\" required autocomplete=\"off\" /></td>\n                            <td><div class=\"page-form-input-status password1\">This field must be filled</div></td>\n                        </tr>\n                        <tr>\n                            <td>Repeat:</td>\n                            <td><input type=\"password\" data-type=\"password-confirm\" value=\"\" name=\"password2\" required autocomplete=\"off\" /></td>\n                            <td><div class=\"page-form-input-status password2\">This field must be filled</div></td>\n                        </tr>\n                    </table>\n                    <div class=\"page-form-buttons\">\n                        <input type=\"submit\" value=\"Reset\" />\n                    </div>\n                </form>\n            </div>\n        </div>\n        <!-- module:us.footer -->\n        </body>\n</html>','','$(\".page-user-form.passwordReset\").jsubmit(\"/login?msg=PASSWORD_CHANGED\");','','',1,'2014-10-10 13:04:28',''),('page.us.player','<?php\n$stream = application::singular(\'stream\', $_MODULE[\'stream_id\']);\n$stream_owner = user::getUserByUid($stream->getOwner());\n?><!DOCTYPE html>\n<html>\n    <head>\n        <title><?= application::getDocTitle($stream->getStreamName()); ?></title>\n        <!-- include:css -->\n        <!-- module:us.common -->\n        <script src=\"/js/jquery.jplayer.min.js.gz\"></script>\n        <script>var stream = { stream_id : <?= $stream->getStreamId(); ?> };</script>\n        <script>var activeBitrate = <?= application::get(\"br\", 2, REQ_INT); ?></script>\n        <script>var streamPreload = parseInt(<?= config::getSetting(\"server\", \"stream_buffer\"); ?>) * 1000</script>\n        <!-- include:js -->\n    </head>\n    <body token=\"<?= (user::getCurrentUserId() > 0) ? user::getCurrentUserToken() : \"\" ?>\">\n        <?php if(user::getCurrentUserId() == 1): ?>\n            <div id=\"logger\">info</div>\n        <?php endif; ?>\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-body\">\n                <!-- module:us.player -->\n            </div>\n        </div>\n        <!-- module:us.footer -->\n        </body>\n</html>','','','','',1,'2014-09-26 20:59:36',''),('page.us.recover','<!DOCTYPE html>\n<html>\n    <head>\n        <title><?= application::getDocTitle(\"Password Reset\"); ?></title>\n        <!-- module:us.common -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body>\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-form-frame\">\n                <h1>PASSWORD RESET</h1>\n                <div class=\"page-form-description\">To recover access to your account please enter your login or email into field below and press <span>Reset</span> button. Link for password reset will be sent to your email address.</div>\n                <form action=\"/api/v2/auth/passwordRecoverRequest\" method=\"post\" class=\"page-user-form recover\">\n                    <table class=\"page-form-table\">\n                        <tr>\n                            <td>Login or Email:</td>\n                            <td><input type=\"text\"  data-type=\"login\" value=\"\" name=\"email\" required /></td>\n                            <td><div class=\"page-form-input-status login\">This field must be filled</div></td>\n                        </tr>\n                    </table>\n                    <div class=\"page-form-buttons\">\n                        <input type=\"submit\" value=\"Reset\" />\n                    </div>\n                </form>\n            </div>\n        </div>\n        <!-- module:us.footer -->\n        </body>\n</html>','','$(\".page-user-form.recover\").jsubmit(\"/\");','','',1,'2014-10-09 17:45:04','recover'),('page.us.search','<?php\nif(empty(application::get(\"q\", \"\", REQ_STRING)))\n{\n    header(\"Location: /browse\");\n    exit();\n}\n?><!DOCTYPE html>\n<html>\n    <head>\n        <title>myownradio.biz - create your own web radio station</title>\n        <!-- module:us.common -->\n        <!-- module:us.common.streams -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body class=\"partial\">\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-body\">\n                <!-- module:us.searchstreams -->\n            </div>\n        </div>\n        <!-- module:us.footer -->\n    </body>\n</html>','','','','',1,'2014-09-12 18:31:19','search'),('page.us.signup1','<!DOCTYPE html>\n<html>\n    <head>\n        <title><?= application::getDocTitle(\"Sign Up\"); ?></title>\n        <!-- module:us.common -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body>\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-form-frame\">\n                <h1>SIGN UP</h1>\n                <div class=\"page-form-description\">To create account type your email in field below and press <span>Send</span> button. Instructions about continuing registration will be sent to it.</div>\n                <form action=\"/api/v2/auth/requestRegistration\" method=\"post\" class=\"page-user-form signup\">\n                    <table class=\"page-form-table\">\n                        <tr>\n                            <td>Email:</td>\n                            <td><input type=\"text\" data-type=\"email\" value=\"\" name=\"email\" required /></td>\n                            <td><div class=\"page-form-input-status email\">This field must be filled</div></td>\n                        </tr>\n                    </table>\n                    <div class=\"page-form-description\">Already registered? <a href=\"/login\">Sign in</a></div>\n                    <div class=\"page-form-buttons\">\n                        <input type=\"submit\" value=\"Send\" />\n                    </div>\n                </form>\n            </div>\n        </div>\n        <!-- module:us.footer -->\n    </body>\n</html>','','$(\".page-user-form.signup\").jsubmit(\"/static/message-sent\");','','<?php echo user::requestRegistration();',1,'2014-09-25 12:38:51','signup'),('page.us.signup2','<!DOCTYPE html>\n<html>\n    <head>\n        <title><?= application::getDocTitle(\"Complete Registration\"); ?></title>\n        <!-- module:us.common -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body>\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-form-frame\">\n                <h1>REGISTRATION</h1>\n                <div class=\"page-form-description\">Please fill the fields below to complete the registration.</div>\n                <form method=\"post\" class=\"page-user-form complete\">\n                    <table class=\"page-form-table\">\n                        <tr>\n                            <td>Login:</td>\n                            <td><input type=\"text\"  data-type=\"login\" value=\"\" name=\"login\" required autocomplete=\"off\" /></td>\n                            <td><div class=\"page-form-input-status login\">This field must be filled</div></td>\n                        </tr>\n                        <tr>\n                            <td>Your name:</td>\n                            <td><input type=\"text\" data-type=\"text\" value=\"\" name=\"name\" autocomplete=\"off\" /></td>\n                            <td><div class=\"page-form-input-status name\">This field must be filled</div></td>\n                        </tr>\n                        <tr>\n                            <td>Password:</td>\n                            <td><input type=\"password\" data-type=\"password\" value=\"\" name=\"password1\" required autocomplete=\"off\" /></td>\n                            <td><div class=\"page-form-input-status password\">This field must be filled</div></td>\n                        </tr>\n                        <tr>\n                            <td>Password again:</td>\n                            <td><input type=\"password\" data-type=\"password-confirm\" value=\"\" name=\"password2\" required autocomplete=\"off\" /></td>\n                            <td><div class=\"page-form-input-status password\">This field must be filled</div></td>\n                        </tr>\n                    </table>\n                    <div class=\"page-form-buttons\">\n                        <input type=\"submit\" value=\"Sign Up\" />\n                    </div>\n                </form>\n            </div>\n        </div>\n        <!-- module:us.footer -->\n    </body>\n</html>','','$(\".page-user-form.complete\").jsubmit(\"/static/registration-completed\");','','',1,'2014-10-09 16:46:04',''),('page.us.static.403','<!DOCTYPE html>\n<html>\n    <head>\n        <title>Forbidden</title>\n        <style>\n        body {\n            font-family: Monospace;\n            padding: 16px;\n            margin: 0;\n        }\n        \n        h1 {\n            margin: 0 0 8px 0;\n        }\n        </style>\n    </head>\n    <body>\n        <h1>ERROR 403</h1>\n        <div>You do not have access to this document.</div>\n    </body>\n</html>','','','','',1,'2014-09-22 22:00:35',''),('page.us.static.404','<!DOCTYPE html>\n<html>\n    <head>\n        <title>Document not found</title>\n        <style>\n        body {\n            font-family: Monospace;\n            padding: 16px;\n            margin: 0;\n        }\n        \n        h1 {\n            margin: 0 0 8px 0;\n        }\n        </style>\n    </head>\n    <body>\n        <h1>ERROR 404</h1>\n        <div>Requested document not found on this server.</div>\n    </body>\n</html>','','','','',1,'2014-09-22 21:56:37',''),('page.us.static.error','<!DOCTYPE html>\n<html>\n    <head>\n        <title>Error Ocured</title>\n        <style>\n        body {\n            font-family: Monospace;\n            padding: 16px;\n            margin: 0;\n        }\n        \n        h1 {\n            margin: 0 0 8px 0;\n        }\n        </style>\n    </head>\n    <body>\n        <h1>UNKNOWN ERROR</h1>\n        <div>Some error ocured.</div>\n    </body>\n</html>','','','','',1,'2014-09-22 22:16:05',''),('page.us.static.expiredcode','<!DOCTYPE html>\n<html>\n    <head>\n        <title><?= application::getDocTitle(\"Code Expired\"); ?></title>\n        <!-- module:us.common -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body>\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-form-frame\">\n                <h1>PASSWORD RESET</h1>\n                <div class=\"page-form-description\">Sorry, but this link is no longer available. To reset password please use <a href=\"/recover\">password reset</a> page.</div>\n            </div>\n        </div>\n        <!-- module:us.footer -->\n    </body>\n</html>','','','','',1,'2014-09-15 18:39:29',''),('page.us.static.msgsent','<!DOCTYPE html>\n<html>\n    <head>\n        <title><?= application::getDocTitle(\"Sign Up\"); ?></title>\n        <!-- module:us.common -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body>\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-form-frame\">\n                <h1>SIGN UP</h1>\n                <div class=\"page-form-description\">\n                We sent to <span><?= application::get(\"email\", \"your email\", REQ_STRING); ?></span> a letter with further step to continue registration.<br>Please check your email for letter and follow confirmation link.\n                <br><br><a href=\"/\">Back to home page</a>\n                </div>\n            </div>\n        </div>\n        <!-- module:us.footer -->\n    </body>\n</html>','','','','',1,'2014-09-25 16:08:32','static/message-sent'),('page.us.static.nostream','<!DOCTYPE html>\n<html>\n    <head>\n        <title><?= application::getDocTitle(\"Stream is unavailable\"); ?></title>\n        <!-- module:us.common -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body>\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-form-frame\">\n                <h1>NO STREAM</h1>\n                <div class=\"page-form-description\">Sorry, but requested stream not found or stopped by its owner.</div>\n            </div>\n        </div>\n        <!-- module:us.footer -->\n    </body>\n</html>','','','','',1,'2014-09-16 15:59:35',''),('page.us.static.regcomplete','<!DOCTYPE html>\n<html>\n    <head>\n        <title><?= application::getDocTitle(\"Registration Completed\"); ?></title>\n        <!-- module:us.common -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body>\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-form-frame\">\n                <h1>SUCCESS!</h1>\n                <div class=\"page-form-description\">Congratulations!<br><br>You\'re successfully registered on <a href=\"/\">myownradio.biz</a>.<br><br>Now you can <a href=\"/login\">Sign in</a> to start creating and streaming your own web radio station!</div>\n            </div>\n        </div>\n        <!-- module:us.footer -->\n    </body>\n</html>','','','','',1,'2014-09-01 09:35:05','static/registration-completed'),('rm.common','<link rel=\'stylesheet\' type=\"text/css\" href=\"http://fonts.googleapis.com/css?family=Open+Sans:400,600&subset=latin,cyrillic\" />\n<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/reset.css\" />\n<link rel=\"stylesheet\" type=\"text/css\" href=\"/icomoon/style.css\" />\n<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/rm-common.css\" />\n\n<link rel=\"icon shortcut\" href=\"/images/mor-logo-dark.png\" />\n\n<script type=\"text/javascript\">var mor = <?= json_encode(array(\n    \'user_token\'            => user::getCurrentUserToken(),\n    \'last_event\'            => events::getLastID(),\n    \'filter_type_delay\'     => 150,\n    \'categories\'            => category::getAllCategories()\n)); ?>\n</script>\n\n<script type=\"text/javascript\">var user = <?= json_encode(application::getProfileStats()); ?></script>\n\n<script src=\"/js/jquery-1.11.0.min.js.gz\"></script>\n<script src=\"/js/jquery-migrate-1.2.1.min.js.gz\"></script>\n<script src=\"/js/jquery-ui.min.js.gz\"></script>\n<script src=\"/js/jquery.livequery.js.gz\"></script>\n<script src=\"/js/jquery.jplayer.min.js.gz\"></script>\n<script src=\"/js/jquery.tmpl.js\"></script>\n<script src=\"/js/jquery.change.js\"></script>\n<script src=\"/js/module.js\"></script>\n<script src=\"/js/common.js\"></script>\n<script src=\"/js/lang.en.js\"></script>\n\n<?php \n$dir = opendir(\"js\"); \nwhile($file = readdir($dir))\n{\n    if(preg_match(\"/^common\\.\\d+\\.mod\\..+\\.js$/\", $file))\n    {\n        echo \"<script src=\\\"/js/{$file}\\\"></script>\";\n    }\n}\nclosedir($dir); \n?>\n\n<!-- module:rm.functions -->\n<!-- module:rm.menu -->\n<!-- module:rm.upload -->\n<!-- module:rm.windows -->\n<!-- module:rm.create.stream -->\n<!-- module:rm.form.tageditor -->\n<!-- module:rm.service.subscribe -->\n<!-- module:rm.service.popup -->\n<!-- module:rm.module.audio -->\n<!-- module:rm.gui.common -->\n<!-- module:rm.gui.picker -->\n','body,html {\r\n    font-family: \"Open Sans\";\r\n    font-size: 9pt;\r\n    height: 100%;\r\n}\r\n\r\nbody {\r\n    position: relative;\r\n    -webkit-text-size-adjust: 100%;\r\n\r\n}\r\n\r\nb {\r\n    font-weight: bold;\r\n}\r\n\r\n.no-margin {\r\n    margin: 0 !important;\r\n}\r\n\r\nbody,html,ul { margin: 0; }\r\n\r\n.rm_max_width { width: 100%; margin: 0 auto; }\r\n\r\n.rm_menu_wrap {\r\n    height: 64px;\r\n    line-height: 64px;\r\n    list-style-type: none;\r\n    margin: 0 4px;\r\n    padding: 0;\r\n}\r\n\r\n.rm_menu_wrap > li {\r\n    float: left;\r\n    margin: 0 8px;\r\n}\r\n\r\n\r\n.rm_body_wrap {\r\n    display: table;\r\n    position: relative;\r\n    height: 600px;\r\n}\r\n\r\n.rm_content_wrap {\r\n    display: table-cell;\r\n    vertical-align: top;\r\n    position: relative;\r\n}\r\n\r\n.rm_tbl_row {\r\n    display: table-row;\r\n}\r\n\r\n.rm_badge {\r\n    border-radius: 3px;\r\n    color: #ffffff;\r\n    box-shadow: inset 1px 1px 4px #888888;\r\n    text-shadow: 0px 0px 1px rgba(0, 0, 0, 1);\r\n    background-color: #bbbbbb;\r\n    padding: 1px 6px 2px 6px;\r\n    font-size: 8pt;\r\n    font-weight: bold;\r\n    width: 20px;\r\n    text-align: center;\r\n}\r\n\r\n.rm_fl_left { float: left }\r\n.rm_fl_right { float: right }\r\n\r\na {\r\n    color: #000000;\r\n    text-decoration: none;\r\n}\r\n\r\na:hover {\r\n    color: #39c;\r\n}\r\n\r\nh1 {\r\n    font-size: 13pt;\r\n    padding-bottom: 8px;\r\n}\r\n\r\n.rm_sep {\r\n    height: 10px;\r\n}\r\n\r\n.rm_ui_button {\r\n    border: 1px solid #dddddd;\r\n    background: #f8f8f8;\r\n    padding: 2px 6px;\r\n    border-radius: 3px;\r\n    cursor: pointer;\r\n    outline: none;\r\n    vertical-align: middle;\r\n}\r\n\r\n\r\n.rm_ui_button:hover {\r\n    background: #ffffff;\r\n}\r\n\r\n.rm_ui_button:active {\r\n    padding-left: 7px;\r\n    padding-right: 5px;\r\n    padding-top: 3px;\r\n    padding-bottom: 1px;\r\n    background: #f1f1f1;\r\n}\r\n\r\n.rm_ui_def_button {\r\n    border: 1px solid #dddddd;\r\n    background: #f8f8f8;\r\n    border-radius: 3px;\r\n    cursor: pointer;\r\n    outline: none;\r\n    vertical-align: middle;\r\n    width: 100px;\r\n    padding: 4px 0;\r\n    transtition: background-color linear 250ms;\r\n}\r\n\r\n.rmButton {\r\n    padding: 2px 8px;\r\n    border: 1px solid #dddddd;\r\n    background: #f8f8f8;\r\n    border-radius: 3px;\r\n    cursor: pointer;\r\n    outline: none;\r\n    vertical-align: middle;\r\n    transtition: background-color linear 250ms;\r\n    color: #000000;\r\n}\r\n\r\n\r\n.rmBigTextInput {\r\n    font-family: \"Open Sans\";\r\n    font-size: 10pt;\r\n    padding: 8px;\r\n    border-radius: 5px;\r\n    border: 1px solid #dddddd;\r\n    outline: none;\r\n    transition: border-color linear 500ms, background-color linear 500ms;\r\n    background-color: #ffffff;\r\n    margin-bottom: 16px;\r\n}\r\n\r\n.rmBigTextInput[type=\"text\"] {\r\n    height: 34px;\r\n    line-height: 34px;\r\n}\r\n\r\n.rmBigTextInput:not([readonly]):focus {\r\n    border-color: #5594b4;\r\n}\r\n\r\n.rmBigTextInput.error {\r\n    border-color: #f00 !important;\r\n}\r\n\r\n.rmButton {\r\n    border: 1px solid #dddddd;\r\n    background: #f8f8f8;\r\n    border-radius: 5px;\r\n    cursor: pointer;\r\n    outline: none;\r\n    vertical-align: middle;\r\n    padding: 6px 24px;\r\n    transtition: background-color linear 250ms;\r\n}\r\n\r\n.rmButton:hover {\r\n    background: #ffffff;\r\n}\r\n\r\n.rmButton:active {\r\n    background: #f1f1f1;\r\n}\r\n\r\n\r\n.rm_ui_def_button:hover {\r\n    background: #ffffff;\r\n}\r\n\r\n.rm_ui_def_button:active {\r\n    padding-top: 5px;\r\n    padding-bottom: 3px;\r\n    background: #f1f1f1;\r\n}\r\n\r\n.rm_ui_input_text {\r\n    border-radius: 3px;\r\n    outline: none;\r\n    border: 1px solid #dddddd;\r\n    padding: 8px;\r\n    box-sizing: border-box;\r\n    transition: border-color linear 500ms, background-color linear 500ms;\r\n    width: 300px;\r\n}\r\n\r\n.rm_ui_input_text[readonly] {\r\n    color: #888;\r\n}\r\n\r\n.rm_ui_input_text:not([readonly]):focus {\r\n    border-color: #5594b4;\r\n}\r\n\r\n.rm_ui_status {\r\n    color: #f66;\r\n}\r\n\r\n.rm_ui_input_select:focus {\r\n    border-color: #5594b4;\r\n}\r\n\r\n.rm_ui_textarea {\r\n    font: inherit;\r\n    border-radius: 3px;\r\n    outline: none;\r\n    border: 1px solid #dddddd;\r\n    padding: 8px;\r\n    box-sizing: border-box;\r\n    transition: border-color linear 500ms, background-color linear 500ms;\r\n    resize: none;\r\n}\r\n\r\n.rm_ui_textarea:focus {\r\n    border-color: #5594b4;\r\n}\r\n\r\n.rm_ui_input_warn {\r\n    color: #f66;\r\n    position: absolute;\r\n    box-sizing: border-box;\r\n    padding: 8px 0px;\r\n}\r\n\r\n.rm_ui_input_div {\r\n    border-radius: 3px;\r\n    outline: none;\r\n    border: 1px solid #dddddd;\r\n    padding: 6px 8px;\r\n    box-sizing: border-box;\r\n    transition: border-color linear 500ms, background-color linear 500ms;\r\n}\r\n\r\n.rm_ui_input_clear {\r\n    border: none;\r\n    background: #eee;\r\n    outline: none;\r\n    margin: 0;\r\n    padding: 0;\r\n    overflow: visible;\r\n}\r\n\r\n.rm_genre_close {\r\n    width: 8px;\r\n    height: 8px;\r\n    margin-left: 4px;\r\n    cursor: pointer;\r\n}\r\n\r\n.placeholder {\r\n    position: absolute;\r\n    color: #aaa;\r\n}\r\n\r\n\r\n.dynTop,\r\n.-rm-center {\r\n    position: absolute;\r\n    top: 50%;\r\n    left: 50%;\r\n    -webkit-transform: translate(-50%, -50%);\r\n    -moz-transform: translate(-50%, -50%);\r\n    -ms-transform: translate(-50%, -50%);\r\n    -o-transform: translate(-50%, -50%);\r\n    transform: translate(-50%, -50%);\r\n}\r\n\r\n\r\ni[class*=\"icon-\"] {\r\n    margin-right: 6px;\r\n}\r\n\r\n.fl_l {\r\n    float: left;\r\n}\r\n\r\n.fl_r {\r\n    float: right;\r\n}\r\n\r\n\r\n.rm-common-wrapper {\r\n    box-sizing: border-box;\r\n    padding: 16px;\r\n    font-family: \"Open Sans\";\r\n    font-size: 10pt;\r\n}\r\n\r\n\r\n.rm-common-wrapper-header {\r\n    border-bottom: 1px solid #ddd;\r\n    margin-bottom: 10px;\r\n}\r\n\r\n.wpad4px {\r\n    padding-left: 8px !important;\r\n    padding-right: 8px !important;\r\n}','var radiomanager = {\r\n    updateCounters: function() {\r\n        $(\".profile-tracks-count\").text(user.user_stats.user_tracks_count);\r\n        $(\".profile-tracks-time\").text(secondsToHms(user.user_stats.user_tracks_time));\r\n        if (user.plan_data.plan_time_limit === 0) {\r\n            $(\"#total_time_left\").html(\"&infin;\");\r\n            $(\".rm_infobar_progress #handle\").width(\"100%\");\r\n            $(\".rm_infobar_progress #cents\").text(\"100%\");\r\n            $(\".rm_infobar_progress\").removeClass(\"over\");\r\n        } else if (user.plan_data.plan_time_limit < user.user_stats.user_tracks_time) {\r\n            $(\"#total_time_left\").html(secondsToHms(0));\r\n            $(\".rm_infobar_progress #handle\").width(\"100%\");\r\n            $(\".rm_infobar_progress #cents\").text(\">100%\");\r\n            $(\".rm_infobar_progress\").addClass(\"over\");\r\n        } else {\r\n            $(\"#total_time_left\").text(secondsToHms(user.plan_data.plan_time_limit - user.user_stats.user_tracks_time));\r\n            $(\".rm_infobar_progress #handle\").width((100 / user.plan_data.plan_time_limit * user.user_stats.user_tracks_time).toString() + \"%\");\r\n            $(\".rm_infobar_progress #cents\").text(Math.floor(100 / user.plan_data.plan_time_limit * user.user_stats.user_tracks_time).toString() + \"%\");\r\n            $(\".rm_infobar_progress\").removeClass(\"over\");\r\n        } \r\n    },\r\n};\r\n\r\nvar stream = {\r\n    shuffle: function(stream_id) {\r\n        $.post(\"/api/v2/stream/shuffleStream\", {\r\n            id : stream_id,\r\n        }, function(data) {\r\n            if (data.status === 1) {\r\n                callModuleFunction(\"stream.reload\", stream_id);\r\n            }\r\n        });\r\n        return stream;\r\n    },\r\n    sort: function(stream_id, target, index) {\r\n        $.post(\"/api/v2/stream/moveTrack\", {\r\n            id          : stream_id,\r\n            unique_id   : target,\r\n            new_index   : index + 1,\r\n        }, function(data) {\r\n            console.log(data);\r\n        });\r\n        return stream;\r\n    },\r\n    reload: function(stream_id) {\r\n        if(typeof active_stream === \"undefined\") {\r\n            return;\r\n        }\r\n        if(active_stream.stream_id === stream_id) {\r\n            ajaxGetTrackUniversal(true);\r\n        }\r\n    },\r\n    state: function(stream_id) {\r\n        $.post(\"/api/v2/stream/switchState\", {\r\n            id : stream_id,\r\n        }, function(json){\r\n            //var json = filterAJAXResponce(data);\r\n            //showPopup(lang.conv(json.code, \"stream.state\"));\r\n            if (json.status === 1) {\r\n                showPopup(\"Stream state changed\");\r\n            }\r\n        });\r\n    },\r\n    purge: function(stream_id) {\r\n        myOwnQuestion(\"Are you sure want to purge all tracks from selected stream?\", function() {\r\n            $.post(\"/api/v2/stream/purgeStream\", { id : stream_id }, function(json) {\r\n                if (json.status === 1) {\r\n                    showPopup(\"Stream purged successfully\");\r\n                }\r\n            });\r\n        });\r\n    },\r\n    delete: function(stream_id) {\r\n        myOwnQuestion(\"Are you sure want to delete selected stream?\", function() {\r\n            $.post(\"/api/v2/stream/deleteStream\", { id : stream_id }, function(data){\r\n                if (json.status === 1) {\r\n                    showPopup(\"Stream deleted successfully\");\r\n                }\r\n            });\r\n        });\r\n    },\r\n    play: function(stream_id, unique_id) {\r\n        $.post(\"/api/v2/stream/playFrom\", {\r\n            \'id\'        : stream_id,\r\n            \'unique_id\' : unique_id\r\n        }, function(data) {\r\n            if (data.status === 1) {\r\n                showPopup(\"Current playing track changed successfully\");\r\n            }\r\n        });\r\n    }\r\n};\r\n\r\n\r\nfunction updateRadioManagerInterface() {\r\n    callModuleFunction(\"radiomanager.updateCounters\");\r\n}\r\n\r\n$(document).on(\"ready\", function(){\r\n    $(\".rm_body_wrap\").height($(\"body\").height() - 65);\r\n    \r\n    radiomanager.updateCounters();\r\n    \r\n    // autopopup\r\n    switch(window.location.hash) {\r\n        case \'#password\':\r\n            callModuleFunction(\"dialogs.changePassword\");\r\n            break;\r\n    }\r\n});\r\n\r\n$(window).on(\"resize\", function(){\r\n    $(\".rm_body_wrap\").height($(\"body\").height() - 65);\r\n});\r\n\r\nfunction filterAJAXResponce(jsonDATA)\r\n{\r\n    try\r\n    {\r\n        if(jsonDATA.error === undefined)\r\n        {\r\n            return jsonDATA;\r\n        }\r\n        if(jsonDATA.error === \"ERROR_UNAUTHORIZED\")\r\n        {\r\n            redirectLogin();\r\n            return jsonDATA;\r\n        }\r\n    }\r\n    catch(e)\r\n    {\r\n        myMessageBox(\"Wrong server responce: \" . responce);\r\n    }\r\n}\r\n','<script id=\"questionBoxTemplate\" type=\"text/x-jquery-tmpl\">\r\n<div class=\"rm_mbox_shader\">\r\n    <div class=\"rm_mbox_wrap dynTop\">\r\n        <div class=\"rm_mbox_header\">\r\n            Confirmation\r\n            <div class=\"rm_mbox_close_btn\">\r\n                <img src=\"/images/closeButton.gif\" />\r\n            </div>\r\n        </div>\r\n        <div class=\"rm_mbox_text\">${message}</div>\r\n        <div class=\"rm_mbox_footer\">\r\n            <input type=\"button\" class=\"rm_ui_def_button rm_mbox_btn_action\" value=\"Yes\" />\r\n            <input type=\"button\" class=\"rm_ui_def_button rm_mbox_btn_close\" value=\"No\" />\r\n        </div>\r\n    </div>\r\n</div>\r\n</script>\r\n\r\n<script id=\"messageBoxTemplate\" type=\"text/x-jquery-tmpl\">\r\n<div class=\"rm_mbox_shader\">\r\n    <div class=\"rm_mbox_wrap dynTop\">\r\n        <div class=\"rm_mbox_header\">\r\n            Information\r\n            <div class=\"rm_mbox_close_btn\">\r\n                <img src=\"/images/closeButton.gif\" />\r\n            </div>\r\n        </div>\r\n        <div class=\"rm_mbox_text\">${message}</div>\r\n        <div class=\"rm_mbox_footer\">\r\n            <input type=\"button\" class=\"rm_ui_def_button rm_mbox_btn_close\" value=\"Close\" />\r\n        </div>\r\n    </div>\r\n</div>\r\n</script>\r\n\r\n','',1,'2014-10-15 10:00:06',''),('rm.create.stream','','','','','',1,'2014-09-11 20:56:57',''),('rm.form.tageditor','','.rm_tagedit_table tr td {\r\n    padding: 4px 8px;\r\n}\r\n\r\n.rm_tagedit_table tr td:first-child {\r\n    text-align: left;\r\n}\r\n\r\n.rm_tagedit_table tr td .rm_ui_input_text {\r\n    width: 400px;\r\n}','function showTagEditorBox(track_id)\r\n{\r\n    $.post(\"/radiomanager/api/getTrackItem\", { \r\n        track_id : track_id, \r\n        type : \"tags\"\r\n    }, function (json) \r\n    {\r\n                json.tid = track_id;\r\n                \r\n            var item = $(\"#tagEditorTemplate\").tmpl(json);\r\n                item.find(\".rm_mbox_btn_close\").bind(\"click\", function () { item.remove(); });\r\n                item.find(\".rm_mbox_close_btn\").bind(\"click\", function () { item.remove(); });\r\n                item.find(\".rm_mbox_btn_save\").bind(\"click\", function () { \r\n                    $.post(\"/radiomanager/changeTrackInfo\", item.find(\'form\').serialize(), function (json) {\r\n                        try {\r\n                            if(json.code !== \"SUCCESS\") {\r\n                               myMessageBox(json.code);\r\n                            } else {\r\n                                showPopup(\"Track information successfully updated\");\r\n                                json.data.forEach(function(el) {\r\n                                    if(typeof el.result[1] !== undefined) {\r\n                                        callModuleFunction(\"tracklist.trackUpdate\", el.value, el.result[1]);\r\n                                    }\r\n                                });\r\n                            }\r\n                        } catch(e) {\r\n\r\n                        }\r\n                        item.remove();\r\n                    });\r\n                });\r\n            item.appendTo(\"body\");\r\n    });\r\n}','<script id=\"tagEditorTemplate\" type=\"text/x-jquery-tmpl\">\r\n<div class=\"rm_mbox_shader\">\r\n    <div class=\"rm_mbox_wrap dynTop\">\r\n        <div class=\"rm_mbox_header\">\r\n            Track Metadata Editor\r\n            <div class=\"rm_mbox_close_btn\">\r\n                <img src=\"/images/closeButton.gif\" />\r\n            </div>\r\n        </div>\r\n        <div class=\"rm_mbox_text\">\r\n            <form>\r\n                <table class=\"rm_tagedit_table\">\r\n                    <tr><td>Track ID</td><td><input name=\"track_id\" class=\"rm_ui_input_text\" type=\"text\" id=\"tagID\" value=\"${tid}\" readonly /></td></tr>\r\n                    <tr><td>Title</td><td><input name=\"title\" class=\"rm_ui_input_text\" type=\"text\" id=\"tagTitle\" value=\"${title}\" /></td></tr>\r\n                    <tr><td>Artist</td><td><input name=\"artist\" class=\"rm_ui_input_text\" type=\"text\" id=\"tagArtist\" value=\"${artist}\" /></td></tr>\r\n                    <tr><td>Album</td><td><input name=\"album\" class=\"rm_ui_input_text\" type=\"text\" id=\"tagAlbum\" value=\"${album}\" /></td></tr>\r\n                    <tr><td>Date</td><td><input name=\"date\" class=\"rm_ui_input_text\" type=\"text\" id=\"tagDate\" value=\"${date}\" /></td></tr>\r\n                    <tr><td>Genre</td><td><input name=\"genre\" class=\"rm_ui_input_text\" type=\"text\" id=\"tagGenre\" value=\"${genre}\" /></td></tr>\r\n                    <tr><td>Track Number</td><td><input name=\"track_number\" class=\"rm_ui_input_text\" type=\"text\" id=\"tagTrackNr\" value=\"${track_number}\" /></td></tr>\r\n                </table>  \r\n            </form>\r\n        </div>\r\n        <div class=\"rm_mbox_footer\">\r\n            <input type=\"button\" class=\"rm_ui_def_button rm_mbox_btn_save\" value=\"Save\" />\r\n            <input type=\"button\" class=\"rm_ui_def_button rm_mbox_btn_close\" value=\"Close\" />\r\n        </div>\r\n    </div>\r\n</div>\r\n</script>\r\n','',1,'2014-09-26 15:02:04',''),('rm.functions','','','function secondsToHandM(time)\r\n{\r\n    var hours = Math.floor(time / 1000 / 3600);\r\n    var minutes = Math.floor(time / 1000 / 60) % 60;\r\n\r\n    var out = \"\";\r\n\r\n    (hours > 0) ? out += hours.toString() + \" hour(s) and \" : null;\r\n    out += minutes.toString() + \" minute(s)\";\r\n\r\n    return out;\r\n}\r\n\r\nfunction secondsToHms(sec)\r\n{\r\n    if(sec < 0)\r\n    {\r\n        return \"-\";\r\n    }\r\n    \r\n    var hours = Math.floor(sec / 1000 / 3600);\r\n    var minutes = Math.floor(sec / 1000 / 60) % 60;\r\n    var seconds = Math.floor(sec / 1000) % 60;\r\n    \r\n    var out = \"\";\r\n    \r\n    if(hours)\r\n        out += (hours > 9)   ? hours.toString() + \":\"   : \"0\" + hours.toString() + \":\";\r\n    \r\n    out += (minutes > 9) ? minutes.toString() + \":\" : \"0\" + minutes.toString() + \":\";\r\n    out += (seconds > 9) ? seconds.toString() : \"0\" + seconds.toString();\r\n    \r\n    return out;\r\n}\r\n\r\n// Add remove event\r\n(function() {\r\n    var ev = new $.Event(\'remove\'),\r\n            orig = $.fn.remove;\r\n    $.fn.remove = function() {\r\n        $(this).trigger(ev);\r\n        //return ;//orig.apply(this, arguments);\r\n    };\r\n})();\r\n\r\n// Add increaser\r\n(function() {\r\n    $.fn.increment = function(attr, incr)\r\n        {\r\n            var ov = parseInt($(this).attr(attr));\r\n            return $(this).attr(attr, ov + parseInt(incr));\r\n        };\r\n    \r\n    \r\n    $.fn.justtext = function() {\r\n        var clone = $(this).clone();\r\n            clone.children().remove();\r\n        return clone\r\n                .text().trim();\r\n    };\r\n    \r\n    String.prototype.toInt = function()\r\n    {\r\n        return parseInt(this);\r\n    };\r\n})();\r\n\r\n// Warn for input boxes\r\n(function($) {\r\n    $.fn.extend({\r\n        validate: function() {\r\n            var warns = 0;\r\n            $(this).each(function() {\r\n                if ($(this).filter(\"div\").length > 0 && $(this).filter(\"div\").serializeGenres().length === 0) {\r\n                    blinkElement(this);\r\n                    $(this).focus();\r\n                    warns++;\r\n                    return false;\r\n                }\r\n                if ($(this).filter(\"input, textarea\").length > 0 && $(this).filter(\"input, textarea\").val().length === 0) {\r\n                    blinkElement(this);\r\n                    $(this).focus();\r\n                    warns++;\r\n                    return false;\r\n                }\r\n            });\r\n            return warns;\r\n\r\n        },\r\n        serializeGenres: function() {\r\n            var genres = [];\r\n            $(\".rm_create_stream_genrelist > div\")\r\n                    .filter(\":not(.placeholder)\")\r\n                    .each(function() {\r\n                        if ($(this).text().length > 0)\r\n                        {\r\n                            genres.push($(this).text());\r\n                        }\r\n                    });\r\n            return genres.join(\", \");\r\n        }\r\n    });\r\n\r\n    function blinkElement(el)\r\n    {\r\n        var borderColor = $(el).css(\"border-color\");\r\n        var backColor = $(el).css(\"background-color\");\r\n        var item = $(el);\r\n        item.css({\r\n            \"background-color\": \"#fcc\",\r\n            \"border-color\": \"#f00\"\r\n        });\r\n        window.setTimeout(function() {\r\n            item.css({\r\n                \"background-color\": backColor,\r\n                \"border-color\": borderColor\r\n            });\r\n        }, 250);\r\n    }\r\n})(jQuery);\r\n\r\nArray.prototype.unique = function() {\r\n    var a = this.concat();\r\n    for(var i=0; i<a.length; ++i) {\r\n        for(var j=i+1; j<a.length; ++j) {\r\n            if(a[i] === a[j])\r\n                a.splice(j--, 1);\r\n        }\r\n    }\r\n\r\n    return a;\r\n};\r\n\r\nfunction redirectHome()\r\n{\r\n    window.location = \"/\";\r\n}\r\n\r\nfunction redirectLogin()\r\n{\r\n    window.location = \"/login\";\r\n}\r\n\r\n$.fn.extend({\r\n    fadeText: function(text)\r\n    {\r\n        if($(this).text() !== text)\r\n        {\r\n            $(this).stop().animate({opacity:0}, 250, function(){\r\n                $(this).text(text)\r\n                    .animate({opacity:1}, 250);\r\n            });\r\n        }\r\n    }\r\n});\r\n','','',1,'2014-09-09 12:17:18',''),('rm.gui.common','','.rm-gui-alert-error {\n    background: #f2dede;\n    border: 1px solid #eed3d7;\n    color: #b94a48;\n    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);\n    padding: 8px;\n    border-radius: 5px;\n    line-height: 150%;\n    margin-bottom: 16px;\n    display: none;\n}\n','','','',1,'2014-10-14 12:37:47',''),('rm.gui.picker','','.rm-gui-picker {\n    padding-top: 4px;\n    min-height: 28px;\n    height: auto;\n    cursor: text;\n}\n\n.rm-gui-picker.focused {\n    border-color: #5594b4;\n}\n\n.rm-gui-picker-search {\n    width: 68px;\n    padding: 2px;\n    outline: none;\n}\n\n.rm-gui-picker-text {\n    border: none;\n    width: 100%;\n    outline: none;\n    line-height: 18px;\n    height: 28px;\n    box-sizing: border-box;\n    border: 1px solid #ddd;\n    padding: 8px;\n}\n\n.rm-gui-picker-container {\n    width: 100%;\n    background-color: #fff;\n    border: 1px solid #eee;\n    box-sizing: border-box;\n    position: absolute;\n    border-radius: 5px;\n    padding: 4px;\n    display: none;\n    max-height: 200px;\n    overflow-y: auto;\n}\n\n.rm-gui-picker-container .-variants-block {\n}\n\n.rm-gui-picker-container .-variants-block .-variants > li {\n    padding: 0 8px;\n    line-height: 28px;\n    height: 28px;\n    cursor: pointer;\n}\n\n.rm-gui-picker-container .-variants-block .-variants > li:hover {\n    background-color: #ddd;\n}\n\n.rm-gui-picker-item {\n    background-color: #3a87ad;\n    color: #eee;\n    border-radius: 5px;\n    padding: 0 4px;\n    margin-right: 4px;\n    margin-bottom: 4px;\n    display: inline-block;\n    cursor: default;\n}\n\n\n.rm-gui-picker-item-close {\n    margin-left: 4px;\n    cursor: pointer;\n}\n\n.rm-gui-picker-empty {\n    width: 100%;\n    text-align: center;\n    padding: 0 8px;\n    line-height: 28px;\n    height: 28px;\n    display: none;\n    box-sizing: border-box;\n}','$.fn.extend({\n    rmpicker: function() {\n        \n        var initial = $(this);\n        var getter = $(this).attr(\"data-url\");\n        var list = $(this).text();\n        var ro = ($(this).attr(\"read-only\") !== undefined) ? true : false;\n        \n        var timer = false;\n        \n        var helper = $(\"#rmGuiPickerHepler\").tmpl();\n        var search = $(\"<span>\")\n            .attr(\"contentEditable\", true)\n            .addClass(\"rm-gui-picker-search\")\n            .bind(\'textchange\', function(event){\n                var req = $(this).text();\n                var vars = helper.find(\".-variants-block .-variants\");\n                \n                if (timer !== false) {\n                    window.clearInterval(timer);\n                }\n                timer = window.setTimeout(function(){\n                    timer = false;\n                    helper.show();\n                    $.post(\"/api/v2/tags/getList\", {\n                        s: req\n                    }, function(json){\n                        vars.children().remove();\n                        if (json.status === 1) {\n                            if (json.data.length === 0) {\n                                helper.find(\".rm-gui-picker-empty\").show();\n                            } else {\n                                helper.find(\".rm-gui-picker-empty\").hide();\n                                json.data.forEach(function(item){\n                                    vars.append($(\"<li>\")\n                                            .attr(\"data-value\", item.genre)\n                                            .attr(\"data-id\", item.id)\n                                            .text(item.genre))\n                                });\n                            }\n                        }\n                        if (!ro) {\n                            vars.append($(\"<li>\").attr(\"data-value\", req).text(\"Add \\\"\" + req + \"\\\"\"))\n                        }\n                    });\n                }, 250);\n            });\n\n        helper.find(\".-variants-block .-variants > li\").livequery(\"click\", function(event){\n            event.stopPropagation();\n            var editor = initial.find(\".rm-gui-picker-search\");\n            $(\"<span>\")\n                .addClass(\"rm-gui-picker-item\")\n                .text($(this).attr(\"data-value\"))\n                .append($(\"<input>\")\n                    .attr(\"type\", \"hidden\")\n                    .attr(\"name\", \"genre[]\")\n                    .val($(this).attr(\"data-id\")))\n                .append($(\"<img>\")\n                    .attr(\"src\", \"/images/iconCloseWhite.png\")\n                    .addClass(\"rm-gui-picker-item-close\")\n                    .on(\'click\', function(event){\n                        var targ = $(this).parent();\n                        targ.stop().animate({opacity:0}, 250, function(){\n                            targ.remove();\n                            editor.focus();\n                        });\n                    }))\n                .insertBefore(editor);\n            editor.text(\"\").focus();\n            helper.hide();\n        });\n        \n        initial\n            .live(\'focusin\', function(){ $(this).addClass(\"focused\"); })\n            .live(\'focusout\', function(){ $(this).removeClass(\'focused\'); });\n        \n        \n        $(\"body\").on(\"click\", function(event){\n            helper.hide();\n        });\n\n        search.appendTo(initial);\n        helper.insertAfter(initial);\n\n    }\n    \n});\n\n$(\".rm-gui-picker\").livequery(function(){\n    $(this).rmpicker();\n});\n','<script id=\"rmGuiPickerHepler\" type=\"text/x-jquery-tmpl\">\n<div class=\"rm-gui-picker-container\">\n    <div class=\"-variants-block\">\n        <div class=\"rm-gui-picker-empty\">No results</div>\n        <ul class=\"-variants\"></ul>\n    </div>\n</div>\n</script>','',1,'2014-10-20 19:39:08',''),('rm.header','<div class=\"rm_header_wrap rm_max_width\">\r\n    <ul class=\"rm_menu_wrap rm_fl_left\">\r\n        <li><a href=\"/\"><span id=\"rm_logo\">radiomanager</span></a></li>\r\n        <li><a class=\"createStream\" href=\"#\"><i class=\"icon-magic\"></i>Create stream</a></li>\r\n        <li><a class=\"upload\" href=\"#\"><i class=\"icon-cloud-upload\"></i>Upload audio</a></li>\r\n    </ul>\r\n    <ul class=\"rm_menu_wrap rm_fl_right\">\r\n        <?php if(user::getCurrentUserRights() > 0) : ?><li><a class=\"linkLarizet\" target=\"_blank\" href=\"/larize/\"><i class=\"icon-file\"></i>Larize</a></li><?php endif; ?>\r\n        <li><i class=\"icon-user\"></i><?= user::getCurrentUserName() ?></li>\r\n        <li><a class=\"linkLogout\" href=\"javascript:void(0);\"><i class=\"icon-exit\"></i>Logout</a></li>\r\n    </ul>\r\n</div>','.rm_header_wrap {\n    font-size: 11pt;\n}\n\n#rm_logo {\n    font-size: 14pt;\n    font-weight: bold;\n    color: #39c;\n}\n\n\n.rm_header_wrap {\n    border-bottom: 1px solid #eeeeee;\n    position: relative;\n    overflow: visible;\n    height: 64px;\n    background: #ffffff;\n}\n','function clientLogin(login, password)\r\n{\r\n    $.post(\"/login\", {login: login, password: password}, function(data) {\r\n        console.log(data);\r\n    });\r\n}\r\n\r\nfunction clientLogout()\r\n{\r\n    $.post(\"/logout\", {}, function(data) {\r\n        try\r\n        {\r\n            var json = JSON.parse(data);\r\n            if (json.code !== undefined)\r\n            {\r\n                if (json.code === \'LOGOUT_SUCCESS\')\r\n                {\r\n                    location.reload();\r\n                }\r\n                else\r\n                {\r\n                    myMessageBox(langMessages.user[json.code]);\r\n                }\r\n            }\r\n        }\r\n        catch (e)\r\n        {\r\n\r\n        }\r\n\r\n    });\r\n}\r\n\r\n\r\n$(\".linkLogout\").livequery(\"click\", function() {\r\n    clientLogout();\r\n});\r\n\r\n$(\".createStream\").live(\"click\", function() {\r\n    callModuleFunction(\"dialogs.createStream\");\r\n    return false;\r\n});\r\n','','',1,'2014-09-12 11:27:11',''),('rm.home.filter','','','','','',1,'2014-09-09 10:25:48',''),('rm.home.status','<?php\r\n$profile_tracks = track::getTracksCount(user::getCurrentUserId());\r\n$profile_time = track::getTracksDuration(user::getCurrentUserId());\r\n$profile_time_left = user::userUploadLeft();\r\n?>\r\n\r\n<div class=\"rm_status_wrap\">\r\n    <ul class=\"rm_status_list rm_status_selected rm_fl_left\">\r\n        <li>Selected tracks count <span id=\"sel_tracks_count\">0</span></li>\r\n        <li>Selected tracks duration <span id=\"sel_tracks_time\">0:00:00</span></li>\r\n    </ul>\r\n    <ul class=\"rm_status_list rm_fl_right\">\r\n        <li class=\"sort_filter\">\r\n            <input id=\"filterBox\" type=\"text\" placeholder=\"Type title, artist, album or genre\" value=\"\" />\r\n            <div id=\"filterReset\" title=\"Clear\"  />\r\n        </li>\r\n    </ul>\r\n</div>','.rm_status_wrap {\r\n    height: 48px;\r\n    background: #eee;\r\n    white-space: nowrap;\r\n}\r\n\r\n.rm_status_wrap:not(.selected) .rm_status_selected,\r\n.rm_status_wrap.selected .rm_status_total {\r\n    display: none;\r\n}\r\n\r\n.rm_status_list {\r\n    list-style-type: none;\r\n    padding: 0px;\r\n}\r\n\r\n.rm_status_list > li {\r\n    float: left;\r\n    margin: 0 8px;\r\n    line-height: 46px;\r\n}\r\n\r\n.rm_status_list > li > span {\r\n    background-color: #666;\r\n    padding: 0 8px;\r\n    border-radius: 3px;\r\n    color: #ffffff;\r\n    box-shadow: inset 1px 1px 4px #222222;\r\n    text-shadow: 0px 0px 3px rgba(0, 0, 0, 1);\r\n    margin-left: 8px\r\n}\r\n\r\n\r\n.rm_status_processing_item.hidden {\r\n    display: none;\r\n}\r\n\r\n.sort_filter {\r\n    position: relative;\r\n}\r\n\r\n.sort_filter:before {\r\n    content: \'Filter:\';\r\n    float: left;\r\n    padding-right: 8px;\r\n    overflow: hidden;\r\n}\r\n\r\n.sort_filter > input[type=\"text\"] {\r\n    width: 250px;\r\n    border-radius: 3px;\r\n    border: 1px solid #bbb;\r\n    height: 23px;\r\n    padding: 0px 8px;\r\n    box-sizing: border-box;\r\n    outline: none;\r\n    transition: border-color linear 250ms;\r\n    line-height: 23px;\r\n}\r\n\r\n.sort_filter > input[type=\"text\"]:focus {\r\n    border: 1px solid #39c;\r\n}\r\n\r\n.sort_filter > #filterReset {\r\n    width: 21px;\r\n    height: 21px;\r\n    background: #fff url(\"/images/iconClose.png\") no-repeat center center;\r\n    cursor: pointer;\r\n    position: absolute;\r\n    top: 0px;\r\n    right: 0px;\r\n    z-index: 20;\r\n    margin-top: 12px;\r\n    margin-right: 1px;\r\n    box-sizing: border-box;\r\n    opacity: 0.6;\r\n    display: none\r\n}\r\n\r\n.sort_filter > #filterReset.visible {\r\n    display: block;\r\n}\r\n\r\n.sort_filter > #filterReset:hover {\r\n    opacity: 1;\r\n}','(function(){\r\n    var timerHandle = false\r\n    \r\n    $(\"#filterBox\").livequery(function(){\r\n        if($(\"body\").hasClass(\"library\", \"unused\"))\r\n        {\r\n            $(this).bind(\'textchange\', function(){\r\n                if($(this).val().length > 0)\r\n                {\r\n                    $(\"#filterReset\").addClass(\"visible\");\r\n                }\r\n                else\r\n                {\r\n                    $(\"#filterReset\").removeClass(\"visible\");\r\n                }\r\n                queryTrackFilter();\r\n            });\r\n        }\r\n    });  \r\n    \r\n    $(\"#filterReset\").live(\"click\", function(e){\r\n        $(\"#filterBox\").val(\"\");\r\n        $(\"#filterReset\").removeClass(\"visible\");\r\n        queryTrackFilter();\r\n    });\r\n    \r\n    function queryTrackFilter() \r\n    {\r\n        if(timerHandle)\r\n        {\r\n            window.clearTimeout(timerHandle);\r\n        }\r\n        timerHandle = window.setTimeout(function(){\r\n            timerHandle = false;\r\n            ajaxGetTrackUniversal(true);\r\n        }, 200);\r\n    }\r\n})();\r\n','','',1,'2014-09-18 09:36:23',''),('rm.home.tracklist','','','','','',1,'2014-09-07 17:10:36',''),('rm.menu','','div.rm_mouse_menu_wrap {\r\n    position: absolute;\r\n}\r\n\r\nul.rm_mouse_menu {\r\n    position: absolute;\r\n    background: #ffffff;\r\n    border-radius: 5px;\r\n    box-shadow: 0px 0px 10px #888;\r\n    width: 200px;\r\n    list-style-type: none;\r\n    list-style-position: outside;\r\n}\r\n\r\nul.rm_mouse_menu > li.rm_mouse_menu_item {\r\n    padding: 8px;\r\n    cursor: pointer;\r\n    white-space: nowrap;\r\n    position: relative;\r\n}\r\n\r\nspan.rm_mouse_menu_title {\r\n    overflow: hidden;\r\n    text-overflow: ellipsis;\r\n}\r\n\r\nli.rm_mouse_menu_item > ul.rm_mouse_menu {\r\n    display: none;\r\n}\r\n\r\nli.rm_mouse_menu_item > ul.rm_mouse_menu:not(.rm_menu_right) {\r\n    left: 200px;\r\n}\r\n\r\nli.rm_mouse_menu_item > ul.rm_mouse_menu.rm_menu_right {\r\n    right: 200px;\r\n}\r\n\r\nli.rm_mouse_menu_item > ul.rm_mouse_menu:not(.rm_menu_bottom) {\r\n    top: 0px;\r\n}\r\n\r\nli.rm_mouse_menu_item > ul.rm_mouse_menu.rm_menu_bottom {\r\n    bottom: 0px;\r\n}\r\n\r\n\r\nli.rm_mouse_menu_item:hover > ul.rm_mouse_menu {\r\n    display: block;\r\n}\r\n\r\nul.rm_mouse_menu > li.rm_mouse_menu_item:first-child {\r\n    border-radius: 5px 5px 0 0;\r\n}\r\n\r\nul.rm_mouse_menu > li:not(.rm_mouse_menu_separator):last-child {\r\n    border-radius: 0 0 5px 5px;\r\n}\r\n\r\nul.rm_mouse_menu > li.rm_mouse_menu_separator\r\n{\r\n    height: 8px;\r\n    padding-top: 0;\r\n    padding-bottom: 0;\r\n}\r\n\r\nul.rm_mouse_menu > li.rm_mouse_menu_item:not(.rm_mouse_menu_disabled):not(.rm_mouse_menu_separator):hover {\r\n    background: #c2c7d1;\r\n}\r\n\r\n.rm_menu_arrow {\r\n    float: right;\r\n    font-size: 12pt;\r\n    line-height: 10px;\r\n    top: 0;\r\n}\r\n\r\n.rm_mouse_menu_disabled {\r\n    cursor: default !important;\r\n    color: rgba(0,0,0,0.3);\r\n}\r\n\r\nul.rm_mouse_menu .rm-right {\r\n    float: right;\r\n    margin: 0;\r\n}','// Menu for stream list\r\nfunction showStreamListMenu(element, event) {\r\n    var stream_id = $(element).attr(\"data-stream-id\");\r\n    var menu = [\r\n        {\r\n            name: \'<i class=\"icon-switch\"></i>Start/Stop stream\',\r\n            action: function() { callModuleFunction(\"stream.state\", stream_id); }\r\n        },\r\n        {\r\n            name: \'\'\r\n        },\r\n        {\r\n            name: \'<i class=\"icon-shuffle\"></i>Shuffle stream\',\r\n            action: function() { callModuleFunction(\"stream.shuffle\", stream_id); }\r\n        },\r\n        {   \r\n            name: \'<i class=\"icon-pencil\"></i>Edit stream...\',\r\n            action: function() { }\r\n        },\r\n        {\r\n            name: \'\'\r\n        },\r\n        {\r\n            name: \'<i class=\"icon-trash\"></i>Purge stream\',\r\n            action: function() { callModuleFunction(\"stream.purge\", stream_id); }\r\n        },\r\n\r\n        {\r\n            name: \'<i class=\"icon-cross\"></i>Delete stream\',\r\n            action: function() { callModuleFunction(\"stream.delete\", stream_id); }\r\n        }\r\n    ];\r\n   \r\n    var m = $(\"<div>\")\r\n        .addClass(\"rm_mouse_menu_wrap\")\r\n        .append(arrayToSubmenu(event, menu))\r\n        .bind(\"click\", function() { /* event.stopPropagation(); */ });\r\n\r\n    createMenu(event, m, \"body\");\r\n}\r\n\r\n\r\n// Stream View Tracklist Menu\r\nfunction showTrackInStreamMenu(e)\r\n{\r\n    var menu = [\r\n        {\r\n            name: $(\"<i>\").addClass(\"icon-play2\").get(0).outerHTML + \"Play on Radio\",\r\n            action: function() {\r\n                if($(\".rm_tracks_item.active\").length === 0) return;\r\n                var unique_id = $(\".rm_tracks_item.active\").attr(\"data-unique\");\r\n                var stream_id = active_stream.stream_id;\r\n                callModuleFunction(\"stream.play\", stream_id, unique_id);\r\n            }\r\n        },\r\n        {\r\n            name: $(\"<i>\").addClass(\"icon-pencil\").get(0).outerHTML + \"Metadata editor\",\r\n            enabled: $(\".rm_tracks_item.active\").length > 0,\r\n            action: function() { callModuleFunction(\"trackworks.tagEditor\"); }\r\n        },\r\n        {\r\n            name: $(\"<i>\").addClass(\"icon-plus\").get(0).outerHTML + \"Add selection to...\",\r\n            enabled: $(\".rm_tracks_item.selected[low-state=\'1\']\").length > 0,\r\n            submenu: showAddToStreamMenu()\r\n        },\r\n        {\r\n            name: $(\"<i>\").addClass(\"icon-trash\").get(0).outerHTML + \"Remove from stream\",\r\n            action: function() { callModuleFunction(\"trackworks.removeSelectionFromStream\"); }\r\n        }\r\n    ];\r\n\r\n    var m = $(\"<div>\")\r\n            .addClass(\"rm_mouse_menu_wrap\")\r\n            .append(arrayToSubmenu(e, menu))\r\n            .bind(\"click\", function() { /* event.stopPropagation(); */ });\r\n\r\n\r\n    createMenu(e, m, \"body\");\r\n}\r\n\r\n// Library Tracklist Menu\r\nfunction showTrackInTracklistMenu(e)\r\n{\r\n\r\n    var menu = [\r\n        {\r\n            name: $(\"<i>\").addClass(\"icon-pencil\").get(0).outerHTML + \"Metadata editor\",\r\n            enabled: $(\".rm_tracks_item.active\").length > 0,\r\n            action: function() { callModuleFunction(\"trackworks.tagEditor\"); }\r\n        },\r\n        {\r\n            name: $(\"<i>\").addClass(\"icon-plus\").get(0).outerHTML + \"Add selection to...\",\r\n            enabled: $(\".rm_tracks_item.selected[low-state=\'1\']\").length > 0,\r\n            submenu: showAddToStreamMenu()\r\n        },\r\n        {\r\n            name: $(\"<i>\").addClass(\"icon-trash\").get(0).outerHTML + \"Delete selected track(s)\",\r\n            action: function() { callModuleFunction(\"trackworks.killSelection\"); }\r\n        }\r\n    ];\r\n\r\n    var m = $(\"<div>\")\r\n            .addClass(\"rm_mouse_menu_wrap\")\r\n            .append(arrayToSubmenu(e, menu))\r\n            .bind(\"click\", function() { /* event.stopPropagation(); */\r\n            });\r\n\r\n\r\n    createMenu(e, m, \"body\");\r\n}\r\n\r\n\r\n\r\n$(document).ready(function()\r\n{\r\n    $(this).bind(\"click\", function() {\r\n        hideTracklistMenu();\r\n    });\r\n});\r\n\r\nfunction showAddToStreamMenu()\r\n{\r\n    var submenu = [];\r\n\r\n    $(\"ul.rm_streamlist > li\").each(function()\r\n    {\r\n        (function(sid, name) {\r\n            submenu.push({\r\n                name: \'<i class=\"icon-feed\"></i>\' + name,\r\n                action: function() {\r\n                    callModuleFunction(\"trackworks.addSelectionToStream\", sid);\r\n                }\r\n            });\r\n        })($(this).attr(\"data-stream-id\"), $(this).attr(\"data-name\"));\r\n    });\r\n\r\n    return submenu;\r\n}\r\n\r\n\r\nfunction createMenu(e, m, dst) {\r\n\r\n    var pageW = $(document).width();\r\n    var pageH = $(document).height();\r\n    var windH = $(window).height();\r\n\r\n    leftSide = (e.pageX < pageW / 2);\r\n    topSide = (e.clientY < windH / 2);\r\n\r\n    $(\"div.rm_mouse_menu_wrap\").remove();\r\n\r\n    m.appendTo(dst);\r\n\r\n    leftSide ? m.css(\"left\", (e.pageX + 4) + \"px\") : m.css({\"left\": (e.pageX - 4 - m.get(0).scrollWidth) + \"px\"});\r\n    topSide ? m.css(\"top\", (e.pageY + 4) + \"px\") : m.css({\"top\": (e.pageY - 4 - m.get(0).scrollHeight) + \"px\"});\r\n\r\n    return m;\r\n\r\n}\r\n\r\nfunction arrayToSubmenu(e, el)\r\n{\r\n    var pageW = $(document).width();\r\n    var pageH = $(document).height();\r\n    var windH = $(window).height();\r\n\r\n    leftSide = (e.pageX < pageW / 2);\r\n    topSide = (e.clientY < windH / 2);\r\n\r\n    var m = $(\"<ul>\").addClass(\"rm_mouse_menu\");\r\n\r\n    (leftSide === false) ? m.addClass(\"rm_menu_right\") : null;\r\n    (topSide === false) ? m.addClass(\"rm_menu_bottom\") : null;\r\n\r\n    el.forEach(function(item, i) {\r\n        m.append(arrayToItem(e, item));\r\n    });\r\n\r\n    return m;\r\n}\r\n\r\nfunction arrayToItem(e, el)\r\n{\r\n    var subArrow = $(\"<i>\").addClass(\"icon-arrow-right2 rm-right\");\r\n    var item = $(\"<li>\");\r\n    var span = $(\"<span>\").html(el.name).addClass(\"rm_mouse_menu_title\");\r\n\r\n    item.addClass(\"rm_mouse_menu_item\");\r\n\r\n    if (el.name === \"\")\r\n    {\r\n        item.addClass(\"rm_mouse_menu_separator\");\r\n        item.append(span);\r\n        return item;\r\n    }\r\n    \r\n    if (el.enabled === false)\r\n    {\r\n        item.addClass(\"rm_mouse_menu_disabled\");\r\n        item.append(span);\r\n        return item;\r\n    }\r\n\r\n    if (el.submenu !== undefined)\r\n    {\r\n        span.prepend(subArrow);\r\n    }\r\n\r\n    item.append(span);\r\n\r\n    if (el.submenu !== undefined)\r\n    {\r\n        item.append(arrayToSubmenu(e, el.submenu));\r\n        return item;\r\n    }\r\n\r\n    if (el.action !== undefined)\r\n    {\r\n        item.bind(\'click\', el.action);\r\n    }\r\n    return item;\r\n}\r\n\r\nfunction hideTracklistMenu()\r\n{\r\n    $(\"div.rm_mouse_menu_wrap\")\r\n            .remove();\r\n}','','',1,'2014-09-17 19:23:30',''),('rm.module.audio','','','(function(){\r\n    \r\n    \r\n    $(window).ready(function(){\r\n        $(\"#jplayer\").jPlayer({\r\n            ready: function(event) {\r\n            },\r\n            ended: function(event) {\r\n                playerStopped();\r\n            },\r\n            error: function(event) {\r\n                \r\n            },\r\n            timeupdate: function(event) {\r\n            },\r\n            progress: function(event) {\r\n            },\r\n            swfPath: \"/swf\",\r\n            supplied: \"mp3\",\r\n            solution: \"flash,html\",\r\n            volume: 1\r\n        });\r\n    });\r\n    \r\n    $(\'.rm_tracks_item .rm-track-preview\').live(\"click\", function(event) {\r\n        var track_id = $(this).parents(\".rm_tracks_item\").attr(\"track-id\");\r\n        if(track_id === now_playing) {\r\n            console.log(\"Stop\");\r\n            stopPlayer();\r\n            playerStopped();\r\n        } else {\r\n            console.log(\"Play\");\r\n            playerStopped();\r\n            startPlayer(track_id);\r\n        }\r\n    });\r\n    \r\n    function playerStopped() {\r\n        console.log(\"Stopped\");\r\n        $(\'.rm_tracks_item\').find(\".rm-track-preview\").html(\'<i class=\"icon-play2 no-margin\"></i>\');\r\n        now_playing = null;\r\n    }\r\n    \r\n})();\r\n\r\nvar now_playing = null;\r\n\r\n    function startPlayer(track_id) {\r\n        var selected = tracklist.getById(track_id);\r\n        var file = selected.find(\"input\").val();\r\n        selected.find(\".rm-track-preview\").html(\'<i class=\"icon-stop2 no-margin\"></i>\');\r\n        $(\"#jplayer\").jPlayer(\"setMedia\", {mp3:file}).jPlayer(\"play\");\r\n        now_playing = track_id;\r\n    }\r\n    \r\n\r\n    function stopPlayer() {\r\n        \r\n        $(\"#jplayer\").jPlayer(\"stop\").jPlayer(\"clearMedia\");\r\n        \r\n    }','','',1,'2014-09-13 20:57:38',''),('rm.service.popup','<div class=\"rm-popup-window\"></div>','.rm-popup-window {\n    position: absolute;\n    bottom: 20px;\n    left: 35%;\n    right: 35%;\n    padding: 16px;\n    font-size: 14pt;\n    color: #ffffff;\n    background: #25749c;\n    box-shadow: 0 0 16px 0 #1b5978;\n    text-align: center;\n    border-radius: 10px;\n    z-index: 20000;\n    opacity: 0;\n    visibility:hidden;\n    transition: visibility 0s linear 150ms, opacity linear 150ms;\n}\n\n.rm-popup-window.visible {\n    visibility:visible;\n    opacity: 1;\n    transition-delay:0s;\n}\n\n.rm-question-background {\n    top: 0;\n    left: 0;\n    width: 100%;\n    height: 100%;\n    background-color: rgba(0,0,0,0.5);\n    position: fixed;\n    overflow: hidden;\n    z-index: 1000;\n    font-size: 10pt;\n}\n\n.rm-question-border {\n    border-radius: 10px;\n    background-color: #ffffff;\n    box-shadow: 0 0 10px #000000;\n    padding: 8px;\n    box-sizing: border-box;\n}\n\n.rm-question-body {\n    padding: 32px 16px;\n    min-width: 400px;\n    box-sizing: border-box;\n    text-align: center;\n}\n\n.rm-question-body > h1 {\n    text-align: left;\n    padding: 0 4px;\n    margin: 0 0 24px 0;\n}\n\n.rm-action-buttons {\n    overflow: hidden;\n}\n\n.rm-action-buttons > * {\n    float: right;\n}\n\n.rm-question-button {\n    min-width: 48px;\n    text-align: center;\n    line-height: 20px;\n    margin-left: 4px;\n}\n\n.rm-question-close-button {\n    position: absolute;\n    top: 16px;\n    right: 16px;\n    cursor: pointer;\n    \n}\n\n.rm-question-close-button:hover {\n    color: #39c;\n}\n\n/* Default form styles */\n.rm-form-input-text {\n    border: 1px solid #eee;\n    width: 100%;\n    height: 28px;\n    outline: none;\n    line-height: 18px;\n    padding-left: 8px;\n    padding-right: 8px;\n    box-sizing: border-box;\n    border-radius: 5px;\n    vertical-align: baseline;\n}\n\n.rm-form-input-text:focus {\n    border-color: #5594b4;\n}\n\n.rm-form-field-wrapper {\n    position: relative;\n}\n\n.rm-form-field-wrapper:not(:last-child) {\n    margin-bottom: 16px;\n}\n\n.rm-form-field-title {\n    margin-bottom: 8px;\n}\n\n.rm-form-field-title {\n    padding-left: 4px;\n    padding-right: 4px;\n}\n\n.rm-form-field-validate {\n    position: absolute;\n    display: block;\n    right: 8px;\n    font-size: 9pt;\n    color: #f00;\n    bottom: 8px;\n    visibility: hidden;\n}\n\n.rm-form-field-validate.visible {\n    visibility: visible;\n}\n\n.rm-form-input-select {\n    border: 1px solid #eee;\n    width: 100%;\n    height: 28px;  \n    outline: none;\n    line-height: 18px;\n    padding-left: 8px;\n    padding-right: 8px;\n    box-sizing: border-box;\n    border-radius: 5px;\n    vertical-align: baseline;\n}\n\n.rm-form-input-select > option {\n    \n}\n\n/* Create stream .css */\n.rm-form-stream-create-wrapper {\n    width: 100%;\n    text-align: left;\n}\n\n/* Change password .css */\n.rm-form-change-password-wrapper {\n    width: 100%;\n    text-align: left;\n}\n\n','var popHandle = false;\n\nfunction showPopup(msg)\n{\n    $(\".rm-popup-window\").stop().text(msg).addClass(\"visible\");\n    if(popHandle) {\n        window.clearTimeout(popHandle);\n    }\n    popHandle = window.setTimeout(function() {\n        popHandle = false;\n        $(\".rm-popup-window\").removeClass(\"visible\");\n    }, 5000);\n}\n\nfunction myOwnQuestion(question, action) {\n    question = question || \"Are you sure?\";\n    action = action || function () {};\n    var dialog = $(\"#myOwnQuestionTemplate\").tmpl({message:question});\n    dialog.find(\"._close-question\").on(\"click\", function () { \n        dialog.remove();\n    });\n    dialog.find(\"._accept-question\").on(\"click\", function () { \n        dialog.remove(); \n        action();\n    });\n    raiseToHighestZindex($(dialog)).appendTo(\"body\");\n}\n\nvar dialogs = {\n    /* Create stream */\n    createStream: function () {\n        var dialog = $(\"#myOwnCreateStreamTemplate\").tmpl();\n        dialog.find(\"._close-question\").on(\"click\", function () { \n            dialog.remove();\n        });\n        \n        var select = dialog.find(\".rm-form-input-select\");\n        for(var i = 0; i < mor.categories.length; i ++) {\n            var option = $(\"<option>\").val(mor.categories[i].id).text(mor.categories[i].name);\n            if(i === 12) {\n                option.attr(\"selected\", \"selected\");\n            }\n            option.appendTo(select);\n        }\n\n        dialog.find(\"._accept-question\").on(\"click\", function (event) { \n            // Validate data\n            var form = dialog.find(\":input\");\n            dialog.find(\".rm-gui-alert-error\").hide();\n\n            // Create stream\n            $.post(\"/api/v2/stream/createStream\", form.serialize(), function(json) {\n                if(json.status === 1) {\n                    dialog.remove();\n                    callModuleFunction(\"streams.loadFromData\", json.jobs);\n                } else {\n                    dialog.find(\".rm-gui-alert-error\").show().text(json.message);\n                }\n            });\n        });\n        raiseToHighestZindex($(dialog)).appendTo(\"body\");\n    },\n    changePassword: function() {\n        var dialog = $(\"#myOwnChangePassword\").tmpl();\n        dialog.find(\"._close-question\").on(\"click\", function() { \n            dialog.remove();\n        });\n        dialog.find(\"._accept-question\").on(\"click\", function(event) {\n            var form = dialog.find(\":input\");\n            \n            // Check old password\n            var old = form.filter(\"#old\");\n            if(typeof old.attr(\"disabled\") === \"undefined\") {\n                if(old.val().length < 3 || old.val().length > 32) {\n                    dialog.find(\".rm-form-field-validate#old\").addClass(\"visible\").text(\"Password must contain from 3 to 32 chars\");\n                    return;\n                } else {\n                    dialog.find(\".rm-form-field-validate#old\").removeClass(\"visible\");\n                }\n            } else {\n                dialog.find(\".rm-form-field-validate#old\").removeClass(\"visible\");\n            }\n            \n            // Check password length\n            var new1 = form.filter(\"#new1\").val();\n            if(new1.length < 3 || new1.length > 32) {\n                dialog.find(\".rm-form-field-validate#new1\").addClass(\"visible\").text(\"Password must contain from 3 to 32 chars\");\n                return;\n            } else {\n                dialog.find(\".rm-form-field-validate#new1\").removeClass(\"visible\");\n            }\n            \n            // Check password match\n            var new2 = form.filter(\"#new2\").val();\n            if(new1 != new2) {\n                dialog.find(\".rm-form-field-validate#new2\").addClass(\"visible\").text(\"Passwords mismatch\");\n                return;\n            } else {\n                dialog.find(\".rm-form-field-validate#new2\").removeClass(\"visible\");\n            }\n            \n            // Create request\n            $.post(\"/radiomanager/changePassword\", form.serialize(), function(data) {\n                try {\n                    var json = JSON.parse(data);\n                } catch(e) {\n                    return;\n                }\n                if(json.code === \"SUCCESS\") {\n                    mor.user_permanent = 1;\n                    dialog.remove();\n                }\n                showPopup(lang.conv(json.code, \"user.password\"));\n            });\n            \n        });\n        raiseToHighestZindex($(dialog)).appendTo(\"body\");\n    }\n};\n\nraiseToHighestZindex = function(elem) {\n    var highest_index = 0;\n    $(\"*\").each(function() {\n        var cur_zindex= $(this).css(\"z-index\");\n        if (cur_zindex > highest_index) {\n            highest_index = cur_zindex;\n            $(elem).css(\"z-index\", cur_zindex + 1);\n        }\n    });\n    return $(elem);\n};\n\n\n','<script id=\"myOwnQuestionTemplate\" type=\"text/x-jquery-tmpl\">\n<div class=\"rm-question-background\">\n    <div class=\"rm-question-border -rm-center\">\n        <div class=\"_close-question rm-question-close-button\"><i class=\"icon-close no-margin\"></i></div>\n        <div class=\"rm-question-body\">${message}</div>\n        <div class=\"rm-action-buttons\">\n            <div class=\"rm-single-button rm-question-button _close-question\">No</div>\n            <div class=\"rm-single-button rm-question-button _accept-question\">Yes</div>\n        </div>\n    </div>\n</div>\n</script>\n<script id=\"myOwnCreateStreamTemplate\" type=\"text/x-jquery-tmpl\">\n<div class=\"rm-question-background\">\n    <div class=\"rm-question-border -rm-center\">\n        <div class=\"_close-question rm-question-close-button\"><i class=\"icon-close no-margin\"></i></div>\n        <div class=\"rm-question-body\">\n            <h1>Create stream</h1>\n            <div class=\"rm-gui-alert-error\"><b>Alert!</b> Try again.</div>\n            <form class=\"rm-form-stream-create\">\n                <div class=\"rm-form-stream-create-wrapper\">\n                    <div class=\"rm-form-field-wrapper\">\n                        <div class=\"rm-form-field-title\">Stream name:</div>\n                        <div class=\"rm-form-field-input\">\n                            <input type=\"text\" id=\"name\" name=\"name\" class=\"rm-form-input-text\" placeholder=\"\" autocomplete=\"off\" />\n                            <div id=\"name\" class=\"rm-form-field-validate\">Some error occured</div>\n                        </div>\n                    </div>\n                    <div class=\"rm-form-field-wrapper\">\n                        <div class=\"rm-form-field-title\">Stream category:</div>\n                        <div class=\"rm-form-field-input\">\n                            <select name=\"category\" class=\"rm-form-input-select\"></select>\n                        </div>\n                    </div>\n                    <div class=\"rm-form-field-wrapper\">\n                        <div class=\"rm-form-field-title\">Stream genres:</div>\n                        <div class=\"rm-form-field-input\">\n                            <div class=\"rm-form-input-text rm-gui-picker\" read-only data-url=\"/api/v2/genres/getList\"></div>\n                        </div>\n                    </div>\n                </div>\n            </form>\n        </div>\n        <div class=\"rm-action-buttons\">\n            <div class=\"rm-single-button rm-question-button _accept-question\"><i class=\"icon-magic\"></i>Create</div>\n            <div class=\"rm-single-button rm-question-button _close-question\"><i class=\"icon-close\"></i>Cancel</div>\n        </div>\n    </div>\n</div>\n</script>\n<script id=\"myOwnChangePassword\" type=\"text/x-jquery-tmpl\">\n<div class=\"rm-question-background\">\n    <div class=\"rm-question-border -rm-center\">\n        <div class=\"_close-question rm-question-close-button\"><i class=\"icon-close no-margin\"></i></div>\n        <div class=\"rm-question-body\">\n            <h1>Change password</h1>\n            <form class=\"rm-form-change-password\">\n                <div class=\"rm-form-change-password-wrapper\">\n                    <div class=\"rm-form-field-wrapper\">\n                        <div class=\"rm-form-field-title\">Your current password:</div>\n                        <div class=\"rm-form-field-input\">\n                            <input type=\"password\" id=\"old\" name=\"old\" class=\"rm-form-input-text\" placeholder=\"\" autocomplete=\"off\" />\n                            <div id=\"old\" class=\"rm-form-field-validate\">Some error occured</div>\n                        </div>\n                    </div>\n                    <div class=\"rm-form-field-wrapper\">\n                        <div class=\"rm-form-field-title\">Your new password:</div>\n                        <div class=\"rm-form-field-input\">\n                            <input type=\"password\" id=\"new1\" name=\"new1\" class=\"rm-form-input-text\" placeholder=\"\" autocomplete=\"off\" />\n                            <div id=\"new1\" class=\"rm-form-field-validate\">Some error occured</div>\n                        </div>\n                    </div>\n                    <div class=\"rm-form-field-wrapper\">\n                        <div class=\"rm-form-field-title\">Repeat:</div>\n                        <div class=\"rm-form-field-input\">\n                            <input type=\"password\" id=\"new2\" name=\"new2\" class=\"rm-form-input-text\" placeholder=\"\" autocomplete=\"off\" />\n                            <div id=\"new2\" class=\"rm-form-field-validate\">Some error occured</div>\n                        </div>\n                    </div>\n                </div>\n            </form>\n        </div>\n        <div class=\"rm-action-buttons\">\n            <div class=\"rm-single-button rm-question-button _close-question\"><i class=\"icon-close\"></i>Cancel</div>\n            <div class=\"rm-single-button rm-question-button _accept-question\"><i class=\"icon-magic\"></i>Change</div>\n        </div>\n    </div>\n</div>\n</script>\n','',1,'2014-10-20 19:15:47',''),('rm.service.subscribe','','','(function($, w, j) {\r\n    \r\n    $(document).on(\"ready\", function () {\r\n        //initStatus(0);\r\n    });\r\n\r\n    function initStatus(timeout) {\r\n        \r\n        w.setTimeout(function() {\r\n\r\n            $.post(\"/radiomanager/eventListen\", { s: mor.last_event }, function(data) {\r\n                    var json = filterAJAXResponce(data);\r\n                    // Code here\r\n                    var eventData = json.data.EVENTS;\r\n                    for (var i in eventData) {\r\n                        var ev = eventData[i];\r\n                        switch(ev.event_type) {\r\n                            case \'LORES_CHANGED\':\r\n                                //callModuleFunction(\"tracklist.trackChangeState\", ev.event_target, ev.event_value); \r\n                                break;\r\n                            case \'TRACK_INFO_CHANGED\':\r\n                                //callModuleFunction(\"tracklist.trackUpdate\", ev.event_target);\r\n                                break;\r\n                            case \'TRACK_DELETED\':\r\n                                //callModuleFunction(\"tracklist.trackDelete\", ev.event_target);\r\n                                //mor.tracks_count --;\r\n                                break;\r\n                            case \'TRACK_ADDED\':\r\n                                //callModuleFunction(\"tracklist.trackAdd\", ev.event_target);\r\n                                //mor.tracks_count ++;\r\n                                break;\r\n                            case \'STREAM_DELETED\':\r\n                                callModuleFunction(\"streams.deleteStream\", ev.event_target);\r\n                                mor.streams_count --;\r\n                                break;\r\n                            case \'STREAM_ADDED\':\r\n                                callModuleFunction(\"streams.addStream\", ev.event_target);\r\n                                mor.streams_count ++;\r\n                                break;\r\n                            case \'STREAM_TRACKS_CHANGED\':\r\n                                //eventUpdateStream(ev.event_target);\r\n                                callModuleFunction(\"streams.setTrackCount\", ev.event_target, ev.event_value);\r\n                                break;\r\n                            case \'STREAM_TRACK_ADDED\':\r\n                                //eventUpdateStream(ev.event_target);\r\n                                break;\r\n                            case \'STREAM_TRACK_DELETED\':\r\n                                //callModuleFunction(\"tracklist.removeFromStream\", ev.event_value);\r\n                                break;\r\n                            case \'STREAM_SET_CURRENT\':\r\n                                callModuleFunction(\"tracklist.setNowPlaying\", ev.event_value, ev.event_target);\r\n                                break;\r\n                            case \'STREAM_SORT\':\r\n                                callModuleFunction(\"tracklist.setNewIndex\", ev.event_target, ev.event_value);\r\n                                break;\r\n                            case \'TOKEN_REMOVE\':\r\n                                if(ev.event_value === mor.user_token) {\r\n                                    redirectLogin();\r\n                                }\r\n                                break;\r\n                            case \'LIB_DURATION_CHANGED\':\r\n                                mor.tracks_duration = ev.event_value;\r\n                                break;\r\n                            case \'STREAM_SORTED\':\r\n                                callModuleFunction(\"stream.reload\", ev.event_target);\r\n                                break;\r\n                            case \'STREAM_UPDATED\':\r\n                                callModuleFunction(\"streams.updateStream\", ev.event_target);\r\n                                break;\r\n                        }\r\n                    }\r\n                    try { updateRadioManagerInterface() } catch(e) {}\r\n                    mor.last_event = json.data.LAST_EVENT_ID;\r\n                    initStatus(0);\r\n            })\r\n                    .error(function()\r\n                    {\r\n                        initStatus(1000);\r\n                    });\r\n        }, timeout);\r\n    }\r\n\r\n})(jQuery, window, JSON);\r\n','','',1,'2014-09-23 14:04:22',''),('rm.sidebar','<div class=\"rm_sidebar_wrap\">\r\n    <div class=\"rm_sidebar_content\">\r\n        <h1><i class=\"icon-folder-open\"></i>My Library</h1>\r\n        <ul class=\"rm_sidebar_list rm_library\">\r\n            <li class=\"<?= application::testRoute(\"radiomanager\", \"active\"); ?>\">\r\n                <div title=\"Number of tracks in library\" class=\"rm_fl_right profile-tracks-count\"><?= track::getTracksCount(user::getCurrentUserId()) ?></div>\r\n                <a href=\"/radiomanager/\"><i class=\"icon-music\"></i>All tracks</a>\r\n            </li>\r\n            <li class=\"<?= application::testRoute(\"radiomanager/unused\", \"active\"); ?>\">\r\n                <a href=\"/radiomanager/unused\"><i class=\"icon-music\"></i>Unused tracks</a>\r\n            </li>\r\n        </ul>\r\n        <div class=\"rm_sep\"></div>\r\n        <h1><i class=\"icon-folder-open\"></i>My Streams</h1>\r\n        <!-- module:rm.sidebar.streamlist -->\r\n        <ul class=\"rm_sidebar_list rm_streamlist\"></ul>\r\n        <div class=\"rm_sep\"></div>\r\n        <h1><i class=\"icon-folder-open\"></i>My Settings</h1>\r\n        <ul class=\"rm_sidebar_list rm_settings\">\r\n            <li class=\"<?= application::testRoute(\"radiomanager/profile\", \"active\"); ?>\">\r\n                <a href=\"/radiomanager/profile\" title=\"Edit my profile\"><i class=\"icon-profile\"></i>User Profile</a>\r\n            </li>\r\n            <li class=\"<?= application::testRoute(\"radiomanager/preferences\", \"active\"); ?>\">\r\n                <a href=\"/radiomanager/preferences\" title=\"Edit my preferences\"><i class=\"icon-tools\"></i>Preferences</a>\r\n            </li>\r\n            <li class=\"<?= application::testRoute(\"radiomanager/statistics\", \"active\"); ?>\">\r\n                <a href=\"/radiomanager/statistics\" title=\"My streams statistics\"><i class=\"icon-stats\"></i>Statistics</a>\r\n            </li>\r\n        </ul>\r\n        <div class=\"rm_infobar_container\">\r\n            <table class=\"rm_infobar_list\">\r\n                <tr><td>Total tracks:</td><td><span class=\"profile-tracks-count\">0</span></td></tr>\r\n                <tr><td>Total duration:</td><td><span class=\"profile-tracks-time\">0:00:00</span></td></tr>\r\n                <tr><td>Time left:</td><td><span id=\"total_time_left\"><?= misc::convertuSecondsToTime(user::userUploadLeft()) ?></span></td></tr>\r\n            </table>\r\n            <div class=\"rm_infobar_progress\">\r\n                <div id=\"handle\"></div>\r\n                <div id=\"cents\">0%</div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</div>','.rm_sidebar_wrap {\r\n    border-right: 1px solid #eeeeee;\r\n    width: 225px;\r\n    box-sizing: border-box;\r\n    display: table-cell;\r\n    vertical-align: top;\r\n    position: relative;\r\n}\r\n\r\n.rm_sidebar_wrap h1 {\r\n    padding-left: 8px;\r\n}\r\n\r\n.rm_sidebar_content {\r\n    padding: 0;\r\n    padding-top: 8px;\r\n    \r\n}\r\n\r\n.rm_sidebar_list {\r\n    padding-bottom: 12px;\r\n}\r\n\r\n.rm_sidebar_list > li {\r\n    padding-top: 4px;\r\n    padding-bottom: 4px;\r\n    padding-left: 16px;\r\n    padding-right: 8px;\r\n}\r\n\r\n.rm_sidebar_list > li > a {\r\n    display: block;\r\n}\r\n\r\n\r\n\r\n.rm_sidebar_list {\r\n    \r\n}\r\n\r\nul.rm_sidebar_list > li {\r\n    \r\n}\r\n\r\n.rm_sidebar_list > .active,\r\n.rm_sidebar_list > .selected,\r\n.rm_sidebar_list > .current {\r\n    background-color: #c2c7d1;\r\n}\r\n\r\n\r\n.rm_infobar_container {\r\n    background-color: #eee;\r\n    position: absolute;\r\n    bottom: 0;\r\n    width: 100%;\r\n    box-sizing: border-box;\r\n    height: 100px;\r\n    padding: 8px;\r\n}\r\n\r\n.rm_infobar_list {\r\n    width: 100%;\r\n    text-shadow: 1px 1px 0px #fff;\r\n}\r\n\r\n.rm_infobar_list tr td {\r\n    padding: 4px 0;\r\n}\r\n\r\n.rm_infobar_list tr td:last-child {\r\n    text-align: right;\r\n    font-weight: bold;\r\n}\r\n\r\n.rm_infobar_progress {\r\n    position: relative;\r\n    height: 16px;\r\n    background-color: #888;\r\n    margin-top: 4px;\r\n    box-shadow: inset 1px 1px 4px #555;\r\n}\r\n\r\n\r\n.rm_infobar_progress #handle {\r\n    width: 0;\r\n    height: 100%;\r\n    position: absolute;\r\n    z-index: 1;\r\n    /* background-color: #25749c; */\r\n    box-shadow: inset -1px -1px 1px #333, inset 1px 1px 1px rgb(57,136,176);\r\n    \r\n    background: rgb(37,116,156); /* Old browsers */\r\n    background: -moz-linear-gradient(top, rgba(37,116,156,1) 0%, rgba(68,126,155,1) 47%, rgba(37,116,156,1) 48%, rgba(37,116,156,1) 48%, rgba(87,132,155,1) 100%); /* FF3.6+ */\r\n    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(37,116,156,1)), color-stop(47%,rgba(68,126,155,1)), color-stop(48%,rgba(37,116,156,1)), color-stop(48%,rgba(37,116,156,1)), color-stop(100%,rgba(87,132,155,1))); /* Chrome,Safari4+ */\r\n    background: -webkit-linear-gradient(top, rgba(37,116,156,1) 0%,rgba(68,126,155,1) 47%,rgba(37,116,156,1) 48%,rgba(37,116,156,1) 48%,rgba(87,132,155,1) 100%); /* Chrome10+,Safari5.1+ */\r\n    background: -o-linear-gradient(top, rgba(37,116,156,1) 0%,rgba(68,126,155,1) 47%,rgba(37,116,156,1) 48%,rgba(37,116,156,1) 48%,rgba(87,132,155,1) 100%); /* Opera 11.10+ */\r\n    background: -ms-linear-gradient(top, rgba(37,116,156,1) 0%,rgba(68,126,155,1) 47%,rgba(37,116,156,1) 48%,rgba(37,116,156,1) 48%,rgba(87,132,155,1) 100%); /* IE10+ */\r\n    background: linear-gradient(to bottom, rgba(37,116,156,1) 0%,rgba(68,126,155,1) 47%,rgba(37,116,156,1) 48%,rgba(37,116,156,1) 48%,rgba(87,132,155,1) 100%); /* W3C */\r\n    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr=\'#25749c\', endColorstr=\'#57849b\',GradientType=0 ); /* IE6-9 */\r\n}\r\n\r\n.rm_infobar_progress #cents {\r\n    text-align: center;\r\n    position: absolute;\r\n    width: 100%;\r\n    height: 100%;\r\n    font-size: 8pt;\r\n    padding-top: 3px;\r\n    color: #fff;\r\n    text-shadow: 1px 1px 2px #666;\r\n    z-index: 2;\r\n    box-sizing: border-box;\r\n}\r\n\r\n.rm_infobar_progress.over #handle {\r\n    background-color: #9e5454;\r\n}\r\n','','','',1,'2014-09-29 13:44:18',''),('rm.sidebar.streamlist','<?php\r\n$stream_id = application::get(\'stream_id\', NULL, REQ_INT);\r\n$stream_data = base64_encode(json_encode(stream::getStreams(user::getCurrentUserId(), $stream_id)));\r\n?>\r\n<div class=\"rm_streams_data\" content=\"<?= $stream_data ?>\"></div>','ul.rm_streamlist:empty:after {\r\n    padding: 4px;\r\n    content: \'No streams yet\';\r\n}\r\n\r\n\r\nul.rm_streamlist > li .rm_playStream {\r\n    display: none;\r\n}\r\n\r\nul.rm_streamlist > li:hover .rm-streams-badge {\r\n    display: none;\r\n}\r\n\r\nul.rm_streamlist > li:hover .rm_playStream {\r\n    display: block;\r\n}\r\n\r\n.stream-state-color {\r\n    color: #ff0000;\r\n}\r\n\r\nul.rm_streamlist > li[data-state=\"1\"] .stream-state-color {\r\n    color: #00aa00;\r\n}','var streams = {\r\n    setTrackCount: function(stream_id, track_count) {\r\n        return streams.getById(stream_id).find(\".rm-streams-badge\").text(track_count);\r\n    },\r\n    streamExists: function(stream_id) {\r\n        return $(\".rm_streamlist > li[data-stream-id=\'\" + stream_id + \"\']\").length > 0;\r\n    },\r\n    getById: function(stream_id) {\r\n        return $(\".rm_streamlist > li[data-stream-id=\'\" + stream_id + \"\']\");\r\n    },\r\n    deleteStream: function(stream_id) {\r\n        return streams.getById(stream_id).remove();\r\n    },\r\n    addStream: function(stream_id) {\r\n        if (streams.getById(stream_id).length !== 0) return;\r\n        $.post(\"/radiomanager/api/getStreamItem\", { stream_id: stream_id }, function(data) {\r\n            try {\r\n                var json = JSON.parse(data);\r\n                streams.loadFromData(json);\r\n            } catch(e) {}\r\n        });\r\n    },\r\n    updateStream: function(stream_id) {\r\n        if(streams.getById(stream_id).length === 0) return;\r\n        $.post(\"/radiomanager/api/getStreamItem\", { stream_id: stream_id }, function(data) {\r\n            try {\r\n                var json = JSON.parse(data);\r\n                streams.updateFromData(stream_id, json);\r\n            } catch(e) {}\r\n        });\r\n    },\r\n    loadFromData: function(data) {\r\n        $(\"#streamTemplate\").tmpl(data).appendTo(\".rm_streamlist\");\r\n    },\r\n    updateFromData: function(stream_id, data) {\r\n        streams.getById(stream_id).replaceWith($(\"#streamTemplate\").tmpl(data));\r\n    },\r\n    setStatus: function(stream_id, status) {\r\n        streams.getById(stream_id).attr(\"data-state\", status);\r\n    }\r\n};\r\n\r\n$(document).ready(function()\r\n{\r\n    $(\".track-accept.stream\").livequery(function() {\r\n        $(this).droppable({\r\n            drop: function(event, ui) {\r\n                var stream_id = $(this).attr(\'data-stream-id\');\r\n                var track_id = ui.helper.attr(\'track-id\');\r\n                callModuleFunction(\"trackworks.addSelectionToStream\", stream_id, track_id);\r\n                $(this).toggleClass(\'selected\', false);\r\n            },\r\n            over: function(event, ui) {\r\n                $(this).toggleClass(\'selected\', true);\r\n            },\r\n            out: function(event, ui) {\r\n                $(this).toggleClass(\'selected\', false);\r\n            },\r\n            accept: \":not(.rm_streamview) .rm_tracks_item\",\r\n            tolerance: \"pointer\"\r\n        });\r\n    });\r\n    $(\".rm_streams_data\").livequery(function(){\r\n        var data = JSON.parse(atob($(this).attr(\'content\')));\r\n        $(this).remove();\r\n        streams.loadFromData(data);\r\n    });\r\n    $(\".rm_streamlist > li\").livequery(function(){\r\n        // Context menu implementation\r\n        $(this).on(\'contextmenu\', function(event) {\r\n            showStreamListMenu(this, event);\r\n            event.preventDefault();\r\n            return false;\r\n        });\r\n    });\r\n});','<script id=\"streamTemplate\" type=\"text/x-jquery-tmpl\">\r\n<li data-stream-id=\"${sid}\" data-state=\"${status}\" data-name=\"${name}\" class=\"track-accept stream ${active}\">\r\n    <div title=\"Listen to this stream\" class=\"rm_fl_right rm_playStream\">\r\n        <a href=\"/stream/${sid}#play\">listen</a>\r\n    </div>\r\n    <div title=\"Number of tracks in stream\" class=\"rm-streams-badge rm_fl_right\">${tracks}</div>\r\n    <a href=\"/radiomanager/stream?stream_id=${sid}\" title=\"${name}\">\r\n        <i class=\"icon-feed stream-state-color\"></i>\r\n        ${name}\r\n    </a>\r\n</li>\r\n</script>\r\n','',1,'2014-09-11 12:44:31',''),('rm.stream.status','<?php $stream = application::singular(\"stream\", $_MODULE[\'stream_id\']); ?>\r\n<div class=\"rm_status_wrap\">\r\n    <ul class=\"rm_float_list fl_l\">\r\n        <li style=\"padding-top:2px\">\r\n            <div class=\"rm-button-group\">\r\n                <div class=\"rm-stream-ctrl-button\" title=\"Stream previous track\"><i class=\"icon-backward no-margin\"></i></div>\r\n                <div class=\"rm-stream-ctrl-button rm-stream-switch\" title=\"Stream start/stop\"><i class=\"icon-play2 no-margin\"></i></div>\r\n                <div class=\"rm-stream-ctrl-button\" title=\"Stream next track\"><i class=\"icon-forward no-margin\"></i></div>\r\n            </div>\r\n            <div class=\"rm-button-group\">\r\n                <div class=\"rm-stream-ctrl-button rm-shuffle-button\" title=\"Shuffle stream\"><i class=\"icon-shuffle no-margin\"></i></div>\r\n                <div class=\"rm-stream-ctrl-button rm-purge-button\" title=\"Purge stream\"><i class=\"icon-trash-o no-margin\"></i></div>\r\n            </div>\r\n        </li>\r\n    </ul>\r\n    <ul class=\"rm_float_list fl_r rm_subpad\">\r\n        <li id=\"listen\" style=\"padding-top:2px\">\r\n            <div class=\"rm-button-group\">\r\n                <div class=\"rm-stream-ctrl-button wpad4px\" title=\"Open stream on page\">\r\n                    <a href=\"<?= $stream->getStreamLink() ?>\"><i class=\"icon-link special\"></i>Open</a>\r\n                </div>\r\n                <div class=\"rm-stream-ctrl-button wpad4px\" title=\"Open stream in external player\">\r\n                    <a href=\"/tools/m3u?stream_id=<?= $stream->getStreamId(); ?>\"><i class=\"icon-disk special\"></i>Save .m3u</a>\r\n                </div>\r\n            </div>\r\n        </li>\r\n    </ul>\r\n    <div class=\"rm-stream-status-now\"><div class=\"rm-stream-status-inner\"><i class=\"icon-bullhorn\"></i><span class=\"ttl\"></span></div></div>\r\n</div>','.rm_status_wrap {\r\n    padding: 8px;\r\n    overflow: hidden;\r\n    background: #eee;\r\n    height: 48px;\r\n    box-sizing: border-box;\r\n}\r\n\r\n.rm_status_wrap .ttl {\r\n    font-weight: bold;\r\n}\r\n\r\n.rm_status_wrap li {\r\n    overflow: hidden;\r\n    text-overflow: ellipsis; \r\n}\r\n\r\n.rm_float_list {\r\n    overflow: hidden;\r\n}\r\n\r\n.rm_float_list > li {\r\n    float: left;\r\n}\r\n\r\n.rm_float_list > li#open,\r\n.rm_float_list > li#listen {\r\n    margin-right: 8px;\r\n}\r\n\r\n\r\n/* Switcher Button */\r\n\r\n.mor_ui_switch {\r\n    width: 42px;\r\n    height: 22px;\r\n    border-radius: 11px;\r\n    overflow: hidden;\r\n    cursor: pointer;\r\n    position: relative;\r\n    box-sizing: border-box;\r\n    display: inline-block;\r\n    box-shadow: inset 1px 1px 1px rgba(0,0,0,.3), inset -1px -1px 1px rgba(0,0,0,.1);\r\n    vertical-align: text-bottom;\r\n    margin: 0 4px;\r\n}\r\n\r\n.mor_ui_switch:not(.on) {\r\n    background: #ff6464;\r\n}\r\n\r\n.mor_ui_switch.on {\r\n    background: #1fd40c;\r\n}\r\n\r\n.mor_ui_switch .rm_ui_switch_handle {\r\n    width: 18px;\r\n    height: 18px;\r\n    margin: 2px;\r\n    border-radius: 10px;\r\n    background: #f9f9f9;\r\n    box-sizing: border-box;\r\n    position: absolute;\r\n    left: 0px;\r\n}\r\n\r\n.mor_ui_switch.on .rm_ui_switch_handle {\r\n    left: 20px;\r\n}\r\n\r\n.mor_ui_switch.on .rm_ui_switch_text {\r\n    left: 0px ;\r\n}\r\n\r\n.rm-stream-status-listen:hover {\r\n    background-color: #fafafa;\r\n}\r\n\r\n.rm-stream-ctrl-button {\r\n    text-align: center;\r\n}\r\n\r\n.rm-stream-ctrl-button > i {\r\n    position: relative;\r\n    margin: 0;\r\n    font-size: 12pt;\r\n    vertical-align: -2px;\r\n}\r\n\r\n.rm-stream-status-now {\r\n    overflow: hidden;\r\n    cursor: default;\r\n}\r\n\r\n.rm-stream-status-inner {\r\n    padding: 8px 8px;\r\n    margin: 0 8px;\r\n    border-radius: 6px;\r\n    background-color: #25749c;\r\n    color: #eeeeee;\r\n    box-shadow: inset 2px 2px 4px #1b5978, 0 0 1px #1b5978;\r\n    text-shadow: 1px 1px 1px #1b5978;\r\n}\r\n\r\n\r\n','var nowplaying = {\r\n    update_interval_max: 10000,\r\n    handle: false,\r\n    previousTrack: null,\r\n    currentTrack: null,\r\n    sync: function(forced) {\r\n        if(nowplaying.handle) {\r\n            window.clearTimeout(nowplaying.handle);\r\n        }\r\n        $.post(\"/streamStatus\", { \r\n            stream_id : active_stream.stream_id\r\n        }, function(data) {\r\n            var json = filterAJAXResponce(data);\r\n            var nextIn;\r\n            if(json.stream_status === 1) {\r\n                nextIn = json.time_left || 5000 > nowplaying.update_interval_max ? nowplaying.update_interval_max : json.time_left || 5000;\r\n                nowplaying.currentTrack = json;\r\n            } else {\r\n                nextIn = 5000;\r\n                nowplaying.currentTrack = null;\r\n            }\r\n            nowplaying.update(forced).handle = window.setTimeout(function() {\r\n                nowplaying.handle = false;\r\n                nowplaying.sync();\r\n            }, nextIn);\r\n        });\r\n        return nowplaying;\r\n    },\r\n    update: function(forced) {\r\n        forced = forced || false;\r\n\r\n        if((nowplaying.currentTrack !== null) && (nowplaying.currentTrack.stream_status === 1)) {\r\n            $(\".rm-stream-switch:not(.active)\").addClass(\"active\");\r\n        } else {\r\n            tracklist.nothingPlaying();\r\n            $(\".rm-stream-switch.active\").removeClass(\"active\");\r\n            $(\".rm_status_wrap .ttl\").text(\"Stream stopped\");\r\n            return nowplaying;\r\n        }\r\n        if(forced || (nowplaying.previousTrack === null) || (nowplaying.previousTrack.unique_id !== nowplaying.currentTrack.unique_id)) {\r\n            $(\".rm_status_wrap .ttl\").text(nowplaying.currentTrack.t_order + \". \" + nowplaying.currentTrack.now_playing);\r\n            tracklist.setNowPlaying(nowplaying.currentTrack.unique_id);\r\n            nowplaying.previousTrack = nowplaying.currentTrack;\r\n        }\r\n        return nowplaying;\r\n    }\r\n};\r\n\r\n$(document).on(\"ready\", function() {\r\n    if($(\"body\").hasClass(\"stream\")) {\r\n        nowplaying.sync();\r\n        $(\".rm-stream-switch\").live(\"click\", function(e){\r\n            callModuleFunction(\"stream.state\", active_stream.stream_id);\r\n        });\r\n        $(\".rm-shuffle-button\").live(\"click\", function(e){\r\n            callModuleFunction(\"stream.shuffle\", active_stream.stream_id);\r\n        });\r\n    }\r\n});\r\n\r\n\r\n','','',1,'2014-09-29 12:32:21',''),('rm.stream.tracklist','<div class=\"rm_tracks_wrap\">\n    <div class=\"rm_tracks_table\">\n        <div class=\"rm_tracks_head\">\n            <div class=\"rm_tracks_cell\"></div>\n            <div class=\"rm_tracks_cell\">#</div>\n            <div class=\"rm_tracks_cell\">Title</div>\n            <div class=\"rm_tracks_cell\">Artist</div>\n            <div class=\"rm_tracks_cell\">Album</div>\n            <div class=\"rm_tracks_cell\">Genre</div>\n            <div class=\"rm_tracks_cell\">Duration</div>\n            <div class=\"rm_tracks_cell\">Track #</div>\n        </div>\n        <div class=\"rm_tracks_body rm_streamview\"></div>\n    </div>\n</div>\n','','','','',1,'2014-09-07 17:21:25',''),('rm.tracklist','<?php\r\nswitch(application::getRoute())\r\n{\r\n    case \"radiomanager\":\r\n        $subclass = \"rm_library\";\r\n        break;\r\n    case \"radiomanager/unused\":\r\n        $subclass = \"rm_unused\";\r\n        break;\r\n    case \"radiomanager/stream\":\r\n        $subclass = \"rm_streamview\";\r\n        break;\r\n    default:\r\n        $subclass = \"\";\r\n}\r\n?><div class=\"rm_tracks_wrap\">\r\n    <div class=\"rm_tracks_table\">\r\n        <div class=\"rm_tracks_head\">\r\n            <div class=\"rm_tracks_cell\"></div>\r\n            <div class=\"rm_tracks_cell\">#</div>\r\n            <div class=\"rm_tracks_cell\">Title</div>\r\n            <div class=\"rm_tracks_cell\">Artist</div>\r\n            <div class=\"rm_tracks_cell\">Album</div>\r\n            <div class=\"rm_tracks_cell\">Genre</div>\r\n            <div class=\"rm_tracks_cell\">Duration</div>\r\n            <div class=\"rm_tracks_cell\">Track #</div>\r\n        </div>\r\n        <div class=\"rm_tracks_body <?= $subclass ?>\"></div>\r\n    </div>\r\n</div>\r\n','.rm_tracks_wrap {\r\n    width: 100%;\r\n    padding: 8px;\r\n    box-sizing: border-box;\r\n    position: absolute;\r\n    top: 48px;\r\n    bottom: 0px;\r\n    overflow-y: scroll;\r\n    overflow-x: visible;\r\n}\r\n\r\n.rm_tracks_table {\r\n    display: table;\r\n    border-collapse: separate;\r\n    width: 100%;\r\n    table-layout: fixed;\r\n    position: relative;\r\n}\r\n\r\n.rm_tracks_head, .rm_tracks_item {\r\n    display: table-row;\r\n}\r\n\r\n.rm_tracks_cell {\r\n    display: table-cell;\r\n    padding: 0px 4px;\r\n    overflow: hidden;\r\n    text-overflow: ellipsis;\r\n    white-space: nowrap;\r\n}\r\n\r\n.rm_tracks_body {\r\n    display: table-row-group;\r\n}\r\n\r\n.rm_tracks_body:empty:after {\r\n    content: \"No tracks\";\r\n    padding: 32px 4px;\r\n    border: 1px solid #eee;\r\n    border-top: none;\r\n    width: 100%;\r\n    display: block;\r\n    position: absolute;\r\n    box-sizing: border-box;\r\n    text-align: center;\r\n}\r\n\r\n.rm_tracks_body.no_results:after {\r\n    content: \"No search results\";\r\n    padding: 32px 4px;\r\n    border: 1px solid #eee;\r\n    border-top: none;\r\n    width: 100%;\r\n    display: block;\r\n    position: absolute;\r\n    box-sizing: border-box;\r\n    text-align: center;\r\n}\r\n\r\n.rm_tracks_item {\r\n    background-color: #fafafa;\r\n    position: relative;\r\n    transition: color linear 250ms;\r\n}\r\n\r\n.rm_tracks_item.i, .rm_tracks_item.filtered {\r\n    display: none;\r\n}\r\n\r\n.rm_tracks_item[low-state=\"0\"] {\r\n    color: rgba(0,0,0,0.4);\r\n    cursor: default;\r\n}\r\n\r\n.rm_tracks_item[low-state=\"1\"] {\r\n    cursor: pointer;\r\n}\r\n\r\n.rm_tracks_item[low-state=\"-1\"] {\r\n    color: #f66;\r\n    cursor: pointer;\r\n}\r\n\r\n.rm_tracks_cell:nth-child(1) { width: 4px; padding: 0; }\r\n.rm_tracks_cell:nth-child(2) { text-align: right; width: 38px; }\r\n.rm_tracks_cell:nth-child(3) { text-align: left; }\r\n.rm_tracks_cell:nth-child(4) { text-align: left; }\r\n.rm_tracks_cell:nth-child(5) { text-align: left; }\r\n.rm_tracks_cell:nth-child(6) { text-align: left; width: 128px; }\r\n.rm_tracks_cell:nth-child(7) { text-align: right; width: 68px; }\r\n.rm_tracks_cell:nth-child(8) { text-align: right; width: 64px; }\r\n\r\n\r\n.rm_tracks_head {\r\n\r\n}\r\n\r\n.rm_tracks_head > .rm_tracks_cell { \r\n    background-color: #efefef; \r\n    padding-top: 8px; \r\n    padding-bottom: 8px; \r\n    border-top: 1px solid #eeeeee;\r\n    border-bottom: 1px solid #d9d9d9;\r\n}\r\n\r\n.rm_tracks_head > .rm_tracks_cell:first-child { \r\n    border-radius: 4px 0 0 0; \r\n    border-left: 1px solid #eeeeee;\r\n}\r\n.rm_tracks_head > .rm_tracks_cell:last-child { \r\n    border-radius: 0 4px 0 0; \r\n    border-right: 1px solid #eeeeee;\r\n}\r\n\r\n.rm_tracks_item > .rm_tracks_cell {\r\n    border-top: 1px solid rgba(255, 255, 255, 0.5);\r\n    border-bottom: 1px solid rgba(127, 127, 127, 0.1);\r\n    vertical-align: middle;\r\n    line-height: 24px;\r\n}\r\n\r\n.rm_tracks_item:not(.nowplaying) > .rm_tracks_cell:first-child {\r\n    border-left: 1px solid #eeeeee;\r\n}\r\n\r\n.rm_tracks_item:not(.nowplaying) > .rm_tracks_cell:last-child {\r\n    border-right: 1px solid #eeeeee;\r\n}\r\n\r\n.rm_tracks_item.odd  {\r\n    background-color: #f1f4f7;\r\n}\r\n\r\n\r\n\r\n.rm_tracks_item.nowplaying .rm_tracks_cell {\r\n    background-color: #25749c;\r\n    color: #eee;\r\n    border-top: 1px solid #2b82af;\r\n    border-bottom: 1px solid #1f6081;\r\n}\r\n\r\n.rm_tracks_item.nowplaying .rm_tracks_cell:nth-child(1) { color: rgba(255,255,255,1); }\r\n\r\n\r\n.rm_tracks_item.selected {\r\n    background-color: #c2c7d1;\r\n}\r\n\r\n.rm_tracks_item.selected > .rm_tracks_cell {\r\n    border-top: 1px solid #c2c7d1;\r\n    border-bottom: 1px solid #c2c7d1;\r\n}\r\n\r\n\r\n.rm_tracks_state {\r\n    position: absolute;\r\n    left: 0px;\r\n}\r\n\r\n.rm_tracks_item[data-color=\"1\"] > .rm_tracks_cell:first-child {\r\n    background-color: green;\r\n    border: none;\r\n}\r\n\r\n.rm_tracks_item[data-color=\"2\"] > .rm_tracks_cell:first-child {\r\n    background-color: red;\r\n    border: none;\r\n}\r\n\r\n.rm_tracks_item[data-color=\"3\"] > .rm_tracks_cell:first-child {\r\n    background-color: yellow;\r\n    border: none;\r\n}\r\n\r\n.rm_tracks_item[data-color=\"4\"] > .rm_tracks_cell:first-child {\r\n    background-color: blue;\r\n    border: none;\r\n}\r\n\r\n\r\n.rm_tracks_item.nowplaying {\r\n\r\n}\r\n\r\n\r\n.rm_track_drag {\r\n    padding: 8px;\r\n    border-radius: 3px;\r\n    background: #ffffff;\r\n    border: 1px solid #eeeeee;\r\n    z-index: 20;\r\n}\r\n\r\n.rm-track-preview {\r\n    float: left;\r\n    border-radius: 50%;\r\n    background-color: #ddd;\r\n    color: #fff;\r\n    width: 13px;\r\n    height: 13px;\r\n    line-height: 13px;\r\n    font-size: 6pt;\r\n    text-align: center;\r\n    cursor: pointer;\r\n    margin-top: 6px;\r\n    margin-right: 6px;\r\n    box-sizing: border-box;\r\n    padding-left: 1px;\r\n}\r\n\r\n.rm-track-preview:hover {\r\n    background-color: #aaa;\r\n}\r\n\r\n.rm-track-title-span {\r\n    display: inline-block;\r\n    white-space: nowrap;\r\n    overflow: hidden;\r\n}\r\n\r\n.rm_loader_icon {\r\n    width: 16px;\r\n    height: 16px;\r\n    vertical-align: middle;\r\n    opacity: 0.7;\r\n    display: none;\r\n}\r\n','var tracklist = {\r\n    /* Tracklist common methods */\r\n    trackDelete: function(track_id) {\r\n        tracklist.getById(track_id).remove();\r\n        tracklist.renumberTracks();\r\n        tracklist.updateSelection();\r\n        $(document).scroll();\r\n        return tracklist;\r\n    },\r\n    trackChangeState: function(track_id, value) {\r\n        tracklist.getById(track_id).attr(\'low-state\', value);\r\n        return tracklist;\r\n    },\r\n    trackAdd: function(track_id, data) {\r\n        data = data || false;\r\n\r\n        if (tracklist.trackExists(track_id) === true) return false;\r\n\r\n        if(data === false) {\r\n            $.post(\"/radiomanager/api/getTrackItem\", {\r\n                track_id: track_id, \r\n                type: \"json\"\r\n            }, function(json) {\r\n                try {\r\n                    tracklist.trackAdd(track_id, json);\r\n                } catch(e) {}\r\n            });\r\n        } else {\r\n            $(\"#streamTrackTemplate\").tmpl(data).prependTo(\".rm_tracks_body:not(.rm_streamview)\");\r\n            tracklist.renumberTracks();\r\n        }\r\n        return tracklist;\r\n    },\r\n    trackUpdate: function(track_id, data) {\r\n        data = data || false;\r\n        if(tracklist.trackExists(track_id) === false) return false;\r\n        if(data === false) {\r\n            $.post(\"/radiomanager/api/getTrackItem\", {\r\n                track_id: track_id, \r\n                type: \"json\" \r\n            }, function(json) {\r\n                try {\r\n                    tracklist.trackUpdate(track_id, json);\r\n                } catch(e) {}\r\n            });\r\n        } else {\r\n            var elem = tracklist.getById(track_id);\r\n            var attributes = elem.prop(\"attributes\");\r\n            var target = $(\"#streamTrackTemplate\").tmpl(data);\r\n\r\n            $.each(attributes, function () {\r\n                target.attr(this.name, this.value);\r\n            });\r\n\r\n            elem.replaceWith(target);\r\n            tracklist.updateSelection().renumberTracks();\r\n        }\r\n        return tracklist;\r\n    },\r\n    \r\n    /* Tracklist in stream view methods */\r\n    removeFromStream: function(unique_id) {\r\n        tracklist.getByUnique(unique_id).remove();\r\n        tracklist.updateSelection().renumberTracks();\r\n        $(document).scroll();\r\n        return tracklist;\r\n    },\r\n    setNowPlaying: function(unique_id, stream_id) {\r\n        if(typeof active_stream === \"undefined\") { \r\n            return; \r\n        }\r\n        stream_id = stream_id || active_stream.stream_id;\r\n        if(parseInt(stream_id) !== parseInt(active_stream.stream_id)) { \r\n            return; \r\n        }\r\n        tracklist.nothingPlaying().getByUnique(unique_id).addClass(\"nowplaying\");\r\n        return tracklist;\r\n    },\r\n    nothingPlaying: function() {\r\n        $(\".rm_streamview .rm_tracks_item.nowplaying\").removeClass(\"nowplaying\");\r\n        return tracklist;\r\n    },\r\n    setNewIndex: function(unique_id, index) {\r\n        var element = tracklist.getByUnique(unique_id);\r\n        if(element.index() !== index - 1) {\r\n            var badge = element.appendTo(\"<div>\");\r\n            var e = $(\".rm_streamview .rm_tracks_item\").eq(index - 1);\r\n            badge.insertBefore(e);\r\n            tracklist.renumberTracks();\r\n        }\r\n        return tracklist;\r\n    },\r\n    \r\n    /* Special helper methods */\r\n    trackExists: function(track_id) {\r\n        return tracklist.getById(track_id).length > 0;\r\n    },\r\n    getById: function(track_id) {\r\n        return $(\".rm_tracks_item[track-id=\'\" + track_id + \"\']\");\r\n    },\r\n    getByUnique: function(unique_id) {\r\n        return $(\".rm_streamview .rm_tracks_item[data-unique=\'\" + unique_id + \"\']\");\r\n    },\r\n    \r\n    /* Data organization methods */\r\n    renumberTracks: function() {\r\n        $(\".rm_tracks_item\").each(function(i) {\r\n            $(this).find(\"div\").eq(1).html(i+1);\r\n            if (i % 2 === 0) {\r\n                $(this).removeClass(\"odd\");\r\n            } else {\r\n                $(this).addClass(\"odd\");\r\n            }\r\n        });\r\n        return tracklist;\r\n    },\r\n    updateSelection: function() {\r\n        var selected = $(\".rm_tracks_item.selected\");\r\n\r\n        var selectionCount = selected.length;\r\n        var selectionTime = 0;\r\n    \r\n        selected.each(function() {\r\n            selectionTime += parseInt($(this).attr(\'track-duration\'));\r\n        });\r\n\r\n        $(\"#sel_tracks_count\").text(selectionCount);\r\n        $(\"#sel_tracks_time\").text(secondsToHms(selectionTime));\r\n    \r\n        if(selectionCount > 0) {\r\n            $(\".rm_status_wrap\").addClass(\"selected\");\r\n        } else {\r\n            $(\".rm_status_wrap\").removeClass(\"selected\");\r\n        }\r\n        return tracklist;\r\n    },\r\n    selectAll: function() {\r\n        $(\".rm_tracks_item[low-state=\'1\']\")\r\n            .addClass(\"selected\")\r\n            .removeClass(\"active\");\r\n        $(\".rm_tracks_item[low-state=\'1\']:last-child\")\r\n            .addClass(\"active\");\r\n        tracklist.updateSelection();\r\n        return tracklist;\r\n    },\r\n    invertSelection: function() {\r\n        $(\".rm_tracks_item[low-state=\'1\']\")\r\n            .toggleClass(\"selected\");\r\n        tracklist.updateSelection();\r\n        return tracklist;\r\n    },\r\n    noSelection: function() {\r\n        $(\".rm_tracks_item\")\r\n            .removeClass(\"selected\")\r\n            .removeClass(\"active\");\r\n        tracklist.updateSelection();\r\n        return tracklist;\r\n    },\r\n    clearAll: function() {\r\n        $(\".rm_tracks_item\").remove();\r\n    },\r\n    getSelected: function() {\r\n        return $(\".rm_tracks_item.selected\").map(function () { return $(this).attr(\"track-id\"); }).toArray().join(\",\");\r\n    }\r\n};\r\n\r\nvar trackworks = {\r\n    killSelection: function() {\r\n        myOwnQuestion(\"Are you sure want to delete selected tracks from account?\", function() {\r\n            var selected_ids = $(\".rm_tracks_item.selected\").map(function(){return $(this).attr(\"track-id\");}).toArray().join(\",\");\r\n            $.post(\"/radiomanager/removeTrack\", { track_id : selected_ids }, function (data) {\r\n                var json = filterAJAXResponce(data);\r\n                showPopup(lang.conv(json.code, \"track.delete\"));\r\n                if(json.code === \"SUCCESS\" && typeof json.data === \"object\")\r\n                {\r\n                    json.data.forEach(function(e) {\r\n                        if(e.result === \"SUCCESS\")\r\n                        {\r\n                            tracklist.trackDelete(e.value);\r\n                        }\r\n                    });\r\n                    try { updateRadioManagerInterface() } catch(e) {}\r\n                }\r\n            });\r\n        });\r\n        return trackworks;\r\n    },\r\n    removeSelectionFromStream: function() {\r\n        myOwnQuestion(\"Are you sure want to remove selected tracks from stream?\", function() {\r\n            var selected_ids = $(\".rm_tracks_item.selected\").map(function(){return $(this).attr(\"data-unique\");}).toArray();\r\n            var stream_id = active_stream.stream_id;\r\n            stopPlayer();\r\n            $.post(\"/api/v2/stream/removeTracks\", {\r\n                id      : stream_id,\r\n                tracks  : selected_ids.join(\",\")\r\n            }, function(data) {\r\n                if(data.status === 1)\r\n                {\r\n                    selected_ids.forEach(function(e) {\r\n                        tracklist.removeFromStream(e);\r\n                    });\r\n                }\r\n            });\r\n        });\r\n        return trackworks;\r\n    },\r\n    addSelectionToStream: function(stream_id, track_ids) {\r\n        track_ids = track_ids || false;\r\n        if(track_ids === false) {\r\n            var selected = $(\".rm_tracks_item.selected[low-state=\'1\']\");\r\n            if(selected.length === 0) { return; }\r\n            track_ids = selected.map(function(){return $(this).attr(\"track-id\");}).toArray().join(\",\");\r\n        }\r\n        $.post(\"/api/v2/stream/addTracks\", {\r\n            id      : stream_id,\r\n            tracks  : track_ids\r\n        }, function(data) {\r\n            if (data.status === 1) {\r\n                showPopup(\"Tracks added to stream successfully\");\r\n            }\r\n        });\r\n    },\r\n    tagEditor: function() {\r\n        var track_id = tracklist.getSelected();\r\n        if(track_id) {\r\n            showTagEditorBox(track_id);\r\n        }\r\n    }\r\n};\r\n\r\n\r\nfunction ajaxGetTrackUniversal(replace)\r\n{\r\n    $(\"body\").addClass(\"ajaxBusy\");\r\n    var lastTrack = $(\".rm_tracks_item\").length;\r\n    $.post(\"\", { \r\n        from      : replace || false ? 0 : lastTrack,\r\n        filter    : $(\"#filterBox\").val(),\r\n    }, function(json){\r\n        if(replace || false === true)\r\n        {\r\n            $(\"body\").addClass(\"partial\");\r\n            callModuleFunction(\"tracklist.clearAll\");\r\n        }\r\n        if(json.length < 50)\r\n        {\r\n            $(\"body\").removeClass(\"partial\");\r\n        }\r\n        $(\"#streamTrackTemplate\").tmpl(json).appendTo(\".rm_tracks_body\");\r\n        tracklist.renumberTracks();\r\n        callModuleFunction(\"nowplaying.update\", true);\r\n        $(\"body\").removeClass(\"ajaxBusy\");\r\n    });\r\n}\r\n\r\n/* Tracklist Initialization */\r\n$(document).on(\'ready\', function() {\r\n\r\n    $(\".rm_tracks_wrap\").on(\"scroll\", function(){\r\n        if($(\"body\").hasClass(\"partial\") === false) {\r\n            return;\r\n        }\r\n        if($(\"body\").hasClass(\"ajaxBusy\")) { \r\n            return; \r\n        }\r\n        \r\n        var bottom = $(\".rm_tracks_table\").height() - ($(\".rm_tracks_wrap\").scrollTop() + $(\".rm_tracks_wrap\").height());\r\n        \r\n        if(bottom >= 400) {\r\n            return;\r\n        }\r\n        \r\n        ajaxGetTrackUniversal();\r\n    });\r\n    \r\n    tracklist.updateSelection();\r\n\r\n    // Load first pack of tracks from encoded array    \r\n    $(\".rm_tracks_data\").livequery(function() {\r\n        var data = JSON.parse(atob($(this).attr(\'content\')));\r\n        $(this).remove();\r\n        $(\"#streamTrackTemplate\").tmpl(data).appendTo(\".rm_tracks_body\");\r\n        tracklist.renumberTracks();\r\n    });\r\n\r\n});\r\n\r\n/* Tracklist Model Methods */\r\n(function() {\r\n    // Stream View sort implementation\r\n    $(\".rm_tracks_body.rm_streamview\").livequery(function(event){\r\n        $(this).sortable({\r\n            items: \".rm_tracks_item:visible\",\r\n            stop: function( event, ui ) {\r\n                var this_element = ui.item.attr(\"data-unique\");\r\n                var this_index = $(ui.item).index();\r\n                var stream_id = active_stream.stream_id;\r\n                stream.sort(stream_id, this_element, this_index);\r\n                tracklist.renumberTracks();\r\n            }\r\n        });\r\n    }); \r\n    \r\n    // Library view drag and drop implementation\r\n    $(\".rm_tracks_body:not(.rm_streamview) .rm_tracks_item\").livequery(function(event) {\r\n        $(this).draggable({\r\n            cursor: \"move\",\r\n            appendTo: \'body\',\r\n            cursorAt: {top: 8, left: 8},\r\n            containment: \'window\',\r\n            helper: function() {\r\n                if($(this).hasClass(\"selected\") === false)\r\n                {\r\n                    $(\".rm_tracks_item\").removeClass(\"selected active\");\r\n                    $(this).addClass(\"selected active\");\r\n                }\r\n                var selected = $(\".rm_tracks_item.selected\");\r\n                var selected_ids = selected.map(function(){ return $(this).attr(\"track-id\"); }).toArray();\r\n                var caption = (selected.length > 1) ? (selected.length + \" track(s)\") : (\"<b>\" + selected.find(\"div\").eq(2).text() + \"</b> - <b>\" + selected.find(\"div\").eq(1).text() + \"</b>\");\r\n                return $(\"<div>\")\r\n                        .attr(\"track-id\", selected_ids.join(\",\"))\r\n                        .addClass(\"rm_track_drag\")\r\n                        .html(\"Selected \" + caption);\r\n            }\r\n        });\r\n    });\r\n \r\n    // Click outside of list unselects all\r\n    $(\"html\").bind(\'click\', function(event) {\r\n        if ($(event.target).parents().andSelf().filter(\".rm_tracks_table, .rm_mouse_menu_wrap, .rm_popup_form_background, .rm_mbox_shader\").length === 0) {\r\n            tracklist.noSelection();\r\n        }\r\n    })\r\n    \r\n    // Hotkeys for tracklist\r\n    $(document).bind(\'keydown\', function(event) {\r\n        if (event.ctrlKey && event.keyCode === 65) {\r\n            tracklist.selectAll();\r\n        } else if (event.ctrlKey && event.keyCode === 73) {\r\n            tracklist.invertSelection();\r\n        } else if (event.ctrlKey && event.keyCode === 83) {\r\n            var stream_id = active_stream.stream_id || false;\r\n            if(stream_id !== false) {\r\n                stream.shuffle(stream_id);\r\n            }\r\n        } else {\r\n            return;\r\n        }\r\n        event.preventDefault();\r\n    });\r\n\r\n    // Tracklist items selectors\r\n    $(\".rm_tracks_item\").livequery(function() {\r\n        $(this)\r\n                // Context menu implementation\r\n                .live(\'contextmenu\', function(event) {\r\n                    // Context menu for selected tracks from library\r\n                    if ($(\":not(.rm_streamview) .rm_tracks_item.selected\").length > 0) {\r\n                        showTrackInTracklistMenu(event);\r\n                    } \r\n\r\n                    // Context menu for selected tracks from stream\r\n                    if ($(\".rm_streamview .rm_tracks_item.selected\").length > 0) {\r\n                        showTrackInStreamMenu(event);\r\n                    }\r\n                    \r\n                    event.preventDefault();\r\n                    return false;\r\n                })\r\n                // Selection black magic\r\n                .live(\'mouseup\', function(event) {\r\n                    if (event.button === 2 && $(this).hasClass(\"selected\")) {\r\n                        return;\r\n                    }\r\n\r\n                    var prevClicked = $(\".rm_tracks_item.active\").index();\r\n                    var ctrlKey = event[\'ctrlKey\'];\r\n                    var shiftKey = event[\'shiftKey\'];\r\n\r\n                    if (ctrlKey === false) {\r\n                        $(\".rm_tracks_item\").removeClass(\"selected\");\r\n                    }\r\n\r\n                    $(\".rm_tracks_item\").removeClass(\"active\");\r\n\r\n                    $(this).addClass(\'active\');\r\n\r\n                    if (shiftKey === false || prevClicked === -1) {\r\n                        $(this).toggleClass(\'selected\');\r\n                    } else {\r\n                        var newClicked = $(this).index();\r\n\r\n                        if (newClicked > prevClicked) {\r\n                            $(\".rm_tracks_item\").slice(prevClicked, newClicked + 1).addClass(\'selected\');\r\n                        } else {\r\n                            $(\".rm_tracks_item\").slice(newClicked, prevClicked + 1).addClass(\'selected\');\r\n                        }\r\n                    }\r\n                    tracklist.updateSelection();\r\n                });\r\n\r\n    });\r\n\r\n})();\r\n\r\n','<script id=\"streamTrackTemplate\" type=\"text/x-jquery-tmpl\">\r\n    <div class=\"rm_tracks_item\" data-color=\"${color}\" low-state=\"${lores}\" track-id=\"${tid}\" data-unique=\"${unique_id}\" track-duration=\"${duration}\" track-title=\"${title}\" track-artist=\"${artist}\" track-album=\"${album}\" track-genre=\"${genre}\">\r\n        <div class=\"rm_tracks_cell\"></div>\r\n        <div class=\"rm_tracks_cell\"></div>\r\n        <div class=\"rm_tracks_cell\" title=\"${title}\"><div class=\"rm-track-preview\"><i class=\"icon-play2 no-margin\"></i></div>${title}</div>\r\n        <div class=\"rm_tracks_cell\" title=\"${artist}\">${artist}</div>\r\n        <div class=\"rm_tracks_cell\" title=\"${album}\">${album}</div>\r\n        <div class=\"rm_tracks_cell\" title=\"${genre}\">${genre}</div>\r\n        <div class=\"rm_tracks_cell\" auto-time=\"${duration}\"></div>\r\n        <div class=\"rm_tracks_cell\">${track_number}</div>\r\n        <input type=\"hidden\" value=\"/radiomanager/previewAudio?track_id=${tid}\" />\r\n    </div>\r\n</script>\r\n','',1,'2014-09-27 23:40:32',''),('rm.unused.tracklist','<div class=\"rm_tracks_wrap\">\r\n    <div class=\"rm_tracks_table\">\r\n        <div class=\"rm_tracks_head\">\r\n            <div class=\"rm_tracks_cell\"></div>\r\n            <div class=\"rm_tracks_cell\">#</div>\r\n            <div class=\"rm_tracks_cell\">Title</div>\r\n            <div class=\"rm_tracks_cell\">Artist</div>\r\n            <div class=\"rm_tracks_cell\">Album</div>\r\n            <div class=\"rm_tracks_cell\">Genre</div>\r\n            <div class=\"rm_tracks_cell\">Duration</div>\r\n            <div class=\"rm_tracks_cell\">Track #</div>\r\n        </div>\r\n        <div class=\"rm_tracks_body rm_library\"></div>\r\n    </div>\r\n</div>\r\n','','','','',1,'2014-09-07 17:24:47',''),('rm.upload','','div.rm_upload_frame {\r\n    margin-top: 16px;\r\n}\r\n\r\n.rm_upload_progress_wrap {\r\n    text-align: left;\r\n    position: relative;\r\n    display: none;\r\n\r\n    margin-top: 8px;\r\n    margin-left: 8px;\r\n    margin-right: 8px;\r\n    box-sizing: border-box;\r\n}\r\n\r\n.rm_upload_progress_wrap.visible {\r\n    display: block;\r\n}\r\n\r\n#progress_background {\r\n    margin-top: 8px;\r\n    height: 8px;\r\n    border: 1px solid #5594b4;\r\n    position: relative;\r\n}\r\n\r\n#progress_background #progress_handle {\r\n    height: 100%;\r\n    width: 0;\r\n    background: #93ceec;\r\n}\r\n\r\n.rm_upload_progress_wrap  #title {\r\n    text-align: center;\r\n    white-space: nowrap;\r\n    font-size: 1.1em;\r\n    padding-bottom: 16px;\r\n}\r\n\r\n#curr_id, #total_id {\r\n	\r\n}\r\n\r\n#curr_name {\r\n    color: #2578a2;\r\n}\r\n\r\n\r\n.rm_abort_icon {\r\n    float: right;\r\n    opacity: 0.5;\r\n    cursor: pointer;\r\n    background: url(/images/iconCancel.png) no-repeat;\r\n    width: 10px;\r\n    height: 10px;\r\n    margin-top: 6px;\r\n}\r\n\r\n.rm_upload_prompt.hidden {\r\n    display: none;\r\n}','(function() {\n    $(\"a.upload\").live(\'click\', function() {\n        createForm(\"#uploaderTemplate\", {}, 500, 220);\n        return false;\n    });\n})();\n\n\n(function(w) {\n    var jobQueue = [];\n    var procFlag = false;\n    var currentFile = 0;\n    var totalFiles = 0;\n    var uploadHandle = false;\n\n    var successfullyUploaded = 0;\n    var totalFilesSize = 0;\n    var summingFilesSize = 0;\n\n    $(\".rm_browse\").live(\'click\', function() { $(\".rm_input_files\").click(); });\n    $(\".rm_close\").live(\'click\', function()\n    {\n        uploadReset();\n        formDestroy();\n    });\n\n    $(\".rm_input_files\").livequery(function()\n    {\n        $(this).on(\'change\', function(event)\n        {\n            $.each(event.target.files, function(i, file)\n            {\n                if(file.size < 512 * 1024 * 1024)\n                {\n                    $(\"#total_id\").text(++totalFiles);\n                    jobQueue.push(file);\n                    totalFilesSize += file.size;\n                }\n                else\n                {\n                    showInfo(\"One or more files has unsupported file size. Maximum size is 512 MB!\");\n                }\n            });\n            if (procFlag === false)\n            {\n                uploadNextFile();\n            }\n        });\n    });\n\n    function uploadReset()\n    {\n        jobQueue = [];\n        if (uploadHandle !== false)\n        {\n            uploadHandle.abort();\n\n        }\n        procFlag = false;\n        totalFiles = 0;\n        currentFile = 0;\n        successfullyUploaded = 0;\n        totalFilesSize = 0;\n        summingFilesSize = 0;\n    }\n\n    function uploadNextFile()\n    {\n        if (jobQueue.length === 0)\n        {\n            totalFiles = 0;\n            currentFile = 0;\n            procFlag = false;\n            uploadReset();\n            formDestroy();\n            return;\n        }\n\n        currentFile++;\n        procFlag = true;\n\n        $(\".rm_upload_progress_wrap\").addClass(\"visible\");\n        $(\".rm_upload_prompt\").remove();\n\n        var file = jobQueue.shift();\n\n        $(\"#curr_name\").html(file.name);\n        $(\"#total_id\").html(totalFiles);\n        $(\"#curr_id\").html(currentFile);\n\n        var data = new FormData();\n        data.append(\'file\', file);\n        data.append(\'authtoken\', mor.user_token);\n\n        uploadHandle = $.ajax({\n            type: \"POST\",\n            xhr: function() \n            {\n                var myXhr = $.ajaxSettings.xhr();\n                if (myXhr.upload) \n                {\n                    myXhr.upload.addEventListener(\'progress\', progressHandlingFunction, false);\n                }\n                return myXhr;\n            },\n            url: \"/radiomanager/upload\",\n            data: data,\n            processData: false,\n            contentType: false,\n            cache: false,\n            success: function(data) {\n                uploadHandle = false;\n                try\n                {\n                    var json = JSON.parse(data);\n                    if (json.code === \"UPLOAD_SUCCESS\")\n                    {\n                        successfullyUploaded ++;\n                        summingFilesSize += file.size;\n                        callModuleFunction(\"tracklist.trackAdd\", json.data.tid, json.data);\n                        mor.tracks_count ++;\n                        try { updateRadioManagerInterface() } catch(e) {}\n                    }\n                    else if (json.code === \"UPLOAD_ERROR_NO_SPACE\")\n                    {\n                        totalFiles = 0;\n                        currentFile = 0;\n                        procFlag = false;\n                        uploadReset();\n                        formDestroy();\n                        myMessageBox(\"Not enought time left on your account!\");\n                    }\n                    else\n                    {\n                        myMessageBox(file.name + \": \" + json.code);\n                    }\n                }\n                catch (e)\n                {\n      \n                }\n                uploadNextFile();\n            },\n            error: function() {\n                uploadHandle = false;\n            }\n        });\n\n    }\n\n    function progressHandlingFunction(e) {\n        if (e.lengthComputable && totalFilesSize > 0) {\n            $(\"#progress_handle\").width((100 / totalFilesSize * (summingFilesSize + e.loaded)) + \"%\");\n        }\n    }\n\n})(window);','<script id=\"uploaderTemplate\" type=\"text/x-jquery-tmpl\">\r\n    <div class=\"rm_mbox_shader\">\r\n        <div class=\"rm_mbox_wrap dynTop\">\r\n            <div class=\"rm_window_header\">Upload audio file(s)<div class=\"rm_window_close_wrap\"><img src=\"/images/closeButton.gif\" /></div></div>\r\n            <div class=\"rm_window_body\">\r\n                <div style=\"text-align: center; padding-top: 16px; padding-bottom: 10px;\">\r\n                    <div class=\"rm_upload_prompt\">\r\n                        You have <span id=\"info_hm\">0 hours and 0 minutes</span> left for upload.<br>\r\n                        Click <b>browse</b> to select files you wish to upload.\r\n                        <div class=\"rm_upload_frame\">\r\n                            <input type=\"file\" class=\"rm_input_files\" style=\"display: none;\" multiple=\"multiple\" accept=\"audio/*\" />\r\n                            <input type=\"button\" class=\"rm_ui_def_button rm_browse\" value=\"Browse...\" />\r\n                        </div>\r\n                    </div>\r\n                    <div class=\"rm_upload_progress_wrap\">\r\n                        <div id=\"title\">Uploading file <span id=\"curr_id\">1</span> of <span id=\"total_id\">1</span>...</div>\r\n                        <div id=\"progress_background\">\r\n                            <div id=\"progress_handle\">\r\n                            </div>\r\n                        </div>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n            <div class=\"rm_window_bottom\">\r\n                <input type=\"button\" class=\"rm_ui_def_button rm_close\" value=\"Cancel\" />\r\n            </div>\r\n        </div>\r\n    </div>\r\n</script>\r\n','',1,'2014-09-17 19:40:36',''),('rm.windows','','div.rm_popup_form_background {\r\n    position: fixed;\r\n    left: 0;\r\n    top: 0;\r\n    padding: 0;\r\n    margin: 0;\r\n    width: 100%;\r\n    height: 100%;\r\n    background: rgba(0,0,0,0.5);\r\n}\r\n\r\ndiv.rm_popup_form {\r\n    position: absolute;\r\n    z-index: 100;\r\n    box-shadow: 0px 0px 32px rgba(0,0,0,0.7);\r\n    background: #fff;\r\n    line-height: 1.5em;\r\n    left: 50%;\r\n    top: 50%;\r\n}\r\n\r\n.rm_window_header {\r\n    width: 100%;\r\n    padding: 6px 10px;\r\n    box-sizing: border-box;\r\n    background: #5594b4;\r\n    font-weight: bold;\r\n    color: white;\r\n}\r\n\r\n.rm_window_body {\r\n    padding: 8px 10px;\r\n    box-sizing: border-box;\r\n    margin-bottom: 48px;\r\n}\r\n\r\n.rm_window_bottom {\r\n    position: absolute;\r\n    bottom: 0;\r\n    width: 100%;\r\n    padding: 8px 10px;\r\n    box-sizing: border-box;\r\n    height: 48px;\r\n    background: #eee;\r\n    text-align: right;\r\n}\r\n\r\n.rm_window_close_wrap {\r\n    position: absolute;\r\n    top: 8px;\r\n    right: 10px;\r\n    cursor: pointer;\r\n    opacity: 0.5;\r\n    transition: opacity linear 250ms;\r\n}\r\n\r\n.rm_window_close_wrap:hover {\r\n    opacity: 1;\r\n}\r\n\r\n.rm_mbox_shader {\r\n    position: fixed;\r\n    left: 0;\r\n    top: 0;\r\n    padding: 0;\r\n    margin: 0;\r\n    width: 100%;\r\n    height: 100%;\r\n    background: rgba(0,0,0,0.5);\r\n    z-index: 100;\r\n}\r\n\r\n.rm_mbox_wrap {\r\n    box-shadow: 0px 0px 32px rgba(0,0,0,0.7);\r\n    background: #fff;\r\n    line-height: 1.5em;\r\n    min-width: 320px;\r\n    min-height: 32px;\r\n    position: relative;\r\n}\r\n\r\n.rm_mbox_header {\r\n    width: 100%;\r\n    padding: 6px 10px;\r\n    box-sizing: border-box;\r\n    background: #5594b4;\r\n    font-weight: bold;\r\n    color: white;    \r\n}\r\n\r\n.rm_mbox_close_btn {\r\n    position: absolute;\r\n    top: 8px;\r\n    right: 10px;\r\n    cursor: pointer;\r\n    opacity: 0.5;\r\n    transition: opacity linear 250ms;   \r\n}\r\n\r\n.rm_mbox_close_btn:hover {\r\n    opacity: 1;\r\n}\r\n\r\n.rm_mbox_footer {\r\n    position: absolute;\r\n    bottom: 0;\r\n    width: 100%;\r\n    padding: 8px 10px;\r\n    box-sizing: border-box;\r\n    height: 48px;\r\n    background: #eee;\r\n    text-align: right; \r\n}\r\n\r\n.rm_mbox_text {\r\n    position: relative;\r\n    padding: 32px 8px;\r\n    text-align: center;\r\n    margin-bottom: 48px;\r\n}\r\n\r\n.rm_mbox_wrap .rm_ui_def_button {\r\n    margin-left: 4px;\r\n}\r\n','\r\nfunction myMessageBox(message, callback) {\r\n    var item = $(\"#messageBoxTemplate\").tmpl({message:message});\r\n    callback = callback || function() {};\r\n    item.find(\".rm_mbox_btn_close\").bind(\"click\", function () { item.remove(); callback(); });\r\n    item.find(\".rm_mbox_close_btn\").bind(\"click\", function () { item.remove(); callback(); });\r\n    item.appendTo(\"body\");\r\n}\r\n\r\nfunction myQuestionBox(message, callback) {\r\n    var item = $(\"#questionBoxTemplate\").tmpl({message:message});\r\n    callback = callback || function() {};\r\n    item.find(\".rm_mbox_btn_close\").bind(\"click\", function () { item.remove(); });\r\n    item.find(\".rm_mbox_close_btn\").bind(\"click\", function () { item.remove(); });\r\n    item.find(\".rm_mbox_btn_action\").bind(\"click\", function () { callback(); item.remove();  });\r\n    item.appendTo(\"body\");\r\n}\r\n\r\nfunction createForm(pattern, params, w, h)\r\n{\r\n    $(\".rm_popup_form_background\").remove();\r\n\r\n        var wrap = $(\"<div>\")\r\n                .addClass(\"rm_popup_form_background\")\r\n                .bind(\'mousewheel\', function() {\r\n                    return false;\r\n                });\r\n\r\n        $(\"<div>\")\r\n                .addClass(\"rm_popup_form\")\r\n                .html($(pattern).tmpl())\r\n                .appendTo(wrap);\r\n\r\n        wrap\r\n                .appendTo(\"body\")\r\n                .find(\".rm_window_close_wrap\")\r\n                .unbind(\'click\')\r\n                .bind(\'click\', function()\r\n                {\r\n                    formDestroy();\r\n                });\r\n}\r\n\r\n\r\nfunction formDestroy()\r\n{\r\n    $(\".rm_popup_form_background\").remove();\r\n}\r\n','','',1,'2014-09-04 10:18:34',''),('test','<?php\necho layout::parseHashTags(\"#data #info #ok #may be\");\n?>','','','','',1,'2014-09-08 20:41:02','tast'),('tools.crop.image','<?php\n\n$input_file 	= application::get(\'f\', NULL, REQ_STRING);\n$input_size 	= application::get(\'s\', 64, REQ_INT);\n$crop_enable 	= application::get(\'c\', false, REQ_BOOL);\n\n$cache_enable 	= 1;\n\nif(!file_exists($input_file))\n{\n    header(\'HTTP/1.0 404 Not Found\');\n    exit(\'<h1>File not found!</h1>\');\n}\n\n$cache = config::getSetting(\"content\", \"content_folder\") . \"/../cache/\" . md5(serialize(application::getAll(array(\"rnd\")))) . \".jpg\";\n$file_mtime = filemtime($input_file);\n\n\nif($cache_enable) \n{\n	/* Проверим не кэшировано ли изображение на стороне клиента */\n	if (isset($_SERVER[\'HTTP_IF_MODIFIED_SINCE\']) && strtotime($_SERVER[\'HTTP_IF_MODIFIED_SINCE\']) >= $file_mtime) \n	{\n		header($_SERVER[\"SERVER_PROTOCOL\"].\' 304 Not Modified\');\n		header(\"Last-Modified: \" . gmdate(\"D, d M Y H:i:s\", $file_mtime) . \" GMT\");\n		header(\'Cache-Control: max-age=0\');\n		die();\n	} \n	else \n	{\n		header(\"Last-Modified: \" . gmdate(\"D, d M Y H:i:s\", $file_mtime) . \" GMT\");\n		header(\'Cache-Control: max-age=0\');\n	}\n\n	/* Проверим не кэшировано ли изображение на стороне сервера */\n//	if(file_exists($cache) && (filemtime($cache) == $file_mtime) ) \n//	{\n//		header($_SERVER[\"SERVER_PROTOCOL\"].\' 301 Moved Permanently\');\n//		header(\"Location: /\" . $cache);\n//		exit();\n//	}\n}\n\n\n/* Создаем класс обработки изображений и \nполучаем детальную информацию для кэширования */\n$img = new acResizeImage($input_file);\n$pi = pathinfo($cache);\n\n/* Если папка для кэша не существует - создаём её */\nif(!file_exists($pi[\'dirname\'])) mkdir($pi[\'dirname\'], 0770, true);\n\nif($crop_enable)\n{\n	$img	->cropSquare();\n}\n\n$img->resize($input_size);\n\n\n        \n$new = $img->interlace()->save($pi[\'dirname\'] . \"/\", $pi[\'filename\'], \'jpg\', true, 100);\n\n\nif($new) \n{\n	if(file_exists($new))\n		touch($new, $file_mtime, null);\n\n	/* Перенаправляем браузер на созданное изображение */\n	//header($_SERVER[\"SERVER_PROTOCOL\"].\' 301 Moved Permanently\');\n	//header(\"Location: /\" . $new);\n	header(\"Content-Type: image/jpeg\");\n	echo file_get_contents($new);\n} \nelse \n{\n	/* Если что-то пошло не так - ругаемся */\n	header($_SERVER[\"SERVER_PROTOCOL\"].\' 500 Internal Server Error\');\n	echo \"Check directory permission.\";\n}\n\n','','','','',1,'2014-09-05 18:00:36','tools/imageCrop'),('us.allstreams','<?php\n\n/* \n    myownradio.biz module\n    section : user-size\n    action  : getting list of streams\n*/\n\n$start_from = application::get(\"start\", 0, REQ_INT);\n$items_limit = 10;\n$max_info_length = 512;\n?>\n<div class=\"page-stream-list -limit-width\">\n    <h1>ALL STREAMS</h1>\n    <ul class=\"page-stream-list-list _ajax-upload-subject\">\n<?php \n$streams = stream::streamList($start_from, $items_limit);  \n$tmpl = new Template(\"application/tmpl/us.streamitem.tmpl\");\n$cover_url = null;\nforeach($streams as $stream): \n    $stream_instance = new radioStreamInfo($stream);\n    $tmpl->reset();\n    $tmpl->addVariable(\"stream_id\", $stream_instance->getId());\n    $tmpl->addVariable(\"stream_url\", $stream_instance->getLink());\n    $tmpl->addVariable(\"stream_title\", $stream_instance->getName()->toUpperCase());\n    $tmpl->addVariable(\"stream_info\", (strlen($stream[\'info\']) < $max_info_length) ? ($stream[\'info\']) : (substr($stream[\'info\'], 0, $max_info_length) . \"...\"));\n    $tmpl->addVariable(\"stream_hashtags\", layout::parseHashTags($stream[\'genres\']), true);\n    echo $tmpl->makeDocument();\nendforeach; ?>\n    </ul>\n</div>','','','','',1,'2014-11-26 10:05:18',''),('us.catstreams','<?php\n\n$c_handle = application::singular(\"category\", application::get(\"permalink\", \"uncategorized\", REQ_STRING));\n$start_from = application::get(\"start\", 0, REQ_INT);\n$items_limit = 10;\n$max_info_length = 512;\n?>\n<div class=\"page-stream-list -limit-width\">\n    <h1>STREAMS IN \"<?= htmlspecialchars(strtoupper($c_handle->getName())) ?>\" CATEGORY</h1>\n    <ul class=\"page-stream-list-list _ajax-upload-subject\">\n<?php \n$streams = $c_handle->getStreams($start_from, $items_limit);  \n$tmpl = new template(\"application/tmpl/us.streamitem.tmpl\");\nforeach($streams as $stream):\n    $tmpl->reset();\n    $tmpl->addVariable(\"stream_id\", $stream[\'sid\']);\n    $tmpl->addVariable(\"stream_url\", stream::staticStreamLink($stream));\n    $tmpl->addVariable(\"stream_title\", strtoupper($stream[\'name\']));\n    $tmpl->addVariable(\"stream_info\", (strlen($stream[\'info\']) < $max_info_length) ? ($stream[\'info\']) : (substr($stream[\'info\'], 0, $max_info_length) . \"...\"));\n    $tmpl->addVariable(\"stream_hashtags\", layout::parseHashTags($stream[\'genres\']), true);\n    echo $tmpl->makeDocument();\nendforeach; ?>\n    </ul>\n</div>','','','','',1,'2014-09-23 10:20:12',''),('us.common','<meta charset=\"utf8\" />\n<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/reset.css\" />\n<link rel=\"stylesheet\" type=\"text/css\" href=\"/icomoon/style.css\" />\n<link rel=\"icon shortcut\" href=\"/images/mor-logo-dark.png\" />\n\n<!-- include:css -->\n<script src=\"/js/lang.en.js\"></script>\n<script src=\"/js/jquery-1.11.0.min.js.gz\"></script>\n<script src=\"/js/jquery-migrate-1.2.1.min.js.gz\"></script>\n<script src=\"/js/jquery-ui.min.js.gz\"></script>\n\n<script src=\"/js/jquery.livequery.js.gz\"></script>\n<script src=\"/js/functions.js\"></script>\n<script>\n    var timeDifference = Math.floor(<?= microtime(true) * 1000 ?> - new Date().getTime());\n</script>\n<!-- include:js -->\n\n<!-- module:us.common.submit -->\n','@import url(http://fonts.googleapis.com/css?family=Open+Sans:400,100,500&subset=latin,cyrillic);\n\n@font-face {\n    font-family: \"Myriad Pro\";\n    src: url(\"/fonts/MYRIAD.otf\") format(\"opentype\");\n}\n\n@font-face {\n    font-family: \"Myriad Pro Regular\";\n    src: url(\"/fonts/MYRIAD-STD.otf\") format(\"opentype\");\n}\n\n@font-face {\n    font-family: \"Myriad Pro Thin\";\n    src: url(\"/fonts/MYRIAD-THIN.otf\") format(\"opentype\");\n}\n\nbody {\n    -webkit-text-size-adjust: 100%;\n}\n\n/* Global Styles */\nb {\n    font-weight: bold;\n}\n\n.-limit-width {\n    width: 1000px;\n    left: 50%;\n    margin-left: -500px;\n    position: relative;\n}\n\n.-no-overflow {\n    overflow: hidden;\n}\n\n.-auto-overflow {\n    overflow: auto;\n}\n\n.-show-overflow {\n    overflow: auto;\n}\n\n.-float-left {\n    float: left;\n}\n\n.-float-right {\n    float: right;\n}\n\n.-center-vertical {\n    top: 50%;\n    -webkit-transform: translate(0, -50%);\n    -moz-transform: translate(0, -50%);\n    -ms-transform: translate(0, -50%);\n    transform: translate(0, -50%);\n}\n\n.-center-horizontal {\n    left: 50%;\n    -webkit-transform: translate(-50%, 0);\n    -moz-transform: translate(-50%, 0);\n    -ms-transform: translate(-50%, 0);\n    transform: translate(-50%, 0);\n}\n\n.-no-padding {\n    padding: 0 !important;\n}\n\na {\n    color: #ffffff;\n    text-decoration: none;\n    padding: 0 4px;\n    transition: color linear 150ms;\n}\n\na:hover {\n    color: #a4d35a;\n}\n\n/* Special Fixes */\n\ni[class*=\"icon-\"] {\n    padding-right: 6px;\n    font-size: 1em;\n    vertical-align: middle;\n}\n\n/* Main */\nhtml {\n    height: 100%;\n}\n\nbody {\n    background-color: #161a26;\n    color: #abbcc3;\n    font-family: \"Open Sans\";\n    font-size: 10pt;\n    position: relative;\n    min-height: 100%;\n}\n\n.page {\n    height: 100%;\n    padding-bottom: 350px;\n}\n\n.page a {\n    color: #0af;\n    margin: 0;\n    padding: 0;\n}\n\n\n.mor-logo {\n    background: url(\"/images/mor-logo.png\") no-repeat center center;\n    width: 32px;\n    height: 32px;\n    display: inline-block;\n    vertical-align: -5px;\n    padding-right: 8px;\n}\n\n\n.hashtag-element {\n    font-size: 13px;\n    padding: 4px 6px;\n    background-color: #3369b1;\n    border-radius: 5px;\n    opacity: 1;\n    transition: opacity linear 150ms;\n    vertical-align: middle;\n    display: inline-block;\n    text-shadow: 0 0 2 #000000;\n    margin-bottom: 4px;\n}\n\n.hashtag-element a {\n    color: #ffffff;\n}\n\n.hashtag-element:hover {\n    background-color: #5389d1;\n}\n\n.rm-page-busy {\n    text-align: center;\n    font-size: 14pt;\n    display: none;\n}\n\nbody.ajaxBusy .rm-page-busy {\n    display: block;\n}\n\n.us-section-label {\n    font-size: 30pt;\n    font-family: \"Myriad Pro Thin\";\n    margin: 16px 0;\n    color: #ffffff;\n}','(function(){\n    \n    function documentResize()\n    {\n        $(\".page-head-prefix\").width($(\".page-head-logo-cell\").offset().left);\n    }\n    \n    $(window).bind(\"resize\", function(){\n        documentResize();\n    });\n    \n    $(document).on(\"ready\", function(){\n        documentResize();\n        (function (token) {\n            $.ajaxSetup({\n                headers: { \'My-Own-Token\': token }\n            });\n        })($(\"body\").attr(\"token\"));\n    });\n    \n    $(\".-fix-center\").livequery(function(){\n        var w = $(this).width();\n        var h = $(this).height();\n        $(this).width(w + w%2).height(h+h%2);\n    });\n    \n\n    /* Ajax Page Load Section */\n    $(document).on(\"scroll\", function (e) {\n        if($(\"body\").hasClass(\"partial\"))\n        {\n            var bottom = $(document).height() - $(window).height() - $(window).scrollTop();\n            if(bottom < 400)\n            {\n                if($(\"body\").hasClass(\"ajaxBusy\") === false)\n                {\n                    ajaxGetContent();\n                }\n            }\n        }\n        \n    });\n    \n    $(\".page-body\").livequery(function(){\n        $(\"<div>\").addClass(\"rm-page-busy\").text(\"LOADING...\").appendTo($(this));\n    });\n\n    function ajaxGetContent()\n    {\n        var from = $(\"._ajax-upload-subject\").children().length;\n        var url = window.location.href;\n        $(\"body\").addClass(\"ajaxBusy\");\n        if(url.indexOf(\"?\") > -1)\n        {\n            url += \"&start=\" + from;\n        }\n        else\n        {\n            url += \"?start=\" + from;\n        }\n        $.get(url, function(data){\n            var elements = $(data).find(\"._ajax-upload-subject\").children();\n            if(elements.length === 0)\n            {\n                $(\"body\").removeClass(\"partial\");\n            }\n            else\n            {\n                elements.appendTo(\"._ajax-upload-subject\");\n            }\n            $(\"body\").removeClass(\"ajaxBusy\");\n        });\n    }\n    /* End of Ajax Page Load Section */\n    \n})();\n\n\n','<script type=\"text/x-jquery-tmpl\" id=\"loginWindow\">\n</script>','',1,'2014-10-11 12:08:26',''),('us.common.streams','','.page-stream-list h1 {\n    font-size: 30pt;\n    font-family: \"Myriad Pro Thin\";\n    margin: 16px 0;\n    color: #ffffff;\n}\n\n.page-stream-list h1 > a {\n    color: #ffffff;\n}\n\n.page-stream-list h1 > a > i {\n    font-size: 12pt;\n    vertical-align: inline;\n    line-height: 16px;\n}\n\n.page-stream-list-list {\n    overflow: hidden;\n}\n\n.page-stream-list-list > li {\n    margin-left: 0;\n    margin-top: 0;\n    margin-bottom: 16px;\n    margin-right: 0px;\n    width: 100%;\n    height: 150px;\n    overflow: hidden;\n    text-overflow: ellipsis;\n    position: relative;\n    opacity: 0;\n    transition: opacity linear 150ms;\n}\n\n.page-stream-list-list > li:not(:last-child)\n{\n    padding-bottom: 16px;\n    border-bottom: 1px solid #000000;\n}\n\n.page-stream-list-list > li a {\n    color: #ffffff;\n    text-shadow: 0 0 3px #000000;\n}\n\n.page-stream-list-item {\n    box-sizing: border-box;\n    transition: background-color linear 150ms;\n    height: 100%;\n    float: right;\n    width: 850px;\n    padding-left: 16px;\n    vertical-align: middle;\n}\n\n.page-stream-list-item-title {\n    text-align: left;\n    font-size: 22pt;\n    font-family: \"Myriad Pro Thin\";\n    margin-bottom: 5px;\n}\n\n.page-stream-list-item-genres {\n    margin-bottom: 8px;\n}\n\n.page-stream-list-item-genres > span {\n    color: #ffffff;\n}\n\n.page-stream-list-item-info {\n    line-height: 125%;\n}\n\n.page-stream-list-item-cover-wrapper {\n    width: 150px;\n    height: 150px;\n    float: left;\n    background-image: url(\"/images/static/nocover.png\");\n    background-position: center center;\n    background-repeat: no-repeat;\n    background-size: 100% 100%;\n    border-radius: 5px;\n    padding: 0px;\n    margin: 0px;\n    background-color: #272d43;\n    box-sizing: border-box;\n}\n\n.page-stream-list-item-cover {\n    width: 100%;\n    height: 100%;\n    border: none;\n    border-radius: 5px;\n    opacity: 0;\n    transition: opacity linear 150ms;\n}\n\n.page-stream-list-item-title-bitrates {\n    overflow: hidden;\n    float: right;\n    line-height: 20px;\n}\n\n.page-stream-list-item-title-bitrates > li {\n    display: inline-block;\n    font-size: 16px;\n    padding: 0px 4px;\n    background-color: #3369b1;\n    border-radius: 5px;\n    opacity: 1;\n    transition: opacity linear 150ms;\n    vertical-align: middle;\n}\n\n.page-stream-list-item-title-bitrates > li.orange {\n    background-color: #F59500;\n    color: #FFFFFF;\n}\n\n.page-stream-list-item-title-bitrates > li:hover {\n    opacity: 0.7;\n}\n\n.page-stream-list-item-title-bitrates > li a {\n    color: #ddd;\n    text-shadow: none;\n}\n\n.page-stream-no-results {\n    \n}','(function(){\n    $(\"img.page-stream-list-item-cover\").livequery(function(){\n\n        $(this).load(function(){\n            $(this).css(\"opacity\", 1);\n        }).each(function() {\n            if(this.complete) $(this).load();\n        });\n\n            \n    });\n    \n    $(\".page-stream-list-list > li\").livequery(function(){\n        $(this).css({opacity:1});\n    });\n})();','','',1,'2014-10-22 13:44:49',''),('us.common.submit','','.page-form-frame {\n    margin-top: 100px;\n    margin-bottom: 100px;\n    width: 500px;\n    background-color: #ffffff;\n    padding: 18px 24px;\n    left: 50%;\n    position: relative;\n    margin-left: -250px;\n    box-sizing: border-box;\n    color: #737c85;\n    border-radius: 5px;\n}\n\n.page-form-frame b {\n    font-weight: bold;\n    line-height: 150%;\n}\n\n.page-form-frame h1 {\n    font-size: 30px;\n    margin-bottom: 16px;\n    font-family: \"Myriad Pro\";\n}\n\n.page-user-form {\n    margin-top: 32px;\n    overflow: visible;\n}\n\n.page-user-form a {\n    color: #4af;\n}\n\n.page-user-form > div {\n    padding: 8px 0;\n}\n\n.page-user-form input[type=\"text\"],\n.page-user-form input[type=\"password\"] {\n    width: 100%;\n    box-sizing: border-box;\n    padding: 0 8px;\n    background-color: #ffffff;\n    border: 1px solid #dddddd;\n    outline: none;\n    border-radius: 3px;\n    height: 36px;\n    line-height: 36px;\n}\n\n.page-user-form input[type=\"text\"]:focus,\n.page-user-form input[type=\"password\"]:focus {\n    border-color: #4af;\n}\n\n.page-form-buttons {\n    padding: 0;\n    margin: 0;\n    overflow: hidden;\n}\n\n.page-user-form input[type=\"submit\"] {\n    float: right;\n    margin-top: 8px;\n    box-sizing: border-box;\n    display: block;\n    padding: 8px;\n    background-color: #4af;\n    border: none; /* 1px solid #dddddd; */\n    outline: none;\n    border-radius: 5px;\n    cursor: pointer;\n    width: 130px;\n    color: #fff;\n}\n\n.page-user-form  input[type=\"submit\"]:hover {\n    background-color: #79c2ff;\n}\n\n.page-user-form  input[type=\"checkbox\"] {\n    margin: 0px;\n    margin-right: 4px;\n}\n\n.page-form-table {\n    border-collapse: collapse;\n    width: 100%;\n}\n\n.page-form-input-status {\n    top: 0px;\n    position: absolute;\n    color: #000;\n    opacity: 0;\n    display: inline;\n    vertical-align: middle;\n    border: 1px solid #eeeeee;\n    padding: 4px 8px;\n    border-radius: 5px;\n    margin-top: 14px;\n    margin-left: 14px;\n    background-color: #ffffff;\n    white-space: nowrap;\n    box-shadow: 2px 2px 2px -2px #000;\n    transition: opacity linear 150ms;\n}\n\n.page-form-input-status:before {\n    content: \"\";\n    position: absolute;\n    top: 7px;\n    left: -5px;\n    border-style: solid;\n    border-width: 4px 4px 4px 0;\n    border-color: transparent #eeeeee;\n    display: block;\n    width: 0;\n    z-index: 0;\n}\n\n.page-form-input-status:after {\n    content: \"\";\n    position: absolute;\n    top: 7px;\n    left: -4px;\n    border-style: solid;\n    border-width: 4px 4px 4px 0;\n    border-color: transparent #FFFFFF;\n    display: block;\n    width: 0;\n    z-index: 1;\n}\n\n\n.page-form-table tr > td {\n    padding: 8px 0;\n    box-sizing: border-box;\n    vertical-align: middle;\n}\n\n.page-form-table tr > td:nth-child(1) {\n    width: 120px;\n}\n\n.page-form-table tr > td:nth-child(2) {\n    width: 360px;\n}\n\n.page-form-table tr > td:nth-child(3) {\n    \n    position: relative;\n}\n\n.page-form-description {\n    margin: 8px 0;\n    line-height: 150%;\n}\n\n.page-form-description > span {\n    color: #4af;\n}\n\n.page-form-additional {\n    margin: 0;\n}','/* Validator section */\n(function(){\n    \n    $(\".page-user-form\").find(\"input[type=\\\"text\\\"], input[type=\\\"password\\\"]\").live(\"change\", function(){\n        $(\".page-form-input-status\").css(\"opacity\", 0).text(\"\");\n    });\n\n    $.fn.extend({\n        jsubmit: function(url) {\n            \n            $(this).live(\"submit\", function(){\n            \n                $(\".page-form-input-status\").css(\"opacity\", 0).text(\"\");\n                \n                var args = $(this).serialize();\n                var href = $(this).attr(\"action\");\n                            \n                $.post(href, args + \"&submit=true\", function(json) {\n                    if (json.status === 1)\n                    {  \n                        // Goto success file\n                        if (typeof url !== \"undefined\") {\n                            window.location.href = url;\n                        }\n                    }\n                    else\n                    {\n                        // Displaying context\n                        var context = json.context;\n                        var message = json.message;\n                        $(\".page-form-input-status.\" + context).css(\"opacity\", 1).text(message);\n                    }\n                });\n            \n                return false;\n\n            });\n\n        }\n        \n    });\n})();','','',1,'2014-10-09 17:33:06',''),('us.error.confirm','<!DOCTYPE html>\n<html>\n    <head>\n        <title>Confirmation Error</title>\n        <!-- module:us.common -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body>\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-form-frame\">\n                <h1>ERROR</h1>\n                <div class=\"page-form-description\">\n                    Sorry, but this link is deactivated. It\'s belongs to email which already registered. May be it\'s has been processed previously.<br><br>\n                    If you already registered please <a href=\"/login\">Sign In</a>.\n                </div>\n            </div>\n        </div>\n        <!-- module:us.footer -->\n    </body>\n</html>','','','','',1,'2014-08-28 10:53:08',''),('us.error.regcode','<!DOCTYPE html>\n<html>\n    <head>\n        <title>Confirmation Error</title>\n        <!-- module:us.common -->\n        <!-- include:css -->\n        <!-- include:js -->\n    </head>\n    <body>\n        <!-- module:us.header -->\n        <div class=\"page\">\n            <div class=\"page-form-frame\">\n                <h1>ERROR</h1>\n                <div class=\"page-form-description\">\n                    Sorry, but code in this link is incorrect. Please follow the <a href=\"/signup\">registration</a> process again.<br><br>\n                    If you already registered please <a href=\"/login\">Sign In</a>.\n                </div>\n            </div>\n        </div>\n        <!-- module:us.footer -->\n    </body>\n</html>','','','','',1,'2014-08-28 11:12:50',''),('us.footer','<?php\n$date = date(\"Y\", time());\nif($date != 2014)\n{\n    $copy = \"2014 - {$date}\";\n}\nelse\n{\n    $copy = \"2014\";\n}\n?><div class=\"page-footer-background\">\n   <div class=\"page-footer-content -limit-width -shadow-both -show-overflow\">\n        <ul class=\"page-footer-content-list -show-overflow\">\n            <li>\n                <h1>MYOWNRADIO</h1>\n                <ul class=\"page-footer-content-subitem-list\">\n                    <li><a href=\"/\">Home</a></li>\n                    <li><a href=\"/browse\">Browse streams</a></li>\n                    <li><a href=\"/categories\">Categories</a></li>\n                    <li><a href=\"/radiomanager\">Radio Manager</a></li>\n                </ul>\n            </li>\n            <li>\n                <h1>COMMUNITY</h1>\n                <ul class=\"page-footer-content-subitem-list\">\n                    <!--<li><a href=\"#\">Facebook</a></li>\n                    <li><a href=\"#\">Google+</a></li>\n                    <li><a href=\"#\">Twitter</a></li>\n                    <li><a href=\"#\">VK</a></li>-->\n                </ul>\n            </li>\n            <li>\n                <h1>COMPANY</h1>\n                <ul class=\"page-footer-content-subitem-list\">\n                    <!--<li><a href=\"/static/about-us\">About Us</a></li>\n                    <li><a href=\"/static/terms-of-use\">Terms of Use</a></li>\n                    <li><a href=\"/static/copyright-infringement\">Copyright Infringement</a></li>\n                    <li><a href=\"/static/DMCA\">DMCA</a></li>-->\n                </ul>\n            </li>\n            <li>\n                <h1>HELP & SUPPORT</h1>\n                <ul class=\"page-footer-content-subitem-list\">\n                    <!--<li><a href=\"/static/faq\">FAQ</a></li>\n                    <li><a href=\"/static/contact-us\">Contact Us</a></li>\n                    <li><a href=\"/static/provacy\">Privacy</a></li>-->\n                </ul>\n            </li>\n        </ul>\n        <div class=\"page-footer-copyright\">MYOWNRADIO &copy; <?= $copy ?></div>\n    </div>\n</div>','.page-footer-background {\n    width: 100%;\n    background: url(\"/images/bg.png\") repeat center center;\n    position: absolute;\n    bottom: 0px;\n    z-index: 100;\n    box-shadow: inset 0 16px 16px -12px rgba(0,0,0,0.6);\n}\n\n.page-footer-content {\n    \n    text-align: center;\n}\n\n.page-footer-content-list {\n    box-sizing: border-box;\n    padding: 53px 16px;\n    color: #737c85;\n    display: inline-block;\n    height: 278px;\n}\n\n.page-footer-content-list > li {\n    float: left;\n    width: 170px;\n    text-align: left;\n}\n\n.page-footer-content-list > li:not(:last-child) {\n    margin-right: 32px;\n}\n\n.page-footer-content-list > li > h1 {\n    font-weight: bold;\n    margin-bottom: 18px;\n}\n\n.page-footer-content-subitem-list {\n    padding-right: 8px;\n}\n\n.page-footer-content-subitem-list > li:not(:last-child) {\n    margin-bottom: 14px;\n}\n\n.page-footer-content-subitem-list a {\n    color: #737c85;\n    margin: 0;\n    padding: 0;\n}\n\n.page-footer-content-subitem-list a:hover {\n    color: #a4d35a;\n}\n\n.page-footer-copyright {\n    color: #737c85;\n    clear: both;\n    padding: 0  16px 32px 16px;\n    box-sizing: border-box;\n    text-align: center;\n    vertical-align: middle;\n}\n','','','',1,'2014-09-09 10:52:20',''),('us.header','<div class=\"page-head\">\n    <div class=\"page-head-prefix\"></div>\n    <div class=\"-limit-width -no-overflow page-head-fixed\">\n        <div class=\"page-head-logo-cell page-head-cell -float-left\">\n            <h1 class=\"page-head-logo-cell-text\"><a href=\"/\"><i class=\"mor-logo\"></i>MYOWNRADIO</a></h1>\n        </div>\n        <div class=\"page-head-search-cell page-head-cell -float-left\">\n            <div class=\"page-head-search-border\">\n                <form class=\"page-head-form-search\" action=\"/search\" method=\"GET\">\n                    <input type=\"text\" name=\"q\" value=\"<?= htmlspecialchars(application::get(\"q\", \"\", REQ_STRING)); ?>\" placeholder=\"Search\" autocomplete=\"off\" autofocus />\n                    <input type=\"submit\" value=\"\" title=\"Search\" />\n                </form>\n            </div>\n        </div>\n        <div class=\"page-head-menu-cell page-head-cell -float-left\">\n            <a class=\"page-head-menu-link\" href=\"/browse\"><i class=\"icon-podcast\"></i>STREAMS</a>\n            <a class=\"page-head-menu-link\" href=\"/categories\"><i class=\"icon-podcast\"></i>CATEGORIES</a>\n        </div>\n        <div class=\"page-head-login-cell page-head-cell -float-right\">\n            <?php if(user::getCurrentUserId() === 0): ?>\n                <a class=\"page-head-signup-link\" href=\"/signup\">Sign Up</a>\n                or\n                <a class=\"page-head-login-link\" href=\"/login\">Sign In</a>\n            <?php else: ?>\n                <a class=\"page-head-signup-link\" target=\"_blank\" href=\"/radiomanager\"><i class=\"icon-enter\"></i>Radio Manager</a>\n                <a class=\"page-head-signup-link\" href=\"/logout\"><i class=\"icon-exit\"></i>Logout</a>\n            <?php endif; ?>\n        </div>\n    </div>\n</div>','.page-head {\n    background-color: #272d43;\n    overflow: hidden;\n    border-bottom: 1px solid #0e1119;\n    height: 90px;\n    width: 100%;\n    position: relative;\n}\n\n.page-head-prefix {\n    width: 10px;\n    height: 100%;\n    background-color: #a4d35a;\n    position: absolute;\n}\n\n.page-head-cell {\n    line-height: 88px;\n    height: 90px;\n    padding: 0px 6px;\n    vertical-align: middle;\n}\n\n.page-head-cell:first-child {\n    padding-left: 0 !important;\n}\n\n.page-head-cell:last-child {\n    padding-right: 0 !important;\n}\n\n.page-head-logo-cell {\n    background-color: #a4d35a;\n    color: #272d43;\n}\n\nh1.page-head-logo-cell-text {\n    font-family: \"Myriad Pro\";\n    font-size: 30px;\n    color: #ffffff;\n}\n\nh1.page-head-logo-cell-text span {\n    color: #272d43;\n}\n\nh1.page-head-logo-cell-text a {\n    color: #fff;\n}\n\nh1.page-head-logo-cell-text a:hover {\n    color: #fff;\n}\n\n.page-head-search-cell {\n    width: 300px;\n    padding-left: 12px;\n}\n\n.page-head-search-border {\n    position: relative;\n    width: 100%;\n    border-radius: 3px;\n    display: inline-block;\n    box-sizing: border-box;\n    background-color: #ffffff;\n    border: none;\n    position: relative;\n    vertical-align: middle;\n}\n\n.page-head-form-search {\n    position: relative;\n    display: block;\n    line-height: 22px;\n}\n\n.page-head-form-search > input[type=\"text\"] {\n    width: 100%;\n    height: 22px;\n    margin: 3px 0;\n    padding: 3px 16px;\n    outline: none;\n    border: none;\n    background: #ffffff;\n    box-sizing: border-box;\n    display: block;\n    color: #000000;\n}\n\n.page-head-form-search > input[type=\"submit\"] {\n    display: none;\n    position: absolute;\n    border: none;\n    background: #ffffff url(\"/images/mor-icon-search.png\") no-repeat center center;\n    width: 32px;\n    height: 22px;\n    right: 0px;\n    display: block;\n    top: 0px;\n    margin-right: 2px;\n    border-left: 1px dashed #dddddd;\n    cursor: pointer;\n/*    opacity: 0.7;\n    transition: opacity linear 150ms; */\n}\n\n.page-head-form-search > input[type=\"submit\"]:hover {\n    /* opacity: 1; */\n}\n\n.page-head-login-link {\n    background-color: #fc7d7d;\n    padding: 6px 12px;\n    text-decoration: none;\n    color: #ffffff;\n    border-radius: 12px;\n    margin-left: 8px;\n    transition: background-color linear 150ms;\n}\n\n.page-head-signup-link {\n    background-color: #000000;\n    padding: 6px 12px;\n    text-decoration: none;\n    color: #ffffff;\n    border-radius: 12px;\n    margin-right: 8px;\n    transition: background-color linear 150ms;\n}\n\n.page-head-login-link:hover {\n    background-color: #fc7d7d;\n    color: #ffffff;\n}\n\n.page-head-signup-link:hover {\n    background-color: #fc7d7d;\n    color: #ffffff;\n}\n','(function(){\n    $(\".page-head-form-search > input[type=\'text\']\").livequery(function(){\n        this.setSelectionRange($(this).val().length, $(this).val().length);\n    });\n})();','','',1,'2014-09-12 23:39:24',''),('us.player','<?php\r\n$stream = application::singular(\'stream\', $_MODULE[\'stream_id\']);\r\n$stream_owner = user::getUserByUid($stream->getOwner());\r\n$stream_genres = explode(\",\", strtoupper($stream->getStreamGenres()));\r\n?>\r\n<div class=\"page-player-background stopped awaiting\">\r\n    <div class=\"-limit-width -no-overflow page-title-content\">\r\n        <div class=\"page-title-text\"><?= strtoupper($stream->getStreamName()); ?></div>\r\n    </div>\r\n    <div class=\"-limit-width -no-overflow page-player-content\">\r\n        <div class=\"-float-right page-player-title-block\">\r\n            <div class=\"page-player-title-nowplaying\">STOPPED</div>\r\n            <div class=\"page-player-title-hider\">\r\n                <div class=\"page-player-title-track\">NONE</div>\r\n                <div class=\"page-player-title-progress\">\r\n                    <div class=\"page-player-title-progress-value\">\r\n                        <div class=\"page-player-title-progress-value-roll\"><i class=\"icon-arrow-right2 -no-padding\"></i></div>\r\n                    </div>\r\n                </div>\r\n                <div class=\"page-player-title-next\">NEXT WILL BE</div>\r\n                <div class=\"page-player-title-next-track\"></div>\r\n            </div>\r\n        </div>\r\n        <div class=\"-float-left page-player-control-block -fix-center\">\r\n            <i class=\"icon-play page-player-control-button _player-status-toggle\"></i>\r\n        </div>\r\n        <?php if($stream->getOwner() === user::GetCurrentUserId()): ?>\r\n           <div class=\"page-player-stream-control\">\r\n                <ul class=\"page-player-stream-control-actions\">\r\n                    <li><a href=\"javascript:commonPreviousTrack();\" title=\"Previous track\">PREV</a></li>\r\n                    <li><a href=\"javascript:removeTrackFromStream();\" title=\"Remove from stream\">REM</a></li>\r\n                    <li><a href=\"javascript:removeTrackTotally();\" title=\"Remove track completely\">KILL</a></li>\r\n                    <li><a href=\"javascript:commonNextTrack();\" title=\"Next track\">NEXT</a></li>\r\n                </ul>\r\n           </div>\r\n        <?php endif; ?>\r\n    </div>\r\n</div>\r\n<div class=\"page-info-background\">\r\n    <div class=\"-limit-width -no-overflow page-info-content\">\r\n        <div class=\"page-info-description -float-right\">\r\n            <div class=\"page-info-description-text\"><?= $stream->getStreamInfo(); ?></div>\r\n        </div>\r\n        <div class=\"page-info-main -float-left\">\r\n            <div class=\"page-info-main-title\">STREAM OWNER</div>\r\n            <div class=\"page-info-main-value\"><?= strtoupper($stream_owner[\'name\']); ?></div>\r\n            <div class=\"page-info-main-title\">STREAM TAGS</div>\r\n            <div class=\"page-info-main-value\"><ul class=\"page-info-stream-genres\"><?= layout::parseHashTags($stream->getStreamGenres(), \'li\'); ?></ul></div>\r\n        </div>\r\n    </div>\r\n</div>','.page-player-content {\n    background: #000000 url(\"/images/playerbg2.jpg\") no-repeat center center;\n    height: 400px;\n    color: white;\n    text-shadow: 0 0 2px #000000;\n    box-shadow: inset 0 0 64px 8px #000;\n}\n\n\n.page-player-title-block {\n    font-family: \"Myriad Pro Thin\";\n    font-size: 20pt;\n    width: 600px;\n    box-sizing: border-box;\n    position: relative;\n    transition: all linear 150ms;\n    top: 80px;\n    \n}\n\n.page-player-title-nowplaying {\n    margin-bottom: 8px;\n}\n\n.page-player-title-track {\n    font-size: 30pt;\n    transition: opacity linear 150ms;\n    min-height: 40px;\n    /* color: #a4d35a; */\n}\n\n.page-player-title-progress {\n    margin-top: 24px;\n    margin-bottom: 24px;\n    margin-left: 2px;\n    height: 2px;\n    width: 550px;\n    box-sizing: border-box;\n    background-color: #a4d35a;\n    border-radius: 3px;\n    box-shadow: 0 0 6px 1px #000000;\n}\n\n.page-player-title-hider {\n    transition: opacity linear 150ms;\n}\n\n.stopped .page-player-title-hider {\n    opacity: 0;\n}\n\n.page-player-title-progress-value {\n    width: 0%;\n    height: 100%;\n    background-color: #ffffff;\n    position: relative;\n    transition: all linear 150ms;\n}\n\n.page-player-title-progress-value-roll {\n    right: -7px;\n    top: -6px;\n    width: 14px;\n    height: 14px;\n    border-radius: 50%;\n    background-color: #ffffff;\n    position: absolute;\n    color: #000000;\n    font-size: 8pt;\n    text-align: center;\n    transition: all linear 150ms;\n}\n\n.page-player-title-progress-value-roll > i {\n    position: relative;\n    top: 1.5px;\n    text-shadow: none;\n}\n\n.page-player-title-next {\n    font-size: 22pt;\n    color: #ffffff;\n    margin-bottom: 8px;\n}\n\n.page-player-title-next-track {\n    color: #ffffff;\n}\n\n\n.page-player-control-block {\n    width: 400px;\n    box-sizing: border-box;\n    position: relative; \n    font-size: 120pt;\n    text-align: center;\n    line-height: 360px;\n    vertical-align: middle;\n}\n\n.page-player-control-button {\n    cursor: pointer;\n    opacity: 0.5;\n    transition: opacity linear 150ms;\n}\n\n.page-player-control-button:hover {\n    opacity: 1;\n}\n\n\n.page-info-background {\n\n}\n\n.page-info-content {\n    min-height: 300px;\n    background-color: #161a26;\n}\n\n.page-info-main {\n    width: 300px;\n    padding: 16px 0px;\n    box-sizing: border-box;\n}\n\n.page-info-main-title {\n    color: #ffffff;\n    margin-bottom: 4px;\n}\n\n.page-info-main-value {\n    color: #697486;\n    margin-bottom: 16px;\n}\n\n.page-info-description {\n    width: 700px;\n    padding: 16px;\n    box-sizing: border-box;\n}\n\n.page-title-background {\n}\n\n.page-title-content {\n    background-color: #161a26;\n}\n\n.page-title-text {\n    color: white;\n    font-size: 30pt;\n    font-family: \"Myriad Pro Thin\";\n    margin: 16px 0;\n}\n\n.page-title-text > .page-title-test-sep {\n    font-size: 20pt;\n    vertical-align: 2px;\n    margin: 0 !important;\n    padding: 0 8px !important;\n}\n\n.page-info-description-text {\n    line-height: 150%;\n}\n\n\n.page-player-stream-control {\n    position: absolute;\n    right: 16px;\n    bottom: 16px;\n    opacity: 0;\n    transition: opacity linear 150ms;\n}\n\n.page-player-content:hover .page-player-stream-control {\n    opacity: 1;\n}\n\n.page-player-stream-control-actions {\n    overflow: hidden;\n}\n\n.page-player-stream-control-actions > li {\n    float: left;\n    margin: 0 8px;\n}\n\n#logger {\n    padding: 2px;\n    position: fixed;\n    bottom: 0;\n    right: 0;\n    z-index: 20;\n    font-size: 8pt;\n    z-index: 1000;\n}\n\n/* Bitrate section */\n\n.page-bitrate-container {\n    float: right;\n    margin: 16px 0;\n    line-height: 38px;\n}\n\n.page-bitrate-container-list {\n    overflow: hidden;\n    font-family: \"Myriad Pro Thin\";\n    font-size: 18pt;\n}\n\n\n\n.page-bitrate-container-list > li {\n    float: left;\n    padding: 0px 4px;\n}\n\n.page-bitrate-container-list > li > a {\n    color: #fff;\n}\n\n.page-bitrate-container-list > li.active > a {\n    color: #4af;\n    border-bottom: 1px solid #4af;\n}\n\n.page-bitrate-container-list > li:hover > a {\n    color: #a4d35a;\n}\n\n','/* Audio Player Section */\n\nvar radioStatus = false;\n\n(function() {\n\n\n    var radioPosition = 0;\n    var radioStarted = false;\n    \n    var delayedStart = false;\n\n    $(document).on(\"ready\", function() {\n        $(\"<div>\").attr(\"id\", \"jplayer\").appendTo(\"body\");\n        initRadioPlayer();\n    });\n\n    function initRadioPlayer() {\n        $(\"#logger\").text(\"Client-Server time delta: \" + timeDifference + \" ms\");\n        $(\"#jplayer\").jPlayer({\n            ready: function(event) {\n                if(window.location.hash === \'#play\') {\n                    startStream();\n                }\n            },\n            ended: function(event) {\n                stopStream();\n            },\n            error: function(event) {\n                errorStream();\n            },\n            timeupdate: function(event) {\n                radioPosition = radioStarted + (event.jPlayer.status.currentTime * 1000) + timeDifference;\n\n                if(event.jPlayer.status.currentTime > 0 && ! connectEvent) {\n                    connected();\n                }\n            },\n            progress: function(event) {\n            },\n            swfPath: \"/swf\",\n            supplied: \"mp3\",\n            solution: \"flash, html\",\n            volume: 1\n        });\n    }\n\n    function startStream() {\n        if(delayedStart) {\n            window.clearTimeout(delayedStart);\n        }\n            \n        connectEvent = false;\n        radioStatus = true;\n        radioStarted = Math.floor(new Date().getTime());\n\n        $(\"#jplayer\").jPlayer(\"setMedia\", {\n            mp3: \"http://\" + window.location.host + \":7778/audio?s=\" + stream.stream_id\n        }).jPlayer(\"play\");\n\n        updateCurrentTrack(true);\n\n        $(\".page-player-background\").removeClass(\"awaiting\");\n        $(\".page-player-title-nowplaying\").text(\"CONNECTING...\");\n        $(\".page-player-control-button\").removeClass(\"icon-play\").addClass(\"icon-stop\");\n    }\n\n    function stopStream() {\n        connectEvent = false;\n        radioStatus = false;\n        $(\"#jplayer\").jPlayer(\"stop\").jPlayer(\"clearMedia\");\n        $(\".page-player-background\").addClass(\"stopped awaiting\");\n        $(\".page-player-title-nowplaying\").text(\"STOPPED\");\n        $(\".page-player-control-button\").addClass(\"icon-play\").removeClass(\"icon-stop\");\n        $(\".page-player-title-track\").text(\"NONE\");\n        $(\".page-player-title-progress-value\").width(0);\n        radioMicroSync = 0;\n    }\n    \n    function errorStream() {\n        connectEvent = false;\n        radioStatus = false;\n        $(\"#jplayer\").jPlayer(\"stop\").jPlayer(\"clearMedia\");\n        $(\".page-player-background\").addClass(\"stopped awaiting\");\n        $(\".page-player-title-nowplaying\").text(\"STREAM ERROR\");\n        $(\".page-player-control-button\").addClass(\"icon-play\").removeClass(\"icon-stop\");\n        $(\".page-player-title-track\").text(\"NONE\");\n        $(\".page-player-title-progress-value\").width(0);\n        radioMicroSync = 0;\n        delayedStart = window.setTimeout(function(){\n            delayedStart = false;\n            startStream();\n        }, 5000);\n    }\n    \n    var connectEvent = false;\n    function connected() {\n        connectEvent = true;\n        $(\".page-player-background\").removeClass(\"stopped\");\n        $(\".page-player-title-nowplaying\").text(\"NOW PLAYING\");\n    }\n\n    var refreshHandle = false;\n    var iteratorCount = 0;\n    \n    function statusRefresh() {\n        if(radioStatus === false || typeof myRadio === \"undefined\") \n            return false;\n        \n        // Update interface\n        if(typeof myRadio.data.now_playing !== \"undefined\") {\n            var realPos = radioPosition - myRadio.data.now_playing.started_at;\n            var perCent = 100 / myRadio.data.now_playing.duration * realPos;\n            if(perCent < 0 || perCent > 100) {\n                $(\".page-player-title-progress-value\").hide();\n            } else {\n                $(\".page-player-title-progress-value\").width(perCent.toString() + \"%\").show();\n            }\n        } else {\n            $(\".page-player-title-progress-value\").width(\"0%\").hide();\n        }\n        \n        if (iteratorCount > 40) {\n            updateCurrentTrack();\n        } else {\n            iteratorCount ++;\n            refreshHandle = window.setTimeout(function() { statusRefresh(); }, 250);\n        }\n    }\n\n    function updateCurrentTrack(sync)\n    {\n\n        $.post(\"/api/v2/stream\", {id: stream.stream_id, time: radioPosition - streamPreload}, function(json) {\n            iteratorCount = 0;\n            myRadio = json;\n            if(myRadio.data.stream_status === 1 && typeof myRadio.data.now_playing !== \"undefined\") {\n                var titleUpperCase = myRadio.data.now_playing.title.toUpperCase();\n                var nextTitleUpperCase = myRadio.data.next.title.toUpperCase();\n            } else {\n                var titleUpperCase = \"NO SIGNAL\";\n                var nextTitleUpperCase = \"NO SIGNAL\";\n            }\n\n            $(\".page-player-title-track\").fadeText(titleUpperCase);\n            $(\".page-player-title-next-track\").fadeText(nextTitleUpperCase);\n            \n        }).complete(function(){\n            statusRefresh();\n        });\n    }\n\n    $(\"._player-status-toggle\").live(\"click\", function() {\n        if (radioStatus)\n            stopStream();\n        else\n            startStream();\n    });\n\n})();\n\n\n// Common links\nfunction removeTrackTotally() {\n    \n    if(radioStatus === false)\n        return false;\n    \n    var ret = confirm(\"Are you sure want to completely remove this track from all your streams and profile?\");\n    if(ret) {\n        var stream_id   = myRadio.stream_id;\n        var track_id    = myRadio.track_id;\n\n        $.post(\"/radiomanager/removeTrack\", {\n            track_id    : track_id\n        }, function(json){\n            if(json.code === \"DELETE_SUCCESS\")\n            {\n                $(\".page-player-title-track\").fadeText(\"WAIT...\");\n            }\n            else\n            {\n                alert(data.code);\n            }\n        });\n    }\n    \n    return false;\n    \n}\n\nfunction removeTrackFromStream() {\n    \n    if(radioStatus === false || typeof myRadio.unique_id === \"undefined\")\n        return false;\n    \n    var ret = confirm(\"Are you sure want to remove this track from stream?\");\n    if(ret) {\n        var stream_id   = myRadio.stream_id;\n        var unique_id   = myRadio.unique_id;\n        var token       = $(\"body\").attr(\"token\");\n        \n        $.post(\"/radiomanager/removeTrackFromStream\", {\n            authtoken   : token,\n            unique_id   : unique_id,\n            stream_id   : stream_id\n        }, function(data){\n            try\n            {\n                var json = JSON.parse(data);\n                if(json.code === \"REMOVE_FROM_STREAM_SUCCESS\")\n                {\n                    $(\".page-player-title-track\").fadeText(\"WAIT...\");\n                }\n                else\n                {\n                    alert(data.code);\n                }\n            }\n            catch(e){}\n        });\n    }\n    \n    return false;\n    \n}\n','','',1,'2014-10-20 20:12:03',''),('us.searchstreams','<?php\n\n/* \n    myownradio.biz module\n    section : user-size\n    action  : getting list of streams by search query\n*/\n\n$start_from = application::get(\"start\", 0, REQ_INT);\n$search_filter = application::get(\"q\", \"\", REQ_STRING);\n$items_limit = 10;\n$max_info_length = 512;\n?>\n<div class=\"page-stream-list -limit-width\">\n    <h1>FOUND RESULTS FOR \"<?= htmlspecialchars(strtoupper($search_filter)); ?>\" <a class=\"Clear Results\" href=\"/browse\">[CLEAR]</a></h1>\n    <ul class=\"page-stream-list-list _ajax-upload-subject\">\n<?php \n$streams = stream::streamSearch(misc::searchQueryFilter($search_filter), $start_from, $items_limit);  \n$tmpl = new template(\"application/tmpl/us.streamitem.tmpl\");\nforeach($streams as $stream): \n    $tmpl->reset();\n    $tmpl->addVariable(\"stream_id\", $stream[\'sid\']);\n    $tmpl->addVariable(\"stream_url\", stream::staticStreamLink($stream));\n    $tmpl->addVariable(\"stream_title\", strtoupper($stream[\'name\']));\n    $tmpl->addVariable(\"stream_info\", (strlen($stream[\'info\']) < $max_info_length) ? ($stream[\'info\']) : (substr($stream[\'info\'], 0, $max_info_length) . \"...\"));\n    $tmpl->addVariable(\"stream_hashtags\", layout::parseHashTags($stream[\'genres\']), true);\n    echo $tmpl->makeDocument();\nendforeach; ?>\n    </ul>\n<?php $count = count($streams); if($count === 0): ?>\n    <div class=\"page-stream-no-results\">No results</div>\n<?php endif; ?>\n</div>','','','','',1,'2014-09-23 10:22:00',''),('us.service.checkurl','','','','','<?php\n$url = application::post(\"url\", null, REQ_STRING);\nif($url == NULL) {\n    misc::errorJSON(\"NO_ARGUMENTS\");\n}\n\nif(misc::pageExists($url)){\n    echo misc::outputJSON(\"PAGE_PRESENT\");\n} else {\n    echo misc::outputJSON(\"NO_PAGE\");\n}\n?>',1,'2014-09-06 21:17:22','radiomanager/checkUrl'),('us.service.streamstatus','','','','','',1,'2014-09-22 18:33:25','');
/*!40000 ALTER TABLE `r_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_sessions`
--

DROP TABLE IF EXISTS `r_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `r_sessions` (
                              `uid` int(11) NOT NULL,
                              `ip` varchar(15) NOT NULL,
                              `token` varchar(32) NOT NULL,
                              `client_id` varchar(8) NOT NULL,
                              `authorized` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                              `http_user_agent` varchar(4096) NOT NULL,
                              `session_id` varchar(255) NOT NULL,
                              `permanent` tinyint(1) NOT NULL DEFAULT '1',
                              `expires` timestamp NULL DEFAULT NULL,
                              PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_sessions`
--

LOCK TABLES `r_sessions` WRITE;
/*!40000 ALTER TABLE `r_sessions` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=380 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=24568 DEFAULT CHARSET=utf8;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`mor`@`%`*/ /*!50003 TRIGGER `track.when.added` AFTER INSERT ON `r_tracks` FOR EACH ROW BEGIN
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
) ENGINE=InnoDB AUTO_INCREMENT=334 DEFAULT CHARSET=utf8;
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

-- Dump completed on 2021-03-28 18:03:48
