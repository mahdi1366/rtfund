<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	94.02
//---------------------------

Leave.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function Leave()
{
	
	this.formPanel = new Ext.form.Panel({
			renderTo : this.get("leaveFormDIV"),
			frame : true,
			bodyStyle : "text-align:right;padding:5px",
			title : "محاسبه ذخیره و بازخرید مرخصی استحقاقی",
			defaults : {
				labelWidth :150
			},
			width : 500,
	items :[ 
	{
		xtype : "trigger",
		name : "SID",
		inputId:"SID",
		fieldLabel : "شماره شناسایی",
		onTriggerClick : function(){

			var retVal = showLOV("/HumanResources/global/LOV/StaffLOV.php", 900, 550);
			if(retVal != '')
			{
				this.setValue(retVal);
			}
		} ,											
		width:250,
		triggerCls:'x-form-search-trigger'
	},
	{
		xtype:"numberfield" ,
		fieldLabel: 'سال',
		inputId:'pay_year',
		name: 'pay_year',
		width:250,
		hideTrigger:true
	}		
	],
	buttons : [{
	text :  "ذخیره و بازخرید مرخصی ",
	iconCls : "process",
	handler : function()
	{	mask = new Ext.LoadMask(Ext.getCmp(LeaveObject.TabID), {msg:'در حال انجام عملیات...'});
		mask.show();

		this.up('form').getForm().submit({				     
			clientValidation: true,
			url: LeaveObject.address_prefix + 'RedemptionLeave.php?task=Compute',
			method : "POST",                                                                                
			success : function(form,action){
				mask.hide();
				LeaveObject.get("result").innerHTML = ' محاسبه ذخیره مرخصی استحقاقی با موفقیت صورت گرفت. ' ;
			},
			failure : function(form,action)
			{	mask.hide();
				alert("عملیات مورد نظر با شکست مواجه شد");
			}
		}); 
	}
	},
	{
	text :  " ابطال ذخیره مرخصی ",
	iconCls : "cross",
	handler : function()
	{	mask = new Ext.LoadMask(Ext.getCmp(LeaveObject.TabID), {msg:'در حال انجام عملیات...'});
		mask.show();

		this.up('form').getForm().submit({				     
			clientValidation: true,
			url: LeaveObject.address_prefix + 'RedemptionLeave.php?task=Cancle',
			method : "POST",                                                                                
			success : function(form,action){
				mask.hide();
				LeaveObject.get("result").innerHTML = 'ابطال ذخیره مرخصی با موفقیت صورت گرفت.' ;
			},
			failure : function(form,action)
			{	mask.hide();
				alert("عملیات مورد نظر با شکست مواجه شد");
			}
		}); 
	}
	}]
	});
		
}

var LeaveObject = new Leave();


</script>