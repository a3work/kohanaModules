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
-- Table structure for table `shop_templates`
--

DROP TABLE IF EXISTS `shop_templates`;
CREATE TABLE IF NOT EXISTS `shop_templates` (
`id` mediumint(9) NOT NULL,
  `type` enum('txt/csv','xls/xlsx') COLLATE utf8_unicode_ci NOT NULL COMMENT 'file and parser type',
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `logo_global` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `regexp` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `producer` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `descr` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `price` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `quan` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `dtime` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `logo` varchar(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `currency_global` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `begin_line` tinyint(2) NOT NULL,
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `separator` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `delimiter` varchar(1) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shop_templates`
--
ALTER TABLE `shop_templates`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shop_templates`
--
ALTER TABLE `shop_templates`
MODIFY `id` mediumint(9) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
