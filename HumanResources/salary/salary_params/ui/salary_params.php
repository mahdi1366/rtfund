<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.03
//---------------------------
require_once '../../../header.inc.php';
require_once inc_dataGrid;

$Detail_datasource = PdoDataAccess::runquery(" SELECT city_id , state_id , ptitle  FROM HRM_cities ");

$temp = PdoDataAccess::runquery(" select * 
                                  from HRM_salary_param_types
                                  where param_type = ".$_POST['param_type']." and person_type = 3 ");

$dg = new sadaf_datagrid("dg", $js_prefix_address .
	"../data/salary_param.data.php?task=selectAll&param_type=".$_POST['param_type']."&person_type=3" ,"dgDiv", "mainForm");

$dg->addColumn('', "param_id", "string", true);

$col = $dg->addColumn('از تاریخ', "from_date", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();

$col = $dg->addColumn('تا تاریخ', "to_date", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
//---------------------- dim1_id --------------------------------
if( $temp[0]['dim1_id'] == 'STATE' )
{
    $col = $dg->addColumn("استان محل خدمت","dim1_id" ,"int");
    $col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_State(), "state_id", "ptitle", "ext_State");
}

else if ( $temp[0]['dim1_id'] == 'CITY'  )
{
    $col = $dg->addColumn("شهر محل خدمت", "dim1_id" );
	$col->editor = ColumnEditor::ComboBox($Detail_datasource, "city_id", "ptitle");
	
}

elseif ($temp[0]['dim1_id'] == 'SGROUP')
{
    $col = $dg->addColumn('گروه شروع', "dim1_id", "int");
    $col->editor = ColumnEditor::NumberField();
}
elseif ($temp[0]['dim1_id'] == 'EGROUP')
{
	$col = $dg->addColumn('گروه پایان', "dim1_id", "int");
    $col->editor = ColumnEditor::NumberField();
}

elseif ($temp[0]['dim1_id'] == 'MARITALS')
{
    $col = $dg->addColumn("وضعیت تاهل","dim1_id","int");
	$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_Marital_Status(), "InfoID", "InfoDesc");
}
elseif ($temp[0]['dim1_id'] == 'EDUCLEVEL')
{
    $col = $dg->addColumn("مدرک تحصیلی","dim1_id","int");
    $col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_Educ_Level(), "InfoID", "InfoDesc");
}
elseif ($temp[0]['dim1_id'] == 'DUTY_YEAR')
{
    $col = $dg->addColumn("سال سنوات خدمت","dim1_id","int");
   $col->editor = ColumnEditor::NumberField();
}

//-------------- dim2_id ----------------
if( $temp[0]['dim2_id'] == 'STATE' )
{
    $col = $dg->addColumn("استان محل خدمت","dim2_id" ,"int");
	$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_State(), "state_id", "ptitle", "ext_State");
}

else if ( $temp[0]['dim2_id'] == 'CITY'  )
{
    $col = $dg->addColumn("شهر محل خدمت", "dim2_id" );
	$col->editor = ColumnEditor::SlaveComboBox($Detail_datasource, "city_id", "ptitle", "state_id", "ext_State");
}

elseif ($temp[0]['dim2_id'] == 'SGROUP')
{
    $col = $dg->addColumn('گروه شروع', "dim2_id", "int");
    $col->editor = ColumnEditor::NumberField();
}
elseif ($temp[0]['dim2_id'] == 'EGROUP')
{
	$col = $dg->addColumn('گروه پایان', "dim2_id", "int");
   $col->editor = ColumnEditor::NumberField();
}
elseif ($temp[0]['dim2_id'] == 'MARITALS')
{
    $col = $dg->addColumn("وضعیت تاهل","dim2_id","int");
    $col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_Marital_Status(), "InfoID", "InfoDesc");
}
elseif ($temp[0]['dim2_id'] == 'EDUCLEVEL')
{
    $col = $dg->addColumn("مدرک تحصیلی","dim2_id","int");
    $col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_Educ_Level(), "InfoID", "InfoDesc");
}
elseif ($temp[0]['dim2_id'] == 'DUTY_YEAR')
{
    $col = $dg->addColumn("سال سنوات خدمت","dim2_id","int");
    $col->editor = ColumnEditor::NumberField();
}

//-----------------------dim3_id ----------------------------
if( $temp[0]['dim3_id'] == 'STATE' )
{
    $col = $dg->addColumn("استان محل خدمت","dim3_id" ,"int");
	$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_State(), "state_id", "ptitle", "ext_State");
}

