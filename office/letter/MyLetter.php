<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.10
//-----------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;
require_once inc_component;

$mode = $_REQUEST["mode"];

$task = "";
if($mode == "receive")
{
	require_once 'letter.data.php';
	$summary = ReceivedSummary();
	$task = "SelectReceivedLetters";
}
if($mode == "send")
{
	$summary = array();
	$task = "SelectSendedLetters";
}

if($task == "")
	die();
$dg = new sadaf_datagrid("dg", $js_prefix_address . "letter.data.php?task=" . $task, "grid_div");

$dg->addColumn("", "LetterID", "", true);
$dg->addColumn("", "IsSeen", "", true);
$dg->addColumn("", "IsDeleted", "", true);
$dg->addColumn("", "SendID", "", true);
$dg->addColumn("", "_SendDate", "", true);
$dg->addColumn("", "ResponseTimeout", "", true);

$dg->addColumn("", "SendComment", "", true);

$col = $dg->addColumn("<img src=/office/icons/LetterType.gif>", "LetterType", "");
$col->renderer = "MyLetter.LetterTypeRender";
$col->width = 30;

$col = $dg->addColumn("<img src=/office/icons/attach.gif>", "hasAttach", "");
$col->renderer = "function(v,p,r){if(v == 'YES') return '<img src=/office/icons/attach.gif>';}";
$col->width = 30;

$col = $dg->addColumn("شماره", "LetterID", "");
$col->width = 60;
$col->align = "center";

$col = $dg->addColumn("موضوع نامه", "LetterTitle", "");
$col->renderer = "MyLetter.TitleRender";
if($mode == "receive")
	$col = $dg->addColumn("فرستنده", "FromPersonName", "");
else
	$col = $dg->addColumn("گیرنده", "ToPersonName", "");
$col->width = 150;

$col = $dg->addColumn("عملیات", "");
$col->renderer = "function(v,p,r){return MyLetter.OperationRender(v,p,r);}";
$col->width = 80;

if($mode == "receive")
	$dg->addObject("this.deletedBtnObj");

$dg->EnableGrouping = true;
$dg->DefaultGroupField = "_SendDate";
if($mode == "send")
	$dg->groupHeaderTpl = "تاریخ ارسال : {[MiladiToShamsi(values.rows[0].data._SendDate)]}";
else
	$dg->groupHeaderTpl = "تاریخ دریافت : {[MiladiToShamsi(values.rows[0].data._SendDate)]}";

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 490;
$dg->width = 640;
$dg->title = $mode == "send" ? "نامه های ارسالی" : "نامه های دریافتی";
$dg->DefaultSortField = "_SendDate";
$dg->autoExpandColumn = "LetterTitle";
$grid = $dg->makeGrid_returnObjects();	
?>
<script>
	
MyLetter.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	mode : '<?= $mode ?>',
	summary : <?= common_component::PHPArray_to_JSArray($summary) ?>,
		
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function MyLetter(){
	
	this.deletedBtnObj = Ext.button.Button({
		xtype: "button",
		text : "مشاهده نامه های حذف شده", 
		iconCls : "list",
		enableToggle : true,
		handler : function(){
			me = MyLetterObject;
			me.grid.getStore().proxy.extraParams.deleted = this.pressed ? "true" : "false";
			me.grid.getStore().load();
		}
	});
	this.grid = <?= $grid ?>;
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsSeen == "NO")
			return "yellowRow";
		if(record.data.IsDeleted == "YES")
			return "pinkRow";
		return "";
	}	
	this.grid.on("itemdblclick", function(view, record){
			
		framework.OpenPage("/office/letter/LetterInfo.php", "مشخصات نامه", 
		{
			LetterID : record.data.LetterID,
			SendID : record.data.SendID
		});
	});
	if(this.mode == "send")
	{
		this.grid.render(this.get("DivPanel"));
		return;
	}
	ButtonItems = new Array();
	for(i=0; i<this.summary.length; i++)
		ButtonItems.push({
			xtype : "button",
			width : 130,
			height : 50,
			autoScroll : true,
			enableToggle : true,
			scale : "large",
			style : "margin-bottom:10px",	
			itemId : this.summary[i].SendType,
			text : this.summary[i].SendTypeDesc + "<br><br><div style=float:right>" + " تعداد : " + 
				this.summary[i].totalCnt + "</div><div style=float:left>" + "جدید : " + (this.summary[i].newCnt*1>0 ? "<b>" : "") + 
				"( " + this.summary[i].newCnt + " )" + (this.summary[i].newCnt*1>0 ? "<b>" : "") + "</div>",
			handler : function(){
				
				for(i=0; i<MyLetterObject.summary.length; i++)
					if(MyLetterObject.summary[i].SendType != this.itemId)
						MyLetterObject.panel.down("[itemId=" + MyLetterObject.summary[i].SendType + "]").toggle(false);
				
				MyLetterObject.grid.getStore().proxy.extraParams.SendType = this.itemId;
				if(MyLetterObject.grid.rendered)
					MyLetterObject.grid.getStore().loadPage(1);
				else
					MyLetterObject.panel.add(MyLetterObject.grid);
				
			}
		});
	
	this.panel = new Ext.panel.Panel({
		renderTo : this.get("DivPanel"),
		//border : false,
		layout : "column",
		height : 500,
		columns : 2,
		style : " ",
		width : 800,
		items : [{
			xtype : "container",
			width : 150,
			autoScroll : true,
			height: 500,
			style : "border-left : 1px solid #99bce8;margin-left:5px",
			layout : "vbox",
			itemId : "cmp_buttons",
			items : ButtonItems
		}]
	});
	
	
}

