<?php
//---------------------------
// developer:	Sh.Jafarkhani
// Date:		94.06
//---------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dg_cost = new sadaf_datagrid('cost',$js_prefix_address."baseinfo.data.php?task=SelectCostCode",'divCost');

$dg_cost->addcolumn('','CostID',GridColumn::ColumnType_int,true);
$dg_cost->addcolumn('','CostCode',GridColumn::ColumnType_int,true);
$dg_cost->addcolumn('','level1',GridColumn::ColumnType_int,true);
$dg_cost->addcolumn('','level2',GridColumn::ColumnType_int,true);
$dg_cost->addcolumn('','level3',GridColumn::ColumnType_int,true);

$col = $dg_cost->addcolumn('کد حساب','CostCode');

$col = $dg_cost->addcolumn("گروه حساب", "LevelTitle1");
$col->width = 200;

$col = $dg_cost->addcolumn("حساب کل", "LevelTitle2");
$col->width = 200;

$col = $dg_cost->addcolumn("معین", "LevelTitle3");
$col->width = 200;

if($accessObj->RemoveFlag)
{
	$col=$dg_cost->addcolumn('حذف','','string');
	$col->renderer = "CostCode.RemoveCost";
	$col->width=50;
}

if($accessObj->AddFlag)
{
	$dg_cost->addButton = true;
	$dg_cost->addHandler = 'function(){return CostCodeObj.AddCost();}';
}
$dg_cost->addButton('prn__btn', 'چاپ کد حسابها', 'print', 'function(e){ return CostCodeObj.PrintCost(); }');

$dg_cost->title = 'کدینگ حساب';
$dg_cost->width = 870;
$dg_cost->pageSize = 19;
$dg_cost->height=620;
$dg_cost->autoExpandColumn = "CostCode";
$dgCost = $dg_cost->makeGrid_returnObjects();

require_once 'CostCode.js.php';

?>

<script type="text/javascript" >
	
	CostCode.prototype.afterLoad=function(){
		
		this.grid=<?= $dgCost?>;
		this.grid.render(this.get("divCost"));
	}
	
	var CostCodeObj = new CostCode();
	
</script>
<center>
	<div><div id="mainform"></div>
	<br></br>
	</div>
	<div id="divCost"></div>
</center>
