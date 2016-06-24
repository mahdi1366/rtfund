<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.12
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "cheques.data.php?task=selectOutcomeCheques", "grid_div");

$col = $dg->addColumn("", "ChequeID", "", true);

$col = $dg->addColumn("شماره سند", "LocalNo");
$col->width = 80;

$col = $dg->addColumn("حساب", "AccountDesc");
$col->width = 120;

$col = $dg->addColumn("شماره چک", "CheckNo");
$col->width = 70;

$col = $dg->addColumn("تاریخ چک", "CheckDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("مبلغ چک", "amount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("گیرنده", "TafsiliDesc", "");
$col->width = 120;

$col = $dg->addColumn("وضعیت چک", "StatusDesc", "");
$col->width = 90;

$col = $dg->addColumn("توضیحات", "description", "");

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 400;
$dg->width = 800;
$dg->title = "چک های پرداختی";
$dg->DefaultSortField = "CheckDate";
$dg->DefaultSortDir = "Desc";
$dg->autoExpandColumn = "description";
$grid = $dg->makeGrid_returnObjects();

?>
<script>

OutcomeCheque.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function OutcomeCheque(){
	
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("div_form"),
		width : 600,
		frame : true,
		title : "تنظیمات گزارش",
		layout : {
			type : "table",
			columns : 2			
		},
		
		items : [{
			xtype : "numberfield",
			name : "FromDocNo",
			hideTrigger : true,
			fieldLabel : "از شماره سند"
		},{
			xtype : "numberfield",
			name : "ToDocNo",
			hideTrigger : true,
			fieldLabel : "تا شماره سند"
		},{
			xtype : "shdatefield",
			name : "DFromDate",
			fieldLabel : "از تاریخ سند"
		},{
			xtype : "shdatefield",
			name : "DToDate",
			fieldLabel : "تا تاریخ سند"
		},{
			xtype : "numberfield",
			name : "FromCheckNo",
			hideTrigger : true,
			fieldLabel : "از شماره چک"
		},{
			xtype : "numberfield",
			name : "ToCheckNo",
			hideTrigger : true,
			fieldLabel : "تا شماره چک"
		},{
			xtype : "shdatefield",
			name : "FromDate",
			fieldLabel : "از تاریخ چک"
		},{
			xtype : "shdatefield",
			name : "ToDate",
			fieldLabel : "تا تاریخ چک"
		},{
			xtype : "currencyfield",
			name : "FromAmount",
			hideTrigger : true,
			fieldLabel : "از مبلغ"
		},{
			xtype : "currencyfield",
			name : "ToAmount",
			hideTrigger : true,
			fieldLabel : "تا مبلغ"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?' +
						"task=GetBankData",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['BankID','BankDesc'],
				autoLoad : true
			}),
			fieldLabel : "بانک",
			displayField : "BankDesc",
			queryMode : "local",
			valueField : "BankID",
			hiddenName :"ChequeBank"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?' +
						"task=SelectChequeStatuses",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['InfoID','InfoDesc'],
				autoLoad : true
			}),
			fieldLabel : "وضعیت چک",
			displayField : "InfoDesc",
			queryMode : "local",
			valueField : "InfoID",
			hiddenName :"CheckStatus"
		}],
		buttons :[{
			text : "جستجو",
			iconCls : "search",
			handler : function(){
				if(!OutcomeChequeObject.grid.rendered)
					OutcomeChequeObject.grid.render(OutcomeChequeObject.get("div_grid"));
				else
					OutcomeChequeObject.grid.getStore().loadPage(1);
			}
		},{
			text : "پاک کردن فرم",
			iconCls : "clear",
			handler : function(){
				this.up('form').getForm().reset();
			}
		}]
	});
	
	this.formPanel.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
		if(!OutcomeChequeObject.grid.rendered)
			OutcomeChequeObject.grid.render(OutcomeChequeObject.get("div_grid"));
		else
			OutcomeChequeObject.grid.getStore().loadPage(1);
		e.preventDefault();
		e.stopEvent();
		return false;
	});
		
	this.grid = <?= $grid ?>;
	this.grid.getStore().proxy.form = this.get("MainForm");
	//this.grid.render(this.get("div_grid"));
}

OutcomeChequeObject = new OutcomeCheque();

OutcomeCheque.prototype.FilterGrid = function(item){
	alert(item);
	
}

</script>
<center>
	<br>
	<form id="MainForm">
		<div id="div_form"></div>
	</form>
	<br>
	<div id="div_grid"></div>
</center>