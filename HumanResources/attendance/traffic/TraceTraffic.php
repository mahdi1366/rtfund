<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.02
//-----------------------------

require_once '../../header.inc.php';
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
	
	$PersonID = $_SESSION["USER"]["PersonID"];
	$PersonID = !empty($_POST["PersonID"]) ? $_POST["PersonID"] : $PersonID;
	
	$dt = ATN_traffic::Get(" AND t.PersonID=? AND TrafficDate>= ? AND TrafficDate <= ? 
		order by TrafficDate,TrafficTime", 
		array($PersonID, $StartDate, $EndDate));
	$dt = $dt->fetchAll();
	
	//........................ create days array ..................
	$returnArr = array();
	while($StartDate < $EndDate)
	{
		if(count($dt)>0 && $StartDate == $dt[0]["TrafficDate"])
			break;
		
		$shiftRecord = ATN_PersonShifts::GetShiftOfDate($PersonID, $StartDate);

		$returnArr[] = array("TrafficID" => "", 
			"TrafficDate" => $StartDate , 
			"ShiftTitle" => $shiftRecord["ShiftTitle"], 
			"FromTime" => $shiftRecord["FromTime"], 
			"ToTime" => $shiftRecord["ToTime"], 
			"TrafficTime" => "");
		$StartDate = DateModules::AddToGDate($StartDate, 1);
	}
	
	$returnArr = array_merge($returnArr, $dt);
	$StartDate = count($dt) > 0 ? $dt[ count($dt)-1 ]["TrafficDate"] : $StartDate;
	$StartDate = DateModules::AddToGDate($StartDate, 1);
	
	while($StartDate <= $EndDate)
	{
		$shiftRecord = ATN_PersonShifts::GetShiftOfDate($PersonID, $StartDate);
		
		$returnArr[] = array("TrafficID" => "", 
			"TrafficDate" => $StartDate , 
			"ShiftTitle" => $shiftRecord["ShiftTitle"], 
			"FromTime" => $shiftRecord["FromTime"], 
			"ToTime" => $shiftRecord["ToTime"], 
			"TrafficTime" => "");;
		$StartDate = DateModules::AddToGDate($StartDate, 1);
	}	
	//...........................................................
		
	function ShowTime($arr){
		
		if($arr[0] == "00" && $arr[1] == "00")
			return "";
		return $arr[0] . ":" . $arr[1];
	}
	
	$returnStr = "";
	for($i=0; $i < count($returnArr); $i++)
	{
		$requests = PdoDataAccess::runquery("
			select t.*, InfoDesc OffTypeDesc from ATN_requests t
				left join BaseInfo on(TypeID=20 AND InfoID=OffType)
			where PersonID=:p AND FromDate <= :td 
				AND if(ToDate is not null, ToDate >= :td, 1=1)
			order by ToDate desc,StartTime asc
		", array(
			":p" => $PersonID,
			":td" => $returnArr[$i]["TrafficDate"]
		));
		
		//........... Daily off and mission ...................
		if(count($requests) > 0)
		{
			if($requests[0]["ToDate"] != "")
			{
				if($requests[0]["ReqType"] == "OFF")
				{
					$returnStr .= 
						"<td>" . DateModules::$JWeekDays[ DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") ] . "</td>
						<td>" . DateModules::miladi_to_shamsi($returnArr[$i]["TrafficDate"]) . "</td>
						<td colspan=8> مرخصی " . $requests[0]["OffTypeDesc"] . "<td></tr>";
					continue;
				}
				if($requests[0]["ReqType"] == "MISSION")
				{
					$returnStr .= 
						"<td>" . DateModules::$JWeekDays[ DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") ] . "</td>
						<td>" . DateModules::miladi_to_shamsi($returnArr[$i]["TrafficDate"]) . "</td>
						<td colspan=8> ماموریت " . $requests[0]["MissionSubject"] . "<td></tr>";
					continue;
				}
			}
		}
		//....................................................
		
		$returnStr .= "<tr>
			<td>" . DateModules::$JWeekDays[ DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") ] . "</td>
			<td>" . DateModules::miladi_to_shamsi($returnArr[$i]["TrafficDate"]) . "</td>
			<td>" . $returnArr[$i]["ShiftTitle"] . "</td>
			<td>";
		
		$firstAbsence = 0;
		if($returnArr[$i]["TrafficTime"] != "" && 
			strtotime($returnArr[$i]["TrafficTime"]) > strtotime($returnArr[$i]["FromTime"]))
				$firstAbsence = strtotime($returnArr[$i]["TrafficTime"]) - strtotime($returnArr[$i]["FromTime"]);
		
		$Absence = ($returnArr[$i]["TrafficTime"] != "") ? 0 :
			strtotime($returnArr[$i]["ToTime"]) - strtotime($returnArr[$i]["FromTime"]);
		
		$index = 1;
		$totalAttend = 0;
		$currentDay = $returnArr[$i]["TrafficDate"];
		while($i < count($returnArr) && $currentDay == $returnArr[$i]["TrafficDate"])
		{
			$returnStr .= substr($returnArr[$i]["TrafficTime"],0,5);
			$returnStr .= $index % 2 == 0 ? "<br>" : " - ";
			
			if($index % 2 == 0)
			{
				$totalAttend += strtotime($returnArr[$i]["TrafficTime"]) - 
				strtotime($returnArr[$i-1]["TrafficTime"]);
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
		$extra = ($totalAttend > $ShiftDuration) ? $totalAttend - $ShiftDuration : 0;
		
		$Absence = $Absence + $firstAbsence + $lastAbsence;
		$totalAttend = TimeModules::SecondsToTime($totalAttend);
		$firstAbsence = TimeModules::SecondsToTime($firstAbsence);
		$lastAbsence = TimeModules::SecondsToTime($lastAbsence);
		$Absence = TimeModules::SecondsToTime($Absence);
		$extra = TimeModules::SecondsToTime($extra);
		
		$returnStr .= "</td><td class=attend>" . ShowTime($totalAttend) . "</td>
			<td class=extra>" . ShowTime($extra) . "</td>
			<td class=off></td>
			<td></td>
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
	.reportTbl .sub { background-color: #FFcfdd; text-align:center}
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