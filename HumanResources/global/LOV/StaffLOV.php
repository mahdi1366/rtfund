<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.03
//---------------------------
require_once "../../header.inc.php";
require_once inc_dataGrid;

?>
<html>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-all.css" />
    <link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-rtl.css" />
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/icons.css" />
    <body dir="rtl" onLoad="document.getElementById('pfname').focus();">
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-all.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-extend.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/component.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/message.js"></script>

	<script type="text/javascript">
	Ext.onReady(function(){ 	
		
		new Ext.panel.Panel({
			renderTo: "searchStaffDIV",
			contentEl : "searchStaffPNL",
            frame:true , 
			title: "جستجوی شخص",
			width: 500,
			buttons :[{
				text : "جستجو",
				iconCls : "search",
				handler : searching
			}]
		});
		new Ext.KeyMap("searchStaffPNL", [{key:Ext.EventObject.ENTER, fn:searching}]);
	});
	function searching()
	{
		if(!grid.rendered)
			grid.render("div_dg");
		else
			grid.getStore().loadPage(1);       
		
	}
	
	function SelectRender(v,p,r)
	{
		return "<a href='javascript:void(0)' onclick='select(" + v + ");'>" + v + "</a>";
	}
	
	function select(value)
	{
		window.returnValue = value;
		window.close();
	}
	</script>
<?php
$dg = new sadaf_datagrid("dg","../../personal/persons/data/person.data.php?task=gridSelect","div_dg","search");

$col = $dg->addColumn("شماره شناسایی","staff_id","int");
$col->renderer = "SelectRender";
$col->width = 80;
$col = $dg->addColumn("نام", "pfname","string");
$col->width = 100;
$col = $dg->addColumn("نام خانوادگی", "plname","string");
$col->width = 150;
$col = $dg->addColumn("نام پدر", "father_name","string");
$col->width = 100;
$col = $dg->addColumn("نوع فرد", "last_person_type","string");
$col->width = 80;

$dg->addColumn("واحد محل خدمت", "org_unit_title","string");
$dg->autoExpandColumn = "org_unit_title";
$dg->EnableSearch = false;
$dg->notRender = true;
$dg->width = 700;
$dg->height = 300;
$grid = $dg->makeGrid_returnObjects();
?>
<script>
    var grid = <?=$grid?> ;
</script>
	<form id="search">
		<center>
		<div id="searchStaffDIV" align="center">
		<table width="100%" id="searchStaffPNL">
			<tr>
				<td>نام :</td>
				<td><input type="text" tabindex="1" id="pfname" name="pfname" class="x-form-text x-form-field"></td>
				<td>نام خانوادگی :</td>
				<td><input type="text" name="plname" class="x-form-text x-form-field"></td>
			</tr>
			<tr>
				<td>شماره شناسایی از :</td>
				<td><input type="text" name="from_StaffID" class="x-form-text x-form-field"></td>
				<td>تا :</td>
				<td><input type="text" name="to_StaffID" class="x-form-text x-form-field"></td>
			</tr>
			<tr>
				<td>عنوان واحد :</td>
				<td colspan="3"><input class="x-form-text x-form-field" type="text" name="unitName"></td>
			</tr>
			<tr>
				<td colspan="4" align="center"><div id="btn_search"></div></td>
			</tr>
		</table>
		</div>
		<div id="div_dg"></div>
		</center>
	</form>
</body>
</html>