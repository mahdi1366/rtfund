<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.04.11
//---------------------------
require_once("../../../header.inc.php");
require_once '../js/create_new_staff.js.php';

?>

<form method="post" id="form_newStaff">
	<center>
		<br>
		<div id="newStaff_DIV" style="width: 750px">
			<table id="newStaff_TBL" style="width: 100%">
				<tr>
					<td width="10%"  >انتخاب فرد :</td>
					<td width="90%" colspan="3"><br>
						<input type="hidden" name="person_type" id="person_type">
						<input type="hidden" name="staff_id" id="staff_id">
						<input type="hidden" name="personid" id="personid">
					</td>
				</tr>
				<tr>
					<td colspan="4">
                    <font color="red">
							* با استفاده از این ماژول می توانید وضعیت استخدامی یک فرد را تغییر دهید .
						</font>
					</td>
				</tr>
                
                <tr>
					<td colspan="4">
						
					</td>
				</tr>
			</table>
		</div>
       
	</center>
</form>


