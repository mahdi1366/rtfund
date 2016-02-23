<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.12
//-----------------------------

require_once 'header.inc.php';
require_once inc_dataGrid;
require_once 'ManagePlans.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "plan.data.php?task=SelectAllPlans", "grid_div");

$dg->addColumn("", "StatusID", "", true);

$col = $dg->addColumn("شماره", "PlanID", "");
$col->width = 50;

$col = $dg->addColumn("عنوان طرح", "PlanDesc", "");

$col = $dg->addColumn("تاریخ درخواست", "RegDate", GridColumn::ColumnType_date);
$col->width = 110;

$col = $dg->addColumn("درخواست کننده", "ReqFullname");
$col->width = 150;

$col = $dg->addColumn("وضعیت", "StatusDesc", "");
$col->width = 100;

$col = $dg->addColumn('عملیات', '', 'string');
$col->renderer = "ManagePlan.OperationRender";
$col->width = 50;
$col->align = "center";

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 500;
$dg->pageSize = 15;
$dg->width = 770;
$dg->title = "طرح های ارسالی";
$dg->DefaultSortField = "RegDate";
$dg->autoExpandColumn = "PlanDesc";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
ManagePlanObject.grid = <?= $grid ?>;
ManagePlanObject.grid.on("itemdblclick", function(view, record){
	framework.OpenPage("../plan/PlanInfo.php", "جداول اطلاعاتی طرح", {PlanID : record.data.PlanID});
});	
ManagePlanObject.grid.render(ManagePlanObject.get("DivGrid"));
</script>
<center><br>
	<div id="DivGrid"></div>
	<br>
	<div id="LoanInfo"></div>
</center>