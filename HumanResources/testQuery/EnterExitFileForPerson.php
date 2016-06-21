<?php

include("../header.inc.php");
include("PAS_shared_utils.php");
HTMLBegin();
	$now = date("Ymd"); 
	$yy = substr($now,0,4); 
	$mm = substr($now,4,2); 
	$dd = substr($now,6,2);
	list($dd,$mm,$yy) = ConvertX2SDate($dd,$mm,$yy);
	if(strlen($mm)==1)
		$mm = "0".$mm;
	if(strlen($dd)==1)
		$dd = "0".$dd;
	$yy = substr($yy, 2, 2);
	$CurYear = $yy;
	$DefaultValue = "0";
	if(isset($_REQUEST["ActivePersonID"]))
		$DefaultValue = $_REQUEST["ActivePersonID"];
	$PersonList = PASUtils::CreateThisUnitPersonelOptions($DefaultValue);
	
	$mysql = dbclass::getInstance();

if(isset($_REQUEST["ActivePersonID"]))
{
	$res = $mysql->Execute("select * from ValidTardiness where UnitID='".$_SESSION["UserGroup"]."'");
	if($arr_res=$res->FetchRow())
	{
		$UnitValidTardiness = $arr_res["ValidTardinessMinutes"];
		$UnitValidHaste = $arr_res["ValidHasteMinutes"];
		$ValidFloat = $arr_res["ValidFloatMinutes"];
	}
	else
	{
		$UnitValidTardiness = 0;
		$UnitValidHaste = 0;
		$ValidFloat = 0;
	}
	
	$Year = $_REQUEST["CurYear"];
	$query = "select * from pas.ActivePersons 
											JOIN hrmstotal.staff using (PersonID)
										where ActivePersons.PersonID='".$_REQUEST["ActivePersonID"]."' ";
	$res = $mysql->Execute($query);
	$arr_res=$res->FetchRow();
	$PersonType = $arr_res["person_type"];
	$ValidTardiness = $UnitValidTardiness;
	$ValidHaste = $UnitValidHaste;
	$SelectedPersonID = $arr_res["PersonID"];
	$res2 = $mysql->Execute("select UsePersonValidTardiness, UsePersonValidHaste, ValidHaste, ValidTardiness from PersonSettings JOIN hrmstotal.staff using (PersonID) where PersonID='".$SelectedPersonID."'");
	if($arr_res2=$res2->FetchRow())
	{
		if($arr_res2["UsePersonValidTardiness"]=="1")
			$ValidTardiness = $arr_res2["ValidTardiness"];
		if($arr_res2["UsePersonValidHaste"]=="1")
			$ValidHaste = $arr_res2["ValidHaste"];
	}
	$ExtraworkColor = "#99ff99";
	$AbsentColor = "#ff9999";
	$HasteColor = "#ffaaaa";
	$TardinessColor = "#ffcccc";
	$LeaveColor = "#9999ff";
	
	echo "<br><table width=98% align=center cellpadding=5 cellspacing=0 border=1>";
	echo "<tr><td colspan='19'>پرونده حضور و غیاب سال : ".$CurYear." مربوط به ".$arr_res["PFName"]." ".$arr_res["PLName"];
	echo "</td></tr>";
	echo "<tr bgcolor=#cccccc>";
	echo "<td width=1% rowspan=3>ماه</td>";
	echo "<td align=center  colspan=4 bgcolor=".$AbsentColor.">غیبت</td>";
	echo "<td bgcolor=".$LeaveColor." colspan=3 align=center>مرخصی</td>";
	echo "<td rowspan=3>حضور</td>";		
	echo "<td colspan=2>ماموریت</td>";	
	echo "<td rowspan=3>حضور+ماموریت</td>";		
	echo "<td rowspan=3 bgcolor=".$ExtraworkColor.">اضافه کار</td>";
	echo "</tr>";
	echo "<tr bgcolor=#cccccc>";
	echo "<td rowspan=2 bgcolor=".$TardinessColor.">تاخیر</td>";
	echo "<td rowspan=2 bgcolor=".$TardinessColor.">تعجیل</td>";
	echo "<td rowspan=2 bgcolor=".$TardinessColor." nowrap>بین وقت</td>";
	echo "<td rowspan=2 bgcolor=".$AbsentColor.">مجموع</td>";
	echo "<td bgcolor=".$LeaveColor." colspan=2 align=center>روزانه</td>";
	echo "<td bgcolor=".$LeaveColor." rowspan=2>ساعتی</td>";
	echo "<td rowspan=2>روزانه</td>";
	echo "<td rowspan=2>ساعتی</td>";
	
	echo "</tr>";
	echo "<tr>";
	echo "<td bgcolor=".$LeaveColor." >استحقاقی</td>";
	echo "<td bgcolor=".$LeaveColor." >استعلاجی</td>";
	echo "</tr>";
	$TotalHaste = $TotalTardiness = $TotalAbsent = $TotalAllAbsent = 0;
	$TotalDailyOfficialLeaves = $TotalDailyCureLeaves = $TotalLeaveTime = 0;
	$TotalPresentTime = $TotalMissionDays = $TotalMission = $TotalAllPresent = 0;
	$TotalExtraWorkTime = 0;
	for($CurMonth=1; $CurMonth<13; $CurMonth++)
	{
		$FromMonth=$CurMonth;
		$ToMonth=$CurMonth;
		$FromDay = 1;
		$ToDay = 31;
		if($CurMonth>6)
			$ToDate=30;
		$FromDate = PASUtils::GetMiladiDate($Year+1300, $FromMonth, $FromDay);
		$ToDate = PASUtils::GetMiladiDate($Year+1300, $ToMonth, $ToDay);
		$FromDate2  = substr($FromDate, 4, 2)."/".substr($FromDate, 6, 2)."/".substr($FromDate, 0, 4);
		$ToDate2  = substr($ToDate, 4, 2)."/".substr($ToDate, 6, 2)."/".substr($ToDate, 0, 4);
		$DaysCount = (strtotime($ToDate2)-strtotime($FromDate2))/86400+1;
	
		unset($ret);
		$ret = PASUtils::CalculateAPersonSummaryStatusInARange(521, $FromDate, $ToDate, $ValidFloat, $ValidTardiness, $ValidHaste);

		if($CurMonth%2==0)
			echo "<tr class=OddRow>";
		else
			echo "<tr class=EvenRow>";
		echo "<td>".$CurMonth."</td>";
		echo "<td bgcolor=".$TardinessColor.">".PASUtils::ShowTimeInHourAndMinuteOrEmpty($ret["Tardiness"])."</td>";
		echo "<td bgcolor=".$TardinessColor.">".PASUtils::ShowTimeInHourAndMinuteOrEmpty($ret["Haste"])."</td>";		
		echo "<td bgcolor=".$TardinessColor.">".PASUtils::ShowTimeInHourAndMinuteOrEmpty($ret["Absent"])."</td>";
		echo "<td bgcolor=".$TardinessColor.">".PASUtils::ShowTimeInHourAndMinuteOrEmpty($ret["Haste"]+$ret["Tardiness"]+$ret["Absent"])."</td>";
		
		echo "<td bgcolor=".$LeaveColor." >".$ret["DailyOfficialLeaves"]."</td>";
		echo "<td bgcolor=".$LeaveColor." >".$ret["DailyCureLeaves"]."</td>";
		echo "<td bgcolor=".$LeaveColor." >".PASUtils::ShowTimeInHourAndMinuteOrEmpty($ret["LeaveTime"])."</td>";
		
		echo "<td>".PASUtils::ShowTimeInHourAndMinuteOrEmpty($ret["PresentTime"])."</td>";
		
		echo "<td>".$ret["MissionDays"]."</td>";
		echo "<td>".PASUtils::ShowTimeInHourAndMinuteOrEmpty($ret["Mission"])."</td>";
		
		echo "<td>".PASUtils::ShowTimeInHourAndMinuteOrEmpty($ret["Mission"]+$ret["PresentTime"]+($ret["MissionDays"]*450))."</td>";
		
		echo "<td  bgcolor=".$ExtraworkColor.">".PASUtils::ShowTimeInHourAndMinuteOrEmpty($ret["ExtraWorkTime"])."</td>";
		echo "</tr>";
		$TotalHaste += $ret["Haste"];
		$TotalTardiness += $ret["Tardiness"];
		$TotalAbsent += $ret["Absent"];
		$TotalAllAbsent += $ret["Tardiness"]+$ret["Haste"]+$ret["Absent"];
		$TotalDailyOfficialLeaves += $ret["DailyOfficialLeaves"];
		$TotalDailyCureLeaves += $ret["DailyOfficialLeaves"];
		$TotalLeaveTime += $ret["LeaveTime"];
		$TotalPresentTime += $ret["PresentTime"];
		$TotalMissionDays += $ret["MissionDays"];
		$TotalMission += $ret["Mission"];
		$TotalAllPresent += $ret["Mission"]+$ret["PresentTime"]+($ret["MissionDays"]*450);
		$TotalExtraWorkTime += $ret["ExtraWorkTime"];
	}	
	echo "<tr bgcolor=#cccccc>";
	echo "<td>مجموع</td>";
	echo "<td bgcolor=".$TardinessColor.">".PASUtils::ShowTimeInHourAndMinuteOrEmpty($TotalTardiness)."</td>";
	echo "<td bgcolor=".$TardinessColor.">".PASUtils::ShowTimeInHourAndMinuteOrEmpty($TotalHaste)."</td>";		
	echo "<td bgcolor=".$TardinessColor.">".PASUtils::ShowTimeInHourAndMinuteOrEmpty($TotalAbsent)."</td>";
	echo "<td bgcolor=".$TardinessColor.">".PASUtils::ShowTimeInHourAndMinuteOrEmpty($TotalAllAbsent)."</td>";
	
	echo "<td bgcolor=".$LeaveColor." >".$TotalDailyOfficialLeaves."</td>";
	echo "<td bgcolor=".$LeaveColor." >".$TotalDailyCureLeaves."</td>";
	echo "<td bgcolor=".$LeaveColor." >".PASUtils::ShowTimeInHourAndMinuteOrEmpty($TotalLeaveTime)."</td>";
	
	echo "<td>".PASUtils::ShowTimeInHourAndMinuteOrEmpty($TotalPresentTime)."</td>";
	
	echo "<td>".$TotalMissionDays."</td>";
	echo "<td>".PASUtils::ShowTimeInHourAndMinuteOrEmpty($TotalMission)."</td>";
	
	echo "<td>".PASUtils::ShowTimeInHourAndMinuteOrEmpty($TotalAllPresent)."</td>";
	
	echo "<td  bgcolor=".$ExtraworkColor.">".PASUtils::ShowTimeInHourAndMinuteOrEmpty($TotalExtraWorkTime)."</td>";
	
	echo "</tr>";
	echo "</table>";
	die();
}
?>
<br>
<form method="post" id=f1 name=f1>
<table border="1" cellspacing="0" align=center>
	<tr>
		<td>
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
				<tr>
					<td colspan="2" align="center" class="HeaderOfTable">نمایش پرونده حضور و غیاب فرد</td>
				</tr>
				<tr>
					<td>سال: </td>
					<td><input size=2 maxlength=2 type=text name=CurYear id=CurTYear value='<?php echo $CurYear ?>'></td>
				</tr>
				<tr>
					<td width="20%">نام فرد:</td>
					<td>
						<select name=ActivePersonID>
						<? echo $PersonList; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center" class="HeaderOfTable">
					<input type="submit" value="نمایش" name="btn_search" onclick=""></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br>

</body>
</html>