MyLetter.LetterTypeRender = function(v,p,r){
	
	if(v == 'INNER') 
		return "<img data-qtip='نامه داخلی' src=/office/icons/inner.gif>";
	if(v == 'INCOME') 
		return "<img data-qtip='نامه وارده' src=/office/icons/income.gif>";
	if(v == 'OUTCOME') 
		return "<img data-qtip='نامه صادره' src=/office/icons/outcome.gif>";
}

MyLetter.TitleRender = function(v,p,r){
	
	if(r.data.ResponseTimeout != "0000-00-00" && r.data.ResponseTimeout != null)
		v += "<br>مهلت پاسخ : " + MiladiToShamsi(r.data.ResponseTimeout);
	return v;
}

MyLetterObject = new MyLetter();

MyLetter.OperationRender = function(v,p,r){
	
	return "<div  title='ارجاع نامه' class='sendLetter' "+
		" onclick='MyLetterObject.SendLetter();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;float:right;width:20px;height:16'></div>" + 
		
		(MyLetterObject.mode == "send" && r.data.IsSeen == "NO" ? 
			"<div  title='برگشت' class='undo' " +
			" onclick='MyLetterObject.ReturnLetter();' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;float:right;width:20px;height:16'></div>" : ""
		) +
		
		"<div  title='سابقه' class='history' "+
		" onclick='MyLetterObject.ShowHistory();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;float:right;width:20px;height:16'></div>" +
		
		(MyLetterObject.mode == "receive" && r.data.IsDeleted == "NO" ? 
			"<div  title='حذف از کارتابل' class='remove' " +
			" onclick='MyLetterObject.DeleteSend(0);' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;float:right;width:20px;height:16'></div>" : ""
		) +
		(MyLetterObject.mode == "receive" && r.data.IsDeleted == "YES" ? 
			"<div  title='اضافه به کارتابل' class='add' " +
			" onclick='MyLetterObject.DeleteSend(1);' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;float:right;width:20px;height:16'></div>" : ""
		) ;
}

MyLetter.prototype.SendLetter = function(){
	
	if(!this.SendingWin)
	{
		this.SendingWin = new Ext.window.Window({
			title : "ارجاع نامه",
			width : 620,			
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
			parent : "MyLetterObject.SendingWin",
			AfterSendHandler : "MyLetterObject.AfterSend",
			LetterID : this.grid.getSelectionModel().getLastSelected().data.LetterID,
			SendID : this.grid.getSelectionModel().getLastSelected().data.SendID
		}
	});
}

MyLetter.prototype.ReturnLetter = function(){
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();
	
	Ext.Ajax.request({
		url : this.address_prefix + "letter.data.php?task=ReturnSend",
		method : "post",
		params : {
			SendID : this.grid.getSelectionModel().getLastSelected().data.SendID,
			LetterID : this.grid.getSelectionModel().getLastSelected().data.LetterID
		},
		
		success : function(response){
			mask.hide();
			result = Ext.decode(response.responseText);
			if(result.success)
				MyLetterObject.grid.getStore().load();
			else
			{	
				if(result.data == "IsSeen")
					Ext.MessageBox.alert("Error", "نامه توسط گیرنده دیده شده و قابل برگشت نمی باشد");
				else
					Ext.MessageBox.alert("Error", "عملیات مورد نظر با شکست مواجه شد");
			}
		}
	})
}

MyLetter.prototype.DeleteSend = function(mode){
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();
	
	Ext.Ajax.request({
		url : this.address_prefix + "letter.data.php?task=DeleteSend",
		method : "post",
		params : {
			mode : mode,
			SendID : this.grid.getSelectionModel().getLastSelected().data.SendID,
			LetterID : this.grid.getSelectionModel().getLastSelected().data.LetterID
		},
		
		success : function(response){
			mask.hide();
			result = Ext.decode(response.responseText);
			if(result.success)
				MyLetterObject.grid.getStore().load();
			else
			{	
				if(result.data == "IsSeen")
					Ext.MessageBox.alert("Error", "نامه توسط گیرنده دیده شده و قابل برگشت نمی باشد");
				else
					Ext.MessageBox.alert("Error", "عملیات مورد نظر با شکست مواجه شد");
			}
		}
	})
}

MyLetter.prototype.ShowHistory = function(){

	if(!this.HistoryWin)
	{
		this.HistoryWin = new Ext.window.Window({
			title: 'سابقه گردش نامه',
			modal : true,
			autoScroll : true,
			width: 710,
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

MyLetter.prototype.AfterSend = function(){

	this.grid.getStore().load();
}

</script>
	<br>
	<div id="DivPanel" style="margin-right:8px;"></div>