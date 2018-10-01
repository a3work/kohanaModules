-- phpMyAdmin SQL Dump
-- version 4.0.10.18
-- https://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Apr 02, 2017 at 02:43 PM
-- Server version: 5.5.54-cll
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `podbitor_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `shop_categories`
--

DROP TABLE IF EXISTS `shop_categories`;
CREATE TABLE IF NOT EXISTS `shop_categories` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `parent_id` mediumint(9) NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `level` tinyint(4) NOT NULL DEFAULT '1',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `symcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `symcode` (`symcode`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop_currency`
--

DROP TABLE IF EXISTS `shop_currency`;
CREATE TABLE IF NOT EXISTS `shop_currency` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `code` smallint(4) NOT NULL,
  `code_char` char(3) COLLATE utf8_unicode_ci NOT NULL,
  `rate` decimal(10,4) NOT NULL,
  `nom` smallint(6) NOT NULL DEFAULT '1',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=27140 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop_discount`
--

DROP TABLE IF EXISTS `shop_discount`;
CREATE TABLE IF NOT EXISTS `shop_discount` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `start` int(11) DEFAULT '0',
  `end` int(11) DEFAULT '0',
  `user_id` int(11) DEFAULT '0',
  `pricelist_id` mediumint(9) DEFAULT '0',
  `supplier_id` mediumint(9) DEFAULT '0',
  `value` tinyint(2) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop_goods`
--

DROP TABLE IF EXISTS `shop_goods`;
CREATE TABLE IF NOT EXISTS `shop_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` mediumint(9) NOT NULL,
  `supplier_id` mediumint(8) NOT NULL,
  `pricelist_id` mediumint(9) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `text` varchar(2048) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'later: name',
  `symcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL COMMENT ':DEPRECATED: use comment system ',
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `code` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `producer` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `is_special` bit(1) NOT NULL,
  `dtime` smallint(6) NOT NULL COMMENT 'days',
  `auction_id` mediumint(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `supplier_id_2` (`supplier_id`,`pricelist_id`,`producer`,`code`),
  KEY `symcode` (`symcode`),
  FULLTEXT KEY `descr` (`name`,`text`,`code`,`producer`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=65594 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop_markup`
--

DROP TABLE IF EXISTS `shop_markup`;
CREATE TABLE IF NOT EXISTS `shop_markup` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `supplier_id` mediumint(9) NOT NULL,
  `pricelist_id` mediumint(9) NOT NULL,
  `value` smallint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`,`pricelist_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=45 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop_orders`
--

DROP TABLE IF EXISTS `shop_orders`;
CREATE TABLE IF NOT EXISTS `shop_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `comment` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop_orders_items`
--

DROP TABLE IF EXISTS `shop_orders_items`;
CREATE TABLE IF NOT EXISTS `shop_orders_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` mediumint(9) NOT NULL,
  `state_id` smallint(6) NOT NULL,
  `supplier_id` mediumint(8) NOT NULL,
  `pricelist_id` mediumint(9) DEFAULT NULL,
  `goods_id` int(11) NOT NULL,
  `supplier_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pricelist_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `descr` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'later: name',
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL COMMENT ':DEPRECATED: use comment system ',
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `code` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `producer` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `dtime` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `supplier_id_2` (`supplier_id`,`pricelist_id`,`producer`,`code`),
  KEY `order_id` (`order_id`),
  FULLTEXT KEY `descr` (`descr`,`code`,`producer`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop_pricelists`
--

DROP TABLE IF EXISTS `shop_pricelists`;
CREATE TABLE IF NOT EXISTS `shop_pricelists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` mediumint(9) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dtime` smallint(6) DEFAULT NULL COMMENT 'days',
  `count` mediumint(9) DEFAULT NULL,
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop_states`
--

DROP TABLE IF EXISTS `shop_states`;
CREATE TABLE IF NOT EXISTS `shop_states` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `parent` smallint(6) NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `descr` varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `color` varchar(7) COLLATE utf8_unicode_ci NOT NULL DEFAULT '#000',
  `position` mediumint(9) NOT NULL DEFAULT '1000',
  `is_initial` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'flag of start state',
  `is_removable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `label` (`label`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop_suppliers`
--

DROP TABLE IF EXISTS `shop_suppliers`;
CREATE TABLE IF NOT EXISTS `shop_suppliers` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Название',
  `class_order` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `class_trace` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `class_acceptance` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Изменён',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Поставщики' AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop_suppliers_templates`
--

DROP TABLE IF EXISTS `shop_suppliers_templates`;
CREATE TABLE IF NOT EXISTS `shop_suppliers_templates` (
  `supplier_id` mediumint(9) NOT NULL,
  `template_id` mediumint(9) NOT NULL,
  UNIQUE KEY `supplier_id` (`supplier_id`,`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_templates`
--

DROP TABLE IF EXISTS `shop_templates`;
CREATE TABLE IF NOT EXISTS `shop_templates` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `type` enum('txt/csv','xls/xlsx') COLLATE utf8_unicode_ci NOT NULL COMMENT 'file and parser type',
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `logo_global` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `regexp` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `producer` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `col_name` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `price` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `quan` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `dtime` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `logo` varchar(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `currency_global` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `begin_line` tinyint(2) NOT NULL,
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `separator` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `delimiter` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
