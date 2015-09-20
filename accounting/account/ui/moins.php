<?php 
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

require_once '../../header.inc.php';
//________________  GET ACCESS  _________________
$accessObj = manage_access::getAccess($_POST["formID"]);
//-----------------------------------------------

require_once '../js/moins.js.php';

$dgh = new sadaf_datagrid("dg",$js_prefix_address."../data/moins.data.php?task=selectMoin","div_dg");

$dgh->addColumn("","kolTitle","",true);

$col = $dgh->addColumn("حساب کل", "kolID");
$col->renderer = "function(v,p,r){return r.data.kolTitle;}";
$col->editor = "MoinObject.kolCombo";
$col->width = 100;

$col = $dgh->addColumn("کد","moinID");
$col->editor = ColumnEditor::NumberField(true);
$col->width = 50;

$col = $dgh->addColumn("عنوان", "moinTitle", GridColumn::ColumnType_string);
$col->editor = ColumnEditor::TextField();

if($accessObj->removeFlag)
{
	$col = $dgh->addColumn("حذف", "", "string");
	$col->renderer = "Moin.deleteRender";
	$col->width = 50;
	$col->align = "center";
}
if($accessObj->addFlag)
{
	$dgh->addButton = true;
	$dgh->addHandler = "function(v,p,r){ return MoinObject.Add(v,p,r);}";
}

$dgh->title = "حساب های معین";
$dgh->width = 600;
$dgh->DefaultSortField = "moinID";

$dgh->DefaultGroupField = "kolID";
$dgh->EnableGrouping = true;

$dgh->autoExpandColumn = "moinTitle";
$dgh->DefaultSortDir = "ASC";
$dgh->height = 550;
$dgh->EnableSearch = false;
if($accessObj->addFlag || $accessObj->editFlag)
{
	$dgh->enableRowEdit = true ;
	$dgh->rowEditOkHandler = "function(v,p,r){ return MoinObject.Save(v,p,r);}";
}
$grid = $dgh->makeGrid_returnObjects();

?>
<script>

MoinObject.grid = <?= $grid ?>;
MoinObject.grid.render(MoinObject.get("div_dg"));
	
</script>
<center>
<form id="MoinForm"><br>
	<div id="div_dg"></div>
</form>
</center>
