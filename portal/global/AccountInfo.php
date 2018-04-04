<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;
require_once 'global.data.php';

$temp = AccDocFlow(COSTID_saving, true);
$TotalAmount = count($temp) > 0 ? $temp[0]["amount"] : 0;

//------------------------------------------------------------------------------

$dg = new sadaf_datagrid("dg","global/global.data.php?task=AccDocFlow&CostID=" . COSTID_saving, "");

$col = $dg->addColumn("تاریخ", "DocDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("شرح سند", "description", "");
$col->width = 250;

$col = $dg->addColumn("شرح ردیف", "details", "");

$col = $dg->addColumn("مبلغ بدهکار", "DebtorAmount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("مبلغ بستانکار", "CreditorAmount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("مانده", "Remainder", GridColumn::ColumnType_money);
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
AccountInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function AccountInfo()
{
	this.grid = <?= $grid ?>;
	
	this.mainPanel = new Ext.form.FormPanel({
		frame: true,
		applyTo : this.get("panelDIV"),
		title: 'اطلاعات حساب قرض الحسنه',
		width: 770,
		bodyPadding: ' 10 10 ',
		items: [{
			xtype : "displayfield",
			labelWidth : 150,
			style : "margin-bottom:20px;margin-top:10px",
			width : 300,
			fieldLabel: 'مبلغ حساب قرض الحسنه',
			fieldCls : "blueText",			
			value : <?= $TotalAmount ?>,
			renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
		}, this.grid]
	});
}

AccountInfoObject = new AccountInfo();


</script>

<div id="panelDIV"></div>

