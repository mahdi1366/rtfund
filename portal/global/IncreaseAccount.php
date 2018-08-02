<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.08
//-------------------------
 
require_once '../header.inc.php';
require_once 'global.data.php';

$temp = AccDocFlow(COSTID_saving, true);
$TotalAmount = count($temp) > 0 ? $temp[0]["amount"] : 0;
?>
<script type="text/javascript">

IncreaseAccount.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	
	CostID : "<?= COSTID_saving ?>",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function IncreaseAccount()
{
	this.PayPanel = new Ext.form.FieldSet({
		title: "واریز به حساب قرض الحسنه",
		width: 650,
		renderTo : this.get("div_paying"),
		frame: true,
		items : [{
			xtype : "displayfield",
			labelWidth : 150,
			width : 300,
			fieldLabel: 'مبلغ حساب قرض الحسنه',
			fieldCls : "blueText",			
			value : <?= $TotalAmount ?>,
			renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
		},{
			xtype : "currencyfield",
			hideTrigger : true,
			width: 300,
			fieldLabel : "مبلغ پرداخت",
			itemId : "PayAmount"
		},{
			xtype : "button",
			border : true,
			disabled : true,
			style : "margin-right:10px",
			text : "پرداخت الکترونیک بانک اقتصاد نوین",
			iconCls : "epay",
			handler : function(){ IncreaseAccountObject.Pay1(); }
		},{
			xtype : "button",
			border : true,
			itemId : "cmp_ayande",
			style : "margin-right:10px",
			text : "پرداخت الکترونیک بانک آینده",
			iconCls : "epay",
			handler : function(){ IncreaseAccountObject.Pay2(); }
		},{
			xtype : "container",
			columns : 3,
			html : "در حال حاضر به دلیل خطای فنی در شبکه پرداخت الکترونیکی شاپرک امکان پرداخت از طریق بانک اقتصاد نوین میسر نمی باشد.",
			style : "color:red"
		}]
	});
}

var IncreaseAccountObject = new IncreaseAccount();

IncreaseAccount.prototype.Pay1 = function(){
	
	PayAmount = this.PayPanel.down("[itemId=PayAmount]").getValue();
	
	if(PayAmount == "")
		return;

	window.open(this.address_prefix + "../../portal/epayment/account_step1.php?CostID=" + this.CostID
		+ "&amount=" + PayAmount);	
}

IncreaseAccount.prototype.Pay2 = function(){
	
	PayAmount = this.PayPanel.down("[itemId=PayAmount]").getValue();
	
	if(PayAmount == "")
		return;

	window.open(this.address_prefix + "../../portal/epayment-ayande/account_step1.php?CostID=" + this.CostID
		+ "&amount=" + PayAmount);	
}

</script>
<center>
	<div id="div_loans"></div>
	<div id="div_paying"></div>	
	<div id="div_grid"></div>	
</center>