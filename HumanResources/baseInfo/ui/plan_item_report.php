<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.03
//---------------------------
require_once '../../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;

require_once '../js/plan_item_report.js.php';

$dg = new sadaf_datagrid("PIGrid", $js_prefix_address . "../data/plan_item_report.data.php?task=searchPITR", "PIDIV");

$dg->addColumn("کد قلم", "PlanItemID","",true);

$col = $dg->addColumn("عنوان قلم", "PlanItemTitle", "string");
$col->editor = ColumnEditor::TextField(true); 

$col = $dg->addColumn("قلم مرتبط", "RelatedItem", "integer");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_PlanItem("-"), "InfoID", "Title","","",true);
$col->width = 180;

$col = $dg->addColumn("نوع فرد", "PersonType", "integer");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_PersonType("-"), "InfoID", "Title","","",true);
$col->width = 60;

$col = $dg->addColumn("مرکز هزینه", "CostCenterID", "integer");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_plancostCenter("-"), "CostCenterID", "Title","","",true);
$col->width = 120;

$col = $dg->addColumn("سال", "PayYear", "integer");
$col->editor = ColumnEditor::NumberField(); 
$col->width = 60;

$col = $dg->addColumn("ماه", "PayMonth", "integer");
$col->editor = ColumnEditor::NumberField(); 
$col->width = 60;

$col = $dg->addColumn("مبلغ", "PayValue", "integer");
$col->editor = ColumnEditor::NumberField(); 
$col->width = 80;

$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){return PlanItemReport.opRender(v,p,r);}";
$col->width = 50;

$dg->addButton = true;
$dg->addHandler = "function(){PlanItemReportObject.AddPIR();}";

$dg->pageSize = "10";
$dg->EnableSearch = false ;
$dg->width = 780;
$dg->height = 530;
$dg->title = " اقلام گزارش طرح و برنامه";
$dg->autoExpandColumn = "PlanItemTitle";
$dg->DefaultSortField = "PlanItemID";

$dg->enableRowEdit = true ;
$dg->rowEditOkHandler = "function(v,p,r){ return PlanItemReportObject.editPIR(v,p,r);}";

$grid = $dg->makeGrid_returnObjects();

?>
<script>
    PlanItemReportObject.grid = <?=$grid?>;   
    /*PlanItemReportObject.grid.plugins[0].on("beforeedit", function(editor,e){	
	if(e.record.data.state == 2 )
		return false;
	});*/
    PlanItemReportObject.grid.render("PIDIV");
    
</script>
<center>
    <div id="ErrorDiv" style="width:40%"></div>
	<div id="PIDIV" style="width:100%"></div>
</center>
