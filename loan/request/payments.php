<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg","/loan/request/request.data.php?task=GetRequestParts", "grid_div");

$dg->addColumn("", "InstallmentID","", true);
$dg->addColumn("", "PartID","", true);
$dg->addColumn("", "InstallmentAmount","", true);
$dg->addColumn("", "WageAmount","", true);
$dg->addColumn("", "WagePercent","", true);
$dg->addColumn("", "PaidDate","", true);
$dg->addColumn("", "PaidAmount","", true);
$dg->addColumn("", "StatusID","", true);

$col = $dg->addColumn("تاریخ سررسید", "PartDate", "");
$col->sortable = false;

$col = $dg->addColumn("مبلغ خالص", "PartID", GridColumn::ColumnType_money);
$col->width = 80;

$col = $dg->addColumn("کارمزد", "WageAmount", GridColumn::ColumnType_money);
$col->width = 80;

$col = $dg->addColumn("کارمزد", "WagePercent", "");
$col->width = 80;

$dg->HeaderMenu = false;
$dg->hideHeaders = true;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 150;
$dg->width = 150;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "PartDate";
$dg->disableFooter = true;

$grid = $dg->makeGrid_returnObjects();

?>
<script>
PartPayment.prototype = {
	parentID : <?= $_REQUEST["ParentID"] ?>,
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.parentID.getEl(), elementID);
	}
};

function PartPayment()
{
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("div_grid"))
	
}

PartPayment.OperationRender = function(v,p,r){
	
	return "<div align='center' title='ویرایش' class='edit' "+
		"onclick='PartPaymentObject.PartInfo(\"edit\");' " +
		"style='float:right;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:18px;height:16'></div>" + 
		
		"<div align='center' title='حذف' class='remove' "+
		"onclick='PartPaymentObject.DeletePart();' " +
		"style='float:right;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:18px;height:16'></div>";
}

PartPaymentObject = new PartPayment();

</script>

<div id="div_grid"></div>