<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.01
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................
$dg = new sadaf_datagrid("dg", $js_prefix_address . "shift.data.php?task=GetAllSettings", "grid_div");

$dg->addColumn("", "RowID", "", true);

$col = $dg->addColumn("از تاریخ", "StartDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();

$col = $dg->addColumn("تا تاریخ", "EndDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 120;

$col = $dg->addColumn("تلورانس (دقیقه)", "telorance");
$col->editor = ColumnEditor::NumberField();
$col->width = 120;

$col = $dg->addColumn("سقف اضافه کار روزانه", "MaxDayExtra");
$col->editor = ColumnEditor::NumberField();
$col->width = 120;

$col = $dg->addColumn("سقف اضافه کار ماهانه", "MaxMonthExtra");
$col->editor = ColumnEditor::NumberField();
$col->width = 120;

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){ATNSettingObject.AddATNSetting();}";
	
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(){return ATNSettingObject.SaveATNSetting();}";
}
if($accessObj->RemoveFlag)
{
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return ATNSetting.DeleteRender(v,p,r);}";
	$col->width = 40;
}
$dg->title = "قوانین حضور و غیاب";
$dg->height = 500;
$dg->width = 750;
$dg->EnablePaging = false;
$dg->DefaultSortField = "StartDate";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "StartDate";
$dg->EnableSearch = false;
$dg->EnablePaging = false;

$grid = $dg->makeGrid_returnObjects();

?>
<script>

ATNSetting.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

ATNSetting.DeleteRender = function(v,p,r)
{
	if(ATNSettingObject.RemoveAccess)	
		return "<div align='center' title='حذف' class='remove' "+
		"onclick='ATNSettingObject.DeleteATNSetting();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

function ATNSetting(){
	
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("grid_div"));	
}

var ATNSettingObject = new ATNSetting();	

ATNSetting.prototype.AddATNSetting = function(){

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		RowID : null
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

ATNSetting.prototype.DeleteATNSetting = function()
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = ATNSettingObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'shift.data.php',
			params:{
				task: "DeleteSetting",
				RowID : record.data.RowID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				ATNSettingObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

ATNSetting.prototype.SaveATNSetting = function(){

	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'shift.data.php',
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
				ATNSettingObject.grid.getStore().load();
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
