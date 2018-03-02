<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "form.data.php?task=SelectMyRequests", "grid_div");

$dg->addColumn("", "FlowID", "", true);
$dg->addColumn("", "IsStarted", "", true);
$dg->addColumn("", "IsEnded", "", true);
$dg->addColumn("", "JustStarted", "", true);

$col = $dg->addColumn("شماره درخواست", "RequestID", "");
$col->width = 100;
$col->align = "center";

$col = $dg->addColumn("نوع فرم", "FormTitle", "");

$col = $dg->addColumn("تاریخ ایجاد", "RegDate", GridColumn::ColumnType_datetime);
$col->width = 130;

$col = $dg->addColumn("وضعیت", "StepDesc", "");
$col->width = 100;

$dg->addButton("", "ایجاد فرم جدید", "add", "function(){WFM_MyRequestsObject.AddNewRequest(0);}");

$col = $dg->addColumn('عملیات', '', 'string');
$col->renderer = "WFM_MyRequests.OperationRender";
$col->width = 50;
$col->align = "center";

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 500;
$dg->width = 750;
$dg->title = "فرم های من";
$dg->DefaultSortField = "RegDate";
$dg->autoExpandColumn = "FormTitle";
$grid = $dg->makeGrid_returnObjects();
?>
<script>

WFM_MyRequests.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function WFM_MyRequests(){
	
	this.mainPanel = new Ext.form.FormPanel({
		frame: true,
		bodyStyle : "padding:10px",
		renderTo : this.get("mainForm"),
		title: 'فرم ها',
		width: 500,
		layout : {
			type : "table",
			columns : 2
		}
	});	
		
	this.NewVoteFormsStore = new Ext.data.Store({
		proxy:{
			type: 'jsonp',
			url: this.address_prefix + "form.data.php?task=SelectValidForms",
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["FormID","FormTitle"],
		autoLoad : true,
		listeners :{
			load : function(){
				
				WFM_MyRequestsObject.mainPanel.removeAll();
				for(i=0; i<this.totalCount; i++)
				{
					WFM_MyRequestsObject.mainPanel.add({
						xtype : "button",
						style : "margin:5px ",
						itemId : this.getAt(i).data.FormID,
						border : true,
						iconCls : "add",
						text : "ایجاد " + this.getAt(i).data.FormTitle,
						handler : function(){ 
							WFM_MyRequestsObject.AddNewRequest("",this.itemId);
						}
					});
				}
			}
		}
	});	
	
	this.grid = <?= $grid ?>;
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsEnded == "YES")
			return "greenRow";
		return "";
	}	

	this.grid.render(this.get("DivGrid"));
}

