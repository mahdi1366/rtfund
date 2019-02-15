<?php
//-------------------------
// Create Date:	97.11
//-------------------------
require_once('../header.inc.php');
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$MeetingID = $_REQUEST["MeetingID"];

$dg = new sadaf_datagrid("dg", $js_prefix_address . "meeting.data.php?task=GetMeetingAgendas", "grid_div");

$dg->addColumn("", "AgendaID", "", true);
$dg->addColumn("", "MeetingID", "", true);
$dg->addColumn("", "fullname", "", true);

$col = $dg->addColumn("عنوان", "title", "");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("ارائه دهنده", "PersonRowID", "");
$col->renderer="function(v,p,r){return r.data.fullname;}";
$col->editor = "this.PersonCombo";
$col->width = 200;

$col = $dg->addColumn("زمان(دقیقه)", "PresentTime");
$col->editor = ColumnEditor::NumberField();
$col->align = "center";
$col->width = 70;

if($accessObj->AddFlag)
{
	$dg->addButton("", "ایجاد دعوتنامه جدید", "add", "function(){MTG_agendaObject.AddAgenda();}");
	$dg->enableRowEdit = true ;
	$dg->rowEditOkHandler = "function(v,p,r){ return MTG_agendaObject.Save(v,p,r);}";
}


$col = $dg->addColumn("حذف", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return MTG_agenda.DeleteRender(v,p,r);}";
$col->width = 50;

$dg->height = 365;
$dg->width = 770;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->DefaultSortField = "AgendaID";
$dg->autoExpandColumn = "title";
$dg->DefaultSortDir = "ASC";
$dg->emptyTextOfHiddenColumns = true;

$grid = $dg->makeGrid_returnObjects();

?>
<center>
        <div id="grid_div"></div>
</center>
<script>

MTG_agenda.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	MeetingID : "<?= $MeetingID ?>",
	
	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function MTG_agenda(){
	
	this.PersonCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			proxy:{
				type: 'jsonp',
				url: this.address_prefix + 'meeting.data.php?task=GetMeetingPersons&MeetingID=' + this.MeetingID,
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields :  ['RowID','fullname']
		}),
		displayField: 'fullname',
		valueField : "RowID",
		allowBlank : false,
		hiddenName : "PersonRowID",
		width : 400
	});
	
	this.grid = <?= $grid ?>;
	this.grid.getStore().proxy.extraParams.MeetingID = this.MeetingID;
	this.grid.plugins[0].on("beforeedit", function(editor,e){
		if(e.record.data.RowID > 0)
			return false;
		return MTG_agendaObject.AddAccess;
	});
	this.grid.render(this.get("grid_div"));
}

MTG_agenda.DeleteRender = function(v,p,r){
	
	return "<div align='center' title='حذف' class='remove' "+
	"onclick='MTG_agendaObject.DeleteAgenda();' " +
	"style='background-repeat:no-repeat;background-position:center;" +
	"cursor:pointer;width:100%;height:16'></div>";
}

MTG_agenda.prototype.Save = function(store,record,op){
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();    
	Ext.Ajax.request({
		url: this.address_prefix + 'meeting.data.php?task=SaveMeetingAgenda',
		params:{
			record : Ext.encode(record.data)
		},
		method: 'POST',
		success: function(response,option){
			mask.hide();
			MTG_agendaObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

MTG_agenda.prototype.DeleteAgenda = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = MTG_agendaObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'meeting.data.php',
			params:{
				task: "RemoveAgenda",
				AgendaID : record.data.AgendaID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				MTG_agendaObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

MTG_agenda.prototype.AddAgenda = function(){
	
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		AgendaID:null,
		MeetingID : this.grid.getStore().proxy.extraParams.MeetingID,
		PersonID : null

	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

var MTG_agendaObject = new MTG_agenda();	

</script>