<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.03
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ.class.php';
require_once inc_dataReader;
require_once inc_dataGrid;

require_once '../js/advance_search_writ.js.php';
require_once '../js/confirm_writ.js.php';


$dg = new sadaf_datagrid("dg", $js_prefix_address . "../data/writ.data.php?task=selectPossibleTransferWrits&view=1", "result", "form_search");

$col = $dg->addColumn("<input type=checkbox onclick=\"ConfirmWritObject.selectAll(this);\">تایید", "view_flag");
$col->renderer = "ConfirmWrit.CheckRender";
$col->width = 40;
$col->sortable = false;

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
$dg->EnableRowNumber = false;
$dg->width = "780";
$grid = $dg->makeGrid_returnObjects();

?>
<script>
	ConfirmWrit.prototype.afterLoad = function()
	{
		this.grid = <?= $grid?>;
        this.grid.getView().on('render', function(view) {
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
                            });
	}

	var ConfirmWritObject = new ConfirmWrit();
	var advanceSearchObject;
</script>
<div id="form_ConfirmWrit">
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
			<input type="button" value="تایید احکام برای مشاهده در پرتال" class="big_button" style="width:200!important"
				onclick="ConfirmWritObject.confirm();"><br>
			<br><div align="right" id="result" style="width:780px"></div>
		</div>
	</form>
	<!-- ------------------------------------------------------------ -->
	</center>
</div>