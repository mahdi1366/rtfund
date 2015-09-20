<?php 
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

require_once '../../header.inc.php';
//________________  GET ACCESS  _________________
$accessObj = manage_access::getAccess($_POST["formID"]);
//-----------------------------------------------

require_once '../js/tafsilis.js.php';

$dgh = new sadaf_datagrid("dg",$js_prefix_address."../data/tafsilis.data.php?task=selectTafsili&all=true","div_dg", "TafsiliForm");

$col = $dgh->addColumn("کد","tafsiliID");
$col->editor = ColumnEditor::NumberField(true);
$col->width = 50;

$col = $dgh->addColumn("عنوان", "tafsiliTitle", GridColumn::ColumnType_string);
//$col->editor = ColumnEditor::TextField();
$col->editor = "TafsiliObject.tafsiliCombo";
$col->width = 250;

$col = $dgh->addColumn("توضیحات","description");
$col->editor = ColumnEditor::TextField(true);

if($accessObj->removeFlag)
{
	$col = $dgh->addColumn("حذف", "", "string");
	$col->renderer = "Tafsili.deleteRender";
	$col->width = 50;
	$col->align = "center";
}
if($accessObj->addFlag)
{
	$dgh->addButton = true;
	$dgh->addHandler = "function(v,p,r){ return TafsiliObject.Add(v,p,r);}";
}

$dgh->addColumn("","IsActive","",true);

$dgh->title = "حساب های تفصیلی";
$dgh->width = 700;
$dgh->DefaultSortField = "tafsiliID";
$dgh->autoExpandColumn = "description";
$dgh->DefaultSortDir = "ASC";
$dgh->height = 400;

if($accessObj->addFlag || $accessObj->editFlag)
{
	$dgh->enableRowEdit = true ;
	$dgh->rowEditOkHandler = "function(v,p,r){ return TafsiliObject.Save(v,p,r);}";
}
$grid = $dgh->makeGrid_returnObjects();

?>
<script>

TafsiliObject.grid = <?= $grid ?>;
TafsiliObject.grid.getView().getRowClass = function(record, index)
{
	if(record.data.IsActive == "0")
		return "pinkRow";
	return "";
}
TafsiliObject.grid.plugins[0].on("beforeedit", function(editor,e){
	if(e.record.data.IsActive == "0")
		return false;
});
TafsiliObject.grid.render(TafsiliObject.get("div_dg"));
	
</script>
<style type="text/css">
.pinkRow, .pinkRow td,.pinkRow div{ background-color:#FFB8C9 !important;}
</style>
<center>
<form id="TafsiliForm"><br>
	<div id="div_filter">
		<table id="tbl_filter" style="width:100%">
			<tr>
				<td>کد تفصیلی از :</td>
				<td><input type="text" class="x-form-text x-form-field" name="from_tafsiliID"></td>
				<td>تا :</td>
				<td><input type="text" class="x-form-text x-form-field" name="to_tafsiliID"></td>
			</tr>
		</table>		
	</div>
	<br>
	<div id="div_dg"></div>
</form>
</center>
