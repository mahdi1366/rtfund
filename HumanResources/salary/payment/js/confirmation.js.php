<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// Date:		90.06
//---------------------------

Confirm.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	mainPanel : "",    	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Confirm()
{
	                             
    var pTypeStore =  <?= dataReader::MakeStoreObject_Data(manage_domains::GETALL_Payment_Type(),"'InfoID','InfoDesc'")?> ; 
	this.formPanel = new Ext.form.Panel({
		applyTo: this.get("mainpanel"),
		layout: {
			type:"table",
			columns:2
		},
		collapsible: false,
		frame: true,
		title: 'قطعی کردن حقوق',
		bodyPadding: '5 5 0',
		width:580,
		fieldDefaults: {
			msgTarget: 'side',
			labelWidth: 100	 
		},
		defaultType: 'textfield',
		items: [{
			xtype:"numberfield" ,
			fieldLabel: 'سال',
			name: 'pay_year',
			allowBlank : false ,
			width:200,
			hideTrigger:true
		},{
			xtype:"numberfield" ,
			fieldLabel: 'ماه',
			name: 'pay_month', 
			allowBlank : false ,
			width:200,
			hideTrigger:true
		},{
			xtype : "combo",  
			colspan : 2,
			fieldLabel : "نوع محاسبه",
			store: pTypeStore ,
			inputId:"payment_type",
			valueField: 'InfoID',
			value :"1" ,
			displayField: 'InfoDesc'
		 },		 
		{
			xtype: 'radiogroup',                                           
			fieldLabel: 'وضعیت',
			allowBlank : false ,
			colspan : 2,    
			width : 400 , 
			items:
				[{boxLabel: 'قطعی', name: 'state', inputValue: '2'},
				 {boxLabel: 'غیرقطعی', name: 'state', inputValue: '1'}]
		}], 
		buttons : [{
			iconCls : "refresh",
			text : "اعمال وضعیت",
			handler : function(){ConfirmObject.Save(this);}
		}]
	});	

						

}

var ConfirmObject = new Confirm() ;

Confirm.prototype.Save = function(btn)
{              
	btn.up('form').getForm().submit({
	    clientValidation: true,
	    url: ConfirmObject.address_prefix + '../data/payment.data.php?task=confirmation',
	    method : "POST",		
	    success : function(form,action){

			if(action.result.success)
			{
			   alert("عملیات با موفقیت انجام گرفت ."); 
			}
			else
			{
				alert(action.result.data);
			}
	    }
	});
}

Confirm.prototype.registerDoc = function(btn)
{
	if(!btn.up('form').getForm().isValid())
		return;
	
	var mask = new Ext.LoadMask(btn.up('form'), {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	btn.up('form').getForm().submit({
	    clientValidation: true,
	    url: ConfirmObject.address_prefix + '../data/payment.data.php?task=registerDoc',
	    method : "POST",		
	    success : function(form,action){
			alert("عملیات با موفقیت انجام گرفت ."); 
			mask.hide();
			
	    },
		failure : function(form,action){
			alert(action.result.data);
			mask.hide();
		}
	});
}

Confirm.prototype.deleteDoc = function(btn)
{
	if(!btn.up('form').getForm().isValid())
		return;
	
	var mask = new Ext.LoadMask(btn.up('form'), {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	btn.up('form').getForm().submit({
	    clientValidation: true,
	    url: ConfirmObject.address_prefix + '../data/payment.data.php?task=deleteDoc',
	    method : "POST",		
	    success : function(form,action){

			alert(action.result.data);
			mask.hide();
	    },
		failure : function(form,action){
			alert(action.result.data);
			mask.hide();
		}
	});
}

</script>