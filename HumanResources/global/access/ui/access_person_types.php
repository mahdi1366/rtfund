<?php
//-------------------------
// programmer:	Jafarkhani
// Date:    	90.02
//-------------------------
require_once '../../../header.inc.php';
require_once inc_dataGrid;

require_once '../js/access_person_types.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "../data/access.data.php?task=selectUsers", "div_grid");

$dg->addColumn("شناسه کاربر", "UserID");

$dg->addColumn("نام", "pfname");
$dg->addColumn("نام خانوادگی", "plname");

$col = $dg->addColumn("بارگذاری", "");
$col->renderer = "PersonTypeAccess.PersonTypeRender";
$col->width = 50;

$dg->DefaultSortField = "plname";
$dg->width = 400;
$dg->height = 500;
$dg->title = "لیست کاربران سیستم";
$gridUsers = $dg->makeGrid_returnObjects();

//------------------------------------------------------------------------------

$dg2 = new sadaf_datagrid("dg2", $js_prefix_address . "../data/access.data.php?task=selectPersonTypes", "div_grid2", "form_personTypeAccess");

$dg2->addColumn("", "InfoID", "", true);
$dg2->addColumn("نوع فرد", "Title");

$col = $dg2->addColumn('<input type="checkbox" onclick="PersonTypeAccessObject.checkAll(this);">انتخاب', "access");
$col->renderer = "PersonTypeAccess.accessRender";
$col->width = 60;
$col->sortable = false;
$col->align = "center";

$dg2->addButton("", "ذخیره دسترسی های انواع افراد", "save", "function(){PersonTypeAccessObject.SaveAccess();}");

$dg2->notRender = true;
$dg2->width = 350;
$dg2->height = 500;
$dg2->title = "لیست انواع افراد سازمان";
$dg2->EnableSearch = false;
$dg2->EnablePaging = false;
$gridPersonTypes = $dg2->makeGrid_returnObjects();
?>
<script>
PersonTypeAccess.prototype.afterLoad = function()
{
	this.UsersGrid = <?= $gridUsers?>;
	this.PersonTypesGrid = <?= $gridPersonTypes?>;

	this.UsersGrid.render(this.get("div_grid"));
}

var PersonTypeAccessObject = new PersonTypeAccess();
</script>
	<form id="form_personTypeAccess">
		<table width="800px">
			<tr>
				<td width="50%">
					<div id="div_grid"></div>
				</td>
				<td width="50%">
					<input type="hidden" id="UserID" name="UserID">
					<div id="div_grid2"></div>
				</td>
			</tr>
		</table>
	</form>
