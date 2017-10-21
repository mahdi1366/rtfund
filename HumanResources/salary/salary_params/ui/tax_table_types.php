<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.09
//---------------------------
require_once '../../../header.inc.php';
require_once inc_dataGrid;


require_once '../js/tax_table_types.js.php';
 
$tdg = new sadaf_datagrid("tdg", $js_prefix_address . "../data/tax_table_types.data.php?task=selectAll","tdgDiv");

$tdg->addColumn('', "tax_table_type_id", "int", true);
$tdg->addColumn('', "person_type", "int", true);

$col = $tdg->addColumn('عنوان جدول', "title", "string");
$col->editor = ColumnEditor::TextField();

$col = $tdg->addColumn("نوع شخص","person_title","int");
$col->width = 100 ;

	$tdg->addButton = true;
	$tdg->addHandler =  "function(v,p,r){ return TaxTableTypeObject.AddSPT(v,p,r);}";

$col = $tdg->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){ return TaxTableTypes.opRender(v,p,r);}";
$col->width = 60;

$tdg->title = " انواع جدول مالیاتی";
$tdg->width = 600;
$tdg->autoExpandColumn = "title";
$tdg->DefaultSortField = "person_type";
$tdg->DefaultSortDir = "ASC";
$tdg->EnableSearch = false;
$tdg->pageSize = 20 ;

    $tdg->enableRowEdit = true ;
    $tdg->rowEditOkHandler = "function(v,p,r){ return TaxTableTypeObject.editPST(v,p,r);}";

$tdg->collapsible = true ;
$tdg->collapsed = false ;
$grid = $tdg->makeGrid_returnObjects();
//..............................................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address .
						 "../data/tax_tables.data.php?task=selectAll","dgDiv");

$dg->addColumn('', "tax_table_id", "int", true);
$dg->addColumn('', "tax_table_type_id", "int", true);

$col = $dg->addColumn('از تاریخ', "from_date", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 200;

$col = $dg->addColumn('تا تاریخ', "to_date", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 200;

$dg->addButton = true;
$dg->addHandler =  "function(v,p,r){ return TaxTableTypeObject.Adding(v,p,r);}";


$col = $dg->addColumn("حذف", "", "string");
$col->renderer = "function(v,p,r){ return TaxTableTypes.opDelRender(v,p,r);}";
$col->width = 50;

$dg->width = 450;
$dg->DefaultSortField = "from_date";
$dg->DefaultSortDir = "Desc";
$dg->height = 400 ;
$dg->EnableSearch = false;
$dg->collapsible = true ;
$dg->collapsed = false ;

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){ return TaxTableTypeObject.editTax(v,p,r);}";

$ttgrid = $dg->makeGrid_returnObjects();

//..............................................................................

$tidg = new sadaf_datagrid("tidg", $js_prefix_address .
						   "../data/tax_table_items.data.php?task=selectAll","tidgDiv");

$tidg->addColumn('', "tax_table_id", "int", true);
$tidg->addColumn('', "row_no", "int", true);

$col = $tidg->addColumn('از مبلغ', "from_value", "int" );
$col->editor = ColumnEditor::NumberField();
$col->width = 140;

$col = $tidg->addColumn('تا مبلغ', "to_value","int");
$col->editor = ColumnEditor::NumberField();
$col->width = 140;

$col = $tidg->addColumn('ضریب', "coeficient","int");
$col->editor = ColumnEditor::NumberField();
$col->width = 70;

$tidg->addButton = true;
$tidg->addHandler =  "function(v,p,r){ return TaxTableTypeObject.TaxItemAdding(v,p,r);}";


$col = $tidg->addColumn("حذف", "", "string");
$col->renderer = "function(v,p,r){ return TaxTableTypes.opDelItemRender(v,p,r);}";
$col->width = 50;

$tidg->width = 400;
$tidg->height = 400 ; 
$tidg->DefaultSortField = "from_value";
$tidg->DefaultSortDir = "Desc";
$tidg->EnableSearch = false;
$tidg->enableRowEdit = true;
$tidg->rowEditOkHandler = "function(v,p,r){ return TaxTableTypeObject.editTaxItem(v,p,r);}";

$cgrid = $tidg->makeGrid_returnObjects();


?>
<script>
TaxTableTypes.prototype.afterLoad = function()
{
	this.grid = <?= $grid ?>;
    this.ttgrid = <?= $ttgrid ?>;
    this.cgrid = <?= $cgrid ?>;
	this.grid.render(this.get("tdgDiv"));
}
var TaxTableTypeObject = new TaxTableTypes();
</script>



<center>
<div id="ErrorDiv" style="width:40%"></div>
<div id="form_Tax_Types">
	<div id="tdgDiv"></div>
	<br><br>
	<div id="dgDiv"></div> 
	<br><br>
	<div id="tidgDiv"></div>
</div>
</center>