<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.07
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "doc.data.php?task=GetAllCostBlocks", "grid_div");

$dg->addColumn("", "CostBlockID", "", true);
$dg->addColumn("", "CostBlockType", "", true);
$dg->addColumn("", "title", "", true);
$dg->addColumn("", "ObjectID", "", true);

$col = $dg->addColumn("کد حساب", "CostCode");
$col->width = 80;

$col = $dg->addColumn("عنوان حساب", "CostDesc", "");

$col = $dg->addColumn("گروه تفصیلی", "TafsiliTypeDesc", "");
$col->width = 120;
$col = $dg->addColumn("تفصیلی", "TafsiliDesc", "");
$col->width = 120;

$col = $dg->addColumn("مبلغ بلوکه", "BlockAmount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("جزئیات", "details", "");
$col->width = 120;
$col->ellipsis = 20;

if($accessObj->AddFlag)
{
	//$dg->addButton = true;
	//$dg->addHandler = "function(){CostBlockObject.AddCostBlock();}";
}
if($accessObj->RemoveFlag)
{
	/*$col = $dg->addColumn("عملیات", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return CostBlock.OperationRender(v,p,r);}";
	$col->width = 50;*/
}

$dg->title = "مبالغ بلوکه حساب ها";
$dg->height = 500;
$dg->width = 750;
$dg->DefaultSortField = "BlockID";
$dg->autoExpandColumn = "CostDesc";
$dg->emptyTextOfHiddenColumns = true;
$grid = $dg->makeGrid_returnObjects();

?>
<center>
    <form id="mainForm">
        <br>
        <div id="div_selectGroup"></div>
        <br>
		<div id="newDiv"></div>
        <div id="grid_div"></div>
    </form>
</center>
<script>

CostBlock.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function CostBlock(){

	this.grid = <?= $grid ?>;
	this.grid.plugins[0].on("beforeedit", function(editor,e){
		if(e.record.data.ObjectID*1 > 0)
			return false;
		if(!e.record.data.CostBlockID)
			return CostBlockObject.AddAccess;
		return CostBlockObject.EditAccess;
	});
	this.grid.render(this.get("grid_div"));
}

CostBlock.OperationRender = function(v,p,r){
	
	if(r.data.ObjectID*1 > 0)
		return "";
	return "<div align='center' title='حذف' class='remove' "+
		"onclick='CostBlockObject.DeleteCostBlock();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

CostBlock.prototype.AddCostBlock = function(){

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		CostBlockID: null,
		CostBlockType : this.groupPnl.down("[name=CostBlockType]").getValue(),
		CostBlockCode: null
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

CostBlock.prototype.SaveCostBlock = function(index){

	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'baseinfo.data.php',
		method: "POST",
		params: {
			task: "SaveCostBlock",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				CostBlockObject.grid.getStore().load();
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

CostBlock.prototype.DeleteGroup = function(CostBlockType){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = CostBlockObject;
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'baseinfo.data.php',
			params:{
				task: "DeleteGroup",
				CostBlockType : CostBlockType
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				sd = Ext.decode(response.responseText);

				if(sd.success)
				{
					CostBlockObject.groupPnl.down('[name=CostBlockType]').setValue();
					CostBlockObject.groupPnl.down('[name=CostBlockType]').getStore().load();
					CostBlockObject.grid.hide();
				}	
				else
				{
					Ext.MessageBox.alert("Error","در این گروه وام تعریف شده و قادر به حذف آن نمی باشید");
				}
			},
			failure: function(){}
		});
	});
}

CostBlock.prototype.DeleteCostBlock = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = CostBlockObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'baseinfo.data.php',
			params:{
				task: "DeleteCostBlock",
				CostBlockID : record.data.CostBlockID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				CostBlockObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

var CostBlockObject = new CostBlock();	

</script>