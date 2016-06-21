<?php
	include("../header.inc.php");
	include("PAS_shared_utils.php");
	ini_set("memory_limit","300M");
	ini_set("max_execution_time","360");
	
	HTMLBegin();
	$now = date("Ymd"); 
	$yy = substr($now,0,4); 
	$mm = substr($now,4,2); 
	$dd = substr($now,6,2);
	$CurrentDay = $yy."/".$mm."/".$dd;
	$AbsentCount = 0;
	list($dd,$mm,$yy) = ConvertX2SDate($dd,$mm,$yy);
	$yy = substr($yy, 2, 2);
	$CurYear = 1300+$yy;
	$CurMonth = $mm;
	if(isset($_REQUEST["SelectedYear"]))
	{	
		$SelectedYear = $_REQUEST["SelectedYear"];
		$SelectedMonth = $_REQUEST["SelectedMonth"];
		if($SelectedYear==$CurYear && $SelectedMonth==$CurMonth)
		{
			if($CurMonth<7)
				$differ = 31-$dd;
			else if($CurMonth<12)
				$differ = 30-$dd;
			else
				$differ = 29-$dd;
		}
		else
			$differ = 0;
		$AbsentCount = $_REQUEST["AbsentCount"];
		$mysql = dbclass::getInstance();
		
		$PersonList = "";
		$mysql = dbclass::getInstance();
		$query = "select PersonID, plname, pfname, PName, floor(sum(AbsentTime)/60) as AbsentCount 
					from pas.MonthlyCalculationSummary
					JOIN hrmstotal.persons using (PersonID)
					JOIN pas.PersonSettings using (PersonID)
					LEFT JOIN pas.Units on (PersonSettings.WorkUnitCode=Units.id) 
					where CalculatedYear='".$SelectedYear."' 
					group by PersonID order by sum(AbsentTime) DESC";
		$res = $mysql->Execute($query);
		$i = 0;
		
		while($arr_res=$res->FetchRow())
		{
			
				$i++;		
				if($i%2==0)
					$PersonList .= "<tr class=OddRow>";
				else
					$PersonList .= "<tr class=EvenRow>";
				$PersonList .= "<td nowrap width=30%>".$arr_res["PersonID"]."</td>";
				$PersonList .= "<td nowrap width=30%>".$arr_res["AbsentCount"]."</td>";
				$PersonList .= "</tr>";
			
		}
	}
	else
	{
		$SelectedYear = $CurYear;
		$SelectedMonth = $CurMonth;
	}
?>
<script>
	<?= PersiateKeyboard() ?>
</script>
<br>
<form method=post>
<table width=90% align=center border=1 cellspacing=0>
<tr class=HeaderOfTable>
	<td>
		سال: <input type=text size=3 maxlength=4 value='<?php echo $SelectedYear ?>' id=SelectedYear name=SelectedYear>
		ماه: <input type=text size=2 maxlength=2 value='<?php echo $SelectedMonth ?>' id=SelectedMonth name=SelectedMonth>
		حداقل میزان غیبت: <input type=text name=AbsentCount id=AbsentCount value='<?php echo $AbsentCount ?>'>
		<input type=submit value='مشاهده'>
	</td>
</tr>
</table>
</form>
<br>
<?php if(isset($_REQUEST["SelectedYear"])) { ?>
<table width=90% align=center border=1 cellspacing=0>
<tr>
<td>
	<table width=100% border=1 cellspacing=0 cellpadding=5>
	<tr class=HeaderOfTable>
		<td >نام و نام خانوادگی</td>
		<td >محل کار</td>
		<td >میزان غیبت در ماه (به روز)</td>
	</tr>
	<?php echo $PersonList ?>
	</table>
</td>
</tr>
</table>
<?php } ?>
</form>
<script>
	function CheckValidity()
	{
		if(f1.ActivePersonID.value=='0')
		{
			alert('لطفا یک شخص را انتخاب کنید');
			return;
		}

		FixNumbers();
		f1.submit();
	}
	
	function FixNumber(InputName)
	{
		obj = document.getElementById(InputName);
		if(obj.value.length<2)
		{
			obj.value = '0'+obj.value;
		}
	}
	function FixNumbers()
	{
		FixNumber("FromDay");
		FixNumber("FromMonth");
		FixNumber("ToDay");
		FixNumber("ToMonth");
	}
</script>

<?
	HTMLEnd();
?>
