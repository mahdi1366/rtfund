<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.08
//---------------------------
require_once '../../../header.inc.php';
require_once("../data/person.data.php");
require_once '../../staff/class/staff.class.php';
require_once '../class/staff_tax.class.php';
require_once inc_dataGrid;

//________________  GET ACCESS  _________________
$accessTaxObj = new ModuleAccess($_POST["FacilID"], SubModule_tax_information , Deputy , Module_personel);
//-----------------------------------------------

$staffInfo = new manage_staff($_POST['Q0']);
if($staffInfo->person_type == HR_RETIRED )
{
	$Last_staffID = manage_person::Last_StaffID_Before_Retired($_POST['Q0']);
	$staffInfo = new manage_staff("","",$Last_staffID);
}

	$staffTaxHistory = new manage_staff_tax($staffInfo->staff_id);
	$drp_taxTyp = manage_domains::DRP_TaxType("tax_table_type_id",$staffTaxHistory->tax_table_type_id,"-","150 px",$staffInfo->person_type);

	$dg = new sadaf_datagrid("TaxHistory",$js_prefix_address . "../data/staff_tax.data.php?task=selectTaxHistory&PID=".$_POST['Q0']  ,
							 "TaxHistoryGRID");
	$dg->addColumn("", "staff_id","",true);
	$dg->addColumn("", "tax_history_id","",true);
        $dg->addColumn("", "personid","",true);
        
        $col = $dg->addColumn("نوع شخص", "person_type", "int");
	$col->width = 90;
        
        $col = $dg->addColumn("شماره شناسایی", "staff_id", "int");
	$col->width = 90;

	$col = $dg->addColumn("جدول مالیاتی", "tax_table_type_id", GridColumn::ColumnType_string);
        $col->editor = ColumnEditor::ComboBox(manage_domains::getAll_TaxType($staffInfo->person_type), "tax_table_type_id", "title");
     	
	$col = $dg->addColumn("تاريخ شروع", "start_date", GridColumn::ColumnType_date);
	$col->editor = ColumnEditor::SHDateField();
	$col->width = 150;

	$col = $dg->addColumn("تاريخ پايان", "end_date", GridColumn::ColumnType_date);
	$col->editor = ColumnEditor::SHDateField(true);
	$col->width = 150;

	$dg->width = 700;
	$dg->height = 200;
    $dg->DefaultSortField = "start_date";    
	$dg->EnableSearch = false ;
	$dg->EnablePaging = false ;
	$dg->title = "سابقه مالیاتی";
    $dg->autoExpandColumn = "tax_table_type_id";
	
	if( $accessTaxObj->InsertAccess() && $accessTaxObj->UpdateAccess() ){
		$col = $dg->addColumn("حذف", "", "string");
		$col->renderer = " function(v,p,r){ return StaffTaxObject.opDelRender(v,p,r); }";
		$col->width = 60;

		$dg->addButton = true;
		$dg->addHandler = "function(v,p,r){ return StaffTaxObject.AddIncludeHistory(v,p,r);}";

		$dg->enableRowEdit = true ;
		$dg->rowEditOkHandler = "function(v,p,r){ return StaffTaxObject.SaveHistory(v,p,r);}";

	}

	$taxHistoryGrid = $dg->makeGrid_returnObjects();
	
require_once '../js/staff_tax.js.php';

?>
<script>
StaffTax.prototype.afterLoad = function()
{
	this.PersonID = <?= $_POST["Q0"]?>;
	this.IncludeTaxGrid = <?= $taxHistoryGrid?>;
	this.IncludeTaxGrid.render(this.parent.get("TaxHistoryGRID"));
	this.sid = <?= $staffInfo->staff_id ?> ;
	
}

var StaffTaxObject = new StaffTax();

</script>
<fieldset class="x-fieldset x-form-label-left" style="border-color: #99BBE8;width:700px">
<legend class="blueText" >مجموع مقرری پرداخت شده </legend>
	<form id="form_StaffTax" >
		<input type='hidden' id='PersonID' name='PersonID' value="<?= $_POST['Q0'] ?>">
		<input type='hidden' id='person_type' name='person_type' value="<?= $staffInfo->person_type ?>">
		<input type='hidden' id='staff_id' name='staff_id' value="<?= $staffInfo->staff_id ?>">
		
		<table id="staffPNL" width="640px">
				<tr>
					<td width="25%">
					مجموع مقرری :
					</td>
					<td width="75%" colspan="3" >
					<input type="text" id="sum_paied_pension" name="sum_paied_pension" class="x-form-text x-form-field" style="width: 150px"
						   value="<?= $staffInfo->sum_paied_pension ?>" >
					</td>
				</tr>
				<tr><td></td><td colspan="3" ><hr  width="500px" style="color: #99BBE8 "></td></tr>
			
				<tr>
					<td></td>
					<td colspan="3" > &nbsp;&nbsp;&nbsp;
					<input type="button" id="tax_save_btn"  class="button" onclick="StaffTaxObject.saveTaxAction();" value="ذخیره">
					</td>
				</tr>
			
		</table>
	</form>
</fieldset>
<fieldset class="x-fieldset x-form-label-left" style="border-color: #99BBE8;width:700px">
<legend class="blueText" >مجموع حقوق مشمول ماليات تا بدو استخدام</legend>
	<form id="form_TaxHistory">
		<input type='hidden' id='PersonID' name='PersonID' value="<?= $_POST['Q0'] ?>">
		<input type='hidden' id='tax_history_id' name='tax_history_id' value="<?= $staffTaxHistory->tax_history_id ?>">
		<input type='hidden' id='staff_id' name='staff_id' value="<?= $staffInfo->staff_id ?>">
		<table id="TaxHisoryPNL" width="640px">
			<tr>
				<td width="25%">
				جدول مالیاتی :
				</td>
				<td width="25%" ><?=$drp_taxTyp ?></td>
				<td width="25%">
				مجموع حقوق مشمول مالیات :
				</td>
				<td width="25%" ><input type="text" id="payed_tax_value" name="payed_tax_value" class="x-form-text x-form-field" style="width: 150px"
						   value="<?= $staffTaxHistory->payed_tax_value ?>" ></td>
			</tr>
			<tr>
				<td width="25%">
				تاريخ شروع :
				</td>
				<td width="25%" >
				<input type="text" id="start_date" name="start_date" class="x-form-text x-form-field" style="width: 80px"
					   value="<?= DateModules::Miladi_to_Shamsi($staffTaxHistory->start_date) ?>">
				</td>
				<td width="25%">
				تاريخ پايان :
				</td>
				<td width="25%" >
				<input type="text" id="end_date" name="end_date" class="x-form-text x-form-field" style="width: 80px"
					   value="<?= DateModules::Miladi_to_Shamsi($staffTaxHistory->end_date) ?>"></td>
			</tr>
			<tr><td></td><td colspan="3" ><hr  width="500px" style="color: #99BBE8 "></td></tr>
			
				<tr>
					<td></td>
					<td colspan="3" > &nbsp;&nbsp;&nbsp;
					<input type="button" id="tax_save_btn"  class="button" onclick="StaffTaxObject.saveTaxHis();" value="ذخیره">
					</td>
				</tr>
			
		</table>
	</form>
</fieldset>

<div id="TaxHistoryGRID"></div>
<? if( !$accessTaxObj->InsertAccess() && !$accessTaxObj->UpdateAccess() ){ ?>
		<script>
			Ext.get(StaffTaxObject.parent.TabID).readonly('');
		</script>
<?	}	?>