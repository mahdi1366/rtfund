<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.05.12
//---------------------------
require_once '../../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;

require_once '../js/cost_centers.js.php';

$dg = new sadaf_datagrid("CCGrid", $js_prefix_address . "../data/cost_centers.data.php?task=searchcostCenter", "CCDIV");

$dg->addColumn("نام کارفرما", "employer_name", "string",true);
$dg->addColumn("آدرس کارگاه", "detective_address", "string",true);
$dg->addColumn("نام شعبه", "collective_security_branch", "string",true);
$dg->addColumn("شرح", "description", "string",true);
 
$col = $dg->addColumn("کد  ", "cost_center_id","int");
$col->width = 30;

$col = $dg->addColumn("مرکز هزینه", "title", "string");
$col->editor =  ColumnEditor::TextField();

$col = $dg->addColumn("شماره کارگاه", "daily_work_place_no","int");
$col->editor =  ColumnEditor::NumberField(); 
$col->width = 100;

$col = $dg->addColumn("نام کارگاه", "detective_name", "string");
$col->editor =  ColumnEditor::TextField();
$col->width = 200;

$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){return CostCenter.opRender(v,p,r);}";
$col->width = 50;

$dg->addButton = true;
$dg->addHandler = "function(){CostCenterObject.AddCostCenter();}";

$dg->pageSize = "20";
$dg->EnableSearch = false ;
$dg->width = 580;
$dg->height = 430;
$dg->title = "مراکز هزینه";
$dg->autoExpandColumn = "title";

$dg->enableRowEdit = true ;
$dg->rowEditOkHandler = "function(v,p,r){ return CostCenterObject.editCostCenter(v,p,r);}";

$grid = $dg->makeGrid_returnObjects();

?>
<script>
    CostCenterObject.grid = <?=$grid?>;  
    CostCenterObject.grid.render("CCDIV");
    
</script>
<center>
    <div id="ErrorDiv" style="width:40%"></div><br>
    <div> <div id="mainpanel"></div> </div>    <br>
    <div id="CCDIV" style="width:100%"></div>
</center>
