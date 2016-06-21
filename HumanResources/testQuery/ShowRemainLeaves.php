<?php
	include("../header.inc.php");
	include("PAS_shared_utils.php");
	include("../../organization/classes/ChartServices.class.php");
	ini_set("memory_limit","100M");
	ini_set("max_execution_time","3600000000");
	
	function CalculateLeavesForPerson($SelectedPersonID, $Year)
	{
		$now = date("Ymd"); 
		$yy = substr($now,0,4); 
		$mm = substr($now,4,2); 
		$dd = substr($now,6,2);
		$CurrentDay = $yy."/".$mm."/".$dd;
		list($dd,$mm,$yy) = ConvertX2SDate($dd,$mm,$yy);
		$yy = substr($yy, 2, 2);
		$CurYear = 1300+$yy;
			
		$ValidHaste = $ValidTardiness = $ValidFloat = 0;
		$mysql = dbclass::getInstance();
		$res = $mysql->Execute("select * from pas.ValidTardiness JOIN pas.PersonSettings on (ValidTardiness.UnitID=PersonSettings.WorkUnitCode) where PersonID='".$SelectedPersonID."'");
		if($rec = $res->FetchRow())
		{
			if($rec["ValidTardinessMinutes"]!="")
			{
				$ValidTardiness = $rec["ValidTardinessMinutes"];
				$ValidHaste = $rec["ValidHasteMinutes"];
				$ValidFloat = $rec["ValidFloatMinutes"];
			}
			if($rec["UsePersonValidTardiness"]=="1")
				$ValidTardiness = $rec["ValidTardiness"];
			if($rec["UsePersonValidHaste"]=="1")
				$ValidHaste = $rec["ValidHaste"];
		}
		$TotalOfficial = $TotalCure = $TotalLeaveTime = $TotalAbsent = 0;
		for($Month=1; $Month<13; $Month++)
		{
			// ماه هایی که هنوز نیامده نیاز به محاسبه ندارد
			if($Year<$CurYear || $Month<$mm+1)
			{
				// در صورتیکه نتیجه محاسبات از قبل موجود بود از آن استفاده می کند
				$tmp = $mysql->Execute("select * from pas.MonthlyCalculationSummary where CalculatedYear='".$Year."' and CalculatedMonth='".$Month."' and PersonID='".$SelectedPersonID."'");
				if($t_rec = $tmp->FetchRow())
				{
					$ret["Haste"] = $t_rec["HasteTime"];
					$ret["Tardiness"] = $t_rec["TardinessTime"];
					$ret["Absent"] = $t_rec["AbsentTime"];
					$ret["ExtraWorkTime"] = $t_rec["ExtraWorkTime"];
					$ret["LeaveTime"] = $t_rec["LeaveTime"];
					$ret["Mission"] = $t_rec["MissionTime"];
					$ret["PresentTime"] = $t_rec["PresentTime"];
					$ret["MissionDays"] = $t_rec["MissionDays"];
					$ret["DailyOfficialLeaves"] = $t_rec["DailyOfficialLeaves"];
					$ret["DailyCureLeaves"] = $t_rec["DailyCureLeaves"];
				}
				else
				{
					$ret = PASUtils::CalculateAPersonSummaryStatusInAMonth($SelectedPersonID, $Year, $Month, $ValidFloat, $ValidTardiness, $ValidHaste);
					// ذخیره نتایج در خلاصه محاسبات ذخیره شده
								// قبل از ذخیره کردن یکبار دیگر چک می کند که داده در دیتابیس درج نشده باشد
					$tmp = $mysql->Execute("select * from pas.MonthlyCalculationSummary where CalculatedYear='".$Year."' and CalculatedMonth='".$Month."' and PersonID='".$SelectedPersonID."'");
					if(!($t_rec = $tmp->FetchRow()))
					{
					
						$query = "insert into pas.MonthlyCalculationSummary (CalculatedYear, CalculatedMonth, PersonID, PresentTime, WorkTime, TardinessTime, HasteTime, AbsentTime, ExtraWorkTime, LeaveTime, MissionTime, MissionDays, DailyOfficialLeaves, DailyCureLeaves) values (";
						$query .= "'".$Year."', '".$Month."', '".$SelectedPersonID."', ";
						$query .= "'".$ret["PresentTime"]."', ";
						$query .= "'".$ret["PresentTime"]."', ";
						$query .= "'".$ret["Tardiness"]."', ";
						$query .= "'".$ret["Haste"]."', ";
						$query .= "'".$ret["Absent"]."', ";
						$query .= "'".$ret["ExtraWorkTime"]."', ";
						$query .= "'".$ret["LeaveTime"]."', ";
						$query .= "'".$ret["Mission"]."', ";
						$query .= "'".$ret["MissionDays"]."', ";
						$query .= "'".$ret["DailyOfficialLeaves"]."', ";
						$query .= "'".$ret["DailyCureLeaves"]."') ";
						$mysql->Execute($query);
					}
				}
				$TotalOfficial += $ret["DailyOfficialLeaves"];
				$TotalCure += $ret["DailyCureLeaves"];
				$TotalLeaveTime += $ret["LeaveTime"];
				$TotalAbsent += $ret["Absent"]+$ret["Tardiness"]+$ret["Haste"];
			}
		}
		$res = array();
		$res["Official"] = $TotalOfficial;
		$res["Cure"] = $TotalCure;
		$res["LeaveTime"] = $TotalLeaveTime;
		$res["Absent"] = $TotalAbsent;
		return $res;
	}
	
	function ShowLeaveReport($SelectedPersonID, $Year)
	{
		$JobStatus = "OFFICIAL";
		$ValidHaste = $ValidTardiness = $ValidFloat = 0;
		$mysql = dbclass::getInstance();
		$res = $mysql->Execute("select * from pas.ValidTardiness 
								JOIN pas.PersonSettings on (ValidTardiness.UnitID=PersonSettings.WorkUnitCode)
								JOIN pas.EnterExitTypes using (EnterExitTypeID)  
								where PersonID='".$SelectedPersonID."'");
		if($rec = $res->FetchRow())
		{
			if($rec["ValidTardinessMinutes"]!="")
			{
				$ValidTardiness = $rec["ValidTardinessMinutes"];
				$ValidHaste = $rec["ValidHasteMinutes"];
				$ValidFloat = $rec["ValidFloatMinutes"];
			}
			if($rec["UsePersonValidTardiness"]=="1")
				$ValidTardiness = $rec["ValidTardiness"];
			if($rec["UsePersonValidHaste"]=="1")
				$ValidHaste = $rec["ValidHaste"];
			$JobStatus = $rec["JobStatus"];
		}
		
		$TotalAbsent = 0;
		for($Month=1; $Month<13; $Month++)
		{
			$ret = PASUtils::CalculateAPersonSummaryStatusInAMonth($SelectedPersonID, $Year, $Month, $ValidFloat, $ValidTardiness, $ValidHaste);
			$TotalAbsent += $ret["Absent"]+$ret["Haste"]+$ret["Tardiness"];
		}
		echo "<tr>";
		echo "<td>".$SelectedPersonID."</td>";
		echo "<td>".PASUtils::ShowTimeInHourAndMinuteOrEmpty($TotalAbsent)."</td>";
		echo "</tr>";
		
	}
	
	$mysql = dbclass::getInstance();
	
	//.................
	$tmp = $mysql->Execute("select * from hrmstotal.persons where person_type in (2,3,5) and personid = 521 ");
	echo "<table width=50% align=center cellspacing=0 cellpadding=5 border=1>";	
		
while($rec = $tmp->FetchRow())
{ 
		$SelectedPersonID = $rec["PersonID"] ; //401366284 ; 
		$CurYear = 1390;
		ShowLeaveReport($SelectedPersonID, $CurYear);
}
echo "</table>" ; 
die() ; 




	if(isset($_REQUEST["SelectedYear"]))
		$CurYear = $_REQUEST["SelectedYear"];
	
	if($_REQUEST["SelectedGroup"]<1000000)
	{
			// کاربر یک گروه را انتخاب کرده است
		if($_REQUEST["SelectedGroup"]>0)
		{
			$SelectedUnitCode = 0;
			/*
			$query = "select * from pas.WorkGroups 
							INNER JOIN pas.WorkGroupManagers using (WorkGroupID) 
							LEFT JOIN pas.ValidTardiness on (UnitID=WorkUnitCode) 
							where PersonID='".$_SESSION["PersonID"]."' and WorkGroupID='".$_REQUEST["SelectedGroup"]."'";
			*/
			$query = "select distinct persons.PersonID, JobStatus, pfname, plname, remain, CurrentYearRemain from hrmstotal.persons
			JOIN pas.PersonSettings using (PersonID)
			JOIN pas.EnterExitTypes using (EnterExitTypeID)
			JOIN hrmstotal.staff using (PersonID, person_type) 
			LEFT JOIN pas.ValidTardiness on (UnitID=UnitCode) 
			LEFT JOIN pas.RemainLeaves using (PersonID) 
			where PersonSettings.WorkGroupID='".$_REQUEST["SelectedGroup"]."' and CardStatus='ENABLE' order by plname";
			
		}
		else
		{
			// کاربر افراد زیر مجموعه اش را انتخاب کرده است
			$SelectedUnitCode = -1;
			$ex = "(0";
			// لیست کلیه افراد زیر مجموعه را درون پرانتز قرار می دهد
			for($i=0; $i<count($Childs); $i++)
			{
				$ex .= ",";
				$ex .= $Childs[$i]->PersonID;
			}
			// در صورتیکه فرد نایب افرادی باشد زیر مجموعه آنها را هم در نظر میگیرد
			$tmp = $mysql->Execute("select * from pas.PersonProxy where ProxyID='".$_SESSION["PersonID"]."'");
			while($rec = $tmp->FetchRow())
			{
				$Childs2 = ChartServices::GetAllChildsOfPerson(1, $rec["PersonID"]);
				for($i=0; $i<count($Childs2); $i++)
				{
					$ex .= ",";
					$ex .= $Childs2[$i]->PersonID;
				}
			}
			$ex .= ")";
			$query = "select distinct persons.PersonID, JobStatus, pfname, plname, remain, CurrentYearRemain from hrmstotal.persons
						JOIN pas.PersonSettings using (PersonID)
						JOIN pas.EnterExitTypes using (EnterExitTypeID)
						JOIN hrmstotal.staff using (PersonID, person_type) 
						LEFT JOIN pas.ValidTardiness on (UnitID=UnitCode) 
						LEFT JOIN pas.RemainLeaves using (PersonID) 
						where persons.PersonID in ".$ex." order by plname";
		}
	}
	else // کاربر تمام افراد یک واحد را انتخاب کرده است
	{
		$SelectedUnitCode = $_REQUEST["SelectedGroup"]-1000000;
		$query = "select * from pas.PermittedUsersForReporting where UnitCode='".$SelectedUnitCode."' and PersonID='".$_SESSION["PersonID"]."'";
		$tmp = $mysql->Execute($query);
		if($rec = $tmp->FetchRow())
		{
			$query = "select * from pas.PersonSettings 
						JOIN pas.EnterExitTypes using (EnterExitTypeID)
						JOIN hrmstotal.persons using (PersonID)
						JOIN hrmstotal.staff using (PersonID, person_type) 
						LEFT JOIN pas.ValidTardiness on (UnitID=UnitCode) 
						LEFT JOIN pas.RemainLeaves using (PersonID) 
						where WorkUnitCode='".$SelectedUnitCode."' and CardStatus='ENABLE'";
					//echo $query;
		}
		else
		{
			// کاربر به گروه انتخاب شده یا واحد انتخاب شده دسترسی نداشته است
			echo ":)";
			die();
		}
	}
	//if($_SESSION["PersonID"]=="201309")
	//	echo $query;
	/*
	$WorkGroupID = $_REQUEST["SelectedGroup"];
	
	$mysql = dbclass::getInstance();
	
	$PersonList = "";
	
	$UserGroup = $_SESSION["UserGroup"];
	$query = "select persons.PersonID, plname, pfname, remain, CurrentYearRemain
								from hrmstotal.persons 
								JOIN pas.PersonSettings using (PersonID)
								LEFT JOIN pas.RemainLeaves using (PersonID) 
								where CardStatus='ENABLE' and ";
	if($SelectedUnitCode==0)
		$query .= " WorkGroupID='".$WorkGroupID."' ";
	else
		$query .= " WorkUnitCode='".$SelectedUnitCode."' ";
	$query .= " order by plname, pfname";
	if($_SESSION["UserID"]=="omid")
		echo $query;
	$res2 = $mysql->Execute($query);
	*/

	
	$i = 0;
	$PersonList = "";
	$res = $mysql->Execute($query);
	while($arr_res = $res->FetchRow())
	{
		$JobStatus = $arr_res["JobStatus"];
		$cres = CalculateLeavesForPerson($arr_res["PersonID"], $CurYear);
		if($JobStatus=="SHIFT") // اگر شخص شیفتی باشد مرخصیهای شیفتی تبدیل به ساعتی شده اورا هم به مرخصی ساعتی اضافه می کند
		{
			$tmp = $mysql->Execute("select sum(EquivalentLeaveMinute) from pas.ShiftLeaves where PersonID='".$arr_res["PersonID"]."' and SelectedYear='".$CurYear."'");
			if($tmrec = $tmp->FetchRow())
			{
				$cres["LeaveTime"] += $tmrec[0];
			}
		}
		
		$i++;
		if($i%2==0)
			$PersonList .= "<tr class=OddRow>";
		else
			$PersonList .= "<tr class=EvenRow>";
		$PersonList .= "<td nowrap width=10%><a href='ShowRemainLeaves.php?SelectedPersonID=".$arr_res["PersonID"]."'>".$arr_res["plname"]." ".$arr_res["pfname"]."</td>";
		$remain = $arr_res["remain"]+$arr_res["CurrentYearRemain"];
		if($remain=="")
			$remain = "0";
		$PersonList .= "<td dir=ltr>".$cres["Official"]."</td>";
		$PersonList .= "<td dir=ltr>".PASUtils::ShowTimeInHourAndMinuteOrEmpty($cres["LeaveTime"])."</td>";
		$PersonList .= "<td dir=ltr>".($cres["Official"]+floor($cres["LeaveTime"]/HOURLY_LEAVES_EQUAL_ONE_DAY))."</td>";
		$PersonList .= "<td dir=ltr>".$cres["Cure"]."</td>";
		$PersonList .= "<td dir=ltr>".$remain."</td>";		
		
		$PersonList .= "<td dir=ltr>".($remain-($cres["Official"]+floor($cres["LeaveTime"]/HOURLY_LEAVES_EQUAL_ONE_DAY)+floor($cres["Absent"]/HOURLY_LEAVES_EQUAL_ONE_DAY)))."</td>";
		$PersonList .= "<td dir=ltr>".PASUtils::ShowTimeInHourAndMinuteOrEmpty($cres["Absent"])."</td>";
		$PersonList .= "</tr>";
	}
	
?>

<br>
<form method=post id=f1 name=f1>
<input type=hidden name=SelectedGroup id=SelectedGroup value='<?php echo $_REQUEST["SelectedGroup"]; ?>'>
		
<table width=60% align=center border=1 cellspacing=0>
<tr>
<td>
	<table width=100% cellpadding=5 border=1 cellspacing=0>
	<tr class=HeaderOfTable>
		<td rowspan=2>نام و نام خانوادگی</td>
		<td align=center colspan=3 width=1% nowrap>مرخصیهای استحقاقی مصرف شده در سال 
		<select name=SelectedYear id=SelectedYear onchange='javascript: document.f1.submit();'>
			<option value='1388'>1388
			<option value='1389' <?php if($CurYear=="1389") echo "selected"; ?> >1389
			<option value='1390' <?php if($CurYear=="1390") echo "selected"; ?> >1390
			<option value='1391' <?php if($CurYear=="1391") echo "selected"; ?> >1391
			<option value='1392' <?php if($CurYear=="1392") echo "selected"; ?> >1392
		</select>
		</td>
		<td rowspan=2 width=1% nowrap>مرخصی استعلاجی</td>
		<td rowspan=2 width=1% nowrap>مانده اولیه</td>
		<td rowspan=2 width=1% nowrap>مانده مرخصی تاکنون</td>
		<td rowspan=2 width=1% nowrap>مجموع غیبت - تاخیر - تعجیل</td>
	</tr>
	<tr class=HeaderOfTable>
		<td width=1%> روزانه</td><td width=1% nowrap>ساعتی </td><td width=1%>مجموع (روز)</td>	
	</tr>
	<?php echo $PersonList ?>
	</table>
</td>
</tr>
</table>

</form>
<?
	HTMLEnd();
?>
