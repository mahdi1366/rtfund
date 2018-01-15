<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once '../header.inc.php';
require_once 'form.data.php';
require_once 'form.class.php';
require_once inc_dataGrid;

if (!empty($_REQUEST['FormID']))
    $FormID = $_REQUEST['FormID'];
else
    $FormID = GetEmptyFormID();

$dg = new sadaf_datagrid("dg", $js_prefix_address . "form.data.php?task=selectFormItems&NotGlobal=true", "div_dg");

$dg->addColumn("", "FormID", "", true);
$dg->addColumn("", "ordering", "");

$col = $dg->addColumn("شماره ", "FormItemID");
$col->width = 50;

$col = $dg->addColumn("عنوان", "ItemName");
$col->editor = ColumnEditor::TextField();
$col->width = 200;

$col = $dg->addColumn("نوع", "ItemType");
$col->editor = "this.ItemTypeCombo";

$col = $dg->addColumn("مقادیر لیست", "ComboValues");
$col->editor = ColumnEditor::TextField(true);


$col = $dg->addColumn("","","");
$col->renderer = "WFM_NewForm.upRender";
$col->sortable = false;
$col->width = 30;

$col = $dg->addColumn("","","");
$col->renderer = "WFM_NewForm.downRender";
$col->sortable = false;
$col->width = 30;

$col = $dg->addColumn("حذف", "FormItemID", "string");
$col->sortable = false;
$col->renderer = "function(v,p,r){return WFM_NewForm.deleteRender(v,p,r);}";
$col->width = 50;

$dg->addButton("", " ایجاد", "add", "function(){WFM_NewFormObj.AddFormItem();}");

$dg->DefaultSortField = "FormItemID";
$dg->DefaultSortDir = "desc";
$dg->autoExpandColumn = "ComboValues";
$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){ return WFM_NewFormObj.SaveItem(v,p,r);}";

$dg->width = 790;
$dg->height = 460;
$dg->pageSize = 20;

$grid = $dg->makeGrid_returnObjects();
?>

<br>
<center>
    <div id="WFM_NewFormDIV"></div>    
    <div id='FormEditorDIV'>
    </div>
</center>
<script type='text/javascript'>
WFM_NewForm.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	TplItemSeperator: '<?= WFM_forms::TplItemSeperator ?>',
	address_prefix: "<?= $js_prefix_address ?>",
	
	FormID : <?= $FormID ?>,
	
	get: function (elementID) {
		return findChild(this.TabID, elementID);
	}
};

function WFM_NewForm() {

	this.ItemTypeCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields: ["id", "name"],
			data: [
				{"id": "numberfield", "name": "عدد"},
				{"id": "currencyfield", "name": "مبلغ"},
				{"id": "textfield", "name": "متن کوتاه"},
				{"id": "textarea", "name": "متن بلند"},
				{"id": "shdatefield", "name": "تاریخ"},
				{"id": "combo", "name": "لیستی"},
				{"id": "checkbox", "name": "انتخابی"}
			]
		}),
		emptyText: 'انتخاب ...',
		name: "name",
		valueField: "id",
		displayField: "name",
		allowBlank : false
	});

	this.grid = <?= $grid ?>;
	this.grid.plugins[0].on("beforeedit", function(editor,e){
		if(e.record.data.FormID == "0")
			return false;
	});
	this.grid.addDocked({
		xtype : "toolbar", 
		dock : "bottom", 
		items :[{
			xtype : "container",
			html : "توجه : لطفا مقادیر مختلف لیست را با # جدا کنید"
		}]
	});
	
	this.BuildForms();
	this.LoadForm();
}

WFM_NewForm.upRender = function(v,p,r){
	store = WFM_NewFormObj.grid.getStore();
	record = store.getAt(0);
	if(r.data.ordering == record.data.ordering)
		return "";
	return "<div align='center' title='حذف ' class='up' onclick='WFM_NewFormObj.moveStep(-1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WFM_NewForm.downRender = function(v,p,r){
	store = WFM_NewFormObj.grid.getStore();
	record = store.getAt(store.getCount()-1);
	if(r.data.ordering == record.data.ordering)
		return "";
	return "<div align='center' title='حذف ' class='down' onclick='WFM_NewFormObj.moveStep(1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WFM_NewForm.prototype.moveStep = function(direction){
	var record = this.grid.getSelectionModel().getLastSelected();
	
    mask = new Ext.LoadMask(this.itemWin, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'MoveItem',
			FormID : this.FormID,
			FormItemID : record.data.FormItemID,
			direction : direction
		},
		url: this.address_prefix + 'form.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				WFM_NewFormObj.grid.getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("Error",st.data);
			}
		},
		failure: function(){}
	});
}

