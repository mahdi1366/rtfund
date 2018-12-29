<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.02
//-----------------------------

require_once '../header.inc.php';
require_once 'traffic.class.php';
require_once '../baseinfo/shift.class.php';
require_once inc_reportGenerator;
require_once inc_dataGrid;

$admin = isset($_REQUEST["admin"]) ? true : false;

if(isset($_REQUEST["showReport"]))
{
	ShowReport($admin);
}

function ShowReport($admin){
	
	if($_POST["FromDate"] == "")
	{
		$StartDate = DateModules::shamsi_to_miladi($_POST["year"] . "-" . $_POST["month"] . "-01", "-");
		$EndDate = DateModules::shamsi_to_miladi($_POST["year"] . "-" . $_POST["month"] ."-" . DateModules::DaysOfMonth($_POST["year"] ,$_POST["month"]), "-");
	}
	else
	{
		$StartDate = DateModules::shamsi_to_miladi($_POST["FromDate"], "-");
		$EndDate = DateModules::shamsi_to_miladi($_POST["ToDate"], "-");
	}
	$PersonID = $_SESSION["USER"]["PersonID"];
	$PersonID = !empty($_POST["PersonID"]) ? $_POST["PersonID"] : $PersonID;
		
	$returnStr = "";
	$SUM = ATN_traffic::Compute($StartDate, $EndDate, $PersonID, $admin, $returnStr);
	if($SUM === false)
	{
		echo ExceptionHandler::GetExceptionsToString();
		die();
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
		<th style=width:80px>ورود/خروج</th>
		<th>حضور</th>
		<th class="extra" width="60">اضافه کار</th>
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
			$SUM["LegalExtra"] = TimeModules::SecondsToTime($SUM["LegalExtra"]);
			$SUM["AllowedExtra"] = TimeModules::SecondsToTime($SUM["AllowedExtra"]);			
		?>
		<td colspan="4"></td>
		<td><?= TimeModules::ShowTime($SUM["attend"]) ?></td>
		<td><?= TimeModules::ShowTime($SUM["extra"]) ?></td>
		<td><?= TimeModules::ShowTime($SUM["Off"]) ?></td>
		<td><?= TimeModules::ShowTime($SUM["mission"]) ?></td>
		<td><?= TimeModules::ShowTime($SUM["firstAbsence"]) ?></td>
		<td><?= TimeModules::ShowTime($SUM["lastAbsence"]) ?></td>
		<td><?= TimeModules::ShowTime($SUM["absence"]) ?></td>
	</tr>
	<tr class="footer">
		<td colspan="4">مجموع عملکرد</td>
		<td colspan="3">
			جمع اضافه کار : <?= TimeModules::ShowTime($SUM["extra"]) ?><br>
			جمع اضافه کار مجاز: <?= TimeModules::ShowTime($SUM["LegalExtra"]) ?><br>
			جمع اضافه کار مجوز: <?= TimeModules::ShowTime($SUM["AllowedExtra"]) ?><br>
			<hr>
			جمع مرخصی استعلاجی : <?= $SUM["DailyOff_1"] ?><br>
			جمع مرخصی استحقاقی : <?= $SUM["DailyOff_2"] ?><br>
			جمع مرخصی بدون حقوق : <?= $SUM["DailyOff_3"] ?><br>
		</td>
		<td colspan="4">
			جمع ماموریت روزانه : <?= $SUM["DailyMission"] ?><br>
			جمع غیبت روزانه : <?= $SUM["DailyAbsence"]?><br>
		</td>
	</tr>
</table>
<?	
	die();
}

$dg = new sadaf_datagrid("dg", $js_prefix_address . "traffic.data.php?task=SelectDayTraffics&admin="
		. ($admin ? "true" : "false"), "grid_div");

$dg->addColumn("", "TrafficID", "", true);
$dg->addColumn("", "IsActive", "", true);

$col = $dg->addColumn("نوع رکورد", "ReqType");
$col->renderer = "TraceTraffic.RecordTypeRender";

$col = $dg->addColumn("ساعت", "TrafficTime");
$col->width = 60;

$col = $dg->addColumn("تا ساعت", "EndTime");
$col->width = 60;

if($admin)
{
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return TraceTraffic.DeleteRender(v,p,r);}";
	$col->width = 40;
}
$dg->height = 230;
$dg->width = 400;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->autoExpandColumn = "ReqType";
$dg->DefaultSortField = "TrafficTime";
$dg->emptyTextOfHiddenColumns = true;

