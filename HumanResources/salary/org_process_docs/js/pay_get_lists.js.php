<script>
//---------------------------
// programmer:	Mahdipour
// create Date:		91.01.22
//---------------------------

    PGList.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"]?>',
        address_prefix : "<?= $js_prefix_address?>",
	staff_id : "" ,
	cost_center_id : "" ,
	salary_item_type_id :"",
	
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };

function PGList()
{
    this.form = this.get("form_PGList");
     
     this.mainPanel = new Ext.Panel({
                applyTo: this.get("mainpanelDIV"),
                contentEl : this.get("PGTBL"),
                title: "" ,
                width: 600,
                hidden:true , 
                frame:true,
                buttons : [		    
                    {
                        text : "افزودن افراد مرکز",
                        iconCls : "user_add",
                        handler : function(){ 	
			    if(PGListObject.list_type = 4 ) {
				    PGListObject.selectItmWindow =
						    new Ext.Window({
							    applyTo:PGListObject.get("SelectItemWindow"),						
							    contentEl : PGListObject.get("SelectItemPanel"),
							    layout:'fit',
							    modal: true,
							    width:400,
							    autoHeight : true,
							    closeAction:'hide',
							    buttons : [{
									    text : "ثبت افراد",
									    iconCls : "save",
									    handler : function(){ 
												    PGListObject.salary_item_type_id = PGListObject.get("salary_item_type_id").value ; 
												    PGListObject.InsertAllPrn();
												    PGListObject.selectItmWindow.hide();
												}
								    },
								    {
									    text : "انصراف",
									    handler: function(){PGListObject.selectItmWindow.hide();}
								    }
							    ]
						    });

				    PGListObject.selectItmWindow.show();
			    }
			    else {
				    PGListObject.InsertAllPrn();  
				 }
			}
                    },
                    {
                        text : "انصراف ",
                        iconCls : "back",
                        handler : function(){
                            PGListObject.grid.show();
                            PGListObject.mainPanel.hide();
                            PGListObject.mgrid.hide(); }
                    }
                ]
            });
	    
	    this.MissionPanel = new Ext.form.Panel({
			applyTo: this.get("MissionPanel"),
			layout: {
                                type:"table",
                                columns:2
                            },
			collapsible: true,
			frame: true,
			hidden:true , 
			title: 'فرم ماموریت',
			bodyPadding: '2 2 0',
			width:780,
			fieldDefaults: {
				msgTarget: 'side',
				labelWidth: 120	 
			},
			defaultType: 'textfield',
			
			items: [{   xtype : "combo",
				    anchor : "100%",
				    fieldLabel : "نام و نام خانوادگی",
				    store: new Ext.data.Store({
				    pageSize: 10,
				    colspan:2, 
				    model: Ext.define(Ext.id(), {
				    extend: 'Ext.data.Model',
				    fields:['PersonID','pfname','plname','unit_name','person_type','staff_id','personTypeName',{
					    name : "fullname",
					    convert : function(v,record){return record.data.pfname+" "+record.data.plname;}
				    }]
				    }),
				    remoteSort: true,
				    proxy:{
				    type: 'jsonp',
				    url: '/HumanResources/personal/persons/data/person.data.php?task=searchPerson&cid=' + this.cost_center_id ,
				    reader: {
				    root: 'rows',
				    totalProperty: 'totalCount'
				    }
				    }
				    }),
				    emptyText:'جستجوي استاد/كارمند بر اساس نام و نام خانوادگي ...',
				    typeAhead: false,
				    listConfig : {
					loadingText: 'در حال جستجو...'
				    },
				    pageSize:10,
				    width: 550,
				    colspan:4,
				    itemId:'STID', 				   
				    name:"STID" ,				    
				    valueField : "staff_id",
				    displayField : "fullname" ,
				    tpl: new Ext.XTemplate(
					    '<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
						,'<td>کد شخص</td>'
						,'<td>نام</td>'
						,'<td>نام خانوادگی</td>'
						,'<td>نوع شخص</td></tr>',
					    '<tpl for=".">',
					    '<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
						,'<td style="border-left:0;border-right:0" class="search-item">{staff_id}</td>'
						,'<td style="border-left:0;border-right:0" class="search-item">{pfname}</td>'
						,'<td style="border-left:0;border-right:0" class="search-item">{plname}</td>'
						,'<td style="border-left:0;border-right:0" class="search-item">{personTypeName}</td>'
						,'</tr>'
						,'</tpl>'
						,'</table>')
			,listeners :{
				    select : function(combo, records){
						var record = records[0];
						this.setValue(record.data.fullname);
						PGListObject.MissionPanel.down('[itemId=STID]').setValue(record.data.staff_id) ;						
						this.collapse();
					}
				    }
				},		
				{	fieldLabel: 'شماره برگه ماموريت ',
					name: 'doc_no',					
					width:200,
					allowBlank : false
				},{	
					fieldLabel: 'تاریخ برگه ماموریت ',
					xtype : "shdatefield",
					format: 'Y/m/d',
					width : 220,					
					allowBlank: false,
					name: 'doc_date'
				},{
					fieldLabel: ' از تاریخ',
					xtype : "shdatefield",
					format: 'Y/m/d',
					width : 220,					
					allowBlank: false,
					name: 'from_date'
				},{ 
					fieldLabel: 'تا تاریخ',
					xtype : "shdatefield",
					format: 'Y/m/d',
					width : 220,					
					allowBlank: false,
					name: 'to_date'
				},{
					xtype : "numberfield",
					fieldLabel: 'مدت به روز',					
					width:200,
					name: 'duration',					
					allowBlank : false
				},{
					fieldLabel: 'مقصد',
					name: 'destination',
					allowBlank : false,					
					width:300
				},{
					fieldLabel: 'ضریب منطقه',
					name: 'region_coef',
					allowBlank : false,					
					width:200
				},
				{
				    xtype : "checkbox",
				    name : "using_facilities",
				    inputValue : "1",
				    boxLabel : "استفاده از تسهیلات",
				    anchor: '100%'
				},
				{
					fieldLabel: 'هزینه سفر',
					itemId:'travel_cost',
					name: 'travel_cost',
					allowBlank : false,
					colspan:2,					
					width:200
				},
				{	xtype:'textareafield',
					fieldLabel: 'توضیحات',
					name: 'comments',
					colspan:2,
					width:450
				} , 
				{ 
				  xtype:'hidden' , 
				  name:'list_id',
				  itemId:'list_id'
				} , 
				{
				  xtype:'hidden' , 
				  name:'list_row_no' 
				}
				],
				buttons: [{
					text : "ذخیره",
					iconCls : "save",
					handler : function(){  
						PGListObject.MissionPanel.getForm().submit({ 						     
								clientValidation: true,
								url:'salary/org_process_docs/data/pay_get_lists.data.php?task=SaveMission',
								method : "POST",
								params : {								  
								    staffID : PGListObject.staff_id
								},
								success : function(form,action){
									if(action.result.success)
									{
									    alert("ذخیره سازی با موفقیت انجام شد .") ; 
										PGListObject.mgrid.getStore().load();
										PGListObject.MissionPanel.hide();
										PGListObject.mgrid.show() ;
									}
									else
									{										
										alert(action.result.data);
									}
								}
								,
								failure : function(form,action)
								{
									alert(action.result.data);
								}
						    });

					}
				},{
					text : "انصراف",
					iconCls : "undo",
					handler : function(){
						PGListObject.MissionPanel.hide();
						PGListObject.mgrid.show() ;
					}
				}]

		});
		
            
 }

