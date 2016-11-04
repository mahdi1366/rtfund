<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.12
//---------------------------
require_once '../../../header.inc.php';
require_once("../data/person.data.php");
require_once '../../staff/class/staff.class.php';
require_once '../class/staff_tax.class.php';
require_once inc_dataGrid;

	$staffInfo = new manage_staff($_POST['Q0']);
	$staffTaxHistory = new manage_staff_tax($staffInfo->staff_id);
	
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
		
		$col = $dg->addColumn("حذف", "", "string");
		$col->renderer = " function(v,p,r){ return StaffTaxObject.opDelRender(v,p,r); }";
		$col->width = 60;

		$dg->addButton = true;
		$dg->addHandler = "function(v,p,r){ return StaffTaxObject.AddIncludeHistory(v,p,r);}";

		$dg->enableRowEdit = true ;
		$dg->rowEditOkHandler = "function(v,p,r){ return StaffTaxObject.SaveHistory(v,p,r);}";


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

<div id="TaxHistoryGRID"></div>
