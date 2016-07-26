<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.02
//---------------------------
require_once '../../../header.inc.php';
require_once inc_dataGrid;

require_once '../js/salary_param_type.js.php';

 
$pdg = new sadaf_datagrid("pdg", $js_prefix_address . "../data/salary_param_type.data.php?task=selectAll","pdgDiv");

$pdg->addColumn('', "param_type", "int", true);

$col = $pdg->addColumn('عنوان پارامتر', "title", "string");
$col->editor = ColumnEditor::TextField();

$col = $pdg->addColumn("نوع شخص","person_type_title","int");
$col->width = 100 ;

$pdg->openHeaderGroup("پارامترهای مرتبط");

$col = $pdg->addColumn('پارامتر1', "dim1_id");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_SalaryParam(), "id", "value");


$col->width = 100;

$col = $pdg->addColumn('پارامتر2', "dim2_id");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_SalaryParam(), "id", "value");
$col->width = 100;

$col = $pdg->addColumn('پارامتر3', "dim3_id");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_SalaryParam(), "id", "value");
$col->width = 100;

$col = $pdg->addColumn('پارامتر4', "dim4_id");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_SalaryParam(), "id", "value");
$col->width = 100;

$pdg->closeHeaderGroup();

	$pdg->addButton = true;
	$pdg->addHandler =  "function(v,p,r){ return SalaryParamTypeObject.AddSPT(v,p,r);}";

$col = $pdg->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){ return SalaryParamTypes.opRender(v,p,r);}";
$col->width = 100;

$pdg->title = "انواع پارامترهای حقوقی";
$pdg->width = 800;
$pdg->DefaultSortField = "param_type";
$pdg->DefaultSortDir = "Desc";
$pdg->EnableSearch = false;

    $pdg->enableRowEdit = true ;
    $pdg->rowEditOkHandler = "function(v,p,r){ return SalaryParamTypeObject.editPST(v,p,r);}";

$pdg->collapsible = true ;
$pdg->collapsed = false ;
$pdg->HeaderMenu = false;
$grid = $pdg->makeGrid_returnObjects();
?>
<script>

SalaryParamTypes.prototype.afterLoad = function()
{
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("pdgDiv"));
}
var SalaryParamTypeObject = new SalaryParamTypes();
</script>
<center>
<div id="form_salaryParamTypes">
	<div id="pdgDiv"></div>
	<br><br>
	<div align="center" id="InfoPNL" ></div>
</div>
</center>