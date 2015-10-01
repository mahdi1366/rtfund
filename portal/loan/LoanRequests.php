<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "../../loan/request/request.data.php?task=SelectRequests", "grid_div");

$col = $dg->addColumn("پیگیری", "RequestID", "");
$col->width = 50;

$col = $dg->addColumn("تاریخ درخواست", "ReqDate", GridColumn::ColumnType_date);
$col->width = 110;

$col = $dg->addColumn("عنوان وام درخواستی", "LoanDesc", "");

$col = $dg->addColumn("مبلغ درخواست", "ReqAmount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("مبلغ تایید شده", "OkAmount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("وضعیت", "StatusDesc", "");
$col->width = 100;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 420;
$dg->width = 770;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->title = "درخواست وام های ارسالی";
$dg->DefaultSortField = "ReqDate";
$dg->disableFooter = true;
$dg->autoExpandColumn = "LoanDesc";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
	
LoanRequests.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function LoanRequests()
{
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("DivGrid"))
}

LoanRequestsObject = new LoanRequests();

LoanRequests.prototype.LoanRequests = function()
{
	if(this.get("new_pass").value != this.get("new_pass2").value)
	{
		return;
	}
}

</script>
<center>
	<div id="DivGrid"></div>
</center>