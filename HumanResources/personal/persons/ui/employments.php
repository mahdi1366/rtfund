<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	88.07.07
//---------------------------
require_once '../../../header.inc.php';
require_once("../data/person.data.php");
require_once inc_dataGrid;

//________________  GET ACCESS  _________________
$accessObj = new ModuleAccess($_POST["FacilID"], SubModule_person_employment);
//-----------------------------------------------

require_once '../js/employment.js.php';

$dg = new sadaf_datagrid("emp",$js_prefix_address . "../data/employment.data.php?task=selectEmp&Q0=".$_POST['Q0'],"EmpGRID");

$col= $dg->addColumn("شماره پرسنلی","PersonID","int",true);
$col= $dg->addColumn("نوع سازمان","org_type","int",true);
$col= $dg->addColumn("نوع خدمت","person_type","int",true);
$col= $dg->addColumn("وضعیت استخدامی","emp_state","int",true);
$col= $dg->addColumn("حالت استخدامی","emp_mode","int",true);
$col= $dg->addColumn("دلیل خاتمه","unemp_cause","int",true);
$col= $dg->addColumn("مدت خدمت سال","duration_year","int",true);
$col= $dg->addColumn("مدت خدمت ماه","duration_month","int",true);
$col= $dg->addColumn("مدت خدمت روز","duration_day","int",true);
$col= $dg->addColumn("مدت قابل قبول بازنشستگی سال","retired_duration_year","int",true);
$col= $dg->addColumn("مدت قابل قبول بازنشستگی ماه","retired_duration_month","int",true);
$col= $dg->addColumn("مدت قابل قبول بازنشستگی روز","retired_duration_day","int",true);
$col= $dg->addColumn("مدت قابل قبول اعطای گروه سال","group_duration_year","int",true);
$col= $dg->addColumn("مدت قابل قبول اعطای گروه ماه","group_duration_month","int",true);
$col= $dg->addColumn("مدت قابل قبول اعطای گروه روز","group_duration_day","int",true);
$col= $dg->addColumn("توضیحات","comments","string",true);

$col = $dg->addColumn("ردیف", "row_no", "int");
$col->width = 50;

$col = $dg->addColumn("سازمان محل خدمت", "organization", "string");
$col->width = 120;

$col = $dg->addColumn("واحد محل خدمت", "unit", "string");

$col = $dg->addColumn("از تاریخ", "from_date", "string");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 80;

$col = $dg->addColumn("تا تاریخ", "to_date", "string");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 80;

$col = $dg->addColumn("نوع سازمان", "org_title", "string");
$col->width = 80;

$col = $dg->addColumn("عنوان شغل", "title", "string");
$col->width = 100;

$col = $dg->addColumn("دلیل خاتمه", "unempTitle", "string");
$col->width = 100;

$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "PersonEmplyment.opRender";
$col->width = 50;

$dg->height = 400;
$dg->width = 780;
$dg->DefaultSortField = "row_no";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "unit";

if($accessObj->InsertAccess())
{
	$dg->addButton = true;
	$dg->addHandler = "function(){PersonEmplymentObject.AddEmp();}";
}
$dg->EnableSearch = false;
$grid = $dg->makeGrid_returnObjects();

$drp_org_type = manage_domains::DRP_OrgType("org_type","","with:50%");
$drp_PType = manage_domains::DRP_PersonTypeNoAccess("person_type","","with:50%");
$drp_EmpState = manage_domains::DRP_EmpState("emp_state", "","","with:60%");
$drp_EmpMode = manage_domains::DRP_EmpMode("emp_mode", "","","with:60%");
$drp_UnEmp = manage_domains::DRP_UnEmpCause("unemp_cause", "","","with:60%");

?>
<script>
PersonEmplyment.prototype.afterLoad = function()
{
	this.grid = <?= $grid?>;
	this.grid.render(this.get("EmpGRID"));

	this.PersonID = <?= $_POST['Q0']?>;
}

var PersonEmplymentObject = new PersonEmplyment();
	
</script>
<form id="form_PersonEmployment" method="POST">

