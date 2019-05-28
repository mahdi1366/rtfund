<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 98.03
//-----------------------------
require_once("../header.inc.php");
require_once inc_dataGrid;
 
//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dg = new sadaf_datagrid("dg",$js_prefix_address . "budget.data.php?task=SelectBudgets","div_grid_user");

$dg->addColumn("","BudgetID","", true);
$dg->addcolumn('','IsActive',"",true);

$col = $dg->addColumn("عنوان بودجه","BudgetDesc","string");
$col->editor = ColumnEditor::TextField();
$col->sortable = false;

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){ACC_budgetObject.Adding();}";
}
if($accessObj->AddFlag || $accessObj->EditFlag)
{
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(v,p,r){return ACC_budgetObject.saveData(v,p,r);}";
}
if($accessObj->RemoveFlag)
{
	$col = $dg->addcolumn('حذف','','string');
	$col->renderer = "ACC_budget.RemoveRender";
	$col->width=40;
}

$col = $dg->addcolumn('کدهای حساب','','string');
$col->renderer = "ACC_budget.CostRender";
$col->width=80;
$col->align = "center";

$dg->height = 350;
$dg->width = 500;
$dg->DefaultSortField = "BudgetID";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "BudgetDesc";
$dg->editorGrid = true;
$dg->title = "بودجه ها";
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$grid = $dg->makeGrid_returnObjects();
?>
<script>
ACC_budget.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ACC_budget()
{
	this.grid = <?= $grid?>;
	this.grid.render(this.get("div_grid"));
}

ACC_budget.RemoveRender = function(value,p,record){
	
	if(record.data.IsActive == "YES")
		return  "<div  title='حذف اطلاعات' class='remove' onclick='ACC_budgetObject.Remove();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:50%;height:16'></div>";	
	else
		return  "<div  title='فعال سازی اطلاعات' class='undo' onclick='ACC_budgetObject.Activate();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:50%;height:16'></div>";	
}

ACC_budget.CostRender = function(value,p,record){
	
		return  "<div  title='کدهای حساب بودجه' class='list' onclick='ACC_budgetObject.ShowCostCodes();' " +
		"style=';background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";	
}

ACC_budget.prototype.Adding = function(){
	
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		BudgetID : ""
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

ACC_budget.prototype.saveData = function(store,record){
	
    mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SaveBudget',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix +'budget.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				ACC_budgetObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){
			mask.hide();
		}
	});
}

ACC_budget.prototype.Remove = function(){

	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = ACC_budgetObject;
		var record = me.grid.getSelectionModel().getLastSelected();

		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخيره سازي...'});
		mask.show();

		Ext.Ajax.request({
			params: {
				task: 'DeleteBudget',
				BudgetID: record.data.BudgetID
			},
			url:  me.address_prefix +'budget.data.php',
			method: 'POST',
			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);
				if(st.data == "conflict")
					alert('این آیتم در جای دیگری استفاده شده و قابل حذف نمی باشد.');
				else
					ACC_budgetObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

ACC_budget.prototype.Activate = function(){

	Ext.MessageBox.confirm("","آیا مایل به فعال شدن مجدد می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = ACC_budgetObject;
		var record = me.grid.getSelectionModel().getLastSelected();

		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخيره سازي...'});
		mask.show();

		Ext.Ajax.request({
			params: {
				task: 'ActivateBudget',
				BudgetID: record.data.BudgetID
			},
			url:  me.address_prefix +'budget.data.php',
			method: 'POST',
			success: function(response){
				mask.hide();
				var result = Ext.decode(response.responseText);
				if(result.success)
					ACC_budgetObject.grid.getStore().load();
				else if(result.data != "")
					Ext.MessageBox.alert("",result.data);
				else
					Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه گردید");
					
			},
			failure: function(){}
		});
	});
}

ACC_budget.prototype.ShowCostCodes = function(){

	var record = this.grid.getSelectionModel().getLastSelected();
	
	if(!this.CostCodeWin)
	{
		this.CostCodeWin = new Ext.window.Window({
			width : 765,
			title : "کدهای حساب متصل به بودجه",
			bodyStyle : "background-color:white;text-align:-moz-center",
			height : 565,
			modal : true,
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "BudgetCostCodes.php",
				scripts : true
			},
			buttons :[{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.CostCodeWin);
	}
	this.CostCodeWin.show();
	this.CostCodeWin.center();
	this.CostCodeWin.loader.load({
		params : { 
			ExtTabID : this.CostCodeWin.getEl().id,
			MenuID : <?= $_REQUEST["MenuID"] ?>,
			BudgetID : record.data.BudgetID
		}
	});
}

var ACC_budgetObject = new ACC_budget();

</script>
<center>
	<br>
	<div id="div_grid"></div>
</center>