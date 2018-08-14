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
	$FormID = !empty($_REQUEST['FormID']) ? $_REQUEST['FormID'] : "";
}

$StepRowID = empty($_REQUEST["StepRowID"]) ? 0 : $_REQUEST["StepRowID"];

$LoanRequestID = !empty($_REQUEST["LoanRequestID"]) ? $_REQUEST["LoanRequestID"] : "0";

?>
<script type="text/javascript">
function merge(obj1,obj2){
    var obj3 = {};
    for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
    for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
    return obj3;
}

WFM_NewRequest.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix: "<?= $js_prefix_address ?>",
	TplItemSeperator: "<?= WFM_forms::TplItemSeperator ?>",
	parentHandler : <?= !empty($_REQUEST["parentHandler"]) ? $_REQUEST["parentHandler"] : "function(){}" ?>,
	
	RequestID : "<?= $RequestID ?>",
	FormID : "<?= $FormID ?>",
	StepRowID : "<?= $StepRowID ?>",
	preview : <?= isset($_REQUEST["preview"]) ? "true" : "false" ?>,
	
	LoanRequestID : <?= $LoanRequestID?>,
	
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
		renderTo: this.get("SelectTplComboDIV"),
		items: [{
			xtype: "container",
			itemId: "FormItems",
			width : 700,
			style : "text-align:right",
			height : 500,
			autoScroll: true
		},{
			xtype: "hidden",
			itemId: "RequestID",
			name : "RequestID",
			value : this.RequestID
		}],
		buttons: [{
			text: "ذخیره موقت",
			hidden : this.preview,
			handler: function () {
				WFM_NewRequestObj.SaveRequest(false, false);
			},
			iconCls: "save"
		},{
			text: "ذخیره و ارسال فرم",
			hidden : this.preview,
			handler: function () {
				WFM_NewRequestObj.SaveRequest(false, true);
			},
			iconCls: "send"
		}, {
			text: "  مشاهده",
			itemId : "btn_view",
			handler: function () {
				WFM_NewRequestObj.SaveRequest(true, false);
			},
			iconCls: "print"
		}]
	});
	
	this.FormItemsStore = new Ext.data.Store({
		fields: ['FormItemID',"GroupID","GroupDesc","FormID", 'ItemName', 'ItemType', "ComboValues", "access"],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + "form.data.php?task=selectFormItems&NotGlobal=true&StepRowID=" + this.StepRowID,
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
	
	this.ColumnsStore = new Ext.data.Store({
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + 'form.data.php?task=selectGridColumns&FormID=' + this.FormID,
			reader: {root: 'rows', totalProperty: 'totalCount'}
		},
		fields: ['FormItemID', 'ColumnID','ItemName', 'ItemType', "ComboValues", "EditorProperties", "properties"],
		autoLoad : true
	});
	
	var t = setInterval(function(){
		if(!WFM_NewRequestObj.ColumnsStore.isLoading())
		{
			clearInterval(t);
			if(WFM_NewRequestObj.RequestID > 0)
				WFM_NewRequestObj.LoadRequest();
			else
				WFM_NewRequestObj.FormSelect(WFM_NewRequestObj.FormID);
		}
	}, 1000);
}

WFM_NewRequest.prototype.FormSelect = function(FormID){
	
	fieldset = this.MainForm.down("[itemId=FormItems]");
	this.ItemsMmask = new Ext.LoadMask(fieldset, {msg:'در حال بارگذاری...'});
	this.ItemsMmask.show();
	this.FormItemsStore.load({
		params : {FormID : FormID},
		callback : function(){
			WFM_NewRequestObj.ShowTplItemsForm();
		}
	});
}

