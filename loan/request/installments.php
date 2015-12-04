<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.08
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

$dg = new sadaf_datagrid("dg",$js_prefix_address . "request.data.php?task=GetPartInstallments","grid_div");

$dg->addColumn("", "InstallmentID", "", true);
$dg->addColumn("", "IsPaid", "", true);
$dg->addColumn("", "BankDesc", "", true);
$dg->addColumn("", "ChequeBranch", "", true);
$dg->addColumn("", "PaidTypeDesc", "", true);
$dg->addColumn("", "PaidDate", "", true);
$dg->addColumn("", "PaidAmount", "", true);
$dg->addColumn("", "PaidRefNo", "", true);
$dg->addColumn("", "PaidBillNo", "", true);

$col = $dg->addColumn("سررسید", "InstallmentDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("شناسه واریز", "");
$col->renderer = "Installment.PayCodeRender";
$col->width = 90;

$col = $dg->addColumn("مبلغ قسط", "InstallmentAmount", GridColumn::ColumnType_money);

$col = $dg->addColumn("مبلغ جریمه", "ForfeitAmount", GridColumn::ColumnType_money);
$col->width = 80;

$col = $dg->addColumn("اطلاعات پرداخت", "PaidRefNo", "");
$col->renderer = "function(v,p,r){ return Installment.InstallmentPaidInfo(v,p,r);}";

$col = $dg->addColumn("شماره چک", "ChequeNo", "string");
$col->renderer = "Installment.ChequeRender";
$col->width = 80;

$col = $dg->addColumn("پرداخت", "");
$col->renderer = "Installment.payRender";
$col->align = "center";
$col->width = 40;

$dg->height = 370;
$dg->width = 750;
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
	GroupRecord : null,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Installment()
{
	this.grid = <?= $grid ?>;
	
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
				fields :  ['PartAmount','PartDesc',"RequestID","PartDate", "PartID"]
			}),
			displayField: 'RequestID',
			valueField : "PartID",
			queryMode: "local",
			width : 600,
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct" style="height: 23px;">',
				'<td style="padding:7px">کد وام</td>',
				'<td style="padding:7px">مرحله وام</td>',
				'<td style="padding:7px">مبلغ وام</td>',
				'<td style="padding:7px">تاریخ پرداخت</td> </tr>',
				'<tpl for=".">',
					'<tr class="x-boundlist-item" style="border-left:0;border-right:0">',

					'<td style="border-left:0;border-right:0" class="search-item">{RequestID}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{PartDesc}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">',
						'{[Ext.util.Format.Money(values.PartAmount)]}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{PartDate}</td> </tr>',
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

Installment.ChequeRender = function(v,p,r){

	
	var qtip = "<table>"+
			"<tr><td style=padding:3px>بانک :</td><td><b>" + r.data.BankDesc + "</b></td></tr>" +
			"<tr><td style=padding:3px>شعبه :</td><td><b>" + r.data.ChequeBranch + "</b></td></tr>" +
			"</table>";
	if(v != null)
		p.tdAttr = 'data-qtip=\"' + qtip + '\"';
	return v;
}

Installment.PayCodeRender = function(v,p,r){

	st = r.data.InstallmentID.toString().lpad("0", 11);
	num = (st[0]*11) + (st[1]*10) + (st[2]*9) + (st[3]*1) + (st[4]*2) + (st[5]*3)
		+ (st[6]*4) + (st[7]*5) + (st[8]*6) + (st[9]*7) + (st[10]*8);
	remain = num % 99;
	
	return st + remain.toString().lpad("0", 2);
}

Installment.InstallmentPaidInfo = function(v,p,r){
	
	if(r.data.IsPaid == "NO")
		return "";
	
	return "<table width=100%>"+
			"<tr><td>نحوه پرداخت : </td><td>" + r.data.PaidTypeDesc + "</td></tr>" +
			"<tr><td>تاریخ پرداخت: </td><td>" + MiladiToShamsi(r.data.PaidDate.substring(0,10)) + "</td></tr>" + 
			"<tr><td>مبلغ پرداخت : </td><td>" + Ext.util.Format.Money(r.data.PaidAmount) + "</td></tr>" + 
			"<tr><td>شماره پیگیری : </td><td>" + (r.data.PaidRefNo == null ? "-" : r.data.PaidRefNo) + "</td></tr>" + 
			"<tr><td>شماره فیش : </td><td>" + (r.data.PaidBillNo == null ? "-" : r.data.PaidBillNo) + "</td></tr>" + 
			"</table>";
}

var InstallmentObject = new Installment();
	
Installment.prototype.PayInstallment = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	window.open(this.address_prefix + "../../portal/epayment/epayment_step1.php?InstallmentID=" + 
		record.data.InstallmentID + "&amount=" + (record.data.InstallmentAmount*1+record.data.ForfeitAmount*1));	
}

	
</script>
<center>
	<br>
	<div id="div_loans"></div>
	<div id="div_grid"></div>
</center>