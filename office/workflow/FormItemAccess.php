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

$dg = new sadaf_datagrid("dg", $js_prefix_address . "form.data.php?task=selectFormItems", "div_dg");

$col = $dg->addColumn("عنوان", "FormItemID");
$col->renderer = "WFM_FormAccess.CheckRender";

$col = $dg->addColumn("عنوان", "ItemName");
$col->width = 200;

$col = $dg->addColumn("نوع", "ItemType");
$col->editor = "this.ItemTypeCombo";

$col = $dg->addColumn("مقادیر لیست", "ComboValues");

$dg->DefaultSortField = "FormItemID";
$dg->DefaultSortDir = "desc";
$dg->autoExpandColumn = "ComboValues";

$dg->width = 790;
$dg->height = 460;
$dg->pageSize = 20;

$grid = $dg->makeGrid_returnObjects();
?>
<center>
	<form id="mainForm">
        <div id="grid_div"></div>
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
	
	this.formPanel = new Ext.form.Panel({
		border : false,
		items : [{
			xtype : "combo",
			store: new Ext.data.Store({
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + 'form.data.php?task=SelectFormSteps&FormID=' + this.FormID,
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['StepRowID','StepDesc']
			}),
			fieldLabel : "انتخاب مرحله گردش فرم",
			displayField: 'StepDesc',
			valueField : "StepRowID",
			name : "StepRowID",
			width : 400
		},
		this.grid]
	});
}

WFM_FormAccess.DeleteRender = function(v,p,r){
	
	return "<div align='center' title='حذف' class='remove' "+
	"onclick='WFM_FormAccessObject.DeletePerson();' " +
	"style='background-repeat:no-repeat;background-position:center;" +
	"cursor:pointer;width:100%;height:16'></div>";
}

WFM_FormAccess.CheckRender = function(v,p,r){
	
	return "<input type=checkbox name='chk_"+v+"' >";
}

WFM_FormAccess.prototype.Save = function(store,record,op){
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();    
	Ext.Ajax.request({
		url: this.address_prefix + 'wfm.data.php?task=SaveStepPerson',
		params:{
			record : Ext.encode(record.data)
		},
		method: 'POST',
		success: function(response,option){
			mask.hide();
			WFM_FormAccessObject.grid.getStore().load();
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

var WFM_FormAccessObject = new WFM_FormAccess();	

</script>