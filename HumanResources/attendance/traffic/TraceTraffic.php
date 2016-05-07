<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.02
//-----------------------------

require_once '../../header.inc.php';
require_once 'traffic.class.php';
require_once inc_reportGenerator;

if(isset($_REQUEST["showReport"]))
{
	ShowReport();
}

function ShowReport(){

	$StartDate = DateModules::shamsi_to_miladi($_POST["year"] . "-" . $_POST["month"] . "-01", "-");
	$EndDate = DateModules::shamsi_to_miladi($_POST["year"] . "-" . $_POST["month"] ."-" . DateModules::DaysOfMonth($_POST["year"] ,$_POST["month"]), "-");
	
	$dt = ATN_traffic::Get(" AND t.PersonID=? AND TrafficDate>= ? AND TrafficDate <= ? order by TrafficDate,TrafficTime", 
		array($_SESSION["USER"]["PersonID"], $StartDate, $EndDate));
	
	$dt = $dt->fetchAll();
	
	$returnArr = array();
	while($StartDate < $EndDate)
	{
		if(count($dt)>0 && $StartDate == $dt[0]["TrafficDate"])
			break;
		$returnArr[] = array("TrafficID" => "", "TrafficDate" => $StartDate , "ShiftTitle" => "", "TrafficTime" => "");;
		$StartDate = DateModules::AddToGDate($StartDate, 1);
	}
	
	$returnArr = array_merge($returnArr, $dt);
	$StartDate = count($dt) > 0 ? $dt[ count($dt)-1 ]["TrafficDate"] : $StartDate;
	$StartDate = DateModules::AddToGDate($StartDate, 1);
	
	while($StartDate <= $EndDate)
	{
		$returnArr[] = array("TrafficID" => "", "TrafficDate" => $StartDate , "ShiftTitle" => "", "TrafficTime" => "");;
		$StartDate = DateModules::AddToGDate($StartDate, 1);
	}
		
	function DayRender($row, $value){
		
		return DateModules::$JWeekDays[ DateModules::GetWeekDay($value, "N") ];
	}
	function DateRender($row, $value){
		
		return DateModules::miladi_to_shamsi($value);
	}
	
	$returnStr = "";
	for($i=0; $i < count($returnArr); $i++)
	{
		$returnStr .= "<tr>
			<td>" . DateModules::$JWeekDays[ DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") ] . "</td>
			<td>" . DateModules::miladi_to_shamsi($returnArr[$i]["TrafficDate"]) . "</td>
			<td>" . $returnArr[$i]["ShiftTitle"] . "</td>
			<td>";
		
		$index = 1;
		$totalAttend = 0;
		$currentDay = $returnArr[$i]["TrafficDate"];
		while($i < count($returnArr) && $currentDay == $returnArr[$i]["TrafficDate"])
		{
			$returnStr .= $returnArr[$i]["TrafficTime"];
			$returnStr .= $index % 2 == 0 ? "<br>" : " - ";
			
			if($index % 2 == 0)
			{
				$totalAttend += $returnArr[$i]["TrafficTime"] - $returnArr[$i-1]["TrafficTime"];
			}
				
			$index++;
			$i++;
		}
		$i--;
		$returnStr .= "</td><td>" . $totalAttend . "</td>
			<td class=extra></td>
			<td class=off></td>
			<td></td>
			<td class=sub></td>
			<td class=sub></td>
			</tr>";
	}
?>
<style>
	.reportTbl td {padding:4px;}
	.reportTbl th {padding:4px;text-align: center; background-color: #efefef; font-weight: bold}
	.reportTbl .extra { background-color: #D0F7E2}
	.reportTbl .off { background-color: #D7BAFF}
	.reportTbl .sub { background-color: #FFcfdd}
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
		layout : {
			type : "table",
			columns :4
		},
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش تردد",
		width : 800,
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
			hiddenName : "month"
		},{
			xtype : "button",
			border : true,
			style : "margin-right:20px",
			text : "مشاهده گزارش",
			iconCls : "report",
			handler : function(){ TraceTrafficObj.LoadReport(); }
		},{
			xtype : "container",
			html : "<hr>",
			width : 780,
			colspan : 4
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