<div id='EmpDIV'  style="display: none;width:750px" class="panel" >
<input type='hidden' id='row_no' name='row_no' >
<table width="100%">
	<tr>
		<td width="10%">
		سازمان محل خدمت:
		</td>
		<td width="40%">
		<input type="text" id="organization" name="organization" class="x-form-text x-form-field" style="width: 180px">
		</td>
		<td width="40%">
		واحد محل خدمت:
		</td>
		<td width="10%">
		<input type="text" id="unit" name="unit" class="x-form-text x-form-field" style="width: 120px" >
		</td>
	</tr>
	<tr>
		<td width="10%">
		نوع سازمان:
		</td>
		<td width="40%"><?= $drp_org_type ?></td>
		<td width="40%">
		نوع خدمت:
		</td>
		<td width="10%"><?= $drp_PType ?></td>
	</tr>
	
	<tr>
		<td width="10%">
		وضعیت استخدامی:
		</td>
		<td width="40%"><?= $drp_EmpState ?></td>
		<td width="40%">
		حالت استخدامی:
		</td>
		<td width="10%"><?= $drp_EmpMode ?></td>
	</tr>
	
	<tr>
		<td width="10%">
		تاریخ شروع:
		</td>
		<td width="40%">
		<input type="text" id="from_date" name="from_date" class="x-form-text x-form-field" style="width: 100px">
		</td>
		<td width="40%">
		تاریخ خاتمه:
		</td>
		<td width="10%">
		<input type="text" id="to_date" name="to_date" class="x-form-text x-form-field" style="width: 100px" >
		</td>
	</tr>
	
	<tr>
		<td width="10%">
		عنوان شغل :
		</td>
		<td width="40%">
		<input type="text" id="title" name="title" class="x-form-text x-form-field" style="width: 100px">
		</td>
		<td width="40%" >
		دليل خاتمه خدمت : 
		</td>
		<td width="10%"><?= $drp_UnEmp ?></td>
	</tr>
	
	<tr>
		<td width="40%" colspan="1">
		مدت خدمت:
		<td width="60%" colspan="3" >
		<input type="text" id="duration_year" name="duration_year" class="x-form-text x-form-field" style="width: 50px">سال
		<input type="text" id="duration_month" name="duration_month" class="x-form-text x-form-field" style="width: 50px">ماه
		<input type="text" id="duration_day" name="duration_day" class="x-form-text x-form-field" style="width: 50px">روز
		</td>
	<tr>
	<tr>
		<td width="40%" colspan="1">
		مدت قابل قبول بازنشستگی:
		<td width="60%" colspan="3" >
		<input type="text" id="retired_duration_year" name="retired_duration_year" class="x-form-text x-form-field" style="width: 50px">سال
		<input type="text" id="retired_duration_month" name="retired_duration_month" class="x-form-text x-form-field" style="width: 50px">ماه
		<input type="text" id="retired_duration_day" name="retired_duration_day" class="x-form-text x-form-field" style="width: 50px">روز
		</td>
	<tr>
	<tr>
		<td width="40%" colspan="1">
		مدت قابل قبول اعطای گروه :
		<td width="60%" colspan="3" >
		<input type="text" id="group_duration_year" name="group_duration_year" class="x-form-text x-form-field" style="width: 50px">سال
		<input type="text" id="group_duration_month" name="group_duration_month" class="x-form-text x-form-field" style="width: 50px">ماه
		<input type="text" id="group_duration_day" name="group_duration_day" class="x-form-text x-form-field" style="width: 50px">روز
		</td>
	<tr>
	<tr>
		<td width="40%">
		توضیحات:
		</td>
		<td width="60%" colspan="3">
		<textarea id="comments" name="comments" rows="50" cols="10" class="x-form-text x-form-field" style="width: 500px;height: 50px" > 
		</textarea>
		</td>
	<tr>
	<tr><td><br></td></tr>
	<tr><td colspan="4" >
		<hr  width="570px" style="color: #99BBE8 " align="left"></td></tr>
	<tr>
		<td></td>
		<td colspan="3" > 
			<input type="button" class="button" onclick="PersonEmplymentObject.saveEmp();" value="ذخیره">
			<input type="button" id="btn_cancel" class="button" onclick="PersonEmplymentObject.empCancle();" value="بازگشت">
		</td>
	</tr>
		
</table>
</div>
<div id="EmpGRID"></div>
</form>