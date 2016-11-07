<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------
include_once("../header.inc.php");
require_once inc_dataGrid;
require_once 'forms.js.php';

$dg = new sadaf_datagrid("dg",$js_prefix_address . "vote.data.php?task=SelectAllForms","");

$col = $dg->addColumn("عنوان فرم","FormTitle","string");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("تاریخ شروع","StartDate",  GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();

$col = $dg->addColumn("تاریخ پایان","EndDate",  GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();

$col = $dg->addColumn("<font style=font-size:10px>کاربر</font>","IsStaff","string");
$col->renderer = "function(v){return (v=='YES') ? '٭' : '';}";
$col->editor = ColumnEditor::CheckField("","YES");
$col->align = "center";
$col->width = 35;

$col = $dg->addColumn("<font style=font-size:10px>مشتری</font>","IsCustomer","string");
$col->renderer = "function(v){return (v=='YES') ? '٭' : '';}";
$col->editor = ColumnEditor::CheckField("","YES");
$col->align = "center";
$col->width = 35;

$col = $dg->addColumn("<font style=font-size:10px>سهامدار</font>","IsShareholder","string");
$col->renderer = "function(v){return (v=='YES') ? '٭' : '';}";
$col->editor = ColumnEditor::CheckField("","YES");
$col->align = "center";
$col->width = 35;

$col = $dg->addColumn("<font style=font-size:10px>سرمایه گذار</font>","IsAgent","string");
$col->renderer = "function(v){return (v=='YES') ? '٭' : '';}";
$col->editor = ColumnEditor::CheckField("","YES");
$col->align = "center";
$col->width = 35;

$col = $dg->addColumn("<font style=font-size:10px>حامی</font>","IsSupporter","string");
$col->renderer = "function(v){return (v=='YES') ? '٭' : '';}";
$col->editor = ColumnEditor::CheckField("","YES");
$col->align = "center";
$col->width = 35;

$col = $dg->addColumn("<font style=font-size:10px>کارشناس</font>","IsExpert","string");
$col->renderer = "function(v){return (v=='YES') ? '٭' : '';}";
$col->editor = ColumnEditor::CheckField("","YES");
$col->align = "center";
$col->width = 35;

$col = $dg->addColumn("آیتم ها","","");
$col->renderer = "VOT_Form.ItemsRender";
$col->sortable = false;
$col->align = "center";
$col->width = 40;

$col = $dg->addColumn("نمایش","","");
$col->renderer = "VOT_Form.previewRender";
$col->sortable = false;
$col->width = 40;

$col = $dg->addColumn("حذف","FormID","");
$col->renderer = "VOT_Form.deleteRender";
$col->sortable = false;
$col->width = 40;

$dg->addButton = true;
$dg->addHandler = "function(){VOT_FormObject.Adding();}";

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){return VOT_FormObject.saveData(v,p,r);}";

$dg->height = 350;
$dg->width = 700;
$dg->DefaultSortField = "FormID";
$dg->autoExpandColumn = "FormTitle";
$dg->editorGrid = true;
$dg->title = "فرم های نظر سنجی";
$dg->EnablePaging = true;
$dg->EnableSearch = false;

$grid = $dg->makeGrid_returnObjects();

//------------------------------------------------------------------------------

$dg = new sadaf_datagrid("dg",$js_prefix_address . "vote.data.php?task=SelectItems","");

$dg->addColumn("","ItemID","string", true);
$dg->addColumn("","FormID","string", true);
$dg->addColumn("","ordering","string", true);

$col = $dg->addColumn("ترتیب","ordering","string");
$col->width = 60;

$col = $dg->addColumn("عنوان آیتم","ItemTitle","string");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("وزن","weight","string");
$col->width = 50;
$col->editor = ColumnEditor::NumberField();

$col = $dg->addColumn("نوع آیتم", "ItemType");
$col->editor = "VOT_FormObject.ItemTypeCombo";
$col->width = 100;

$col = $dg->addColumn("مقادیر", "ItemValues");
$col->editor = ColumnEditor::TextField(true);
$col->width = 200;

$col = $dg->addColumn("","","");
$col->renderer = "VOT_Form.upRender";
$col->sortable = false;
$col->width = 30;

$col = $dg->addColumn("","","");
$col->renderer = "VOT_Form.downRender";
$col->sortable = false;
$col->width = 30;

$col = $dg->addColumn("حذف","","");
$col->renderer = "VOT_Form.deleteItemRender";
$col->sortable = false;
$col->width = 40;

$dg->addButton = true;
$dg->addHandler = "function(){VOT_FormObject.AddItem();}";

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){return VOT_FormObject.saveItem(v,p,r);}";

$dg->height = 400;
$dg->width = 600;
$dg->DefaultSortField = "ordering";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "ItemTitle";
$dg->editorGrid = true;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->emptyTextOfHiddenColumns = true;
$grid2 = $dg->makeGrid_returnObjects();

?>
<script>

var VOT_FormObject = new VOT_Form();

VOT_FormObject.grid = <?= $grid?>;
VOT_FormObject.grid.render(VOT_FormObject.get("div_grid"));
VOT_FormObject.ItemsGrid = <?= $grid2 ?>;

</script>
<center>
	<br>
	<div id="div_grid"></div>
</center>