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

$dg = new sadaf_datagrid("dg", $js_prefix_address . "doc.data.php?task=GetAccountFlow" .
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

$dg->addObject("this.BaseCostCombo");

if(!$reportOnly)
{
	$dg->addButton("", "واریز وجه", "arrow_down", "function(){InOutAccountObject.BeforeOperation(1);}");
	$dg->addButton("", "برداشت وجه", "arrow_up", "function(){InOutAccountObject.BeforeOperation(-1);}");
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

InOutAccount.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function InOutAccount()
{
	this.BaseCostCombo = new Ext.form.ComboBox({
		itemId : "BaseCostID",
		pageSize : 20,
		store: new Ext.data.SimpleStore({
			fields : ['id','title'],
			data : [ 
				['<?= COSTID_saving ?>', 'حساب پس انداز'],
				['<?= COSTID_ShortDeposite ?>', 'سپرده کوتاه مدت'],
				['<?= COSTID_LongDeposite ?>', 'سپرده بلند مدت'],
				['<?= COSTID_current ?>', 'حساب جاری']
			]
		}),
		displayField: 'title',
		value : "<?= COSTID_saving ?>",
		valueField : "id",
		listeners :{
			select : function(combo,records){
				me = InOutAccountObject;

				me.grid.getStore().proxy.extraParams.BaseCostID = this.getValue();
				me.grid.getStore().load();
			}
		}
	});
	this.grid = <?= $grid ?>;
	this.grid.getStore().proxy.extraParams.BaseCostID = "<?= COSTID_saving ?>";
	
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
		summaryObject = InOutAccountObject.grid.features.findObject("ftype", "summary");
		DebtorCol = InOutAccountObject.grid.columns.findObject("dataIndex", "DebtorAmount").id;
		CreditorCol = InOutAccountObject.grid.columns.findObject("dataIndex", "CreditorAmount").id;
		
		remaindar = summaryObject.summaryData[CreditorCol]*1 - summaryObject.summaryData[DebtorCol]*1;
		InOutAccountObject.grid.down("[itemId=remaindar]").setValue( Ext.util.Format.Money(remaindar) );
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
					url: this.address_prefix + '../../framework/person/persons.data.php?task=selectPersons',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['PersonID','fullname']
			}),
			displayField: 'fullname',
			valueField : "PersonID",
			listeners :{
				select : function(combo,records){
					me = InOutAccountObject;
					
					me.grid.getStore().proxy.extraParams.PersonID = this.getValue();
					if(me.grid.rendered)
						me.grid.getStore().load();
					else
						me.grid.render(me.get("div_grid"));					
				}
			}
		}]
	});
	
}

var InOutAccountObject = new InOutAccount();
	
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
			BaseCostID : this.grid.down("[itemId=BaseCostID]").getValue(),
			PersonID : this.grid.getStore().proxy.extraParams.PersonID,
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
	<div id="div_form"></div>
	<div id="div_grid"></div>
</center>