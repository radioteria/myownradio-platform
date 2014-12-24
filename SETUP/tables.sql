SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База даних: `myownradio`
--
CREATE DATABASE IF NOT EXISTS `myownradio`
  DEFAULT CHARACTER SET utf8
  COLLATE utf8_general_ci;
USE `myownradio`;

DELIMITER $$
--
-- Процедури
--
CREATE DEFINER =`admin`@`localhost` PROCEDURE `GET_LAST_REVISION`(IN `module` VARCHAR(64))
NO SQL
  BEGIN
    SELECT
      `html`
    INTO @html
    FROM `r_modules`
    WHERE `html` IS NOT NULL AND `name` = module
    ORDER BY `revision` DESC
    LIMIT 1;
    SELECT
      `css`
    INTO @css
    FROM `r_modules`
    WHERE `css` IS NOT NULL AND `name` = module
    ORDER BY `revision` DESC
    LIMIT 1;
    SELECT
      `js`
    INTO @js
    FROM `r_modules`
    WHERE `js` IS NOT NULL AND `name` = module
    ORDER BY `revision` DESC
    LIMIT 1;
    SELECT
      `tmpl`
    INTO @tmpl
    FROM `r_modules`
    WHERE `tmpl` IS NOT NULL AND `name` = module
    ORDER BY `revision` DESC
    LIMIT 1;
    SELECT
      @html AS `html`,
      @css  AS `css`,
      @js   AS `js`,
      @tmpl AS `tmpl`;
  END$$

CREATE DEFINER =`admin`@`localhost` PROCEDURE `moveListenerToLog`(IN `listener` VARCHAR(32))
NO SQL
  BEGIN
    INSERT INTO `r_listener_log`
    (`ip`, `unique_id`, `stream_id`, `started`, `duration`, `bitrate`)
      SELECT
        `client_ip`,
        `listener_id`,
        `stream_id`,
        `connected_at`,
        `listening_time`,
        `bitrate`
      FROM `r_listener`
      WHERE `listener_id` = listener;
    DELETE FROM `r_listener`
    WHERE `listener_id` = listener;
  END$$

CREATE DEFINER =`admin`@`localhost` PROCEDURE `moveListenerToLogShedule`()
NO SQL
  BEGIN
    INSERT INTO `r_listener_log`
    (`ip`, `unique_id`, `stream_id`, `started`, `duration`, `bitrate`)
      SELECT
        `client_ip`,
        `listener_id`,
        `stream_id`,
        `connected_at`,
        `listening_time`,
        `bitrate`
      FROM `r_listener`
      WHERE UNIX_TIMESTAMP(NOW()) - `last_activity` > 30;
    DELETE FROM `r_listener`
    WHERE UNIX_TIMESTAMP(NOW()) - `last_activity` > 30;
  END$$

CREATE DEFINER =`admin`@`localhost` PROCEDURE `PLAN_CHANGE_EVENT`(IN `user_id` INT)
NO SQL
  BEGIN
    SELECT
      `plan`
    INTO @plan
    FROM `r_subscriptions`
    WHERE
      `uid` = user_id AND
      `expire` > UNIX_TIMESTAMP(NOW())
    ORDER BY `expire` DESC
    LIMIT 1;

    IF (@plan = NULL)
    THEN
      SET @plan := 0;
    END IF;

    INSERT INTO `m_events_log`
    SET
      `user_id`      = user_id,
      `event_type`   = 'PLAN_CHANGED',
      `event_target` = @plan,
      `event_value`  = NULL;
  END$$

CREATE DEFINER =`admin`@`localhost` PROCEDURE `p_optimize_stream`(IN `s_id` INT)
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

