<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.02
//-----------------------------

require_once '../header.inc.php';
require_once '../traffic/traffic.class.php';
require_once '../baseinfo/shift.class.php';
require_once inc_reportGenerator;
require_once inc_dataGrid;

if(isset($_REQUEST["show"]))
{
	ShowReport();
}

function ShowTime($arr){

	if($arr[0] == "00" && $arr[1] == "00")
		return "";
	return $arr[0] . ":" . $arr[1];
}
		
function ShowReport(){
	
	if($_POST["FromDate"] == "")
	{
		$OrigStartDate = DateModules::shamsi_to_miladi($_POST["year"] . "-" . $_POST["month"] . "-01", "-");
		$OrigEndDate = DateModules::shamsi_to_miladi($_POST["year"] . "-" . $_POST["month"] ."-" . DateModules::DaysOfMonth($_POST["year"] ,$_POST["month"]), "-");
	}
	else
	{
		$OrigStartDate = DateModules::shamsi_to_miladi($_POST["FromDate"], "-");
		$OrigEndDate = DateModules::shamsi_to_miladi($_POST["ToDate"], "-");
	}
	$holidays = ATN_holidays::Get(" AND TheDate between ? AND ? order by TheDate", array($OrigStartDate, $OrigEndDate));
	$holidayIndex = 0;
	$holidays = $holidays->fetchAll();
	
	$where = "";
	$param = array();
	if(!empty($_POST["PersonID"]))
	{
		$where .= " AND PersonID = ?";
		$param[] = $_POST["PersonID"];
	}
	$PersonsDT = PdoDataAccess::runquery("select PersonID, concat(fname,' ',lname) fullname from BSC_persons
		where IsStaff='YES' " . $where, $param);
	
	$returnStr = "";
	foreach($PersonsDT as $personRecord)
	{
		$holidayIndex = 0;
		$holidayRecord = $holidayIndex < count($holidays) ? $holidays[$holidayIndex++] : null;
		
		$PersonID = $personRecord["PersonID"];
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
		
		$StartDate = $OrigStartDate;
		$EndDate = $OrigEndDate;
		
		$query = "select * from (

				select 'normal' RecordType,'' ReqType, TrafficDate,TrafficTime,s.ShiftTitle,s.FromTime,s.ToTime
					,ExceptFromTime,ExceptToTime
				from ATN_traffic t
				left join ATN_PersonShifts ps on(ps.IsActive='YES' AND t.PersonID=ps.PersonID AND TrafficDate between FromDate AND ToDate)
				left join ATN_shifts s on(ps.ShiftID=s.ShiftID)
				where t.IsActive='YES' AND t.PersonID=:p AND TrafficDate>= :sd AND TrafficDate <= :ed 

				union All

				select 'start' RecordType,t.ReqType, t.FromDate,StartTime,s.ShiftTitle,s.FromTime,s.ToTime
					,ExceptFromTime,ExceptToTime
				from ATN_requests t
				left join ATN_PersonShifts ps on(ps.IsActive='YES' AND t.PersonID=ps.PersonID AND t.FromDate between ps.FromDate AND ps.ToDate)
				left join ATN_shifts s on(ps.ShiftID=s.ShiftID)
				where t.PersonID=:p AND t.ToDate is null AND ReqStatus=2 AND t.FromDate>= :sd

				union all

				select 'end' RecordType,t.ReqType, t.FromDate,EndTime,s.ShiftTitle,s.FromTime,s.ToTime
					,ExceptFromTime,ExceptToTime
				from ATN_requests t
				left join ATN_PersonShifts ps on(ps.IsActive='YES' AND t.PersonID=ps.PersonID AND t.FromDate between ps.FromDate AND ps.ToDate)
				left join ATN_shifts s on(ps.ShiftID=s.ShiftID)
				where t.PersonID=:p AND t.ToDate is null AND ReqStatus=2 AND t.FromDate>= :sd
					AND EndTime is not null
			)t2
			order by  TrafficDate,TrafficTime";
		$dt = PdoDataAccess::runquery($query, array(":p" => $PersonID, 
			":sd" => $StartDate, ":ed" => $EndDate));
		
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
			if(FridayIsHoliday && DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") == "5")
				$holiday = true;
			if(ThursdayIsHoliday && DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") == "4")
				$holiday = true;

			if($holidayRecord && $holidayRecord["TheDate"] == $returnArr[$i]["TrafficDate"])
			{
				$holiday = true;
				$holidayRecord = $holidayIndex < count($holidays) ? $holidays[$holidayIndex++] : null;
			}

			$returnArr[$i]["holiday"] = $holiday;
		}
		//...........................................................

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
						$SUM["DailyOff_" . $requests[0]["OffType"] ]++;

						$currentDay = $returnArr[$i]["TrafficDate"];
						while($i < count($returnArr) && $currentDay == $returnArr[$i]["TrafficDate"])
							$i++;
						$i--;
						continue;
					}
					if($requests[0]["ReqType"] == "DayMISSION")
					{
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
			$extra = 0;
			while($i < count($returnArr) && $currentDay == $returnArr[$i]["TrafficDate"])
			{
				//....................................................
				if( DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "l") == "Thursday")
				{
					$returnArr[$i]["FromTime"] = $returnArr[$i]["ExceptFromTime"];
					$returnArr[$i]["ToTime"] = $returnArr[$i]["ExceptToTime"];
				}
				//....................................................

				if($index % 2 == 0)
				{
					$totalAttend += strtotime($returnArr[$i]["TrafficTime"]) - strtotime($returnArr[$i-1]["TrafficTime"]);

					if(strtotime($returnArr[$i]["TrafficTime"]) > strtotime($returnArr[$i]["ToTime"]))
					{
						if(strtotime($returnArr[$i-1]["TrafficTime"]) > strtotime($returnArr[$i]["ToTime"]))
							$extra += strtotime($returnArr[$i]["TrafficTime"]) - strtotime($returnArr[$i-1]["TrafficTime"]);
						else
							$extra += strtotime($returnArr[$i]["TrafficTime"]) - strtotime($returnArr[$i-1]["ToTime"]);
					}
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
			{
				$SUM["DailyAbsence"]++;
				$Absence = 0;
			}

			$SUM["absence"] += $Absence;
			$SUM["attend"] += $totalAttend;
			$SUM["firstAbsence"] += $firstAbsence;
			$SUM["lastAbsence"] += $lastAbsence;
			$SUM["extra"] += $extra;
			$SUM["Off"] += $Off;
			$SUM["mission"] += $mission;		
		}
		$SUM["absence"] = TimeModules::SecondsToTime($SUM["absence"]);
		$SUM["attend"] = TimeModules::SecondsToTime($SUM["attend"] );
		$SUM["firstAbsence"] = TimeModules::SecondsToTime($SUM["firstAbsence"]);
		$SUM["lastAbsence"] = TimeModules::SecondsToTime($SUM["lastAbsence"]);
		$SUM["extra"] = TimeModules::SecondsToTime($SUM["extra"]);
		$SUM["Off"] = TimeModules::SecondsToTime($SUM["Off"]);
		$SUM["mission"] = TimeModules::SecondsToTime($SUM["mission"]);
			
		$returnStr .= "<tr>
			<td>" . $personRecord["fullname"] . "</td>
			<td>" . ShowTime($SUM["attend"]) . "</td>
			<td>" . ShowTime($SUM["extra"]) . "</td>
			<td>" . ShowTime($SUM["Off"]) . "</td>
			<td>" . ShowTime($SUM["mission"]) . "</td>
			<td>" . ShowTime($SUM["firstAbsence"]) . "</td>
			<td>" . ShowTime($SUM["lastAbsence"]) . "</td>
			<td>" . ShowTime($SUM["absence"]) . "</td>
			
			<td>" . $SUM["DailyOff_1"] . "</td>
			<td>" . $SUM["DailyOff_2"] . "</td>
			<td>" . $SUM["DailyOff_3"] . "</td>
			<td>" . $SUM["DailyMission"] . "</td>
			<td>" . $SUM["DailyAbsence"] . "</td>
		</tr>";
	}
?>
<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">
<link rel="stylesheet" type="text/css" href="/generalUI/fonts/fonts.css" /></head>
<style>
	.reportTbl {border-collapse:collapse}
	.reportTbl td {padding:4px;font-family: nazanin; font-size:14px;}
	.reportTbl th {font-family: nazanin; font-size:14px;padding:4px;text-align: center; 
				  background-color: #efefef; font-weight: bold}
	.reportTbl .attend { text-align:center}
	.reportTbl .extra { background-color: #D0F7E2; text-align:center}
	.reportTbl .off { background-color: #D7BAFF; text-align:center}
	.reportTbl .mission { text-align:center}
	.reportTbl .sub { background-color: #FFcfdd; text-align:center}
	.reportTbl .footer { background-color: #eee; text-align:center; line-height: 18px}
</style>
<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'>
	<tr>
		<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
		<td align='center' style='height:100px;vertical-align:middle;font-family:b titr;font-size:15px'>
			گزارش خلاصه کارکرد پرسنل
			<br>از تاریخ <?= DateModules::miladi_to_shamsi($OrigStartDate) ?> تا تاریخ 
				<?= DateModules::miladi_to_shamsi($OrigEndDate) ?>
		</td>
		<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : 
			<?= DateModules::shNow() ?>
		</td>
	</tr>
</table>
<table class="reportTbl" width="100%" border="1">
	<tr class="blueText">
		<th>نام و نام خانوادگی</th>
		<th>حضور</th>
		<th>اضافه کار</th>
		<th>مرخصی</th>
		<th>ماموریت</th>
		<th>تاخیر</th>
		<th>تعجیل</th>
		<th>غیبت</th>
		<th>مرخصی استعلاجی</th>
		<th>مرخصی استحقاقی</th>
		<th>مرخصی بدون حقوق</th>
		<th>ماموریت روزانه</th>
		<th>غیبت روزانه</th>
	</tr>
	<?= $returnStr ?>
</table>
<?	
	die();
}

?>
<script>
ATN_SummaryReport.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",


	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function ATN_SummaryReport()
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
			layout : "column",
			columns : 4,
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
				handler : function(){ ATN_SummaryReportObj.LoadReport(); }
			},{
				xtype : "shdatefield",
				name : "FromDate",
				fieldLabel : "از تاریخ"
			},{
				xtype : "shdatefield",
				name : "ToDate",
				fieldLabel : "تا تاریخ"
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

ATN_SummaryReport.prototype.LoadReport = function(){
	
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "summary.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;

}

ATN_SummaryReportObj = new ATN_SummaryReport();

ATN_SummaryReport.DeleteRender = function(v,p,r)
{
	if(r.data.TrafficID == null)
	{
		return r.data.ReqType == "MISSION" ? "ماموریت"  : "مرخصی";
	}
	if(r.data.IsActive == "YES" )
		return "<div align='center' title='حذف' class='remove' "+
		"onclick='ATN_SummaryReportObj.DeleteTraffic();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;float:left;height:16'></div>";
}

ATN_SummaryReport.prototype.TrafficList = function(TrafficDate){
	
	this.grid.getStore().proxy.extraParams.TrafficDate = TrafficDate;
	this.grid.getStore().proxy.extraParams.PersonID = this.mainPanel.down("[hiddenName=PersonID]").getValue();
	if(!this.TraffficWin)
	{
		this.TraffficWin = new Ext.window.Window({
			width : 310,
			height : 290,
			modal : true,
			bodyStyle : "background-color:white",
			items : this.grid,
			closeAction : "hide",
			buttons : [{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.TraffficWin);
	}
	else
		this.grid.getStore().load();
	
	this.TraffficWin.show();
	this.TraffficWin.center();

}

ATN_SummaryReport.prototype.DeleteTraffic = function()
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = ATN_SummaryReportObj;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'traffic.data.php',
			params:{
				task: "DeleteTraffic",
				TrafficID : record.data.TrafficID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				ATN_SummaryReportObj.grid.getStore().load();
				ATN_SummaryReportObj.LoadReport();
			},
			failure: function(){}
		});
	});
}

</script>
<style>
	.link{
		cursor: pointer;
		color : blue;
	}
</style>
<form id="mainForm">
	<center><br>
		<div id="main" ></div><br>
	</center>
</form>
