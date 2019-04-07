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

//------------------------------------------------------------------------------

$dg = new sadaf_datagrid("dg",$js_prefix_address . "form.data.php?task=SelectGroups&FormID=" . $FormID,"");

$dg->addColumn("","GroupID","string", true);
$dg->addColumn("","FormID","string", true);

$col = $dg->addColumn("ترتیب","ordering","string");
$col->width = 50;

$col = $dg->addColumn("عنوان گروه","GroupDesc","string");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("","","");
$col->renderer = "WFM_NewForm.GroupupRender";
$col->sortable = false;
$col->width = 30;

$col = $dg->addColumn("","","");
$col->renderer = "WFM_NewForm.GroupdownRender";
$col->sortable = false;
$col->width = 30;

$col = $dg->addColumn("حذف","","");
$col->renderer = "WFM_NewForm.deleteGroupRender";
$col->sortable = false;
$col->width = 40;
	
$dg->addButton = true;
$dg->addHandler = "function(){WFM_NewFormObj.AddGroup();}";

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){return WFM_NewFormObj.saveGroup(v,p,r);}";

$dg->height = 140;
$dg->width = 500;
$dg->DefaultSortField = "ordering";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "GroupDesc";
$dg->editorGrid = true;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->emptyTextOfHiddenColumns = true;
$GroupGrid = $dg->makeGrid_returnObjects();

//------------------------------------------------------------------------------

$dg = new sadaf_datagrid("dg", $js_prefix_address . "form.data.php?task=selectFormItems&NotGlobal=true", "div_dg");

$dg->addColumn("", "FormID", "", true);
$dg->addColumn("", "ordering", "", true);
$dg->addColumn("","GroupDesc","string", true);
$dg->addColumn("","DisplayDesc","string", true);

$col = $dg->addColumn("گروه","GroupID","string");
$col->renderer = "function(v,p,r){return r.data.GroupDesc;}";
$col->editor = "this.FormGroupCombo";

$col = $dg->addColumn("شماره ", "FormItemID", "", true);
$col->width = 50;

$col = $dg->addColumn("عنوان", "ItemName");
$col->editor = ColumnEditor::TextField();
$col->width = 200;

$col = $dg->addColumn("نوع", "ItemType");
$col->editor = "this.ItemTypeCombo";
$col->renderer = "WFM_NewForm.ItemTypeRender";

$col = $dg->addColumn("مقدار", "FieldName");
$col->editor = "this.FieldNameCombo";
$col->renderer = "function(v,p,r){return r.data.DisplayDesc}";
$col->width = 160;

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

$col = $dg->addColumn("","","");
$col->renderer = "WFM_NewForm.gridListRender";
$col->sortable = false;
$col->width = 30;

$col = $dg->addColumn("حذف", "FormItemID", "string");
$col->sortable = false;
$col->renderer = "function(v,p,r){return WFM_NewForm.deleteRender(v,p,r);}";
$col->width = 50;

$dg->addButton("", " ایجاد", "add", "function(){WFM_NewFormObj.AddFormItem();}");

$dg->EnableGrouping = true;
$dg->DefaultGroupField = "GroupID";

$dg->DefaultSortField = "FormItemID";
$dg->DefaultSortDir = "desc";
$dg->autoExpandColumn = "ComboValues";
$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(store, record){ return WFM_NewFormObj.SaveItem(store, record);}";

$dg->width = 990;
$dg->height = 390;
$dg->pageSize = 20;

$grid = $dg->makeGrid_returnObjects();

//..............................................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "form.data.php?task=selectGridColumns", "grid_div");

$dg->addColumn("", "ColumnID", "", true);
$dg->addColumn("", "FormItemID", "", true);

$col = $dg->addColumn("ترتیب", "ordering", "");
$col->editor = ColumnEditor::NumberField();
$col->width = 60;

$col = $dg->addColumn("عنوان آیتم". 
	'<span style="float:right;width:16px;height: 16px;margin:2px;cursor:pointer" class=add '.
	'onclick=WFM_NewFormObj.AddColumn()></span>', "ItemName", "");
