<?php
//---------------------------
// programmer:	b.Mahdipour
// Date:		93.07
//---------------------------
require_once '../../../header.inc.php';
require_once inc_dataReader;
require_once inc_manage_unit;

$drp_personTypes = manage_domains::DRP_PersonType("PTyp", "", "width:90");
$drp_month = manage_domains::DRP_months("tax_n_m");

require_once '../js/arrear_payment_process.js.php';
?>
<form method="post" id="mainForm">
	<center>
		<div>
		<br>
		<div id="issuePayment_DIV" style="width: 750px">
			<table id="issuePayment_TBL" style="width: 100%">
				<tr>
					<td width="20%">واحد محل خدمت :</td>
					<td width="30%"><input type="text" id="ouid" name="ouid"></td>
					<td width="20%"></td>
					<td width="30%"></td>
				</tr>
				<tr>
					<td>نوع افراد :</td>
					<td><?= $drp_personTypes ?></td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td>شماره شناسایی از :</td>
					<td><input type="text" name="from_staff_id" id="from_staff_id"></td>
					<td>تا :</td>
					<td><input type="text" name="to_staff_id" id="to_staff_id" ></td>
				</tr>
				<tr>
					<td> سال :</td>
					<td colspan="3" height="25px" ><input type="text" name="pay_year" id="pay_year" class="x-form-field x-form-text"></td>					
					<td>&nbsp;</td><td>&nbsp;</td>
				</tr>
				
				<!--<tr>
					<td>شروع تعديل ماليات از سال:</td>
					<td><input type="text" name="tax_normalized_year" id="tax_normalized_year" class="x-form-text x-form-field" ></td>
					<td>شروع تعديل ماليات از ماه:</td>
					<td><?= $drp_month ?></td>
				</tr>
				<tr>					
					<td colspan="4">
						<input type="checkbox" name="tax_normalize" id="tax_normalize" value ="1" checked >
						تعديل ماليات انجام شود؟
					</td>
				</tr>-->
				
				<tr>
					<td>پيام :</td>
					<td colspan="3"><textarea id="message" name="message" class="x-form-field" style="width:90%"></textarea></td>
				</tr>
			</table>
		</div>
		</div>
		<!-- ------------------------------------------------------------ -->
		<div align="right" class="panel" id="result" style="display:none;width: 750px;">
			<br>
			<img src="img/loading.gif" id="img_loading" style="vertical-align: middle">
				<font style="font-size:'10px';font-weight: bold" color="#194775" > &nbsp;&nbsp;&nbsp;
				محاسبه حقوق <br>
				<hr size="3" width="50%" noshade align="right" style="color:#66A3E0" >				
				</font>
				
				<div id="result_data"></div>
		</div>
		
		
		<!-- ------------------------------------------------------------ -->
	</center>
</form>
</body>
</html>		