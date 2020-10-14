<?php
//-----------------------------
//	Date		: 1397.11
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;
var_dump($_REQUEST);
//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "AgencyCnt.data.php?task=SelectAgencyCnt", "grid_div");

$dg->addColumn("", "agencyCntID", "", true);
$dg->addColumn("", "AgencyID", "", true);
$dg->addColumn("", "agencyWage", "", true);
$dg->addColumn("", "selfWage", "", true);
$dg->addColumn("", "commitAmount", "", true);
$dg->addColumn("", "receiptOption", "", true);
$dg->addColumn("", "defrayTime", "", true);

$col = $dg->addColumn("عنوان قرارداد", "title");
$col->align = "center";
$col->width = 350;

$col = $dg->addColumn("تاریخ شروع", "startDate", GridColumn::ColumnType_date);
$col->width = 100;

$col = $dg->addColumn("تاریخ پايان", "endDate", GridColumn::ColumnType_date);
$col->width = 100;

$col = $dg->addColumn('عملیات', '', 'string');
$col->renderer = "ManageAgencyCnt.OperationRender";
$col->width = 50;
$col->align = "center";

$dg->title = "لیست قراردادها";
$dg->DefaultSortField = "agencyCntID";
$dg->DefaultSortDir = "desc";
/*$dg->autoExpandColumn = "PersonFullname";*/
$dg->emptyTextOfHiddenColumns = true;
$dg->width = 640;
$dg->height = 500;
$dg->pageSize = 10;
$dg->EnableRowNumber = true;
$grid = $dg->makeGrid_returnObjects();


?>
<form id="mainForm">
	<div id="DivPanel" style="margin:8px;width:98%"></div>
</form>
<? require_once 'manageAgencyCnt.js.php';?>
