<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.03
//-----------------------------

require_once '../../header.inc.php';

?>
<script>
DebtorsExport.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function DebtorsExport()
{
	new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "تولید فایل برای سیستم حقوق دانشگاه",
		width : 350,
		items :[{
			xtype : "numberfield",
			name : "month",
			fieldLabel : "ماه",		
			hideTrigger : true
		}],
		buttons : [{
			text : "دریافت فایل مربوط به کسر از حقوق ها برای سیستم دانشگاه",
			iconCls : "excel",
			handler : function()
			{
				var month = this.up('form').down("[name=month]").getValue();
				window.open(DebtorsExportObj.address_prefix + '../data/debtors.data.php?task=GetSalaryExcel&month=' + month);
			}
		}]
	});
	
	/*new Ext.form.Panel({
		renderTo : this.get("regDocDIV"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "صدور سند حسابداری",
		width : 350,
		items :[{
			xtype : "numberfield",
			name : "DocID",
			fieldLabel : "شماره سند",		
			hideTrigger : true
		},{
			xtype : "numberfield",
			name : "month",
			fieldLabel : "ماه",		
			hideTrigger : true
		}],
		buttons : [{
			text : "صدور سند",
			iconCls : "excel",
			handler : function()
			{
				var DocID = this.up('form').down("[name=DocID]").getValue();
				var month = this.up('form').down("[name=month]").getValue();
				Ext.Ajax.request({
					url : DebtorsExportObj.address_prefix + '../data/debtors.data.php',
					method : "post",
					params : {
						task : "RegisterDebtorsDoc",
						month: month,
						DocID : DocID
					},
					success : function(response){
						var sd = Ext.decode(response.responseText);
						if(sd.success)
							alert("عملیات مورد نظر با موفقیت انجام شد");
						else if(sd.data == "RegBefore")
							alert("سند بدهکاران مربوط به این ماه قبلا صادر شده است");
						else
							alert("عملیات با شکست مواجه شد");
					}
				});
			}
		}]
	});*/
	
	this.form2 = new Ext.form.Panel({
		renderTo : this.get("regDocDIV2"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "صدور سند بر اساس فایل دریافتی از سیستم حقوق دانشگاه",
		width : 350,
		items :[{
			xtype : "numberfield",
			name : "DocID",
			fieldLabel : "شماره سند",		
			hideTrigger : true
		},{
			xtype : "numberfield",
			name : "month",
			fieldLabel : "ماه",		
			allowBlank : false,
			hideTrigger : true
		},{
			xtype : "filefield",
			name : "attach",
			allowBlank : false,
			fieldLabel : "فایل excel"
		}, {
			xtype : "container",
			itemId : "errors"
		}],
		buttons : [{
			text : "صدور سند",
			iconCls : "excel",
			handler : function()
			{
				var form = this.up('form').getForm();
                if(form.isValid()){
                    form.submit({
                        url: DebtorsExportObj.address_prefix + '../data/debtors.data.php?task=ExcelRegisterDebtorsDoc',
                        success: function(fp, o) {
							var errors = o.result.data;
							if(errors.length == 0)
								alert("سند با موفقیت صادر شد");
							else
								DebtorsExportObj.form2.down("[itemId=errors]").update("<hr>" + Ext.Array.implode("<br>",  o.result.data));
                            
                        }
                    });
                }				
			}
		}]
	});
}
var aaa;
DebtorsExportObj = new DebtorsExport();


</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div><br><br>
		<div id="regDocDIV"></div><br>
		<div id="regDocDIV2"></div><br>
	</center>
</form>