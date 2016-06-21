<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.06
//---------------------------
require_once '../../header.inc.php';
require_once '../data/report.data.php';

require_once inc_dataGrid;

require_once '../js/columns.js.php';
?>

<div id="div_tree"></div>

<form id="form_info">

	<div id="newTableWin" class="x-hidden">
		<div id="newTablePnl">
			<table width="100%" style="background-color:white">
				<tr>
					<td>نام جدول :</td>
					<td><input type="text" class="x-form-text x-form-field" id="table_name" name="table_name" style="direction:ltr;width:90%"></td>
				</tr>
				<tr>
					<td>توضیحات :</td>
					<td><textarea type="text" class="x-form-field" rows="3" id="description" name="description" style="width:90%"></textarea></td>
				</tr>
			</table>
		</div>
	</div>

</form>

<select id="search_mode">
	<option value="INT">INT</option>
	<option value="TEXT">TEXT</option>
	<option value="DATE">DATE</option>
	<option value="SELECT">SELECT</option>
	<option value="CHECK">CHECK</option>
</select>