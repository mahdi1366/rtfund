-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.0.27-community-nt


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema krrtfir_rtfund
--

CREATE DATABASE IF NOT EXISTS krrtfir_rtfund;
USE krrtfir_rtfund;

--
-- Definition of table `FRG_FillFormElems`
--

DROP TABLE IF EXISTS `FRG_FillFormElems`;
CREATE TABLE `FRG_FillFormElems` (
  `RowID` int(10) unsigned NOT NULL auto_increment,
  `FillFormID` int(10) unsigned NOT NULL,
  `ElementID` int(10) unsigned NOT NULL,
  `ElementValue` blob NOT NULL,
  PRIMARY KEY  (`RowID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='آیتم فرم';

--
-- Dumping data for table `FRG_FillFormElems`
--

/*!40000 ALTER TABLE `FRG_FillFormElems` DISABLE KEYS */;
INSERT INTO `FRG_FillFormElems` (`RowID`,`FillFormID`,`ElementID`,`ElementValue`) VALUES 
 (3,1,13,0x333435343334),
 (4,1,14,0xD985D986D8B320D8AAD985D986D8AA20D8B3DB8CD8AAD986D985);
/*!40000 ALTER TABLE `FRG_FillFormElems` ENABLE KEYS */;


--
-- Definition of table `FRG_FillForms`
--

DROP TABLE IF EXISTS `FRG_FillForms`;
CREATE TABLE `FRG_FillForms` (
  `FillFormID` int(10) unsigned NOT NULL auto_increment,
  `FormID` int(10) unsigned NOT NULL,
  `PersonID` int(10) unsigned NOT NULL,
  `RegDate` datetime NOT NULL,
  PRIMARY KEY  (`FillFormID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='فرم هاي ايجاد شده';

--
-- Dumping data for table `FRG_FillForms`
--

/*!40000 ALTER TABLE `FRG_FillForms` DISABLE KEYS */;
INSERT INTO `FRG_FillForms` (`FillFormID`,`FormID`,`PersonID`,`RegDate`) VALUES 
 (1,4,1000,'2017-05-12 00:00:00');
/*!40000 ALTER TABLE `FRG_FillForms` ENABLE KEYS */;


--
-- Definition of table `FRG_FormElems`
--

DROP TABLE IF EXISTS `FRG_FormElems`;
CREATE TABLE `FRG_FormElems` (
  `ElementID` int(10) unsigned NOT NULL auto_increment,
  `FormID` int(10) unsigned NOT NULL,
  `ParentID` int(10) unsigned NOT NULL,
  `ElementTitle` varchar(400) NOT NULL,
  `ElementType` varchar(45) NOT NULL,
  `alias` varchar(45) default NULL,
  `properties` varchar(500) default NULL,
  `EditorProperties` varchar(200) default NULL,
  `ElementValues` varchar(1000) default NULL,
  `IsActive` enum('YES','NO') NOT NULL default 'YES',
  PRIMARY KEY  (`ElementID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `FRG_FormElems`
--

/*!40000 ALTER TABLE `FRG_FormElems` DISABLE KEYS */;
INSERT INTO `FRG_FormElems` (`ElementID`,`FormID`,`ParentID`,`ElementTitle`,`ElementType`,`alias`,`properties`,`EditorProperties`,`ElementValues`,`IsActive`) VALUES 
 (1,0,0,'نام و نام خانوادگی','displayfield','fullname',NULL,NULL,NULL,'YES'),
 (9,4,0,'لیست دارایی ها','grid',NULL,' ',' ',NULL,'YES'),
 (10,4,9,'عنوان دارایی','textfield',NULL,' ',' ',NULL,'YES'),
 (11,4,9,'تاریخ خرید','shdatefield',NULL,' ',' ',NULL,'YES'),
 (12,4,9,'ارزش دارایی','currencyfield',NULL,' ',' ',NULL,'YES'),
 (13,4,0,'مبلغ درخواست وام','currencyfield',NULL,' ',' ',NULL,'YES'),
 (14,4,0,'توضیحات','textarea',NULL,' ',' ',NULL,'YES'),
 (15,5,0,'لیست دارایی ها','grid',NULL,' ',' ',NULL,'YES'),
 (16,5,9,'عنوان دارایی','textfield',NULL,' ',' ',NULL,'YES'),
 (17,5,9,'تاریخ خرید','shdatefield',NULL,' ',' ',NULL,'YES'),
 (18,5,9,'ارزش دارایی','currencyfield',NULL,' ',' ',NULL,'YES'),
 (19,5,0,'مبلغ درخواست وام','currencyfield',NULL,' ',' ',NULL,'YES'),
 (20,5,0,'توضیحات','textarea',NULL,' ',' ',NULL,'YES');
/*!40000 ALTER TABLE `FRG_FormElems` ENABLE KEYS */;


--
-- Definition of table `FRG_forms`
--

DROP TABLE IF EXISTS `FRG_forms`;
CREATE TABLE `FRG_forms` (
  `FormID` int(10) unsigned NOT NULL auto_increment,
  `ParentID` int(10) unsigned NOT NULL default '0',
  `FormTitle` varchar(200) default NULL,
  `FormContent` text,
  `IsActive` enum('YES','NO') NOT NULL default 'YES',
  PRIMARY KEY  (`FormID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `FRG_forms`
--

/*!40000 ALTER TABLE `FRG_forms` DISABLE KEYS */;
INSERT INTO `FRG_forms` (`FormID`,`ParentID`,`FormTitle`,`FormContent`,`IsActive`) VALUES 
 (3,0,'فرم های بانک انصار',NULL,'YES'),
 (4,3,'افتتاح حساب','<p><span style=\"font-size:12px\"><span style=\"font-family:b nazanin\">نام و نام خانواد گی : &nbsp;#1#&nbsp;</span></span></p>\n\n<p><span style=\"font-size:12px\"><span style=\"font-family:b nazanin\">لیست دارایی ها :</span></span></p>\n\n<p><span style=\"font-size:12px\"><span style=\"font-family:b nazanin\">&nbsp;#9#&nbsp;</span></span></p>\n\n<p><span style=\"font-size:12px\"><span style=\"font-family:b nazanin\">مبلغ درخواست : &nbsp;#13#&nbsp; ریال منتس منتب سمنت بسمنیت سمینت سمنیت سینمتب </span></span></p>\n\n<p><span style=\"font-size:12px\"><span style=\"font-family:b nazanin\">نس تبمنس مسنیت مسنیتب سمنیتب مسنیتب مسنیتب مسنیتب</span></span></p>\n\n<p><span style=\"font-size:12px\"><span style=\"font-family:b nazanin\">&nbsp;سمینبتسمینتب سنمیتب کسنمیتب </span></span></p>\n\n<p><span style=\"font-size:12px\"><span style=\"font-family:b nazanin\">سمنتب س تسنیت نست بن </span></span></p>\n\n<hr />\n<p><span style=\"font-size:12px\"><span style=\"font-family:b nazanin\">توضیحات : &nbsp;#14#&nbsp;</span></span></p>\n','YES'),
 (5,3,'افتتاح حساب (کپی)','<p><span style=\"font-size:12px\"><span style=\"font-family:b nazanin\">نام و نام خانواد گی : &nbsp;#1#&nbsp;</span></span></p>\n\n<p><span style=\"font-size:12px\"><span style=\"font-family:b nazanin\">لیست دارایی ها :</span></span></p>\n\n<p><span style=\"font-size:12px\"><span style=\"font-family:b nazanin\">&nbsp;#9#&nbsp;</span></span></p>\n','YES');
/*!40000 ALTER TABLE `FRG_forms` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
