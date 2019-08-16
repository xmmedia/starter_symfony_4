-- DB Creation commands
-- Server version: 5.7.27
-- PHP Version: 7.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `flcdb_main`
--

-- --------------------------------------------------------

--
-- Table structure for table `command_log`
--

CREATE TABLE `command_log` (
    `no`         bigint(20)                       NOT NULL,
    `command_id` char(36) COLLATE utf8mb4_bin     NOT NULL,
    `command`    varchar(100) COLLATE utf8mb4_bin NOT NULL,
    `payload`    json                             NOT NULL,
    `metadata`   json                             NOT NULL,
    `sent_at`    datetime(6)                      NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `event_streams`
--

CREATE TABLE `event_streams` (
    `no`               bigint(20)                    NOT NULL,
    `real_stream_name` varchar(150) COLLATE utf8_bin NOT NULL,
    `stream_name`      char(41) COLLATE utf8_bin     NOT NULL,
    `metadata`         json                          DEFAULT NULL,
    `category`         varchar(150) COLLATE utf8_bin DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `projections`
--

CREATE TABLE `projections` (
    `no`           bigint(20)                    NOT NULL,
    `name`         varchar(150) COLLATE utf8_bin NOT NULL,
    `position`     json                      DEFAULT NULL,
    `state`        json                      DEFAULT NULL,
    `status`       varchar(28) COLLATE utf8_bin  NOT NULL,
    `locked_until` char(26) COLLATE utf8_bin DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `command_log`
--
ALTER TABLE `command_log`
    ADD PRIMARY KEY (`no`),
    ADD UNIQUE KEY `command_id` (`command_id`);

--
-- Indexes for table `event_streams`
--
ALTER TABLE `event_streams`
    ADD PRIMARY KEY (`no`),
    ADD UNIQUE KEY `ix_rsn` (`real_stream_name`),
    ADD KEY `ix_cat` (`category`);

--
-- Indexes for table `projections`
--
ALTER TABLE `projections`
    ADD PRIMARY KEY (`no`),
    ADD UNIQUE KEY `ix_name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `command_log`
--
ALTER TABLE `command_log`
    MODIFY `no` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_streams`
--
ALTER TABLE `event_streams`
    MODIFY `no` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projections`
--
ALTER TABLE `projections`
    MODIFY `no` bigint(20) NOT NULL AUTO_INCREMENT;
COMMIT;
