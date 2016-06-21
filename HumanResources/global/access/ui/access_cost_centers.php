<?php
//-------------------------
// programmer:	Jafarkhani
// Date:    	90.02
//-------------------------
require_once '../../../header.inc.php';
require_once inc_dataGrid;

require_once '../js/access_cost_centers.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "../data/access.data.php?task=selectUsers", "div_grid");

$dg->addColumn("شناسه کاربر", "UserID");

$dg->addColumn("نام", "pfname");
$dg->addColumn("نام خانوادگی", "plname");

$col = $dg->addColumn("بارگذاری", "");
$col->renderer = "CostCenterAccess.LoadCostCentersRender";
$col->width = 50;

$dg->DefaultSortField = "plname";
$dg->width = 400;
$dg->height = 500;
$dg->pageSize = 10;
$dg->autoExpandColumn = "UserID";
$dg->title = "لیست کاربران سیستم";
$gridUsers = $dg->makeGrid_returnObjects();

//------------------------------------------------------------------------------

$dg2 = new sadaf_datagrid("dg2", $js_prefix_address . "../data/access.data.php?task=selectCostCenters", "div_grid2", "form_costCenterAccess");

$dg2->addColumn("", "cost_center_id", "", true);
$dg2->addColumn("مرکز هزینه", "title");

$col = $dg2->addColumn('<input type="checkbox" onclick="CostCenterAccessObject.checkAll(this);">انتخاب', "access");
$col->renderer = "CostCenterAccess.accessRender";
$col->width = 60;
$col->sortable = false;
$col->align = "center";

$dg2->addButton("", "ذخیره دسترسی های مراکز هزینه", "save", "function(){CostCenterAccessObject.SaveAccess();}");

$dg2->notRender = true;
$dg2->width = 350;
$dg2->height = 500;
$dg2->title = "لیست مراکز هزینه";
$dg2->autoExpandColumn = "title";
$dg2->EnableSearch = false;
$dg2->EnablePaging = false;
$gridCostCenters = $dg2->makeGrid_returnObjects();
?>
<script>
CostCenterAccess.prototype.afterLoad = function()
{
	this.UsersGrid = <?= $gridUsers?>;
	this.CostCentersGrid = <?= $gridCostCenters?>;

	this.UsersGrid.render(this.get("div_grid"));
}

	var CostCenterAccessObject = new CostCenterAccess();
</script>
	<form id="form_costCenterAccess">
		<table width="800px">
			<tr>
				<td width="50%">
					<div id="div_grid"></div>
				</td>
				<td width="50%">
					<input type="hidden" id="UserID" name="UserID">
					<div id="div_grid2"></div>
				</td>
			</tr>
		</table>
	</form>
