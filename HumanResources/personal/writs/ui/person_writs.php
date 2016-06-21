<?php
//---------------------------
// programmer:	sh.Jafarkhani
// create Date:	90.10
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ.class.php';
require_once inc_dataReader;
require_once inc_dataGrid;
require_once inc_PDODataAccess;
?>

<?
//________________  GET ACCESS  _________________
$accessObj = new ModuleAccess($_POST["FacilID"]);
//-----------------------------------------------

$dg = new sadaf_datagrid("searchWritGrid", $js_prefix_address . "../data/writ.data.php?task=selectPersonWrt", "WritResultDIV","form_PSearchWrt");

$col= $dg->addColumn("شماره پرسنلی","PersonID","int",true);
$col= $dg->addColumn("روال اصلاح", "correct_completed","int",true);

$col= $dg->addColumn("شماره شناسایی","staff_id","int");
//$col->renderer = "function(v){return ' ' ;}";
$col->width = 60;

$col = $dg->addColumn("شماره حکم", "writ_id", "int");
$col->width = 60;

$col = $dg->addColumn("<span style=\"font-size:8px\">نسخه</span>", "writ_ver", "int");
$col->width = 30;

$col = $dg->addColumn("واحد محل خدمت", "parentTitle", "string",true);
$col->renderer = "function(v){return '';}";
//$col->width = 100;

$col = $dg->addColumn("نوع حکم", "wt_title", "string");

$col = $dg->addColumn("<span style=\"font-size:8px\">وضعیت استخدامی</span>", "emp_state_title", "string");
$col->width = 70;

$col = $dg->addColumn("تاریخ اجرا", "execute_date", "string");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 80;

    $col = $dg->addColumn("مرتبه علمی", "science_level_title", "string");
    $col->width = 50;

    $col = $dg->addColumn("پایه", "base", "int");
    $col->width = 40;

$col = $dg->addColumn("<span style=\"font-size:8px\">ثبت سابقه</span>", "history_only_title", "string",true);
$col->renderer = "function(v){return ' ' ;}";
//$col->width = 30;

$col = $dg->addColumn("<span style=\"font-size:8px\">اصلاحی</span>", "corrective_title", "string",true);
$col->renderer = "function(v){return ' ' ;}";
//$col->width = 40;

$col = $dg->addColumn("شماره اصلاح", "corrective_writ_id", "int");
$col->width = 70;

$col = $dg->addColumn("مبلغ", "sumValue", "int");
$col->width = 70;

$col = $dg->addColumn("<span style=\"font-size:8px\">عملیات</span>", "", "string");
$col->renderer = "function(v,p,r){return PersonWrits.opRender(v,p,r);}";
$col->width = 40;

$dg->EnableSearch = false;
$dg->notRender = true;
$dg->pageSize = 10;
$dg->width = "780";
$dg->EnableRowNumber = true;
$dg->DefaultSortField = "execute_date";
$dg->autoExpandColumn = "wt_title";
$dg->DefaultSortDir = "desc";
$grid = $dg->makeGrid_returnObjects();

require_once '../js/person_writs.js.php';

?>
<style>
      .YellowRow td, .YellowRow div { background-color:#FFC !important; }
</style>
<form id="form_PSearchWrt" method="POST">
<center>
<div>
	<div style="width:90%" id="selectPersonDIV"></div>
	<br>
</div>
<div id="PersonWritResultDIV" style="width:780px" align="right"></div>
</center>
</form>
