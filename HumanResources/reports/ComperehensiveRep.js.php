<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// Date:		93.11
//---------------------------

CompRep.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	mainPanel : "",    	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};
CompRep.prototype.showReport = function(btn,e)
{     		
	this.form = this.get("mainFormReport") ; 	
		
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "ComperehensiveRep.php?show=true"; 
	this.form.submit();	
	return;
}

function CompRep()
{								 
	 this.formPanel = new Ext.form.Panel({
			applyTo: this.get("mainpanelReport"),
			layout: {
                        type:"table",
                        columns:2
                    },
					collapsible: false,
					frame: true,
					title: ' خلاصه پرونده نيروي انساني',
					bodyPadding: '5 5 0',
					width:680,
					fieldDefaults: {
							msgTarget: 'side',
							labelWidth: 100	 
					},
					defaultType: 'textfield',
					items: [{
								xtype : "shdatefield",
								name : "from_execute_date",
								fieldLabel : "از تاريخ",
								width:200
							},
							{
								xtype : "shdatefield",
								name : "to_execute_date",
								fieldLabel : "تا تاريخ",
								width:200
							},
							{
								xtype : "combo",											
								store :  new Ext.data.Store({
									fields : ["ouid","ptitle"],
									proxy : {
												type: 'jsonp',
												url : this.address_prefix + "../global/domain.data.php?task=searchUnits",
												reader: {
													root: 'rows',
													totalProperty: 'totalCount'
												}
											}
											,
										autoLoad : true,
										listeners:{
											load : function(){
													CompRepObject.formPanel.down("[itemId=unitId]").setValue("1");										
											}
										}
											
															}),
								valueField : "ouid",
								displayField : "ptitle",
								hiddenName : "unitId",
								itemId : "unitId",
								fieldLabel : " واحد محل خدمت ",						
								listConfig: {
									loadingText: 'در حال جستجو...',
									emptyText: 'فاقد اطلاعات',
									itemCls : "search-item"
								},
								width:450
							} ] , 
                                buttons: [{
                                            text : "مشاهده گزارش",
                                            handler : Ext.bind(this.showReport,this),
                                            iconCls : "report"                                
                                          }]
                                });
			
}

var CompRepObject = new CompRep() ; 


</script>