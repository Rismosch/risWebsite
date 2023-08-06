-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 06. Aug 2023 um 13:14
-- Server-Version: 5.6.51-cll-lve
-- PHP-Version: 7.4.33

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
  `id` varchar(256) NOT NULL,
  `type_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(256) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `link` varchar(256) DEFAULT NULL,
  `thumbnail_path` varchar(256) DEFAULT NULL,
  `description` varchar(256) NOT NULL,
  `keywords` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `Articles`
--

INSERT INTO `Articles` (`id`, `type_id`, `category_id`, `title`, `timestamp`, `link`, `thumbnail_path`, `description`, `keywords`) VALUES
('a-neat-trick-to-debug-exceptions-in-c-sharp', 0, 2, 'A neat Trick to Debug Exceptions in C#', '2021-08-24 19:29:22', NULL, NULL, 'Using the when-keyword to better debug exceptions in C#.', 'csharp, exception, debug, try, catch, when'),
('angery', 1, 1, 'Angery', '2021-07-06 18:32:15', 'https://rismosch.bandcamp.com/album/angery', 'assets/thumbnails/angery.webp', '', ''),
('building-a-job-system', 0, 2, 'Building a JobSystem', '2022-10-06 16:46:08', NULL, NULL, 'The latest feature of my gameengine is thread pool, which uses all CPU resources all of the time. In this blogpost I go into the deisgn of this first working prototype.', 'rust, gamedev, gameengine, jobsystem, threadpool, concurrency, multithreading, safe, fast, performance'),
('button-down-up-and-hold', 0, 2, 'How to programm Button Down, Up and Hold with 3 lines of code', '2022-08-01 17:12:56', NULL, NULL, 'In this post I describe a quick and easy solution to compute the single frame, where a button was pressed down and released', 'rust, gamedev, engine, programming, tutorial'),
('crisis', 0, 42, 'Crisis', '2022-04-10 15:52:53', NULL, NULL, 'My project to write a gameengine hit a wall. In this post I go over my struggles and possible ways to overcome them.', 'crisis, reddit, rplace, place, cpp, c++, resharper, unit tests, struggle, rust'),
('css-on-mobile-and-the-most-important-header-tag', 0, 2, 'CSS on Mobile, and The Most Important Header Tag', '2021-04-25 17:19:00', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'Tackling meta-tags. And a solution to make the website responsive, such that it is perfectly usable on any device.', 'css, meta, viewport, media, responsive, mobile'),
('good-enough-is-sometimes-not-good-enough', 0, 42, 'Good enough is sometimes not good enough', '2021-03-07 15:35:15', NULL, NULL, 'My post mortem of the GMTK GameJam 2020, which changed my perspective on how I approach my projects.', 'gamejam, gmtk, unity, 2020, '),
('hosting-a-webserver-and-money', 0, 2, 'Hosting a Webserver and Money', '2021-04-25 17:17:50', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'A brief summary, of what services you need to buy to get a website running.', 'www, godaddy, ssl, http, price, money, hosting, webserver, domain, email'),
('how-i-build-my-website', 0, 2, 'How I Build My Website', '2021-04-25 17:18:42', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'An overview, of how I used HTML, CSS, and Javascript to build my website.', 'devtools, view-source, div, html, css, javascript, pixelart'),
('how-i-made-the-world-between-my-mind-and-reality', 0, 1, 'How I made The World Between My Mind And Reality', '2023-03-10 20:37:48', NULL, 'assets/thumbnails/the_world_between_my_mind_and_reality.webp', 'I describe how I made my latest album. Which instruments I used, how I went about recording vocals and how I made the cover art.', 'DnB, Drum and Bass, EDM, Punk, Arturia Polybrute, Vocals, Lyrics, Singing, FL Studio, DALL E, AI, John Carmack, Brian Caris, Undertale'),
('html-css-and-javascript', 0, 2, 'HTML, CSS and JavaScript', '2021-04-25 17:18:14', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'An explanation of what a browser is, and a Hello-World example with HTML, CSS and Javascript.', 'browser, files, html, css, javascript '),
('i-made-a-website-only-with-notepad-plus-plus', 0, 2, 'How to make a website from scratch', '2021-04-25 17:16:37', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'Summary of the things which I have learned, by writing this very website only with Notepad++.', 'webdev, web, notepad, html, css, javascript, php, cpanel, wordpress'),
('improving-website-performance', 0, 2, 'Improving Website Performance', '2021-04-25 17:22:41', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'Summary of the tricks I used, to boost the performance of my website.', 'lighthouse, pageinsight, insight, speed, performance, overhead, single, webp, google, powershell, pixelart, cache, late-image, thirdparty, service, end'),
('making-videos-is-hard', 0, 42, 'Making videos is hard', '2023-08-01 06:44:16', NULL, NULL, 'I was planning to make a beginner friendly video to explain quaternions. Unfortunately, that took a lot longer than expected.', 'Quaternion, YouTube, Vulkan, GameDev, Rust, Unity, 3d'),
('newsletter-collecting-data-and-recaptcha', 0, 2, 'Newsletter, Collecting Data and reCAPTCHA', '2021-04-25 17:22:19', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'My implementation of a Newsletter and my opinion on collecting data.', 'newsletter, privacy, contact, data, cookies, reCAPTCHA, bot, protection, email, xampp'),
('oops-i-deleted-my-newsletter', 0, 2, 'Oops, I deleted my Newsletter', '2023-05-07 14:33:56', NULL, NULL, 'Storing user data is hard. In this blogpost I describe my issues with my Newsletter and why it caused much problems', 'Newsletter, Webdev, Privacy Policy, Userdata, Email, Gamedev, Gameengine, Programming'),
('php-databases-and-how-my-blog-works', 0, 2, 'PHP, Databases and how my Blog works', '2021-04-25 17:21:58', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'Introducing the concept of backend. A Brief overview of PHP and SQL.', 'backend, php, database, sql, mysql, github, late-image'),
('post-crisis', 0, 42, 'Post Crisis', '2022-06-12 06:27:35', NULL, NULL, 'A reflection after the crisis of my previous blogpost. I give my opinion about Rust and how I continue to write my gameengine.', 'rust, c++, cpp, sdl2, sdl, gameengine, gamedev, music, synth, arturia, polybrute, m8, dirtywave, tracker'),
('quaternion-playground', 1, 2, 'Quaternion Playground', '2023-06-20 06:15:34', NULL, NULL, 'A visualization to better understand Quaternions.', 'quaternion, visualization, programming, unity, learn, education, 3d, game engine, gamedev'),
('rebinding-controls-via-a-rebind-matrix', 0, 2, 'Rebinding Controls via a RebindMatrix', '2022-07-11 18:56:39', NULL, NULL, 'I propose a RebindSystem, that is quite powerful but also easy to use. It uses matrices, which are inspired by ModMatrices, that are found on some Synthesizers.', 'rebind, rebind-system, rebind-controls, controls, input, gamedev, gameengine, engine, rust, bitwise, matrix'),
('ris-website', 1, 2, 'risWebsite', '2021-04-25 17:27:28', 'https://www.rismosch.com/article?id=i-made-a-website-only-with-notepad-plus-plus', 'assets/meta_image_x5.png', '', ''),
('ris_engine', 1, 2, 'ris_engine', '2022-01-02 15:37:08', 'https://github.com/Rismosch/ris_engine', 'assets/thumbnails/ris_engine.webp', '', ''),
('running-test-in-series', 0, 2, 'Running Tests in Series in Rust', '2022-08-28 08:06:48', NULL, NULL, 'Proposing an easy solution, to execute selected tests in series, without using third party crates.', 'rust, test, cargo, serial, sequential, sync, concurrent, thread'),
('the-land-behind-the-waves', 1, 1, 'The Land Behind The Waves', '2019-08-09 07:00:00', 'https://rismosch.bandcamp.com/album/the-land-behind-the-waves', 'assets/thumbnails/the_land_behind_the_waves.webp', '', ''),
('the-three-stages-of-competent-enjoyment', 0, 42, 'The three Stages of Competent Enjoyment', '2021-10-07 17:59:00', NULL, NULL, 'A short blogpost, in which I document the three levels, one can find themself in, to enjoy art.', 'level, enjoyment, competence, model, gamedev, videgame, music, art'),
('the-world-between-my-mind-and-reality', 1, 1, 'The World Between My Mind And Reality', '2023-03-10 19:07:11', 'https://rismosch.bandcamp.com/album/the-world-between-my-mind-and-reality', 'assets/thumbnails/the_world_between_my_mind_and_reality.webp', '', ''),
('why-people-love-bad-art', 0, 42, 'Why people love Bad Art', '2021-10-09 13:39:14', NULL, NULL, 'My take, on why people prefer different kinds of art. I go over modern, avantgarde, challenging and popular art, and classify which is liked by whom.', 'art, the shaggs, outsider music, monalisa, david, leonardo da vinci, michelangelo, robert florczak, dirty apron, prageru, jackson pollok, modern, creator, consumer, pink floyd, zeitgeist');

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
(1, 'Music'),
(2, 'Programming'),
(42, 'Other');

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
(0, 'Blog'),
(1, 'Project');

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
