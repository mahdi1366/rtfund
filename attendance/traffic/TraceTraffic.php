<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.02
//-----------------------------

require_once '../header.inc.php';
require_once 'traffic.class.php';
require_once '../baseinfo/shift.class.php';
require_once inc_reportGenerator;

$admin = isset($_POST["admin"]) ? true : false;

if(isset($_REQUEST["showReport"]))
{
	ShowReport();
}

function ShowReport(){

	$StartDate = DateModules::shamsi_to_miladi($_POST["year"] . "-" . $_POST["month"] . "-01", "-");
	$EndDate = DateModules::shamsi_to_miladi($_POST["year"] . "-" . $_POST["month"] ."-" . DateModules::DaysOfMonth($_POST["year"] ,$_POST["month"]), "-");
	
	$holidays = ATN_holidays::Get(" AND TheDate between ? AND ? order by TheDate", array($StartDate, $EndDate));
	$holidayRecord = $holidays->fetch();
	
	$PersonID = $_SESSION["USER"]["PersonID"];
	$PersonID = !empty($_POST["PersonID"]) ? $_POST["PersonID"] : $PersonID;
	
	
	$query = "select * from (
		
			select 'normal' RecordType,'' ReqType, TrafficDate,TrafficTime,s.ShiftTitle,s.FromTime,s.ToTime
				,ExceptFromTime,ExceptToTime
			from ATN_traffic t
			left join ATN_PersonShifts ps on(ps.IsActive='YES' AND t.PersonID=ps.PersonID AND TrafficDate between FromDate AND ToDate)
			left join ATN_shifts s on(ps.ShiftID=s.ShiftID)
			where t.PersonID=:p AND TrafficDate>= :sd AND TrafficDate <= :ed 
			
			union All
			
			select 'start' RecordType,t.ReqType, t.FromDate,StartTime,s.ShiftTitle,s.FromTime,s.ToTime
				,ExceptFromTime,ExceptToTime
			from ATN_requests t
			left join ATN_PersonShifts ps on(ps.IsActive='YES' AND t.PersonID=ps.PersonID AND t.FromDate between ps.FromDate AND ps.ToDate)
			left join ATN_shifts s on(ps.ShiftID=s.ShiftID)
			where t.PersonID=:p AND t.ToDate is null AND ReqStatus=2 
			
			union all
			
			select 'end' RecordType,t.ReqType, t.FromDate,EndTime,s.ShiftTitle,s.FromTime,s.ToTime
				,ExceptFromTime,ExceptToTime
			from ATN_requests t
			left join ATN_PersonShifts ps on(ps.IsActive='YES' AND t.PersonID=ps.PersonID AND t.FromDate between ps.FromDate AND ps.ToDate)
			left join ATN_shifts s on(ps.ShiftID=s.ShiftID)
			where t.PersonID=:p AND t.ToDate is null AND ReqStatus=2 
		)t2
		order by  TrafficDate,TrafficTime";
	$dt = PdoDataAccess::runquery($query, array(":p" => $PersonID, ":sd" => $StartDate, ":ed" => $EndDate));
