<?php

/*










CREATE TABLE  `krrtfir_rtfund`.`ATN_traffic` (
  `TrafficID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PersonID` int(10) unsigned NOT NULL,
  `TrafficDate` date NOT NULL,
  `TrafficTime` time NOT NULL,
  `IsSystemic` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  PRIMARY KEY (`TrafficID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='تردد پرسنل';
	
CREATE TABLE  `krrtfir_rtfund`.`ATN_shifts` (
  `ShiftID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `FromTime` time NOT NULL,
  `ToTime` time NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL,
  PRIMARY KEY (`ShiftID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='شیفت های کاری';
	
CREATE TABLE  `krrtfir_rtfund`.`ATN_PersonShifts` (
  `RowID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PersonID` int(10) unsigned NOT NULL,
  `ShiftID` int(10) unsigned NOT NULL,
  `FromDate` date NOT NULL,
  `ToDate` date NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL,
  PRIMARY KEY (`RowID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='شیفت های پرسنل';
 * 
 * 
 * 

 * 
 */

require_once "framework/header.inc.php";
?>
<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">
<?
if(isset($_POST["submit"]))
{
	$arr = array(
array(1002,1473),array(
1017,1389),array(
1517,1873),array(
1886,1655),array(
1670,1888),array(
1714,1093),array(
1026,1541),array(
1236,1253),array(
1195,1647),array(
1476,1852),array(
1073,1514),array(
1052,1479),array(
1091,1527),array(
1034,1686),array(
1057,1543),array(
1059,1643),array(
1357,1037),array(
1062,1895),array(
1067,1658),array(
1707,1071),array(
1849,1394),array(
1349,1415),array(
1074,1395),array(
1075,1751),array(
1661,1078),array(
1125,1232),array(
1082,1669),array(
1072,1791),array(
1184,1220),array(
1217,1152),array(
1185,1198),array(
1739,1090),array(
1348,1417),array(
1483,1857),array(
1095,1577),array(
1954,1181),array(
1097,1554),array(
1850,1393),array(
1632,1099),array(
1101,1265),array(
1107,1676),array(
1301,1853),array(
1113,1763),array(
1416,1851),array(
1264,1361),array(
1406,1629),array(
1199,1552),array(
1414,1880),array(
1877,1403),array(
1157,1219),array(
1239,1519),array(
1151,1353),array(
1154,1218),array(
1188,1539),array(
1726,1757),array(
1193,1648),array(
1208,1735),array(
1240,1553),array(
1254,1439),array(
1441,1955),array(
1446,1568),array(
1323,1390),array(
1878,1470),array(
1890,1321),array(
1386,1859),array(
1366,1396),array(
1650,1883),array(1413,1724));
	
	foreach($arr as $row)
	{
		//merging($_POST["main"],$_POST["sub"]);
		merging($row[0],$row[1]);
	}
	
	
	
}

function merging($mainTafsiliCode,$subTafsiliCode){
	$dtmain = PdoDataAccess::runquery("select * from ACC_tafsilis where TafsiliCode=?",array($mainTafsiliCode));
	$dtsub = PdoDataAccess::runquery("select * from ACC_tafsilis where TafsiliCode=?",array($subTafsiliCode));
	
	if(count($dtmain) == 0 || count($dtsub) == 0)
	{
		echo "یکی از کد ها نا معتبر است";
	}
	else
	{
		echo $dtmain[0]["TafsiliDesc"] . "<br>" . $dtsub[0]["TafsiliDesc"] . "<br>";
		$TafsiliID1 = $dtmain[0]["TafsiliID"];
		$PersonID1 = $dtmain[0]["ObjectID"];
		$TafsiliID2 = $dtsub[0]["TafsiliID"];
		$PersonID2 = $dtsub[0]["ObjectID"];
		
		PdoDataAccess::runquery("update LON_requests set LoanPersonID=? where LoanPersonID=?", 
			array($PersonID1, $PersonID2));
		echo "update LON_requests : " . PdoDataAccess::AffectedRows() . "<br>";
		PdoDataAccess::runquery("delete from BSC_persons where PersonID=?", array($PersonID2));
		echo "delete BSC_persons : " . PdoDataAccess::AffectedRows() . "<br>";
		
		PdoDataAccess::runquery("update ACC_DocItems set TafsiliID=? where TafsiliID=?", 
			array($TafsiliID1, $TafsiliID2));
		echo "update ACC_DocItems : " . PdoDataAccess::AffectedRows() . "<br>";
		PdoDataAccess::runquery("update ACC_DocItems set TafsiliID2=? where TafsiliID2=?", 
			array($TafsiliID1, $TafsiliID2));		
		echo "update ACC_DocItems : " . PdoDataAccess::AffectedRows() . "<br>";
		PdoDataAccess::runquery("delete from ACC_tafsilis where TafsiliID=?", array($TafsiliID2));
		echo "delete ACC_tafsilis : " . PdoDataAccess::AffectedRows() . "<br>";
		
		print_r(ExceptionHandler::PopAllExceptions());
	}
}
?>

<form method="post">
	تفصیلی اصلی : 
	<input type="text" name="main">
	<br>
	تفصیلی دوم : 
	<input type="text" name="sub">
	<br>
	<input type="submit" name="submit">
</form>
</body>	