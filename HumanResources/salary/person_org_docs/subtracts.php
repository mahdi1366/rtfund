<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.07
//-----------------------------

require_once '../../header.inc.php';
require_once inc_dataGrid;

//if($_SESSION['UserID'] != 'jafarkhani' ) {
	//die() ; 
//} 
$subtract_type = $_REQUEST["subtract_type"];
switch($subtract_type)
{
	case SUBTRACT_TYPE_LOAN : 
		$subtractTitle = "وام ها";
		break;
	case SUBTRACT_TYPE_FIX_FRACTION : 
		$subtractTitle = "کسورات";
		break;
	case SUBTRACT_TYPE_FIX_BENEFIT : 
		$subtractTitle = "مزایا";
		break;
}
require_once 'subtracts.js.php';

$dgh = new sadaf_datagrid("dg",$js_prefix_address."subtracts.data.php?task=AllSubtracts&subtract_type=" . $subtract_type,"div_dg");

$dgh->addColumn("", "subtract_id","",true);
$dgh->addColumn("", "IsEditable","",true);
$dgh->addColumn("", "comments","",true);
$dgh->addColumn("", "bank_id","",true);

$col = $dgh->addColumn("کدقلم", "salary_item_type_id");
$col->width = 60;

$col = $dgh->addColumn("قلم حقوقی", "full_title");
$col = $dgh->addColumn("بانک/صندوق", "bankTitle");
$col->width = 70;

if($subtract_type == SUBTRACT_TYPE_LOAN)
{
	$col = $dgh->addColumn("شماره وام", "loan_no");
	$col->width = 90;

	$col = $dgh->addColumn("ش قرارداد", "contract_no");
	$col->width = 70;
	
	$col = $dgh->addColumn("موجودی اولیه", "first_value", GridColumn::ColumnType_money);
	$col->width = 90;
}

$col = $dgh->addColumn("مبلغ ماهانه", "instalment", GridColumn::ColumnType_money);
$col->width = 70;

if($subtract_type == SUBTRACT_TYPE_LOAN)
{
	$col = $dgh->addColumn("مبلغ مانده", "remainder", GridColumn::ColumnType_money);
	$col->width = 70;
}
else
{
	$col = $dgh->addColumn("مجموع پرداختی", "receipt", GridColumn::ColumnType_money);
	$col->width = 100;
}
$col = $dgh->addColumn("تاریخ شروع", "start_date", GridColumn::ColumnType_date);
$col->width = 70;

$col = $dgh->addColumn("تاریخ پایان", "end_date", GridColumn::ColumnType_date);
$col->width = 70;

$col = $dgh->addColumn("", "", "string");
$col->sortable = false;
$col->renderer = "Subtract.OperationRender";
$col->width = 30;

$dgh->addButton = true;
$dgh->addHandler = "function(){SubtractObj.BeforeEdit(false);}";

$dgh->width = 850;
$dgh->DefaultSortField = "subtract_id";
$dgh->autoExpandColumn = "full_title";
$dgh->DefaultSortDir = "DESC";
$dgh->height = 460;
$dgh->pageSize = 15;
$dgh->EnableSearch = false;
$dgh->EnablePaging = false;
$grid = $dgh->makeGrid_returnObjects();

//..............................................................................
$dgh = new sadaf_datagrid("dg",$js_prefix_address."subtracts.data.php?task=AllSubtractFlows");

$dgh->addColumn("", "subtract_id","",true);
$dgh->addColumn("", "row_no","",true);
$dgh->addColumn("", "alter","",true);
$dgh->addColumn("", "IsEditable","",true);
$dgh->addColumn("", "flow_type","",true);

$col = $dgh->addColumn("تاریخ گردش", "flow_date", GridColumn::ColumnType_date);
$col->width = 100;

$col = $dgh->addColumn("نوع گردش", "flow_coaf");
$col->editor = ColumnEditor::ComboBox(array( array("id" => "1", "title" => "کسر از وام"), array("id" => "-1", "title" => "افزودن به وام") ), "id", "title", "","cmp_flow");
$col->width = 100;

$col = $dgh->addColumn("مبلغ", "amount", GridColumn::ColumnType_money);
$col->editor = ColumnEditor::CurrencyField(false,"cmp_amount");
$col->width = 100;

$col = $dgh->addColumn("گردش موقت", "tempFlow");
$col->editor = ColumnEditor::CheckField("cmp_tempFlow");
$col->renderer = "function(v){
					if (v==0) return 'خیر'  ;
					if (v==1) return 'بلی' ; 
				  }";
$col->width = 80;

$col = $dgh->addColumn("توضیحات", "comments","");
$col->editor = ColumnEditor::TextField(true);

$col = $dgh->addColumn("", "", "string");
$col->sortable = false;
$col->renderer = "Subtract.deleteRender";
$col->width = 30;

$dgh->addButton = true;
$dgh->addHandler = "function(){SubtractObj.BeforeAddFlow();}";
$dgh->enableRowEdit = true ;
$dgh->rowEditOkHandler = "function(s,r){ return SubtractObj.SaveFlow(s,r);}";
	
$dgh->width = 700;
$dgh->DefaultSortField = "flow_date";
$dgh->autoExpandColumn = "comments";
$dgh->DefaultSortDir = "DESC";
$dgh->height = 430;
$dgh->pageSize = 15;
$dgh->EnableSearch = false;
$dgh->EnablePaging = false;
$grid2 = $dgh->makeGrid_returnObjects();
?>
<style type="text/css">
.pinkRow, .pinkRow td,.pinkRow div{ background-color:#FFB8C9 !important;}
.greenRow,.greenRow td,.greenRow div{ background-color:#D0F7E2 !important;}
</style>
<script>
SubtractObj.grid = <?=$grid?>;
SubtractObj.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.locked == "1")
			return "greenRow";
		return "";
	}
	
SubtractObj.flowGrid = <?=$grid2?>;
SubtractObj.flowGrid.plugins[0].on("beforeedit", function(editor,e){

	if(e.record.data.row_no > 0)
	{
		editor.editor.down("[itemId=cmp_flow]").disable();
		editor.editor.down("[itemId=cmp_amount]").disable();
		editor.editor.down("[itemId=cmp_tempFlow]").disable(); 
		
	}		
	if( e.record.data.row_no == "" ) {	
		editor.editor.down("[itemId=cmp_flow]").enable();
		editor.editor.down("[itemId=cmp_amount]").enable();
		editor.editor.down("[itemId=cmp_tempFlow]").enable();
		
	}
	return true ; 
});

</script>
<form id="mainForm">
	<center><br>
		<div><div id="selectPersonDIV" ></div></div>
		<br>
		<div><div id="div_dg"></div></div>
	</center>
</form>