//print_r(ExceptionHandler::PopAllExceptions());
//print_r($dt);
	//........................ create days array ..................
	
	$index = 0;
	$returnArr = array();
	while($StartDate <= $EndDate)
	{
		if($index < count($dt) && $StartDate == $dt[$index]["TrafficDate"])
		{
			while($index < count($dt) && $StartDate == $dt[$index]["TrafficDate"])
				$returnArr[] = $dt[$index++];
			
			$StartDate = DateModules::AddToGDate($StartDate, 1);	
			continue;
		}
		
		$shiftRecord = ATN_PersonShifts::GetShiftOfDate($PersonID, $StartDate);

		$returnArr[] = array(
			"RecordType" => "normal",
			"TrafficID" => "", 
			"TrafficDate" => $StartDate , 
			"ShiftTitle" => $shiftRecord["ShiftTitle"], 
			"FromTime" => $shiftRecord["FromTime"], 
			"ToTime" => $shiftRecord["ToTime"], 
			"ExceptFromTime" => $shiftRecord["ExceptFromTime"], 
			"ExceptToTime" => $shiftRecord["ExceptToTime"], 
			"TrafficTime" => "");
		$StartDate = DateModules::AddToGDate($StartDate, 1);
	}
	//------------ holidays ------------------
	for($i=0; $i<count($returnArr); $i++)
	{
		$holiday = false;
		$holidayTitle = "تعطیل";
		if(FridayIsHoliday && DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") == "5")
			$holiday = true;
		if(ThursdayIsHoliday && DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") == "4")
			$holiday = true;

		if($holidayRecord && $holidayRecord["TheDate"] == $returnArr[$i]["TrafficDate"])
		{
			$holidayTitle .= $holidayRecord["details"] != "" ? "(" . $holidayRecord["details"] . ")" : "";
			$holiday = true;
			$holidayRecord = $holidays->fetch();
		}
		
		$returnArr[$i]["holiday"] = $holiday;
		$returnArr[$i]["holidayTitle"] = $holidayTitle;
	}
	//...........................................................
		
	function ShowTime($arr){
		
		if($arr[0] == "00" && $arr[1] == "00")
			return "";
		return $arr[0] . ":" . $arr[1];
	}
	
	$returnStr = "";
	$SUM = array(
		"absence" => 0,
		"attend"=> 0,
		"firstAbsence" => 0,
		"lastAbsence" => 0,
		"extra" => 0,
		"Off" => 0,
		"mission" => 0,
		"DailyOff_1" => 0,
		"DailyOff_2" => 0,
		"DailyOff_3" => 0,
		"DailyMission" => 0,
		"DailyAbsence" => 0
	);
	
	for($i=0; $i < count($returnArr); $i++)
	{
		if(!$returnArr[$i]["holiday"])
		{
			//........... Daily off and mission ...................
			$requests = PdoDataAccess::runquery("
				select t.*, InfoDesc OffTypeDesc from ATN_requests t
					left join BaseInfo on(TypeID=20 AND InfoID=OffType)
				where ReqStatus=2 AND PersonID=:p AND FromDate <= :td 
					AND if(ToDate is not null, ToDate >= :td, 1=1)
				order by ToDate desc,StartTime asc
			", array(
				":p" => $PersonID,
				":td" => $returnArr[$i]["TrafficDate"]
			));

			if(count($requests) > 0)
			{
				if($requests[0]["ReqType"] == "DayOFF")
				{
					$returnStr .= 
						"<td>" . DateModules::$JWeekDays[ DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") ] . "</td>
						<td>" . DateModules::miladi_to_shamsi($returnArr[$i]["TrafficDate"]) . "</td>
						<td colspan=8> مرخصی " . $requests[0]["OffTypeDesc"] . "<td></tr>";
					$SUM["DailyOff_" . $requests[0]["OffType"] ]++;

					$currentDay = $returnArr[$i]["TrafficDate"];
					while($i < count($returnArr) && $currentDay == $returnArr[$i]["TrafficDate"])
						$i++;
					$i--;
					continue;
				}
				if($requests[0]["ReqType"] == "DayMISSION")
				{
					$returnStr .= 
						"<td>" . DateModules::$JWeekDays[ DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") ] . "</td>
						<td>" . DateModules::miladi_to_shamsi($returnArr[$i]["TrafficDate"]) . "</td>
						<td colspan=8> ماموریت " . $requests[0]["MissionSubject"] . "<td></tr>";
					$SUM["DailyMission"]++;

					$currentDay = $returnArr[$i]["TrafficDate"];
					while($i < count($returnArr) && $currentDay == $returnArr[$i]["TrafficDate"])
						$i++;
					$i--;
					continue;
				}
			}
		}
		//....................................................
		if( DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "l") == "Thursday")
		{
			$returnArr[$i]["FromTime"] = $returnArr[$i]["ExceptFromTime"];
			$returnArr[$i]["ToTime"] = $returnArr[$i]["ExceptToTime"];
		}
		//....................................................
		
		$returnStr .= "<tr>
			<td>" . DateModules::$JWeekDays[ DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") ] . "</td>
			<td>" . DateModules::miladi_to_shamsi($returnArr[$i]["TrafficDate"]) . "</td>
			<td>" . ($returnArr[$i]["holiday"] ? $returnArr[$i]["holidayTitle"] : $returnArr[$i]["ShiftTitle"]) . "</td>
			<td>";
		
		$firstAbsence = 0;
		$Off = 0;	
		$mission = 0;
		$index = 1;
		$totalAttend = 0;
		
		if($returnArr[$i]["TrafficTime"] != "" && 
			strtotime($returnArr[$i]["TrafficTime"]) > strtotime($returnArr[$i]["FromTime"]))
				$firstAbsence = strtotime($returnArr[$i]["TrafficTime"]) - strtotime($returnArr[$i]["FromTime"]);

		$currentDay = $returnArr[$i]["TrafficDate"];
		$startOff = 0;
		$endOff = 0;
		while($i < count($returnArr) && $currentDay == $returnArr[$i]["TrafficDate"])
		{
			$returnStr .= substr($returnArr[$i]["TrafficTime"],0,5);
			$returnStr .= $index % 2 == 0 ? "<br>" : " - ";
			
			if($index % 2 == 0)
			{
				$totalAttend += strtotime($returnArr[$i]["TrafficTime"]) - strtotime($returnArr[$i-1]["TrafficTime"]);
			}	
			
			if($returnArr[$i]["RecordType"] != "normal")
			{
				if($i>0 && $returnArr[$i-1]["TrafficDate"] == $currentDay && $returnArr[$i]["RecordType"] == "start")
				{
					if($i == 0 || $returnArr[$i-1]["TrafficDate"] != $currentDay)
						$startDiff = 0;
					else
						$startDiff = strtotime($returnArr[$i]["TrafficTime"]) - strtotime($returnArr[$i-1]["TrafficTime"]);
					
					if($startDiff > Valid_Traffic_diff)
						$startOff = strtotime($returnArr[$i]["TrafficTime"]) - Valid_Traffic_diff;						
					else
						$startOff = strtotime($returnArr[$i-1]["TrafficTime"]);
				}
				if( ($i==0 || $returnArr[$i-1]["TrafficDate"] != $currentDay) && $returnArr[$i]["RecordType"] == "start")
				{
					$startOff = strtotime($returnArr[$i]["TrafficTime"]);
				}
				if($returnArr[$i]["RecordType"] == "end")
				{
					if($i == count($returnArr)-1 || $returnArr[$i+1]["TrafficDate"] != $currentDay)
						$endDiff = 0;
					else
						$endDiff = strtotime($returnArr[$i+1]["TrafficTime"]) - strtotime($returnArr[$i]["TrafficTime"]);
					
					if($endDiff > Valid_Traffic_diff)
						$endOff = strtotime($returnArr[$i]["TrafficTime"]) - Valid_Traffic_diff;						
					else
						$endOff = strtotime($returnArr[$i]["TrafficTime"]);
					
					if($returnArr[$i]["ReqType"] == "OFF")
						$Off += $endOff - $startOff;
					else
						$mission += $endOff - $startOff;
				}				
			}
			$index++;
			$i++;
		}
		$i--;
		
		$lastAbsence = 0;
		if($returnArr[$i]["TrafficTime"] != "" && 
			strtotime($returnArr[$i]["TrafficTime"]) < strtotime($returnArr[$i]["ToTime"]))
				$lastAbsence = strtotime($returnArr[$i]["ToTime"]) - strtotime($returnArr[$i]["TrafficTime"]);

		$ShiftDuration = strtotime($returnArr[$i]["ToTime"]) - strtotime($returnArr[$i]["FromTime"]);
		$extra = ($totalAttend > $ShiftDuration) ? $totalAttend - $ShiftDuration  : 0;
		
		$Absence = $totalAttend < $ShiftDuration ? $ShiftDuration - $totalAttend : 0;
		
		if($returnArr[$i]["holiday"])
		{
			$extra = $totalAttend + $mission;
			$lastAbsence = 0;
			$firstAbsence = 0;
			$Absence = 0;
			$Off = 0;
		}
		
		if($Absence == $ShiftDuration)
			$SUM["DailyAbsence"]++;
		
		$SUM["absence"] += $Absence;
		$SUM["attend"] += $totalAttend;
		$SUM["firstAbsence"] += $firstAbsence;
		$SUM["lastAbsence"] += $lastAbsence;
		$SUM["extra"] += $extra;
		$SUM["Off"] += $Off;
		$SUM["mission"] += $mission;		
		
		$totalAttend = TimeModules::SecondsToTime($totalAttend);
		$firstAbsence = TimeModules::SecondsToTime($firstAbsence);
		$lastAbsence = TimeModules::SecondsToTime($lastAbsence);
		$Absence = TimeModules::SecondsToTime($Absence);
		$extra = TimeModules::SecondsToTime($extra);
		$Off = TimeModules::SecondsToTime($Off);
		$mission = TimeModules::SecondsToTime($mission);
		
		$returnStr .= "</td><td class=attend>" . ShowTime($totalAttend) . "</td>
			<td class=extra>" . ShowTime($extra) . "</td>
			<td class=off>" . ShowTime($Off) . "</td>
			<td class=mission>" . ShowTime($mission) . "</td>
			<td class=sub>" . ShowTime($firstAbsence) . "</td>
			<td class=sub>" . ShowTime($lastAbsence) . "</td>
			<td class=sub>" . ShowTime($Absence) . "</td>
			</tr>";
	}
