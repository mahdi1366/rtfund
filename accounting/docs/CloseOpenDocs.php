<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';

?>
<script>
CloseOpenDoc.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function CloseOpenDoc()
{
	this.form2 = new Ext.form.Panel({
		renderTo : this.get("regDocDIV2"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "صدور سند افتتاحیه و اختتامیه",
		width : 350,
		items :[{
			xtype : "numberfield",
			name : "LocalNo",
			fieldLabel : "شماره سند",		
			hideTrigger : true
		},{
			xtype : "fieldset",
			html : "توجه : در صورتیکه شماره سند را وارد نکنید، سند در آخرین برگه صادر می شود"
		}],
		buttons : [{
			text : "صدور سند اختتامیه",
			iconCls : "account",
			handler : function()
			{
				var mask = new Ext.LoadMask(this.up('form'), {msg:'در حال ذخيره سازي...'});
				mask.show();
				
				var form = this.up('form').getForm();
				form.submit({
					clientValidation: true,
					url: CloseOpenDocObj.address_prefix + 'doc.data.php?task=RegisterEndDoc',
					success: function(fp, o) {
						if(o.result.success)
							Ext.MessageBox.alert("","سند با موفقیت صادر شد");
						else
							Ext.MessageBox.alert("",o.result.data);
						
						mask.hide();
					},
					failure : function(fp, o){
						Ext.MessageBox.alert("",o.result.data);
						mask.hide();
					}
				});
			}
		},{
			text : "صدور سند افتتاحیه",
			iconCls : "account",
			handler : function()
			{
				var mask = new Ext.LoadMask(this.up('form'), {msg:'در حال ذخيره سازي...'});
				mask.show();
				
				var form = this.up('form').getForm();
				form.submit({
					clientValidation: true,
					url: CloseOpenDocObj.address_prefix + 'doc.data.php?task=RegisterStartDoc',
					success: function(fp, o) {
						if(o.result.success)
							Ext.MessageBox.alert("","سند با موفقیت صادر شد");
						else
							Ext.MessageBox.alert("",o.result.data);
						
						mask.hide();
					},
					failure : function(fp, o){
						Ext.MessageBox.alert("",o.result.data);
						mask.hide();
					}
				});
			}
		}]
	});
}

CloseOpenDocObj = new CloseOpenDoc();


</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div><br><br>
		<div id="regDocDIV"></div><br>
		<div id="regDocDIV2"></div><br>
	</center>
</form>