WFM_MyRequests.prototype.AddNewRequest = function(RequestID, FormID){
	
	if(!this.requestWin)
	{
		this.requestWin = new Ext.window.Window({
			width : 740,
			height : 660, 
			modal : true,
			bodyStyle : "background-color:white;padding: 0 10px 0 10px",
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "NewRequest.php",
				scripts : true
			},
			buttons :[{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.requestWin);
	}

	this.requestWin.show();
	this.requestWin.center();
	this.requestWin.loader.load({
		params : {
			ExtTabID : this.requestWin.getEl().id,
			RequestID : RequestID,
			FormID : FormID
		}
	});
	
}

WFM_MyRequests.prototype.WFM_MyRequests = function(){
	if(this.get("new_pass").value != this.get("new_pass2").value)
	{
		return;
	}
}

WFM_MyRequests.OperationRender = function(value, p, record){
	
	return "<div  title='عملیات' class='setting' onclick='WFM_MyRequestsObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WFM_MyRequests.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();
	
	if(record.data.IsStarted == "NO")
	{
		op_menu.add({text: 'ویرایش فرم',iconCls: 'edit', 
		handler : function(){
			record = WFM_MyRequestsObject.grid.getSelectionModel().getLastSelected();
			return WFM_MyRequestsObject.AddNewRequest(record.data.RequestID); }});
	
		op_menu.add({text: 'حذف فرم',iconCls: 'remove', 
		handler : function(){ WFM_MyRequestsObject.DeleteRequest();  }});
	
		op_menu.add({text: 'ارسال فرم',iconCls: 'send', 
		handler : function(){ return WFM_MyRequestsObject.StartFlow(); }});
	}
	if(record.data.JustStarted == "YES")
	{
		op_menu.add({text: 'برگشت فرم',iconCls: 'return',
		handler : function(){ return WFM_MyRequestsObject.ReturnStartFlow(); }});
	}
	op_menu.add({text: 'سابقه گردش فرم',iconCls: 'history', 
		handler : function(){ return WFM_MyRequestsObject.ShowHistory(); }});
		
	op_menu.add({text: 'چاپ فرم',iconCls: 'print', 
		handler : function(){ 
			me = WFM_MyRequestsObject;
			record = me.grid.getSelectionModel().getLastSelected();
			window.open(me.address_prefix + 'PrintForm.php?RequestID=' + record.data.RequestID);
	}});
	
	op_menu.showAt(e.pageX-120, e.pageY);
}

WFM_MyRequests.prototype.DeleteRequest = function(){
	
	record = this.grid.getSelectionModel().getLastSelected();
	if(record.data.IsStarted == "YES")
	{
		Ext.MessageBox.alert("Error","فرم دارای گردش بوده و قابل حذف نمی باشد");
		return;
	}
	
	Ext.MessageBox.confirm("","آیا مایل به حذف فرم می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = WFM_MyRequestsObject;
		mask = new Ext.LoadMask(me.grid, {msg:'در حال حذف...'});
		mask.show();  

		Ext.Ajax.request({
			methos : "post",
			url : me.address_prefix + "form.data.php",
			params : {
				task : "DeleteRequest",
				RequestID : record.data.RequestID
			},

			success : function(response){
				mask.hide();

				result = Ext.decode(response.responseText);
				if(!result.success)
				{
					if(result.data == "")
						Ext.MessageBox.alert("Error","عملیات مورد نظر با شکست مواجه شد");
					else
						Ext.MessageBox.alert("Error",result.data);
				}

				WFM_MyRequestsObject.grid.getStore().load();
			}
		});
	});
}

WFM_MyRequests.prototype.StartFlow = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به شروع گردش فرم می باشید؟",function(btn){
		
		if(btn == "no")
			return;
		
		me = WFM_MyRequestsObject;
		var record = me.grid.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: '/office/workflow/wfm.data.php',
			method: "POST",
			params: {
				task: "StartFlow",
				FlowID : record.data.FlowID,
				ObjectID : record.data.RequestID
			},
			success: function(response){
				mask.hide();
				WFM_MyRequestsObject.grid.getStore().load();
			}
		});
	});
}

WFM_MyRequests.prototype.ReturnStartFlow = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به برگشت فرم می باشید؟",function(btn){
		
		if(btn == "no")
			return;
		
		me = WFM_MyRequestsObject;
		var record = me.grid.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: '/office/workflow/wfm.data.php',
			method: "POST",
			params: {
				task: "ReturnStartFlow",
				FlowID : record.data.FlowID,
				ObjectID : record.data.RequestID
			},
			success: function(response){
				mask.hide();
				WFM_MyRequestsObject.grid.getStore().load();
			}
		});
	});
}

WFM_MyRequests.prototype.ShowHistory = function(){

	if(!this.HistoryWin)
	{
		this.HistoryWin = new Ext.window.Window({
			title: 'سابقه گردش',
			modal : true,
			autoScroll : true,
			width: 700,
			height : 500,
			closeAction : "hide",
			loader : {
				url : "/office/workflow/history.php",
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
	record = this.grid.getSelectionModel().getLastSelected();
	this.HistoryWin.show();
	this.HistoryWin.center();
	this.HistoryWin.loader.load({
		params : {
			FlowID : record.data.FlowID,
			ObjectID : record.data.RequestID
		}
	});
}

WFM_MyRequestsObject = new WFM_MyRequests();

</script>
<center>
	<div id="mainForm"></div><BR>
	<div id="DivGrid"></div>
	توجه : ردیف های سبز رنگ ردیف هایی هستند که فرایند گردش آنها خاتمه یافته است
</center>