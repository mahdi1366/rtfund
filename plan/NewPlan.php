<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.12
//-----------------------------

require_once 'header.inc.php';
require_once inc_dataGrid;
require_once 'plan.class.php';

$dt = PLN_plans::SelectAll("PersonID=? AND StatusID<>100", array($_SESSION["USER"]["PersonID"]));
$dt = $dt->fetchAll();
$Mode = count($dt) == 0 ? "new" : ($dt[0]["StatusID"] == "1" ? "edit" : "list");

$PlanID = $Mode == "new" ? "0" : $dt[0]["PlanID"];
$PlanDesc = $Mode == "new" ? "" : $dt[0]["PlanDesc"];

//.............................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "plan.data.php?task=SelectMyPlans", "grid_div");

$dg->addColumn("", "StatusID", "", true);

$col = $dg->addColumn("شماره درخواست", "PlanID", "");
$col->width = 100;
$col->align = "center";

$col = $dg->addColumn("عنوان طرح", "PlanDesc", "");

$col = $dg->addColumn("تاریخ درخواست", "RegDate", GridColumn::ColumnType_date);
$col->width = 110;

$col = $dg->addColumn("وضعیت", "StatusDesc", "");
$col->width = 120;

$col = $dg->addColumn('عملیات', '', 'string');
$col->renderer = "NewPlan.OperationRender";
$col->width = 50;
$col->align = "center";

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 300;
$dg->width = 770;
$dg->title = "طرح های ارسالی";
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->DefaultSortField = "RegDate";
$dg->autoExpandColumn = "PlanDesc";
$grid = $dg->makeGrid_returnObjects();

?>
<center>
	<div id="div_plan"></div>
	<div id="div_grid"></div>
</center>	
<script>
NewPlan.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	PlanID : <?= $PlanID ?>,
	PlanDesc : '<?= $PlanDesc ?>',
	Mode : '<?= $Mode ?>',
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function NewPlan(){
	
	if(this.Mode == "new" || this.Mode == "edit")
	{
		this.planFS = new Ext.form.FieldSet({
			title : "ثبت طرح جدید",
			width : 700,
			renderTo : this.get("div_plan"),
			items : [{
				xtype : "textfield",
				fieldLabel : "عنوان طرح",
				name : "PlanDesc",
				width : 600,
				value : this.PlanDesc
			},{
				xtype : "button",
				text : this.PlanID == 0 ? "ثبت طرح و تکمیل جداول اطلاعاتی" : "ویرایش جداول اطلاعاتی",
				iconCls : "arrow_left",
				handler : function(){ NewPlanObject.SaveNewPlan(); }
			},{
				xtype : "hidden",
				name : "PlanID",
				value : this.PlanID
			}]
		});
	}
	
	this.grid = <?= $grid ?>;
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.StatusID == "3")
			return "pinkRow";

		return "";
	}
	this.grid.render(this.get("div_grid"));
	
}

NewPlan.OperationRender = function(v,p,record){

	var str = "";
	
	str += "<div  title='اطلاعات طرح' class='info2' onclick='NewPlanObject.ShowPlanInfo();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;height:16;float:right'></div>";
	
	str += "<div  title='سابقه درخواست' class='history' onclick='NewPlanObject.ShowHistory();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;height:16;float:right'></div>";
	
	return str;
}

NewPlanObject = new NewPlan();

NewPlan.prototype.SaveNewPlan = function(){

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();  

	Ext.Ajax.request({
		methos : "post",
		url : this.address_prefix + "plan.data.php",
		params : {
			task : "SaveNewPlan",
			PlanID : this.PlanID,
			PlanDesc : this.planFS.down("[name=PlanDesc]").getValue()
		},

		success : function(response){
			mask.hide();
			result = Ext.decode(response.responseText);
			if(result.success)
				portal.OpenPage("../plan/PlanInfo.php", {PlanID : result.data});
			else
				Ext.MessageBox.alert("Error", "عملیات مورد نظر با شکست مواجه شد");
		}
	});
		
}

NewPlan.prototype.ShowPlanInfo = function(){
	
	record = this.grid.getSelectionModel().getLastSelected();
	portal.OpenPage("../plan/PlanInfo.php", {PlanID : record.data.PlanID});
}

NewPlan.prototype.ShowHistory = function(){

	if(!this.HistoryWin)
	{
		this.HistoryWin = new Ext.window.Window({
			title: 'سابقه گردش طرح',
			modal : true,
			autoScroll : true,
			width: 700,
			height : 500,
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "history.php",
				scripts : true
			},
			buttons : [{
					text : "بازگشت",
					iconCls : "undo",
					handler : function(){
						this.up('window').hide();
					}
				}]
		});
		Ext.getCmp(this.TabID).add(this.HistoryWin);
	}
	this.HistoryWin.show();
	this.HistoryWin.center();
	this.HistoryWin.loader.load({
		params : {
			PlanID : this.grid.getSelectionModel().getLastSelected().data.PlanID
		}
	});
}

</script>
