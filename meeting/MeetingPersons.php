<?php
//-------------------------
// Create Date:	97.11
//-------------------------
require_once('../header.inc.php');
require_once inc_dataGrid;
require_once 'meeting.class.php';

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$MeetingID = (int)$_REQUEST["MeetingID"];
$obj = new MTG_meetings($MeetingID);
$readOnly = $obj->StatusID == MTG_STATUSID_RAW ? false : true;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "meeting.data.php?task=GetMeetingPersons"
		. "&MeetingID=" . $MeetingID, "grid_div");

$dg->addColumn("", "RowID", "", true);
$dg->addColumn("", "MeetingID", "", true);
$dg->addColumn("", "fullname", "", true);

$col = $dg->addColumn("نام و نام خانوادگی", "PersonID", "");
$col->renderer="function(v,p,r){return r.data.fullname;}";
$col->editor = "this.PersonCombo";

$col = $dg->addColumn("نوع فرد", "AttendType", "");
$col->renderer = "function(v,p,r){return v == 'MEMBER' ? 'عضو جلسه' : 'مهمان جلسه';}";
$col->width = 120;

$col = $dg->addColumn("وضعیت حضور", "IsPresent", "");
$col->renderer = "MTG_MeetingPersons.AttendRender";
$col->align = "center";
$col->width = 80;

if($accessObj->AddFlag && !$readOnly)
{
	$dg->addButton("", "ایجاد مهمان از ذینفعان", "add", "function(){MTG_MeetingPersonsObject.AddPerson();}");
	$dg->addButton("", "ایجاد مهمان خارجی", "add", "function(){MTG_MeetingPersonsObject.AddGuest();}");
	
	$dg->enableRowEdit = true ;
	$dg->rowEditOkHandler = "function(v,p,r){ return MTG_MeetingPersonsObject.Save(v,p,r);}";
}
if($accessObj->RemoveFlag && !$readOnly)
{
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return MTG_MeetingPersons.DeleteRender(v,p,r);}";
	$col->width = 50;
}
$dg->height = 365;
$dg->width = 770;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->DefaultSortField = "RowID";
$dg->DefaultSortDir = "ASC";
$dg->emptyTextOfHiddenColumns = true;

$grid = $dg->makeGrid_returnObjects();

?>
<center>
        <div id="grid_div"></div>
</center>
<script>

MTG_MeetingPersons.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	MeetingID : "<?= $MeetingID ?>",
	readOnly : <?= $readOnly ? "true" : "false"?>,
	
	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function MTG_MeetingPersons(){
	
	this.PersonCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			proxy:{
				type: 'jsonp',
				url: '/framework/person/persons.data.php?task=selectPersons',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields :  ['PersonID','fullname']
		}),
		displayField: 'fullname',
		valueField : "PersonID",
		hiddenName : "PersonID",
		width : 400,
		itemId : "PersonID"
	});
	
	this.grid = <?= $grid ?>;
	if(!this.readOnly)
	{
		this.grid.plugins[0].on("beforeedit", function(editor,e){
			if(e.record.data.RowID > 0)
				return false;
			return MTG_MeetingPersonsObject.AddAccess;
		});
	}
	this.grid.render(this.get("grid_div"));
}

MTG_MeetingPersons.DeleteRender = function(v,p,r){
	
	if(r.data.AttendType == "MEMBER" || r.data.IsPresent != "NOTSET")
		return "";
	return "<div align='center' title='حذف' class='remove' "+
	"onclick='MTG_MeetingPersonsObject.DeletePerson();' " +
	"style='background-repeat:no-repeat;background-position:center;" +
	"cursor:pointer;width:100%;height:16'></div>";
}

MTG_MeetingPersons.prototype.Save = function(store,record,op){
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();    
	Ext.Ajax.request({
		url: this.address_prefix + 'meeting.data.php?task=SaveMeetingPerson',
		params:{
			record : Ext.encode(record.data)
		},
		method: 'POST',
		success: function(response,option){
			mask.hide();
			MTG_MeetingPersonsObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

MTG_MeetingPersons.prototype.DeletePerson = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = MTG_MeetingPersonsObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'meeting.data.php',
			params:{
				task: "RemoveMeetingPersons",
				RowID : record.data.RowID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				MTG_MeetingPersonsObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

MTG_MeetingPersons.prototype.AddPerson = function(){
	
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		RowID : 0,
		MeetingID : this.MeetingID,
		PersonID:null		

	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

MTG_MeetingPersons.prototype.AddGuest = function(){

	Ext.MessageBox.prompt('', 'لطفا نام مهمان را وارد کنید:', function(btn, text){
		
		if(btn == "cancel")
			return;
		
		me = MTG_MeetingPersonsObject;
		var modelClass = me.grid.getStore().model;
		var record = new modelClass({
			RowID : 0,
			MeetingID : me.MeetingID,
			fullname : text
		});
		me.Save(null, record);
	});
}

MTG_MeetingPersons.AttendRender = function(v,p,r){
	
	if(v != "NOTSET")
		return v == "YES" ? 
			"<div align='center' title='حاضر' class='tick' "+
			"style='background-repeat:no-repeat;background-position:center;" +
			"width:16px;height:16px;'></div>" 
			: 
			"<div align='center' title='غایب' class='cross' "+
			"style='background-repeat:no-repeat;background-position:center;" +
			"width:16px;height:16px;'></div>" ;
	
	return "<div align='center' title='حاضر' class='tick' "+
	"onclick='MTG_MeetingPersonsObject.SetPresent(\"YES\");' " +
	"style='background-repeat:no-repeat;background-position:center;" +
	"cursor:pointer;width:16px;height:16px;float:right'></div>" + 
	
	"<div align='center' title='غایب' class='cross' "+
	"onclick='MTG_MeetingPersonsObject.SetPresent(\"NO\");' " +
	"style='background-repeat:no-repeat;background-position:center;" +
	"cursor:pointer;width:16px;height:16px;float:left'></div>";
}

MTG_MeetingPersons.prototype.SetPresent = function(value)
{
	var record = this.grid.getSelectionModel().getLastSelected();
	if(!record)
		return;

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: me.address_prefix +'meeting.data.php',
		method: 'POST',
		params: {
			task: 'SetPresent',
			RowID : record.data.RowID,
			IsPresent : value
		},

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
				MTG_MeetingPersonsObject.grid.getStore().load();
			else
				alert(st.data);
		},
		failure: function(){}
	});
}

var MTG_MeetingPersonsObject = new MTG_MeetingPersons();	

</script>