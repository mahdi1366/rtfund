<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................
require_once 'docs.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "doc.data.php?task=selectDocs","div_dg");

$dg->addColumn("کد سند","DocID","",true);
$dg->addColumn("","DocStatus","",true);
$dg->addColumn("تاریخ سند","DocDate","",true);
$dg->addColumn("تاریخ ثبت","RegDate","",true);
$dg->addColumn("توضیحات","description","",true);
$dg->addColumn("کد سند","LocalNo","",true);
$dg->addColumn("نوع سند","DocType","",true);
$dg->addColumn("ثبت کننده سند","regPerson","",true);

$col = $dg->addColumn("اطلاعات سند","DocID");
$col->renderer = "AccDocs.docRender";

$dg->addButton('HeaderBtn', 'عملیات', 'list', 'function(e){ return AccDocsObject.operationhMenu(e); }');

$dg->title = "سند های حسابداری";
$dg->width = 780;
$dg->DefaultSortField = "DocID";
$dg->DefaultSortDir = "DESC";
$dg->autoExpandColumn = "DocID";
$dg->emptyTextOfHiddenColumns = true;
$dg->hideHeaders = true;
$dg->pageSize = 1;
$dg->disableChangePageSize = true;
$dg->PageSizeChange = false;
$dg->EnableRowNumber = false;
$dg->hideHeaders = true;
$dg->EnableSearch = false;
$grid = $dg->makeGrid_returnObjects();

//--------------------------------------------
$dg = new sadaf_datagrid("dg",$js_prefix_address . "doc.data.php?task=selectDocItems","div_detail_dg");

$dg->addColumn("","DocID","",true);
$dg->addColumn("","CostID","",true);
$dg->addColumn("","CostCode","",true);
$dg->addColumn("","TafsiliType","",true);
$dg->addColumn("","TafsiliType2","",true);
$dg->addColumn("","TafsiliDesc","",true);
$dg->addColumn("","TafsiliGroupDesc","",true);
$dg->addColumn("","Tafsili2Desc","",true);
$dg->addColumn("","Tafsili2GroupDesc","",true);
$dg->addColumn("", "locked", "", true);

$col = $dg->addColumn("ردیف","ItemID","", true);

$col = $dg->addColumn("سرفصل حساب", "CostDesc");
$col->renderer = "function(v,p,r){return '[' + r.data.CostCode + '] ' + v;}";

/*$col = $dg->addColumn("گروه تفصیلی", "TafsiliType");
$col->editor = "AccDocsObject.tafsiliGroupCombo";
$col->renderer = "function(v,p,r){return r.data.TafsiliGroupDesc;}";
$col->width = 100;*/

$col = $dg->addColumn("تفصیلی", "TafsiliID");
//$col->editor = "AccDocsObject.tafsiliCombo";
$col->renderer = "function(v,p,r){p.tdAttr = \"data-qtip='\" + r.data.TafsiliGroupDesc + \"'\";".
		"return r.data.TafsiliDesc;}";
$col->width = 150;

/*$col = $dg->addColumn("گروه تفصیلی2", "TafsiliType2");
$col->editor = "AccDocsObject.tafsili2GroupCombo";
$col->renderer = "function(v,p,r){return r.data.Tafsili2GroupDesc;}";
$col->width = 100;*/

$col = $dg->addColumn("تفصیلی2", "TafsiliID2");
//$col->editor = "AccDocsObject.tafsili2Combo";
$col->renderer = "function(v,p,r){p.tdAttr = \"data-qtip='\" + r.data.Tafsili2GroupDesc + \"'\";".
		"return r.data.Tafsili2Desc;}";
$col->width = 150;

$col = $dg->addColumn("بدهکار", "DebtorAmount", GridColumn::ColumnType_money);
//$col->editor = ColumnEditor::CurrencyField(true, "cmp_DebtorAmount");
$col->width = 90;

$col = $dg->addColumn("بستانکار", "CreditorAmount", GridColumn::ColumnType_money);
//$col->editor = ColumnEditor::CurrencyField(true, "cmp_CreditorAmount");
$col->width = 90;

$col = $dg->addColumn("جزئیات", "details");
//$col->editor = ColumnEditor::TextField(true, "cmp_details");
$col->width = 180;
$col->ellipsis = 50;

if($accessObj->RemoveFlag)
{
    $col = $dg->addColumn("", "", "string");
    $col->sortable = false;
    $col->renderer = "AccDocs.deleteitemRender";
    $col->width = 50;	
}

if($accessObj->AddFlag)
{
	$dg->addButton("", "ایجاد ردیف سند", "add", "function(v,p,r){ return AccDocsObject.AddItem(v,p,r);}");
}

//$dg->enableRowEdit = true ;
//$dg->rowEditOkHandler = "function(store,record){ return AccDocsObject.SaveItem(store,record);}";

$dg->DefaultSortField = "ItemID";
$dg->autoExpandColumn = "CostDesc";
$dg->DefaultSortDir = "ASC";
$dg->emptyTextOfHiddenColumns = true;
$dg->height = 320;
$itemsgrid = $dg->makeGrid_returnObjects();
//---------------------------------------------------
$dgh = new sadaf_datagrid("dg",$js_prefix_address."doc.data.php?task=selectCheques","div_dg");

