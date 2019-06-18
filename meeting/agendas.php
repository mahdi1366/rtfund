<?php
//-------------------------
// Create Date:	97.12
//-------------------------
require_once('../header.inc.php');
require_once inc_dataGrid;
require_once 'meeting.class.php';

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "meeting.data.php?task=GetNotDoneAgendas", "grid_div");

$dg->addColumn("", "AgendaID", "", true);
$dg->addColumn("", "MeetingID", "", true);
$dg->addColumn("", "fullname", "", true);
$dg->addColumn("", "IsDone", "", true);

$col = $dg->addColumn("نوع جلسه", "MeetingType", "");
$dt = PdoDataAccess::runquery("select * from BaseInfo where typeID=".TYPEID_MeetingType." AND IsActive='YES'");
$col->editor = ColumnEditor::ComboBox($dt,"InfoID","InfoDesc");
$col->width = 130;

$col = $dg->addColumn("شماره جلسه", "MeetingNo", "");
$col->align = "center";
$col->width = 80;

$col = $dg->addColumn("عنوان", "title", "");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("ارائه دهنده", "PersonID", "");
$col->renderer="function(v,p,r){return r.data.fullname;}";
$col->editor = "this.PersonCombo";
$col->width = 200;

$col = $dg->addColumn("زمان(دقیقه)", "PresentTime");
$col->editor = ColumnEditor::NumberField();
$col->align = "center";
$col->width = 70;

$dg->addObject("this.FilterObj");

if($accessObj->AddFlag)
{
	$dg->addButton("", "ایجاد دستور جلسه جدید", "add", "function(){MTG_agendaObject.AddAgenda();}");
	$dg->enableRowEdit = true ;
	$dg->rowEditOkHandler = "function(v,p,r){ return MTG_agendaObject.Save(v,p,r);}";
}

if($accessObj->RemoveFlag)
{
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return MTG_agenda.DeleteRender(v,p,r);}";
	$col->width = 50;
}

if($accessObj->EditFlag)
{
	$col = $dg->addColumn("", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return MTG_agenda.DoneRender(v,p,r);}";
	$col->width = 80;
}

$dg->height = 500;
$dg->title = "مدیریت دستورات جلسه";
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->DefaultSortField = "AgendaID";
$dg->autoExpandColumn = "title";
$dg->DefaultSortDir = "ASC";
$dg->emptyTextOfHiddenColumns = true;

$grid = $dg->makeGrid_returnObjects();

?>
<center>
	<div id="grid_div" style="margin: 10px"></div>
</center>
<script>

MTG_agenda.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function MTG_agenda(){
	
	this.FilterObj = Ext.button.Button({
		text: 'فیلتر لیست',
		iconCls: 'list',
		menu: {
			xtype: 'menu',
			plain: true,
			showSeparator : true,
			items: [{
				text: "دستور جلسه انجام نشده",
				group: 'filter',
				checked: true,
				handler : function(){
					me = MTG_agendaObject;
					me.grid.getStore().proxy.extraParams.IsDone = "NO";
					me.grid.getStore().loadPage(1);
				}
			},{
				text: "دستور جلسه انجام شده",
				group: 'filter',
				checked: true,
				handler : function(){
					me = MTG_agendaObject;
					me.grid.getStore().proxy.extraParams.IsDone = "YES";
					me.grid.getStore().loadPage(1);
				}
			}]
		}
	});	
	
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
		allowBlank : true,
		width : 400
	});
	
	this.grid = <?= $grid ?>;
	this.grid.getStore().proxy.extraParams.IsDone = "NO";
	this.grid.getView().getRowClass = function(record)
	{
		if(record.data.IsDone == "YES")
			return "greenRow";
		return "";
	}
	this.grid.plugins[0].on("beforeedit", function(editor,e){
		if(e.record.data.MeetingID*1 > 0 || e.record.data.IsDone == "YES")
			return false;
		return MTG_agendaObject.AddAccess;
	});
	this.grid.render(this.get("grid_div"));
}

MTG_agenda.DeleteRender = function(v,p,r){
	
	if(r.data.IsDone == "YES")
		return "";
	if(r.data.MeetingID*1 > 0)
		return "";
	return "<div align='center' title='حذف' class='remove' "+
	"onclick='MTG_agendaObject.DeleteAgenda();' " +
	"style='background-repeat:no-repeat;background-position:center;" +
	"cursor:pointer;width:100%;height:16'></div>";
}

MTG_agenda.prototype.Save = function(store,record,op){
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();    
	Ext.Ajax.request({
		url: this.address_prefix + 'meeting.data.php?task=SaveAgenda',
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
				MTG_agendaObject.RemainAgendaGrid.getStore().load();
				MTG_agendaObject.RecordGrid.getStore().load();
			},
			failure: function(){}
		});
	});
}

MTG_agenda.prototype.AddAgenda = function(){
	
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		AgendaID: null,
		MeetingID : 0,
		PersonID : null

	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

MTG_agenda.prototype.PrintAgenda = function(){
	
	window.open(this.address_prefix + "PrintAgendas.php?MeetingID=" + this.MeetingID);
}

MTG_agenda.DoneRender = function(v,p,r){
	if(r.data.IsDone == "YES")
		return "";
	return '<?= sadaf_datagrid::buttonRender("انجام شد", "tick", "MTG_agendaObject.DoneAgenda()") ?>';
}

MTG_agenda.prototype.DoneAgenda = function(store,record,op){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخيره سازي...'});
	mask.show();    
	
	Ext.Ajax.request({
		url: this.address_prefix + 'meeting.data.php?task=DoneAgenda',
		params:{
			AgendaID : record.data.AgendaID
		},
		method: 'POST',
		success: function(response,option){
			mask.hide();
			MTG_agendaObject.grid.getStore().load();
		},
		failure: function(){}
	});
}


var MTG_agendaObject = new MTG_agenda();	

</script>