else if ( $temp[0]['dim3_id'] == 'CITY'  )
{
    $col = $dg->addColumn("شهر محل خدمت", "dim3_id" );
	$col->editor = ColumnEditor::SlaveComboBox($Detail_datasource, "city_id", "ptitle", "state_id", "ext_State");
}

elseif ($temp[0]['dim3_id'] == 'SGROUP')
{
    $col = $dg->addColumn('گروه شروع', "dim3_id", "int");
   $col->editor = ColumnEditor::NumberField();
}
elseif ($temp[0]['dim3_id'] == 'EGROUP')
{
	$col = $dg->addColumn('گروه پایان', "dim3_id", "int");
   $col->editor = ColumnEditor::NumberField();
}
elseif ($temp[0]['dim3_id'] == 'MARITALS')
{
    $col = $dg->addColumn("وضعیت تاهل","dim3_id","int");
    $col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_Marital_Status(), "InfoID", "InfoDesc");
}
elseif ($temp[0]['dim3_id'] == 'EDUCLEVEL')
{
    $col = $dg->addColumn("مدرک تحصیلی","dim3_id","int");
    $col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_Educ_Level(), "InfoID", "InfoDesc");
}
elseif ($temp[0]['dim3_id'] == 'DUTY_YEAR')
{
    $col = $dg->addColumn("سال سنوات خدمت","dim3_id","int");
    $col->editor = ColumnEditor::NumberField();
}
//---------------------- dim4_id --------------------------
if( $temp[0]['dim4_id'] == 'STATE' )
{
    $col = $dg->addColumn("استان محل خدمت","dim4_id" ,"int");
	$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_State(), "state_id", "ptitle", "ext_State");
}

else if ( $temp[0]['dim4_id'] == 'CITY'  )
{
    $col = $dg->addColumn("شهر محل خدمت", "dim4_id" );
	$col->editor = ColumnEditor::SlaveComboBox($Detail_datasource, "city_id", "ptitle", "state_id", "ext_State");
}

elseif ($temp[0]['dim4_id'] == 'SGROUP')
{
    $col = $dg->addColumn('گروه شروع', "dim4_id", "int");
    $col->editor = ColumnEditor::NumberField();
}
elseif ($temp[0]['dim4_id'] == 'EGROUP')
{
	$col = $dg->addColumn('گروه پایان', "dim4_id", "int");
    $col->editor = ColumnEditor::NumberField();
}
elseif ($temp[0]['dim4_id'] == 'MARITALS')
{
    $col = $dg->addColumn("وضعیت تاهل","dim4_id","int");
    $col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_Marital_Status(), "InfoID", "InfoDesc");
}
elseif ($temp[0]['dim4_id'] == 'EDUCLEVEL')
{
    $col = $dg->addColumn("مدرک تحصیلی","dim4_id","int");
    $col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_Educ_Level(), "InfoID", "InfoDesc");
}
elseif ($temp[0]['dim4_id'] == 'DUTY_YEAR')
{
    $col = $dg->addColumn("سال سنوات خدمت","dim4_id","int");
    $col->editor = ColumnEditor::NumberField();
}
//-----------------------------------------

$col = $dg->addColumn('مقدار', "value", "int");
$col->editor = ColumnEditor::NumberField();

	$dg->addButton = true;
	$dg->addHandler =  "function(v,p,r){ return SalaryParamObject.Adding(v,p,r);}";

	$col = $dg->addColumn("حذف", "", "string");
	$col->renderer = "function(v,p,r){ return SalaryParam.opDelRender(v,p,r);}";
	$col->width = 20;

$dg->title = "اطلاعات " . $temp[0]['title'];
$dg->width = 800;
$dg->DefaultSortField = "to_date";
$dg->DefaultSortDir = "Desc";
$dg->height = 520;
$dg->EnableSearch = false;


	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(store,record){SalaryParamObject.editparam(store,record);}";

$grid = $dg->makeGrid_returnObjects();
?>
<script>
	SalaryParam.prototype.afterLoad = function()
	{
		this.grid = <?= $grid?>;
		this.grid.render(this.get("dgDiv"));

		this.param_type = '<?= $_POST['param_type'] ?>';
		this.person_type = '<?= $_POST['person_type'] ?>';
	}

	var SalaryParamObject = new SalaryParam();
</script>
<div id="dgDiv"></div>