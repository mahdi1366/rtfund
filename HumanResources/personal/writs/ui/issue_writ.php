<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ.class.php';
require_once inc_dataReader;
ini_set("display_errors","Off") ; 

  //$WritTypeArr = manage_domains::DRP_writType_writSubType("form_issueWrit","writ_type_id", "writ_subtype_id", "" , "" , "" , "" , "200");

  $today = getdate();				        
  $current_date = DateModules::Miladi_to_Shamsi($today['year']."-".$today['mon']."-".$today['mday']); 
  list($year,$month,$day) = explode('/',$current_date);
  $start_date = $year . "/01/01";
  $end_date = $year . "/12/29";
  
require_once '../js/issue_writ.js.php';
?>

<form id="form_issueWrit" method="post">
		<center>
		<div id="errordiv_issueWrit" style="width: 600px"></div>
		<br>
		<div id="newWrit_DIV" style="width: 600px">
			<table id="newWrit_TBL" cellpadding="2">
				<tr>
					<td>انتخاب فرد :</td>
					<td>
					<input type="text" id="issueWrit_PID">
						<input type="hidden" name="staff_id" id="staff_id" >
						<input type="hidden" name="person_type" id="person_type">
						<input type="hidden" name="OrdinaryWrit" id="OrdinaryWrit">
					</td>
				</tr>
				<tr>
					<td>نوع اصلی حکم :</td>
				<td><select id="writ_type_id"></select></td>
				</tr>
				<tr>
					<td>نوع فرعی حکم :</td>
				<td><input type="text" id="writ_subtype_id"></td>
				</tr>
				<tr>
					<td>تاریخ صدور :</td>
					<td><input type="text" name="issue_date" id="issue_date"></td>
				</tr>
				<tr>
					<td>تاریخ اجرا :</td>
				<td id="td1"><input type="text" name="execute_date" id="execute_date"></td>
				</tr>
				    
				<tr id="tr_SD">
				  	<td>تاریخ شروع قرارداد :</td>
				  	<td><input type="text" name="contract_start_date" id="contract_start_date" value=<?= $start_date ?> ></td>
				</tr>
				<tr id="tr_ED">
				  	<td>تاریخ خاتمه قرارداد :</td>
				  	<td><input type="text" name="contract_end_date" id="contract_end_date" value=<?= $end_date ?> ></td>
				</tr>				
				<tr>
					<td style="height: 21px">فقط ثبت سابقه :</td>
					<td><input type="checkbox" name="history_only" id="history_only"></td>
				</tr>
			</table>
		</div>	
		</center>
	</form>
 