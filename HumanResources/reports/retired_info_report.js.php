<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	92.05
//---------------------------
InsurePerson.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	mainPanel : "",
	advanceSearchPanel : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function InsurePerson()
{
	
		
       var types = Ext.create('Ext.data.ArrayStore', {
			fields: ['val', 'title'],
			data : [
                                    ['1','هیئت علمی'],                               
                                ['2','کارمند']                                
                          ]
                             });
       this.advanceSearchPanel = new Ext.Panel({
                    applyTo: this.get("mainpanel"),
                    layout: {
                    type:"table",
                    columns:2
                    },
                    collapsible: false,
                    frame: true,
                    title: "گزارش جامع سازمان بازنشستگی",
                    bodyPadding: '5 5 0',
                    width:740,
                    fieldDefaults: {
                            msgTarget: 'side',
                            labelWidth: 130	 
                    },
                    defaultType: 'textfield',
                    items: [{
                                xtype:"numberfield" ,
                                fieldLabel: 'سال',
                                itemId:'pay_year',
                                name: 'pay_year',
                                width:200,
                                hideTrigger:true
                            },										
                            {
                                xtype:"numberfield" ,
                                fieldLabel: 'ماه',
                                itemId:'pay_month',
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
                                hiddenName:"PersonType",                                    
                                fieldLabel : "نوع فرد",
                                store: types,
                                value:"1",
                                valueField: 'val',
                                displayField: 'title'
                               
                            }],
                    buttons : [{
					text:'جستجو',
					iconCls: 'search',
					handler: function(){InsurePersonObject.advance_searching('show');}
				   },{
					text : "خروجی Excel",
					iconCls : "excel",
					handler : function(){InsurePersonObject.advance_searching('excel');}
                               }]
       });         
        
	
	
}

var InsurePersonObject = new InsurePerson();

InsurePerson.prototype.advance_searching = function(type)
{ 
	
		<?
			 
			
			
			
		/*	 */
			
			
			
		?>
		
		this.form = this.get("form_SearchPost") ;
		this.form.target = "_blank";
		this.form.method = "POST";
		this.form.action =  this.address_prefix + "retired_info_report.php?showRes=1";
		this.form.action += type == "excel" ? "&excel=true" : "";
		this.form.submit();	
		return;
			
}

</script>