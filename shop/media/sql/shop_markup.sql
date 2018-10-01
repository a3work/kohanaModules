-- phpMyAdmin SQL Dump
-- version 4.2.12deb2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 21, 2015 at 07:02 PM
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
-- Table structure for table `shop_markup`
--

DROP TABLE IF EXISTS `shop_markup`;
CREATE TABLE IF NOT EXISTS `shop_markup` (
`id` mediumint(9) NOT NULL,
  `supplier_id` mediumint(9) NOT NULL,
  `pricelist_id` mediumint(9) NOT NULL,
  `value` smallint(3) NOT NULL,
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shop_markup`
--
ALTER TABLE `shop_markup`
 ADD PRIMARY KEY (`id`), ADD KEY `supplier_id` (`supplier_id`,`pricelist_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shop_markup`
--
ALTER TABLE `shop_markup`
MODIFY `id` mediumint(9) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
