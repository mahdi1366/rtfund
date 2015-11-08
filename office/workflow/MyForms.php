<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;
require_once 'MyForms.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "wfm.data.php?task=SelectMyForms", "grid_div");

$dg->addColumn("", "FlowID", "", true);
$dg->addColumn("", "RowID", "", true);
$dg->addColumn("", "StepID", "", true);
$dg->addColumn("", "ObjectID", "", true);
$dg->addColumn("", "PersonID", "", true);
$dg->addColumn("", "ActionType", "", true);
$dg->addColumn("", "ActionComment", "", true);

$col = $dg->addColumn("نوع فرم دریافتی", "ObjectTypeDesc", "");
$col->width = 130;

$col = $dg->addColumn("تاریخ دریافت", "ActionDate", GridColumn::ColumnType_date);
$col->width = 90;

$col = $dg->addColumn("ارسال کننده", "fullname");
$col->width = 130;

$col = $dg->addColumn("وضعیت", "StepDesc", "");

$col = $dg->addColumn('عملیات', '', 'string');
$col->renderer = "MyForm.OperationRender";
$col->width = 50;
$col->align = "center";

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 500;
$dg->width = 770;
$dg->title = "درخواست های وام";
$dg->DefaultSortField = "ActionDate";
$dg->autoExpandColumn = "StepDesc";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
MyFormObject.grid = <?= $grid ?>;
MyFormObject.grid.render(MyFormObject.get("DivGrid"));
</script>
<center><br>
	<div id="DivGrid"></div>
	<br>
	<div id="LoanInfo"></div>
</center>