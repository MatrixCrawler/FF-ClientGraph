-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:               5.6.24 - MySQL Community Server (GPL)
-- Server Betriebssystem:        Win32
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Exportiere Struktur von Tabelle matrixcrawler_ff.data_timestamp
DROP TABLE IF EXISTS `data_timestamp`;
CREATE TABLE IF NOT EXISTS `data_timestamp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` datetime NOT NULL,
  `timezone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt


-- Exportiere Struktur von Tabelle matrixcrawler_ff.node
DROP TABLE IF EXISTS `node`;
CREATE TABLE IF NOT EXISTS `node` (
  `nodeId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`nodeId`),
  UNIQUE KEY `UNIQ_857FE8459621787` (`nodeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt


-- Exportiere Struktur von Tabelle matrixcrawler_ff.node_stats
DROP TABLE IF EXISTS `node_stats`;
CREATE TABLE IF NOT EXISTS `node_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `clients` int(11) NOT NULL,
  `dataTimestamp_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4EECF556460D9FD7` (`node_id`),
  KEY `IDX_4EECF556966C18C8` (`dataTimestamp_id`),
  CONSTRAINT `FK_4EECF556460D9FD7` FOREIGN KEY (`node_id`) REFERENCES `node` (`nodeId`),
  CONSTRAINT `FK_4EECF556966C18C8` FOREIGN KEY (`dataTimestamp_id`) REFERENCES `data_timestamp` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
