<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.03
//-----------------------------

require_once '../../header.inc.php';

?>
<script>
ExtraSub.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function ExtraSub()
{
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "اضافی/کسری سهامداران",
		defaults : {
			labelWidth :150
		},
		width : 500,
		items :[{
			xtype : "shdatefield",
			name : "DayDate",
			fieldLabel : "تاریخ روز انجام عملیات"
		},{
			xtype : "combo",
			anchor : "100%",
			fieldLabel : "انتخاب تفصیلی",
			store: new Ext.data.Store({
				fields:["tafsiliID","tafsiliTitle"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../data/tafsilis.data.php?task=selectTafsili',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			emptyText:'انتخاب تفصیلی ...',
			typeAhead: false,
			name : "tafsiliID",
			pageSize : 10,
			displayField : "tafsiliTitle",
			valueField : "tafsiliID",
			listConfig: {
				loadingText: 'در حال جستجو...',
				emptyText: 'فاقد اطلاعات'
			}
		},{
			xtype : "currencyfield",
			name : "amount",
			hideTrigger : true,
			fieldLabel : "مبلغ"			
		},{
			xtype : "combo",
			store : new Ext.data.Store({
				fields:["id","title"],
				data : [{"id" : 1, "title" : 'اضافی'},{"id" : 2, "title" : "کسری"}]
			}),
			
			displayField : "title",
			fieldLabel : "نوع سند",
			queryMode: 'local',
			valueField : "id",
			name : "amount_type"
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
					url: ExtraSubObj.address_prefix + '../data/acc_docs.data.php?task=ExtraSubDoc',
					method : "POST",
					success : function(form,action){
						alert("سند مربوطه به موفقیت صادر گردید");
						if(ExtraSubObj.formPanel.down('[name=amount_type]').getValue() == 2)
							window.open(ExtraSubObj.address_prefix + "../../sale/ui/printReceipt.php?rowID=" + action.result.data);
						form.reset();
					},
					failure : function(form,action)
					{
						if(action.result.data == "ConfirmError")
							alert("گزارش روزانه در تاریخ مورد نظر تایید شده و امکان ثبت در آن روز را ندارید");
						else
							alert("عملیات مورد نظر با شکست مواجه شد");
					}
				});
			}
		}]
	});
}

ExtraSubObj = new ExtraSub();

</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
		<span >اضافی : فرد پول اضافه ای را پرداخت کرده و صندوق باید مبلغ اضافه را به فرد برگرداند
			<br>
			کسری : فرد پولی را بدهکار است و به صندوق می دهد.
		</span>
	</center>
</form>