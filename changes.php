<?php

/*


ALTER TABLE `krrtfir_rtfund`.`LON_loans` ADD COLUMN `IsPlan` ENUM('YES','NO') NOT NULL DEFAULT 'NO' AFTER `IsCustomer`;
ALTER TABLE `krrtfir_rtfund`.`PLN_plans` ADD COLUMN `LoanID` INTEGER UNSIGNED NOT NULL AFTER `PlanDesc`;
ALTER TABLE `krrtfir_rtfund`.`PLN_plans` MODIFY COLUMN `LoanID` INTEGER UNSIGNED DEFAULT NULL;
ALTER TABLE `krrtfir_rtfund`.`PLN_plans` ADD COLUMN `SupportPersonID` INTEGER UNSIGNED AFTER `StatusID`;
ALTER TABLE `krrtfir_rtfund`.`WFM_FlowSteps` ADD COLUMN `IsOuter` ENUM('YES','NO') NOT NULL DEFAULT 'NO' AFTER `IsActive`;
insert into BaseInfo values(11, 3, 'طرح', '../plan/PlanInfo.php', 'PlanID', 'YES')
ALTER TABLE `krrtfir_rtfund`.`PLN_plans` CHANGE COLUMN `StatusID` `StepID` INTEGER UNSIGNED NOT NULL DEFAULT 101;
ALTER TABLE `krrtfir_rtfund`.`WFM_FlowRows` MODIFY COLUMN `ActionType` ENUM('CONFIRM','REJECT','DONE');
ALTER TABLE `krrtfir_rtfund`.`PLN_PlanSurvey` DROP COLUMN `StatusID`;
ALTER TABLE `krrtfir_rtfund`.`BSC_persons` ADD COLUMN `IsExpert` ENUM('YES','NO') NOT NULL DEFAULT 'NO' AFTER `IsSupporter`;
ALTER TABLE `krrtfir_rtfund`.`FRW_menus` MODIFY COLUMN `IsSupporter` ENUM('YES','NO') NOT NULL DEFAULT 'NO',
 ADD COLUMN `IsExpert` ENUM('YES','NO') NOT NULL DEFAULT 'NO' AFTER `IsSupporter`;

CREATE TABLE  `krrtfir_rtfund`.`PLN_experts` 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 

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
	merging($_POST["main"],$_POST["sub"]);
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