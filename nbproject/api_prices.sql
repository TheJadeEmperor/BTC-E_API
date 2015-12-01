-- phpMyAdmin SQL Dump
-- version 3.5.8.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 12, 2015 at 01:34 PM
-- Server version: 5.5.42-37.1-log
-- PHP Version: 5.4.23

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `codegeas_cc`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_prices`
--

DROP TABLE IF EXISTS `api_prices`;
CREATE TABLE IF NOT EXISTS `api_prices` (
  `count` int(11) NOT NULL,
  `price` float NOT NULL,
  PRIMARY KEY (`count`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `api_prices`
--

INSERT INTO `api_prices` (`count`, `price`) VALUES
(1, 326.25),
(2, 324.805),
(3, 322.133),
(4, 327.44),
(5, 323.58),
(6, 321.312),
(7, 324.7),
(8, 324),
(9, 327),
(10, 327.028),
(11, 326),
(12, 322.751),
(13, 323.221),
(14, 323.9),
(15, 331.191),
(16, 329.011),
(17, 331.503),
(18, 329.605),
(19, 328.223),
(20, 335.2),
(21, 338.722),
(22, 332.5),
(23, 329.5),
(24, 327.548),
(25, 327),
(26, 326.8),
(27, 326.001),
(28, 329.776),
(29, 320.727);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
