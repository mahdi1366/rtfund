<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.10
//---------------------------
require_once '../../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;

require_once '../js/priority.js.php';

$dg = new sadaf_datagrid("PGrid", $js_prefix_address . "../data/priority.data.php?task=SearchPriority", "PDIV");

$col = $dg->addColumn("اولویت", "PriorityID","");
$col->editor =  ColumnEditor::NumberField();
$col->width = 60; 

$col = $dg->addColumn("عنوان ", "PriorityTitle", "string");
$col->editor =  ColumnEditor::TextField();

$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "  function(v,p,r){  return priority.opRender(v,p,r);}";
$col->width = 80; 

$dg->addButton = true;
$dg->addHandler = " function(){  priorityObject.AddPriority();}";

$dg->pageSize = "15";
$dg->EnableSearch = false ;
$dg->width = 450;
$dg->height = 530;
$dg->title = " اولویت اقلام کسری";
$dg->autoExpandColumn = "PriorityTitle";

$dg->enableRowEdit = true ;
$dg->rowEditOkHandler = "function(v,p,r){ return priorityObject.editPriority(v,p,r);}";

$grid = $dg->makeGrid_returnObjects();

?>
<script>
    priorityObject.grid = <?=$grid?>;  
    priorityObject.grid.render("PDIV");    
</script>
<center>
    <div id="ErrorDiv" style="width:40%"></div>
    <div id="PDIV" style="width:100%"></div>
</center>
