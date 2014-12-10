SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База даних: `myownradio`
--
CREATE DATABASE IF NOT EXISTS `myownradio` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `myownradio`;

DELIMITER $$
--
-- Процедури
--
CREATE DEFINER=`admin`@`localhost` PROCEDURE `GET_LAST_REVISION`(IN `module` VARCHAR(64))
    NO SQL
BEGIN
SELECT `html` INTO @html FROM `r_modules` WHERE `html` IS NOT NULL AND `name` = module ORDER BY `revision` DESC LIMIT 1;
SELECT `css` INTO @css FROM `r_modules` WHERE `css` IS NOT NULL AND `name` = module ORDER BY `revision` DESC LIMIT 1;
SELECT `js` INTO @js FROM `r_modules` WHERE `js` IS NOT NULL AND `name` = module ORDER BY `revision` DESC LIMIT 1;
SELECT `tmpl` INTO @tmpl FROM `r_modules` WHERE `tmpl` IS NOT NULL AND `name` = module ORDER BY `revision` DESC LIMIT 1;
SELECT @html as `html`, @css as `css`, @js as `js`, @tmpl as `tmpl`;
END$$

CREATE DEFINER=`admin`@`localhost` PROCEDURE `moveListenerToLog`(IN `listener` VARCHAR(32))
    NO SQL
BEGIN
INSERT INTO `r_listener_log`
(`ip`, `unique_id`, `stream_id`, `started`, `duration`, `bitrate`)
SELECT `client_ip`, `listener_id`, `stream_id`, `connected_at`, `listening_time`, `bitrate` FROM `r_listener` WHERE `listener_id` = listener;
DELETE FROM `r_listener` WHERE `listener_id` = listener;
END$$

CREATE DEFINER=`admin`@`localhost` PROCEDURE `moveListenerToLogShedule`()
    NO SQL
BEGIN
INSERT INTO `r_listener_log`
(`ip`, `unique_id`, `stream_id`, `started`, `duration`, `bitrate`)
SELECT `client_ip`, `listener_id`, `stream_id`, `connected_at`, `listening_time`, `bitrate` FROM `r_listener` WHERE UNIX_TIMESTAMP(NOW()) - `last_activity` > 30;
DELETE FROM `r_listener` WHERE UNIX_TIMESTAMP(NOW()) - `last_activity` > 30;
END$$

CREATE DEFINER=`admin`@`localhost` PROCEDURE `PLAN_CHANGE_EVENT`(IN `user_id` INT)
    NO SQL
BEGIN
SELECT `plan` INTO @plan FROM `r_subscriptions`
WHERE
	`uid` = user_id AND
    `expire` > UNIX_TIMESTAMP(NOW())
ORDER BY `expire` DESC LIMIT 1;

IF(@plan = NULL) THEN
	SET @plan := 0;
END IF;

INSERT INTO `m_events_log`
SET
	`user_id` = user_id,
    `event_type` = 'PLAN_CHANGED',
    `event_target` = @plan,
    `event_value` = NULL;
END$$

CREATE DEFINER=`admin`@`localhost` PROCEDURE `p_optimize_stream`(IN `s_id` INT)
    NO SQL
BEGIN
SET @row := 0;

UPDATE `r_link`
SET
	`t_order` = @row := @row + 1
WHERE
	`stream_id` = s_id
ORDER BY `t_order`;
END$$

CREATE DEFINER=`admin`@`localhost` PROCEDURE `P_SHUFFLE_STREAM`(IN `s_id` INT)
    NO SQL
BEGIN
SET @row := 0;

UPDATE `r_link`
SET
	`t_order` = @row := @row + 1
WHERE
	`stream_id` = s_id
ORDER BY RAND();

SELECT `uid` INTO @myID FROM `r_streams` WHERE `sid` = s_id LIMIT 1;

INSERT INTO `m_events_log`
SET
	`user_id` = @myID,
    `event_type` = "STREAM_SORTED",
    `event_target` = s_id,
    `event_value` = NULL;

END$$

CREATE DEFINER=`admin`@`localhost` PROCEDURE `serverListenersRotate`()
    NO SQL
BEGIN

