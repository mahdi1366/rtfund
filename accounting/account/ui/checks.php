<?php 
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

require_once '../../header.inc.php';
$docID = $_REQUEST["docID"];
$docStatus = $_REQUEST["docStatus"];
$isRAW = $docStatus == "RAW" ? true : false;

$dgh = new sadaf_datagrid("dg",$js_prefix_address."../data/acc_docs.data.php?task=selectChecks&docID=". $docID,"div_dg");

$dgh->addColumn("","docID","",true);
$col = $dgh->addColumn("","accountTitle","",true);
$col->renderer = "function(){return '';}";
$col = $dgh->addColumn("","tafsiliTitle","",true);
$col->renderer = "function(){return '';}";

$col = $dgh->addColumn("کد","checkID","",true);
$col->width = 50;

$col = $dgh->addColumn("حساب", "accountID");
$col->renderer = "function(v,p,r){return r.data.accountTitle;}";
$col->editor = "AccDocsObject.accountCombo";
$col->width = 80;

$col = $dgh->addColumn("شماره چک", "checkNo");
$col->editor = ColumnEditor::TextField(true, "cmp_checkNo");
$col->width = 60;

$col = $dgh->addColumn("تاریخ سررسید", "checkDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 70;

$col = $dgh->addColumn("مبلغ", "amount", GridColumn::ColumnType_money);
$col->editor = ColumnEditor::CurrencyField();
$col->width = 80;

$col = $dgh->addColumn("در وجه", "reciever");
$col->editor = ColumnEditor::TextField(true);

$col = $dgh->addColumn("در وجه(تفصیلی)", "tafsiliID");
$col->editor = "AccDocsObject.checktafsiliCombo";
$col->renderer = "function(v,p,r){return r.data.tafsiliTitle;}";

$col = $dgh->addColumn("بابت", "description");
$col->editor = ColumnEditor::TextField(true);

$col = $dgh->addColumn("وضعیت", "checkTitle");
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

$dgh->addColumn("", "checkStatus","",true);
$dgh->addColumn("", "PrintPage1","",true);
$dgh->addColumn("", "PrintPage2","",true);

$dgh->width = 780;
$dgh->DefaultSortField = "checkID";
$dgh->autoExpandColumn = "tafsiliID";
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
	AccDocsObject.checkGrid.plugins[0].getEditor().down("[itemId=cmp_checkNo]").on("blur",function(){
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
	window.open(this.address_prefix + record.data.PrintPage1 + "?checkID=" + record.data.checkID);
	
	Ext.Ajax.request({
		url: this.address_prefix + '../data/acc_docs.data.php?task=RegisterCheck',
		method: 'POST',
		params: {
			checkID : record.data.checkID
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

		