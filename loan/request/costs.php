<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.07
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

$RequestID = $_REQUEST["RequestID"];

$dg = new sadaf_datagrid("dg",$js_prefix_address . "request.data.php?task=GetCosts&RequestID=" .$RequestID,"grid_div");

$dg->addColumn("", "CostID","", true);
$dg->addColumn("", "RequestID","", true);
$dg->addColumn("", "DocID","", true);

$col = $dg->addColumn("شرح هزینه", "CostDesc");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("مبلغ", "CostAmount", GridColumn::ColumnType_money);
$col->editor = ColumnEditor::CurrencyField();
$col->width = 100;

$col = $dg->addColumn("سند", "LocalNo");
$col->width = 80;

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(store,record){return LoanCostObject.BeforeSaveCost(record);}";

$dg->addButton("AddBtn", "ایجاد ردیف هزینه", "add", "function(){LoanCostObject.AddCost();}");

$col = $dg->addColumn("حذف", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return LoanCost.DeleteRender(v,p,r);}";
$col->width = 35;

$dg->height = 336;
$dg->width = 585;
$dg->emptyTextOfHiddenColumns = true;
$dg->EnableSearch = false;
$dg->HeaderMenu = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "CostID";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "CostDesc";

$grid = $dg->makeGrid_returnObjects();

?>
<script type="text/javascript">

LoanCost.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	
	RequestID : <?= $RequestID ?>,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function LoanCost()
{
	this.grid = <?= $grid ?>;
	this.grid.plugins[0].on("beforeedit", function(editor,e){
			
		if(e.record.data.CostID != null)
			return false;

	});
	this.grid.render(this.get("div_grid"));	
}

LoanCost.DeleteRender = function(v,p,r){
	
	if(r.data.DocID != null &&  r.data.DocID != "")
		return "";

	return "<div align='center' title='حذف' class='remove' "+
		"onclick='LoanCostObject.DeleteCost();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

LoanCost.prototype.BeforeSaveCost = function(record){
	
	if(!this.BankWin)
	{
		this.BankWin = new Ext.window.Window({
			width : 400,
			height : 350,
			bodyStyle : "background-color:white",
			modal : true,
			closeAction : "hide",
			items : [{
				xtype : "form",
				border : false,
				items :[{
					xtype : "combo",
					width : 385,
					fieldLabel : "حساب مربوطه",
					colspan : 2,
					store: new Ext.data.Store({
						fields:["CostID","CostCode","CostDesc", "TafsiliType","TafsiliType2",{
							name : "fullDesc",
							convert : function(value,record){
								return "[ " + record.data.CostCode + " ] " + record.data.CostDesc
							}				
						}],
						proxy: {
							type: 'jsonp',
							url: '/accounting/baseinfo/baseinfo.data.php?task=SelectCostCode',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						}
					}),
					typeAhead: false,
					name : "CostID",
					valueField : "CostID",
					displayField : "fullDesc",
					listeners : {
						select : function(combo,records){
							
							me = LoanCostObject;
							if(records[0].data.TafsiliType != null)
							{
								me.BankWin.down("[itemId=TafsiliID]").setValue();
								me.BankWin.down("[itemId=TafsiliID]").getStore().proxy.extraParams.TafsiliType = records[0].data.TafsiliType;
								me.BankWin.down("[itemId=TafsiliID]").getStore().load();
							}
							if(records[0].data.TafsiliType2 != null)
							{
								me.BankWin.down("[itemId=TafsiliID2]").setValue();
								me.BankWin.down("[itemId=TafsiliID2]").getStore().proxy.extraParams.TafsiliType = records[0].data.TafsiliType2;
								me.BankWin.down("[itemId=TafsiliID2]").getStore().load();
							}
						}
					}
				},{
					xtype : "combo",
					store: new Ext.data.Store({
						fields:["TafsiliID","TafsiliCode","TafsiliDesc",{
							name : "title",
							convert : function(v,r){ return "[ " + r.data.TafsiliCode + " ] " + r.data.TafsiliDesc;}
						}],
						proxy: {
							type: 'jsonp',
							url: '/accounting/baseinfo/baseinfo.data.php?task=GetAllTafsilis',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						}
					}),
					emptyText:'انتخاب تفصیلی1 ...',
					typeAhead: false,
					pageSize : 10,
					width : 385,
					valueField : "TafsiliID",
					itemId : "TafsiliID",
					name : "TafsiliID",
					displayField : "title"
				},{
					xtype : "combo",
					store: new Ext.data.Store({
						fields:["TafsiliID","TafsiliCode","TafsiliDesc",{
							name : "title",
							convert : function(v,r){ return "[ " + r.data.TafsiliCode + " ] " + r.data.TafsiliDesc;}
						}],
						proxy: {
							type: 'jsonp',
							url: '/accounting/baseinfo/baseinfo.data.php?task=GetAllTafsilis',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						}
					}),
					emptyText:'انتخاب تفصیلی2 ...',
					typeAhead: false,
					pageSize : 10,
					width : 385,
					valueField : "TafsiliID",
					itemId : "TafsiliID2",
					name : "TafsiliID2",
					displayField : "title"
				}]
			}],
			buttons :[{
				text : "ذخیره",
				iconCls : "save",
				itemId : "btn_save",
				handler : function(){ LoanCostObject.SaveCost();}
			},{
				text : "انصراف",
				iconCls : "undo",
				handler : function(){this.up('window').hide(); LoanPayObject.grid.getStore().load();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.BankWin);
	}
	
	this.BankWin.show();
	this.BankWin.down("[itemId=btn_save]").setHandler(function(){ 
		LoanCostObject.SaveCost(record); 
	});
}

LoanCost.prototype.SaveCost = function(record){

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();
	
	params = {
		task: "SaveCosts",
		record: Ext.encode(record.data)
	};
	params = mergeObjects(params, this.BankWin.down('form').getForm().getValues());

	Ext.Ajax.request({
		url: this.address_prefix +'request.data.php',
		method: "POST",
		params : params,
		
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				LoanCostObject.grid.getStore().load();
				LoanCostObject.BankWin.hide();
			}
			else
			{
				Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
			}
		},
		failure: function(){}
	});
}

LoanCost.prototype.AddCost = function(){


	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		CostID: null,
		RequestID : this.RequestID
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

LoanCost.prototype.DeleteCost = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = LoanCostObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(me.grid, {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'request.data.php',
			params:{
				task: "DeleteCosts",
				CostID : record.data.CostID
			},
			method: 'POST',

			success: function(response,option){
				result = Ext.decode(response.responseText);
				if(result.success)
					LoanCostObject.grid.getStore().load();
				else if(result.data == "")
					Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
				else
					Ext.MessageBox.alert("",result.data);
				mask.hide();
				
			},
			failure: function(){}
		});
	});
}

var LoanCostObject = new LoanCost();

</script>
<center>
	<div id="div_grid"></div>
</center>