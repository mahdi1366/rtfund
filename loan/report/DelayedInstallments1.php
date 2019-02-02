<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.01
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";
require_once '../request/request.data.php';
require_once inc_dataGrid;

function intervslRender($row, $value){
	return $value . ($row["IntervalType"] == "DAY" ? " روز" : " ماه");
}

if(!empty($_REQUEST["print"]))
{
	$_REQUEST["sort"] = '[{"property":"InstallmentDate","direction":"DESC"}]';
	$data = GetDelayedInstallments(true);
	require_once inc_reportGenerator;
	
	$rpg = new ReportGenerator();
	$rpg->mysql_resource = $data;
	
	$col = $rpg->addColumn("شماره وام", "RequestID");
	$col = $rpg->addColumn("شعبه وام", "BranchName");
	$col = $rpg->addColumn("نوع وام", "LoanDesc");
	$col = $rpg->addColumn("معرف", "ReqPersonName");
	$col = $rpg->addColumn("وام گیرنده", "LoanPersonName");
	$col = $rpg->addColumn("تضامین", "tazamin");
	$col = $rpg->addColumn("تاریخ آخرین قسط", "LastInstallmentDate","ReportDateRender");
	$col = $rpg->addColumn("تاریخ آخرین پرداخت مشتری", "MaxPayDate","ReportDateRender");
	
	$col = $rpg->addColumn("سررسید", "InstallmentDate","ReportDateRender");
	$col = $rpg->addColumn("مبلغ قسط", "InstallmentAmount", "ReportMoneyRender");
	$col->EnableSummary();
	$col = $rpg->addColumn("قابل پرداخت معوقه", "TotalRemainder","ReportMoneyRender");
	$col->EnableSummary();
	
	$col = $rpg->addColumn("اصل و کارمزد معوقه", "PureRemain","ReportMoneyRender");
	$col->EnableSummary();
	
	$rpg->addColumn("شرح", "PartDesc");
	$col = $rpg->addColumn("مبلغ پرداخت", "PartAmount", "ReportMoneyRender");
	$col->EnableSummary();
	$rpg->addColumn("ماه تنفس", "DelayMonths");
	$rpg->addColumn("روز تنفس", "DelayDays");
	$rpg->addColumn("فاصله اقساط", "PayInterval", "intervslRender");
	$rpg->addColumn("تعداد اقساط", "InstallmentCount");
	$rpg->addColumn("کارمزد مشتری", "CustomerWage");
	$rpg->addColumn("کارمزد صندوق", "FundWage");
	$rpg->addColumn("درصد دیرکرد", "ForfeitPercent");
	
	BeginReport();
	
	echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
			<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
			<td align='center' style='height:100px;vertical-align:middle;font-family:titr;font-size:15px'>
				گزارش اقساط معوق
			</td>
			<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
		. DateModules::shNow() . "<br>";
	
	echo "</td></tr></table>";
	$rpg->generateReport();
	die();
}
if(!empty($_REQUEST["EXCEL"]))
{
	$data = GetDelayedInstallments(true);
	$rpt = new ReportGenerator();
	$rpt->rowNumber = false;
	$rpt->excel = true;
	$rpt->mysql_resource = $data;
	$rpt->addColumn("PID", "LoanPersonID");
	$rpt->addColumn("کد وام", "RequestID");
	$rpt->addColumn("شعبه وام", "BranchName");
	$rpt->addColumn("نوع وام", "LoanDesc");
	$rpt->addColumn("تضامین", "tazamin");
	$rpt->addColumn("معرف", "ReqPersonName");
	$rpt->addColumn("وام گیرنده", "LoanPersonName");
	$rpt->addColumn("موبایل", "mobile");
	$rpt->addColumn("شماره پیامک", "SmsNo");
	$rpt->addColumn("وام گیرنده", "LoanPersonName");
	$rpt->addColumn("تاریخ آخرین قسط", "LastInstallmentDate","ReportDateRender");
	$rpt->addColumn("تاریخ آخرین پرداخت مشتری", "MaxPayDate","ReportDateRender");
	$rpt->addColumn("سررسید", "InstallmentDate","ReportDateRender");
	$rpt->addColumn("مبلغ قسط", "InstallmentAmount","ReportMoneyRender");
	$rpt->addColumn("قابل پرداخت معوقه", "TotalRemainder","ReportMoneyRender");
	$rpt->addColumn("اصل و کارمزد معوقه", "PureRemain","ReportMoneyRender");
	$rpt->addColumn("مبلغ پرداخت", "PartAmount", "ReportMoneyRender");
	$rpt->addColumn("شرح", "PartDesc");
	$rpt->addColumn("ماه تنفس", "DelayMonths");
	$rpt->addColumn("روز تنفس", "DelayDays");
	$rpt->addColumn("فاصله اقساط", "PayInterval", "intervslRender");
	$rpt->addColumn("تعداد اقساط", "InstallmentCount");
	$rpt->addColumn("کارمزد مشتری", "CustomerWage");
	$rpt->addColumn("کارمزد صندوق", "FundWage");
	$rpt->addColumn("درصد دیرکرد", "ForfeitPercent");
	$rpt->generateReport();
	die(); 
}
if(!empty($_REQUEST["NTC_EXCEL"]))
{
	$data = GetDelayedInstallments(true);
	$rpt = new ReportGenerator();
	$rpt->rowNumber = false;
	$rpt->excel = true;
	$rpt->mysql_resource = $data;
	$rpt->addColumn("PID", "LoanPersonID");
	$rpt->addColumn("کد وام", "RequestID");
	$rpt->addColumn("شعبه وام", "BranchName");
	$rpt->addColumn("نوع وام", "LoanDesc");
	$rpt->addColumn("معرف", "ReqPersonName");
	$rpt->addColumn("وام گیرنده", "LoanPersonName");
	$rpt->addColumn("موبایل", "mobile");
	$rpt->addColumn("شماره پیامک", "SmsNo");
	$rpt->addColumn("وام گیرنده", "LoanPersonName");
	$rpt->addColumn("سررسید", "InstallmentDate","ReportDateRender");
	$rpt->addColumn("مبلغ قسط", "InstallmentAmount","ReportMoneyRender");
	$rpt->addColumn("قابل پرداخت معوقه", "TotalRemainder","ReportMoneyRender");
	$rpt->generateReport();
	die(); 
}

