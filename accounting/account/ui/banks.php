<?php 
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

require_once '../../header.inc.php';
//________________  GET ACCESS  _________________
$accessObj = manage_access::getAccess($_POST["formID"]);
//-----------------------------------------------

require_once '../js/banks.js.php';

$dgh = new sadaf_datagrid("dg",$js_prefix_address."../data/banks.data.php?task=selectBank","div_dg", "BankForm");

$col = $dgh->addColumn("کد","bankID");
$col->width = 50;

$col = $dgh->addColumn("عنوان", "bankTitle", GridColumn::ColumnType_string);
$col->editor = ColumnEditor::TextField();
if($accessObj->removeFlag)
{
	$col = $dgh->addColumn("حذف", "", "string");
	$col->renderer = "Bank.deleteRender";
	$col->width = 50;
	$col->align = "center";
}
if($accessObj->addFlag)
{
	$dgh->addButton = true;
	$dgh->addHandler = "function(v,p,r){ return BankObject.Add(v,p,r);}";
}

$dgh->title = "بانک ها";
$dgh->width = 400;
$dgh->DefaultSortField = "bankID";
$dgh->autoExpandColumn = "bankTitle";
$dgh->DefaultSortDir = "ASC";
$dgh->height = 400;
$dgh->EnableSearch = false;
$dgh->EnablePaging = false;
if($accessObj->addFlag || $accessObj->editFlag)
{
	$dgh->enableRowEdit = true ;
	$dgh->rowEditOkHandler = "function(v,p,r){ return BankObject.Save(v,p,r);}";
}
$grid = $dgh->makeGrid_returnObjects();

?>
<script>

BankObject.grid = <?= $grid ?>;
BankObject.grid.render(BankObject.get("div_dg"));
	
</script>
<center>
<form id="BankForm"><br>
	<div id="div_dg"></div>
</form>
</center>
