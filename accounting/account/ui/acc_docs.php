<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.10
//-----------------------------

require_once '../../header.inc.php';
//________________  GET ACCESS  _________________
$accessObj = manage_access::getAccess($_POST["formID"]);
//-----------------------------------------------
require_once '../js/acc_docs.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address."../data/acc_docs.data.php?task=selectDocs","div_dg");

$dg->addColumn("کد سند","docID","",true);
$dg->addColumn("","docStatus","",true);
$dg->addColumn("تاریخ سند","docDate","",true);
$dg->addColumn("تاریخ ثبت","regDate","",true);
$col = $dg->addColumn("توضیحات","description","",true);
$col->renderer = "function(){return '';}";
$dg->addColumn("شماره سند عطف","ref_docID","",true);
$dg->addColumn("شماره سند انبار","storeDocID","",true);
$dg->addColumn("نوع سند","docType","",true);
$dg->addColumn("ثبت کننده سند","regPerson","",true);
$dg->addColumn("","atf","",true);
$dg->addColumn("","detail","",true);

$col = $dg->addColumn("اطلاعات سند","docID");
$col->renderer = "AccDocs.docRender";

$dg->title = "سند های حسابداری";
$dg->width = 780;
$dg->DefaultSortField = "docID";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "docID";
$dg->hideHeaders = true;
$dg->pageSize = 1;

$grid = $dg->makeGrid_returnObjects();

//--------------------------------------------
$dg = new sadaf_datagrid("dg",$js_prefix_address."../data/acc_docs.data.php?task=selectDocItems","div_detail_dg");

$dg->addColumn("","docID","",true);

$col = $dg->addColumn("ردیف","rowID","", true);
$col->width = 60;

$col = $dg->addColumn("کل", "kolID");
$col->editor = "AccDocsObject.kolCombo";
$col->renderer = "function(v,p,r){return r.data.kolTitle;}";
$col->width = 100;

$col = $dg->addColumn("معین", "moinID");
$col->editor = "AccDocsObject.moinCombo";
$col->renderer = "function(v,p,r){return r.data.moinTitle;}";
$col->width = 100;

$col = $dg->addColumn("تفصیلی", "tafsiliID");
$col->editor = "AccDocsObject.tafsiliCombo";
$col->renderer = "function(v,p,r){return r.data.tafsiliTitle;}";
$col->width = 100;

$col = $dg->addColumn("تفصیلی 2", "tafsili2ID");
$col->editor = "AccDocsObject.tafsili2Combo";
$col->summaryRenderer = "function(){return 'جمع : ';}";
$col->renderer = "function(v,p,r){return r.data.tafsiliTitle2;}";
$col->width = 100;

$col = $dg->addColumn("مبلغ بدهکار", "bdAmount", GridColumn::ColumnType_money);
$col->editor = ColumnEditor::CurrencyField(true, "cmp_bdAmount");
//$col->summaryRenderer = "function(value){return Ext.util.Format.Money(value) + ' ریال ';}";
//$col->summaryType = GridColumn::SummeryType_sum;
$col->width = 100;

$col = $dg->addColumn("مبلغ بستانکار", "bsAmount", GridColumn::ColumnType_money);
$col->editor = ColumnEditor::CurrencyField(true, "cmp_bsAmount");
//$col->summaryRenderer = "function(value){return Ext.util.Format.Money(value) + ' ریال ';}";
//$col->summaryType = GridColumn::SummeryType_sum;
$col->width = 100;

$col = $dg->addColumn("جزئیات", "details");
$col->editor = ColumnEditor::TextField(true, "cmp_details");

if($accessObj->removeFlag)
{
    $col = $dg->addColumn("", "", "string");
    $col->sortable = false;
    $col->renderer = "AccDocs.deleteitemRender";
    $col->width = 30;	
}
$col = $dg->addColumn("", "kolTitle", "", true);
$col->renderer = "function(){return '';}";
$col = $dg->addColumn("", "moinTitle", "", true);
$col->renderer = "function(){return '';}";
$col = $dg->addColumn("", "tafsiliTitle", "", true);
$col->renderer = "function(){return '';}";
$col = $dg->addColumn("", "tafsiliTitle2", "", true);
$col->renderer = "function(){return '';}";

$dg->addColumn("", "locked", "", true);
if($accessObj->addFlag)
{
	$dg->addButton("", "ایجاد ردیف سند", "add", "function(v,p,r){ return AccDocsObject.AddItem(v,p,r);}");
}

