<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	88.07.20
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ.class.php';
require_once inc_dataReader;
require_once inc_dataGrid;
require_once inc_PDODataAccess;


$dg = new sadaf_datagrid("searchWritGrid", $js_prefix_address . "../data/writ.data.php?task=selectWrt", "WritResultDIV","form_SearchWrt");

$col= $dg->addColumn("شماره پرسنلی","PersonID","int",true);

$col = $dg->addColumn("شماره حکم", "writ_id", "int");
$col->width = 70;

$col = $dg->addColumn("<span style=\"font-size:8px\">نگارش حکم</span>", "writ_ver", "int");
$col->width = 70;

$col = $dg->addColumn("نام ونام خانوادگی", "fullname", "string");
$col->width = 100;

$col = $dg->addColumn(" شناسایی", "staff_id", "string");
$col->width = 80;


$col = $dg->addColumn("نوع حکم", "wt_title", "string");


$col = $dg->addColumn("تاریخ اجرا", "execute_date", "string");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 80;
   

$col = $dg->addColumn("  سابقه؟", "history_only_title", "string");
$col->width = 50;

$col = $dg->addColumn("<span style=\"font-size:8px\">اصلاحی</span>", "corrective_title", "string");
$col->width = 50;

$col = $dg->addColumn("شماره اصلاح", "corrective_writ_id", "int");
$col->width = 80;

$col = $dg->addColumn("مبلغ", "sumValue", "int");
$col->width = 80;

$col = $dg->addColumn("<span style=\"font-size:8px\">عملیات</span>", "", "string");
$col->renderer = "function(v,p,r){return SearchWrit.opRender(v,p,r);}";
$col->width = 40;

$dg->EnableSearch = false;
$dg->width = 900;
$dg->notRender = true;
$dg->pageSize = 10;
$dg->EnableRowNumber = true;
$dg->autoExpandColumn = "wt_title";
$dg->DefaultSortField = "execute_date";
$dg->DefaultSortDir = "desc";
$grid = $dg->makeGrid_returnObjects();

require_once '../js/advance_search_writ.js.php';
require_once '../js/search_writ.js.php';
?>
<style>
      .YellowRow td, .YellowRow div { background-color:#FFC !important; }
</style>
<form id="form_SearchWrt" method="POST">
<center>
<div>
	<div id="AdvanceSearchDIV">
		<div id="AdvanceSearchPNL"></div>
	</div>
</div>
<br>
	<div id="WritResultDIV" style="width:100%"></div>
</center>
</form>
