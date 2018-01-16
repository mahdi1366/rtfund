<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.08
//-------------------------
require_once('../header.inc.php');
require_once 'request.class.php';
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$framework = isset($_SESSION["USER"]["framework"]);
$RequestID = 0;
$editable = false;
if($framework)
{
	if(empty($_POST["RequestID"]))
		die();
	
	$RequestID = $_POST["RequestID"];
	$ReqObj = new LON_requests($RequestID);
	
	if($ReqObj->IsEnded == "NO")
		$editable = true;
}	

$dg = new sadaf_datagrid("dg",$js_prefix_address . "request.data.php?task=GetInstallments","grid_div");

$dg->addColumn("", "InstallmentID","", true);
$dg->addColumn("", "RequestID","", true);
$dg->addColumn("", "RequestID","", true);
$dg->addColumn("", "BankDesc", "", true);
$dg->addColumn("", "ChequeBranch", "", true);
$dg->addColumn("", "history", "", true);

$col = $dg->addColumn("سررسید", "InstallmentDate", GridColumn::ColumnType_date);
//$col->editor = ColumnEditor::SHDateField();
$col->width = 80;

$col = $dg->addColumn("مبلغ قسط", "InstallmentAmount", GridColumn::ColumnType_money);
//$col->editor = ColumnEditor::CurrencyField();

$col = $dg->addColumn("مبلغ تاخیر", "ForfeitAmount", GridColumn::ColumnType_money);
$col->width = 80;

$col = $dg->addColumn("مانده قسط", "remainder", GridColumn::ColumnType_money);
$col->width = 120;

$col = $dg->addColumn("وضعیت تمدید", "IsDelayed");
$col->renderer = "function(v,p,r){ return v == 'YES' ? 'تمدید شده' : '';}";
$col->width = 120;

$col = $dg->addColumn("اسناد", "docs");
$col->width = 120;

$col = $dg->addColumn("ثبت سابقه", "");
$col->renderer = "Installment.HistoryRender";
$col->width = 80;

if($editable && $accessObj->EditFlag)
{
	$dg->addButton("cmp_computeInstallment", "محاسبه اقساط", "list", 
			"function(){InstallmentObject.ComputeInstallments();}");
	//$dg->enableRowEdit = true;
	//$dg->rowEditOkHandler = "function(store,record){return InstallmentObject.SaveInstallment(store,record);}";
	
	$dg->addButton("", "ایجاد اقساط", "add", "function(){InstallmentObject.AddInstallments();}");
	
	$dg->addButton("", "تغییر اقساط", "delay", "function(){InstallmentObject.DelayInstallments();}");
}

$dg->addButton("cmp_report2", "گزارش پرداخت", "report", "function(){InstallmentObject.PayReport();}");

$dg->height = 377;
$dg->width = 755;
$dg->emptyTextOfHiddenColumns = true;
$dg->EnableSearch = false;
$dg->HeaderMenu = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "InstallmentID";
$dg->DefaultSortDir = "ASC";
$dg->title = "جدول اقساط";
$dg->autoExpandColumn = "InstallmentAmount";

$grid = $dg->makeGrid_returnObjects();

?>
<script type="text/javascript">

