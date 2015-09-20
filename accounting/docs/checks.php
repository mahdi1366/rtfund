<?php 
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$DocID = $_REQUEST["DocID"];
$DocStatus = $_REQUEST["DocStatus"];
$isRAW = $DocStatus == "RAW" ? true : false;

$dgh = new sadaf_datagrid("dg",$js_prefix_address."doc.data.php?task=selectChecks&DocID=". $DocID,"div_dg");

$dgh->addColumn("","DocID","",true);
$dgh->addColumn("","AccountDesc","",true);

$col = $dgh->addColumn("کد","CheckID","",true);
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

$col = $dgh->addColumn("در وجه", "reciever");
$col->editor = ColumnEditor::TextField(true);

$col = $dgh->addColumn("بابت", "description");
$col->editor = ColumnEditor::TextField(true);

$col = $dgh->addColumn("وضعیت", "StatusTitle");
$col->width = 60;

if($isRAW)
{
	$col = $dgh->addColumn("حذف", "", "string");
	$col->renderer = "AccDocsObject.check_deleteRender";
	$col->width = 50;
	$col->align = "center";
}
if($isRAW)
{
	$dgh->addButton = true;
	$dgh->addHandler = "function(v,p,r){ return AccDocsObject.check_Add(v,p,r);}";
}

$dgh->addButton("", "چاپ چک", "print", "function(){ return AccDocsObject.printCheck();}");

$dgh->addColumn("", "CheckStatus","",true);
$dgh->addColumn("", "PrintPage1","",true);
$dgh->addColumn("", "PrintPage2","",true);

$dgh->width = 780;
$dgh->DefaultSortField = "CheckID";
$dgh->autoExpandColumn = "description";
$dgh->emptyTextOfHiddenColumns = true;
$dgh->DefaultSortDir = "ASC";
$dgh->height = 315;
$dgh->EnableSearch = false;
$dgh->EnablePaging = false;
if($isRAW)
{
	$dgh->enableRowEdit = true ;
	$dgh->rowEditOkHandler = "function(v,p,r){ return AccDocsObject.check_Save(v,p,r);}";
}
$grid = $dgh->makeGrid_returnObjects();

?>
<script>

AccDocsObject.checkGrid = <?= $grid ?>;
AccDocsObject.checkGrid.render(AccDocsObject.get("div_check"));
if(AccDocsObject.checkGrid.plugins[0])
	AccDocsObject.checkGrid.plugins[0].getEditor().down("[itemId=cmp_CheckNo]").on("blur",function(){
		var record = AccDocsObject.accountCombo.findRecordByValue(AccDocsObject.accountCombo.value);

		if((this.value*1 < record.data.StartNo*1 || this.value*1 > record.data.EndNo*1) && 
			(this.value*1 < record.data.StartNo2*1 || this.value*1 > record.data.EndNo2*1))
		{
			alert("شماره چک در محدوده دسته چک تعریف شده نمی باشد.");
			//this.setValue();
			//this.focus();
		}
	});


AccDocsObject.printCheck = function()
{
	var record = this.checkGrid.getSelectionModel().getLastSelected();
	window.open(this.address_prefix + record.data.PrintPage1 + "?CheckID=" + record.data.CheckID);
	
	Ext.Ajax.request({
		url: this.address_prefix + '../data/ACC_docs.data.php?task=RegisterCheck',
		method: 'POST',
		params: {
			CheckID : record.data.CheckID
		},

		success: function(response){
			var sd = Ext.decode(response.responseText);
			if(sd.success)
				AccDocsObject.checkGrid.getStore().load();
		},
		failure: function(){}
	});
}

</script>
<div></div>
<div id="div_check"></div>

		