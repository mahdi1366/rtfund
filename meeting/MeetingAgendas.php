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

if($accessObj->AddFlag && !$readOnly)
{
	$dg->addButton("", "ایجاد دعوتنامه جدید", "add", "function(){MTG_MeetingAgendaObject.AddAgenda();}");
	$dg->enableRowEdit = true ;
	$dg->rowEditOkHandler = "function(v,p,r){ return MTG_MeetingAgendaObject.Save(v,p,r);}";
}

$dg->addButton("", "چاپ دعوتنامه ها", "print", "function(){MTG_MeetingAgendaObject.PrintAgenda();}");

if($accessObj->RemoveFlag && !$readOnly)
{
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return MTG_MeetingAgenda.DeleteRender(v,p,r);}";
	$col->width = 50;
}
$dg->height = 200;
$dg->title = "بررسی درخواست‌های واصله";
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->DefaultSortField = "AgendaID";
$dg->autoExpandColumn = "title";
$dg->DefaultSortDir = "ASC";
$dg->emptyTextOfHiddenColumns = true;

$grid = $dg->makeGrid_returnObjects();
//...................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "meeting.data.php?task=GetDueDateRecords", "grid_div");

$dg->addColumn("", "RecordID", "", true);

$col = $dg->addColumn("موضوع", "subject", "");

$col = $dg->addColumn("مسئول اجرا", "fullname", "");
$col->width = 180;

$col = $dg->addColumn("تاریخ پیگیری", "FollowUpDate", GridColumn::ColumnType_date);
$col->width = 100;

$col = $dg->addColumn("اضافه به لیست", "", "");
$col->renderer = "MTG_MeetingAgenda.AddRecordRender";
$col->align = "center";
$col->width = 80;

$dg->height = 200;
$dg->title = "پیگیری مصوبات گذشته";
$dg->EnableSearch = false;
$dg->EnablePaging = false;
//$dg->disableFooter = true;
$dg->DefaultSortField = "FollowUpDate";
$dg->autoExpandColumn = "subject";
$dg->DefaultSortDir = "ASC";
$dg->emptyTextOfHiddenColumns = true;

$RecordGrid = $dg->makeGrid_returnObjects();

//...................................................
$dg = new sadaf_datagrid("dg", $js_prefix_address . "meeting.data.php?task=GetRemainAgendas", "grid_div");

$dg->addColumn("", "AgendaID", "", true);

$col = $dg->addColumn("عنوان", "title", "");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("ارائه دهنده", "fullname", "");
$col->width = 200;

$col = $dg->addColumn("زمان(دقیقه)", "PresentTime");
$col->align = "center";
$col->width = 70;

$col = $dg->addColumn("اضافه به لیست", "", "");
$col->renderer = "MTG_MeetingAgenda.AddAgendaRender";
$col->align = "center";
$col->width = 80;

$dg->height = 200;
$dg->title = "موارد باقی‌مانده از جلسه گذشته";
$dg->EnableSearch = false;
$dg->EnablePaging = false;
//$dg->disableFooter = true;
$dg->DefaultSortField = "PresentTime";
$dg->autoExpandColumn = "title";
$dg->DefaultSortDir = "ASC";
$dg->emptyTextOfHiddenColumns = true;

$RemainAgendaGrid = $dg->makeGrid_returnObjects();
?>
<center>
	<div id="grid_div1"></div>
	<div id="grid_div2"></div>
    <div id="grid_div3"></div>
</center>
<script>

MTG_MeetingAgenda.prototype = {
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

function MTG_MeetingAgenda(){
	
	if(!this.readOnly)
	{
		this.RecordGrid = <?= $RecordGrid ?>;
		this.RecordGrid.getStore().proxy.extraParams.MeetingID = this.MeetingID;
		this.RecordGrid.render(this.get("grid_div1"));

		this.RemainAgendaGrid = <?= $RemainAgendaGrid ?>;
		this.RemainAgendaGrid.getStore().proxy.extraParams.MeetingID = this.MeetingID;
		this.RemainAgendaGrid.render(this.get("grid_div2"));
	}
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
		allowBlank : true,
		hiddenName : "PersonRowID",
		width : 400
	});
	
	this.grid = <?= $grid ?>;
	this.grid.getStore().proxy.extraParams.MeetingID = this.MeetingID;
	this.grid.render(this.get("grid_div3"));
}

MTG_MeetingAgenda.DeleteRender = function(v,p,r){
	
	return "<div align='center' title='حذف' class='remove' "+
	"onclick='MTG_MeetingAgendaObject.DeleteAgenda();' " +
	"style='background-repeat:no-repeat;background-position:center;" +
	"cursor:pointer;width:100%;height:16'></div>";
}

MTG_MeetingAgenda.prototype.Save = function(store,record,op){
	
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
			MTG_MeetingAgendaObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

MTG_MeetingAgenda.prototype.DeleteAgenda = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = MTG_MeetingAgendaObject;
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
				MTG_MeetingAgendaObject.grid.getStore().load();
				MTG_MeetingAgendaObject.RemainAgendaGrid.getStore().load();
				MTG_MeetingAgendaObject.RecordGrid.getStore().load();
			},
			failure: function(){}
		});
	});
}

MTG_MeetingAgenda.prototype.AddAgenda = function(){
	
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

MTG_MeetingAgenda.prototype.PrintAgenda = function(){
	
	window.open(this.address_prefix + "PrintAgendas.php?MeetingID=" + this.MeetingID);
}

MTG_MeetingAgenda.AddRecordRender = function(){
	
	return '<?= sadaf_datagrid::buttonRender("اضافه", "add", "MTG_MeetingAgendaObject.AddRecord()") ?>';
}

MTG_MeetingAgenda.AddAgendaRender = function(){
	
	return '<?= sadaf_datagrid::buttonRender("اضافه", "add", "MTG_MeetingAgendaObject.AddRemainAgenda()") ?>';
}

MTG_MeetingAgenda.prototype.AddRecord = function(){
	
	Ext.MessageBox.prompt("", "زمان لازم جهت ارائه", function(btn, text){
		if(btn == "cancel")
			return;
		
		me = MTG_MeetingAgendaObject;
		var record = me.RecordGrid.getSelectionModel().getLastSelected();

		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'meeting.data.php',
			params:{
				task: "AddRecordToAgenda",
				MeetingID : me.MeetingID,
				RecordID : record.data.RecordID,
				PresentTime : text
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				MTG_MeetingAgendaObject.RecordGrid.getStore().load();
				MTG_MeetingAgendaObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

MTG_MeetingAgenda.prototype.AddRemainAgenda = function(){
	
	var record = this.RemainAgendaGrid.getSelectionModel().getLastSelected();

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + 'meeting.data.php',
		params:{
			task: "AddRemainAgendaToAgenda",
			MeetingID : this.MeetingID,
			AgendaID : record.data.AgendaID
		},
		method: 'POST',

		success: function(response,option){
			mask.hide();
			MTG_MeetingAgendaObject.RemainAgendaGrid.getStore().load();
			MTG_MeetingAgendaObject.grid.getStore().load();
		},
		failure: function(){}
	});
}


var MTG_MeetingAgendaObject = new MTG_MeetingAgenda();	

</script>