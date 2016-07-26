<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// Date:		90.04
//---------------------------

Insure.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	mainPanel : "",    	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};
Insure.prototype.showReport = function(btn,e)
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
	
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "insure_report.php?show=true";
	this.form.submit();	
	return;
}

function Insure()
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
							 
	 this.formPanel = new Ext.form.Panel({
			applyTo: this.get("mainpanel"),
			layout: {
                        type:"table",
                        columns:2
                    },
                                collapsible: false,
                                frame: true,
                                title: ' گزارش بیمه خدمات درمانی',
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
                                        }
										,
                                        {
                                            xtype:"textfield" ,
                                            fieldLabel: 'نام',
                                            name: 'FName', 
                                            width:200,
                                            hideTrigger:true
                                        }
										,
                                        {
                                            xtype:"textfield" ,
                                            fieldLabel: 'نام خانوادگی',
                                            name: 'LName', 
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
                                         }] , 
                                buttons: [{
                                            text : "مشاهده گزارش",
                                            handler : Ext.bind(this.showReport,this),
                                            iconCls : "report"                                
                                          }]
                                });
	
}

var InsureObject = new Insure() ; 


</script>