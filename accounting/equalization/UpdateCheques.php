<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.03
//-----------------------------

require_once '../header.inc.php';

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
			fieldLabel : "بانک",
			store: new Ext.data.Store({
				fields:["BankID","BankDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=GetBankData',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			displayField : "BankDesc",
			name : "BankID",
			valueField : "BankID"
		},{
			xtype : "filefield",
			name : "attach",
			fieldLabel : "فایل csv",
			anchor : "100%"
		}],
		buttons : [{
			text : "به روز رسانی چک ها",
			iconCls : "refresh",
			handler : function()
			{
				this.up('form').getForm().submit({
					clientValidation: true,
					url: UpdateChecksObj.address_prefix + 'operation.data.php?task=Equalization_UpdateChecks',
					method : "POST",
					success : function(form,action){
						
						UpdateChecksObj.resultFS.update(action.result.data);
					},
					failure : function(form,action)
					{
						alert("عملیات مورد نظر با شکست مواجه شد");
					}
				});
			}
		}]
	});
	
	this.resultFS = new Ext.form.FieldSet({
		renderTo : this.get("result"),
		width : 510,
		cls : "blueText",
		style : "line-height:22px;text-align:right"
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