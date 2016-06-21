<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.05.03
//---------------------------
require_once '../../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;

require_once '../js/cost_center_exception.js.php';

$dg = new sadaf_datagrid("CCEGrid", $js_prefix_address . "../data/cost_center_exception.data.php?task=searchcostCenterItm", "CCEDIV");

$dg->addColumn("کد قلم", "SalaryItemTypeID","",true);
$dg->addColumn("نوع فرد ", "PersonType","",true);

$col = $dg->addColumn("مرکز هزینه", "CostCenterID", "string");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_costCenter(), "cost_center_id", "title");
$col->width = 200;

$col = $dg->addColumn("قلم کسری", "full_title");
$col->editor = "CostExceptionObject.subItemCombo";

$col = $dg->addColumn("نوع فرد", "person_type_title", "string");
$col->width = 100;

$col = $dg->addColumn("از تاریخ", "FromDate", GridColumn::ColumnType_date );
$col->editor = ColumnEditor::SHDateField();
$col->width = 100;

$col = $dg->addColumn(" تا تاریخ", "ToDate", GridColumn::ColumnType_date );
$col->editor = ColumnEditor::SHDateField();
$col->width = 100;

$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){return CostException.opRender(v,p,r);}";
$col->width = 50;

$dg->addButton = true;
$dg->addHandler = "function(){CostExceptionObject.AddCostException();}";

$dg->pageSize = "20";
$dg->EnableSearch = false ;
$dg->width = 680;
$dg->height = 630;
$dg->title = "اقلام مستثنی در مراکز هزینه";
$dg->autoExpandColumn = "full_title";

$dg->enableRowEdit = true ;
$dg->rowEditOkHandler = "function(v,p,r){ return CostExceptionObject.editCostException(v,p,r);}";

$grid = $dg->makeGrid_returnObjects();

?>
<script>
    CostExceptionObject.grid = <?=$grid?>;   
   /* CostExceptionObject.grid.plugins[0].on("beforeedit", function(editor,e){	
	if(e.record.data.SalaryItemTypeID != "")
		return false;
	});    */
    CostExceptionObject.grid.render("CCEDIV");
    
</script>
<center>
    <div id="ErrorDiv" style="width:40%"></div>
	<div id="CCEDIV" style="width:100%"></div>
</center>
