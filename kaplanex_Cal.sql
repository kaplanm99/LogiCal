-- phpMyAdmin SQL Dump
-- version 3.4.11.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 04, 2013 at 10:39 AM
-- Server version: 5.5.30
-- PHP Version: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `kaplanex_Cal`
--

-- --------------------------------------------------------

--
-- Table structure for table `TaskEvents`
--

DROP TABLE IF EXISTS `TaskEvents`;
CREATE TABLE IF NOT EXISTS `TaskEvents` (
  `event_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `email` varchar(200) NOT NULL,
  `start_time` varchar(29) NOT NULL,
  `end_time` varchar(29) NOT NULL,
  `added_to_g_cal` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=101 ;

-- --------------------------------------------------------

--
-- Table structure for table `Tasks`
--

DROP TABLE IF EXISTS `Tasks`;
CREATE TABLE IF NOT EXISTS `Tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(200) NOT NULL,
  `what` varchar(300) NOT NULL,
  `due_date` varchar(10) NOT NULL,
  `due_hour` int(2) NOT NULL,
  `due_minute` int(2) NOT NULL,
  `due_is_am` tinyint(1) NOT NULL,
  `estimated_effort` double NOT NULL,
  `task_distribution` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
