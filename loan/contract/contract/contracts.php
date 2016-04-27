<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.10
//-----------------------------
require_once '../../header.inc.php';
require_once 'contracts.js.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "contract.data.php?task=SelectContracts", "div_dg");

$dg->addColumn("", "TemplateID", "", true);
$dg->addColumn("", "IsStarted", "", true);
$dg->addColumn("", "IsEnded", "", true);
$dg->addColumn("", "RegDate", "", true);
$dg->addColumn("", "StatusID", "", true);

$col = $dg->addColumn("شماره قرارداد", "ContractID");
$col->align = "center";
$col->width = 110;

$col = $dg->addColumn("الگو", "TemplateTitle");
$col->width = 150;

$dg->addColumn("", "RegPersonID", "", true);

$col = $dg->addColumn("تاریخ شروع", "StartDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("تاریخ پایان", "EndDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("طرف قرارداد", "PersonFullname");
$col->ellipsis = 60;

$col = $dg->addColumn("وضعیت تایید", "StepDesc");

$col = $dg->addColumn("وضعیت", "StatusDesc");

$col = $dg->addColumn("", "TemplateID");
$col->renderer = "ManageContractsObj.OperationRender";
$col->width = 30;

$dg->addButton("", "ایجاد قرارداد", "add", "function(){ManageContractsObj.AddContract();}");

$dg->title = "لیست قراردادها";
$dg->DefaultSortField = "ContractID";
$dg->DefaultSortDir = "desc";
$dg->autoExpandColumn = "PersonFullname";
$dg->width = 780;
$dg->height = 500;
$dg->pageSize = 10;

$grid = $dg->makeGrid_returnObjects();
?>
<script>
    ManageContractsObj = new ManageContracts();
    ManageContractsObj.grid = <?= $grid ?>;
	ManageContractsObj.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsEnded == "YES")
			return "greenRow";
		
		if(record.data.StatusID == "2")
			return "pinkRow";
		
		if(record.data.StatusID == "3")
			return "violetRow";
		
		return "";
	}	
    ManageContractsObj.grid.render(ManageContractsObj.get("div_dg"));
	
</script>
<br>
<center>
    <div id="NewForm"></div>
    <div id="div_dg"></div>
</center>
