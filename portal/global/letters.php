<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.10
//-----------------------------
require_once getenv("DOCUMENT_ROOT") . '/portal/header.inc.php';
require_once inc_dataGrid;
require_once inc_component;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "global.data.php?task=CustomerLetters" , "grid_div");

$dg->addColumn("", "LetterID", "", true);
$dg->addColumn("", "LetterDate", "", true);

$col = $dg->addColumn("<img src=/office/icons/LetterType.gif>", "LetterType", "");
$col->renderer = "PortalLetters.LetterTypeRender";
$col->width = 30;

$col = $dg->addColumn("فوری", "IsUrgent", "");
$col->renderer = "function(v,p,r){if(v == 'YES') return '<img width=16px src=/office/icons/light.gif>';}";
$col->width = 30;

$col = $dg->addColumn("شماره", "LetterID", "");
$col->width = 60;
$col->align = "center";

$col = $dg->addColumn("تاریخ نامه", "LetterDate", GridColumn::ColumnType_date);
$col->width = 100;
$col->align = "center";

$col = $dg->addColumn("موضوع نامه", "LetterTitle", "");
$col->renderer = "PortalLetters.TitleRender";

$col = $dg->addColumn("مشاهده نامه", "");
$col->align = "center";
$col->renderer = "function(v,p,r){return PortalLetters.OperationRender(v,p,r);}";
$col->width = 100;

$dg->emptyTextOfHiddenColumns = true;
$dg->width = 750;
$dg->title = "لیست نامه های مربوط به شما";
$dg->DefaultSortField = "LetterDate";
$dg->autoExpandColumn = "LetterTitle";
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$grid = $dg->makeGrid_returnObjects();	
?>
<script>
	
PortalLetters.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function PortalLetters(){
	
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("DivPanel"));
}

PortalLetters.LetterTypeRender = function(v,p,r){
	
	if(v == 'INNER') 
		return "<img data-qtip='نامه داخلی' src=/office/icons/inner.gif>";
	if(v == 'INCOME') 
		return "<img data-qtip='نامه وارده' src=/office/icons/income.gif>";
	if(v == 'OUTCOME') 
		return "<img data-qtip='نامه صادره' src=/office/icons/outcome.gif>";
}

PortalLettersObject = new PortalLetters();

PortalLetters.OperationRender = function(v,p,r){
	
	return "<div  title='اطلاعات نامه' class='info2' "+
		" onclick='PortalLettersObject.LetterInfo();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:20px;height:16'></div>" ;
}

PortalLetters.prototype.LetterInfo = function(){
	
	if(!this.LetterWin)
	{
		this.LetterWin = new Ext.window.Window({
			title: 'اطلاعات نامه',
			modal : true,
			width: 630,
			height : 580,
			closeAction : "hide",
			loader : {
				url : "/office/letter/LetterInfo.php?ReadOnly=true",
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
		Ext.getCmp(this.TabID).add(this.LetterWin);
	}
	record = this.grid.getSelectionModel().getLastSelected();
	this.LetterWin.show();
	this.LetterWin.center();
	this.LetterWin.loader.load({
		params : {
			ExtTabID : this.LetterWin.getEl().id,
			LetterID : record.data.LetterID
		}
	});
}

PortalLetters.prototype.ShowHistory = function(){

	if(!this.HistoryWin)
	{
		this.HistoryWin = new Ext.window.Window({
			title: 'سابقه گردش نامه',
			modal : true,
			autoScroll : true,
			width: 615,
			height : 530,
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
	<div id="DivPanel"></div>