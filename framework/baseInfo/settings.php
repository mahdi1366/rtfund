<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.01
//-------------------------
include('../../header.inc.php');
include_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "baseinfo.data.php?task=GetAllSettings", "grid_div");

$dg->addColumn("", "ParamID", "", true);
$dg->addColumn("", "SystemID", "", true);

$col = $dg->addColumn("توضیحات", "ParamTitle");

$col = $dg->addColumn("مقادیر مجاز", "ParamValues");

$col = $dg->addColumn("مقادیر مجاز", "ParamDesc");

$col = $dg->addColumn("مقدار", "ParamValue");
$col->editor = ColumnEditor::TextField();

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){SettingObject.AddSetting();}";
	
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(){return SettingObject.SaveSetting();}";
}
if($accessObj->RemoveFlag)
{
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return Setting.DeleteRender(v,p,r);}";
	$col->width = 40;
}
$dg->title = "تعطیلات رسمی";
$dg->height = 500;
$dg->width = 750;
$dg->EnablePaging = false;
$dg->DefaultSortField = "TheDate";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "details";
$dg->EnableSearch = false;
$dg->EnablePaging = false;

$grid = $dg->makeGrid_returnObjects();

?>
<script>

Setting.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

Setting.DeleteRender = function(v,p,r)
{
	if(SettingObject.RemoveAccess)	
		return "<div align='center' title='حذف' class='remove' "+
		"onclick='SettingObject.DeleteSetting();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

function Setting(){
	
	this.grid = <?= $grid ?>;
	
	this.YearFieldSet = new Ext.form.FieldSet({
		title: "انتخاب سیستم",
		width: 300,
		renderTo : this.get("div_Years"),
		frame: true,
		items : [{
			xtype : "combo",
			store: new Ext.data.Store({
				autoLoad : true,
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + '../management/framework.data.php?task=selectSystems',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['SystemID','SysName']
			}),
			fieldLabel : "سیستم",
			queryMode : 'local',
			name : "SystemID",
			displayField: 'SysName',
			valueField : "SystemID",
			width : 400,
			listeners : {
				select : function(){
					me = SettingObject;
					me.grid.getStore().proxy.extraParams.SystemID = this.getValue();
					if(me.grid.rendered)
						me.grid.getStore().load();
					else
						me.grid.render(me.get("grid_div"));	
				}
			}
		}]
	});
}

var SettingObject = new Setting();	

Setting.prototype.SaveSetting = function(){

	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'baseinfo.data.php',
		method: "POST",
		params: {
			task: "SaveSetting",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				SettingObject.grid.getStore().load();
			}
			else
			{
				if(st.data == "")
					Ext.MessageBox.alert("","خطا در اجرای عملیات");
				else
					Ext.MessageBox.alert("",st.data);
			}
		},
		failure: function(){}
	});
}

</script>
<center>
    <form id="mainForm">
        <br>
        <div id="div_Years"></div>
        <br>
        <div id="grid_div"></div>
    </form>
</center>