$col->width = 200;
$col->editor = ColumnEditor::TextField();
$col->sortable = false;

$col = $dg->addColumn("نوع آیتم", "ItemType", "");
$col->editor = "this.ColumnTypeCombo";
$col->width = 100;

$col = $dg->addColumn("مشخصات ادیتور", "EditorProperties", "");
$col->editor = ColumnEditor::TextField(true);
$col->width = 150;
$col->ellipsis = 20;

$col = $dg->addColumn("مشخصات", "properties", "");
$col->editor = ColumnEditor::TextField(true);
$col->width = 150;
$col->ellipsis = 20;

$col = $dg->addColumn("مقادیر", "ComboValues", "");
$col->editor = ColumnEditor::TextField(true);
$col->ellipsis = 50;

$col = $dg->addColumn("", "", "");
$col->renderer = "WFM_NewForm.ColumnOperationRender";
$col->width = 50;

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(store, record){ return WFM_NewFormObj.SaveColumn(store, record);}";

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 300;
$dg->width = 780;
$dg->DefaultSortField = "ordering";
$dg->autoExpandColumn = "ComboValues";
$dg->EnablePaging = false;
$dg->HeaderMenu = false;
$dg->EnableSearch = false;

$ColumnsGrid = $dg->makeGrid_returnObjects();
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
				{"id": "checkbox", "name": "انتخابی"},
				{"id": "grid", "name": "گرید"},	
				{"id": "loan", "name": "وام های فرد"},
				{"id": "branch", "name": "شعبه های صندوق"},
				{"id": "displayfield", "name": "نمایشی"}
			]
		}),
		emptyText: 'انتخاب ...',
		name: "name",
		valueField: "id",
		displayField: "name",
		allowBlank : false,
		listeners : {
			select : function(){
				if(this.getValue() == "displayfield")
					WFM_NewFormObj.FieldNameCombo.enable();
				else
					WFM_NewFormObj.FieldNameCombo.disable();
			}
		}
	});
	
	this.ColumnTypeCombo = new Ext.form.ComboBox({
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
	
	this.FieldNameCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + 'form.data.php?task=selectFormItems&FormID=0',
				reader: {root: 'rows', totalProperty: 'totalCount'}
			},
			fields: ['FormItemID', "FormID", 'ItemName', 'ItemType'],
			pageSize : 9
		}),
		typeAhead : true,
		disabled  :true,
		displayField: 'ItemName',
		valueField: "FormItemID",
		pageSize : 9
	});

	this.FormGroupCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields: ["GroupID", "GroupDesc"],
			proxy : {
				type: 'jsonp',
				url : this.address_prefix + "form.data.php?task=SelectGroups&FormID=" + this.FormID,
				reader: {root: 'rows',totalProperty: 'totalCount'}
			}
		}),
		valueField: "GroupID",
		displayField: "GroupDesc",
		allowBlank : false
	});
	
	this.GroupGrid = <?= $GroupGrid ?>;
	
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
	
	this.gridColumnsGrid = <?= $ColumnsGrid ?>;
	
	this.BuildForms();
	this.LoadForm();
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
		fields : ["FormID","FormTitle", "content", "FlowID", "SmsSend","SendOnce",
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
						url: this.address_prefix + 'form.data.php?task=selectFormItems&CreateMode=true'+
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
				rowspan : 3,
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
							'&ObjectType=<?= FLOWID_WFM_FORM ?>',
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
				xtype : "checkbox",
				colspan : 2,
				name : "SmsSend",
				inputValue : "YES",
				style : "margin-right:80px",
				boxLabel : "ارسال فرم با تاییدیه پیامک باشد"
			},{
				xtype : "checkbox",
				colspan : 2,
				name : "SendOnce",
				inputValue : "YES",
				style : "margin-right:80px",
				boxLabel : "متقاضی فقط یکبار قادر به ارسال این فرم باشد"
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
				handler: function () { window.open(WFM_NewFormObj.address_prefix + "PrintRequest.php?RequestID=0&FormID=" + WFM_NewFormObj.FormID);}
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

//.....................................................

WFM_NewForm.deleteGroupRender = function(v,p,r){
	return "<div align='center' title='حذف ' class='remove' onclick='WFM_NewFormObj.DeleteGroup();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WFM_NewForm.GroupupRender = function(v,p,r){
	
	if(r.data.ordering == "1")
		return "";
	return "<div align='center' title='up' class='up' onclick='WFM_NewFormObj.moveGroup(-1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WFM_NewForm.GroupdownRender = function(v,p,r){
	
	store = WFM_NewFormObj.GroupGrid.getStore();
	record = store.getAt(store.getCount()-1);
	if(r.data.ordering == record.data.ordering)
		return "";
	return "<div align='center' title='down' class='down' onclick='WFM_NewFormObj.moveGroup(1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WFM_NewForm.prototype.AddGroup = function(){
	
	var modelClass = this.GroupGrid.getStore().model;
	var record = new modelClass({
		FormID : this.FormID,
		GroupID : "",
		GroupDesc : ""
	});

	this.GroupGrid.plugins[0].cancelEdit();
	this.GroupGrid.getStore().insert(0, record);
	this.GroupGrid.plugins[0].startEdit(0, 0);
}

WFM_NewForm.prototype.saveGroup = function(store,record){
	
    mask = new Ext.LoadMask(this.GroupGrid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SaveGroup',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix +'form.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				WFM_NewFormObj.GroupGrid.getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("Error",st.data);
			}
		},
		failure: function(){}
	});
}

