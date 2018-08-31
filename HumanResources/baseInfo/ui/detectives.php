<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	97.02
//---------------------------
require_once '../../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;

require_once '../js/detectives.js.php';

$dg = new sadaf_datagrid("DGrid", $js_prefix_address . "../data/detectives.data.php?task=SearchDet", "UniDIV");


$col = $dg->addColumn("ردیف", "DetID", "int"); 
$col->width = 80;

$col = $dg->addColumn("کد کارگاه ", "detectiveCode", "string");
$col->editor =  ColumnEditor::TextField();
$col->width = 100;

$col = $dg->addColumn("عنوان کارگاه", "detectiveName", "string");
$col->editor =  ColumnEditor::TextField();


$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "  function(v,p,r){  return University.opRender(v,p,r);}";
$col->width = 50; 

$dg->addButton = true;
$dg->addHandler = " function(){  UniversityObject.AddUni();}";

$dg->pageSize = "15";
$dg->EnableSearch = false ;
$dg->width = 380;
$dg->height = 530;
$dg->title = " کارگاهها";
$dg->autoExpandColumn = "detectiveName";

$dg->enableRowEdit = true ;
$dg->rowEditOkHandler = "function(v,p,r){ return UniversityObject.editUniversity(v,p,r);}";

$grid = $dg->makeGrid_returnObjects();

?>
<script>
    UniversityObject.grid = <?=$grid?>;  
    UniversityObject.grid.render("UniDIV");
    
</script>
<center>
    <div id="ErrorDiv" style="width:40%"></div>
	<div id="UniDIV" style="width:100%"></div>
</center>