Installment.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	
	framework : <?= $framework ? "true" : "false" ?>,
	RequestID : <?= $RequestID ?>,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Installment()
{
	this.grid = <?= $grid ?>;
	if(this.framework)
	{
		if(this.grid.plugins[0])
			this.grid.plugins[0].on("beforeedit", function(editor,e){
				
				if(e.record.data.IsDelayed == "YES")
					return false;
				if(e.rowIdx == e.grid.getStore().getCount()-1)
					return false;
			});
		
		this.grid.getView().getRowClass = function(record, index)
		{
			if(record.data.history == "YES")
				return "greenRow";
			if(record.data.IsDelayed == "YES")
				return "yellowRow";

			return "";
		}
		
		this.grid.getStore().proxy.extraParams = {RequestID : this.RequestID};
		this.grid.render(this.get("div_grid"));
		return;
	}
		
	this.PartPanel = new Ext.form.FieldSet({
		title: "انتخاب وام",
		width: 700,
		renderTo : this.get("div_loans"),
		collapsible : true,
		collapsed : false,
		frame: true,
		items : [{
			xtype : "combo",
			store: new Ext.data.Store({
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + 'request.data.php?task=SelectMyRequests&mode=customer',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['PartAmount',"RequestID","ReqAmount","ReqDate", "RequestID", "CurrentRemain",{
					name : "fullTitle",
					convert : function(value,record){
						return "کد وام : " + record.data.RequestID + " به مبلغ " + 
							Ext.util.Format.Money(record.data.ReqAmount) + " مورخ " + 
							MiladiToShamsi(record.data.ReqDate);
					}
				}]
			}),
			displayField: 'fullTitle',
			valueField : "RequestID",
			width : 600,
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct" style="height: 23px;">',
				'<td style="padding:7px">کد وام</td>',
				'<td style="padding:7px">مبلغ وام</td>',
				'<td style="padding:7px">تاریخ پرداخت</td> </tr>',
				'<tpl for=".">',
					'<tr class="x-boundlist-item" style="border-left:0;border-right:0">',
					'<td style="border-left:0;border-right:0" class="search-item">{RequestID}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">',
						'{[Ext.util.Format.Money(values.ReqAmount)]}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{[MiladiToShamsi(values.ReqDate)]}</td> </tr>',
				'</tpl>',
				'</table>'
			),
			itemId : "RequestID",
			listeners :{
				select : function(combo,records){
					InstallmentObject.grid.getStore().proxy.extraParams = {
						RequestID : this.getValue()
					};
					
					InstallmentObject.RequestID = this.getValue();
					
					InstallmentObject.PayPanel.show();
					InstallmentObject.PayPanel.down("[itemId=PayCode]").setValue(
						LoanRFID(records[0].data.RequestID));
				
					InstallmentObject.PayPanel.down("[itemId=PayAmount]").setValue(records[0].data.CurrentRemain);	
				}
			}
		}]
	});
	
	this.grid.getStore().on("load", function(store){
		var r = store.getProxy().getReader().jsonData;
		InstallmentObject.PayPanel.down("[itemId=PayAmount]").setValue(r.message);
	});
	
	this.PayPanel = new Ext.form.FieldSet({
		title: "پرداخت وام",
		hidden : true,
		layout : "column",
		columns : 3,
		width: 650,
		renderTo : this.get("div_paying"),
		frame: true,
		items : [{
			xtype : "displayfield",
			fieldCls : "blueText",
			itemId : "PayCode",
			fieldLabel : "شناسه پرداخت"
		},{
			xtype : "currencyfield",
			hideTrigger : true,
			width: 300,
			fieldLabel : "مبلغ قابل پرداخت",
			itemId : "PayAmount"
		},{
			xtype : "button",
			border : true,
			disabled : true,
			style : "margin-right:10px",
			text : "پرداخت الکترونیک بانک اقتصاد نوین",
			iconCls : "epay",
			handler : function(){ InstallmentObject.PayInstallment(); }
		},{
			xtype : "button",
			border : true,
			itemId : "cmp_ayande",
			style : "margin-right:10px",
			text : "پرداخت الکترونیک بانک آینده",
			iconCls : "epay",
			handler : function(){ InstallmentObject.PayInstallment_ayande(); }
		},{
			xtype : "container",
			columns : 3,
			html : "در حال حاضر به دلیل خطای فنی در شبکه پرداخت الکترونیکی شاپرک امکان پرداخت از طریق بانک اقتصاد نوین میسر نمی باشد.",
			style : "color:red"
		},{
			xtype : "container",
			columns : 3,
			html : "* برای مشاهده ریز گزارش پرداخت وام خود می توانید از منوی گزارش پرداخت وام استفاده کنید ",
			cls : "blueText"
		}]
	});
	
	/*if(<?= $_SESSION["USER"]["UserName"] == "tureini" ? "true" : "false" ?>)
		this.PayPanel.down("[itemId=cmp_ayande]").show();*/
}

