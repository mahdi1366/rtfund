<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$GharzolhasaneCostID = 65;

$CurYear = substr(DateModules::shNow(),0,4);

$temp = PdoDataAccess::runquery("
	select sum(CreditorAmount-DebtorAmount) amount 
	from ACC_DocItems di join ACC_docs d using(DocID)
	left join ACC_tafsilis t1 on(t1.TafsiliType=1 AND di.TafsiliID=t1.TafsiliID)
	left join ACC_tafsilis t2 on(t2.TafsiliType=1 AND di.TafsiliID2=t2.TafsiliID)
	where CycleID=:year 
		AND CostID = " . $GharzolhasaneCostID . " 
		AND (t1.ObjectID=:pid or t2.ObjectID=:pid) 
		/*AND DocStatus in('CONFIRM','ARCHIVE')*/
	group by CostID
	order by CostID
", array(":year" => $CurYear, ":pid" => $_SESSION["USER"]["PersonID"]));

$TotalAmount = count($temp) > 0 ? $temp[0]["amount"] : 0;

//------------------------------------------------------------------------------

$dg = new sadaf_datagrid("dg","global/global.data.php?task=AccDocFlow&CostID=" . $GharzolhasaneCostID, "");

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