$grid = $dg->makeGrid_returnObjects();

?>
<script>
TraceTraffic.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	admin : <?= $admin ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function TraceTraffic()
{
	this.grid = <?= $grid ?>;
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsActive == "NO")
			return "pinkRow";
	}	

	
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
						url: '/framework/person/persons.data.php?task=selectPersons&UserType=IsStaff&IncludeInactive=true',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields :  ['PersonID','fullname']
				}),
				displayField: 'fullname',
				hidden : !this.admin,
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
			},{
				xtype : "shdatefield",
				name : "FromDate",
				hidden : !this.admin,
				fieldLabel : "از تاریخ"
			},{
				xtype : "shdatefield",
				name : "ToDate",
				hidden : !this.admin,
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

TraceTraffic.prototype.LoadReport = function(){
	
	mask = new Ext.LoadMask(this.mainPanel,{msg:'در حال بارگذاری ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'TraceTraffic.php?showReport=true<?= $admin ? "&admin=true" : "" ?>',
		method: "POST",
		form : this.get("mainForm"),

		success: function(response){
			mask.hide();
			TraceTrafficObj.mainPanel.getComponent("div_report").update(response.responseText);
		},
		failure: function(){}
	});	

}

TraceTraffic.RecordTypeRender = function(v,p,r){
	switch(v)
	{
		case "user" :	return "ثبت ورود خروج کاربر";
		case "CORRECT" :	return "فراموشی";
		case "OFF" :		return "مرخصی ساعتی";
		case "MISSION" :	return "ماموریت ساعتی";
	}
}

TraceTraffic.DeleteRender = function(v,p,r)
{
	if(r.data.TrafficID == null)
	{
		return "";
	}
	if(r.data.IsActive == "YES" )
		return "<div align='center' title='حذف' class='remove' "+
		"onclick='TraceTrafficObj.DeleteTraffic(true);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;float:left;height:16'></div>";
	if(r.data.IsActive == "NO" )
		return "<div align='center' title='برگشت' class='undo' "+
		"onclick='TraceTrafficObj.DeleteTraffic(false);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;float:left;height:16'></div>";

}

TraceTraffic.prototype.TrafficList = function(TrafficDate){
	
	this.grid.getStore().proxy.extraParams.TrafficDate = TrafficDate;
	this.grid.getStore().proxy.extraParams.PersonID = this.mainPanel.down("[hiddenName=PersonID]").getValue();
	if(!this.TraffficWin)
	{
		this.TraffficWin = new Ext.window.Window({
			width : 410,
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

TraceTraffic.prototype.DeleteTraffic = function(DeleteMode)
{
	message = DeleteMode ? "آیا مایل به حذف می باشید؟" : "آیا مایل به برگشت می باشید؟";
	Ext.MessageBox.confirm("",message, function(btn){
		if(btn == "no")
			return;
		
		me = TraceTrafficObj;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'traffic.data.php',
			params:{
				task: "DeleteTraffic",
				DeleteMode : DeleteMode ? "delete" : "undo",
				TrafficID : record.data.TrafficID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				TraceTrafficObj.grid.getStore().load();
				TraceTrafficObj.LoadReport();
			},
			failure: function(){}
		});
	});
}

TraceTraffic.prototype.CreateRequest = function(date,type)
{
	if(!this.NewTrafficWin)
	{
		this.NewTrafficWin = new Ext.window.Window({
			width : 600,
			height : 400,
			modal : true,
			bodyStyle : "background-color:white",
			loader : {
				url : this.address_prefix + "NewRequest.php",
				scripts : true
			},
			closeAction : "hide",
			buttons : [{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.NewTrafficWin);
	}
	this.NewTrafficWin.show();
	this.NewTrafficWin.center();
	this.NewTrafficWin.loader.load({
		params : {
			ExtTabID : this.NewTrafficWin.getEl().id,
			TheDate : date,
			type : type
		}
	});
}

TraceTrafficObj = new TraceTraffic();

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
