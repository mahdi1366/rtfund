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
	
	$dt = ATN_traffic::Get(" AND PersonID=? AND TrafficDate>= ? AND TrafficDate <= ? order by TrafficDate", 
		array($_SESSION["USER"]["PersonID"], $StartDate, $EndDate));
	
	$dt = $dt->fetchAll();
	
	$returnArr = array();
	while($StartDate < $EndDate)
	{
		if(count($dt)>0 && $StartDate == $dt[0]["TrafficDate"])
			break;
		$returnArr[] = array("TrafficDate" => $StartDate);
		$StartDate = DateModules::AddToGDate($StartDate, 1);
	}
	
	$returnArr = array_merge($returnArr, $dt);
	$StartDate = count($dt) > 0 ? $dt[ count($dt)-1 ]["TrafficDate"] : $StartDate;
	
	while($StartDate < $EndDate)
	{
		$returnArr[] = array("TrafficDate" => $StartDate);
		$StartDate = DateModules::AddToGDate($StartDate, 1);
	}
		
	function DayRender($row, $value){
		
		return DateModules::$JWeekDays[ DateModules::GetWeekDay($value, "N") ];
	}
	function DateRender($row, $value){
		
		return DateModules::miladi_to_shamsi($value);
	}
	
	$rpg = new ReportGenerator();
	$rpg->rowNumber = false;
	$rpg->mysql_resource = $returnArr;
	
	$rpg->addColumn("روز", "TrafficDate", "DayRender");
	$rpg->addColumn("تاریخ", "TrafficDate", "DateRender");
	
	$rpg->generateReport();
	
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
			width : 790,
			colspan : 4
		},{
			xtype : "container",
			colspan : 4,
			width : 790,
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