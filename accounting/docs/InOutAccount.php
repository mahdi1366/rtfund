<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1395.03
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "doc.data.php?task=GetAccountSummary", "grid_div");

$dg->addColumn("", "TafsiliID","", true);

$col = $dg->addColumn("تفصیلی", "TafsiliDesc","");

$col = $dg->addColumn("پس انداز", "pasandaz",  GridColumn::ColumnType_money);
$col->renderer = "InOutAccount.AccountDetailRender";
$col->width = 110;

$col = $dg->addColumn("کوتاه مدت", "kootah",  GridColumn::ColumnType_money);
$col->renderer = "InOutAccount.AccountDetailRender";
$col->width = 110;

$col = $dg->addColumn("بلند مدت", "boland",  GridColumn::ColumnType_money);
$col->renderer = "InOutAccount.AccountDetailRender";
$col->width = 110;

$col = $dg->addColumn("جاری", "jari",  GridColumn::ColumnType_money);
$col->renderer = "InOutAccount.AccountDetailRender";
$col->width = 110;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 220;
$dg->pageSize = 5;
$dg->width = 780;
$dg->title = "خلاصه حساب های ذینفعان";
$dg->DefaultSortField = "TafsiliDesc";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "TafsiliDesc";
$Hgrid = $dg->makeGrid_returnObjects();

//..............................................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "doc.data.php?task=GetAccountFlow", "grid_div");

$col = $dg->addColumn("شماره سند", "LocalNo","");
$col->width = 60;

$dg->addColumn("", "DocID","", true);
$dg->addColumn("", "ItemID","", true);
$dg->addColumn("", "TafsiliID","", true);
$dg->addColumn("", "CostID","", true);
$dg->addColumn("", "DocStatus","", true);
$dg->addColumn("شرح سند", "description","");

$col = $dg->addColumn("شرح ردیف", "details","");
$col->width = 200;

$col = $dg->addColumn("تاریخ سند", "DocDate", GridColumn::ColumnType_date);

$col = $dg->addColumn("برداشت", "DebtorAmount", GridColumn::ColumnType_money);
$col->width = 110;
$col->summaryType = GridColumn::SummeryType_sum;
$col->summaryRenderer = "function(v){return Ext.util.Format.Money(v);}";
$col->align = "center";

$col = $dg->addColumn("واریز", "CreditorAmount", GridColumn::ColumnType_money);
$col->width = 110;
$col->summaryType = GridColumn::SummeryType_sum;
$col->summaryRenderer = "function(v){return Ext.util.Format.Money(v);}";
$col->align = "center";

$dg->addButton("", "واریز وجه", "arrow_down", "function(){InOutAccountObject.BeforeOperation(1);}");
$dg->addButton("", "برداشت وجه", "arrow_up", "function(){InOutAccountObject.BeforeOperation(-1);}");

$dg->EnableSummaryRow = true;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 280;
$dg->width = 780;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "DocDate";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "description";
$grid = $dg->makeGrid_returnObjects();

?>
<script type="text/javascript">

InOutAccount.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

InOutAccount.AccountDetailRender = function(v,p,r,index, col){
	
	return "<a href='javascript:void(1)' onclick='InOutAccountObject.LoadAccountDetail(" +col+ ")'>" 
		+ Ext.util.Format.Money(v) + "</a>";
}

function InOutAccount()
{
	this.grid = <?= $grid ?>;
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.DocStatus ==  "CONFIRM")
			return "greenRow";
		return "";
	}	
	this.grid.addDocked({
		xtype : "toolbar",
		dock:'bottom', 
		layout : "vbox",
		items : ['->',{
			xtype : "displayfield",
			itemId : "remaindar",
			fieldCls : "blueText",
			fieldLabel : "مانده حساب : "
		},{
			xtype : "displayfield",
			itemId : "bllocked",
			fieldCls : "blueText",
			fieldLabel : "مبلغ بلوکه شده : "
		},{
			xtype : "displayfield",
			itemId : "freeAmount",
			fieldCls : "blueText",
			fieldLabel : "مبلغ قابل برداشت :"
		}]
	});
	this.grid.getStore().on("load", function(){
		summaryObject = InOutAccountObject.grid.features.findObject("ftype", "summary");
		DebtorCol = InOutAccountObject.grid.columns.findObject("dataIndex", "DebtorAmount").id;
		CreditorCol = InOutAccountObject.grid.columns.findObject("dataIndex", "CreditorAmount").id;
		
		remaindar = summaryObject.summaryData[CreditorCol]*1 - summaryObject.summaryData[DebtorCol]*1;
		InOutAccountObject.grid.down("[itemId=remaindar]").setValue( Ext.util.Format.Money(remaindar) );
		
		var r = this.getProxy().getReader().jsonData;
		BlockedAmount = r.message;
		if(BlockedAmount == null)
			BlockedAmount = 0;
		InOutAccountObject.grid.down("[itemId=bllocked]").setValue( Ext.util.Format.Money(BlockedAmount) );
		free = remaindar - BlockedAmount*1;
		InOutAccountObject.grid.down("[itemId=freeAmount]").setValue( Ext.util.Format.Money(free) );
	});
	
	this.Hgrid = <?= $Hgrid ?>;
	this.Hgrid.render(this.get("div_hgrid"));
}

