<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.10
//-----------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;

$mode = $_REQUEST["mode"];

$task = "";
if($mode == "receive")
	$task = "SelectReceivedLetters";
if($mode == "send")
	$task = "SelectSendedLetters";

if($task == "")
	die();
$dg = new sadaf_datagrid("dg", $js_prefix_address . "letter.data.php?task=" . $task, "grid_div");

$dg->addColumn("", "LetterID", "", true);
$dg->addColumn("", "IsSeen", "", true);
$dg->addColumn("", "IsDeleted", "", true);
$dg->addColumn("", "SendID", "", true);

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

if($mode == "receive")
	$col = $dg->addColumn("فرستنده", "FromPersonName", "");
else
	$col = $dg->addColumn("گیرنده", "ToPersonName", "");
$col->width = 150;

$col = $dg->addColumn("شرح ارجاع", "SendComment", "");
$col->ellipsis = 25;
$col->width = 150;

$col = $dg->addColumn("تاریخ ارجاع", "SendDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("عملیات", "");
$col->renderer = "function(v,p,r){return MyLetter.OperationRender(v,p,r);}";
$col->width = 80;

if($mode == "receive")
	$dg->addObject("this.deletedBtnObj");

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 420;
$dg->width = 770;
$dg->title = "نامه های ارسالی";
$dg->DefaultSortField = "SendDate";
$dg->autoExpandColumn = "LetterTitle";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
	
MyLetter.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	mode : '<?= $mode ?>',
		
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
			me.grid.getStore().proxy.extraParams["deleted"] = this.pressed ? "true" : "false";
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

	this.grid.render(this.get("DivGrid"));
}

MyLetter.LetterTypeRender = function(v,p,r){
	
	if(v == 'INNER') 
		return "<img data-qtip='نامه داخلی' src=/office/icons/inner.gif>";
	if(v == 'INCOME') 
		return "<img data-qtip='نامه وارده' src=/office/icons/income.gif>";
	if(v == 'OUTCOME') 
		return "<img data-qtip='نامه صادره' src=/office/icons/outcome.gif>";
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
			AfterSendHandler : function(){
				MyLetterObject.grid.getStore().load();
			},
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

</script>
<center>
	<br>
	<div id="DivGrid"></div>
</center>