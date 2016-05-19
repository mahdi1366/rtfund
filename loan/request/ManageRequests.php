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
$dg->addColumn("", "BranchID", "", true);
$dg->addColumn("", "BorrowerDesc", "", true);
$dg->addColumn("", "BorrowerID", "", true);
$dg->addColumn("", "LoanPersonID", "", true);
$dg->addColumn("", "ReqPersonID", "", true);
$dg->addColumn("", "IsEnded", "", true);

$col = $dg->addColumn("گیرنده وام", "LoanFullname");
$col->renderer = "function(v,p,r){return v == '' || v == null ? '<span style=color:red>' + r.data.BorrowerDesc + '</span>' : v;}";

$col = $dg->addColumn("<span style=font-size:8px>شناسه پرداخت</span>", "RequestID", "");
$col->renderer = "LoanRFID";
$col->width = 70;

$col = $dg->addColumn("شماره", "RequestID", "");
$col->width = 50;

$col = $dg->addColumn("شعبه", "BranchName", "");
$col->width = 100;

$col = $dg->addColumn("تاریخ", "ReqDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("مبلغ درخواست", "ReqAmount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("معرفی کننده", "ReqFullname");
$col->width = 100;

$col = $dg->addColumn("وضعیت", "StatusDesc", "");
$col->width = 100;

$col = $dg->addColumn('عملیات', '', 'string');
$col->renderer = "ManageRequest.OperationRender";
$col->width = 50;
$col->align = "center";

$dg->addObject("ManageRequestObject.FilterObj");

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 500;
$dg->pageSize = 15;
$dg->width = 800;
$dg->title = "درخواست های وام";
$dg->DefaultSortField = "ReqDate";
$dg->autoExpandColumn = "LoanFullname";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
ManageRequestObject.grid = <?= $grid ?>;
ManageRequestObject.grid.on("itemdblclick", function(view, record){
	framework.OpenPage("../loan/request/RequestInfo.php", "اطلاعات درخواست", {RequestID : record.data.RequestID});
});	
ManageRequestObject.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsEnded == "YES")
			return "greenRow";
		return "";
	}	
	
ManageRequestObject.grid.render(ManageRequestObject.get("DivGrid"));
</script>
<center><br>
	<div id="DivGrid"></div>
	<br>
	<div id="LoanInfo"></div>
</center>