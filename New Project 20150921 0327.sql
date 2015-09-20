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
-- Definition of table `BaseTypes`
--

DROP TABLE IF EXISTS `BaseTypes`;
CREATE TABLE `BaseTypes` (
  `TypeID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TypeDesc` varchar(45) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`TypeID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `BaseTypes`
--

/*!40000 ALTER TABLE `BaseTypes` DISABLE KEYS */;
INSERT INTO `BaseTypes` (`TypeID`,`TypeDesc`) VALUES 
 (1,'adsa');
/*!40000 ALTER TABLE `BaseTypes` ENABLE KEYS */;


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
  `QueryString` varchar(2000) COLLATE utf8_persian_ci DEFAULT NULL COMMENT 'query اجرا شده',
  PRIMARY KEY (`DataAuditID`)
) ENGINE=MyISAM AUTO_INCREMENT=131 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci COMMENT='اطلاعات ممیزی ';

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
 (100,1000,'FRW_menus',11,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-20 01:16:17','insert into FRW_menus(SystemID,ParentID,MenuDesc,ordering) values (\'4\',\'0\',\'فرمساز\',\'4\')'),
 (101,1000,'FRW_menus',12,NULL,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-20 01:16:59','insert into FRW_menus(SystemID,ParentID,MenuDesc,IsActive,ordering,MenuPath) values (\'4\',\'11\',\'مدیریت فرم ها\',\'YES\',\'1\',\'formGenerator/buildForm.php\')'),
 (102,1000,'FRW_access',0,1000,'ADD',1,'http://rtfund/framework/start.php?SystemID=1',NULL,'127.0.0.1','2015-09-20 01:17:20','insert into FRW_access(MenuID,PersonID,ViewFlag,AddFlag,EditFlag,RemoveFlag) values (\'12\',\'1000\',\'YES\',\'YES\',\'YES\',\'YES\')'),
 (103,1000,'FGR_forms',2,NULL,'ADD',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-20 01:29:19','insert into FGR_forms(FormName) values (\'sadasd\')'),
 (104,1000,'FGR_forms',3,NULL,'ADD',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-20 01:29:56','insert into FGR_forms(FormName) values (\'sadasd\')'),
 (105,1000,'FGR_forms',3,NULL,'DELETE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-20 01:43:29','delete from FGR_forms where  FormID=\'3\''),
 (106,1000,'FGR_forms',2,NULL,'DELETE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-20 01:44:03','delete from FGR_forms where  FormID=\'2\''),
 (107,1000,'FGR_forms',1,NULL,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-20 02:05:06','update FGR_forms set FormID=\'1\'ld00,FormName=\'1\'ld01,reference=null where  FormID=\'1\''),
 (108,1000,'FGR_forms',1,NULL,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-20 02:05:21','update FGR_forms set FormID=\'1\'ld00,FormName=\'1\'ld01,reference=null where  FormID=\'1\''),
 (109,1000,'FGR_forms',1,NULL,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-20 02:05:31','update FGR_forms set FormID=\'1\'ld00,FormName=\'1\'ld01,reference=null where  FormID=\'1\''),
 (110,1000,'FGR_forms',1,NULL,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-20 02:07:24','update FGR_forms set FormID=\'1\'ld00,FormName=\'1\'ld01,reference=null where  FormID=\'1\''),
 (111,1000,'FGR_forms',1,NULL,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-20 02:07:43','update FGR_forms set FormID=\'1\'ld00,FormName=\'1\'ld01,reference=null where  FormID=\'1\''),
 (112,1000,'FGR_forms',1,NULL,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-20 02:08:20','update FGR_forms set FormID=\'1\'ld00,FormName=\'1\'ld01,reference=null where  FormID=\'1\''),
 (113,1000,'FGR_forms',1,NULL,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-20 02:08:53','update FGR_forms set FormID=\'1\'ld00,FormName=\'1\'ld01,reference=null where  FormID=\'1\''),
 (114,1000,'FGR_forms',1,NULL,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-20 02:14:16','update FGR_forms set FormID=\'1\'ld00,FormName=\'1\'ld01,reference=null where  FormID=\'1\''),
 (115,1000,'FGR_forms',1,NULL,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-20 02:14:16','update FGR_forms set FormID=\'1\'ld00,FormName=\'1\'ld01,reference=null,FileInclude=\'1\'ld03 where  FormID=\'1\''),
 (116,1000,'FGR_FormElements',1,1,'ADD',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 01:26:42','insert into FGR_FormElements(FormID,ElTitle,ElType,ordering,width) values (\'1\',\'asdas\',\'textfield\',\'1\',\'50\')'),
 (117,1000,'FGR_FormElements',1,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 01:37:25','update FGR_FormElements set ElementID=\'1\',FormID=\'1\',ElTitle=\'مدت مرخصی\',ElType=\'textfield\',ElValue=null,RefField=null,ordering=\'1\',width=\'50\' where ElementID=\'1\''),
 (118,1000,'FGR_FormElements',1,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 01:37:34','update FGR_FormElements set ElementID=\'1\',FormID=\'1\',ElTitle=\'مدت مرخصی\',ElType=\'textfield\',ElValue=null,RefField=null,ordering=\'1\',width=\'50\' where ElementID=\'1\''),
 (119,1000,'FGR_FormElements',1,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 01:37:41','update FGR_FormElements set ElementID=\'1\',FormID=\'1\',ElTitle=\'مدت مرخصی\',ElType=\'textfield\',ElValue=null,RefField=null,TypeID=\'1\',ordering=\'1\',width=\'50\' where ElementID=\'1\''),
 (120,1000,'FGR_FormElements',1,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 02:49:08','update FGR_FormElements set ElementID=\'1\',FormID=\'1\',ElTitle=\'مدت مرخصی\',ElType=\'combo\',ElValue=null,RefField=null,TypeID=\'1\',ordering=\'1\',width=\'50\' where ElementID=\'1\''),
 (121,1000,'FGR_FormElements',1,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 02:51:26','update FGR_FormElements set ElementID=\'1\',FormID=\'1\',ElTitle=\'مدت مرخصی\',ElType=\'currencyfield\',RefField=null,TypeID=\'1\',ordering=\'1\',width=\'50\' where ElementID=\'1\''),
 (122,1000,'FGR_FormElements',1,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 02:51:42','update FGR_FormElements set ElementID=\'1\',FormID=\'1\',ElTitle=\'مدت مرخصی\',ElType=\'combo\',ElValue=null,RefField=null,ordering=\'1\',width=\'50\' where ElementID=\'1\''),
 (123,1000,'FGR_FormElements',1,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 02:52:54','update FGR_FormElements set ElementID=\'1\',FormID=\'1\',ElTitle=\'مدت مرخصی\',ElType=\'combo\',ElValue=null,RefField=null,TypeID=null,ordering=\'1\',width=\'50\' where ElementID=\'1\''),
 (124,1000,'FGR_FormElements',1,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 02:53:03','update FGR_FormElements set ElementID=\'1\',FormID=\'1\',ElTitle=\'مدت مرخصی\',ElType=\'combo\',ElValue=null,RefField=null,TypeID=\'1\',ordering=\'1\',width=\'50\' where ElementID=\'1\''),
 (125,1000,'FGR_FormElements',1,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 02:53:25','update FGR_FormElements set ElementID=\'1\',FormID=\'1\',ElTitle=\'مدت مرخصی\',ElType=\'numberfield\',RefField=null,TypeID=\'1\',ordering=\'1\',width=\'50\' where ElementID=\'1\''),
 (126,1000,'FGR_FormElements',1,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 02:53:49','update FGR_FormElements set ElementID=\'1\',FormID=\'1\',ElTitle=\'مدت مرخصی\',ElType=\'textfield\',RefField=null,TypeID=\'1\',ordering=\'1\',width=\'50\' where ElementID=\'1\''),
 (127,1000,'FGR_FormElements',1,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 02:55:23','update FGR_FormElements set ElementID=\'1\',FormID=\'1\',ElTitle=\'مدت مرخصی\',ElType=\'textfield\',ElValue=null,RefField=null,TypeID=\'0\',ordering=\'1\',width=\'50\' where ElementID=\'1\''),
 (128,1000,'FGR_FormElements',1,1,'UPDATE',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 02:55:39','update FGR_FormElements set ElementID=\'1\',FormID=\'1\',ElTitle=\'مدت مرخصی\',ElType=\'combo\',ElValue=\'1:2:3\',RefField=null,TypeID=\'0\',ordering=\'1\',width=\'50\' where ElementID=\'1\''),
 (129,1000,'FGR_FormElements',2,1,'ADD',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 02:56:17','insert into FGR_FormElements(FormID,ElTitle,ElType,TypeID,ordering) values (\'1\',\'سمتنبی سمین\',\'textfield\',\'0\',\'2\')'),
 (130,1000,'FGR_FormElements',3,1,'ADD',4,'http://rtfund/office/start.php?SystemID=4',NULL,'127.0.0.1','2015-09-21 03:03:15','insert into FGR_FormElements(FormID,ElTitle,ElType,TypeID,ordering) values (\'1\',\'شسیشس\',\'currencyfield\',\'0\',\'3\')');
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
-- Definition of table `FGR_forms`
--

DROP TABLE IF EXISTS `FGR_forms`;
CREATE TABLE `FGR_forms` (
  `FormID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `FormName` varchar(500) CHARACTER SET utf8 NOT NULL,
  `reference` varchar(45) DEFAULT NULL,
  `FileInclude` enum('YES','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`FormID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `FGR_forms`
--

/*!40000 ALTER TABLE `FGR_forms` DISABLE KEYS */;
INSERT INTO `FGR_forms` (`FormID`,`FormName`,`reference`,`FileInclude`) VALUES 
 (1,'فرم مرخصی',NULL,'YES');
/*!40000 ALTER TABLE `FGR_forms` ENABLE KEYS */;


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
  PRIMARY KEY (`MenuID`,`PersonID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `FRW_access`
--

/*!40000 ALTER TABLE `FRW_access` DISABLE KEYS */;
INSERT INTO `FRW_access` (`MenuID`,`PersonID`,`ViewFlag`,`AddFlag`,`EditFlag`,`RemoveFlag`) VALUES 
 (3,1000,'YES','YES','YES','YES'),
 (4,1000,'YES','YES','YES','YES'),
 (5,1000,'YES','YES','YES','NO'),
 (6,1000,'YES','YES','NO','YES'),
 (8,1000,'YES','YES','YES','YES'),
 (10,1000,'YES','YES','YES','YES'),
 (12,1000,'YES','YES','YES','YES');
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
  PRIMARY KEY (`MenuID`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `FRW_menus`
--

/*!40000 ALTER TABLE `FRW_menus` DISABLE KEYS */;
INSERT INTO `FRW_menus` (`SystemID`,`MenuID`,`ParentID`,`MenuDesc`,`IsActive`,`ordering`,`icon`,`MenuPath`) VALUES 
 (1,1,0,'مدیریت  سیستم ها','YES',1,NULL,NULL),
 (1,2,0,'مدیریت کاربران','YES',2,NULL,NULL),
 (1,3,1,'مدیریت سیستم ها','YES',1,NULL,'management/systems.php'),
 (1,4,1,'مدیریت منوها','YES',2,NULL,'management/menus.php'),
 (1,5,2,'دسترسی کاربران','YES',2,'access.gif','management/UserAccess.php'),
 (1,6,2,'کاربران','YES',1,'users.gif','management/users.php'),
 (2,7,0,'اطلاعات پایه','YES',NULL,NULL,NULL),
 (2,8,7,'مدیریت کد حساب','YES',1,NULL,'account'),
 (1,9,0,'اطلاعات پایه','YES',NULL,NULL,NULL),
 (1,10,9,'واحدهای سازمان','YES',1,'unit.png','unit/units.php'),
 (4,11,0,'فرمساز','YES',4,NULL,NULL),
 (4,12,11,'مدیریت فرم ها','YES',1,NULL,'formGenerator/buildForm.php');
/*!40000 ALTER TABLE `FRW_menus` ENABLE KEYS */;


--
-- Definition of table `FRW_persons`
--

DROP TABLE IF EXISTS `FRW_persons`;
CREATE TABLE `FRW_persons` (
  `PersonID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` varchar(200) NOT NULL,
  `UserPass` varchar(60) DEFAULT NULL,
  `fname` varchar(500) CHARACTER SET utf8 NOT NULL,
  `lname` varchar(500) CHARACTER SET utf8 NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  PRIMARY KEY (`PersonID`)
) ENGINE=InnoDB AUTO_INCREMENT=1002 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `FRW_persons`
--

/*!40000 ALTER TABLE `FRW_persons` DISABLE KEYS */;
INSERT INTO `FRW_persons` (`PersonID`,`UserID`,`UserPass`,`fname`,`lname`,`IsActive`) VALUES 
 (1000,'admin','$P$B8EFk1xg.sI1HQghqLPJ8Fv/uCtmLw0','شبنم','جعفرخانی','YES'),
 (1001,'mahdipour',NULL,'بهاره','مهدی پور','YES');
/*!40000 ALTER TABLE `FRW_persons` ENABLE KEYS */;


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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `FRW_systems`
--

/*!40000 ALTER TABLE `FRW_systems` DISABLE KEYS */;
INSERT INTO `FRW_systems` (`SystemID`,`SysName`,`SysPath`,`IsActive`,`SysIcon`) VALUES 
 (1,'سیستم مدیریت فریم ورک','framework','YES','framework.gif'),
 (2,'سیستم حسابداری ','accountancy','YES','accountancy.gif'),
 (4,'سیستم اتوماسیون اداری','office','YES','office.gif'),
 (5,'سیستم حضور و غیاب','rollcall','YES','rollcall.png');
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




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
