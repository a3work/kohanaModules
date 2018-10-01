-- phpMyAdmin SQL Dump
-- version 4.0.8deb1
-- http://www.phpmyadmin.net
--
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Структура таблицы `files_map`
--

CREATE TABLE IF NOT EXISTS `files_map` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `parent` mediumint(9) NOT NULL DEFAULT '0',
  `uri` varchar(8192) COLLATE utf8_unicode_ci NOT NULL COMMENT 'uri страницы, определяется в момент изменения данных страницы',
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'адрес страницы, разделитель -- запятые (0,15,244,357), определяется в момент изменения данных страницы',
  `path` varchar(8192) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ext` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mime` varchar(127) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'max length in compliance with http://www.ietf.org/rfc/rfc4288.txt',
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Системные страницы не показываются в деревьях страниц и в меню',
  `is_dir` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'directory flag',
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `id_hash` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'used in permanent links',
  `uri_hash` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'used in temporary links',
  `path_hash` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'used for filesystem synchronization',
  `file_hash` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'used for filesystem synchronization',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `id_hash` (`id_hash`),
  KEY `uri_hash` (`uri_hash`),
  KEY `path_hash` (`path_hash`),
  KEY `file_hash` (`file_hash`),
  KEY `is_directory` (`is_dir`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `files_map`
--

INSERT INTO `files_map` (`id`, `parent`, `uri`, `address`, `path`, `name`, `ext`, `mime`, `ctime`, `is_system`, `is_dir`, `comment`, `id_hash`, `uri_hash`, `path_hash`, `file_hash`) VALUES
(0, -1, '', '0', '', '', '', '', '2013-12-05 15:21:07', 1, 1, 'Корень файловой системы', '509c649eb16ad2eab7777dbc22c8456e', '39fcaa5c3abf69efda97ab0e004c89f1', '39fcaa5c3abf69efda97ab0e004c89f1', 'd41d8cd98f00b204e9800998ecf8427e');


INSERT INTO `db_lesgaft`.`site_config` (`config_key`, `group_name`, `config_value`, `label`, `position`) VALUES ('last_scan_time', 'files', NULL, 'Время сканирования файловой системы', NULL);