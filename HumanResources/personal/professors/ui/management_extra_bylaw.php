<?php
//---------------------------
// programmer:	SH.Jafarkhani
// create Date:	90.06
//---------------------------
require_once("../../../header.inc.php");
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg",$js_prefix_address . "../data/management_extra_bylaw.data.php?task=selectAll","divGRID");

$col= $dg->addColumn("تاریخ شروع","from_date", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 100;

$col= $dg->addColumn("تاریخ پایان","to_date", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 100;

$col = $dg->addColumn("شرح", "description", "string");
$col->editor = ColumnEditor::TextField(true);
$dg->addColumn("", "bylaw_id", "string", true);

$col = $dg->addColumn("اقلام", "");
$col->renderer = "function(v,p,r){return ByLawObject.itemsRender(v,p,r);}";
$col->align = "center";
$col->width = 50;

$col = $dg->addColumn("حذف", "");
$col->renderer = "function(v,p,r){return ByLawObject.DeleteRender(v,p,r);}";
$col->align = "center";
$col->width =50;

$dg->addButton = true;
$dg->addHandler = "function(){ByLawObject.Add();}";

$dg->addButton("copy", "تهیه کپی", "copy", "function(){ByLawObject.Copy();}");

$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->width = 600 ;
$dg->DefaultSortField = "from_date" ;
$dg->autoExpandColumn = "description";
$dg->EnableRowNumber = true;
$dg->enableRowEdit = true;
$dg->DefaultSortField = "bylaw_id";
$dg->rowEditOkHandler = "function(store,record){return ByLawObject.Save(store,record);}";
$dg->title = "بخشنامه فوق العاده مديريت";
$dg->collapsible = true;
$dg->collapsed = false;
$grid = $dg->makeGrid_returnObjects();

//------------------------------------------------------------------------------
//
$dg = new sadaf_datagrid("dg",$js_prefix_address . "../data/management_extra_bylaw.data.php?task=selectAllItems","divItemsGrid", "mainForm");

$col= $dg->addColumn("شماره شناسایی پست","post_id");
$col->width = 150;

$col= $dg->addColumn("شماره - عنوان پست","post_title");


$col = $dg->addColumn("داخل شمول", "included", "string");
$col->width = 70;
$col->align = "center";

$col = $dg->addColumn("مقدار", "value", "int");
$col->editor = ColumnEditor::NumberField(false);
$col->width = 90;

$dg->addColumn("", "bylaw_id", "", true);

$dg->width = 800;
$dg->height = 500;
$dg->DefaultSortField = "post_id";

$col = $dg->addColumn("حذف", "");
$col->renderer = "function(v,p,r){return ByLawObject.ItemDeleteRender(v,p,r);}";
$col->align = "center";
$col->width = 50;

$dg->addButton = true;
$dg->addHandler = "function(){ByLawObject.AddItem();}";

$dg->EnableRowNumber = true;
$dg->enableRowEdit = true;
$dg->autoExpandColumn = "post_title";
$dg->rowEditOkHandler = "function(store,record){return ByLawObject.SaveItem(store,record);}";
$dg->title = "اقلام بخشنامه فوق العاده مديريت";
$itemsgrid = $dg->makeGrid_returnObjects();

require_once '../js/management_extra_bylaw.js.php';
?>
<form id="mainForm">
<center>
	<div id="divGRID"></div><br><br>
	<input type="hidden" id="bylaw_id" name="bylaw_id">
	<div id="divItemsGrid"></div>

	<div id="newWin" class="x-hidden">
		<div id="newPnl">
			<table width="100%" style="background-color:white">
				<tr>
					<td width="30%"> انتخاب پست :</td>
					<td> <input type="text" id="post_id" name="post_id"></td>
				</tr>
				<tr>
					<td>شماره-عنوان پست :</td>
					<td id="post_title"></td>
				</tr>
				<tr>
					<td>مقدار :</td>
					<td><input type="text" id="value" name="value" class="x-form-text x-form-field"></td>
				</tr>
			</table>
		</div>
	</div>
</center>
</form>
