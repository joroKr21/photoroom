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
-- Structure for view `recent`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `recent` AS select `p`.`id` AS `id`,`p`.`author_id` AS `author_id`,`p`.`album_id` AS `album_id`,`p`.`filename` AS `filename`,`p`.`date` AS `date`,`p`.`category` AS `category`,`p`.`description` AS `description`,`a`.`title` AS `title`,`u`.`username` AS `username`,`u`.`firstname` AS `firstname`,`u`.`lastname` AS `lastname` from ((`photos` `p` join `albums` `a`) join `users` `u`) where ((`p`.`album_id` = `a`.`id`) and (`a`.`visibility` = 1) and (`p`.`author_id` = `u`.`id`)) group by `p`.`album_id`,`p`.`author_id`,`p`.`category` order by `p`.`date` desc,(`a`.`title` collate utf8_unicode_ci) limit 50;

--
-- VIEW  `recent`
-- Data: None
--


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
