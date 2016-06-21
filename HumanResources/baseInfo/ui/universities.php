<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.05
//---------------------------
require_once '../../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;

require_once '../js/universities.js.php';

$dg = new sadaf_datagrid("UGrid", $js_prefix_address . "../data/universities.data.php?task=SearchUni", "UniDIV");

$dg->addColumn("کد دانشگاه", "university_id","",true);

$col = $dg->addColumn("کشور","country_id", "int");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_Country(),"country_id","ptitle") ;        
$col->width = 300;

$col = $dg->addColumn("عنوان دانشگاه", "ptitle", "string");
$col->editor =  ColumnEditor::TextField();
$col->width = 200;

$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "  function(v,p,r){  return University.opRender(v,p,r);}";
$col->width = 50; 

$dg->addButton = true;
$dg->addHandler = " function(){  UniversityObject.AddUni();}";

$dg->pageSize = "15";
$dg->EnableSearch = false ;
$dg->width = 550;
$dg->height = 530;
$dg->title = " دانشگاه ها";
$dg->autoExpandColumn = "ptitle";

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