var InOutAccountObject = new InOutAccount();

InOutAccount.prototype.LoadAccountDetail = function(col){
	
	var record = this.Hgrid.getSelectionModel().getLastSelected();
	CostID = "";
	switch(col)
	{
		case 2 : CostID = <?= COSTID_saving ?>; break;
		case 3 : CostID = <?= COSTID_ShortDeposite ?>; break;
		case 4 : CostID = <?= COSTID_LongDeposite ?>; break;
		case 5 : CostID = <?= COSTID_current ?>; break;
	}

	this.grid.getStore().proxy.extraParams.BaseCostID = CostID;
	this.grid.getStore().proxy.extraParams.TafsiliID = record.data.TafsiliID;
	if(this.grid.rendered)
		this.grid.getStore().load();
	else
		this.grid.render(this.get("div_grid"));			
	
	
}

InOutAccount.prototype.BeforeOperation = function(mode){
	
	if(!this.mainWin)
	{
		this.mainWin = new Ext.window.Window({
			width : 400,
			height : 180,
			bodyStyle : "background-color:white",
			modal : true,
			closeAction : "hide",
			items : [{
				xtype : "combo",
				width : 380,
				store: new Ext.data.SimpleStore({
					fields:["CostID","CostCode","CostDesc",{
						name : "title",
						convert : function(v,r){ return "[ " + r.data.CostCode + " ] " + r.data.CostDesc;}
					}],
					data : [
						["<?= COSTID_Fund ?>", "100" , "صندوق"],
						["<?= COSTID_Bank ?>", "101", "بانک"]
					]
				}),
				valueField : "CostID",
				fieldLabel : "کد حساب",
				name : "CostID",
				displayField : "title",
				listeners : {
					select : function(combo,records){
						if(records[0].data.CostID == "<?= COSTID_Bank ?>")
						{
							this.up('window').down("[name=TafsiliID]").enable();
							this.up('window').down("[name=TafsiliID2]").enable();
						}	
						else
						{
							this.up('window').down("[name=TafsiliID]").disable();
							this.up('window').down("[name=TafsiliID2]").disable();
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
						url: '/accounting/baseinfo/baseinfo.data.php?task=GetAllTafsilis&TafsiliType=6',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					}
				}),
				emptyText:'انتخاب بانک ...',
				typeAhead: false,
				disabled : true,
				width : 380,
				fieldLabel : "تفصیلی بانک",
				valueField : "TafsiliID",
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
						url: '/accounting/baseinfo/baseinfo.data.php?task=GetAllTafsilis&TafsiliType=3',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					}
				}),
				emptyText:'انتخاب حساب ...',
				typeAhead: false,
				disabled : true,
				width : 380,
				fieldLabel : "تفصیلی بانک",
				valueField : "TafsiliID",
				name : "TafsiliID2",
				displayField : "title"
			},{
				xtype : "currencyfield",
				fieldLabel : "مبلغ",
				name : "amount",
				width : 380,
				hideTrigger : true				
			}],
			buttons :[{
				text : "ذخیره",
				iconCls : "save",
				itemId : "btn_save",
				handler : function(){ InOutAccountObject.SaveOperation(); }
			},{
				text : "انصراف",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.mainWin);
	}
	this.mode = mode;
	
	this.mainWin.down("[name=CostID]").setValue();
	this.mainWin.down("[name=TafsiliID]").setValue();
	this.mainWin.down("[name=TafsiliID]").disable();
	this.mainWin.down("[name=amount]").setValue();
	
	this.mainWin.show();	
	this.mainWin.center();
}
	
InOutAccount.prototype.SaveOperation = function(){

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'doc.data.php',
		method: "POST",
		params: {
			task: "RegisterInOutAccountDoc",
			mode : this.mode,
			BaseCostID : this.grid.getStore().proxy.extraParams.BaseCostID,
			BaseTafsiliID : this.grid.getStore().proxy.extraParams.TafsiliID,
			CostID : this.mainWin.down("[name=CostID]").getValue(),
			TafsiliID: this.mainWin.down("[name=TafsiliID]").getValue(),
			TafsiliID2: this.mainWin.down("[name=TafsiliID2]").getValue(),
			amount: this.mainWin.down("[name=amount]").getValue()
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				InOutAccountObject.grid.getStore().load();
				InOutAccountObject.mainWin.hide();
				Ext.MessageBox.alert("","سند حسابداری مربوطه صادر گردید");
			}
			else
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

</script>
<center>
	<br>
	<div id="div_hgrid"></div>
	<br>
	<div id="div_grid"></div>
</center>