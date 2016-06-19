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

$col = $dg->addColumn("تلفن","PhoneNo","string");
$col->width = 100;

$col = $dg->addColumn("موبایل","mobile","string");
$col->width = 100;

$col = $dg->addColumn("آدرس","address","string");

$col = $dg->addColumn("نام كاربري","UserName","string");
$col->width = 150;

$col = $dg->addColumn("","","");
$col->renderer = "StartPage.OperationRender";
$col->sortable = false;
$col->width = 50;

$dg->height = 200;
$dg->width = 900;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->title = "کاربران جدیدی که ثبت نام کرده اند";
$dg->DefaultSortField = "PersonID";
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
	
	this.grid1 = <?= $grid1 ?>;
	this.grid1.render(this.get("DivGrid1"));
	
}

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

StartPageObject = new StartPage();

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
<center><br>
	<div id="DivGrid1"></div><br>
	<div id="DivGrid2"></div>
</center>