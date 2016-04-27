<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

if(isset($_SESSION["USER"]["portal"]))
{
	$portal = true;
	$PersonID = $_SESSION["USER"]["PersonID"];
}
else
{
	$portal = false;
	$PersonID = $_POST["PersonID"];
}


if(empty($PersonID))
	die();

$dg = new sadaf_datagrid("dg", $js_prefix_address . 
		"../../office/dms/dms.data.php?task=SelectAll&ObjectType=Person&ObjectID=" . $PersonID , "grid_div");

$dg->addColumn("", "DocumentID", "", true);
$dg->addColumn("", "ObjectType", "", true);
$dg->addColumn("", "ObjectID", "", true);
$dg->addColumn("", "IsConfirm", "", true);

$col = $dg->addColumn("مدرک", "DocType", "");
$col->editor = ColumnEditor::ComboBox(PdoDataAccess::runquery("select * from BaseInfo where typeID=8"), "InfoID", "InfoDesc");
$col->width = 140;

$col = $dg->addColumn("توضیح", "DocDesc", "");
$col->editor = ColumnEditor::TextField(true);

$col = $dg->addColumn("فایل", "FileType", "");
$col->renderer = "function(v,p,r){return PersonalInfo.FileRender(v,p,r)}";
$col->editor = "this.FileCmp";
$col->align = "center";
$col->width = 100;

$col = $dg->addColumn("عملیات", "", "");
$col->renderer = "function(v,p,r){return PersonalInfo.OperationRender(v,p,r)}";
$col->width = 60;

$dg->addButton("", "اضافه مدرک", "add", "function(){PersonalInfoObject.AddDocument();}");

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(){return PersonalInfoObject.SaveDocument();}";

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 330;
$dg->width = 690;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "DocTypeDesc";
$dg->autoExpandColumn = "DocDesc";
$grid = $dg->makeGrid_returnObjects();

require_once 'PersonInfo.js.php';
?>
<br>
<center>
<div id="mainForm"><div>
</center>