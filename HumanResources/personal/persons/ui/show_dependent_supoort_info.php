<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.10
//---------------------------
require_once '../../../header.inc.php';
require_once '../data/person.data.php';
require_once inc_dataGrid;

$sigh = new sadaf_datagrid("sinfo", $js_prefix_address . "../data/dependent.data.php?task=selectDepSupport&info=1&PID=".$_POST["PersonID"]."&row_no=".$_POST["row_no"],"depInfoGRID");

$sigh->addColumn("شماره پرسنلی","PersonID","int",true);
$sigh->addColumn("","master_row_no","int",true);

$col = $sigh->addColumn("ردیف","row_no", "string");
$col->width = 50;

$col = $sigh->addColumn("دلیل کفالت", "support_cause_title", GridColumn::ColumnType_string);

$col = $sigh->addColumn("نوع بیمه", "insure_type_title", "string");
$col->width = 100;

$col = $sigh->addColumn("از تاریخ", "from_date", GridColumn::ColumnType_date);
$col->width = 80;

$col = $sigh->addColumn("تا تاریخ", "to_date", GridColumn::ColumnType_date);
$col->width = 80;

$col = $sigh->addColumn("تاریخ محاسبه از", "start_calc", "string");
$col->width = 100;

$col = $sigh->addColumn("تاریخ محاسبه تا", "end_calc", "string");
$col->width = 100;

$col = $sigh->addColumn("وضعیت", "status_title", "string");
$col->width = 80;

$sigh->EnableSearch = false;
$sigh->height = 300;
$sigh->width = 750;
$sigh->autoExpandColumn = "support_cause_title";
$sigh->DefaultSortField = "from_date";
$sigh->EnablePaging = false ;
$sigh->DefaultSortField = "row_no";
$sigh->DefaultSortDir = "ASC";
$sigh->notRender = true ;

$gridSupportinfo = $sigh->makeGrid_returnObjects();
?>
<script>

	
		PersonDependencyObject.supportInfoGrid = <?= $gridSupportinfo ?> ;
		PersonDependencyObject.supportInfoGrid.render(PersonDependencyObject.get("depInfoGRID"));
    
        PersonDependencyObject.showWinInfo.center();

</script>
<div id="depInfoGRID"></div>