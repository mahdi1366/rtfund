<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.01
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";
require_once '../request/request.data.php';
require_once inc_dataGrid;

if(!empty($_REQUEST["print"]))
{
	$_REQUEST["sort"] = '[{"property":"InstallmentDate","direction":"DESC"}]';
	$data = GetDelayedInstallments(true);
	require_once inc_reportGenerator;
	
	$rpg = new ReportGenerator();
	$rpg->mysql_resource = $data;
	
	function DateRender($row, $value){ return DateModules::miladi_to_shamsi($value);}
	function MoneyRender($row,$value){ return number_format($value);}
	
	$rpg->addColumn("وام گیرنده", "LoanPersonName");
	$rpg->addColumn("سررسید", "InstallmentDate","DateRender");
	$rpg->addColumn("مبلغ قسط", "InstallmentAmount", "MoneyRender");
	$rpg->addColumn("مانده", "remainder", "MoneyRender");
			
	
	echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
	echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
			<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
			<td align='center' style='height:100px;vertical-align:middle;font-family:b titr;font-size:15px'>
				گزارش اقساط معوق
			</td>
			<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
		. DateModules::shNow() . "<br>";
	
	echo "</td></tr></table>";
	$rpg->generateReport();
	die();
}

$dg = new sadaf_datagrid("dg",$js_prefix_address . "../request/request.data.php?task=GetDelayedInstallments","grid_div");

$dg->addColumn("", "PartID","", true);

$col = $dg->addColumn("کد وام", "RequestID","");
$col->width = 50;

$col = $dg->addColumn("وام گیرنده", "LoanPersonName");


$col = $dg->addColumn("سررسید", "InstallmentDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("تاخیر", "ForfeitDays", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("مبلغ قسط", "InstallmentAmount", GridColumn::ColumnType_money);
$col->width = 150;

$col = $dg->addColumn("قابل پرداخت", "remainder", GridColumn::ColumnType_money);
$col->width = 100;

$dg->addButton("", "گزارش پرداخت", "report", "function(){LoanReport_DelayedInstallmentsObj.PayReport();}");

$dg->height = 377;
$dg->width = 850;
$dg->emptyTextOfHiddenColumns = true;

$dg->addButton("", "چاپ", "print", "LoanReport_DelayedInstallments.print");

$dg->HeaderMenu = false;
$dg->DefaultSortField = "InstallmentDate";
$dg->DefaultSortDir = "DESC";
$dg->title = "اقساط معوق";
$dg->autoExpandColumn = "LoanPersonName";

$grid = $dg->makeGrid_returnObjects();

?>
<script>
LoanReport_DelayedInstallments.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function LoanReport_DelayedInstallments()
{
	this.DateFS = new Ext.form.FieldSet({
		width : 400,
		renderTo : this.get("divPanel"),
		items : [{
			xtype : "shdatefield",
			itemId : "ToDate",
			labelWidth : 110,
			fieldLabel : "اقساط معوق تا تاریخ",
			value : "<?= DateModules::shNow() ?>"
		},{
			xtype : "numberfield",
			itemId : "minDays",
			labelWidth : 110,
			fieldLabel : "حداقل روز تاخیر در پرداخت قسط",
			hideTrigger : true
		},{
			xtype : "button",
			iconCls : "refresh",
			text : "بارگذاری گزارش",
			handler : function(){
				me = LoanReport_DelayedInstallmentsObj;
				me.grid.getStore().proxy.extraParams.ToDate = me.DateFS.down("[itemId=ToDate]").getRawValue();
				me.grid.getStore().proxy.extraParams.minDays = me.DateFS.down("[itemId=minDays]").getRawValue();
				me.grid.getStore().loadPage(1);
			}
		}]
	});
	
	this.grid = <?= $grid ?>;
	this.grid.getStore().proxy.extraParams.ToDate = this.DateFS.down("[itemId=ToDate]").getRawValue();
	this.grid.on("itemdblclick", function(view, record){
		framework.OpenPage("../loan/request/RequestInfo.php", "اطلاعات درخواست", {RequestID : record.data.RequestID});
	});
	this.grid.render(this.get("divGrid"));
}

LoanReport_DelayedInstallments.prototype.PayReport = function(){

	record = this.grid.getSelectionModel().getLastSelected();
	window.open(this.address_prefix + "../report/LoanPayment.php?show=true&PartID=" + record.data.PartID);
}

LoanReport_DelayedInstallments.print = function(){
	window.open(LoanReport_DelayedInstallmentsObj.address_prefix + "DelayedInstallments.php?print=true");
}

LoanReport_DelayedInstallmentsObj = new LoanReport_DelayedInstallments();
</script>
<center><br>
	<div id="divPanel"></div>
	<br>
	<div id="divGrid" ></div>
</center>
