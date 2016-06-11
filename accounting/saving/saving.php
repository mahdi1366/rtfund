<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1395.03
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$reportOnly = false;
if(!empty($_REQUEST["reportOnly"]))
{
	$reportOnly = true;
	if(isset($_SESSION["USER"]["portal"]))
		$PersonID = $_SESSION["USER"]["PersonID"];
	else
		$PersonID = $_POST["PersonID"];
	
	if(empty($PersonID))
		die();
}

$dg = new sadaf_datagrid("dg", $js_prefix_address . "saving.data.php?task=GetSavingFlow" . 
		($reportOnly ? "&PersonID=" .$PersonID : ""  ), "grid_div");

$col = $dg->addColumn("کد سند", "DocID","");
$col->width = 60;

$dg->addColumn("", "ItemID","", true);
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

if(!$reportOnly)
{
	$dg->addButton("", "واریز وجه", "arrow_down", "function(){SavingObject.BeforeOperation(1);}");
	$dg->addButton("", "برداشت وجه", "arrow_up", "function(){SavingObject.BeforeOperation(-1);}");
}
$dg->EnableSummaryRow = true;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 480;
$dg->width = 780;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "DocDate";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "description";
$grid = $dg->makeGrid_returnObjects();

?>
<script type="text/javascript">

Saving.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Saving()
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
		items : ['->',{
			xtype : "displayfield",
			width : 120,
			fieldCls : "blueText",
			value : "مانده حساب پس انداز : "
		},{
			xtype : "displayfield",
			itemId : "remaindar",
			width : 120,
			fieldCls : "blueText",
			style : "direction:ltr"
		}]
	});
	this.grid.getStore().on("load", function(){
		summaryObject = SavingObject.grid.features.findObject("ftype", "summary");
		DebtorCol = SavingObject.grid.columns.findObject("dataIndex", "DebtorAmount").id;
		CreditorCol = SavingObject.grid.columns.findObject("dataIndex", "CreditorAmount").id;
		
		remaindar = summaryObject.summaryData[CreditorCol]*1 - summaryObject.summaryData[DebtorCol]*1;
		SavingObject.grid.down("[itemId=remaindar]").setValue( Ext.util.Format.Money(remaindar) );
	});
		
	if(<?= !$reportOnly ? "false" : "true" ?>)
	{
		this.grid.render(this.get("div_grid"));
		return;
	}

		
	//.......................................................
		
	this.PartPanel = new Ext.form.FieldSet({
		title: "انتخاب فرد",
		width: 500,
		renderTo : this.get("div_form"),
		frame: true,
		items : [{
			xtype : "combo",
			width : 450,
			fieldLabel : "انتخاب فرد",
			pageSize : 20,
			store: new Ext.data.Store({
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + 'saving.data.php?task=selectPersons',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['PackNo','PersonID','fullname', {
					name : "title",
					convert : function(v,r){
						return "[ " + r.data.PackNo + " ] " + r.data.fullname;
					}
				}]
			}),
			displayField: 'title',
			valueField : "PersonID",
			listeners :{
				select : function(combo,records){
					me = SavingObject;
					
					me.grid.getStore().proxy.extraParams = {
						PersonID : this.getValue()
					};
					if(me.grid.rendered)
						me.grid.getStore().load();
					else
						me.grid.render(me.get("div_grid"));					
				}
			}
		}]
	});
	
}

var SavingObject = new Saving();
	
Saving.prototype.BeforeOperation = function(mode){
	
	if(!this.mainWin)
	{
		this.mainWin = new Ext.window.Window({
			width : 400,
			height : 150,
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
							this.up('window').down("[name=TafsiliID]").enable();
						else
							this.up('window').down("[name=TafsiliID]").disable();
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
						url: '/accounting/baseinfo/baseinfo.data.php?task=GetAllTafsilis&TafsiliType=3',
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
				handler : function(){ SavingObject.SaveOperation(); }
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
	
Saving.prototype.SaveOperation = function(){

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'saving.data.php',
		method: "POST",
		params: {
			task: "RegisterDoc",
			mode : this.mode,
			PersonID : this.grid.getStore().proxy.extraParams.PersonID,
			CostID : this.mainWin.down("[name=CostID]").getValue(),
			TafsiliID: this.mainWin.down("[name=TafsiliID]").getValue(),
			amount: this.mainWin.down("[name=amount]").getValue()
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				SavingObject.grid.getStore().load();
				SavingObject.mainWin.hide();
				Ext.MessageBox.alert("","برگه حسابداری مربوطه صادر گردید");
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
	<div id="div_form"></div>
	<div id="div_grid"></div>
</center>