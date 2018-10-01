-- phpMyAdmin SQL Dump
-- version 4.2.12deb2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 10, 2015 at 12:46 PM
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
-- Table structure for table `shop_orders_items`
--

DROP TABLE IF EXISTS `shop_orders_items`;
CREATE TABLE IF NOT EXISTS `shop_orders_items` (
`id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT ':DEPRECATED:',
  `order_id` int(11) NOT NULL,
  `supplier_id` mediumint(8) NOT NULL,
  `state_id` smallint(3) NOT NULL,
  `emex_id` int(11) NOT NULL,
  `replaceable_id` int(11) DEFAULT NULL,
  `goods_id` int(11) NOT NULL DEFAULT '0',
  `pricelist_id` mediumint(9) DEFAULT NULL,
  `supplier_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pricelist_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_close` int(11) NOT NULL DEFAULT '0',
  `code` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `descr` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'later: name',
  `producer` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `producer_logo` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `supplier` char(4) COLLATE utf8_unicode_ci NOT NULL COMMENT ':DEPRECATED: use supplier_name and pricelist_name\\nlater: Plogo',
  `dtime` datetime NOT NULL COMMENT 'later: date_delivery',
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `price_client` decimal(10,2) NOT NULL,
  `price_buy` decimal(10,2) NOT NULL,
  `payment` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL COMMENT ':DEPRECATED: use comment system ',
  `parent` int(11) NOT NULL DEFAULT '0',
  `hide_change` tinyint(1) NOT NULL DEFAULT '0',
  `is_archive` tinyint(1) NOT NULL,
  `reference` varchar(90) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user comment',
  `is_freeze` tinyint(1) NOT NULL COMMENT 'later: freeze',
  `is_quick` int(11) NOT NULL DEFAULT '0' COMMENT 'заказ, доставка котором меньше пяти дней (quick = 1) удалению не подлежит  later: quick :DEPRECATED:',
  `is_removed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'removed flag',
  `esas_send_flag` tinyint(1) NOT NULL DEFAULT '0',
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=30860 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shop_orders_items`
--
ALTER TABLE `shop_orders_items`
 ADD PRIMARY KEY (`id`), ADD KEY `ID_ORDERS` (`order_id`), ADD KEY `ID_STATE` (`state_id`), ADD KEY `ID_ORDERS_EMEX` (`emex_id`), ADD KEY `parent` (`parent`), ADD KEY `is_archive` (`is_archive`), ADD KEY `user_id` (`user_id`), ADD KEY `supplier_id` (`supplier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shop_orders_items`
--
ALTER TABLE `shop_orders_items`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=30860;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
