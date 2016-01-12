<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.10
//-----------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "letter.data.php?task=SelectSendedLetters", "grid_div");

$dg->addColumn("", "LetterID", "", true);

$col = $dg->addColumn("<img src=/office/icons/LetterType.gif>", "LetterType", "");
$col->renderer = "SendedLetter.LetterTypeRender";
$col->width = 30;

$col = $dg->addColumn("<img src=/office/icons/attach.gif>", "hasAttach", "");
$col->renderer = "function(v,p,r){if(v == 'YES') return '<img src=/office/icons/attach.gif>';}";
$col->width = 30;

$col = $dg->addColumn("شماره", "LetterID", "");
$col->width = 60;
$col->align = "center";

$col = $dg->addColumn("موضوع نامه", "LetterTitle", "");

$col = $dg->addColumn("گیرنده", "ToPersonName", "");
$col->width = 150;

$col = $dg->addColumn("شرح ارجاع", "SendComment", "");
$col->ellipsis = 30;
$col->width = 150;

$col = $dg->addColumn("تاریخ ارجاع", "SendDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("عملیات", "");
$col->renderer = "function(v,p,r){return SendedLetter.OperationRender(v,p,r);}";
$col->width = 50;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 420;
$dg->width = 770;
$dg->title = "نامه های ارسالی";
$dg->DefaultSortField = "SendDate";
$dg->autoExpandColumn = "LetterTitle";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
	
SendedLetter.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function SendedLetter(){
	
	this.grid = <?= $grid ?>;
	this.grid.on("itemdblclick", function(view, record){
			
		framework.OpenPage("/office/letter/LetterInfo.php", "مشخصات نامه", 
		{
			LetterID : record.data.LetterID,
			SendID : record.data.SendID
		});

	});
	this.grid.render(this.get("DivGrid"))
}

SendedLetter.LetterTypeRender = function(v,p,r){
	
	if(v == 'INNER') 
		return "<img data-qtip='نامه داخلی' src=/office/icons/inner.gif>";
	if(v == 'INCOME') 
		return "<img data-qtip='نامه وارده' src=/office/icons/income.gif>";
	if(v == 'OUTCOME') 
		return "<img data-qtip='نامه صادره' src=/office/icons/outcome.gif>";
}

SendedLetterObject = new SendedLetter();

SendedLetter.OperationRender = function(v,p,r){
	
	return "<div  title='عملیات' class='setting' onclick='SendedLetterObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

SendedLetter.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();
	
	if(this.User == "Customer" && (record.data.StatusID == "40" || record.data.StatusID == "60"))
	{
		op_menu.add({text: 'تایید تکمیل مدارک',iconCls: 'tick', 
		handler : function(){ return SendedLetterObject.ChangeStatus(50); }});
	}	
	if(this.User == "Customer" && record.data.StatusID == "60")
	{
		op_menu.add({text: "دلیل رد مدارک",iconCls: 'comment', 
		handler : function(){ return SendedLetterObject.ShowComment(); }});
	}	
	
	if(this.User == "Customer" && record.data.LoanID > 0 && record.data.StatusID == "10")
	{
		op_menu.add({text: 'اطلاعات وام',iconCls: 'info2', 
		handler : function(){ return SendedLetterObject.EditRequest(false); }});
	}
	else
		op_menu.add({text: 'اطلاعات وام',iconCls: 'info2',	
		handler : function(){ return SendedLetterObject.EditRequest(true); }});
	
	
	
	op_menu.add({text: 'مدارک وام',iconCls: 'attach', 
		handler : function(){ return SendedLetterObject.LoadAttaches(); }});
	
	op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return SendedLetterObject.ShowHistory(); }});
	
	if(record.data.StatusID == "1")
	{
		op_menu.add({text: 'ویرایش درخواست',iconCls: 'edit', 
		handler : function(){ return SendedLetterObject.EditRequest(); }});
	
		op_menu.add({text: 'حذف درخواست',iconCls: 'remove',
		handler : function(){ return SendedLetterObject.DeleteRequest(); }});
	}
	
	op_menu.showAt(e.pageX-120, e.pageY);
}

SendedLetter.prototype.ShowHistory = function(){

	if(!this.HistoryWin)
	{
		this.HistoryWin = new Ext.window.Window({
			title: 'سابقه گردش درخواست',
			modal : true,
			autoScroll : true,
			width: 700,
			height : 500,
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "history.php",
				scripts : true
			},
			buttons : [{
					text : "بازگشت",
					iconCls : "undo",
					handler : function(){
						this.up('window').hide();
					}
				}]
		});
		Ext.getCmp(this.TabID).add(this.HistoryWin);
	}
	this.HistoryWin.show();
	this.HistoryWin.center();
	this.HistoryWin.loader.load({
		params : {
			ExtTabID : this.HistoryWin.getEl().id,
			LetterID : this.grid.getSelectionModel().getLastSelected().data.LetterID
		}
	});
}
</script>
<center>
	<br>
	<div id="DivGrid"></div>
</center>