$dgh->addColumn("","DocID","",true);
$dgh->addColumn("","AccountDesc","",true);
$dgh->addColumn("","TafsiliDesc","",true);
$dgh->addColumn("","StatusTitle","",true);

$col = $dgh->addColumn("کد","ChequeID","",true);
$col->width = 50;

$col = $dgh->addColumn("حساب", "AccountID");
$col->renderer = "function(v,p,r){return r.data.AccountDesc;}";
$col->editor = "AccDocsObject.accountCombo";
$col->width = 150;

$col = $dgh->addColumn("شماره چک", "CheckNo");
$col->editor = ColumnEditor::TextField(true, "cmp_CheckNo");
$col->width = 70;

$col = $dgh->addColumn("تاریخ چک", "CheckDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 70;

$col = $dgh->addColumn("مبلغ", "amount", GridColumn::ColumnType_money);
$col->editor = ColumnEditor::CurrencyField();
$col->width = 80;

$col = $dgh->addColumn("در وجه", "TafsiliID");
$col->renderer = "function(v,p,r){return r.data.TafsiliDesc;}";
$col->editor = "AccDocsObject.checkTafsiliCombo";

$col = $dgh->addColumn("بابت", "description");
$col->editor = ColumnEditor::TextField(true);

$col = $dgh->addColumn("وضعیت", "CheckStatus");
$col->editor = "AccDocsObject.ChequeStatusCombo";
$col->renderer = "function(v,p,r){return r.data.StatusTitle}";
$col->width = 80;

if($accessObj->RemoveFlag)
{
	$col = $dgh->addColumn("حذف", "", "string");
	$col->renderer = "AccDocsObject.check_deleteRender";
	$col->width = 50;
	$col->align = "center";
}
if($accessObj->AddFlag)
{
	$dgh->addButton = true;
	$dgh->addHandler = "function(v,p,r){ return AccDocsObject.check_Add(v,p,r);}";
}

$dgh->enableRowEdit = true ;
$dgh->rowEditOkHandler = "function(v,p,r){ return AccDocsObject.check_Save(v,p,r);}";

$dgh->addButton("", "چاپ چک", "print", "function(){ return AccDocsObject.printCheck();}");

$dgh->addColumn("", "CheckStatus","",true);
$dgh->addColumn("", "PrintPage1","",true);
$dgh->addColumn("", "PrintPage2","",true);

$dgh->width = 780;
$dgh->DefaultSortField = "ChequeID";
$dgh->autoExpandColumn = "description";
$dgh->emptyTextOfHiddenColumns = true;
$dgh->DefaultSortDir = "ASC";
$dgh->height = 315;
$dgh->EnableSearch = false;
$dgh->EnablePaging = false;

$checksgrid = $dgh->makeGrid_returnObjects();
?>
<script>
AccDocsObject.grid = <?= $grid ?>;
AccDocsObject.grid.getView().getRowClass = function(record, index)
{
	if(record.data.DocStatus == "CONFIRM")
		return "greenRow";
	if(record.data.DocStatus == "ARCHIVE")
		return "yellowRow";
	
	return "";
}

var pagingToolbar = AccDocsObject.grid.getDockedItems('pagingtoolbar')[0];
pagingToolbar.dock = "top";
pagingToolbar.down("[itemId=inputItem]").hide();
pagingToolbar.down("[itemId=displayItem]").hide();

pagingToolbar.add([{
	xtype : "numberfield", 
	itemId : "Number",
	value: '',
	hideTrigger: true,
	size:5,
	fieldLabel:'شماره سند',
	labelWidth:65,
	width:120
},{
	xtype : "button",
	iconCls : "search",
	handler : function(){
		return AccDocsObject.SearchDoc();}
}]);

AccDocsObject.grid.getStore().on("load", AccDocsObject.afterHeaderLoad);			
AccDocsObject.grid.render(AccDocsObject.get("div_dg"));

bars = AccDocsObject.grid.getDockedItems();
bars[2].add(bars[1].items.items);
bars[1].hide();

//...................................................
AccDocsObject.itemGrid = <?= $itemsgrid ?>;
//AccDocsObject.itemGrid.plugins[0].on("beforeedit", AccDocs.beforeRowEdit);
AccDocsObject.itemGrid.getView().getRowClass = function(record, index)
{
	if(record.data.locked == "YES")
		return "violetRow";
	return "";
}

//...................................................
AccDocsObject.checkGrid = <?= $checksgrid ?>;
AccDocsObject.checkGrid.plugins[0].on("beforeedit", AccDocs.beforeCheckEdit);
AccDocsObject.checkGrid.plugins[0].on("beforeedit", function(editor,e){
	if(!e.record.data.ChequeID)
		return AccDocsObject.AddAccess;
	return AccDocsObject.EditAccess;
});
//...................................................
</script>
<style type="text/css">
.check {background-image:url('/framework/icons/check.png') !important;}
.archive {background-image:url('/framework/icons/archive.png') !important;}
.docInfo td{height:20px;}
</style>
<center>
<form id="mainForm">
	<br><div id="div_dg"></div>
	<br>
	<div id="div_tab" >
		<div id="tabitem_rows">
			<div style="margin-left:10px;margin-right: 10px" id="div_detail_dg"></div>
		</div>
	</div>	
</form>
<div id="fs_summary"></div>
<div id="div_checksWin" class="x-hidden"></div>
</center>