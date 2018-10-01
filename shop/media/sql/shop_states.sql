-- phpMyAdmin SQL Dump
-- version 4.2.12deb2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 10, 2015 at 12:55 PM
-- Server version: 5.5.40-1
-- PHP Version: 5.6.4-4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `db_esp`
--

-- --------------------------------------------------------

--
-- Table structure for table `shop_states`
--

DROP TABLE IF EXISTS `shop_states`;
CREATE TABLE IF NOT EXISTS `shop_states` (
`id` smallint(6) NOT NULL,
  `parent` smallint(6) NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `descr` varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `color` varchar(7) COLLATE utf8_unicode_ci NOT NULL DEFAULT '#000',
  `position` float NOT NULL DEFAULT '0',
  `is_initial` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'flag of start state',
  `is_final` tinyint(1) NOT NULL DEFAULT '0',
  `is_removable` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shop_states`
--
ALTER TABLE `shop_states`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `position` (`position`), ADD KEY `label` (`label`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shop_states`
--
ALTER TABLE `shop_states`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=21;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
