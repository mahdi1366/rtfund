<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 97.10
//-----------------------------

require_once "../header.inc.php";
require_once inc_dataGrid;
require_once '../loan/request/request.class.php';
require_once './ExecuteEvent.class.php';

$EventID = $_POST["EventID"]*1;

$SourcesArr = array();
$SourceIDs = "";
switch($EventID)
{
	case EVENT_LOAN_PAYMENT:
		$SourcesArr = array(
			"RequestID" => $_POST["RequestID"],
			"PartID" => LON_ReqParts::GetValidPartObj($_POST["RequestID"])->PartID,
			"PayID" => $_POST["PayID"]);
		$SourceIDs = "&RequestID=" . $_POST["RequestID"] . "&PayID=" . $_POST["PayID"];
}

if(count($SourcesArr)>0)
{
	$LocalNo = ExecuteEvent::GetRegisteredDoc($EventID, $SourcesArr);
	//print_r(ExceptionHandler::PopAllExceptions());
	if($LocalNo !== false)
	{
		echo "<center><br><h1>" . 
				"رویداد مربوط به شرایط انتخابی شما قبلا در سند شماره " . $LocalNo . 
				" صادر شده و قادر به صدور مجدد سند نمی باشید" . 
				"</h1></center>";
		die();
	}
}

$dg = new sadaf_datagrid("dg", $js_prefix_address . "ExecuteEvent.data.php?task=selectEventRows&EventID=" . 
		$EventID . $SourceIDs, "div_detail_dg");

$dg->addColumn(" ", "RowID", "", true);
$dg->addColumn(" ", "EventID", "", true);
$dg->addColumn(" ", "CostID", "", true);
$dg->addColumn(" ", "CostType", "", true);

$dg->addColumn(" ", "TafsiliType", "", true);
$dg->addColumn(" ", "TafsiliType2", "", true);
$dg->addColumn(" ", "TafsiliType3", "", true);

$dg->addColumn(" ", "TafsiliDesc", "", true);
$dg->addColumn(" ", "Tafsili2Desc", "", true);
$dg->addColumn(" ", "Tafsili3Desc", "", true);

$dg->addColumn(" ", "TafsiliTypeDesc", "", true);
$dg->addColumn(" ", "TafsiliType2Desc", "", true);
$dg->addColumn(" ", "TafsiliType3Desc", "", true);

$dg->addColumn(" ", "TafsiliValue1", "", true);
$dg->addColumn(" ", "TafsiliValue2", "", true);
$dg->addColumn(" ", "TafsiliValue3", "", true);

$dg->addColumn(" ", "CostDesc", "", true);
$dg->addColumn(" ", "IsActive", "", true);
$dg->addColumn(" ", "ChangeDate", "", true);
$dg->addColumn(" ", "changePersonName", "", true);
$dg->addColumn(" ", "PriceDesc", "", true);
$dg->addColumn(" ", "DocDesc", "", true);
$dg->addColumn(" ", "ComputeItemID", "", true);
$dg->addColumn(" ", "ComputeItemDesc", "", true);

$col = $dg->addColumn(" کد حساب ", "CostCode");

$col = $dg->addColumn("تفصیلی", "Tafsili");
$col->renderer = "ExecuteEvent.TafsiliRenderer1";
$col->width = 180; 

$col = $dg->addColumn("تفصیلی2", "Tafsili2");
$col->renderer = "ExecuteEvent.TafsiliRenderer2";
$col->width = 180; 

$col = $dg->addColumn("تفصیلی3", "Tafsili3");
$col->renderer = "ExecuteEvent.TafsiliRenderer3";
$col->width = 180; 

$col = $dg->addColumn("بدهکار", "DebtorAmount", "int");
$col->renderer = "ExecuteEvent.DebtorAmountRenderer";
$col->width = 130; 
$col->summaryType = GridColumn::SummeryType_sum;
$col->summaryRenderer = "function(value){return Ext.util.Format.Money(value);}";

$col = $dg->addColumn("بستانکار", "CreditorAmount", "int");
$col->renderer = "ExecuteEvent.CreditorAmountRenderer";
$col->width = 130; 
$col->summaryType = GridColumn::SummeryType_sum;
$col->summaryRenderer = "function(value){return Ext.util.Format.Money(value);}";

$dg->addPlugin("this.RowDetails");
$dg->addButton("", "صدور سند رویداد", "send", "function(){ExecuteEventObj.RegisterEventDoc()}");

$dg->EnableSummaryRow = true;
$dg->DefaultSortField = "RowID";
$dg->autoExpandColumn = "CostCode";
$dg->DefaultSortDir = "DESC";
$dg->EnableRowNumber = false;
$dg->EnableSearch = false;
$dg->height = 460;
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