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
	pTypeStore =  <?= dataReader::MakeStoreObject_Data(manage_domains::GETALL_Payment_Type(),"'InfoID','InfoDesc'")?> ; 
	
	this.formPanel = new Ext.form.Panel({
		applyTo: this.get("mainpanel"),
		layout: {
			type:"table",
			columns:1
		},
		collapsible: false,
		frame: true,
		title: 'پرداخت حقوق',
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
			value : '<?= substr(DateModules::shNow(),0,4) ?>',
			width:200,
			hideTrigger:true
		},{
			xtype:"numberfield" ,
			fieldLabel: 'ماه',
			value : '<?= substr(DateModules::shNow(),5,2)*1 ?>',
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
		 },{
			 xtype : "container",
			 html : "<hr>"
		 }], 
		buttons : [{
			iconCls : "tick",
			text : "پرداخت حقوق",
			handler : function(){ConfirmObject.Save(this, 2);}
		},{
			iconCls : "cross",
			text : "برگشت پرداخت",
			handler : function(){ConfirmObject.Save(this, 1);}
		}]
	});	

	this.SettingPanel = new Ext.form.Panel({
		applyTo: this.get("settingPanel"),
		layout: "vbox",
		collapsible: true,
		//collapsed : true,
		frame: true,
		title: 'تنظیمات حساب های سند حقوق',
		bodyPadding: '5 5 0',
		width:580,
		fieldDefaults: {
			msgTarget: 'side',
			labelWidth: 150	 
		},
		items: [{
			xtype : "combo",
			width : 385,
			store: new Ext.data.Store({
				fields:["CostID","CostCode","CostDesc", "TafsiliType","TafsiliType2",{
					name : "fullDesc",
					convert : function(value,record){
						return "[ " + record.data.CostCode + " ] " + record.data.CostDesc
					}				
				}],
				proxy: {
					type: 'jsonp',
					url: '/accounting/baseinfo/baseinfo.data.php?task=SelectCostCode',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			fieldLabel : "خالص پرداختی هر فرد",
			valueField : "CostID",
			name : "RT_PurePay",
			displayField : "CostDesc"
		}],
		buttons :[{
			text : "ذخیره",
			iconCls : "save",
			handler: function(){ConfirmObject.SaveSetting(this);}
		}]
	});

}

var ConfirmObject = new Confirm() ;

Confirm.prototype.Save = function(btn, state)
{             
	var mask = new Ext.LoadMask(btn.up('form'), {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	btn.up('form').getForm().submit({
	    clientValidation: true,
	    url: ConfirmObject.address_prefix + '../data/payment.data.php?task=confirmation',
	    method : "POST",
		params : {
			state : state
		},
	    success : function(form,action){
			mask.hide();
			Ext.MessageBox.alert("","عملیات با موفقیت انجام گرفت ."); 
	    },
		failure : function(form,action){
			mask.hide();
			Ext.MessageBox.alert("ERROR",action.result.data);
	    }
	});
}

Confirm.prototype.SaveSetting = function(btn)
{             
	var mask = new Ext.LoadMask(btn.up('form'), {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	btn.up('form').getForm().submit({
	    clientValidation: true,
	    url: this.address_prefix + '../data/payment.data.php?task=SaveSetting',
	    method : "POST",
		
	    success : function(form,action){
			mask.hide();
			Ext.MessageBox.alert("","عملیات با موفقیت انجام گرفت ."); 
	    },
		failure : function(form,action){
			mask.hide();
			Ext.MessageBox.alert("ERROR",action.result.data);
	    }
	});
}
</script>