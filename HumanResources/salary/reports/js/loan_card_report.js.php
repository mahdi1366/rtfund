<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// Date:		92.08
//---------------------------

LoanCard.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	mainPanel : "",    	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};
LoanCard.prototype.showReport = function(btn,e)
{     	
	this.form = this.get("mainForm")
	
	if(!this.form.pay_year.value)
	{
		alert('وارد کردن سال الزامی می باشد.')  ; 	
		return false ; 
	}
	
	if(!this.form.pay_month.value)
	{
		alert('وارد کردن ماه الزامی می باشد.')  ; 	
		return false ; 
	}
//this.form.RepFormat_1.checked ||
	if( ( this.form.RepFormat_2.checked )  && !this.form.SITID1.value)
	{
		alert('وارد کردن کد وام الزامی است .')  ; 	
		return false ; 
	}
	//this.form.RepFormat_0.checked ||
	if((  this.form.RepFormat_2.checked ) && !this.form.SITID2.value)
	{
		alert('وارد کردن کد کسور الزامی می باشد.')  ; 	
		return false ; 
	}
	if(!this.form.PTY.value)
	{
		alert('وارد کردن نوع فرد الزامی است.')  ; 	
		return false ; 
	}
	
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "loan_card_report.php?show=true";
	this.form.submit();	
	return;
}

function LoanCard()
{
	 var types = Ext.create('Ext.data.ArrayStore', {
			fields: ['val', 'title'],
			data : [
                                    ['1','هیئت علمی'],                               
                                ['2','کارمند'],                               
                                        ['3','روزمزدبیمه ای'],                               
['5','قراردادی'],                               
['10','بازنشسته'],
                                   ['102','هیئت علمی،کارمند،روزمزد'],		 
						    
                                  
                          ]
                             });  
								 
	 this.formPanel = new Ext.form.Panel({
			applyTo: this.get("mainpanel"),
			layout: {
                        type:"table",
                        columns:2
                    },
                                collapsible: false,
                                frame: true,
                                title: ' گزارش ترکیبی پس انداز و اقساط وام',
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
                                         width:200,
                                         hideTrigger:true
                                        },
                                        {
                                            xtype:"numberfield" ,
                                            fieldLabel: 'ماه',
                                            name: 'pay_month', 
                                            width:200,
                                            hideTrigger:true
                                        },
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
                                            xtype : "combo",
                                            hiddenName:"PTY",                                    
                                            fieldLabel : "نوع فرد",
                                            store: types,
                                            valueField: 'val',
                                            displayField: 'title'
                                         }
										,{
                                            xtype : "trigger",
											name : "SITID1",
											inputId:"SITID1",
											fieldLabel : "کد وام",
											onTriggerClick : function(){

												var retVal = showLOV("/HumanResources/global/LOV/SalaryItemLOV.php?type=1", 900, 550);
												if(retVal != '')
												{
													this.setValue(retVal);
												}
											} ,											
											width:250,
											triggerCls:'x-form-search-trigger'
                                        },{
                                            xtype : "trigger",
											name : "SITID2",
											inputId:"SITID2",
											fieldLabel : "کد کسور ثابت",
											onTriggerClick : function(){

												var retVal = showLOV("/HumanResources/global/LOV/SalaryItemLOV.php?type=2", 900, 550);
												if(retVal != '')
												{
													this.setValue(retVal);
												}
											} ,											
											width:250,
											triggerCls:'x-form-search-trigger'
                                        },
										{
											xtype : "combo",
											store :  new Ext.data.Store({
												fields : ["cost_center_id","title"],
												proxy : {
															type: 'jsonp',
															url : this.address_prefix + "../../../global/domain.data.php?task=searchCostCenter&rep=1",
															reader: {
																root: 'rows',
																totalProperty: 'totalCount'
															}
														}
																		}),
											valueField : "cost_center_id",
											displayField : "title",
											hiddenName : "cost_center_id",											
											fieldLabel : "مرکز هزینه",
											listConfig: {
												loadingText: 'در حال جستجو...',
												emptyText: 'فاقد اطلاعات',
												itemCls : "search-item"
											},
											width:400
										},
										{
											xtype : "combo",
											store :  new Ext.data.Store({
												fields : ["bank_id","name"],
												proxy : {
															type: 'jsonp',
															url : this.address_prefix + "../../../global/domain.data.php?task=searchBank&rep=1",
															reader: {
																root: 'rows',
																totalProperty: 'totalCount'
															}
														}
																		}),
											valueField : "bank_id",
											displayField : "name",
											hiddenName : "BankID",										
											fieldLabel : "نام بانک",
											listConfig: {
												loadingText: 'در حال جستجو...',
												emptyText: 'فاقد اطلاعات',
												itemCls : "search-item"
											},
											width:250
										},{
						xtype : "combo",
						colspan:3,
						store :  new Ext.data.Store({
							fields : ["InfoID","Title"],
							proxy : {
										type: 'jsonp',
										url : this.address_prefix + "../../../global/domain.data.php?task=searchPayType",
										reader: {
											root: 'rows',
											totalProperty: 'totalCount'
										}
									}
									,
								autoLoad : true,
								listeners:{
									load : function(){
											LoanCardObject.formPanel.down("[itemId=PayType]").setValue("1");										
									}
								}
									
													}),
						valueField : "InfoID",
						displayField : "Title",
						hiddenName : "PayType",
						itemId : "PayType",
						fieldLabel : "نوع پرداخت&nbsp;",						
						listConfig: {
							loadingText: 'در حال جستجو...',
							emptyText: 'فاقد اطلاعات',
							itemCls : "search-item"
						},
						width:300
					},
										{
											colspan:4,										
											xtype: 'container',                    											
											html:" نوع گزارش : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
												 "<input type=radio id='RepFormat_0' name='RepFormat' value='0' checked>&nbsp;  کسورات "+
												 "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
												 "<input type=radio id='RepFormat_1' name='RepFormat' value='1'>&nbsp;  اقساط وام " +
												 "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
												 "<input type=radio id='RepFormat_2' name='RepFormat' value='2'>&nbsp;  ترکیبی "
										},
										{
											colspan:4,										
											xtype: 'container',                    
											html:"<br>" + 
												"خروجی گزارش : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
												 "<input type=radio name='RepType' value='0' checked>&nbsp; به تفکیک مرکز هزینه "+
												 "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
												 "<input type=radio name='RepType' value='1'>&nbsp;  نمایش الفبایی "
										}
										] , 
                                buttons: [{
                                            text : "مشاهده گزارش",
                                            handler : Ext.bind(this.showReport,this),
                                            iconCls : "report"                                
                                          }]
                                });
	
}

var LoanCardObject = new LoanCard() ; 


</script>