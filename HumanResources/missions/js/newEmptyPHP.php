<script type="text/javascript">
//---------------------------
// programmer:	B.Mahdipour
// Date:		94.05
//---------------------------

BestChild.prototype = {
	TabID : "maindiv",
        address_prefix : "",
        get : function(elementID){
           return document.getElementById(elementID);
        }
};

function BestChild()
{
	 var EducLevelTypes = Ext.create('Ext.data.ArrayStore', {
			fields: ['val', 'title'],
			data : [
						['1','ابتدایی'],                               
						['2','متوسط مرحله اول'],
						['3','متوسط مرحله دوم']                                                         
				   ]
                             });  
							 
	  var EducBaseTypes = Ext.create('Ext.data.ArrayStore', {
			fields: ['parentID','val', 'title'],
			data : [
						['1','1','اول'],                               
						['1','2','دوم'],
						['1','3','سوم'],
						['1','4','چهارم'],
						['1','5','پنجم'],
						['1','6','ششم']                                                     ,
						['2','1','اول'],
						['2','2','دوم'],
						
						['3','1','اول'],
						['3','2','دوم'],
						['3','3','سوم'],
						['3','4','پیش دانشگاهی'],
						
						
				   ]
                             }); 
							 
	 this.formAgreementPanel = new Ext.form.Panel({ 
					applyTo: this.get("AgreementPanel"),
					layout: {
								type:"table",
								columns:1
							},
							collapsible: false,
							frame: true,
							title: 'بخشنامه',
							bodyPadding: '5 5 0',
							width:580,
							fieldDefaults: {
									msgTarget: 'side',
									labelWidth: 80	 
							},
							defaultType: 'textfield',
							items: [{
									xtype:"container" ,
									contentEl : this.get("AgrPNL")									
									},
									{
									xtype : "checkbox",
									name : "AggField",
									itemId : "AggField",
									inputValue : "1",
									boxLabel : "شرایط فوق را قبول دارم.",
									listeners : {
										change : function(){

											if(this.getValue() == true )
											{ 
												BestChildObject.formAgreementPanel.down('[itemId=continue]').enable(true);											
											}
											if(this.getValue() == false)
											{
												BestChildObject.formAgreementPanel.down('[itemId=continue]').disable(true);
											}
										}
									}
									}
									],
							buttons: [{
										text : "ادامه...",
										itemId: 'continue',
										iconCls: "",
										handler : function(){ 
											BestChildObject.formAgreementPanel.hide();
											BestChildObject.formPanel.getForm().reset(); 	        
											BestChildObject.formPanel.show();											
										}
									  }]
	
	 });
							 							 
	 this.formPanel = new Ext.form.Panel({
			applyTo: this.get("MainChildPanel"),
			layout: {
                        type:"table",
                        columns:2
                    },
                                collapsible: false,
                                frame: true,
                                title: 'ثبت شکوفه های دانشگاه',
                                bodyPadding: '5 5 0',
                                width:580,
                                fieldDefaults: {
                                        msgTarget: 'side',
                                        labelWidth: 80	 
                                },
                                defaultType: 'textfield',
                                items: [{
                                         xtype:"textfield" ,
                                         fieldLabel: 'نام',
                                         name: 'CFName',
                                         width:200,
                                         hideTrigger:true
                                        },
                                        {
                                            xtype:"textfield" ,
                                            fieldLabel: 'نام خانوادگی',
                                            name: 'CLName', 
                                            width:200,
                                            hideTrigger:true
                                        },
										{
										xtype      : 'fieldcontainer',
										colspan :2,
										fieldLabel : 'جنسیت',
										defaultType: 'radiofield',
										defaults: {
											flex: 1
										},
										layout: 'column',
										columns : 2 ,
										items: [
											{
												boxLabel  : 'دختر&nbsp;',
												name      : 'sex',
												inputValue: '1',
												id        : 'radio1'
											}, {
												boxLabel  : 'پسر &nbsp;',
												name      : 'sex',
												inputValue: '2',
												id        : 'radio2'
											}
										]
									},
                                        {
                                            xtype : "combo",
											colspan: 2,                                          
											name:"EducLevel",
                                            fieldLabel : "دوره تحصیلی",
                                            store: EducLevelTypes,
                                            valueField: 'val',
                                            displayField: 'title',
											listeners : {
												select : function(combo,records){
													var record = records[0];
													var elem = this.up('form').down('[name=EducBase]');
													elem.setValue();
													elem.getStore().clearFilter();
													elem.getStore().filter('parentID',record.data.val)
																									
												}
											}
                                        },
										{
                                            xtype : "combo",
											colspan: 2,
                                            name:"EducBase",                                    
                                            fieldLabel : "پایه تحصیلی",
                                            store: EducBaseTypes,
                                            valueField: 'val',
                                            displayField: 'title'
                                        },
										{
                                         xtype:"numberfield" ,
                                         colspan: 2,
                                         fieldLabel: 'معدل',
                                         name: 'grade',
                                         width:200,
                                         hideTrigger:true
                                        },
										{
											xtype: "container",
											colspan:2,
											items:[
													{
													xtype: "filefield",
													name : "PicFileType",
													fieldLabel : "فایل عکس ",

													itemId :'PicFileType',
													width:300

												},
												{

												}
											]
										},
										{
											xtype: "filefield",
											name : "PaperFileType",
											fieldLabel : "فایل کارنامه ",
											colspan:2,
											itemId :'PaperFileType',
											width:300
											
										}
										] , 
                                buttons: [{
											text : "ذخیره",
											iconCls: "save",
											handler : function(){ 
												BestChildObject.formPanel.getForm().submit({ 						     
												clientValidation: true,
												url:'../data/BestChildren.data.php?task=SaveItem',
												method : "POST",
												params : {								  																									
												},
												success : function(form,action){
													if(action.result.success)
													{
													  BestChildObject.grid.getStore().load();
													  BestChildObject.formPanel.getForm().reset(); 				        										  
													}
													else
													{										
														alert(st.data);
													}
												}
												,
												failure : function(form,action)
												{
													
												}
											});
											}
										}]
                                });
								this.afterLoad();
								this.formPanel.hide();
								this.formAgreementPanel.hide();
								
	
}
var BestChildObject ;
Ext.onReady(function(){ BestChildObject = new BestChild();})


BestChild.opRender = function(value, p, record)
{
	var st = "";
	
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='BestChildObject.EditRequest();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	st += "<div  title='حذف اطلاعات' class='remove' onclick='BestChildObject.deleteInfo();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	return st;
}

 BestChild.prototype.EditRequest = function(record)
 {   		            
	BestChildObject.formPanel.getForm().reset(); 	        
	BestChildObject.formPanel.show();
	var record = this.grid.getSelectionModel().getLastSelected();

	BestChildObject.formPanel.getForm().loadRecord(record);                 
 }
 
  BestChild.prototype.AddRequest = function()
  {
	BestChildObject.formAgreementPanel.show();
	BestChildObject.formAgreementPanel.down('[itemId=continue]').disable(true);	
  }

function ItemStatus(v,p,r)
{	
	if(r.data.status == 0 )
		return 'ارسال شده' ;

	if(r.data.status == 1 )
		return 'تائید' ; 

	if(r.data.status == 2 )
		return 'عدم تائید' ; 

}

</script>