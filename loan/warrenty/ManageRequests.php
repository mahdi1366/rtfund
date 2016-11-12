<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once '../header.inc.php';
require_once 'config.inc.php';
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

require_once 'ManageRequests.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "request.data.php?task=SelectAllWarrentyRequests", "grid_div");

$dg->addColumn("", "StatusID", "", true);
$dg->addColumn("", "PersonID", "", true);
$dg->addColumn("", "TypeID", "", true);
$dg->addColumn("", "IsEnded", "", true);
$dg->addColumn("", "LetterNo", "", true);
$dg->addColumn("", "LetterDate", "", true);
$dg->addColumn("", "DocID", "", true);
$dg->addColumn("", "DocStatus", "", true);
$dg->addColumn("", "IsBlock", "", true);
$dg->addColumn("", "BranchID", "", true);
$dg->addColumn("", "BranchName", "", true);
$dg->addColumn("", "RegisterAmount", "", true);

$col = $dg->addColumn("نوع", "TypeDesc");
$col->renderer = "function(v,p,r){p.tdAttr = \"data-qtip='\"+r.data.BranchName+\"'\"; return v;}";
$col->width = 80;

$col = $dg->addColumn("ضمانت خواه", "fullname");

$col = $dg->addColumn("شماره", "RequestID", "");
$col->width = 50;

$col = $dg->addColumn("مبلغ درخواست", "amount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("سازمان مربوطه", "organization");
$col->renderer = "WarrentyRequest.OrgRender";
$col->width = 120;

$col = $dg->addColumn("تاریخ شروع", "StartDate", GridColumn::ColumnType_date);
$col->width = 70;

$col = $dg->addColumn("تاریخ پایان", "EndDate", GridColumn::ColumnType_date);
$col->width = 70;

$col = $dg->addColumn("کارمزد", "wage");
$col->width = 40;
$col->align = "center";

$col = $dg->addColumn("وضعیت", "StepDesc", "");
$col->width = 80;

$col = $dg->addColumn("سند", "LocalNo", "");
$col->width = 40;

$col = $dg->addColumn('عملیات', '', 'string');
$col->renderer = "WarrentyRequest.OperationRender";
$col->width = 50;
$col->align = "center";

$dg->addObject("WarrentyRequestObject.FilterObj");

if($accessObj->AddFlag)
	$dg->addButton("", "ایجاد ضمانتنامه جدید", "add", "function(){WarrentyRequestObject.AddNew();}");

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 500;
$dg->pageSize = 15;
$dg->width = 800;
$dg->title = "درخواست های وام";
$dg->DefaultSortField = "RequestID";
$dg->autoExpandColumn = "fullname";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
WarrentyRequestObject.grid = <?= $grid ?>;
WarrentyRequestObject.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsEnded == "YES")
			return "greenRow";
		return "";
	}	
	
WarrentyRequestObject.grid.render(WarrentyRequestObject.get("DivGrid"));
</script>
<center><br>
	<div><div id="RequestInfo"></div></div>
	<br>
	<div id="DivGrid"></div>	
</center>