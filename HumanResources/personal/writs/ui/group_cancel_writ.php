<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.04
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ.class.php';
require_once inc_dataReader;
require_once inc_manage_unit;

$drp_personTypes = manage_domains::DRP_PersonType("pt","","width:90", "انتخاب نوع فرد");

require_once '../js/group_cancel_writ.js.php';
?>
<form method="post" id="form_groupCancelWrit">
	<center>
	<br>
<div>
	<div id="newWrit_DIV" style="width: 750px">
		<table id="newWrit_TBL" style="width: 100%">
			<tr>
                <td>واحد محل خدمت :</td>
                <td colspan="3">
                <input type="text" id="ouid" name="ouid">
                </td>
            </tr>
			<tr>
				<td>شماره شناسایی از :</td>
				<td><input type="text" name="from_PersonID" id="from_PersonID"></td>
				<td>تا :</td>
				<td><input type="text" name="to_PersonID" id="to_PersonID" ></td>
			</tr>
			<tr>
				<td>نوع افراد :</td>
				<td colspan="3"><?= $drp_personTypes ?></td>

			</tr>
			<tr>
				<td>نوع حکم :</td>
				<td colspan="3"><input type="text" id="writ_type_id"></td>
			</tr>
			<tr>
				<td>نوع فرعی حکم :</td>
				<td colspan="3"><input type="text" id="writ_subtype_id"></td>
			</tr>
			<tr>
				<td>تاریخ صدور از :</td>
				<td><input type="text" name="from_issue_date" id="from_issue_date" style="width:104"></td>
				<td>تا :</td>
				<td><input type="text" name="to_issue_date" id="to_issue_date" style="width:104"></td>
			</tr>
			<tr>
				<td>تاریخ اجرا :</td>
				<td><input type="text" name="execute_date" id="execute_date" style="width:104px"></td>
			</tr>
		</table>
	</div>
</div>
	<!-- ------------------------------------------------------------ -->
	<div id="result" class="panel" style="width: 750px" align="right"></div>
	<!-- ------------------------------------------------------------ -->
	</center>
</form>
