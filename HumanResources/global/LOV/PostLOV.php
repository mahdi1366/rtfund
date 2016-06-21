<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.03
//---------------------------
require_once "../../header.inc.php";
require_once inc_dataGrid;
require_once inc_manage_post;

$unitsArr = manage_domains::DRP_Units("search","ouid","","همه واحدها","210","(parent_ouid='' or parent_ouid is null)");
$drp_post_type = manage_posts::dropdown_post_type("post_type", "", "همه");
?>
<html>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-all.css" />
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-rtl.css" />
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/icons.css" />
<body dir="rtl" onLoad="document.getElementById('post_no').focus();">
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-all.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-extend.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/component.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/message.js"></script>
	<script type="text/javascript">
	Ext.onReady(function(){
		new Ext.Button({renderTo : "btn_search", text: "جستجو", iconCls: 'search', handler: searching});
		
		new Ext.panel.Panel({
			id : "searchPost",
			renderTo: "searchPostDIV",
			contentEl : "searchPostPNL",
			title: "جستجوی شخص",
			width: 500,
			autoHeight: true
		});
		
		new Ext.KeyMap("searchPostPNL", [{key:Ext.EventObject.ENTER, fn:searching}]);

		<?= $unitsArr["extCombo"]?>;
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
			
		return "<a href='javascript:void(0)' onclick='select(" + r.data.post_id + ",\"" + r.data.post_no + 
			"\",\"" + r.data.title + "\",\"" + r.data.validity_start + "\",\"" + r.data.validity_end + "\");'>" + v + "</a>";
	}
	function select(post_id, post_no, title, validity_start, validity_end)
	{
		<?if($_REQUEST["Param"] != 'ALL'){ ?>
		
				window.returnValue = post_id;
				window.close(); 	
		<?	} else if($_REQUEST["Param"] == "ALL") {	?> 
				
		window.returnValue = {
			post_id : post_id,
			post_no : post_no,
			post_title : title,
			validity_start : validity_start,
			validity_end : validity_end
		};
		window.close();
			
		<? } ?>
	}
	</script>
<?php
$dg = new sadaf_datagrid("dg","../domain.data.php?task=selectAllPosts","div_dg", "search");

$col = $dg->addColumn("شناسه پست", "post_id");
$col->renderer = "SelectRender";
$col->width = 60;

$col = $dg->addColumn(" شماره پست", "post_no" );
$col->width = 80;
$col = $dg->addColumn("عنوان پست", "title");

$col = $dg->addColumn("واحد سازمانی", "unitTitle");

$col = $dg->addColumn("عنوان کامل واحد سازمانی", "full_unit_title");

$col = $dg->addColumn("تاریخ شروع اعتبار", "validity_start",GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("تاریخ پایان اعتبار", "validity_end",GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("دارنده پست", "fullname");
$col->width = 120;

$dg->EnableSearch = false;
$dg->width = 1000;
$dg->autoExpandColumn = "full_unit_title";
$dg->height = 300;
$grid = $dg->makeGrid_returnObjects();

?>
<script>
    var grid = <?=$grid?> ;
</script>

	<form id="search">
		<center>
		<div id="searchPostDIV" align="center">
		<table width="100%" id="searchPostPNL">
			<tr>
				<td>شماره پست :</td>
				<td><input type="text" tabindex="1" id="post_no" name="post_no" class="x-form-text x-form-field"></td>
				<td>نوع پست :</td>
				<td><?= $drp_post_type?></td>
			</tr>
			<tr>
				<td>عنوان پست :</td>
				<td colspan="3"><input type="text" name="title" class="x-form-text x-form-field"></td>
			</tr>
			<tr>
				<td>واحد محل خدمت :</td>
				<td colspan="3"><?= $unitsArr["combo"] ?></td>
			</tr>
			<tr>
				<td>جستجو در زیر واحد ها :</td>
				<td colspan="3"><input type="checkbox" name="sub_units"></td>
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