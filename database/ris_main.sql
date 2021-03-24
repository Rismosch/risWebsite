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
CREATE TABLE IF NOT EXISTS `Articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(256) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `link` varchar(256) DEFAULT NULL,
  `thumbnail_path` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `Article_Categories`;
CREATE TABLE IF NOT EXISTS `Article_Categories` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `Article_Categories` (`id`, `name`) VALUES
(1, 'Music'),
(2, 'Programming'),
(99, 'Other');

DROP TABLE IF EXISTS `Article_Types`;
CREATE TABLE IF NOT EXISTS `Article_Types` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `Article_Types` (`id`, `name`) VALUES
(0, 'Blog'),
(1, 'Project');

DROP TABLE IF EXISTS `Emails`;
CREATE TABLE IF NOT EXISTS `Emails` (
  `id` varchar(32) NOT NULL,
  `email` varchar(320) NOT NULL,
  `confirmed` int(11) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `Articles`
  ADD CONSTRAINT `Articles_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `Article_Types` (`id`),
  ADD CONSTRAINT `Articles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `Article_Categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
