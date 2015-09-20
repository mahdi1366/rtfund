<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.03
//-----------------------------

require_once '../../header.inc.php';

?>
<script>
shareCreate.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function shareCreate()
{
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "افتتاح سهام",
		defaults : {
			labelWidth :150
		},
		width : 500,
		items :[{
			xtype : "shdatefield",
			name : "DayDate",
			fieldLabel : "تاریخ روز انجام عملیات",
		},{
			xtype : "textfield",
			anchor : "100%",
			fieldLabel : "نام و نام خانوادگی",
			name : "tafsiliName"
		},{
			xtype : "currencyfield",
			name : "amount",
			hideTrigger : true,
			fieldLabel : "مبلغ"			
		},{
			xtype : "checkbox",
			boxLabel : "صدور فاکتور متفرقه برای پرداخت به صورت غیر نقدی",
			name : "CashPay",
			inputValue : "1"
		}],
		buttons : [{
			text : "صدور سند مربوطه",
			iconCls : "account",
			handler : function()
			{
				this.up('form').getForm().submit({
					clientValidation: true,
					url: shareCreateObj.address_prefix + '../data/acc_docs.data.php?task=shareCreateDoc',
					method : "POST",
					success : function(form,action){
						alert("سند مربوطه به موفقیت صادر گردید");
						form.reset();
					},
					failure : function(form,action)
					{
						alert("عملیات مورد نظر با شکست مواجه شد");
					}
				});
			}
		}]
	});
}

shareCreateObj = new shareCreate();

</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
</form>