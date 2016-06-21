<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.12
//---------------------------

require_once '../../../header.inc.php';
require_once inc_dataGrid;

require_once '../js/writ_type.js.php';

$dg = new sadaf_datagrid("WGrid", $js_prefix_address . "../data/writ_type.data.php?task=SelectWritTypes", "ItemResDIV");
//$col= $dg->addColumn(" ", "valid","int",true);

$col= $dg->addColumn("کد ","writ_type_id","int");
$col->width = 50;
	
$col = $dg->addColumn("نیروی انسانی", "PTitle", "string");
$col->width = 100;

$col = $dg->addColumn("عنوان اصلی حکم", "writTitle", "string");
$col->width = 150;

$col = $dg->addColumn("عنوان فرعی حکم", "writSubTitle", "string");
//$col->ellipsis = 200 ; 

/*$col = $dg->addColumn("اثر", "effectTitle", "string");
$col->width = 90;

$col = $dg->addColumn("بیمه", "insure_include_title", "string");
$col->width = 50;

$col = $dg->addColumn("مالیات", "tax_include_title", "string");
$col->width = 50;

$col = $dg->addColumn("بازنشستگی", "retired_include_title", "string");
$col->width = 70;

$col = $dg->addColumn("مقرری", "pension_include_title", "string");
$col->width = 50;

$col = $dg->addColumn("غیرخودکار", "user_data_entry_title", "string");
$col->width = 60;

$col = $dg->addColumn("نوع محاسبه", "salary_compute_type_title", "string");
$col->width = 100;
*/
$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){return SalaryItemType.opRender(v,p,r);}";
$col->width = 50;

	$dg->addButton = true;
	$dg->addHandler = "function(){SalaryItemTypeObject.AddSit();}";

$dg->pageSize = "15";
$dg->width = 650;

$dg->title = "انواع احکام";
$dg->EnableRowNumber = true ;
$dg->autoExpandColumn = "writSubTitle";
$dg->DefaultSortField = "PTitle";

$grid = $dg->makeGrid_returnObjects();
?>
<style>
      .VioletRow td, .VioletRow div { background-color:#FFC !important; }
</style>
<script>
	SalaryItemType.prototype.afterLoad = function()
	{
		this.grid = <?= $grid?>;
		
		this.grid.getView().getRowClass = function(record,index)
                                        { 
                                           if(record.data.valid == 1 ){  return "VioletRow"; };
                                           return "";
                                        }
		this.grid.render("ItemResDIV");
	}

	var SalaryItemTypeObject = new SalaryItemType();
</script>
<form id="form_SalaryItemTypes" method="POST">
<center>
	<div id="mainpanel"></div>
	<br>
	<div id="ItemResDIV" style="width:100%"></div>
</center>
</form>
