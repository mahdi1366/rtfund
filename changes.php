<?php

/*

insert into DMS_packages(BranchID,PackNo,PersonID)
select BranchID,@i:=@i+1,LoanPersonID from
(select BranchID,LoanPersonID
from LON_requests
where LoanPersonID > 0
group by LoanPersonID,BranchID
order by RequestID)t1,(select @i:=0)t2
;

insert into DMS_PackageItems(PackageID,ObjectType,ObjectID)
select PackageID,1,RequestID from LON_requests r
join DMS_packages p on(p.BranchID=r.BranchID AND r.LoanPersonID=p.PersonID)
where LoanPersonID > 0

 */

require_once "framework/header.inc.php";
?>
<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">
<?
if(isset($_POST["submit"]))
{
	merging($_POST["main"],$_POST["sub"]);
}

function merging($main,$sub){
	
	$dtmain = PdoDataAccess::runquery("select PersonID,concat_ws(' ',fname,lname,CompanyName) fullname from BSC_persons where PersonID=?",array($main));
	$dtsub = PdoDataAccess::runquery("select PersonID,concat_ws(' ',fname,lname,CompanyName) fullname from BSC_persons where PersonID=?",array($sub));
	
	if(count($dtmain) == 0 || count($dtsub) == 0)
	{
		echo "یکی از کد ها نا معتبر است";
	}
	else
	{
		$PersonID1 = $main; 
		$PersonID2 = $sub;
		echo $dtmain[0]["fullname"] . "<br>" . $dtsub[0]["fullname"] . "<br>";
				
		PdoDataAccess::runquery("update LON_requests set LoanPersonID=? where LoanPersonID=?", 	array($PersonID1, $PersonID2));
		echo "update LON_requests : " . PdoDataAccess::AffectedRows() . "<br>";
		PdoDataAccess::runquery("update BSC_OrgSigners set PersonID=? where PersonID=?", array($PersonID1, $PersonID2));
		echo "update BSC_OrgSigners : " . PdoDataAccess::AffectedRows() . "<br>";
		PdoDataAccess::runquery("update BSC_PersonExpertDomain set PersonID=? where PersonID=?", array($PersonID1, $PersonID2));
		echo "update BSC_PersonExpertDomain : " . PdoDataAccess::AffectedRows() . "<br>";
		PdoDataAccess::runquery("update BSC_licenses set PersonID=? where PersonID=?", array($PersonID1, $PersonID2));
		echo "update BSC_licenses : " . PdoDataAccess::AffectedRows() . "<br>";
		PdoDataAccess::runquery("update CNT_contracts set PersonID=? where PersonID=?", array($PersonID1, $PersonID2));
		echo "update CNT_contracts : " . PdoDataAccess::AffectedRows() . "<br>";
		PdoDataAccess::runquery("update DMS_packages set PersonID=? where PersonID=?", array($PersonID1, $PersonID2));
		echo "update DMS_packages : " . PdoDataAccess::AffectedRows() . "<br>";
		PdoDataAccess::runquery("update PLN_experts set PersonID=? where PersonID=?", array($PersonID1, $PersonID2));
		echo "update PLN_experts : " . PdoDataAccess::AffectedRows() . "<br>";
		PdoDataAccess::runquery("update PLN_plans set PersonID=? where PersonID=?", array($PersonID1, $PersonID2));
		echo "update PLN_plans : " . PdoDataAccess::AffectedRows() . "<br>";
		
		PdoDataAccess::runquery("update DMS_documents set ObjectID=? where ObjectType='person' AND ObjectID=?", array($PersonID1, $PersonID2));
		echo "update DMS_documents : " . PdoDataAccess::AffectedRows() . "<br>";
		
		PdoDataAccess::runquery("delete from BSC_persons where PersonID=?", array($PersonID2));
			echo "delete BSC_persons : " . PdoDataAccess::AffectedRows() . "<br>";

		
		$TafsiliID1 = PdoDataAccess::runquery("select * from ACC_tafsilis where TafsiliType=1 AND ObjectID=?",array($main));
		$TafsiliID2 = PdoDataAccess::runquery("select * from ACC_tafsilis where TafsiliType=1 AND ObjectID=?",array($sub));
		if(count($TafsiliID1) == 0 || count($TafsiliID2) == 0)
		{
			echo "یکی از کد ها فاقد تفصیلی است";
		}
		else
		{
			$TafsiliID1 = $TafsiliID1[0]["TafsiliID"];
			$TafsiliID2 = $TafsiliID2[0]["TafsiliID"];
			
			
			PdoDataAccess::runquery("update ACC_DocItems set TafsiliID=? where TafsiliID=?", 
				array($TafsiliID1, $TafsiliID2));
			echo "update ACC_DocItems : " . PdoDataAccess::AffectedRows() . "<br>";
			PdoDataAccess::runquery("update ACC_DocItems set TafsiliID2=? where TafsiliID2=?", 
				array($TafsiliID1, $TafsiliID2));		
			echo "update ACC_DocItems : " . PdoDataAccess::AffectedRows() . "<br>";
			PdoDataAccess::runquery("delete from ACC_tafsilis where TafsiliID=?", array($TafsiliID2));
			echo "delete ACC_tafsilis : " . PdoDataAccess::AffectedRows() . "<br>";
		}
		
		print_r(ExceptionHandler::PopAllExceptions());
	}
}
?>

<form method="post">
 کد پرسنلی اصلی : 
	<input type="text" name="main">
	<br>
	کد پرسنلی که باید در اصلی ادغام شود :
	<input type="text" name="sub">
	<br>
	<input type="submit" name="submit">
</form>
</body>	
