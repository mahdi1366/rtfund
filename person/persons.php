<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------
include_once("header.inc.php");
require_once inc_dataGrid;
require_once 'persons.js.php';

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dg = new sadaf_datagrid("dg",$js_prefix_address . "persons.data.php?task=selectPersons", "div_grid_persons");

$dg->addColumn("","PersonID","string", true);
$dg->addColumn("","IsActive","string", true);

$col = $dg->addColumn("نام و نام خانوادگی","fullname","string");
$col->sortable = false;

$col = $dg->addColumn("نام كاربري","UserName","string");
$col->sortable = false;
$col->width = 120;

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

$col = $dg->addColumn("<font style=font-size:10px>عامل</font>","IsAgent","string");
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
$col->renderer = "Person.infoRender";
$col->sortable = false;
$col->width = 40;

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
$dg->width = 750;
$dg->DefaultSortField = "fullname";
$dg->autoExpandColumn = "fullname";
$grid = $dg->makeGrid_returnObjects();
//..........................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "../../dms/dms.data.php?task=SelectAll&ObjectType=Person" , "grid_div");

$dg->addColumn("", "DocumentID", "", true);
$dg->addColumn("", "IsConfirm", "", true);

$col = $dg->addColumn("مدرک", "DocTypeDesc", "");
$col->width = 140;

$col = $dg->addColumn("توضیح", "DocDesc", "");

$col = $dg->addColumn("تایید کننده", "confirmfullname");
$col->width = 120;

$col = $dg->addColumn("فایل", "FileType", "");
$col->renderer = "function(v,p,r){return Person.FileRender(v,p,r)}";
$col->align = "center";
$col->width = 40;

$col = $dg->addColumn("تایید", "", "");
$col->renderer = "function(v,p,r){return Person.ConfirmRender(v,p,r)}";
$col->width = 40;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 210;
$dg->width = 690;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->disableFooter = true;
$dg->DefaultSortField = "DocTypeDesc";
$dg->autoExpandColumn = "DocDesc";
$grid2 = $dg->makeGrid_returnObjects();
?>
<style type="text/css">
.pinkRow, .pinkRow td,.pinkRow div{ background-color:#FFB8C9 !important;}
</style>
<script>

var PersonObject = new Person();

PersonObject.docgrid = <?= $grid2?>;
PersonObject.docgrid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsConfirm == "YES")
			return "greenRow";
		return "";
	}

PersonObject.tabPanel.getComponent("documents").insert(0,PersonObject.docgrid);

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
	<br>
	<div id="info"></div>
</center>