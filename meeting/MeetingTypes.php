<?php
//-------------------------
// Create Date:	97.11
//-------------------------
require_once('../header.inc.php');
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................
$dg = new sadaf_datagrid("dg", $js_prefix_address . "meeting.data.php?task=selectMeetingTypes", "grid_div");

$dg->addColumn("", "TypeID", "", true);
$dg->addColumn("", "param1", "", true);

$col = $dg->addColumn("کد", "InfoID");
$col->width = 100;

$col = $dg->addColumn("شرح", "InfoDesc", "");
$col->editor = ColumnEditor::TextField();

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){MeetingTypeObject.AddMeetingType(1);}";
}

$col = $dg->addColumn("اعضا", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return MeetingType.listRender(v,p,r,2);}";
$col->width = 50;

if($accessObj->RemoveFlag)
{
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return MeetingType.DeleteRender(v,p,r,1);}";
	$col->width = 50;
}
$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(){
	var record = MeetingTypeObject.grid.getSelectionModel().getLastSelected();
	return MeetingTypeObject.SaveMeetingType(record);}";

$dg->title = "انواع جلسات";
$dg->height = 200;
$dg->width = 500;
$dg->DefaultSortField = "InfoDesc";
$dg->autoExpandColumn = "InfoDesc";
$dg->emptyTextOfHiddenColumns = true;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$grid1 = $dg->makeGrid_returnObjects();

//.............................................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "meeting.data.php?task=GetMeetingTypePersons", "grid_div");
$dg->addColumn("", "RowID", "", true);
$dg->addColumn("", "MeetingType", "", true);
$dg->addColumn("", "fullname", "", true);

$col = $dg->addColumn("نام و نام خانوادگی", "PersonID", "");
$col->renderer="function(v,p,r){return r.data.fullname;}";
$col->editor = "this.PersonCombo";

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){MeetingTypeObject.AddPerson();}";
	
	$dg->enableRowEdit = true ;
	$dg->rowEditOkHandler = "function(v,p,r){ return MeetingTypeObject.SavePerson(v,p,r);}";
}
if($accessObj->RemoveFlag)
{
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "MeetingType.DeletePersonRender";
	$col->width = 50;
}

$dg->title = "اعضای نوع جلسه";
$dg->height = 300;
$dg->width = 500;
$dg->DefaultSortField = "fullname";
$dg->autoExpandColumn = "PersonID";
$dg->emptyTextOfHiddenColumns = true;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$grid2 = $dg->makeGrid_returnObjects();

?>
<center>
	<br>
	<div id="div_grid"></div>
	<br>
	<div id="div_grid2"></div>
</center>
<script>

MeetingType.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function MeetingType(){

	this.grid = <?= $grid1 ?>;
	this.grid.plugins[0].on("beforeedit", function(editor,e){
		if(!e.record.data.InfoID)
			return MeetingTypeObject.AddAccess;
		return MeetingTypeObject.EditAccess;
	});
	this.grid.render(this.get("div_grid"));
	//.........................................................
	this.PersonCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			proxy:{
				type: 'jsonp',
				url: '/framework/person/persons.data.php?task=selectPersons&IncludeInactive=true',
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
	
	this.grid2 = <?= $grid2 ?>;
	this.grid2.plugins[0].on("beforeedit", function(editor,e){
		if(e.record.data.RowID > 0)
			return false;
		return MeetingTypeObject.AddAccess;
	});
}

MeetingType.listRender = function(v,p,r, gridIndex){
	
	return "<div align='center' title='لیست اعضا' class='user' "+
		"onclick='MeetingTypeObject.LoadItems();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

MeetingType.DeleteRender = function(v,p,r, gridIndex){
	
	return "<div align='center' title='حذف' class='remove' "+
		"onclick='MeetingTypeObject.DeleteMeetingType(" + gridIndex + ");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

MeetingType.prototype.LoadItems = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	this.grid2.getStore().proxy.extraParams = {
		MeetingType : record.data.InfoID
	};
	if(this.grid2.rendered)
		this.grid2.getStore().load();
	else
		this.grid2.render(this.get("div_grid2"));

	this.MeetingType = record.data.InfoID;
}

MeetingType.prototype.AddMeetingType = function(){

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		InfoID: 0
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

MeetingType.prototype.SaveMeetingType = function(record){

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'meeting.data.php',
		method: "POST",
		params: {
			task: "SaveMeetingType",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				MeetingTypeObject.grid.getStore().load();
				MeetingTypeObject.grid2.getStore().load();
			}
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
}

MeetingType.prototype.DeleteMeetingType = function(gridIndex){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = MeetingTypeObject;
		var record = gridIndex == 1 ? me.grid.getSelectionModel().getLastSelected() : 
			me.grid2.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'meeting.data.php',
			params:{
				task: "DeleteMeetingType",
				InfoID : record.data.InfoID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				MeetingTypeObject.grid.getStore().load();
				MeetingTypeObject.grid2.getStore().load();
			},
			failure: function(){}
		});
	});
}

//-----------------------------------------------------------

MeetingType.DeletePersonRender = function(v,p,r){
	
	return "<div align='center' title='حذف' class='remove' "+
	"onclick='MeetingTypeObject.DeletePerson();' " +
	"style='background-repeat:no-repeat;background-position:center;" +
	"cursor:pointer;width:100%;height:16'></div>";
}

MeetingType.prototype.SavePerson = function(store,record,op){
	
	mask = new Ext.LoadMask(this.grid2, {msg:'در حال ذخيره سازي...'});
	mask.show();    
	Ext.Ajax.request({
		url: this.address_prefix + 'meeting.data.php?task=SaveMeetingTypePerson',
		params:{
			record : Ext.encode(record.data)
		},
		method: 'POST',
		success: function(response,option){
			mask.hide();
			MeetingTypeObject.grid2.getStore().load();
		},
		failure: function(){}
	});
}

MeetingType.prototype.DeletePerson = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = MeetingTypeObject;
		var record = me.grid2.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(me.grid2, {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'meeting.data.php',
			params:{
				task: "RemoveMeetingTypePersons",
				RowID : record.data.RowID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				MeetingTypeObject.grid2.getStore().load();
			},
			failure: function(){}
		});
	});
}

MeetingType.prototype.AddPerson = function(){
	
	var modelClass = this.grid2.getStore().model;
	var record = new modelClass({
		RowID : 0,
		MeetingType : this.MeetingType,
		PersonID:null		

	});
	this.grid2.plugins[0].cancelEdit();
	this.grid2.getStore().insert(0, record);
	this.grid2.plugins[0].startEdit(0, 0);
}

var MeetingTypeObject = new MeetingType();	

</script>