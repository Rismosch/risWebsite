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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

INSERT INTO `Articles` (`id`, `type_id`, `category_id`, `title`, `timestamp`, `link`, `thumbnail_path`) VALUES
(9, 1, 1, 'The Land Behind The Waves', '2019-08-09 07:00:00', 'https://rismosch.bandcamp.com/album/the-land-behind-the-waves', 'assets/thumbnails/the_land_behind_the_waves.jpg');

DROP TABLE IF EXISTS `Article_Categories`;
CREATE TABLE IF NOT EXISTS `Article_Categories` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `Article_Categories` (`id`, `name`) VALUES
(1, 'Music'),
(2, 'Programming'),
(3, 'Other');

DROP TABLE IF EXISTS `Article_Types`;
CREATE TABLE IF NOT EXISTS `Article_Types` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `Article_Types` (`id`, `name`) VALUES
(0, 'Blog'),
(1, 'Project');


ALTER TABLE `Articles`
  ADD CONSTRAINT `Articles_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `Article_Types` (`id`),
  ADD CONSTRAINT `Articles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `Article_Categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
