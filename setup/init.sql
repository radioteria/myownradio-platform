# ************************************************************
# Sequel Pro SQL dump
# Version 4499
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: mor.cl5io6q5kceb.eu-central-1.rds.amazonaws.com (MySQL 5.6.27-log)
# Database: mor
# Generation Time: 2016-11-06 21:06:45 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table fs_file
# ------------------------------------------------------------

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `add` AFTER INSERT ON `fs_file` FOR EACH ROW UPDATE fs_list SET files_count = files_count + 1 WHERE fs_id = NEW.server_id */;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `del` AFTER DELETE ON `fs_file` FOR EACH ROW UPDATE fs_list SET files_count = files_count - 1 WHERE fs_id = OLD.server_id */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table fs_list
# ------------------------------------------------------------

CREATE TABLE `fs_list` (
  `fs_id` int(11) NOT NULL AUTO_INCREMENT,
  `is_online` tinyint(1) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL,
  `fs_host` varchar(255) NOT NULL,
  `files_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mor_access
# ------------------------------------------------------------

CREATE TABLE `mor_access` (
  `access` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mor_comment
# ------------------------------------------------------------

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mor_countries
# ------------------------------------------------------------

CREATE TABLE `mor_countries` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(2) NOT NULL DEFAULT '',
  `country_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`country_id`),
  FULLTEXT KEY `FT` (`country_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mor_email_queue
# ------------------------------------------------------------

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



# Dump of table mor_genres
# ------------------------------------------------------------

CREATE TABLE `mor_genres` (
  `genre_id` int(11) NOT NULL AUTO_INCREMENT,
  `genre_name` varchar(255) NOT NULL,
  PRIMARY KEY (`genre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mor_letter_event
# ------------------------------------------------------------

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



# Dump of table mor_limits
# ------------------------------------------------------------

CREATE TABLE `mor_limits` (
  `limit_id` int(11) NOT NULL AUTO_INCREMENT,
  `streams_max` int(11) DEFAULT NULL,
  `time_max` bigint(20) NOT NULL,
  `min_track_length` int(11) NOT NULL DEFAULT '0',
  `max_listeners` int(11) DEFAULT '0',
  PRIMARY KEY (`limit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mor_payment_order
# ------------------------------------------------------------

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mor_payments
# ------------------------------------------------------------

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mor_plans
# ------------------------------------------------------------

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mor_plans_view
# ------------------------------------------------------------

CREATE TABLE `mor_plans_view` (
   `plan_id` INT(11) NOT NULL DEFAULT '0',
   `plan_name` VARCHAR(128) NOT NULL,
   `plan_duration` BIGINT(20) NULL DEFAULT NULL,
   `plan_period` VARCHAR(32) NULL DEFAULT NULL,
   `plan_value` DECIMAL(11) NOT NULL,
   `limit_id` INT(11) NOT NULL,
   `streams_max` INT(11) NULL DEFAULT NULL,
   `time_max` BIGINT(20) NOT NULL,
   `min_track_length` INT(11) NOT NULL DEFAULT '0',
   `max_listeners` INT(11) NULL DEFAULT '0'
) ENGINE=MyISAM;



# Dump of table mor_playlists
# ------------------------------------------------------------

CREATE TABLE `mor_playlists` (
  `playlist_id` int(11) NOT NULL AUTO_INCREMENT,
  `used_id` int(11) NOT NULL,
  `playlist_name` varchar(255) NOT NULL,
  PRIMARY KEY (`playlist_id`),
  KEY `used_id` (`used_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mor_playlists_link
# ------------------------------------------------------------

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



# Dump of table mor_playlists_view
# ------------------------------------------------------------

CREATE TABLE `mor_playlists_view` (
   `link_id` INT(11) NOT NULL DEFAULT '0',
   `playlist_id` INT(11) NOT NULL,
   `position_id` INT(11) NOT NULL,
   `track_id` INT(11) NOT NULL DEFAULT '0',
   `user_id` INT(11) NOT NULL,
   `filename` VARCHAR(255) NOT NULL,
   `file_extension` VARCHAR(32) NOT NULL,
   `artist` VARCHAR(255) NOT NULL,
   `title` VARCHAR(255) NOT NULL,
   `album` VARCHAR(255) NOT NULL,
   `track_number` VARCHAR(11) NOT NULL,
   `genre` VARCHAR(255) NOT NULL,
   `date` VARCHAR(64) NOT NULL,
   `cue` TEXT NULL DEFAULT NULL,
   `duration` INT(11) NOT NULL,
   `filesize` INT(11) NOT NULL,
   `color` INT(11) NOT NULL DEFAULT '0',
   `uploaded` INT(11) NOT NULL,
   `is_new` TINYINT(1) NOT NULL DEFAULT '1'
) ENGINE=MyISAM;



# Dump of table mor_promo_codes
# ------------------------------------------------------------

CREATE TABLE `mor_promo_codes` (
  `promo_code` varchar(16) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `expires` int(11) NOT NULL,
  `use_left` int(11) NOT NULL,
  PRIMARY KEY (`promo_code`),
  KEY `PLAN` (`plan_id`),
  CONSTRAINT `mor_promo_codes_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `mor_plans` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mor_servers_list
# ------------------------------------------------------------

CREATE TABLE `mor_servers_list` (
  `server_id` int(11) NOT NULL AUTO_INCREMENT,
  `server_kind` enum('FS','STREAMER') NOT NULL,
  `server_kind_id` int(11) NOT NULL,
  `is_enabled` int(11) NOT NULL,
  `is_online` int(11) NOT NULL,
  PRIMARY KEY (`server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mor_stream_stats_view
# ------------------------------------------------------------

CREATE TABLE `mor_stream_stats_view` (
   `sid` INT(11) NOT NULL DEFAULT '0',
   `permalink` VARCHAR(255) NULL DEFAULT NULL,
   `uid` INT(11) NOT NULL,
   `started` BIGINT(20) NULL DEFAULT NULL,
   `started_from` BIGINT(20) NULL DEFAULT NULL,
   `status` INT(11) NOT NULL DEFAULT '0',
   `tracks_count` INT(11) NOT NULL DEFAULT '0',
   `tracks_duration` BIGINT(20) NOT NULL DEFAULT '0',
   `listeners_count` INT(11) NOT NULL DEFAULT '0',
   `bookmarks_count` INT(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM;



# Dump of table mor_stream_tracklist_view
# ------------------------------------------------------------

CREATE TABLE `mor_stream_tracklist_view` (
   `tid` INT(11) NOT NULL DEFAULT '0',
   `file_id` INT(11) NULL DEFAULT NULL,
   `uid` INT(11) NOT NULL,
   `filename` VARCHAR(255) NOT NULL,
   `ext` VARCHAR(32) NOT NULL,
   `artist` VARCHAR(255) NOT NULL,
   `title` VARCHAR(255) NOT NULL,
   `album` VARCHAR(255) NOT NULL,
   `track_number` VARCHAR(11) NOT NULL,
   `genre` VARCHAR(255) NOT NULL,
   `date` VARCHAR(64) NOT NULL,
   `cue` TEXT NULL DEFAULT NULL,
   `buy` VARCHAR(255) NULL DEFAULT NULL,
   `duration` INT(11) NOT NULL,
   `filesize` INT(11) NOT NULL,
   `color` INT(11) NOT NULL DEFAULT '0',
   `uploaded` INT(11) NOT NULL,
   `id` BIGINT(20) NOT NULL DEFAULT '0',
   `stream_id` INT(11) NOT NULL,
   `track_id` INT(11) NOT NULL,
   `t_order` INT(11) NOT NULL,
   `unique_id` VARCHAR(8) NOT NULL,
   `time_offset` BIGINT(20) NOT NULL,
   `is_new` TINYINT(1) NOT NULL DEFAULT '1',
   `can_be_shared` TINYINT(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM;



# Dump of table mor_tag_list
# ------------------------------------------------------------

CREATE TABLE `mor_tag_list` (
  `tag_name` varchar(255) NOT NULL,
  `usage_count` int(11) NOT NULL,
  PRIMARY KEY (`tag_name`),
  KEY `first_letter` (`tag_name`(1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mor_track_like
# ------------------------------------------------------------

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


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `ADD` AFTER INSERT ON `mor_track_like` FOR EACH ROW IF (NEW.relation = "like") THEN
	UPDATE mor_track_stat SET likes = likes + 1 WHERE track_id = NEW.track_id;
ELSE
	UPDATE mor_track_stat SET dislikes = dislikes + 1 WHERE track_id = NEW.track_id;
END IF */;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `DEL` AFTER DELETE ON `mor_track_like` FOR EACH ROW IF (OLD.relation = "like") THEN
	UPDATE mor_track_stat SET likes = likes - 1 WHERE track_id = OLD.track_id;
ELSE
	UPDATE mor_track_stat SET dislikes = dislikes - 1 WHERE track_id = OLD.track_id;
END IF */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table mor_track_stat
# ------------------------------------------------------------

CREATE TABLE `mor_track_stat` (
  `track_id` int(11) NOT NULL,
  `likes` int(11) NOT NULL,
  `dislikes` int(11) NOT NULL,
  PRIMARY KEY (`track_id`),
  CONSTRAINT `mor_track_stat_ibfk_1` FOREIGN KEY (`track_id`) REFERENCES `r_tracks` (`tid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mor_users_view
# ------------------------------------------------------------

CREATE TABLE `mor_users_view` (
   `uid` INT(11) NOT NULL DEFAULT '0',
   `mail` VARCHAR(128) NOT NULL,
   `login` VARCHAR(32) NULL DEFAULT NULL,
   `password` VARCHAR(64) NULL DEFAULT NULL,
   `name` VARCHAR(255) NULL DEFAULT NULL,
   `info` VARCHAR(4096) NULL DEFAULT NULL,
   `rights` INT(11) NULL DEFAULT NULL,
   `registration_date` INT(10) UNSIGNED NOT NULL,
   `last_visit_date` INT(10) UNSIGNED NULL DEFAULT NULL,
   `permalink` VARCHAR(255) NULL DEFAULT NULL,
   `avatar` VARCHAR(255) NULL DEFAULT NULL,
   `country_id` INT(11) NULL DEFAULT NULL,
   `user_id` INT(11) NOT NULL,
   `tracks_count` INT(11) NOT NULL DEFAULT '0',
   `tracks_duration` BIGINT(20) NOT NULL DEFAULT '0',
   `tracks_size` BIGINT(20) NOT NULL DEFAULT '0',
   `streams_count` INT(11) NOT NULL DEFAULT '0',
   `plan_id` BIGINT(11) NOT NULL DEFAULT '0',
   `plan_expires` BIGINT(11) NULL DEFAULT NULL
) ENGINE=MyISAM;



# Dump of table opt_user_options
# ------------------------------------------------------------

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



# Dump of table opt_valid_format
# ------------------------------------------------------------

CREATE TABLE `opt_valid_format` (
  `format_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_type` int(11) NOT NULL,
  `format_string` varchar(16) NOT NULL,
  `format_name` varchar(16) NOT NULL,
  `format_bitrate` int(11) NOT NULL,
  PRIMARY KEY (`format_id`),
  KEY `account_type` (`account_type`),
  CONSTRAINT `opt_valid_format_ibfk_1` FOREIGN KEY (`account_type`) REFERENCES `mor_limits` (`limit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table opt_valid_lang
# ------------------------------------------------------------

CREATE TABLE `opt_valid_lang` (
  `lang_id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_code` varchar(2) NOT NULL,
  `lang_locale` varchar(5) NOT NULL,
  `lang_name` varchar(32) NOT NULL,
  PRIMARY KEY (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table r_bookmarks
# ------------------------------------------------------------

CREATE TABLE `r_bookmarks` (
  `user_id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`stream_id`),
  KEY `STREAM_idx` (`stream_id`),
  CONSTRAINT `STREAM` FOREIGN KEY (`stream_id`) REFERENCES `r_streams` (`sid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `USER` FOREIGN KEY (`user_id`) REFERENCES `r_users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `bookmark.when.added` AFTER INSERT ON `r_bookmarks` FOR EACH ROW UPDATE r_static_stream_vars SET bookmarks_count = bookmarks_count + 1 WHERE stream_id = NEW.stream_id */;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `bookmark.when.removed` AFTER DELETE ON `r_bookmarks` FOR EACH ROW UPDATE r_static_stream_vars SET bookmarks_count = bookmarks_count - 1 WHERE stream_id = OLD.stream_id */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table r_categories
# ------------------------------------------------------------

CREATE TABLE `r_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `category_permalink` varchar(255) NOT NULL,
  `streams_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table r_colors
# ------------------------------------------------------------

CREATE TABLE `r_colors` (
  `color_id` int(11) NOT NULL,
  `color_name` varchar(32) NOT NULL,
  `color_code` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`color_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table r_link
# ------------------------------------------------------------

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `STREAM_ADD_TRACK` AFTER INSERT ON `r_link` FOR EACH ROW BEGIN

SELECT duration INTO @duration FROM r_tracks WHERE tid = NEW.track_id;
UPDATE r_tracks SET used_count = used_count + 1 WHERE tid = NEW.track_id;

INSERT INTO `r_static_stream_vars` SET `stream_id` = NEW.`stream_id`, `tracks_count` = 1, `tracks_duration` = @duration ON DUPLICATE KEY UPDATE  `tracks_count` = `tracks_count` + 1, `tracks_duration` = `tracks_duration` + @duration;

END */;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `STREAM_MODIFY_TRACK` AFTER UPDATE ON `r_link` FOR EACH ROW BEGIN
IF (OLD.track_id != NEW.track_id) THEN
UPDATE r_tracks SET used_count = used_count - 1 WHERE tid = OLD.track_id;
UPDATE r_tracks SET used_count = used_count + 1 WHERE tid = NEW.track_id;
END IF;
END */;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `STREAM_REMOVE_TRACK` BEFORE DELETE ON `r_link` FOR EACH ROW BEGIN

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
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table r_listener
# ------------------------------------------------------------

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `when.listener.added` AFTER INSERT ON `r_listener` FOR EACH ROW BEGIN
UPDATE r_static_stream_vars set listeners_count = listeners_count + 1, playbacks = playbacks + 1 WHERE stream_id = NEW.stream;
UPDATE r_static_user_vars set listeners_count = listeners_count + 1 WHERE user_id = (SELECT uid FROM r_streams WHERE sid = NEW.stream);
END */;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `when.listener.finished` AFTER UPDATE ON `r_listener` FOR EACH ROW IF NEW.finished IS NOT NULL THEN
	UPDATE r_static_stream_vars
    SET listeners_count = listeners_count - 1, summary_played = summary_played + TIMESTAMPDIFF(SECOND, NEW.started, NEW.finished)
    WHERE stream_id = NEW.stream;
    UPDATE r_static_user_vars set listeners_count = listeners_count - 1 WHERE user_id = (SELECT uid FROM r_streams WHERE sid = NEW.stream);
END IF */;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `when.listener.deleted` AFTER DELETE ON `r_listener` FOR EACH ROW IF OLD.finished IS NULL THEN
UPDATE r_static_stream_vars SET listeners_count = listeners_count - 1 WHERE stream_id = OLD.stream AND listeners_count > 0;
UPDATE r_static_user_vars set listeners_count = listeners_count - 1 WHERE user_id = (SELECT uid FROM r_streams WHERE sid = OLD.stream);
END IF */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table r_listener_stats
# ------------------------------------------------------------

CREATE TABLE `r_listener_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `stream_id` int(11) NOT NULL,
  `listeners` int(11) NOT NULL,
  `ips` int(11) NOT NULL,
  `average` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `stream_id` (`stream_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table r_modules
# ------------------------------------------------------------

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



# Dump of table r_sessions
# ------------------------------------------------------------

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



# Dump of table r_static_stream_vars
# ------------------------------------------------------------

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



# Dump of table r_static_user_vars
# ------------------------------------------------------------

CREATE TABLE `r_static_user_vars` (
  `user_id` int(11) NOT NULL,
  `tracks_count` int(11) NOT NULL DEFAULT '0',
  `tracks_duration` bigint(20) NOT NULL DEFAULT '0',
  `tracks_size` bigint(20) NOT NULL DEFAULT '0',
  `streams_count` int(11) NOT NULL DEFAULT '0',
  `listeners_count` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table r_stats_memory
# ------------------------------------------------------------

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



# Dump of table r_streams
# ------------------------------------------------------------

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `stream.when.added` AFTER INSERT ON `r_streams` FOR EACH ROW BEGIN
INSERT INTO r_static_stream_vars SET stream_id = NEW.sid;
UPDATE r_static_user_vars SET streams_count = streams_count + 1 WHERE user_id = NEW.uid;
IF NEW.access = "PUBLIC" THEN
	CALL increase_streams_in_category(NEW.category);
END IF;
END */;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `stream.when.changed` AFTER UPDATE ON `r_streams` FOR EACH ROW BEGIN
IF OLD.access = "PUBLIC" THEN
	CALL decrease_streams_in_category(OLD.category);
END IF;
IF NEW.access = "PUBLIC" THEN
	CALL increase_streams_in_category(NEW.category);
END IF;
END */;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `stream.when.removed` AFTER DELETE ON `r_streams` FOR EACH ROW BEGIN
DELETE FROM r_static_stream_vars WHERE stream_id = OLD.sid;
UPDATE r_static_user_vars SET streams_count = streams_count - 1 WHERE user_id = OLD.uid;

IF OLD.access = "PUBLIC" THEN
	CALL decrease_streams_in_category(OLD.category);
END IF;

END */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table r_tracks
# ------------------------------------------------------------

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `ADD_TRACK` AFTER INSERT ON `r_tracks` FOR EACH ROW BEGIN
INSERT INTO `r_static_user_vars` SET `user_id` = NEW.`uid`, `tracks_count` = 1, `tracks_duration` = NEW.`duration`, `tracks_size` = NEW.`filesize`
ON DUPLICATE KEY UPDATE `tracks_count` = `tracks_count` + 1, `tracks_duration` = `tracks_duration` + NEW.`duration`, `tracks_size` = `tracks_size` + NEW.`filesize`;
INSERT INTO mor_track_stat SET track_id = NEW.tid;
END */;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `DELETE_TRACK` BEFORE DELETE ON `r_tracks` FOR EACH ROW BEGIN

UPDATE `r_static_user_vars`
	SET
		`tracks_count` = GREATEST(`tracks_count` - 1, 0),
		`tracks_duration` = GREATEST(`tracks_duration` - OLD.`duration`, 0),
        `tracks_size` = GREATEST(`tracks_size` - OLD.`filesize`, 0)
	WHERE
    	`user_id` = OLD.`uid`;

END */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table r_users
# ------------------------------------------------------------

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `when.user.added` AFTER INSERT ON `r_users` FOR EACH ROW BEGIN
INSERT INTO r_static_user_vars SET user_id = NEW.uid;
INSERT INTO opt_user_options SET user_id = NEW.uid;
END */;;
/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`mor`@`%` */ /*!50003 TRIGGER `when.user.removed` AFTER DELETE ON `r_users` FOR EACH ROW DELETE FROM r_static_user_vars WHERE user_id = OLD.uid */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;




# Replace placeholder table for mor_users_view with correct view syntax
# ------------------------------------------------------------

DROP TABLE `mor_users_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`mor`@`%` SQL SECURITY DEFINER VIEW `mor_users_view`
AS SELECT
   `r_users`.`uid` AS `uid`,
   `r_users`.`mail` AS `mail`,
   `r_users`.`login` AS `login`,
   `r_users`.`password` AS `password`,
   `r_users`.`name` AS `name`,
   `r_users`.`info` AS `info`,
   `r_users`.`rights` AS `rights`,
   `r_users`.`registration_date` AS `registration_date`,
   `r_users`.`last_visit_date` AS `last_visit_date`,
   `r_users`.`permalink` AS `permalink`,
   `r_users`.`avatar` AS `avatar`,
   `r_users`.`country_id` AS `country_id`,
   `r_static_user_vars`.`user_id` AS `user_id`,
   `r_static_user_vars`.`tracks_count` AS `tracks_count`,
   `r_static_user_vars`.`tracks_duration` AS `tracks_duration`,
   `r_static_user_vars`.`tracks_size` AS `tracks_size`,
   `r_static_user_vars`.`streams_count` AS `streams_count`,ifnull((select `mor_payments`.`plan_id`
FROM `mor_payments` where ((`mor_payments`.`user_id` = `r_users`.`uid`) and `mor_payments`.`success`) order by `mor_payments`.`payment_id` desc limit 1),1) AS `plan_id`,(select `mor_payments`.`expires` from `mor_payments` where ((`mor_payments`.`user_id` = `r_users`.`uid`) and `mor_payments`.`success`) order by `mor_payments`.`expires` desc limit 1) AS `plan_expires` from (`r_users` join `r_static_user_vars` on((`r_users`.`uid` = `r_static_user_vars`.`user_id`)));


# Replace placeholder table for mor_plans_view with correct view syntax
# ------------------------------------------------------------

DROP TABLE `mor_plans_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`mor`@`%` SQL SECURITY DEFINER VIEW `mor_plans_view`
AS SELECT
   `p`.`plan_id` AS `plan_id`,
   `p`.`plan_name` AS `plan_name`,
   `p`.`plan_duration` AS `plan_duration`,
   `p`.`plan_period` AS `plan_period`,
   `p`.`plan_value` AS `plan_value`,
   `p`.`limit_id` AS `limit_id`,
   `l`.`streams_max` AS `streams_max`,
   `l`.`time_max` AS `time_max`,
   `l`.`min_track_length` AS `min_track_length`,
   `l`.`max_listeners` AS `max_listeners`
FROM (`mor_plans` `p` join `mor_limits` `l` on((`p`.`limit_id` = `l`.`limit_id`)));


# Replace placeholder table for mor_playlists_view with correct view syntax
# ------------------------------------------------------------

DROP TABLE `mor_playlists_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`mor`@`%` SQL SECURITY DEFINER VIEW `mor_playlists_view`
AS SELECT
   `mor_playlists_link`.`link_id` AS `link_id`,
   `mor_playlists_link`.`playlist_id` AS `playlist_id`,
   `mor_playlists_link`.`position_id` AS `position_id`,
   `r_tracks`.`tid` AS `track_id`,
   `r_tracks`.`uid` AS `user_id`,
   `r_tracks`.`filename` AS `filename`,
   `r_tracks`.`ext` AS `file_extension`,
   `r_tracks`.`artist` AS `artist`,
   `r_tracks`.`title` AS `title`,
   `r_tracks`.`album` AS `album`,
   `r_tracks`.`track_number` AS `track_number`,
   `r_tracks`.`genre` AS `genre`,
   `r_tracks`.`date` AS `date`,
   `r_tracks`.`cue` AS `cue`,
   `r_tracks`.`duration` AS `duration`,
   `r_tracks`.`filesize` AS `filesize`,
   `r_tracks`.`color` AS `color`,
   `r_tracks`.`uploaded` AS `uploaded`,
   `r_tracks`.`is_new` AS `is_new`
FROM (`r_tracks` join `mor_playlists_link` on((`r_tracks`.`tid` = `mor_playlists_link`.`track_id`))) order by `mor_playlists_link`.`position_id`;


# Replace placeholder table for mor_stream_stats_view with correct view syntax
# ------------------------------------------------------------

DROP TABLE `mor_stream_stats_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`mor`@`%` SQL SECURITY DEFINER VIEW `mor_stream_stats_view`
AS SELECT
   `a`.`sid` AS `sid`,
   `a`.`permalink` AS `permalink`,
   `a`.`uid` AS `uid`,
   `a`.`started` AS `started`,
   `a`.`started_from` AS `started_from`,
   `a`.`status` AS `status`,
   `b`.`tracks_count` AS `tracks_count`,
   `b`.`tracks_duration` AS `tracks_duration`,
   `b`.`listeners_count` AS `listeners_count`,
   `b`.`bookmarks_count` AS `bookmarks_count`
FROM (`r_streams` `a` join `r_static_stream_vars` `b` on((`a`.`sid` = `b`.`stream_id`)));


# Replace placeholder table for mor_stream_tracklist_view with correct view syntax
# ------------------------------------------------------------

DROP TABLE `mor_stream_tracklist_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`mor`@`%` SQL SECURITY DEFINER VIEW `mor_stream_tracklist_view`
AS SELECT
   `r_tracks`.`tid` AS `tid`,
   `r_tracks`.`file_id` AS `file_id`,
   `r_tracks`.`uid` AS `uid`,
   `r_tracks`.`filename` AS `filename`,
   `r_tracks`.`ext` AS `ext`,
   `r_tracks`.`artist` AS `artist`,
   `r_tracks`.`title` AS `title`,
   `r_tracks`.`album` AS `album`,
   `r_tracks`.`track_number` AS `track_number`,
   `r_tracks`.`genre` AS `genre`,
   `r_tracks`.`date` AS `date`,
   `r_tracks`.`cue` AS `cue`,
   `r_tracks`.`buy` AS `buy`,
   `r_tracks`.`duration` AS `duration`,
   `r_tracks`.`filesize` AS `filesize`,
   `r_tracks`.`color` AS `color`,
   `r_tracks`.`uploaded` AS `uploaded`,
   `r_link`.`id` AS `id`,
   `r_link`.`stream_id` AS `stream_id`,
   `r_link`.`track_id` AS `track_id`,
   `r_link`.`t_order` AS `t_order`,
   `r_link`.`unique_id` AS `unique_id`,
   `r_link`.`time_offset` AS `time_offset`,
   `r_tracks`.`is_new` AS `is_new`,
   `r_tracks`.`can_be_shared` AS `can_be_shared`
FROM (`r_tracks` join `r_link` on((`r_tracks`.`tid` = `r_link`.`track_id`))) order by `r_link`.`stream_id`,`r_link`.`t_order`;

--
-- Dumping routines (PROCEDURE) for database 'mor'
--
DELIMITER ;;

# Dump of PROCEDURE decrease_streams_in_category
# ------------------------------------------------------------

/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION"*/;;
/*!50003 CREATE*/ /*!50020 DEFINER=`mor`@`%`*/ /*!50003 PROCEDURE `decrease_streams_in_category`(IN `id` INT)
    NO SQL
UPDATE r_categories
SET streams_count = GREATEST(streams_count - 1, 0)
WHERE category_id = id */;;

/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;;
# Dump of PROCEDURE increase_streams_in_category
# ------------------------------------------------------------

/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION"*/;;
/*!50003 CREATE*/ /*!50020 DEFINER=`mor`@`%`*/ /*!50003 PROCEDURE `increase_streams_in_category`(IN `id` INT)
    NO SQL
UPDATE r_categories
SET streams_count = streams_count + 1
WHERE category_id = id */;;

/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;;
# Dump of PROCEDURE move_track_channel
# ------------------------------------------------------------

/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION"*/;;
/*!50003 CREATE*/ /*!50020 DEFINER=`mor`@`%`*/ /*!50003 PROCEDURE `move_track_channel`(IN `s_id` INT, IN `s_target` VARCHAR(16), IN `s_index` INT)
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

END */;;

/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;;
# Dump of PROCEDURE optimize_channel
# ------------------------------------------------------------

/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION"*/;;
/*!50003 CREATE*/ /*!50020 DEFINER=`mor`@`%`*/ /*!50003 PROCEDURE `optimize_channel`(IN `s_id` INT)
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

END */;;

/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;;
# Dump of PROCEDURE serverListenersRotate
# ------------------------------------------------------------

/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION"*/;;
/*!50003 CREATE*/ /*!50020 DEFINER=`mor`@`%`*/ /*!50003 PROCEDURE `serverListenersRotate`()
    NO SQL
BEGIN

INSERT INTO `r_listener_stats` (`date`, `stream_id`, `listeners`, `average`, `ips`)
SELECT CAST(NOW() AS DATE), `stream`, COUNT(DISTINCT client_id), AVG(TIMESTAMPDIFF(MINUTE, started, finished)), COUNT(DISTINCT client_ip)
FROM `r_listener`
WHERE TODAY(`started`) AND `finished` IS NOT NULL
GROUP BY `stream`;

END */;;

/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;;
# Dump of PROCEDURE shuffle_channel
# ------------------------------------------------------------

/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION"*/;;
/*!50003 CREATE*/ /*!50020 DEFINER=`mor`@`%`*/ /*!50003 PROCEDURE `shuffle_channel`(IN `s_id` INT)
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

END */;;

/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;;
DELIMITER ;

--
-- Dumping routines (FUNCTION) for database 'mor'
--
DELIMITER ;;

# Dump of FUNCTION TODAY
# ------------------------------------------------------------

/*!50003 SET SESSION SQL_MODE="NO_ENGINE_SUBSTITUTION"*/;;
/*!50003 CREATE*/ /*!50020 DEFINER=`mor`@`%`*/ /*!50003 FUNCTION `TODAY`(`PARAM` TIMESTAMP) RETURNS tinyint(1)
    NO SQL
RETURN CAST(NOW() AS DATE) = CAST(PARAM AS DATE) */;;

/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;;
DELIMITER ;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

--
-- 07.11.2016 Delete trigger DELETE_TRACK for use in-code implementation
--
DROP TRIGGER `DELETE_TRACK`;

