<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.06
//---------------------------
require_once '../../../header.inc.php';
require_once("../data/person.data.php");
require_once inc_dataGrid;
require_once inc_dataReader;

require_once '../js/SalaryPersonReceipt.js.php';

$qry = " select PersonID from HRM_persons where RefPersonID = ? " ; 
$res = PdoDataAccess::runquery($qry,array($_SESSION["USER"]["PersonID"])) ;
 
$PersonID = $res[0]['PersonID'] ;
   

$dg = new sadaf_datagrid("salaryreceipt",$js_prefix_address . "../data/person.data.php?task=selectSalaryReceipt&Q0=".$PersonID,"ReceiptGRID");

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
$col->renderer = "PersonSalaryReceipt.opRender";
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
    
   PersonSalaryReceipt.prototype.afterLoad = function()
{
	this.grid = <?= $grid?>;
	this.grid.render(this.get("ReceiptGRID"));

	this.PersonID= <?= $PersonID?>;
}

var PersonSalaryReceiptObject = new PersonSalaryReceipt();
    
</script>
<center>
    <div id="form_PersonReceipt">
	<div id="ReceiptGRID"></div>
    </div>
    <div id="SalaryPrintWindow" class="x-hidden"></div>
</center>
