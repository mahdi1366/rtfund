<?php
//---------------------------
// developer:	Sh.Jafarkhani
// Date:		98.06
//---------------------------
require_once '../header.inc.php'; 
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dg = new sadaf_datagrid('grid',$js_prefix_address."store.data.php?task=SelectAssets",'div_grid');

$dg->addcolumn('','IsActive',"",true);
$dg->addcolumn('','IsNew',"",true);
$dg->addcolumn('','CostID',"",true);
$dg->addcolumn('','CostCode',"",true);
$dg->addcolumn('','level1',"",true);
$dg->addcolumn('','level2',"",true);
$dg->addcolumn('','level3',"",true);
$dg->addcolumn('','TafsiliType1',"",true);
$dg->addcolumn('','TafsiliType2',"",true);
$dg->addcolumn('','TafsiliType3',"",true);
$dg->addcolumn('','IsBlockable',"",true);
$dg->addcolumn('','param1',"",true);
$dg->addcolumn('','param2',"",true);
$dg->addcolumn('','param3',"",true);

$col = $dg->addcolumn('کد حساب','CostCode');
$col->width = 60;

$col = $dg->addcolumn('گروه حساب','LevelTitle0');

$col = $dg->addcolumn("کل", "LevelTitle1");

$col = $dg->addcolumn("معین1", "LevelTitle2");

$col = $dg->addcolumn("معین2", "LevelTitle3");

$col = $dg->addcolumn("معین3", "LevelTitle4");

$col = $dg->addcolumn("تفصیلی1", "TafsiliTypeDesc1");

$col = $dg->addcolumn("تفصیلی2", "TafsiliTypeDesc2");

$col = $dg->addcolumn("تفصیلی3", "TafsiliTypeDesc3");

$col = $dg->addcolumn('آیتم اطلاعاتی1','ParamDesc1');

$col = $dg->addcolumn('آیتم اطلاعاتی2','ParamDesc2');

$col = $dg->addcolumn('آیتم اطلاعاتی3','ParamDesc3');

//$col = $dg->addcolumn('ObjectType1','ObjectType1');
$col = $dg->addcolumn('ObjectType2','ObjectType2');
//$col = $dg->addcolumn('ObjectType3','ObjectType3');


if($accessObj->EditFlag	)
{
	$col=$dg->addcolumn('ویرایش','','string');
	$col->renderer = "CostCode.EditCost";
	$col->width=40;
}
if($accessObj->RemoveFlag)
{
	$col=$dg->addcolumn('حذف','','string');
	$col->renderer = "CostCode.RemoveCost";
	$col->width=40;
}

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = 'function(){return CostCodeObj.BeforeSaveCost(false);}';
}

$dg->addButton('prn__btn', 'چاپ کد حسابها', 'print', 'function(e){ return CostCodeObj.PrintCost(); }');

$dg->title = 'کدینگ حساب';
$dg->pageSize = 19;
$dg->pageSize = 12;
//$dg->height = 500;
$dg->autoExpandColumn = "LevelTitle1";
$dg->DefaultSortField = "CostCode";
$dg->DefaultSortDir = "ASC";
$dgCost = $dg->makeGrid_returnObjects();

require_once 'CostCode.js.php';

?>

<script type="text/javascript" >
	
	CostCode.prototype.afterLoad=function(){
		
		this.grid=<?= $dgCost?>;
		this.grid.getView().getRowClass = function(record, index)
		{
			if(record.data.IsNew == "YES")
				return "yellowRow";
			if(record.data.IsActive == "NO")
				return "pinkRow";
		}	

		this.grid.render(this.get("divCost"));
	}
	
	var CostCodeObj = new CostCode();
	
</script>
<center>
	<div><div id="mainform"></div>
	<br>	
	</div>
	<div style='width:98%' id="div_grid"></div>
</center>
