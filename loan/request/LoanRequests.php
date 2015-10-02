<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;
require_once 'LoanRequests.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "../../loan/request/request.data.php?task=SelectRequests", "grid_div");

$dg->addColumn("", "StatusID", "", true);
$dg->addColumn("", "GroupDesc", "", true);
$dg->addColumn("", "InsureAmount", "", true);
$dg->addColumn("", "FirstCostusAmount", "", true);
$dg->addColumn("", "ForfeitPercent", "", true);
$dg->addColumn("", "FeePercent", "", true);
$dg->addColumn("", "FeeAmount", "", true);
$dg->addColumn("", "ProfitPercent", "", true);
$dg->addColumn("", "CostusCount", "", true);
$dg->addColumn("", "CostusInterval", "", true);
$dg->addColumn("", "DelayCount", "", true);
$dg->addColumn("", "MaxAmount", "", true);

$col = $dg->addColumn("کد درخواست", "RequestID", "");
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

$col = $dg->addColumn('عملیات', '', 'string');
$col->renderer = "LoanRequests.OperationRender";
$col->width = 50;
$col->align = "center";

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 420;
$dg->width = 770;
$dg->title = "درخواست های وام";
$dg->DefaultSortField = "ReqDate";
$dg->autoExpandColumn = "LoanDesc";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
LoanRequestsObject.grid = <?= $grid ?>;
LoanRequestsObject.grid.render(LoanRequestsObject.get("DivGrid"));
</script>
<center><br>
	<div id="DivGrid"></div>
</center>