<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.03
//-----------------------------

require_once '../../header.inc.php';

?>
<script>
UpdateChecks.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function UpdateChecks()
{
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "به روز رسانی اطلاعات چک ها با فایل بانک",
		defaults : {
			labelWidth :150
		},
		width : 510,
		items :[{
			xtype : "combo",
			fieldLabel : "حساب",
			store: new Ext.data.Store({
				fields:["accountID","accountTitle"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../data/accounts.data.php?task=selectAccount',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			displayField : "accountTitle",
			name : "accountID",
			valueField : "accountID"
		},{
			xtype : "filefield",
			name : "attach",
			fieldLabel : "فایل csv",
			anchor : "100%"
		},{
			xtype : "numberfield",
			name : "DocID",
			labelWidth : 270,
			fieldLabel : "شماره سند حسابداری برای درج ردیف های بدهکاری",
			
			hideTrigger : true
		}],
		buttons : [{
			text : "به روز رسانی چک ها",
			iconCls : "refresh",
			handler : function()
			{
				this.up('form').getForm().submit({
					clientValidation: true,
					url: UpdateChecksObj.address_prefix + '../data/acc_docs.data.php?task=UpdateChecks',
					method : "POST",
					success : function(form,action){
						
						UpdateChecksObj.get("result").innerHTML = action.result.data;
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

UpdateChecksObj = new UpdateChecks();


</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
		<div id="result"></div>
	</center>
</form>