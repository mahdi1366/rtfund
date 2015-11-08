<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.08
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

$dg = new sadaf_datagrid("dg",$js_prefix_address . "request.data.php?task=GetPartInstallments","grid_div");

$dg->addColumn("", "InstallmentID", "", true);
$dg->addColumn("", "StatusID", "", true);
$dg->addColumn("", "ChequeDate", "", true);
$dg->addColumn("", "BankDesc", "", true);
$dg->addColumn("", "ChequeBranch", "", true);

$col = $dg->addColumn("سررسید", "InstallmentDate", GridColumn::ColumnType_date);
$col->width = 90;

$col = $dg->addColumn("مبلغ قسط", "InstallmentAmount", GridColumn::ColumnType_money);

$col = $dg->addColumn("مبلغ جریمه", "ForfeitAmount", GridColumn::ColumnType_money);
$col->width = 80;

$col = $dg->addColumn("تاریخ پرداخت", "PaidDate", GridColumn::ColumnType_date);
$col->width = 90;

$col = $dg->addColumn("مبلغ پرداخت", "PaidAmount", GridColumn::ColumnType_money);
$col->width = 90;

$col = $dg->addColumn("شماره پیگیری", "PaidRefNo", "");
$col->width = 90;

$col = $dg->addColumn("شماره چک", "ChequeNo", "string");
$col->width = 80;

$col = $dg->addColumn("پرداخت", "");
$col->renderer = "Installment.payRender";
$col->align = "center";
$col->width = 50;

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
				fields :  ['PartID','PartDesc',"RequestID","PartDate"]
			}),
			displayField: 'PartDesc',
			valueField : "PartID",
			queryMode: "local",
			width : 600,
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

	if(r.data.StatusID == "100")
		return "";
	return  "<div  title='پرداخت قسط' class='epay' onclick='InstallmentObject.PayInstallment();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
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