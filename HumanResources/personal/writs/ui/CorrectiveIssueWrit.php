<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ.class.php';
require_once inc_dataReader;

//$baseWritTypeArr = manage_domains::DRP_writType_writSubType("form_correctiveIssueWrit", "base_writ_type_id", "base_writ_subtype_id");
//$WritTypeArr = manage_domains::DRP_writType_writSubType("form_correctiveIssueWrit", "writ_type_id", "writ_subtype_id");
require_once '../js/CorrectiveIssueWrit.js.php';
?>
<form method="post" id="form_correctiveIssueWrit">
<center>
<br><div id="errorDiv_correctiveWrit" style="width:600px"></div><br>
<div id="CorrectiveWrit_DIV" style="width: 600px">
		<table id="CorrectiveWrit_TBL" style="width: 100%; padding-right:10px">
			<tr>
				<td width="15%"  ><br>انتخاب فرد :</td>
				<td colspan="3"><br>
					<input type="text" id="PID">
					<input type="hidden" name="person_type" id="person_type">
                    <input type="hidden" name="staff_id" id="staff_id">
				</td>
			</tr>
			<tr>
				<td height="21px" >تاريخ شروع اصلاح :</td>
				<td width="25%"><input type="text" name="corrective_date" id="corrective_date" size="12px"></td>
				<td  colspan="2">
					<input type="checkbox" name="base_writ_issue" id="base_writ_issue" value="1"> صدور حکم پایه ؟</td>
			</tr>
			<tr>
				<td colspan="4" height="21px" ><font color="green">
				در صورتي كه مي خواهيد يك حكم در تاريخ گذشته صادر و حكم هاي پس از آن را اصلاح نمائيد از گزينه صدور حكم پايه استفاده نمائيد .
				</font></td>
			</tr>
		<tr>
			<td colspan="4" style="padding-right:10px">
				<div id="BaseWritSet">
					<table width="80%" id="basewritTbl">
						<tr>
							<td width="32%" >نوع حکم پايه:</td>
							<td width="68%">
								<input type="text" id="base_writ_type_id">
							</td>
						</tr>
						<tr>
							<td colspan="1" width="32%">&nbsp;</td>
							<td width="68%">
								<input type="text" id="base_writ_subtype_id">
							</td>
						</tr>
						<tr>
							<td>تاريخ اجرا حکم پايه:</td>
							<td><input type="text" name="base_execute_date" id="base_execute_date" size="12px" ></td>
						</tr>
						<tr>
							<td>تاريخ صدور حکم پايه: </td>
							<td><input type="text" name="base_issue_date" id="base_issue_date" size="12px" ></td>
						</tr>
						<tr>
							<td width="32%">شماره دبيرخانه حکم پايه : </td>
							<td width="68%"><input type="text" name="base_send_letter_no" id="base_send_letter_no" class="x-form-text"></td>
						</tr>
						<tr id= "prof1" style="display:none">
							<td>پایه :</td>
							<td><input type="text" name="base_base" id="base_base" size="8px"  class="x-form-text"></td>
						</tr>
					</table>
				</div>

				<div id="CWritSet">
					<table width="80%" id="CWritTbl">
				<tr>
					<td width="32%">نوع حکم :</td>
					<td>
						<input type="text" id="writ_type_id">
					</td>
				</tr>
				<tr>
					<td width="32%" colspan="1">&nbsp;</td>
					<td>
						<input type="text" id="writ_subtype_id">
					</td>
				</tr>
				<tr>
					<td>تاريخ اجرا(خاتمه اصلاح) :</td>
					<td id="tdCorr"><input type="text" name="execute_date" id="execute_date" size="12px" ></td>
				</tr>
				<tr>
					<td>تاريخ صدور : </td>
					<td><input type="text" name="issue_date" id="issue_date" size="12px" ></td>
				</tr>
				<tr>
					<td>شماره دبيرخانه :</td>
					<td><input type="text" name="send_letter_no" id="send_letter_no" class="x-form-text"></td>
				</tr>

				<tr id= "prof2" style="display:none">
					<td>پایه :</td>
					<td><input type="text" name="base" id="base" size="8px" class="x-form-text"></td>
				</tr>
			</table>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="4">
			<font color="red">* در صورت صدور حکم پايه اصلاح از اولين حکم بعد از حکم پايه شروع مي شود و نيازي به ثبت تاريخ شروع اصلاح نمي باشد .</font>
			<br>&nbsp;
			</td>
		</tr>
	</table>
	</div>
</center>
</form>