Installment.HistoryRender = function(v,p,r){
	if(r.data.history == "YES")
		return "";
	return  "<div  title='سابقه' class='history' onclick='InstallmentObject.SetHistory();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

var InstallmentObject = new Installment();

Installment.prototype.PayCodeRender = function(){

	RequestID = this.PartPanel.down("[itemId=RequestID]").getValue();

	st = RequestID.lpad("0", 11);
	num = (st[0]*11) + (st[1]*10) + (st[2]*9) + (st[3]*1) + (st[4]*2) + (st[5]*3)
		+ (st[6]*4) + (st[7]*5) + (st[8]*6) + (st[9]*7) + (st[10]*8);
	remain = num % 99;
	
	return st + remain.toString().lpad("0", 2);
}

Installment.prototype.PayInstallment = function(){
	
	RequestID = this.PartPanel.down("[itemId=RequestID]").getValue();
	PayAmount = this.PayPanel.down("[itemId=PayAmount]").getValue();
	
	if(PayAmount == "")
		return;

	window.open(this.address_prefix + "../../portal/epayment/epayment_step1.php?RequestID=" + 
		RequestID + "&amount=" + PayAmount);	
}

Installment.prototype.PayInstallment_ayande = function(){
	
	RequestID = this.PartPanel.down("[itemId=RequestID]").getValue();
	PayAmount = this.PayPanel.down("[itemId=PayAmount]").getValue();
	
	if(PayAmount == "")
		return;

	window.open(this.address_prefix + "../../portal/epayment-ayande/epayment_step1.php?RequestID=" + 
		RequestID + "&amount=" + PayAmount);	
}

Installment.prototype.SetHistory = function(){
	
	Ext.MessageBox.confirm("","آیا مایلید که این ردیف را ثبت سابقه کنید؟",function(btn){
		if(btn == "no")
			return;
		
		me = InstallmentObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		mask = new Ext.LoadMask(me.grid, {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'request.data.php',
			method: "POST",
			params: {
				task: "SetHistory",
				InstallmentID : record.data.InstallmentID
			},
			success: function(response){
				mask.hide();
				InstallmentObject.grid.getStore().load();
			}
		});
	});	
}

Installment.prototype.ComputeInstallments = function(){
	
	Ext.MessageBox.confirm("","در صورت محاسبه مجدد کلیه ردیف ها حذف و مجدد محاسبه و ایجاد می شوند <br>" + 
		"آیا مایل به محاسبه مجدد می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = InstallmentObject;
	
		mask = new Ext.LoadMask(me.grid, {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'request.data.php',
			method: "POST",
			params: {
				task: "ComputeInstallments",
				RequestID : me.RequestID
			},
			success: function(response){
				mask.hide();
				
				result = Ext.decode(response.responseText);
				if(!result.success)
				{
					if(result.data == "DocExists")
						Ext.MessageBox.alert("Error", "این وام دارای سند اختلاف قسط می باشد و قادر به محاسبه مجدد نمی باشید");
					else
						Ext.MessageBox.alert("", "عملیات مورد نظر با شکست مواجه شد");
				}
				
				InstallmentObject.grid.getStore().load();
			}
		});
	});	
}

Installment.prototype.SaveInstallment = function(store, record){

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'request.data.php',
		method: "POST",
		params: {
			task: "SaveInstallment",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				InstallmentObject.grid.getStore().load();
			}
			else
			{
				alert("خطا در اجرای عملیات");
			}
		},
		failure: function(){}
	});
}

Installment.prototype.PayReport = function(){

	window.open(this.address_prefix + "../report/LoanPayment2.php?show=true&RequestID=" + this.RequestID);
}