CREATE DEFINER =`admin`@`localhost` PROCEDURE `P_SHUFFLE_STREAM`(IN `s_id` INT)
NO SQL
  BEGIN
    SET @row := 0;

    UPDATE `r_link`
    SET
      `t_order` = @row := @row + 1
    WHERE
      `stream_id` = s_id
    ORDER BY RAND();

    SELECT
      `uid`
    INTO @myID
    FROM `r_streams`
    WHERE `sid` = s_id
    LIMIT 1;

    INSERT INTO `m_events_log`
    SET
      `user_id`      = @myID,
      `event_type`   = "STREAM_SORTED",
      `event_target` = s_id,
      `event_value`  = NULL;

  END$$

CREATE DEFINER =`admin`@`localhost` PROCEDURE `serverListenersRotate`()
NO SQL
  BEGIN

    INSERT INTO `r_listener_stats` (`date`, `stream_id`, `listeners`)
      SELECT
        NOW(),
        `stream_id`,
        COUNT(DISTINCT `listener_id`)
      FROM `r_listener`
      WHERE 1
      GROUP BY `stream_id`;

  END$$

CREATE DEFINER =`admin`@`localhost` PROCEDURE `TRACK_UPDATE`(IN `uid` INT)
NO SQL
  INSERT INTO `m_track_update`
  SET `user_id` = uid, `changed` = UNIX_TIMESTAMP(NOW())
  ON DUPLICATE KEY UPDATE `changed` = UNIX_TIMESTAMP(NOW())$$

--
-- Функції
--
CREATE DEFINER =`admin`@`localhost` FUNCTION `NEW_STREAM_SORT`(`s_id` INT, `s_target` VARCHAR(16), `s_index` INT)
  RETURNS INT(11)
NO SQL
  BEGIN

    SELECT
      `t_order`,
      `track_id`
    INTO @order, @id
    FROM `r_link`
    WHERE `unique_id` = s_target;

    IF (@order = s_index)
    THEN
      RETURN NULL;
    END IF;

    SELECT
      `duration`
    INTO @duration
    FROM `r_tracks`
    WHERE `tid` = @id;

    IF (s_index > @order)
    THEN
      UPDATE `r_link`
      SET `t_order` = `t_order` - 1, `time_offset` = `time_offset` - @duration
      WHERE `t_order` > @order AND `t_order` <= s_index AND `stream_id` = s_id
      ORDER BY `t_order` ASC;

      SELECT
        `time_offset`,
        `track_id`
      INTO @tmpOffset, @tmpTrackId
      FROM `r_link`
      WHERE `t_order` = s_index - 1 AND `stream_id` = s_id
      LIMIT 1;

      SELECT
        `duration`
      INTO @tmpDuration
      FROM `r_tracks`
      WHERE `tid` = @tmpTrackId;

      SET @newOffset := @tmpOffset + @tmpDuration;
    ELSE
      SELECT
        `time_offset`
      INTO @newOffset
      FROM `r_link`
      WHERE `t_order` = s_index AND `stream_id` = s_id;
      UPDATE `r_link`
      SET `t_order` = `t_order` + 1, `time_offset` = `time_offset` + @duration
      WHERE `t_order` >= s_index AND `t_order` < @order AND `stream_id` = s_id
      ORDER BY `t_order` ASC;
    END IF;

    UPDATE `r_link`
    SET `t_order` = s_index, `time_offset` = @newOffset
    WHERE `unique_id` = s_target;

    RETURN 1;
  END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `m_events_log`
--

