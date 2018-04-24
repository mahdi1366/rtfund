<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.01.22
//---------------------------
require_once '../../../header.inc.php';
require_once inc_dataGrid;

require_once '../js/evaluation_lists.js.php';

$dg = new sadaf_datagrid("EvalGrid", $js_prefix_address . "../data/evaluation.data.php?task=SelectEvalList", "EvalDIV");

$col = $dg->addColumn("شماره لیست", "list_id", "int");
$col->width = 100;

$col = $dg->addColumn("تاریخ", "list_date", "string");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->editor = ColumnEditor::SHDateField();
$col->width = 90;

$col = $dg->addColumn("عنوان واحد", "unit_full_title", "string",true);
$col->renderer = "function(v){ return ' '; }" ;

$col = $dg->addColumn("نوع شخص", "person_title", "string",true);
$col->renderer = "function(v){ return ' '; }" ;

$col = $dg->addColumn("واحد سازمانی", "ouid", "int");
$col->renderer = "function(v,p,r){return r.data.unit_full_title}";
$col->editor = "EvaluationListObject.UnitLOV";

$col = $dg->addColumn("نوع فرد", "person_type", "int");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_PersonType(), "InfoID", "Title");
$col->width = 90;

$col = $dg->addColumn("وضعیت", "doc_state", "int");
$col->editor = ColumnEditor::ComboBox(manage_domains::DRP_Doc_State(), "value", "caption");
$col->width = 90;

$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){return EvaluationList.opRender(v,p,r);}";
$col->width = 50;

	$dg->addButton = true;
	$dg->addHandler = "function(){EvaluationListObject.AddEvalList();}";

$dg->pageSize = "20";
$dg->EnableSearch = false ;
$dg->width = 600;
$dg->height = 630;
$dg->title = "لیست ارزشیابی";
$dg->autoExpandColumn = "ouid";
$dg->DefaultSortField = "list_date";

    $dg->enableRowEdit = true ;
    $dg->rowEditOkHandler = "function(v,p,r){ return EvaluationListObject.editValList(v,p,r);}";

$grid = $dg->makeGrid_returnObjects();

//..............................................................................


//..............................................................................

$drp_state = manage_domains::DRP_Doc_State2("doc_state","","with:50%") ;

?>
<script>
    EvaluationListObject.grid = <?=$grid?>;
   
    EvaluationListObject.grid.render("EvalDIV");
    
</script>
<center>
<form id="form_EvalList" >
	<div id="mainpanelDIV">
    <table id="EvalTBL" width="500">
    <input type='hidden' id='ouid' name='ouid'>
    <input type='hidden' id='person_type' name='person_type'>
        <tr>
            <td>شماره :</td><td class="blueText" id="list_id"></td>
        </tr>
         <tr>
            <td>مرکز هزینه:</td><td class="blueText" id="ouidTitle"></td>
            <td>تاریخ:</td><td class="blueText" id="list_date"></td>
        </tr>
        <tr>
            <td colspan="1">گروه : </td><td colspan="3" class="blueText" id="person_type_Title"></td>	   
        </tr>
	<tr>
            <td>وضعیت : </td><td ><?= $drp_state ?></td>
        </tr>
       
    </table>
    </div>
</form>
	<br>
    <div id="MemberEvalDIV" style="width:100%"></div>
	<div id="EvalDIV" style="width:100%"></div>
</center>
