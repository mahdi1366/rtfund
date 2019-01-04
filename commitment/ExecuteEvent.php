<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 97.10
//-----------------------------

require_once "../header.inc.php";
require_once inc_dataGrid;

$EventID = (int) $_POST["EventID"];
 
$dg = new sadaf_datagrid("dg", $js_prefix_address . "ExecuteEvent.data.php?task=selectEventRows&EventID=" . 
		$EventID, "div_detail_dg");

$dg->addColumn(" ", "RowID", "", true);
$dg->addColumn(" ", "EventID", "", true);
$dg->addColumn(" ", "CostID", "", true);
$dg->addColumn(" ", "CostType", "", true);

$dg->addColumn(" ", "TafsiliType", "", true);
$dg->addColumn(" ", "TafsiliType2", "", true);
$dg->addColumn(" ", "TafsiliType3", "", true);
$dg->addColumn(" ", "TafsiliTypeDesc", "", true);
$dg->addColumn(" ", "TafsiliType2Desc", "", true);
$dg->addColumn(" ", "TafsiliType3Desc", "", true);

$dg->addColumn(" ", "Tafsili", "", true);
$dg->addColumn(" ", "Tafsili2", "", true);
$dg->addColumn(" ", "Tafsili3", "", true);
$dg->addColumn(" ", "TafsiliDesc", "", true);
$dg->addColumn(" ", "Tafsili2Desc", "", true);
$dg->addColumn(" ", "Tafsili3Desc", "", true);

$dg->addColumn(" ", "CostDesc", "", true);
$dg->addColumn(" ", "IsActive", "", true);
$dg->addColumn(" ", "ChangeDate", "", true);
$dg->addColumn(" ", "changePersonName", "", true);
$dg->addColumn(" ", "PriceDesc", "", true);
$dg->addColumn(" ", "DocDesc", "", true);
$dg->addColumn(" ", "ComputeItemID", "", true);

$col = $dg->addColumn(" کد حساب ", "CostCode");
$col->width = 65;

$col = $dg->addColumn(" عنوان حساب ", "CostDesc");

$col = $dg->addColumn("تفصیلی", "TafsiliID");
$col->renderer = "ExecuteEvent.TafsiliRenderer1";
$col->width = 100; 

$col = $dg->addColumn("تفصیلی2", "TafsiliID2");
$col->renderer = "ExecuteEvent.TafsiliRenderer2";
$col->width = 100; 

$col = $dg->addColumn("تفصیلی3", "TafsiliID3");
$col->renderer = "ExecuteEvent.TafsiliRenderer3";
$col->width = 100; 

$col = $dg->addColumn("آیتم محاسباتی", "ComputeItemDesc");
$col->width = 120;
$col->ellipsis = 40; 

$col = $dg->addColumn("بدهکار", "DebtorAmount");
$col->renderer = "ExecuteEvent.DebtorAmountRenderer";
$col->width = 100; 

$col = $dg->addColumn("بستانکار", "CreditorAmount");
$col->renderer = "ExecuteEvent.CreditorAmountRenderer";
$col->width = 100; 

$dg->DefaultSortField = "RowID";
$dg->autoExpandColumn = "CostDesc";
$dg->DefaultSortDir = "DESC";
$dg->EnableRowNumber = false;
$dg->EnableSearch = false;
$dg->height = 500;
$dg->emptyTextOfHiddenColumns = true;
$dg->EnablePaging = false;
$grid = $dg->makeGrid_returnObjects();

require_once './ExecuteEvent.js.php';

?>
<center>
	<form id="MainForm">
		<div id="div_grid"></div>
	</form>
</center>