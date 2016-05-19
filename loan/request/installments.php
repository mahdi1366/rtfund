<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.08
//-------------------------
include('../header.inc.php');
require_once 'request.class.php';
include_once inc_dataGrid;

$framework = isset($_SESSION["USER"]["framework"]);
$PartID = 0;
$editable = false;
if($framework)
{
	if(empty($_POST["PartID"]))
		die();
	$PartID = $_POST["PartID"];
	
	$obj = new LON_ReqParts($PartID);
	$ReqObj = new LON_requests($obj->RequestID);
	
	if($ReqObj->IsEnded == "NO")
		$editable = true;
}	

$dg = new sadaf_datagrid("dg",$js_prefix_address . "request.data.php?task=GetPartInstallments","grid_div");

$dg->addColumn("", "InstallmentID","", true);
$dg->addColumn("", "PartID","", true);
$dg->addColumn("", "RequestID","", true);
$dg->addColumn("", "BankDesc", "", true);
$dg->addColumn("", "ChequeBranch", "", true);

$col = $dg->addColumn("سررسید", "InstallmentDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 80;

$col = $dg->addColumn("مبلغ قسط", "InstallmentAmount", GridColumn::ColumnType_money);
$col->editor = ColumnEditor::CurrencyField();

$col = $dg->addColumn("مبلغ جریمه", "ForfeitAmount", GridColumn::ColumnType_money);
$col->width = 80;

$col = $dg->addColumn("مانده", "TotalRemainder", GridColumn::ColumnType_money);
$col->width = 120;

$col = $dg->addColumn("وضعیت تمدید", "IsDelayed");
$col->renderer = "function(v,p,r){ return v == 'YES' ? 'تمدید شده' : '';}";
$col->width = 120;

if($editable)
{
	$dg->addButton("cmp_computeInstallment", "محاسبه اقساط", "list", 
			"function(){InstallmentObject.ComputeInstallments();}");
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(store,record){return InstallmentObject.SaveInstallment(store,record);}";
	
	$dg->addButton("", "تمدید اقساط", "delay", "function(){InstallmentObject.DelayInstallments();}");
}

if($framework)
{
	$dg->addButton("cmp_report", "گزارش پرداخت", "report", 
			"function(){InstallmentObject.PayReport();}");
}

$dg->height = 377;
$dg->width = 755;
$dg->emptyTextOfHiddenColumns = true;
$dg->EnableSearch = false;
$dg->HeaderMenu = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "InstallmentDate";
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
	PartID : <?= $PartID ?>,
	
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
			if(record.data.IsDelayed == "YES")
				return "yellowRow";

			return "";
		}
		
		this.grid.getStore().proxy.extraParams = {PartID : this.PartID};
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
					url: this.address_prefix + 'request.data.php?task=selectParts',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['PartAmount','PartDesc',"RequestID","PartDate", "PartID",{
					name : "fullTitle",
					convert : function(value,record){
						return "کد وام : " + record.data.RequestID + "  " + record.data.PartDesc + " به مبلغ " + 
							Ext.util.Format.Money(record.data.PartAmount) + " مورخ " + 
							MiladiToShamsi(record.data.PartDate);
					}
				}]
			}),
			displayField: 'fullTitle',
			valueField : "PartID",
			width : 600,
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct" style="height: 23px;">',
				'<td style="padding:7px">کد وام</td>',
				'<td style="padding:7px">فاز وام</td>',
				'<td style="padding:7px">مبلغ وام</td>',
				'<td style="padding:7px">تاریخ پرداخت</td> </tr>',
				'<tpl for=".">',
					'<tr class="x-boundlist-item" style="border-left:0;border-right:0">',
					'<td style="border-left:0;border-right:0" class="search-item">{RequestID}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{PartDesc}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">',
						'{[Ext.util.Format.Money(values.PartAmount)]}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{[MiladiToShamsi(values.PartDate)]}</td> </tr>',
				'</tpl>',
				'</table>'
			),
			itemId : "PartID",
			listeners :{
				select : function(combo,records){
					InstallmentObject.grid.getStore().proxy.extraParams = {
						PartID : this.getValue()
					};
					if(InstallmentObject.grid.rendered)
						InstallmentObject.grid.getStore().load();
					else
						InstallmentObject.grid.render(InstallmentObject.get("div_grid"));

					InstallmentObject.PartPanel.collapse();
					
					InstallmentObject.PayPanel.show();
					InstallmentObject.PayPanel.down("[itemId=PayCode]").setValue(
						LoanRFID(records[0].data.RequestID));
					
					
				}
			}
		}]
	});
	
	this.PayPanel = new Ext.form.FieldSet({
		title: "انتخاب وام",
		hidden : true,
		layout : "column",
		columns : 2,
		width: 500,
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
			text : "پرداخت الکترونیک",
			iconCls : "epay",
			handler : function(){ InstallmentObject.PayInstallment(); }
		}]
	});
	
	this.grid.getStore().on("load", function(store){
		var r = store.getProxy().getReader().jsonData;
		InstallmentObject.PayPanel.down("[itemId=PayAmount]").setValue(r.message);
	});
}

Installment.payRender = function(v,p,r){

	if(r.data.IsPaid == "YES")
		return "";
	return  "<div  title='پرداخت قسط' class='epay' onclick='InstallmentObject.PayInstallment();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

var InstallmentObject = new Installment();

Installment.prototype.PayCodeRender = function(){

	PartID = this.PartPanel.down("[itemId=PartID]").getValue();

	st = PartID.lpad("0", 11);
	num = (st[0]*11) + (st[1]*10) + (st[2]*9) + (st[3]*1) + (st[4]*2) + (st[5]*3)
		+ (st[6]*4) + (st[7]*5) + (st[8]*6) + (st[9]*7) + (st[10]*8);
	remain = num % 99;
	
	return st + remain.toString().lpad("0", 2);
}

Installment.prototype.PayInstallment = function(){
	
	PartID = this.PartPanel.down("[itemId=PartID]").getValue();
	PayAmount = this.PayPanel.down("[itemId=PayAmount]").getValue();
	
	if(PayAmount == "")
		return;

	window.open(this.address_prefix + "../../portal/epayment/epayment_step1.php?PartID=" + 
		PartID + "&amount=" + PayAmount);	
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
				PartID : me.PartID
			},
			success: function(response){
				mask.hide();
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

	window.open(this.address_prefix + "../report/LoanPayment.php?show=true&PartID=" + this.PartID);
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
			width : 160,
			height : 85,
			modal : true,
			title : "تعداد ماه تمدید",
			bodyStyle : "background-color:white",
			items : [{
				xtype : "numberfield",
				name : "monthCount",
				hideTrigger : true
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
							PartID : record.data.PartID,
							InstallmentID : record.data.InstallmentID,
							months : me.delayWin.down("[name=monthCount]").getValue()						
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

function LoanRFID(RequestID)
{
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