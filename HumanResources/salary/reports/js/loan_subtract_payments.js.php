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
	this.form.action =  this.address_prefix + "loan_subtract_payments.php?show=true";
	this.form.submit();	
	return;
}

function LoanSubtract()
{
	var types = Ext.create('Ext.data.ArrayStore', {
			fields: ['val', 'title'],
			data : [
                                    ['1','هیئت علمی'],                               
                                ['2','کارمند'],                               
                                        ['3','روزمزدبیمه ای'],                               
['5','قراردادی'],                               
                                   ['102','هیئت علمی،کارمند،روزمزد'], 
                                  
                          ]
                             });
                             
        var BeneficiaryCombo = Ext.create('Ext.data.ArrayStore',{
                    fields:['val','title'],
                    data:[  ['100','همه'],
                        ['1','تجارت'], 
                           ['2','ملی'] , 
                           ['3','پردیس'] , 
                          ['10','سایر بانکها']                     
                        
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
                                title: 'گزارش در خواست پرداخت  اقساط وام و کسورات متفرقه حقوق',
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
                                        },
                                        {
                                            xtype : "combo",
                                            hiddenName:"PersonType",                                    
                                            fieldLabel : "نوع فرد",
                                            store: types,
                                            valueField: 'val',
                                            displayField: 'title'
                                         },
                                        {
                                            xtype : "combo",
                                            hiddenName:"BeneficiaryID",                                    
                                            fieldLabel : "ذی نفع",
                                            store: BeneficiaryCombo ,
                                            valueField: 'val',
                                            displayField: 'title'
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