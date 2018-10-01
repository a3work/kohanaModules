-- phpMyAdmin SQL Dump
-- version 4.2.12deb2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 10, 2015 at 12:47 PM
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
-- Table structure for table `shop_suppliers`
--

DROP TABLE IF EXISTS `shop_suppliers`;
CREATE TABLE IF NOT EXISTS `shop_suppliers` (
`id` mediumint(8) unsigned NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Название',
  `class_order` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `class_trace` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `class_acceptance` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Изменён'
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Поставщики';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shop_suppliers`
--
ALTER TABLE `shop_suppliers`
 ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shop_suppliers`
--
ALTER TABLE `shop_suppliers`
MODIFY `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