WFM_NewForm.prototype.LoadForm = function(){
		
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال بارگذاری...'});
	mask.show();
	
	this.store = new Ext.data.Store({
		proxy : {
			type: 'jsonp',
			url: this.address_prefix + "form.data.php?task=SelectForms"+
				"&EditContent=true&FormID=" + this.FormID,
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["FormID","FormTitle", "content", "FlowID", 
			"IsStaff", "IsCustomer", "IsShareholder", "IsSupporter", "IsExpert", "IsAgent"],
		autoLoad : true,
		listeners : {
			load : function(){
				me = WFM_NewFormObj;
				//..........................................................
				record = this.getAt(0);
				me.formPanel.loadRecord(record);
				
				CKEDITOR.instances.FormEditor.on('instanceReady', function( ev ) {
					ev.editor.setData(record.data.content);
					mask.hide();										
				});			
				CKEDITOR.instances.FormEditor.setData(record.data.content);
				mask.hide();
			}
		}
	});
}

WFM_NewForm.prototype.BuildForms = function(){
	
	this.formPanel = new Ext.form.Panel({
		renderTo: this.get('WFM_NewFormDIV'),
		width: 960,
		height : 610,
		title : "اطلاعات فرم",
		frame: true,
		fieldDefaults: {
			labelWidth: 80
		},
		layout: {
			type: 'table',
			columns: 3
		},
		items: [{
				xtype: 'textfield',
				fieldLabel: 'عنوان فرم',
				width: 450,
				allowBlank : false,
				name : "FormTitle",
				itemId: 'FormTitle'
			}, {
				xtype: 'combo',
				fieldLabel: 'اضافه آیتم',
				store: new Ext.data.Store({
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + 'form.data.php?task=selectFormItems'+
							'&FormID=' + this.FormID,
						reader: {root: 'rows', totalProperty: 'totalCount'}
					},
					fields: ['FormItemID', "FormID", 'ItemName', 'ItemType'],
					pageSize : 9
				}),
				displayField: 'ItemName',
				pageSize : 10,
				valueField: "FormItemID",
				itemId : "FormItemID",
				width: 400,
				tpl : new Ext.XTemplate(
					'<tpl for=".">',
						'<tpl if="FormID == 0">',
							'<div class="x-boundlist-item" style="background-color:#fcfcb6">{ItemName}</div>',
						'<tpl else>',
							'<div class="x-boundlist-item">{ItemName}</div>',
						'</tpl>',						
					'</tpl>'
				),
				listeners: {
					select: function (combo, records) {
						this.collapse();
						CKEDITOR.instances.FormEditor.insertText(' ' + 
							WFM_NewFormObj.TplItemSeperator + 
							records[0].data.FormItemID + '--' + 
							records[0].data.ItemName + WFM_NewFormObj.TplItemSeperator + ' ');

					}
				}
			},{
				xtype : "button",
				border : true,
				text : "مدیریت آیتم ها",
				iconCls : "list",
				handler : function(){WFM_NewFormObj.ManageItems();}
			},{
				xtype : "fieldset",
				title : " ذینفع",
				layout : "hbox",
				defaults : {style : "margin-right : 10px"},
				items :[{
					xtype : "checkbox",
					boxLabel: 'همکاران صندوق',
					name: 'IsStaff',
					inputValue: 'YES'
				},{
					xtype : "checkbox",
					boxLabel: 'مشتری',
					name: 'IsCustomer',
					inputValue: 'YES'
				},{
					xtype : "checkbox",
					boxLabel: 'سهامدار',
					name: 'IsShareholder',
					inputValue: 'YES'
				},{
					xtype : "checkbox",
					boxLabel: 'سرمایه گذار',
					name: 'IsAgent',
					inputValue: 'YES'
				},{
					xtype : "checkbox",
					boxLabel: 'حامی',
					name: 'IsSupporter',
					inputValue: 'YES'
				},{
					xtype : "checkbox",
					boxLabel: 'کارشناس',
					name: 'IsExpert',
					inputValue: 'YES'
				}]
			},{
				xtype: 'combo',
				colspan : 2,
				allowBlank : false,
				fieldLabel: 'فرایند گردش',
				store: new Ext.data.Store({
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + 'wfm.data.php?task=SelectAllFlows'+
							'&ObjectType=<?= WFM_FORM_FLOWID ?>',
						reader: {root: 'rows', totalProperty: 'totalCount'}
					},
					fields: ['FlowID', "FlowDesc"],
					pageSize : 100,
					autoLoad : true
				}),
				queryMode : 'local',
				displayField: 'FlowDesc',
				pageSize : 100,
				valueField: "FlowID",
				itemId : "FlowID",
				name : "FlowID",
				width: 400
			},{
				xtype : "container",
				colspan : 3,
				html : "<div id=FormEditor></div>"
			},{
				xtype: 'hidden',
				name : "FormID",
				itemId: 'FormID'
			}],
		buttons: [{
				iconCls: "print",
				text: " پیش نمایش",
				handler: function () { window.open(WFM_NewFormObj.address_prefix + "PrintForm.php?RequestID=0&FormID=" + WFM_NewFormObj.FormID);}
			},'->',{
				iconCls: "save",
				text: " ذخیره",
				handler: function () { WFM_NewFormObj.SaveForm();}
			}]
	});

	if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 )
		CKEDITOR.tools.enableHtml5Elements( document );

	CKEDITOR.config.width = 'auto';
	CKEDITOR.config.height = 350;
	CKEDITOR.config.autoGrow_minHeight = 350;
	CKEDITOR.replace('FormEditor');
	CKEDITOR.add;
}

