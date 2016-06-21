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
				['2','7','اول'],
				['2','8','دوم'],
						
				['3','9','اول'],
				['3','10','دوم'],
				['3','11','سوم'],
				['3','12','پیش دانشگاهی'],
						
						
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
					xtype:"textfield" ,
					fieldLabel: 'کد ملی',
					name: 'NationalCode', 
					colspan :2,
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
					layout:'hbox',
					items:[
						{
							xtype: "filefield",
							name : "PicFileType",
							fieldLabel : "فایل عکس ",

							itemId :'PicFileType',
							width:300
						},
						{
							xtype : "button",													
							iconCls: "down",
							itemId : "DownPic" ,
							handler : function(){ 
								var BSID = BestChildObject.grid.getSelectionModel().getLastSelected().data.BSID;        
								window.open("/hrms_portal/StaffUtility/ui/ReceiptFile.php?BSID="+BSID);
							}
						}
					]
				},
				{
					xtype: "container",
					colspan:2,
					layout:'hbox',
					items:[
						{
							xtype: "filefield",
							name : "PaperFileType",
							fieldLabel : "فایل کارنامه ",														
							itemId :'PaperFileType',
							width:300
						},
						{
							xtype : "button",													
							iconCls: "down",
							itemId : "DownPaper" ,
							handler : function(){ 
								var BSID = BestChildObject.grid.getSelectionModel().getLastSelected().data.BSID;        
								window.open("/hrms_portal/StaffUtility/ui/ReceiptFile.php?PaperID="+BSID);
							}
						}
													
					]
				},
				{
					xtype : "numberfield",
					name : "BSID",
					itemId : "BSID",
					hidden : true 															
				},
                                {
                                        xtype:"container" ,
					html: '*همکار گرامی ارسال فایل عکس و کارنامه الزامی می باشد.',
                                        colspan:2,
                                        style : "font-color:red;"
                                        
                                        
					
                                }
										
			] , 
			buttons: [{
					text : "ذخیره",
					itemId : "b1" ,
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
									BestChildObject.formPanel.hide();		        										  
								}
								else
								{										
									alert(st.data);
								}
							}
							,
							failure : function(form,action)
							{
								alert(action.result.data);
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
        this.formAgreementPanel.hide();
		var record = this.grid.getSelectionModel().getLastSelected();

		BestChildObject.formPanel.getForm().loadRecord(record);    

		if(record.data.status > 0 ) {
		   BestChildObject.formPanel.getEl().readonly(); 
		   BestChildObject.formPanel.down('[itemId=b1]').setDisabled = true ;	 
		}
		   
		if(!record.data.PicFileType) 
			BestChildObject.formPanel.down('[itemId=DownPic]').hide();   
		else if(record.data.PicFileType) 
			BestChildObject.formPanel.down('[itemId=DownPic]').show();   
	
		if(!record.data.PaperFileType) 
			BestChildObject.formPanel.down('[itemId=DownPaper]').hide();   
		else if(record.data.PaperFileType) 
			BestChildObject.formPanel.down('[itemId=DownPaper]').show();   
	}
 
	BestChild.prototype.AddRequest = function()
	{
		BestChildObject.formPanel.hide();
		BestChildObject.formAgreementPanel.show();
        BestChildObject.formPanel.down('[itemId=DownPic]').hide();  
        BestChildObject.formPanel.down('[itemId=DownPaper]').hide();  
		BestChildObject.formAgreementPanel.down('[itemId=continue]').disable(true);	
	}
	
	BestChild.prototype.deleteInfo = function()
	{
		if(!confirm("آیا از حذف اطمینان دارید؟"))
			return;
	
		var record = this.grid.getSelectionModel().getLastSelected();
		

		Ext.Ajax.request({
			url: this.address_prefix + '../data/BestChildren.data.php?task=DelStu',
			params:{
				BSID: record.data.BSID
			},
			method: 'POST',
			success: function(response,option){
				
				var st = Ext.decode(response.responseText);
				if(st.success)
				{
					alert("حذف با موفقیت انجام شد.");
					BestChildObject.grid.getStore().load();
				}
				else
				{
					alert(st.data);
				}
			},
			failure: function(){}		
		});
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