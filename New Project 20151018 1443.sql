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
  `BranchID` int(10) unsigned NOT NULL COMMENT 'کد شعبه',
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `CostCode` varchar(45) DEFAULT NULL COMMENT 'کد حساب',
  PRIMARY KEY (`CostID`),
  KEY `FK_ACC_CostCodes_1` (`level1`),
  KEY `FK_ACC_CostCodes_2` (`level2`),
  KEY `FK_ACC_CostCodes_3` (`level3`),
  CONSTRAINT `FK_ACC_CostCodes_1` FOREIGN KEY (`level1`) REFERENCES `acc_blocks` (`BlockID`),
  CONSTRAINT `FK_ACC_CostCodes_2` FOREIGN KEY (`level2`) REFERENCES `acc_blocks` (`BlockID`),
  CONSTRAINT `FK_ACC_CostCodes_3` FOREIGN KEY (`level3`) REFERENCES `acc_blocks` (`BlockID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='کدهای حساب';

--
-- Dumping data for table `ACC_CostCodes`
--

/*!40000 ALTER TABLE `ACC_CostCodes` DISABLE KEYS */;
INSERT INTO `ACC_CostCodes` (`CostID`,`level1`,`level2`,`level3`,`BranchID`,`IsActive`,`CostCode`) VALUES 
 (4,2,3,6,1,'YES','10102'),
 (5,2,4,7,1,'YES','10203');
/*!40000 ALTER TABLE `ACC_CostCodes` ENABLE KEYS */;


--
-- Definition of table `ACC_DocChecks`
--

DROP TABLE IF EXISTS `ACC_DocChecks`;
CREATE TABLE `ACC_DocChecks` (
  `DocID` int(10) unsigned NOT NULL COMMENT 'کد سند',
  `CheckID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'کد چک',
  `AccountID` int(10) unsigned NOT NULL COMMENT 'کد حساب',
  `CheckNo` int(10) unsigned NOT NULL COMMENT 'شماره چک',
  `CheckDate` date NOT NULL COMMENT 'تاریخ چک',
  `amount` decimal(15,0) NOT NULL COMMENT 'مبلغ',
  `CheckStatus` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT 'وضعیت چک',
  `description` varchar(500) DEFAULT NULL COMMENT 'توضیحات',
  `reciever` varchar(500) DEFAULT NULL,
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
INSERT INTO `ACC_DocChecks` (`DocID`,`CheckID`,`AccountID`,`CheckNo`,`CheckDate`,`amount`,`CheckStatus`,`description`,`reciever`) VALUES 
 (1,3,1,14587,'2015-09-18','1500000',1,NULL,NULL),
 (4,4,1,147,'2015-09-18','4780000',1,NULL,NULL);
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
  `DebtorAmount` decimal(15,0) NOT NULL DEFAULT '0' COMMENT 'مبلغ بدهکار',
  `CreditorAmount` decimal(15,0) NOT NULL DEFAULT '0' COMMENT 'مبلغ بستانکار',
  `details` varchar(500) DEFAULT NULL COMMENT 'جزئیات',
  `locked` enum('YES','NO') NOT NULL DEFAULT 'NO' COMMENT 'قفل بودن ردیف',
  PRIMARY KEY (`ItemID`),
  KEY `FK_ACC_DocItems_1` (`DocID`),
  KEY `FK_ACC_DocItems_2` (`CostID`),
  KEY `FK_ACC_DocItems_3` (`TafsiliID`),
  CONSTRAINT `FK_ACC_DocItems_1` FOREIGN KEY (`DocID`) REFERENCES `acc_docs` (`DocID`),
  CONSTRAINT `FK_ACC_DocItems_2` FOREIGN KEY (`CostID`) REFERENCES `acc_costcodes` (`CostID`),
  CONSTRAINT `FK_ACC_DocItems_3` FOREIGN KEY (`TafsiliID`) REFERENCES `acc_tafsilis` (`TafsiliID`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_DocItems`
--

/*!40000 ALTER TABLE `ACC_DocItems` DISABLE KEYS */;
INSERT INTO `ACC_DocItems` (`DocID`,`ItemID`,`CostID`,`TafsiliType`,`TafsiliID`,`DebtorAmount`,`CreditorAmount`,`details`,`locked`) VALUES 
 (1,2,4,1,1,'150000','0',NULL,'NO'),
 (1,3,5,2,3,'0','150000',NULL,'NO'),
 (4,4,4,1,1,'15478000','0',NULL,'NO'),
 (1,5,4,1,1,'350000','0',NULL,'NO'),
 (1,6,5,1,1,'0','350000',NULL,'NO'),
 (14,16,4,1,1,'0','15978000',NULL,'YES'),
 (14,17,5,1,1,'350000','0',NULL,'YES'),
 (14,18,5,2,3,'150000','0',NULL,'YES');
/*!40000 ALTER TABLE `ACC_DocItems` ENABLE KEYS */;


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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_accounts`
--

/*!40000 ALTER TABLE `ACC_accounts` DISABLE KEYS */;
INSERT INTO `ACC_accounts` (`AccountID`,`BankID`,`BranchID`,`AccountDesc`,`IsActive`,`AccountNo`,`AccountType`,`TafsiliID`) VALUES 
 (1,1,1,'98098 جاری ملی','YES','3000098098',1,NULL);
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
  `BranchID` int(10) unsigned NOT NULL COMMENT 'کد شعبه',
  `essence` enum('DEBTOR','CREDITOR','NONE') NOT NULL DEFAULT 'NONE' COMMENT 'ماهیت',
  `IsActive` enum('YES','NO') NOT NULL,
  PRIMARY KEY (`BlockID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_blocks`
--

/*!40000 ALTER TABLE `ACC_blocks` DISABLE KEYS */;
INSERT INTO `ACC_blocks` (`BlockID`,`LevelID`,`BlockCode`,`BlockDesc`,`BranchID`,`essence`,`IsActive`) VALUES 
 (2,1,'1','گروه  1',1,'NONE','YES'),
 (3,2,'01','بانک',1,'NONE','YES'),
 (4,2,'02','هزینه',1,'DEBTOR','YES'),
 (5,3,'01','بدهکاران',1,'NONE','YES'),
 (6,3,'02','پرداختنی',1,'NONE','YES'),
 (7,3,'03','معین 1',1,'NONE','YES'),
 (8,3,'04','سسس',1,'NONE','YES'),
 (9,3,'05','ییشسیش',1,'NONE','YES');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_cycles`
--

/*!40000 ALTER TABLE `ACC_cycles` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_docs`
--

/*!40000 ALTER TABLE `ACC_docs` DISABLE KEYS */;
INSERT INTO `ACC_docs` (`DocID`,`CycleID`,`BranchID`,`LocalNo`,`DocDate`,`RegDate`,`DocStatus`,`DocType`,`description`,`RegPersonID`) VALUES 
 (1,1,1,1,'2015-09-18','2015-09-18','ARCHIVE','NORMAL',NULL,1000),
 (2,1,1,2,'2015-09-21','2015-09-18','RAW','NORMAL',NULL,1000),
 (4,1,1,4,'2015-09-21','2015-09-18','RAW','NORMAL',NULL,1000),
 (14,1,1,5,'2015-09-21','2015-09-20','RAW','ENDCYCLE','سند اختتامیه',1000);
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
  `BranchID` int(10) unsigned NOT NULL COMMENT 'کد شعبه',
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `PersonID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`TafsiliID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ACC_tafsilis`
--

/*!40000 ALTER TABLE `ACC_tafsilis` DISABLE KEYS */;
INSERT INTO `ACC_tafsilis` (`TafsiliID`,`TafsiliCode`,`TafsiliType`,`TafsiliDesc`,`BranchID`,`IsActive`,`PersonID`) VALUES 
 (1,'1000',1,'شرکت ی111',1,'YES',NULL),
 (2,'1001',1,'شرکت 2',1,'YES',NULL),
 (3,'1',2,'شخص 1',1,'YES',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=1006 DEFAULT CHARSET=utf8 COMMENT='ذینفعان';

--
-- Dumping data for table `BSC_persons`
--

/*!40000 ALTER TABLE `BSC_persons` DISABLE KEYS */;
INSERT INTO `BSC_persons` (`PersonID`,`UserName`,`UserPass`,`IsReal`,`fname`,`lname`,`CompanyName`,`NationalID`,`EconomicID`,`PhoneNo`,`mobile`,`address`,`email`,`IsStaff`,`IsCustomer`,`IsShareholder`,`IsAgent`,`IsSupporter`,`IsActive`,`PostID`) VALUES 
 (1000,'admin','$P$BCy9D77Tk5UrJibOCgIkum/NYvq3Ym1','YES','شبنم','جعفرخانی','','0943021723',NULL,NULL,NULL,'sdfsdf',NULL,'YES','YES','YES','YES','YES','YES',1),
 (1005,'park','$P$BcoXpMFz3xw6B108dtdAtm.iA9V5pa0','NO',' ',NULL,'پارک علم و فناوری',NULL,'7777777777',NULL,NULL,'جاده قوچان - پارک علم و فناوری','park@us.com','NO','YES','NO','YES','NO','YES',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='پست هاي سازماني';

--
-- Dumping data for table `BSC_posts`
--

/*!40000 ALTER TABLE `BSC_posts` DISABLE KEYS */;
INSERT INTO `BSC_posts` (`PostID`,`UnitID`,`PostName`) VALUES 
 (1,1,'کارشناس'),
 (2,1,'مدیر عامل');
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
  PRIMARY KEY (`InfoID`,`TypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `BaseInfo`
--

/*!40000 ALTER TABLE `BaseInfo` DISABLE KEYS */;
INSERT INTO `BaseInfo` (`TypeID`,`InfoID`,`InfoDesc`) VALUES 
 (1,1,'وام های جعاله'),
 (2,1,'شرکتها'),
 (3,1,'حساب جاری'),
 (4,1,'در جریان'),
 (5,1,'درخواست خام'),
 (7,1,'وثیقه ملکی'),
 (8,1,'صفحه اول شناسنامه'),
 (1,2,'وام های مسکن'),
 (2,2,'اشخاص'),
 (3,2,'حساب سپرده '),
 (7,2,'ضمانت بانکی'),
 (8,2,'صفحه دوم شناسنامه'),
 (1,3,'وام های جزیی'),
 (7,3,'سفته'),
 (8,3,'توضیحات شناسنامه'),
 (7,4,'چک'),
 (8,4,'کارت ملی'),
 (7,5,'کسر از حقوق'),
 (8,5,'پشت کارت ملی'),
 (7,6,'ماشین آلات'),
 (5,10,'ارسال درخواست'),
 (6,10,'خام'),
 (5,20,'رد درخواست'),
 (6,20,'پرداخت شده'),
 (5,30,'تایید درخواست');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

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
 (7,6,'انواع تضمین','LON_requests','assurance','YES'),
 (8,7,'انواع مدارک','DMS_documents','DocType','NO');
/*!40000 ALTER TABLE `BaseTypes` ENABLE KEYS */;


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
  `IsConfirm` enum('YES','NO') NOT NULL DEFAULT 'NO' COMMENT 'برابر اصل',
  `ConfirmPersonID` int(10) unsigned DEFAULT NULL COMMENT 'فرد تایید کننده',
  PRIMARY KEY (`DocumentID`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `DMS_documents`
--

/*!40000 ALTER TABLE `DMS_documents` DISABLE KEYS */;
INSERT INTO `DMS_documents` (`DocumentID`,`DocDesc`,`DocType`,`ObjectType`,`ObjectID`,`FileType`,`FileContent`,`IsConfirm`,`ConfirmPersonID`) VALUES 
 (18,NULL,1,'person',1000,'jpg',0xFFD8FFE000104A46494600010200006400640000FFEC00114475636B7900010004000000640000FFEE000E41646F62650064C000000001FFDB008400010101010101010101010101010101010101010101010101010101010101010101010101010101010101010202020202020202020202030303030303030303030101010101010102010102020201020203030303030303030303030303030303030303030303030303030303030303030303030303030303030303030303030303FFC0001108012C00C80301,'YES',1000),
 (19,NULL,4,'person',1000,'pdf',0x255044462D312E330A25E2E3CFD30A312030206F626A0A3C3C0A2F4C656E67746820363234330A2F46696C746572205B2F466C6174654465636F64655D0A3E3E0A73747265616D0A5885D5994BAB64C7B185E7F52BCAB36EC329ED4755ED2A381CB06CB54133DB07EE40EDC9C56E5D84647063B87FDF11192B2256EEC8EA6EC9606C0C2D9FAF763EE21D99F9F7C3D7AF87AFDE4DC7F9F8FAE1302FC749FE27FF596EF7D3BA1CD7FB69BA4DEBF1F5A7C377CFD33C2D2FF3F3B4BE9C9FA7F3CBD3FA3C5D5E9E1C5C5F,'YES',1000),
 (20,'qqqqq',1,'person',1005,'jpg',0xFFD8FFE000104A46494600010101012C012C0000FFE106E545786966000049492A000800000001001250040001000000010000001A000000030028010300010000000200000001020400010000004400000002020400010000009906000000000000FFD8FFE000104A46494600010101000500050000FFDB004300080606070605080707070909080A0C140D0C0B0B0C1912130F141D1A1F1E1D1A1C1C20242E2720222C231C1C2837292C30313434341F27393D38323C2E333432FFDB0043010909090C0B0C180D,'NO',NULL),
 (22,NULL,5,'person',1005,'jpg',0xFFD8FFE000104A46494600010100000100010000FFFE003E43524541544F523A2067642D6A7065672076312E3020287573696E6720494A47204A50454720763632292C2064656661756C74207175616C6974790AFFDB004300080606070605080707070909080A0C140D0C0B0B0C1912130F141D1A1F1E1D1A1C1C20242E2720222C231C1C2837292C30313434341F27393D38323C2E333432FFDB0043010909090C0B0C180D0D1832211C2132323232323232323232323232323232323232323232323232323232,'YES',1000);
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
) ENGINE=MyISAM AUTO_INCREMENT=551 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci COMMENT='اطلاعات ممیزی ';

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
 (550,1000,'DMS_documents',22,NULL,'UPDATE',8,'http://rtfund/person/start.php?SystemID=8',NULL,'127.0.0.1','2015-10-18 14:07:56','update DMS_documents set DocumentID=\'22\',IsConfirm=\'YES\',ConfirmPersonID=\'1000\' where  DocumentID=\'22\'');
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
 (25,1000,'YES','YES','NO','NO'),
 (26,1000,'YES','YES','YES','YES'),
 (27,1000,'YES','YES','YES','YES'),
 (28,1000,'YES','YES','YES','YES'),
 (30,1000,'YES','YES','YES','YES'),
 (31,1000,'YES','YES','YES','YES'),
 (32,1000,'YES','YES','YES','YES'),
 (33,1000,'YES','YES','YES','YES'),
 (34,1000,'YES','NO','NO','NO'),
 (35,1000,'YES','YES','YES','YES'),
 (36,1000,'YES','YES','NO','NO'),
 (38,1000,'YES','YES','YES','YES'),
 (45,1000,'YES','YES','YES','YES'),
 (49,1000,'YES','YES','YES','YES');
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
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;

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
 (1000,42,39,'وام های دریافتی','YES',2,'list','loan/MyRequests.php','YES','NO','NO','NO','NO'),
 (1000,43,39,'پرداخت اقساط','YES',3,'credit-card','/','YES','NO','NO','NO','NO'),
 (1000,44,40,'مدیریت سهام','YES',1,'database','/','NO','YES','NO','NO','NO'),
 (6,45,13,'مدیریت ذینفعان','YES',2,'users.gif','../framework/person/persons.php','NO','NO','NO','NO','NO'),
 (1000,46,0,'عاملین','YES',NULL,NULL,NULL,'NO','NO','NO','YES','NO'),
 (1000,47,46,'معرفی اخذ وام','YES',NULL,NULL,'loan/AgentNewRequest.php','NO','NO','NO','YES','NO'),
 (8,48,0,'مدیریت ذینفعان','YES',1,NULL,NULL,'NO','NO','NO','NO','NO'),
 (8,49,48,'اطلاعات و مدارک ذینفعان','YES',1,'user','persons.php','NO','NO','NO','NO','NO');
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
) ENGINE=InnoDB AUTO_INCREMENT=1002 DEFAULT CHARSET=latin1;

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
  `PayAmount` decimal(15,0) NOT NULL COMMENT 'مبلغ قابل پرداخت',
  `StatusID` smallint(5) unsigned NOT NULL DEFAULT '1',
  `FeePercent` smallint(5) unsigned NOT NULL COMMENT 'درصد کارمزد',
  `PaidDate` datetime NOT NULL COMMENT 'تاریخ پرداخت',
  `PaidAmount` decimal(15,0) NOT NULL COMMENT 'مبلغ چرداخت شده',
  PRIMARY KEY (`PayID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `LON_PartPayments`
--

/*!40000 ALTER TABLE `LON_PartPayments` DISABLE KEYS */;
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
  PRIMARY KEY (`ReqDocID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `LON_ReqDocs`
--

/*!40000 ALTER TABLE `LON_ReqDocs` DISABLE KEYS */;
/*!40000 ALTER TABLE `LON_ReqDocs` ENABLE KEYS */;


--
-- Definition of table `LON_ReqParts`
--

DROP TABLE IF EXISTS `LON_ReqParts`;
CREATE TABLE `LON_ReqParts` (
  `PartID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RequestID` int(10) unsigned NOT NULL,
  `PartDesc` varchar(200) NOT NULL,
  `PayDate` date NOT NULL,
  `PartAmount` decimal(15,0) NOT NULL,
  `PayCount` smallint(5) unsigned NOT NULL DEFAULT '1',
  `IntervalType` enum('MONTH','DAY') NOT NULL DEFAULT 'MONTH',
  `PayInterval` smallint(5) unsigned NOT NULL DEFAULT '1',
  `DelayMonths` smallint(5) unsigned NOT NULL DEFAULT '0',
  `ForfeitPercent` smallint(5) unsigned NOT NULL DEFAULT '0',
  `CustomerFee` smallint(5) unsigned NOT NULL DEFAULT '0',
  `FundFee` smallint(5) unsigned NOT NULL DEFAULT '0',
  `AgentFee` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`PartID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `LON_ReqParts`
--

/*!40000 ALTER TABLE `LON_ReqParts` DISABLE KEYS */;
INSERT INTO `LON_ReqParts` (`PartID`,`RequestID`,`PartDesc`,`PayDate`,`PartAmount`,`PayCount`,`IntervalType`,`PayInterval`,`DelayMonths`,`ForfeitPercent`,`CustomerFee`,`FundFee`,`AgentFee`) VALUES 
 (1,15,'','2015-10-26','60000000',12,'MONTH',1,0,4,4,10,0),
 (2,16,'مرحله اول','2015-10-26','60000000',12,'MONTH',1,6,4,4,10,0),
 (3,16,'مرحله دوم','2015-06-29','60000000',12,'DAY',1,0,4,10,4,6),
 (4,18,'مرحله اول','2016-01-21','60000000',12,'MONTH',1,0,4,10,4,6),
 (5,18,'مرحله دوم','2016-01-21','60000000',12,'MONTH',1,0,4,10,4,6),
 (6,19,'م اول','2016-01-06','60000000',12,'MONTH',1,0,4,5,10,2);
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
  `OkAmount` decimal(15,0) NOT NULL DEFAULT '0' COMMENT 'مبلغ تایید شده',
  `StatusID` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'وضعیت',
  `ReqDetails` varchar(4000) DEFAULT NULL,
  `CompanyName` varchar(5000) DEFAULT NULL COMMENT 'شرکت معرفی شده',
  `NationalID` varchar(20) DEFAULT NULL COMMENT 'کد اقتصادی',
  `LoanPersonID` int(10) unsigned DEFAULT NULL COMMENT 'وام گیرنده',
  `assurance` smallint(5) unsigned DEFAULT NULL COMMENT 'تضمین وام',
  `AgentGuarantee` enum('YES','NO') NOT NULL DEFAULT 'NO' COMMENT 'با ضمانت عامل',
  PRIMARY KEY (`RequestID`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `LON_requests`
--

/*!40000 ALTER TABLE `LON_requests` DISABLE KEYS */;
INSERT INTO `LON_requests` (`RequestID`,`BranchID`,`LoanID`,`ReqPersonID`,`ReqDate`,`ReqAmount`,`OkAmount`,`StatusID`,`ReqDetails`,`CompanyName`,`NationalID`,`LoanPersonID`,`assurance`,`AgentGuarantee`) VALUES 
 (15,NULL,NULL,1000,'2015-10-13 11:48:18','0','0',1,NULL,'','',NULL,NULL,'NO'),
 (16,1,NULL,1000,'2015-10-13 11:57:46','120000000','0',10,NULL,'شرکت فلان','05131684972',NULL,NULL,'NO'),
 (17,1,NULL,1000,'2015-10-13 13:42:03','120000000','0',10,NULL,NULL,NULL,NULL,NULL,'NO'),
 (18,1,NULL,1000,'2015-10-13 13:43:04','120000000','0',10,NULL,NULL,NULL,NULL,NULL,'NO'),
 (19,2,NULL,1000,'2015-10-13 13:47:59','150000000','0',10,NULL,NULL,NULL,NULL,NULL,'NO'),
 (20,2,NULL,1000,'2015-10-13 14:54:58','21312','0',10,NULL,NULL,NULL,NULL,NULL,'NO');
/*!40000 ALTER TABLE `LON_requests` ENABLE KEYS */;


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
