<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.12
//-------------------------
include('../header.inc.php');
require_once 'request.class.php';
include_once inc_dataGrid;

$RequestID = $_POST["RequestID"];

$dg = new sadaf_datagrid("dg",$js_prefix_address . "request.data.php?task=GetRequestPeriods" .
		"&RequestID=" . $RequestID,"grid_div");

$dg->addColumn("", "PeriodID","", true);
$dg->addColumn("", "RequestID","", true);
$dg->addColumn("", "DocID","", true);

$col = $dg->addColumn("تاریخ شروع", "StartDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();

$col = $dg->addColumn("تاریخ پایان", "EndDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 100;

$col = $dg->addColumn("کارمزد", "wage");
$col->editor = ColumnEditor::NumberField();
$col->width = 60;

$col = $dg->addColumn("شماره نامه", "LetterNo");
$col->editor = ColumnEditor::TextField();
$col->width = 100;

$col = $dg->addColumn("تاریخ نامه", "LetterDate");
$col->editor = ColumnEditor::SHDateField(true);
$col->width = 80;

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(store,record){return WarrentyPeriodObject.SavePeriod(record);}";

$dg->addButton("AddBtn", "ایجاد ردیف پرداخت", "add", "function(){WarrentyPeriodObject.AddPeriod();}");

$col = $dg->addColumn("سند", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return WarrentyPeriod.DocRender(v,p,r);}";
$col->width = 50;

$col = $dg->addColumn("حذف", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return WarrentyPeriod.DeleteRender(v,p,r);}";
$col->width = 35;

$dg->height = 240;
$dg->width = 590;
$dg->emptyTextOfHiddenColumns = true;
$dg->EnableSearch = false;
$dg->HeaderMenu = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "PayDate";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "StartDate";

$grid = $dg->makeGrid_returnObjects();

?>
<script>
WarrentyPeriod.prototype = {
	TabID : "<?= $_REQUEST["ExtTabID"] ?>",
	address_prefix : "<?= $js_prefix_address?>",

	RequestID : "<?= $RequestID ?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function WarrentyPeriod()
{
	this.grid = <?= $grid ?>;
	this.grid.plugins[0].on("beforeedit", function(editor,e){
		if(e.record.data.DocID*1 > 0)
			return false;
		return true;
	});
	this.grid.render(this.get("div_grid"));
	
}

WarrentyPeriod.DeleteRender = function(v,p,r){
	
	return "<div align='center' title='حذف' class='remove' "+
		"onclick='WarrentyPeriodObject.DeletePeriod();' " +
		"style='float:right;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:18px;height:16'></div>";
}

WarrentyPeriod.DocRender = function(v,p,r){
	
	if(r.data.DocID != "0")
	{
		st = v;
		if(r.data.DocStatus == "RAW")
		{
			st += "<div align='center' title='برگشت سند' class='undo' "+
				"onclick='WarrentyPeriodObject.ReturnWarrentyDoc();' " +
				"style='float:left;background-repeat:no-repeat;background-position:center;" +
				"cursor:pointer;width:16px;height:16'></div>";
			
			st += "&nbsp;&nbsp;&nbsp;<div align='center' title='اصلاح سند' class='edit' "+
				"onclick='WarrentyPeriodObject.RegWarrentyDoc(2);' " +
				"style='float:left;background-repeat:no-repeat;background-position:center;" +
				"cursor:pointer;width:16px;height:16'></div>";
		}
		return st;
	}
	return "<div align='center' title='صدور سند' class='send' "+
		"onclick='WarrentyPeriodObject.RegWarrentyDoc(1);' " +
		"style='float:right;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WarrentyPeriodObject = new WarrentyPeriod();
	
WarrentyPeriod.prototype.AddPeriod = function(){

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		PeriodID: null,
		DocID : 0,
		RequestID : this.RequestID
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}
	
WarrentyPeriod.prototype.SavePeriod = function(record){

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'request.data.php',
		method: "POST",
		params: {
			task: "SavePeriod",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(!st.success)
			{
				if(st.data == "")
					Ext.MessageBox.alert("Error","عملیات مورد نظر با شکست مواجه شد");
				else
					Ext.MessageBox.alert("Error",st.data);
			}
			else
				WarrentyPeriodObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

WarrentyPeriod.prototype.DeletePeriod = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = WarrentyPeriodObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(me.grid, {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'request.data.php',
			params:{
				task: "DeletePeriod",
				PeriodID : record.data.PeriodID
			},
			method: 'POST',

			success: function(response,option){
				result = Ext.decode(response.responseText);
				if(result.success)
					WarrentyPeriodObject.grid.getStore().load();
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

WarrentyPeriod.prototype.BeforeRegDoc = function(mode){
	
	if(!this.BankWin)
	{
		this.BankWin = new Ext.window.Window({
			width : 300,
			height : 120,
			modal : true,
			closeAction : "hide",
			items : [{
				xtype : "combo",
				store: new Ext.data.Store({
					fields:["TafsiliID","TafsiliDesc"],
					proxy: {
						type: 'jsonp',
						url: '/accounting/baseinfo/baseinfo.data.php?task=GetAllTafsilis&TafsiliType=6',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					}
				}),
				emptyText:'انتخاب بانک ...',
				width : 287,
				typeAhead: false,
				pageSize : 10,
				valueField : "TafsiliID",
				itemId : "TafsiliID",
				displayField : "TafsiliDesc"
			},{
				xtype : "combo",
				store: new Ext.data.Store({
					fields:["TafsiliID","TafsiliDesc"],
					proxy: {
						type: 'jsonp',
						url: '/accounting/baseinfo/baseinfo.data.php?task=GetAllTafsilis&TafsiliType=3',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					}
				}),
				emptyText:'انتخاب حساب ...',
				width : 287,
				typeAhead: false,
				pageSize : 10,
				valueField : "TafsiliID",
				itemId : "TafsiliID2",
				displayField : "TafsiliDesc"
			}],
			buttons :[{
				text : "ذخیره",
				iconCls : "save",
				itemId : "btn_save",
				handler : function(){
					WarrentyPeriodObject.RegWarrentyDoc();
				}
			},{
				text : "انصراف",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.BankWin);
	}
	
	this.BankWin.show();
	this.BankWin.down("[itemId=btn_save]").setHandler(function(){
		WarrentyPeriodObject.RegPayPartDoc(mode == "1" ? "RegWarrentyDoc" : "editWarrentyDoc");
	});
}

WarrentyPeriod.prototype.RegWarrentyDoc = function(task){
	
	var record = this.grid.getSelectionModel().getLastSelected();

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'request.data.php',
		method: "POST",
		params: {
			task: task,
			PeriodID : record.data.PeriodID,
			BankTafsili : this.BankWin.down("[itemId=TafsiliID]").getValue(),
			AccountTafsili : this.BankWin.down("[itemId=TafsiliID2]").getValue()
		},
		success: function(response){

			result = Ext.decode(response.responseText);
			mask.hide();
			if(!result.success)
				Ext.MessageBox.alert("Error", result.data);
			
			WarrentyPeriodObject.BankWin.hide();
			WarrentyPeriodObject.grid.getStore().load();
		}
	});				
}

WarrentyPeriod.prototype.ReturnWarrentyDoc = function(DocID){
	
	Ext.MessageBox.confirm("","آیا مایل به برگشت سند پرداخت می باشید؟",function(btn){
		
		if(btn == "no")
			return;
		
		me = WarrentyPeriodObject;
		var record = me.grid.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(me.grid, {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'request.data.php',
			method: "POST",
			params: {
				task: "ReturnWarrentyDoc",
				PeriodID : record.data.PeriodID
			},
			success: function(response){
				
				result = Ext.decode(response.responseText);
				mask.hide();
				if(!result.success)
				{
					if(result.data == "")
						Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
					else
						Ext.MessageBox.alert("", result.data);
					return;
				}				
				WarrentyPeriodObject.grid.getStore().load();
			}
		});
	});
}

</script>
<center>
	<div id="div_grid"></div>
</center>