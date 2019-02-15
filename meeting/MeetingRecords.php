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

$dg = new sadaf_datagrid("dg", $js_prefix_address . "meeting.data.php?task=GetMeetingRecords"
		. "&MeetingID=" . $MeetingID, "grid_div");

$dg->addColumn("", "RecordID", "", true);
$dg->addColumn("", "MeetingID", "", true);
$dg->addColumn("", "PersonID", "", true);
$dg->addColumn("", "details", "", true);
$dg->addColumn("", "keywords", "", true);

$col = $dg->addColumn("موضوع", "subject", "");

$col = $dg->addColumn("مسئول اجرا", "fullname", "");
$col->width = 180;

$col = $dg->addColumn("تاریخ پیگیری", "FollowUpDate", "", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("وضعیت", "RecordStatus", "");
$col->renderer = "MTG_MeetingRecords.StatusRender";
$col->width = 80;

if($accessObj->AddFlag)
{
	$dg->addButton("", "ایجاد مصوبه", "add", "function(){MTG_MeetingRecordsObject.AddRecord();}");
}
if($accessObj->EditFlag)
{
	$col = $dg->addColumn("ویرایش", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return MTG_MeetingRecords.EditRender(v,p,r);}";
	$col->width = 50;
}
if($accessObj->RemoveFlag)
{
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return MTG_MeetingRecords.DeleteRender(v,p,r);}";
	$col->width = 50;
}

$dg->addPlugin("this.Details");

$dg->height = 365;
$dg->width = 770;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->DefaultSortField = "RecordID";
$dg->DefaultSortDir = "ASC";
$dg->emptyTextOfHiddenColumns = true;

$grid = $dg->makeGrid_returnObjects();

?>
<center>
        <div id="grid_div"></div>
</center>
<script>

MTG_MeetingRecords.prototype = {
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

function MTG_MeetingRecords(){
	
	this.Details = {
		ptype: 'rowexpander',
		rowBodyTpl : [
			'<hr>','توضیحات: {details}<br>',
			'کلمات کلیدی: {[values.keywords == null ? "" : values.keywords]}'
		]
	};
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("grid_div"));
	
	this.formWindow = new Ext.Window({
		width : 600,
		autoHeight : true,
		modal : true,
		items : this.formPanel = new Ext.form.FormPanel({
			items :[{
				xtype : "textfield",
				name : "subject",
				width : 550,
				fieldLabel : "موضوع",
				allowBlank : false
			},{
				xtype : "textarea",
				name : "details",
				width : 550,
				fieldLabel : "توضیحات",
				rows : 4
			},{
				xtype : "textfield",
				name : "keywords",
				width : 550,
				fieldLabel : "کلمات کلیدی"
			},{
				xtype : "combo",
				name : "PersonID",
				fieldLabel : "مسئول اجرا",
				store: new Ext.data.Store({
					proxy:{
						type: 'jsonp',
						url: '/framework/person/persons.data.php?task=selectPersons&UserType=IsStaff',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields :  ['PersonID','fullname']
				}),
				displayField: 'fullname',
				valueField : "PersonID",
				allowBlank : false
			},{
				xtype : "shdatefield",
				name : "FollowUpDate",
				fieldLabel : "تاریخ پیگیری"
			},{
				xtype : "combo",
				name : "RecordStatus",
				fieldLabel : "وضعیت",
				store : new Ext.data.SimpleStore({
					data : [
						['CUR' , "جاری" ],
						['END' , "مختومه" ],
						['REF' , "ارجاعی" ]
					],
					fields : ['id','value']
				}),
				displayField : "value",
				valueField : "id",
				allowBlank : false
			},{
				xtype : "hidden",
				name :"MeetingID",
				value : this.MeetingID
			},{
				xtype : "hidden",
				name :"RecordID"
			}]
		}),
		buttons : [{
			text : "ذخیره",
			iconCls : "save",
			handler : function(){ MTG_MeetingRecordsObject.Save(); }
		},{
			text : "بازگشت",
			iconCls : "undo",
			handler : function(){this.up('window').hide();}
		}]
	});
}

MTG_MeetingRecords.DeleteRender = function(v,p,r){
	
	return "<div align='center' title='حذف' class='remove' "+
	"onclick='MTG_MeetingRecordsObject.DeleteRecord();' " +
	"style='background-repeat:no-repeat;background-position:center;" +
	"cursor:pointer;width:100%;height:16'></div>";
}

MTG_MeetingRecords.EditRender = function(v,p,r){
	
	return "<div align='center' title='ویرایش' class='edit' "+
	"onclick='MTG_MeetingRecordsObject.EditRecord();' " +
	"style='background-repeat:no-repeat;background-position:center;" +
	"cursor:pointer;width:100%;height:16'></div>";
}

MTG_MeetingRecords.StatusRender = function(v,p,r){
	
	switch(v)
	{
		case 'CUR' : return 'جاری';
		case 'END' : return 'مختومه';
		case 'REF' : return 'ارجاعی';
	}
}

MTG_MeetingRecords.prototype.Save = function(){
	
	if(!this.formPanel.getForm().isValid())
		return;
	
	mask = new Ext.LoadMask(this.formWindow, {msg:'در حال ذخيره سازي...'});
	mask.show();  
	
	this.formPanel.getForm().submit({
		url: this.address_prefix + 'meeting.data.php?task=SaveMeetingRecord',
		method: 'POST',
		success: function(){
			mask.hide();
			MTG_MeetingRecordsObject.grid.getStore().load();
			MTG_MeetingRecordsObject.formWindow.hide();
		},
		failure: function(){
			mask.hide();
			Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه گردید");
		}
	});
}

MTG_MeetingRecords.prototype.DeleteRecord = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = MTG_MeetingRecordsObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'meeting.data.php',
			params:{
				task: "RemoveMeetingRecords",
				RecordID : record.data.RecordID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				MTG_MeetingRecordsObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

MTG_MeetingRecords.prototype.AddRecord = function(){
	
	this.formWindow.show();
	this.formPanel.getForm().reset();
}

MTG_MeetingRecords.prototype.EditRecord = function(){
	
	record = this.grid.getSelectionModel().getLastSelected();
	this.formWindow.show();
	this.formPanel.getForm().loadRecord(record);
}

var MTG_MeetingRecordsObject = new MTG_MeetingRecords();	

</script>