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
	var types = Ext.create('Ext.data.ArrayStore', {
		fields: ['val', 'title'],
		data : [ 
				['100','همه '],
				['1','هیئت علمی'],                               
				['2','کارمند'],                               
				['3','روزمزدبیمه ای'],                               
				['5','قراردادی'],                               
				['102','هیئت علمی،کارمند،روزمزد'], 
			]
	});
                             
    var pTypeStore =  <?= dataReader::MakeStoreObject_Data(manage_domains::GETALL_Payment_Type(),"'InfoID','Title'")?> ; 
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
		width:680,
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
			fieldLabel : "نوع محاسبه",
			store: pTypeStore ,
			inputId:"payment_type",
			valueField: 'InfoID',
			value :"1" ,
			displayField: 'Title'
		 },{
			xtype : "combo",                                                 
			inputId:"PersonType",
			fieldLabel : "نوع فرد",
			store: types,
			allowBlank : false ,
			valueField: 'val',
			displayField: 'title'
		 },
		 {
			xtype: 'fieldset',
			title : "مراکز هزینه",
			colspan : 3,		
			style:'background-color:#DFEAF7;font-size:10px',					
			width : 650,						
			fieldLabel: 'Auto Layout',
			itemId : "chkgroup",
			collapsible: true,
			collapsed: true,
			layout : {
				type : "table",
				columns : 4,
				tableAttrs : {
					width : "100%",
					align : "center"
				},
				tdAttrs : {							
					align:'right',
					width : "۱6%"
				}
			},
			items : [{
				xtype : "checkbox",
				boxLabel : "همه",
				checked : true,
				listeners : {
					change : function(){
						val = this.getValue();
						ConfirmObject.formPanel.down("[itemId=chkgroup]").items.each(function(elem){
							elem.setValue(val);
						});														
					}
				}
			}]
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
		},{
			xtype: 'radiogroup',                                           
			fieldLabel: 'اعمال بر',
			colspan : 2,   
			width : 400 , 
			allowBlank : false ,
			items:
				[{boxLabel: 'حقوق', name: 'ItemType', inputValue: 'salary'},
				 {boxLabel: 'گزارش  پرداخت کسورات', name: 'ItemType' , inputValue: 'reportSub'}]
		},{
			colspan:2,		
			collapsible: true,
			collapsed : true,
			title:'تنظیم پارامترها',
			xtype: 'fieldset',                    
			items:	[{
				xtype:"numberfield" ,
				fieldLabel: 'شماره حساب دانشگاه',
				labelWidth: 150 , 
				name: 'UniAcc',													
				width:300,
				hideTrigger:true
			 },{
				xtype:"numberfield" ,
				fieldLabel: 'بیمه عمر(سهم سازمان)',
				labelWidth: 150 , 
				name: 'OrgOmrInsure',													
				width:300,
				hideTrigger:true
			}]
		}], 
		buttons : [{
			iconCls : "refresh",
			text : "اعمال وضعیت",
			handler : function(){ConfirmObject.Save(this);}
		}]
	});	

	new Ext.data.Store({
						fields : ["cost_center_id","title"],
						proxy : {
							type: 'jsonp',
							url : this.address_prefix + "../../../global/domain.data.php?task=searchCostCenter",
							reader: {
								root: 'rows',
								totalProperty: 'totalCount'
							}
						},
						autoLoad : true,
						listeners:{
							load : function(){
								this.each(function (record) {
									ConfirmObject.formPanel.down("[itemId=chkgroup]").add({
										xtype : "checkbox",
										name : "chkcostID_" + record.data.cost_center_id ,
										boxLabel : record.data.title,
										checked:true,
										style:'font-size:11px'		
									});
									
								});
													
							}}
						
					});
					
	this.DocPanel = new Ext.form.Panel({
		applyTo: this.get("docpanel"),
		layout: {
			type:"table",
			columns:2
		},
		collapsible: false,
		frame: true,
		title: 'صدور پیش سند حسابداری',
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
			fieldLabel : "نوع فرد",
			colspan : 2,
			store : new Ext.data.ArrayStore({
				fields: ['val', 'title'],
				data : [ 
						["contract" , "قراردادی"],
						["other" , "سایر کارکنان"]
					]
			}),
			displayField : "title",
			valueField : "val",
			name : "PersonType"
		},{
			xtype : "fieldset",
			itemId : "cmp_differ",
			style : "text-align:center",
			colspan : 2,
			title : "لیست اقلام حقوقی فاقد کد حساب مربوط به ماه انتخابی",
			items : [{
				xtype : "button",				
				text : "بازیابی اطلاعات",
				handler : function(){
					var mask = new Ext.LoadMask(ConfirmObject.DocPanel.down("[itemId=cmp_differ]"), {msg:'در حال بازیابی اطلاعات...'});
					mask.show();
					Ext.Ajax.request({
						url : ConfirmObject.address_prefix + '../data/payment.data.php',
						method : 'POST',
						params : {
							task : 'DifferSalaryItems',
							PersonType : ConfirmObject.DocPanel.down("[name=PersonType]").getValue(),
							pay_year : ConfirmObject.DocPanel.down("[name=pay_year]").getValue(),
							pay_month : ConfirmObject.DocPanel.down("[name=pay_month]").getValue()
						},
						success : function(response){
							ConfirmObject.DocPanel.down("[itemId=cmp_result]").update(response.responseText);
							mask.hide();
						},
						failure : function(){}
					});
				}
			},{
				type : "container",
				itemId : "cmp_result",
				border : false
			}]
		}], 
		buttons : [{
			iconCls : "list",
			text : "صدور پیش سند حسابداری",
			handler : function(){ConfirmObject.registerDoc(this);}
		},{
			iconCls : "remove",
			text : "حذف پیش سند",
			handler : function(){ConfirmObject.deleteDoc(this);}
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