<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.01
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";
require_once '../request/request.data.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg",$js_prefix_address . "../request/request.data.php?task=GetDelayedInstallments","grid_div");

$dg->addColumn("", "PartID","", true);

$col = $dg->addColumn("کد وام", "RequestID","");
$col->width = 50;

$col = $dg->addColumn("وام گیرنده", "LoanPersonName");
$col->width = 100;

$col = $dg->addColumn("سررسید", "InstallmentDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("مبلغ قسط", "InstallmentAmount", GridColumn::ColumnType_money);


$col = $dg->addColumn("مانده", "remainder", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("شماره چک", "ChequeNo", "string");
$col->width = 80;

$col = $dg->addColumn("بانک", "ChequeBank", "");
$col->width = 70;

$col = $dg->addColumn("شعبه", "ChequeBranch", "");
$col->width = 90;

$dg->addButton("", "گزارش پرداخت", "report", "function(){LoanReport_DelayedInstallmentsObj.PayReport();}");

$dg->height = 377;
$dg->width = 850;
$dg->emptyTextOfHiddenColumns = true;
$dg->EnableSearch = false;
$dg->HeaderMenu = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "InstallmentDate";
$dg->DefaultSortDir = "ASC";
$dg->title = "اقساط معوق";
$dg->autoExpandColumn = "InstallmentAmount";

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
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("divGrid"));
}

LoanReport_DelayedInstallmentsObj = new LoanReport_DelayedInstallments();

LoanReport_DelayedInstallments.prototype.PayReport = function(){

	record = this.grid.getSelectionModel().getLastSelected();
	window.open(this.address_prefix + "../report/LoanPayment.php?show=true&PartID=" + record.data.PartID);
}

</script>
<center><br>
	<div id="divGrid" ></div>
</center>