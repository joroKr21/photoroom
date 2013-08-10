-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 01, 2012 at 09:29 PM
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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
