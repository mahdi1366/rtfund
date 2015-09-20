<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.06
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

require_once 'tafsilis.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "baseinfo.data.php?task=GetAllTafsilis", "grid_div");

$dg->addColumn("کد", "TafsiliID", "", true);
$dg->addColumn("", "TafsiliType", "", true);

$col = $dg->addColumn("کد تفصیلی", "TafsiliCode");
$col->editor = ColumnEditor::TextField();
$col->width = 100;

$col = $dg->addColumn("عنوان تفصیلی", "TafsiliDesc", "");
$col->editor = ColumnEditor::TextField();

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){TafsiliObject.AddTafsili();}";
}
if($accessObj->RemoveFlag)
{
	$col = $dg->addColumn("عملیات", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return Tafsili.DeleteRender(v,p,r);}";
	$col->width = 50;
}
$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(){return TafsiliObject.SaveTafsili();}";

$dg->title = "لیست تفصیلی ها";
$dg->height = 500;
$dg->width = 750;
$dg->EnablePaging = false;
$dg->DefaultSortField = "TafsiliDesc";
$dg->autoExpandColumn = "TafsiliDesc";
$grid = $dg->makeGrid_returnObjects();

?>
<center>
    <form id="mainForm">
        <br>
        <div id="div_selectGroup"></div>
        <br>
		<div id="newDiv"></div>
        <div id="grid_div"></div>
    </form>
</center>
<script>
    var TafsiliObject = new Tafsili();	

	TafsiliObject.grid = <?= $grid ?>;
	TafsiliObject.grid.plugins[0].on("beforeedit", function(editor,e){
		if(!e.record.data.TafsiliID)
			return AccountObj.AddAccess;
		return AccountObj.EditAccess;
	});
</script>