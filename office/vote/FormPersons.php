<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.06
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$FormID = $_REQUEST["FormID"];

$dg = new sadaf_datagrid("dg", $js_prefix_address . "vote.data.php?task=GetFormPersons"
		. "&FormID=" . $FormID, "grid_div");

$dg->addColumn("", "RowID", "", true);
$dg->addColumn("", "FormID", "", true);
$dg->addColumn("", "fullname", "", true);

$col = $dg->addColumn("نام و نام خانوادگی", "PersonID", "");
$col->renderer="function(v,p,r){return r.data.fullname;}";
$col->editor = "this.PersonCombo";

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){VOT_FormPersonsObject.AddPerson();}";
	
	$dg->enableRowEdit = true ;
	$dg->rowEditOkHandler = "function(v,p,r){ return VOT_FormPersonsObject.Save(v,p,r);}";
}

$dg->height = 500;
$dg->width = 750;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->DefaultSortField = "ToPersonID";
$dg->emptyTextOfHiddenColumns = true;

$col = $dg->addColumn("حذف", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return VOT_FormPersons.DeleteRender(v,p,r);}";
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

VOT_FormPersons.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	FormID : "<?= $FormID ?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function VOT_FormPersons(){
	
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

VOT_FormPersons.DeleteRender = function(v,p,r){
	
	return "<div align='center' title='حذف' class='remove' "+
	"onclick='VOT_FormPersonsObject.DeletePerson();' " +
	"style='background-repeat:no-repeat;background-position:center;" +
	"cursor:pointer;width:100%;height:16'></div>";
}

VOT_FormPersons.prototype.Save = function(store,record,op){
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();    
	Ext.Ajax.request({
		url: this.address_prefix + 'vote.data.php?task=SaveFormPerson',
		params:{
			record : Ext.encode(record.data)
		},
		method: 'POST',
		success: function(response,option){
			mask.hide();
			VOT_FormPersonsObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

VOT_FormPersons.prototype.DeletePerson = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = VOT_FormPersonsObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'vote.data.php',
			params:{
				task: "RemoveFormPersons",
				RowID : record.data.RowID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				VOT_FormPersonsObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

VOT_FormPersons.prototype.AddPerson = function(){
	
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		FormID : this.FormID,
		PersonID:null		

	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

var VOT_FormPersonsObject = new VOT_FormPersons();	

</script>