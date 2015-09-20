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
$col->renderer = "FGR_Form.stepsRender";
$col->sortable = false;
$col->width = 40;
//---------------------------

$dg->addButton("","ایجاد","add","function(){FGR_FormObject.LoadInfo('new')}");

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

$dg->addColumn('', "FormID", "string", true);
$dg->addColumn('كد جزء', "ElementID", "string", true);

$col = $dg->addColumn("ترتیب","ordering","string");
$col->width = 40;
$col->editor = ColumnEditor::NumberField(true);

$col = $dg->addColumn('عنوان', "ElTitle", "string");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn('عرض', "width", "string");
$col->editor = ColumnEditor::TextField(true);
$col->width = 40;
$typeArr = array(
	array("id" => "textfield" , "value" => "فیلد متنی"),
	array("id" => "numberfield" , "value" => "فیلد عددی"),
	array("id" => "currencyfield" , "value" => "فیلد مبلغ"),
	array("id" => "textarea" , "value" => "فیلد متن بلند"),
	array("id" => "shdatefield" , "value" => "تاریخ"),
	array("id" => "displayfield" , "value" => "اطلاعات نمایشی"),	
	array("id" => "combo" , "value" => "فیلد کشویی"),
	array("id" => "radio" , "value" => "انتخابی یکتا"),
	array("id" => "checkbox" , "value" => "انتخابی چندگانه")
);
$col = $dg->addColumn("نوع ستون","ElType","string");
$col->editor = ColumnEditor::ComboBox($typeArr, "id", "value", "", "editor_ElType");
$col->width = 100;

$col = $dg->addColumn("مقادیر آیتم","ElValue","string");
$col->editor = ColumnEditor::TextField(true, "editor_ElValue");

$temp = PdoDataAccess::runquery("select * from BaseTypes");
$col = $dg->addColumn("دامین مرجع","TypeID","string");
$col->editor = ColumnEditor::ComboBox($temp, "TypeID", "TypeDesc", "", "editor_TypeID", true);
$col->width = 100;

$col = $dg->addColumn("فیلد مرجع","RefField","string");
$col->editor = ColumnEditor::TextField(true);

$dg->addButton("","بازگشت","undo","function(){FGR_FormObject.Return('elementsWin')}");
$dg->addButton("","ایجاد","add","function(){FGR_FormObject.AddElement()}");

	
	$col = $dg->addColumn("حذف","","");
	$col->renderer = "FGR_Form.deleteElementRender";
	$col->sortable = false;
	$col->width = 40;
	
$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(store,record){return FGR_FormObject.SaveElement(store,record);}";

$dg->height = 400;
$dg->title = "اجزای فرم ";
$dg->width = 750;
$dg->editorGrid = true;
$dg->EnableSearch = false;
$dg->DefaultSortField = "ordering";
$dg->autoExpandColumn = "ElTitle";
$dg->EnablePaging = false;
$dg->DefaultSortDir = "asc";
$ElGrid = $dg->makeGrid_returnObjects();

//..............................................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "form.data.php?task=SelectSteps", "dg_grid");

$dg->addColumn('کد مرحله', "StepID", "string", true);

$col = $dg->addColumn('عنوان مرحله', "StepTitle", "string");
$col->width = 40;

$col = $dg->addColumn('مجری', "fullName", "string");
$col->width = 50;

$col = $dg->addColumn('مهلت به روز', "BreakDuration", "string");
$col->width = 20;

$dg->addColumn('', "elements", "string",true);
$dg->addColumn('', "PersonID", "string",true);
//---------------------------
$col = $dg->addColumn('بالا', "", "string");
$col->renderer = "FGR_Form.UPRender";
$col->sortable = false;
$col->width = 10;

$col = $dg->addColumn('پایین', "", "string");
$col->renderer = "FGR_Form.DOWNRender";
$col->sortable = false;
$col->width = 10;

$dg->addButton("","ایجاد مرحله", "add", "function(){FGR_FormObject.AddStep()}");
$dg->addButton("","بازگشت","undo","function(){FGR_FormObject.Return('stepsWin')}");

$col = $dg->addColumn("حذف","","");
$col->renderer = "FGR_Form.DeleteStepRender";
$col->sortable = false;
$col->width = 10;

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(store,record){return FGR_FormObject.SaveStep(store,record);}";

$dg->height = 300;
$dg->title = "مراحل گردش فرم";
$dg->width = 600;
$dg->DefaultSortField = "StepID";
$dg->autoExpandColumn = "StepTitle";
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$stepsGrid = $dg->makeGrid_returnObjects();

?>
<script>
	FGR_FormObject.grid = <?= $grid?>;
	FGR_FormObject.grid.render(FGR_FormObject.get("dg_forms"));

	FGR_FormObject.ElementsGrid = <?= $ElGrid?>;
	FGR_FormObject.ElementsGrid.plugins[0].on("beforeedit", function(editor,e){
		
		editor = FGR_FormObject.ElementsGrid.plugins[0].getEditor();
		editor.down("[itemId=editor_ElValue]").setDisabled(e.record.data.ElType != "combo");
		editor.down("[itemId=editor_TypeID]").setDisabled(e.record.data.ElType != "combo");
		
		editor.down("[itemId=editor_ElType]").addListener("change", function(){
			
			editor = FGR_FormObject.ElementsGrid.plugins[0].getEditor();
			
			editor.down("[itemId=editor_ElValue]").setDisabled(true);
			editor.down("[itemId=editor_TypeID]").setDisabled(true);
				
			if(this.getValue() == "combo")
			{
				editor.down("[itemId=editor_ElValue]").setDisabled(false);
				editor.down("[itemId=editor_TypeID]").setDisabled(false);
			}
		});
	});
	
	FGR_FormObject.StepsGrid = <?= $stepsGrid?>;
	
</script>
<center>
	<br>
	<div id="newForm"></div>
	<br>
	<div id="dg_forms"></div>
	<br>
	<div id="div_tabs"></div>
</center>
