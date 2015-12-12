<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$ShortDepositeCostID = 66;
$LongDepositeCostID = 119 ;

$CurYear = substr(DateModules::shNow(),0,4);

$temp = PdoDataAccess::runquery("
	select CostID, sum(CreditorAmount-DebtorAmount) amount 
	from ACC_DocItems di join Acc_docs d using(DocID)
	left join ACC_tafsilis t1 on(t1.TafsiliType=1 AND di.TafsiliID=t1.TafsiliID)
	left join ACC_tafsilis t2 on(t2.TafsiliType=1 AND di.Tafsili2ID=t2.TafsiliID)
	where CycleID=:year AND CostID in($ShortDepositeCostID,$LongDepositeCostID) 
		AND (t1.ObjectID=:pid or t2.ObjectID=:pid) 
		AND DocStatus in('CONFIRM','ARCHIVE')
	group by CostID
	order by CostID
", array(":year" => $CurYear, ":pid" => $_SESSION["USER"]["PersonID"]));

$ShortDeposite = 0;
$LongDeposite = 0;
for($i=0; $i < count($temp); $i++)
{
	$ShortDeposite = $temp[$i]["CostID"] == $ShortDepositeCostID ? $temp[$i]["amount"] : $ShortDeposite;
	$LongDeposite = $temp[$i]["CostID"] == $LongDepositeCostID ? $temp[$i]["amount"] : $LongDeposite;
}

//------------------------------------------------------------------------------

$dg = new sadaf_datagrid("dg","global/global.data.php?task=AccDocFlow", "");

$col = $dg->addColumn("تاریخ", "DocDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("شرح سند", "description", "");
$col->width = 250;

$col = $dg->addColumn("شرح ردیف", "details", "");

$col = $dg->addColumn("مبلغ بدهکار", "DebtorAmount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("مبلغ بستانکار", "CreditorAmount", GridColumn::ColumnType_money);
$col->width = 100;

$dg->HeaderMenu = false;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 300;
$dg->width = 740;
$dg->EnableSearch = false;
$dg->DefaultSortField = "DocDate";
$dg->autoExpandColumn = "details";

$grid = $dg->makeGrid_returnObjects();


?>
<script>
DepositeInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function DepositeInfo()
{
	this.grid = <?= $grid ?>;
	
	this.mainPanel = new Ext.form.FormPanel({
		frame: true,
		applyTo : this.get("panelDIV"),
		title: 'اطلاعات سپرده',
		width: 770,
		bodyPadding: ' 10 10 ',
		
		items: [{
			xtype : "container",
			anchor : "100%",
			style : "margin-top:10px",
			layout : "hbox",
			items : [{
				xtype : "displayfield",
				labelWidth : 150,
				width : 300,
				fieldLabel: 'مبلغ سپرده کوتاه مدت',
				fieldCls : "blueText",			
				value : <?= $ShortDeposite ?>,
				renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
			},{
				xtype : "button",
				handler : function(){ DepositeInfoObject.DepositeFlow(<?= $ShortDepositeCostID ?>) },
				text : "[ ریز گردش ]",
				iconCls : "list",
				style : "margin-right:50px;border:1px solid #ccc"
			}]			
		},{
			xtype : "container",
			layout : "hbox",
			anchor : "100%",
			style : "margin-top:10px;margin-bottom:10px;",
			items :[{
				xtype : "displayfield",
				labelWidth : 150,
				width : 300,
				fieldLabel: 'مبلغ سپرده بلند مدت',
				fieldCls : "blueText",				
				value : <?= $LongDeposite ?>,
				renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
			},{
				xtype : "button",
				handler : function(){ DepositeInfoObject.DepositeFlow(<?= $LongDepositeCostID ?>) },
				text : "[ ریز گردش ]",
				iconCls : "list",
				style : "margin-right:50px;border:1px solid #ccc;"	
			}]
		},{
			xtype : "container",
			width : 750,
			itemId : "gridCNT"
		}]
	});
}

DepositeInfoObject = new DepositeInfo();

DepositeInfo.prototype.DepositeFlow = function(CostID){
	
	this.grid.getStore().proxy.extraParams = {CostID : CostID};
	
	if(this.grid.rendered)
		this.grid.getStore().load();
	else
		this.grid.render(this.mainPanel.getComponent("gridCNT").getEl());
}

</script>

<div id="panelDIV"></div>

