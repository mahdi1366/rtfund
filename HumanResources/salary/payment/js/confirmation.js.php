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
		 },{
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
			fieldLabel : "حساب مربوطه",
			valueField : "CostID",
			itemId : "CostID",
			name : "CostID",
			displayField : "CostDesc",
			listeners : {
				select : function(combo,records){
					me = ConfirmObject;
					me.formPanel.down("[itemId=TafsiliID]").setValue();
					me.formPanel.down("[itemId=TafsiliID]").getStore().proxy.extraParams.TafsiliType = records[0].data.TafsiliType;
					me.formPanel.down("[itemId=TafsiliID]").getStore().load();

					me.formPanel.down("[itemId=TafsiliID2]").setValue();
					me.formPanel.down("[itemId=TafsiliID2]").getStore().proxy.extraParams.TafsiliType = records[0].data.TafsiliType2;
					me.formPanel.down("[itemId=TafsiliID2]").getStore().load();

					if(this.getValue() == "<?= COSTID_Bank ?>")
					{
						me.formPanel.down("[itemId=TafsiliID]").setValue(
							"<?= $_SESSION["accounting"]["DefaultBankTafsiliID"] ?>");
						me.formPanel.down("[itemId=TafsiliID2]").setValue(
							"<?= $_SESSION["accounting"]["DefaultAccountTafsiliID"] ?>");
					}

				}
			}
		},{
			xtype : "combo",
			store: new Ext.data.Store({
				fields:["TafsiliID","TafsiliDesc"],
				proxy: {
					type: 'jsonp',
					url: '/accounting/baseinfo/baseinfo.data.php?task=GetAllTafsilis',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			fieldLabel : "تفصیلی",
			width : 385,
			typeAhead: false,
			pageSize : 10,
			valueField : "TafsiliID",
			itemId : "TafsiliID",
			name : "TafsiliID",
			displayField : "TafsiliDesc",
			listeners : { 
				change : function(){
					t1 = this.getStore().proxy.extraParams["TafsiliType"];
					combo = ConfirmObject.formPanel.down("[itemId=TafsiliID2]");

					if(t1 == <?= TAFTYPE_BANKS ?>)
					{
						combo.getStore().proxy.extraParams["ParentTafsili"] = this.getValue();
						combo.getStore().load();
					}			
					else
						combo.getStore().proxy.extraParams["ParentTafsili"] = "";
				}
			}
		},{
			xtype : "combo",
			store: new Ext.data.Store({
				fields:["TafsiliID","TafsiliDesc"],
				proxy: {
					type: 'jsonp',
					url: '/accounting/baseinfo/baseinfo.data.php?task=GetAllTafsilis',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			fieldLabel : "تفصیلی2",
			width : 385,
			typeAhead: false,
			pageSize : 10,
			valueField : "TafsiliID",
			itemId : "TafsiliID2",
			name : "TafsiliID2",
			displayField : "TafsiliDesc"
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

</script>