INSERT INTO `r_listener_stats` (`date`, `stream_id`, `listeners`)
SELECT NOW(), `stream_id`, COUNT(DISTINCT `listener_id`) FROM `r_listener` WHERE 1 GROUP BY `stream_id`;

END$$

CREATE DEFINER=`admin`@`localhost` PROCEDURE `TRACK_UPDATE`(IN `uid` INT)
    NO SQL
INSERT INTO `m_track_update` SET `user_id` = uid, `changed` = UNIX_TIMESTAMP(NOW()) ON DUPLICATE KEY UPDATE `changed` = UNIX_TIMESTAMP(NOW())$$

--
-- Функції
--
CREATE DEFINER=`admin`@`localhost` FUNCTION `NEW_STREAM_SORT`(`s_id` INT, `s_target` VARCHAR(16), `s_index` INT) RETURNS int(11)
    NO SQL
BEGIN

SELECT `t_order`,`track_id` INTO @order, @id FROM `r_link` WHERE `unique_id` = s_target;

IF(@order = s_index) THEN
	RETURN NULL;
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

RETURN 1;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `m_events_log`
--

CREATE TABLE IF NOT EXISTS `m_events_log` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_type` enum('LORES_CHANGED','TRACK_ADDED','TRACK_DELETED','STREAM_ADDED','STREAM_DELETED','TRACK_INFO_CHANGED','STREAM_TRACKS_CHANGED','PLAN_CHANGED','STREAM_TRACK_ADDED','STREAM_TRACK_DELETED','STREAM_SET_CURRENT','STREAM_SORT','TOKEN_REMOVED','LIB_DURATION_CHANGED','STREAM_SORTED','STREAM_UPDATED') NOT NULL,
  `event_target` varchar(64) NOT NULL,
  `event_value` varchar(1024) DEFAULT NULL,
  `event_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 AUTO_INCREMENT=37 ;

-- --------------------------------------------------------

--
-- Структура таблиці `m_stream_update`
--

