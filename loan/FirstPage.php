<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once 'header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "request/request.data.php?task=SelectReadyToPayParts", "grid_div");

$col = $dg->addColumn("شماره وام", "RequestID", "");
$col->width = 90;

$col = $dg->addColumn("مرحله وام", "PartDesc", "");
$col->width = 200;

$col = $dg->addColumn("تاریخ پرداخت", "PartDate", GridColumn::ColumnType_date);
$col->width = 100;

$col = $dg->addColumn("مبلغ ", "PartAmount", GridColumn::ColumnType_money);
$col->width = 110;

$col = $dg->addColumn("درخواست کننده", "ReqFullname");
$col->width = 100;

$col = $dg->addColumn("گیرنده وام", "LoanFullname");
$col->renderer = "function(v,p,r){return v == '' || v == null ? r.data.BorrowerDesc : v;}";

$dg->emptyTextOfHiddenColumns = true;
$dg->EnablePaging = false;
$dg->height = 500;
$dg->width = 770;
$dg->title = "درخواست های وام آماده پرداخت";
$dg->DefaultSortField = "PartDate";
$dg->autoExpandColumn = "LoanFullname";
$grid = $dg->makeGrid_returnObjects();
?>
<script>

StartPage.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function StartPage(){
	
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("DivGrid"));
}

StartPageObject = new StartPage();

</script>
<center><br>
	<div id="DivGrid"></div>
</center>