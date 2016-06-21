<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------
require_once '../header.inc.php';
require_once 'unit.class.php';
require_once 'unit.data.php';
 
$ouid = !empty($_GET["ouid"]) ? $_GET["ouid"] : "";

$obj = new manage_units($ouid);

$drp_org_unit_types = manage_units::DRP_org_unit_type("org_unit_type", $obj->org_unit_type, "", "200px");
$drp_unitTypes = manage_units::DRP_unitType("UnitType", $obj->UnitType, "", "200px");
$drp_LevelTypes = manage_units::DRP_LevelType("LevelType", $obj->LevelType, "", "200px");

DRP_State_City($state_id, $city_id, "state_id", "ctid", $obj->state_id, $obj->ctid);
$drp_costCenters = manage_domains::DRP_CostCenters("ccid", $obj->ccid);
?>
<form id="form_newUnit">
<input type="hidden" name="ouid" id="ouid" value="<?= isset($_GET["ouid"]) ? $_GET["ouid"] : "" ?>"/>
<input type="hidden" name="parent_ouid" id="parent_ouid" value="<?= $_GET["parent_ouid"] ?>"/>
<table width="100%">
	<tr>
		<td width="35%">واحد سازماني رده بالاتر :</td>
		<td height="21px"><b><?= $_GET["parentText"] ?></b><input type="hidden" id="parent_path" name="parent_path" value="<?= $_GET["parent_path"]?>"></td>
	</tr>
	<tr>
		<td>کد واحد سازمانی‌:</td>
		<td><b><?= isset($_GET["ouid"]) ? $_GET["ouid"] : "" ?></b></td>
	</tr>
	<tr>
		<td>نوع واحد سازمانی:</td>
		<td><?= $drp_org_unit_types ?></td>
	</tr>
	<tr>
		<td>عنوان فارسی:</td>
		<td><input type="text" class="x-form-text x-form-field" name="ptitle" id="ptitle" 
			style="width:90%" value="<?= $obj->ptitle ?>"></td>
	</tr>
	<tr>
		<td>عنوان انگلیسی:</td>
		<td><input type="text" class="x-form-text x-form-field" dir="ltr" name="etitle" id="etitle" 
			style="width:90%" value="<?= $obj->etitle ?>"></td>
	</tr>
	<tr>
		<td>شماره کارگاه روزمزد بيمه اي :</td>
		<td><input type="text" class="x-form-text x-form-field" name="daily_work_place_no" 
			style="width:90%" id="daily_work_place_no" value="<?= $obj->daily_work_place_no ?>"></td>
	</tr>
	<tr>
		<td>شماره کارگاه پيماني :</td>
		<td><input type="text" class="x-form-text x-form-field" name="contract_work_place_no" 
			style="width:90%" id="contract_work_place_no" value="<?= $obj->contract_work_place_no ?>"></td>
	</tr>
	<tr>
		<td>نام کارگاه :</td>
		<td><input type="text" class="x-form-text x-form-field" name="detective_name" id="detective_name" 
			style="width:90%" value="<?= $obj->detective_name ?>"></td>
	</tr>
	<tr>
		<td>نشاني کارگاه :</td>
		<td><input type="text" class="x-form-text x-form-field" name="detective_address" id="detective_address"
			style="width:90%" value="<?= $obj->detective_address ?>"></td>
	</tr>
	<tr>
		<td>نام کارفرما :</td>
		<td><input type="text" class="x-form-text x-form-field" name="employer_name" id="employer_name" 
			style="width:90%" value="<?= $obj->employer_name ?>"></td>
	</tr>
	<tr>
		<td>شعبه تامين اجتماعي :</td>
		<td><input type="text" class="x-form-text x-form-field" name="collective_security_branch" 
			style="width:90%" id="collective_security_branch" value="<?= $obj->collective_security_branch ?>"></td>
	</tr>
	<tr>
		<td>استان :</td>
		<td><?= $state_id ?></td>
	</tr>
	<tr>
		<td>شهر :</td>
		<td><?= $city_id ?></td>
	</tr>
	<tr>
		<td>مرکز هزينه :</td>
		<td><?= $drp_costCenters ?></td>
	</tr>
	<tr>
		<td>نوح واحد دستگاه:</td>
		<td><?= $drp_unitTypes ?></td>
	</tr>
	<tr>
		<td>نوح سطح سازمانی:</td>
		<td><?= $drp_LevelTypes ?></td>
	</tr>
	<tr>
		<td>پست سازماني مدير واحد :</td>
		<td></td>
	</tr>
	<tr><td colspan="2" align="center">
			<input type="button" value="ذخیره" onclick="saveUnit();" class="button">
			<input type="button" value="انصراف" onclick="Ext.getCmp('Ext_NewUnit').hide();" class="button"></td></tr>
</table>
<br>
</form>