var PGListObject = new PGList();

PGList.opRender = function(value,p,record)
{
	var st = "";
	
		st += "<div  title='حذف اطلاعات' class='remove' onclick='PGListObject.DelPGList();' " +
			  "style='float:left;background-repeat:no-repeat;background-position:center;" +
			  "cursor:pointer;width:50%;height:16'></div>";

        st += "<div  title='مشاهده' class='view' onclick='PGListObject.ShowPGDetail();' " +
              "style='float:left;background-repeat:no-repeat;background-position:center;" +
              "cursor:pointer;width:50%;height:16'></div>" ;
	return st;
}
PGList.prototype.editPGList = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/pay_get_lists.data.php?task=SavePGList',
		params:{
			record: Ext.encode(record.data)
		},
		method: 'POST',
		success: function(response,option){
			mask.hide();
			PGListObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

PGList.prototype.AddPGList = function()
{
      
    var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		list_id: null,
		list_date: GtoJ(new Date()).format("Y/m/d") ,		
		doc_state: "1" ,
		list_type: this.list_type , 
		cost_center_id : null 
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}
PGList.prototype.DelPGList = function()
{
    var record = this.grid.getSelectionModel().getLastSelected();

   if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

   mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال حذف...'});
   mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/pay_get_lists.data.php',
		params:{
			task: "deletePG",
			list_id : record.data.list_id ,
			list_type : record.data.list_type
		},
		method: 'POST',

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				PGListObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
    
}

PGList.prototype.DelPGListItems = function()
{
    
   if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

   mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال حذف...'});
   mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/pay_get_lists.data.php',
		params:{
			task: "deletePGItems",
			list_id : this.list_id 
		},
		method: 'POST',

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				PGListObject.mgrid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
    
}

PGList.prototype.ShowPGDetail = function(record)
{
    var record =  this.grid.getSelectionModel().getLastSelected();
    this.mainPanel.show();  
    
    this.get('list_id').innerHTML = record.data.list_id;
    this.list_id = record.data.list_id;
    this.cost_center_id = record.data.cost_center_id;  
    this.list_type = record.data.list_type ;    
    this.get('costTitle').innerHTML = record.data.cost_center_title;
    this.get('list_date').innerHTML =  MiladiToShamsi(record.data.list_date) ;
    this.get('status').innerHTML = ( record.data.doc_state == 1) ? 'پیش نویس' : 'تائید شده'  ;
    this.get('listType').innerHTML =record.data.list_title ;
   
    this.grid.hide();    
    this.LoadPersonInfo() ; 
 
    this.mgrid.getStore().proxy.extraParams["list_id"] = record.data.list_id ;
    this.mgrid.getStore().proxy.extraParams["list_type"] = this.list_type ;
     
    if(this.mgrid.rendered){	
       this.mgrid.getStore().load(); 
       this.mgrid.show() ;
    }
    else
        this.mgrid.render(this.get("MemberPGDIV"));

}
PGList.opRenderMission = function(value,p,record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='PGListObject.editList();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";

		st += "<div  title='حذف اطلاعات' class='remove' onclick='PGListObject.DelMember();' " +
			  "style='float:left;background-repeat:no-repeat;background-position:center;" +
			  "cursor:pointer;width:50%;height:16'></div>";
	return st;	
	
}
PGList.opRenderMembers = function(value,p,record)
{
	var st = "";
		st += "<div  title='حذف اطلاعات' class='remove' onclick='PGListObject.DelMember();' " +
			  "style='float:left;background-repeat:no-repeat;background-position:center;" +
			  "cursor:pointer;width:50%;height:16'></div>";
	return st;	
	
}
PGList.prototype.editList = function(record)
{
	this.MissionPanel.show() ; 
	var record = this.mgrid.getSelectionModel().getLastSelected();	
	this.staff_id = record.data.staff_id ; 
	
	record.data.doc_date = MiladiToShamsi(record.data.doc_date);
	record.data.from_date = MiladiToShamsi(record.data.from_date);
	record.data.to_date = MiladiToShamsi(record.data.to_date);
	
	this.MissionPanel.loadRecord(record);
	if(record.data.list_row_no > 0 ){	
		this.MissionPanel.getComponent("STID").setValue(record.data.staff_id +":"+ record.data.pfname +" " + record.data.plname) ; 			
		this.MissionPanel.getComponent("STID").disable(); 
	}
			
	this.mgrid.hide() ;
		
}
PGList.prototype.DelMember = function()
{

   var record = this.mgrid.getSelectionModel().getLastSelected();

   if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

   mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال حذف...'});
   mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/pay_get_lists.data.php',
		params:{
			task: "deleteMember",
			rowNo : record.data.list_row_no ,
			list_id :this.list_id ,
			list_type : this.list_type
		       },
		method: 'POST',

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				PGListObject.mgrid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});

}

