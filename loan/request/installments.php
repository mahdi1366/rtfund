<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.08
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

$framework = isset($_SESSION["USER"]["framework"]);
$PartID = 0;
if($framework)
{
	if(empty($_POST["PartID"]))
		die();
	$PartID = $_POST["PartID"];
}	

$dg = new sadaf_datagrid("dg",$js_prefix_address . "request.data.php?task=GetPartInstallments","grid_div");

$dg->addColumn("", "InstallmentID","", true);
$dg->addColumn("", "PartID","", true);
$dg->addColumn("", "RequestID","", true);
$dg->addColumn("", "InstallmentAmount","", true);
$dg->addColumn("", "BankDesc", "", true);
$dg->addColumn("", "ChequeBranch", "", true);

$col = $dg->addColumn("سررسید", "InstallmentDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("مبلغ قسط", "InstallmentAmount", GridColumn::ColumnType_money);

$col = $dg->addColumn("مبلغ جریمه", "TotalForfeit", GridColumn::ColumnType_money);
$col->width = 80;

$col = $dg->addColumn("مانده", "TotalRemainder", GridColumn::ColumnType_money);
$col->width = 80;

$col = $dg->addColumn("شماره چک", "ChequeNo", "string");
if($framework)
	$col->editor = ColumnEditor::NumberField(true);
$col->width = 80;

$col = $dg->addColumn("بانک", "ChequeBank", "");
if($framework)
	$col->editor = ColumnEditor::ComboBox(PdoDataAccess::runquery("select * from ACC_banks"), 
	"BankID", "BankDesc", "", "", true);
$col->width = 70;

$col = $dg->addColumn("شعبه", "ChequeBranch", "");
if($framework)
	$col->editor = ColumnEditor::TextField(true);
$col->width = 90;

if($framework)
{
	$dg->addButton("cmp_computeInstallment", "محاسبه اقساط", "list", 
			"function(){InstallmentObject.ComputeInstallments();}");
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(store,record){return InstallmentObject.SavePartPayment(store,record);}";
	
	$dg->addButton("cmp_report", "گزارش پرداخت", "report", 
			"function(){InstallmentObject.PayReport();}");
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(store,record){return InstallmentObject.SavePartPayment(store,record);}";
}
if(!$framework)
{
	$col = $dg->addColumn("پرداخت", "");
	$col->renderer = "Installment.payRender";
	$col->align = "center";
	$col->width = 40;
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
		this.grid.plugins[0].on("beforeedit", function(editor,e){
			if(e.record.data.IsPaid == "YES")
				return false;
		});
		
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
				autoLoad : true,
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + 'request.data.php?task=selectMyParts',
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
			queryMode: "local",
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
				select : function(){
					InstallmentObject.grid.getStore().proxy.extraParams = {
						PartID : this.getValue()
					};
					if(InstallmentObject.grid.rendered)
						InstallmentObject.grid.getStore().load();
					else
						InstallmentObject.grid.render(InstallmentObject.get("div_grid"));

					InstallmentObject.PartPanel.collapse();
				}
			}
		}]
	});
	
}

Installment.payRender = function(v,p,r){

	if(r.data.IsPaid == "YES")
		return "";
	return  "<div  title='پرداخت قسط' class='epay' onclick='InstallmentObject.PayInstallment();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

Installment.PayCodeRender = function(v,p,r){

	st = (r.data.RequestID + r.data.PartID).lpad("0", 11);
	num = (st[0]*11) + (st[1]*10) + (st[2]*9) + (st[3]*1) + (st[4]*2) + (st[5]*3)
		+ (st[6]*4) + (st[7]*5) + (st[8]*6) + (st[9]*7) + (st[10]*8);
	remain = num % 99;
	
	return st + remain.toString().lpad("0", 2);
}

Installment.InstallmentPaidInfo = function(v,p,r){
	
	if(r.data.IsPaid == "NO")
		return "";
	
	
}

var InstallmentObject = new Installment();
	
Installment.prototype.PayInstallment = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	window.open(this.address_prefix + "../../portal/epayment/epayment_step1.php?InstallmentID=" + 
		record.data.InstallmentID + "&amount=" + (record.data.InstallmentAmount*1+record.data.ForfeitAmount*1));	
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

Installment.prototype.SavePartPayment = function(store, record){

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'request.data.php',
		method: "POST",
		params: {
			task: "SavePartPayment",
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



</script>
<center>
	<div id="div_loans"></div>
	<div id="div_grid"></div>
</center>