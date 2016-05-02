<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.02
//-----------------------------

require_once '../../header.inc.php';
require_once '../traffic/traffic.class.php';
require_once inc_reportGenerator;

if(isset($_REQUEST["showReport"]))
{
	ShowReport();
}

function ShowReport(){

	$StartDate = DateModules::shamsi_to_miladi($_POST["year"] . "-" . $_POST["month"] . "-01", "-");
	$EndDate = DateModules::shamsi_to_miladi($_POST["year"] . "-" . $_POST["month"] ."-" . DateModules::DaysOfMonth($_POST["year"] ,$_POST["month"]), "-");
	
	$days = DateModules::DaysOfMonth($_POST["year"] ,$_POST["month"]);
	$dt = ATN_traffic::Get(" AND PersonID=? AND TrafficDate>= ? AND TrafficDate <= ? order by TrafficDate", 
		array($_SESSION["USER"]["PersonID"], $StartDate, $EndDate));
	
	$returnArr = array();
	for($i=0; $i < $days; $i++)
	{
		
	}
	
	
	
	function DayRender($row, $value){
		
		return DateModules::$JWeekDays[ DateModules::GetWeekDay($value, "N") ];
	}
	
	$rpg = new ReportGenerator();
	$rpg->rowNumber = false;
	$rpg->mysql_resource = $dt;
	
	$rpg->addColumn("روز", "TrafficDate", "DayRender");
	
	$rpg->generateReport();
	
	die();
}

$r = rand(1,1000);
?>
<script type="text/javascript" src="/generalUI/ext4/ux/calendar.js?v=<?= $r ?>"></script>
<script>
Calandar.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	DocID : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function Calandar()
{
	this.mainPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		autoHeight : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "تنظیم تقویم کاری",
		width : 800,
		items :[{
			xtype : "combo",
			store: YearStore,   
			labelWidth : 30,
			width : 120,
			fieldLabel : "سال",
			displayField: 'title',
			valueField : "id",
			hiddenName : "year",
			listeners : {
				select : function(){ CalandarObj.LoadReport(); }
			}			
		}]
	});
	
}

Calandar.prototype.LoadReport = function(){

	this.calendar = new Ext.calendar({
		title : "تقویم کاری",
		width : 800,
		height : 500,
		StartDate : new Ext.SHDate(<?= substr(DateModules::shNow(),0,4) ?>, 1, 1),
		renderTo : this.get("calendar"),
		layout : {
			type : "table",
			columns : 7
		},
		defaults :{
			width : 100,
			height : 100
		}
	});
}


CalandarObj = new Calandar();


</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div><br>
		<div id="calendar" ></div><br>
	</center>
</form>