<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1395.02
//-----------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................
if(!isset($_REQUEST["FormType"]))
	die();
$FormType = $_REQUEST["FormType"];

$dg = new sadaf_datagrid("dg", $js_prefix_address . "elements.data.php?task=selectGroupElements&ParentID=0", "grid_div");

$dg->addColumn("", "GroupID", "", true);
$dg->addColumn("", "ParentID", "", true);
$dg->addColumn("", "ElementID", "", true);

$title = $accessObj->AddFlag ? '<span style="float:right;width:16px;height: 16px;margin:2px;cursor:pointer" class=add '.
	'onclick=PlanElementsObject.AddHElement()></span>' : "";

$col = $dg->addColumn("نوع آیتم" . $title, "ElementType", "");
$col->renderer = "PlanElements.ElementTypeRender";
$col->width = 100;
$col->sortable = false;

$col = $dg->addColumn("مشخصات", "properties", "");
$col->width = 300;

$col = $dg->addColumn("مقادیر", "ElementValues", "");
$col->ellipsis = 70;

$col = $dg->addColumn("", "", "");
$col->renderer = "PlanElements.HOperationRender";
$col->width = 50;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 150;
$dg->width = 780;
$dg->HeaderMenu = false;
$dg->DefaultSortField = "ElementID";
$dg->autoExpandColumn = "ElementValues";
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$grid1 = $dg->makeGrid_returnObjects();

//..............................................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "elements.data.php?task=selectGroupElements&FormType=" . $FormType, "grid_div");

$dg->addColumn("", "GroupID", "", true);
$dg->addColumn("", "ParentID", "", true);
$dg->addColumn("", "ElementID", "", true);

$col = $dg->addColumn("عنوان آیتم". 
	'<span style="float:right;width:16px;height: 16px;margin:2px;cursor:pointer" class=add '.
	'onclick=PlanElementsObject.AddDElement()></span>', "ElementTitle", "");
$col->width = 200;
$col->sortable = false;

$col = $dg->addColumn("نوع آیتم", "ElementType", "");
$col->renderer = "PlanElements.ElementTypeRender";
$col->width = 100;

$col = $dg->addColumn("مشخصات ادیتور", "EditorProperties", "");
$col->width = 150;
$col->ellipsis = 20;

$col = $dg->addColumn("مشخصات", "properties", "");
$col->width = 150;
$col->ellipsis = 20;

$col = $dg->addColumn("مقادیر", "ElementValues", "");
$col->ellipsis = 50;

$col = $dg->addColumn("", "", "");
$col->renderer = "PlanElements.DOperationRender";
$col->width = 50;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 300;
$dg->width = 780;
$dg->DefaultSortField = "ElementID";
$dg->autoExpandColumn = "ElementValues";
$dg->EnablePaging = false;
$dg->HeaderMenu = false;
$dg->EnableSearch = false;

$grid2 = $dg->makeGrid_returnObjects();

?>
<script>

