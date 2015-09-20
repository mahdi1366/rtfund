<?php 
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

require_once '../../header.inc.php';
//________________  GET ACCESS  _________________
$accessObj = manage_access::getAccess($_POST["formID"]);
//-----------------------------------------------

require_once '../js/accounts.js.php';

$dgh = new sadaf_datagrid("dg",$js_prefix_address."../data/accounts.data.php?task=selectAccount","div_dg", "AccountForm");

$col = $dgh->addColumn("کد","accountID");
$col->width = 30;

$col = $dgh->addColumn("عنوان","accountTitle");
$col->editor = ColumnEditor::TextField();

$col = $dgh->addColumn("بانک", "bankID", GridColumn::ColumnType_string);
$col->editor = ColumnEditor::ComboBox(PdoDataAccess::runquery("select * from banks"), "bankID", "bankTitle");
$col->width = 80;

$col = $dgh->addColumn("شعبه","branchTitle");
$col->editor = ColumnEditor::TextField();
$col->width = 80;

$col = $dgh->addColumn("شماره حساب","accountNo");
$col->editor = ColumnEditor::NumberField();
$col->width = 100;

$col = $dgh->addColumn("شروع دسته چک","StartNo");
$col->editor = ColumnEditor::NumberField();
$col->width = 80;

$col = $dgh->addColumn("پایان دسته چک","EndNo");
$col->editor = ColumnEditor::NumberField();
$col->width = 80;

$col = $dgh->addColumn("شروع دسته چک2","StartNo2");
$col->editor = ColumnEditor::NumberField();
$col->width = 80;

$col = $dgh->addColumn("پایان دسته چک2","EndNo2");
$col->editor = ColumnEditor::NumberField();
$col->width = 80;

if($accessObj->removeFlag)
{
	$col = $dgh->addColumn("حذف", "", "string");
	$col->renderer = "Account.deleteRender";
	$col->width = 50;
	$col->align = "center";
}
if($accessObj->addFlag)
{
	$dgh->addButton = true;
	$dgh->addHandler = "function(v,p,r){ return AccountObject.Add(v,p,r);}";
}

$dgh->title = "حساب های بانک";
$dgh->width = 780;
$dgh->DefaultSortField = "accountID";
$dgh->autoExpandColumn = "accountTitle";
$dgh->DefaultSortDir = "ASC";
$dgh->height = 400;
$dgh->EnableSearch = false;
$dgh->EnablePaging = false;
if($accessObj->addFlag || $accessObj->editFlag)
{
	$dgh->enableRowEdit = true ;
	$dgh->rowEditOkHandler = "function(v,p,r){ return AccountObject.Save(v,p,r);}";
}
$grid = $dgh->makeGrid_returnObjects();

?>
<script>

AccountObject.grid = <?= $grid ?>;
AccountObject.grid.render(AccountObject.get("div_dg"));
	
</script>
<center>
<form id="AccountForm"><br>
	<div id="div_dg"></div>
</form>
</center>
