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

$dg = new sadaf_datagrid("dg", $js_prefix_address . "form.data.php?task=selectFormItems", "div_dg");

$dg->addColumn("", "FormID", "", true);

$col = $dg->addColumn("شماره ", "ElementID");
$col->width = 50;

$col = $dg->addColumn("عنوان", "ElementTitle");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("نوع", "ElementType");
$col->editor = "this.ElementTypeCombo";

$col = $dg->addColumn("مقادیر لیست", "ComboValues");
$col->editor = ColumnEditor::TextField(true);
$col->width = 100;

$col = $dg->addColumn("حذف", "ElementID", "string");
$col->sortable = false;
$col->renderer = "function(v,p,r){return FRG_NewForm.deleteRender(v,p,r);}";
$col->width = 50;

$dg->addButton("", " ایجاد", "add", "function(){FRG_NewFormObj.AddFormItem();}");

$dg->DefaultSortField = "ElementID";
$dg->DefaultSortDir = "desc";
$dg->autoExpandColumn = "ElementTitle";
$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){ return FRG_NewFormObj.SaveItem(v,p,r);}";

$dg->width = 590;
$dg->height = 460;
$dg->pageSize = 20;

$grid = $dg->makeGrid_returnObjects();
?>

<br>
<center>
    <div id="FRG_NewFormDIV"></div>    
    <div id='FormEditorDIV'>
    </div>
</center>
<script type='text/javascript'>
FRG_NewForm.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	TplItemSeperator: '<?= FRG_forms::TplItemSeperator ?>',
	address_prefix: "<?= $js_prefix_address ?>",
	
	FormID : <?= $FormID ?>,
	
	get: function (elementID) {
		return findChild(this.TabID, elementID);
	}
};

function FRG_NewForm() {
	
	this.BuildForms();
	this.LoadForm();
}

FRG_NewForm.prototype.LoadForm = function(){
		
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال بارگذاری...'});
	mask.show();
	
	this.store = new Ext.data.Store({
		proxy : {
			type: 'jsonp',
			url: this.address_prefix + "form.data.php?task=SelectForms"+
				"&EditContent=true&FormID=" + this.FormID,
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["FormID","FormTitle", "content"],
		autoLoad : true,
		listeners : {
			load : function(){
				me = FRG_NewFormObj;
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

FRG_NewForm.prototype.BuildForms = function(){
	
	this.formPanel = new Ext.form.Panel({
		renderTo: this.get('FRG_NewFormDIV'),
		width: 960,
		height : 550,
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
						url: this.address_prefix + 'form.data.php?task=selectFromElements'+
							'&all=true&ParentID=0&FormID=' + this.FormID,
						reader: {root: 'rows', totalProperty: 'totalCount'}
					},
					fields: ['ElementID', "FormID", 'ElementTitle', 'ElementType'],
					pageSize : 10
				}),
				displayField: 'ElementTitle',
				pageSize : 10,
				valueField: "ElementID",
				itemId : "ElementID",
				width: 400,
				tpl : new Ext.XTemplate(
					'<tpl for=".">',
						'<tpl if="FormID == 0">',
							'<div class="x-boundlist-item" style="background-color:#fcfcb6">{ElementTitle}</div>',
						'<tpl else>',
							'<div class="x-boundlist-item">{ElementTitle}</div>',
						'</tpl>',						
					'</tpl>'
				),
				listeners: {
					select: function (combo, records) {
						this.collapse();
						CKEDITOR.instances.FormEditor.insertText(' ' + 
							FRG_NewFormObj.TplItemSeperator + 
							records[0].data.ElementID + '--' + 
							records[0].data.ElementTitle + FRG_NewFormObj.TplItemSeperator + ' ');

					}
				}
			},{
				xtype : "button",
				border : true,
				text : "مدیریت آیتم ها",
				iconCls : "list",
				handler : function(){FRG_NewFormObj.ManageItems();}
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
				iconCls: "save",
				text: " ذخیره",
				handler: function () { FRG_NewFormObj.SaveForm();}
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

FRG_NewFormObj = new FRG_NewForm();

FRG_NewForm.prototype.SaveForm = function(){

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg: 'در حال ذخیره سازی ...'});
	mask.show();
	this.formPanel.getForm().submit({
		url: FRG_NewFormObj.address_prefix + "form.data.php",
		method: "POST",
		params: {
			task: 'SaveForm',
			FormContent: CKEDITOR.instances.FormEditor.getData()
		},
		success: function (form, action) {
			mask.hide();
			Ext.MessageBox.alert('', 'با موفقیت ذخیره شد');
		},
		failure : function(form,action){
			Ext.MessageBox.alert('خطا', action.result.data);
		}
	});
}

FRG_NewForm.prototype.ManageItems = function(){

	if(!this.itemWin)
	{
		this.itemWin = new Ext.window.Window({
			width : 800,
			title : "آیتم های فرم",
			height : 600,
			modal : true,
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "FormElems.php",
				params : {
					FormID : this.FormID
				},
				scripts : true
			},
			buttons :[{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.itemWin);
	}
	
	this.itemWin.show();
	this.itemWin.center();
	this.itemWin.loader.load({
		params : {
			ExtTabID : this.itemWin.getEl().id
		}
	});

	
}

</script>