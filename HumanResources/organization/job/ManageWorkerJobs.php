<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------
	require_once '../../header.inc.php';
	require_once 'job.data.php';
	require_once inc_dataGrid;
	
	echo "<html><head>";
	
	jsConfig::initialExt();
	jsConfig::grid(true,"",true);
	
	require_once 'ManageWorkerJobs.js.php';
	
	$dg = new sadaf_datagrid("wjob","job.data.php?task=selectWorkerJobs","WorkerJobGRID");
	
	$col = $dg->addColumn("شناسه","job_id");
	$col->width = 10;

	$col = $dg->addColumn("عنوان", "title");
	$col->editor = ColumnEditor::TextField();
	$col->width = 25; 
	
	$col = $dg->addColumn("گروه", "job_group");
	$col->editor = ColumnEditor::NumberField();
	$col->width = 10;
	
	$col = $dg->addColumn("شرایط احراز", "conditions");
	$col->editor = ColumnEditor::TextField(true);
	$col->width = 50;

	$col = $dg->addColumn("شرح وظایف", "duties");
	$col->editor = ColumnEditor::TextField(true);
	$col->width = 50;
	
	$col = $dg->addColumn("عملیات", "", "string");
	$col->renderer = "ManageWorkerJobs.opDelRender";
	$col->width = 10;
	
	$dg->addButton = true;
	$dg->addHandler = "function(v,p,r){ return ManageWorkerJobsObject.AddWorkerJobs(v,p,r);}";
	
	$dg->enableRowEdit = true ;
	$dg->rowEditOkHandler = "function(v,p,r){ return ManageWorkerJobsObject.SaveWorkerJobs(v,p,r);}";	

	$dg->title = "مشاغل روزمزد";
	$dg->height = 600;
	$dg->width = 980;
	$dg->DefaultSortField = "job_id";
	$dg->DefaultSortDir = "ASC";
	$dg->pageSize = "20";
	$dg->EnableRowNumber = true ; 
	$grid = $dg->makeGrid_returnObjects();
?>
<script>
	ManageWorkerJobs.prototype.afterLoad = function()
	{
		this.grid = <?= $grid?>;
		this.grid.render(this.get("WorkerJobGRID"));
	}
</script>
</head>
<body dir='rtl'>
	<br><br>
	<form id="Form1">
	<center>
	<div id="WorkerJobGRID"></div>
	</center>
	</form>
	<script>
	var ManageWorkerJobsObject = new ManageWorkerJobs();
	</script>
</body>