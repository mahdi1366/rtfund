<?php 
require_once '../../header.inc.php';
require_once inc_dataGrid;

require_once '../js/study_fields.js.php';

$dgh = new sadaf_datagrid("deph",$js_prefix_address."../data/study_fields.data.php?task=selectfields","div_dg");

$col = $dgh->addColumn("کد","sfid");
$col->width = 50;

$col = $dgh->addColumn("رشته تحصیلی", "ptitle", GridColumn::ColumnType_string);
$col->editor = ColumnEditor::TextField();

$col = $dgh->addColumn("حذف", "", "");
$col->renderer = "StudyFields.opRender";
$col->width = 70;

$dgh->addButton = true;
$dgh->addHandler = "function(v,p,r){ return StudyFieldsObject.AddStudyField(v,p,r);}";


$dgh->title = "رشته های تحصیلی";
$dgh->width = 500;
$dgh->DefaultSortField = "sfid";
$dgh->DefaultSortDir = "ASC";
$dgh->autoExpandColumn = "ptitle";
$dgh->EnableSearch = true;
$dgh->enableRowEdit = true ;
$dgh->pageSize = "20" ;
$dgh->collapsible = true;
$dgh->collapsed = false;
$dgh->rowEditOkHandler = "function(v,p,r){ return StudyFieldsObject.SaveField(v,p,r);}";

$gridSupport = $dgh->makeGrid_returnObjects();
//........................گرایش تحصیلی.........................

$dg = new sadaf_datagrid("bg",$js_prefix_address."../data/study_fields.data.php?task=selectbanchs","div_branch");

$dg->addColumn("کد رشته","sfid","",true);

$col = $dg->addColumn("کد","sbid");
$col->width = 50;

$col = $dg->addColumn("عنوان گرایش", "ptitle", GridColumn::ColumnType_string);
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("حذف", "", "");
$col->renderer = "StudyFields.opDelRender";
$col->width = 50;

$dg->addButton = true;
$dg->addHandler = "function(v,p,r){ return StudyFieldsObject.AddStudyBranch(v,p,r);}";


$dg->title = "گرایش ها ";
$dg->width = 500;
$dg->DefaultSortField = "sbid";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "ptitle";
$dg->EnableSearch = false ; 
$dg->enableRowEdit = true ;
$dg->rowEditOkHandler = "function(v,p,r){ return StudyFieldsObject.SaveBranch(v,p,r);}";

$branchGrid = $dg->makeGrid_returnObjects();

?>
<script>
	StudyFields.prototype.afterLoad = function()
	{
		this.grid = <?= $gridSupport?>;
		this.grid.render(this.get("div_dg"));
        this.branchGrid = <?= $branchGrid ?> ;
	}

	var StudyFieldsObject = new StudyFields();
</script>
<center>

	<div id="div_dg"></div>
	<br><br>
	<div id="div_branch" ></div>

</center>