?>
<style>
	.reportTbl td {padding:4px;}
	.reportTbl th {padding:4px;text-align: center; background-color: #efefef; font-weight: bold}
	.reportTbl .attend { text-align:center}
	.reportTbl .extra { background-color: #D0F7E2; text-align:center}
	.reportTbl .off { background-color: #D7BAFF; text-align:center}
	.reportTbl .mission { text-align:center}
	.reportTbl .sub { background-color: #FFcfdd; text-align:center}
	.reportTbl .footer { background-color: #eee; text-align:center; line-height: 18px}
</style>
<table class="reportTbl" width="100%" border="1">
	<tr class="blueText">
		<th>روز</th>
		<th>تاریخ</th>
		<th>شیفت</th>
		<th>ورود/خروج</th>
		<th>حضور</th>
		<th class="extra">اضافه کار</th>
		<th class="off" >مرخصی</th>
		<th>ماموریت</th>
		<th class=sub>تاخیر</th>
		<th class=sub>تعجیل</th>
		<th class=sub>غیبت</th>
	</tr>
	<?= $returnStr ?>
	<tr class="footer">
		<?
			$SUM["absence"] = TimeModules::SecondsToTime($SUM["absence"]);
			$SUM["attend"] = TimeModules::SecondsToTime($SUM["attend"] );
			$SUM["firstAbsence"] = TimeModules::SecondsToTime($SUM["firstAbsence"]);
			$SUM["lastAbsence"] = TimeModules::SecondsToTime($SUM["lastAbsence"]);
			$SUM["extra"] = TimeModules::SecondsToTime($SUM["extra"]);
			$SUM["Off"] = TimeModules::SecondsToTime($SUM["Off"]);
			$SUM["mission"] = TimeModules::SecondsToTime($SUM["mission"]);
		?>
		<td colspan="4"></td>
		<td><?= ShowTime($SUM["attend"]) ?></td>
		<td><?= ShowTime($SUM["extra"]) ?></td>
		<td><?= ShowTime($SUM["Off"]) ?></td>
		<td><?= ShowTime($SUM["mission"]) ?></td>
		<td><?= ShowTime($SUM["firstAbsence"]) ?></td>
		<td><?= ShowTime($SUM["lastAbsence"]) ?></td>
		<td><?= ShowTime($SUM["absence"]) ?></td>
	</tr>
	<tr class="footer">
		<td colspan="4">مجموع عملکرد</td>
		<td colspan="3">	
			مجموع مرخصی استعلاجی : <?= $SUM["DailyOff_1"] ?><br>
			مجموع مرخصی استحقاقی : <?= $SUM["DailyOff_2"] ?><br>
			مجموع مرخصی بدون حقوق : <?= $SUM["DailyOff_3"] ?><br>
		</td>
		<td colspan="4">
			مجموع ماموریت روزانه : <?= $SUM["DailyMission"] ?><br>
			مجموع غیبت روزانه : <?= $SUM["DailyAbsence"]?><br>
		</td>
	</tr>
</table>
<?	
	die();
}
?>
<script>
TraceTraffic.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	DocID : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function TraceTraffic()
{
	this.mainPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		autoHeight : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش تردد",
		width : 800,
		items :[{
			xtype : "container",
			layout : "hbox",
			items :[{
				xtype : "combo",
				width : 300,
				fieldLabel : "انتخاب فرد",
				store: new Ext.data.Store({
					proxy:{
						type: 'jsonp',
						url: '/framework/person/persons.data.php?task=selectPersons&UserType=IsStaff',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields :  ['PersonID','fullname']
				}),
				displayField: 'fullname',
				hidden : <?= $admin ? "false" : "true" ?>,
				valueField : "PersonID",
				hiddenName : "PersonID"
			},{
				xtype : "combo",
				store: YearStore,   
				labelWidth : 30,
				width : 120,
				fieldLabel : "سال",
				displayField: 'title',
				valueField : "id",
				hiddenName : "year",
				value : '<?= substr(DateModules::shNow(),0,4) ?>'
			},{
				xtype : "combo",
				store: MonthStore,   
				labelWidth : 30,
				width : 120,
				fieldLabel : "ماه",
				displayField: 'title',
				valueField : "id",
				hiddenName : "month",
				value : '<?= substr(DateModules::shNow(),5,2)*1 ?>'
			},{
				xtype : "button",
				border : true,
				style : "margin-right:20px",
				text : "مشاهده گزارش",
				iconCls : "report",
				handler : function(){ TraceTrafficObj.LoadReport(); }
			}]
		},{
			xtype : "container",
			html : "<hr>",
			width : 780
		},{
			xtype : "container",
			colspan : 4,
			width : 780,
			itemId : "div_report"
		}]
	});
	
}

TraceTraffic.prototype.LoadReport = function(){
	
	mask = new Ext.LoadMask(this.mainPanel,{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'TraceTraffic.php?showReport=true',
		method: "POST",
		form : this.get("mainForm"),

		success: function(response){
			mask.hide();
			TraceTrafficObj.mainPanel.getComponent("div_report").update(response.responseText);
		},
		failure: function(){}
	});	

}

TraceTrafficObj = new TraceTraffic();


</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div><br>
	</center>
</form>