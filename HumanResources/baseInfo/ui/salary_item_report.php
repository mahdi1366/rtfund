<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.05.10
//---------------------------
require_once '../../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;

require_once '../js/salary_item_report.js.php';

$dg = new sadaf_datagrid("SIGrid", $js_prefix_address . "../data/salary_item_report.data.php?task=searchSITR", "SIDIV");

$dg->addColumn("کد قلم", "SalaryItemReportID","",true);
$dg->addColumn("وضعیت", "state","",true);

$col = $dg->addColumn("عنوان قلم", "SalaryItemTitle", "string");
$col->editor = ColumnEditor::TextField(true); 
$col->width = 150;

$col = $dg->addColumn("شرح", "description", "string");
$col->editor = ColumnEditor::TextField(true); 

$col = $dg->addColumn("ذی نفع", "BeneficiaryID", "integer");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_beneficiary(), "BeneficiaryID", "title");
$col->width = 80;

$col = $dg->addColumn("نوع قلم", "ItemType", "integer");
$col->editor = ColumnEditor::ComboBox(array(array("ItemType"=>"1","title"=>"متفرقه"),
                                            array("ItemType"=>"2","title"=>"خزانه")), "ItemType", "title");
$col->width = 80;

$col = $dg->addColumn("نوع فرد", "PersonType", "integer");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_PersonType(), "InfoID", "Title");
$col->width = 80;

$col = $dg->addColumn("سال", "PayYear", "integer");
$col->editor = ColumnEditor::NumberField(true); 
$col->width = 70;

$col = $dg->addColumn("ماه", "PayMonth", "integer");
$col->editor = ColumnEditor::NumberField(true); 
$col->width = 70;

$col = $dg->addColumn("مبلغ", "ItemValue", "integer");
$col->editor = ColumnEditor::NumberField(true); 
$col->width = 80;


$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){return SalaryItemReport.opRender(v,p,r);}";
$col->width = 50;

$dg->addButton = true;
$dg->addHandler = "function(){SalaryItemReportObject.AddSIR();}";

$dg->pageSize = "20";
$dg->EnableSearch = false ;
$dg->width = 780;
$dg->height = 430;
$dg->title = " اقلام گزارش پرداخت کسورات";
$dg->autoExpandColumn = "description";
$dg->DefaultSortField = "SalaryItemReportID";

$dg->enableRowEdit = true ;
$dg->rowEditOkHandler = "function(v,p,r){ return SalaryItemReportObject.editSIR(v,p,r);}";

$grid = $dg->makeGrid_returnObjects();

?>
<script>
    SalaryItemReportObject.grid = <?=$grid?>;   
    SalaryItemReportObject.grid.plugins[0].on("beforeedit", function(editor,e){	
	if(e.record.data.state == 2 )
		return false;
	});
    SalaryItemReportObject.grid.render("SIDIV");
    
</script>
<center>
    <div id="ErrorDiv" style="width:40%"></div>
	<div id="SIDIV" style="width:100%"></div>
</center>
