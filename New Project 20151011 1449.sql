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
-- Definition of table `BSC_persons`
--

DROP TABLE IF EXISTS `BSC_persons`;
CREATE TABLE `BSC_persons` (
  `PersonID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد فرد',
  `UserName` varchar(100) NOT NULL COMMENT 'کلمه کاربری',
  `UserPass` varchar(60) NOT NULL COMMENT 'رمز عبور',
  `IsReal` enum('YES','NO') NOT NULL COMMENT 'حقیقی است؟',
  `fname` varchar(500) DEFAULT NULL COMMENT 'عنوان',
  `lname` varchar(200) DEFAULT NULL,
  `CompanyName` varchar(200) DEFAULT NULL,
  `NationalID` varchar(10) DEFAULT NULL COMMENT 'کد ملی',
  `EconomicID` varchar(10) DEFAULT NULL COMMENT 'کد اقتصادی',
  `PhoneNo` varchar(45) DEFAULT NULL COMMENT 'تلفن',
  `mobile` varchar(45) DEFAULT NULL COMMENT 'موبایل',
  `address` varchar(500) DEFAULT NULL COMMENT 'آدرس',
  `email` varchar(100) DEFAULT NULL,
  `IsStaff` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `IsCustomer` enum('YES','NO') CHARACTER SET latin1 NOT NULL DEFAULT 'YES' COMMENT 'مشتری',
  `IsShareholder` enum('YES','NO') NOT NULL DEFAULT 'NO' COMMENT 'سهامدار',
  `IsAgent` enum('YES','NO') NOT NULL DEFAULT 'NO' COMMENT 'عامل',
  `IsSupporter` enum('YES','NO') NOT NULL DEFAULT 'NO' COMMENT 'حامی',
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `PostID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`PersonID`)
) ENGINE=InnoDB AUTO_INCREMENT=1006 DEFAULT CHARSET=utf8 COMMENT='ذینفعان';

--
-- Dumping data for table `BSC_persons`
--

/*!40000 ALTER TABLE `BSC_persons` DISABLE KEYS */;
INSERT INTO `BSC_persons` (`PersonID`,`UserName`,`UserPass`,`IsReal`,`fname`,`lname`,`CompanyName`,`NationalID`,`EconomicID`,`PhoneNo`,`mobile`,`address`,`email`,`IsStaff`,`IsCustomer`,`IsShareholder`,`IsAgent`,`IsSupporter`,`IsActive`,`PostID`) VALUES 
 (1000,'admin','$P$BCy9D77Tk5UrJibOCgIkum/NYvq3Ym1','YES','شبنم','جعفرخانی','','0943021723',NULL,NULL,NULL,'sdfsdf',NULL,'YES','YES','YES','NO','YES','YES',1),
 (1005,'park','$P$BcoXpMFz3xw6B108dtdAtm.iA9V5pa0','NO',' ',NULL,'پارک علم و فناوری',NULL,NULL,NULL,NULL,NULL,'park@us.com','NO','YES','NO','NO','NO','YES',NULL);
/*!40000 ALTER TABLE `BSC_persons` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
