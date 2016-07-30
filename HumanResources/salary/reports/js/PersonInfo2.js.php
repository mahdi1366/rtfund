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
	
		
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "PersonInfo2.php?show=true";
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
					title: ' گزارش تشکیلات سازمانی',
					bodyPadding: '5 5 0',
					width:680,
					fieldDefaults: {
							msgTarget: 'side',
							labelWidth: 100	 
					},
					defaultType: 'textfield',
					items: [
							{
								xtype : "combo",											
								store :  new Ext.data.Store({
									fields : ["ouid","ptitle"],
									proxy : {
												type: 'jsonp',
												url : this.address_prefix + "../../../global/domain.data.php?task=searchUnits",
												reader: {
													root: 'rows',
													totalProperty: 'totalCount'
												}
											}
											,
										autoLoad : true,
										listeners:{
											load : function(){
													InsureObject.formPanel.down("[itemId=unitId]").setValue("1");										
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
							}/*,
										{
											xtype: 'fieldset',
											title : "وضعیت استخدامی",
											colspan : 3,
											style:'background-color:#DFEAF7',					
											width : 700,						
											fieldLabel: 'Auto Layout',
											itemId : "chkgroup2",	
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
														parentNode = InsureObject.formPanel.down("[itemId=chkgroup2]").getEl().dom;
														elems = parentNode.getElementsByTagName("input");
														for(i=0; i<elems.length; i++)
														{
															if(elems[i].id.indexOf("chkEmpState_") != -1)
																elems[i].checked = this.getValue();
														}
													}
												}
											}]
										},
										{
											xtype: 'fieldset',
											title : "حالت استخدامی",
											colspan : 3,
											style:'background-color:#DFEAF7',					
											width : 700,						
											fieldLabel: 'Auto Layout',
											itemId : "chkgroup3",	
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
														parentNode = InsureObject.formPanel.down("[itemId=chkgroup3]").getEl().dom;
														elems = parentNode.getElementsByTagName("input");
														for(i=0; i<elems.length; i++)
														{
															if(elems[i].id.indexOf("chkEmpMod_") != -1)
																elems[i].checked = this.getValue();
														}
													}
												}
											}]
										} */ ] , 
                                buttons: [{
                                            text : "مشاهده گزارش",
                                            handler : Ext.bind(this.showReport,this),
                                            iconCls : "report"                                
                                          }]
                                });
								
			new Ext.data.Store({
					fields : ["InfoID","Title"],
					proxy : {
						type: 'jsonp',
						url : this.address_prefix + "../../../global/domain.data.php?task=searchEmpState",
						reader: {
							root: 'rows',
							totalProperty: 'totalCount'
						}
					},
					autoLoad : true,
					listeners:{
						load : function(){
							this.each(function (record) {
								InsureObject.formPanel.down("[itemId=chkgroup2]").add({
									xtype : "container",
									html : "<input type=checkbox name=chkEmpState_" + record.data.InfoID + " id=chkEmpState_" + record.data.InfoID + " checked > " + record.data.Title
								});
								
							});
												
						}}
					
				});		
				
			new Ext.data.Store({
					fields : ["InfoID","Title"],
					proxy : {
						type: 'jsonp',
						url : this.address_prefix + "../../../global/domain.data.php?task=searchEmpMod",
						reader: {
							root: 'rows',
							totalProperty: 'totalCount'
						}
					},
					autoLoad : true,
					listeners:{
						load : function(){
							this.each(function (record) {
								InsureObject.formPanel.down("[itemId=chkgroup3]").add({
									xtype : "container",
									html : "<input type=checkbox name=chkEmpMod_" + record.data.InfoID + " id=chkEmpMod_" + record.data.InfoID + " checked > " + record.data.Title
								});
								
							});
												
						}}
					
				});		
	
}

var InsureObject = new Insure() ; 


</script>