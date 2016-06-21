<?php
//---------------------------
// programmer:	B.Mahdipour
// create Date:	90.08
//---------------------------
require_once '../../../header.inc.php';
require_once inc_dataReader;
require_once inc_dataGrid;
require_once inc_PDODataAccess;
require_once '../js/person_dependents_transfer.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "../data/dependent.data.php?task=SelectPersonDependents" , "personResultDIV", "form_SearchPersonDep");

$col = $dg->addColumn("<input type=checkbox onclick=\"SearchDepPersonObject.selectAll(this);\">", "");
$col->renderer = "DepTransfer.CheckRender";
$col->width = 80;
$col->sortable = false;

$dg->addColumn('', "PersonID", "int", true);
$col = $dg->addColumn("شماره شناسایی", "staff_id");
$col->width = 100;

$col = $dg->addColumn("نام شخص", "full_name");
$col->width = 200;

$col = $dg->addColumn("مرکز هزینه", "title");

$dg->width = 780;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->autoExpandColumn = "title";
$dg->notRender = true;

$grid = $dg->makeGrid_returnObjects();

?>

<script>
	DepTransfer.prototype.afterLoad = function()
	{
		this.grid = <?= $grid?>;		
	}

	var SearchDepPersonObject = new DepTransfer();
</script>

<form id="form_SearchPersonDep" method="POST">
<center>
<div>
	<div id="advanceSearchDIV">
		<div id="advanceSearchPNL" style="padding: 5px" >
			<table id="searchTBL" style="width:100%">
				<tr>
					<td>شماره شناسایی از :</td>
					<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="from_StaffID" name="from_StaffID"></td>
					<td>تا :</td>
					<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="to_StaffID" name="to_StaffID"></td>
				</tr>
				<tr>
					<td>نام :</td>
					<td><input type="text" class="x-form-text x-form-field" style="width: 90%" id="pfname" name="pfname"></td>
					<td>نام خانوادگی :</td>
					<td><input type="text" class="x-form-text x-form-field" style="width: 90%" id="plname" name="plname"></td>
				</tr>				
				
			</table>
		</div>
	</div>
</div>
<div>
		<div id="possibleDeps" style="display: none">
			<br>
			<font style="color: red;font-size: 12pt;font-weight: bold ; font-family: BNazanin">
			براي دريافت سوابق کفالت بستگان اشخاص از سيستم کارگزيني ، افراد مورد نظر را از ليست زير انتخاب نموده و بر روي دکمه زير کليک کنيد.
			</font><br><br>
			<input type="button" value="دریافت" class="big_button" onclick="SearchDepPersonObject.tranfering();"><br>
			<br><div id="personResultDIV" ></div>
		</div>
</div>
</form>

</center>

