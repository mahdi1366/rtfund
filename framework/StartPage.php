<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once 'header.inc.php';
require_once 'management/framework.class.php';
require_once inc_dataGrid;

//....................................................

$frameworkAccess = false;
$LoanAccess = false;
$PlanAccess = false;

$systems = FRW_access::getAccessSystems();
foreach($systems as $row)
{
	switch($row["SystemID"])
	{
		case SYSTEMID_framework: $frameworkAccess = true; break;
		case SYSTEMID_loan: $LoanAccess = true; break;
		case SYSTEMID_plan: $PlanAccess = true; break;
	}
}

//....................................................

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

FrameworkStartPage.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function FrameworkStartPage(){
	
	 new Ext.panel.Panel({
		renderTo : this.get("panel1"),
		title : "اتوماسیون اداری",
        width: 220,
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
	
	<?if($frameworkAccess){?>
	this.grid1 = <?= $grid1 ?>;
	new Ext.panel.Panel({
		renderTo : this.get("panel2"),
		items : this.grid1,
		frame : true
	});
	<?}?>
	
	new Ext.panel.Panel({
		renderTo : this.get("panel3"),
		title : "ثبت ورود و خروج",
        width: 100,
        height: 200,
		frame : true,
        layout: 'fit',
        loader : {
			url : "../attendance/traffic/AddTraffic.php?StartPage=true",
			params : {
				ExtTabID : this.TabID
			},
			scripts : true,
			autoLoad : true
		}
    });
	<?if($LoanAccess){?>
	new Ext.panel.Panel({
		renderTo : this.get("panel4"),
		title : "تسهیلات",
        width: 800,
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
	<?}?>
		
	<?if($PlanAccess){?>
	new Ext.panel.Panel({
		renderTo : this.get("panel5"),
        width: 800,
		title : "هشدارهای مربوط به کارشناسی طرح ها",
		autoScroll : true,
		frame : true,
		autoHeight : true,
        layout: 'fit',
        loader : {
			url : "../plan/FirstPage.php",
			params : {
				ExtTabID : this.TabID
			},
			scripts : true,
			autoLoad : true
		}
    });
	<?}?>
}

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
				task: "ConfirmPersons",
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

FrameworkStartPageObject = new FrameworkStartPage();

</script>
<table style="margin:10px">
	<tr>
		<td><div id="panel1"></div></td>
		<td width="10px"></td>
		<td><div id="panel3"></div></td>
		<td width="10px"></td>
		<td><div id="panel2"></div></td>
	</tr>
	<tr>
		<td colspan="5">&nbsp;</td>
	</tr>
	<tr>
		<td id="panel4" colspan="5"></td>
	</tr>
	<tr>
		<td colspan="5">&nbsp;</td>
	</tr>
	<tr>
		<td id="panel5" colspan="5"></td>
	</tr>
</table>