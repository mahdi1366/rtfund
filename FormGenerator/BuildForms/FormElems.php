<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1395.02
//-----------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;

$FormID = $_REQUEST['FormID'];

$dg = new sadaf_datagrid("dg", $js_prefix_address . "form.data.php?task=selectFromElements&FormID=" 
		. $FormID . "&ParentID=0", "grid_div");

$dg->addColumn("", "GroupID", "", true);
$dg->addColumn("", "ParentID", "", true);
$dg->addColumn("", "ElementID", "", true);

$col = $dg->addColumn("عنوان آیتم", "ElementTitle", "");
$col->width = 200;
$col->sortable = false;

$col = $dg->addColumn("نوع آیتم", "ElementType", "");
$col->renderer = "FRG_FormElems.ElementTypeRender";
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
$col->renderer = "FRG_FormElems.DOperationRender";
$col->width = 50;

$dg->addButton("", "ایجاد آیتم", "add", "function(){FRG_FormElemsObject.AddDElement()}");
$dg->addButton("", "بازگشت به کلیه آیتم ها", "undo", "function(){FRG_FormElemsObject.Undo()}");

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 535;
$dg->width = 780;
$dg->DefaultSortField = "ElementID";
$dg->autoExpandColumn = "ElementValues";
$dg->EnablePaging = false;
$dg->HeaderMenu = false;
$dg->EnableSearch = false;

$grid2 = $dg->makeGrid_returnObjects();

?>
<script>

FRG_FormElems.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	FormID : <?= $FormID ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function FRG_FormElems(){

	this.DGrid = <?= $grid2 ?>;
	this.DGrid.on("itemdblclick", function(view, record){
		
		me = FRG_FormElemsObject;
		if(record.data.ElementType == "grid")
		{
			me.DGrid.getStore().proxy.extraParams.ParentID = record.data.ElementID;
			FRG_FormElemsObject.DGrid.getStore().load();
		}

	});	
	this.DGrid.render(this.get("DIV_DGrid"));
	//................................................................
	this.ElemsWin = new Ext.window.Window({
		width : 500,
		autoHeight : true,
		modal : true,
		closeAction : "hide",
		items : new Ext.FormPanel({
			items : [{
				xtype : "combo",
				store: new Ext.data.SimpleStore({
					fields : ['id'],
					data : [ 
						["grid"],
						["displayfield"],
						["textfield"],
						["shdatefield"],
						["datefield"],
						["currencyfield"],
						["radio"],
						["checkbox"],
						["numberfield"],
						["textarea"],	
						["combo"]
					]					
				}),
				fieldLabel : "نوع آیتم",
				valueField : "id",
				displayField : "id",
				name : "ElementType",
				listeners : {
					select : function(combo,records){
						if( records[0].data.id == "radio" || 
							records[0].data.id == "displayfield" || 
							records[0].data.id == "combo")
							FRG_FormElemsObject.ElemsWin.down("[name=ElementValues]").enable();
						else
							FRG_FormElemsObject.ElemsWin.down("[name=ElementValues]").disable();
						
						if( records[0].data.id == "displayfield")
							FRG_FormElemsObject.ElemsWin.down("[name=alias]").enable();
						else
							FRG_FormElemsObject.ElemsWin.down("[name=alias]").disable();
					}
				}
			},{
				xtype : "textfield",
				fieldLabel : "عنوان آیتم",
				name : "ElementTitle",
				width : 480
			},{
				xtype : "container",
				html : "در صورتی که این آیتم از مقادیر ثابت می باشد alias مربوطه در کوئری را وارد کنید"
			},{
				xtype : "textfield",
				fieldLabel : "alias",
				name : "alias",
				width : 300,
				disabled : true,
				fieldStyle : "direction:ltr"
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
				name : "ElementID"
			},{
				xtype : "hidden",
				name : "ParentID",
				value : 0
			},{
				xtype : "hidden",
				name : "FormID",
				value : this.FormID
			}]
		}),
		buttons :[{
			text : "ذخیره",
			iconCls : "save",
			handler : function(){
				FRG_FormElemsObject.SaveElement( FRG_FormElemsObject.ElemsWin, FRG_FormElemsObject.DGrid);
			} 
		},{
			text : "انصراف",
			iconCls : "undo",
			handler : function(){this.up('window').hide();}
		}]
	});
	Ext.getCmp(this.TabID).add(this.ElemsWin);
}

FRG_FormElems.prototype.Undo = function(){
	
	this.DGrid.getStore().proxy.extraParams.ParentID = 0;
	FRG_FormElemsObject.DGrid.getStore().load();
}

FRG_FormElems.DOperationRender = function(v,p,r){
	
	st = "";
	if(FRG_FormElemsObject.EditAccess)
		st += "<div align='center' title='ویرایش' class='edit' "+
		"onclick='FRG_FormElemsObject.EditElement(FRG_FormElemsObject.ElemsWin, FRG_FormElemsObject.DGrid);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;float:right;width:16px;height:16'></div>";
	if(FRG_FormElemsObject.RemoveAccess)
		st += "<div align='center' title='حذف' class='remove' "+
		"onclick='FRG_FormElemsObject.DeleteElement(FRG_FormElemsObject.DGrid);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;float:right;width:16px;height:16'></div>";
	
	return st;
}

FRG_FormElems.prototype.AddDElement = function(){
	
	this.ElemsWin.down('form').getForm().reset();
	this.ElemsWin.down("[name=ElementValues]").disable();
	this.ElemsWin.down('[name=ParentID]').setValue(this.DGrid.getStore().proxy.extraParams.ParentID);
	this.ElemsWin.show();	
}

FRG_FormElems.prototype.EditElement = function(win, grid){
	
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

FRG_FormElems.prototype.SaveElement = function(window, grid){
	
	mask = new Ext.LoadMask(window, {msg:'در حال ذخیره سازی...'});
	mask.show();
	window.down('form').submit({
		url: this.address_prefix + 'form.data.php?task=SaveElement',
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

FRG_FormElems.prototype.DeleteElement = function(grid){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		var record = grid.getSelectionModel().getLastSelected();
		mask = new Ext.LoadMask(grid, {msg:'در حال حذف...'});
		mask.show();

		Ext.Ajax.request({
			url : FRG_FormElemsObject.address_prefix + "form.data.php?task=DeleteElement",
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

FRG_FormElemsObject = new FRG_FormElems();

</script>
<center>
	<div id="DIV_DGrid"></div>	
</center>