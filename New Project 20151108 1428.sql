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
-- Definition of table `ACC_CostCodes`
--

DROP TABLE IF EXISTS `ACC_CostCodes`;
CREATE TABLE `ACC_CostCodes` (
  `CostID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد ردیف',
  `level1` int(10) unsigned NOT NULL COMMENT 'سطح 1',
  `level2` int(10) unsigned DEFAULT NULL COMMENT 'سطح 2',
  `level3` int(10) unsigned DEFAULT NULL COMMENT 'سطح 3',
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `CostCode` varchar(45) DEFAULT NULL COMMENT 'کد حساب',
  PRIMARY KEY (`CostID`),
  KEY `FK_ACC_CostCodes_1` (`level1`),
  KEY `FK_ACC_CostCodes_2` (`level2`),
  KEY `FK_ACC_CostCodes_3` (`level3`),
  CONSTRAINT `FK_ACC_CostCodes_1` FOREIGN KEY (`level1`) REFERENCES `acc_blocks` (`BlockID`),
  CONSTRAINT `FK_ACC_CostCodes_2` FOREIGN KEY (`level2`) REFERENCES `acc_blocks` (`BlockID`),
  CONSTRAINT `FK_ACC_CostCodes_3` FOREIGN KEY (`level3`) REFERENCES `acc_blocks` (`BlockID`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8 COMMENT='کدهای حساب';

--
-- Dumping data for table `ACC_CostCodes`
--

/*!40000 ALTER TABLE `ACC_CostCodes` DISABLE KEYS */;
INSERT INTO `ACC_CostCodes` (`CostID`,`level1`,`level2`,`level3`,`IsActive`,`CostCode`) VALUES 
 (1,2,33,95,'YES','10-101-01'),
 (2,2,36,99,'YES','10-107-07'),
 (3,2,39,100,'YES','10-110-10'),
 (4,2,39,101,'YES','10-110-11'),
 (5,2,39,102,'YES','10-110-12'),
 (6,2,39,103,'YES','10-110-15'),
 (7,2,39,104,'YES','10-110-16'),
 (8,2,39,105,'YES','10-110-17'),
 (9,2,39,106,'YES','10-110-19'),
 (10,2,39,107,'YES','10-110-20'),
 (11,2,39,108,'YES','10-110-23'),
 (12,2,39,109,'YES','10-110-24'),
 (13,2,39,110,'YES','10-110-27'),
 (14,2,41,111,'YES','10-190-03'),
 (15,2,41,112,'YES','10-190-04'),
 (16,5,34,96,'YES','20-102-01'),
 (17,5,34,97,'YES','20-102-02'),
 (18,5,34,98,'YES','20-102-04'),
 (19,5,43,113,'YES','20-209-10'),
 (20,5,44,114,'YES','20-210-01'),
 (21,5,46,115,'YES','20-310-01'),
 (22,5,46,116,'YES','20-310-02'),
 (23,8,49,117,'YES','30-500-01'),
 (24,8,49,118,'YES','30-500-04'),
 (25,8,49,119,'YES','30-500-06'),
 (26,8,49,120,'YES','30-500-07'),
 (27,8,49,121,'YES','30-500-08'),
 (28,8,49,122,'YES','30-500-10'),
 (29,8,49,123,'YES','30-500-11'),
 (30,8,49,124,'YES','30-500-12'),
 (31,8,49,125,'YES','30-500-13'),
 (32,8,49,126,'YES','30-500-14'),
 (33,8,49,127,'YES','30-500-15'),
 (34,8,49,128,'YES','30-500-18'),
 (35,8,49,129,'YES','30-500-19'),
 (36,8,49,130,'YES','30-500-22'),
 (37,8,49,131,'YES','30-500-23'),
 (38,8,49,132,'YES','30-500-26'),
 (39,8,49,133,'YES','30-500-27'),
 (40,8,49,134,'YES','30-500-28'),
 (41,8,49,135,'YES','30-500-29'),
 (42,8,55,136,'YES','30-660-02'),
 (43,8,55,137,'YES','30-660-03'),
 (44,8,55,138,'YES','30-660-04'),
 (45,8,55,139,'YES','30-660-05'),
 (46,8,71,140,'YES','30-750-01'),
 (47,8,71,141,'YES','30-750-02'),
 (48,8,71,142,'YES','30-750-03'),
 (49,8,71,143,'YES','30-750-04'),
 (50,8,71,144,'YES','30-750-05'),
 (51,8,71,145,'YES','30-750-06'),
 (52,8,71,146,'YES','30-750-07'),
 (53,8,71,147,'YES','30-750-08'),
 (54,8,71,148,'YES','30-750-09'),
 (55,8,71,149,'YES','30-750-15'),
 (56,8,71,150,'YES','30-750-16'),
 (57,8,71,151,'YES','30-750-17'),
 (58,8,72,152,'YES','30-760-04'),
 (59,8,72,153,'YES','30-760-05'),
 (60,8,72,154,'YES','30-760-06'),
 (61,8,72,155,'YES','30-760-94'),
 (62,8,72,156,'YES','30-760-96'),
 (63,8,75,157,'YES','30-904-01'),
 (64,8,75,158,'YES','30-904-02'),
 (65,8,75,159,'YES','30-904-03'),
 (66,8,75,160,'YES','30-904-04'),
 (67,8,75,161,'YES','30-904-05'),
 (68,8,76,162,'YES','30-905-01'),
 (69,8,76,163,'YES','30-905-02'),
 (70,8,76,164,'YES','30-905-03'),
 (71,8,76,165,'YES','30-905-04'),
 (72,8,76,166,'YES','30-905-05'),
 (73,2,39,NULL,'YES','10110'),
 (74,8,46,NULL,'YES','30310'),
 (75,5,34,NULL,'YES','20102'),
 (76,2,33,NULL,'YES','10101'),
 (77,8,55,NULL,'YES','30660');
/*!40000 ALTER TABLE `ACC_CostCodes` ENABLE KEYS */;


--
-- Definition of table `ACC_DocChecks`
--

DROP TABLE IF EXISTS `ACC_DocChecks`;
CREATE TABLE `ACC_DocChecks` (
  `DocID` int(10) unsigned NOT NULL COMMENT 'کد سند',
  `CheckID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد چک',
  `AccountID` int(10) unsigned DEFAULT NULL COMMENT 'کد حساب',
  `CheckNo` int(10) unsigned DEFAULT NULL COMMENT 'شماره چک',
  `CheckDate` date NOT NULL COMMENT 'تاریخ چک',
  `amount` decimal(15,0) NOT NULL COMMENT 'مبلغ',
  `CheckStatus` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT 'وضعیت چک',
  `description` varchar(500) DEFAULT NULL COMMENT 'توضیحات',
  `TafsiliID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`CheckID`),
  KEY `FK_ACC_DocChecks_1` (`AccountID`),
  KEY `FK_ACC_DocChecks_2` (`DocID`),
  CONSTRAINT `FK_ACC_DocChecks_1` FOREIGN KEY (`AccountID`) REFERENCES `acc_accounts` (`AccountID`),
  CONSTRAINT `FK_ACC_DocChecks_2` FOREIGN KEY (`DocID`) REFERENCES `acc_docs` (`DocID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_DocChecks`
--

/*!40000 ALTER TABLE `ACC_DocChecks` DISABLE KEYS */;
INSERT INTO `ACC_DocChecks` (`DocID`,`CheckID`,`AccountID`,`CheckNo`,`CheckDate`,`amount`,`CheckStatus`,`description`,`TafsiliID`) VALUES 
 (34,1,2,1245,'2015-10-26','58800000',1,NULL,13),
 (35,2,NULL,NULL,'2015-10-26','58800000',1,' مرحله مرحله دوم وام شماره 16',13),
 (36,3,NULL,NULL,'2015-11-22','564000000',1,' پرداخت مرحله اول وام شماره 1000',NULL),
 (38,4,NULL,NULL,'2015-11-22','564000000',1,' پرداخت مرحله اول وام شماره 1000',25);
/*!40000 ALTER TABLE `ACC_DocChecks` ENABLE KEYS */;


--
-- Definition of table `ACC_DocItems`
--

DROP TABLE IF EXISTS `ACC_DocItems`;
CREATE TABLE `ACC_DocItems` (
  `DocID` int(10) unsigned NOT NULL COMMENT 'کد سند',
  `ItemID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد ردیف',
  `CostID` int(10) unsigned NOT NULL COMMENT 'کد حساب',
  `TafsiliType` smallint(5) unsigned DEFAULT NULL,
  `TafsiliID` int(10) unsigned DEFAULT NULL COMMENT 'کد تفصیلی',
  `Tafsili2Type` smallint(5) unsigned DEFAULT NULL,
  `Tafsili2ID` int(10) unsigned DEFAULT NULL,
  `DebtorAmount` decimal(15,0) NOT NULL DEFAULT '0' COMMENT 'مبلغ بدهکار',
  `CreditorAmount` decimal(15,0) NOT NULL DEFAULT '0' COMMENT 'مبلغ بستانکار',
  `details` varchar(500) DEFAULT NULL COMMENT 'جزئیات',
  `locked` enum('YES','NO') NOT NULL DEFAULT 'NO' COMMENT 'قفل بودن ردیف',
  `SourceType` varchar(50) DEFAULT NULL,
  `SourceID` int(10) unsigned DEFAULT NULL,
  `SourceID2` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ItemID`),
  KEY `FK_ACC_DocItems_1` (`DocID`),
  KEY `FK_ACC_DocItems_2` (`CostID`),
  KEY `FK_ACC_DocItems_3` (`TafsiliID`),
  CONSTRAINT `FK_ACC_DocItems_1` FOREIGN KEY (`DocID`) REFERENCES `acc_docs` (`DocID`),
  CONSTRAINT `FK_ACC_DocItems_2` FOREIGN KEY (`CostID`) REFERENCES `acc_costcodes` (`CostID`),
  CONSTRAINT `FK_ACC_DocItems_3` FOREIGN KEY (`TafsiliID`) REFERENCES `acc_tafsilis` (`TafsiliID`)
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_DocItems`
--

/*!40000 ALTER TABLE `ACC_DocItems` DISABLE KEYS */;
INSERT INTO `ACC_DocItems` (`DocID`,`ItemID`,`CostID`,`TafsiliType`,`TafsiliID`,`Tafsili2Type`,`Tafsili2ID`,`DebtorAmount`,`CreditorAmount`,`details`,`locked`,`SourceType`,`SourceID`,`SourceID2`) VALUES 
 (18,12,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (18,13,74,2,15,1,12,'0','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (18,14,74,2,16,1,12,'0','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (22,24,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (22,25,74,2,15,NULL,NULL,'0','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (22,26,74,2,16,NULL,NULL,'0','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (23,27,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (23,28,74,2,15,NULL,NULL,'2','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (23,29,74,2,16,NULL,NULL,'2','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (24,30,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (24,31,74,2,15,NULL,NULL,'2','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (24,32,74,2,16,NULL,NULL,'2','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (25,33,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (25,34,74,2,15,NULL,NULL,'0','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (25,35,74,2,16,NULL,NULL,'0','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (26,36,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (26,37,74,2,15,NULL,NULL,'0','965638',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (26,38,74,2,16,NULL,NULL,'0','2309136',NULL,'YES','PAY_LOAN_PART',6,NULL),
 (27,39,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (27,40,74,2,15,NULL,NULL,'0','1776621',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (27,41,74,2,16,NULL,NULL,'0','1522818',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (28,42,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (28,43,76,1,13,1,12,'0','57000000',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (28,44,74,2,15,NULL,NULL,'0','3000000',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (28,45,74,2,15,NULL,NULL,'0','1776621',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (28,46,75,1,12,NULL,NULL,'1776621','0',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (28,47,74,2,16,NULL,NULL,'0','1522818',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (28,48,75,1,12,NULL,NULL,'1522818','0',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (29,49,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',16,2),
 (29,50,76,1,13,1,12,'0','57000000',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (29,51,74,2,15,NULL,NULL,'0','3000000','کارمزد دوره تنفس','YES','PAY_LOAN_PART',2,NULL),
 (29,52,74,2,15,NULL,NULL,'0','1776621','کارمزد دوره تنفس','YES','PAY_LOAN_PART',2,NULL),
 (29,53,75,1,12,NULL,NULL,'1776621','0','کارمزد دوره تنفس','YES','PAY_LOAN_PART',2,NULL),
 (29,54,74,2,16,NULL,NULL,'0','1522818','کارمزد دوره تنفس','YES','PAY_LOAN_PART',2,NULL),
 (29,55,75,1,12,NULL,NULL,'1522818','0','کارمزد دوره تنفس','YES','PAY_LOAN_PART',2,NULL),
 (29,56,75,1,12,NULL,NULL,'60000000','0','کارمزد دوره تنفس','YES','PAY_LOAN_PART',2,NULL),
 (29,57,77,1,12,NULL,NULL,'0','60000000','کارمزد دوره تنفس','YES','PAY_LOAN_PART',2,NULL),
 (30,58,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',16,2),
 (30,59,76,NULL,NULL,NULL,NULL,'0','57000000',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (30,60,74,2,15,NULL,NULL,'0','3000000','کارمزد دوره تنفس','YES','PAY_LOAN_PART',2,NULL),
 (30,61,74,2,15,NULL,NULL,'0','1776621',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (30,62,75,1,12,NULL,NULL,'1776621','0',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (30,63,74,2,16,NULL,NULL,'0','1522818',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (30,64,75,1,12,NULL,NULL,'1522818','0',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (30,65,75,1,12,NULL,NULL,'60000000','0',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (30,66,77,1,12,NULL,NULL,'0','60000000',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (31,67,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',16,2),
 (31,68,76,3,21,NULL,NULL,'0','57000000',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (31,69,74,2,15,NULL,NULL,'0','3000000','کارمزد دوره تنفس','YES','PAY_LOAN_PART',2,NULL),
 (31,70,74,2,15,NULL,NULL,'0','1776621',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (31,71,75,1,12,NULL,NULL,'1776621','0',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (31,72,74,2,16,NULL,NULL,'0','1522818',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (31,73,75,1,12,NULL,NULL,'1522818','0',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (31,74,75,1,12,NULL,NULL,'60000000','0',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (31,75,77,1,12,NULL,NULL,'0','60000000',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (32,76,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',16,2),
 (32,77,76,3,21,NULL,NULL,'0','57000000',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (32,78,74,2,15,NULL,NULL,'0','3000000','کارمزد دوره تنفس','YES','PAY_LOAN_PART',2,NULL),
 (32,79,74,2,15,NULL,NULL,'0','1776621',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (32,80,75,1,12,NULL,NULL,'1776621','0',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (32,81,74,2,16,NULL,NULL,'0','1522818',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (32,82,75,1,12,NULL,NULL,'1522818','0',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (32,83,75,1,12,NULL,NULL,'60000000','0',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (32,84,77,1,12,NULL,NULL,'0','60000000',NULL,'YES','PAY_LOAN_PART',2,NULL),
 (33,85,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',16,3),
 (33,86,76,3,NULL,NULL,NULL,'0','58800000',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (33,87,74,2,15,NULL,NULL,'0','1200000','کارمزد دوره تنفس','YES','PAY_LOAN_PART',3,NULL),
 (33,88,74,2,15,NULL,NULL,'0','1940012',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (33,89,75,1,12,NULL,NULL,'1940012','0',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (33,90,74,2,16,NULL,NULL,'0','3795676',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (33,91,75,1,12,NULL,NULL,'3795676','0',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (33,92,74,2,17,NULL,NULL,'0','843484',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (33,93,75,1,12,NULL,NULL,'843484','0',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (33,94,75,1,12,NULL,NULL,'60000000','0',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (33,95,77,1,12,NULL,NULL,'0','60000000',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (34,96,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',16,3),
 (34,97,76,3,NULL,NULL,NULL,'0','58800000',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (34,98,74,2,15,NULL,NULL,'0','1200000','کارمزد دوره تنفس','YES','PAY_LOAN_PART',3,NULL),
 (34,99,74,2,15,NULL,NULL,'0','1940012',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (34,100,75,1,12,NULL,NULL,'1940012','0',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (34,101,74,2,16,NULL,NULL,'0','3795676',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (34,102,75,1,12,NULL,NULL,'3795676','0',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (34,103,74,2,17,NULL,NULL,'0','843484',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (34,104,75,1,12,NULL,NULL,'843484','0',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (34,105,75,1,12,NULL,NULL,'60000000','0',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (34,106,77,1,12,NULL,NULL,'0','60000000',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (35,107,73,1,13,1,12,'60000000','0',NULL,'YES','PAY_LOAN_PART',16,3),
 (35,108,76,3,21,NULL,NULL,'0','58800000',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (35,109,74,2,15,NULL,NULL,'0','1200000','کارمزد دوره تنفس','YES','PAY_LOAN_PART',3,NULL),
 (35,110,74,2,15,NULL,NULL,'0','1940012',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (35,111,75,1,12,NULL,NULL,'1940012','0',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (35,112,74,2,16,NULL,NULL,'0','3795676',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (35,113,75,1,12,NULL,NULL,'3795676','0',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (35,114,74,2,17,NULL,NULL,'0','843484',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (35,115,75,1,12,NULL,NULL,'843484','0',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (35,116,75,1,12,NULL,NULL,'60000000','0',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (35,117,77,1,12,NULL,NULL,'0','60000000',NULL,'YES','PAY_LOAN_PART',3,NULL),
 (36,118,73,1,NULL,1,12,'600000000','0',NULL,'YES','PAY_LOAN_PART',1000,1),
 (36,119,76,3,NULL,NULL,NULL,'0','564000000',NULL,'YES','PAY_LOAN_PART',1,NULL),
 (36,120,74,2,15,NULL,NULL,'0','36000000','کارمزد دوره تنفس','YES','PAY_LOAN_PART',1,NULL),
 (36,121,74,2,15,NULL,NULL,'0','16800925',NULL,'YES','PAY_LOAN_PART',1,NULL),
 (36,122,75,1,12,NULL,NULL,'16800925','0',NULL,'YES','PAY_LOAN_PART',1,NULL),
 (36,123,74,2,16,NULL,NULL,'0','22910353',NULL,'YES','PAY_LOAN_PART',1,NULL),
 (36,124,75,1,12,NULL,NULL,'22910353','0',NULL,'YES','PAY_LOAN_PART',1,NULL),
 (36,125,75,1,12,NULL,NULL,'600000000','0',NULL,'YES','PAY_LOAN_PART',1,NULL),
 (36,126,77,1,12,NULL,NULL,'0','600000000',NULL,'YES','PAY_LOAN_PART',1,NULL),
 (38,127,73,1,25,1,12,'600000000','0',NULL,'YES','PAY_LOAN_PART',1000,1),
 (38,128,76,3,NULL,NULL,NULL,'0','564000000',NULL,'YES','PAY_LOAN_PART',1,NULL),
 (38,129,74,2,15,NULL,NULL,'0','36000000','کارمزد دوره تنفس','YES','PAY_LOAN_PART',1,NULL),
 (38,130,74,2,15,NULL,NULL,'0','16800925',NULL,'YES','PAY_LOAN_PART',1,NULL),
 (38,131,75,1,12,NULL,NULL,'16800925','0',NULL,'YES','PAY_LOAN_PART',1,NULL),
 (38,132,74,2,16,NULL,NULL,'0','22910353',NULL,'YES','PAY_LOAN_PART',1,NULL),
 (38,133,75,1,12,NULL,NULL,'22910353','0',NULL,'YES','PAY_LOAN_PART',1,NULL),
 (38,134,75,1,12,NULL,NULL,'600000000','0',NULL,'YES','PAY_LOAN_PART',1,NULL),
 (38,135,77,1,12,NULL,NULL,'0','600000000',NULL,'YES','PAY_LOAN_PART',1,NULL);
/*!40000 ALTER TABLE `ACC_DocItems` ENABLE KEYS */;


--
-- Definition of table `ACC_UserState`
--

DROP TABLE IF EXISTS `ACC_UserState`;
CREATE TABLE `ACC_UserState` (
  `PersonID` int(10) unsigned NOT NULL,
  `BranchID` smallint(5) unsigned NOT NULL,
  `CycleID` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`PersonID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ACC_UserState`
--

/*!40000 ALTER TABLE `ACC_UserState` DISABLE KEYS */;
INSERT INTO `ACC_UserState` (`PersonID`,`BranchID`,`CycleID`) VALUES 
 (1000,1,1394);
/*!40000 ALTER TABLE `ACC_UserState` ENABLE KEYS */;


--
-- Definition of table `ACC_accounts`
--

DROP TABLE IF EXISTS `ACC_accounts`;
CREATE TABLE `ACC_accounts` (
  `AccountID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد حساب',
  `BankID` int(10) unsigned NOT NULL COMMENT 'کد بانک',
  `BranchID` int(10) unsigned NOT NULL COMMENT 'کد شعبه',
  `AccountDesc` varchar(500) NOT NULL COMMENT 'عنوان حساب',
  `IsActive` enum('YES','NO') NOT NULL,
  `AccountNo` varchar(500) NOT NULL COMMENT 'شماره حساب',
  `AccountType` int(10) unsigned NOT NULL COMMENT 'نوع حساب',
  `TafsiliID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`AccountID`),
  KEY `FK_ACC_accounts_1` (`BankID`),
  CONSTRAINT `FK_ACC_accounts_1` FOREIGN KEY (`BankID`) REFERENCES `acc_banks` (`BankID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_accounts`
--

/*!40000 ALTER TABLE `ACC_accounts` DISABLE KEYS */;
INSERT INTO `ACC_accounts` (`AccountID`,`BankID`,`BranchID`,`AccountDesc`,`IsActive`,`AccountNo`,`AccountType`,`TafsiliID`) VALUES 
 (1,1,1,'98098 جاری ملی','YES','3000098098',1,NULL),
 (2,1,1,'ملی 780005 شعبه پردیس','YES','7800005',1,NULL);
/*!40000 ALTER TABLE `ACC_accounts` ENABLE KEYS */;


--
-- Definition of table `ACC_banks`
--

DROP TABLE IF EXISTS `ACC_banks`;
CREATE TABLE `ACC_banks` (
  `BankID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد بانک',
  `BranchID` int(10) unsigned NOT NULL,
  `BankDesc` varchar(500) NOT NULL COMMENT 'عنوان بانک',
  `IsAvtive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  PRIMARY KEY (`BankID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_banks`
--

/*!40000 ALTER TABLE `ACC_banks` DISABLE KEYS */;
INSERT INTO `ACC_banks` (`BankID`,`BranchID`,`BankDesc`,`IsAvtive`) VALUES 
 (1,1,'ملی','YES');
/*!40000 ALTER TABLE `ACC_banks` ENABLE KEYS */;


--
-- Definition of table `ACC_blocks`
--

DROP TABLE IF EXISTS `ACC_blocks`;
CREATE TABLE `ACC_blocks` (
  `BlockID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد بلاک',
  `LevelID` smallint(5) unsigned NOT NULL,
  `BlockCode` varchar(10) NOT NULL COMMENT 'کد سطح',
  `BlockDesc` varchar(500) NOT NULL COMMENT 'عنوان بلاک',
  `essence` enum('DEBTOR','CREDITOR','NONE') NOT NULL DEFAULT 'NONE' COMMENT 'ماهیت',
  `IsActive` enum('YES','NO') NOT NULL,
  `parent` int(10) unsigned NOT NULL,
  `AccCode` varchar(45) NOT NULL,
  PRIMARY KEY (`BlockID`)
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_blocks`
--

/*!40000 ALTER TABLE `ACC_blocks` DISABLE KEYS */;
INSERT INTO `ACC_blocks` (`BlockID`,`LevelID`,`BlockCode`,`BlockDesc`,`essence`,`IsActive`,`parent`,`AccCode`) VALUES 
 (2,1,'10','دارائيهاي جاري','NONE','YES',0,'10'),
 (3,1,'11','دارائيهاي غير جاري','NONE','YES',0,'11'),
 (4,1,'19','ساير دارائيها','NONE','YES',0,'19'),
 (5,1,'20','بدهي هاي جاري','NONE','YES',0,'20'),
 (6,1,'21','بدهي هاي بلند مدت','NONE','YES',0,'21'),
 (7,1,'29','ساير بدهي ها','NONE','YES',0,'29'),
 (8,1,'30','حقوق صاحبان سهام','NONE','YES',0,'30'),
 (9,1,'40','درآمدهاي عملياتي','NONE','YES',0,'40'),
 (10,1,'50','قيمت تمام شده','NONE','YES',0,'50'),
 (12,1,'69','درآمدها و هزينه هاي غير عملياتي','NONE','YES',0,'69'),
 (13,1,'70','اقلام غير مترقبه','NONE','YES',0,'70'),
 (14,1,'80','جذب سربار','NONE','YES',0,'80'),
 (15,1,'90','ساير حسابها','NONE','YES',0,'90'),
 (16,1,'95','سپرده','NONE','YES',0,'95'),
 (32,2,'100','صندوق ','NONE','YES',20,'100'),
 (33,2,'101','بانكها','NONE','YES',10,'101'),
 (34,2,'102','سپرده هاي بلند مدت ','NONE','YES',20,'102'),
 (35,2,'104','تمبر مالياتي ','NONE','YES',20,'104'),
 (36,2,'107','چکهاي دريافتني','NONE','YES',10,'107'),
 (37,2,'108',' چکهاي مدت دار جهت قردادها','NONE','YES',20,'108'),
 (38,2,'109','تنخواه گردان','NONE','YES',70,'109'),
 (39,2,'110','وام پرداختي ','NONE','YES',10,'110'),
 (40,2,'180','اموال منقول اثاثيه','NONE','YES',10,'180'),
 (41,2,'190','بدهکاران متفرقه','NONE','YES',10,'190'),
 (42,2,'200','بستانكاران (حسابهاي پرداختني)','NONE','YES',20,'200'),
 (43,2,'209','حساب پس انداز','NONE','YES',20,'209'),
 (44,2,'210','حساب سپرده','NONE','YES',20,'210'),
 (45,2,'300','ضمانت نامه ها','NONE','YES',10,'300'),
 (46,2,'310','کارمزد پرداختي','NONE','YES',20,'310'),
 (47,2,'315','تعديل سود وزيان','NONE','YES',20,'315'),
 (48,2,'499','حساب مرکز( شعبه مرکزي)','NONE','YES',20,'499'),
 (49,2,'500','هزينه ها','NONE','YES',30,'500'),
 (50,2,'550','سود وکارمزد پرداختي ','NONE','YES',20,'550'),
 (51,2,'610','ذخيره استهلاک اثاثيه','NONE','YES',30,'610'),
 (52,2,'620','ذخيره هزينه تلفن و00000','NONE','YES',30,'620'),
 (53,2,'630','ذخيره ماليات ','NONE','YES',30,'630'),
 (54,2,'635','سايرذخاير','NONE','YES',20,'635'),
 (55,2,'660','اسناد وحسابهاي پرداختني','NONE','YES',30,'660'),
 (56,2,'689','وجوه ضمانتنامه هاي نقدي','NONE','YES',20,'689'),
 (57,2,'690','سپرده ضمانت نامه ها','NONE','YES',30,'690'),
 (58,2,'700','تعهدات ضمانت نامه ها','NONE','YES',30,'700'),
 (59,2,'721','وجوه واريزي مجتمع آموزش عالي ق','NONE','YES',20,'721'),
 (60,2,'722','وجوه واريزي مجتمع آموزش عالي ق','NONE','YES',20,'722'),
 (61,2,'731','وجوه واريزي علم تا عمل 92 (تعه','NONE','YES',20,'731'),
 (62,2,'732','وجوه واريزي علم تا عمل 92(تعهد','NONE','YES',20,'732'),
 (63,2,'733','وجوه واريزي صندوق نوآوري و شکو','NONE','YES',20,'733'),
 (64,2,'734','وجوه واريزي صندوق نوآوري و شکو','NONE','YES',20,'734'),
 (65,2,'735','وجوه فناوريهاي نوين (قراردادسه','NONE','YES',20,'735'),
 (66,2,'736','وجوه فناوريهاي نوين (قرارداد س','NONE','YES',20,'736'),
 (67,2,'737','وجوه فناوريهاي نوين (قرارداد س','NONE','YES',20,'737'),
 (68,2,'738','وجوه فناوريهاي نوين (قرارداد س','NONE','YES',20,'738'),
 (69,2,'739','وجوه واريزي صندوق حمايت ازپژوه','NONE','YES',20,'739'),
 (70,2,'740','وجوه واريزي صندوق حمايت ازپژوه','NONE','YES',30,'740'),
 (71,2,'750','کارمزد وسود دريافتي سال جاري','NONE','YES',30,'750'),
 (72,2,'760','کارمزد و سود سالهاي آينده ','NONE','YES',30,'760'),
 (73,2,'765','حساب سود وزيان ','NONE','YES',20,'765'),
 (74,2,'770','سود ويژه ','NONE','YES',30,'770'),
 (75,2,'904','حساب انتظامي','NONE','YES',30,'904'),
 (76,2,'905','طرف حساب انتظامي','NONE','YES',30,'905'),
 (95,3,'01','بانكها-اقتصاد نوين بلوار سجاد','NONE','YES',101,'101-01'),
 (96,3,'01','سپرده 99501','NONE','YES',102,'102-01'),
 (97,3,'02','سپرده 1-9917','NONE','YES',102,'102-02'),
 (98,3,'04','سپرده 2-995','NONE','YES',102,'102-04'),
 (99,3,'07','خرج چك','NONE','YES',107,'107-07'),
 (100,3,'10','وام پرداختي -تسهيلات قرض الحسنه ','NONE','YES',110,'110-10'),
 (101,3,'11','وام پرداختي -کارگشائي','NONE','YES',110,'110-11'),
 (102,3,'12','وام پرداختي -ضروري','NONE','YES',110,'110-12'),
 (103,3,'15','وام پرداختي -تسهيلات پژوهشي','NONE','YES',110,'110-15'),
 (104,3,'16','وام پرداختي -تسهيلات عامليت با دانشگاه','NONE','YES',110,'110-16'),
 (105,3,'17','وام پرداختي -تسهيلات علم تا عمل','NONE','YES',110,'110-17'),
 (106,3,'19','وام پرداختي -تسهيلات عامليت مجتم فني ق','NONE','YES',110,'110-19'),
 (107,3,'20','وام پرداختي -قرض الحسنه خيرين ','NONE','YES',110,'110-20'),
 (108,3,'23','وام پرداختي -تسهيلات علم تاعمل 92','NONE','YES',110,'110-23'),
 (109,3,'24','وام پرداختي -تسهيلات فناوريهاي نوين (س','NONE','YES',110,'110-24'),
 (110,3,'27','وام پرداختي -تسهيلات نوآوري و شکوفايي','NONE','YES',110,'110-27'),
 (111,3,'03','شرکت جهان انديشه ','NONE','YES',190,'190-03'),
 (112,3,'04',' هزينه هاي دادرسي مشتريان','NONE','YES',190,'190-4'),
 (113,3,'10','حساب پس انداز-قرض الحسنه','NONE','YES',209,'209-10'),
 (114,3,'01','سپرده کوتاه مدت','NONE','YES',210,'210-01'),
 (115,3,'01','خدمات بانکي','NONE','YES',310,'310-01'),
 (116,3,'02','ساير خدمات','NONE','YES',310,'310-02'),
 (117,3,'01','حقوق ودستمزد پرسنل','NONE','YES',500,'500-01'),
 (118,3,'04','پاداش وعيدي','NONE','YES',500,'500-04'),
 (119,3,'06','مطبوعات وملزومات','NONE','YES',500,'500-06'),
 (120,3,'07','اياب وذهاب','NONE','YES',500,'500-07'),
 (121,3,'08','تنظيفات','NONE','YES',500,'500-08'),
 (122,3,'10','پذيرايي','NONE','YES',500,'500-10'),
 (123,3,'11','استهلاک اثاثيه','NONE','YES',500,'500-11'),
 (124,3,'12','تبليغات','NONE','YES',500,'500-12'),
 (125,3,'13','ماموريت و فوق العاده روزانه','NONE','YES',500,'500-13'),
 (126,3,'14','قضايي','NONE','YES',500,'500-14'),
 (127,3,'15','تلفن','NONE','YES',500,'500-15'),
 (128,3,'18','متفرقه','NONE','YES',500,'500-18'),
 (129,3,'19','تعميرو قطعات وملزومات رايانه ه','NONE','YES',500,'500-19'),
 (130,3,'22','خريد نرم افزار','NONE','YES',500,'500-22'),
 (131,3,'23','هزينه تعميروتجهيز ساختمانهاي ش','NONE','YES',500,'500-23'),
 (132,3,'26','مجمع','NONE','YES',500,'500-26'),
 (133,3,'27','پست ونمابر','NONE','YES',500,'500-27'),
 (134,3,'28','کارشناسي','NONE','YES',500,'500-28'),
 (135,3,'29','سنوات خدمت کارکنان ','NONE','YES',500,'500-29'),
 (136,3,'02','اسناد وچکهاي مدت دارپرداختني','NONE','YES',660,'660-02'),
 (137,3,'03','قرض الحسنه خيرين (تعهدات مشتري','NONE','YES',660,'660-03'),
 (138,3,'04','عامليت دانشگاه فردوسي (تعهدات ','NONE','YES',660,'660-04'),
 (139,3,'05','علم تاعمل 92(تعهدات مشتريان )','NONE','YES',660,'660-05'),
 (140,3,'01','کارمزد دريافتي تسهيلات قرض الح','NONE','YES',750,'750-01'),
 (141,3,'02','کارمزد دريافتي تسهيلات کارگشاي','NONE','YES',750,'750-02'),
 (142,3,'03','کارمزد دريافتي تسهيلات ضروري','NONE','YES',750,'750-03'),
 (143,3,'04','سود وکارمزد قرارداد عامليت','NONE','YES',750,'750-04'),
 (144,3,'05','سود وکارمزد قرارداد پژوهشي','NONE','YES',750,'750-05'),
 (145,3,'06','سود وکارمزد دريافتي از سپرده ه','NONE','YES',750,'750-06'),
 (146,3,'07','کارمزد دريافتي ضمانت نامه ها ','NONE','YES',750,'750-07'),
 (147,3,'08','کارمزد دريافتي متفرقه ','NONE','YES',750,'750-08'),
 (148,3,'09','کارمزد کارگزاري (علم تاعمل)','NONE','YES',750,'750-09'),
 (149,3,'15','تسهيلات پژوهشي','NONE','YES',750,'750-15'),
 (150,3,'16','تسهيلات عامليت با دانشگاه','NONE','YES',750,'750-16'),
 (151,3,'17','تسهيلات عامليت جديد(90)','NONE','YES',750,'750-17'),
 (152,3,'04','کارمزد وسود دريافتي سال 93','NONE','YES',760,'760-04'),
 (153,3,'05','کارمزد وسود دريافتي سال 94','NONE','YES',760,'760-05'),
 (154,3,'06','کارمزد وسود دريافتي سال 95','NONE','YES',760,'760-06'),
 (155,3,'94','94','NONE','YES',760,'760-94'),
 (156,3,'96','سودوکارمزد سالهاي آينده 96','NONE','YES',760,'760-96'),
 (157,3,'01','حساب انتظامي-تعداد اسناد ضمانتي','NONE','YES',904,'904-1'),
 (158,3,'02','حساب انتظامي-مبلغ اسناد ضمانتي','NONE','YES',904,'904-2'),
 (159,3,'03','حساب انتظامي-تعداد اسناد دريافتي','NONE','YES',904,'904-3'),
 (160,3,'04','حساب انتظامي-مبلغ اسناد دريافتي','NONE','YES',904,'904-4'),
 (161,3,'05','اسناد توديعي نزد اشخاص','NONE','YES',904,'904-5'),
 (162,3,'01','طرف حساب انتظامي-تعداد اسناد ضمانتي','NONE','YES',905,'905-1'),
 (163,3,'02','طرف حساب انتظامي-مبلغ اسناد ضمانتي','NONE','YES',905,'905-2'),
 (164,3,'03','طرف حساب انتظامي-تعداد اسناد دريافتي','NONE','YES',905,'905-3'),
 (165,3,'04','طرف حساب انتظامي-مبلغ اسناد دريافتي','NONE','YES',905,'905-4'),
 (166,3,'05','اسناد توديعي نزد اشخاص ','NONE','YES',905,'905-5');
/*!40000 ALTER TABLE `ACC_blocks` ENABLE KEYS */;


--
-- Definition of table `ACC_cheques`
--

DROP TABLE IF EXISTS `ACC_cheques`;
CREATE TABLE `ACC_cheques` (
  `ChequeID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد دسته چک',
  `AccountID` int(10) unsigned NOT NULL COMMENT 'کد حساب',
  `SerialNo` varchar(100) NOT NULL COMMENT 'شماره سریال',
  `MinNo` decimal(10,0) NOT NULL COMMENT 'از شماره',
  `MaxNo` decimal(10,0) NOT NULL COMMENT 'تا شماره',
  `IsActive` enum('YES','NO') NOT NULL,
  PRIMARY KEY (`ChequeID`),
  KEY `FK_ACC_cheques_1` (`AccountID`),
  CONSTRAINT `FK_ACC_cheques_1` FOREIGN KEY (`AccountID`) REFERENCES `acc_accounts` (`AccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_cheques`
--

/*!40000 ALTER TABLE `ACC_cheques` DISABLE KEYS */;
/*!40000 ALTER TABLE `ACC_cheques` ENABLE KEYS */;


--
-- Definition of table `ACC_cycles`
--

DROP TABLE IF EXISTS `ACC_cycles`;
CREATE TABLE `ACC_cycles` (
  `CycleID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد دوره ',
  `CycleDesc` varchar(500) NOT NULL,
  `CycleYear` smallint(5) unsigned NOT NULL COMMENT 'سال',
  `IsClosed` enum('YES','NO') NOT NULL DEFAULT 'NO' COMMENT 'بسته است؟',
  PRIMARY KEY (`CycleID`)
) ENGINE=InnoDB AUTO_INCREMENT=1395 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_cycles`
--

/*!40000 ALTER TABLE `ACC_cycles` DISABLE KEYS */;
INSERT INTO `ACC_cycles` (`CycleID`,`CycleDesc`,`CycleYear`,`IsClosed`) VALUES 
 (1394,'دوره سال 1394',1394,'NO');
/*!40000 ALTER TABLE `ACC_cycles` ENABLE KEYS */;


--
-- Definition of table `ACC_docs`
--

DROP TABLE IF EXISTS `ACC_docs`;
CREATE TABLE `ACC_docs` (
  `DocID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد سند',
  `CycleID` int(10) unsigned NOT NULL COMMENT 'کد دوره',
  `BranchID` int(10) unsigned NOT NULL COMMENT 'کد شعبه',
  `LocalNo` smallint(5) unsigned NOT NULL,
  `DocDate` date NOT NULL COMMENT 'تاریخ سند',
  `RegDate` date NOT NULL COMMENT 'تاریخ ثبت سند',
  `DocStatus` varchar(50) NOT NULL DEFAULT 'RAW' COMMENT 'وضعیت برگه',
  `DocType` varchar(50) NOT NULL DEFAULT 'NORMAL' COMMENT 'نوع برگه',
  `description` varchar(500) DEFAULT NULL COMMENT 'توضیحات',
  `RegPersonID` int(10) unsigned NOT NULL COMMENT 'ثبت کننده',
  PRIMARY KEY (`DocID`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_docs`
--

/*!40000 ALTER TABLE `ACC_docs` DISABLE KEYS */;
INSERT INTO `ACC_docs` (`DocID`,`CycleID`,`BranchID`,`LocalNo`,`DocDate`,`RegDate`,`DocStatus`,`DocType`,`description`,`RegPersonID`) VALUES 
 (18,1394,2,1,'2015-11-05','2015-11-05','RAW','4',NULL,1000),
 (22,1394,2,2,'2015-11-05','2015-11-05','RAW','4',NULL,1000),
 (23,1394,2,3,'2015-11-05','2015-11-05','RAW','4',NULL,1000),
 (24,1394,2,4,'2015-11-05','2015-11-05','RAW','4',NULL,1000),
 (25,1394,2,5,'2015-11-05','2015-11-05','RAW','4',NULL,1000),
 (26,1394,2,6,'2015-11-05','2015-11-05','RAW','4',NULL,1000),
 (27,1394,1,1,'2015-11-05','2015-11-05','RAW','4',NULL,1000),
 (28,1394,1,2,'2015-11-05','2015-11-05','RAW','4',NULL,1000),
 (29,1394,1,3,'2015-11-05','2015-11-05','RAW','4','پرداخت مرحله مرحله اول وام شماره 16',1000),
 (30,1394,1,4,'2015-11-05','2015-11-05','RAW','4','پرداخت مرحله مرحله اول وام شماره 16',1000),
 (31,1394,1,5,'2015-11-05','2015-11-05','RAW','4','پرداخت مرحله مرحله اول وام شماره 16',1000),
 (32,1394,1,6,'2015-11-05','2015-11-05','RAW','4','پرداخت مرحله مرحله اول وام شماره 16',1000),
 (33,1394,1,7,'2015-11-05','2015-11-05','RAW','4','پرداخت مرحله مرحله دوم وام شماره 16',1000),
 (34,1394,1,8,'2015-11-05','2015-11-05','RAW','4','پرداخت مرحله مرحله دوم وام شماره 16',1000),
 (35,1394,1,9,'2015-11-05','2015-11-05','RAW','4','پرداخت مرحله مرحله دوم وام شماره 16',1000),
 (36,1394,1,10,'2015-11-07','2015-11-07','RAW','4','پرداخت مرحله مرحله اول وام شماره 1000',1000),
 (38,1394,1,11,'2015-11-07','2015-11-07','RAW','4','پرداخت مرحله مرحله اول وام شماره 1000',1000);
/*!40000 ALTER TABLE `ACC_docs` ENABLE KEYS */;


--
-- Definition of table `ACC_tafsilis`
--

DROP TABLE IF EXISTS `ACC_tafsilis`;
CREATE TABLE `ACC_tafsilis` (
  `TafsiliID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد تفصیلی',
  `TafsiliCode` varchar(100) NOT NULL COMMENT 'کد تفصیلی',
  `TafsiliType` int(10) unsigned NOT NULL COMMENT 'نوع تفصیلی',
  `TafsiliDesc` varchar(500) NOT NULL COMMENT 'عنوان',
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `ObjectID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`TafsiliID`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_tafsilis`
--

/*!40000 ALTER TABLE `ACC_tafsilis` DISABLE KEYS */;
INSERT INTO `ACC_tafsilis` (`TafsiliID`,`TafsiliCode`,`TafsiliType`,`TafsiliDesc`,`IsActive`,`ObjectID`) VALUES 
 (1,'1000',1,'آقای ایکس','NO',NULL),
 (12,'1001',1,'پارک علم و فناوری','YES',1001),
 (13,'1002',1,'شرکت داده 3','YES',1002),
 (14,'1',1,'صندوق نوآوری و شکوفایی','YES',NULL),
 (15,'1394',2,'1394','YES',NULL),
 (16,'1395',2,'1395','YES',NULL),
 (17,'1396',2,'1396','YES',NULL),
 (18,'1397',2,'1397','YES',NULL),
 (19,'1398',2,'1398','YES',NULL),
 (20,'1399',2,'1399','YES',NULL),
 (21,'7800005',3,'ملی 780005 شعبه پردیس','YES',2),
 (24,'1009',1,'-- سیدیان','YES',1009),
 (25,'1003',1,'شرکت صنعتی شرق','YES',1003);
/*!40000 ALTER TABLE `ACC_tafsilis` ENABLE KEYS */;


--
-- Definition of table `BSC_BranchAccess`
--

DROP TABLE IF EXISTS `BSC_BranchAccess`;
CREATE TABLE `BSC_BranchAccess` (
  `PersonID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `BranchID` int(10) unsigned NOT NULL,
  `IsCurrent` enum('YES','NO') NOT NULL,
  PRIMARY KEY (`PersonID`,`BranchID`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `BSC_BranchAccess`
--

/*!40000 ALTER TABLE `BSC_BranchAccess` DISABLE KEYS */;
INSERT INTO `BSC_BranchAccess` (`PersonID`,`BranchID`,`IsCurrent`) VALUES 
 (1000,1,'NO'),
 (1000,2,'NO');
/*!40000 ALTER TABLE `BSC_BranchAccess` ENABLE KEYS */;


--
-- Definition of table `BSC_branches`
--

DROP TABLE IF EXISTS `BSC_branches`;
CREATE TABLE `BSC_branches` (
  `BranchID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `BranchName` varchar(500) CHARACTER SET utf8 NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL,
  PRIMARY KEY (`BranchID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COMMENT='شعبه ها';

--
-- Dumping data for table `BSC_branches`
--

/*!40000 ALTER TABLE `BSC_branches` DISABLE KEYS */;
INSERT INTO `BSC_branches` (`BranchID`,`BranchName`,`IsActive`) VALUES 
 (1,'شعبه مرکزی','YES'),
 (2,'شعبه بجنورد','YES'),
 (3,'ششششششششش','NO');
/*!40000 ALTER TABLE `BSC_branches` ENABLE KEYS */;


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
) ENGINE=InnoDB AUTO_INCREMENT=1010 DEFAULT CHARSET=utf8 COMMENT='ذینفعان';

--
-- Dumping data for table `BSC_persons`
--

/*!40000 ALTER TABLE `BSC_persons` DISABLE KEYS */;
INSERT INTO `BSC_persons` (`PersonID`,`UserName`,`UserPass`,`IsReal`,`fname`,`lname`,`CompanyName`,`NationalID`,`EconomicID`,`PhoneNo`,`mobile`,`address`,`email`,`IsStaff`,`IsCustomer`,`IsShareholder`,`IsAgent`,`IsSupporter`,`IsActive`,`PostID`) VALUES 
 (1000,'admin','$P$BCy9D77Tk5UrJibOCgIkum/NYvq3Ym1','YES','شبنم','جعفرخانی','','0943021723',NULL,NULL,NULL,'sdfsdf',NULL,'YES','NO','NO','NO','NO','YES',1),
 (1001,'park','$P$Bycap3x5ddHssUbkMErS3tBiQmf7OE1','NO',NULL,NULL,'پارک علم و فناوری',NULL,NULL,NULL,NULL,NULL,'park@uc.ir','NO','NO','YES','YES','NO','YES',NULL),
 (1002,'data','$P$B0DJkog522gyIoHt71lfA5JK8tZAwy/','NO',NULL,NULL,'شرکت داده 3',NULL,NULL,NULL,NULL,NULL,'data3@hh.com','NO','YES','NO','NO','NO','YES',NULL),
 (1003,'shargh','$P$B5D6VAC6pIoTW8oJf83Nq60.B.hA09/','NO',NULL,NULL,'شرکت صنعتی شرق',NULL,NULL,NULL,NULL,NULL,'sharg@sss.com','NO','YES','NO','NO','NO','YES',NULL),
 (1009,'seyedian','$P$Bf4.yxNncAAkjyqymR3dzr6/9bYvSh0','YES','--','سیدیان',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'YES','YES','NO','NO','NO','YES',3);
/*!40000 ALTER TABLE `BSC_persons` ENABLE KEYS */;


--
-- Definition of table `BSC_posts`
--

DROP TABLE IF EXISTS `BSC_posts`;
CREATE TABLE `BSC_posts` (
  `PostID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UnitID` int(10) unsigned NOT NULL,
  `PostName` varchar(500) NOT NULL,
  PRIMARY KEY (`PostID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='پست هاي سازماني';

--
-- Dumping data for table `BSC_posts`
--

/*!40000 ALTER TABLE `BSC_posts` DISABLE KEYS */;
INSERT INTO `BSC_posts` (`PostID`,`UnitID`,`PostName`) VALUES 
 (1,1,'کارشناس'),
 (2,1,'مدیر عامل'),
 (3,1,'ناظر');
/*!40000 ALTER TABLE `BSC_posts` ENABLE KEYS */;


--
-- Definition of table `BSC_units`
--

DROP TABLE IF EXISTS `BSC_units`;
CREATE TABLE `BSC_units` (
  `UnitID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ParentID` int(10) unsigned DEFAULT NULL,
  `UnitName` varchar(500) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`UnitID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COMMENT='واحدهای سازمانی';

--
-- Dumping data for table `BSC_units`
--

/*!40000 ALTER TABLE `BSC_units` DISABLE KEYS */;
INSERT INTO `BSC_units` (`UnitID`,`ParentID`,`UnitName`) VALUES 
 (1,NULL,'مدیریت'),
 (8,1,'معاونت');
/*!40000 ALTER TABLE `BSC_units` ENABLE KEYS */;


--
-- Definition of table `BaseInfo`
--

DROP TABLE IF EXISTS `BaseInfo`;
CREATE TABLE `BaseInfo` (
  `TypeID` int(10) unsigned NOT NULL COMMENT 'کد نوع',
  `InfoID` int(10) unsigned NOT NULL COMMENT 'کد آیتم',
  `InfoDesc` varchar(500) NOT NULL COMMENT 'عنوان',
  `param1` varchar(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`InfoID`,`TypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `BaseInfo`
--

/*!40000 ALTER TABLE `BaseInfo` DISABLE KEYS */;
INSERT INTO `BaseInfo` (`TypeID`,`InfoID`,`InfoDesc`,`param1`) VALUES 
 (1,1,'وام های جعاله','0'),
 (2,1,'اشخاص و شرکتها','0'),
 (3,1,'حساب جاری','0'),
 (4,1,'در جریان','0'),
 (5,1,'ایجاد درخواست','0'),
 (7,1,'تضمین','0'),
 (8,1,'وثیقه ملکی','1'),
 (9,1,'سند افتتاحیه','0'),
 (10,1,'پرداخت وام',''),
 (11,1,'پرداخت مرحله وام','0'),
 (1,2,'وام های مسکن','0'),
 (2,2,'سال','0'),
 (3,2,'حساب سپرده ','0'),
 (7,2,'مدارک شخص حقیقی','0'),
 (8,2,'ضمانت بانکی','1'),
 (9,2,'سند دستی','0'),
 (10,2,'پرداخت قسط','0'),
 (1,3,'وام های جزیی','0'),
 (2,3,'بانک ها','0'),
 (7,3,'مدارک شخص حقوقی','0'),
 (8,3,'چک','1'),
 (9,3,'سند اختتامیه','0'),
 (8,4,'سفته','1'),
 (9,4,'سند پرداخت مرحله وام','0'),
 (8,5,'کسر از حقوق','1'),
 (9,5,'سند پرداخت قسط','0'),
 (8,6,'ماشین آلات','1'),
 (9,6,'سند جریمه تاخیر پرداخت اقساط','0'),
 (9,7,'سند محاسبه سود سپرده','0'),
 (5,10,'ارسال درخواست','0'),
 (6,10,'خام','0'),
 (6,11,'برگشت چک','0'),
 (5,20,'رد درخواست','0'),
 (8,21,'صفحه اول شناسنامه','2'),
 (8,22,'صفحه دوم شناسنامه','2'),
 (8,23,'توضیحات شناسنامه','2'),
 (8,24,'کارت ملی','2'),
 (8,25,'پشت کارت ملی','2'),
 (5,30,'تایید درخواست','0'),
 (5,40,'ارسال به مشتری جهت تکمیل مدارک','0'),
 (8,41,'اساسنامه','3'),
 (8,42,'آگهی ثبتی','3'),
 (8,43,'آگهی روزنامه رسمی','3'),
 (8,44,'آخرین تغییرات','3'),
 (5,50,'تکمیل مدارک توسط مشتری','0'),
 (5,60,'عدم تایید مدارک','0'),
 (5,70,'تایید مدارک مشتری','0'),
 (5,80,'تایید نهایی','0'),
 (6,100,'پرداخت شده','0');
/*!40000 ALTER TABLE `BaseInfo` ENABLE KEYS */;


--
-- Definition of table `BaseTypes`
--

DROP TABLE IF EXISTS `BaseTypes`;
CREATE TABLE `BaseTypes` (
  `TypeID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد نوع',
  `SystemID` int(10) unsigned NOT NULL,
  `TypeDesc` varchar(500) DEFAULT NULL COMMENT 'عنوان',
  `TableName` varchar(100) DEFAULT NULL COMMENT 'نام جدول',
  `FieldName` varchar(100) DEFAULT NULL COMMENT 'نام فیلد',
  `editable` enum('YES','NO') NOT NULL,
  PRIMARY KEY (`TypeID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `BaseTypes`
--

/*!40000 ALTER TABLE `BaseTypes` DISABLE KEYS */;
INSERT INTO `BaseTypes` (`TypeID`,`SystemID`,`TypeDesc`,`TableName`,`FieldName`,`editable`) VALUES 
 (1,6,'گروه وام ها','LON_loans','GroupID','YES'),
 (2,2,'انواع تفصیلی','ACC_Tafsilis','TafsiliType','YES'),
 (3,2,'نوع حساب بانکی','ACC_accounts','AccountType','NO'),
 (4,2,'وضعیت چک','ACC_DocChecks','CheckStatus','NO'),
 (5,6,'وضعیت درخواست وام','LON_requests','StatusID','NO'),
 (6,6,'وضعیت قسط وام','LON_RequestParts','StatusID','NO'),
 (7,7,'گروه مدرک','BaseInfo','param1','YES'),
 (8,7,'نوع مدرک','DMS_documents','DocType','NO'),
 (9,2,'نوع سند','ACC_docs','DocType','NO'),
 (10,2,'مرجع سند حسابداری','ACC_DocItems','SourceType','NO'),
 (11,4,'انواع آیتم گردش فرم','WFM_flows','ObjectType','NO');
/*!40000 ALTER TABLE `BaseTypes` ENABLE KEYS */;


--
-- Definition of table `DMS_documents`
--

DROP TABLE IF EXISTS `DMS_documents`;
CREATE TABLE `DMS_documents` (
  `DocumentID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد سند',
  `DocDesc` varchar(200) DEFAULT NULL COMMENT 'توضیحات کلی',
  `DocType` int(10) unsigned NOT NULL COMMENT 'نوع سند',
  `DocSerial` varchar(100) DEFAULT NULL,
  `ObjectType` varchar(50) DEFAULT NULL COMMENT 'نوع آبجکت',
  `ObjectID` int(10) unsigned DEFAULT NULL COMMENT 'کد آبجکت',
  `FileType` varchar(20) DEFAULT NULL COMMENT 'نوع فایل',
  `FileContent` tinyblob COMMENT 'قسمتی از محتوای فایل',
  `IsConfirm` enum('NOTSET','YES','NO') NOT NULL DEFAULT 'NOTSET' COMMENT 'برابر اصل',
  `RegPersonID` int(10) unsigned NOT NULL,
  `ConfirmPersonID` int(10) unsigned DEFAULT NULL COMMENT 'فرد تایید کننده',
  `RejectDesc` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`DocumentID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `DMS_documents`
--

/*!40000 ALTER TABLE `DMS_documents` DISABLE KEYS */;
INSERT INTO `DMS_documents` (`DocumentID`,`DocDesc`,`DocType`,`DocSerial`,`ObjectType`,`ObjectID`,`FileType`,`FileContent`,`IsConfirm`,`RegPersonID`,`ConfirmPersonID`,`RejectDesc`) VALUES 
 (1,'ضامن اول',21,'12547','loan',1000,'jpg',0xFFD8FFE000104A46494600010201006000600000FFE1302D4578696600004D4D002A00000008000D010E000200000010000000AA011200030000000100010000011A000500000001000000BA011B000500000001000000C2012800030000000100020000013100020000001C000000CA0132000200000014000000E6013B00020000001C000000FA829800020000001C000001169C9B000100000020000001329C9D00010000003800000152EA1C0007000007C40000018A87690004000000010000095000001194,'YES',1003,1000,NULL),
 (2,'-',24,'0943021723','loan',1000,'jpg',0xFFD8FFE000104A46494600010201006000600000FFE123384578696600004D4D002A00000008000D010E000200000010000000AA011200030000000100010000011A000500000001000000BA011B000500000001000000C2012800030000000100020000013100020000001C000000CA0132000200000014000000E6013B00020000001C000000FA829800020000001C000001169C9B000100000020000001329C9D00010000003800000152EA1C0007000007C40000018A87690004000000010000095000001194,'YES',1003,1000,NULL),
 (3,NULL,2,NULL,'loan',1000,'jpg',0xFFD8FFE000104A46494600010201006000600000FFE132E34578696600004D4D002A00000008000D010E000200000010000000AA011200030000000100010000011A000500000001000000BA011B000500000001000000C2012800030000000100020000013100020000001C000000CA0132000200000014000000E6013B00020000001C000000FA829800020000001C000001169C9B000100000020000001329C9D00010000003800000152EA1C0007000007C40000018A87690004000000010000095000001194,'YES',1003,1000,NULL),
 (4,NULL,3,'12345','loan',1000,'jpg',0xFFD8FFE000104A46494600010201006000600000FFE12E774578696600004D4D002A00000008000D010E000200000010000000AA011200030000000100010000011A000500000001000000BA011B000500000001000000C2012800030000000100020000013100020000001C000000CA0132000200000014000000E6013B00020000001C000000FA829800020000001C000001169C9B000100000020000001329C9D00010000003800000152EA1C0007000007C40000018A87690004000000010000095000001194,'YES',1003,1000,NULL),
 (5,NULL,3,'12346','loan',1000,'jpg',0xFFD8FFE000104A46494600010201006000600000FFE135E14578696600004D4D002A00000008000D010E000200000010000000AA011200030000000100010000011A000500000001000000BA011B000500000001000000C2012800030000000100020000013100020000001C000000CA0132000200000014000000E6013B00020000001C000000FA829800020000001C000001169C9B000100000020000001329C9D00010000003800000152EA1C0007000007C40000018A87690004000000010000095000001194,'YES',1003,1000,NULL),
 (6,NULL,41,NULL,'person',1003,'jpg',0xFFD8FFE000104A46494600010201006000600000FFE12C724578696600004D4D002A00000008000D010E000200000010000000AA011200030000000100010000011A000500000001000000BA011B000500000001000000C2012800030000000100020000013100020000001C000000CA0132000200000014000000E6013B00020000001C000000FA829800020000001C000001169C9B000100000020000001329C9D00010000003800000152EA1C0007000007C40000018A87690004000000010000095000001194,'YES',1003,1000,NULL),
 (7,NULL,42,NULL,'person',1003,'jpg',0xFFD8FFE000104A46494600010201006000600000FFE1309B4578696600004D4D002A00000008000D010E000200000010000000AA011200030000000100010000011A000500000001000000BA011B000500000001000000C2012800030000000100020000013100020000001C000000CA0132000200000014000000E6013B00020000001C000000FA829800020000001C000001169C9B000100000020000001329C9D00010000003800000152EA1C0007000007C40000018A87690004000000010000095000001194,'YES',1003,1000,NULL);
/*!40000 ALTER TABLE `DMS_documents` ENABLE KEYS */;


--
-- Definition of table `DMS_packages`
--

DROP TABLE IF EXISTS `DMS_packages`;
CREATE TABLE `DMS_packages` (
  `PackageID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد بسته',
  `PackDesc` varchar(500) CHARACTER SET utf8 NOT NULL COMMENT 'عتوان',
  `ObjectType` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT 'نوع آبجکت',
  `ObjectID` int(10) unsigned DEFAULT NULL COMMENT 'کد آبجکت',
  `description` varchar(1000) CHARACTER SET utf8 DEFAULT NULL COMMENT 'توضیحات',
  PRIMARY KEY (`PackageID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `DMS_packages`
--

/*!40000 ALTER TABLE `DMS_packages` DISABLE KEYS */;
/*!40000 ALTER TABLE `DMS_packages` ENABLE KEYS */;


--
-- Definition of table `DataAudit`
--

DROP TABLE IF EXISTS `DataAudit`;
CREATE TABLE `DataAudit` (
  `DataAuditID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PersonID` int(11) NOT NULL COMMENT 'کد شخصی عمل کننده',
  `TableName` varchar(100) COLLATE utf8_persian_ci NOT NULL COMMENT 'نام جدول',
  `MainObjectID` int(11) NOT NULL COMMENT 'کد داده اصلی دستکاری شده',
  `SubObjectID` int(11) DEFAULT NULL COMMENT 'کد داده فرعی دستکاری شده',
  `ActionType` enum('ADD','DELETE','UPDATE','VIEW','SEARCH','SEND','RETURN','CONFIRM','REJECT','OTHER') CHARACTER SET utf8 DEFAULT NULL COMMENT 'نوع عمل',
  `SystemID` int(11) NOT NULL COMMENT 'کد سیستم جاری',
  `PageName` varchar(100) COLLATE utf8_persian_ci NOT NULL COMMENT 'نام صفحه ای این دستکاری توسط آن انجام شده',
  `description` varchar(200) COLLATE utf8_persian_ci DEFAULT NULL COMMENT 'توضیحات بیشتر',
  `IPAddress` varchar(100) COLLATE utf8_persian_ci NOT NULL COMMENT 'آدرس آی پی کامپیوتر عمل کننده',
  `ActionTime` datetime NOT NULL COMMENT 'زمان انجام عمل',
  `QueryString` varchar(2000) CHARACTER SET utf8 DEFAULT NULL COMMENT 'query اجرا شده',
  PRIMARY KEY (`DataAuditID`)
) ENGINE=MyISAM AUTO_INCREMENT=1293 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci COMMENT='اطلاعات ممیزی ';

--
-- Dumping data for table `DataAudit`
--

/*!40000 ALTER TABLE `DataAudit` DISABLE KEYS */;
INSERT INTO `DataAudit` (`DataAuditID`,`PersonID`,`TableName`,`MainObjectID`,`SubObjectID`,`ActionType`,`SystemID`,`PageName`,`description`,`IPAddress`,`ActionTime`,`QueryString`) VALUES 
 (1,1000,'FRW_menus',8,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 14:47:31','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,icon,MenuPath) values (\'1\',\'2\',\'sdfdsf\',\'YES\',\'3\',\'fsdfsd\',\'sdfsd\')'),
 (2,1000,'FRW_menus',6,NULL,'DELETE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 14:48:08','delete from FRW_menus where MenuID=\'6\''),
 (3,1000,'FRW_menus',7,NULL,'DELETE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 14:48:13','delete from FRW_menus where MenuID=\'7\''),
 (4,1000,'FRW_menus',8,NULL,'DELETE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 14:48:17','delete from FRW_menus where MenuID=\'8\''),
 (5,1000,'FRW_menus',9,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 14:55:15','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'1\',\'0\',\'یسبسیبیس\')'),
 (6,1000,'FRW_menus',10,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 14:55:41','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,icon,MenuPath) values (\'1\',\'9\',\'سشیش\',\'YES\',\'شسی\',\'یشسی\')'),
 (7,1000,'FRW_menus',10,NULL,'DELETE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 14:56:25','delete from FRW_menus where MenuID=\'10\''),
 (8,1000,'FRW_menus',9,NULL,'DELETE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 15:02:02','delete from FRW_menus where MenuID=\'9\''),
 (9,1000,'FRW_menus',11,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 15:03:43','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'1\',\'0\',\'یسیشسیشس\')'),
 (10,1000,'FRW_menus',11,NULL,'DELETE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 15:04:12','delete from FRW_menus where MenuID=\'11\''),
 (11,1000,'FRW_menus',12,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 15:04:20','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'1\',\'0\',\'یسیشسیشس\')'),
 (12,1000,'FRW_menus',12,NULL,'DELETE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 15:04:27','delete from FRW_menus where MenuID=\'12\''),
 (13,1000,'FRW_menus',13,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 15:07:52','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'1\',\'0\',\'یسیشسی\')'),
 (14,1000,'FRW_menus',13,NULL,'DELETE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 15:07:59','delete from FRW_menus where MenuID=\'13\''),
 (15,1000,'FRW_menus',14,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 15:08:30','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'1\',\'0\',\'سشیشسی\')'),
 (16,1000,'FRW_menus',14,NULL,'DELETE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 15:10:36','delete from FRW_menus where MenuID=\'14\''),
 (17,1000,'FRW_menus',15,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 15:17:59','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'1\',\'0\',\'سیبسیب\')'),
 (18,1000,'FRW_menus',15,NULL,'DELETE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-26 15:18:41','delete from FRW_menus where MenuID=\'15\''),
 (19,1000,'FRW_menus',6,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 08:47:14','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,MenuPath) values (\'1\',\'2\',\'سیسش\',\'YES\',\'zadasda\')'),
 (20,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:34:49','insert into FRW_access(MenuID,PersonID,AddFlag,EditFlag,RemoveFlag) values (\'3\',\'1000\',\'YES\',\'YES\',\'YES\')'),
 (21,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:34:49','insert into FRW_access(MenuID,PersonID,AddFlag,EditFlag,RemoveFlag) values (\'4\',\'1000\',\'YES\',\'YES\',\'YES\')'),
 (22,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:34:49','insert into FRW_access(MenuID,PersonID,AddFlag,EditFlag,RemoveFlag) values (\'6\',\'1000\',\'YES\',\'YES\',\'YES\')'),
 (23,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:34:49','insert into FRW_access(MenuID,PersonID,AddFlag,EditFlag,RemoveFlag) values (\'5\',\'1000\',\'YES\',\'YES\',\'YES\')'),
 (24,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:39:06','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'3\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (25,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:39:06','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'4\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (26,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:39:06','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'6\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (27,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:39:06','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'5\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (28,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:40:50','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'3\',\'1000\',\'YES\',\'NO\',\'NO\',\'NO\')'),
 (29,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:40:50','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'4\',\'1000\',\'YES\',\'NO\',\'YES\',\'YES\')'),
 (30,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:40:50','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'6\',\'1000\',\'YES\',\'YES\',\'NO\',\'YES\')'),
 (31,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:40:50','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'5\',\'1000\',\'YES\',\'YES\',\'YES\',\'NO\')'),
 (32,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:40:58','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'3\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (33,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:40:58','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'4\',\'1000\',\'YES\',\'NO\',\'YES\',\'YES\')'),
 (34,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:40:59','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'6\',\'1000\',\'YES\',\'YES\',\'NO\',\'YES\')'),
 (35,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:40:59','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'5\',\'1000\',\'YES\',\'YES\',\'YES\',\'NO\')'),
 (36,1000,'FRW_menus',6,NULL,'UPDATE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:42:04','update FRW_menus set SystemID=\'1\',MenuID=\'6\',MenuDesc=\'سیسش\',IsActive=\'YES\',ordering=\'1\',icon=\'users.gif\',MenuPath=\'zadasda\' where MenuID=\'6\''),
 (37,1000,'FRW_menus',5,NULL,'UPDATE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:42:15','update FRW_menus set SystemID=\'1\',MenuID=\'5\',MenuDesc=\'دسترسی کاربران\',IsActive=\'YES\',ordering=\'2\',icon=\'access.gif\',MenuPath=\'management/ui/UserAccess.php\' where MenuID=\'5\''),
 (38,1000,'FRW_menus',6,NULL,'UPDATE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:42:44','update FRW_menus set SystemID=\'1\',MenuID=\'6\',MenuDesc=\'کاربران\',IsActive=\'YES\',ordering=\'1\',icon=\'users.gif\',MenuPath=\'management/ui/users.php\' where MenuID=\'6\''),
 (39,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:51:58','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'3\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (40,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:51:58','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'4\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (41,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:51:58','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'6\',\'1000\',\'YES\',\'YES\',\'NO\',\'YES\')'),
 (42,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 10:51:58','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'5\',\'1000\',\'YES\',\'YES\',\'YES\',\'NO\')'),
 (43,1000,'FRW_persons',1001,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:07:52','insert into FRW_persons(UserID,fname,lname) values (\'mahdipour\',\'بهاره\',\'مهدی پور\')'),
 (44,1000,'FRW_persons',1002,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:08:07','insert into FRW_persons(UserID,fname,lname,IsActive) values (\'mahdipour\',\'بهاره\',\'مهدی پور---\',\'YES\')'),
 (45,1000,'FRW_persons',1002,NULL,'DELETE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:11:10','update FRW_persons set IsActive=\'NO\' where PersonID=\'1002\''),
 (46,1000,'FRW_persons',1001,NULL,'UPDATE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:12:41','update FRW_persons set PersonID=\'1001\',UserID=\'mahdipour\',fname=\'بهاره\',lname=\'مهدی پور999\',IsActive=\'YES\' where PersonID=\'1001\''),
 (47,1000,'FRW_persons',1001,NULL,'UPDATE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:12:48','update FRW_persons set PersonID=\'1001\',UserID=\'mahdipour\',fname=\'بهاره\',lname=\'مهدی پور\',IsActive=\'YES\' where PersonID=\'1001\''),
 (48,1000,'FRW_menus',6,NULL,'UPDATE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:13:20','update FRW_menus set SystemID=\'1\',MenuID=\'6\',MenuDesc=\'کاربران\',IsActive=\'NO\',ordering=\'1\',icon=\'users.gif\',MenuPath=\'management/users.php\' where MenuID=\'6\''),
 (49,1000,'FRW_menus',6,NULL,'UPDATE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:14:05','update FRW_menus set SystemID=\'1\',MenuID=\'6\',MenuDesc=\'کاربران\',IsActive=\'YES\',ordering=\'1\',icon=\'users.gif\',MenuPath=\'management/users.php\' where MenuID=\'6\''),
 (50,1000,'FRW_persons',1001,NULL,'UPDATE',1,'http://rtfund/framework/start.php','پاک کردن پسورد','127.0.0.1','2015-08-29 11:22:11','update FRW_persons set UserPass=null where PersonID=\'1001\''),
 (51,1000,'FRW_persons',1001,NULL,'UPDATE',1,'http://rtfund/framework/start.php','پاک کردن پسورد','127.0.0.1','2015-08-29 11:23:23','update FRW_persons set UserPass=null where PersonID=\'1001\''),
 (52,1000,'FRW_persons',1001,NULL,'UPDATE',1,'http://rtfund/framework/start.php','پاک کردن پسورد','127.0.0.1','2015-08-29 11:24:03','update FRW_persons set UserPass=null where PersonID=\'1001\''),
 (53,1000,'FRW_systems',2,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:33:56','insert into FRW_systems(SysName,SysPath,SysIcon) values (\'سیستم حسابداری\',\'accountancy\',\'accountancy.gif\')'),
 (54,1000,'FRW_systems',2,NULL,'UPDATE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:34:03','update FRW_systems set SystemID=\'2\',SysName=\'سیستم حسابداری ---\',SysPath=\'accountancy\',SysIcon=\'accountancy.gif\',IsActive=\'YES\' where SystemID=\'2\''),
 (55,1000,'FRW_systems',2,NULL,'UPDATE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:34:41','update FRW_systems set SystemID=\'2\',SysName=\'سیستم حسابداری ---\',SysPath=\'accountancy\',SysIcon=\'accountancy.gif\',IsActive=\'NO\' where SystemID=\'2\''),
 (56,1000,'FRW_systems',3,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:35:40','insert into FRW_systems(SysName,SysPath,SysIcon,IsActive) values (\'یسشسیش\',\'adasd\',\'adasda\',\'YES\')'),
 (57,1000,'FRW_systems',2,NULL,'UPDATE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:36:33','update FRW_systems set SystemID=\'2\',SysName=\'سیستم حسابداری ---\',SysPath=\'accountancy\',SysIcon=\'accountancy.gif\',IsActive=\'YES\' where SystemID=\'2\''),
 (58,1000,'FRW_systems',2,NULL,'UPDATE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:36:40','update FRW_systems set SystemID=\'2\',SysName=\'سیستم حسابداری ---\',SysPath=\'accountancy\',SysIcon=\'accountancy.gif\',IsActive=\'NO\' where SystemID=\'2\''),
 (59,1000,'FRW_systems',2,NULL,'UPDATE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:37:09','update FRW_systems set SystemID=\'2\',SysName=\'سیستم حسابداری ---\',SysPath=\'accountancy\',SysIcon=\'accountancy.gif\',IsActive=\'YES\' where SystemID=\'2\''),
 (60,1000,'FRW_systems',2,NULL,'UPDATE',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:37:14','update FRW_systems set SystemID=\'2\',SysName=\'سیستم حسابداری \',SysPath=\'accountancy\',SysIcon=\'accountancy.gif\',IsActive=\'YES\' where SystemID=\'2\''),
 (61,1000,'FRW_menus',7,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:37:29','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'2\',\'0\',\'اطلاعات پایه\')'),
 (62,1000,'FRW_menus',8,NULL,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:38:02','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'2\',\'7\',\'مدیریت کد حساب\',\'YES\',\'1\',\'account\')'),
 (63,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php',NULL,'127.0.0.1','2015-08-29 11:38:18','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'8\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (64,1000,'FRW_systems',4,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-08-29 12:40:24','insert into FRW_systems(SysName,SysPath,SysIcon,IsActive) values (\'سیستم اتوماسیون اداری\',\'office\',\'office.gif\',\'YES\')'),
 (65,1000,'FRW_systems',5,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-08-29 12:41:01','insert into FRW_systems(SysName,SysPath,SysIcon,IsActive) values (\'سیستم حضور و غیاب\',\'rollcall\',\'rollcall.gif\',\'YES\')'),
 (66,1000,'FRW_systems',5,NULL,'UPDATE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-08-29 12:41:20','update FRW_systems set SystemID=\'5\',SysName=\'سیستم حضور و غیاب\',SysPath=\'rollcall\',SysIcon=\'rollcall.png\',IsActive=\'YES\' where SystemID=\'5\''),
 (67,1000,'FRW_menus',9,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 13:01:08','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'1\',\'0\',\'اطلاعات پایه\')'),
 (68,1000,'FRW_menus',10,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 13:02:33','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,icon,MenuPath) values (\'1\',\'9\',\'واحدهای سازمان\',\'YES\',\'1\',\'unit.png\',\'unit/units.php\')'),
 (69,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 13:02:48','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'10\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (70,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 13:02:48','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'3\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (71,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 13:02:48','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'4\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (72,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 13:02:48','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'6\',\'1000\',\'YES\',\'YES\',\'NO\',\'YES\')'),
 (73,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 13:02:48','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'5\',\'1000\',\'YES\',\'YES\',\'YES\',\'NO\')'),
 (74,1000,'FRW_units',1,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:14:54','insert into FRW_units(UnitName) values (\'مدیریت\')'),
 (75,1000,'FRW_units',2,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:16:08','insert into FRW_units(ParentID,UnitName) values (\'1\',\'معاونت\')'),
 (76,1000,'FRW_units',3,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:16:59','insert into FRW_units(ParentID,UnitName) values (\'2\',\'یشسیشس\')'),
 (77,1000,'FRW_units',3,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:22:14','delete from FRW_units where  UnitID=\'3\''),
 (78,1000,'FRW_units',4,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:23:54','insert into FRW_units(ParentID,UnitName) values (\'2\',\'شسیسشیسش\')'),
 (79,1000,'FRW_units',5,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:35:17','insert into FRW_units(ParentID,UnitName) values (\'4\',\'تاات\')'),
 (80,1000,'FRW_units',5,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:39:50','delete from FRW_units where  UnitID=\'5\''),
 (81,1000,'FRW_units',4,NULL,'UPDATE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:42:23','update FRW_units set UnitID=\'4\',ParentID=\'4\',UnitName=\'یییی\' where  UnitID=\'4\''),
 (82,1000,'FRW_units',4,NULL,'UPDATE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:43:43','update FRW_units set UnitID=\'4\',ParentID=null,UnitName=\'شسیشسی\' where  UnitID=\'4\''),
 (83,1000,'FRW_units',2,NULL,'UPDATE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:46:09','update FRW_units set UnitID=\'2\',ParentID=null,UnitName=\'معاونتff\' where  UnitID=\'2\''),
 (84,1000,'FRW_units',2,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:46:19','delete from FRW_units where  UnitID=\'2\''),
 (85,1000,'FRW_units',6,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:46:28','insert into FRW_units(ParentID,UnitName) values (\'4\',\'111111\')'),
 (86,1000,'FRW_units',7,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:46:54','insert into FRW_units(ParentID,UnitName) values (\'6\',\'222222222\')'),
 (87,1000,'FRW_units',7,NULL,'UPDATE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:47:02','update FRW_units set UnitID=\'7\',ParentID=\'6\',UnitName=\'222222222222\' where  UnitID=\'7\''),
 (88,1000,'FRW_units',7,NULL,'UPDATE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:47:19','update FRW_units set UnitID=\'7\',ParentID=\'6\',UnitName=\'ffff222222222222\' where  UnitID=\'7\''),
 (89,1000,'FRW_units',7,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:47:28','delete from FRW_units where  UnitID=\'7\''),
 (90,1000,'FRW_units',6,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:47:40','delete from FRW_units where  UnitID=\'6\''),
 (91,1000,'FRW_units',4,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:47:44','delete from FRW_units where  UnitID=\'4\''),
 (92,1000,'FRW_units',8,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:47:58','insert into FRW_units(ParentID,UnitName) values (\'1\',\'معاونت\')'),
 (93,1000,'FRW_units',9,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:48:06','insert into FRW_units(UnitName) values (\'اداری\')'),
 (94,1000,'FRW_units',10,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:50:56','insert into FRW_units(UnitName) values (\'fdgfdg\')'),
 (95,1000,'FRW_units',11,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:51:01','insert into FRW_units(ParentID,UnitName) values (\'10\',\'fdgdfg\')'),
 (96,1000,'FRW_units',11,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:51:06','delete from FRW_units where  UnitID=\'11\''),
 (97,1000,'FRW_units',12,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:51:13','insert into FRW_units(ParentID,UnitName) values (\'10\',\'یییی\')'),
 (98,1000,'FRW_units',12,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:51:28','delete from FRW_units where  UnitID=\'12\''),
 (99,1000,'FRW_units',10,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-01 14:51:32','delete from FRW_units where  UnitID=\'10\''),
 (100,1000,'FRW_units',9,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 14:12:32','delete from FRW_units where  UnitID=\'9\''),
 (101,1000,'FRW_posts',1,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 14:16:33','insert into FRW_posts(UnitID,PostName) values (\'1\',\'یبلی\')'),
 (102,1000,'FRW_posts',2,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 14:16:55','insert into FRW_posts(UnitID,PostName) values (\'1\',\'سیبسی\')'),
 (103,1000,'FRW_posts',3,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 14:49:04','insert into FRW_posts(UnitID,PostName) values (\'8\',\'سسیشس\')'),
 (104,1000,'FRW_units',10,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 14:52:00','insert into FRW_units(ParentID,UnitName) values (\'8\',\'یسبسیب\')'),
 (105,1000,'FRW_units',11,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 14:54:22','insert into FRW_units(ParentID,UnitName) values (\'10\',\'sdfsdf\')'),
 (106,1000,'FRW_posts',4,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 14:54:28','insert into FRW_posts(UnitID,PostName) values (\'11\',\'سیبسیبس\')'),
 (107,1000,'FRW_posts',5,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 14:54:34','insert into FRW_posts(UnitID,PostName) values (\'10\',\'سیبسیبس\')'),
 (108,1000,'FRW_posts',5,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 14:56:04','delete from FRW_posts where  PostID=\'5\''),
 (109,1000,'FRW_posts',4,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 14:56:11','delete from FRW_posts where  PostID=\'4\''),
 (110,1000,'FRW_posts',3,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 14:56:15','delete from FRW_posts where  PostID=\'3\''),
 (111,1000,'FRW_units',11,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 14:56:20','delete from FRW_units where  UnitID=\'11\''),
 (112,1000,'FRW_units',10,NULL,'DELETE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 14:56:24','delete from FRW_units where  UnitID=\'10\''),
 (113,1000,'FRW_persons',1000,NULL,'UPDATE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 15:02:04','update FRW_persons set PersonID=\'1000\',UserID=\'admin\',fname=\'شبنم\',lname=\'جعفرخانی\',IsActive=\'YES\' where PersonID=\'1000\''),
 (114,1000,'FRW_persons',1000,NULL,'UPDATE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 15:02:29','update FRW_persons set PersonID=\'1000\',UserID=\'admin\',fname=\'شبنم\',lname=\'جعفرخانی\',IsActive=\'YES\',PostID=\'1\' where PersonID=\'1000\''),
 (115,1000,'FRW_persons',1001,NULL,'UPDATE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-05 15:02:38','update FRW_persons set PersonID=\'1001\',UserID=\'mahdipour\',fname=\'بهاره\',lname=\'مهدی پور\',IsActive=\'YES\',PostID=\'2\' where PersonID=\'1001\''),
 (116,1000,'FRW_menus',11,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:45:25','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'1\',\'9\',\'مدیریت شعب\',\'YES\',\'2\',\'baseinfo/branches.php\')'),
 (117,1000,'FRW_menus',10,NULL,'UPDATE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:45:37','update FRW_menus set SystemID=\'1\',MenuID=\'10\',MenuDesc=\'واحدهای سازمان\',IsActive=\'YES\',ordering=\'1\',icon=\'unit.png\',MenuPath=\'baeinfo/units.php\' where MenuID=\'10\''),
 (118,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:45:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'10\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (119,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:45:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'11\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (120,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:45:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'3\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (121,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:45:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'4\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (122,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:45:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'6\',\'1000\',\'YES\',\'YES\',\'NO\',\'YES\')'),
 (123,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:45:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'5\',\'1000\',\'YES\',\'YES\',\'YES\',\'NO\')'),
 (124,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:46:00','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'10\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (125,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:46:00','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'11\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (126,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:46:00','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'3\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (127,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:46:00','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'4\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (128,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:46:00','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'6\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (129,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:46:00','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'5\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (130,1000,'BSC_branches',1,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:57:53','insert into BSC_branches(BranchName,IsActive) values (\'سیسشیشس\',\'YES\')'),
 (131,1000,'BSC_branches',1,NULL,'UPDATE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:58:23','update BSC_branches set BranchID=\'1\',BranchName=\'شعبه مرکزی\',IsActive=\'YES\' where  BranchID=\'1\''),
 (132,1000,'BSC_branches',2,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:58:36','insert into BSC_branches(BranchName,IsActive) values (\'شعبه بجنورد\',\'YES\')'),
 (133,1000,'BSC_branches',3,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:59:14','insert into BSC_branches(BranchName,IsActive) values (\'سیشی\',\'YES\')'),
 (134,1000,'BSC_branches',3,NULL,'UPDATE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 13:59:20','update BSC_branches set BranchID=\'3\',BranchName=\'ششششششششش\',IsActive=\'YES\' where  BranchID=\'3\''),
 (135,1000,'FRW_menus',12,NULL,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 14:41:37','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'1\',\'9\',\'دسترسی شعب\',\'YES\',\'3\',\'baseInfo/BranchAccesd.php\')'),
 (136,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 14:42:39','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'10\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (137,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 14:42:39','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'11\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (138,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 14:42:39','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'12\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (139,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 14:42:39','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'3\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (140,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 14:42:39','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'4\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (141,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 14:42:39','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'6\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (142,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 14:42:39','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'5\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (143,1000,'FRW_menus',12,NULL,'UPDATE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 14:43:20','update FRW_menus set SystemID=\'1\',MenuID=\'12\',MenuDesc=\'دسترسی شعب\',IsActive=\'YES\',ordering=\'3\',icon=null,MenuPath=\'baseInfo/BranchAccess.php\' where MenuID=\'12\''),
 (144,1000,'FRW_systems',2,NULL,'UPDATE',1,'http://rtfund/framework/systems.php',NULL,'127.0.0.1','2015-09-07 15:05:44','update FRW_systems set SystemID=\'2\',SysName=\'سیستم حسابداری \',SysPath=\'accounting\',SysIcon=\'accountancy.gif\',IsActive=\'YES\' where SystemID=\'2\''),
 (145,1000,'FRW_systems',6,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:23:22','insert into FRW_systems(SysName,SysPath,SysIcon,IsActive) values (\'سیستم تسهیلات\',\'loan\',\'loan.png\',\'YES\')'),
 (146,1000,'FRW_menus',13,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:24:19','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'6\',\'0\',\'اطلاعات پایه\')'),
 (147,1000,'FRW_menus',14,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:30:24','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'6\',\'13\',\'انواع وام\',\'YES\',\'1\',\'loan/loans.php\')'),
 (148,1000,'FRW_menus',15,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:31:11','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'6\',\'0\',\'اعطای تسهیلات\')'),
 (149,1000,'FRW_menus',16,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:31:58','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'6\',\'15\',\'مدیریت درخواست ها\',\'YES\',\'1\',\'request/requsts.php\')'),
 (150,1000,'FRW_menus',17,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:32:18','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'6\',\'0\',\'گزارشات\')'),
 (151,1000,'FRW_menus',18,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:32:54','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'6\',\'17\',\'گزارش درخواست های تسهیلات\',\'YES\',\'1\',\'report/requests.php\')'),
 (152,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:33:29','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'14\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (153,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:33:29','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'16\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (154,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:33:29','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'18\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (155,1000,'FRW_menus',19,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:33:44','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'2\',\'0\',\'گزارشات\')'),
 (156,1000,'FRW_menus',20,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:33:55','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'2\',\'0\',\'عملیات\')'),
 (157,1000,'FRW_menus',20,NULL,'DELETE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:34:07','delete from FRW_menus where MenuID=\'20\''),
 (158,1000,'FRW_menus',21,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:34:13','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'2\',\'0\',\'عملیات برگه\')'),
 (159,1000,'FRW_menus',22,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:34:43','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'2\',\'21\',\'مدیریت برگه ها\',\'YES\',\'1\',\'docs/docs.php\')'),
 (160,1000,'FRW_menus',23,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:35:19','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'2\',\'21\',\'سند افتتاحیه / اختتامیه\',\'YES\',\'2\',\'docs/CloseDocs.php\')'),
 (161,1000,'FRW_menus',24,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:36:23','insert into FRW_menus(SystemID,ParentID,MenuDesc) values (\'2\',\'0\',\'مدیریت تفصیلی ها\')'),
 (162,1000,'FRW_menus',24,NULL,'DELETE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:36:29','delete from FRW_menus where MenuID=\'24\''),
 (163,1000,'FRW_menus',25,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:36:57','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'2\',\'7\',\'مدیریت تفصیلی ها\',\'YES\',\'2\',\'account/tafsilis.php\')'),
 (164,1000,'FRW_menus',8,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:37:11','update FRW_menus set SystemID=\'2\',MenuID=\'8\',MenuDesc=\'مدیریت کد حساب\',IsActive=\'YES\',ordering=\'1\',icon=null,MenuPath=\'account/ accounts.php\' where MenuID=\'8\''),
 (165,1000,'FRW_menus',26,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:37:36','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'2\',\'19\',\'گزارش تراز\',\'YES\',\'1\',\'report/taraz.php\')'),
 (166,1000,'FRW_menus',27,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:38:08','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'2\',\'19\',\'گزارش گردش حساب\',\'YES\',\'1\',\'report/flow.php\')'),
 (167,1000,'FRW_menus',28,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:38:32','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'2\',\'19\',\'گزارش اسناد\',\'YES\',\'3\',\'report/docs.php\')'),
 (168,1000,'FRW_menus',27,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 12:38:41','update FRW_menus set SystemID=\'2\',MenuID=\'27\',MenuDesc=\'گزارش گردش حساب\',IsActive=\'YES\',ordering=\'2\',icon=null,MenuPath=\'report/flow.php\' where MenuID=\'27\''),
 (169,1000,'FRW_menus',19,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 13:04:04','update FRW_menus set SystemID=\'2\',MenuID=\'19\',ParentID=\'0\',MenuDesc=\'گزارشات\',ordering=\'3\' where MenuID=\'19\''),
 (170,1000,'FRW_menus',29,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 13:14:24','insert into FRW_menus(SystemID,ParentID,MenuDesc,ordering) values (\'2\',\'0\',\'یسشیشسی\',\'4\')'),
 (171,1000,'FRW_menus',29,NULL,'DELETE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 13:14:29','delete from FRW_menus where MenuID=\'29\''),
 (172,1000,'FRW_menus',21,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 13:14:37','update FRW_menus set SystemID=\'2\',MenuID=\'21\',ParentID=\'0\',MenuDesc=\'عملیات برگه\',ordering=\'2\' where MenuID=\'21\''),
 (173,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 13:15:27','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'8\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (174,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 13:15:27','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'25\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (175,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 13:15:27','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'22\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (176,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 13:15:27','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'23\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (177,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 13:15:27','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'26\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (178,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 13:15:27','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'27\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (179,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-08 13:15:27','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'28\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (180,1000,'FRW_menus',29,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-09 13:19:29','insert into FRW_menus(SystemID,ParentID,MenuDesc,ordering) values (\'4\',\'0\',\'اطلاعات پایه\',\'1\')'),
 (181,1000,'FRW_menus',30,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-09 13:25:04','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'4\',\'29\',\'نامه های رسیده\',\'YES\',\'1\',\'letter/receive.php\')'),
 (182,1000,'FRW_menus',31,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-09 13:26:22','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'4\',\'29\',\'ایجاد نامه\',\'YES\',\'1\',\'letter/newLetter.php\')'),
 (183,1000,'FRW_menus',30,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-09 13:26:37','update FRW_menus set SystemID=\'4\',MenuID=\'30\',MenuDesc=\'نامه های رسیده\',IsActive=\'YES\',ordering=\'2\',icon=null,MenuPath=\'letter/receive.php\' where MenuID=\'30\''),
 (184,1000,'FRW_menus',32,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-09 13:27:03','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'4\',\'29\',\'نامه های ارسالی\',\'YES\',\'3\',\'letter/send.php\')'),
 (185,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-09 13:27:26','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'31\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (186,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-09 13:27:26','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'30\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (187,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-09 13:27:26','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'32\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (188,1000,'LON_loans',2,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-09-11 12:05:13','insert into LON_loans(GroupID,LoanDesc) values (\'0\',\'ثثثثثثثث\')'),
 (189,1000,'LON_loans',3,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-09-11 12:49:49','insert into LON_loans(GroupID,LoanDesc,MaxAmount) values (\'2\',\'سشیشسیشسیش\',\'1000000000000\')'),
 (190,1000,'LON_loans',1,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-09-11 12:53:21','update LON_loans set LoanID=\'1\',GroupID=\'1\',LoanDesc=\'ثثثثثثثث\',MaxAmount=\'120000000\',CostusCount=\'36\',CostusInterval=\'30\',DelayCount=\'365\',InsureAmount=\'1200000\',FirstCostusAmount=\'1200000\',ForfeitPercent=\'10\',FeePercent=\'10\',FeeAmount=\'20000\',ProfitPercent=\'20\' where  LoanID=\'1\''),
 (191,1000,'LON_loans',1,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-09-11 12:53:46','update LON_loans set LoanID=\'1\',GroupID=\'1\',LoanDesc=\'وام طرح های بزرگ\',MaxAmount=\'120000000\',CostusCount=\'36\',CostusInterval=\'30\',DelayCount=\'365\',InsureAmount=\'1200000\',FirstCostusAmount=\'1200000\',ForfeitPercent=\'10\',FeePercent=\'10\',FeeAmount=\'20000\',ProfitPercent=\'20\' where  LoanID=\'1\''),
 (192,1000,'LON_loans',3,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-09-11 13:00:07','update LON_loans set LoanID=\'3\',GroupID=\'2\',LoanDesc=\'وام مسکن شماره 1000\',MaxAmount=\'1000000000000\',CostusCount=\'24\',CostusInterval=\'30\',DelayCount=\'60\',InsureAmount=\'12000\',FirstCostusAmount=\'10000000\',ForfeitPercent=\'20\',FeePercent=\'30\',FeeAmount=\'0\',ProfitPercent=\'10\' where  LoanID=\'3\''),
 (193,1000,'LON_loans',4,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-09-11 13:03:36','insert into LON_loans(GroupID,LoanDesc,MaxAmount,CostusCount,CostusInterval,DelayCount,InsureAmount,FirstCostusAmount,ForfeitPercent,FeePercent,ProfitPercent) values (\'3\',\'وام جزیی 1\',\'200000000\',\'36\',\'30\',\'12\',\'120000\',\'2000000\',\'30\',\'20\',\'10\')'),
 (194,1000,'LON_loans',5,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-09-11 13:06:25','insert into LON_loans(GroupID,LoanDesc,MaxAmount,CostusCount) values (\'2\',\'نمست نمست بت مست بمنتسی\',\'120000000\',\'34\')'),
 (195,1000,'LON_loans',2,NULL,'DELETE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-09-11 13:08:10','delete from LON_loans where  LoanID=\'2\''),
 (196,1000,'LON_loans',6,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-09-11 13:10:20','insert into LON_loans(GroupID,LoanDesc) values (\'1\',\'ئسبدوسیئبم.سو\')'),
 (197,1000,'LON_loans',6,NULL,'DELETE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-09-11 13:11:10','delete from LON_loans where  LoanID=\'6\''),
 (198,1000,'FRW_menus',33,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:35:51','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'2\',\'7\',\'حساب کل\',\'YES\',\'1\',\'baseinfo/kols.php\')'),
 (199,1000,'FRW_menus',34,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:36:11','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'2\',\'7\',\'حساب معین\',\'YES\',\'2\',\'baseinfo/moins.php\')'),
 (200,1000,'FRW_menus',8,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:36:20','update FRW_menus set SystemID=\'2\',MenuID=\'8\',MenuDesc=\'مدیریت کد حساب\',IsActive=\'YES\',ordering=\'3\',icon=null,MenuPath=\'account/ accounts.php\' where MenuID=\'8\''),
 (201,1000,'FRW_menus',25,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:36:27','update FRW_menus set SystemID=\'2\',MenuID=\'25\',MenuDesc=\'مدیریت تفصیلی ها\',IsActive=\'YES\',ordering=\'4\',icon=null,MenuPath=\'account/tafsilis.php\' where MenuID=\'25\''),
 (202,1000,'FRW_menus',35,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:37:06','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'2\',\'7\',\'بانک ها\',\'YES\',\'5\',\'baseinfo/banks.php\')'),
 (203,1000,'FRW_menus',36,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:37:34','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'2\',\'7\',\'حساب های بانکی\',\'YES\',\'6\',\'baseinfo/accounts.php\')'),
 (204,1000,'FRW_menus',8,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:38:01','update FRW_menus set SystemID=\'2\',MenuID=\'8\',MenuDesc=\'مدیریت کد حساب\',IsActive=\'YES\',ordering=\'3\',icon=null,MenuPath=\'baseinfo/ accounts.php\' where MenuID=\'8\''),
 (205,1000,'FRW_menus',25,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:38:07','update FRW_menus set SystemID=\'2\',MenuID=\'25\',MenuDesc=\'مدیریت تفصیلی ها\',IsActive=\'YES\',ordering=\'4\',icon=null,MenuPath=\'baseinfo/tafsilis.php\' where MenuID=\'25\''),
 (206,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:38:57','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'33\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (207,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:38:57','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'34\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (208,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:38:57','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'8\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (209,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:38:57','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'25\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (210,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:38:57','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'35\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (211,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:38:57','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'36\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (212,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:38:57','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'22\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (213,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:38:57','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'23\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (214,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:38:57','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'26\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (215,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:38:57','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'27\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (216,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 13:38:57','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'28\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (217,1000,'FRW_menus',33,NULL,'DELETE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 14:17:37','delete from FRW_menus where MenuID=\'33\''),
 (218,1000,'FRW_menus',34,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 14:18:00','update FRW_menus set SystemID=\'2\',MenuID=\'34\',MenuDesc=\'اجزای حساب\',IsActive=\'YES\',ordering=\'2\',icon=null,MenuPath=\'baseinfo/blocks.php\' where MenuID=\'34\''),
 (219,1000,'ACC_blocks',1,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 14:56:30','insert into ACC_blocks(BlockCode,LevelID,BlockDesc,BranchID) values (\'01\',\'1\',\'منتمطن\',\'1\')'),
 (220,1000,'ACC_blocks',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 14:56:47','update ACC_blocks set BlockID=\'1\',BlockCode=\'01\',LevelID=\'1\',BlockDesc=\'منتمطنییی\' where  BlockID=\'\''),
 (221,1000,'ACC_blocks',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 14:57:45','update ACC_blocks set BlockID=\'1\',BlockCode=\'01\',LevelID=\'1\',BlockDesc=\'منتمطنییی\' where  BlockID=\'1\''),
 (222,1000,'ACC_blocks',1,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 15:01:13','delete from ACC_blocks where BlockID=\'1\' '),
 (223,1000,'ACC_blocks',2,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 15:01:34','insert into ACC_blocks(BlockCode,LevelID,BlockDesc,BranchID) values (\'01\',\'1\',\'گروه 1\',\'1\')'),
 (224,1000,'ACC_blocks',3,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 15:01:55','insert into ACC_blocks(BlockCode,LevelID,BlockDesc,BranchID) values (\'01\',\'2\',\'بانک\',\'1\')'),
 (225,1000,'ACC_blocks',4,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 15:04:28','insert into ACC_blocks(LevelID,BlockCode,BlockDesc,essence,BranchID) values (\'2\',\'02\',\'هزینه\',\'DEBTOR\',\'1\')'),
 (226,1000,'ACC_blocks',2,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 15:08:57','update ACC_blocks set BlockID=\'2\',LevelID=\'1\',BlockCode=\'01\',BlockDesc=\'گروه  1\' where  BlockID=\'2\''),
 (227,1000,'ACC_blocks',5,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 15:10:01','insert into ACC_blocks(LevelID,BlockCode,BlockDesc,BranchID) values (\'3\',\'01\',\'بدهکاران\',\'1\')'),
 (228,1000,'ACC_blocks',6,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 15:10:12','insert into ACC_blocks(LevelID,BlockCode,BlockDesc,BranchID) values (\'3\',\'02\',\'پرداختنی\',\'1\')'),
 (229,1000,'FRW_menus',8,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-11 18:08:39','update FRW_menus set SystemID=\'2\',MenuID=\'8\',MenuDesc=\'مدیریت کد حساب\',IsActive=\'YES\',ordering=\'3\',icon=null,MenuPath=\'baseinfo/CostCodes.php\' where MenuID=\'8\''),
 (230,1000,'ACC_blocks',7,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 18:20:48','insert into ACC_blocks(LevelID,BlockCode,BlockDesc,BranchID) values (\'3\',\'03\',\'معین 1\',\'1\')'),
 (231,1000,'ACC_blocks',8,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 18:21:57','insert into ACC_blocks(LevelID,BlockCode,BlockDesc,BranchID) values (\'3\',\'04\',\'سسس\',\'1\')'),
 (232,1000,'ACC_blocks',9,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 18:23:26','insert into ACC_blocks(LevelID,BlockCode,BlockDesc,BranchID) values (\'3\',\'05\',\'ییشسیش\',\'1\')'),
 (233,1000,'ACC_CostCodes',3,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 18:27:41','update ACC_CostCodes c \n			left join ACC_blocks b1 on(b1.levelID=1 AND b1.blockID=c.level1)\n			left join ACC_blocks b2 on(b2.levelID=2 AND b2.blockID=c.level2)\n			left join ACC_blocks b3 on(b3.levelID=3 AND b3.blockID=c.level3)\n			set c.CostCode=concat(ifnull(b1.blockCode,\'\'),\n								ifnull(b2.blockCode,\'\'),\n								ifnull(b3.blockCode,\'\') )\n			where CostID=\'3\''),
 (234,1000,'ACC_blocks',2,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 18:28:07','update ACC_blocks set BlockID=\'2\',LevelID=\'1\',BlockCode=\'01\',BlockDesc=\'گروه  1\' where  BlockID=\'2\''),
 (235,1000,'ACC_blocks',2,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 18:29:14','update ACC_blocks set BlockID=\'2\',LevelID=\'1\',BlockCode=\'1\',BlockDesc=\'گروه  1\' where  BlockID=\'2\''),
 (236,1000,'ACC_CostCodes',3,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 18:30:24','delete from ACC_CostCodes where CostID=\'3\''),
 (237,1000,'ACC_CostCodes',4,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 18:30:57','update ACC_CostCodes c \n			left join ACC_blocks b1 on(b1.levelID=1 AND b1.blockID=c.level1)\n			left join ACC_blocks b2 on(b2.levelID=2 AND b2.blockID=c.level2)\n			left join ACC_blocks b3 on(b3.levelID=3 AND b3.blockID=c.level3)\n			set c.CostCode=concat(ifnull(b1.blockCode,\'\'),\n								ifnull(b2.blockCode,\'\'),\n								ifnull(b3.blockCode,\'\') )\n			where CostID=\'4\''),
 (238,1000,'ACC_CostCodes',5,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 18:31:06','update ACC_CostCodes c \n			left join ACC_blocks b1 on(b1.levelID=1 AND b1.blockID=c.level1)\n			left join ACC_blocks b2 on(b2.levelID=2 AND b2.blockID=c.level2)\n			left join ACC_blocks b3 on(b3.levelID=3 AND b3.blockID=c.level3)\n			set c.CostCode=concat(ifnull(b1.blockCode,\'\'),\n								ifnull(b2.blockCode,\'\'),\n								ifnull(b3.blockCode,\'\') )\n			where CostID=\'5\''),
 (239,1000,'ACC_tafsilis',1,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 19:28:02','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID) values (\'1\',\'1000\',\'شرکت 1\',\'1\')'),
 (240,1000,'ACC_tafsilis',2,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 19:28:28','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID) values (\'1\',\'1001\',\'شرکت 2\',\'1\')'),
 (241,1000,'ACC_tafsilis',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 19:29:50','update ACC_tafsilis set TafsiliID=\'1\',TafsiliType=\'1\',TafsiliCode=\'1000\',TafsiliDesc=\'شرکت 111\' where  TafsiliID=\'1\''),
 (242,1000,'ACC_tafsilis',3,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 19:30:19','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID) values (\'2\',\'1\',\'شخص 1\',\'1\')'),
 (243,1000,'ACC_tafsilis',4,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 19:30:50','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID) values (\'2\',\'2\',\'شخص 2\',\'1\')'),
 (244,1000,'ACC_tafsilis',4,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 19:30:54','update ACC_tafsilis set TafsiliID=\'4\',TafsiliType=\'2\',TafsiliCode=\'2\',TafsiliDesc=\'شخص 222\' where  TafsiliID=\'4\''),
 (245,1000,'ACC_tafsilis',4,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-11 19:31:03','delete from ACC_tafsilis where  TafsiliID=\'4\''),
 (246,1000,'ACC_banks',1,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 17:57:07','insert into ACC_banks(BankID,BranchID,BankDesc) values (\'0\',\'1\',\'شیسشی\')'),
 (247,1000,'ACC_banks',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 17:57:49','update ACC_banks set BankID=\'1\',BankDesc=\'ملی\' where BankID=\'1\''),
 (248,1000,'ACC_accounts',1,1,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 18:11:25','insert into ACC_accounts(BranchID,BankID,AccountDesc,AccountNo,AccountType,IsActive) values (\'1\',\'1\',\'سبس\',\'2\',\'1\',\'YES\')'),
 (249,1000,'ACC_accounts',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 18:14:29','update ACC_accounts set AccountID=\'1\',BranchID=\'1\',BankID=\'1\',AccountDesc=\'ملی 98098\',AccountNo=\'0300098098\',AccountType=\'1\',IsActive=\'YES\' where AccountID=\'1\''),
 (250,1000,'ACC_accounts',1,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 18:18:00','update ACC_accounts set IsActive=\'NO\' where AccountID=\'1\''),
 (251,1000,'ACC_accounts',1,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 18:20:44','delete from ACC_accounts where AccountID=\'1\''),
 (252,1000,'ACC_accounts',2,1,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 18:21:10','insert into ACC_accounts(BranchID,BankID,AccountDesc,AccountNo,AccountType,IsActive) values (\'1\',\'1\',\'ملی 98098\',\'3000098098\',\'1\',\'YES\')'),
 (253,1000,'ACC_cheques',1,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 18:35:16','insert into ACC_cheques(ChequeID,AccountID,SerialNo,MinNo,MaxNo,IsActive) values (\'0\',\'2\',\'33333\',\'1\',\'12\',\'YES\')'),
 (254,1000,'ACC_cheques',2,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 18:36:28','insert into ACC_cheques(ChequeID,AccountID,SerialNo,MinNo,MaxNo,IsActive) values (\'0\',\'2\',\'33333\',\'1\',\'12\',\'YES\')'),
 (255,1000,'ACC_cheques',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 18:39:32','update ACC_cheques set ChequeID=\'1\',AccountID=\'2\',SerialNo=\'123456\',MinNo=\'1\',MaxNo=\'12\',IsActive=\'YES\' where  ChequeID=\'1\''),
 (256,1000,'ACC_cheques',2,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 18:42:14','insert into ACC_cheques(ChequeID,AccountID,SerialNo,MinNo,MaxNo,IsActive) values (\'0\',\'2\',\'45678\',\'10\',\'20\',\'YES\')'),
 (257,1000,'FRW_menus',35,NULL,'DELETE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-12 18:45:58','delete from FRW_menus where MenuID=\'35\''),
 (258,1000,'FRW_menus',36,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-12 18:46:03','update FRW_menus set SystemID=\'2\',MenuID=\'36\',MenuDesc=\'حساب های بانکی\',IsActive=\'YES\',ordering=\'5\',icon=null,MenuPath=\'baseinfo/accounts.php\' where MenuID=\'36\''),
 (259,1000,'ACC_cheques',2,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 18:52:31','delete from ACC_cheques where ChequeID=\'2\''),
 (260,1000,'ACC_cheques',1,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 18:52:34','delete from ACC_cheques where ChequeID=\'1\''),
 (261,1000,'ACC_accounts',2,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 18:52:39','delete from ACC_accounts where AccountID=\'2\''),
 (262,1000,'ACC_banks',1,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 18:52:43','delete from ACC_banks where BankID=\'1\'');
INSERT INTO `DataAudit` (`DataAuditID`,`PersonID`,`TableName`,`MainObjectID`,`SubObjectID`,`ActionType`,`SystemID`,`PageName`,`description`,`IPAddress`,`ActionTime`,`QueryString`) VALUES 
 (263,1000,'ACC_docs',1,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 22:50:28','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'1\',now(),\'2015/09/12\',\'1000\')'),
 (264,1000,'ACC_docs',2,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 22:50:44','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'1\',now(),\'2015/09/12\',\'1000\')'),
 (265,1000,'ACC_docs',3,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 22:51:17','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'1\',now(),\'2015/09/12\',\'1000\')'),
 (266,1000,'ACC_docs',4,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 23:16:55','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'1\',now(),\'2015/09/12\',\'1000\')'),
 (267,1000,'ACC_docs',5,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 23:20:32','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'1\',now(),\'2015/09/12\',\'1000\')'),
 (268,1000,'ACC_docs',6,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 23:22:27','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'1\',now(),\'2015/09/12\',\'1000\')'),
 (269,1000,'ACC_docs',7,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 23:24:01','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'1\',now(),\'2015/09/12\',\'1000\')'),
 (270,1000,'ACC_docs',8,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 23:25:00','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'1\',now(),\'2015/09/12\',\'1000\')'),
 (271,1000,'ACC_docs',1,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 23:26:03','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'1\',now(),\'2015/09/12\',\'1000\')'),
 (272,1000,'ACC_docs',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 23:30:23','update ACC_docs set DocID=\'1\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'1\',DocDate=\'2015/09/12\',description=\'من بتمنب تسمنتب\' where  DocID=\'1\''),
 (273,1000,'ACC_docs',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 23:30:33','update ACC_docs set DocID=\'1\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'1\',DocDate=\'2015/09/12\',description=\'س بسکمیب کسمنب کس\' where  DocID=\'1\''),
 (274,1000,'ACC_docs',1,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 23:36:36','delete from ACC_docs where DocID=\'1\''),
 (275,1000,'ACC_docs',2,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-12 23:59:23','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'1\',now(),\'2015/09/12\',\'1000\')'),
 (276,1000,'ACC_docs',3,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:04:07','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'2\',now(),\'2015/09/12\',\'1000\')'),
 (277,1000,'ACC_docs',4,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:04:10','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'3\',now(),\'2015/09/12\',\'1000\')'),
 (278,1000,'ACC_docs',5,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:04:16','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'4\',now(),\'2015/09/12\',\'1000\')'),
 (279,1000,'ACC_docs',5,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:04:20','delete from ACC_docs where DocID=\'5\''),
 (280,1000,'ACC_docs',6,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:05:43','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'4\',now(),\'2015/09/12\',\'1000\')'),
 (281,1000,'ACC_docs',6,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:05:47','delete from ACC_docs where DocID=\'6\''),
 (282,1000,'ACC_docs',4,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:06:45','delete from ACC_docs where DocID=\'4\''),
 (283,1000,'ACC_docs',7,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:11:08','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'3\',now(),\'2015/09/12\',\'1000\')'),
 (284,1000,'ACC_docs',8,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:11:11','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'4\',now(),\'2015/09/12\',\'1000\')'),
 (285,1000,'ACC_docs',7,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:11:37','delete from ACC_docs where DocID=\'7\''),
 (286,1000,'ACC_docs',8,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:11:44','delete from ACC_docs where DocID=\'8\''),
 (287,1000,'ACC_docs',2,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:11:51','delete from ACC_docs where DocID=\'2\''),
 (288,1000,'ACC_docs',3,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:11:56','delete from ACC_docs where DocID=\'3\''),
 (289,1000,'ACC_docs',9,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:12:19','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'1\',now(),\'2015/09/12\',\'1000\')'),
 (290,1000,'ACC_DocItems',1,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-13 00:18:30','insert into ACC_DocItems(DocID,RowID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount) values (\'9\',\'0\',\'5\',\'1\',\'1\',\'120000\',\'0\')'),
 (291,1000,'ACC_DocItems',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 11:44:55','update ACC_DocItems set DocID=\'9\',RowID=\'1\',CostID=\'4\',TafsiliType=\'1\',TafsiliID=\'1\',DebtorAmount=\'120000\',CreditorAmount=\'0\',details=null,locked=\'NO\' where RowID=\'1\''),
 (292,1000,'ACC_DocItems',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 11:46:24','update ACC_DocItems set DocID=\'9\',RowID=\'1\',CostID=\'4\',TafsiliType=\'2\',DebtorAmount=\'120000\',CreditorAmount=\'0\',details=null,locked=\'NO\' where RowID=\'1\''),
 (293,1000,'ACC_DocItems',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 11:47:15','update ACC_DocItems set DocID=\'9\',RowID=\'1\',CostID=\'4\',TafsiliType=\'1\',DebtorAmount=\'120000\',CreditorAmount=\'0\',details=null,locked=\'NO\' where RowID=\'1\''),
 (294,1000,'ACC_DocItems',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 11:47:19','update ACC_DocItems set DocID=\'9\',RowID=\'1\',CostID=\'4\',TafsiliType=\'2\',DebtorAmount=\'120000\',CreditorAmount=\'0\',details=null,locked=\'NO\' where RowID=\'1\''),
 (295,1000,'ACC_DocItems',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 11:47:37','update ACC_DocItems set DocID=\'9\',RowID=\'1\',CostID=\'4\',TafsiliType=\'1\',DebtorAmount=\'120000\',CreditorAmount=\'0\',details=null,locked=\'NO\' where RowID=\'1\''),
 (296,1000,'ACC_DocItems',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 11:48:52','update ACC_DocItems set DocID=\'9\',RowID=\'1\',CostID=\'4\',TafsiliType=\'2\',TafsiliID=null,DebtorAmount=\'120000\',CreditorAmount=\'0\',details=null,locked=\'NO\' where RowID=\'1\''),
 (297,1000,'ACC_DocItems',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 11:49:01','update ACC_DocItems set DocID=\'9\',RowID=\'1\',CostID=\'4\',TafsiliType=\'2\',TafsiliID=\'3\',DebtorAmount=\'120000\',CreditorAmount=\'0\',details=null,locked=\'NO\' where RowID=\'1\''),
 (298,1000,'ACC_DocItems',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 11:49:06','update ACC_DocItems set DocID=\'9\',RowID=\'1\',CostID=\'4\',TafsiliType=\'2\',TafsiliID=\'3\',DebtorAmount=\'120000\',CreditorAmount=\'0\',details=\'sdfsdf\',locked=\'NO\' where RowID=\'1\''),
 (299,1000,'ACC_DocItems',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 11:49:10','update ACC_DocItems set DocID=\'9\',RowID=\'1\',CostID=\'4\',TafsiliType=\'2\',TafsiliID=\'3\',DebtorAmount=\'120000\',CreditorAmount=\'0\',details=\'سییبسی\',locked=\'NO\' where RowID=\'1\''),
 (300,1000,'ACC_DocItems',2,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 11:49:33','insert into ACC_DocItems(DocID,RowID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount) values (\'9\',\'0\',\'5\',\'1\',\'1\',\'0\',\'120000\')'),
 (301,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:03:18','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'34\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (302,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:03:18','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'8\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (303,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:03:18','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'25\',\'1000\',\'YES\',\'NO\',\'NO\',\'NO\')'),
 (304,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:03:18','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'36\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (305,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:03:18','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'22\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (306,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:03:18','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'23\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (307,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:03:18','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'26\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (308,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:03:18','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'27\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (309,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:03:18','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'28\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (310,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:05:48','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'34\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (311,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:05:48','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'8\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (312,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:05:48','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'25\',\'1000\',\'YES\',\'NO\',\'YES\',\'NO\')'),
 (313,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:05:48','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'36\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (314,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:05:48','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'22\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (315,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:05:48','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'23\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (316,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:05:48','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'26\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (317,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:05:48','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'27\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (318,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:05:48','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'28\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (319,1000,'ACC_tafsilis',1,NULL,'UPDATE',1,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 12:05:59','update ACC_tafsilis set TafsiliID=\'1\',TafsiliType=\'1\',TafsiliCode=\'1000\',TafsiliDesc=\'شرکت ی111\' where  TafsiliID=\'1\''),
 (320,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:08:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'34\',\'1000\',\'YES\',\'NO\',\'NO\',\'NO\')'),
 (321,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:08:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'8\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (322,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:08:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'25\',\'1000\',\'YES\',\'NO\',\'YES\',\'NO\')'),
 (323,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:08:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'36\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (324,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:08:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'22\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (325,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:08:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'23\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (326,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:08:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'26\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (327,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:08:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'27\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (328,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:08:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'28\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (329,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'34\',\'1000\',\'YES\',\'NO\',\'NO\',\'NO\')'),
 (330,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'8\',\'1000\',\'YES\',\'NO\',\'NO\',\'NO\')'),
 (331,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'25\',\'1000\',\'YES\',\'NO\',\'YES\',\'NO\')'),
 (332,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'36\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (333,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'22\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (334,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'23\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (335,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'26\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (336,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'27\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (337,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:17','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'28\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (338,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'34\',\'1000\',\'YES\',\'NO\',\'NO\',\'NO\')'),
 (339,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'8\',\'1000\',\'YES\',\'NO\',\'NO\',\'NO\')'),
 (340,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'25\',\'1000\',\'YES\',\'NO\',\'YES\',\'NO\')'),
 (341,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'36\',\'1000\',\'YES\',\'NO\',\'NO\',\'NO\')'),
 (342,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'22\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (343,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'23\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (344,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'26\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (345,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'27\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (346,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:24:55','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'28\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (347,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:26:59','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'34\',\'1000\',\'YES\',\'NO\',\'NO\',\'NO\')'),
 (348,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:26:59','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'8\',\'1000\',\'YES\',\'NO\',\'NO\',\'NO\')'),
 (349,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:26:59','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'25\',\'1000\',\'YES\',\'NO\',\'YES\',\'NO\')'),
 (350,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:26:59','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'36\',\'1000\',\'YES\',\'YES\',\'NO\',\'NO\')'),
 (351,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:26:59','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'22\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (352,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:26:59','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'23\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (353,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:26:59','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'26\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (354,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:26:59','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'27\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (355,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:26:59','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'28\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (356,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:28:08','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'34\',\'1000\',\'YES\',\'NO\',\'NO\',\'NO\')'),
 (357,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:28:08','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'8\',\'1000\',\'YES\',\'NO\',\'NO\',\'NO\')'),
 (358,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:28:08','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'25\',\'1000\',\'YES\',\'YES\',\'NO\',\'NO\')'),
 (359,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:28:08','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'36\',\'1000\',\'YES\',\'YES\',\'NO\',\'NO\')'),
 (360,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:28:08','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'22\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (361,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:28:08','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'23\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (362,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:28:08','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'26\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (363,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:28:08','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'27\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (364,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:28:08','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'28\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (365,1000,'ACC_banks',1,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 12:37:05','insert into ACC_banks(BankID,BranchID,BankDesc) values (\'0\',\'1\',\'ملی\')'),
 (366,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:44:13','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'34\',\'1000\',\'YES\',\'NO\',\'NO\',\'NO\')'),
 (367,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:44:13','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'8\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (368,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:44:13','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'25\',\'1000\',\'YES\',\'YES\',\'NO\',\'NO\')'),
 (369,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:44:13','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'36\',\'1000\',\'YES\',\'YES\',\'NO\',\'NO\')'),
 (370,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:44:13','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'22\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (371,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:44:13','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'23\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (372,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:44:13','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'26\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (373,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:44:13','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'27\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (374,1000,'FRW_access',0,1000,'ADD',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-17 12:44:13','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'28\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (375,1000,'ACC_accounts',1,1,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 12:51:01','insert into ACC_accounts(BranchID,BankID,AccountDesc,AccountNo,AccountType,IsActive) values (\'1\',\'1\',\'98098 جاری ملی\',\'3000098098\',\'1\',\'YES\')'),
 (376,1000,'ACC_DocChecks',1,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 12:56:53','insert into ACC_DocChecks(DocID,CheckNo,AccountID,CheckDate,amount) values (\'9\',\'1234\',\'1\',\'2015/09/17\',\'1254000\')'),
 (377,1000,'ACC_DocChecks',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 13:00:25','update ACC_DocChecks set CheckID=\'1\',DocID=\'9\',CheckNo=\'1245\',AccountID=\'1\',CheckDate=\'2015/09/17\',amount=\'1254000\',CheckStatus=\'1\',description=null where  CheckID=\'1\''),
 (378,1000,'ACC_DocChecks',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 13:00:31','update ACC_DocChecks set CheckID=\'1\',DocID=\'9\',CheckNo=\'1245\',AccountID=\'1\',CheckDate=\'2015/09/17\',amount=\'1254000\',CheckStatus=\'1\',description=null where  CheckID=\'1\''),
 (379,1000,'ACC_DocChecks',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 13:01:27','update ACC_DocChecks set CheckID=\'1\',DocID=\'9\',CheckNo=\'1245\',AccountID=\'1\',CheckDate=\'2015/09/17\',amount=\'1254000\',CheckStatus=\'1\',reciever=\'سیبسی\',description=null where  CheckID=\'1\''),
 (380,1000,'ACC_DocChecks',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 13:01:29','update ACC_DocChecks set CheckID=\'1\',DocID=\'9\',CheckNo=\'1245\',AccountID=\'1\',CheckDate=\'2015/09/17\',amount=\'1254000\',CheckStatus=\'1\',reciever=\'سیبسی\',description=\'سیبسی\' where  CheckID=\'1\''),
 (381,1000,'ACC_DocChecks',1,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 13:01:35','delete from ACC_DocChecks where CheckID=\'1\' '),
 (382,1000,'ACC_DocItems',9,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 13:05:17','delete from ACC_DocItems where RowID=\'9\''),
 (383,1000,'ACC_DocItems',2,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 13:08:35','delete from ACC_DocItems where RowID=\'2\''),
 (384,1000,'ACC_DocChecks',2,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 13:09:25','insert into ACC_DocChecks(DocID,CheckNo,AccountID,CheckDate,amount,reciever,description) values (\'9\',\'1245\',\'1\',\'2015/09/17\',\'1540000\',\'فلانی\',\'وام فلان ....\')'),
 (385,1000,'ACC_docs',9,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-17 20:47:47','delete from ACC_docs where DocID=\'9\''),
 (386,1000,'ACC_docs',1,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:24:14','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'1\',now(),\'2015/09/18\',\'1000\')'),
 (387,1000,'ACC_DocItems',2,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:25:06','insert into ACC_DocItems(DocID,ItemID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount) values (\'1\',\'0\',\'4\',\'1\',\'1\',\'150000\',\'0\')'),
 (388,1000,'ACC_DocItems',3,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:25:21','insert into ACC_DocItems(DocID,ItemID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount) values (\'1\',\'0\',\'5\',\'2\',\'3\',\'0\',\'150000\')'),
 (389,1000,'ACC_DocChecks',3,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:28:04','insert into ACC_DocChecks(DocID,CheckNo,AccountID,CheckDate,amount) values (\'1\',\'14587\',\'1\',\'2015/09/18\',\'1500000\')'),
 (390,1000,'ACC_docs',2,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:29:14','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'2\',now(),\'2015/09/18\',\'1000\')'),
 (391,1000,'ACC_docs',3,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:29:35','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'3\',now(),\'2015/09/18\',\'1000\')'),
 (392,1000,'ACC_docs',4,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:32:00','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'4\',now(),\'2015/09/18\',\'1000\')'),
 (393,1000,'ACC_DocChecks',4,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:32:20','insert into ACC_DocChecks(DocID,CheckNo,AccountID,CheckDate,amount) values (\'4\',\'147\',\'1\',\'2015/09/18\',\'4780000\')'),
 (394,1000,'ACC_DocItems',4,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:32:32','insert into ACC_DocItems(DocID,ItemID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount) values (\'4\',\'0\',\'4\',\'1\',\'1\',\'15478000\',\'0\')'),
 (395,1000,'ACC_docs',2,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:32:59','update ACC_docs set DocID=\'2\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'2\',DocDate=\'2015/09/20\',description=null where  DocID=\'2\''),
 (396,1000,'ACC_docs',2,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:33:09','update ACC_docs set DocID=\'2\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'2\',DocDate=\'2015/09/18\',description=null where  DocID=\'2\''),
 (397,1000,'ACC_docs',2,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:48:27','update ACC_docs set DocID=\'2\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'2\',DocDate=\'2015/09/20\',description=null where  DocID=\'2\''),
 (398,1000,'ACC_docs',2,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:49:00','update ACC_docs set DocID=\'2\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'2\',DocDate=\'2015/09/20\',description=null where  DocID=\'2\''),
 (399,1000,'ACC_docs',2,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:49:26','update ACC_docs set DocID=\'2\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'2\',DocDate=\'2015/09/20\',description=null where  DocID=\'2\''),
 (400,1000,'ACC_docs',2,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:51:08','update ACC_docs set DocID=\'2\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'2\',DocDate=\'2015/09/18\',description=null where  DocID=\'2\''),
 (401,1000,'ACC_docs',2,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:51:15','update ACC_docs set DocID=\'2\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'2\',DocDate=\'2015/09/18\',description=null where  DocID=\'2\''),
 (402,1000,'ACC_docs',4,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:51:26','update ACC_docs set DocID=\'4\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'4\',DocDate=\'2015/09/21\',description=null where  DocID=\'4\''),
 (403,1000,'ACC_docs',3,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:51:42','update ACC_docs set DocID=\'3\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'3\',DocDate=\'2015/09/22\',description=null where  DocID=\'3\''),
 (404,1000,'ACC_docs',3,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:52:09','update ACC_docs set DocID=\'3\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'3\',DocDate=\'2015/09/22\',description=null where  DocID=\'3\''),
 (405,1000,'ACC_docs',3,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:53:06','update ACC_docs set DocID=\'3\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'3\',DocDate=\'2015/09/21\',description=null where  DocID=\'3\''),
 (406,1000,'ACC_docs',2,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:53:13','update ACC_docs set DocID=\'2\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'2\',DocDate=\'2015/09/20\',description=null where  DocID=\'2\''),
 (407,1000,'ACC_docs',2,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:53:24','update ACC_docs set DocID=\'2\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'2\',DocDate=\'2015/09/21\',description=null where  DocID=\'2\''),
 (408,1000,'ACC_docs',5,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:54:17','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'5\',now(),\'2015/09/18\',\'1000\')'),
 (409,1000,'ACC_docs',5,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:56:29','update ACC_docs set DocID=\'5\',CycleID=\'1\',BranchID=\'1\',LocalNo=\'5\',DocDate=\'2015/09/23\',description=null where  DocID=\'5\''),
 (410,1000,'ACC_docs',6,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:56:49','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'6\',\'2015/09/23\',\'2015/09/18\',\'1000\')'),
 (411,1000,'ACC_docs',7,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:58:06','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1\',\'1\',\'7\',\'2015/09/23\',\'2015/09/18\',\'1000\')'),
 (412,1000,'ACC_docs',7,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:58:39','delete from ACC_docs where DocID=\'7\''),
 (413,1000,'ACC_docs',5,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:58:46','delete from ACC_docs where DocID=\'5\''),
 (414,1000,'ACC_docs',3,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:58:59','delete from ACC_docs where DocID=\'3\''),
 (415,1000,'ACC_docs',6,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 10:59:09','delete from ACC_docs where DocID=\'6\''),
 (416,1000,'ACC_DocItems',5,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 11:41:04','insert into ACC_DocItems(DocID,ItemID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount) values (\'1\',\'0\',\'4\',\'1\',\'1\',\'350000\',\'0\')'),
 (417,1000,'ACC_DocItems',6,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 11:41:22','insert into ACC_DocItems(DocID,ItemID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount) values (\'1\',\'0\',\'5\',\'1\',\'1\',\'0\',\'40000\')'),
 (418,1000,'ACC_DocItems',6,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 11:44:12','update ACC_DocItems set DocID=\'1\',ItemID=\'6\',CostID=\'5\',TafsiliType=\'1\',TafsiliID=\'1\',DebtorAmount=\'0\',CreditorAmount=\'350000\',details=null,locked=\'NO\' where ItemID=\'6\''),
 (419,1000,'FRW_menus',23,NULL,'UPDATE',2,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-18 20:12:05','update FRW_menus set SystemID=\'2\',MenuID=\'23\',MenuDesc=\'سند افتتاحیه / اختتامیه\',IsActive=\'YES\',ordering=\'2\',icon=null,MenuPath=\'docs/CloseOpenDocs.php\' where MenuID=\'23\''),
 (420,1000,'ACC_docs',8,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 20:24:14','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1\',\'1\',\'5\',\'2015/09/21\',now(),\'ENDCYCLE\',\'سند اختتامیه\',\'1000\')'),
 (421,1000,'ACC_docs',9,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-18 20:24:57','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1\',\'1\',\'5\',\'2015/09/21\',now(),\'ENDCYCLE\',\'سند اختتامیه\',\'1000\')'),
 (422,1000,'ACC_docs',10,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-20 21:38:45','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1\',\'1\',\'6\',\'2015/09/21\',now(),\'ENDCYCLE\',\'سند اختتامیه\',\'1000\')'),
 (423,1000,'ACC_docs',10,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-20 21:43:55','delete from ACC_docs where DocID=\'10\''),
 (424,1000,'ACC_docs',9,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-20 21:44:05','delete from ACC_docs where DocID=\'9\''),
 (425,1000,'ACC_docs',11,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-20 21:44:11','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1\',\'1\',\'5\',\'2015/09/21\',now(),\'ENDCYCLE\',\'سند اختتامیه\',\'1000\')'),
 (426,1000,'ACC_docs',12,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-20 23:08:58','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1\',\'1\',\'6\',\'2015/09/21\',now(),\'ENDCYCLE\',\'سند اختتامیه\',\'1000\')'),
 (427,1000,'ACC_docs',13,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-20 23:09:01','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1\',\'1\',\'7\',\'2015/09/21\',now(),\'ENDCYCLE\',\'سند اختتامیه\',\'1000\')'),
 (428,1000,'ACC_docs',13,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-20 23:56:54','delete from ACC_docs where DocID=\'13\''),
 (429,1000,'ACC_docs',12,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-20 23:57:10','delete from ACC_docs where DocID=\'12\''),
 (430,1000,'ACC_docs',11,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-20 23:57:16','delete from ACC_docs where DocID=\'11\''),
 (431,1000,'ACC_docs',14,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-09-20 23:57:21','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1\',\'1\',\'5\',\'2015/09/21\',now(),\'ENDCYCLE\',\'سند اختتامیه\',\'1000\')'),
 (432,1000,'FRW_menus',37,NULL,'ADD',1,'http://rtfund/office/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-21 23:19:40','insert into FRW_menus(SystemID,ParentID,MenuDesc,ordering) values (\'4\',\'0\',\'فرمساز\',\'4\')'),
 (433,1000,'FRW_menus',38,NULL,'ADD',1,'http://rtfund/office/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-21 23:20:40','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'4\',\'37\',\'مدیریت فرم ها\',\'YES\',\'1\',\'formGenerator/buildForm.php\')'),
 (434,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/office/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-21 23:20:52','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'31\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (435,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/office/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-21 23:20:52','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'30\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (436,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/office/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-21 23:20:52','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'32\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (437,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/office/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-21 23:20:52','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'38\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (438,1000,'FGR_forms',1,NULL,'ADD',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 23:26:12','insert into FGR_forms(FormName) values (\'dsfsdfs\')'),
 (439,1000,'FGR_forms',1,NULL,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 23:26:46','update FGR_forms set FormID=\'1\'ld00,FormName=\'1\'ld01,reference=null where  FormID=\'1\''),
 (440,1000,'FGR_steps',1,1,'ADD',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 23:30:12','insert into FGR_steps(FormID,ordering,StepTitle,PostID,BreakDuration) values (\'1\',\'1\',\'asdsad\',\'1\',\'2\')'),
 (441,1000,'FGR_steps',2,1,'ADD',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 23:32:45','insert into FGR_steps(FormID,ordering,StepTitle,PostID,BreakDuration) values (\'1\',\'2\',\'sdsds\',\'2\',\'2\')'),
 (442,1000,'FGR_steps',2,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 23:32:59','update FGR_steps set StepID=\'2\',FormID=\'1\',ordering=\'3\',StepTitle=\'sdsds\',PostID=\'2\',BreakDuration=\'2\' where StepID=\'2\''),
 (443,1000,'FGR_steps',1,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 23:33:03','update FGR_steps set StepID=\'1\',FormID=\'1\',ordering=\'4\',StepTitle=\'asdsad\',PostID=\'1\',BreakDuration=\'2\' where StepID=\'1\''),
 (444,1000,'FGR_steps',2,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 23:47:19','update FGR_steps set StepID=\'2\',FormID=\'1\',ordering=\'1\',StepTitle=\'sdsds\',PostID=\'2\',BreakDuration=\'2\' where StepID=\'2\''),
 (445,1000,'FGR_steps',1,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 23:47:23','update FGR_steps set StepID=\'1\',FormID=\'1\',ordering=\'2\',StepTitle=\'asdsad\',PostID=\'1\',BreakDuration=\'2\' where StepID=\'1\''),
 (446,1000,'FGR_steps',3,1,'ADD',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 23:47:43','insert into FGR_steps(FormID,ordering,StepTitle,PostID,BreakDuration) values (\'1\',\'3\',\'dsfsdfsd\',\'1\',\'3\')'),
 (447,1000,'FGR_steps',1,NULL,'DELETE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 23:49:32','delete from FGR_steps where StepID=\'1\''),
 (448,1000,'FGR_steps',4,1,'ADD',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 23:50:12','insert into FGR_steps(FormID,ordering,StepTitle,PostID,BreakDuration) values (\'1\',\'3\',\'fffffff\',\'1\',\'3\')'),
 (449,1000,'FGR_steps',3,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-22 00:23:41','update FGR_steps set StepID=\'3\',FormID=\'1\',ordering=\'2\',StepTitle=\'dsfsdfsd\',PostID=\'1\',BreakDuration=\'3\' where StepID=\'3\''),
 (450,1000,'FRW_systems',7,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-26 22:06:15','insert into FRW_systems(SysName,SysPath,SysIcon,IsActive) values (\'پرتال \',\'portal\',\'-\',\'YES\')'),
 (451,1000,'FRW_menus',39,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-26 22:06:42','insert into FRW_menus(SystemID,ParentID,MenuDesc,ordering) values (\'7\',\'0\',\'وام گیرنده\',\'2\')'),
 (452,1000,'FRW_menus',40,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-26 22:06:52','insert into FRW_menus(SystemID,ParentID,MenuDesc,ordering) values (\'7\',\'0\',\'سهامداران\',\'1\')'),
 (453,1000,'FRW_menus',41,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-26 22:07:12','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive) values (\'7\',\'39\',\'مدیریت اطلاعات وام\',\'YES\')'),
 (454,1000,'FRW_menus',41,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-26 22:07:29','update FRW_menus set SystemID=\'7\',MenuID=\'41\',MenuDesc=\'درخواست وام\',IsActive=\'YES\',ordering=\'1\',icon=null,MenuPath=\'/\' where MenuID=\'41\''),
 (455,1000,'FRW_menus',42,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-26 22:07:52','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive) values (\'7\',\'39\',\'مدیریت وام های دریافتی\',\'YES\')'),
 (456,1000,'FRW_menus',42,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-26 22:07:59','update FRW_menus set SystemID=\'7\',MenuID=\'42\',MenuDesc=\'مدیریت وام های دریافتی\',IsActive=\'YES\',ordering=\'2\',icon=null,MenuPath=\'/\' where MenuID=\'42\''),
 (457,1000,'FRW_menus',43,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-26 22:08:26','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'7\',\'39\',\'پرداخت اقساط\',\'YES\',\'3\',\'/\')'),
 (458,1000,'FRW_menus',44,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-26 22:08:43','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'7\',\'40\',\'مدیریت سهام\',\'YES\',\'1\',\'/\')'),
 (459,1000,'BSC_persons',1000,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-29 00:44:57','update BSC_persons set PersonID=\'1000\',UserName=\'admin\',fname=\'شبنم\',lname=\'جعفرخانی\',IsActive=\'YES\' where  PersonID=\'1000\''),
 (460,1000,'BSC_persons',1000,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-29 00:46:01','update BSC_persons set PersonID=\'1000\',UserName=\'admin\',fname=\'شبنم\',lname=\'جعفرخانی\',IsActive=\'YES\',PostID=\'1\' where  PersonID=\'1000\''),
 (461,1000,'BSC_persons',1000,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-09-29 00:55:11','update BSC_persons set PersonID=\'1000\',fname=\'شبنم\',lname=\'جعفرخانی\',NationalID=\'0943021723\',EconomicID=null,PhoneNo=null,mobile=null,address=null,email=null where  PersonID=\'1000\''),
 (462,1000,'BSC_persons',1000,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-09-29 00:55:37','update BSC_persons set PersonID=\'1000\',fname=\'شبنم\',lname=\'جعفرخانی\',NationalID=\'0943021723\',EconomicID=null,PhoneNo=null,mobile=null,address=null,email=null where  PersonID=\'1000\''),
 (463,1000,'BSC_persons',1000,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-09-29 00:56:05','update BSC_persons set PersonID=\'1000\',fname=\'شبنم\',lname=\'جعفرخانی\',NationalID=\'0943021723\',EconomicID=null,PhoneNo=null,mobile=null,address=null,email=null where  PersonID=\'1000\''),
 (464,1000,'BSC_persons',1000,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-09-29 00:56:13','update BSC_persons set PersonID=\'1000\',fname=\'شبنم\',lname=\'جعفرخانی\',NationalID=\'0943021723\',EconomicID=null,PhoneNo=null,mobile=null,address=\'sdfsdf\',email=null where  PersonID=\'1000\''),
 (465,1000,'LON_requests',1,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-01 02:16:44','insert into LON_requests(LoanID,PersonID,ReqDate,ReqAmount) values (\'1\',\'1000\',now(),\'120000000\')'),
 (466,1000,'LON_requests',2,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-01 02:30:39','insert into LON_requests(LoanID,PersonID,ReqDate,ReqAmount) values (\'1\',\'1000\',now(),\'120000000\')'),
 (467,1000,'LON_requests',3,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-01 02:31:03','insert into LON_requests(LoanID,PersonID,ReqDate,ReqAmount) values (\'3\',\'1000\',now(),\'1000000000000\')'),
 (468,1000,'LON_requests',4,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-01 02:32:58','insert into LON_requests(LoanID,PersonID,ReqDate,ReqAmount) values (\'1\',\'1000\',now(),\'120000000\')'),
 (469,1000,'LON_requests',1001,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-01 02:33:38','insert into LON_requests(LoanID,PersonID,ReqDate,ReqAmount) values (\'1\',\'1000\',now(),\'120000000\')'),
 (470,1000,'LON_loans',1,NULL,'UPDATE',1000,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-06 20:28:17','update LON_loans set LoanID=\'1\',GroupID=\'1\',LoanDesc=\'وام طرح های بزرگ\',MaxAmount=\'120000000\',PartCount=\'24\',PartInterval=\'30\',DelayCount=\'60\',InsureAmount=\'1200000\',FirstPartAmount=\'18800000\',ForfeitPercent=\'10\',FeePercent=\'4\',FeeAmount=\'0\',ProfitPercent=\'20\' where  LoanID=\'1\''),
 (471,1000,'LON_loans',1,NULL,'UPDATE',1000,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-06 20:28:56','update LON_loans set LoanID=\'1\',GroupID=\'1\',LoanDesc=\'وام طرح های بزرگ\',MaxAmount=\'120000000\',PartCount=\'24\',PartInterval=\'30\',DelayCount=\'60\',InsureAmount=\'1200000\',FirstPartAmount=\'18800000\',ForfeitPercent=\'10\',FeePercent=\'4\',FeeAmount=\'0\',ProfitPercent=\'20\' where  LoanID=\'1\''),
 (472,1000,'LON_loans',1,NULL,'UPDATE',1000,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-06 20:31:36','update LON_loans set LoanID=\'1\',GroupID=\'1\',LoanDesc=\'وام طرح های بزرگ\',MaxAmount=\'120000000\',PartCount=\'24\',PartInterval=\'30\',DelayCount=\'60\',InsureAmount=\'1200000\',FirstPartAmount=\'18800000\',ForfeitPercent=\'10\',FeePercent=\'4\',FeeAmount=\'0\',ProfitPercent=\'20\' where  LoanID=\'1\''),
 (473,1000,'LON_requests',1,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-06 20:31:48','insert into LON_requests(LoanID,PersonID,ReqDate,ReqAmount,PartCount,PartInterval,DelayCount,InsureAmount,FirstPartAmount,ForfeitPercent,FeePercent,FeeAmount,ProfitPercent) values (\'1\',\'1000\',now(),\'120000000\',\'24\',\'30\',\'60\',\'1200000\',\'18800000\',\'10\',\'4\',\'0\',\'20\')'),
 (474,1000,'LON_requests',1,NULL,'UPDATE',1000,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-06 21:01:13','update LON_requests set RequestID=\'1\',ReqAmount=\'120000000\',ReqDetails=null,PartCount=\'24\',PartInterval=\'30\',DelayCount=\'60\',InsureAmount=\'1200000\',FirstPartAmount=\'18800000\',ForfeitPercent=\'10\',FeePercent=\'5\',FeeAmount=\'0\',ProfitPercent=\'20\' where  RequestID=\'1\''),
 (475,1000,'LON_requests',1,NULL,'UPDATE',1000,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-06 21:16:37','update LON_requests set RequestID=\'1\',OkAmount=\'120000000\',ReqDetails=null,PartCount=\'24\',PartInterval=\'30\',DelayCount=\'60\',InsureAmount=\'1200000\',FirstPartAmount=\'18800000\',ForfeitPercent=\'10\',FeePercent=\'5\',FeeAmount=\'0\',ProfitPercent=\'20\' where  RequestID=\'1\''),
 (476,1000,'LON_requests',2,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-06 21:47:04','insert into LON_requests(BranchID,LoanID,PersonID,ReqDate,ReqAmount,StatusID,PartCount,PartInterval,DelayCount,InsureAmount,FirstPartAmount,ForfeitPercent,FeePercent,FeeAmount,ProfitPercent) values (\'2\',\'4\',\'1000\',now(),\'200000000\',\'10\',\'36\',\'30\',\'12\',\'120000\',\'2000000\',\'30\',\'20\',\'0\',\'10\')'),
 (477,1000,'FRW_menus',45,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-06 23:36:52','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,icon,MenuPath) values (\'6\',\'13\',\'مدیریت ذینفعان\',\'YES\',\'2\',\'users.gif\',\'../framework/person/persons.php\')'),
 (478,1000,'FRW_menus',17,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-06 23:40:10','update FRW_menus set SystemID=\'6\',MenuID=\'17\',ParentID=\'0\',MenuDesc=\'گزارشات\',ordering=\'3\' where MenuID=\'17\''),
 (479,1000,'FRW_menus',13,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-06 23:40:42','update FRW_menus set SystemID=\'6\',MenuID=\'13\',ParentID=\'0\',MenuDesc=\'اطلاعات پایه\',ordering=\'1\' where MenuID=\'13\''),
 (480,1000,'FRW_menus',15,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-06 23:40:48','update FRW_menus set SystemID=\'6\',MenuID=\'15\',ParentID=\'0\',MenuDesc=\'اعطای تسهیلات\',ordering=\'2\' where MenuID=\'15\''),
 (481,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-06 23:55:26','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'14\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (482,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-06 23:55:26','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'45\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (483,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-06 23:55:26','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'16\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (484,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-06 23:55:26','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'18\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (485,1000,'LON_requests',3,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 10:57:22','insert into LON_requests(PersonID,ReqDate,StatusID) values (\'1000\',now(),\'1\')'),
 (486,1000,'LON_requests',4,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 10:58:26','insert into LON_requests(PersonID,ReqDate,StatusID) values (\'1000\',now(),\'1\')'),
 (487,1000,'LON_requests',5,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 10:59:30','insert into LON_requests(PersonID,ReqDate,StatusID) values (\'1000\',now(),\'1\')'),
 (488,1000,'LON_requests',6,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 11:04:12','insert into LON_requests(PersonID,ReqDate,StatusID) values (\'1000\',now(),\'1\')'),
 (489,1000,'LON_requests',7,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 11:04:48','insert into LON_requests(PersonID,ReqDate,StatusID) values (\'1000\',now(),\'1\')'),
 (490,1000,'LON_requests',8,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 11:10:05','insert into LON_requests(PersonID,ReqDate,StatusID) values (\'1000\',now(),\'1\')'),
 (491,1000,'LON_requests',9,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 11:10:51','insert into LON_requests(PersonID,ReqDate,StatusID) values (\'1000\',now(),\'1\')'),
 (492,1000,'LON_requests',10,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 11:45:09','insert into LON_requests(PersonID,ReqDate,StatusID) values (\'1000\',now(),\'1\')'),
 (493,1000,'LON_requests',11,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 11:45:42','insert into LON_requests(PersonID,ReqDate,StatusID) values (\'1000\',now(),\'1\')'),
 (494,1000,'LON_requests',12,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 11:45:59','insert into LON_requests(PersonID,ReqDate,StatusID) values (\'1000\',now(),\'1\')'),
 (495,1000,'LON_requests',13,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 11:46:25','insert into LON_requests(PersonID,ReqDate,StatusID) values (\'1000\',now(),\'1\')'),
 (496,1000,'LON_requests',14,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 11:46:46','insert into LON_requests(PersonID,ReqDate,StatusID) values (\'1000\',now(),\'1\')'),
 (497,1000,'LON_requests',15,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 11:48:18','insert into LON_requests(PersonID,ReqDate,StatusID) values (\'1000\',now(),\'1\')'),
 (498,1000,'LON_ReqParts',1,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 11:48:18','insert into LON_ReqParts(RequestID,PayDate,PartAmount,PayCount,IntervalType,ForfeitPercent,CustomerFee,FundFee,AgentFee) values (\'15\',\'2015/10/26\',\'60000000\',\'12\',\'MONTH\',\'4\',\'4\',\'10\',\'0\')'),
 (499,1000,'LON_requests',16,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 11:57:46','insert into LON_requests(PersonID,ReqDate,StatusID) values (\'1000\',now(),\'1\')'),
 (500,1000,'LON_ReqParts',2,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 11:57:46','insert into LON_ReqParts(RequestID,PayDate,PartAmount,PayCount,IntervalType,ForfeitPercent,CustomerFee,FundFee) values (\'16\',\'2015/10/26\',\'60000000\',\'12\',\'MONTH\',\'4\',\'4\',\'10\')'),
 (501,1000,'LON_ReqParts',3,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 13:14:14','insert into LON_ReqParts(RequestID,PartDesc,PayDate,PartAmount,PayCount,IntervalType,ForfeitPercent,CustomerFee,FundFee,AgentFee) values (\'16\',\'مرحله دوم\',\'2015/06/29\',\'60000000\',\'12\',\'DAY\',\'4\',\'10\',\'4\',\'6\')'),
 (502,1000,'LON_requests',17,NULL,'ADD',2,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 13:42:03','insert into LON_requests(BranchID,PersonID,ReqDate,ReqAmount,StatusID) values (\'1\',\'1000\',now(),\'120000000\',\'10\')'),
 (503,1000,'LON_requests',18,NULL,'ADD',2,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 13:43:05','insert into LON_requests(BranchID,PersonID,ReqDate,ReqAmount,StatusID) values (\'1\',\'1000\',now(),\'120000000\',\'10\')'),
 (504,1000,'LON_ReqParts',4,NULL,'ADD',2,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 13:44:36','insert into LON_ReqParts(RequestID,PartDesc,PayDate,PartAmount,PayCount,IntervalType,ForfeitPercent,CustomerFee,FundFee,AgentFee) values (\'18\',\'مرحله اول\',\'2016/01/21\',\'60000000\',\'12\',\'MONTH\',\'4\',\'10\',\'4\',\'6\')'),
 (505,1000,'LON_ReqParts',5,NULL,'ADD',2,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 13:46:08','insert into LON_ReqParts(RequestID,PartDesc,PayDate,PartAmount,PayCount,IntervalType,ForfeitPercent,CustomerFee,FundFee,AgentFee) values (\'18\',\'مرحله دوم\',\'2016/01/21\',\'60000000\',\'12\',\'MONTH\',\'4\',\'10\',\'4\',\'6\')'),
 (506,1000,'LON_requests',19,NULL,'ADD',2,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 13:47:59','insert into LON_requests(BranchID,PersonID,ReqDate,ReqAmount,StatusID) values (\'2\',\'1000\',now(),\'150000000\',\'10\')');
INSERT INTO `DataAudit` (`DataAuditID`,`PersonID`,`TableName`,`MainObjectID`,`SubObjectID`,`ActionType`,`SystemID`,`PageName`,`description`,`IPAddress`,`ActionTime`,`QueryString`) VALUES 
 (507,1000,'LON_ReqParts',6,NULL,'ADD',2,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 14:53:39','insert into LON_ReqParts(RequestID,PartDesc,PayDate,PartAmount,PayCount,IntervalType,ForfeitPercent,CustomerFee,FundFee,AgentFee) values (\'19\',\'م اول\',\'2016/01/06\',\'60000000\',\'12\',\'MONTH\',\'4\',\'5\',\'10\',\'2\')'),
 (508,1000,'LON_requests',20,NULL,'ADD',2,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 14:54:58','insert into LON_requests(BranchID,PersonID,ReqDate,ReqAmount,StatusID) values (\'2\',\'1000\',now(),\'21312\',\'10\')'),
 (509,1000,'LON_requests',20,NULL,'UPDATE',2,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-13 14:55:01','update LON_requests set RequestID=\'20\',BranchID=\'2\',ReqAmount=\'21312\',ReqDetails=null where  RequestID=\'20\''),
 (510,1000,'DMS_documents',1,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-17 14:14:28','insert into DMS_documents(DocType) values (\'1\')'),
 (511,1000,'DMS_documents',2,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-17 14:16:07','insert into DMS_documents(DocType) values (\'1\')'),
 (512,1000,'DMS_documents',3,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-17 14:16:48','insert into DMS_documents(DocType) values (\'1\')'),
 (513,1000,'DMS_documents',4,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-17 14:17:40','insert into DMS_documents(DocType) values (\'1\')'),
 (514,1000,'DMS_documents',5,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-17 14:26:58','insert into DMS_documents(DocType) values (\'1\')'),
 (515,1000,'DMS_documents',6,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-17 14:36:21','insert into DMS_documents(DocType) values (\'1\')'),
 (516,1000,'DMS_documents',7,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-17 14:36:46','insert into DMS_documents(DocType) values (\'1\')'),
 (517,1000,'DMS_documents',8,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-17 14:37:13','insert into DMS_documents(DocType) values (\'1\')'),
 (518,1000,'DMS_documents',9,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-17 14:38:34','insert into DMS_documents(DocType) values (\'1\')'),
 (519,1000,'DMS_documents',10,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-17 14:40:28','insert into DMS_documents(DocType) values (\'1\')'),
 (520,1000,'DMS_documents',11,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 08:28:59','insert into DMS_documents(DocType) values (\'1\')'),
 (521,1000,'DMS_documents',12,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 08:33:19','insert into DMS_documents(DocType) values (\'2\')'),
 (522,1000,'DMS_documents',13,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 08:36:02','insert into DMS_documents(DocType) values (\'2\')'),
 (523,1000,'DMS_documents',11,NULL,'DELETE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 08:51:44','delete from DMS_documents where  DocumentID=\'11\''),
 (524,1000,'DMS_documents',12,NULL,'DELETE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 08:51:47','delete from DMS_documents where  DocumentID=\'12\''),
 (525,1000,'DMS_documents',13,NULL,'DELETE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 08:51:51','delete from DMS_documents where  DocumentID=\'13\''),
 (526,1000,'DMS_documents',14,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 08:52:44','insert into DMS_documents(DocType) values (\'1\')'),
 (527,1000,'DMS_documents',14,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 09:00:58','update DMS_documents set DocumentID=\'14\',DocDesc=\'aaaaaaaaaaa\',DocType=\'1\',FileType=\'jpg\' where  DocumentID=\'14\''),
 (528,1000,'DMS_documents',14,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 09:01:15','update DMS_documents set DocumentID=\'14\',DocDesc=\'یسشیسش\',DocType=\'1\',FileType=\'jpg\' where  DocumentID=\'14\''),
 (529,1000,'DMS_documents',15,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 09:02:10','insert into DMS_documents(DocType) values (\'3\')'),
 (530,1000,'DMS_documents',16,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 09:04:33','insert into DMS_documents(DocType) values (\'4\')'),
 (531,1000,'DMS_documents',17,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 09:05:12','insert into DMS_documents(DocType) values (\'5\')'),
 (532,1000,'DMS_documents',17,NULL,'DELETE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 09:05:20','delete from DMS_documents where  DocumentID=\'17\''),
 (533,1000,'DMS_documents',16,NULL,'DELETE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 09:06:38','delete from DMS_documents where  DocumentID=\'16\''),
 (534,1000,'DMS_documents',15,NULL,'DELETE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 09:06:46','delete from DMS_documents where  DocumentID=\'15\''),
 (535,1000,'DMS_documents',14,NULL,'DELETE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 09:06:48','delete from DMS_documents where  DocumentID=\'14\''),
 (536,1000,'DMS_documents',18,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 09:07:45','insert into DMS_documents(DocType) values (\'1\')'),
 (537,1000,'FRW_systems',1001,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-18 09:22:09','insert into FRW_systems(SysName,SysPath,SysIcon,IsActive) values (\'سیستم مدیریت ذینفعان\',\'person\',\'person.gif\',\'YES\')'),
 (538,1000,'FRW_systems',8,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-18 09:23:34','update FRW_systems set SystemID=\'8\',SysName=\'سیستم مدیریت ذینفعان\',SysPath=\'person\',SysIcon=\'person.gif\',IsActive=\'YES\' where SystemID=\'8\''),
 (539,1000,'FRW_systems',8,NULL,'UPDATE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-18 09:23:40','update FRW_systems set SystemID=\'8\',SysName=\'سیستم مدیریت ذینفعان\',SysPath=\'person\',SysIcon=\'person.gif\',IsActive=\'YES\' where SystemID=\'8\''),
 (540,1000,'FRW_menus',48,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-18 09:29:57','insert into FRW_menus(SystemID,ParentID,MenuDesc,ordering) values (\'8\',\'0\',\'مدیریت ذینفعان\',\'1\')'),
 (541,1000,'FRW_menus',49,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-18 09:36:50','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,icon,MenuPath) values (\'8\',\'48\',\'اطلاعات و مدارک ذینفعان\',\'YES\',\'1\',\'user\',\'persons.php\')'),
 (542,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-18 09:38:13','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'49\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (543,1000,'DMS_documents',19,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 13:49:00','insert into DMS_documents(DocType) values (\'4\')'),
 (544,1000,'DMS_documents',19,NULL,'UPDATE',8,'http://rtfund/person/start.php?SystemID=8',NULL,'127.0.0.1','2015-10-18 13:50:22','update DMS_documents set DocumentID=\'19\',IsConfirm=\'YES\',ConfirmPersonID=\'1000\' where  DocumentID=\'19\''),
 (545,1005,'BSC_persons',1005,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 13:56:07','update BSC_persons set PersonID=\'1005\',fname=\' \',lname=null,CompanyName=\'پارک علم و فناوری\',NationalID=null,EconomicID=\'7777777777\',PhoneNo=null,mobile=null,address=\'جاده قوچان - پارک علم و فناوری\',email=\'park@us.com\' where  PersonID=\'1005\''),
 (546,1005,'DMS_documents',20,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 14:00:41','insert into DMS_documents(DocDesc,DocType) values (\'qqqqq\',\'1\')'),
 (547,1005,'DMS_documents',21,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 14:02:14','insert into DMS_documents(DocType,ObjectType,ObjectID) values (\'4\',\'person\',\'1005\')'),
 (548,1005,'DMS_documents',21,NULL,'DELETE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 14:03:56','delete from DMS_documents where  DocumentID=\'21\''),
 (549,1005,'DMS_documents',22,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-18 14:04:06','insert into DMS_documents(DocType,ObjectType,ObjectID) values (\'5\',\'person\',\'1005\')'),
 (550,1000,'DMS_documents',22,NULL,'UPDATE',8,'http://rtfund/person/start.php?SystemID=8',NULL,'127.0.0.1','2015-10-18 14:07:56','update DMS_documents set DocumentID=\'22\',IsConfirm=\'YES\',ConfirmPersonID=\'1000\' where  DocumentID=\'22\''),
 (551,1000,'LON_ReqParts',3,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-20 08:48:28','update LON_ReqParts set PartID=\'3\',RequestID=\'16\',PartDesc=\'مرحله دوم\',PayDate=\'2015/06/29\',PartAmount=\'60000000\',PayCount=\'12\',IntervalType=\'DAY\',PayInterval=\'45\',ForfeitPercent=\'4\',CustomerFee=\'4\',FundFee=\'10\' where  PartID=\'3\''),
 (552,1000,'LON_ReqParts',3,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-20 08:49:02','update LON_ReqParts set PartID=\'3\',RequestID=\'16\',PartDesc=\'مرحله دوم\',PayDate=\'2015/10/26\',PartAmount=\'60000000\',PayCount=\'12\',IntervalType=\'DAY\',PayInterval=\'45\',ForfeitPercent=\'4\',CustomerFee=\'4\',FundFee=\'10\' where  PartID=\'3\''),
 (553,1000,'LON_ReqParts',3,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-20 08:51:59','update LON_ReqParts set PartID=\'3\',RequestID=\'16\',PartDesc=\'مرحله دوم\',PayDate=\'2015/10/26\',PartAmount=\'60000000\',PayCount=\'12\',IntervalType=\'DAY\',PayInterval=\'45\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerFee=\'4\',FundFee=\'10\' where  PartID=\'3\''),
 (554,1000,'LON_ReqParts',3,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-20 08:52:17','update LON_ReqParts set PartID=\'3\',RequestID=\'16\',PartDesc=\'مرحله دوم\',PayDate=\'2015/10/26\',PartAmount=\'60000000\',PayCount=\'12\',IntervalType=\'DAY\',PayInterval=\'30\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerFee=\'4\',FundFee=\'10\' where  PartID=\'3\''),
 (555,1000,'LON_ReqParts',3,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-20 08:52:27','update LON_ReqParts set PartID=\'3\',RequestID=\'16\',PartDesc=\'مرحله دوم\',PayDate=\'2015/10/26\',PartAmount=\'60000000\',PayCount=\'12\',IntervalType=\'DAY\',PayInterval=\'60\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerFee=\'4\',FundFee=\'10\' where  PartID=\'3\''),
 (556,1000,'LON_requests',16,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-20 09:15:55','update LON_requests set RequestID=\'16\',BranchID=\'1\',ReqAmount=\'120000000\',StatusID=\'1\',ReqDetails=\'مثلا یه چیزی ....\',BorrowerDesc=\'شرکت فلان\',BorrowerID=\'05131684972\',LoanPersonID=\'1005\',assurance=\'1\',AgentGuarantee=\'YES\' where  RequestID=\'16\''),
 (557,1000,'LON_requests',16,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-20 10:13:55','update LON_requests set RequestID=\'16\' where  RequestID=\'16\''),
 (558,1000,'LON_requests',16,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-20 10:14:12','update LON_requests set RequestID=\'16\',StatusID=\'30\' where  RequestID=\'16\''),
 (559,1000,'LON_PartPayments',1,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:15:45','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'1970/01/01\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (560,1000,'LON_PartPayments',2,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:15:45','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'1970/01/01\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (561,1000,'LON_PartPayments',3,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:15:45','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'1970/01/01\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (562,1000,'LON_PartPayments',4,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:15:45','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'1970/01/01\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (563,1000,'LON_PartPayments',5,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:15:45','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'1970/01/01\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (564,1000,'LON_PartPayments',6,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:15:45','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'1970/01/01\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (565,1000,'LON_PartPayments',7,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:15:45','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'1970/01/01\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (566,1000,'LON_PartPayments',8,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:15:45','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'1970/01/01\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (567,1000,'LON_PartPayments',9,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:15:45','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'1970/01/01\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (568,1000,'LON_PartPayments',10,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:15:45','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'1970/01/01\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (569,1000,'LON_PartPayments',11,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:15:45','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'1970/01/01\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (570,1000,'LON_PartPayments',12,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:15:45','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'1970/01/01\',\'5000000\',\'108931\',\'4\',\'10\')'),
 (571,1000,'LON_PartPayments',13,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:18:17','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/10/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (572,1000,'LON_PartPayments',14,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:18:17','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/10/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (573,1000,'LON_PartPayments',15,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:18:17','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/10/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (574,1000,'LON_PartPayments',16,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:18:17','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/10/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (575,1000,'LON_PartPayments',17,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:18:17','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/10/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (576,1000,'LON_PartPayments',18,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:18:17','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/10/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (577,1000,'LON_PartPayments',19,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:18:17','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/10/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (578,1000,'LON_PartPayments',20,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:18:17','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/10/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (579,1000,'LON_PartPayments',21,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:18:17','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/10/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (580,1000,'LON_PartPayments',22,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:18:17','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/10/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (581,1000,'LON_PartPayments',23,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:18:17','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/10/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (582,1000,'LON_PartPayments',24,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:18:17','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'1970/01/01\',\'5000000\',\'108931\',\'4\',\'10\')'),
 (583,1000,'LON_PartPayments',25,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:20:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (584,1000,'LON_PartPayments',26,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:20:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/12/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (585,1000,'LON_PartPayments',27,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:20:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/01/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (586,1000,'LON_PartPayments',28,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:20:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/02/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (587,1000,'LON_PartPayments',29,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:20:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/03/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (588,1000,'LON_PartPayments',30,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:20:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/04/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (589,1000,'LON_PartPayments',31,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:20:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/05/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (590,1000,'LON_PartPayments',32,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:20:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/06/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (591,1000,'LON_PartPayments',33,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:20:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/07/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (592,1000,'LON_PartPayments',34,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:20:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/08/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (593,1000,'LON_PartPayments',35,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:20:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/09/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (594,1000,'LON_PartPayments',36,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:20:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'1970/01/01\',\'5000000\',\'108931\',\'4\',\'10\')'),
 (595,1000,'LON_PartPayments',37,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:49:10','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (596,1000,'LON_PartPayments',38,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:49:10','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/12/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (597,1000,'LON_PartPayments',39,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:49:10','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/01/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (598,1000,'LON_PartPayments',40,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:49:10','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/02/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (599,1000,'LON_PartPayments',41,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:49:10','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/03/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (600,1000,'LON_PartPayments',42,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:49:10','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/04/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (601,1000,'LON_PartPayments',43,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:49:10','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/05/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (602,1000,'LON_PartPayments',44,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:49:10','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/06/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (603,1000,'LON_PartPayments',45,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:49:10','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/07/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (604,1000,'LON_PartPayments',46,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:49:10','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/08/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (605,1000,'LON_PartPayments',47,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:49:10','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/09/26\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (606,1000,'LON_PartPayments',48,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:49:10','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/10/26\',\'5000000\',\'108931\',\'4\',\'10\')'),
 (607,1000,'LON_PartPayments',49,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:50:53','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (608,1000,'LON_PartPayments',50,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:50:53','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (609,1000,'LON_PartPayments',51,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:50:53','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (610,1000,'LON_PartPayments',52,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:50:53','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (611,1000,'LON_PartPayments',53,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:50:53','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (612,1000,'LON_PartPayments',54,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:50:53','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (613,1000,'LON_PartPayments',55,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:50:53','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (614,1000,'LON_PartPayments',56,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:50:53','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (615,1000,'LON_PartPayments',57,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:50:53','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (616,1000,'LON_PartPayments',58,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:50:53','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (617,1000,'LON_PartPayments',59,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:50:53','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (618,1000,'LON_PartPayments',60,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:50:53','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'108931\',\'4\',\'10\')'),
 (619,1000,'LON_PartPayments',61,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:54:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (620,1000,'LON_PartPayments',62,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:54:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/12/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (621,1000,'LON_PartPayments',63,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:54:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/01/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (622,1000,'LON_PartPayments',64,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:54:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/02/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (623,1000,'LON_PartPayments',65,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:54:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/03/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (624,1000,'LON_PartPayments',66,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:54:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/04/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (625,1000,'LON_PartPayments',67,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:54:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/05/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (626,1000,'LON_PartPayments',68,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:54:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/06/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (627,1000,'LON_PartPayments',69,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:54:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/07/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (628,1000,'LON_PartPayments',70,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:54:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/08/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (629,1000,'LON_PartPayments',71,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:54:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/09/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (630,1000,'LON_PartPayments',72,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:54:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/10/25\',\'5000000\',\'108931\',\'4\',\'10\')'),
 (631,1000,'LON_PartPayments',73,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:14','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (632,1000,'LON_PartPayments',74,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:14','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/12/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (633,1000,'LON_PartPayments',75,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:14','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/01/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (634,1000,'LON_PartPayments',76,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:14','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/02/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (635,1000,'LON_PartPayments',77,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:14','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/03/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (636,1000,'LON_PartPayments',78,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:14','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/04/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (637,1000,'LON_PartPayments',79,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:14','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/05/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (638,1000,'LON_PartPayments',80,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:14','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/06/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (639,1000,'LON_PartPayments',81,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:14','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/07/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (640,1000,'LON_PartPayments',82,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:14','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/08/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (641,1000,'LON_PartPayments',83,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:14','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/09/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (642,1000,'LON_PartPayments',84,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:14','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/10/25\',\'5000000\',\'108931\',\'4\',\'10\')'),
 (643,1000,'LON_PartPayments',85,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:32','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (644,1000,'LON_PartPayments',86,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:32','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/12/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (645,1000,'LON_PartPayments',87,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:32','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/01/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (646,1000,'LON_PartPayments',88,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:32','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/02/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (647,1000,'LON_PartPayments',89,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:32','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/03/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (648,1000,'LON_PartPayments',90,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:32','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/04/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (649,1000,'LON_PartPayments',91,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:32','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/05/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (650,1000,'LON_PartPayments',92,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:32','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/06/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (651,1000,'LON_PartPayments',93,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:32','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/07/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (652,1000,'LON_PartPayments',94,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:32','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/08/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (653,1000,'LON_PartPayments',95,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:32','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/09/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (654,1000,'LON_PartPayments',96,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:32','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/10/25\',\'5000000\',\'108931\',\'4\',\'10\')'),
 (655,1000,'LON_PartPayments',97,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:55','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (656,1000,'LON_PartPayments',98,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:55','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/12/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (657,1000,'LON_PartPayments',99,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:55','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/01/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (658,1000,'LON_PartPayments',100,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:55','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/02/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (659,1000,'LON_PartPayments',101,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:55','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/03/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (660,1000,'LON_PartPayments',102,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:55','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/04/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (661,1000,'LON_PartPayments',103,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:55','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/05/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (662,1000,'LON_PartPayments',104,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:55','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/06/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (663,1000,'LON_PartPayments',105,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:55','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/07/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (664,1000,'LON_PartPayments',106,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:55','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/08/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (665,1000,'LON_PartPayments',107,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:55','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/09/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (666,1000,'LON_PartPayments',108,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:57:55','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/10/25\',\'5000000\',\'108931\',\'4\',\'10\')'),
 (667,1000,'LON_PartPayments',109,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:58:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (668,1000,'LON_PartPayments',110,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:58:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/12/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (669,1000,'LON_PartPayments',111,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:58:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/01/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (670,1000,'LON_PartPayments',112,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:58:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/02/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (671,1000,'LON_PartPayments',113,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:58:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/03/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (672,1000,'LON_PartPayments',114,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:58:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/04/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (673,1000,'LON_PartPayments',115,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:58:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/05/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (674,1000,'LON_PartPayments',116,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:58:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/06/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (675,1000,'LON_PartPayments',117,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:58:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/07/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (676,1000,'LON_PartPayments',118,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:58:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/08/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (677,1000,'LON_PartPayments',119,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:58:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/09/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (678,1000,'LON_PartPayments',120,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:58:24','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/10/25\',\'5000000\',\'108931\',\'4\',\'10\')'),
 (679,1000,'LON_PartPayments',121,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:39','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (680,1000,'LON_PartPayments',122,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:39','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/12/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (681,1000,'LON_PartPayments',123,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:39','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/01/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (682,1000,'LON_PartPayments',124,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:39','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/02/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (683,1000,'LON_PartPayments',125,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:39','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/03/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (684,1000,'LON_PartPayments',126,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:39','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/04/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (685,1000,'LON_PartPayments',127,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:39','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/05/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (686,1000,'LON_PartPayments',128,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:39','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/06/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (687,1000,'LON_PartPayments',129,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:39','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/07/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (688,1000,'LON_PartPayments',130,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:39','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/08/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (689,1000,'LON_PartPayments',131,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:39','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/09/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (690,1000,'LON_PartPayments',132,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:39','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/10/25\',\'5000000\',\'108931\',\'4\',\'10\')'),
 (691,1000,'LON_PartPayments',133,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:41','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (692,1000,'LON_PartPayments',134,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:41','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/12/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (693,1000,'LON_PartPayments',135,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:41','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/01/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (694,1000,'LON_PartPayments',136,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:41','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/02/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (695,1000,'LON_PartPayments',137,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:41','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/03/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (696,1000,'LON_PartPayments',138,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:41','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/04/23\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (697,1000,'LON_PartPayments',139,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:41','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/05/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (698,1000,'LON_PartPayments',140,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:41','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/06/24\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (699,1000,'LON_PartPayments',141,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:41','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/07/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (700,1000,'LON_PartPayments',142,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:41','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/08/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (701,1000,'LON_PartPayments',143,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:41','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/09/25\',\'5000000\',\'109000\',\'4\',\'10\')'),
 (702,1000,'LON_PartPayments',144,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 10:59:41','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/10/25\',\'5000000\',\'108931\',\'4\',\'10\')'),
 (703,1000,'LON_PartPayments',145,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:22:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'3\',\'2015/12/25\',\'5000000\',\'220000\',\'4\',\'10\')'),
 (704,1000,'LON_PartPayments',146,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:22:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'3\',\'2016/02/23\',\'5000000\',\'220000\',\'4\',\'10\')'),
 (705,1000,'LON_PartPayments',147,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:22:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'3\',\'2016/04/23\',\'5000000\',\'220000\',\'4\',\'10\')'),
 (706,1000,'LON_PartPayments',148,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:22:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'3\',\'2016/06/22\',\'5000000\',\'220000\',\'4\',\'10\')'),
 (707,1000,'LON_PartPayments',149,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:22:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'3\',\'2016/08/21\',\'5000000\',\'220000\',\'4\',\'10\')'),
 (708,1000,'LON_PartPayments',150,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:22:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'3\',\'2016/10/20\',\'5000000\',\'220000\',\'4\',\'10\')'),
 (709,1000,'LON_PartPayments',151,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:22:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'3\',\'2016/12/19\',\'5000000\',\'220000\',\'4\',\'10\')'),
 (710,1000,'LON_PartPayments',152,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:22:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'3\',\'2017/02/17\',\'5000000\',\'220000\',\'4\',\'10\')'),
 (711,1000,'LON_PartPayments',153,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:22:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'3\',\'2017/04/18\',\'5000000\',\'220000\',\'4\',\'10\')'),
 (712,1000,'LON_PartPayments',154,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:22:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'3\',\'2017/06/17\',\'5000000\',\'220000\',\'4\',\'10\')'),
 (713,1000,'LON_PartPayments',155,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:22:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'3\',\'2017/08/16\',\'5000000\',\'220000\',\'4\',\'10\')'),
 (714,1000,'LON_PartPayments',156,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:22:50','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'3\',\'2017/10/15\',\'5000000\',\'211669\',\'4\',\'10\')'),
 (715,1000,'LON_ReqParts',2,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:24:47','update LON_ReqParts set PartID=\'2\',RequestID=\'16\',PartDesc=\'مرحله اول\',PayDate=\'2015/10/26\',PartAmount=\'60000000\',PayCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'10\',FundWage=\'10\' where  PartID=\'2\''),
 (716,1000,'LON_PartPayments',157,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:25:02','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (717,1000,'LON_PartPayments',158,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:25:02','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/12/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (718,1000,'LON_PartPayments',159,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:25:02','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/01/24\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (719,1000,'LON_PartPayments',160,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:25:02','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/02/23\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (720,1000,'LON_PartPayments',161,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:25:02','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/03/23\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (721,1000,'LON_PartPayments',162,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:25:02','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/04/23\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (722,1000,'LON_PartPayments',163,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:25:02','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/05/24\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (723,1000,'LON_PartPayments',164,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:25:02','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/06/24\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (724,1000,'LON_PartPayments',165,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:25:02','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/07/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (725,1000,'LON_PartPayments',166,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:25:02','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/08/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (726,1000,'LON_PartPayments',167,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:25:02','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/09/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (727,1000,'LON_PartPayments',168,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 11:25:02','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/10/25\',\'5000000\',\'274439\',\'10\',\'10\')'),
 (728,1000,'LON_PartPayments',157,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:05:24','update LON_PartPayments set PayID=\'157\',PartID=\'2\',PayDate=\'2015/11/25\',PayAmount=\'5000000\',WageAmount=\'275000\',CustomerWage=\'10\',FundWage=\'10\',StatusID=\'1\',ChequeNo=\'1234\',ChequeDate=\'1394-09-04\',ChequeBank=\'1\',ChequeBranch=\'فردوسی\' where  PayID=\'157\''),
 (729,1000,'LON_PartPayments',157,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:06:47','update LON_PartPayments set PayID=\'157\',PartID=\'2\',PayDate=\'2015/11/25\',PayAmount=\'5000000\',WageAmount=\'275000\',CustomerWage=\'10\',FundWage=\'10\',StatusID=\'1\',ChequeNo=\'1234\',ChequeDate=\'2015/11/26\',ChequeBank=\'1\',ChequeBranch=\'فردوسی\' where  PayID=\'157\''),
 (730,1000,'LON_PartPayments',158,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:15:24','update LON_PartPayments set PayID=\'158\',PartID=\'2\',PayDate=\'2015/12/25\',PayAmount=\'5000000\',WageAmount=\'275000\',CustomerWage=\'10\',FundWage=\'10\',StatusID=\'1\',ChequeNo=\'8798\',ChequeDate=\'2015/12/25\',ChequeBank=\'1\',ChequeBranch=\'پارک\' where  PayID=\'158\''),
 (731,1000,'LON_PartPayments',158,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:15:54','update LON_PartPayments set PayID=\'158\',PartID=\'2\',PayDate=\'2015/12/26\',PayAmount=\'5000000\',WageAmount=\'275000\',CustomerWage=\'10\',FundWage=\'10\',StatusID=\'1\',ChequeNo=\'8798\',ChequeDate=\'2015/12/25\',ChequeBank=\'1\',ChequeBranch=\'پارک\' where  PayID=\'158\''),
 (732,1000,'LON_PartPayments',158,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:16:05','update LON_PartPayments set PayID=\'158\',PartID=\'2\',PayDate=\'2015/12/26\',PayAmount=\'5000000\',WageAmount=\'275000\',CustomerWage=\'10\',FundWage=\'10\',StatusID=\'1\',ChequeNo=\'8798\',ChequeDate=\'2015/12/26\',ChequeBank=\'1\',ChequeBranch=\'پارک\' where  PayID=\'158\''),
 (733,1000,'LON_PartPayments',164,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:18:28','update LON_PartPayments set PayID=\'164\',PartID=\'2\',PayDate=\'2016/06/24\',PayAmount=\'5000000\',WageAmount=\'275000\',CustomerWage=\'10\',FundWage=\'10\',StatusID=\'1\',ChequeNo=\'6656\',ChequeDate=\'2016/06/24\',ChequeBank=\'1\',ChequeBranch=\'یسیشسیش\' where  PayID=\'164\''),
 (734,1000,'LON_PartPayments',164,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:19:11','update LON_PartPayments set PayID=\'164\',PartID=\'2\',PayDate=\'2016/06/24\',PayAmount=\'5000000\',WageAmount=\'275000\',CustomerWage=\'10\',FundWage=\'10\',StatusID=\'1\',ChequeNo=\'6656\',ChequeDate=\'2016/06/24\',ChequeBank=\'1\',ChequeBranch=\'لببپ\' where  PayID=\'164\''),
 (735,1000,'LON_PartPayments',169,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:22:31','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (736,1000,'LON_PartPayments',170,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:22:31','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/12/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (737,1000,'LON_PartPayments',171,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:22:31','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/01/24\',\'5000000\',\'275000\',\'10\',\'10\')');
INSERT INTO `DataAudit` (`DataAuditID`,`PersonID`,`TableName`,`MainObjectID`,`SubObjectID`,`ActionType`,`SystemID`,`PageName`,`description`,`IPAddress`,`ActionTime`,`QueryString`) VALUES 
 (738,1000,'LON_PartPayments',172,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:22:31','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/02/23\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (739,1000,'LON_PartPayments',173,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:22:31','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/03/23\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (740,1000,'LON_PartPayments',174,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:22:31','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/04/23\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (741,1000,'LON_PartPayments',175,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:22:31','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/05/24\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (742,1000,'LON_PartPayments',176,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:22:31','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/06/24\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (743,1000,'LON_PartPayments',177,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:22:31','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/07/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (744,1000,'LON_PartPayments',178,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:22:31','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/08/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (745,1000,'LON_PartPayments',179,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:22:31','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/09/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (746,1000,'LON_PartPayments',180,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:22:31','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/10/25\',\'5000000\',\'274439\',\'10\',\'10\')'),
 (747,1000,'LON_PartPayments',181,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:23:52','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (748,1000,'LON_PartPayments',182,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:23:52','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/12/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (749,1000,'LON_PartPayments',183,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:23:52','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/01/24\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (750,1000,'LON_PartPayments',184,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:23:52','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/02/23\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (751,1000,'LON_PartPayments',185,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:23:52','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/03/23\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (752,1000,'LON_PartPayments',186,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:23:52','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/04/23\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (753,1000,'LON_PartPayments',187,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:23:52','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/05/24\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (754,1000,'LON_PartPayments',188,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:23:52','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/06/24\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (755,1000,'LON_PartPayments',189,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:23:52','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/07/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (756,1000,'LON_PartPayments',190,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:23:52','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/08/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (757,1000,'LON_PartPayments',191,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:23:52','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/09/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (758,1000,'LON_PartPayments',192,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:23:52','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/10/25\',\'5000000\',\'274439\',\'10\',\'10\')'),
 (759,1000,'LON_PartPayments',184,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:24:48','update LON_PartPayments set PayID=\'184\',PartID=\'2\',PayDate=\'2016/02/23\',PayAmount=\'5000000\',WageAmount=\'275000\',CustomerWage=\'10\',FundWage=\'10\',StatusID=\'1\',ChequeBank=\'1\',ChequeBranch=null where  PayID=\'184\''),
 (760,1000,'LON_PartPayments',193,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:24:51','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/11/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (761,1000,'LON_PartPayments',194,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:24:51','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2015/12/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (762,1000,'LON_PartPayments',195,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:24:51','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/01/24\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (763,1000,'LON_PartPayments',196,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:24:51','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/02/23\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (764,1000,'LON_PartPayments',197,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:24:51','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/03/23\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (765,1000,'LON_PartPayments',198,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:24:51','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/04/23\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (766,1000,'LON_PartPayments',199,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:24:51','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/05/24\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (767,1000,'LON_PartPayments',200,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:24:51','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/06/24\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (768,1000,'LON_PartPayments',201,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:24:51','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/07/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (769,1000,'LON_PartPayments',202,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:24:51','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/08/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (770,1000,'LON_PartPayments',203,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:24:51','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/09/25\',\'5000000\',\'275000\',\'10\',\'10\')'),
 (771,1000,'LON_PartPayments',204,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-22 23:24:51','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'2\',\'2016/10/25\',\'5000000\',\'274439\',\'10\',\'10\')'),
 (772,1000,'LON_requests',16,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 00:28:36','update LON_requests set RequestID=\'16\',StatusID=\'40\' where  RequestID=\'16\''),
 (773,1000,'BSC_persons',1006,NULL,'ADD',1000,'http://rtfund/portal/login.php',NULL,'127.0.0.1','2015-10-23 00:30:45','insert into BSC_persons(UserName,UserPass,IsReal,CompanyName,email,IsCustomer) values (\'data\',\'$P$BNVG9Rrdv82.HBgdV6Gsq8SsXOJN671\',\'NO\',\'شرکت داده 3\',\'data3@yahoo.com\',\'YES\')'),
 (774,1000,'LON_ReqParts',5,NULL,'DELETE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:28:30','delete from LON_ReqParts where  PartID=\'5\''),
 (775,1000,'LON_PartPayments',205,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:29:00','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'4\',\'2016/02/20\',\'5000000\',\'275000\',\'10\',\'4\')'),
 (776,1000,'LON_PartPayments',206,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:29:00','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'4\',\'2016/03/20\',\'5000000\',\'275000\',\'10\',\'4\')'),
 (777,1000,'LON_PartPayments',207,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:29:00','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'4\',\'2016/04/20\',\'5000000\',\'275000\',\'10\',\'4\')'),
 (778,1000,'LON_PartPayments',208,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:29:00','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'4\',\'2016/05/21\',\'5000000\',\'275000\',\'10\',\'4\')'),
 (779,1000,'LON_PartPayments',209,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:29:00','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'4\',\'2016/06/21\',\'5000000\',\'275000\',\'10\',\'4\')'),
 (780,1000,'LON_PartPayments',210,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:29:00','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'4\',\'2016/07/22\',\'5000000\',\'275000\',\'10\',\'4\')'),
 (781,1000,'LON_PartPayments',211,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:29:00','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'4\',\'2016/08/22\',\'5000000\',\'275000\',\'10\',\'4\')'),
 (782,1000,'LON_PartPayments',212,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:29:00','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'4\',\'2016/09/22\',\'5000000\',\'275000\',\'10\',\'4\')'),
 (783,1000,'LON_PartPayments',213,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:29:00','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'4\',\'2016/10/22\',\'5000000\',\'275000\',\'10\',\'4\')'),
 (784,1000,'LON_PartPayments',214,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:29:00','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'4\',\'2016/11/21\',\'5000000\',\'275000\',\'10\',\'4\')'),
 (785,1000,'LON_PartPayments',215,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:29:00','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'4\',\'2016/12/21\',\'5000000\',\'275000\',\'10\',\'4\')'),
 (786,1000,'LON_PartPayments',216,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:29:00','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'4\',\'2017/01/20\',\'5000000\',\'274439\',\'10\',\'4\')'),
 (787,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:47:01','update LON_requests set RequestID=\'19\',BranchID=\'2\',ReqAmount=\'150000000\',ReqDetails=null,BorrowerDesc=null,BorrowerID=null,LoanPersonID=\'1006\',assurance=\'2\',AgentGuarantee=\'NO\' where  RequestID=\'19\''),
 (788,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:57:44','update LON_requests set RequestID=\'19\',BranchID=\'2\',ReqAmount=\'150000000\',ReqDetails=null,BorrowerDesc=null,BorrowerID=null,LoanPersonID=\'1006\',assurance=\'2\',AgentGuarantee=\'NO\' where  RequestID=\'19\''),
 (789,1000,'LON_PartPayments',217,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:58:06','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'6\',\'2016/02/05\',\'5000000\',\'137000\',\'5\',\'10\')'),
 (790,1000,'LON_PartPayments',218,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:58:06','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'6\',\'2016/03/06\',\'5000000\',\'137000\',\'5\',\'10\')'),
 (791,1000,'LON_PartPayments',219,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:58:06','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'6\',\'2016/04/04\',\'5000000\',\'137000\',\'5\',\'10\')'),
 (792,1000,'LON_PartPayments',220,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:58:06','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'6\',\'2016/05/05\',\'5000000\',\'137000\',\'5\',\'10\')'),
 (793,1000,'LON_PartPayments',221,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:58:06','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'6\',\'2016/06/05\',\'5000000\',\'137000\',\'5\',\'10\')'),
 (794,1000,'LON_PartPayments',222,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:58:06','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'6\',\'2016/07/06\',\'5000000\',\'137000\',\'5\',\'10\')'),
 (795,1000,'LON_PartPayments',223,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:58:06','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'6\',\'2016/08/06\',\'5000000\',\'137000\',\'5\',\'10\')'),
 (796,1000,'LON_PartPayments',224,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:58:06','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'6\',\'2016/09/06\',\'5000000\',\'137000\',\'5\',\'10\')'),
 (797,1000,'LON_PartPayments',225,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:58:06','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'6\',\'2016/10/07\',\'5000000\',\'137000\',\'5\',\'10\')'),
 (798,1000,'LON_PartPayments',226,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:58:06','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'6\',\'2016/11/06\',\'5000000\',\'137000\',\'5\',\'10\')'),
 (799,1000,'LON_PartPayments',227,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:58:06','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'6\',\'2016/12/06\',\'5000000\',\'137000\',\'5\',\'10\')'),
 (800,1000,'LON_PartPayments',228,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:58:06','insert into LON_PartPayments(PartID,PayDate,PayAmount,WageAmount,CustomerWage,FundWage) values (\'6\',\'2017/01/05\',\'5000000\',\'130387\',\'5\',\'10\')'),
 (801,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:58:58','update LON_requests set RequestID=\'19\',StatusID=\'30\' where  RequestID=\'19\''),
 (802,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 03:59:41','update LON_requests set RequestID=\'19\',StatusID=\'40\' where  RequestID=\'19\''),
 (803,1006,'LON_requests',19,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-23 10:29:23','update LON_requests set RequestID=\'19\',StatusID=\'50\' where  RequestID=\'19\''),
 (804,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 10:34:13','update LON_requests set RequestID=\'19\',BranchID=\'2\',ReqAmount=\'150000000\',ReqDetails=null,BorrowerDesc=null,BorrowerID=null,LoanPersonID=\'1006\',assurance=\'2\',AgentGuarantee=\'NO\',DocumentDesc=\'یک ضامن حقیقی با فیش حقوقی و حکم کارگزینی\nاطلاعات کامل مدیران شرکت\' where  RequestID=\'19\''),
 (805,1006,'DMS_documents',23,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-23 10:36:55','insert into DMS_documents(DocDesc,DocType,ObjectType,ObjectID) values (\'ضامن \',\'4\',\'person\',\'1006\')'),
 (806,1006,'DMS_documents',24,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-23 10:39:29','insert into DMS_documents(DocDesc,DocType,ObjectType,ObjectID) values (\'ضامن\',\'1\',\'loan\',\'19\')'),
 (807,1006,'DMS_documents',24,NULL,'DELETE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-23 10:40:16','delete from DMS_documents where  DocumentID=\'24\''),
 (808,1006,'DMS_documents',25,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-23 11:01:22','insert into DMS_documents(DocDesc,DocType,ObjectType,ObjectID) values (\'ضامن\',\'1\',\'loan\',\'19\')'),
 (809,1006,'LON_requests',19,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-23 11:01:28','update LON_requests set RequestID=\'19\',StatusID=\'50\' where  RequestID=\'19\''),
 (810,1006,'LON_requests',19,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-23 11:01:38','update LON_requests set RequestID=\'19\',StatusID=\'50\' where  RequestID=\'19\''),
 (811,1006,'LON_requests',19,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-23 11:02:22','update LON_requests set RequestID=\'19\',StatusID=\'50\' where  RequestID=\'19\''),
 (812,1000,'DMS_documents',23,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 21:48:46','update DMS_documents set DocumentID=\'23\',IsConfirm=\'YES\',ConfirmPersonID=\'1000\' where  DocumentID=\'23\''),
 (813,1000,'DMS_documents',25,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 21:54:25','update DMS_documents set DocumentID=\'25\',IsConfirm=\'NO\',ConfirmPersonID=\'1000\' where  DocumentID=\'25\''),
 (814,1000,'DMS_documents',25,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 21:54:59','update DMS_documents set DocumentID=\'25\',IsConfirm=\'NO\',ConfirmPersonID=\'1000\' where  DocumentID=\'25\''),
 (815,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-23 21:57:15','update LON_requests set RequestID=\'19\',StatusID=\'60\' where  RequestID=\'19\''),
 (816,1006,'DMS_documents',25,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-23 22:35:57','update DMS_documents set DocumentID=\'25\',DocDesc=\'ضامن \',DocType=\'1\',ObjectType=\'loan\',ObjectID=\'19\',FileType=\'jpg\',IsConfirm=\'NOTSET\',RejectDesc=\'سشتی مشیت خنشصم\' where  DocumentID=\'25\''),
 (817,1006,'LON_requests',19,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-23 22:55:52','update LON_requests set RequestID=\'19\',StatusID=\'50\' where  RequestID=\'19\''),
 (818,1000,'DMS_documents',25,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 00:48:18','update DMS_documents set DocumentID=\'25\',IsConfirm=\'NO\',ConfirmPersonID=\'1000\',RejectDesc=\'function () {\n        var me = this,\n            val = me.rawToValue(me.processRawValue(me.getRawValue()));\n        me.value = val;\n        return val;\n    }\' where  DocumentID=\'25\''),
 (819,1000,'DMS_documents',25,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 00:54:27','update DMS_documents set DocumentID=\'25\',IsConfirm=\'NO\',ConfirmPersonID=\'1000\',RejectDesc=\'function () {\n        var me = this,\n            val = me.rawToValue(me.processRawValue(me.getRawValue()));\n        me.value = val;\n        return val;\n    }\' where  DocumentID=\'25\''),
 (820,1000,'DMS_documents',25,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 00:55:51','update DMS_documents set DocumentID=\'25\',IsConfirm=\'NO\',ConfirmPersonID=\'1000\',RejectDesc=\'تناقض در شرح و فایل ارسالی\' where  DocumentID=\'25\''),
 (821,1000,'DMS_documents',25,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 00:57:22','update DMS_documents set DocumentID=\'25\',IsConfirm=\'NO\',ConfirmPersonID=\'1000\',RejectDesc=\'تناقض در فایل ارسالی با شرح\' where  DocumentID=\'25\''),
 (822,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 00:57:35','update LON_requests set RequestID=\'19\',StatusID=\'60\' where  RequestID=\'19\''),
 (823,1006,'DMS_documents',25,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-24 00:58:27','update DMS_documents set DocumentID=\'25\',DocDesc=\'ضامن اول\',DocType=\'1\',ObjectType=\'loan\',ObjectID=\'19\',FileType=\'jpg\',IsConfirm=\'NOTSET\',RejectDesc=\'تناقض در فایل ارسالی با شرح\' where  DocumentID=\'25\''),
 (824,1006,'DMS_documents',25,NULL,'DELETE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-24 00:58:35','delete from DMS_documents where  DocumentID=\'25\''),
 (825,1006,'DMS_documents',26,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-24 00:58:50','insert into DMS_documents(DocDesc,DocType,ObjectType,ObjectID) values (\'ضامن\',\'3\',\'loan\',\'19\')'),
 (826,1006,'LON_requests',19,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-24 00:59:04','update LON_requests set RequestID=\'19\',StatusID=\'50\' where  RequestID=\'19\''),
 (827,1000,'DMS_documents',26,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 00:59:59','update DMS_documents set DocumentID=\'26\',IsConfirm=\'YES\',ConfirmPersonID=\'1000\',RejectDesc=null where  DocumentID=\'26\''),
 (828,1000,'DMS_documents',26,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 01:00:46','update DMS_documents set DocumentID=\'26\',IsConfirm=\'YES\',ConfirmPersonID=\'1000\',RejectDesc=null where  DocumentID=\'26\''),
 (829,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 01:09:27','update LON_requests set RequestID=\'19\',StatusID=\'60\' where  RequestID=\'19\''),
 (830,1000,'LON_requests',18,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 02:09:30','update LON_requests set RequestID=\'18\',StatusID=\'30\' where  RequestID=\'18\''),
 (831,1000,'LON_requests',18,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 02:11:52','update LON_requests set RequestID=\'18\',StatusID=\'40\' where  RequestID=\'18\''),
 (832,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 02:16:10','update LON_requests set RequestID=\'19\',StatusID=\'60\' where  RequestID=\'19\''),
 (833,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 02:19:29','update LON_requests set RequestID=\'19\',StatusID=\'60\' where  RequestID=\'19\''),
 (834,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 02:20:25','update LON_requests set RequestID=\'19\',StatusID=\'60\' where  RequestID=\'19\''),
 (835,1006,'DMS_documents',27,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-24 02:45:02','insert into DMS_documents(DocType,ObjectType,ObjectID) values (\'3\',\'person\',\'1006\')'),
 (836,1006,'LON_requests',19,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-10-24 02:45:13','update LON_requests set RequestID=\'19\',StatusID=\'50\' where  RequestID=\'19\''),
 (837,1000,'DMS_documents',27,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 02:49:32','update DMS_documents set DocumentID=\'27\',IsConfirm=\'YES\',ConfirmPersonID=\'1000\',RejectDesc=null where  DocumentID=\'27\''),
 (838,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-24 02:49:41','update LON_requests set RequestID=\'19\',StatusID=\'70\' where  RequestID=\'19\''),
 (839,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-28 08:46:42','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (840,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 00:52:46','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'34\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (841,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 00:52:46','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'8\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (842,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 00:52:46','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'25\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (843,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 00:52:46','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'36\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (844,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 00:52:46','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'22\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (845,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 00:52:46','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'23\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (846,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 00:52:46','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'26\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (847,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 00:52:46','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'27\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (848,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 00:52:46','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'28\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (849,1000,'ACC_tafsilis',2,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-10-30 00:53:22','delete from ACC_tafsilis where  TafsiliID=\'2\''),
 (850,1000,'ACC_tafsilis',3,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-10-30 01:55:49','update ACC_tafsilis set TafsiliID=\'3\',TafsiliType=\'2\',TafsiliCode=\'1\',TafsiliDesc=\'94\' where  TafsiliID=\'3\''),
 (851,1000,'ACC_tafsilis',4,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-10-30 01:55:58','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID) values (\'2\',\'95\',\'95\',\'1\')'),
 (852,1000,'ACC_tafsilis',4,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-10-30 01:56:11','update ACC_tafsilis set TafsiliID=\'4\',TafsiliType=\'2\',TafsiliCode=\'95\',TafsiliDesc=\'95\' where  TafsiliID=\'4\''),
 (853,1000,'ACC_tafsilis',3,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-10-30 01:56:16','update ACC_tafsilis set TafsiliID=\'3\',TafsiliType=\'2\',TafsiliCode=\'1394\',TafsiliDesc=\'94\' where  TafsiliID=\'3\''),
 (854,1000,'ACC_tafsilis',3,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-10-30 01:56:22','update ACC_tafsilis set TafsiliID=\'3\',TafsiliType=\'2\',TafsiliCode=\'1394\',TafsiliDesc=\'1394\' where  TafsiliID=\'3\''),
 (855,1000,'ACC_tafsilis',4,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-10-30 01:56:26','update ACC_tafsilis set TafsiliID=\'4\',TafsiliType=\'2\',TafsiliCode=\'1395\',TafsiliDesc=\'1395\' where  TafsiliID=\'4\''),
 (856,1000,'ACC_tafsilis',5,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-10-30 01:56:38','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID) values (\'2\',\'1396\',\'1396\',\'1\')'),
 (857,1000,'ACC_tafsilis',6,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-10-30 01:56:46','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID) values (\'2\',\'1397\',\'1397\',\'1\')'),
 (858,1000,'ACC_tafsilis',7,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-10-30 01:56:52','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID) values (\'2\',\'1398\',\'1398\',\'1\')'),
 (859,1000,'ACC_tafsilis',8,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-10-30 01:57:01','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID) values (\'2\',\'1399\',\'1399\',\'1\')'),
 (860,1000,'ACC_tafsilis',9,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-10-30 01:57:08','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID) values (\'2\',\'1400\',\'1400\',\'1\')'),
 (861,1000,'FRW_menus',50,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 13:16:49','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'2\',\'7\',\'تعیین شعبه و دوره\',\'YES\',\'1\',\'global/UserState.php\')'),
 (862,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 13:17:04','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'50\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (863,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 13:17:04','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'34\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (864,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 13:17:04','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'8\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (865,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 13:17:04','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'25\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (866,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 13:17:04','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'36\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (867,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 13:17:04','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'22\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (868,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 13:17:04','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'23\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (869,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 13:17:04','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'26\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (870,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 13:17:04','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'27\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (871,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-10-30 13:17:04','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'28\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (872,1000,'ACC_blocks',10,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-10-30 14:33:59','insert into ACC_blocks(LevelID,BlockCode,BlockDesc,BranchID) values (\'1\',\'02\',\'وام ها\',\'1\')'),
 (873,1000,'ACC_CostCodes',6,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-10-30 14:34:43','update ACC_CostCodes c \n			left join ACC_blocks b1 on(b1.levelID=1 AND b1.blockID=c.level1)\n			left join ACC_blocks b2 on(b2.levelID=2 AND b2.blockID=c.level2)\n			left join ACC_blocks b3 on(b3.levelID=3 AND b3.blockID=c.level3)\n			set c.CostCode=concat(ifnull(b1.blockCode,\'\'),\n								ifnull(b2.blockCode,\'\'),\n								ifnull(b3.blockCode,\'\') )\n			where CostID=\'6\''),
 (874,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:41:14','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (875,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:42:33','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (876,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:42:33','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (877,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:43:53','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (878,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:43:53','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (879,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:44:18','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (880,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:44:18','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (881,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:45:00','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (882,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:45:00','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (883,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:46:06','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (884,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:46:06','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (885,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:46:37','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (886,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:46:37','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (887,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:47:21','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (888,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:47:21','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (889,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:48:10','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (890,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:48:10','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (891,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:50:14','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (892,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:50:14','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (893,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:50:45','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (894,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:50:45','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (895,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:51:17','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (896,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-10-30 14:51:17','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (897,1000,'LON_installments',228,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 15:50:02','update LON_installments set InstallmentID=\'228\',PartID=\'6\',InstallmentDate=\'2015/11/04\',InstallmentAmount=\'5000000\',WageAmount=\'130387\',CustomerWage=\'5\',FundWage=\'10\',StatusID=\'1\',ChequeBank=\'1\',ChequeBranch=null where  InstallmentID=\'228\''),
 (898,1000,'LON_installments',228,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 16:04:09','update LON_installments set InstallmentID=\'228\',PartID=\'6\',InstallmentDate=\'2015/11/03\',InstallmentAmount=\'5000000\',WageAmount=\'130387\',CustomerWage=\'5\',FundWage=\'10\',StatusID=\'1\',ChequeBank=\'1\',ChequeBranch=null where  InstallmentID=\'228\''),
 (899,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 16:07:02','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (900,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 16:07:02','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (901,1000,'ACC_docs',15,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 16:07:02','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (902,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 16:15:14','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (903,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 16:15:14','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (904,1000,'ACC_docs',16,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 16:15:14','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (905,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 16:16:27','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (906,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 16:16:27','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (907,1000,'ACC_docs',17,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 16:16:27','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (908,1000,'ACC_docs',1,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 16:58:29','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1394\',\'1\',\'1\',now(),\'2015/11/05\',\'1000\')'),
 (909,1000,'ACC_docs',2,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 16:58:38','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1394\',\'1\',\'2\',now(),\'2015/11/05\',\'1000\')'),
 (910,1000,'ACC_docs',1,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 16:59:44','delete from ACC_docs where DocID=\'1\''),
 (911,1000,'ACC_docs',2,NULL,'DELETE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 16:59:52','delete from ACC_docs where DocID=\'2\''),
 (912,1000,'ACC_docs',3,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 17:00:01','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,regPersonID) values (\'1394\',\'1\',\'1\',now(),\'2015/11/05\',\'1000\')'),
 (913,1000,'ACC_DocItems',1,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 17:14:56','insert into ACC_DocItems(DocID,ItemID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount) values (\'3\',\'0\',\'1\',\'1\',\'1\',\'2\',\'3\',\'1000\',\'0\')'),
 (914,1000,'ACC_tafsilis',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 17:17:18','update ACC_tafsilis set TafsiliID=\'1\',TafsiliType=\'1\',TafsiliCode=\'1000\',TafsiliDesc=\'آقای ایکس\' where  TafsiliID=\'1\''),
 (915,1000,'ACC_tafsilis',10,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 17:17:36','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID) values (\'3\',\'1\',\'شرکت داده 3\',\'1\')'),
 (916,1000,'ACC_tafsilis',10,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 17:58:05','update ACC_tafsilis set TafsiliID=\'10\',TafsiliType=\'3\',TafsiliCode=\'1\',TafsiliDesc=\'شرکت داده 3\',PersonID=\'1006\' where  TafsiliID=\'10\''),
 (917,1000,'ACC_tafsilis',11,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 17:59:37','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID,PersonID) values (\'3\',\'2\',\'پارک علم و فناوری\',\'1\',\'1005\')'),
 (918,1000,'ACC_CostCodes',73,NULL,'ADD',6,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 18:17:28','update ACC_CostCodes c \n			left join ACC_blocks b1 on(b1.levelID=1 AND b1.blockID=c.level1)\n			left join ACC_blocks b2 on(b2.levelID=2 AND b2.blockID=c.level2)\n			left join ACC_blocks b3 on(b3.levelID=3 AND b3.blockID=c.level3)\n			set c.CostCode=concat(ifnull(b1.blockCode,\'\'),\n								ifnull(b2.blockCode,\'\'),\n								ifnull(b3.blockCode,\'\') )\n			where CostID=\'73\''),
 (919,1000,'ACC_CostCodes',74,NULL,'ADD',6,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 18:24:34','update ACC_CostCodes c \n			left join ACC_blocks b1 on(b1.levelID=1 AND b1.blockID=c.level1)\n			left join ACC_blocks b2 on(b2.levelID=2 AND b2.blockID=c.level2)\n			left join ACC_blocks b3 on(b3.levelID=3 AND b3.blockID=c.level3)\n			set c.CostCode=concat(ifnull(b1.blockCode,\'\'),\n								ifnull(b2.blockCode,\'\'),\n								ifnull(b3.blockCode,\'\') )\n			where CostID=\'74\''),
 (920,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 18:26:16','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (921,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 18:26:16','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (922,1000,'ACC_docs',4,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 18:26:16','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (923,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 18:28:06','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (924,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 18:28:06','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (925,1000,'ACC_docs',5,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 18:28:06','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (926,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 18:28:49','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (927,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 18:28:49','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (928,1000,'ACC_docs',6,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 18:28:49','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (929,1000,'ACC_tafsilis',12,NULL,'ADD',6,'http://rtfund/portal/login.php',NULL,'127.0.0.1','2015-11-05 18:52:33','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID,PersonID) values (\'1\',\'1001\',\'پارک علم و فناوری\',\'1\',\'1001\')'),
 (930,1000,'BSC_persons',1001,NULL,'ADD',6,'http://rtfund/portal/login.php',NULL,'127.0.0.1','2015-11-05 18:52:33','insert into DataAudit(PersonID,SystemID,PageName,IPAddress,ActionTime,TableName,MainObjectID,ActionType,QueryString) values (\'1000\',\'6\',\'http://rtfund/portal/login.php\',\'127.0.0.1\',now(),\'ACC_tafsilis\',\'12\',\'ADD\',\'insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID,PersonID) values (\'1\',\'1001\',\'پارک علم و فناوری\',\'1\',\'1001\')\')'),
 (931,1000,'ACC_tafsilis',1,NULL,'DELETE',6,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 18:56:06','update ACC_tafsilis set IsActibe=\'NO\' where TafsiliID=\'1\''),
 (932,1000,'ACC_tafsilis',1,NULL,'DELETE',6,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 18:56:26','update ACC_tafsilis set IsActive=\'NO\' where TafsiliID=\'1\''),
 (933,1000,'ACC_tafsilis',14,NULL,'ADD',6,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 19:27:24','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID) values (\'1\',\'1\',\'صندوق نوآوری و شکوفایی\',\'1\')'),
 (934,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:31:22','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (935,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:31:22','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (936,1000,'ACC_docs',7,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:31:22','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (937,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:35:41','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (938,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:35:41','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (939,1000,'ACC_docs',8,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:35:41','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (940,1000,'ACC_DocItems',2,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:35:41','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'8\',\'73\',\'1\',\'13\',\'1\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (941,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:37:12','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (942,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:37:12','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (943,1000,'ACC_docs',9,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:37:12','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (944,1000,'ACC_DocItems',3,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:37:12','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'9\',\'73\',\'1\',\'13\',\'1\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (945,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:37:41','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (946,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:37:41','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (947,1000,'ACC_docs',10,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:37:41','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (948,1000,'ACC_DocItems',4,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:37:41','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'10\',\'73\',\'1\',\'13\',\'1\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (949,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:39:04','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (950,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:39:04','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (951,1000,'ACC_docs',11,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:39:04','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (952,1000,'ACC_DocItems',5,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:39:04','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'11\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (953,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:40:45','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (954,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:40:45','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (955,1000,'ACC_docs',12,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:40:45','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (956,1000,'ACC_DocItems',6,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:40:45','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'12\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (957,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:41:10','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (958,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:41:10','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (959,1000,'ACC_docs',13,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:41:10','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (960,1000,'ACC_DocItems',7,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:41:10','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'13\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (961,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:41:29','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (962,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:41:29','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\'');
INSERT INTO `DataAudit` (`DataAuditID`,`PersonID`,`TableName`,`MainObjectID`,`SubObjectID`,`ActionType`,`SystemID`,`PageName`,`description`,`IPAddress`,`ActionTime`,`QueryString`) VALUES 
 (963,1000,'ACC_docs',14,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:41:29','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (964,1000,'ACC_DocItems',8,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:41:29','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'14\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (965,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:42:46','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (966,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:42:46','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (967,1000,'ACC_docs',15,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:42:46','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (968,1000,'ACC_DocItems',9,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:42:46','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'15\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (969,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:45:26','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (970,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:45:26','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (971,1000,'ACC_docs',16,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:45:26','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (972,1000,'ACC_DocItems',10,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:45:27','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'16\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (973,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:55:46','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (974,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:55:46','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (975,1000,'ACC_docs',17,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:55:46','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (976,1000,'ACC_DocItems',11,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:55:46','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'17\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (977,1000,'LON_ReqParts',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:57:22','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (978,1000,'LON_requests',19,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:57:22','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (979,1000,'ACC_docs',18,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:57:22','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'2016\',\'2\',\'1\',now(),now(),\'4\',\'1000\')'),
 (980,1000,'ACC_DocItems',12,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:57:22','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'18\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (981,1000,'ACC_DocItems',13,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:57:22','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,locked,SourceType,SourceID) values (\'18\',\'74\',\'2\',\'15\',\'1\',\'12\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (982,1000,'ACC_DocItems',14,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 19:57:22','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,locked,SourceType,SourceID) values (\'18\',\'74\',\'2\',\'16\',\'1\',\'12\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (983,1000,'ACC_docs',3,NULL,'DELETE',6,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 19:58:16','delete from ACC_docs where DocID=\'3\''),
 (984,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:29:16','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (985,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:29:16','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (986,1000,'ACC_docs',19,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:29:16','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'1394\',\'2\',\'2\',now(),now(),\'4\',\'1000\')'),
 (987,1000,'ACC_DocItems',15,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:29:16','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'19\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (988,1000,'ACC_DocItems',16,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:29:16','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,locked,SourceType,SourceID) values (\'19\',\'74\',\'2\',\'15\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (989,1000,'ACC_DocItems',17,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:29:16','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,locked,SourceType,SourceID) values (\'19\',\'74\',\'2\',\'16\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (990,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:32:24','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (991,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:32:24','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (992,1000,'ACC_docs',20,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:32:24','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'1394\',\'2\',\'2\',now(),now(),\'4\',\'1000\')'),
 (993,1000,'ACC_DocItems',18,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:32:24','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'20\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (994,1000,'ACC_DocItems',19,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:32:24','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'20\',\'74\',\'2\',\'15\',\'0\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (995,1000,'ACC_DocItems',20,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:32:24','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'20\',\'74\',\'2\',\'16\',\'0\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (996,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:35:04','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (997,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:35:04','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (998,1000,'ACC_docs',21,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:35:04','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'1394\',\'2\',\'2\',now(),now(),\'4\',\'1000\')'),
 (999,1000,'ACC_DocItems',21,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:35:04','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'21\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1000,1000,'ACC_DocItems',22,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:35:04','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'21\',\'74\',\'2\',\'15\',\'0\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1001,1000,'ACC_DocItems',23,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:35:04','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'21\',\'74\',\'2\',\'16\',\'0\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1002,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:35:26','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'6\''),
 (1003,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:35:26','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (1004,1000,'ACC_docs',22,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:35:26','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'1394\',\'2\',\'2\',now(),now(),\'4\',\'1000\')'),
 (1005,1000,'ACC_DocItems',24,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:35:26','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'22\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1006,1000,'ACC_DocItems',25,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:35:26','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'22\',\'74\',\'2\',\'15\',\'0\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1007,1000,'ACC_DocItems',26,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:35:26','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'22\',\'74\',\'2\',\'16\',\'0\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1008,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:36:47','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'NO\' where  PartID=\'6\''),
 (1009,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:36:47','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (1010,1000,'ACC_docs',23,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:36:47','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'1394\',\'2\',\'3\',now(),now(),\'4\',\'1000\')'),
 (1011,1000,'ACC_DocItems',27,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:36:47','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'23\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1012,1000,'ACC_DocItems',28,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:36:47','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'23\',\'74\',\'2\',\'15\',\'2\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1013,1000,'ACC_DocItems',29,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:36:47','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'23\',\'74\',\'2\',\'16\',\'2\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1014,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:37:44','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'NO\' where  PartID=\'6\''),
 (1015,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:37:44','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (1016,1000,'ACC_docs',24,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:37:44','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'1394\',\'2\',\'4\',now(),now(),\'4\',\'1000\')'),
 (1017,1000,'ACC_DocItems',30,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:37:44','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'24\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1018,1000,'ACC_DocItems',31,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:37:44','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'24\',\'74\',\'2\',\'15\',\'2\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1019,1000,'ACC_DocItems',32,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:37:44','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'24\',\'74\',\'2\',\'16\',\'2\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1020,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:38:01','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'NO\' where  PartID=\'6\''),
 (1021,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:38:01','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (1022,1000,'ACC_docs',25,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:38:01','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'1394\',\'2\',\'5\',now(),now(),\'4\',\'1000\')'),
 (1023,1000,'ACC_DocItems',33,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:38:01','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'25\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1024,1000,'ACC_DocItems',34,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:38:01','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'25\',\'74\',\'2\',\'15\',\'0\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1025,1000,'ACC_DocItems',35,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:38:01','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'25\',\'74\',\'2\',\'16\',\'0\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1026,1000,'LON_ReqParts',6,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:38:26','update LON_ReqParts set PartID=\'6\',RequestID=\'19\',PartDesc=\'م اول\',PartDate=\'2016/01/06\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'0\',ForfeitPercent=\'4\',CustomerWage=\'5\',FundWage=\'10\',IsPayed=\'NO\' where  PartID=\'6\''),
 (1027,1000,'LON_requests',19,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:38:26','update LON_requests set RequestID=\'19\',StatusID=\'80\' where  RequestID=\'19\''),
 (1028,1000,'ACC_docs',26,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:38:26','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'1394\',\'2\',\'6\',now(),now(),\'4\',\'1000\')'),
 (1029,1000,'ACC_DocItems',36,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:38:26','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'26\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1030,1000,'ACC_DocItems',37,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:38:26','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'26\',\'74\',\'2\',\'15\',\'0\',\'965638\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1031,1000,'ACC_DocItems',38,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:38:26','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'26\',\'74\',\'2\',\'16\',\'0\',\'2309136\',\'YES\',\'PAY_LOAN_PART\',\'6\')'),
 (1032,1000,'LON_ReqParts',2,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:52:36','update LON_ReqParts set PartID=\'2\',RequestID=\'16\',PartDesc=\'مرحله اول\',PartDate=\'2015/10/26\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'10\',FundWage=\'10\',IsPayed=\'NO\' where  PartID=\'2\''),
 (1033,1000,'LON_requests',16,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:52:36','update LON_requests set RequestID=\'16\',StatusID=\'80\' where  RequestID=\'16\''),
 (1034,1000,'ACC_docs',27,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:52:36','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'1394\',\'1\',\'1\',now(),now(),\'4\',\'1000\')'),
 (1035,1000,'ACC_DocItems',39,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:52:36','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'27\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1036,1000,'ACC_DocItems',40,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:52:36','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'27\',\'74\',\'2\',\'15\',\'0\',\'1776621\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1037,1000,'ACC_DocItems',41,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 20:52:36','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'27\',\'74\',\'2\',\'16\',\'0\',\'1522818\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1038,1000,'ACC_CostCodes',75,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 21:00:52','update ACC_CostCodes c \n			left join ACC_blocks b1 on(b1.levelID=1 AND b1.blockID=c.level1)\n			left join ACC_blocks b2 on(b2.levelID=2 AND b2.blockID=c.level2)\n			left join ACC_blocks b3 on(b3.levelID=3 AND b3.blockID=c.level3)\n			set c.CostCode=concat(ifnull(b1.blockCode,\'\'),\n								ifnull(b2.blockCode,\'\'),\n								ifnull(b3.blockCode,\'\') )\n			where CostID=\'75\''),
 (1039,1000,'ACC_CostCodes',76,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 21:06:15','update ACC_CostCodes c \n			left join ACC_blocks b1 on(b1.levelID=1 AND b1.blockID=c.level1)\n			left join ACC_blocks b2 on(b2.levelID=2 AND b2.blockID=c.level2)\n			left join ACC_blocks b3 on(b3.levelID=3 AND b3.blockID=c.level3)\n			set c.CostCode=concat(ifnull(b1.blockCode,\'\'),\n								ifnull(b2.blockCode,\'\'),\n								ifnull(b3.blockCode,\'\') )\n			where CostID=\'76\''),
 (1040,1000,'LON_ReqParts',2,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 21:29:18','update LON_ReqParts set PartID=\'2\',RequestID=\'16\',PartDesc=\'مرحله اول\',PartDate=\'2015/10/26\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'10\',FundWage=\'10\',IsPayed=\'NO\' where  PartID=\'2\''),
 (1041,1000,'LON_requests',16,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 21:29:18','update LON_requests set RequestID=\'16\',StatusID=\'80\' where  RequestID=\'16\''),
 (1042,1000,'ACC_docs',28,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 21:29:18','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,regPersonID) values (\'1394\',\'1\',\'2\',now(),now(),\'4\',\'1000\')'),
 (1043,1000,'ACC_DocItems',42,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 21:29:18','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'28\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1044,1000,'ACC_DocItems',43,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 21:29:18','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'28\',\'76\',\'1\',\'13\',\'1\',\'12\',\'0\',\'57000000\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1045,1000,'ACC_DocItems',44,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 21:29:18','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'28\',\'74\',\'2\',\'15\',\'0\',\'3000000\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1046,1000,'ACC_DocItems',45,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 21:29:18','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'28\',\'74\',\'2\',\'15\',\'0\',\'1776621\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1047,1000,'ACC_DocItems',46,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 21:29:18','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'28\',\'75\',\'1\',\'12\',\'1776621\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1048,1000,'ACC_DocItems',47,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 21:29:18','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'28\',\'74\',\'2\',\'16\',\'0\',\'1522818\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1049,1000,'ACC_DocItems',48,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 21:29:18','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'28\',\'75\',\'1\',\'12\',\'1522818\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1050,1000,'ACC_CostCodes',77,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 22:06:59','update ACC_CostCodes c \n			left join ACC_blocks b1 on(b1.levelID=1 AND b1.blockID=c.level1)\n			left join ACC_blocks b2 on(b2.levelID=2 AND b2.blockID=c.level2)\n			left join ACC_blocks b3 on(b3.levelID=3 AND b3.blockID=c.level3)\n			set c.CostCode=concat(ifnull(b1.blockCode,\'\'),\n								ifnull(b2.blockCode,\'\'),\n								ifnull(b3.blockCode,\'\') )\n			where CostID=\'77\''),
 (1051,1000,'LON_ReqParts',2,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:09:59','update LON_ReqParts set PartID=\'2\',RequestID=\'16\',PartDesc=\'مرحله اول\',PartDate=\'2015/10/26\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'10\',FundWage=\'10\',IsPayed=\'NO\' where  PartID=\'2\''),
 (1052,1000,'LON_requests',16,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:09:59','update LON_requests set RequestID=\'16\',StatusID=\'80\' where  RequestID=\'16\''),
 (1053,1000,'ACC_docs',29,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:09:59','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1394\',\'1\',\'3\',now(),now(),\'4\',\'پرداخت مرحله مرحله اول وام شماره 16\',\'1000\')'),
 (1054,1000,'ACC_DocItems',49,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:09:59','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID,SourceID2) values (\'29\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'16\',\'2\')'),
 (1055,1000,'ACC_DocItems',50,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:09:59','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'29\',\'76\',\'1\',\'13\',\'1\',\'12\',\'0\',\'57000000\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1056,1000,'ACC_DocItems',51,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:09:59','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'29\',\'74\',\'2\',\'15\',\'0\',\'3000000\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1057,1000,'ACC_DocItems',52,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:09:59','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'29\',\'74\',\'2\',\'15\',\'0\',\'1776621\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1058,1000,'ACC_DocItems',53,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:09:59','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'29\',\'75\',\'1\',\'12\',\'1776621\',\'0\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1059,1000,'ACC_DocItems',54,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:09:59','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'29\',\'74\',\'2\',\'16\',\'0\',\'1522818\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1060,1000,'ACC_DocItems',55,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:09:59','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'29\',\'75\',\'1\',\'12\',\'1522818\',\'0\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1061,1000,'ACC_DocItems',56,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:09:59','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'29\',\'75\',\'1\',\'12\',\'60000000\',\'0\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1062,1000,'ACC_DocItems',57,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:09:59','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'29\',\'77\',\'1\',\'12\',\'0\',\'60000000\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1063,1000,'LON_ReqParts',2,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:12:29','update LON_ReqParts set PartID=\'2\',RequestID=\'16\',PartDesc=\'مرحله اول\',PartDate=\'2015/10/26\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'10\',FundWage=\'10\',IsPayed=\'NO\' where  PartID=\'2\''),
 (1064,1000,'LON_requests',16,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:12:29','update LON_requests set RequestID=\'16\',StatusID=\'80\' where  RequestID=\'16\''),
 (1065,1000,'ACC_docs',30,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:12:29','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1394\',\'1\',\'4\',now(),now(),\'4\',\'پرداخت مرحله مرحله اول وام شماره 16\',\'1000\')'),
 (1066,1000,'ACC_DocItems',58,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:12:29','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID,SourceID2) values (\'30\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'16\',\'2\')'),
 (1067,1000,'ACC_DocItems',59,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:12:29','insert into ACC_DocItems(DocID,CostID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'30\',\'76\',\'0\',\'57000000\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1068,1000,'ACC_DocItems',60,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:12:29','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'30\',\'74\',\'2\',\'15\',\'0\',\'3000000\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1069,1000,'ACC_DocItems',61,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:12:29','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'30\',\'74\',\'2\',\'15\',\'0\',\'1776621\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1070,1000,'ACC_DocItems',62,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:12:29','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'30\',\'75\',\'1\',\'12\',\'1776621\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1071,1000,'ACC_DocItems',63,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:12:29','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'30\',\'74\',\'2\',\'16\',\'0\',\'1522818\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1072,1000,'ACC_DocItems',64,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:12:29','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'30\',\'75\',\'1\',\'12\',\'1522818\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1073,1000,'ACC_DocItems',65,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:12:29','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'30\',\'75\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1074,1000,'ACC_DocItems',66,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:12:29','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'30\',\'77\',\'1\',\'12\',\'0\',\'60000000\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1075,1000,'ACC_tafsilis',21,NULL,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 22:24:17','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID,ObjectID) values (\'3\',\'7800005\',\'ملی 780005 شعبه پردیس\',\'1\',\'2\')'),
 (1076,1000,'ACC_accounts',2,1,'ADD',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 22:24:17','insert into DataAudit(PersonID,SystemID,PageName,IPAddress,ActionTime,TableName,MainObjectID,ActionType,QueryString) values (\'1000\',\'2\',\'http://rtfund/accounting/start.php?SystemID=2\',\'127.0.0.1\',now(),\'ACC_tafsilis\',\'21\',\'ADD\',\'insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,BranchID,ObjectID) values (\'3\',\'7800005\',\'ملی 780005 شعبه پردیس\',\'1\',\'2\')\')'),
 (1077,1000,'LON_ReqParts',2,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:26:32','update LON_ReqParts set PartID=\'2\',RequestID=\'16\',PartDesc=\'مرحله اول\',PartDate=\'2015/10/26\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'10\',FundWage=\'10\',IsPayed=\'NO\' where  PartID=\'2\''),
 (1078,1000,'LON_requests',16,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:26:32','update LON_requests set RequestID=\'16\',StatusID=\'80\' where  RequestID=\'16\''),
 (1079,1000,'ACC_docs',31,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:26:32','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1394\',\'1\',\'5\',now(),now(),\'4\',\'پرداخت مرحله مرحله اول وام شماره 16\',\'1000\')'),
 (1080,1000,'ACC_DocItems',67,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:26:32','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID,SourceID2) values (\'31\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'16\',\'2\')'),
 (1081,1000,'ACC_DocItems',68,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:26:32','insert into ACC_DocItems(DocID,CostID,TafsiliType,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'31\',\'76\',\'3\',\'0\',\'57000000\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1082,1000,'ACC_DocItems',69,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:26:32','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'31\',\'74\',\'2\',\'15\',\'0\',\'3000000\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1083,1000,'ACC_DocItems',70,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:26:32','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'31\',\'74\',\'2\',\'15\',\'0\',\'1776621\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1084,1000,'ACC_DocItems',71,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:26:32','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'31\',\'75\',\'1\',\'12\',\'1776621\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1085,1000,'ACC_DocItems',72,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:26:32','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'31\',\'74\',\'2\',\'16\',\'0\',\'1522818\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1086,1000,'ACC_DocItems',73,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:26:32','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'31\',\'75\',\'1\',\'12\',\'1522818\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1087,1000,'ACC_DocItems',74,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:26:32','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'31\',\'75\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1088,1000,'ACC_DocItems',75,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:26:32','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'31\',\'77\',\'1\',\'12\',\'0\',\'60000000\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1089,1000,'ACC_DocItems',68,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 22:42:44','update ACC_DocItems set DocID=\'31\',ItemID=\'68\',CostID=\'76\',TafsiliType=\'3\',TafsiliID=\'21\',DebtorAmount=\'0\',CreditorAmount=\'57000000\',locked=\'YES\' where ItemID=\'68\''),
 (1090,1000,'LON_ReqParts',2,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:58:25','update LON_ReqParts set PartID=\'2\',RequestID=\'16\',PartDesc=\'مرحله اول\',PartDate=\'2015/10/26\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'10\',FundWage=\'10\',IsPayed=\'YES\' where  PartID=\'2\''),
 (1091,1000,'LON_requests',16,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:58:25','update LON_requests set RequestID=\'16\',StatusID=\'80\' where  RequestID=\'16\''),
 (1092,1000,'ACC_docs',32,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:58:25','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1394\',\'1\',\'6\',now(),now(),\'4\',\'پرداخت مرحله مرحله اول وام شماره 16\',\'1000\')'),
 (1093,1000,'ACC_DocItems',76,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:58:25','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID,SourceID2) values (\'32\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'16\',\'2\')'),
 (1094,1000,'ACC_DocItems',77,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:58:25','insert into ACC_DocItems(DocID,CostID,TafsiliType,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'32\',\'76\',\'3\',\'0\',\'57000000\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1095,1000,'ACC_DocItems',78,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:58:25','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'32\',\'74\',\'2\',\'15\',\'0\',\'3000000\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1096,1000,'ACC_DocItems',79,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:58:25','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'32\',\'74\',\'2\',\'15\',\'0\',\'1776621\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1097,1000,'ACC_DocItems',80,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:58:25','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'32\',\'75\',\'1\',\'12\',\'1776621\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1098,1000,'ACC_DocItems',81,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:58:25','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'32\',\'74\',\'2\',\'16\',\'0\',\'1522818\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1099,1000,'ACC_DocItems',82,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:58:25','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'32\',\'75\',\'1\',\'12\',\'1522818\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1100,1000,'ACC_DocItems',83,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:58:25','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'32\',\'75\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1101,1000,'ACC_DocItems',84,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 22:58:25','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'32\',\'77\',\'1\',\'12\',\'0\',\'60000000\',\'YES\',\'PAY_LOAN_PART\',\'2\')'),
 (1102,1000,'ACC_DocItems',77,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 22:59:03','update ACC_DocItems set DocID=\'32\',ItemID=\'77\',CostID=\'76\',TafsiliType=\'3\',TafsiliID=\'21\',DebtorAmount=\'0\',CreditorAmount=\'57000000\',locked=\'YES\' where ItemID=\'77\''),
 (1103,1000,'LON_ReqParts',3,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:47:13','update LON_ReqParts set PartID=\'3\',RequestID=\'16\',PartDesc=\'مرحله دوم\',PartDate=\'2015/10/26\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'DAY\',PayInterval=\'60\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'4\',FundWage=\'10\',IsPayed=\'NO\' where  PartID=\'3\''),
 (1104,1000,'LON_requests',16,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:47:13','update LON_requests set RequestID=\'16\',StatusID=\'80\' where  RequestID=\'16\''),
 (1105,1000,'ACC_docs',33,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:47:13','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1394\',\'1\',\'7\',now(),now(),\'4\',\'پرداخت مرحله مرحله دوم وام شماره 16\',\'1000\')'),
 (1106,1000,'ACC_DocItems',85,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:47:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID,SourceID2) values (\'33\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'16\',\'3\')'),
 (1107,1000,'ACC_DocItems',86,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:47:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'33\',\'76\',\'3\',\'0\',\'58800000\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1108,1000,'ACC_DocItems',87,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:47:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'33\',\'74\',\'2\',\'15\',\'0\',\'1200000\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1109,1000,'ACC_DocItems',88,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:47:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'33\',\'74\',\'2\',\'15\',\'0\',\'1940012\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1110,1000,'ACC_DocItems',89,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:47:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'33\',\'75\',\'1\',\'12\',\'1940012\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1111,1000,'ACC_DocItems',90,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:47:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'33\',\'74\',\'2\',\'16\',\'0\',\'3795676\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1112,1000,'ACC_DocItems',91,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:47:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'33\',\'75\',\'1\',\'12\',\'3795676\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1113,1000,'ACC_DocItems',92,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:47:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'33\',\'74\',\'2\',\'17\',\'0\',\'843484\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1114,1000,'ACC_DocItems',93,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:47:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'33\',\'75\',\'1\',\'12\',\'843484\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1115,1000,'ACC_DocItems',94,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:47:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'33\',\'75\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1116,1000,'ACC_DocItems',95,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:47:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'33\',\'77\',\'1\',\'12\',\'0\',\'60000000\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1117,1000,'LON_ReqParts',3,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','update LON_ReqParts set PartID=\'3\',RequestID=\'16\',PartDesc=\'مرحله دوم\',PartDate=\'2015/10/26\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'DAY\',PayInterval=\'60\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'4\',FundWage=\'10\',IsPayed=\'NO\' where  PartID=\'3\''),
 (1118,1000,'LON_requests',16,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','update LON_requests set RequestID=\'16\',StatusID=\'80\' where  RequestID=\'16\''),
 (1119,1000,'ACC_docs',34,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1394\',\'1\',\'8\',now(),now(),\'4\',\'پرداخت مرحله مرحله دوم وام شماره 16\',\'1000\')'),
 (1120,1000,'ACC_DocItems',96,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID,SourceID2) values (\'34\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'16\',\'3\')'),
 (1121,1000,'ACC_DocItems',97,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'34\',\'76\',\'3\',\'0\',\'58800000\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1122,1000,'ACC_DocItems',98,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'34\',\'74\',\'2\',\'15\',\'0\',\'1200000\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1123,1000,'ACC_DocItems',99,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'34\',\'74\',\'2\',\'15\',\'0\',\'1940012\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1124,1000,'ACC_DocItems',100,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'34\',\'75\',\'1\',\'12\',\'1940012\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1125,1000,'ACC_DocItems',101,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'34\',\'74\',\'2\',\'16\',\'0\',\'3795676\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1126,1000,'ACC_DocItems',102,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'34\',\'75\',\'1\',\'12\',\'3795676\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1127,1000,'ACC_DocItems',103,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'34\',\'74\',\'2\',\'17\',\'0\',\'843484\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1128,1000,'ACC_DocItems',104,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'34\',\'75\',\'1\',\'12\',\'843484\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1129,1000,'ACC_DocItems',105,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'34\',\'75\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1130,1000,'ACC_DocItems',106,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'34\',\'77\',\'1\',\'12\',\'0\',\'60000000\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1131,1000,'ACC_DocChecks',1,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:48:13','insert into ACC_DocChecks(DocID,CheckDate,amount,TafsiliID) values (\'34\',\'2015/10/26\',\'58800000\',\'13\')'),
 (1132,1000,'ACC_DocChecks',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 23:54:01','update ACC_DocChecks set CheckID=\'1\',DocID=\'34\',CheckNo=null,CheckDate=\'2015/10/26\',amount=\'58800000\',CheckStatus=\'1\',TafsiliID=\'14\',description=null where  CheckID=\'1\''),
 (1133,1000,'ACC_DocChecks',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 23:54:07','update ACC_DocChecks set CheckID=\'1\',DocID=\'34\',CheckNo=null,CheckDate=\'2015/10/26\',amount=\'58800000\',CheckStatus=\'1\',TafsiliID=\'13\',description=null where  CheckID=\'1\''),
 (1134,1000,'ACC_DocChecks',1,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-05 23:54:22','update ACC_DocChecks set CheckID=\'1\',DocID=\'34\',CheckNo=\'1245\',AccountID=\'2\',CheckDate=\'2015/10/26\',amount=\'58800000\',CheckStatus=\'1\',TafsiliID=\'13\',description=null where  CheckID=\'1\''),
 (1135,1000,'LON_ReqParts',3,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:03','update LON_ReqParts set PartID=\'3\',RequestID=\'16\',PartDesc=\'مرحله دوم\',PartDate=\'2015/10/26\',PartAmount=\'60000000\',InstallmentCount=\'12\',IntervalType=\'DAY\',PayInterval=\'60\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'4\',FundWage=\'10\',IsPayed=\'NO\' where  PartID=\'3\''),
 (1136,1000,'LON_requests',16,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:03','update LON_requests set RequestID=\'16\',StatusID=\'80\' where  RequestID=\'16\''),
 (1137,1000,'ACC_docs',35,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:03','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1394\',\'1\',\'9\',now(),now(),\'4\',\'پرداخت مرحله مرحله دوم وام شماره 16\',\'1000\')'),
 (1138,1000,'ACC_DocItems',107,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:03','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID,SourceID2) values (\'35\',\'73\',\'1\',\'13\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'16\',\'3\')'),
 (1139,1000,'ACC_DocItems',108,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:03','insert into ACC_DocItems(DocID,CostID,TafsiliType,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'35\',\'76\',\'3\',\'0\',\'58800000\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1140,1000,'ACC_DocItems',109,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:03','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'35\',\'74\',\'2\',\'15\',\'0\',\'1200000\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1141,1000,'ACC_DocItems',110,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:03','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'35\',\'74\',\'2\',\'15\',\'0\',\'1940012\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1142,1000,'ACC_DocItems',111,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:03','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'35\',\'75\',\'1\',\'12\',\'1940012\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1143,1000,'ACC_DocItems',112,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:03','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'35\',\'74\',\'2\',\'16\',\'0\',\'3795676\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1144,1000,'ACC_DocItems',113,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:04','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'35\',\'75\',\'1\',\'12\',\'3795676\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1145,1000,'ACC_DocItems',114,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:04','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'35\',\'74\',\'2\',\'17\',\'0\',\'843484\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1146,1000,'ACC_DocItems',115,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:04','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'35\',\'75\',\'1\',\'12\',\'843484\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1147,1000,'ACC_DocItems',116,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:04','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'35\',\'75\',\'1\',\'12\',\'60000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1148,1000,'ACC_DocItems',117,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:04','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'35\',\'77\',\'1\',\'12\',\'0\',\'60000000\',\'YES\',\'PAY_LOAN_PART\',\'3\')'),
 (1149,1000,'ACC_DocChecks',2,NULL,'ADD',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-05 23:56:04','insert into ACC_DocChecks(DocID,CheckDate,amount,TafsiliID,description) values (\'35\',\'2015/10/26\',\'58800000\',\'13\',\' مرحله مرحله دوم وام شماره 16\')'),
 (1150,1000,'LON_requests',20,NULL,'UPDATE',2,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 00:06:11','update LON_requests set RequestID=\'20\',StatusID=\'30\' where  RequestID=\'20\''),
 (1151,1000,'ACC_DocItems',108,NULL,'UPDATE',2,'http://rtfund/accounting/start.php?SystemID=2',NULL,'127.0.0.1','2015-11-06 00:08:51','update ACC_DocItems set DocID=\'35\',ItemID=\'108\',CostID=\'76\',TafsiliType=\'3\',TafsiliID=\'21\',DebtorAmount=\'0\',CreditorAmount=\'58800000\',locked=\'YES\' where ItemID=\'108\''),
 (1152,1000,'LON_requests',17,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 11:39:15','update LON_requests set RequestID=\'17\',StatusID=\'30\' where  RequestID=\'17\''),
 (1153,1000,'LON_ReqParts',7,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 11:40:07','insert into LON_ReqParts(RequestID,PartDesc,PartDate,PartAmount,InstallmentCount,IntervalType,PayInterval,DelayMonths,ForfeitPercent,CustomerWage,FundWage) values (\'17\',\'مرحله اول\',\'2015/11/21\',\'120000000\',\'12\',\'MONTH\',\'1\',\'6\',\'4\',\'10\',\'4\')'),
 (1154,1001,'LON_requests',1,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 12:01:39','insert into LON_requests(RequestID,BranchID,ReqPersonID,ReqDate,ReqAmount,StatusID,BorrowerDesc,BorrowerID,guarantees,AgentGuarantee) values (\'0\',\'1\',\'1001\',now(),\'1200000000\',\'1\',\'شرکت صنعتی شرق\',\'124587415\',\'1,3,4\',\'NO\')'),
 (1155,1001,'LON_ReqParts',1,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 12:02:26','insert into LON_ReqParts(RequestID,PartDesc,PartDate,PartAmount,InstallmentCount,IntervalType,PayInterval,DelayMonths,ForfeitPercent,CustomerWage,FundWage) values (\'1\',\'مرحله اول\',\'2015/11/22\',\'600000000\',\'12\',\'MONTH\',\'1\',\'6\',\'4\',\'10\',\'4\')'),
 (1156,1001,'LON_ReqParts',2,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 12:03:08','insert into LON_ReqParts(RequestID,PartDesc,PartDate,PartAmount,InstallmentCount,IntervalType,PayInterval,DelayMonths,ForfeitPercent,CustomerWage,FundWage) values (\'1\',\'مرحله دوم\',\'2016/11/21\',\'600000000\',\'12\',\'MONTH\',\'1\',\'6\',\'4\',\'4\',\'10\')');
INSERT INTO `DataAudit` (`DataAuditID`,`PersonID`,`TableName`,`MainObjectID`,`SubObjectID`,`ActionType`,`SystemID`,`PageName`,`description`,`IPAddress`,`ActionTime`,`QueryString`) VALUES 
 (1157,1001,'LON_ReqParts',2,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 12:03:22','update LON_ReqParts set PartID=\'2\',RequestID=\'1\',PartDesc=\'مرحله دوم\',PartDate=\'2016/11/21\',PartAmount=\'600000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'4\',FundWage=\'9\' where  PartID=\'2\''),
 (1158,1001,'LON_requests',1,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 12:15:29','update LON_requests set RequestID=\'1\',BranchID=\'1\',ReqAmount=\'1200000000\',StatusID=\'10\',ReqDetails=null,BorrowerDesc=\'شرکت صنعتی شرق\',BorrowerID=\'124587415\',LoanPersonID=null,guarantees=\'1,3,4\',AgentGuarantee=\'NO\',DocumentDesc=null where  RequestID=\'1\''),
 (1159,1000,'LON_requests',1000,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 12:37:22','update LON_requests set RequestID=\'1000\',BranchID=\'1\',ReqAmount=\'1200000000\',ReqDetails=null,BorrowerDesc=\'شرکت صنعتی شرق\',BorrowerID=\'124587415\',LoanPersonID=\'1003\',guarantees=\'1,3,4\',AgentGuarantee=\'NO\',DocumentDesc=null where  RequestID=\'1000\''),
 (1160,1000,'LON_requests',1000,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 12:47:48','update LON_requests set RequestID=\'1000\',BranchID=\'1\',ReqAmount=\'1200000000\',ReqDetails=null,BorrowerDesc=\'شرکت صنعتی شرق\',BorrowerID=\'124587415\',LoanPersonID=\'1002\',guarantees=\'1,3,4\',AgentGuarantee=\'NO\',DocumentDesc=null where  RequestID=\'1000\''),
 (1161,1000,'LON_requests',1000,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 12:47:57','update LON_requests set RequestID=\'1000\',BranchID=\'1\',ReqAmount=\'1200000000\',ReqDetails=null,BorrowerDesc=\'شرکت صنعتی شرق\',BorrowerID=\'124587415\',LoanPersonID=\'1003\',guarantees=\'1,3,4\',AgentGuarantee=\'NO\',DocumentDesc=null where  RequestID=\'1000\''),
 (1162,1000,'LON_requests',1000,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 12:49:18','update LON_requests set RequestID=\'1000\',BranchID=\'1\',ReqAmount=\'1200000000\',ReqDetails=null,BorrowerDesc=\'شرکت صنعتی شرق\',BorrowerID=\'124587415\',LoanPersonID=\'1003\',guarantees=\'1,3,4\',AgentGuarantee=\'NO\',DocumentDesc=\'سه فقره چک و سه ضامن با سفته لازم می باشد.\' where  RequestID=\'1000\''),
 (1163,1000,'LON_requests',1000,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 13:00:03','update LON_requests set RequestID=\'1000\',StatusID=\'20\' where  RequestID=\'1000\''),
 (1164,1001,'LON_ReqParts',1,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 13:26:15','update LON_ReqParts set PartID=\'1\',RequestID=\'1000\',PartDesc=\'مرحله اول\',PartDate=\'2015/11/22\',PartAmount=\'600000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'10\',FundWage=\'12\' where  PartID=\'1\''),
 (1165,1001,'LON_ReqParts',1,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 13:26:27','update LON_ReqParts set PartID=\'1\',RequestID=\'1000\',PartDesc=\'مرحله اول\',PartDate=\'2015/11/22\',PartAmount=\'600000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'12\',FundWage=\'12\' where  PartID=\'1\''),
 (1166,1001,'LON_requests',1000,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 13:26:48','update LON_requests set RequestID=\'1000\',BranchID=\'1\',ReqAmount=\'1200000000\',StatusID=\'10\',ReqDetails=null,BorrowerDesc=\'شرکت صنعتی شرق\',BorrowerID=\'124587415\',LoanPersonID=\'1003\',guarantees=\'1,3,4\',AgentGuarantee=\'NO\',DocumentDesc=\'سه فقره چک و سه ضامن با سفته لازم می باشد.\' where  RequestID=\'1000\''),
 (1167,1000,'LON_requests',1000,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 13:28:59','update LON_requests set RequestID=\'1000\',StatusID=\'30\' where  RequestID=\'1000\''),
 (1168,1000,'LON_requests',1000,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 15:39:12','update LON_requests set RequestID=\'1000\',StatusID=\'40\' where  RequestID=\'1000\''),
 (1169,1003,'DMS_documents',1,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 15:50:32','insert into DMS_documents(DocDesc,DocType,DocSerial,ObjectType,ObjectID,RegPersonID) values (\'ضامن اول\',\'21\',\'12547\',\'loan\',\'1000\',\'1003\')'),
 (1170,1003,'DMS_documents',2,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 15:51:02','insert into DMS_documents(DocType,DocSerial,ObjectType,ObjectID,RegPersonID) values (\'24\',\'0943021723\',\'loan\',\'1000\',\'1003\')'),
 (1171,1003,'DMS_documents',3,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:04:12','insert into DMS_documents(DocType,ObjectType,ObjectID,RegPersonID) values (\'1\',\'loan\',\'1000\',\'1003\')'),
 (1172,1003,'DMS_documents',3,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:08:03','update DMS_documents set DocumentID=\'3\',DocDesc=null,DocType=\'2\',DocSerial=null,ObjectType=\'loan\',ObjectID=\'1000\',FileType=\'jpg\',IsConfirm=\'NOTSET\',RegPersonID=\'1003\' where  DocumentID=\'3\''),
 (1173,1003,'DMS_documents',3,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:08:17','update DMS_documents set DocumentID=\'3\',DocDesc=null,DocType=\'2\',DocSerial=null,ObjectType=\'loan\',ObjectID=\'1000\',FileType=\'jpg\',IsConfirm=\'NOTSET\',RegPersonID=\'1003\' where  DocumentID=\'3\''),
 (1174,1003,'DMS_documents',4,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:09:55','insert into DMS_documents(DocType,ObjectType,ObjectID,RegPersonID) values (\'3\',\'loan\',\'1000\',\'1003\')'),
 (1175,1003,'DMS_documents',5,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:12:07','insert into DMS_documents(DocType,ObjectType,ObjectID,RegPersonID) values (\'3\',\'loan\',\'1000\',\'1003\')'),
 (1176,1003,'DMS_documents',4,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:12:16','update DMS_documents set DocumentID=\'4\',DocDesc=null,DocType=\'3\',DocSerial=\'12345\',ObjectType=\'loan\',ObjectID=\'1000\',FileType=\'jpg\',IsConfirm=\'NOTSET\',RegPersonID=\'1003\' where  DocumentID=\'4\''),
 (1177,1003,'DMS_documents',5,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:12:28','update DMS_documents set DocumentID=\'5\',DocDesc=null,DocType=\'3\',DocSerial=\'12346\',ObjectType=\'loan\',ObjectID=\'1000\',FileType=\'jpg\',IsConfirm=\'NOTSET\',RegPersonID=\'1003\' where  DocumentID=\'5\''),
 (1178,1003,'LON_requests',1000,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:13:19','update LON_requests set RequestID=\'1000\',StatusID=\'50\' where  RequestID=\'1000\''),
 (1179,1000,'DMS_documents',1,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 16:25:56','update DMS_documents set DocumentID=\'1\',IsConfirm=\'YES\',ConfirmPersonID=\'1000\',RejectDesc=null where  DocumentID=\'1\''),
 (1180,1000,'DMS_documents',2,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 16:26:03','update DMS_documents set DocumentID=\'2\',IsConfirm=\'NO\',ConfirmPersonID=\'1000\',RejectDesc=\'dfgdfgd\' where  DocumentID=\'2\''),
 (1181,1000,'DMS_documents',3,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 16:26:13','update DMS_documents set DocumentID=\'3\',IsConfirm=\'YES\',ConfirmPersonID=\'1000\',RejectDesc=null where  DocumentID=\'3\''),
 (1182,1000,'DMS_documents',4,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 16:26:16','update DMS_documents set DocumentID=\'4\',IsConfirm=\'YES\',ConfirmPersonID=\'1000\',RejectDesc=null where  DocumentID=\'4\''),
 (1183,1000,'DMS_documents',5,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 16:26:20','update DMS_documents set DocumentID=\'5\',IsConfirm=\'YES\',ConfirmPersonID=\'1000\',RejectDesc=null where  DocumentID=\'5\''),
 (1184,1000,'LON_requests',1000,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 16:26:58','update LON_requests set RequestID=\'1000\',StatusID=\'60\' where  RequestID=\'1000\''),
 (1185,1003,'DMS_documents',2,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:35:05','update DMS_documents set DocumentID=\'2\',DocDesc=\'-\',DocType=\'24\',DocSerial=\'0943021723\',ObjectType=\'loan\',ObjectID=\'1000\',FileType=\'jpg\',IsConfirm=\'NOTSET\',RegPersonID=\'1003\',RejectDesc=\'dfgdfgd\' where  DocumentID=\'2\''),
 (1186,1003,'DMS_documents',6,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:36:40','insert into DMS_documents(DocType,ObjectType,ObjectID,RegPersonID) values (\'41\',\'person\',\'1003\',\'1003\')'),
 (1187,1003,'DMS_documents',7,NULL,'ADD',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:36:55','insert into DMS_documents(DocType,ObjectType,ObjectID,RegPersonID) values (\'42\',\'person\',\'1003\',\'1003\')'),
 (1188,1003,'DMS_documents',7,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:37:03','update DMS_documents set DocumentID=\'7\',DocDesc=null,DocType=\'42\',DocSerial=null,ObjectType=\'person\',ObjectID=\'1003\',IsConfirm=\'NOTSET\',RegPersonID=\'1003\' where  DocumentID=\'7\''),
 (1189,1003,'DMS_documents',6,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:37:55','update DMS_documents set DocumentID=\'6\',DocDesc=null,DocType=\'41\',DocSerial=null,ObjectType=\'person\',ObjectID=\'1003\',IsConfirm=\'NOTSET\',RegPersonID=\'1003\' where  DocumentID=\'6\''),
 (1190,1003,'DMS_documents',6,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:38:37','update DMS_documents set DocumentID=\'6\',DocDesc=null,DocType=\'41\',DocSerial=null,ObjectType=\'person\',ObjectID=\'1003\',IsConfirm=\'NOTSET\',RegPersonID=\'1003\' where  DocumentID=\'6\''),
 (1191,1003,'DMS_documents',7,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:38:47','update DMS_documents set DocumentID=\'7\',DocDesc=null,DocType=\'42\',DocSerial=null,ObjectType=\'person\',ObjectID=\'1003\',IsConfirm=\'NOTSET\',RegPersonID=\'1003\' where  DocumentID=\'7\''),
 (1192,1003,'LON_requests',1000,NULL,'UPDATE',1000,'http://rtfund/portal/index.php',NULL,'127.0.0.1','2015-11-06 16:39:10','update LON_requests set RequestID=\'1000\',StatusID=\'50\' where  RequestID=\'1000\''),
 (1193,1000,'DMS_documents',2,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 16:40:04','update DMS_documents set DocumentID=\'2\',IsConfirm=\'YES\',ConfirmPersonID=\'1000\',RejectDesc=null where  DocumentID=\'2\''),
 (1194,1000,'DMS_documents',6,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 16:41:17','update DMS_documents set DocumentID=\'6\',IsConfirm=\'YES\',ConfirmPersonID=\'1000\',RejectDesc=null where  DocumentID=\'6\''),
 (1195,1000,'DMS_documents',7,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 16:41:20','update DMS_documents set DocumentID=\'7\',IsConfirm=\'YES\',ConfirmPersonID=\'1000\',RejectDesc=null where  DocumentID=\'7\''),
 (1196,1000,'LON_requests',1000,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 16:41:35','update LON_requests set RequestID=\'1000\',StatusID=\'70\' where  RequestID=\'1000\''),
 (1197,1000,'WFM_FlowRows',1,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-06 18:16:36','insert into WFM_FlowRows(FlowID,StepID,ObjectID,PersonID,ActionDate,ActionType) values (\'1\',\'0\',\'1\',\'1000\',now(),\'CONFIRM\')'),
 (1198,1000,'FRW_menus',52,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 18:20:19','insert into FRW_menus(SystemID,ParentID,MenuDesc,ordering) values (\'4\',\'0\',\'کارتابل شخصی\',\'2\')'),
 (1199,1000,'FRW_menus',53,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 18:21:02','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'4\',\'52\',\'کارتابل فرم های رسیده\',\'YES\',\'1\',\'workflow/MyForms.php\')'),
 (1200,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 18:21:15','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'31\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (1201,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 18:21:15','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'30\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (1202,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 18:21:15','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'32\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (1203,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 18:21:15','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'53\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (1204,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 18:21:15','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'38\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (1205,1000,'WFM_FlowRows',2,NULL,'ADD',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-11-06 21:37:21','insert into WFM_FlowRows(FlowID,StepID,ObjectID,PersonID,ActionDate,ActionType,ActionComment) values (\'1\',\'1\',\'1\',\'1000\',now(),\'CONFIRM\',\'-----------\')'),
 (1206,1000,'BSC_persons',1004,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 21:41:49','insert into ACC_tafsilis(TafsiliType,TafsiliCode,ObjectID) values (\'1\',\'1004\',\'1004\')'),
 (1207,1000,'BSC_persons',1005,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 21:42:46','insert into ACC_tafsilis(TafsiliType,TafsiliCode,ObjectID) values (\'1\',\'1005\',\'1005\')'),
 (1208,1000,'BSC_persons',1006,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 21:43:32','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,ObjectID) values (\'1\',\'1006\',\'-- سیدیان\',\'1006\')'),
 (1209,1000,'ACC_tafsilis',22,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 21:44:24','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,ObjectID) values (\'1\',\'1007\',\'-- سیدیان\',\'1007\')'),
 (1210,1000,'BSC_persons',1007,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 21:44:24','insert into DataAudit(PersonID,SystemID,PageName,IPAddress,ActionTime,TableName,MainObjectID,ActionType,QueryString) values (\'1000\',\'1\',\'http://rtfund/framework/start.php?SystemID=1\',\'127.0.0.1\',now(),\'ACC_tafsilis\',\'22\',\'ADD\',\'insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,ObjectID) values (\'1\',\'1007\',\'-- سیدیان\',\'1007\')\')'),
 (1211,1000,'BSC_persons',1007,NULL,'DELETE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 21:44:33','delete from BSC_persons where  PersonID=\'1007\''),
 (1212,1000,'BSC_persons',1006,NULL,'DELETE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 21:44:35','delete from BSC_persons where  PersonID=\'1006\''),
 (1213,1000,'BSC_persons',1005,NULL,'DELETE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 21:44:36','delete from BSC_persons where  PersonID=\'1005\''),
 (1214,1000,'BSC_persons',1004,NULL,'DELETE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 21:44:39','delete from BSC_persons where  PersonID=\'1004\''),
 (1215,1000,'ACC_tafsilis',23,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 22:59:57','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,ObjectID) values (\'1\',\'1008\',\'-- سیدیان\',\'1008\')'),
 (1216,1000,'BSC_persons',1008,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 22:59:57','insert into DataAudit(PersonID,SystemID,PageName,IPAddress,ActionTime,TableName,MainObjectID,ActionType,QueryString) values (\'1000\',\'1\',\'http://rtfund/framework/start.php?SystemID=1\',\'127.0.0.1\',now(),\'ACC_tafsilis\',\'23\',\'ADD\',\'insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,ObjectID) values (\'1\',\'1008\',\'-- سیدیان\',\'1008\')\')'),
 (1217,1000,'FRW_access',0,1008,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 23:00:30','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'53\',\'1008\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (1218,1000,'BSC_persons',1008,NULL,'DELETE',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 23:02:35','delete from BSC_persons where  PersonID=\'1008\''),
 (1219,1000,'ACC_tafsilis',24,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 23:02:52','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,ObjectID) values (\'1\',\'1009\',\'-- سیدیان\',\'1009\')'),
 (1220,1000,'BSC_persons',1009,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 23:02:52','insert into DataAudit(PersonID,SystemID,PageName,IPAddress,ActionTime,TableName,MainObjectID,ActionType,QueryString) values (\'1000\',\'1\',\'http://rtfund/framework/start.php?SystemID=1\',\'127.0.0.1\',now(),\'ACC_tafsilis\',\'24\',\'ADD\',\'insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,ObjectID) values (\'1\',\'1009\',\'-- سیدیان\',\'1009\')\')'),
 (1221,1000,'FRW_access',0,1009,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-11-06 23:03:06','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'53\',\'1009\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (1222,1009,'WFM_FlowRows',3,NULL,'ADD',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-11-06 23:06:17','insert into WFM_FlowRows(FlowID,StepID,ObjectID,PersonID,ActionDate,ActionType,ActionComment) values (\'1\',\'2\',\'1\',\'1009\',now(),\'REJECT\',\'چونکه ......\')'),
 (1223,1000,'WFM_FlowRows',4,NULL,'ADD',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-11-06 23:09:50','insert into WFM_FlowRows(FlowID,StepID,ObjectID,PersonID,ActionDate,ActionType) values (\'1\',\'1\',\'1\',\'1000\',now(),\'CONFIRM\')'),
 (1224,1009,'WFM_FlowRows',5,NULL,'ADD',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-11-06 23:10:13','insert into WFM_FlowRows(FlowID,StepID,ObjectID,PersonID,ActionDate,ActionType) values (\'1\',\'2\',\'1\',\'1009\',now(),\'CONFIRM\')'),
 (1225,1000,'LON_installments',1,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 09:48:05','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount,WageAmount,CustomerWage,FundWage) values (\'1\',\'2015/12/22\',\'50000000\',\'3310000\',\'12\',\'12\')'),
 (1226,1000,'LON_installments',2,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 09:48:05','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount,WageAmount,CustomerWage,FundWage) values (\'1\',\'2016/01/21\',\'50000000\',\'3310000\',\'12\',\'12\')'),
 (1227,1000,'LON_installments',3,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 09:48:05','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount,WageAmount,CustomerWage,FundWage) values (\'1\',\'2016/02/20\',\'50000000\',\'3310000\',\'12\',\'12\')'),
 (1228,1000,'LON_installments',4,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 09:48:05','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount,WageAmount,CustomerWage,FundWage) values (\'1\',\'2016/03/20\',\'50000000\',\'3310000\',\'12\',\'12\')'),
 (1229,1000,'LON_installments',5,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 09:48:05','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount,WageAmount,CustomerWage,FundWage) values (\'1\',\'2016/04/20\',\'50000000\',\'3310000\',\'12\',\'12\')'),
 (1230,1000,'LON_installments',6,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 09:48:05','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount,WageAmount,CustomerWage,FundWage) values (\'1\',\'2016/05/21\',\'50000000\',\'3310000\',\'12\',\'12\')'),
 (1231,1000,'LON_installments',7,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 09:48:05','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount,WageAmount,CustomerWage,FundWage) values (\'1\',\'2016/06/21\',\'50000000\',\'3310000\',\'12\',\'12\')'),
 (1232,1000,'LON_installments',8,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 09:48:05','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount,WageAmount,CustomerWage,FundWage) values (\'1\',\'2016/07/22\',\'50000000\',\'3310000\',\'12\',\'12\')'),
 (1233,1000,'LON_installments',9,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 09:48:05','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount,WageAmount,CustomerWage,FundWage) values (\'1\',\'2016/08/22\',\'50000000\',\'3310000\',\'12\',\'12\')'),
 (1234,1000,'LON_installments',10,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 09:48:05','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount,WageAmount,CustomerWage,FundWage) values (\'1\',\'2016/09/22\',\'50000000\',\'3310000\',\'12\',\'12\')'),
 (1235,1000,'LON_installments',11,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 09:48:05','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount,WageAmount,CustomerWage,FundWage) values (\'1\',\'2016/10/22\',\'50000000\',\'3310000\',\'12\',\'12\')'),
 (1236,1000,'LON_installments',12,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 09:48:05','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount,WageAmount,CustomerWage,FundWage) values (\'1\',\'2016/11/21\',\'50000000\',\'3301278\',\'12\',\'12\')'),
 (1237,1000,'LON_installments',13,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:30','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2015/12/22\',\'53310000\')'),
 (1238,1000,'LON_installments',14,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:30','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/01/21\',\'53310000\')'),
 (1239,1000,'LON_installments',15,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:30','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/02/20\',\'53310000\')'),
 (1240,1000,'LON_installments',16,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:30','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/03/20\',\'53310000\')'),
 (1241,1000,'LON_installments',17,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:30','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/04/20\',\'53310000\')'),
 (1242,1000,'LON_installments',18,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:30','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/05/21\',\'53310000\')'),
 (1243,1000,'LON_installments',19,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:30','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/06/21\',\'53310000\')'),
 (1244,1000,'LON_installments',20,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:30','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/07/22\',\'53310000\')'),
 (1245,1000,'LON_installments',21,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:30','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/08/22\',\'53310000\')'),
 (1246,1000,'LON_installments',22,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:30','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/09/22\',\'53310000\')'),
 (1247,1000,'LON_installments',23,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:30','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/10/22\',\'53310000\')'),
 (1248,1000,'LON_installments',24,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:30','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/11/21\',\'53301278\')'),
 (1249,1000,'LON_installments',25,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:51','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2015/12/22\',\'53310000\')'),
 (1250,1000,'LON_installments',26,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:51','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/01/21\',\'53310000\')'),
 (1251,1000,'LON_installments',27,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:51','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/02/20\',\'53310000\')'),
 (1252,1000,'LON_installments',28,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:51','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/03/20\',\'53310000\')'),
 (1253,1000,'LON_installments',29,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:51','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/04/20\',\'53310000\')'),
 (1254,1000,'LON_installments',30,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:51','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/05/21\',\'53310000\')'),
 (1255,1000,'LON_installments',31,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:51','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/06/21\',\'53310000\')'),
 (1256,1000,'LON_installments',32,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:51','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/07/22\',\'53310000\')'),
 (1257,1000,'LON_installments',33,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:51','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/08/22\',\'53310000\')'),
 (1258,1000,'LON_installments',34,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:51','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/09/22\',\'53310000\')'),
 (1259,1000,'LON_installments',35,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:51','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/10/22\',\'53310000\')'),
 (1260,1000,'LON_installments',36,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:05:51','insert into LON_installments(PartID,InstallmentDate,InstallmentAmount) values (\'1\',\'2016/11/21\',\'53301278\')'),
 (1261,1000,'LON_ReqParts',1,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:17:31','update LON_ReqParts set PartID=\'1\',RequestID=\'1000\',PartDesc=\'مرحله اول\',PartDate=\'2015/11/22\',PartAmount=\'600000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'12\',FundWage=\'12\',IsPayed=\'YES\' where  PartID=\'1\''),
 (1262,1000,'LON_requests',1000,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:17:31','update LON_requests set RequestID=\'1000\',StatusID=\'80\' where  RequestID=\'1000\''),
 (1263,1000,'ACC_docs',36,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:17:31','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1394\',\'1\',\'10\',now(),now(),\'4\',\'پرداخت مرحله مرحله اول وام شماره 1000\',\'1000\')'),
 (1264,1000,'ACC_DocItems',118,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:17:31','insert into ACC_DocItems(DocID,CostID,TafsiliType,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID,SourceID2) values (\'36\',\'73\',\'1\',\'1\',\'12\',\'600000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'1000\',\'1\')'),
 (1265,1000,'ACC_DocItems',119,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:17:31','insert into ACC_DocItems(DocID,CostID,TafsiliType,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'36\',\'76\',\'3\',\'0\',\'564000000\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1266,1000,'ACC_DocItems',120,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:17:31','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'36\',\'74\',\'2\',\'15\',\'0\',\'36000000\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1267,1000,'ACC_DocItems',121,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:17:31','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'36\',\'74\',\'2\',\'15\',\'0\',\'16800925\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1268,1000,'ACC_DocItems',122,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:17:31','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'36\',\'75\',\'1\',\'12\',\'16800925\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1269,1000,'ACC_DocItems',123,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:17:31','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'36\',\'74\',\'2\',\'16\',\'0\',\'22910353\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1270,1000,'ACC_DocItems',124,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:17:31','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'36\',\'75\',\'1\',\'12\',\'22910353\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1271,1000,'ACC_DocItems',125,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:17:31','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'36\',\'75\',\'1\',\'12\',\'600000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1272,1000,'ACC_DocItems',126,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:17:31','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'36\',\'77\',\'1\',\'12\',\'0\',\'600000000\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1273,1000,'ACC_DocChecks',3,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:17:31','insert into ACC_DocChecks(DocID,CheckDate,amount,description) values (\'36\',\'2015/11/22\',\'564000000\',\' پرداخت مرحله اول وام شماره 1000\')'),
 (1274,1000,'LON_ReqParts',1,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:43:48','update LON_ReqParts set PartID=\'1\',RequestID=\'1000\',PartDesc=\'مرحله اول\',PartDate=\'2015/11/22\',PartAmount=\'600000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'12\',FundWage=\'12\',IsPayed=\'YES\' where  PartID=\'1\''),
 (1275,1000,'LON_requests',1000,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:43:48','update LON_requests set RequestID=\'1000\',StatusID=\'80\' where  RequestID=\'1000\''),
 (1276,1000,'ACC_docs',37,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 11:43:48','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1394\',\'1\',\'11\',now(),now(),\'4\',\'پرداخت مرحله مرحله اول وام شماره 1000\',\'1000\')'),
 (1277,1000,'ACC_tafsilis',25,NULL,'ADD',2,'http://rtfund/portal/login.php',NULL,'127.0.0.1','2015-11-07 12:18:28','insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,ObjectID) values (\'1\',\'1010\',\'qeqwe\',\'1010\')'),
 (1278,1000,'BSC_persons',1010,NULL,'ADD',2,'http://rtfund/portal/login.php',NULL,'127.0.0.1','2015-11-07 12:18:28','insert into DataAudit(PersonID,SystemID,PageName,IPAddress,ActionTime,TableName,MainObjectID,ActionType,QueryString) values (\'1000\',\'2\',\'http://rtfund/portal/login.php\',\'127.0.0.1\',now(),\'ACC_tafsilis\',\'25\',\'ADD\',\'insert into ACC_tafsilis(TafsiliType,TafsiliCode,TafsiliDesc,ObjectID) values (\'1\',\'1010\',\'qeqwe\',\'1010\')\')'),
 (1279,1000,'LON_ReqParts',1,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 12:30:57','update LON_ReqParts set PartID=\'1\',RequestID=\'1000\',PartDesc=\'مرحله اول\',PartDate=\'2015/11/22\',PartAmount=\'600000000\',InstallmentCount=\'12\',IntervalType=\'MONTH\',PayInterval=\'1\',DelayMonths=\'6\',ForfeitPercent=\'4\',CustomerWage=\'12\',FundWage=\'12\',IsPayed=\'YES\' where  PartID=\'1\''),
 (1280,1000,'LON_requests',1000,NULL,'UPDATE',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 12:30:57','update LON_requests set RequestID=\'1000\',StatusID=\'80\' where  RequestID=\'1000\''),
 (1281,1000,'ACC_docs',38,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 12:30:57','insert into ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,DocType,description,regPersonID) values (\'1394\',\'1\',\'11\',now(),now(),\'4\',\'پرداخت مرحله مرحله اول وام شماره 1000\',\'1000\')'),
 (1282,1000,'ACC_DocItems',127,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 12:30:57','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID,SourceID2) values (\'38\',\'73\',\'1\',\'25\',\'1\',\'12\',\'600000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'1000\',\'1\')'),
 (1283,1000,'ACC_DocItems',128,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 12:30:57','insert into ACC_DocItems(DocID,CostID,TafsiliType,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'38\',\'76\',\'3\',\'0\',\'564000000\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1284,1000,'ACC_DocItems',129,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 12:30:57','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked,SourceType,SourceID) values (\'38\',\'74\',\'2\',\'15\',\'0\',\'36000000\',\'کارمزد دوره تنفس\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1285,1000,'ACC_DocItems',130,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 12:30:57','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'38\',\'74\',\'2\',\'15\',\'0\',\'16800925\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1286,1000,'ACC_DocItems',131,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 12:30:57','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'38\',\'75\',\'1\',\'12\',\'16800925\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1287,1000,'ACC_DocItems',132,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 12:30:57','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'38\',\'74\',\'2\',\'16\',\'0\',\'22910353\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1288,1000,'ACC_DocItems',133,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 12:30:57','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'38\',\'75\',\'1\',\'12\',\'22910353\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1289,1000,'ACC_DocItems',134,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 12:30:57','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'38\',\'75\',\'1\',\'12\',\'600000000\',\'0\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1290,1000,'ACC_DocItems',135,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 12:30:57','insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,locked,SourceType,SourceID) values (\'38\',\'77\',\'1\',\'12\',\'0\',\'600000000\',\'YES\',\'PAY_LOAN_PART\',\'1\')'),
 (1291,1000,'ACC_DocChecks',4,NULL,'ADD',6,'http://rtfund/loan/start.php?SystemID=6',NULL,'127.0.0.1','2015-11-07 12:30:57','insert into ACC_DocChecks(DocID,CheckDate,amount,TafsiliID,description) values (\'38\',\'2015/11/22\',\'564000000\',\'25\',\' پرداخت مرحله اول وام شماره 1000\')'),
 (1292,1003,'LON_installments',25,NULL,'UPDATE',1000,'D:/webserver/rtfund/portal/epayment/epayment_step2.php',NULL,'127.0.0.1','2015-11-08 11:33:12','update LON_installments set InstallmentID=\'25\',PartID=\'1\',InstallmentDate=\'2015/12/22\',InstallmentAmount=\'53310000\',PaidDate=now(),PaidAmount=\'1000\',PaidRefNo=\'000079454618\',StatusID=\'100\' where  InstallmentID=\'25\'');
/*!40000 ALTER TABLE `DataAudit` ENABLE KEYS */;


--
-- Definition of table `FGR_FormElements`
--

DROP TABLE IF EXISTS `FGR_FormElements`;
CREATE TABLE `FGR_FormElements` (
  `ElementID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `FormID` int(10) unsigned NOT NULL,
  `ElTitle` varchar(45) CHARACTER SET utf8 NOT NULL,
  `ElType` varchar(45) NOT NULL,
  `ElValue` varchar(45) DEFAULT NULL,
  `RefField` varchar(45) DEFAULT NULL,
  `TypeID` int(10) unsigned DEFAULT NULL,
  `ordering` varchar(45) NOT NULL,
  `width` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`ElementID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `FGR_FormElements`
--

/*!40000 ALTER TABLE `FGR_FormElements` DISABLE KEYS */;
INSERT INTO `FGR_FormElements` (`ElementID`,`FormID`,`ElTitle`,`ElType`,`ElValue`,`RefField`,`TypeID`,`ordering`,`width`) VALUES 
 (1,1,'مدت مرخصی','combo','1:2:3',NULL,0,'1','50'),
 (2,1,'سمتنبی سمین','textfield',NULL,NULL,0,'2',NULL),
 (3,1,'شسیشس','currencyfield',NULL,NULL,0,'3',NULL);
/*!40000 ALTER TABLE `FGR_FormElements` ENABLE KEYS */;


--
-- Definition of table `FGR_StepElements`
--

DROP TABLE IF EXISTS `FGR_StepElements`;
CREATE TABLE `FGR_StepElements` (
  `StepID` int(10) unsigned NOT NULL,
  `ElementID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`StepID`,`ElementID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `FGR_StepElements`
--

/*!40000 ALTER TABLE `FGR_StepElements` DISABLE KEYS */;
/*!40000 ALTER TABLE `FGR_StepElements` ENABLE KEYS */;


--
-- Definition of table `FGR_forms`
--

DROP TABLE IF EXISTS `FGR_forms`;
CREATE TABLE `FGR_forms` (
  `FormID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد فرم',
  `FormName` varchar(500) NOT NULL COMMENT 'عنوان فرم',
  `reference` varchar(45) DEFAULT NULL COMMENT 'آیتم',
  `FileInclude` enum('YES','NO') DEFAULT 'NO',
  PRIMARY KEY (`FormID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `FGR_forms`
--

/*!40000 ALTER TABLE `FGR_forms` DISABLE KEYS */;
INSERT INTO `FGR_forms` (`FormID`,`FormName`,`reference`,`FileInclude`) VALUES 
 (1,'فرم تستی',NULL,'NO');
/*!40000 ALTER TABLE `FGR_forms` ENABLE KEYS */;


--
-- Definition of table `FGR_steps`
--

DROP TABLE IF EXISTS `FGR_steps`;
CREATE TABLE `FGR_steps` (
  `StepID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد مرحله',
  `FormID` int(10) unsigned NOT NULL COMMENT 'کد فرم',
  `ordering` smallint(5) unsigned NOT NULL COMMENT 'ترتیب',
  `StepTitle` varchar(200) NOT NULL COMMENT 'عنوان مرحله',
  `PostID` int(10) unsigned NOT NULL COMMENT 'پست',
  `BreakDuration` smallint(5) unsigned NOT NULL COMMENT 'مهلت به روز',
  PRIMARY KEY (`StepID`),
  KEY `FK_FGR_steps_1` (`FormID`),
  CONSTRAINT `FK_FGR_steps_1` FOREIGN KEY (`FormID`) REFERENCES `fgr_forms` (`FormID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `FGR_steps`
--

/*!40000 ALTER TABLE `FGR_steps` DISABLE KEYS */;
INSERT INTO `FGR_steps` (`StepID`,`FormID`,`ordering`,`StepTitle`,`PostID`,`BreakDuration`) VALUES 
 (2,1,1,'sdsds',2,2),
 (3,1,2,'dsfsdfsd',1,3),
 (4,1,3,'fffffff',1,3);
/*!40000 ALTER TABLE `FGR_steps` ENABLE KEYS */;


--
-- Definition of table `FRW_access`
--

DROP TABLE IF EXISTS `FRW_access`;
CREATE TABLE `FRW_access` (
  `MenuID` int(11) NOT NULL,
  `PersonID` int(11) NOT NULL,
  `ViewFlag` enum('YES','NO') DEFAULT 'NO',
  `AddFlag` enum('YES','NO') DEFAULT 'NO',
  `EditFlag` enum('YES','NO') DEFAULT 'NO',
  `RemoveFlag` enum('YES','NO') DEFAULT 'NO',
  PRIMARY KEY (`MenuID`,`PersonID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `FRW_access`
--

/*!40000 ALTER TABLE `FRW_access` DISABLE KEYS */;
INSERT INTO `FRW_access` (`MenuID`,`PersonID`,`ViewFlag`,`AddFlag`,`EditFlag`,`RemoveFlag`) VALUES 
 (3,1000,'YES','YES','YES','YES'),
 (4,1000,'YES','YES','YES','YES'),
 (5,1000,'YES','YES','YES','YES'),
 (6,1000,'YES','YES','YES','YES'),
 (8,1000,'YES','YES','YES','YES'),
 (10,1000,'YES','YES','YES','YES'),
 (11,1000,'YES','YES','YES','YES'),
 (12,1000,'YES','YES','YES','YES'),
 (14,1000,'YES','YES','YES','YES'),
 (16,1000,'YES','YES','YES','YES'),
 (18,1000,'YES','YES','YES','YES'),
 (22,1000,'YES','YES','YES','YES'),
 (23,1000,'YES','YES','YES','YES'),
 (25,1000,'YES','YES','YES','YES'),
 (26,1000,'YES','YES','YES','YES'),
 (27,1000,'YES','YES','YES','YES'),
 (28,1000,'YES','YES','YES','YES'),
 (30,1000,'YES','YES','YES','YES'),
 (31,1000,'YES','YES','YES','YES'),
 (32,1000,'YES','YES','YES','YES'),
 (33,1000,'YES','YES','YES','YES'),
 (34,1000,'YES','YES','YES','YES'),
 (35,1000,'YES','YES','YES','YES'),
 (36,1000,'YES','YES','YES','YES'),
 (38,1000,'YES','YES','YES','YES'),
 (45,1000,'YES','YES','YES','YES'),
 (49,1000,'YES','YES','YES','YES'),
 (50,1000,'YES','YES','YES','YES'),
 (53,1000,'YES','YES','YES','YES'),
 (53,1008,'YES','YES','YES','YES'),
 (53,1009,'YES','YES','YES','YES');
/*!40000 ALTER TABLE `FRW_access` ENABLE KEYS */;


--
-- Definition of table `FRW_menus`
--

DROP TABLE IF EXISTS `FRW_menus`;
CREATE TABLE `FRW_menus` (
  `SystemID` int(10) unsigned NOT NULL,
  `MenuID` int(11) NOT NULL AUTO_INCREMENT,
  `ParentID` int(10) unsigned DEFAULT NULL,
  `MenuDesc` varchar(500) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `ordering` smallint(5) unsigned DEFAULT NULL,
  `icon` varchar(200) DEFAULT NULL,
  `MenuPath` varchar(500) DEFAULT NULL,
  `IsCustomer` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `IsShareholder` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `IsStaff` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `IsAgent` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `IsSupporter` enum('YES','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`MenuID`),
  KEY `FK_FRW_menus_1` (`SystemID`),
  CONSTRAINT `FK_FRW_menus_1` FOREIGN KEY (`SystemID`) REFERENCES `frw_systems` (`SystemID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `FRW_menus`
--

/*!40000 ALTER TABLE `FRW_menus` DISABLE KEYS */;
INSERT INTO `FRW_menus` (`SystemID`,`MenuID`,`ParentID`,`MenuDesc`,`IsActive`,`ordering`,`icon`,`MenuPath`,`IsCustomer`,`IsShareholder`,`IsStaff`,`IsAgent`,`IsSupporter`) VALUES 
 (1,1,0,'مدیریت  سیستم ها','YES',1,NULL,NULL,'NO','NO','NO','NO','NO'),
 (1,2,0,'مدیریت کاربران','YES',2,NULL,NULL,'NO','NO','NO','NO','NO'),
 (1,3,1,'مدیریت سیستم ها','YES',1,NULL,'management/systems.php','NO','NO','NO','NO','NO'),
 (1,4,1,'مدیریت منوها','YES',2,NULL,'management/menus.php','NO','NO','NO','NO','NO'),
 (1,5,2,'دسترسی کاربران','YES',2,'access.gif','management/UserAccess.php','NO','NO','NO','NO','NO'),
 (1,6,2,'کاربران','YES',1,'users.gif','management/users.php','NO','NO','NO','NO','NO'),
 (2,7,0,'اطلاعات پایه','YES',1,NULL,NULL,'NO','NO','NO','NO','NO'),
 (2,8,7,'مدیریت کد حساب','YES',3,NULL,'baseinfo/CostCodes.php','NO','NO','NO','NO','NO'),
 (1,9,0,'اطلاعات پایه','YES',3,NULL,NULL,'NO','NO','NO','NO','NO'),
 (1,10,9,'واحدهای سازمان','YES',1,'unit.png','baeinfo/units.php','NO','NO','NO','NO','NO'),
 (1,11,9,'مدیریت شعب','YES',2,NULL,'baseinfo/branches.php','NO','NO','NO','NO','NO'),
 (1,12,9,'دسترسی شعب','YES',3,NULL,'baseInfo/BranchAccess.php','NO','NO','NO','NO','NO'),
 (6,13,0,'اطلاعات پایه','YES',1,NULL,NULL,'NO','NO','NO','NO','NO'),
 (6,14,13,'انواع وام','YES',1,NULL,'loan/loans.php','NO','NO','NO','NO','NO'),
 (6,15,0,'اعطای تسهیلات','YES',2,NULL,NULL,'NO','NO','NO','NO','NO'),
 (6,16,15,'مدیریت درخواست ها','YES',1,NULL,'request/ManageRequests.php','NO','NO','NO','NO','NO'),
 (6,17,0,'گزارشات','YES',3,NULL,NULL,'NO','NO','NO','NO','NO'),
 (6,18,17,'گزارش درخواست های تسهیلات','YES',1,NULL,'report/requests.php','NO','NO','NO','NO','NO'),
 (2,19,0,'گزارشات','YES',3,NULL,NULL,'NO','NO','NO','NO','NO'),
 (2,21,0,'عملیات برگه','YES',2,NULL,NULL,'NO','NO','NO','NO','NO'),
 (2,22,21,'مدیریت برگه ها','YES',1,NULL,'docs/docs.php','NO','NO','NO','NO','NO'),
 (2,23,21,'سند افتتاحیه / اختتامیه','YES',2,NULL,'docs/CloseOpenDocs.php','NO','NO','NO','NO','NO'),
 (2,25,7,'مدیریت تفصیلی ها','YES',4,NULL,'baseinfo/tafsilis.php','NO','NO','NO','NO','NO'),
 (2,26,19,'گزارش تراز','YES',1,NULL,'report/taraz.php','NO','NO','NO','NO','NO'),
 (2,27,19,'گزارش گردش حساب','YES',2,NULL,'report/flow.php','NO','NO','NO','NO','NO'),
 (2,28,19,'گزارش اسناد','YES',3,NULL,'report/docs.php','NO','NO','NO','NO','NO'),
 (4,29,0,'اطلاعات پایه','YES',1,NULL,NULL,'NO','NO','NO','NO','NO'),
 (4,30,29,'نامه های رسیده','YES',2,NULL,'letter/receive.php','NO','NO','NO','NO','NO'),
 (4,31,29,'ایجاد نامه','YES',1,NULL,'letter/newLetter.php','NO','NO','NO','NO','NO'),
 (4,32,29,'نامه های ارسالی','YES',3,NULL,'letter/send.php','NO','NO','NO','NO','NO'),
 (2,34,7,'اجزای حساب','YES',2,NULL,'baseinfo/blocks.php','NO','NO','NO','NO','NO'),
 (2,36,7,'حساب های بانکی','YES',5,NULL,'baseinfo/accounts.php','NO','NO','NO','NO','NO'),
 (4,37,0,'فرمساز','YES',4,NULL,NULL,'NO','NO','NO','NO','NO'),
 (4,38,37,'مدیریت فرم ها','YES',1,NULL,'formGenerator/buildForm.php','NO','NO','NO','NO','NO'),
 (1000,39,0,'وام گیرنده','YES',2,NULL,NULL,'YES','NO','NO','NO','NO'),
 (1000,40,0,'سهامداران','YES',1,NULL,NULL,'NO','YES','NO','NO','NO'),
 (1000,41,39,'درخواست وام','YES',1,'clone','loan/NewLoanRequest.php','YES','NO','NO','NO','NO'),
 (1000,42,39,'وام های دریافتی','YES',2,'list','../loan/request/MyRequests.php','YES','NO','NO','NO','YES'),
 (1000,43,39,'پرداخت اقساط','YES',3,'credit-card','../loan/request/installments.php','YES','NO','NO','NO','NO'),
 (1000,44,40,'مدیریت سهام','YES',1,'database','/','NO','YES','NO','NO','NO'),
 (6,45,13,'مدیریت ذینفعان','YES',2,'users.gif','../framework/person/persons.php','NO','NO','NO','NO','NO'),
 (1000,46,0,'سرمایه گذار','YES',NULL,NULL,NULL,'NO','NO','NO','YES','NO'),
 (1000,47,46,'معرفی اخذ وام','YES',1,NULL,'../loan/request/RequestInfo.php','NO','NO','NO','YES','NO'),
 (8,48,0,'مدیریت ذینفعان','YES',1,NULL,NULL,'NO','NO','NO','NO','NO'),
 (8,49,48,'اطلاعات و مدارک ذینفعان','YES',1,'users.gif','persons.php','NO','NO','NO','NO','NO'),
 (2,50,7,'تعیین شعبه و دوره','YES',1,NULL,'global/UserState.php','NO','NO','NO','NO','NO'),
 (1000,51,46,'لیست وام های ارسالی','YES',2,'list','../loan/request/MyRequests.php','NO','NO','NO','YES','NO'),
 (4,52,0,'کارتابل شخصی','YES',2,NULL,NULL,'NO','NO','NO','NO','NO'),
 (4,53,52,'کارتابل فرم های رسیده','YES',1,NULL,'workflow/MyForms.php','NO','NO','NO','NO','NO');
/*!40000 ALTER TABLE `FRW_menus` ENABLE KEYS */;


--
-- Definition of table `FRW_systems`
--

DROP TABLE IF EXISTS `FRW_systems`;
CREATE TABLE `FRW_systems` (
  `SystemID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `SysName` varchar(500) CHARACTER SET utf8 COLLATE utf8_persian_ci NOT NULL,
  `SysPath` varchar(500) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `SysIcon` varchar(500) NOT NULL,
  PRIMARY KEY (`SystemID`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `FRW_systems`
--

/*!40000 ALTER TABLE `FRW_systems` DISABLE KEYS */;
INSERT INTO `FRW_systems` (`SystemID`,`SysName`,`SysPath`,`IsActive`,`SysIcon`) VALUES 
 (1,'سیستم مدیریت فریم ورک','framework','YES','framework.gif'),
 (2,'سیستم حسابداری ','accounting','YES','accountancy.gif'),
 (4,'سیستم اتوماسیون اداری','office','YES','office.gif'),
 (6,'سیستم تسهیلات','loan','YES','loan.jpg'),
 (7,'سیستم مدیریت اسناد','dms','YES','document.gif'),
 (8,'سیستم مدیریت ذینفعان','person','YES','person.png'),
 (1000,'پرتال ','portal','YES','-');
/*!40000 ALTER TABLE `FRW_systems` ENABLE KEYS */;


--
-- Definition of table `FRW_units`
--

DROP TABLE IF EXISTS `FRW_units`;
CREATE TABLE `FRW_units` (
  `UnitID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ParentID` int(10) unsigned DEFAULT NULL,
  `UnitName` varchar(500) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`UnitID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COMMENT='واحدهای سازمانی';

--
-- Dumping data for table `FRW_units`
--

/*!40000 ALTER TABLE `FRW_units` DISABLE KEYS */;
INSERT INTO `FRW_units` (`UnitID`,`ParentID`,`UnitName`) VALUES 
 (1,NULL,'مدیریت'),
 (8,1,'معاونت'),
 (9,NULL,'اداری');
/*!40000 ALTER TABLE `FRW_units` ENABLE KEYS */;


--
-- Definition of table `LON_PartPayments`
--

DROP TABLE IF EXISTS `LON_PartPayments`;
CREATE TABLE `LON_PartPayments` (
  `PayID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ردیف پرداخت',
  `PartID` int(10) unsigned NOT NULL COMMENT 'کد مرحله وام',
  `PayDate` date NOT NULL COMMENT 'تاریخ سررسید',
  `PayAmount` decimal(15,0) NOT NULL COMMENT 'مبلغ خالص',
  `WageAmount` decimal(15,0) NOT NULL COMMENT 'مبلغ کارمزد',
  `CustomerWage` smallint(5) unsigned NOT NULL COMMENT 'درصد کارمزد',
  `FundWage` smallint(5) unsigned NOT NULL,
  `PaidDate` datetime DEFAULT NULL COMMENT 'تاریخ پرداخت',
  `PaidAmount` decimal(15,0) DEFAULT NULL COMMENT 'مبلغ چرداخت شده',
  `StatusID` smallint(5) unsigned NOT NULL DEFAULT '1',
  `ChequeNo` decimal(10,0) DEFAULT NULL,
  `ChequeDate` date DEFAULT NULL,
  `ChequeBank` smallint(5) unsigned DEFAULT NULL,
  `ChequeBranch` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`PayID`)
) ENGINE=InnoDB AUTO_INCREMENT=229 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `LON_PartPayments`
--

/*!40000 ALTER TABLE `LON_PartPayments` DISABLE KEYS */;
INSERT INTO `LON_PartPayments` (`PayID`,`PartID`,`PayDate`,`PayAmount`,`WageAmount`,`CustomerWage`,`FundWage`,`PaidDate`,`PaidAmount`,`StatusID`,`ChequeNo`,`ChequeDate`,`ChequeBank`,`ChequeBranch`) VALUES 
 (145,3,'2015-12-25','5000000','220000',4,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (146,3,'2016-02-23','5000000','220000',4,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (147,3,'2016-04-23','5000000','220000',4,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (148,3,'2016-06-22','5000000','220000',4,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (149,3,'2016-08-21','5000000','220000',4,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (150,3,'2016-10-20','5000000','220000',4,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (151,3,'2016-12-19','5000000','220000',4,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (152,3,'2017-02-17','5000000','220000',4,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (153,3,'2017-04-18','5000000','220000',4,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (154,3,'2017-06-17','5000000','220000',4,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (155,3,'2017-08-16','5000000','220000',4,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (156,3,'2017-10-15','5000000','211669',4,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (193,2,'2015-11-25','5000000','275000',10,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (194,2,'2015-12-25','5000000','275000',10,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (195,2,'2016-01-24','5000000','275000',10,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (196,2,'2016-02-23','5000000','275000',10,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (197,2,'2016-03-23','5000000','275000',10,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (198,2,'2016-04-23','5000000','275000',10,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (199,2,'2016-05-24','5000000','275000',10,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (200,2,'2016-06-24','5000000','275000',10,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (201,2,'2016-07-25','5000000','275000',10,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (202,2,'2016-08-25','5000000','275000',10,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (203,2,'2016-09-25','5000000','275000',10,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (204,2,'2016-10-25','5000000','274439',10,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (205,4,'2016-02-20','5000000','275000',10,4,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (206,4,'2016-03-20','5000000','275000',10,4,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (207,4,'2016-04-20','5000000','275000',10,4,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (208,4,'2016-05-21','5000000','275000',10,4,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (209,4,'2016-06-21','5000000','275000',10,4,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (210,4,'2016-07-22','5000000','275000',10,4,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (211,4,'2016-08-22','5000000','275000',10,4,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (212,4,'2016-09-22','5000000','275000',10,4,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (213,4,'2016-10-22','5000000','275000',10,4,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (214,4,'2016-11-21','5000000','275000',10,4,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (215,4,'2016-12-21','5000000','275000',10,4,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (216,4,'2017-01-20','5000000','274439',10,4,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (217,6,'2016-02-05','5000000','137000',5,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (218,6,'2016-03-06','5000000','137000',5,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (219,6,'2016-04-04','5000000','137000',5,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (220,6,'2016-05-05','5000000','137000',5,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (221,6,'2016-06-05','5000000','137000',5,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (222,6,'2016-07-06','5000000','137000',5,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (223,6,'2016-08-06','5000000','137000',5,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (224,6,'2016-09-06','5000000','137000',5,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (225,6,'2016-10-07','5000000','137000',5,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (226,6,'2016-11-06','5000000','137000',5,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (227,6,'2016-12-06','5000000','137000',5,10,NULL,NULL,1,NULL,NULL,NULL,NULL),
 (228,6,'2017-01-05','5000000','130387',5,10,NULL,NULL,1,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `LON_PartPayments` ENABLE KEYS */;


--
-- Definition of table `LON_ReqDocs`
--

DROP TABLE IF EXISTS `LON_ReqDocs`;
CREATE TABLE `LON_ReqDocs` (
  `ReqDocID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'مدرک درخواست',
  `RequestID` int(10) unsigned NOT NULL COMMENT 'شماره درخواست',
  `DocType` int(10) unsigned NOT NULL COMMENT 'نوع مدرک',
  `description` varchar(5000) NOT NULL COMMENT 'توضیحات',
  PRIMARY KEY (`ReqDocID`),
  KEY `FK_LON_ReqDocs_1` (`RequestID`),
  CONSTRAINT `FK_LON_ReqDocs_1` FOREIGN KEY (`RequestID`) REFERENCES `lon_requests` (`RequestID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `LON_ReqDocs`
--

/*!40000 ALTER TABLE `LON_ReqDocs` DISABLE KEYS */;
/*!40000 ALTER TABLE `LON_ReqDocs` ENABLE KEYS */;


--
-- Definition of table `LON_ReqFlow`
--

DROP TABLE IF EXISTS `LON_ReqFlow`;
CREATE TABLE `LON_ReqFlow` (
  `FlowID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RequestID` int(10) unsigned NOT NULL,
  `PersonID` int(10) unsigned NOT NULL,
  `StatusID` smallint(5) unsigned NOT NULL,
  `ActDate` datetime NOT NULL,
  `StepComment` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`FlowID`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `LON_ReqFlow`
--

/*!40000 ALTER TABLE `LON_ReqFlow` DISABLE KEYS */;
INSERT INTO `LON_ReqFlow` (`FlowID`,`RequestID`,`PersonID`,`StatusID`,`ActDate`,`StepComment`) VALUES 
 (43,1000,1001,1,'2015-11-06 12:01:39',''),
 (44,1000,1001,10,'2015-11-06 12:15:29',''),
 (45,1000,1000,20,'2015-11-06 13:00:03','با کارمزد تعیین شده قادر به پرداخت وام نمی باشیم\nکارمزد را در هر دو مرحله به 12% تغییر دهید.'),
 (46,1000,1001,10,'2015-11-06 13:26:48',''),
 (47,1000,1000,30,'2015-11-06 13:28:59',''),
 (48,1000,1000,40,'2015-11-06 15:39:12',''),
 (49,1000,1003,50,'2015-11-06 16:13:19',''),
 (50,1000,1000,60,'2015-11-06 16:26:58','مدارک وام گیرنده را کامل کنید'),
 (51,1000,1003,50,'2015-11-06 16:39:10',''),
 (52,1000,1000,70,'2015-11-06 16:41:35',''),
 (53,1000,1000,80,'2015-11-07 11:17:31','مرحله اول'),
 (55,1000,1000,80,'2015-11-07 12:30:57','مرحله اول');
/*!40000 ALTER TABLE `LON_ReqFlow` ENABLE KEYS */;


--
-- Definition of table `LON_ReqParts`
--

DROP TABLE IF EXISTS `LON_ReqParts`;
CREATE TABLE `LON_ReqParts` (
  `PartID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RequestID` int(10) unsigned NOT NULL,
  `PartDesc` varchar(200) NOT NULL,
  `PartDate` date NOT NULL,
  `PartAmount` decimal(15,0) NOT NULL,
  `InstallmentCount` smallint(5) unsigned NOT NULL DEFAULT '1',
  `IntervalType` enum('MONTH','DAY') NOT NULL DEFAULT 'MONTH',
  `PayInterval` smallint(5) unsigned NOT NULL DEFAULT '1',
  `DelayMonths` smallint(5) unsigned NOT NULL DEFAULT '0',
  `ForfeitPercent` smallint(5) unsigned NOT NULL DEFAULT '0',
  `CustomerWage` smallint(5) unsigned NOT NULL DEFAULT '0',
  `FundWage` smallint(5) unsigned NOT NULL DEFAULT '0',
  `IsPayed` enum('YES','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`PartID`),
  KEY `FK_LON_ReqParts_1` (`RequestID`),
  CONSTRAINT `FK_LON_ReqParts_1` FOREIGN KEY (`RequestID`) REFERENCES `lon_requests` (`RequestID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `LON_ReqParts`
--

/*!40000 ALTER TABLE `LON_ReqParts` DISABLE KEYS */;
INSERT INTO `LON_ReqParts` (`PartID`,`RequestID`,`PartDesc`,`PartDate`,`PartAmount`,`InstallmentCount`,`IntervalType`,`PayInterval`,`DelayMonths`,`ForfeitPercent`,`CustomerWage`,`FundWage`,`IsPayed`) VALUES 
 (1,1000,'مرحله اول','2015-11-22','600000000',12,'MONTH',1,6,4,12,12,'YES'),
 (2,1000,'مرحله دوم','2016-11-21','600000000',12,'MONTH',1,6,4,4,9,'NO');
/*!40000 ALTER TABLE `LON_ReqParts` ENABLE KEYS */;


--
-- Definition of table `LON_guarantors`
--

DROP TABLE IF EXISTS `LON_guarantors`;
CREATE TABLE `LON_guarantors` (
  `RowID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `LoanID` int(10) unsigned NOT NULL,
  `fullname` varchar(500) NOT NULL,
  PRIMARY KEY (`RowID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `LON_guarantors`
--

/*!40000 ALTER TABLE `LON_guarantors` DISABLE KEYS */;
/*!40000 ALTER TABLE `LON_guarantors` ENABLE KEYS */;


--
-- Definition of table `LON_installments`
--

DROP TABLE IF EXISTS `LON_installments`;
CREATE TABLE `LON_installments` (
  `InstallmentID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ردیف پرداخت',
  `PartID` int(10) unsigned NOT NULL COMMENT 'کد مرحله وام',
  `InstallmentDate` date NOT NULL COMMENT 'تاریخ سررسید',
  `InstallmentAmount` decimal(15,0) NOT NULL COMMENT 'مبلغ خالص',
  `PaidDate` datetime DEFAULT NULL COMMENT 'تاریخ پرداخت',
  `PaidAmount` decimal(15,0) DEFAULT NULL COMMENT 'مبلغ چرداخت شده',
  `PaidRefNo` varchar(100) DEFAULT NULL,
  `StatusID` smallint(5) unsigned NOT NULL DEFAULT '1',
  `ChequeNo` decimal(10,0) DEFAULT NULL,
  `ChequeDate` date DEFAULT NULL,
  `ChequeBank` smallint(5) unsigned DEFAULT NULL,
  `ChequeBranch` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`InstallmentID`),
  KEY `FK_LON_installments_1` (`PartID`),
  CONSTRAINT `FK_LON_installments_1` FOREIGN KEY (`PartID`) REFERENCES `lon_reqparts` (`PartID`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `LON_installments`
--

/*!40000 ALTER TABLE `LON_installments` DISABLE KEYS */;
INSERT INTO `LON_installments` (`InstallmentID`,`PartID`,`InstallmentDate`,`InstallmentAmount`,`PaidDate`,`PaidAmount`,`PaidRefNo`,`StatusID`,`ChequeNo`,`ChequeDate`,`ChequeBank`,`ChequeBranch`) VALUES 
 (25,1,'2015-12-22','53310000','2015-11-08 11:33:12','1000','000079454618',100,NULL,NULL,NULL,NULL),
 (26,1,'2016-01-21','53310000',NULL,NULL,'',1,NULL,NULL,NULL,NULL),
 (27,1,'2016-02-20','53310000',NULL,NULL,'',1,NULL,NULL,NULL,NULL),
 (28,1,'2016-03-20','53310000',NULL,NULL,'',1,NULL,NULL,NULL,NULL),
 (29,1,'2016-04-20','53310000',NULL,NULL,'',1,NULL,NULL,NULL,NULL),
 (30,1,'2016-05-21','53310000',NULL,NULL,'',1,NULL,NULL,NULL,NULL),
 (31,1,'2016-06-21','53310000',NULL,NULL,'',1,NULL,NULL,NULL,NULL),
 (32,1,'2016-07-22','53310000',NULL,NULL,'',1,NULL,NULL,NULL,NULL),
 (33,1,'2016-08-22','53310000',NULL,NULL,'',1,NULL,NULL,NULL,NULL),
 (34,1,'2016-09-22','53310000',NULL,NULL,'',1,NULL,NULL,NULL,NULL),
 (35,1,'2016-10-22','53310000',NULL,NULL,'',1,NULL,NULL,NULL,NULL),
 (36,1,'2016-11-21','53301278',NULL,NULL,'',1,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `LON_installments` ENABLE KEYS */;


--
-- Definition of table `LON_loans`
--

DROP TABLE IF EXISTS `LON_loans`;
CREATE TABLE `LON_loans` (
  `LoanID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد وام',
  `GroupID` int(10) unsigned NOT NULL COMMENT 'کد گروه وام',
  `LoanDesc` varchar(500) NOT NULL COMMENT 'عنوان وام',
  `MaxAmount` decimal(15,0) NOT NULL DEFAULT '0' COMMENT 'سقف مبلغ',
  `PartCount` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'تعداد اقساط',
  `PartInterval` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'فاصله اقساط',
  `DelayCount` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'زمان تنفس به ماه',
  `InsureAmount` decimal(15,0) NOT NULL DEFAULT '0' COMMENT 'مبلغ بیمه',
  `FirstPartAmount` decimal(15,0) NOT NULL DEFAULT '0' COMMENT 'مبلغ قسط اول',
  `ForfeitPercent` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'درصد جریمه',
  `FeePercent` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'درصد کارمزد',
  `FeeAmount` decimal(15,0) NOT NULL DEFAULT '0' COMMENT 'کارمزد ثابت',
  `ProfitPercent` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'درصد سود',
  PRIMARY KEY (`LoanID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `LON_loans`
--

/*!40000 ALTER TABLE `LON_loans` DISABLE KEYS */;
INSERT INTO `LON_loans` (`LoanID`,`GroupID`,`LoanDesc`,`MaxAmount`,`PartCount`,`PartInterval`,`DelayCount`,`InsureAmount`,`FirstPartAmount`,`ForfeitPercent`,`FeePercent`,`FeeAmount`,`ProfitPercent`) VALUES 
 (1,1,'وام طرح های بزرگ','120000000',24,30,60,'1200000','18800000',10,4,'0',20),
 (3,2,'وام مسکن شماره 1000','1000000000000',24,30,60,'12000','10000000',20,30,'0',10),
 (4,3,'وام جزیی 1','200000000',36,30,12,'120000','2000000',30,20,'0',10),
 (5,2,'نمست نمست بت مست بمنتسی','120000000',34,0,0,'0','0',0,0,'0',0);
/*!40000 ALTER TABLE `LON_loans` ENABLE KEYS */;


--
-- Definition of table `LON_requests`
--

DROP TABLE IF EXISTS `LON_requests`;
CREATE TABLE `LON_requests` (
  `RequestID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد درخواست',
  `BranchID` int(10) unsigned DEFAULT NULL,
  `LoanID` int(10) unsigned DEFAULT NULL COMMENT 'کد وام',
  `ReqPersonID` int(10) unsigned NOT NULL COMMENT 'ثبت کننده درخواست',
  `ReqDate` datetime NOT NULL COMMENT 'تاریخ درخواست',
  `ReqAmount` decimal(15,0) NOT NULL DEFAULT '0' COMMENT 'مبلغ درخواست',
  `StatusID` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'وضعیت',
  `ReqDetails` varchar(4000) DEFAULT NULL,
  `BorrowerDesc` varchar(5000) DEFAULT NULL COMMENT 'شرکت معرفی شده',
  `BorrowerID` varchar(20) DEFAULT NULL COMMENT 'کد اقتصادی',
  `LoanPersonID` int(10) unsigned DEFAULT NULL COMMENT 'وام گیرنده',
  `guarantees` varchar(20) DEFAULT NULL COMMENT 'تضمین وام',
  `AgentGuarantee` enum('YES','NO') NOT NULL DEFAULT 'NO' COMMENT 'با ضمانت عامل',
  `DocumentDesc` varchar(2000) DEFAULT NULL COMMENT 'توضیحات مدارک',
  PRIMARY KEY (`RequestID`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `LON_requests`
--

/*!40000 ALTER TABLE `LON_requests` DISABLE KEYS */;
INSERT INTO `LON_requests` (`RequestID`,`BranchID`,`LoanID`,`ReqPersonID`,`ReqDate`,`ReqAmount`,`StatusID`,`ReqDetails`,`BorrowerDesc`,`BorrowerID`,`LoanPersonID`,`guarantees`,`AgentGuarantee`,`DocumentDesc`) VALUES 
 (1000,1,NULL,1001,'2015-11-06 12:01:39','1200000000',80,NULL,'شرکت صنعتی شرق','124587415',1003,'1,3,4','NO','سه فقره چک و سه ضامن با سفته لازم می باشد.');
/*!40000 ALTER TABLE `LON_requests` ENABLE KEYS */;


--
-- Definition of table `WFM_FlowRows`
--

DROP TABLE IF EXISTS `WFM_FlowRows`;
CREATE TABLE `WFM_FlowRows` (
  `RowID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `FlowID` int(10) unsigned NOT NULL,
  `StepID` int(10) unsigned NOT NULL,
  `ObjectID` int(10) unsigned NOT NULL,
  `PersonID` int(10) unsigned NOT NULL,
  `ActionDate` datetime NOT NULL,
  `ActionType` enum('CONFIRM','REJECT') NOT NULL,
  `ActionComment` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`RowID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `WFM_FlowRows`
--

/*!40000 ALTER TABLE `WFM_FlowRows` DISABLE KEYS */;
INSERT INTO `WFM_FlowRows` (`RowID`,`FlowID`,`StepID`,`ObjectID`,`PersonID`,`ActionDate`,`ActionType`,`ActionComment`) VALUES 
 (1,1,0,1,1000,'2015-11-06 18:16:36','CONFIRM',NULL),
 (2,1,1,1,1000,'2015-11-06 21:37:21','CONFIRM','-----------'),
 (3,1,2,1,1009,'2015-11-06 23:06:17','REJECT','چونکه ......'),
 (4,1,1,1,1000,'2015-11-06 23:09:50','CONFIRM',NULL),
 (5,1,2,1,1009,'2015-11-06 23:10:13','CONFIRM',NULL);
/*!40000 ALTER TABLE `WFM_FlowRows` ENABLE KEYS */;


--
-- Definition of table `WFM_FlowSteps`
--

DROP TABLE IF EXISTS `WFM_FlowSteps`;
CREATE TABLE `WFM_FlowSteps` (
  `FlowID` int(10) unsigned NOT NULL,
  `StepID` int(10) unsigned NOT NULL,
  `StepDesc` varchar(45) NOT NULL,
  `PostID` int(10) unsigned NOT NULL,
  `IsObjectEditable` enum('YES','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`FlowID`,`StepID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `WFM_FlowSteps`
--

/*!40000 ALTER TABLE `WFM_FlowSteps` DISABLE KEYS */;
INSERT INTO `WFM_FlowSteps` (`FlowID`,`StepID`,`StepDesc`,`PostID`,`IsObjectEditable`) VALUES 
 (1,1,'تایید اولیه',1,'YES'),
 (1,2,'تایید ناظر',3,'NO');
/*!40000 ALTER TABLE `WFM_FlowSteps` ENABLE KEYS */;


--
-- Definition of table `WFM_RequestElements`
--

DROP TABLE IF EXISTS `WFM_RequestElements`;
CREATE TABLE `WFM_RequestElements` (
  `RowID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RequestID` int(10) unsigned NOT NULL,
  `ElementID` int(10) unsigned NOT NULL,
  `ElementValue` varchar(5000) NOT NULL,
  PRIMARY KEY (`RowID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `WFM_RequestElements`
--

/*!40000 ALTER TABLE `WFM_RequestElements` DISABLE KEYS */;
/*!40000 ALTER TABLE `WFM_RequestElements` ENABLE KEYS */;


--
-- Definition of table `WFM_flows`
--

DROP TABLE IF EXISTS `WFM_flows`;
CREATE TABLE `WFM_flows` (
  `FlowID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ObjectType` smallint(5) unsigned NOT NULL,
  `FlowDesc` varchar(50) NOT NULL,
  `IsSystemic` enum('YES','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`FlowID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `WFM_flows`
--

/*!40000 ALTER TABLE `WFM_flows` DISABLE KEYS */;
INSERT INTO `WFM_flows` (`FlowID`,`ObjectType`,`FlowDesc`,`IsSystemic`) VALUES 
 (1,1,'گردش مرحله وام','YES');
/*!40000 ALTER TABLE `WFM_flows` ENABLE KEYS */;


--
-- Definition of table `WFM_requests`
--

DROP TABLE IF EXISTS `WFM_requests`;
CREATE TABLE `WFM_requests` (
  `RequetID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `FormID` int(10) unsigned NOT NULL,
  `RequestNo` int(10) unsigned NOT NULL,
  `RegPersonID` int(10) unsigned NOT NULL,
  `RegDate` datetime NOT NULL,
  PRIMARY KEY (`RequetID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `WFM_requests`
--

/*!40000 ALTER TABLE `WFM_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `WFM_requests` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
