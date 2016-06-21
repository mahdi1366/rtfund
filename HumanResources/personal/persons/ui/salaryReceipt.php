<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.10.03
//---------------------------
require_once '../../../header.inc.php';
require_once("../data/person.data.php");
require_once inc_dataGrid;

require_once '../js/salaryreceipt.js.php';

$dg = new sadaf_datagrid("salaryreceipt",$js_prefix_address . "../data/person.data.php?task=selectSalaryReceipt&Q0=".$_POST['Q0'],"ReceiptGRID");

$dg->addColumn(" ", "pay_year", "int",true);
$dg->addColumn(" ", "pay_month", "int",true);
$dg->addColumn(" ", "payment_type", "int",true);

$col = $dg->addColumn("شماره شناسایی", "staff_id", "int");
$col->width = 100;

$col = $dg->addColumn("واحد محل خدمت", "full_unit_title", "string");

$col = $dg->addColumn("ماه", "pay_year_month", "string");
$col->width = 100;

$col = $dg->addColumn("جمع حقوق و مزایا", "pay_sum", "int");
$col->width = 100;

$col = $dg->addColumn("جمع کسور", "get_sum", "int");
$col->width = 100;

$col = $dg->addColumn("خالص پرداختی", "pure_pay", "int");
$col->width = 100;

$col = $dg->addColumn("چاپ فیش", "", "string");
$col->renderer = "PersonReceipt.opRender";
$col->width = 50;

$dg->height = 550;
$dg->width = 870;
$dg->EnableSearch = false;
$dg->pageSize = "12" ;
$dg->autoExpandColumn = "full_unit_title";

$dg->EnableRowNumber = true ;
$grid = $dg->makeGrid_returnObjects();

?>
<script>
PersonReceipt.prototype.afterLoad = function()
{
	this.grid = <?= $grid?>;
	this.grid.render(this.get("ReceiptGRID"));

	this.PersonID= <?= $_POST["Q0"]?>;
}

var PersonReceiptObject = new PersonReceipt();

</script>
<div id="form_PersonReceipt">
	<div id="ReceiptGRID"></div>
</div>
<div id="SalaryPrintWindow" class="x-hidden"></div>
