<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.03
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ.class.php';
require_once inc_dataReader;
require_once inc_dataGrid;

$returnFlag = isset($_REQUEST["returnMode"]) ? true : false;
$action_title = "";

require_once '../js/advance_search_writ.js.php';
require_once '../js/writ_transfer.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "../data/writ.data.php?task=selectPossibleTransferWrits" .
	($returnFlag ? "&return=true" : ""), "result", "form_search");

$col = $dg->addColumn("<input type=checkbox onclick=\"TransferWritObject.selectAll(this);\">", "");
$col->renderer = "TransferWrit.CheckRender";
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

$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->notRender = true;
$dg->autoExpandColumn = "wt_title";
$dg->width = "780";
$dg->EnableRowNumber = false;
$grid = $dg->makeGrid_returnObjects();

if(HRSystem == PersonalSystem)
	$action_title = $returnFlag ? "برگشت احکام" : "انتقال احکام";
else
	$action_title = $returnFlag ? "برگشت احکام" : "دریافت احکام";
?>
<script>
	TransferWrit.prototype.afterLoad = function()
	{
		this.grid = <?= $grid?>;
		this.new_state = '<?= HRSystem == PersonalSystem ? ($returnFlag ? WRIT_PERSONAL : WRIT_MIDDLE) :
			($returnFlag ? WRIT_MIDDLE : WRIT_SALARY) ?>';

       /* this.grid.getView().on('render', function(view) {
                                view.tip = Ext.create('Ext.tip.ToolTip', {
                                    target: view.el,
                                    delegate: view.itemSelector,
                                    trackMouse: true,
                                    width : 500,
                                    renderTo: 'result',
                                    listeners: {
                                        beforeshow: function updateTipBody(tip) {
                                            tip.update('شماره شناسایی :  ' + view.getRecord(tip.triggerElement).get('staff_id') + '<br> واحد محل خدمت :  ' +
                                                                            view.getRecord(tip.triggerElement).get('full_unit_title') );
                                        }
                                    }
                                });
                            });*/
	}

	var TransferWritObject = new TransferWrit();
	var advanceSearchObject;
</script>
<div id="form_TransferWrit">
	<center>
	<form id="form_search">
		<div id="AdvanceSearchDIV">
		<div id="AdvanceSearchPNL"></div>
	</div>
	</form>
	<!-- ------------------------------------------------------------ -->
	<form id="form_selectedWrits">
		<div id="possibleWrits" style="display: none">
			<br>
			<font style="color: red;font-size: 10pt;font-weight: bold">
			احكامي را كه مي خواهيد <?= $returnFlag ? "برگشت داده" : "منتقل"?>
			شود انتخاب نمائيد و در صورتي که از انجام اين عمل مطمئن هستيد بر روي دکمه زير کليک کنيد.
			</font><br><br>
			<input type="button" value="<?= $action_title ?>" class="big_button" onclick="TransferWritObject.tranfering();"><br>
			<br>
			<div align="right" id="result" style="width:780px"></div>
		</div>
	</form>
	<!-- ------------------------------------------------------------ -->
	</center>
</div>