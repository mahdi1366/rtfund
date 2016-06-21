<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once 'header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg",$js_prefix_address . "person/persons.data.php?task=selectPendingPersons");

$dg->addColumn("","PersonID","string", true);

$col = $dg->addColumn("نام و نام خانوادگی","fullname","string");
$col->width = 200;

$col = $dg->addColumn("موبایل","mobile","string");
$col->width = 100;

$col = $dg->addColumn("نام كاربري","UserName","string");
$col->width = 150;

$col = $dg->addColumn("","","");
$col->renderer = "FrameworkFirstPage.OperationRender";
$col->sortable = false;
$col->width = 50;

$dg->height = 190;
$dg->width = 500;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "PersonID";
$dg->title = "کاربران جدیدی که ثبت نام کرده اند";
$dg->autoExpandColumn = "address";
$dg->emptyTextOfHiddenColumns = true;
$grid1 = $dg->makeGrid_returnObjects();

?>
<script>

StartPage.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function StartPage(){
	
	 new Ext.panel.Panel({
		renderTo : this.get("panel1"),
		title : "اتوماسیون اداری",
        width: 300,
        height: 200,
		frame : true,
        layout: 'fit',
        loader : {
			url : "../office/FirstPage.php",
			params : {
				ExtTabID : this.TabID
			},
			scripts : true,
			autoLoad : true
		}
    });
	
	this.grid1 = <?= $grid1 ?>;
	new Ext.panel.Panel({
		renderTo : this.get("panel2"),
		items : this.grid1,
		frame : true
	});
	
	new Ext.panel.Panel({
		renderTo : this.get("panel3"),
		title : "تسهیلات",
        width: 820,
		autoScroll : true,
        height: 200,
        layout: 'fit',
        loader : {
			url : "../loan/FirstPage.php",
			params : {
				ExtTabID : this.TabID
			},
			scripts : true,
			autoLoad : true
		}
    }).show();
	
}

StartPageObject = new StartPage();

</script>
<table style="margin:10px">
	<tr>
		<td><div id="panel1"></div></td>
		<td width="20px"></td>
		<td><div id="panel2"></div></td>
	</tr>
	<tr>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td id="panel3" colspan="3"></td>
	</tr>
</table>