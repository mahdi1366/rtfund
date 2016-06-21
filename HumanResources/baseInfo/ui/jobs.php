<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.07
//---------------------------
require_once '../../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;

require_once '../js/jobs.js.php';

$dg = new sadaf_datagrid("JGrid", $js_prefix_address . "../data/jobs.data.php?task=SearchJob", "JobDIV");

$dg->addColumn("نوع شخص", "PersonType","",true);
$dg->addColumn("کد شغل", "job_id");
$col->width = 50;

$col = $dg->addColumn("عنوان شغل", "title", "string");
$col->editor =  ColumnEditor::TextField();

$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "  function(v,p,r){  return Job.opRender(v,p,r);}";
$col->width = 50; 

$dg->addButton = true;
$dg->addHandler = " function(){  JobObject.AddJob();}";

$dg->pageSize = "15";
$dg->EnableSearch = true ;
$dg->width = 450;
$dg->height = 530;
$dg->title = "مشاغل";
$dg->autoExpandColumn = "title";

$dg->enableRowEdit = true ;
$dg->rowEditOkHandler = "function(v,p,r){ return JobObject.editJob(v,p,r);}";

$grid = $dg->makeGrid_returnObjects();

?>
<script>
    JobObject.grid = <?=$grid?>;  
    JobObject.grid.render("JobDIV");
    
</script>
<center>
    <div id="ErrorDiv" style="width:40%"></div>
	<div id="JobDIV" style="width:100%"></div>
</center>
