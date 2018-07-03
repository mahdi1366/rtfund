<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.06
//-------------------------
require_once('../header.inc.php');
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$FormID = $_REQUEST["FormID"];

$dg = new sadaf_datagrid("dg", $js_prefix_address . "form.data.php?task=SelectAccessFromItems&FormID=" . $FormID, "div_dg");

$dg->addColumn("", "access", "", true);
$dg->addColumn("", "AccessID", "", true);
$dg->addColumn("", "GroupID", "", true);
$dg->addColumn("", "GroupDesc", "", true);

$col = $dg->addColumn("", "FormItemID");
$col->renderer = "WFM_FormAccess.CheckRender";
$col->width = 50;
$col->align = "center";
$col = $dg->addColumn("عنوان", "ItemName");

$dg->addButton("", "انتخاب همه", "add", "function(){WFM_FormAccessObject.CheckAll(1);}");
$dg->addButton("", "حذف همه", "cross", "function(){WFM_FormAccessObject.CheckAll(0);}");

$dg->DefaultSortField = "ordering";
$dg->autoExpandColumn = "ItemName";

$dg->EnableGrouping = true;
$dg->DefaultGroupField = "GroupID";
$dg->groupHeaderTpl = "{[values.rows[0].data.GroupDesc]}";

$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->width = 480;
$dg->height = 390;

$grid = $dg->makeGrid_returnObjects();
?>
<center>
	<form id="mainForm">
        <div id="panel_div"></div>
	</form>
</center>
<script>
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.06
//-------------------------

WFM_FormAccess.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	FormID : "<?= $FormID ?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function WFM_FormAccess(){
	
	this.grid = <?= $grid ?>;
	
	this.formPanel = new Ext.form.Panel({
		border : false,
		renderTo : this.get("panel_div"),
		items : [{
			xtype : "combo",
			store: new Ext.data.Store({
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + 'form.data.php?task=SelectFormSteps&FormID=' + this.FormID,
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['StepRowID','StepDesc'],
				autoLoad : true
			}),
			queryMode : "local",
			labelWidth : 150,
			fieldLabel : "انتخاب مرحله گردش فرم",
			displayField: 'StepDesc',
			valueField : "StepRowID",
			name : "StepRowID",
			width : 400,
			listeners : {
				select : function(combo,records){
					me = WFM_FormAccessObject;
					me.grid.getStore().proxy.extraParams.StepRowID = records[0].data.StepRowID;
					if(me.grid.rendered)
						me.grid.getStore().load();
					else
						me.formPanel.add(me.grid);
				}
			}
		}]
	});
}

WFM_FormAccess.CheckRender = function(v,p,r){
	
	return "<input type=checkbox id='chk_"+v+"' name='chk_"+v+"' "+
		(r.data.access == "YES" ? "checked" : "")+" onchange='WFM_FormAccessObject.ChangeAccess(this)'>";
}

WFM_FormAccess.prototype.ChangeAccess = function(el, values){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();    
	Ext.Ajax.request({
		url: this.address_prefix + 'form.data.php?task=ChangeFormAccess',
		params:{
			FormItemID : record.data.FormItemID,
			access : el.checked ? "true" : "false",
			StepRowID : this.formPanel.down("[name=StepRowID]").getValue(),
			AccessID : record.data.AccessID
			
		},
		method: 'POST',
		success: function(response,option){
			mask.hide();
		},
		failure: function(){}
	});
}

WFM_FormAccess.prototype.DeletePerson = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = WFM_FormAccessObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'wfm.data.php',
			params:{
				task: "RemoveStepPersons",
				RowID : record.data.RowID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				WFM_FormAccessObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

WFM_FormAccess.prototype.AddPerson = function(){
	
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		StepRowID : this.StepRowID,
		PersonID:null		

	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

WFM_FormAccess.prototype.CheckAll = function(mode){
	
	this.grid.getStore().each(function(record){
		WFM_FormAccessObject.get("chk_" + record.data.FormItemID).checked = mode == 1 ? true : false;
	});
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();    
	Ext.Ajax.request({
		url: this.address_prefix + 'form.data.php?task=ChangeTotalFormAccess',
		params:{
			FormID : this.FormID,
			StepRowID : this.formPanel.down("[name=StepRowID]").getValue(),
			access : mode == 1 ? "true" : "false"
			
		},
		method: 'POST',
		success: function(response,option){
			mask.hide();
		},
		failure: function(){}
	});
}

var WFM_FormAccessObject = new WFM_FormAccess();	

</script>