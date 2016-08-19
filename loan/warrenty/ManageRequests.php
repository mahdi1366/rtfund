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
$dg->addColumn("", "PersonID", "", true);
$dg->addColumn("", "IsEnded", "", true);

$col = $dg->addColumn("ضمانت خواه", "fullname");

$col = $dg->addColumn("شماره", "RequestID", "");
$col->width = 50;

$col = $dg->addColumn("تاریخ درخواست", "ReqDate", GridColumn::ColumnType_date);
$col->width = 90;

$col = $dg->addColumn("مبلغ درخواست", "amount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("سازمان مربوطه", "organization");
$col->width = 150;

$col = $dg->addColumn("وضعیت", "StatusDesc", "");
$col->width = 100;

$col = $dg->addColumn('عملیات', '', 'string');
$col->renderer = "WarrentyRequest.OperationRender";
$col->width = 50;
$col->align = "center";

$dg->addObject("WarrentyRequestObject.FilterObj");

$dg->addButton("", "ایجاد درخواست جدید", "add", "function(){WarrentyRequestObject.AddNew();}");

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 500;
$dg->pageSize = 15;
$dg->width = 800;
$dg->title = "درخواست های وام";
$dg->DefaultSortField = "ReqDate";
$dg->autoExpandColumn = "fullname";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
WarrentyRequestObject.grid = <?= $grid ?>;
WarrentyRequestObject.grid.on("itemdblclick", function(view, record){
	framework.OpenPage("../loan/warrenty/RequestInfo.php", "اطلاعات درخواست", {RequestID : record.data.RequestID});
});	
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