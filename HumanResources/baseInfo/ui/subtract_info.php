<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.05.03
//---------------------------
require_once '../../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;

require_once '../js/subtract_info.js.php';

$dg = new sadaf_datagrid("SubInfoGrid", $js_prefix_address . "../data/subtract_info.data.php?task=searchSubItem", "SubInfoDIV");

$dg->addColumn("کد قلم", "SalaryItemTypeID","",true);
$dg->addColumn("نوع فرد ", "PersonType","",true);

$col = $dg->addColumn("قلم کسری", "full_title");
$col->editor = "SubInfoObject.subItemCombo";
$col->width = 200;


$col = $dg->addColumn("ذی نفع", "BeneficiaryID", "string");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_beneficiary(), "BeneficiaryID", "title");
$col->width = 80;

$col = $dg->addColumn("شرح", "description", "string");
$col->editor = ColumnEditor::TextField(true);

$col = $dg->addColumn("ترتیب", "arrangement");
$col->editor = ColumnEditor::NumberField();
$col->width = 60;

$col = $dg->addColumn("نوع فرد", "person_type_title", "string");
$col->width = 60;

$col = $dg->addColumn("از تاریخ", "FromDate", GridColumn::ColumnType_date );
$col->editor = ColumnEditor::SHDateField();
$col->width = 80;

$col = $dg->addColumn(" تا تاریخ", "ToDate", GridColumn::ColumnType_date );
$col->editor = ColumnEditor::SHDateField(true);
$col->width = 80;

$col = $dg->addColumn("حذف", "", "string");
$col->renderer = "function(v,p,r){return SubInfo.opRender(v,p,r);}";
$col->width = 40;

$dg->addButton = true;
$dg->addHandler = "function(){SubInfoObject.AddSubInfo();}";

$dg->pageSize = "13";
$dg->EnableSearch = false ;
$dg->width = 850;
$dg->height = 630;
$dg->title = "اطلاعات  اقلام کسور ";
$dg->autoExpandColumn = "description";
$dg->DefaultSortField = "arrangement" ; 

$dg->enableRowEdit = true ;
$dg->rowEditOkHandler = "function(v,p,r){ return SubInfoObject.editSubInfo(v,p,r);}";

$grid = $dg->makeGrid_returnObjects();

?>
<script>
    SubInfoObject.grid = <?=$grid?>;   
    SubInfoObject.grid.render(SubInfoObject.get("SubInfoDIV"));
</script>
<center>
    <div id="ErrorDiv" style="width:40%"></div>
	<div id="SubInfoDIV" style="width:100%"></div>
</center>
