<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.01
//-------------------------
include('../../header.inc.php');
include_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................
$dg = new sadaf_datagrid("dg", $js_prefix_address . "shift.data.php?task=GetAllShifts", "grid_div");

$dg->addColumn("", "ShiftID", "", true);
$dg->addColumn("", "IsActive", "", true);

$col = $dg->addColumn("عنوان شیفت", "ShiftTitle", "");
$col->editor = ColumnEditor::TextField();


$col = $dg->addColumn("از ساعت", "FromTime");
$col->editor = ColumnEditor::TimeField();
$col->width = 80;
$col->align = "center";

$col = $dg->addColumn("تا ساعت", "ToTime");
$col->editor = ColumnEditor::TimeField();
$col->width = 80;
$col->align = "center";

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){ShiftObject.AddShift();}";
	
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(){return ShiftObject.SaveShift();}";
}
$dg->title = "لیست شیفت ها";
$dg->height = 500;
$dg->width = 750;
$dg->EnablePaging = false;
$dg->DefaultSortField = "ShiftDesc";

if($accessObj->RemoveFlag)
{
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return Shift.OperationRender(v,p,r);}";
	$col->width = 40;
}
$grid = $dg->makeGrid_returnObjects();

?>
<script>

Shift.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

Shift.OperationRender = function(v,p,r)
{
	if(ShiftObject.RemoveAccess)	
		return "<div align='center' title='حذف وام' class='remove' "+
		"onclick='ShiftObject.DeleteShift();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

function Shift(){
	
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("grid_div"));
}

var ShiftObject = new Shift();	

Shift.prototype.AddShift = function(){

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		ShiftID	: null
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

Shift.prototype.DeleteShift = function()
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = ShiftObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'shift.data.php',
			params:{
				task: "DeleteShift",
				ShiftID : record.data.ShiftID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				ShiftObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

Shift.prototype.SaveShift = function(){

	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'shift.data.php',
		method: "POST",
		params: {
			task: "SaveShift",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				ShiftObject.grid.getStore().load();
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
        <div id="div_selectGroup"></div>
        <br>
		<div id="newDiv"></div>
        <div id="grid_div"></div>
    </form>
</center>