WFM_NewFormObj = new WFM_NewForm();

WFM_NewForm.prototype.SaveForm = function(){

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg: 'در حال ذخیره سازی ...'});
	mask.show();
	this.formPanel.getForm().submit({
		url: WFM_NewFormObj.address_prefix + "form.data.php",
		method: "POST",
		params: {
			task: 'SaveForm',
			FormContent: CKEDITOR.instances.FormEditor.getData()
		},
		success: function (form, action) {
			mask.hide();
			WFM_NewFormObj.formPanel.getComponent('FormID').setValue(action.result.data);
			Ext.MessageBox.alert('', 'با موفقیت ذخیره شد');
		},
		failure : function(form,action){
			mask.hide();
			Ext.MessageBox.alert('خطا', action.result.data);
		}
	});
}

WFM_NewForm.prototype.ManageItems = function(){

	this.grid.getStore().proxy.extraParams = {
		FormID : this.FormID
	};
	if(!this.itemWin)
	{
		this.itemWin = new Ext.window.Window({
			width : 800,
			title : "آیتم های الگو",
			height : 520,
			modal : true,
			closeAction : "hide",
			items : [this.grid],
			buttons :[{
				text : "پیش نمایش",
				iconCls : "view",
				handler : function(){WFM_NewFormObj.PreviewForm();}
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.itemWin);
	}
	else
		this.grid.getStore().load();

	this.itemWin.show();
	this.itemWin.center();
}

WFM_NewForm.prototype.AddFormItem = function () {

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		FormItemID: 0,
		FormID : this.FormID
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
	//this.ItemTypeCombo.focus();
	this.grid.columns[1].getEditor().focus();
}

WFM_NewForm.prototype.SaveItem = function (store, record) {

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg: 'در حال ذخیره سازی ...'});
	mask.show();
	Ext.Ajax.request({
		url: this.address_prefix + 'form.data.php?task=saveFormItem',
		method: 'POST',
		params: {
			record: Ext.encode(record.data)
		},
		success: function (response) {
			mask.hide();
			var st = Ext.decode(response.responseText);
			if (st.success)
			{
				WFM_NewFormObj.grid.getStore().load();
				WFM_NewFormObj.formPanel.down("[itemId=FormItemID]").getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("خطا", st.data);
			}
		},
		failure: function () {
			mask.hide();
		}
	});
}

WFM_NewForm.deleteRender = function(v,p,r){
	if(r.data.FormID == "0")
			return "";
	return  "<div title='حذف اطلاعات' class='remove' onclick='WFM_NewFormObj.removeItem();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;height:16'></div>";
};

WFM_NewForm.prototype.removeItem = function(){  

	Ext.MessageBox.confirm("","آیا مایل به حذف آیتم می باشید؟", function(btn){
		if(btn == "no")
			return;

		me = WFM_NewFormObj;
		mask = new Ext.LoadMask(me.grid, {msg: 'در حال ذخیره سازی ...'});
		mask.show();
		Ext.Ajax.request({
			url: me.address_prefix + 'form.data.php?task=deleteFormItem',
			method: 'POST',
			params: {
				FormItemID : me.grid.getSelectionModel().getLastSelected().data.FormItemID
			},

			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);
				if(st.success)
				{
					WFM_NewFormObj.grid.getStore().load();
				}
				else
				{
					if(st.data == "USED")
						Ext.MessageBox.alert("خطا","آیتم مورد نظر استفاده شده است و امکان حذف آن وجود ندارد");
					else
						Ext.MessageBox.alert("خطا", st.data);
				}
			},
			failure: function(){}
		});
	})

};

WFM_NewForm.prototype.PreviewForm = function(){
	
	if(!this.requestWin)
	{
		this.requestWin = new Ext.window.Window({
			width : 740,
			height : 660, 
			modal : true,
			bodyStyle : "background-color:white;padding: 0 10px 0 10px",
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "NewRequest.php",
				scripts : true
			},
			buttons :[{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.requestWin);
	}

	this.requestWin.show();
	this.requestWin.center();
	this.requestWin.loader.load({
		params : {
			ExtTabID : this.requestWin.getEl().id,
			FormID : this.FormID
		}
	});
}
</script>