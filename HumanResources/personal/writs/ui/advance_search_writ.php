<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ.class.php';
require_once inc_dataReader;
require_once inc_dataGrid;
require_once inc_PDODataAccess;
require_once $address_prefix . "/HumanResources/organization/org_units/unit.class.php";

//$unitArr = manage_domains::DRP_Unit_SubUnit("" ,"org_unit", "org_sub_unit", "", "" ,"" , "همه" ,"335" );
//$writTypeArr = manage_domains::DRP_writType_writSubType("" ,"writ_type", "writsubtype", "", "" ,"" , "همه" ,"280" );
 //$drp_staff_groups = manage_domains::DRP_staff_groups("staff_group_id","","همه موارد", "style='width:52%'");
$chk_emp_state = manage_domains::CHK_employee_states("emp_state",array(),5);
$chk_emp_mod = manage_domains::CHK_employee_modes("emp_mod",array(),5);
$drp_work_time_type = manage_domains::DRP_WorkTimeType("worktime_type","","همه موارد", "style='width:52%'");  

?>
<table style="width:100%" id="searchTBL">
	<tr>
		<td width="15%">شماره حکم از :</td>
		<td width="40%"><input type="text" class="x-form-text x-form-field" style="width: 40%" id="from_WID" name="from_WID"></td>
		<td width="10%">تا :</td>
		<td width="35%"><input type="text" class="x-form-text x-form-field" style="width: 40%" id="to_WID" name="to_WID"></td>
	</tr>
	
	<tr>
		<td>نوع حکم :</td>
		<td colspan="3">
			<input type="text" id="writ_type_id">
		</td>
	</tr>
	<tr>
		<td>نوع فرعی حکم :</td>
		<td colspan="3">
			<input type="text" id="writ_subtype_id">
		</td>
	</tr>
	<tr>
		<td> شماره پرسنلی از:</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="from_PersonID" name="from_PersonID"></td>
		<td>تا :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="to_PersonID" name="to_PersonID"></td>
	</tr>
	<tr>
		<td> شماره شناسایی از:</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="from_staff_id" name="from_staff_id"></td>
		<td>تا :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="to_staff_id" name="to_staff_id"></td>
	</tr>
	<tr>
		<td>نام :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 70%" id="pfname" name="pfname"></td>
		<td>نام خانوادگی :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 90%" id="plname" name="plname"></td>
	</tr>
	<tr>
		<td>واحد محل خدمت :</td>
		<td colspan="3">
			<input type="text" id="ouid">
		</td>
	</tr>
	
	<tr>
		<td colspan="4" valign="top" align="center">
			<div id="FS_emp_state" style="width:98%"><div id="FS_emp_state2">
				<?= $chk_emp_state ?>
			</div></div>
		</td>
	</tr>
	<tr>
		<td colspan="4" valign="top" align="center">
			<div id="FS_emp_mod" style="width:98%"><div id="FS_emp_mod2">
				<?= $chk_emp_mod ?>
			</div></div>
		</td>
	</tr>	
	<tr>
	</tr>	
	<tr>
		<td>زمان کاری:</td>
		<td colspan="3"><?= $drp_work_time_type ?></td>
	</tr>	
	<tr>
		<td> شناسه پست از:</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 40%" id="from_post_id" name="from_post_id"></td>
		<td>تا :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 40%" id="to_post_id" name="to_post_id"></td>
	</tr>
	<tr>
		<td>شماره نامه مرجع از :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 40%" id="from_ref_letter_no" name="from_ref_letter_no"></td>
		<td>تا :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 40%" id="to_ref_letter_no" name="to_ref_letter_no"></td>
	</tr>
	<tr>
		<td>تاريخ نامه مرجع از :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="from_ref_letter_date" name="from_ref_letter_date"></td>
		<td>تا :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="to_ref_letter_date" name="to_ref_letter_date"></td>
	</tr>
	<tr>
		<td>شماره نامه دبيرخانه از : </td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 40%" id="from_send_letter_no" name="from_send_letter_no"></td>
		<td>تا :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 40%" id="to_send_letter_no" name="to_send_letter_no"></td>
	</tr>
	<tr>
		<td>تاريخ نامه ابلاغ از : </td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="from_send_letter_date" name="from_send_letter_date"></td>
		<td>تا :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="to_send_letter_date" name="to_send_letter_date"></td>
	</tr>
	<tr>
		<td>تاريخ صدور از : </td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="from_issue_date" name="from_issue_date"></td>
		<td>تا :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="to_issue_date" name="to_issue_date"></td>
	</tr>	
	<tr>
		<td>تاريخ اجرا از : </td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="from_execute_date" name="from_execute_date"></td>
		<td>تا :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="to_execute_date" name="to_execute_date"></td>
	</tr>	
	<tr>
		<td>تاريخ پرداخت از : </td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="from_pay_date" name="from_pay_date"></td>
		<td>تا :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="to_pay_date" name="to_pay_date"></td>
	</tr>	
	<tr>
		<td>از گروه : </td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="from_cur_group" name="from_cur_group"></td>
		<td>تا :</td>
		<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="to_cur_group" name="to_cur_group"></td>
	</tr>	
	<tr>
		<td>نمايش آخرين حکم ؟ </td>
		<td>	
			<input type="checkbox" value="1" id="last_writ_view" name="last_writ_view" class="x-form-text x-form-field" style="width: 10px" >
		</td>	
		<td colspan="2">عدم امكان ارسال به حقوق ؟
		<input type="checkbox" value="1" id="dont_transfer" name="dont_transfer" class="x-form-text x-form-field" style="width: 10px" >
		</td>
	</tr>
	
</table>

