<?php


$dg = new sadaf_datagrid("dg",$js_prefix_address . "person/persons.data.php?task=selectPendingPersons");

$dg->addColumn("","PersonID","string", true);

$col = $dg->addColumn("نام و نام خانوادگی","fullname","string");

$col = $dg->addColumn("موبایل","mobile","string");
$col->width = 100;

$col = $dg->addColumn("نام كاربري","UserName","string");
$col->width = 120;

$col = $dg->addColumn("","","");
$col->renderer = "FrameworkStartPage.OperationRender";
$col->sortable = false;
$col->width = 50;

$dg->height = 190;
$dg->width = 450;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "PersonID";
$dg->title = "کاربران جدیدی که ثبت نام کرده اند";
$dg->autoExpandColumn = "fullname";
$dg->emptyTextOfHiddenColumns = true;
$grid1 = $dg->makeGrid_returnObjects();
?>
<script>
FrameworkStartPage.OperationRender = function(){
	
	return "<div align='center' title='تایید مشتری' class='tick' "+
		"onclick='FrameworkStartPageObject.ConfirmPerson(1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;float:right;width:16px;height:16'></div>" +
		
	"<div align='center' title='حذف مشتری' class='cross' "+
		"onclick='FrameworkStartPageObject.ConfirmPerson(0);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;float:left;width:16px;height:16'></div>";
}

FrameworkStartPage.prototype.ConfirmPerson = function(mode){
	
	message = mode == 1 ? "آیا مایل به تایید می باشید؟" : "آیا مایل به حذف مشتری می باشید؟";
	Ext.MessageBox.confirm("",message, function(btn){
		if(btn == "no")
			return;
		me = FrameworkStartPageObject;
		record = me.grid1.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(me.grid1,{msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'person/persons.data.php',
			method: "POST",
			params: {
				task: "ConfirmPendingPerson",
				PersonID : record.data.PersonID,
				mode : mode
			},
			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);

				if(st.success)					
					FrameworkStartPageObject.grid1.getStore().load();
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