CREATE TABLE IF NOT EXISTS `m_stream_update` (
  `user_id` int(11) NOT NULL,
  `changed` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `m_track_update`
--

CREATE TABLE IF NOT EXISTS `m_track_update` (
  `user_id` int(11) NOT NULL,
  `changed` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `r_categories`
--

CREATE TABLE IF NOT EXISTS `r_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `permalink` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_echoprints`
--

CREATE TABLE IF NOT EXISTS `r_echoprints` (
  `tid` int(11) NOT NULL,
  `echoprint` varchar(4096) NOT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `r_genres`
--

CREATE TABLE IF NOT EXISTS `r_genres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `genre` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `FT` (`genre`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1528 ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_hashtags`
--

CREATE TABLE IF NOT EXISTS `r_hashtags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_limitations`
--

CREATE TABLE IF NOT EXISTS `r_limitations` (
  `level` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `upload_limit` bigint(20) NOT NULL,
  `streams_max` int(11) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  UNIQUE KEY `LEVEL` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `r_link`
--

CREATE TABLE IF NOT EXISTS `r_link` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL,
  `track_id` int(11) NOT NULL,
  `t_order` int(11) NOT NULL,
  `unique_id` varchar(8) NOT NULL,
  `time_offset` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ID` (`unique_id`),
  KEY `STREAM` (`stream_id`),
  KEY `TRACK` (`track_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3529 ;

--
-- Тригери `r_link`
--
DROP TRIGGER IF EXISTS `STREAM_ADD_TRACK`;
DELIMITER //
CREATE TRIGGER `STREAM_ADD_TRACK` AFTER INSERT ON `r_link`
 FOR EACH ROW BEGIN

SELECT `duration` INTO @duration FROM `r_tracks` WHERE `tid` = NEW.`track_id`;

INSERT INTO `r_static_stream_vars` SET `stream_id` = NEW.`stream_id`, `tracks_count` = 1, `tracks_duration` = @duration ON DUPLICATE KEY UPDATE  `tracks_count` = `tracks_count` + 1, `tracks_duration` = `tracks_duration` + @duration;

END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `STREAM_REMOVE_TRACK`;
DELIMITER //
CREATE TRIGGER `STREAM_REMOVE_TRACK` BEFORE DELETE ON `r_link`
 FOR EACH ROW BEGIN
SELECT `duration` INTO @duration FROM `r_tracks` WHERE `tid` = OLD.`track_id`;
UPDATE `r_static_stream_vars` SET `tracks_count` = GREATEST(`tracks_count` - 1, 0), `tracks_duration` = GREATEST(`tracks_duration` - @duration, 0) WHERE `stream_id` = OLD.`stream_id`;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_listener`
--

CREATE TABLE IF NOT EXISTS `r_listener` (
  `listener_id` varchar(32) NOT NULL,
  `client_ip` varchar(15) NOT NULL,
  `client_ua` varchar(255) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `bitrate` int(11) NOT NULL,
  `last_activity` int(11) NOT NULL,
  `listening_time` int(11) NOT NULL,
  `connected_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`listener_id`),
  KEY `STREAM` (`stream_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

--
-- Тригери `r_listener`
--
DROP TRIGGER IF EXISTS `LISTENER_IN`;
DELIMITER //
CREATE TRIGGER `LISTENER_IN` AFTER INSERT ON `r_listener`
 FOR EACH ROW BEGIN
INSERT INTO `r_static_stream_vars` SET `stream_id` = NEW.`stream_id`, `listeners_count` = 1
ON DUPLICATE KEY UPDATE `listeners_count` = `listeners_count` + 1;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `LISTENER_OUT`;
DELIMITER //
CREATE TRIGGER `LISTENER_OUT` BEFORE DELETE ON `r_listener`
 FOR EACH ROW BEGIN
UPDATE `r_static_stream_vars` SET `listeners_count` = GREATEST(`listeners_count` - 1, 0) WHERE `stream_id` = OLD.`stream_id`;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_listener_log`
--

CREATE TABLE IF NOT EXISTS `r_listener_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `unique_id` varchar(32) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `started` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `duration` int(11) NOT NULL,
  `bitrate` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=500 ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_listener_stats`
--

CREATE TABLE IF NOT EXISTS `r_listener_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `stream_id` int(11) NOT NULL,
  `listeners` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_listener_stats_daily`
--

CREATE TABLE IF NOT EXISTS `r_listener_stats_daily` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `stream_id` int(11) NOT NULL,
  `listeners` int(11) NOT NULL,
  `average_listening` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `stream_id` (`stream_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1394 ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_modules`
--

CREATE TABLE IF NOT EXISTS `r_modules` (
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

-- --------------------------------------------------------

--
-- Структура таблиці `r_now_playing`
--

CREATE TABLE IF NOT EXISTS `r_now_playing` (
  `stream_id` int(11) NOT NULL,
  `unique_id` varchar(8) NOT NULL,
  PRIMARY KEY (`stream_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `r_server_stats`
--

CREATE TABLE IF NOT EXISTS `r_server_stats` (
  `run_id` int(11) NOT NULL AUTO_INCREMENT,
  `tracks_played` bigint(20) NOT NULL,
  `server_bytes_decoded` bigint(20) NOT NULL,
  `server_bytes_encoded` bigint(20) NOT NULL,
  `client_bytes_sent` bigint(20) NOT NULL,
  `clients_total` bigint(20) NOT NULL,
  `clients_5min` bigint(20) NOT NULL,
  `jingle_streamings` bigint(20) NOT NULL,
  `jingle_playbacks` bigint(20) NOT NULL,
  `uptime_seconds` bigint(20) NOT NULL,
  PRIMARY KEY (`run_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_sessions`
--

CREATE TABLE IF NOT EXISTS `r_sessions` (
  `uid` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `token` varchar(32) NOT NULL,
  `authorized` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `http_user_agent` varchar(4096) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `permanent` tinyint(1) NOT NULL DEFAULT '1',
  `expires` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Тригери `r_sessions`
--
DROP TRIGGER IF EXISTS `TOKEN REMOVE`;
DELIMITER //
CREATE TRIGGER `TOKEN REMOVE` BEFORE DELETE ON `r_sessions`
 FOR EACH ROW INSERT INTO `m_events_log`
SET
	`user_id` = OLD.`uid`,
    `event_type` = 'TOKEN_REMOVED',
    `event_target` = OLD.`uid`,
    `event_value` = OLD.`token`
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_static_stream_vars`
--

CREATE TABLE IF NOT EXISTS `r_static_stream_vars` (
  `stream_id` int(11) NOT NULL,
  `tracks_count` int(11) NOT NULL DEFAULT '0',
  `tracks_duration` bigint(20) NOT NULL DEFAULT '0',
  `listeners_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`stream_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `r_static_user_vars`
--

CREATE TABLE IF NOT EXISTS `r_static_user_vars` (
  `user_id` int(11) NOT NULL,
  `tracks_count` int(11) NOT NULL,
  `tracks_duration` bigint(20) NOT NULL,
  `tracks_size` bigint(20) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Тригери `r_static_user_vars`
--
DROP TRIGGER IF EXISTS `CHANGED`;
DELIMITER //
CREATE TRIGGER `CHANGED` AFTER UPDATE ON `r_static_user_vars`
 FOR EACH ROW INSERT INTO `m_events_log`
SET
	`user_id` = NEW.`user_id`,
    `event_type` = 'LIB_DURATION_CHANGED',
    `event_target` = NEW.`user_id`,
    `event_value` = NEW.`tracks_duration`
//
DELIMITER ;
DROP TRIGGER IF EXISTS `NEW_ADDED`;
DELIMITER //
CREATE TRIGGER `NEW_ADDED` AFTER INSERT ON `r_static_user_vars`
 FOR EACH ROW INSERT INTO `m_events_log`
SET
	`user_id` = NEW.`user_id`,
    `event_type` = 'LIB_DURATION_CHANGED',
    `event_target` = NEW.`user_id`,
    `event_value` = NEW.`tracks_duration`
//
DELIMITER ;
DROP TRIGGER IF EXISTS `OLD_REMOVED`;
DELIMITER //
CREATE TRIGGER `OLD_REMOVED` BEFORE DELETE ON `r_static_user_vars`
 FOR EACH ROW INSERT INTO `m_events_log`
SET
	`user_id` = OLD.`user_id`,
    `event_type` = 'LIB_DURATION_CHANGED',
    `event_target` = OLD.`user_id`,
    `event_value` = 0
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_stats_memory`
--

CREATE TABLE IF NOT EXISTS `r_stats_memory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `uid` int(11) NOT NULL,
  `uri` varchar(4096) NOT NULL,
  `referer` varchar(4096) NOT NULL,
  `useragent` varchar(4096) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_streams`
--

CREATE TABLE IF NOT EXISTS `r_streams` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `permalink` varchar(255) NOT NULL,
  `info` varchar(4096) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL DEFAULT '0',
  `started` bigint(20) NOT NULL,
  `started_from` bigint(20) NOT NULL,
  `access` enum('PUBLIC','UNLISTED','PRIVATE') NOT NULL DEFAULT 'PUBLIC',
  `category` int(11) NOT NULL DEFAULT '13',
  `hashtags` varchar(4096) NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`sid`),
  KEY `UID` (`uid`),
  FULLTEXT KEY `TAGS` (`hashtags`),
  FULLTEXT KEY `FT` (`name`,`permalink`,`hashtags`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=54 ;

--
-- Тригери `r_streams`
--
DROP TRIGGER IF EXISTS `STREAM_ADD`;
DELIMITER //
CREATE TRIGGER `STREAM_ADD` AFTER INSERT ON `r_streams`
 FOR EACH ROW BEGIN
INSERT INTO `m_events_log` SET
	`user_id` = NEW.`uid`,
    `event_type` = "STREAM_ADDED",
    `event_target` = NEW.`sid`,
    `event_value` = NULL;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `STREAM_DEL`;
DELIMITER //
CREATE TRIGGER `STREAM_DEL` BEFORE DELETE ON `r_streams`
 FOR EACH ROW BEGIN
DELETE FROM `r_link` WHERE `stream_id` = OLD.`sid`;
DELETE FROM `r_static_stream_vars` WHERE `stream_id` = OLD.`sid`;
INSERT INTO `m_events_log` SET
	`user_id` = OLD.`uid`,
    `event_type` = "STREAM_DELETED",
    `event_target` = OLD.`sid`,
    `event_value` = NULL;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `STREAM_UPD`;
DELIMITER //
CREATE TRIGGER `STREAM_UPD` AFTER UPDATE ON `r_streams`
 FOR EACH ROW BEGIN
INSERT INTO `m_events_log` SET
	`user_id` = OLD.`uid`,
    `event_type` = "STREAM_UPDATED",
    `event_target` = OLD.`sid`,
    `event_value` = NULL;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_subscriptions`
--

CREATE TABLE IF NOT EXISTS `r_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `plan` int(11) NOT NULL,
  `payment_info` varchar(4096) NOT NULL,
  `expire` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `USER` (`uid`),
  KEY `PLAN` (`plan`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Тригери `r_subscriptions`
--
DROP TRIGGER IF EXISTS `SUBSCR_ADD`;
DELIMITER //
CREATE TRIGGER `SUBSCR_ADD` AFTER INSERT ON `r_subscriptions`
 FOR EACH ROW CALL PLAN_CHANGE_EVENT(NEW.`uid`)
//
DELIMITER ;
DROP TRIGGER IF EXISTS `SUBSCR_CHG`;
DELIMITER //
CREATE TRIGGER `SUBSCR_CHG` AFTER UPDATE ON `r_subscriptions`
 FOR EACH ROW CALL PLAN_CHANGE_EVENT(NEW.`uid`)
//
DELIMITER ;
DROP TRIGGER IF EXISTS `SUBSCR_REM`;
DELIMITER //
CREATE TRIGGER `SUBSCR_REM` BEFORE DELETE ON `r_subscriptions`
 FOR EACH ROW CALL PLAN_CHANGE_EVENT(OLD.`uid`)
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_titlesync`
--

CREATE TABLE IF NOT EXISTS `r_titlesync` (
  `key` varchar(64) NOT NULL,
  `unique_id` varchar(16) NOT NULL,
  `track_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `started` bigint(20) NOT NULL,
  `duration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `r_tracks`
--

CREATE TABLE IF NOT EXISTS `r_tracks` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `ext` varchar(32) NOT NULL,
  `artist` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `album` varchar(255) NOT NULL,
  `track_number` varchar(11) NOT NULL,
  `genre` varchar(255) NOT NULL,
  `date` varchar(64) NOT NULL,
  `duration` int(11) NOT NULL,
  `filesize` int(11) NOT NULL,
  `lores` int(11) NOT NULL DEFAULT '1',
  `color` int(11) NOT NULL DEFAULT '0',
  `blocked` int(11) NOT NULL DEFAULT '0',
  `uploaded` int(11) NOT NULL,
  PRIMARY KEY (`tid`),
  KEY `UID` (`uid`),
  KEY `KEYWORD` (`artist`(2)),
  FULLTEXT KEY `FT` (`artist`,`title`,`album`,`genre`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7159 ;

--
-- Тригери `r_tracks`
--
DROP TRIGGER IF EXISTS `ADD_TRACK`;
DELIMITER //
CREATE TRIGGER `ADD_TRACK` AFTER INSERT ON `r_tracks`
 FOR EACH ROW BEGIN
INSERT INTO `r_static_user_vars` SET `user_id` = NEW.`uid`, `tracks_count` = 1, `tracks_duration` = NEW.`duration`, `tracks_size` = NEW.`filesize`
ON DUPLICATE KEY UPDATE `tracks_count` = `tracks_count` + 1, `tracks_duration` = `tracks_duration` + NEW.`duration`,
`tracks_size` = `tracks_size` + NEW.`filesize`;
CALL TRACK_UPDATE(NEW.`uid`);
INSERT INTO `m_events_log` SET
	`user_id` = NEW.`uid`,
    `event_type` = 'TRACK_ADDED',
    `event_target` = NEW.`tid`,
    `event_value` = NULL;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `DELETE_TRACK`;
DELIMITER //
CREATE TRIGGER `DELETE_TRACK` BEFORE DELETE ON `r_tracks`
 FOR EACH ROW BEGIN

UPDATE `r_static_user_vars`
	SET
		`tracks_count` = GREATEST(`tracks_count` - 1, 0),
		`tracks_duration` = GREATEST(`tracks_duration` - OLD.`duration`, 0),
        `tracks_size` = GREATEST(`tracks_size` - OLD.`filesize`, 0)
	WHERE
    	`user_id` = OLD.`uid`;

DELETE FROM `r_link`
	WHERE `track_id` = OLD.`tid`;

CALL TRACK_UPDATE(OLD.`uid`);
DELETE FROM `r_echoprints` WHERE `tid` = OLD.`tid`;

INSERT INTO `m_events_log` SET
	`user_id` = OLD.`uid`,
    `event_type` = 'TRACK_DELETED',
    `event_target` = OLD.`tid`,
    `event_value` = NULL;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `UPDATE_DURATION`;
DELIMITER //
CREATE TRIGGER `UPDATE_DURATION` BEFORE UPDATE ON `r_tracks`
 FOR EACH ROW BEGIN
SET @delta := NEW.`duration` - OLD.`duration`;
IF @delta != 0 THEN
  UPDATE `r_static_user_vars` SET `tracks_duration` = GREATEST(`tracks_duration` + @delta, 0)
  WHERE `user_id` = NEW.`uid`;
END IF;
CALL TRACK_UPDATE(NEW.`uid`);

IF NEW.`lores` != OLD.`lores` THEN
	INSERT INTO `m_events_log` SET
    `user_id` = NEW.`uid`,
    `event_type` = 'LORES_CHANGED',
    `event_target` = NEW.`tid`,
    `event_value` = NEW.`lores`;
ELSE
	INSERT INTO `m_events_log` SET
    `user_id` = NEW.`uid`,
    `event_type` = 'TRACK_INFO_CHANGED',
    `event_target` = NEW.`tid`,
    `event_value` = NULL;
END IF;

END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_users`
--

CREATE TABLE IF NOT EXISTS `r_users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `mail` varchar(128) NOT NULL,
  `login` varchar(32) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `info` varchar(4096) DEFAULT NULL,
  `rights` int(11) NOT NULL DEFAULT '0',
  `register_date` int(10) unsigned NOT NULL,
  `last_visit_date` int(10) unsigned DEFAULT NULL,
  `permalink` varchar(255) NOT NULL,
  `hasavatar` tinyint(1) NOT NULL DEFAULT '0',
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `UNIQUE_EMAIL` (`mail`),
  UNIQUE KEY `UNIQUE_LOGIN` (`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=74 ;

--
-- Обмеження зовнішнього ключа збережених таблиць
--

--
-- Обмеження зовнішнього ключа таблиці `r_link`
--
ALTER TABLE `r_link`
  ADD CONSTRAINT `r_link_ibfk_1` FOREIGN KEY (`stream_id`) REFERENCES `r_streams` (`sid`),
  ADD CONSTRAINT `r_link_ibfk_2` FOREIGN KEY (`track_id`) REFERENCES `r_tracks` (`tid`);

--
-- Обмеження зовнішнього ключа таблиці `r_streams`
--
ALTER TABLE `r_streams`
  ADD CONSTRAINT `r_streams_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `r_users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Обмеження зовнішнього ключа таблиці `r_tracks`
--
ALTER TABLE `r_tracks`
  ADD CONSTRAINT `r_tracks_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `r_users` (`uid`);

DELIMITER $$
--
-- Події
--
CREATE DEFINER=`admin`@`localhost` EVENT `LISTENERS` ON SCHEDULE EVERY 5 MINUTE STARTS '2014-07-27 15:39:32' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
CALL moveListenerToLogShedule();
DELETE FROM `m_events_log` WHERE TIMESTAMPDIFF(MINUTE, `event_time`, NOW()) > 5;
DELETE FROM `r_static_listeners_count` WHERE `listeners_count` = 0;
END$$

CREATE DEFINER=`admin`@`localhost` EVENT `rotateDailyListenersLog` ON SCHEDULE EVERY 1 DAY STARTS '2014-09-08 23:59:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
INSERT INTO `r_listener_stats_daily` (`date`, `stream_id`, `listeners`, `average_listening`)
(SELECT NOW(), t1.`sid`, IFNULL(t2.`listeners`, 0) , IFNULL(t2.`duration`, 0)
FROM `r_streams` t1 LEFT JOIN (SELECT COUNT(*) as `listeners`, `stream_id`, AVG(`duration`) as `duration` FROM `r_listener_log` WHERE `started` >= NOW() - INTERVAL 1 DAY GROUP BY `stream_id`) t2 ON t1.`sid` = t2.`stream_id`);
END$$

DELIMITER ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