$dg = new sadaf_datagrid("dg",$js_prefix_address . "../request/request.data.php?task=GetDelayedInstallments","grid_div");

$dg->addColumn("", "RequestID","", true);
$dg->addColumn("", "ComputeMode","", true);

$col = $dg->addColumn("کد وام", "RequestID","");
$col->width = 50;

$col = $dg->addColumn("وام گیرنده", "LoanPersonName");


$col = $dg->addColumn("سررسید", "InstallmentDate", GridColumn::ColumnType_date);
$col->width = 110;

//$col = $dg->addColumn("تاخیر", "ForfeitDays", GridColumn::ColumnType_date);
//$col->width = 80;

$col = $dg->addColumn("مبلغ قسط", "InstallmentAmount", GridColumn::ColumnType_money);
$col->width = 150;

$col = $dg->addColumn("قابل پرداخت معوقه", "TotalRemainder", GridColumn::ColumnType_money);
$col->width = 100;

$dg->addButton("", "گزارش پرداخت", "report", "function(){LoanReport_DelayedInstallmentsObj.PayReport();}");

$dg->height = 377;
$dg->width = 750;
$dg->emptyTextOfHiddenColumns = true;

$dg->addButton("", "چاپ", "print", "LoanReport_DelayedInstallments.print");
$dg->addButton("", "excel", "excel", "LoanReport_DelayedInstallments.excel");

$dg->addButton("", "خروجی excel جهت ارتباط با ذینفعان", "excel", "LoanReport_DelayedInstallments.NoticeExcel");

$dg->EnablePaging = false;
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
		width : 650,
		renderTo : this.get("divPanel"),
		layout : "hbox",
		items : [{
			xtype : "shdatefield",
			itemId : "FromDate",
			labelWidth : 110,
			fieldLabel : "اقساط معوق از تاریخ",
			value : "<?= DateModules::shNow() ?>"
		},{
			xtype : "shdatefield",
			itemId : "ToDate",
			labelWidth : 50,
			fieldLabel : "تا تاریخ",
			value : "<?= DateModules::shNow() ?>"
		},{
			xtype : "button",
			iconCls : "refresh",
			text : "بارگذاری گزارش",
			handler : function(){
				me = LoanReport_DelayedInstallmentsObj;
				me.grid.getStore().proxy.extraParams.ToDate = me.DateFS.down("[itemId=ToDate]").getRawValue();
				me.grid.getStore().proxy.extraParams.FromDate = me.DateFS.down("[itemId=FromDate]").getRawValue();
				me.grid.getStore().loadPage(1);
			}
		}]
	});
	
	this.grid = <?= $grid ?>;
	this.grid.getStore().proxy.extraParams.FromDate = this.DateFS.down("[itemId=FromDate]").getRawValue();
	this.grid.getStore().proxy.extraParams.ToDate = this.DateFS.down("[itemId=ToDate]").getRawValue();
	this.grid.on("itemdblclick", function(view, record){
		framework.OpenPage("../loan/request/RequestInfo.php", "اطلاعات درخواست", {RequestID : record.data.RequestID});
	});
	this.grid.render(this.get("divGrid"));
}

LoanReport_DelayedInstallments.prototype.PayReport = function(){

	record = this.grid.getSelectionModel().getLastSelected();
	window.open(this.address_prefix + "../report/LoanPayment.php?show=true&RequestID=" + record.data.RequestID);
}

LoanReport_DelayedInstallments.print = function(){
	window.open(LoanReport_DelayedInstallmentsObj.address_prefix + "DelayedInstallments1.php?print=true"+
		"&FromDate=" + LoanReport_DelayedInstallmentsObj.DateFS.getComponent("FromDate").getRawValue()+
		"&ToDate=" + LoanReport_DelayedInstallmentsObj.DateFS.getComponent("ToDate").getRawValue());
}

LoanReport_DelayedInstallments.excel = function(){
	
	me = LoanReport_DelayedInstallmentsObj;
	window.open(LoanReport_DelayedInstallmentsObj.address_prefix + 
		"DelayedInstallments1.php?EXCEL=true" +
		"&FromDate=" + me.DateFS.getComponent("FromDate").getRawValue()+
		"&ToDate=" + me.DateFS.getComponent("ToDate").getRawValue());
}

LoanReport_DelayedInstallments.NoticeExcel = function(){
	
	me = LoanReport_DelayedInstallmentsObj;
	window.open(LoanReport_DelayedInstallmentsObj.address_prefix + 
		"DelayedInstallments1.php?NTC_EXCEL=true" +
		"&FromDate=" + me.DateFS.getComponent("FromDate").getRawValue()+
		"&ToDate=" + me.DateFS.getComponent("ToDate").getRawValue());
}

LoanReport_DelayedInstallmentsObj = new LoanReport_DelayedInstallments();
</script>
<center><br>
	<div id="divPanel"></div>
	با توجه به اینکه گزارش اقساط معوق محاسباتی می باشد حتی الامکان از گزارش گیری در بازه های طولانی خودداری کنید.
	<br>
	<div id="divGrid" ></div>
</center>