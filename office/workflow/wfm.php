<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------
include_once("../header.inc.php");
require_once inc_dataGrid;
require_once 'wfm.js.php';

$dg = new sadaf_datagrid("dg",$js_prefix_address . "wfm.data.php?task=SelectAllFlows","");

$col = $dg->addColumn("عنوان گردش","FlowDesc","string");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("آیتم مورد نظر", "ObjectType", "string");
$col->editor = ColumnEditor::ComboBox(
		PdoDataAccess::runquery("select * from BaseInfo where typeID=11"), "InfoID", "InfoDesc");
$col->width = 200;

$col = $dg->addColumn("مراحل","","");
$col->renderer = "WFM.StepsRender";
$col->sortable = false;
$col->width = 50;

$col = $dg->addColumn("حذف","FlowID","");
$col->renderer = "WFM.deleteRender";
$col->sortable = false;
$col->width = 40;

$dg->addButton = true;
$dg->addHandler = "function(){WFMObject.Adding();}";

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){return WFMObject.saveData(v,p,r);}";

$dg->height = 350;
$dg->width = 600;
$dg->DefaultSortField = "FlowID";
$dg->autoExpandColumn = "FlowDesc";
$dg->editorGrid = true;
$dg->title = "مدیریت گردش های کار";
$dg->EnablePaging = false;
$dg->EnableSearch = false;

$grid = $dg->makeGrid_returnObjects();

//------------------------------------------------------------------------------

$dg = new sadaf_datagrid("dg",$js_prefix_address . "wfm.data.php?task=SelectSteps","");

$dg->addColumn("","StepRowID","string", true);
$dg->addColumn("","FlowID","string", true);
$dg->addColumn("","fullname","string", true);

$col = $dg->addColumn("مرحله","StepID","string");
$col->width = 60;
$col = $dg->addColumn("عنوان مرحله","StepDesc","string");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("پست مربوطه", "PostID", "string");
$col->editor = ColumnEditor::ComboBox(
		PdoDataAccess::runquery("select * from BSC_posts"), "PostID", "PostName","","", true);
$col->width = 170;

$col = $dg->addColumn("شخص مربوطه", "PersonID", "string");
$col->renderer = "function(v,p,r){return r.data.fullname;}";
$col->editor = "WFMObject.PersonCombo";
$col->width = 170;

$col = $dg->addColumn("","","");
$col->renderer = "WFM.upRender";
$col->sortable = false;
$col->width = 30;

$col = $dg->addColumn("","","");
$col->renderer = "WFM.downRender";
$col->sortable = false;
$col->width = 30;

$col = $dg->addColumn("حذف","","");
$col->renderer = "WFM.deleteStepRender";
$col->sortable = false;
$col->width = 40;

$dg->addButton = true;
$dg->addHandler = "function(){WFMObject.AddStep();}";

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){return WFMObject.saveStep(v,p,r);}";

$dg->height = 400;
$dg->width = 600;
$dg->DefaultSortField = "StepID";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "StepDesc";
$dg->editorGrid = true;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->emptyTextOfHiddenColumns = true;
$grid2 = $dg->makeGrid_returnObjects();

?>
<style type="text/css">
.step{background-image:url('/generalUI/ext4/resources/themes/icons/step.png') !important;}
</style>
<script>

var WFMObject = new WFM();

WFMObject.grid = <?= $grid?>;
WFMObject.grid.render(WFMObject.get("div_grid"));
WFMObject.StepsGrid = <?= $grid2 ?>;

</script>
<center>
	<br>
	<div id="div_grid"></div>
</center>