PlanElements.prototype = {
	
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	FormType : "<?= $FormType ?>",

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function PlanElements(){

	this.HGrid = <?= $grid1 ?>;
	this.HGrid.on("itemdblclick", function(view, record){
		PlanElementsObject.DGrid.getStore().proxy.extraParams.GroupID = record.data.GroupID;
		PlanElementsObject.DGrid.getStore().proxy.extraParams.ParentID = record.data.ElementID;
		
		if(PlanElementsObject.DGrid.rendered)
			PlanElementsObject.DGrid.getStore().load();
		else
			PlanElementsObject.DGrid.render(PlanElementsObject.get("DIV_DGrid"));
		PlanElementsObject.DGrid.show();
	});	
	this.DGrid = <?= $grid2 ?>;
	
	this.tree = new Ext.tree.Panel({
		renderTo : this.get("div_tree"),
		store: new Ext.data.TreeStore({
			proxy: {
				type: 'ajax',
				url: this.address_prefix + 'elements.data.php?task=selectGroups&PlanID=0&FormType=' + this.FormType
			}					
		}),
		root: {id: 'src', text : "سرفصل های اطلاعات"},
		autoScroll : true,
		width : 780,
		height : 200,
		listeners : {
			itemclick : function(v,record){
				if(!record.data.leaf) return; 
				PlanElementsObject.ShowElements(record.data.id);
			},
			itemcontextmenu : function(view, record, item, index, e){
				PlanElementsObject.ShowMenu(view, record, item, index, e);
			}
		}
	});	
	
	this.infoWin = new Ext.window.Window({
		applyTo: this.get("NewWIN"),
		modal : true,
		title: "اطلاعات سطح",
		width : 500,
		closeAction : "hide",

		items : new Ext.form.Panel({
			bodyStyle : "text-align:right;padding:5px",
			frame: true,
			items :[{
					xtype : "textfield",
					name : "GroupDesc",
					itemId : "GroupDesc",
					fieldLabel : "عنوان",
					anchor : "100%"
				},{
					xtype : "combo",
					store: new Ext.data.Store({
						proxy:{
							type: 'jsonp',
							url: this.address_prefix + 'elements.data.php?task=SelectScopes',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						fields :  ['InfoID',"InfoDesc"],
						autoLoad : true
					}),
					displayField: 'InfoDesc',
					valueField : "InfoID",
					itemId : "ScopeID",
					queryMode : "local",
					name : "ScopeID",
					fieldLabel : "حوزه طرح"
				},{
					xtype : "checkbox",
					itemId : "CustomerRelated",
					boxLabel : "پر کردن این بخش به عهده مشتری می باشد",
					name : "CustomerRelated"
				},{
					xtype : "checkbox",
					itemId : "IsMandatory",
					boxLabel : "پر کردن این بخش توسط مشتری الزامی است",
					name : "IsMandatory"
				},{
					xtype : "hidden",
					name : "GroupID",
					itemId : "GroupID"
				},{
					xtype : "hidden",
					name : "ParentID",
					itemId : "ParentID"
				},{
					xtype : "hidden",
					name : "FormType",
					itemId : "FormType",
					value : this.FormType
				}],
			buttons :[{
					text : "ذخیره",
					handler : function(){PlanElementsObject.SaveGroup();},
					iconCls : "save"
				},{
					text : "انصراف",
					handler : function(){
						PlanElementsObject.infoWin.hide();
					},
					iconCls : "undo"
				}]
		})
	});
	//................................................................
	
	this.HFormWin = new Ext.window.Window({
		width : 500,
		height : 340,
		modal : true,
		closeAction : "hide",
		items : new Ext.FormPanel({
			items : [{
				xtype : "combo",
				store: new Ext.data.SimpleStore({
					fields:["id","title"],
					data : [
						["panel", "فرم اطلاعاتی"],
						["grid", "جدول اطلاعاتی"],
						["displayfield", "متن توضیحی"]
					]
				}),
				fieldLabel : "نوع آیتم",
				valueField : "id",
				displayField : "title",
				name : "ElementType",
				listeners : {
					select : function(combo,records){
						if(records[0].data.id == "displayfield")
							PlanElementsObject.HFormWin.down("[name=ElementValues]").enable();
						else
							PlanElementsObject.HFormWin.down("[name=ElementValues]").disable();
					}
				}
			},{
				xtype : "textarea",
				fieldLabel : "مشخصات",
				name : "properties",
				width : 480,
				fieldStyle : "direction:ltr"
			},{
				xtype : "textarea",
				fieldLabel : "مقادیر",
				name : "ElementValues",
				width : 480,
				disabled : true
			},{
				xtype : "hidden",
				name : "GroupID"
			},{
				xtype : "hidden",
				name : "ElementID"
			},{
				xtype : "hidden",
				name : "ParentID",
				value : 0
			}]
		}),
		buttons :[{
			text : "ذخیره",
			disabled : this.AddAccess ? false : true,
			iconCls : "save",
			handler : function(){
				PlanElementsObject.SaveElement( PlanElementsObject.HFormWin, PlanElementsObject.HGrid);
			} 
		},{
			text : "انصراف",
			iconCls : "undo",
			handler : function(){this.up('window').hide();}
		}]
	});
	Ext.getCmp(this.TabID).add(this.HFormWin);
	
	//................................................................
	this.DFormWin = new Ext.window.Window({
		width : 500,
		height : 500,
		modal : true,
		closeAction : "hide",
		items : new Ext.FormPanel({
			items : [{
				xtype : "combo",
				store: new Ext.data.SimpleStore({
					fields:["id","title"],
					data : [
						["displayfield","متن نمایشی"],
						["textfield","متن کوتاه"],
						["shdatefield","تاریخ شمسی"],
						["datefield","تاریخ میلادی"],
						["currencyfield","مبلغ"],
						["radio","گزینه ای"],
						["numberfield","عدد"],
						["textarea","متن بلند"],	
						["displayfield","متن نمایشی"],
						["combo","انتخابی"]		
					]
				}),
				fieldLabel : "نوع آیتم",
				valueField : "id",
				displayField : "title",
				name : "ElementType",
				listeners : {
					select : function(combo,records){
						if( records[0].data.id == "radio" || 
							records[0].data.id == "displayfield" || 
							records[0].data.id == "combo")
							PlanElementsObject.DFormWin.down("[name=ElementValues]").enable();
						else
							PlanElementsObject.DFormWin.down("[name=ElementValues]").disable();
					}
				}
			},{
				xtype : "textfield",
				fieldLabel : "عنوان آیتم",
				name : "ElementTitle",
				width : 480
			},{
				xtype : "textarea",
				fieldLabel : "مشخصات ادیتور",
				name : "EditorProperties",
				width : 480,
				fieldStyle : "direction:ltr"
			},{
				xtype : "textarea",
				fieldLabel : "مشخصات",
				name : "properties",
				width : 480,
				fieldStyle : "direction:ltr"
			},{
				xtype : "displayfield",
				fieldCls : "blueText",
				style : "margin-bottom:10px",
				value: "مقادیر مربوطه برای نوع آیتم انتخابی و گزینه ای را با جدا کننده # به ترتیب وارد کنید"
			},{
				xtype : "textarea",
				fieldLabel : "مقادیر",
				name : "ElementValues",
				width : 480,
				disabled : true
			},{
				xtype : "hidden",
				name : "GroupID"
			},{
				xtype : "hidden",
				name : "ElementID"
			},{
				xtype : "hidden",
				name : "ParentID",
				value : 0
			}]
		}),
		buttons :[{
			text : "ذخیره",
			disabled : this.AddAccess ? false : true,
			iconCls : "save",
			handler : function(){
				PlanElementsObject.SaveElement( PlanElementsObject.DFormWin, PlanElementsObject.DGrid);
			} 
		},{
			text : "انصراف",
			iconCls : "undo",
			handler : function(){this.up('window').hide();}
		}]
	});
	Ext.getCmp(this.TabID).add(this.DFormWin);
}

PlanElements.prototype.ShowMenu = function(view, record, item, index, e)
{
	e.stopEvent();
	e.preventDefault();
	view.select(index);

	Menu = new Ext.menu.Menu();
		
	if(record.data.id == "src")
	{
		if(this.AddAccess)
			Menu.add({
				text: 'ایجاد سطح',
				iconCls: 'add',
				handler: function(){ PlanElementsObject.BeforeSaveGroup("new");}
			});		
	}
	else
	{
		if(this.AddAccess)
			Menu.add({
				text: 'ایجاد زیر سطح',
				iconCls: 'add',
				handler: function(){ PlanElementsObject.BeforeSaveGroup("new");}
			});
		if(this.EditAccess)
			Menu.add({
				text: 'ویرایش سطح',
				handler: function(){ PlanElementsObject.BeforeSaveGroup("edit");},
				iconCls: 'edit'
			});
		if(this.RemoveAccess)
			Menu.add({
				text: 'حذف سطح',
				handler: function(){ PlanElementsObject.DeleteGroup();},
				iconCls: 'remove'
			});
	}

	var coords = e.getXY();
	Menu.showAt([coords[0]-120, coords[1]]);
}

PlanElements.prototype.BeforeSaveGroup = function(mode)
{
	var record = this.tree.getSelectionModel().getSelection()[0];
	this.infoWin.down('form').getForm().reset();

	this.infoWin.show();
	console.log(record);
	this.infoWin.down('form').getComponent("ParentID").setValue(record.data.id);
	
	if(mode == "edit")
	{
		this.infoWin.down('form').getComponent("GroupID").setValue(record.data.id);
		this.infoWin.down('form').getComponent("GroupDesc").setValue(record.data.text);
		this.infoWin.down('form').getComponent("ParentID").setValue(record.data.parentId);
		
		this.infoWin.down('form').getComponent("ScopeID").setValue(record.raw.ScopeID);
		this.infoWin.down('form').getComponent("CustomerRelated").setValue(record.raw.CustomerRelated == "YES");
		this.infoWin.down('form').getComponent("IsMandatory").setValue(record.raw.IsMandatory == "YES");		
	}
	else
		this.infoWin.down('form').getComponent("ParentID").setValue(record.data.id == "src" ? 0 : record.data.id);
}

PlanElements.prototype.SaveGroup = function(){
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	this.infoWin.down('form').getForm().submit({
		clientValidation: true,
		url: this.address_prefix + 'elements.data.php?task=SaveGroup',
		method : "POST",
		
		success : function(form,action){                
			
			GroupID = PlanElementsObject.infoWin.down('form').getComponent("GroupID").getValue();
			mode = GroupID == "" ? "new" : "edit";

			if(mode == "new")
			{
				ParentID = PlanElementsObject.infoWin.down('form').getComponent("ParentID").getValue();
				if(ParentID == "0")
					Parent = PlanElementsObject.tree.getRootNode()
				else
					Parent = PlanElementsObject.tree.getRootNode().findChild("id",ParentID,true);
				Parent.set('leaf', false);
				Parent.appendChild({
					id : action.result.data,
					href : 'javascript:void(0)',
					text :  PlanElementsObject.infoWin.down('form').getComponent("GroupDesc").getValue(),
					leaf : true
				});  
				Parent.expand();
			}
			else
			{
				node = PlanElementsObject.tree.getRootNode().findChild("id", GroupID, true);
				node.set('text', PlanElementsObject.infoWin.down('form').getComponent("GroupDesc").getValue());
				form = PlanElementsObject.infoWin.down('form');
				node.raw.ScopeID = form.getComponent("ScopeID").getValue();
				node.raw.CustomerRelated = form.getComponent("CustomerRelated").checked ? "YES" : "NO";
				node.raw.IsMandatory = form.getComponent("IsMandatory").checked ? "YES" : "NO";
			}

			PlanElementsObject.infoWin.down('form').getForm().reset();
			PlanElementsObject.infoWin.hide();

			mask.hide();

		},
		failure : function(form,action)
		{
			Ext.MessageBox.alert('Error', action.result.data);
			mask.hide();
		}
	});
}

PlanElements.prototype.DeleteGroup = function()
{
	var record = this.tree.getSelectionModel().getSelection()[0];
	
	if(record.childNodes.length != 0)
	{
		alert("این واحد شامل واحد فرعی می باشد و تنها زمانی قابل حذف است که هیچ واحد فرعی نداشته باشد");
		return;
	}
	
	if(!confirm("آیا مایل به حذف می باشید؟"))
	{
		return;
	}
	mask = new Ext.LoadMask(this.tree, {msg:'در حال ذخيره سازي...'});
	mask.show();
	Ext.Ajax.request({
		url : this.address_prefix + 'elements.data.php?task=DeleteGroup',
		method : 'POST',
		params :{
			GroupID : record.data.id
		},
		
		success: function(response,option){			
			mask.hide();
			var sd = Ext.decode(response.responseText );
			if(sd.success)
			{
				record.remove();
	            return;
			}
			else
			{
				alert("عملیات مورد نظر با شکست مواجه شد.");
			}
		}
	});
}

//.......................................................

PlanElements.ElementTypeRender = function(v,p,r){

	switch(v)
	{
		case "panel" : return "فرم اطلاعاتی";
		case "grid" : return "جدول اطلاعاتی";
		case "displayfield" : return "متن نمایشی";
		case "textfield" : return "متن کوتاه";
		case "shdatefield" : return "تاریخ شمسی";
		case "datefield" : return "تاریخ میلادی";
		case "currencyfield" : return "مبلغ";
		case "radio" : return "گزینه ای";
		case "numberfield" : return "عدد";
		case "textarea" : return "متن بلند";	
		case "displayfield" : return "متن نمایشی";
		case "combo" : return "انتخابی";			
	}
}

PlanElements.HOperationRender = function(v,p,r){
	
	return "<div align='center' title='ویرایش' class='edit' "+
		"onclick='PlanElementsObject.EditElement(PlanElementsObject.HFormWin, PlanElementsObject.HGrid);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;float:right;width:16px;height:16'></div>" +
	
	"<div align='center' title='حذف' class='remove' "+
		"onclick='PlanElementsObject.DeleteElement(PlanElementsObject.HGrid);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;float:right;width:16px;height:16'></div>";
}

PlanElements.DOperationRender = function(v,p,r){
	
	st = "";
	if(PlanElementsObject.EditAccess)
		st += "<div align='center' title='ویرایش' class='edit' "+
		"onclick='PlanElementsObject.EditElement(PlanElementsObject.DFormWin, PlanElementsObject.DGrid);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;float:right;width:16px;height:16'></div>";
	if(PlanElementsObject.RemoveAccess)
		st += "<div align='center' title='حذف' class='remove' "+
		"onclick='PlanElementsObject.DeleteElement(PlanElementsObject.DGrid);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;float:right;width:16px;height:16'></div>";
	
	return st;
}

PlanElements.prototype.ShowElements = function(GroupID)
{
	this.HGrid.getStore().proxy.extraParams.GroupID = GroupID;
	if(this.HGrid.rendered)
		this.HGrid.getStore().load();
	else
		this.HGrid.render(this.get("DIV_HGrid"));
	
	this.DGrid.hide();
}

PlanElements.prototype.AddHElement = function()
{
	this.HFormWin.down('form').getForm().reset();
	this.HFormWin.down("[name=ElementValues]").disable();
	this.HFormWin.down('[name=GroupID]').setValue(this.HGrid.getStore().proxy.extraParams.GroupID);
	this.HFormWin.show();	
}

PlanElements.prototype.AddDElement = function()
{
	this.DFormWin.down('form').getForm().reset();
	this.DFormWin.down("[name=ElementValues]").disable();
	this.DFormWin.down('[name=GroupID]').setValue(this.DGrid.getStore().proxy.extraParams.GroupID);
	this.DFormWin.down('[name=ParentID]').setValue(this.DGrid.getStore().proxy.extraParams.ParentID);
	this.DFormWin.show();	
}

PlanElements.prototype.EditElement = function(win, grid)
{
	var record = grid.getSelectionModel().getLastSelected();
	win.down('form').loadRecord(record);
	
	if(record.data.ElementType == "radio"|| 
		record.data.ElementType == "displayfield" || 
		record.data.ElementType == "combo")
		win.down("[name=ElementValues]").enable();
	else
		win.down("[name=ElementValues]").disable();
	
	win.show();
}

PlanElements.prototype.SaveElement = function(window, grid)
{
	mask = new Ext.LoadMask(window, {msg:'در حال ذخیره سازی...'});
	mask.show();
	window.down('form').submit({
		url: this.address_prefix + 'elements.data.php?task=SaveElement',
		method : "POST",

		success : function(form,action){      
			mask.hide();
			window.hide();
			grid.getStore().load();
		},
		failure : function(){
			mask.hide();
			Ext.MessageBox.alert("Error", "عملیات مورد نظر با شکست مواجه شد");
		}
	});
}

PlanElements.prototype.DeleteElement = function(grid)
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		var record = grid.getSelectionModel().getLastSelected();
		mask = new Ext.LoadMask(grid, {msg:'در حال حذف...'});
		mask.show();

		Ext.Ajax.request({
			url : PlanElementsObject.address_prefix + "elements.data.php?task=DeleteElement",
			method : "POST",
			params : {
				ElementID : record.data.ElementID
			},

			success : function(response){
				var result = Ext.decode(response.responseText);
				mask.hide();
				if(result.success)
					grid.getStore().load();
				else if(result.data != "")
					Ext.MessageBox.alert("Error", result.data);
				else
					Ext.MessageBox.alert("Error", "عملیات مورد نظر با شکست مواجه شد.");
			}
		});
		
	});
	
}

PlanElementsObject = new PlanElements();

</script>
<center><br>
	<div id="div_tree"></div>
	<br>
	<div id="DIV_HGrid"></div>
	<div id="DIV_DGrid"></div>
	
</center>