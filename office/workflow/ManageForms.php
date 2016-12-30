<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................
require_once 'ManageForms.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "wfm.data.php?task=SelectAllForms", "grid_div");

$dg->addColumn("", "FlowID", "", true);
$dg->addColumn("", "RowID", "", true);
$dg->addColumn("", "StepID", "", true);
$dg->addColumn("", "ObjectID", "", true);
$dg->addColumn("", "PersonID", "", true);
$dg->addColumn("", "ActionType", "", true);
$dg->addColumn("", "ActionComment", "", true);
$dg->addColumn("", "url", "", true);
$dg->addColumn("", "parameter", "", true);
$dg->addColumn("", "target", "", true);

$col = $dg->addColumn("نوع فرم", "ObjectTypeDesc", "");
$col->width = 130;

$col = $dg->addColumn("اطلاعات فرم", "ObjectDesc", "");

$col = $dg->addColumn("وضعیت", "StepDesc", "");
$col->width = 130;

$col = $dg->addColumn('عملیات', '', 'string');
$col->renderer = "ManageForm.OperationRender";
$col->width = 50;
$col->align = "center";

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 500;
$dg->width = 770;
$dg->title = "درخواست های وام";
$dg->DefaultSortField = "ActionDate";
$dg->autoExpandColumn = "ObjectDesc";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
ManageFormObject.grid = <?= $grid ?>;
ManageFormObject.grid.render(ManageFormObject.get("DivGrid"));
</script>
<center><br>
	<div id="DivGrid"></div>
	<br>
	<div id="LoanInfo"></div>
</center>