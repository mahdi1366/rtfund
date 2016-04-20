<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.10
//-----------------------------
require_once '../header.inc.php';
require_once '../global/CNTconfig.class.php';
require_once 'ManageContracts.js.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "contract.data.php?task=SelectContracts", "div_dg");

$dg->addColumn("", "TemplateID", "", true);

$col = $dg->addColumn("شماره قرارداد", "ContractID");
$col->align = "center";

$col = $dg->addColumn("الگو", "TemplateTitle");
$col->width = 150;

$dg->addColumn("", "RegPersonID", "", true);

$col = $dg->addColumn("تاریخ ثبت", "RegDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("تاریخ شروع", "StartDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("تاریخ پایان", "EndDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("توضیحات", "description");
$col->ellipsis = 60;


$col = $dg->addColumn("وضعیت", "StatusCode");
$col->renderer = "function(v,p,record){
        if(record.data.StatusCode == ".CNTconfig::ContractStatus_Raw.") return 'خام';
        if(record.data.StatusCode == ".CNTconfig::ContractStatus_Sent." ) return ' ارسال برای تایید';
        if(record.data.StatusCode == ". CNTconfig::ContractStatus_Confirmed .") return ' تایید واحد قراردادها';
        ;}";

$col = $dg->addColumn("", "TemplateID");
$col->renderer = "ManageContractsObj.OperationRender";
$col->width = 30;

$dg->addButton("", "ایجاد قرارداد", "add", "function(){ManageContractsObj.AddContract();}");

$dg->title = "لیست قراردادها";
$dg->DefaultSortField = "RegDate";
$dg->DefaultSortDir = "desc";
$dg->autoExpandColumn = "description";
$dg->width = 780;
$dg->height = 500;
$dg->pageSize = 10;

$grid = $dg->makeGrid_returnObjects();
?>
<script>
    ManageContractsObj = new ManageContracts();
    ManageContractsObj.grid = <?= $grid ?>;
    ManageContractsObj.grid.render(ManageContractsObj.get("div_dg"));
</script>
<br>
<center>
    <div id="NewForm"></div>
    <div id="div_dg"></div>
</center>
