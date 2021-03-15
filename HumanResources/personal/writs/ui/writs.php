<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------

require_once '../../../header.inc.php';
require_once inc_dataGrid;


$dg = new sadaf_datagrid("Wrt",$js_prefix_address . "../../writs/data/writ.data.php?task=selectWrt&Q0=".$_POST['Q0'],"WrtGRID");

$dg->addColumn("شماره پرسنلی","PersonID","int",true);

$col = $dg->addColumn("شماره حکم", "writ_id", "int");
$col->width = 70;

$col = $dg->addColumn("نسخه", "writ_ver", "int");
$col->width =50;

$col = $dg->addColumn("شماره شناسایی", "staff_id", "string",true);
$col->width = 70;

$col = $dg->addColumn("نوع حکم", "wt_title", "string");

$col = $dg->addColumn("وضعیت استخدامی", "emp_state_title", "string");
$col->width = 80;

$col = $dg->addColumn("تاریخ اجرا", "execute_date", "string");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 70;

// new added
$col = $dg->addColumn("پیوست", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return WritList.attachRender(v,p,r);}";
$col->width = 50;
// end new added

$col = $dg->addColumn("فقط ثبت سابقه؟", "history_only_title", "string");
$col->width = 70;

$col = $dg->addColumn("اصلاحی؟", "corrective_title", "string");
$col->width = 60;

$col = $dg->addColumn("شماره اصلاح", "corrective_writ_id", "int");
$col->width = 70;
/*
$col = $dg->addColumn("مبلغ", "sumValue", "int");
$col->width = 80;
*/
$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){return WritList.opRender(v,p,r);}";
$col->width = 50;

$dg->height = 500;
$dg->width = 780;
$dg->DefaultSortField = "execute_date";
$dg->DefaultSortDir = "DESC";
$dg->autoExpandColumn = "wt_title";
$dg->EnableRowNumber = true;
$grid = $dg->makeGrid_returnObjects();

require_once '../js/writs.js.php';
?>
<form id="form_WritList" method="POST">
<div id="WrtGRID"></div>
</form>