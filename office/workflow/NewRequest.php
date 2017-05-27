<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once '../header.inc.php';
require_once 'form.class.php';

if (!empty($_REQUEST['RequestID'])) 
	$RequestID = $_REQUEST['RequestID'];
else
	$RequestID = 0;
?>
<script type="text/javascript">

WFM_NewRequest.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix: "<?= $js_prefix_address ?>",
	TplItemSeperator: "<?= WFM_forms::TplItemSeperator ?>",
	
	RequestID : <?= $RequestID ?>,
	
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
		height : 350,
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
			displayField: 'FormTitle',
			valueField: "FormID",
			name : "FormID",
			
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
					masktpl = new Ext.LoadMask(WFM_NewRequestObj.MainForm, {msg:'در حال ذخيره سازي...'});
					masktpl.show();
					WFM_NewRequestObj.FormItemsStore.load({
						params : {FormID : records[0].data.FormID},
						callback : function(){
							WFM_NewRequestObj.ShowTplItemsForm(records[0].data.FormID, false);
							masktpl.hide();
						}
					});
					
				}
			}
		},{
			xtype: "fieldset",
			title : "آیتم های فرم",
			itemId: "FormItems",
			width : 680,
			height : 280,
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
			colspan : 2,
			xtype: "hidden",
			itemId: "RequestID",
			name : "RequestID"
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
		fields: ['ReqItemID', 'RequestID','FormID', 'FormItemID', 'ItemValue']
	});
	
	if(this.RequestID > 0)
		this.LoadRequest();
}

WFM_NewRequest.prototype.LoadRequest = function(){

	mask1 = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال بارگذاری...'});
	mask1.show();
	
	this.ReqItemsStore.load({
		params : {
			RequestID : this.RequestID
		},
		callback : function(){
			
			me = WFM_NewRequestObj;
			if(this.totalCount == 0)
			{
				mask1.hide();
				return;
			}				
			record = this.getAt(0);
		
			me.FormItemsStore.load({
				params : {FormID : record.data.FormID},
				callback : function(){
					me.ShowTplItemsForm(record.data.FormID, true);		
					me.MainForm.loadRecord(record);
					mask1.hide();
				}
			});
		}
	});
}

WFM_NewRequestObj = new WFM_NewRequest();

WFM_NewRequest.prototype.SaveRequest = function (print) {

	if(!this.MainForm.getForm().isValid())
		return;
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	this.MainForm.getForm().submit({
		
		url: this.address_prefix + 'form.data.php?task=SaveRequest',
		method: 'POST',
		
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

WFM_NewRequest.prototype.ShowTplItemsForm = function (FormID, LoadValues) {

	this.MainForm.getComponent("FormItems").removeAll();

	for(i=0; i<this.FormItemsStore.getCount(); i++)
	{
		record = this.FormItemsStore.getAt(i);
		if(record.data.ItemType == "" || record.data.FormID == "0")
			continue;

		if(record.data.ItemType == "combo")
		{
			arr = record.data.ComboValues.split("#");
			data = [];
			for(j=0;j<arr.length;j++)
				data.push([ arr[j] ]);

			this.MainForm.getComponent("FormItems").add({
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
		else
		{
			this.MainForm.getComponent("FormItems").add({
				xtype: record.data.ItemType,
				itemId: 'ReqItem_' + record.data.FormItemID,
				name: 'ReqItem_' + record.data.FormItemID,
				fieldLabel : record.data.ItemName,
				hideTrigger : record.data.ItemType == 'numberfield' || record.data.ItemType == 'currencyfield' ? true : false
			});
		}

		if (LoadValues > 0) {
			var num = this.ReqItemsStore.find('FormItemID', record.data.FormItemID);                                    
			if (this.ReqItemsStore.getAt(num)){
				switch(record.data.ItemType){
					case "shdatefield" :
						this.MainForm.getComponent("FormItems").
							getComponent('ReqItem_' + record.data.FormItemID).setValue(
								MiladiToShamsi(this.ReqItemsStore.getAt(num).data.ItemValue));
						break;
					default : 
						this.MainForm.getComponent("FormItems").
							getComponent('ReqItem_' + record.data.FormItemID).setValue(
								this.ReqItemsStore.getAt(num).data.ItemValue);                                    
				}
			}
		}            
	}
			
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