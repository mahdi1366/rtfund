<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.06
//---------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;

require_once 'buildForm.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "form.data.php?task=formsSelect" ,"dg_forms");

$dg->addColumn('كد فرم', "FormID", "string", true);

$col = $dg->addColumn("فایل","FileInclude","");
$col->renderer = "FGR_Form.FileRender";
$col->sortable = false;
$col->width = 40;
//---------------------------
$col = $dg->addColumn('نام فرم', "FormName", "string");

$col = $dg->addColumn("آیتم وابسته","reference","string");
$col->renderer = "FGR_Form.referenceRender";
$col->width = 70;

$col = $dg->addColumn("ویرایش","","");
$col->renderer = "FGR_Form.editRender";
$col->sortable = false;
$col->width = 40;
//---------------------------
$col = $dg->addColumn("حذف","","");
$col->renderer = "FGR_Form.deleteRender";
$col->sortable = false;
$col->width = 40;
//---------------------------
$col = $dg->addColumn("اجزاء","","");
$col->renderer = "FGR_Form.elementRender";
$col->sortable = false;
$col->width = 40;
//---------------------------
$col = $dg->addColumn("گردش","","");
$col->renderer = "FGR_Form.workflowRender";
$col->sortable = false;
$col->width = 40;
//---------------------------

$dg->addButton("Add","ایجاد","add","function(){FGR_FormObject.LoadInfo('new')}");

$dg->height = 300;
$dg->title = "فرم های ایجاد شده";
$dg->width = 700;
$dg->EnableSearch = false;
$dg->autoExpandColumn = "FormName";
$dg->DefaultSortField = "FormName";
$dg->DefaultSortDir = "asc";
$grid = $dg->makeGrid_returnObjects();

//..............................................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "form.data.php?task=SelectElements","dg_elements");

$dg->addColumn('كد جزء', "ElementID", "string", true);

$col = $dg->addColumn("ترتیب","ordering","string");
$col->width = 40;
$col->editor = ColumnEditor::NumberField();

$col = $dg->addColumn('عنوان', "ElementTitle", "string");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn('عرض', "width", "string");
$col->editor = ColumnEditor::TextField();
$col->width = 40;
$typeArr = array(
	array("id" => "textfield" , "value" => "متن ساده"),
	array("id" => "shdatefield" , "value" => "تاریخ"),
	array("id" => "displayfield" , "value" => "اطلاعات نمایشی"),
	array("id" => "textarea" , "value" => "توضیحات"),
	array("id" => "combo" , "value" => "کشویی"),
	array("id" => "radio" , "value" => "انتخابی یکتا"),
	array("id" => "checkbox" , "value" => "انتخابی"),
	array("id" => "bind" , "value" => "مقدار داده اصلی")
);
$col = $dg->addColumn("نوع ستون","ElementType","string");
$col->editor = ColumnEditor::ComboBox($typeArr, "id", "value");
$col->width = 70;

$col = $dg->addColumn("","ElementValue","string");
$col->editor = ColumnEditor::TextField(true);

$col = $dg->addColumn("","referenceField","string");
$col->editor = ColumnEditor::TextField(true);

$dg->addColumn("","referenceInfoID","string", true);
//---------------------------
$dg->addButton("return","بازگشت","undo","function(){FGR_FormObject.Return()}");

$dg->addButton("Add","ایجاد","add","function(){FGR_FormObject.AddElement()}");

	
	$col = $dg->addColumn("حذف","","");
	$col->renderer = "deleteElementRender";
	$col->sortable = false;
	$col->width = 30;
	
$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){return ManageCreditDescObj.SaveData(v,p,r);}";


//---------------------------
$dg->height = 400;
$dg->title = "اجزای فرم ";
$dg->width = 700;
$dg->editorGrid = true;
$dg->EnableSearch = false;
$dg->DefaultSortField = "ordering";
$dg->EnablePaging = false;
$dg->DefaultSortDir = "asc";
$ElGrid = $dg->makeGrid_returnObjects();
?>
<script>
	FGR_FormObject.grid = <?= $grid?>;
	FGR_FormObject.grid.render(FGR_FormObject.get("dg_forms"));

	FGR_FormObject.ElementsGrid = <?= $ElGrid?>;
</script>
<center>
	<br>
	<div id="newForm"></div>
	<br>
	<div id="dg_forms"></div>
	<br>
	<div id="div_tabs"></div>
</center>
