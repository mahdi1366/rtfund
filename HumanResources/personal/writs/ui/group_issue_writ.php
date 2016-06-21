<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ.class.php';
require_once inc_dataReader;
require_once inc_manage_unit;

$chk_emp_state = manage_domains::CHK_employee_states("emp_state",array(),3);

require_once '../js/group_issue_writ.js.php';

?>
<form id="form_groupIssueWrit">
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
				<td colspan="4">
					<div id="FS_empState">
						<div id="DIV_empState"><?= $chk_emp_state ?></div>
					</div>
				</td>
			</tr>
			<tr>
				<td>شماره شناسایی از :</td>
				<td><input type="text" name="from_PersonID" id="from_PersonID"></td>
				<td>تا :</td>
				<td><input type="text" name="to_PersonID" id="to_PersonID" ></td>
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
				<td>شماره شروع دبیرخانه :</td>
				<td><input type="text" name="send_letter_no" id="send_letter_no" class="x-form-text x-form-field"></td>
				<td>قدم های افزایش :</td>
				<td><input type="text" name="step" id="step" class="x-form-text x-form-field"></td>
			</tr>
			<tr>
				<td>شماره انتهای دبیرخانه :</td>
				<td><input type="text" name="to_send_letter_no" id="to_send_letter_no" class="x-form-text x-form-field"></td>
				<td>تعداد خطاي منجر به توقف :</td>
				<td><input type="text" name="stop_error_count" id="stop_error_count" class="x-form-text x-form-field"></td>
			</tr>
			<tr>
				<td>تاریخ صدور :</td>
				<td><input type="text" name="issue_date" id="issue_date" style="width:104px"></td>
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