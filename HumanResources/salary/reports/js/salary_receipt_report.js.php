<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// Date:		92.07
//---------------------------

salaryReciept.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	mainPanel : "",    	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};
salaryReciept.prototype.showReport = function(btn,e)
{     	
	this.form = this.get("mainForm")
	
	if(!this.form.from_pay_year.value)
	{
		alert('وارد کردن سال الزامی می باشد.')  ; 	
		return false ; 
	}
	
	if(!this.form.from_pay_month.value)
	{
		alert('وارد کردن ماه الزامی می باشد.')  ; 	
		return false ; 
	}

        if(!this.form.PayType.value)
	{
		alert('لطفا نوع پرداخت را وارد نمایید.')  ; 	
		return false ; 
	}
	
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "salary_receipt_report.php?show=true";
	this.form.submit();	
	return;
}

function salaryReciept()
{
	   
	 var types = Ext.create('Ext.data.ArrayStore', {
			fields: ['val', 'title'],
			data : [
                                    ['1','هیئت علمی'],                               
                                ['2','کارمند'],                               
                                        ['3','روزمزدبیمه ای'],                               
['5','قراردادی'],                               
                                   ['102','هیئت علمی،کارمند،روزمزد'],		 
								    ['10','بازنشسته'],
                                  
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
                                title: ' گزارش چاپ فیش حقوقی',
                                bodyPadding: '5 5 0',
                                width:740,
                                fieldDefaults: {
                                        msgTarget: 'side',
                                        labelWidth: 130	 
                                },
                                defaultType: 'textfield',
                                items: [{
                                         xtype:"numberfield" ,
                                         fieldLabel: 'از سال',
										 inputId:'from_pay_year',
                                         name: 'from_pay_year',
                                         width:200,
                                         hideTrigger:true
                                        },										
                                        {
                                            xtype:"numberfield" ,
                                            fieldLabel: 'از ماه',
											inputId:'from_pay_month',
                                            name: 'from_pay_month', 
                                            width:200,
                                            hideTrigger:true
                                        },
										{
                                         xtype:"numberfield" ,
                                         fieldLabel: 'تا سال',
                                         name: 'to_pay_year',
                                         width:200,
                                         hideTrigger:true
                                        },
										{
                                            xtype:"numberfield" ,
                                            fieldLabel: 'تا ماه',
                                            name: 'to_pay_month', 
                                            width:200,
                                            hideTrigger:true
                                        },
										{
											xtype : "combo",
											store :  new Ext.data.Store({
												fields : ["cost_center_id","title"],
												proxy : {
															type: 'jsonp',
															url : this.address_prefix + "../../../global/domain.data.php?task=searchCostCenter",
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
                                            hiddenName:"PersonType",                                    
                                            fieldLabel : "نوع فرد",
                                            store: types,
                                            valueField: 'val',
                                            displayField: 'title'
                                         }
										 ,
										 {
											xtype : "trigger",
											name : "ouid",
											inputId:"ouid",
											fieldLabel : "واحد محل خدمت",
											onTriggerClick : function(){

												var retVal = showLOV("/HumanResources/global/LOV/OrgUnitLOV.php", 900, 550);
												
												if(retVal != '')
												{
													this.setValue(retVal);
												}
											} ,											
											width:200,
											triggerCls:'x-form-search-trigger'
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
																		}),
											valueField : "InfoID",
											displayField : "Title",
											hiddenName : "PayType",
											fieldLabel : "نوع پرداخت",
											listConfig: {
												loadingText: 'در حال جستجو...',
												emptyText: 'فاقد اطلاعات',
												itemCls : "search-item"
											},
											width:300
										}/*,{																					
											xtype:"container" ,
											html:'<input type="checkbox" value="1" id="nshow"  name="nshow"> &nbsp; نمایش تفاوت های منفی'										                                            
										}*/] , 
                                buttons: [{
                                            text : "مشاهده گزارش",
                                            handler : Ext.bind(this.showReport,this),
                                            iconCls : "report"                                
                                          }]
                                });
	
}

var salaryRecieptObject = new salaryReciept() ; 


</script>