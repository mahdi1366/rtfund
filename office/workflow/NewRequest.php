<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once '../header.inc.php';
require_once 'form.class.php';

if (!empty($_REQUEST['RequestID'])) 
{
	$RequestID = $_REQUEST['RequestID'];
	$ReqObj = new WFM_requests($RequestID);
	$FormID = $ReqObj->FormID;
}
else
{
	$RequestID = "";
	$FormID = "";
}
?>
<script type="text/javascript">

WFM_NewRequest.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix: "<?= $js_prefix_address ?>",
	TplItemSeperator: "<?= WFM_forms::TplItemSeperator ?>",
	
	RequestID : "<?= $RequestID ?>",
	FormID : "<?= $FormID ?>",
	ItemsMmask : null,
	
	get: function (elementID) {
		return findChild(this.TabID, elementID);
	}
}

function WFM_NewRequest() {
	
	this.MainForm = new Ext.form.Panel({
		plain: true,            
		frame: false,
		border : false,
		width: 700,
		height : 550,
		fieldDefaults: {
			labelWidth: 100
		},
		renderTo: this.get("SelectTplComboDIV"),
		layout: {
			type: 'table',                
			columns : 2
		},
		items: [{
			xtype: 'combo',
			colspan : 2,
			fieldLabel: 'انتخاب فرم',
			itemId: 'FormID',
			store: new Ext.data.Store({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + 'form.data.php?task=SelectValidForms',
					reader: {root: 'rows', totalProperty: 'totalCount'}
				},
				fields: ['FormID', 'FormTitle', 'FormContent'],
				autoLoad : true
			}),
			queryMode : "local",
			displayField: 'FormTitle',
			valueField: "FormID",
			name : "FormID",	
			value : this.FormID,
			queryMode : "local",
			allowBlank : false,
			listConfig: {
				loadingText: 'در حال جستجو...',
				emptyText: 'فاقد اطلاعات',
				itemCls: "search-item"
			},
			width: 350,
			listeners: {
				select: function (combo, records) {
					
					this.collapse();
					fieldset = WFM_NewRequestObj.MainForm.down("[itemId=FormItems]");
					WFM_NewRequestObj.ItemsMmask = new Ext.LoadMask(fieldset, {msg:'در حال بارگذاری...'});
					WFM_NewRequestObj.ItemsMmask.show();
					WFM_NewRequestObj.FormItemsStore.load({
						params : {FormID : records[0].data.FormID},
						callback : function(){
							WFM_NewRequestObj.ShowTplItemsForm();
						}
					});
					
				}
			}
		},{
			xtype: "fieldset",
			title : "آیتم های فرم",
			itemId: "FormItems",
			width : 680,
			height : 480,
			autoScroll: true,
			colspan : 2,
			layout: {
				type: 'table',                
				columns : 2
			},
			defaults: {
				labelWidth: 100,
				width : 320
			}
		},{
			xtype: "hidden",
			itemId: "RequestID",
			name : "RequestID",
			value : this.RequestID
		}],
		buttons: [{
			text: "  ذخیره",
			handler: function () {
				WFM_NewRequestObj.SaveRequest(false);
			},
			iconCls: "save"
		}, {
			text: "  مشاهده",
			handler: function () {
				WFM_NewRequestObj.SaveRequest(true);
			},
			iconCls: "print"
		}]
	});
	
	this.FormItemsStore = new Ext.data.Store({
		fields: ['FormItemID',"FormID", 'ItemName', 'ItemType', "ComboValues"],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + "form.data.php?task=selectFormItems",
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		},
		pageSize: 500
	});
	
	this.ReqItemsStore = new Ext.data.Store({
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + 'form.data.php?task=GetRequestItems',
			reader: {root: 'rows', totalProperty: 'totalCount'}
		},
		fields: ['ReqItemID', 'RequestID','FormID', 'FormItemID', 'ItemValue', 'ItemType', "ComboValues"]
	});
	
	if(this.RequestID > 0)
		this.LoadRequest();
}

WFM_NewRequest.prototype.LoadRequest = function(){
	
	this.MainForm.down("[itemId=FormID]").disable();
	
	fieldset = this.MainForm.down("[itemId=FormItems]");
	this.ItemsMmask = new Ext.LoadMask(fieldset, {msg:'در حال بارگذاری...'});
	this.ItemsMmask.show();
	
	this.ReqItemsStore.load({
		params : {RequestID : this.RequestID},
		callback : function(){
			
			me = WFM_NewRequestObj;
			if(this.totalCount == 0)
			{
				me.FormItemsStore.load({
					params : {FormID : me.FormID},
					callback : function(){
						WFM_NewRequestObj.ShowTplItemsForm();
						WFM_NewRequestObj.ItemsMmask.hide();
					}
				});				
				return;
			}				
			me.FormItemsStore.load({
				params : {FormID : this.getAt(0).data.FormID},
				callback : function(){
					me = WFM_NewRequestObj;
					me.ShowTplItemsForm();	
					for(i=0; i<me.ReqItemsStore.getCount(); i++){
						record = me.ReqItemsStore.getAt(i); 
						switch(record.data.ItemType){
							case "shdatefield" :
								me.MainForm.getComponent("FormItems").
									down('[name=ReqItem_' + record.data.FormItemID + "]").setValue(MiladiToShamsi(record.data.ItemValue));
								break;
							case "checkbox" :
								if(record.data.ComboValues == null)
									me.MainForm.getComponent("FormItems").
										down('[name=ReqItem_' + record.data.FormItemID + "]").setValue(record.data.ItemValue);
								else
									me.MainForm.getComponent("FormItems").
										down('[name=ReqItem_' + record.data.FormItemID + "_checkbox_" + record.data.ItemValue + "]").setValue(true);
								break; 
							default :
								me.MainForm.getComponent("FormItems").
									down('[name=ReqItem_' + record.data.FormItemID + "]").setValue(record.data.ItemValue);
						}
					};
					
					WFM_NewRequestObj.ItemsMmask.hide();
				}
			});
		}
	});
}

