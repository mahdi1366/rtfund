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

$dg_cost = new sadaf_datagrid('cost',$js_prefix_address."baseinfo.data.php?task=SelectCostCode&All=true",'divCost');

$dg_cost->addcolumn('','IsActive',"",true);
$dg_cost->addcolumn('','CostID',"",true);
$dg_cost->addcolumn('','CostCode',"",true);
$dg_cost->addcolumn('','level1',"",true);
$dg_cost->addcolumn('','level2',"",true);
$dg_cost->addcolumn('','level3',"",true);
$dg_cost->addcolumn('','TafsiliType',"",true);
$dg_cost->addcolumn('','TafsiliType2',"",true);
$dg_cost->addcolumn('','IsBlockable',"",true);

$col = $dg_cost->addcolumn('گروه حساب','LevelTitle0');
$col->width = 120;

$col = $dg_cost->addcolumn('کد حساب','CostCode');
$col->width = 80;

$col = $dg_cost->addcolumn("کل", "LevelTitle1");

$col = $dg_cost->addcolumn("معین", "LevelTitle2");
$col->width = 120;

$col = $dg_cost->addcolumn("جزء معین", "LevelTitle3");
$col->width = 100;

$col = $dg_cost->addcolumn("گروه تفصیلی", "TafsiliTypeDesc");
$col->width = 100;

$col = $dg_cost->addcolumn("گروه تفصیلی2", "TafsiliTypeDesc2");
$col->width = 100;

if($accessObj->EditFlag	)
{
	$col=$dg_cost->addcolumn('ویرایش','','string');
	$col->renderer = "CostCode.EditCost";
	$col->width=40;
}
if($accessObj->RemoveFlag)
{
	$col=$dg_cost->addcolumn('حذف','','string');
	$col->renderer = "CostCode.RemoveCost";
	$col->width=40;
}

if($accessObj->AddFlag)
{
	$dg_cost->addButton = true;
	$dg_cost->addHandler = 'function(){return CostCodeObj.BeforeSaveCost(false);}';
}
$dg_cost->addButton('prn__btn', 'چاپ کد حسابها', 'print', 'function(e){ return CostCodeObj.PrintCost(); }');

$dg_cost->title = 'کدینگ حساب';
$dg_cost->width = 870;
$dg_cost->pageSize = 19;
$dg_cost->height = 580;
$dg_cost->autoExpandColumn = "LevelTitle1";
$dgCost = $dg_cost->makeGrid_returnObjects();

require_once 'CostCode.js.php';

?>

<script type="text/javascript" >
	
	CostCode.prototype.afterLoad=function(){
		
		this.grid=<?= $dgCost?>;
		this.grid.getView().getRowClass = function(record, index)
		{
			if(record.data.IsActive == "NO")
				return "pinkRow";
		}	

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
