-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 01. Jul 2026 um 10:23
-- Server-Version: 10.11.17-MariaDB-cll-lve
-- PHP-Version: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
  `type_id` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) NOT NULL DEFAULT 0,
  `blog_type_id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `link` varchar(256) DEFAULT NULL,
  `thumbnail_path` varchar(256) DEFAULT NULL,
  `description` varchar(256) NOT NULL,
  `keywords` varchar(256) NOT NULL,
  `deprecated` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Daten für Tabelle `Articles`
--

INSERT INTO `Articles` (`id`, `type_id`, `category_id`, `blog_type_id`, `title`, `timestamp`, `link`, `thumbnail_path`, `description`, `keywords`, `deprecated`) VALUES
('a-neat-trick-to-debug-exceptions-in-c-sharp', 0, 2, 2, 'A neat Trick to Debug Exceptions in C#', '2021-08-24 19:29:22', NULL, NULL, 'Using the when-keyword to better debug exceptions in C#.', 'csharp, exception, debug, try, catch, when', NULL),
('an-even-better-error-type', 0, 2, 2, 'An even better Error Type', '2024-02-11 10:13:05', NULL, NULL, 'Utilizing Rusts Backtrace functionality, it\'s possible to make even better errors types.', 'rust, gamedev, gameengine,', NULL),
('angery', 1, 1, 1, 'Angery', '2021-07-06 18:32:15', 'https://rismosch.bandcamp.com/album/angery', 'assets/thumbnails/angery.webp', '', '', NULL),
('building-a-job-system', 0, 2, 2, 'Building a JobSystem', '2022-10-06 16:46:08', NULL, NULL, 'The latest feature of my gameengine is thread pool, which uses all CPU resources all of the time. In this blogpost I go into the deisgn of this first working prototype.', 'rust, gamedev, gameengine, jobsystem, threadpool, concurrency, multithreading, safe, fast, performance', NULL),
('button-down-up-and-hold', 0, 2, 2, 'How to programm Button Down, Up and Hold with 3 lines of code', '2022-08-01 17:12:56', NULL, NULL, 'In this post I describe a quick and easy solution to compute the single frame, where a button was pressed down and released', 'rust, gamedev, engine, programming, tutorial', 'The code is simple enough to be figured out yourself. And I realized, lines of code ist not something you should optimize.'),
('color-picker', 1, 2, 1, 'Color Picker', '2025-05-25 22:08:53', NULL, NULL, 'Color picker based on the OkLab color space', 'design, graphics, gradient, drawing, webdev, ui, gui, visual, shader', NULL),
('crisis', 0, 42, 1, 'Crisis', '2022-04-10 15:52:53', NULL, NULL, 'My project to write a gameengine hit a wall. In this post I go over my struggles and possible ways to overcome them.', 'crisis, reddit, rplace, place, cpp, c++, resharper, unit tests, struggle, rust', NULL),
('css-on-mobile-and-the-most-important-header-tag', 0, 2, 2, 'CSS on Mobile, and The Most Important Header Tag', '2021-04-25 17:19:00', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'Tackling meta-tags. And a solution to make the website responsive, such that it is perfectly usable on any device.', 'css, meta, viewport, media, responsive, mobile', NULL),
('furries-are-cool', 0, 42, 1, 'Furries are cool', '2026-06-29 18:55:32', NULL, NULL, 'I think foxes are cute', 'furry, sexuality, fox', NULL),
('good-enough-is-sometimes-not-good-enough', 0, 42, 1, 'Good enough is sometimes not good enough', '2021-03-07 15:35:15', NULL, NULL, 'My post mortem of the GMTK GameJam 2020, which changed my perspective on how I approach my projects.', 'gamejam, gmtk, unity, 2020, ', NULL),
('hosting-a-webserver-and-money', 0, 2, 2, 'Hosting a Webserver and Money', '2021-04-25 17:17:50', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'A brief summary, of what services you need to buy to get a website running.', 'www, godaddy, ssl, http, price, money, hosting, webserver, domain, email', NULL),
('how-i-build-my-website', 0, 2, 2, 'How I Build My Website', '2021-04-25 17:18:42', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'An overview, of how I used HTML, CSS, and Javascript to build my website.', 'devtools, view-source, div, html, css, javascript, pixelart', NULL),
('how-i-made-the-world-between-my-mind-and-reality', 0, 1, 2, 'How I made The World Between My Mind And Reality', '2023-03-10 20:37:48', NULL, 'assets/thumbnails/the_world_between_my_mind_and_reality.webp', 'I describe how I made my latest album. Which instruments I used, how I went about recording vocals and how I made the cover art.', 'DnB, Drum and Bass, EDM, Punk, Arturia Polybrute, Vocals, Lyrics, Singing, FL Studio, DALL E, AI, John Carmack, Brian Caris, Undertale', NULL),
('how-to-create-your-own-binary-format', 0, 2, 2, 'How to create your own binary format', '2025-04-08 21:24:57', NULL, NULL, 'In this blogpost I am going over on how to go about creating your own custom binary format. It isn\'t difficult and the process is quite fast, I promise.', 'csharp, c#, binary, serialization, io, gameengine, gamedev, file, memory, cpu', NULL),
('html-css-and-javascript', 0, 2, 2, 'HTML, CSS and JavaScript', '2021-04-25 17:18:14', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'An explanation of what a browser is, and a Hello-World example with HTML, CSS and Javascript.', 'browser, files, html, css, javascript ', NULL),
('i-found-a-bug-in-my-job-system', 0, 2, 2, 'I found a Bug in my JobSystem', '2024-03-17 14:40:00', NULL, NULL, 'I\'ve found a rather sever, but interesting bug in my JobSystem.', 'rust, csharp, concurrency, jobsystem, bug, deadlock, gameengine, gamedev', NULL),
('i-made-a-website-only-with-notepad-plus-plus', 0, 2, 2, 'How to make a website from scratch', '2021-04-25 17:16:37', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'Summary of the things which I have learned, by writing this very website only with Notepad++.', 'webdev, web, notepad, html, css, javascript, php, cpanel, wordpress', NULL),
('improving-website-performance', 0, 2, 2, 'Improving Website Performance', '2021-04-25 17:22:41', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'Summary of the tricks I used, to boost the performance of my website.', 'lighthouse, pageinsight, insight, speed, performance, overhead, single, webp, google, powershell, pixelart, cache, late-image, thirdparty, service, end', NULL),
('making-videos-is-hard', 0, 42, 1, 'Making videos is hard', '2023-08-01 06:44:16', NULL, NULL, 'I was planning to make a beginner friendly video to explain quaternions. Unfortunately, that took a lot longer than expected.', 'Quaternion, YouTube, Vulkan, GameDev, Rust, Unity, 3d', NULL),
('my-most-hated-feature-in-rust', 0, 2, 2, 'My most hated feature in Rust', '2023-12-03 20:57:57', NULL, NULL, 'Result is a quite powerful concept. But because it\'s so general, it\'s quite clunky and aweful to use.', 'rust, gamedev, gameengine,', NULL),
('my-thoughts-on-monstercat-001-030', 0, 1, 1, 'My thoughts on Monstercat 001-030', '2024-12-01 22:17:58', NULL, NULL, 'I was on a nostalgia trip and listened to all Monstercat compilation albums, 001 to 030. Here\'s what I think about each one.', 'Monstercat, Ranking, Charts, EDM, DnB, House, Dubstep, Nostalgia', NULL),
('newsletter-collecting-data-and-recaptcha', 0, 2, 2, 'Newsletter, Collecting Data and reCAPTCHA', '2021-04-25 17:22:19', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'My implementation of a Newsletter and my opinion on collecting data.', 'newsletter, privacy, contact, data, cookies, reCAPTCHA, bot, protection, email, xampp', NULL),
('oops-i-deleted-my-newsletter', 0, 2, 1, 'Oops, I deleted my Newsletter', '2023-05-07 14:33:56', NULL, NULL, 'Storing user data is hard. In this blogpost I describe my issues with my Newsletter and why it caused much problems', 'Newsletter, Webdev, Privacy Policy, Userdata, Email, Gamedev, Gameengine, Programming', NULL),
('php-databases-and-how-my-blog-works', 0, 2, 2, 'PHP, Databases and how my Blog works', '2021-04-25 17:21:58', NULL, 'assets/thumbnails/web_dev_crash_course.webp', 'Introducing the concept of backend. A Brief overview of PHP and SQL.', 'backend, php, database, sql, mysql, github, late-image', NULL),
('post-crisis', 0, 42, 1, 'Post Crisis', '2022-06-12 06:27:35', NULL, NULL, 'A reflection after the crisis of my previous blogpost. I give my opinion about Rust and how I continue to write my gameengine.', 'rust, c++, cpp, sdl2, sdl, gameengine, gamedev, music, synth, arturia, polybrute, m8, dirtywave, tracker', NULL),
('quaternion-playground', 1, 2, 1, 'Quaternion Playground', '2023-06-20 06:15:34', NULL, NULL, 'A visualization to better understand Quaternions.', 'quaternion, visualization, programming, unity, learn, education, 3d, game engine, gamedev', NULL),
('rebinding-controls-via-a-rebind-matrix', 0, 2, 2, 'Rebinding Controls via a RebindMatrix', '2022-07-11 18:56:39', NULL, NULL, 'I propose a RebindSystem, that is quite powerful but also easy to use. It uses matrices, which are inspired by ModMatrices, that are found on some Synthesizers.', 'rebind, rebind-system, rebind-controls, controls, input, gamedev, gameengine, engine, rust, bitwise, matrix', NULL),
('ris-website', 1, 2, 1, 'risWebsite', '2021-04-25 17:27:28', 'https://www.rismosch.com/article?id=i-made-a-website-only-with-notepad-plus-plus', 'assets/meta_image_x5.png', '', '', NULL),
('ris_engine', 1, 2, 1, 'ris_engine', '2022-01-02 15:37:08', 'https://github.com/Rismosch/ris_engine', 'assets/thumbnails/ris_engine.webp', '', '', NULL),
('ris_terrain_generator', 1, 2, 1, 'Terrain Generator', '2025-10-03 11:23:51', 'https://github.com/Rismosch/ris_terrain_generator', 'assets/thumbnails/ris_terrain_generator.webp', '', '', NULL),
('running-test-in-series', 0, 2, 2, 'Running Tests in Series in Rust', '2022-08-28 08:06:48', NULL, NULL, 'Proposing an easy solution, to execute selected tests in series, without using third party crates.', 'rust, test, cargo, serial, sequential, sync, concurrent, thread', 'This is a bad practice. I don\'t use it, and I don\'t recommend it anymore. If you absolutely need global state, encapsulate it into an object and have a global variable. In your tests you can then test the object, but in your code you can reference the global variable.'),
('rust-is-the-future', 0, 2, 1, 'Rust is the future', '2025-02-25 21:05:59', NULL, NULL, 'Ranting about bad programming languages and programmers.', 'rust, C++, C#, rant, prediction, gameengine, programming,', NULL),
('the-case-for-porting-to-wii', 0, 2, 1, 'The case for porting to Wii', '2026-02-17 18:58:34', NULL, NULL, 'I have this brainfart of an idea that I just can\'t get rid of.', 'Rust, C, gamedev, gameengine, Wii, Nintendo', NULL),
('the-empty-mind-between-milestones', 0, 2, 1, 'The empty mind between milestones', '2025-11-18 22:08:33', NULL, NULL, 'A retrospective on my development journey thus far.', 'rust, gamedev, gameengine, retrospective', NULL),
('the-good-code-manifesto', 0, 2, 2, 'The Good Code Manifesto', '2025-11-18 21:32:06', NULL, NULL, 'Some best practices I learned over 5 years of writing software.', 'programming, clean code', NULL),
('the-land-behind-the-waves', 1, 1, 1, 'The Land Behind The Waves', '2019-08-09 07:00:00', 'https://rismosch.bandcamp.com/album/the-land-behind-the-waves', 'assets/thumbnails/the_land_behind_the_waves.webp', '', '', NULL),
('the-three-stages-of-competent-enjoyment', 0, 42, 1, 'The three Stages of Competent Enjoyment', '2021-10-07 17:59:00', NULL, NULL, 'A short blogpost, in which I document the three levels, one can find themself in, to enjoy art.', 'level, enjoyment, competence, model, gamedev, videgame, music, art', 'Honestly this is just kinda cringe.'),
('the-world-between-my-mind-and-reality', 1, 1, 1, 'The World Between My Mind And Reality', '2023-03-10 19:07:11', 'https://rismosch.bandcamp.com/album/the-world-between-my-mind-and-reality', 'assets/thumbnails/the_world_between_my_mind_and_reality.webp', '', '', NULL),
('turned-upside-down', 0, 42, 1, 'Turned Upside Down', '2026-06-21 19:07:15', NULL, NULL, 'AI is evil', 'job, quality of life, siggraph, identity, blender, gamedev, game engine, AI, LLM, ChatGPT', NULL),
('why-i-make-my-own-game-engine', 0, 42, 1, 'Why I make my own Game Engine', '2024-05-30 19:02:35', NULL, 'assets/thumbnails/ris_engine.webp', 'Reflecting on the GameDev community as a whole, and comming to terms on the size of the project.', 'gamedev, gameengine, postmortem, philosophy', NULL),
('why-people-love-bad-art', 0, 42, 1, 'Why people love Bad Art', '2021-10-09 13:39:14', NULL, NULL, 'My take, on why people prefer different kinds of art. I go over modern, avantgarde, challenging and popular art, and classify which is liked by whom.', 'art, the shaggs, outsider music, monalisa, david, leonardo da vinci, michelangelo, robert florczak, dirty apron, prageru, jackson pollok, modern, creator, consumer, pink floyd, zeitgeist', 'Honestly, this is just cringe.');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Article_Categories`
--

CREATE TABLE `Article_Categories` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Daten für Tabelle `Article_Types`
--

INSERT INTO `Article_Types` (`id`, `name`) VALUES
(0, 'Blog'),
(1, 'Project');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Blog_Types`
--

CREATE TABLE `Blog_Types` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Daten für Tabelle `Blog_Types`
--

INSERT INTO `Blog_Types` (`id`, `name`) VALUES
(1, 'Rambling'),
(2, 'Writeup');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `Articles`
--
ALTER TABLE `Articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `blog_type_id` (`blog_type_id`);

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
-- Indizes für die Tabelle `Blog_Types`
--
ALTER TABLE `Blog_Types`
  ADD PRIMARY KEY (`id`);

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `Articles`
--
ALTER TABLE `Articles`
  ADD CONSTRAINT `Articles_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `Article_Types` (`id`),
  ADD CONSTRAINT `Articles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `Article_Categories` (`id`),
  ADD CONSTRAINT `Articles_ibfk_3` FOREIGN KEY (`blog_type_id`) REFERENCES `Blog_Types` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
