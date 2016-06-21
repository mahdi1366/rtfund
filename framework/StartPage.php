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
$col->renderer = "StartPage.OperationRender";
$col->sortable = false;
$col->width = 50;

$dg->height = 190;
$dg->width = 480;
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
        width: 810,
		autoScroll : true,
		frame : true,
		autoHeight : true,
        layout: 'fit',
        loader : {
			url : "../loan/request/FirstPage.php",
			params : {
				ExtTabID : this.TabID
			},
			scripts : true,
			autoLoad : true
		}
    });
	
}

StartPageObject = new StartPage();

StartPage.OperationRender = function(){
	
	return "<div align='center' title='تایید مشتری' class='tick' "+
		"onclick='StartPageObject.ConfirmPerson(1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;float:right;width:16px;height:16'></div>" +
		
	"<div align='center' title='حذف مشتری' class='cross' "+
		"onclick='StartPageObject.ConfirmPerson(0);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;float:right;width:16px;height:16'></div>";
}

StartPage.prototype.ConfirmPerson = function(mode){
	
	message = mode == 1 ? "آیا مایل به تایید می باشید؟" : "آیا مایل به حذف مشتری می باشید؟";
	Ext.MessageBox.confirm("",message, function(btn){
		if(btn == "no")
			return;
		me = StartPageObject;
		record = me.grid1.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(me.grid1,{msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'person/persons.data.php',
			method: "POST",
			params: {
				task: "ConfirmPersons",
				PersonID : record.data.PersonID,
				mode : mode
			},
			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);

				if(st.success)
					StartPageObject.grid1.getStore().load();
				else
				{
					if(st.data == "")
						alert("خطا در اجرای عملیات");
					else
						alert(st.data);
				}
			},
			failure: function(){}
		});
	})	
}

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