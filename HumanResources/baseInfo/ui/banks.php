<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.06
//---------------------------
require_once '../../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;

require_once '../js/banks.js.php';

$dg = new sadaf_datagrid("BGrid", $js_prefix_address . "../data/banks.data.php?task=Searchbank", "BDIV");

$dg->addColumn("کد بانک", "bank_id","",true);

$col = $dg->addColumn("نام بانک", "name", "string");
$col->editor =  ColumnEditor::TextField();

$col = $dg->addColumn("کد شعبه", "branch_code", "int");
$col->editor =  ColumnEditor::NumberField(true) ; 
$col->width = 100;

$col = $dg->addColumn("نوع","type", "int");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_BankType(),"InfoID","Title") ;        
$col->width = 200;

$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "  function(v,p,r){  return bank.opRender(v,p,r);}";
$col->width = 80; 

$dg->addButton = true;
$dg->addHandler = " function(){  bankObject.AddBnak();}";

$dg->pageSize = "15";
$dg->EnableSearch = false ;
$dg->width = 550;
$dg->height = 530;
$dg->title = " بانکها";
$dg->autoExpandColumn = "name";

$dg->enableRowEdit = true ;
$dg->rowEditOkHandler = "function(v,p,r){ return bankObject.editBank(v,p,r);}";

$grid = $dg->makeGrid_returnObjects();

?>
<script>
    bankObject.grid = <?=$grid?>;  
    bankObject.grid.render("BDIV");    
</script>
<center>
    <div id="ErrorDiv" style="width:40%"></div>
    <div id="BDIV" style="width:100%"></div>
</center>
