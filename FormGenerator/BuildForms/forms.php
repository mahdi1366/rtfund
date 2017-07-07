<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.09
//-----------------------------
ini_set("display_errors", "On");

require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "form.data.php?task=SelectForms", "div_dg");

$col = $dg->addColumn("عنوان", "FormTitle");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("فرمها", "FormID", "");
$col->align = "center";
$col->renderer = "function(v,p,r){return FRG_forms.FormsRender(v);}";
$col->width = 60;

$col = $dg->addColumn("ساخت فرم", "ParentID", "");
$col->align = "center";
$col->renderer = "function(v,p,r){return FRG_forms.BuildRender(v,p,r);}";
$col->width = 60;

$col = $dg->addColumn("حذف", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return FRG_forms.OperationRender(v,p,r);}";
$col->width = 40;

$dg->addButton("btn_add", "ایجاد گروه جدید", "add", "function(){FRG_formsObject.AddForm();}");
$dg->addButton("btn_return", "بازگشت به گروه ها", "undo", "function(){FRG_formsObject.ChangeGrid(0)}");
$dg->addButton("btn_copy", "کپی فرم", "copy", "function(){FRG_formsObject.copyForm();}");

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){return FRG_formsObject.saveForm(v,p,r);}";

$dg->title = "لیست گروه های فرم ها";
$dg->EnableSearch = false;
$dg->DefaultSortField = "FormID";
$dg->emptyTextOfHiddenColumns = true;
$dg->DefaultSortDir = "desc";
$dg->autoExpandColumn = "FormTitle";
$dg->width = 780;
$dg->height = 400;
$dg->pageSize = 15;

$grid = $dg->makeGrid_returnObjects();
?>
<script>
	
FRG_forms.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix: "<?= $js_prefix_address ?>",

	get: function (elementID) {
		return findChild(this.TabID, elementID);
	}
};

FRG_forms.OperationRender = function(v,p,r){

	return "<div align='center' title='حذف' class='remove' "+
		"onclick='FRG_formsObject.RemoveForm();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;height:16'></div>";
}

FRG_forms.FormsRender = function(v,p,r){

	return "<div align='center' title='فرم ها' class='list' "+
		"onclick='FRG_formsObject.ChangeGrid("+v+");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;height:16'></div>";
}

FRG_forms.BuildRender = function(v,p,r){

	return "<div align='center' title='ساخت فرم' class='process' "+
		"onclick='FRG_formsObject.buildForm();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;height:16'></div>";
}

function FRG_forms() {

	this.grid = <?= $grid ?>;
	this.grid.getStore().proxy.extraParams.ParentID = 0;
	this.grid.render(this.get("div_dg"));
	this.grid.down("[itemId=btn_return]").hide();
	this.grid.down("[itemId=btn_copy]").hide();
	this.grid.columns.findObject('dataIndex','ParentID').hide();
}

FRG_forms.prototype.ChangeGrid = function(FormID){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	if(FormID == 0)
	{
		this.grid.down("[itemId=btn_add]").setText("ایجاد گروه جدید");
		this.grid.setTitle("لیست گروه های فرم ها");
		this.grid.down("[itemId=btn_return]").hide();
		this.grid.down("[itemId=btn_copy]").hide();
		this.grid.columns.findObject('dataIndex','ParentID').hide();
		this.grid.columns.findObject('dataIndex','FormID').show();
	}
	else
	{
		this.grid.down("[itemId=btn_add]").setText("ایجاد فرم جدید");
		this.grid.setTitle("فرم های گروه " + record.data.FormTitle);
		this.grid.down("[itemId=btn_return]").show();
		this.grid.down("[itemId=btn_copy]").show();
		this.grid.columns.findObject('dataIndex','ParentID').show();
		this.grid.columns.findObject('dataIndex','FormID').hide();
	}
	this.grid.getStore().proxy.extraParams.ParentID = FormID;
	this.grid.getStore().load();
}

FRG_formsObject = new FRG_forms();

FRG_forms.prototype.buildForm = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	framework.OpenPage(this.address_prefix + "NewForm.php?FormID=" + record.data.FormID, "ساخت فرم");
}

FRG_forms.prototype.AddForm = function () {
	
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		ParentID : this.grid.getStore().proxy.extraParams.ParentID,
		FormID : ""
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

FRG_forms.prototype.saveForm = function(store,record)
{
    mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SaveForm',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix +'form.data.php?task=SaveForm',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				FRG_formsObject.grid.getStore().load();
			}
		},
		failure: function(){}
	});
}

FRG_forms.prototype.RemoveForm = function () {
	
	Ext.MessageBox.confirm("","آیا مایل به حذف فرم می باشید؟", function(btn){
		
		if(btn == "no")
			return;
		
		me = FRG_formsObject;
		mask = new Ext.LoadMask(FRG_formsObject.grid, {msg:'در حال حذف...'});
		mask.show();
	
		Ext.Ajax.request({
			url: me.address_prefix + 'form.data.php?task=deleteForm',
			params: {                
				FormID: me.grid.getSelectionModel().getLastSelected().data.FormID            
			},
			method: 'POST',
			success: function (res) {
				mask.hide();
				var sd = Ext.decode(res.responseText);
				if (!sd.success) {
					if (sd.data != '')
						Ext.MessageBox.alert('', sd.data); 
					else
						Ext.MessageBox.alert('', 'خطا در اجرای عملیات');
					return;
				}
				FRG_formsObject.grid.getStore().load();
			}
		});
	});
		
}

FRG_forms.prototype.copyForm = function () {
	
	record = this.grid.getSelectionModel().getLastSelected();
	if(record == null)
	{
		Ext.MessageBox.alert("","ابتدا فرم مورد نظر خود را انتخاب کنید");
		return;
	}
	
	Ext.MessageBox.confirm("","آیا مایل به ایجاد کپی از فرم می باشید؟", function(btn){
		
		if(btn == "no")
			return;
		
		me = FRG_formsObject;
		mask = new Ext.LoadMask(me.grid, {msg:'در حال حذف...'});
		mask.show();
	
		Ext.Ajax.request({
			url: me.address_prefix + 'form.data.php?task=CopyForm',
			params: {                
				FormID: me.grid.getSelectionModel().getLastSelected().data.FormID            
			},
			method: 'POST',
			success: function (res) {
				mask.hide();
				FRG_formsObject.grid.getStore().load();
			}
		});
	});
		
}

FRG_forms.FillForm = function(FillFormID)
{
	framework.OpenPage(FRG_formsObject.address_prefix + "../FillForm/FillForm.php?FillFormID=" + FillFormID, "تکمیل فرم");
}
</script>
<br>
<center>    
	<button onclick="FRG_forms.FillForm(1)">تست فرم</button>
    <div id="div_dg"></div>
</center>
