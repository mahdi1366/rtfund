<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// Date:		90.04
//---------------------------

LoanSubtract.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	mainPanel : "",    	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};
LoanSubtract.prototype.showReport = function(btn,e)
{            
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "plan_report.php?show=true";
	this.form.submit();	
	return;
}

function LoanSubtract()
{
	var types = Ext.create('Ext.data.ArrayStore', {
			fields: ['val', 'title'],
			data : [
                                    ['1','به تفکیک مرکز هزینه'],                               
                                ['2','کلی']                                                         ]
                             });                             
       
	 this.formPanel = new Ext.form.Panel({
			applyTo: this.get("mainpanel"),
			layout: {
                        type:"table",
                        columns:2
                    },
                                collapsible: false,
                                frame: true,
                                title: ' گزارش طرح و برنامه',
                                bodyPadding: '5 5 0',
                                width:580,
                                fieldDefaults: {
                                        msgTarget: 'side',
                                        labelWidth: 80	 
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
                                        }/*,										
										{
											xtype : "checkbox",
											name : "Sep",
											boxLabel : "به تفکیک مزکز هزینه",
											inputValue : 1 
										}*/,										
                                        {
                                            xtype : "combo",
                                            hiddenName:"ReportType",                                    
                                            fieldLabel : "نوع گزارش",
                                            store: types,
                                            valueField: 'val',
                                            displayField: 'title'
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
											allowBlank : false,
											listConfig: {
												loadingText: 'در حال جستجو...',
												emptyText: 'فاقد اطلاعات',
												itemCls : "search-item"
											},
											width:300
										}] , 
                                buttons: [{
                                            text : "مشاهده گزارش",
                                            handler : Ext.bind(this.showReport,this),
                                            iconCls : "report"                                
                                          }]
                                });
	
}

var LoanSubtractObject = new LoanSubtract() ; 


</script>