-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Июл 17 2013 г., 22:08
-- Версия сервера: 5.5.30
-- Версия PHP: 5.4.4-14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `db_lesgaft`
--

-- --------------------------------------------------------

--
-- Структура таблицы `access_rules`
--

DROP TABLE IF EXISTS `access_rules`;
CREATE TABLE IF NOT EXISTS `access_rules` (
  `id` int(11) NOT NULL,
  `finder` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `finder_id` int(11) NOT NULL,
  `obj_id` int(11) NOT NULL,
  `privilege` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `obj_id` (`obj_id`),
  KEY `finder` (`finder`,`finder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `user_groups`
--

DROP TABLE IF EXISTS `user_groups`;
CREATE TABLE IF NOT EXISTS `user_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Дамп данных таблицы `user_groups`
--

INSERT INTO `user_groups` (`id`, `name`, `description`, `created`) VALUES
(5, 'Редакторы документации', '', '2013-04-04 06:52:07');

-- --------------------------------------------------------

--
-- Структура таблицы `user_users`
--

DROP TABLE IF EXISTS `user_users`;
CREATE TABLE IF NOT EXISTS `user_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(10) NOT NULL DEFAULT '0' COMMENT 'в перспективе колонка должна использоваться для создания иерархии пользователей',
  `email` varchar(127) NOT NULL,
  `username` varchar(32) NOT NULL DEFAULT '',
  `owner` varchar(60) NOT NULL DEFAULT '',
  `password` char(64) NOT NULL,
  `logins` int(10) unsigned NOT NULL DEFAULT '0',
  `last_login` int(10) unsigned DEFAULT NULL,
  `last_login_ip` varchar(15) NOT NULL DEFAULT '',
  `last_action` int(10) NOT NULL DEFAULT '0',
  `is_system` int(1) NOT NULL DEFAULT '0' COMMENT 'системный пользователь не показывается в общих списках и не удаляется',
  `is_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `position` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_email` (`email`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=109 ;

--
-- Дамп данных таблицы `user_users`
--

INSERT INTO `user_users` (`id`, `parent`, `email`, `username`, `owner`, `password`, `logins`, `last_login`, `last_login_ip`, `last_action`, `is_system`, `is_blocked`, `comment`, `position`) VALUES
(1, 0, 'a3.work@gmail.com', 'root', '', 'f1880803204b7607f1373e793c2c28126b8e123a3e64277c866a7d0ccdb11ad9', 432, 1374079500, '192.168.77.1', 1356539069, 1, 0, '', 0),
(50, 0, 'pro-555@mail.ru', 'pro-555@mail.ru', '', 'be97c94c9297826d707d12ea08f789bc5e885346ac9d8f669a2822dd0d366cc2', 53, 1371241657, '178.66.101.36', 0, 0, 0, '', 1),
(104, 0, 's.alkimovich@gmail.com', 's.alkimovich@gmail.com', '', '42b4de0717be10d49d45ef85d58f93908ab0ed51adc5dc36d93e989b95482b17', 0, NULL, '', 0, 0, 0, '', 55),
(52, 0, 'extreme_boy@mail.ru', 'eXtreme_boy', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 3),
(53, 0, 'Djulietta@list.ru', 'operator_05', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 4),
(54, 0, 'metrolog-stas@list.ru', 'operator_11', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 1, 1365057567, '195.182.143.214', 0, 0, 0, '', 5),
(55, 0, 'f_adapt@lesgaft.spb.ru', 'operator_12', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 6),
(56, 0, 'zaikods@mail.ru', 'operator_13', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 7),
(57, 0, 'olga.dveirina@mail.ru', 'operator_15', '', 'be97c94c9297826d707d12ea08f789bc5e885346ac9d8f669a2822dd0d366cc2', 4, 1370388241, '95.53.222.130', 0, 0, 0, '', 8),
(58, 0, 'zhurovamarina@yandex.ru', 'operator_19', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 9),
(59, 0, 'd.s.mel@mail.ru', 'operator_20', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 10),
(60, 0, 'bdln@yandex.ru', 'kafedra_02', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 11),
(61, 0, 'shoukhardin@msn.com', 'operator_22', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 12),
(62, 0, 'pbord@bk.ru', 'kafedra_03', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 13),
(63, 0, 'ninulya16@rambler.ru', 'kafedra_04', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 14),
(64, 0, 'dimkazak@mail.ru', 'kafedra_05', '', 'afbebdb8aaf08e396a748f3f8c647a9d17eb337e33fdf7993d8ad5cd06766b12', 0, NULL, '', 0, 0, 0, '', 15),
(65, 0, 'oreshek7@list.ru', 'kafedra_06', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 16),
(66, 0, 'IVA_86@inbox.ru', 'kafedra_07', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 17),
(67, 0, 'sanya_osmehina@mail.ru', 'kafedra_08', '', '9f266ae36c43413c5442e4e88631946df298a0092fda8b79209ff20d4127b3d0', 0, NULL, '', 0, 0, 0, '', 18),
(68, 0, 'masterkik@rambler.ru', 'kafedra_09', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 19),
(69, 0, 'vgp54@mail.ru', 'kafedra_10', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 20),
(70, 0, 'k_wrestling@lesgaft.spb.ru', 'kafedra_11', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 21),
(71, 0, 'AntonioStepanov@yandex.ru', 'operator_24', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 22),
(72, 0, 'sabnova@yandex.ru', 'kafedra_12', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 23),
(73, 0, 'tanushka-88@mail.ru', 'kafedra_13', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 24),
(74, 0, 'natalanz@yandex.ru', 'kafedra_15', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 25),
(75, 0, 'wh_06@mail.ru', 'kafedra_16', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 26),
(76, 0, 'lesgaft-fpk@mail.ru', 'operator_25', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 27),
(77, 0, 'doctor_kob@mail.ru', 'kafedra_17', '', 'afbebdb8aaf08e396a748f3f8c647a9d17eb337e33fdf7993d8ad5cd06766b12', 0, NULL, '', 0, 0, 0, '', 28),
(78, 0, 'v19521901@e-mail.ru', 'kafedra_18', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 29),
(79, 0, 'Gulya_77@bk.ru', 'kafedra_19', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 30),
(80, 0, 'k_abc@lesgaft.spb.ru', 'kafedra_20', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 31),
(81, 0, 'kazancevs@mail.ru', 'kafedra_21', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 32),
(82, 0, 'k_feht@lesgaft.spb.ru', 'kafedra_22', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 33),
(83, 0, 'k_velo@lesgaft.spb.ru', 'kafedra_23', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 34),
(84, 0, 'k_cc@lesgaft.spb.ru', 'kafedra_24', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 35),
(85, 0, 'k_avto@lesgaft.spb.ru', 'kafedra_25', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 36),
(86, 0, 'k_gimn@lesgaft.spb.ru', 'kafedra_26', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 37),
(87, 0, 'k_greb@lesgaft.spb.ru', 'kafedra_27', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 38),
(88, 0, 'k_ls@lesgaft.spb.ru', 'kafedra_28', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 39),
(89, 0, 'k_parus@lesgaft.spb.ru', 'kafedra_29', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 40),
(90, 0, 'k_plavanie@lesgaft.spb.ru', 'kafedra_30', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 41),
(91, 0, 'k_futbol@lesgaft.spb.ru', 'kafedra_31', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 42),
(92, 0, 'k_hokkei@lesgaft.spb.ru', 'kafedra_32', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 43),
(93, 0, 'guly_78@inbox.ru', 'kafedra_33', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 44),
(94, 0, 'barnikova@hotmail.com', 'kafedra_34', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 45),
(95, 0, 'i_hsm@lesgaft.spb.ru', 'operator_27', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 46),
(96, 0, 'k_psiho@lesgaft.spb.ru', 'kafedra_35', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 47),
(97, 0, 'master@lesgaft.spb.ru', 'operator_29', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 0, NULL, '', 0, 0, 0, '', 48),
(98, 0, 'i_ssi@lesgaft.spb.ru', 'operator_30', '', '6c1c4fd2cbf97b0679bb86c7e1d75724d258462136a700375ca955ce5e4b707c', 3, 1365522796, '185.16.101.41', 0, 0, 0, '', 49),
(99, 0, 'afk_lesgaft@mail.ru', 'afk_lesgaft@mail.ru', '', '553f544a6c15ff81f7c7f1d83ac3e3ae898ea8128e9731d5c68e453652138b8a', 6, 1370878877, '195.182.155.142', 0, 0, 0, '', 50),
(101, 0, 'nionica@yandex.ru', 'nionica@yandex.ru', '', 'af67e96f6ff1e829a9d1b5cd0e31f4e0e29e195766da04784c75a4e909507605', 25, 1371571146, '195.182.144.122', 0, 0, 0, '', 52),
(102, 0, 'vadelmasoft@yandex.ru', 'operator_01', '', 'be97c94c9297826d707d12ea08f789bc5e885346ac9d8f669a2822dd0d366cc2', 17, 1371490435, '195.182.148.6', 0, 0, 0, '', 53),
(103, 0, 'f_zao@lesgaft.ru', 'f_zao@lesgaft.ru', '', 'f0f9b09fb1adeb611441dabfdf5b2ef23e2de6100f252b3078bf89d0aea36817', 1, 1370881380, '178.64.251.253', 0, 0, 0, '', 54),
(105, 0, '7144054@mail.ru', '7144054@mail.ru', '', '58d1f1d7beda60ff607ddf7bc317d9ab71b457e30c8716a472483e299eb1e71b', 0, NULL, '', 0, 0, 0, '', 56),
(106, 0, 'yurodygina@yandex.ru', 'yurodygina@yandex.ru', '', '5fa18f39a5f0b14064083e0ae5a844847850df6aa64168d5f10ad639ed8e28df', 7, 1371557292, '84.52.108.35', 0, 0, 0, '', 57),
(107, 0, 'fazilspb@mail.ru', 'fazilspb@mail.ru', '', 'c94af32ebc4046378a657bef5d6dea7dc3a094f4d27edc741999577be39636dd', 1, 1370950849, '178.64.251.253', 0, 0, 0, '', 58),
(108, 0, 'mar.evst@mail.ru', 'mar.evst@mail.ru', '', 'deca67cc24b600a0089c5f21f70a3e3026b3cb411439ec3f5c3121ddd5f8b846', 6, 1371412771, '91.151.205.199', 0, 0, 0, '', 59);

-- --------------------------------------------------------

--
-- Структура таблицы `user_users_groups`
--

DROP TABLE IF EXISTS `user_users_groups`;
CREATE TABLE IF NOT EXISTS `user_users_groups` (
  `user_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `fk_role_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user_users_groups`
--

INSERT INTO `user_users_groups` (`user_id`, `group_id`) VALUES
(50, 5),
(52, 5),
(53, 5),
(54, 5),
(55, 5),
(56, 5),
(58, 5),
(59, 5),
(60, 5),
(61, 5),
(62, 5),
(63, 5),
(64, 5),
(65, 5),
(66, 5),
(67, 5),
(68, 5),
(69, 5),
(70, 5),
(71, 5),
(72, 5),
(73, 5),
(74, 5),
(75, 5),
(76, 5),
(77, 5),
(78, 5),
(79, 5),
(80, 5),
(81, 5),
(82, 5),
(83, 5),
(84, 5),
(85, 5),
(86, 5),
(87, 5),
(88, 5),
(89, 5),
(90, 5),
(91, 5),
(92, 5),
(93, 5),
(94, 5),
(95, 5),
(96, 5),
(97, 5),
(98, 5);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
