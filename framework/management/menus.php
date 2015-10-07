<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.06
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

require_once 'menus.js.php';

$dg = new sadaf_datagrid("dg",$js_prefix_address . "framework.data.php?task=GellMenus","grid_div","mainForm");

$dg->addColumn("", "GroupID", "", true);
$dg->addColumn("", "GroupDesc", "", true);
$dg->addColumn("", "MenuID", "", true);
$dg->addColumn("", "SystemID", "", true);
$dg->addColumn("", "GroupSystemID", "", true);
$dg->addColumn("", "GroupOrder", "", true);

$dg->EnableGrouping = true;
$dg->DefaultGroupField = "GroupID";
$dg->groupHeaderTpl = " <table class=infoTbl width=100% >" . 
		"<tr><td width=20px> " .
				"<div title=ایجاد onclick=MenuObject.AddMenu(event,{[values.rows[0].data.GroupID]},{[values.rows[0].data.GroupSystemID]}); class=add " .
				"style=background-repeat:no-repeat;background-position:center;cursor:pointer;width:100%;height:16></div></td>" .
			"<td width=20px><div title=ویرایش onclick=MenuObject.EditMenu(event,{[values.rows[0].data.GroupID]}); class=edit " .
				"style=background-repeat:no-repeat;background-position:center;cursor:pointer;width:100%;height:16></div></td>" .
			"<td>منوی اصلی : <span class=blueText>{[values.rows[0].data.GroupDesc]} [ ترتیب : {[values.rows[0].data.GroupOrder]} ]</span>".
		"</td><td  width=20px>" .
			"<div title=حذف onclick=MenuObject.DeleteMenu({[values.rows[0].data.GroupID]},event); class=remove " .
				"style=background-repeat:no-repeat;background-position:center;cursor:pointer;width:100%;height:16></div>" . 
		"</td></tr>" .        
        "</table>";

$col = $dg->addColumn("عنوان", "MenuDesc", "string");
$col->editor = ColumnEditor::TextField();
$col->sortable = false;

$col = $dg->addColumn("مسیر", "MenuPath", "string");
$col->editor = "MenuObject.PathField";
$col->sortable = false;
$col->align = "left";
$col->renderer = "function(v){return '<div style=direction:ltr>' + v + '</div>'; }";
$col->width = 250;

$col = $dg->addColumn("آیکون", "icon", "string");
$col->editor = ColumnEditor::TextField(true);
$col->sortable = false;
$col->width = 80;

$col = $dg->addColumn("ترتیب", "ordering", "string");
$col->editor = ColumnEditor::NumberField();
$col->sortable = false;
$col->width = 60;
$col->align = "center";

$col = $dg->addColumn("وضعیت", "IsActive", "string");
$col->editor = ColumnEditor::ComboBox(array(array("id"=>"YES", "title"=>'فعال'),array("id"=>'NO', "title"=>'غیرفعال')), "id", "title");
$col->sortable = false;
$col->width = 60;
$col->align = "center";

$dg->addObject('
	{text : "عنوان منوی اصلی : "},
	{xtype: "textfield",itemId: "GroupDesc",width: 200, enableKeyEvents: true},
	{text : "ترتیب : "},
	{xtype: "textfield",itemId: "GroupOrder",width: 100, enableKeyEvents: true},
	{xtype: "hidden",itemId: "GroupID", enableKeyEvents: true},
	{text : "", handler : function(){ MenuObject.EditMenu(null,null);}, iconCls: "clear"},
	{text : "ذخیره منوی اصلی", handler : function(){ MenuObject.SaveGroup();}, iconCls: "save"}');

$col = $dg->addColumn("حذف", "");
$col->sortable = false;
$col->renderer = "Menu.deleteRender";
$col->width = 55;

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(store,record,option){ MenuObject.SaveMenu(store,record,option);}";

$dg->height = 700;
$dg->width = 750;
$dg->emptyTextOfHiddenColumns = true;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "MenuID";
$dg->DefaultSortDir = "DESC";
$dg->title = "منوهای اصلی و فرعی";
$dg->autoExpandColumn = "MenuDesc";
$dg->notRender = true;
$grid = $dg->makeGrid_returnObjects();

?>

<script>
	
MenuObject.PathField = new Ext.form.TextField({
	allowBlank : false,
	fieldStyle : "direction:ltr"
})
	
MenuObject.grid = <?= $grid?>;
MenuObject.grid.plugins[0].on("beforeedit", function(editor,e){
		if(e.record.data.MenuID == null || e.record.data.MenuID == "")
			return false;
	});

//MenuObject.grid.plugins[0].down("[itemId=cmp_path]")

//Ext.getCmp("title").on("keydown", function(elem,e){if(e.getKey() == 13)AddMenu();});
//Ext.get("elementTitle").addKeyListener(13, function(){AddElement();});	
</script>
<center>
	<br>
	<div id="div_systems"></div>
	<br>
	<div id="div_grid"></div>
</center>