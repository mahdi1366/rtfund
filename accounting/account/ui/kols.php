<?php 
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

require_once '../../header.inc.php';
//________________  GET ACCESS  _________________
$accessObj = manage_access::getAccess($_POST["formID"]);
//-----------------------------------------------

require_once '../js/kols.js.php';

$dgh = new sadaf_datagrid("dg",$js_prefix_address."../data/kols.data.php?task=selectKol","div_dg", "KolForm");

$col = $dgh->addColumn("کد","kolID");
$col->editor = ColumnEditor::NumberField(true);
$col->width = 50;

$col = $dgh->addColumn("عنوان", "kolTitle", GridColumn::ColumnType_string);
$col->editor = ColumnEditor::TextField();
if($accessObj->removeFlag)
{
	$col = $dgh->addColumn("حذف", "", "string");
	$col->renderer = "Kol.deleteRender";
	$col->width = 50;
	$col->align = "center";
}
if($accessObj->addFlag)
{
	$dgh->addButton = true;
	$dgh->addHandler = "function(v,p,r){ return KolObject.Add(v,p,r);}";
}

$dgh->title = "حساب های کل";
$dgh->width = 600;
$dgh->DefaultSortField = "kolID";
$dgh->autoExpandColumn = "kolTitle";
$dgh->DefaultSortDir = "ASC";
$dgh->height = 400;
$dgh->EnableSearch = false;
if($accessObj->addFlag || $accessObj->editFlag)
{
	$dgh->enableRowEdit = true ;
	$dgh->rowEditOkHandler = "function(v,p,r){ return KolObject.Save(v,p,r);}";
}
$grid = $dgh->makeGrid_returnObjects();

?>
<script>

KolObject.grid = <?= $grid ?>;
KolObject.grid.render(KolObject.get("div_dg"));
	
</script>
<center>
<form id="KolForm"><br>
	<div id="div_dg"></div>
</form>
</center>
