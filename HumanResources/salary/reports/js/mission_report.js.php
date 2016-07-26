<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// Date:		92.11
//---------------------------

Mission.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	//mainPanel : "",    	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};
Mission.prototype.showReport = function(type)
{ 	
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "mission_report.php?showRes=1";
	this.form.action += type == "excel" ? "&excel=true" : "";	
	this.form.submit();	
	return;
}

function Mission()
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
                                title:'گزارش پرداخت ماموریت',
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
                                            xtype : "combo",
                                            hiddenName:"PersonType",                                    
                                            fieldLabel : "نوع فرد",
                                            store: types,
                                            valueField: 'val',
                                            displayField: 'title'
                                         }*/] , 
                                buttons: [{
                                            text : "مشاهده گزارش",                                           
                                            iconCls : "report",
											handler: function(){MissionObject.showReport('show');}
                                          },{
											text : "خروجی Excel",
											iconCls : "excel",
											handler : function(){MissionObject.showReport('excel');}
										}]
                                });
	
}

var MissionObject = new Mission() ; 


</script>