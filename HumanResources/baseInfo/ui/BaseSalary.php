<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.01.22
//---------------------------
require_once '../../../header.inc.php';
require_once inc_dataGrid;


require_once '../js/BaseSalary.js.php';

$dg = new sadaf_datagrid("EvalGrid", $js_prefix_address . "../data/BaseSalary.data.php?task=SelectRetList", "EvalDIV");
//..............................................................................

$col = $dg->addColumn("نام ", "pfname", "string",true);
$col->renderer = "function(v){ return ' '; }" ;

$col = $dg->addColumn("نام خانوادگی", "plname", "string",true);
$col->renderer = "function(v){ return ' '; }" ;

$col = $dg->addColumn("نام و نام خانوادگی", "ledger_number", "int");
$col->renderer = "function(v,p,r){return r.data.pfname + '  ' + r.data.plname }";
$col->editor = "BaseSalaryObject.personCombo";

$col = $dg->addColumn("مبلغ حقوق", "salary_94", "int");
$col->editor = ColumnEditor::NumberField(true);
$col->width = 110;


$dg->addButton = true;
$dg->addHandler = "function(){BaseSalaryObject.AddEvalList();}";

$dg->pageSize = "20";
$dg->width = 600;
$dg->height = 630;
$dg->title = "لیست حقوق بازنشستگان";
$dg->autoExpandColumn = "ledger_number";
$dg->DefaultSortField = "ledger_number";

$dg->enableRowEdit = true ;
$dg->rowEditOkHandler = "function(v,p,r){ return BaseSalaryObject.editValList(v,p,r);}";

$grid = $dg->makeGrid_returnObjects();


?>
<script>
    BaseSalaryObject.grid = <?=$grid?>;
   
    BaseSalaryObject.grid.render("EvalDIV");
    
</script>
<center>
	<br>
   <div id="ErrorDiv" style="width:40%"></div>
	<div id="EvalDIV" style="width:100%"></div>
</center>
