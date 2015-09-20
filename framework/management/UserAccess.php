<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;
require_once 'UserAccess.js.php';

$dg = new sadaf_datagrid("dg",$js_prefix_address . "framework.data.php?task=selectAccess","div_dg");

$dg->addColumn("","MenuID","string",true);
$dg->addColumn("","GroupID","string",true);
$dg->addColumn("","GroupDesc","string",true);
$dg->addColumn("نام فرم","MenuDesc","string");

$col = $dg->addColumn("دسترسی کامل","","string");
$col->renderer = "Access.fullRender";
$col->align = "center";
$col->width = 60;

$col = $dg->addColumn("دسترسی مشاهده","ViewFlag","string");
$col->renderer = "Access.viewRender";
$col->align = "center";
$col->width = 60;

$col = $dg->addColumn("دسترسی ایجاد","AddFlag","string");
$col->renderer = "Access.addRender";
$col->align = "center";
$col->width = 60;

$col = $dg->addColumn("دسترسی ویرایش","EditFlag","string");
$col->renderer = "Access.editRender";
$col->align = "center";
$col->width = 60;

$col = $dg->addColumn("دسترسی حذف","RemoveFlag","string");
$col->renderer = "Access.removeRender";
$col->align = "center";
$col->width = 60;

$dg->addButton("save","ذخیره تغییرات","save","function(v,p,r){return AccessObject.saveAction(v,p,r);}");
$dg->EnableGrouping = true;
$dg->emptyTextOfHiddenColumns = true;
$dg->DefaultGroupField = "GroupID";
$dg->groupHeaderTpl = "::: {[values.rows[0].data.GroupDesc]}";

$dg->title = "تخصیص دسترسی به کاربران";
$dg->width = 700;
$dg->height = 400;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->notRender = true;
$grid = $dg->makeGrid_returnObjects();
?>
<script>

AccessObject.grid = <?= $grid?>;

</script>
<form id="MainForm">
<center>
	<br>
	<div><div id="div_form"></div></div>
	<br>
	<div><div id="div_dg"></div></div>
</center>
</form>