<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:92.08
//---------------------------
require_once "../../header.inc.php";
require_once inc_dataGrid;

?>
<html>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-all.css" />
    <link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-rtl.css" />
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/icons.css" />
    <body dir="rtl" onLoad="document.getElementById('full_title').focus();">
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-all.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-extend.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/component.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/message.js"></script>

	<script type="text/javascript">
	Ext.onReady(function(){ 	
		
		new Ext.panel.Panel({
			renderTo: "searchSITDIV",
			contentEl : "searchSITPNL",
            frame:true , 
			title: "جستجوی قلم",
			width: 500,
			buttons :[{
				text : "جستجو",
				iconCls : "search",
				handler : searching
			}]
		});
		new Ext.KeyMap("searchSITPNL", [{key:Ext.EventObject.ENTER, fn:searching}]);
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
$dg = new sadaf_datagrid("dg","../../baseInfo/data/salary_item_type.data.php?task=SelectSIT&type=".$_GET['type'],"div_dg","search");

$col = $dg->addColumn("کد قلم","salary_item_type_id","int");
$col->renderer = "SelectRender";
$col->width = 70;
$col = $dg->addColumn("عنوان قلم", "full_title","string");

$col = $dg->addColumn("عنوان چاپی قلم", "print_title","string");
$col->width = 200;

$dg->autoExpandColumn = "full_title" ; 
$dg->pageSize = 15 ; 
$dg->EnableSearch = false;
$dg->notRender = true;
$dg->width = 500;
$dg->height = 400;
$grid = $dg->makeGrid_returnObjects();
?>
<script>
    var grid = <?=$grid?> ;
</script>
	<form id="search">
		<center>
		<div id="searchSITDIV" align="center">
		<table width="100%" id="searchSITPNL">
			<tr>
				<td>عنوان کامل:</td>
				<td><input type="text" tabindex="1" id="full_title" name="full_title" class="x-form-text x-form-field"></td>
				<td>عنوان چاپی</td>
				<td><input type="text" name="print_title" class="x-form-text x-form-field"></td>
			</tr>			
			<tr>
				<td colspan="4" align="center"><div id="btn_search"></div></td>
			</tr>
		</table>
		</div>
			<br>
		<div id="div_dg"></div>
		</center>
	</form>
</body>
</html>