<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once '../header.inc.php';
require_once '../BuildForms/form.class.php';
require_once './FillForm.class.php';

if(empty($_REQUEST['FillFormID'])) 
	die();

$FillFormID = $_REQUEST['FillFormID'];
$obj = new FRG_FillForms($FillFormID);
$FormObj = new FRG_forms($obj->FormID);
$CorrectContent = FRG_forms::CorrectFormContentItems($FormObj->FormContent, "div");
?>
<script type="text/javascript">

FRG_FillForm.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix: "<?= $js_prefix_address ?>",
	
	TplItemSeperator: "<?= FRG_forms::TplItemSeperator ?>",
	FillFormID : <?= $FillFormID ?>,
	FormID : <?= $obj->FormID ?>,
	
	Components : {
		displayfield :	"Ext.form.DisplayField",
		textfield :		"Ext.form.TextField",
		shdatefield :	"Ext.form.SHDateField",
		datefield :		"Ext.form.DateField",
		currencyfield :	"Ext.form.CurrencyField",
		radio :			"Ext.form.Radio",
		checkbox :		"Ext.form.CheckBox",
		numberfield :	"Ext.form.NumberField",
		textarea :		"Ext.form.TextArea",	
		combo :			"Ext.form.ComboBox"
	},
	
	get: function (elementID) {
		return findChild(this.TabID, elementID);
	}
}

function FRG_FillForm() {
	
	this.MainForm = new Ext.form.Panel({
		renderTo : this.get("FormDiv"),
		layout: {
			type: 'table',                
			columns : 2
		},
		defaults: {
			labelWidth: 100,
			width : 320
		},
		buttons :[{
			text : "ذخیره فرم",
			iconCls : "save",
			handler : function(){ FRG_FillFormObj.SaveFillForm();}
		},{
			text : "چاپ فرم",
			iconCls : "print",
			handler : function(){ FRG_FillFormObj.PrintFillForm();}
		}],
		width : 800,
		frame : true
	});
	
	this.ElementsStore = new Ext.data.Store({
		fields: ['ElementID',"ParentID", 'ElementTitle', 'ElementType', "properties", "EditorProperties",
			"ElementValues", "alias"],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + "../BuildForms/form.data.php?task=selectFromElements"+
					"&all=true",
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		},
		pageSize: 500
	});
	
	this.FillFormElemsStore = new Ext.data.Store({
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + 'FillForm.data.php?task=GetFillFormElements',
			reader: {root: 'rows', totalProperty: 'totalCount'}
		},
		fields: ['ElementID',"ElementType",'ElementValue']
	});
	
	this.LoadRequest();
}

FRG_FillForm.prototype.LoadRequest = function(){

	mask1 = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال بارگذاری...'});
	mask1.show();
	
	this.ElementsStore.load({
		params : {
			FormID : this.FormID
		},
		callback : function(){
			
			me = FRG_FillFormObj;
			if(this.totalCount == 0)
			{
				mask1.hide();
				return;
			}				
			record = this.getAt(0);
		
			me.FillFormElemsStore.load({
				params : {
					FillFormID : FRG_FillFormObj.FillFormID
				},
				callback : function(){
					me.BuildForm();		
					me.MainForm.loadRecord(record);
					mask1.hide();
				}
			});
		}
	});
}

FRG_FillFormObj = new FRG_FillForm();

