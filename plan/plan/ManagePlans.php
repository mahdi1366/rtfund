<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.12
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;
require_once 'ManagePlans.js.php';

$portal = isset($_SESSION["USER"]["portal"]) ? true : false;
$expert = isset($_REQUEST["expert"]) ? true : false;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "plan.data.php?task=SelectAllPlans" . 
		($expert ? "&expert=true" : ""), "grid_div");

$dg->addColumn("", "StepID", "", true);

$col = $dg->addColumn("شماره", "PlanID", "");
$col->width = 50;

$col = $dg->addColumn("عنوان طرح", "PlanDesc", "");

$col = $dg->addColumn("تاریخ درخواست", "RegDate", GridColumn::ColumnType_date);
$col->width = 110;

$col = $dg->addColumn("درخواست کننده", "ReqFullname");
$col->width = 150;

$col = $dg->addColumn("وضعیت", "StepDesc", "");
$col->width = 100;

if(!$portal && !$expert)
	$dg->addObject('ManagePlanObject.AllPlansObj');
else if($portal)
{
	$col = $dg->addColumn('طرح', '', 'string');
	$col->renderer = "ManagePlan.PlanInfoRender";
	$col->width = 40;
	$col->align = "center";
}

$col = $dg->addColumn('سابقه', '', 'string');
$col->renderer = "ManagePlan.HistoryRender";
$col->width = 40;
$col->align = "center";

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 450;
$dg->pageSize = 15;
$dg->width = 760;
$dg->title = "طرح های ارسالی";
$dg->DefaultSortField = "RegDate";
$dg->autoExpandColumn = "PlanDesc";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
ManagePlanObject.grid = <?= $grid ?>;
<? if(!$portal){ ?>
	ManagePlanObject.grid.on("itemdblclick", function(view, record){
		framework.OpenPage("/plan/plan/PlanInfo.php", "جداول اطلاعاتی طرح", {PlanID : record.data.PlanID});
	});	
<? } ?>
ManagePlanObject.grid.getView().getRowClass = function(record, index)
{
	if(record.data.StepID == "<?= STEPID_REJECT ?>")
		return "pinkRow";
	
	return "";
}

ManagePlanObject.grid.render(ManagePlanObject.get("DivGrid"));
</script>
<center><br>
	<div id="DivGrid"></div>
	<br>
	<div id="LoanInfo"></div>
</center>