Installment.prototype.DelayInstallments = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	if(!record)
	{
		Ext.MessageBox.alert("","قسطی که بعد از آن مایل به تمدید می باشید را انتخاب کنید");
		return;
	}
	
	if(!this.delayWin)
	{
		this.delayWin = new Ext.window.Window({
			width : 350,
			height : 160,
			modal : true,
			title : "تمدید اقساط",
			bodyStyle : "background-color:white;padding-right:20px",
			items : [{
				xtype : "currencyfield",
				fieldLabel : "مبلغ جدید",
				name : "newAmount",
				hideTrigger : true
			},{
				xtype : "shdatefield",
				fieldLabel : "تمدید تا تاریخ",
				name : "newDate"
			},{
				xtype : "checkbox",
				boxLabel : "محاسبه کارمزد بر اساس باقیمانده قسط باشد",
				inputValue : 1,
				name : "IsRemainCompute"
			},{
				xtype : "checkbox",
				boxLabel : "تمدید برای کلیه اقساط بعدی نیز انجام شود",
				inputValue : 1,
				name : "ContinueToEnd"
			}],
			closeAction : "hide",
			buttons : [{
				text : "اعمال",				
				iconCls : "save",
				itemId : "btn_save",
				handler : function(){
					
					me = InstallmentObject;
					var record = me.grid.getSelectionModel().getLastSelected();
					if(!record)
					{
						Ext.MessageBox.alert("","قسطی که بعد از آن مایل به تمدید می باشید را انتخاب کنید");
						return;
					}

					mask = new Ext.LoadMask(me.delayWin, {msg:'در حال ذخیره سازی ...'});
					mask.show();

					Ext.Ajax.request({
						url: me.address_prefix +'request.data.php',
						method: "POST",
						params: {
							task: "DelayInstallments",
							RequestID : record.data.RequestID,
							InstallmentID : record.data.InstallmentID,
							newDate : me.delayWin.down("[name=newDate]").getRawValue(),
							newAmount : me.delayWin.down("[name=newAmount]").getValue(),
							IsRemainCompute : me.delayWin.down("[name=IsRemainCompute]").checked ? 1 : 0,
							ContinueToEnd : me.delayWin.down("[name=ContinueToEnd]").checked ? 1 : 0
						},
						success: function(response){
							mask.hide();
							result = Ext.decode(response.responseText);
							if(!result.success)
								Ext.MessageBox.alert("", result.data);	
							
							InstallmentObject.delayWin.hide();
							InstallmentObject.grid.getStore().load();
						}
					});
				}				
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.delayWin);
	}
	
	this.delayWin.show();
	this.delayWin.center();
}

Installment.prototype.AddInstallments = function(){
	
	if(!this.AddWin)
	{
		this.card0 = {
			itemId: 'card-0',
			anchor: "100%",
			layout : "vbox",
			items: [{
				xtype : "radiogroup",
				columns : 1,
				items : [{
					xtype : "radio",
					boxLabel : "ورود اقساط بر اساس مبلغ کل وام و اعمال از ابتدای شروع اقساط",
					inputValue : "START",
					checked : true,
					name : "ComputeMode"
				},{
					xtype : "radio",
					boxLabel : "ورود اقساط بر اساس مانده اصل وام و اعمال از تاریخ خاص"	,
					inputValue : "REMAIN",
					name : "ComputeMode",
					listeners : {
						change : function(el){
							InstallmentObject.formPanel.down("[name=ComputeDate]").setDisabled(!el.getValue());
						}
					}
				}]
			},{
				xtype : "shdatefield",
				fieldLabel : "تاریخ شروع تغییرات",
				name : "ComputeDate",
				disabled : true
			}]
		};
		this.card1 = {
			itemId : "card-1",
			anchor: "100%",
			layout : "vbox",
			items : [{
				xtype : "displayfield",
				name : "DefrayAmount",
				fieldLabel : "مانده اصل وام در تاریخ فوق"
			}],
			listeners :{
				activate : function(){
					me = InstallmentObject;
					var mask = new Ext.LoadMask(me.formPanel,{msg:'در حال بارگذاری ...'});
					//mask.show();
					Ext.Ajax.request({
						url : me.address_prefix + "request.data.php?task=GetDefrayAmount",
						method : "POST",
						params : {
							RequestID : this.RequestID,
							ComputeDate : InstallmentObject.formPanel.down("[name=ComputeDate]").getRawValue()
						},
						success : function(response){
							st = Ext.decode(response.responseText);
							InstallmentObject.card1.down("[name=DefrayAmount]").setValue(st.data);
							mask.hide();
						}
					});
				}
			}
		};
		
		this.AddWin = new Ext.window.Window({
			width : 510,
			height : 360,
			modal : true,
			title : "ثبت دستی اقساط",
			items : [
				this.formPanel = new Ext.form.Panel({
					defaults: {border: false},
					height : 300,
					width : 500,
					frame: true,
					bodyPadding: '5 5 0',
					layout: "card",
					activeItem: 0,
					items : [this.card0,this.card1],
					bbar: [{
						itemId: 'card-prev',
						text: '&laquo; قبلی',
						handler: function() {
							var itemId = InstallmentObject.formPanel.items.items[0].itemId;
							var i = itemId.split('card-')[1];
							var next = parseInt(i, 10) - 1;
							InstallmentObject.formPanel.getLayout().setActiveItem(next);
							InstallmentObject.formPanel.down('[itemId=card-prev]').setDisabled(next === 0);
							InstallmentObject.formPanel.down('[itemId=card-next]').setDisabled(next === 6);
						},
						disabled: true
					},'->', {
						itemId: 'card-next',
						text: 'بعدی &raquo;',
						handler: function() {
							var itemId = InstallmentObject.formPanel.items.items[0].itemId;
							var i = itemId.split('card-')[1];
							var next = parseInt(i, 10) + 1;
							InstallmentObject.formPanel.getLayout().setActiveItem(next);
							InstallmentObject.formPanel.down('[itemId=card-prev]').setDisabled(next === 0);
							InstallmentObject.formPanel.down('[itemId=card-next]').setDisabled(next === 6);
						}
					}]
				})
			],
			closeAction : "hide",
			buttons : [{
				text : "بازگشت",				
				iconCls : "undo",
				handler : function(){
					InstallmentObject.AddWin.hide();
				}				
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.AddWin);
	}
	
	this.AddWin.show();
	this.AddWin.center();
}

function LoanRFID(RequestID){
	st = RequestID.lpad("0", 7);
	SUM = st[0]*1 + st[1]*2 + st[2]*3 + st[3]*4 + st[4]*5 + st[5]*6 + st[6]*7;
	remain = SUM % 11;
	remain = remain == 10 ? 0 : remain;
	
	code = st + remain;
	return code;
}
</script>
<center>
	<div id="div_loans"></div>
	<div id="div_paying"></div>	
	<div id="div_grid"></div>	
</center>