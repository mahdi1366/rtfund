<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------
include_once("../header.inc.php");
require_once inc_dataGrid;
require_once 'persons.js.php';

require_once '../management/framework.class.php';
//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dg = new sadaf_datagrid("dg",$js_prefix_address . "persons.data.php?task=selectAllPersons", "div_grid_persons");

$dg->addColumn("کد","PersonID","string", true);
$dg->addColumn("","IsActive","string", true);

$col = $dg->addColumn("نام","fname","string");
$col->sortable = false;
$col->width = 120;

$col = $dg->addColumn("نام خانوادگي","lname","string");
$col->sortable = false;

$col = $dg->addColumn("نام كاربري","PersonName","string");
$col->sortable = false;
$col->width = 120;

if($accessObj->RemoveFlag)
{
	$col = $dg->addColumn("حذف","personID","");
	$col->renderer = "Person.deleteRender";
	$col->sortable = false;
	$col->width = 40;
}
if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){PersonObject.Adding();}";
}

$dg->height = 350;
$dg->width = 700;
$dg->DefaultSortField = "lname";
$dg->autoExpandColumn = "lname";
$grid = $dg->makeGrid_returnObjects();
?>
<style type="text/css">
.pinkRow, .pinkRow td,.pinkRow div{ background-color:#FFB8C9 !important;}
</style>
<script>

var PersonObject = new Person();

PersonObject.grid = <?= $grid?>;
PersonObject.grid.getView().getRowClass = function(record)
{
	if(record.data.IsActive == "NO")
		return "pinkRow";
	return "";
}
PersonObject.grid.render(PersonObject.get("div_grid_user"));

</script>
<center>
	<br>
	<div id="div_grid_user"></div>
</center>