WFM_NewRequest.prototype.LoadRequest = function(){
	
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
				params : {FormID : me.FormID},
				callback : function(){
					me = WFM_NewRequestObj;
					me.ShowTplItemsForm();	
					for(i=0; i<me.ReqItemsStore.getCount(); i++){
						record = me.ReqItemsStore.getAt(i); 
						switch(record.data.ItemType){
							case "grid":
								continue;
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

WFM_NewRequest.prototype.SaveRequest = function (print, sending) {

	if(!this.MainForm.getForm().isValid())
		return;
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	this.MainForm.getForm().submit({
		
		url: this.address_prefix + 'form.data.php?task=SaveRequest',
		method: 'POST',
		params : {
			FormID : this.FormID,
			sending : sending
		},
		
		success: function (form,action) {
			mask.hide();
			
			WFM_NewRequestObj.MainForm.getComponent('RequestID').setValue(action.result.data);
			if (print) 
			{
				var RequestID = WFM_NewRequestObj.MainForm.getComponent('RequestID').getValue();
				window.open(WFM_NewRequestObj.address_prefix + 'PrintForm.php?RequestID=' + RequestID);
			}
			if(sending)
			{
				Ext.MessageBox.alert("", "فرم شما با موفقیت ارسال گردید");
				WFM_NewRequestObj.parentHandler();
			}
			WFM_NewRequestObj.RequestID = action.result.data;
		},
		failure : function(form,action){
			mask.hide();
			Ext.MessageBox.alert('', 'خطا در اجرای عملیات');
		}
	});
}

WFM_NewRequest.prototype.MakeGrid = function (SrcRecord){

	var fields = new Array();
	var columns = [ 
		{dataIndex : "ReqItemID",hidden : true},
		{dataIndex : "ColumnID",hidden : true},
		{dataIndex : "ItemValue",hidden : true}];
	
	for(var i=0; i<this.ColumnsStore.totalCount; i++)
	{
		record = this.ColumnsStore.getAt(i);
		if(record.data.FormItemID != SrcRecord.data.FormItemID)
			continue;
		
		var editor = {xtype : record.data.ItemType};
		if(record.data.ElementType == "datefield")
			editor.format = "Y/m/d";
		if(record.data.ItemType == "combo")
		{
			arr = record.data.ComboValues.split("#");
			data = [];
			for(j=0;j<arr.length;j++)
				data.push([ arr[j] ]);
			editor.store = new Ext.data.SimpleStore({
				fields : ['value'],
				data : data
			});
			editor.displayField = "value";
			editor.valueField = "value";
		}

		if(record.data.EditorProperties != null)
			eval("editor = merge(editor,{" + record.data.EditorProperties + "});");

		NewColumn = {
			menuDisabled : true,
			sortable : false,	
			text : record.data.ItemName,
			dataIndex : "column_" + record.data.ColumnID,
			editor : editor						
		};
		if(record.data.ItemType == "currencyfield")
		{
			NewColumn.type = "numbercolumn";
			NewColumn.renderer = Ext.util.Format.Money;
			NewColumn.summaryType = "sum";
			NewColumn.summaryRenderer = Ext.util.Format.Money;
		}	
		if(record.data.ItemType == "currencyfield" || 
			record.data.ItemType == "numberfield")
			NewColumn.editor.hideTrigger = "true";

		if(record.data.properties != null)
			eval("NewColumn = merge(NewColumn,{" + record.data.properties + "});");

		columns.push(NewColumn);
		if(record.data.ItemType == "currencyfield")
			fields.push({name : "column_" + record.data.ColumnID, type : "int"});
		else
			fields.push("column_" + record.data.ColumnID);
	}
	
	NewElement = {
		xtype : "grid",
		title : SrcRecord.data.ItemName,
		features: [{ftype: 'summary'}],
		viewConfig: {
			stripeRows: true,
			enableTextSelection: true
		},					
		selType : 'rowmodel',
		bbar: new Ext.ExtraBar({store: Ext.getCmp(Ext.id),displayInfo: true}),
		scroll: 'vertical', 
		store : new Ext.data.Store({
			proxy:{
				type: 'jsonp',
				url: this.address_prefix + "form.data.php?task=SelectGridRows",
				reader: {root: 'rows',totalProperty: 'totalCount'},
				extraParams : {
					RequestID : this.RequestID,
					FormItemID : SrcRecord.data.FormItemID					
				}							
			},
			fields : ["ReqItemID", "RequestID", "FormItemID"].concat(fields),
			autoLoad : true,
			listeners : {
				update : function(store,record){
					
					if(WFM_NewRequestObj.RequestID*1 == 0)
						WFM_NewRequestObj.SaveRequest(false, false);
					
					var t = setInterval(function(){
						if(WFM_NewRequestObj.RequestID*1 > 0)
						{
							clearInterval(t);
							record.data.RequestID = WFM_NewRequestObj.RequestID;
							store.proxy.extraParams.RequestID = WFM_NewRequestObj.RequestID;
							
							mask = new Ext.LoadMask(WFM_NewRequestObj.MainForm, {msg:'در حال ذخيره سازي...'});
							mask.show();    
							Ext.Ajax.request({
								url:  WFM_NewRequestObj.address_prefix + 'form.data.php?task=SaveGridRow',
								params:{
									record : Ext.encode(record.data)
								},
								method: 'POST',
								success: function(response,option){
									mask.hide();
									store.load();
								},
								failure: function(){}
							});
						}
					}, 1000);
					
					return true;
				}
			}
		}),
		columns: columns,
		listeners : {
			beforerender : function(){
				var ExtraToolbar = this.getDockedItems('extrabar');
				if(ExtraToolbar.length > 0)
					ExtraToolbar[0].bind(this.getStore());
				}
		}
	};

	if(SrcRecord.data.access == "YES")
	{
		NewElement.plugins = [new Ext.grid.plugin.RowEditing()];
		NewElement.tbar = [{
			text : "ایجاد ردیف",
			iconCls : "add",
			handler : function(){
				var grid = this.up('grid');
				var modelClass = grid.getStore().model;
				var record = new modelClass({
					ReqItemID : null,
					RequestID : WFM_NewRequestObj.RequestID,
					FormItemID : grid.getStore().proxy.extraParams.FormItemID
				});
				grid.plugins[0].cancelEdit();
				grid.getStore().insert(0, record);
				grid.plugins[0].startEdit(0, 0);
			}
		},'-',{
			text : "ویرایش ردیف",
			iconCls : "edit",
			handler : function(){
				var grid = this.up('grid');
				var record = grid.getSelectionModel().getLastSelected();
				if(record == null)
				{
					Ext.MessageBox.alert("","ابتدا ردیف مورد نظر را انتخاب کنید");
					return;
				}
				grid.plugins[0].startEdit(grid.getStore().indexOf(record),0);
			}
		},'-',{
			text : "حذف ردیف",
			iconCls : "remove",
			handler : function(){
				var grid = this.up('grid');
				var record = grid.getSelectionModel().getLastSelected();
				if(record == null)
				{
					Ext.MessageBox.alert("","ابتدا ردیف مورد نظر را انتخاب کنید");
					return;
				}

				Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
					if(btn == "no")
						return;
					var mask = new Ext.LoadMask(WFM_NewRequestObj.MainForm, {msg:'در حال ذخيره سازي...'});
					mask.show();    
					Ext.Ajax.request({
						url:  WFM_NewRequestObj.address_prefix + 'form.data.php?task=DeleteGridRow',
						params:{
							ReqItemID : record.data.ReqItemID
						},
						method: 'POST',
						success: function(response,option){
							mask.hide();
							grid.getStore().load();
						},
						failure: function(){}
					});
				});
			}
		}];
	}
	
	return NewElement;
}

WFM_NewRequest.prototype.ShowTplItemsForm = function () {

	this.MainForm.getComponent("FormItems").removeAll();
	var CurGroupID = 0;
	var parent = null;
	for(i=0; i<this.FormItemsStore.getCount(); i++)
	{
		record = this.FormItemsStore.getAt(i);
		if(record.data.ItemType == "" || record.data.FormID == "0")
			continue;
		
		if(CurGroupID != record.data.GroupID)
		{
			this.MainForm.getComponent("FormItems").add({
				xtype : "fieldset",
				title : record.data.GroupDesc,
				itemId : "Group_" + record.data.GroupID,
				width : 680,
				defaults : {labelWidth:200}
			});
			parent = this.MainForm.down("[itemId=Group_" + record.data.GroupID + "]");
			CurGroupID = record.data.GroupID;
		}
		//...........................................
		if(record.data.ItemType === "loan")
		{
			this.LoanCmp = new Ext.form.ComboBox({
				store: new Ext.data.Store({
					proxy:{
						type: 'jsonp',
						url: '/loan/request/request.data.php?task=SelectMyRequests&mode=customer',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields :  ['PartAmount',"RequestID","ReqAmount","ReqDate", "RequestID", "CurrentRemain","IsEnded",{
						name : "fullTitle",
						convert : function(value,record){
							return "کد وام : " + record.data.RequestID + " به مبلغ " + 
								Ext.util.Format.Money(record.data.ReqAmount) + " مورخ " + 
								MiladiToShamsi(record.data.ReqDate);
						}
					}]
				}),
				displayField: 'fullTitle',
				valueField : "RequestID",
				tpl: new Ext.XTemplate(
					'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct" style="height: 23px;">',
					'<td style="padding:7px">کد وام</td>',
					'<td style="padding:7px">مبلغ وام</td>',
					'<td style="padding:7px">تاریخ پرداخت</td> </tr>',
					'<tpl for=".">',
						'<tr class="x-boundlist-item" style="border-left:0;border-right:0">',
						'<td style="border-left:0;border-right:0" class="search-item">{RequestID}</td>',
						'<td style="border-left:0;border-right:0" class="search-item">',
							'{[Ext.util.Format.Money(values.ReqAmount)]}</td>',
						'<td style="border-left:0;border-right:0" class="search-item">{[MiladiToShamsi(values.ReqDate)]}</td> </tr>',
					'</tpl>',
					'</table>'
				),
				disabled : record.data.access == "NO" ? true : false,
				width : 610,
				itemId: 'ReqItem_' + record.data.FormItemID,
				name: 'ReqItem_' + record.data.FormItemID,
				fieldLabel : record.data.ItemName
			});
			parent.add(this.LoanCmp);
			if(this.LoanRequestID*1 > 0)
			{
				this.LoanCmp.getStore().load({
					params : {
						RequestID : this.LoanRequestID
					},
					callback : function(){
						WFM_NewRequestObj.LoanCmp.setValue(this.getAt(0).data.RequestID)
					}
				});
			}
		}
		else if(record.data.ItemType == "combo")
		{
			arr = record.data.ComboValues.split("#");
			data = [];
			for(j=0;j<arr.length;j++)
				if(arr[j] != "")
					data.push([ arr[j] ]);
			
			titleInLine = false;
			if(record.data.ItemName.length > 40)
			{
				parent.add({
					xtype : "displayfield",
					value : record.data.ItemName,
					anchor : "100%"
				});
				titleInLine = true;
			}
			parent.add({
				store : new Ext.data.SimpleStore({
					fields : ['value'],
					data : data
				}),
				xtype: record.data.ItemType,
				valueField : "value",
				disabled : record.data.access == "NO" ? true : false,
				displayField : "value",
				width : 610,
				itemId: 'ReqItem_' + record.data.FormItemID,
				name: 'ReqItem_' + record.data.FormItemID,
				fieldLabel : titleInLine ? "" : record.data.ItemName
			});
		}
		//...........................................
		else if(record.data.ItemType == "checkbox")
		{
			if(record.data.ComboValues == null)
			{
				parent.add({
					boxLabel : record.data.ItemName,
					xtype : "checkbox",
					name : "ReqItem_" + record.data.FormItemID,
					itemId : "ReqItem_" + record.data.FormItemID,
					disabled : record.data.access == "NO" ? true : false
				});
			}
			else
			{
				parent.add({
					xtype : "displayfield",
					value : record.data.ItemName,
					anchor : "100%"
				});
				var items = new Array();
				arr = record.data.ComboValues.split("#");
				for(j=0; j<arr.length; j++)
					if(arr[j] != "")
						items.push({
							boxLabel : arr[j],
							name : "ReqItem_" + record.data.FormItemID + "_checkbox_" + j,
							itemId : "ReqItem_" + record.data.FormItemID + "_checkbox_" + j
						});
				parent.add({
					xtype : "checkboxgroup",
					items : items,
					width : 610,
					columns : 1,				
					disabled : record.data.access == "NO" ? true : false
				});
			}
		}			
		//...........................................
		else if(record.data.ItemType == "grid")
		{
			parent.add(this.MakeGrid(record));
		}
		else
		{
			titleInLine = false;
			if(record.data.ItemType === "textarea" || record.data.ItemName.length > 40)
			{
				parent.add({
					xtype : "displayfield",
					value : record.data.ItemName,
					anchor : "100%"
				});
				titleInLine = true;
			}
			item = {
				xtype: record.data.ItemType,
				itemId: 'ReqItem_' + record.data.FormItemID,
				name: 'ReqItem_' + record.data.FormItemID,
				fieldLabel : titleInLine ? "" : record.data.ItemName,
				disabled : record.data.access == "NO" ? true : false,
			};
			if(record.data.ItemType == 'numberfield' || record.data.ItemType == 'currencyfield')
				item.hideTrigger =  true;
			if(record.data.ItemType == 'textarea' || record.data.ItemType == 'textfield')
			{
				item.rows = 2;
				item.width = 650;
			}
			if(new Array('numberfield','currencyfield','shdatefield').indexOf(record.data.ItemType) >= 0)
				item.width = 400;
			
			parent.add(item);
		}
	}
	
	this.ItemsMmask.hide();
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