SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `ris_main` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `ris_main`;

DROP TABLE IF EXISTS `Articles`;
CREATE TABLE `Articles` (
  `id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(256) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `link` varchar(256) DEFAULT NULL,
  `thumbnail_path` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `Article_Categories`;
CREATE TABLE `Article_Categories` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `Article_Types`;
CREATE TABLE `Article_Types` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `Articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `category_id` (`category_id`);

ALTER TABLE `Article_Categories`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `Article_Types`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `Articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `Articles`
  ADD CONSTRAINT `Articles_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `Article_Types` (`id`),
  ADD CONSTRAINT `Articles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `Article_Categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
