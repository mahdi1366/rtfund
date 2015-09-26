<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------
include_once("../header.inc.php");
require_once inc_dataGrid;
require_once 'users.js.php';

$dg = new sadaf_datagrid("dg",$js_prefix_address . "framework.data.php?task=selectUsers","div_grid_user");

$dg->addColumn("کد","PersonID","string", true);
$dg->addColumn("","IsActive","string", true);


$col = $dg->addColumn("نام","fname","string");
$col->editor = ColumnEditor::TextField();
$col->sortable = false;
$col->width = 120;
//---------------------------
$col = $dg->addColumn("نام خانوادگي","lname","string");
$col->editor = ColumnEditor::TextField();
$col->sortable = false;

$col = $dg->addColumn("نام كاربري","UserID","string");
$col->editor = ColumnEditor::TextField();
$col->sortable = false;
$col->width = 120;

$col = $dg->addColumn("پست سازمانی","PostID","string");
$col->editor = ColumnEditor::ComboBox(PdoDataAccess::runquery("select * from BSC_posts"), "PostID", "PostName");
$col->sortable = false;
$col->width = 120;

$col = $dg->addColumn("حذف","personID","");
$col->renderer = "User.deleteRender";
$col->sortable = false;
$col->width = 40;

$col = $dg->addColumn("reset pass","personID","");
$col->renderer = "User.resetPassRender";
$col->sortable = false;
$col->width = 80;

$dg->addButton = true;
$dg->addHandler = "function(){UserObject.Adding();}";

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){return UserObject.saveData(v,p,r);}";

$dg->height = 350;
$dg->width = 700;
$dg->DefaultSortField = "lname";
$dg->autoExpandColumn = "lname";
$dg->editorGrid = true;
//$dg->notRender = true;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$grid = $dg->makeGrid_returnObjects();
?>
<style type="text/css">
.pinkRow, .pinkRow td,.pinkRow div{ background-color:#FFB8C9 !important;}
</style>
<script>

var UserObject = new User();

UserObject.grid = <?= $grid?>;
UserObject.grid.getView().getRowClass = function(record)
{
	if(record.data.IsActive == "NO")
		return "pinkRow";
	return "";
}
UserObject.grid.render(UserObject.get("div_grid_user"));

</script>
<center>
	<br>
	<div id="div_grid_user"></div>
</center>