//$dg->EnableSummaryRow = true;
$dg->DefaultSortField = "rowID";
$dg->autoExpandColumn = "details";
//$dg->EnablePaging = false;
//$dg->EnableSearch = false;
$dg->DefaultSortDir = "ASC";
$dg->height = 320;
if($accessObj->addFlag || $accessObj->editFlag)
{
	$dg->enableRowEdit = true ;
	$dg->rowEditOkHandler = "function(v,p,r){ return AccDocsObject.SaveItem(v,p,r);}";
}
$itemsgrid = $dg->makeGrid_returnObjects();

?>
<script>
AccDocsObject.grid = <?= $grid ?>;
AccDocsObject.itemGrid = <?= $itemsgrid ?>;

AccDocsObject.grid.addDocked(
{
	xtype: 'toolbar',
	dock : "bottom",
	defaults: {
		scale: 'small'
	},
   items: ['-'
	<?if($accessObj->addFlag){?>
	,{
		text: 'ایجاد',
		iconCls: 'add',
		handler : function(v,p,r){ return AccDocsObject.Add(v,p,r);}
	},'-'
	<?}if($accessObj->editFlag){?>
	,{
		itemId : "updateDoc",
		text: 'ویرایش',
		iconCls: 'edit',
		handler : function(v,p,r){ return AccDocsObject.Edit(v,p,r);}
	},'-',{
		itemId : "copyDoc",
		text: 'کپی',
		iconCls: 'copy',
		handler : function(v,p,r){ return AccDocsObject.Copy(v,p,r);}
	},'-',{
		itemId : "confirmDoc",
		text : "تایید",
		iconCls : "tick",
		handler : function(v,p,r){ return AccDocsObject.confirmDoc(v,p,r);}
	},'-',{
		itemId : "archiveDoc",
		text : "بایگانی",
		iconCls : "archive",
		handler : function(v,p,r){ return AccDocsObject.archiveDoc(v,p,r);}
	},'-',{
		itemId : "printDoc",
		text : "چاپ",
		iconCls : "print",
		disabled : true,
		handler : function(v,p,r){ return AccDocsObject.PrintDoc(v,p,r);}
	}
	<?}if($accessObj->removeFlag){?>
	,{
		itemId : "deleteDoc",
		text: 'حذف',
		iconCls: 'remove',
		handler : function(v,p,r){ return AccDocsObject.remove(v,p,r);}
	},'-'
	<?}?>
	,{
		text: 'تقسیم سود سهام',
		iconCls: 'account',
		handler : function(v,p,r){ return AccDocsObject.shareCompute(v,p,r);}
	}
	,{
		text: 'صدور سند فاکتورهای خرید',
		iconCls: 'account',
		handler : function(v,p,r){ return AccDocsObject.storeDocRegister(v,p,r);}
	},'-']
},1);

AccDocsObject.grid.getView().getRowClass = function(record, index)
{
	if(record.data.docStatus == "DELETED")
		return "pinkRow";
	if(record.data.docType == "SUMMARY")
		return "violetRow";
	if(record.data.docType == "SHARE")
		return "bluegreenRow";
	if(record.data.docStatus == "CONFIRM")
		return "greenRow";
	if(record.data.docStatus == "ARCHIVE")
		return "yallowRow";
	
	
	
	return "";
}

AccDocsObject.grid.getStore().on("load", AccDocsObject.afterHeaderLoad);			
AccDocsObject.grid.render(AccDocsObject.get("div_dg"));
</script>
<style type="text/css">
.pinkRow, .pinkRow td,.pinkRow div{ background-color:#FFB8C9 !important;}
.greenRow,.greenRow td,.greenRow div{ background-color:#D0F7E2 !important;}
.yallowRow,.yallowRow td,.yallowRow div{ background-color:#FCFCB6 !important;}
.bluegreenRow,.bluegreenRow td,.bluegreenRow div{ background-color:#89F5F5 !important;}

.barcode {background-image:url('../img/barcode.png') !important;}
.label {background-image:url('../img/label.png') !important;}
.check {background-image:url('../img/check.png') !important;}
.archive {background-image:url('../img/archive.png') !important;}
.docInfo td{height:20px;}
.blue{ color: #1E4685; font-weight:bold;}
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