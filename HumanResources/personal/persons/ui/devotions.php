<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	88.07.05
//---------------------------
require_once '../../../header.inc.php';
require_once("../data/person.data.php");
require_once inc_dataGrid;

//________________  GET ACCESS  _________________
$accessObj = new ModuleAccess($_POST["FacilID"], SubModule_person_devotion);
//-----------------------------------------------

require_once '../js/devotions.js.php';

$dg = new sadaf_datagrid("dev",$js_prefix_address . "../data/devotion.data.php?task=selectDevot&Q0=".$_POST['Q0'],"devGRID");

$col= $dg->addColumn("شماره پرسنلی","PersonID","int",true);
$col= $dg->addColumn("کد نوع ایثارگری","devotion_type","int",true);
$col= $dg->addColumn("کد نسبت","personel_relation","int",true);
$col= $dg->addColumn("کد داوطلبانه","enlisted","int",true);
$col= $dg->addColumn("پیوسته","continous","int",true);
$col= $dg->addColumn("شماره نامه","letter_no","int",true);
$col= $dg->addColumn("تاریخ نامه","letter_date","date",true);
$col= $dg->addColumn("توضیحات","comments","string",true);

$col = $dg->addColumn("ردیف", "devotion_row", "string");
$col->width = 100;

$col = $dg->addColumn("نوع ایثارگری", "Title", "string");
$col->width = 100;

$col = $dg->addColumn("داوطلبانه", "Tenlisted", "string");
$col->width = 100;

$col = $dg->addColumn("از تاریخ", "from_date", "string");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 100;

$col = $dg->addColumn("تا تاریخ", "to_date", "string");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 100;

$col = $dg->addColumn("مدت روز/درصد", "amount", "string");
$col->width = 100;

$col = $dg->addColumn("منطقه عملیاتی", "war_place", "string");
$col->width = 100;

$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "PersonDevotion.opRender";
$col->width = 100;

$dg->height = 400;
$dg->width = 780;
$dg->DefaultSortField = "devotion_row";
$dg->DefaultSortDir = "ASC";
if($accessObj->InsertAccess())
{
	$dg->addButton = true;
	$dg->addHandler = "function(){PersonDevotionObject.AddDev();}";
}
$dg->EnableSearch = false;

$grid = $dg->makeGrid_returnObjects();
$drp_devotion = manage_domains::DRP_Devotions("devotion_type","","with:50%");
$drp_dependency = manage_domains::DRP_Dependencies("personel_relation", "","","with:60%");
?>
<script>
PersonDevotion.prototype.afterLoad = function()
{
	this.grid = <?= $grid?>;
	this.grid.render(this.get("devGRID"));

	this.PersonID = <?= $_POST["Q0"]?>;
}

var PersonDevotionObject = new PersonDevotion();
</script>
<form id="form_personDevotion" method="POST">
	<div id='devDIV' style="display: none;width:750px"  class="panel" >
	<input type='hidden' id='devotion_row' name='devotion_row' >
	<table width="100%">
		<tr>
			<td width="10%">
			نوع ايثارگري :
			</td>
			<td width="50%"><?= $drp_devotion?></td>
			<td width="30%">
			نسبت:
			</td>
			<td width="10%"><?=$drp_dependency ?></td>
		</tr>
		<tr>
			<td width="10%">
			از تاريخ:
			</td>
			<td width="25%">
			<input type="text" id="dev_from_date" name="from_date" class="x-form-text x-form-field" style="width: 80px" >
			</td>
			<td width="10%">
			 تا تاريخ:
			</td>
			<td width="25%"><input type="text" id="dev_to_date" name="to_date" class="x-form-text x-form-field" style="width: 80px" >
			</td>
		</tr>
		<tr>
			<td width="5%">
			داوطلبانه؟
			</td>
			<td width="5%" >
			<input type="checkbox" value="1" id="enlisted"  name="enlisted" class="x-form-text x-form-field" style="width: 10px" >
			</td>
			<td width="50%">
			مدت به روز/درصد(مشمول امتياز) :
			</td>
			<td width="25%">
			<input type="text" id="amount" name="amount" class="x-form-text x-form-field" style="width: 50px">
			</td>
		</tr>
		<tr>
			<td width="5%">
			پيوسته ؟
			</td>
			<td width="5%">
			<input type="checkbox" value="1" id="continous" name="continous" class="x-form-text x-form-field" style="width: 10px" >
			</td>
			<td width="25%">
			 منطقه عملياتي:
			</td>
			<td width="25%">
			<input type="text" id="war_place" name="war_place" class="x-form-text x-form-field" style="width: 150px" >
			</td>
		</tr>

		<tr>
			<td width="25%">
			شماره نامه :
			</td>
			<td width="25%">
			<input type="text" id="letter_no" name="letter_no" class="x-form-text x-form-field" style="width: 100px" >
			</td>
			<td width="25%">
			تاريخ نامه:
			</td>
			<td width="25%">
			<input type="text" id="letter_date" name="letter_date" class="x-form-text x-form-field" style="width: 80px" >
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
		<tr>
			<td width="50%" colspan="2">
			مدت قابل قبول بازنشستگی که کسور آن پرداخت شده است(به روز) :
			
			</td>
            <td  width="50%" colspan="2" >
                <input type="text" id="duration_include_paied_retired_fraction" name="duration_include_paied_retired_fraction" class="x-form-text x-form-field" style="width: 100px" >
            </td>
		</tr>
		<tr><td><br></td></tr>
		<tr><td colspan="4" >
			<hr  width="615px" style="color: #99BBE8 " align="left"></td></tr>
		<tr>
			<td></td>
			<td colspan="3" >
				<input type="button" class="button" onclick="PersonDevotionObject.saveDev();" value="ذخیره">
				<input type="button" id="btn_cancel" class="button" onclick="PersonDevotionObject.DevCancle();" value="بازگشت">
			</td>
		</tr>

	</table>
	</div>
	<br><br>
	<div id="devGRID"></div>
</form>