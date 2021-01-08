-- phpMyAdmin SQL Dump
-- version 4.9.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 15. Nov 2020 um 09:42
-- Server-Version: 5.6.49-cll-lve
-- PHP-Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `ris_main`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Articles`
--

CREATE TABLE `Articles` (
  `id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(256) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `Articles`
--

INSERT INTO `Articles` (`id`, `type_id`, `category_id`, `title`, `timestamp`) VALUES
(1, 0, 0, 'my first post', '2020-11-14 20:02:19'),
(2, 0, 0, 'my second post', '2020-11-14 20:04:01'),
(3, 0, 0, 'my third post', '2020-11-14 20:04:01'),
(4, 1, 0, 'my first project', '2020-11-14 20:04:01'),
(5, 1, 0, 'my second project', '2020-11-14 20:04:01'),
(6, 0, 1, 'my music post', '2020-11-14 20:04:01'),
(7, 0, 2, 'my programming post', '2020-11-14 20:04:01'),
(8, 1, 2, 'game engine', '2020-11-14 20:04:01');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Article_Categories`
--

CREATE TABLE `Article_Categories` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `Article_Categories`
--

INSERT INTO `Article_Categories` (`id`, `name`) VALUES
(0, 'other'),
(1, 'music'),
(2, 'programming');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Article_Types`
--

CREATE TABLE `Article_Types` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `Article_Types`
--

INSERT INTO `Article_Types` (`id`, `name`) VALUES
(0, 'blog'),
(1, 'project');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `Articles`
--
ALTER TABLE `Articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indizes für die Tabelle `Article_Categories`
--
ALTER TABLE `Article_Categories`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `Article_Types`
--
ALTER TABLE `Article_Types`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `Articles`
--
ALTER TABLE `Articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `Articles`
--
ALTER TABLE `Articles`
  ADD CONSTRAINT `Articles_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `Article_Types` (`id`),
  ADD CONSTRAINT `Articles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `Article_Categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
