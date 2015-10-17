-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.1.35-community


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema rtfund
--

CREATE DATABASE IF NOT EXISTS rtfund;
USE rtfund;

--
-- Definition of table `DMS_documents`
--

DROP TABLE IF EXISTS `DMS_documents`;
CREATE TABLE `DMS_documents` (
  `DocumentID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد سند',
  `DocDesc` varchar(200) DEFAULT NULL COMMENT 'توضیحات کلی',
  `DocType` int(10) unsigned NOT NULL COMMENT 'نوع سند',
  `ObjectType` varchar(50) DEFAULT NULL COMMENT 'نوع آبجکت',
  `ObjectID` int(10) unsigned DEFAULT NULL COMMENT 'کد آبجکت',
  `FileType` varchar(20) DEFAULT NULL COMMENT 'نوع فایل',
  `FileContent` tinyblob COMMENT 'قسمتی از محتوای فایل',
  `IsConfirm` enum('YES','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`DocumentID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `DMS_documents`
--

/*!40000 ALTER TABLE `DMS_documents` DISABLE KEYS */;
INSERT INTO `DMS_documents` (`DocumentID`,`DocDesc`,`DocType`,`ObjectType`,`ObjectID`,`FileType`,`FileContent`,`IsConfirm`) VALUES 
 (10,NULL,1,NULL,NULL,NULL,NULL,'NO');
/*!40000 ALTER TABLE `DMS_documents` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
