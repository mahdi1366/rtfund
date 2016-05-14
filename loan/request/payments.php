<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$PartID = $_REQUEST["PartID"];
if(empty($PartID))
	die();
$editable = true;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "request.data.php?task=GetPartPayments" . 
		"&PartID=" . $PartID, "grid_div");

$dg->addColumn("", "PayID","", true);
$dg->addColumn("", "PartID","", true);
$dg->addColumn("", "DocID","", true);
$dg->addColumn("", "DocStatus","", true);

$col = $dg->addColumn("تاریخ پرداخت", "PayDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->align = "center";

$col = $dg->addColumn("مبلغ پرداخت", "PayAmount", GridColumn::ColumnType_money);
$col->editor = ColumnEditor::CurrencyField();
$col->width = 120;
$col->align = "center";

if(isset($_SESSION["USER"]["framework"]))
{
	$col = $dg->addColumn("شماره برگه حسابداری", "LocalNo");
	$col->align = "center";
	$col->renderer = "function(v,p,r){return PartPayment.DocRender(v,p,r);}";
	$col->width = 120;
}
if($editable)
{
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(store,record){return PartPaymentObject.SavePartPayment(record);}";
	
	$dg->addButton("AddBtn", "ایجاد ردیف پرداخت", "add", "function(){PartPaymentObject.AddPay();}");
	
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return PartPayment.DeleteRender(v,p,r);}";
	$col->width = 35;
}

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 270;
$dg->width = 410;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "PayDate";
$dg->DefaultSortDir = "ASC";

$grid = $dg->makeGrid_returnObjects();

?>
<script>
PartPayment.prototype = {
	TabID : "<?= $_REQUEST["ExtTabID"] ?>",
	address_prefix : "<?= $js_prefix_address?>",

	PartID : "<?= $PartID ?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function PartPayment()
{
	this.grid = <?= $grid ?>;
	this.grid.plugins[0].on("beforeedit", function(editor,e){
		if(e.record.data.DocID != "0")
			return false;
		return true;
	});
	this.grid.render(this.get("div_grid"));
	
}

PartPayment.DeleteRender = function(v,p,r){
	
	return "<div align='center' title='حذف' class='remove' "+
		"onclick='PartPaymentObject.DeletePayment();' " +
		"style='float:right;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:18px;height:16'></div>";
}

PartPayment.DocRender = function(v,p,r){
	
	if(r.data.DocID != "0")
	{
		st = v;
		if(r.data.DocStatus == "RAW")
			st += "<div align='center' title='برگشت سند' class='undo' "+
				"onclick='PartPaymentObject.ReturnPayPartDoc();' " +
				"style='float:left;background-repeat:no-repeat;background-position:center;" +
				"cursor:pointer;width:16px;height:16'></div>";
		return st;
	}
	return "<div align='center' title='صدور سند' class='send' "+
		"onclick='PartPaymentObject.BeforeRegDoc();' " +
		"style='float:right;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

PartPaymentObject = new PartPayment();
	
PartPayment.prototype.AddPay = function(){

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		PayID: null,
		DocID : 0,
		PartID : this.PartID
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}
	
PartPayment.prototype.SavePartPayment = function(record){

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'request.data.php',
		method: "POST",
		params: {
			task: "SavePartPayment",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			PartPaymentObject.grid.getStore().load();
			if(!st.success)
			{
				if(st.data == "")
					Ext.MessageBox.alert("Error","عملیات مورد نظر با شکست مواجه شد");
				else
					Ext.MessageBox.alert("Error",st.data);
			}
		},
		failure: function(){}
	});
}

PartPayment.prototype.DeletePayment = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = PartPaymentObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(me.grid, {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'request.data.php',
			params:{
				task: "DeletePayment",
				PayID : record.data.PayID
			},
			method: 'POST',

			success: function(response,option){
				result = Ext.decode(response.responseText);
				if(result.success)
					PartPaymentObject.grid.getStore().load();
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

PartPayment.prototype.BeforeRegDoc = function(){
	
	if(!this.BankWin)
	{
		this.BankWin = new Ext.window.Window({
			width : 300,
			height : 84,
			modal : true,
			closeAction : "hide",
			items : [{
				xtype : "combo",
				store: new Ext.data.Store({
					fields:["TafsiliID","TafsiliDesc"],
					proxy: {
						type: 'jsonp',
						url: '/accounting/baseinfo/baseinfo.data.php?task=GetAllTafsilis&TafsiliType=3',
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
			}],
			buttons :[{
				text : "ذخیره",
				iconCls : "save",
				itemId : "btn_save",
				handler : function(){
					PartPaymentObject.RegPayPartDoc();
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
}

PartPayment.prototype.RegPayPartDoc = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'request.data.php',
		method: "POST",
		params: {
			task: "RegPayPartDoc",
			PayID : record.data.PayID,
			BankTafsili : this.BankWin.down("[itemId=TafsiliID]").getValue()
		},
		success: function(response){

			result = Ext.decode(response.responseText);
			mask.hide();
			if(!result.success)
				Ext.MessageBox.alert("Error", result.data);
			
			PartPaymentObject.BankWin.hide();
			PartPaymentObject.grid.getStore().load();
		}
	});				
}

PartPayment.prototype.ReturnPayPartDoc = function(DocID){
	
	Ext.MessageBox.confirm("","آیا مایل به برگشت سند پرداخت می باشید؟",function(btn){
		
		if(btn == "no")
			return;
		
		me = PartPaymentObject;
		var record = me.grid.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(me.grid, {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'request.data.php',
			method: "POST",
			params: {
				task: "RetPayPartDoc",
				PayID : record.data.PayID
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
				PartPaymentObject.grid.getStore().load();
			}
		});
	});
}

</script>
<center>
	<div id="div_grid"></div>
</center>