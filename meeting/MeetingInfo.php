<?php
//-----------------------------
//	Date		: 97.11
//-----------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;

$MeetingID = !empty($_POST["MeetingID"]) ? $_POST["MeetingID"] : "";

//................  GET ACCESS  .....................
if(isset($_POST["MenuID"]))
	$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
else
{
	$accessObj = new FRW_access();
	$accessObj->AddFlag = false;
	$accessObj->EditFlag = false;
	$accessObj->RemoveFlag = false;
}
//...................................................
?>

<script>

MeetingInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	MeetingID : '<?= $MeetingID ?>',
	MenuID : "<?= $_POST["MenuID"] ?>",
	
	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function MeetingInfo(){

	this.store = new Ext.data.Store({
		proxy : {
			type: 'jsonp',
			url: this.address_prefix + "meeting.data.php?task=SelectAllMeetings&MeetingID=" + this.MeetingID,
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["MeetingID","MeetingType","place","MeetingDate","StartTime","EndTime",
			"details","secretary","StatusID"],
		
		listeners : {
			load : function(){
				me = MeetingInfoObject;
				me.BuildForms();
				//..........................................................
				record = this.getAt(0);
				me.MeetingPanel.loadRecord(record);
				me.MeetingPanel.down("[name=StartTime]").setValue(record.data.StartTime.substr(0,5));
				me.MeetingPanel.down("[name=EndTime]").setValue(record.data.EndTime.substr(0,5));			
				MeetingInfoObject.mask.hide();
				//..........................................................
				me.TabPanel.down("[itemId=persons_tab]").enable();	
				me.TabPanel.down("[itemId=agendas_tab]").enable();	
				me.TabPanel.down("[itemId=attach_tab]").enable();
			}
			
		}
	});
	
	if(this.MeetingID > 0)
	{
		this.mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال بارگذاری...'});
		this.mask.show();
		this.store.load();
	}
	else
		this.BuildForms();
}

MeetingInfo.prototype.BuildForms = function(){
	
	StatusID = this.store.totalCount == undefined ? "-1" : this.store.getAt(0).data.StatusID;
	
	this.TabPanel = new Ext.TabPanel({
		renderTo : this.get("mainForm"),
		width : 780,
		height : 430,
		plain:true,
		tbar : [{
			text : "جلسه برگزار شده است",
			iconCls : "tick",
			hidden : StatusID == "<?= MTG_STATUSID_RAW ?>" ? false : true,
			handler : function(){ MeetingInfoObject.ChangeMeetingStatus("<?= MTG_STATUSID_DONE ?>"); }
		},{
			text : "جلسه کنسل شده است",
			iconCls : "cross",
			hidden : StatusID == "<?= MTG_STATUSID_RAW ?>" ? false : true,
			handler : function(){ MeetingInfoObject.ChangeMeetingStatus("<?= MTG_STATUSID_CANCLE ?>"); }
		},{
			text : "چاپ دعوتنامه ها",
			iconCls : "print",
			handler : function(){
				me = MeetingInfoObject;
				window.open(me.address_prefix + "PrintAgendas.php?MeetingID=" + me.MeetingID);
			}
		},{
			text : "چاپ مصوبه ها",
			iconCls : "print",
			disabled : StatusID == "<?= MTG_STATUSID_DONE ?>" ? false : true,
			handler : function(){
				me = MeetingInfoObject;
				window.open(me.address_prefix + "PrintRecords.php?MeetingID=" + me.MeetingID);
			}
		}],
		items :[{
			title : "اطلاعات جلسه",
			items : this.MeetingPanel = new Ext.form.Panel({
				border : false,
				layout : {
					type : "table",
					columns : 2
				},
				defaults : {
					labelWidth : 110,
					width : 370
				},
				width: 780,
				items : [{
					xtype : "combo",
					name : "MeetingType",
					store: new Ext.data.Store({
						proxy:{
							type: 'jsonp',
							url: this.address_prefix + 'meeting.data.php?task=selectMeetingTypes',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						fields :  ['InfoID','InfoDesc'],
						autoLoad : true
					}),
					fieldLabel : "نوع جلسه",
					queryMode : "local",
					displayField: 'InfoDesc',
					valueField : "InfoID",
					allowBlank : false
				},{
					xtype : "textfield",
					fieldLabel : "مکان برگزاری",
					name : "place",
					allowBlank : false
				},{
					xtype : "shdatefield",
					name : "MeetingDate",
					fieldLabel : "تاریخ جلسه",
					allowBlank : false
				},{
					xtype : "container",
					layout : "hbox",
					items : [{
						xtype : "timefield",
						name : "StartTime",
						format : "H:i",
						hideTrigger : true,
						submitFormat : "H:i:s",
						labelWidth : 110,
						width : 240,
						fieldLabel : "از ساعت",
						allowBlank : false
					},{
						xtype : "timefield",
						name : "EndTime",
						fieldLabel : "تا ساعت",
						hideTrigger : true,
						format : "H:i",
						hideTrigger : true,
						submitFormat : "H:i:s",
						labelWidth : 50,
						width : 130,
						allowBlank : false
					}]
				},{
					xtype : "textarea",
					name : "details",
					rows : 4,
					colspan : 2,
					width : 700,
					fieldLabel : "توضیحات"
				},{
					xtype : "combo",
					name : "secretary",
					store: new Ext.data.Store({
						proxy:{
							type: 'jsonp',
							url: '/framework/person/persons.data.php?task=selectPersons&UserType=IsStaff',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						fields :  ['PersonID','fullname'],
						autoLoad : true
					}),
					fieldLabel : "دبیر جلسه",
					queryMode : "local",
					displayField: 'fullname',
					valueField : "PersonID",
					colspan : 2
				}],
				buttons : [{
					text : "ذخیره",
					iconCls : "save",
					handler : function(){
						MeetingInfoObject.SaveMeetingInfo(false);
					}
				}]
			})
		},{
			title : "شرکت کنندگان",
			itemId : "persons_tab",
			disabled : true,
			loader : {
				url : this.address_prefix + "MeetingPersons.php",
				method: "POST",
				text: "در حال بار گذاری...",
				scripts : true
			},
			listeners : {
				activate : function(){
					if(this.loader.isLoaded)
						return;
					this.loader.load({
						params : {
							MeetingID : MeetingInfoObject.MeetingID,
							MenuID : MeetingInfoObject.MenuID,
							ExtTabID : this.getEl().id
						}
					});
				}
			}
		},{
			title : "دعوتنامه ها",
			itemId : "agendas_tab",
			disabled : true,
			loader : {
				url : this.address_prefix + "agendas.php",
				method: "POST",
				text: "در حال بار گذاری...",
				scripts : true
			},
			listeners : {
				activate : function(){
					if(this.loader.isLoaded)
						return;
					this.loader.load({
						params : {
							MeetingID : MeetingInfoObject.MeetingID,
							MenuID : MeetingInfoObject.MenuID,
							ExtTabID : this.getEl().id
						}
					});
				}
			}
		},{
			title : "مصوبه ها",
			itemId : "records_tab",
			disabled : StatusID == "<?= MTG_STATUSID_DONE ?>" ? false : true,
			loader : {
				url : this.address_prefix + "MeetingRecords.php",
				method: "POST",
				text: "در حال بار گذاری...",
				scripts : true
			},
			listeners : {
				activate : function(){
					if(this.loader.isLoaded)
						return;
					this.loader.load({
						params : {
							MeetingID : MeetingInfoObject.MeetingID,
							MenuID : MeetingInfoObject.MenuID,
							ExtTabID : this.getEl().id
						}
					});
				}
			}
		},{
			title : "پیوست ها",
			autoScroll : true,
			itemId : "attach_tab",
			disabled : true,
			loader : {
				url : "../../office/dms/documents.php",
				method: "POST",
				text: "در حال بار گذاری...",
				scripts : true
			},
			listeners : {
				activate : function(){
					if(this.loader.isLoaded)
						return;
					this.loader.load({
						params : {
							ExtTabID : this.getEl().id,
							ObjectType : 'meeting',
							ObjectID : MeetingInfoObject.MeetingID
						}
					});
				}
			}
		}]		
	});
}

MeetingInfoObject = new MeetingInfo();

MeetingInfo.prototype.SaveMeetingInfo = function(SendFile){

	mask = new Ext.LoadMask(this.TabPanel, {msg:'در حال ذخيره سازي...'});
	mask.show();  
	
	this.MeetingPanel.getForm().submit({
		clientValidation: true,
		url: this.address_prefix + 'meeting.data.php?task=SaveMeeting' , 
		isUpload : true,
		method: "POST",
		params : {
			MeetingID : this.MeetingID
		},
		
		success : function(form,action){
			mask.hide();
			me = MeetingInfoObject;
			me.MeetingID = action.result.data;
			
			/*me.TabPanel.down("[itemId=pagesView]").getStore().proxy.extraParams = {
				MeetingID : me.MeetingID
			};*/ 
			me.TabPanel.down("[itemId=persons_tab]").enable();
			me.TabPanel.down("[itemId=agendas_tab]").enable();
			me.TabPanel.down("[itemId=records_tab]").enable();
			me.TabPanel.down("[itemId=attach_tab]").enable();			
		},
		failure : function(form,action){
			mask.hide();
			Ext.MessageBox.alert("Error", action.result.data);
		}
	});
}

MeetingInfo.prototype.ChangeMeetingStatus = function(StatusID){

	mask = new Ext.LoadMask(this.TabPanel, {msg:'در حال ذخيره سازي...'});
	mask.show();  
	
	Ext.Ajax.request({
		url: this.address_prefix + 'meeting.data.php?task=ChangeMeetingStatus' , 
		method: "POST",
		params : {
			MeetingID : this.MeetingID,
			StatusID : StatusID
		},
		
		success : function(){
			mask.hide();
			me = MeetingInfoObject;
			me.TabPanel.down("[itemId=records_tab]").enable();
		},
		failure : function(){
			mask.hide();
			Ext.MessageBox.alert("Error", action.result.data);
		}
	});
}
</script>
<center>
	<br>
	<div id="mainForm"></div>
	<div id="div_grid"></div>
</center>
