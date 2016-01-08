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
$col->renderer = "function(v){".
	"if(v == 'INNER') return '<img src=/office/icons/inner.gif>';".
	"if(v == 'INCOME') return '<img src=/office/icons/income.gif>';".
	"if(v == 'OUTCOME') return '<img src=/office/icons/outcome.gif>';}";
$col->width = 30;

$col = $dg->addColumn("<img src=/office/icons/attach.gif>", "HasAttach", "");
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
	this.grid.render(this.get("DivGrid"))
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

SendedLetter.prototype.EditRequest = function(HavePart){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	if(this.User == "Agent")
	{
		portal.OpenPage("../loan/request/RequestInfo.php", {RequestID : record.data.RequestID});
		return;
	}
	if(this.User == "Customer")
	{
		if(!this.RequestInfoWin)
		{
			this.RequestInfoWin = new Ext.window.Window({
				width : 800,
				height : 460,
				autoScroll : true,
				modal : true,
				bodyStyle : "background-color:white;padding-right:10px",
				closeAction : "hide",
				loader : {
					url : "../loan/request/RequestInfo.php",
					scripts : true
				},
				buttons :[{
					text : "بازگشت",
					iconCls : "undo",
					handler : function(){this.up('window').hide();}
				}]
			});
			Ext.getCmp(this.TabID).add(this.RequestInfoWin);
		}
		
		this.RequestInfoWin.show();
		this.RequestInfoWin.center();
		
		this.RequestInfoWin.loader.load({
			url : HavePart ? "../loan/request/RequestInfo.php" : "../loan/request/CustomerNewRequest.php",
			scripts : true,
			params : {
				ExtTabID : this.RequestInfoWin.getEl().id,
				RequestID : record.data.RequestID
			}
		});
	}
}

SendedLetter.prototype.DeleteRequest = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = SendedLetterObject;
		record = me.grid.getSelectionModel().getLastSelected();
		mask = new Ext.LoadMask(me.grid, {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'request.data.php',
			method: "POST",
			params: {
				task: "DeleteRequest",
				RequestID : record.data.RequestID
			},
			success: function(response){
				result = Ext.decode(response.responseText);
				mask.hide();
				if(result.success)
					SendedLetterObject.grid.getStore().load();
				else
					Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد;")
			}
		});
	});

}

SendedLetter.prototype.LoadAttaches = function(){

	if(!this.documentWin)
	{
		this.documentWin = new Ext.window.Window({
			width : 720,
			height : 440,
			modal : true,
			bodyStyle : "background-color:white;padding: 0 10px 0 10px",
			closeAction : "hide",
			loader : {
				url : "../../dms/documents.php",
				scripts : true
			},
			buttons :[{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.documentWin);
	}

	this.documentWin.show();
	this.documentWin.center();
	var record = this.grid.getSelectionModel().getLastSelected();
	this.documentWin.loader.load({
		scripts : true,
		params : {
			ExtTabID : this.documentWin.getEl().id,
			ObjectType : "loan",
			ObjectID : record.data.RequestID
		}
	});
}

SendedLetter.prototype.ChangeStatus = function(StatusID){
	
	Ext.MessageBox.confirm("","پس از تایید دیگر قادر به تغییر در اطلاعات نمی باشید<br>" +
		"آیا مایل به تایید می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = SendedLetterObject;
		record = me.grid.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(me.grid, {msg:'در حال ذخيره سازي...'});
		mask.show();  

		Ext.Ajax.request({
			methos : "post",
			url : me.address_prefix + "request.data.php",
			params : {
				task : "ChangeRequesrStatus",
				RequestID : record.data.RequestID,
				StatusID : StatusID,
				desc : ""
			},

			success : function(){
				mask.hide();
				SendedLetterObject.grid.getStore().load()
			}
		});
	});
}

SendedLetter.prototype.ShowComment = function(){
	
	if(!this.commentWin)
	{
		this.commentWin = new Ext.window.Window({
			width : 400,
			height : 200,
			bodyStyle : "background-color:white;padding:10px",
			html : "",
			closeAction : "hide",
			buttons : [{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.commentWin);
	}
	var record = this.grid.getSelectionModel().getLastSelected();
	Ext.Ajax.request({
		url : this.address_prefix + "request.data.php",
		method : "post",
		params : {
			task : "GetLastFundComment",
			RequestID : record.data.RequestID
		},
		success : function(res)
		{
			result = Ext.decode(res.responseText);
			SendedLetterObject.commentWin.update(result.data);
		}
	});
	
	this.commentWin.show();
	this.commentWin.center();
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
			RequestID : this.grid.getSelectionModel().getLastSelected().data.RequestID
		}
	});
}
</script>
<center>
	<br>
	<div id="DivGrid"></div>
</center>