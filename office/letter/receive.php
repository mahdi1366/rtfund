<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.10
//-----------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "letter.data.php?task=SelectReceivedLetters", "grid_div");

$dg->addColumn("", "LetterID", "", true);
$dg->addColumn("", "IsSeen", "", true);

$col = $dg->addColumn("<img src=/office/icons/LetterType.gif>", "LetterType", "");
$col->renderer = "ReceivedLetter.LetterTypeRender";
$col->width = 30;

$col = $dg->addColumn("<img src=/office/icons/attach.gif>", "HasAttach", "");
$col->renderer = "function(v,p,r){if(v == 'YES') return '<img src=/office/icons/attach.gif>';}";
$col->width = 30;

$col = $dg->addColumn("شماره", "LetterID", "");
$col->width = 60;
$col->align = "center";

$col = $dg->addColumn("موضوع نامه", "LetterTitle", "");

$col = $dg->addColumn("فرستنده", "FromPersonName", "");
$col->width = 150;

$col = $dg->addColumn("شرح ارجاع", "SendComment", "");
$col->ellipsis = 30;
$col->width = 150;

$col = $dg->addColumn("تاریخ ارجاع", "SendDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("عملیات", "");
$col->renderer = "function(v,p,r){return ReceivedLetter.OperationRender(v,p,r);}";
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
	
ReceivedLetter.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ReceivedLetter(){
	
	this.grid = <?= $grid ?>;
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsSeen == "NO")
			return "yellowRow";
		return "";
	}
	this.grid.on("itemdblclick", function(view, record){
			
		framework.OpenPage("/office/letter/LetterInfo.php", "مشخصات نامه", {LetterID : record.data.LetterID});

	});

	this.grid.render(this.get("DivGrid"));
}

ReceivedLetter.LetterTypeRender = function(v,p,r){
	
	if(v == 'INNER') 
		return "<img data-qtip='نامه داخلی' src=/office/icons/inner.gif>";
	if(v == 'INCOME') 
		return "<img data-qtip='نامه وارده' src=/office/icons/income.gif>";
	if(v == 'OUTCOME') 
		return "<img data-qtip='نامه صادره' src=/office/icons/outcome.gif>";
}

ReceivedLetterObject = new ReceivedLetter();

ReceivedLetter.OperationRender = function(v,p,r){
	
	return "<div  title='عملیات' class='setting' onclick='ReceivedLetterObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

ReceivedLetter.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();
		
	op_menu.add({text: 'ارجاع نامه',iconCls: 'sendLetter', 
		handler : function(){ return ReceivedLetterObject.SendLetter(); }});
	
	op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return ReceivedLetterObject.ShowHistory(); }});
	
	op_menu.showAt(e.pageX-120, e.pageY);
}

ReceivedLetter.prototype.SendLetter = function(){
	
	if(!this.SendingWin)
	{
		this.SendingWin = new Ext.window.Window({
			title : "ارجاع نامه",
			width : 462,			
			height : 435,
			modal : true,
			bodyStyle : "background-color:white;",
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "sending.php",
				scripts : true
			}
		});
		Ext.getCmp(this.TabID).add(this.SendingWin);
	}

	this.SendingWin.show();
	this.SendingWin.center();
	
	this.SendingWin.loader.load({
		scripts : true,
		params : {
			ExtTabID : this.SendingWin.getEl().id,
			parent : "ReceivedLetterObject.SendingWin",
			AfterSendHandler : function(){
				ReceivedLetterObject.grid.getStore().load();
			},
			LetterID : this.grid.getSelectionModel().getLastSelected().data.LetterID
		}
	});
}

ReceivedLetter.prototype.ShowHistory = function(){

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