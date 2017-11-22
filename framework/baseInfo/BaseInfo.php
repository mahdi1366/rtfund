<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.12
//-------------------------
require_once('../header.inc.php');
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "baseInfo.data.php?task=SelectBaseInfo", "grid_div");

$dg->addColumn("", "TypeID", "", true);
$dg->addColumn("", "IsActive", "", true);

$col = $dg->addColumn("کد", "InfoID");
$col->width = 100;

$col = $dg->addColumn("شرح", "InfoDesc", "");
$col->editor = ColumnEditor::TextField();

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){BaseInfoObject.AddBaseInfo();}";
}
if($accessObj->RemoveFlag)
{
	$col = $dg->addColumn("غیر فعال", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return BaseInfo.DeleteRender(v,p,r);}";
	$col->width = 70;
}
$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(){return BaseInfoObject.SaveBaseInfo();}";

$dg->title = "لیست اطلاعات";
$dg->height = 500;
$dg->width = 500;
$dg->DefaultSortField = "InfoDesc";
$dg->autoExpandColumn = "InfoDesc";
$dg->emptyTextOfHiddenColumns = true;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$grid = $dg->makeGrid_returnObjects();

?>
<center>
    <form id="mainForm">
        <br>
        <div id="div_selectGroup"></div>
        <br>
        <div id="div_grid"></div>
    </form>
</center>
<script>

BaseInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function BaseInfo(){

	this.grid = <?= $grid ?>;
	this.grid.plugins[0].on("beforeedit", function(editor,e){
		if(e.record.data.IsActive == "NO")
			return false;
		if(e.record.data.ObjectID*1 > 0)
			return false;
		if(!e.record.data.InfoID)
			return BaseInfoObject.AddAccess;
		return BaseInfoObject.EditAccess;
	});
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsActive == "NO")
			return "pinkRow";
	}
	
	this.groupPnl = new Ext.form.Panel({
		renderTo: this.get("div_selectGroup"),
		title: "انتخاب گروه",
		width: 400,
		collapsible : true,
		collapsed : false,
		frame: true,
		bodyCfg: {style: "background-color:white"},
		items : [{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {type: 'jsonp',
					url: this.address_prefix + 'baseInfo.data.php?task=SelectBaseTypes',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true,
				fields : ['TypeID','TypeDesc']
			}),
			valueField : "TypeID",
			queryMode : "local",
			width : 380,
			name : "TypeID",
			displayField : "TypeDesc",
			fieldLabel : "انتخاب گروه",
			listeners :{
				select : function(){
					BaseInfoObject.grid.getStore().proxy.extraParams = {
						TypeID : this.getValue()
					};
					if(BaseInfoObject.grid.rendered)
						BaseInfoObject.grid.getStore().load();
					else
						BaseInfoObject.grid.render(BaseInfoObject.get("div_grid"));
				}
			}
		}]
	});	
}

var BaseInfoObject = new BaseInfo();	

BaseInfo.DeleteRender = function(v,p,r){
	
	if(r.data.IsActive == "NO")
		return "";
	return "<div align='center' title='حذف' class='remove' "+
		"onclick='BaseInfoObject.DeleteBaseInfo();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

BaseInfo.prototype.AddBaseInfo = function(){

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		InfoID: 0,
		TypeID : this.groupPnl.down("[name=TypeID]").getValue(),
		BaseInfoCode: null
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

BaseInfo.prototype.SaveBaseInfo = function(index){

	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'baseInfo.data.php',
		method: "POST",
		params: {
			task: "SaveBaseInfo",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				BaseInfoObject.grid.getStore().load();
			}
			else
			{
				if(st.data == "")
					alert("خطا در اجرای عملیات");
				else
					alert(st.data);
			}
		},
		failure: function(){}
	});
}

BaseInfo.prototype.DeleteBaseInfo = function(){
	
	Ext.MessageBox.confirm("","در صورتی که آیتم مورد نظر استفاده نشده باشد حذف می شود. آیا مایل به ادامه می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = BaseInfoObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'baseInfo.data.php',
			params:{
				task: "DeleteBaseInfo",
				TypeID : record.data.TypeID,
				InfoID : record.data.InfoID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				BaseInfoObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

</script>