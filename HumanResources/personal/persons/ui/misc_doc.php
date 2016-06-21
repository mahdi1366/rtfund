<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	88.06.15
//---------------------------
require_once '../../../header.inc.php';
require_once("../data/person.data.php");
require_once inc_dataGrid;

//________________  GET ACCESS  _________________
$accessObj = new ModuleAccess($_POST["FacilID"], SubModule_person_misc_doc);
//-----------------------------------------------

require_once '../js/misc_doc.js.php';

$personID = $_POST["Q0"];

$dg = new sadaf_datagrid("misc_doc", $js_prefix_address . "../data/misc_doc.data.php?task=selectMiscDoc&Q0=".$personID, "misc_docDIV");

$col = $dg->addColumn("ردیف", "row_no", "");
$col->width = 20;

$col = $dg->addColumn("شماره", "doc_no", "string");
$col->width = 60;

$col = $dg->addColumn("تاریخ", "doc_date", "date");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 80;

$col = $dg->addColumn("عنوان", "title", "string");

if($accessObj->UpdateAccess() || $accessObj->DeleteAccess())
{
	$col = $dg->addColumn("عملیات", "", "string");
	$col->renderer = "PersonMiscDoc.opRender";
	$col->width = 80;
}

$dg->height = 400;
$dg->width = 750;
$dg->DefaultSortField = "row_no";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "title";

$col = $dg->addColumn("", "comments", "string",true);

if($accessObj->InsertAccess())
{
	$dg->addButton = true;
	$dg->addHandler = "function(){PersonMiscDocObject.AddMiscDoc();}";
}
$dg->EnableSearch = false;
$grid = $dg->makeGrid_returnObjects();

?>
<script>

PersonMiscDoc.prototype.afterLoad = function()
{
	this.grid = <?= $grid?>;
	this.grid.render(this.get("misc_docDIV"));

	this.PersonID = <?= $personID?>;
}

var PersonMiscDocObject = new PersonMiscDoc();
</script>
<form id="form_PersonMiscDoc" method="post">
	<div id='MiscDocDIV' style="width:740px; display: none;" class="panel" >
	<input type='hidden' id='row_no' name='row_no' >
		<table width="100%" id="MiscDocTBL">
			<tr>
				<td width="15%">
				شماره :
				</td>
				<td width="25%">
				<input type="text" id="doc_no" name="doc_no" class="x-form-text x-form-field" style="width: 50%" >
				</td>
				<td width="15%">
				تاریخ:
				</td>
				<td width="25%">
				<input type="text" id="doc_date" name="doc_date" class="x-form-text x-form-field" style="width: 90px" >
				</td>
			</tr>
			<tr>
				<td>عنوان:</td>
				<td colspan="3"><input type="text" id="title" name="title" class="x-form-text x-form-field" style="width: 50%"></td>
			</tr>
			<tr>
				<td width="25%">
				توضیحات:
				</td>
				<td colspan="3">
				<textarea id="comments" name="comments" rows="5" class="x-form-field" style="width: 98%" >
				</textarea>
				</td>
			<tr>
			<tr><td colspan="4" >
				<br><hr  width="600px" style="color: #99BBE8 " align="left"></td></tr>
			<tr>
				<td></td>
				<td colspan="3" >
					<input type="button" class="button" onclick="PersonMiscDocObject.saveMiscDoc();" value="ذخیره">
					<input type="button" class="button" onclick="PersonMiscDocObject.cancel();" value="بازگشت">
				</td>
			</tr>

		</table>
	</div>
	<div id="misc_docDIV"></div>
</form>