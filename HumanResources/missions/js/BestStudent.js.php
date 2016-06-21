<script type="text/javascript">
	//---------------------------
	// programmer:	B.Mahdipour
	// Date:		94.05
	//---------------------------

	BestStu.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",

		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};

	function BestStu()
	{

		this.formPanel = new Ext.form.Panel({ 
			applyTo: this.get("ChildInfoPanel"),
			layout: {
				type:"table",
				columns:1
			},
			collapsible: false,
			frame: true,
			title: 'اطلاعات شخص',
			bodyPadding: '5 5 0',
			width:580,
			fieldDefaults: {
				msgTarget: 'side',
				labelWidth: 120	 
			},
			defaultType: 'textfield',
			items: [{
					xtype : "fieldset",
					width:540,
					style: "margin:0 4 5 10",
					title : " اطلاعات ولی دانش آموز ",
					itemId : "f1",
					colspan:2,
					layout: 'vbox',	
					items :[{
							xtype:"displayfield" ,fieldCls :'blueText',

							fieldLabel: 'نام و نام خانوادگی',
							name: 'FullName', 
							width:300,
							hideTrigger:true
						},
						{
							xtype:"displayfield" ,fieldCls :'blueText',
							fieldLabel: 'نوع فرد',
							name: 'ptype', 
							width:300,
							hideTrigger:true
						},
						{
							xtype:"displayfield" ,fieldCls :'blueText',
							fieldLabel: 'محل خدمت',
							name: 'FullUnitTitle', 
							width:800,
							hideTrigger:true
						},
						{
							xtype:"displayfield" ,fieldCls :'blueText',
							fieldLabel: 'تلفن همراه ',
							name: 'mobile_phone', 
							width:300,
							hideTrigger:true
						} ]
				},
				{
					xtype : "fieldset",
					width:540,
					style: "margin:0 4 5 10",
					title : " اطلاعات دانش آموز",
					itemId : "f2",
					colspan:2,
					layout: 'column',	
					columns:2 ,
					items :[
						{
							xtype: "container",											
							layout:'vbox', width : 430 ,
							items:[
								{
									xtype:"displayfield" ,fieldCls :'blueText',

									fieldLabel: 'نام و نام خانوادگی',
									name: 'FullChildName', 
													
									hideTrigger:true
								},
								{
									xtype:"displayfield" ,fieldCls :'blueText',
									fieldLabel: 'جنسیت',
									name: 'sexTitle', 
									width:300,
									hideTrigger:true
								},
								{
									xtype:"displayfield" ,fieldCls :'blueText',
									fieldLabel: 'دوره تحصیلی ',
									name: 'EducLevelTitle', 
									width:300,
									hideTrigger:true
								},
								{
									xtype:"displayfield" ,fieldCls :'blueText',
									fieldLabel: ' پایه تحصیلی ',
									name: 'EducBaseTitle', 
									width:300,
									hideTrigger:true
								},
								{
									xtype:"displayfield" ,
									fieldCls :'blueText',
									fieldLabel: 'معدل',
									name: 'grade', 
									width:300,
									hideTrigger:true
								} ,	
								{
									xtype: "container",									
									layout :{
										type : "table",
										columns : 2
									},
									width:300,
									items:[
										{										
											xtype : "displayfield",											
											fieldLabel: 'فایل کارنامه'
										},
										{
											xtype : "button",													
											iconCls: "down",
											itemId : "DownPic" ,
											handler : function(){ 
												var BSID = BestStuObject.grid.getSelectionModel().getLastSelected().data.BSID;        
												window.open("<?= $js_prefix_address ?>showImage.php?BPSID="+BSID);
											}
										}
									]
								}]
						},
						{
							xtype : "container",																																				
							itemId : "PicID" ,
							html:''													
						}
					]
				},
				{
					xtype: 'textareafield',
					fieldLabel: 'توضیحات',
					name: 'comments',                              
					colspan:2,
					width:545,
					labelWidth:54,
					rows:3,
					itemId:'comments',
					labelWidth:65
				},									
				{
					xtype : "checkbox",
					name : "AccField",
					itemId : "AccField",
					inputValue : "1",
					boxLabel : "موارد فوق مورد قبول می باشد."									
				},
				{
					xtype : "numberfield",
					name : "BSID",
					itemId : "BSID",
					hidden : true 															
				}
			],
			buttons: [{
					text : "ذخیره",
					itemId: 'save',
					iconCls: "",
					handler : function(){ 
						BestStuObject.formPanel.getForm().submit({ 						     
							clientValidation: true,
							url:'StaffUtility/data/BestChildren.data.php?task=ChangeStatus',
							method : "POST",
							params : {								  																									
							},
							success : function(form,action){
								if(action.result.success)
								{
									BestStuObject.grid.getStore().load();
													 
									BestStuObject.formPanel.hide();		        										  
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
	}




	BestStu.opRender = function(value, p, record)
	{
		var st = "";
	
		st += "<div  title='ویرایش اطلاعات' class='edit' onclick='BestStuObject.EditRequest();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
		
		return st;
	}

	BestStu.prototype.EditRequest = function(record)
	{   	
	 
		this.formPanel.show(); 
		var record = this.grid.getSelectionModel().getLastSelected();

		BestStuObject.formPanel.getForm().loadRecord(record);    

		BestStuObject.formPanel.down('[itemId=PicID]').update('<img src="<?= $js_prefix_address ?>showImage.php?BSID=' + record.data.BSID + '" height="110" >');
	
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