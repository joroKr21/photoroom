-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 01, 2012 at 09:14 PM
-- Server version: 5.5.20
-- PHP Version: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `photoroom`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE IF NOT EXISTS `albums` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int(11) unsigned NOT NULL,
  `title` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `visibility` tinyint(1) NOT NULL DEFAULT '1',
  `description` text CHARACTER SET utf8 COLLATE utf8_bin,
  `cover` varchar(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'images/folder.png',
  PRIMARY KEY (`id`),
  KEY `user_id` (`author_id`),
  FULLTEXT KEY `fulltext` (`title`,`description`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `albums`
--



-- --------------------------------------------------------

--
-- Table structure for table `photos`
--

CREATE TABLE IF NOT EXISTS `photos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int(11) unsigned NOT NULL,
  `author_id` int(11) unsigned NOT NULL,
  `filename` varchar(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_bin,
  `date` date NOT NULL,
  `category` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `album_id` (`album_id`),
  KEY `author_id` (`author_id`),
  FULLTEXT KEY `fulltext` (`category`,`description`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `photos`
--



-- --------------------------------------------------------

--
-- Stand-in structure for view `recent`
--
CREATE TABLE IF NOT EXISTS `recent` (
`id` int(11) unsigned
,`author_id` int(11) unsigned
,`album_id` int(11) unsigned
,`filename` varchar(256)
,`date` date
,`category` varchar(32)
,`description` text
,`title` varchar(32)
,`username` varchar(32)
,`firstname` varchar(32)
,`lastname` varchar(32)
);
-- --------------------------------------------------------

--
-- Table structure for table `tagnames`
--

CREATE TABLE IF NOT EXISTS `tagnames` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photo_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag_index` (`photo_id`,`tag_id`),
  KEY `photo_id` (`photo_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `firstname` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `lastname` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `picture` varchar(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'images/blank.jpg',
  `level` tinyint(1) NOT NULL DEFAULT '0',
  `salt` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `token` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `picture_id` (`picture`),
  FULLTEXT KEY `fulltext` (`firstname`,`lastname`,`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `email`, `password`, `firstname`, `lastname`, `level`, `salt`, `token`) VALUES
('admin', 'joro.kr.21@gmail.com', '89ac8f89c742ae75136762d5c5db407da51999dd03176d95630b045e24adb5ab', 'Georgi', 'Krastev', 9, '21717715e896103af2b9bf8d875e4daa60108eeff5be0cd29848b0615366a71e', '');

-- --------------------------------------------------------

--
-- Structure for view `recent`
--
DROP TABLE IF EXISTS `recent`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `recent` AS select `p`.`id` AS `id`,`p`.`author_id` AS `author_id`,`p`.`album_id` AS `album_id`,`p`.`filename` AS `filename`,`p`.`date` AS `date`,`p`.`category` AS `category`,`p`.`description` AS `description`,`a`.`title` AS `title`,`u`.`username` AS `username`,`u`.`firstname` AS `firstname`,`u`.`lastname` AS `lastname` from ((`photos` `p` join `albums` `a`) join `users` `u`) where ((`p`.`album_id` = `a`.`id`) and (`a`.`visibility` = 1) and (`p`.`author_id` = `u`.`id`)) group by `p`.`album_id`,`p`.`author_id`,`p`.`category` order by `p`.`date` desc,(`a`.`title` collate utf8_unicode_ci) limit 50;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