CREATE TABLE IF NOT EXISTS `m_events_log` (
  `event_id`     INT(11)                                                                                                                                                                                                                                                                                                               NOT NULL AUTO_INCREMENT,
  `user_id`      INT(11)                                                                                                                                                                                                                                                                                                               NOT NULL,
  `event_type`   ENUM('LORES_CHANGED', 'TRACK_ADDED', 'TRACK_DELETED', 'STREAM_ADDED', 'STREAM_DELETED', 'TRACK_INFO_CHANGED', 'STREAM_TRACKS_CHANGED', 'PLAN_CHANGED', 'STREAM_TRACK_ADDED', 'STREAM_TRACK_DELETED', 'STREAM_SET_CURRENT', 'STREAM_SORT', 'TOKEN_REMOVED', 'LIB_DURATION_CHANGED', 'STREAM_SORTED', 'STREAM_UPDATED') NOT NULL,
  `event_target` VARCHAR(64)                                                                                                                                                                                                                                                                                                           NOT NULL,
  `event_value`  VARCHAR(1024) DEFAULT NULL,
  `event_time`   TIMESTAMP                                                                                                                                                                                                                                                                                                             NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`)
)
  ENGINE = MEMORY
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =37;

-- --------------------------------------------------------

--
-- Структура таблиці `m_stream_update`
--

CREATE TABLE IF NOT EXISTS `m_stream_update` (
  `user_id` INT(11) NOT NULL,
  `changed` INT(11) NOT NULL,
  PRIMARY KEY (`user_id`)
)
  ENGINE = MEMORY
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `m_track_update`
--

CREATE TABLE IF NOT EXISTS `m_track_update` (
  `user_id` INT(11) NOT NULL,
  `changed` INT(11) NOT NULL,
  PRIMARY KEY (`user_id`)
)
  ENGINE = MEMORY
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `r_categories`
--

CREATE TABLE IF NOT EXISTS `r_categories` (
  `id`        INT(11)      NOT NULL AUTO_INCREMENT,
  `name`      VARCHAR(255) NOT NULL,
  `permalink` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =15;

-- --------------------------------------------------------

--
-- Структура таблиці `r_echoprints`
--

CREATE TABLE IF NOT EXISTS `r_echoprints` (
  `tid`       INT(11)       NOT NULL,
  `echoprint` VARCHAR(4096) NOT NULL,
  PRIMARY KEY (`tid`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `r_genres`
--

CREATE TABLE IF NOT EXISTS `r_genres` (
  `id`    INT(11)      NOT NULL AUTO_INCREMENT,
  `genre` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `FT` (`genre`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1528;

-- --------------------------------------------------------

--
-- Структура таблиці `r_hashtags`
--

CREATE TABLE IF NOT EXISTS `r_hashtags` (
  `id`   INT(11)     NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(32) NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =5;

-- --------------------------------------------------------

--
-- Структура таблиці `r_limitations`
--

CREATE TABLE IF NOT EXISTS `r_limitations` (
  `level`        INT(11)        NOT NULL,
  `name`         VARCHAR(64)    NOT NULL,
  `upload_limit` BIGINT(20)     NOT NULL,
  `streams_max`  INT(11)        NOT NULL,
  `price`        DECIMAL(11, 2) NOT NULL,
  UNIQUE KEY `LEVEL` (`level`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `r_link`
--

CREATE TABLE IF NOT EXISTS `r_link` (
  `id`          BIGINT(20) NOT NULL AUTO_INCREMENT,
  `stream_id`   INT(11)    NOT NULL,
  `track_id`    INT(11)    NOT NULL,
  `t_order`     INT(11)    NOT NULL,
  `unique_id`   VARCHAR(8) NOT NULL,
  `time_offset` BIGINT(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ID` (`unique_id`),
  KEY `STREAM` (`stream_id`),
  KEY `TRACK` (`track_id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =3529;

--
-- Тригери `r_link`
--
DROP TRIGGER IF EXISTS `STREAM_ADD_TRACK`;
DELIMITER //
CREATE TRIGGER `STREAM_ADD_TRACK` AFTER INSERT ON `r_link`
FOR EACH ROW BEGIN

  SELECT
    `duration`
  INTO @duration
  FROM `r_tracks`
  WHERE `tid` = NEW.`track_id`;

  INSERT INTO `r_static_stream_vars`
  SET `stream_id` = NEW.`stream_id`, `tracks_count` = 1, `tracks_duration` = @duration
  ON DUPLICATE KEY UPDATE `tracks_count` = `tracks_count` + 1, `tracks_duration` = `tracks_duration` + @duration;

END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `STREAM_REMOVE_TRACK`;
DELIMITER //
CREATE TRIGGER `STREAM_REMOVE_TRACK` BEFORE DELETE ON `r_link`
FOR EACH ROW BEGIN
  SELECT
    `duration`
  INTO @duration
  FROM `r_tracks`
  WHERE `tid` = OLD.`track_id`;
  UPDATE `r_static_stream_vars`
  SET `tracks_count` = GREATEST(`tracks_count` - 1, 0), `tracks_duration` = GREATEST(`tracks_duration` - @duration, 0)
  WHERE `stream_id` = OLD.`stream_id`;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_listener`
--

CREATE TABLE IF NOT EXISTS `r_listener` (
  `listener_id`    VARCHAR(32)  NOT NULL,
  `client_ip`      VARCHAR(15)  NOT NULL,
  `client_ua`      VARCHAR(255) NOT NULL,
  `stream_id`      INT(11)      NOT NULL,
  `bitrate`        INT(11)      NOT NULL,
  `last_activity`  INT(11)      NOT NULL,
  `listening_time` INT(11)      NOT NULL,
  `connected_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`listener_id`),
  KEY `STREAM` (`stream_id`)
)
  ENGINE = MEMORY
  DEFAULT CHARSET =utf8;

--
-- Тригери `r_listener`
--
DROP TRIGGER IF EXISTS `LISTENER_IN`;
DELIMITER //
CREATE TRIGGER `LISTENER_IN` AFTER INSERT ON `r_listener`
FOR EACH ROW BEGIN
  INSERT INTO `r_static_stream_vars`
  SET `stream_id` = NEW.`stream_id`, `listeners_count` = 1
  ON DUPLICATE KEY UPDATE `listeners_count` = `listeners_count` + 1;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `LISTENER_OUT`;
DELIMITER //
CREATE TRIGGER `LISTENER_OUT` BEFORE DELETE ON `r_listener`
FOR EACH ROW BEGIN
  UPDATE `r_static_stream_vars`
  SET `listeners_count` = GREATEST(`listeners_count` - 1, 0)
  WHERE `stream_id` = OLD.`stream_id`;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_listener_log`
--

CREATE TABLE IF NOT EXISTS `r_listener_log` (
  `id`        INT(11)     NOT NULL AUTO_INCREMENT,
  `ip`        VARCHAR(15) NOT NULL,
  `unique_id` VARCHAR(32) NOT NULL,
  `stream_id` INT(11)     NOT NULL,
  `started`   TIMESTAMP   NOT NULL DEFAULT '0000-00-00 00:00:00',
  `duration`  INT(11)     NOT NULL,
  `bitrate`   INT(11)     NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =500;

-- --------------------------------------------------------

--
-- Структура таблиці `r_listener_stats`
--

CREATE TABLE IF NOT EXISTS `r_listener_stats` (
  `id`        INT(11)   NOT NULL AUTO_INCREMENT,
  `date`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `stream_id` INT(11)   NOT NULL,
  `listeners` INT(11)   NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1;

-- --------------------------------------------------------

--
-- Структура таблиці `r_listener_stats_daily`
--

CREATE TABLE IF NOT EXISTS `r_listener_stats_daily` (
  `id`                INT(11)   NOT NULL AUTO_INCREMENT,
  `date`              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `stream_id`         INT(11)   NOT NULL,
  `listeners`         INT(11)   NOT NULL,
  `average_listening` INT(11)   NOT NULL,
  PRIMARY KEY (`id`),
  KEY `stream_id` (`stream_id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1394;

-- --------------------------------------------------------

--
-- Структура таблиці `r_modules`
--

CREATE TABLE IF NOT EXISTS `r_modules` (
  `name`     VARCHAR(64)  NOT NULL,
  `html`     TEXT,
  `css`      TEXT,
  `js`       TEXT,
  `tmpl`     TEXT,
  `post`     TEXT         NOT NULL,
  `uid`      INT(11)      NOT NULL DEFAULT '1',
  `modified` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `alias`    VARCHAR(255) NOT NULL,
  PRIMARY KEY (`name`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `r_now_playing`
--

CREATE TABLE IF NOT EXISTS `r_now_playing` (
  `stream_id` INT(11)    NOT NULL,
  `unique_id` VARCHAR(8) NOT NULL,
  PRIMARY KEY (`stream_id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `r_server_stats`
--

CREATE TABLE IF NOT EXISTS `r_server_stats` (
  `run_id`               INT(11)    NOT NULL AUTO_INCREMENT,
  `tracks_played`        BIGINT(20) NOT NULL,
  `server_bytes_decoded` BIGINT(20) NOT NULL,
  `server_bytes_encoded` BIGINT(20) NOT NULL,
  `client_bytes_sent`    BIGINT(20) NOT NULL,
  `clients_total`        BIGINT(20) NOT NULL,
  `clients_5min`         BIGINT(20) NOT NULL,
  `jingle_streamings`    BIGINT(20) NOT NULL,
  `jingle_playbacks`     BIGINT(20) NOT NULL,
  `uptime_seconds`       BIGINT(20) NOT NULL,
  PRIMARY KEY (`run_id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1;

-- --------------------------------------------------------

--
-- Структура таблиці `r_sessions`
--

CREATE TABLE IF NOT EXISTS `r_sessions` (
  `uid`             INT(11)       NOT NULL,
  `ip`              VARCHAR(15)   NOT NULL,
  `token`           VARCHAR(32)   NOT NULL,
  `authorized`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `http_user_agent` VARCHAR(4096) NOT NULL,
  `session_id`      VARCHAR(255)  NOT NULL,
  `permanent`       TINYINT(1)    NOT NULL DEFAULT '1',
  `expires`         TIMESTAMP     NULL DEFAULT NULL,
  PRIMARY KEY (`token`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

--
-- Тригери `r_sessions`
--
DROP TRIGGER IF EXISTS `TOKEN REMOVE`;
DELIMITER //
CREATE TRIGGER `TOKEN REMOVE` BEFORE DELETE ON `r_sessions`
FOR EACH ROW INSERT INTO `m_events_log`
SET
  `user_id`      = OLD.`uid`,
  `event_type`   = 'TOKEN_REMOVED',
  `event_target` = OLD.`uid`,
  `event_value`  = OLD.`token`
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_static_stream_vars`
--

CREATE TABLE IF NOT EXISTS `r_static_stream_vars` (
  `stream_id`       INT(11)    NOT NULL,
  `tracks_count`    INT(11)    NOT NULL DEFAULT '0',
  `tracks_duration` BIGINT(20) NOT NULL DEFAULT '0',
  `listeners_count` INT(11)    NOT NULL DEFAULT '0',
  PRIMARY KEY (`stream_id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `r_static_user_vars`
--

CREATE TABLE IF NOT EXISTS `r_static_user_vars` (
  `user_id`         INT(11)    NOT NULL,
  `tracks_count`    INT(11)    NOT NULL,
  `tracks_duration` BIGINT(20) NOT NULL,
  `tracks_size`     BIGINT(20) NOT NULL,
  PRIMARY KEY (`user_id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

--
-- Тригери `r_static_user_vars`
--
DROP TRIGGER IF EXISTS `CHANGED`;
DELIMITER //
CREATE TRIGGER `CHANGED` AFTER UPDATE ON `r_static_user_vars`
FOR EACH ROW INSERT INTO `m_events_log`
SET
  `user_id`      = NEW.`user_id`,
  `event_type`   = 'LIB_DURATION_CHANGED',
  `event_target` = NEW.`user_id`,
  `event_value`  = NEW.`tracks_duration`
//
DELIMITER ;
DROP TRIGGER IF EXISTS `NEW_ADDED`;
DELIMITER //
CREATE TRIGGER `NEW_ADDED` AFTER INSERT ON `r_static_user_vars`
FOR EACH ROW INSERT INTO `m_events_log`
SET
  `user_id`      = NEW.`user_id`,
  `event_type`   = 'LIB_DURATION_CHANGED',
  `event_target` = NEW.`user_id`,
  `event_value`  = NEW.`tracks_duration`
//
DELIMITER ;
DROP TRIGGER IF EXISTS `OLD_REMOVED`;
DELIMITER //
CREATE TRIGGER `OLD_REMOVED` BEFORE DELETE ON `r_static_user_vars`
FOR EACH ROW INSERT INTO `m_events_log`
SET
  `user_id`      = OLD.`user_id`,
  `event_type`   = 'LIB_DURATION_CHANGED',
  `event_target` = OLD.`user_id`,
  `event_value`  = 0
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_stats_memory`
--

CREATE TABLE IF NOT EXISTS `r_stats_memory` (
  `id`        INT(11)       NOT NULL AUTO_INCREMENT,
  `ip`        VARCHAR(15)   NOT NULL,
  `uid`       INT(11)       NOT NULL,
  `uri`       VARCHAR(4096) NOT NULL,
  `referer`   VARCHAR(4096) NOT NULL,
  `useragent` VARCHAR(4096) NOT NULL,
  `date`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1;

-- --------------------------------------------------------

--
-- Структура таблиці `r_streams`
--

CREATE TABLE IF NOT EXISTS `r_streams` (
  `sid`          INT(11)                               NOT NULL AUTO_INCREMENT,
  `uid`          INT(11)                               NOT NULL,
  `name`         VARCHAR(32)                           NOT NULL,
  `permalink`    VARCHAR(255)                          NOT NULL,
  `info`         VARCHAR(4096)                         NOT NULL DEFAULT '',
  `status`       INT(11)                               NOT NULL DEFAULT '0',
  `started`      BIGINT(20)                            NOT NULL,
  `started_from` BIGINT(20)                            NOT NULL,
  `access`       ENUM('PUBLIC', 'UNLISTED', 'PRIVATE') NOT NULL DEFAULT 'PUBLIC',
  `category`     INT(11)                               NOT NULL DEFAULT '13',
  `hashtags`     VARCHAR(4096)                         NOT NULL,
  `cover`        VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`sid`),
  KEY `UID` (`uid`),
  FULLTEXT KEY `TAGS` (`hashtags`),
  FULLTEXT KEY `FT` (`name`, `permalink`, `hashtags`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =54;

--
-- Тригери `r_streams`
--
DROP TRIGGER IF EXISTS `STREAM_ADD`;
DELIMITER //
CREATE TRIGGER `STREAM_ADD` AFTER INSERT ON `r_streams`
FOR EACH ROW BEGIN
  INSERT INTO `m_events_log`
  SET
    `user_id`      = NEW.`uid`,
    `event_type`   = "STREAM_ADDED",
    `event_target` = NEW.`sid`,
    `event_value`  = NULL;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `STREAM_DEL`;
DELIMITER //
CREATE TRIGGER `STREAM_DEL` BEFORE DELETE ON `r_streams`
FOR EACH ROW BEGIN
  DELETE FROM `r_link`
  WHERE `stream_id` = OLD.`sid`;
  DELETE FROM `r_static_stream_vars`
  WHERE `stream_id` = OLD.`sid`;
  INSERT INTO `m_events_log`
  SET
    `user_id`      = OLD.`uid`,
    `event_type`   = "STREAM_DELETED",
    `event_target` = OLD.`sid`,
    `event_value`  = NULL;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `STREAM_UPD`;
DELIMITER //
CREATE TRIGGER `STREAM_UPD` AFTER UPDATE ON `r_streams`
FOR EACH ROW BEGIN
  INSERT INTO `m_events_log`
  SET
    `user_id`      = OLD.`uid`,
    `event_type`   = "STREAM_UPDATED",
    `event_target` = OLD.`sid`,
    `event_value`  = NULL;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_subscriptions`
--

CREATE TABLE IF NOT EXISTS `r_subscriptions` (
  `id`           INT(11)       NOT NULL AUTO_INCREMENT,
  `uid`          INT(11)       NOT NULL,
  `plan`         INT(11)       NOT NULL,
  `payment_info` VARCHAR(4096) NOT NULL,
  `expire`       INT(11)       NOT NULL,
  PRIMARY KEY (`id`),
  KEY `USER` (`uid`),
  KEY `PLAN` (`plan`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =6;

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
  `key`       VARCHAR(64)  NOT NULL,
  `unique_id` VARCHAR(16)  NOT NULL,
  `track_id`  INT(11)      NOT NULL,
  `title`     VARCHAR(255) NOT NULL,
  `started`   BIGINT(20)   NOT NULL,
  `duration`  BIGINT(20)   NOT NULL,
  PRIMARY KEY (`key`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `r_tracks`
--

CREATE TABLE IF NOT EXISTS `r_tracks` (
  `tid`          INT(11)      NOT NULL AUTO_INCREMENT,
  `uid`          INT(11)      NOT NULL,
  `filename`     VARCHAR(255) NOT NULL,
  `ext`          VARCHAR(32)  NOT NULL,
  `artist`       VARCHAR(255) NOT NULL,
  `title`        VARCHAR(255) NOT NULL,
  `album`        VARCHAR(255) NOT NULL,
  `track_number` VARCHAR(11)  NOT NULL,
  `genre`        VARCHAR(255) NOT NULL,
  `date`         VARCHAR(64)  NOT NULL,
  `duration`     INT(11)      NOT NULL,
  `filesize`     INT(11)      NOT NULL,
  `lores`        INT(11)      NOT NULL DEFAULT '1',
  `color`        INT(11)      NOT NULL DEFAULT '0',
  `blocked`      INT(11)      NOT NULL DEFAULT '0',
  `uploaded`     INT(11)      NOT NULL,
  PRIMARY KEY (`tid`),
  KEY `UID` (`uid`),
  KEY `KEYWORD` (`artist`(2)),
  FULLTEXT KEY `FT` (`artist`, `title`, `album`, `genre`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =7159;

--
-- Тригери `r_tracks`
--
DROP TRIGGER IF EXISTS `ADD_TRACK`;
DELIMITER //
CREATE TRIGGER `ADD_TRACK` AFTER INSERT ON `r_tracks`
FOR EACH ROW BEGIN
  INSERT INTO `r_static_user_vars`
  SET `user_id` = NEW.`uid`, `tracks_count` = 1, `tracks_duration` = NEW.`duration`, `tracks_size` = NEW.`filesize`
  ON DUPLICATE KEY UPDATE `tracks_count` = `tracks_count` + 1, `tracks_duration` = `tracks_duration` + NEW.`duration`,
    `tracks_size`                        = `tracks_size` + NEW.`filesize`;
  CALL TRACK_UPDATE(NEW.`uid`);
  INSERT INTO `m_events_log`
  SET
    `user_id`      = NEW.`uid`,
    `event_type`   = 'TRACK_ADDED',
    `event_target` = NEW.`tid`,
    `event_value`  = NULL;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `DELETE_TRACK`;
DELIMITER //
CREATE TRIGGER `DELETE_TRACK` BEFORE DELETE ON `r_tracks`
FOR EACH ROW BEGIN

  UPDATE `r_static_user_vars`
  SET
    `tracks_count`    = GREATEST(`tracks_count` - 1, 0),
    `tracks_duration` = GREATEST(`tracks_duration` - OLD.`duration`, 0),
    `tracks_size`     = GREATEST(`tracks_size` - OLD.`filesize`, 0)
  WHERE
    `user_id` = OLD.`uid`;

  DELETE FROM `r_link`
  WHERE `track_id` = OLD.`tid`;

  CALL TRACK_UPDATE(OLD.`uid`);
  DELETE FROM `r_echoprints`
  WHERE `tid` = OLD.`tid`;

  INSERT INTO `m_events_log`
  SET
    `user_id`      = OLD.`uid`,
    `event_type`   = 'TRACK_DELETED',
    `event_target` = OLD.`tid`,
    `event_value`  = NULL;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `UPDATE_DURATION`;
DELIMITER //
CREATE TRIGGER `UPDATE_DURATION` BEFORE UPDATE ON `r_tracks`
FOR EACH ROW BEGIN
  SET @delta := NEW.`duration` - OLD.`duration`;
  IF @delta != 0
  THEN
    UPDATE `r_static_user_vars`
    SET `tracks_duration` = GREATEST(`tracks_duration` + @delta, 0)
    WHERE `user_id` = NEW.`uid`;
  END IF;
  CALL TRACK_UPDATE(NEW.`uid`);

  IF NEW.`lores` != OLD.`lores`
  THEN
    INSERT INTO `m_events_log`
    SET
      `user_id`      = NEW.`uid`,
      `event_type`   = 'LORES_CHANGED',
      `event_target` = NEW.`tid`,
      `event_value`  = NEW.`lores`;
  ELSE
    INSERT INTO `m_events_log`
    SET
      `user_id`      = NEW.`uid`,
      `event_type`   = 'TRACK_INFO_CHANGED',
      `event_target` = NEW.`tid`,
      `event_value`  = NULL;
  END IF;

END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблиці `r_users`
--

CREATE TABLE IF NOT EXISTS `r_users` (
  `uid`             INT(11)          NOT NULL AUTO_INCREMENT,
  `mail`            VARCHAR(128)     NOT NULL,
  `login`           VARCHAR(32) DEFAULT NULL,
  `password`        VARCHAR(32) DEFAULT NULL,
  `name`            VARCHAR(255) DEFAULT NULL,
  `info`            VARCHAR(4096) DEFAULT NULL,
  `rights`          INT(11)          NOT NULL DEFAULT '0',
  `register_date`   INT(10) UNSIGNED NOT NULL,
  `last_visit_date` INT(10) UNSIGNED DEFAULT NULL,
  `permalink`       VARCHAR(255)     NOT NULL,
  `hasavatar`       TINYINT(1)       NOT NULL DEFAULT '0',
  `avatar`          VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `UNIQUE_EMAIL` (`mail`),
  UNIQUE KEY `UNIQUE_LOGIN` (`login`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =74;

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
ADD CONSTRAINT `r_streams_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `r_users` (`uid`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

--
-- Обмеження зовнішнього ключа таблиці `r_tracks`
--
ALTER TABLE `r_tracks`
ADD CONSTRAINT `r_tracks_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `r_users` (`uid`);

DELIMITER $$
--
-- Події
--
CREATE DEFINER =`admin`@`localhost` EVENT `LISTENERS`
  ON SCHEDULE EVERY 5 MINUTE STARTS '2014-07-27 15:39:32'
  ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    CALL moveListenerToLogShedule();
    DELETE FROM `m_events_log`
    WHERE TIMESTAMPDIFF(MINUTE, `event_time`, NOW()) > 5;
    DELETE FROM `r_static_listeners_count`
    WHERE `listeners_count` = 0;
  END$$

CREATE DEFINER =`admin`@`localhost` EVENT `rotateDailyListenersLog`
  ON SCHEDULE EVERY 1 DAY STARTS '2014-09-08 23:59:00'
  ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
  INSERT INTO `r_listener_stats_daily` (`date`, `stream_id`, `listeners`, `average_listening`)
    (SELECT
       NOW(),
       t1.`sid`,
       IFNULL(t2.`listeners`, 0),
       IFNULL(t2.`duration`, 0)
     FROM `r_streams` t1 LEFT JOIN (SELECT
                                      COUNT(*)        AS `listeners`,
                                      `stream_id`,
                                      AVG(`duration`) AS `duration`
                                    FROM `r_listener_log`
                                    WHERE `started` >= NOW() - INTERVAL 1 DAY
                                    GROUP BY `stream_id`) t2 ON t1.`sid` = t2.`stream_id`);
END$$

DELIMITER ;

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