WFM_NewForm.prototype.moveGroup = function(direction){
	
	var record = this.GroupGrid.getSelectionModel().getLastSelected();
	
    mask = new Ext.LoadMask(this.GroupGrid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'MoveGroup',
			FormID : record.data.FormID,
			GroupID : record.data.GroupID,
			ordering : record.data.ordering,
			direction : direction
		},
		url: this.address_prefix + 'form.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				WFM_NewFormObj.GroupGrid.getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("Error",st.data);
			}
		},
		failure: function(){}
	});
}

WFM_NewForm.prototype.DeleteGroup = function(){
	
	var record = this.GroupGrid.getSelectionModel().getLastSelected();
	
	Ext.MessageBox.confirm("", "در صورت حذف کلیه آیتم های این گروه حذف می شوند.آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		me = WFM_NewFormObj;
		
		Ext.Ajax.request({
		  	url : me.address_prefix + "form.data.php",
		  	method : "POST",
		  	params : {
		  		task : "DeleteGroup",
		  		GroupID : record.data.GroupID
		  	},
		  	success : function(response)
		  	{
				result = Ext.decode(response.responseText);
				if(result.success)
				{
					WFM_NewFormObj.ItemsGrid.getStore().load();
					WFM_NewFormObj.GroupGrid.getStore().load();
				}
				else
				{
					if(result.data == "")
						Ext.MessageBox.alert("ERROR", "عملیات مورد نظر با شکست مواجه شد");
					else
						Ext.MessageBox.alert("ERROR", result.data);
				}
		  	}
		});
	});
}

//.....................................................

