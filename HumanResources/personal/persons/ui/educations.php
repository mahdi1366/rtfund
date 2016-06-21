<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------
require_once '../../../header.inc.php';
require_once("../data/person.data.php");
require_once inc_dataGrid;

require_once '../js/educations.js.php';

$dg = new sadaf_datagrid("educ",$js_prefix_address . "../data/education.data.php?task=selectEduc&Q0=".$_POST['Q0'],"EducGRID");

$dg->addColumn("شماره پرسنلی","PersonID","int",true);
$dg->addColumn("کدمقطع","education_level","int",true);
$dg->addColumn("کد رشته","sfid","int",true);
$dg->addColumn("کد گرایش","sbid","int",true);
$dg->addColumn("کدکشور","country_id","int",true);
$dg->addColumn("کد دانشگاه","university_id","int",true);
$dg->addColumn("تاریخ شمسی","doc_date","string",true);
$dg->addColumn("تائید مدرک","certificated","int",true);
$dg->addColumn("بورس ","burse","int",true);
$dg->addColumn("معدل","grade","int",true);
$dg->addColumn("عنوان پایان نامه فارسی","thesis_ptitle","string",true);
$dg->addColumn("عنوان پایان نامه انگلیسی","thesis_etitle","string",true);
$dg->addColumn("","comments","string",true);

$col = $dg->addColumn("ردیف", "row_no", "int");
$col->width = 50;

$col = $dg->addColumn("مقطع", "education_level_title", "string");
$col->width = 80;

$col = $dg->addColumn("رشته", "sf_ptitle", "string");
$col->width = 140;

$col = $dg->addColumn("گرايش", "sb_ptitle", "string");
$col->width = 100;

$col = $dg->addColumn("دانشگاه", "u_ptitle", "string");

$col = $dg->addColumn("کشور", "c_ptitle", "string");
$col->width = 80;

$col = $dg->addColumn("تاريخ اخذ مدرک", "doc_date", "string");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 100;

$col = $dg->addColumn("بورس ؟", "burse_title", "string");
$col->width = 80;

$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "PersonEducation.opRender";
$col->width = 50;

$dg->height = 400;
$dg->width = 780;
$dg->DefaultSortField = "row_no";
$dg->DefaultSortDir = "ASC";
$dg->EnableSearch = false;
$dg->autoExpandColumn = "u_ptitle";

	$dg->addButton = true;
	$dg->addHandler = "function(){PersonEducationObject.AddEduc();}";

$grid = $dg->makeGrid_returnObjects();
//-----------------------------------------
$drp_educ = manage_domains::DRP_EducLevel("education_level", "","with:50%");

?>
<script>

PersonEducation.prototype.afterLoad = function()
{
	this.grid = <?= $grid?>;
	this.grid.render(this.get("EducGRID"));

	this.PersonID= <?= $_POST["Q0"]?>;

}
var PersonEducationObject = new PersonEducation();
</script>
<form id="form_PersonEducation"  method="post">

<div id='EducDIV' style="display: none;width:750px" class="panel" >
<input type='hidden' id='row_no' name='row_no' >
<table width="100%">
	<tr>
		<td width="25%">
		مقطع:
		</td>
		<td width="25%"><?= $drp_educ ?></td>
	</tr>
	
	<tr>
		<td width="30%">
         تاريخ شمسي اخذ مدرک  :
		</td>
		<td width="20%">
		<input type="text" id="doc_date" name="doc_date" class="x-form-text x-form-field" style="width: 80px" >
		</td>
		<td width="30%">
		تاريخ ميلادي اخذ مدرک :
		</td>
		<td width="20%">
		<input type="text" id="georgian_doc_date"  class="x-form-text x-form-field" style="width: 80px" >
		</td>
	</tr>
	
	<tr>
		<td width="25%">
		تاييديه مدرك ارائه شده ؟
		</td>
		<td width="25%" colspan="3">
		<input type="checkbox" value="1" id="certificated"  name="certificated" class="x-form-text x-form-field" style="width: 10px" >
		</td>
	</tr>
	
	<tr>
		<td width="25%">
		رشته : 
		</td>
		<td width="75%" colspan="3"><input type="text" id="sfid" ></td>
		
	</tr>
	
	<tr>
		<td width="25%">
		گرايش : 
		</td>
		<td width="75%" colspan="3"><input type="text" id="sbid"></td>
		
	</tr>
	
	<tr>
		<td width="25%">
		کشور:
		</td>
		<td width="75%" colspan="3"><input type="text" id="country_id"></td>
	<tr>
	<tr>
		<td width="25%">
		دانشگاه :
		</td>
		<td width="75%" colspan="3"><input type="text" id="university_id"></td>
		
	</tr>
	<tr>
		<td width="25%">
         معدل :
		</td>
		<td width="25%">
		<input type="text" id="grade" name="grade" class="x-form-text x-form-field" style="width: 100px" >
		</td>
		<td width="25%">
		بورس وزارتخانه؟
		</td>
		<td width="25%">
		<input type="checkbox" value="1" id="burse"  name="burse" class="x-form-text x-form-field" style="width: 10px" >
		</td>
	</tr>
	<tr>
		<td width="25%">
		عنوان فارسي پايان نامه:
		</td>
		<td width="75%" colspan="3">
		<input type="text" id="thesis_ptitle" name="thesis_ptitle" class="x-form-text x-form-field" style="width: 100%" >
		</td>
		
	</tr>
	<tr>
		<td width="25%">
		عنوان لاتين پايان نامه:
		</td>
		<td width="75%" colspan="3">
		<input type="text" id="thesis_etitle" name="thesis_etitle" class="x-form-text x-form-field" style="width: 100%" >
		</td>
		
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
	<tr><td colspan="4" ><br>
		<hr  width="560px" style="color: #99BBE8 " align="left"></td></tr>
	<tr>
		<td></td>
		<td colspan="3" > 
			<input type="button" class="button" onclick="PersonEducationObject.saveEduc();" value="ذخیره">
			<input type="button" id="btn_cancel" class="button" onclick="PersonEducationObject.cancel();" value="بازگشت">
		</td>
	</tr>	
</table>
</div>
<div id="EducGRID"></div>
</form>