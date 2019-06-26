<?php
//-----------------------------
//	Date		: 97.11
//-----------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;
require_once 'meeting.class.php';

$MeetingID = !empty($_POST["MeetingID"]) ? $_POST["MeetingID"] : "";

$MPObj = MTG_MeetingPersons::GetMeetingPersonObj($_SESSION["USER"]["PersonID"], $MeetingID);

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
			"details","secretary","StatusID","InPortal"],
		
		listeners : {
			load : function(){
				me = MeetingInfoObject;
				me.BuildForms();
				//..........................................................
				record = this.getAt(0);
				me.MeetingPanel.loadRecord(record);
				me.MeetingPanel.down("[name=StartTime]").setValue(record.data.StartTime.substr(0,5));
				me.MeetingPanel.down("[name=EndTime]").setValue(record.data.EndTime.substr(0,5));			
				me.MeetingPanel.down("[name=MeetingDate]").setValue(MiladiToShamsi(record.data.MeetingDate));			
				MeetingInfoObject.mask.hide();
				//..........................................................
				me.TabPanel.down("[itemId=persons_tab]").enable();	
				me.TabPanel.down("[itemId=agendas_tab]").enable();	
				me.TabPanel.down("[itemId=records_tab]").enable();	
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
	readOnly = StatusID == "<?= MTG_STATUSID_RAW ?>" || StatusID == "-1" ? false : true;
	
	this.TabPanel = new Ext.TabPanel({
		renderTo : this.get("mainForm"),
		width : 780,
		autoHeight: true,
		plain:true,
		tbar : [{
			text : "جلسه برگزار شده است",
			iconCls : "tick",
			hidden : readOnly,
			handler : function(){ MeetingInfoObject.ChangeMeetingStatus("<?= MTG_STATUSID_DONE ?>"); }
		},{
			text : "جلسه کنسل شده است",
			iconCls : "cross",
			hidden : readOnly,
			handler : function(){ MeetingInfoObject.ChangeMeetingStatus("<?= MTG_STATUSID_CANCLE ?>"); }
		},{
			text : "تایید و امضاء مصوبات",
			iconCls : "sign",
			hidden : <?= session::IsPortal() && $MPObj->IsPresent == "YES" && $MPObj->IsSign == "NO" ? "false" : "true" ?>,
			handler : function(){ MeetingInfoObject.SignRecords(); }
		},{
			text : "جلسه برگزار شده است و اطلاعات جلسه قابل تغییر نمی باشند",
			iconCls : "tick",
			hidden : StatusID == "<?= MTG_STATUSID_DONE ?>" ? false : true
		},{
			text : "جلسه کنسل شده است و اطلاعات جلسه قابل تغییر نمی باشند",
			iconCls : "cross",
			hidden : StatusID == "<?= MTG_STATUSID_CANCLE ?>" ? false : true
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
					readOnly : readOnly,
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
					readOnly : readOnly,
					fieldLabel : "مکان برگزاری",
					name : "place",
					allowBlank : false
				},{
					xtype : "shdatefield",
					readOnly : readOnly,
					name : "MeetingDate",
					fieldLabel : "تاریخ جلسه",
					allowBlank : false
				},{
					xtype : "container",
					layout : "hbox",
					items : [{
						xtype : "timefield",
						readOnly : readOnly,
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
						readOnly : readOnly,
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
					readOnly : readOnly,
					name : "details",
					rows : 4,
					colspan : 2,
					width : 700,
					fieldLabel : "توضیحات"
				},{
					xtype : "combo",
					readOnly : readOnly,
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
				},{
					xtype : "checkbox",
					readOnly : readOnly,
					name : "InPortal",
					inputValue : "YES",
					boxLabel : "نمایش دعوتنامه در پورتال برای شرکت کنندگان در جلسه"
				}],
				buttons : [{
					text : "ذخیره",
					hidden : readOnly,
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
			title : "دستورات جلسه",
			itemId : "agendas_tab",
			disabled : true,
			loader : {
				url : this.address_prefix + "MeetingAgendas.php",
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
			disabled : true,
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

	message = "در صورت تغییر وضعیت جلسه دیگر قادر به تغییر در هیچ یک از اطلاعات جلسه نمی باشید.<br>" +
			"آیا مایل به ادامه می باشید؟";
	Ext.MessageBox.confirm("",message, function(btn){
		if(btn == "no")
			return;
		
		me = MeetingInfoObject;
		mask = new Ext.LoadMask(me.TabPanel, {msg:'در حال ذخيره سازي...'});
		mask.show();  

		Ext.Ajax.request({
			url: me.address_prefix + 'meeting.data.php?task=ChangeMeetingStatus' , 
			method: "POST",
			params : {
				MeetingID : me.MeetingID,
				StatusID : StatusID
			},

			success : function(response){
				mask.hide();
				result = Ext.decode(response.responseText);
				if(result.success)
					framework.ReloadTab(MeetingInfoObject.TabID);
				else
					Ext.MessageBox.alert("Error", result.data);
			},
			failure : function(){
				mask.hide();
				Ext.MessageBox.alert("Error", action.result.data);
			}
		});
	});
}

MeetingInfo.prototype.SignRecords = function(StatusID){

	message = "بعد از تایید و امضاء مصوبات جلسه دیگر قادر به برگشت عملیات فوق نمی باشید.<br> آیا مایل به امضاء می باشید؟";
	Ext.MessageBox.confirm("",message, function(btn){
		if(btn == "no")
			return;
		
		me = MeetingInfoObject;
		mask = new Ext.LoadMask(me.TabPanel, {msg:'در حال ذخيره سازي...'});
		mask.show();  

		Ext.Ajax.request({
			url: me.address_prefix + 'meeting.data.php?task=SignRecords' , 
			method: "POST",
			params : {
				MeetingID : me.MeetingID
			},

			success : function(response){
				mask.hide();
				result = Ext.decode(response.responseText);
				if(result.success)
					portal.ReloadTab(MeetingInfoObject.TabID);
				else
					Ext.MessageBox.alert("Error", result.data);
			},
			failure : function(){
				mask.hide();
				Ext.MessageBox.alert("Error", action.result.data);
			}
		});
	});
}

</script>
<div style="margin: 10px" id="mainForm"></div>