WFM_NewForm.upRender = function(v,p,r,rowIndex){
	
	store = WFM_NewFormObj.grid.getStore();
	if(rowIndex == 0 || store.getAt(rowIndex-1).data.GroupID != r.data.GroupID)
		return "";
	return "<div align='center' title='up' class='up' onclick='WFM_NewFormObj.moveStep(-1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WFM_NewForm.downRender = function(v,p,r, rowIndex){
	store = WFM_NewFormObj.grid.getStore();
	record = store.getAt(store.getCount()-1);
	if(rowIndex+1 == store.getCount() || store.getAt(rowIndex+1).data.GroupID != r.data.GroupID)
		return "";
	return "<div align='center' title='down' class='down' onclick='WFM_NewFormObj.moveStep(1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

//.....................................................

WFM_NewForm.gridListRender = function(v,p,r,rowIndex){
	
	if(r.data.ItemType != "grid")
		return "";
	return "<div align='center' title='ستون های گرید' class='list' "+
		"onclick='WFM_NewFormObj.GridColumns();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WFM_NewForm.ColumnOperationRender  = function(v,p,r,rowIndex){
	
	return  "<div align='center' title='حذف' class='remove' "+
		"onclick='WFM_NewFormObj.DeleteColumn();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;height:16'></div>";
}

WFM_NewForm.prototype.GridColumns = function(){

	if(!this.gridItemWin)
	{
		this.gridItemWin = new Ext.window.Window({
			width : 800,
			title : "ستون های گرید",
			bodyStyle : "background-color:white;text-align:-moz-center",
			height : 350,
			modal : true,
			closeAction : "hide",
			items : [this.gridColumnsGrid]
			
		});
		Ext.getCmp(this.TabID).add(this.gridItemWin);
	}
	else
		this.gridColumnsGrid.getStore().load();

	this.gridItemWin.show();
	this.gridItemWin.center();
	
	record = this.grid.getSelectionModel().getLastSelected();
	this.gridColumnsGrid.getStore().proxy.extraParams.FormItemID = record.data.FormItemID;
	this.gridColumnsGrid.getStore().load();
}

WFM_NewForm.prototype.AddColumn = function () {

	var modelClass = this.gridColumnsGrid.getStore().model;
	var record = new modelClass({
		FormItemID: this.gridColumnsGrid.getStore().proxy.extraParams.FormItemID,
		ColumnID : 0
	});
	this.gridColumnsGrid.plugins[0].cancelEdit();
	this.gridColumnsGrid.getStore().insert(0, record);
	this.gridColumnsGrid.plugins[0].startEdit(0, 0);
	this.gridColumnsGrid.columns[1].getEditor().focus();
}

WFM_NewForm.prototype.SaveColumn = function(store, record){

	mask = new Ext.LoadMask(this.gridColumnsGrid, {msg: 'در حال ذخیره سازی ...'});
	mask.show();
	Ext.Ajax.request({
		url: this.address_prefix + 'form.data.php?task=saveColumn',
		method: 'POST',
		params: {
			record: Ext.encode(record.data)
		},
		success: function (response) {
			mask.hide();
			var st = Ext.decode(response.responseText);
			if (st.success)
				WFM_NewFormObj.gridColumnsGrid.getStore().load();
			else
				Ext.MessageBox.alert("خطا", st.data);
		},
		failure: function () {
			mask.hide();
		}
	});
}

WFM_NewForm.prototype.DeleteColumn = function(){  

	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;

		me = WFM_NewFormObj;
		mask = new Ext.LoadMask(me.gridColumnsGrid, {msg: 'در حال ذخیره سازی ...'});
		mask.show();
		Ext.Ajax.request({
			url: me.address_prefix + 'form.data.php?task=deleteColumn',
			method: 'POST',
			params: {
				ColumnID : me.gridColumnsGrid.getSelectionModel().getLastSelected().data.ColumnID
			},

			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);
				if(st.success)
				{
					WFM_NewFormObj.gridColumnsGrid.getStore().load();
				}
			},
			failure: function(){}
		});
	})

};

//.....................................................

WFM_NewForm.ItemTypeRender = function(v,p,r,rowIndex){
	
	store = WFM_NewFormObj.ItemTypeCombo.getStore();
	for(var i=0; i<store.totalCount; i++)
	{
		record = store.getAt(i);
		if(record.data.id == v)
			return record.data.name;
	}
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

WFM_NewForm.prototype.ManageItems = function(){

	this.grid.getStore().proxy.extraParams = {
		FormID : this.FormID
	};
	if(!this.itemWin)
	{
		this.itemWin = new Ext.window.Window({
			width : 1000,
			title : "آیتم های الگو",
			bodyStyle : "background-color:white;text-align:-moz-center",
			height : 600,
			modal : true,
			closeAction : "hide",
			items : [this.GroupGrid,this.grid],
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
			autoScroll : true,
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
			FormID : this.FormID,
			preview : true
		}
	});
}

WFM_NewFormObj = new WFM_NewForm();

</script>