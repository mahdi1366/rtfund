<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once '../header.inc.php';
require_once '../global/CNTconfig.class.php';
require_once 'templates.data.php';
require_once inc_dataGrid;

if (!empty($_REQUEST['TemplateID']))
    $TemplateID = $_REQUEST['TemplateID'];
else
    $TemplateID = GetEmptyTemplateID();

$dg = new sadaf_datagrid("dg", $js_prefix_address . "templates.data.php?task=selectTemplateItems", "div_dg");

$col = $dg->addColumn("شماره ", "TemplateItemID");
$col->width = 50;

$col = $dg->addColumn("عنوان", "ItemName");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("نوع", "ItemType");
$col->editor = "this.ItemTypeCombo";

$col = $dg->addColumn("حذف", "TemplateItemID", "string");
$col->sortable = false;
$col->renderer = "function(v,p,r){return NewTemplate.deleteRender(v,p,r);}";
$col->width = 50;

$dg->addButton("", " ایجاد", "add", "function(){NewTemplateObj.AddTemplateItem();}");

$dg->DefaultSortField = "TemplateItemID";
$dg->DefaultSortDir = "desc";
$dg->autoExpandColumn = "ItemName";
$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){ return NewTemplateObj.SaveItem(v,p,r);}";

$dg->width = 590;
$dg->height = 460;
$dg->pageSize = 20;

$grid = $dg->makeGrid_returnObjects();
?>

<br>
<center>
    <div id="NewTemplateDIV"></div>    
    <div id='NewTemplateEditorDIV'>
    </div>
</center>
<script type='text/javascript'>
NewTemplate.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	TplItemSeperator: '<?= CNTconfig::TplItemSeperator ?>',
	address_prefix: "<?= $js_prefix_address ?>",
	
	TemplateID : <?= $TemplateID ?>,
	
	get: function (elementID) {
		return findChild(this.TabID, elementID);
	}
};

function NewTemplate() {

	this.ItemTypeCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields: ["id", "name"],
			data: [
				{"id": "numberfield", "name": "عدد"},
				{"id": "currencyfield", "name": "مبلغ"},
				{"id": "textfield", "name": "متن کوتاه"},
				{"id": "textarea", "name": "متن بلند"},
				{"id": "shdatefield", "name": "تاریخ"}
			]
		}),
		emptyText: 'انتخاب ...',
		name: "name",
		valueField: "id",
		displayField: "name",
		allowBlank : false
	});

	this.grid = <?= $grid ?>;

	this.BuildForms();
	this.mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال بارگذاری...'});
	this.mask.show();
	this.LoadTemplate();
}

NewTemplate.prototype.LoadTemplate = function(){
		
	this.store = new Ext.data.Store({
		proxy : {
			type: 'jsonp',
			url: this.address_prefix + "templates.data.php?task=SelectTemplates&TemplateID=" + this.TemplateID,
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["TemplateID","TemplateTitle"],
		autoLoad : true,
		listeners : {
			load : function(){
				me = NewTemplateObj;
				//..........................................................
				record = this.getAt(0);
				me.formPanel.loadRecord(record);
				
				CKEDITOR.on('instanceReady', function (ev) {
					Ext.Ajax.request({
						url: NewTemplateObj.address_prefix + "templates.data.php",
						method: "POST",
						params: {
							task: 'GetTemplateContentToEdit',                 
							TemplateID: NewTemplateObj.TemplateID
						},
						success: function (response) {
							ev.editor.setData(response.responseText);
							NewTemplateObj.mask.hide();
						}
					});

					ev.editor.focus();
				});
			}
		}
	});
}

NewTemplate.prototype.BuildForms = function(){
	
	this.formPanel = new Ext.form.Panel({
		renderTo: this.get('NewTemplateDIV'),
		width: 960,
		height : 550,
		title : "اطلاعات الگو",
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
				fieldLabel: 'عنوان الگو',
				width: 450,
				name : "TemplateTitle",
				itemId: 'TemplateTitle'
			}, {
				xtype: 'combo',
				fieldLabel: 'اضافه آیتم',
				store: new Ext.data.Store({
					pageSize: 10,
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + 'templates.data.php?task=selectTemplateItems',
						reader: {root: 'rows', totalProperty: 'totalCount'}
					},
					fields: ['TemplateItemID', 'ItemName', 'ItemType']
				}),
				displayField: 'ItemName',
				valueField: "TemplateItemID",
				width: 400,
				listeners: {
					select: function (combo, records) {
						this.collapse();
						CKEDITOR.instances.NewTemplateEditor.insertText(' ' + 
							NewTemplateObj.TplItemSeperator + 
							records[0].data.TemplateItemID + '--' + 
							records[0].data.ItemName + NewTemplateObj.TplItemSeperator + ' ');

					}
				}
			},{
				xtype : "button",
				border : true,
				text : "مدیریت آیتم ها",
				iconCls : "list",
				handler : function(){NewTemplateObj.ManageItems();}
			},{
				xtype : "container",
				colspan : 3,
				html : "<div id=NewTemplateEditor></div>"
			},{
				xtype: 'hidden',
				name : "TemplateID",
				itemId: 'TemplateID'
			}],
		buttons: [{
				iconCls: "save",
				text: " ذخیره",
				handler: function () { NewTemplateObj.SaveTemplate();}
			}]
	});

	if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 )
		CKEDITOR.tools.enableHtml5Elements( document );

	CKEDITOR.config.width = 'auto';
	CKEDITOR.config.height = 350;
	CKEDITOR.config.autoGrow_minHeight = 350;
	CKEDITOR.replace('NewTemplateEditor');
}

