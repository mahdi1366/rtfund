<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------
include_once("../header.inc.php");
require_once inc_dataGrid;
require_once 'persons.js.php';

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dg = new sadaf_datagrid("dg",$js_prefix_address . "persons.data.php?task=selectPersons", "div_grid_persons");

$dg->addColumn("","PersonID","string", true);
$dg->addColumn("","IsReal","string", true);
$dg->addColumn("","PostID","string", true);
$dg->addColumn("","fname","string", true);
$dg->addColumn("","lname","string", true);
$dg->addColumn("","NationalID","string", true);
$dg->addColumn("","CompanyName","string", true);
$dg->addColumn("","EconomicID","string", true);
$dg->addColumn("","PhoneNo","string", true);
$dg->addColumn("","mobile","string", true);
$dg->addColumn("","email","string", true);
$dg->addColumn("","address","string", true);
$dg->addColumn("","IsActive","string", true);

$col = $dg->addColumn("نام و نام خانوادگی","fullname","string");
$col->sortable = false;

$col = $dg->addColumn("نام كاربري","UserName","string");
$col->sortable = false;
$col->width = 120;

$col = $dg->addColumn("<font style=font-size:10px>کاربر</font>","IsStaff","string");
$col->renderer = "function(v){return (v=='YES') ? '٭' : '';}";
$col->sortable = false;
$col->align = "center";
$col->width = 35;

$col = $dg->addColumn("<font style=font-size:10px>مشتری</font>","IsCustomer","string");
$col->renderer = "function(v){return (v=='YES') ? '٭' : '';}";
$col->sortable = false;
$col->align = "center";
$col->width = 35;

$col = $dg->addColumn("<font style=font-size:10px>سهامدار</font>","IsShareholder","string");
$col->renderer = "function(v){return (v=='YES') ? '٭' : '';}";
$col->sortable = false;
$col->align = "center";
$col->width = 35;

$col = $dg->addColumn("<font style=font-size:10px>سرمایه گذار</font>","IsAgent","string");
$col->renderer = "function(v){return (v=='YES') ? '٭' : '';}";
$col->sortable = false;
$col->align = "center";
$col->width = 35;

$col = $dg->addColumn("<font style=font-size:10px>حامی</font>","IsSupporter","string");
$col->renderer = "function(v){return (v=='YES') ? '٭' : '';}";
$col->sortable = false;
$col->align = "center";
$col->width = 35;

$col = $dg->addColumn("","","");
$col->renderer = "Person.DocumentRender";
$col->sortable = false;
$col->width = 40;

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){PersonObject.Adding();}";
}

if($accessObj->EditFlag)
{
	$col = $dg->addColumn("","","");
	$col->renderer = "Person.editRender";
	$col->sortable = false;
	$col->width = 40;
}
if($accessObj->RemoveFlag)
{
	$col = $dg->addColumn("حذف","personID","");
	$col->renderer = "Person.deleteRender";
	$col->sortable = false;
	$col->width = 40;
}

$dg->height = 500;
$dg->pageSize = 15;
$dg->width = 750;
$dg->DefaultSortField = "fullname";
$dg->autoExpandColumn = "fullname";
$dg->emptyTextOfHiddenColumns = true;
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
	<div id="div_info"></div>
	<br>
	<div id="div_grid_user"></div>
	<br>
	<div id="info"></div>
</center>