-- phpMyAdmin SQL Dump
-- version 4.2.12deb2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 10, 2015 at 12:44 PM
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
-- Table structure for table `shop_goods`
--

DROP TABLE IF EXISTS `shop_goods`;
CREATE TABLE IF NOT EXISTS `shop_goods` (
`id` int(11) NOT NULL,
  `supplier_id` mediumint(8) NOT NULL,
  `pricelist_id` mediumint(9) DEFAULT NULL,
  `descr` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'later: name',
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL COMMENT ':DEPRECATED: use comment system ',
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `code` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `producer` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `is_special` bit(1) NOT NULL,
  `dtime` smallint(6) NOT NULL COMMENT 'days',
  `auction_id` mediumint(9) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=13676 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shop_goods`
--
ALTER TABLE `shop_goods`
 ADD PRIMARY KEY (`id`), ADD KEY `supplier_id` (`supplier_id`), ADD KEY `supplier_id_2` (`supplier_id`,`pricelist_id`,`producer`,`code`), ADD FULLTEXT KEY `descr` (`descr`,`code`,`producer`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shop_goods`
--
ALTER TABLE `shop_goods`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13676;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
