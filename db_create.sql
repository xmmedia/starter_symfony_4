-- DB Creation commands
-- Server version: 5.7.27
-- PHP Version: 7.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `main`
--

-- --------------------------------------------------------

--
-- Table structure for table `command_log`
--

CREATE TABLE `command_log` (
    `no`         bigint(20)                       NOT NULL AUTO_INCREMENT,
    `command_id` char(36) COLLATE utf8mb4_bin     NOT NULL,
    `command`    varchar(100) COLLATE utf8mb4_bin NOT NULL,
    `payload`    json                             NOT NULL,
    `metadata`   json                             NOT NULL,
    `sent_at`    datetime(6)                      NOT NULL,
    PRIMARY KEY (`no`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `event_streams`
--

CREATE TABLE `event_streams` (
    `no`               bigint(20)                    NOT NULL AUTO_INCREMENT,
    `real_stream_name` varchar(150) COLLATE utf8_bin NOT NULL,
    `stream_name`      char(41) COLLATE utf8_bin     NOT NULL,
    `metadata`         json                          DEFAULT NULL,
    `category`         varchar(150) COLLATE utf8_bin DEFAULT NULL,
    PRIMARY KEY (`no`),
    UNIQUE KEY `ix_rsn` (`real_stream_name`),
    KEY `ix_cat` (`category`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `projections`
--

CREATE TABLE `projections` (
    `no`           bigint(20)                    NOT NULL AUTO_INCREMENT,
    `name`         varchar(150) COLLATE utf8_bin NOT NULL,
    `position`     json                      DEFAULT NULL,
    `state`        json                      DEFAULT NULL,
    `status`       varchar(28) COLLATE utf8_bin  NOT NULL,
    `locked_until` char(26) COLLATE utf8_bin DEFAULT NULL,
    PRIMARY KEY (`no`),
    UNIQUE KEY `ix_name` (`name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `user_token`
--

CREATE TABLE `user_token` (
    `user_token_id` char(36) COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:uuid)',
    `user_id` char(36) COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:uuid)',
    `selector` varchar(20) COLLATE utf8mb4_bin NOT NULL,
    `hashed_token` varchar(100) COLLATE utf8mb4_bin NOT NULL,
    `requested_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    PRIMARY KEY (`user_token_id`),
    KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

COMMIT;