PGList.prototype.AddMember = function()
{
    var modelClass = this.mgrid.getStore().model;
    var record = new modelClass({
				    list_id: this.list_id,
				    list_row_no :null ,  
				    staff_id: null ,
				    salary_item_type_id: null ,
				    initial_amount:null ,
				    approved_amount : null ,
				    value : null ,
				    comments : null 
				});
	this.mgrid.plugins[0].cancelEdit();
	this.mgrid.getStore().insert(0, record);
	this.mgrid.plugins[0].startEdit(0, 0);
}

PGList.prototype.AddMissionMember = function()
{
    var record =  this.grid.getSelectionModel().getLastSelected();
	PGListObject.MissionPanel.getForm().reset();	
    PGListObject.MissionPanel.getComponent('list_id').setValue(record.data.list_id); 
    this.MissionPanel.show(); 
    this.mgrid.hide() ;
}
PGList.prototype.editMember = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/pay_get_lists.data.php?task=SaveMember',
		params:{
			record: Ext.encode(record.data),
			list_id: this.list_id , 
			list_type:this.list_type
			},
		method: 'POST',
		success: function(response,option){
			mask.hide();
			PGListObject.mgrid.getStore().load();
		},
		failure: function(){}
	});
}

PGList.prototype.InsertAllPrn = function()
{	    
    			
	Ext.Ajax.request({
		url: this.address_prefix + '../data/pay_get_lists.data.php?task=AddAllPrn',
		params:{
			list_id: this.list_id  ,
			cost_center_id : this.cost_center_id , 
			list_type : this.list_type ,
			itemID : this.salary_item_type_id
		},
        success: function(response,option){
            
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert('ذخیره سازی با موفقیت انجام شد .');
				PGListObject.mgrid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},

		failure: function(){}
	}); 

}


</script>