FRG_FillForm.prototype.BuildForm = function () {

	for(i=0; i<this.ElementsStore.getCount(); i++)
	{
		record = this.ElementsStore.getAt(i);
		colspan = 1;
		if(record.data.ElementType == "textarea" || record.data.ElementType == "grid")
		{
			colspan = 2;
			if(i != 0 && i % 2 != 0)
				this.MainForm.add({xtype : "container"});
		}
		switch(record.data.ElementType)
		{
			case "grid" :
				
				var fields = new Array();
				var columns = [ {dataIndex : "RowID",hidden : true},
								{dataIndex : "FillFormID",hidden : true},
								{dataIndex : "ElementID",hidden : true}];
				while(true)
				{
					i++;
					var sub_record = this.ElementsStore.getAt(i);
					if(sub_record == null || sub_record.data.ParentID != record.data.ElementID)
					{
						i--;
						break;
					}
					var editor = {xtype : sub_record.data.ElementType};
					if(sub_record.data.ElementType == "datefield")
						editor.format = "Y/m/d";
					if(sub_record.data.ElementType == "combo")
					{
						arr = sub_record.data.ElementValues.split("#");
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
					eval("editor = mergeObjects(editor,{" + sub_record.data.EditorProperties + "});");
					
					NewColumn = {
						menuDisabled : true,
						sortable : false,	
						text : sub_record.data.ElementTitle,
						dataIndex : "ElementID_" + sub_record.data.ElementID,
						editor : editor						
					};
					if(sub_record.data.ElementType == "currencyfield")
					{
						NewColumn.type = "numbercolumn";
						NewColumn.renderer = Ext.util.Format.Money;
						NewColumn.summaryType = "sum";
						NewColumn.summaryRenderer = Ext.util.Format.Money;
						
					}	
					if(sub_record.data.ElementType == "currencyfield" || 
						sub_record.data.ElementType == "numberfield")
						NewColumn.editor.hideTrigger = "true";
					
					
					eval("NewColumn = mergeObjects(NewColumn,{" + sub_record.data.properties + "});");
					columns.push(NewColumn);
					if(sub_record.data.ElementType == "currencyfield")
						fields.push({name : "ElementID_" + sub_record.data.ElementID, type : "int"});
					else
						fields.push("ElementID_" + sub_record.data.ElementID);
				}
				NewElement = {
					xtype : "grid",
					colspan : colspan,
					features: [{ftype: 'summary'}],
					viewConfig: {
						stripeRows: true,
						enableTextSelection: true
					},					
					selType : 'rowmodel',
					scroll: 'vertical', 
					itemId : "ElementID_" + record.data.ElementID,
					store : new Ext.data.Store({
						proxy:{
							type: 'jsonp',
							url: this.address_prefix + "plan.data.php?task=SelectPlanItems",
							reader: {root: 'rows',totalProperty: 'totalCount'},
							extraParams : {
								FillFormID : this.FillFormID,
								ElementID : record.data.ElementID
							}							
						},
						fields : ["RowID", "FillFormID", "ElementID"].concat(fields),
						autoLoad : true,
						listeners : {
							update : function(store,record){
								mask = new Ext.LoadMask(parentEl, {msg:'در حال ذخيره سازي...'});
								mask.show();    
								Ext.Ajax.request({
									url:  FRG_FillFormObj.address_prefix + 'FillForm.data.php?task=SavePlanItems',
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
								return true;
							}
						}
					}),
					columns: columns
				};
				
				
					NewElement.plugins = [new Ext.grid.plugin.RowEditing()];
					NewElement.tbar = [{
						text : "ایجاد ردیف",
						iconCls : "add",
						handler : function(){
							var grid = this.up('grid');
							var modelClass = grid.getStore().model;
							var record = new modelClass({
								RowID : null,
								FillFormID : FRG_FillFormObj.FillFormID,
								ElementID : grid.getStore().proxy.extraParams.ElementID
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
								var mask = new Ext.LoadMask(parentEl, {msg:'در حال ذخيره سازي...'});
								mask.show();    
								Ext.Ajax.request({
									url:  FRG_FillFormObj.address_prefix + 'plan.data.php?task=DeletePlanItem',
									params:{
										RowID : record.data.RowID
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
				
				
				break;
			//..................................................................
			case "radio" :
				record.data.ElementValue = record.data.ElementValue== "" ? -1 : record.data.ElementValue;
				var items = new Array();
				ElementValues = record.data.ElementValues.split('#');
				for(j=0;j<ElementValues.length;j++)
					items.push({
						boxLabel : ElementValues[j],
						name : "ElementID_" + record.data.ElementID,
						inputValue : j,
						readOnly : this.readOnly,
						checked : record.data.ElementValue == j ? true : false
					});
				NewElement = {
					xtype : "radiogroup",
					fieldLabel : record.data.ElementTitle,
					itemId : "ElementID_" + record.data.ElementID,
					items : items,					
					columns: ElementValues.length
				};
				break;
			case "combo":
				arr = record.data.ElementValues.split("#");
				data = [];
				for(j=0;j<arr.length;j++)
					data.push([ arr[j] ]);
				NewElement = {
					xtype : record.data.ElementType,
					store : new Ext.data.SimpleStore({
						fields : ['value'],
						data : data
					}),
					valueField : "value",
					displayField : "value",
					fieldLabel : record.data.ElementTitle,
					itemId : "ElementID_" + record.data.ElementID,
					name : "ElementID_" + record.data.ElementID
				};
				break;
			case "displayfield":
				NewElement = {
					xtype : record.data.ElementType,
					fieldLabel : record.data.ElementTitle,
					itemId : "ElementID_" + record.data.ElementID,
					fieldCls : "desc"
				};
				break;		
			case "currencyfield":
			case "numberfield":
				NewElement = {
					xtype : record.data.ElementType,
					readOnly : this.readOnly,
					fieldLabel : record.data.ElementTitle,
					itemId : "ElementID_" + record.data.ElementID,
					name : "ElementID_" + record.data.ElementID,
					hideTrigger : true
				};
				break;		
			default : 
				NewElement = {
					xtype : record.data.ElementType,
					readOnly : this.readOnly,
					fieldLabel : record.data.ElementTitle,
					itemId : "ElementID_" + record.data.ElementID,
					name : "ElementID_" + record.data.ElementID
				};
		}
		this.MainForm.add(NewElement);
	}
	
	for(i=0; i<this.FillFormElemsStore.getCount(); i++)
	{
		record = this.FillFormElemsStore.getAt(i);
		switch(record.data.ElementType){
			case "shdatefield" :
				value =	MiladiToShamsi(record.data.ElementValue);
				break;
			default : 
				value = record.data.ElementValue;
		}
		Ext.getCmp("ElementID_" + record.data.ElementID).setValue(value);
	}			
}

FRG_FillForm.prototype.SaveFillForm = function (print) {

	if(!this.MainForm.getForm().isValid())
		return;
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	this.MainForm.getForm().submit({
		
		url: this.address_prefix + 'FillForm.data.php?task=SaveFillForm',
		method: 'POST',
		params : {
			FillFormID : this.FillFormID
		},
		
		success: function (form,action) {
			mask.hide();
			Ext.MessageBox.alert('', 'فرم با موفقیت ذخیره شد');
		},
		failure : function(form,action){
			mask.hide();
			Ext.MessageBox.alert('', 'خطا در اجرای عملیات');
		}
	});
}

FRG_FillForm.prototype.PrintFillForm = function(){
	window.open(this.address_prefix + "PrintFillForm.php?FillFormID=" + this.FillFormID);
}

</script>
<br>
<center>
    <div id="FormDiv">
	</div>
</center>
<br>