NewTemplateObj = new NewTemplate();

NewTemplate.prototype.SaveTemplate = function(){

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg: 'در حال ذخیره سازی ...'});
	mask.show();
	Ext.Ajax.request({
		url: NewTemplateObj.address_prefix + "templates.data.php",
		method: "POST",
		params: {
			task: 'SaveTemplate',
			TemplateContent: CKEDITOR.instances.NewTemplateEditor.getData(), 
			TemplateTitle: this.formPanel.getComponent('TemplateTitle').getValue(),
			TemplateID: this.formPanel.getComponent('TemplateID').getValue()
		},
		success: function (response) {
			mask.hide();
			var sd = Ext.decode(response.responseText);
			if (sd.success) 
			{
				NewTemplateObj.formPanel.getComponent('TemplateID').setValue(sd.data);
				Ext.MessageBox.alert('', 'با موفقیت ذخیره شد');
			} else {
				Ext.MessageBox.alert('خطا', sd.data);
			}
		}
	});
}

NewTemplate.prototype.ManageItems = function(){

	this.grid.getStore().proxy.extraParams = {
		TemplateID : this.TemplateID
	};
	if(!this.itemWin)
	{
		this.itemWin = new Ext.window.Window({
			width : 600,
			title : "آیتم های الگو",
			height : 520,
			modal : true,
			closeAction : "hide",
			items : [this.grid],
			buttons :[{
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

NewTemplate.prototype.AddTemplateItem = function () {

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		TemplateItemID: 0
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
	//this.ItemTypeCombo.focus();
	this.grid.columns[1].getEditor().focus();
}

NewTemplate.prototype.SaveItem = function (store, record) {

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg: 'در حال ذخیره سازی ...'});
	mask.show();
	Ext.Ajax.request({
		url: this.address_prefix + 'templates.data.php?task=saveTemplateItem',
		method: 'POST',
		params: {
			record: Ext.encode(record.data)
		},
		success: function (response) {
			mask.hide();
			var st = Ext.decode(response.responseText);
			if (st.success)
			{
				NewTemplateObj.grid.getStore().load();
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

NewTemplate.deleteRender = function(){
	return  "<div title='حذف اطلاعات' class='remove' onclick='NewTemplateObj.removeItem();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;height:16'></div>";
};

NewTemplate.prototype.removeItem = function(){  

	Ext.MessageBox.confirm("","آیا مایل به حذف آیتم می باشید؟", function(btn){
		if(btn == "no")
			return;

		me = NewTemplateObj;
		mask = new Ext.LoadMask(me.grid, {msg: 'در حال ذخیره سازی ...'});
		mask.show();
		Ext.Ajax.request({
			url: me.address_prefix + 'templates.data.php?task=deleteTemplateItem',
			method: 'POST',
			params: {
				TemplateItemID : me.grid.getSelectionModel().getLastSelected().data.TemplateItemID
			},

			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);
				if(st.success)
				{
					NewTemplateObj.grid.getStore().load();
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

</script>