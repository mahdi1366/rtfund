<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.02
//-----------------------------
include('../header.inc.php');
include_once inc_dataGrid;

if(empty($_POST["PlanID"]))
{
	echo "دسترسی غیر مجاز";
	die();
}
$PlanID = $_POST["PlanID"];

$dg = new sadaf_datagrid("dg", $js_prefix_address . "plan.data.php?task=GetPlanExperts&PlanID=" . $PlanID, "grid_div");

$dg->addColumn("", "RowID", "", true);
$dg->addColumn("", "PlanID", "", true);
$dg->addColumn("", "PersonID", "", true);
$dg->addColumn("", "SendDesc", "", true);
$dg->addColumn("", "ReceiveDesc", "", true);

$col = $dg->addColumn("کارشناس", "fullname", "");

$col = $dg->addColumn("تاریخ ارسال", "RegDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("توضیحات ارسالی", "SendDesc");
$col->width = 200;
$col->ellipsis = 50;

$col = $dg->addColumn("مهلت", "EndDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("تاریخ دریافت", "DoneDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("توضیحات دریافتی", "DoneDesc");
$col->width = 200;
$col->ellipsis = 50;

$col = $dg->addColumn("وضعیت", "StatusDesc", "");
$col->width = 90;
$col->renderer = "PlanExpert.StatusRender";
$col->align = "center";

$dg->addButton("", "ارسال به کارشناس", "send", "function(){PlanExpertObject.PlanExpertInfo('new');}");

$dg->height = 535;
$dg->EnablePaging = false;
$dg->autoExpandColumn = "fullname";
$dg->DefaultSortField = "PlanExpertDesc";
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->emptyTextOfHiddenColumns = true;

$col = $dg->addColumn("حذف", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return PlanExpert.DeleteRender(v,p,r);}";
$col->width = 50;

$grid = $dg->makeGrid_returnObjects();

?>
<center>
    <form id="mainForm">
		<div id="newDiv"></div>
        <div id="grid_div"></div>
    </form>
</center>
<script>

PlanExpert.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	PlanID : <?= $PlanID ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function PlanExpert(){
	
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("grid_div"));
}

PlanExpert.DeleteRender = function(v,p,r)
{
	if(r.data.StatusDesc == "RAW")
		return "<div align='center' title='حذف' class='remove' "+
		"onclick='PlanExpertObject.DeleteExpert();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

PlanExpert.StatusRender = function(v,p,r)
{
	switch(v){
		case "RAW" : return "خام";
		case "SEEN" : return "در دست اقدام";
		case "SEND" : return "ارسال کارشناس";
	}
}

PlanExpert.prototype.PlanExpertInfo = function(mode)
{
	if(!this.formPanel)
	{
		this.formPanel = new Ext.form.Panel({
			renderTo: this.get("newDiv"),                  
			frame: true,
			width : 600,
			title: 'ارسال به کارشناس',
			layout :{
				type : "table",
				columns :2,
				anchor : "100%"
			},
			items: [{
				xtype : "combo",
				store : new Ext.data.SimpleStore({
					proxy: {
						type: 'jsonp',
						url: '/framework/person/persons.data.php?task=selectPersons&UserTypes=IsExpert',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields : ['PersonID','fullname'],
					autoLoad : true
				}),
				fieldLabel : "کارشناس",
				displayField : "fullname",
				valueField : "PersonID",
				name : "PersonID",
				width : 300
			},{
				xtype:'shdatefield',
				fieldLabel: "مهلت اقدام",
				name: 'EndDate'
			},{
				xtype:'textarea',
				colspan : 2,
				width : 600,
				rows : 2,
				fieldLabel: 'توضیحات ارسالی',
				name: 'SendDesc',
				hideTrigger : true
			},{
				xtype : "hidden",
				name : "RowID"
			}],		
			buttons: [{
					text : "ذخیره",
					iconCls : "save",
					handler : function(){
						PlanExpertObject.SaveExpert()
					}
				},{
					text : "انصراف",
					iconCls : "undo",
					handler : function(){
						PlanExpertObject.grid.show();
						PlanExpertObject.formPanel.hide();						
					}
				}]
		});
	}
	
	if(mode == "new")
	{
		this.formPanel.getForm().reset();
	}
	else
	{
		var record = this.grid.getSelectionModel().getLastSelected();
		this.formPanel.getForm().loadRecord(record);
		this.formPanel.down("[name=IsCustomer]").setValue(record.data.IsCustomer == "YES");
		this.formPanel.down("[name=IsPlan]").setValue(record.data.IsPlan == "YES");
	}
	
	this.formPanel.show();
	this.grid.hide();
}

PlanExpert.prototype.DeleteExpert = function()
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = PlanExpertObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'plan.data.php',
			params:{
				task: "DeletePlanExpert",
				RowID : record.data.RowID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				PlanExpertObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

PlanExpert.prototype.SaveExpert = function(){

	mask = new Ext.LoadMask(this.formPanel, {msg:'در حال حذف ...'});
	mask.show();

	this.formPanel.getForm().submit({
		clientValidation: true,
		url : this.address_prefix + 'plan.data.php?task=SavePlanExpert',
		params : {
			PlanID : this.PlanID
		},		
		method : "POST",
		
		success : function(form,action){
			mask.hide();
			if(!action.result.success)
			{
				alert("عملیات مورد نظر با شکست مواجه شد.");
				return;
			}
			PlanExpertObject.grid.show();
			PlanExpertObject.grid.getStore().load();			
			PlanExpertObject.formPanel.hide();
		},
		failure : function(){
			mask.hide();
		}
	});
}

var PlanExpertObject = new PlanExpert();	

</script>

