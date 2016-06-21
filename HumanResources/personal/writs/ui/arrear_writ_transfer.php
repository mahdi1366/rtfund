<?php
//---------------------------
// programmer:	B.Mahdipour
// create Date:	93.08
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ.class.php';
require_once inc_dataReader;
require_once inc_dataGrid;

$returnFlag = isset($_REQUEST["returnMode"]) ? true : false;
$action_title = "";

require_once '../js/advance_search_writ.js.php';
require_once '../js/arrear_writ_transfer.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "../data/writ.data.php?task=selectArrearTransferWrits" .
	($returnFlag ? "&return=true" : ""), "result", "formSearch");

$col = $dg->addColumn("<input type=checkbox onclick=\"ArrearTransferWritObject.selectAll(this);\">", "");
$col->renderer = "ArrearTransferWrit.CheckRender";
$col->width = 30;
$col->sortable = false;
$col->searchable = false;

$col = $dg->addColumn("شماره حکم", "writ_id");
$col->width = 50;

$col = $dg->addColumn("<span style=\"font-size:8px\">نسخه</span>", "writ_ver");
$col->width = 30;
$col = $dg->addColumn("نام و نام خانوادگي", "fullname");
$col->width = 100;

$dg->addColumn("شماره شناسايي", "staff_id","int",true);
$col = $dg->addColumn("عنوان کامل واحد محل خدمت", "full_unit_title", "string",true);
$col->renderer = "function(v){ return ' '; }" ;

$col = $dg->addColumn("نوع حکم", "wt_title");

$col = $dg->addColumn("وضعيت استخدامي", "emp_state_title");
$col->width = 80;

$col = $dg->addColumn("تاريخ اجرا", "execute_date");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 70;

$col = $dg->addColumn("اصلاحي؟", "corrective");
$col->renderer = "function(v){if(v == '0') return ''; else return 'بلی';}";
$col->width = 40;

$col = $dg->addColumn("شماره اصلاح", "corrective_writ_id");
$col->width = 60;

$col = $dg->addColumn("<span style=\"font-size:8px\">عملیات</span>", "", "string");
$col->renderer = "function(v,p,r){return ArrearTransferWrit.opRender(v,p,r);}";
$col->width = 40;

$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->notRender = true;
$dg->autoExpandColumn = "wt_title";
$dg->width = "780";
$dg->EnableRowNumber = false;
$grid = $dg->makeGrid_returnObjects();

$action_title = $returnFlag ? " برگشت احکام دیون " :  " دریافت احکام دیون";

?>
<script>
	ArrearTransferWrit.prototype.afterLoad = function()
	{
		this.grid = <?= $grid?>;
		this.new_state = '<?= ($returnFlag ? 0 : 1 ) ?>';       
	}

	var ArrearTransferWritObject = new ArrearTransferWrit();
	var advanceASearchObject;
</script>
<div id="form_ATransferWrit">
	<center>
	<form id="formSearch">
		<div id="AdvanceASearchDIV">
		<div id="AdvanceSearchPNL"></div>
	</div>
	</form>
	<!-- ------------------------------------------------------------ -->
	<form id="formSelectedWrits">
		<div id="possibleAWrits" style="display: none">
			<br>
			<font style="color: red;font-size: 10pt;font-weight: bold">
			<?= $returnFlag ? " لطفا احکام مورد نظر جهت برگشت را انتخاب نمایید" : "لطفا احکام مورد نظر جهت پرداخت دیون را انتخاب نمایید"?>
		و در صورتي که از انجام اين عمل مطمئن هستيد بر روي دکمه زير کليک کنيد. 
			</font><br><br>
			<input type="button" value="<?= $action_title ?>" class="big_button" onclick="ArrearTransferWritObject.tranfering();"><br>
			<br>
			<div align="right" id="result" style="width:780px"></div>
		</div>
	</form>
	<!-- ------------------------------------------------------------ -->
	</center>
</div>