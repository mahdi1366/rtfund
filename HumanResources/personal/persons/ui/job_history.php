<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.07
//---------------------------
require_once '../../../header.inc.php';
require_once("../data/job_history.data.php");
require_once inc_dataGrid;

require_once '../js/job_history.js.php';
 
$dg = new sadaf_datagrid("JobGrid", $js_prefix_address . "../data/job_history.data.php?task=SearchHistoryJob&Q0=".$_POST['Q0'], "PerJobDIV");

$col = $dg->addColumn("شماره پرسنلی", "PersonID", "int",true);
$col = $dg->addColumn("ردیف", "RowNO", "int",true);
$col = $dg->addColumn(" ", "job_title", "int",true);

$col = $dg->addColumn("عنوان شغل", "JobID", "int");
$col->renderer = "function(v,p,r){return  r.data.job_title }";
$col->editor = "PersonJobObject.JobCombo";

$col = $dg->addColumn("از تاریخ", "FromDate", "int");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->editor = ColumnEditor::SHDateField();
$col->width = 100;

$col = $dg->addColumn("تا تاریخ", "ToDate", "int");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->editor = ColumnEditor::SHDateField(true);
$col->width = 100;


$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){return PersonJob.opRender(v,p,r);}";
$col->width = 50;

	$dg->addButton = true;
	$dg->addHandler = "function(){PersonJobObject.AddJobList();}"; 

$dg->pageSize = "20";
$dg->EnableSearch = false ;
$dg->width = 500;
$dg->height = 400;
$dg->title = "سابقه شغلی";
$dg->autoExpandColumn = "JobID";

$dg->enableRowEdit = true ;
$dg->rowEditOkHandler = "function(v,p,r){ return PersonJobObject.editJobList(v,p,r);}"; 

$grid = $dg->makeGrid_returnObjects();

?>

<script>
    PersonJobObject.grid = <?=$grid?>;
   
    PersonJobObject.grid.render("PerJobDIV");
    
</script>

<div id="PerJobDIV" style="width:100%"></div>
