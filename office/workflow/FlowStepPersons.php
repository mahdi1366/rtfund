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

$StepRowID = $_REQUEST["StepRowID"];

$dg = new sadaf_datagrid("dg", $js_prefix_address . "wfm.data.php?task=GetStepPersons"
		. "&StepRowID=" . $StepRowID, "grid_div");

$dg->addColumn("", "RowID", "", true);
$dg->addColumn("", "StepRowID", "", true);
$dg->addColumn("", "fullname", "", true);

$col = $dg->addColumn("نام و نام خانوادگی", "PersonID", "");
$col->renderer="function(v,p,r){return r.data.fullname;}";
$col->editor = "this.PersonCombo";

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){WFM_FlowStepPersonsObject.AddPerson();}";
	
	$dg->enableRowEdit = true ;
	$dg->rowEditOkHandler = "function(v,p,r){ return WFM_FlowStepPersonsObject.Save(v,p,r);}";
}

$dg->height = 500;
$dg->width = 750;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->DefaultSortField = "ToPersonID";
$dg->emptyTextOfHiddenColumns = true;

$col = $dg->addColumn("حذف", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return WFM_FlowStepPersons.DeleteRender(v,p,r);}";
$col->width = 50;

$grid = $dg->makeGrid_returnObjects();

?>
<center>
        <div id="grid_div"></div>
</center>
<script>
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.06
//-------------------------

WFM_FlowStepPersons.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	StepRowID : "<?= $StepRowID ?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function WFM_FlowStepPersons(){
	
	this.PersonCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			proxy:{
				type: 'jsonp',
				url: '/framework/person/persons.data.php?task=selectPersons&UserType=IsStaff',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields :  ['PersonID','fullname']
		}),
		fieldLabel : "کاربر",
		displayField: 'fullname',
		valueField : "PersonID",
		hiddenName : "PersonID",
		width : 400,
		itemId : "PersonID"
	});
	
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("grid_div"));
}

WFM_FlowStepPersons.DeleteRender = function(v,p,r){
	
	return "<div align='center' title='حذف' class='remove' "+
	"onclick='WFM_FlowStepPersonsObject.DeletePerson();' " +
	"style='background-repeat:no-repeat;background-position:center;" +
	"cursor:pointer;width:100%;height:16'></div>";
}

WFM_FlowStepPersons.prototype.Save = function(store,record,op){
	
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
			WFM_FlowStepPersonsObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

WFM_FlowStepPersons.prototype.DeletePerson = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = WFM_FlowStepPersonsObject;
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
				WFM_FlowStepPersonsObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

WFM_FlowStepPersons.prototype.AddPerson = function(){
	
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		StepRowID : this.StepRowID,
		PersonID:null		

	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

var WFM_FlowStepPersonsObject = new WFM_FlowStepPersons();	

</script>