var WFM_NewRequestObj = new WFM_NewRequest();

WFM_NewRequest.prototype.SaveRequest = function (print) {

	if(!this.MainForm.getForm().isValid())
		return;
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	this.MainForm.getForm().submit({
		
		url: this.address_prefix + 'form.data.php?task=SaveRequest',
		method: 'POST',
		params : {
			FormID : this.MainForm.down("[itemId=FormID]").getValue()
		},
		
		success: function (form,action) {
			mask.hide();
			
			WFM_NewRequestObj.MainForm.getComponent('RequestID').setValue(action.result.data);
			if (print) 
			{
				var RequestID = WFM_NewRequestObj.MainForm.getComponent('RequestID').getValue();
				window.open(WFM_NewRequestObj.address_prefix + 'PrintForm.php?RequestID=' + RequestID);
			}
			
			WFM_MyRequestsObject.requestWin.hide();
			WFM_MyRequestsObject.grid.getStore().load();
		},
		failure : function(form,action){
			mask.hide();
			Ext.MessageBox.alert('', 'خطا در اجرای عملیات');
		}
	});
}

WFM_NewRequest.prototype.ShowTplItemsForm = function () {

	this.MainForm.getComponent("FormItems").removeAll();

	for(i=0; i<this.FormItemsStore.getCount(); i++)
	{
		record = this.FormItemsStore.getAt(i);
		if(record.data.ItemType == "" || record.data.FormID == "0")
			continue;
		
		parent = this.MainForm.getComponent("FormItems");

		if(record.data.ItemType == "combo")
		{
			arr = record.data.ComboValues.split("#");
			data = [];
			for(j=0;j<arr.length;j++)
				data.push([ arr[j] ]);

			parent.add({
				store : new Ext.data.SimpleStore({
					fields : ['value'],
					data : data
				}),
				xtype: record.data.ItemType,
				valueField : "value",
				displayField : "value",
				itemId: 'ReqItem_' + record.data.FormItemID,
				name: 'ReqItem_' + record.data.FormItemID,
				fieldLabel : record.data.ItemName
			});
		}
		if(record.data.ItemType == "checkbox")
		{
			if(record.data.ComboValues == null)
			{
				parent.add({
					boxLabel : record.data.ItemName,
					xtype : "checkbox",
					name : "ReqItem_" + record.data.FormItemID,
					itemId : "ReqItem_" + record.data.FormItemID
				});
			}
			else
			{
				if(i%2 > 0 && i != 0)
				{
					parent.add({
						xtype : "container"
					});
				}
				var items = new Array();
				arr = record.data.ComboValues.split("#");
				for(j=0; j<arr.length; j++)
					items.push({
						boxLabel : arr[j],
						name : "ReqItem_" + record.data.FormItemID + "_checkbox_" + j,
						itemId : "ReqItem_" + record.data.FormItemID + "_checkbox_" + j
					});
				parent.add({
					fieldLabel : record.data.ItemName,
					xtype : "checkboxgroup",
					items : items,
					width : 610,
					columns : 1,				
					colspan : 2
				});
			}
		}			
		else
		{
			parent.add({
				xtype: record.data.ItemType,
				itemId: 'ReqItem_' + record.data.FormItemID,
				name: 'ReqItem_' + record.data.FormItemID,
				fieldLabel : record.data.ItemName,
				hideTrigger : record.data.ItemType == 'numberfield' || record.data.ItemType == 'currencyfield' ? true : false
			});
		}
	}
	
	WFM_NewRequestObj.ItemsMmask.hide();
}

WFM_NewRequest.prototype.getShdatefield = function (fieldname, ren) {
	return new Ext.form.SHDateField(
			{
				name: fieldname,
				width: 150,
				format: 'Y/m/d',
				renderTo: WFM_NewRequestObj.get(ren)
			}
	);
};

WFM_NewRequest.prototype.ContractDocuments = function(ObjectType){

	if(!this.documentWin)
	{
		this.documentWin = new Ext.window.Window({
			width : 720,
			height : 440,
			modal : true,
			bodyStyle : "background-color:white;padding: 0 10px 0 10px",
			closeAction : "hide",
			loader : {
				url : "/office/dms/documents.php",
				scripts : true
			},
			buttons :[{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.documentWin);
	}

	this.documentWin.show();
	this.documentWin.center();

	this.documentWin.loader.load({
		scripts : true,
		params : {
			ExtTabID : this.documentWin.getEl().id,
			ObjectType : ObjectType,
			ObjectID : this.RequestID
		}
	});
}

</script>
<br>
<center>
    <div id="SelectTplComboDIV"></div>
</center>
<br>