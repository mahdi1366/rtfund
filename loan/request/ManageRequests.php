<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;
require_once 'ManageRequests.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "request.data.php?task=SelectAllRequests", "grid_div");

$dg->addColumn("", "StatusID", "", true);
$dg->addColumn("", "BranchID", "", true);
$dg->addColumn("", "BorrowerDesc", "", true);
$dg->addColumn("", "BorrowerID", "", true);

$col = $dg->addColumn("شماره", "RequestID", "");
$col->width = 50;

$col = $dg->addColumn("شعبه", "BranchName", "");
$col->width = 80;

$col = $dg->addColumn("تاریخ درخواست", "ReqDate", GridColumn::ColumnType_date);
$col->width = 110;

$col = $dg->addColumn("مبلغ درخواست", "ReqAmount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("درخواست کننده", "ReqFullname");
$col->width = 100;

$col = $dg->addColumn("گیرنده وام", "LoanFullname");
$col->renderer = "function(v,p,r){return v == '' || v == null ? r.data.BorrowerDesc : v;}";

$col = $dg->addColumn("وضعیت", "StatusDesc", "");
$col->width = 100;

$col = $dg->addColumn('عملیات', '', 'string');
$col->renderer = "ManageRequest.OperationRender";
$col->width = 50;
$col->align = "center";

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 420;
$dg->width = 770;
$dg->title = "درخواست های وام";
$dg->DefaultSortField = "ReqDate";
$dg->autoExpandColumn = "LoanFullname";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
ManageRequestObject.grid = <?= $grid ?>;
ManageRequestObject.grid.render(ManageRequestObject.get("DivGrid"));
</script>
<center><br>
	<div id="DivGrid"></div>
</center>