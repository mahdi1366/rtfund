<script type="text/javascript">
    /* -----------------------------
  //	Programmer	: s.taghizadeh
  //	Date		: 94.8
  ----------------------------- */
    // alert('2');
    MissionRequest.prototype = {
	
        address_prefix : "<?= $js_prefix_address ?>",
        formID:"<?= $formID ?>",
        get : function(elementID){
            return document.getElementById(elementID); //findChild(this.TabID, elementID);
        }
         
    };

       
    function MissionRequest()
    {
        this. MissionLocations = Ext.create('Ext.data.Store' , {
            fields : ['city_id','state_id','sname' , 'cname', ],
            proxy: {
                type: 'jsonp',
                url: '../data/BaseInfo.data.php?task=SelectMissionLocations',
                reader: {
                    root: 'rows',
                    totalProperty: 'totalCount'
                }
            }
        
        
        });
        
        
    
        this.subItemCombo = new Ext.form.ComboBox({
            store:this.MissionLocations , 
            emptyText:'جستجوی مکان...',
            typeAhead: false,
            listConfig :{
                loadingText: 'در حال جستجو...'
            },
            pageSize:10,
            width: 200,
            displayTpl: new Ext.XTemplate('<tpl for=".">{cname} {sname}</tpl>'),
            tpl: new Ext.XTemplate(
            '<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
            ,'<td height="23px">استان</td>'
            ,'<td>شهر</td>'
            ,'<tpl for=".">'
            ,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
            ,'<td style="border-left:0;border-right:0" class="search-item">{sname}</td>'
            ,'<td style="border-left:0;border-right:0" class="search-item">{cname}</td></tr>'
            ,'</tpl>'
            ,'</table>')

            ,listeners : {
                Select: function(combo, records){
                         
                    //this.setValue(records[0].data.city_id + " " + records[0].data.state_id);
                    MissionRequestObject.grid2.getStore().getAt(0).data.CityId = records[0].data.city_id;
                    MissionRequestObject.grid2.getStore().getAt(0).data.StateId = records[0].data.state_id;
                    this.collapse();
                }
            }
        });
        
        
        this.subItemComboDetailFrom = new Ext.form.ComboBox({
            store:this. MissionLocations , 
            emptyText:'جستجوی مکان...',
            typeAhead: false,
            listConfig :{
                loadingText: 'در حال جستجو...'
            },
            pageSize:10,
            width: 200,
            displayTpl: new Ext.XTemplate('<tpl for=".">{cname} {sname}</tpl>'),
            tpl: new Ext.XTemplate(
            '<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
            ,'<td height="23px">استان</td>'
            ,'<td>شهر</td>'
            ,'<tpl for=".">'
            ,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
            ,'<td style="border-left:0;border-right:0" class="search-item">{sname}</td>'
            ,'<td style="border-left:0;border-right:0" class="search-item">{cname}</td></tr>'
            ,'</tpl>'
            ,'</table>')

            ,listeners : {
                Select: function(combo, records){
                              
                          
                    //this.setValue(records[0].data.city_id + " " + records[0].data.state_id);
                  //  MissionRequestObject.grid3.getStore().getAt(0).data.FromCityId = records[0].data.city_id;
                  //  MissionRequestObject.grid3.getStore().getAt(0).data.FromStateId = records[0].data.state_id;
                    this.collapse();
                }
            }
        });
         
        this.subItemComboDetailTo = new Ext.form.ComboBox({
            store:this. MissionLocations , 
            emptyText:'جستجوی مکان...',
            typeAhead: false,
            listConfig :{
                loadingText: 'در حال جستجو...'
            },
            pageSize:10,
            width: 200,
            displayTpl: new Ext.XTemplate('<tpl for=".">{cname} ({sname})</tpl>'),
            tpl: new Ext.XTemplate(
            '<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
            ,'<td height="23px">استان</td>'
            ,'<td>شهر</td>'
            ,'<tpl for=".">'
            ,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
            ,'<td style="border-left:0;border-right:0" class="search-item">{sname}</td>'
            ,'<td style="border-left:0;border-right:0" class="search-item">{cname}</td></tr>'
            ,'</tpl>'
            ,'</table>')

            ,listeners : {
                Select: function(combo, records){
                    // alert(records[0].data.CityId);
                    //this.setValue(records[0].data.city_id + " " + records[0].data.state_id);
                  //  MissionRequestObject.grid3.getStore().getAt(0).data.ToCityId = records[0].data.city_id;
                  //  MissionRequestObject.grid3.getStore().getAt(0).data.ToStateId = records[0].data.state_id;
                    this.collapse();
                }
            }
        });
        
        
        this. MissionVehicles = Ext.create('Ext.data.Store' , {
            fields : ['type','name'],
            proxy: {
                type: 'jsonp',
                url: '../data/BaseInfo.data.php?task=SelectMissionVehicles',
                reader: {
                    root: 'rows',
                    totalProperty: 'totalCount'
                }                            
            }
        });
    
    
        this.ComboMissionVehicles = new Ext.form.ComboBox({
     
               
              
            fields: ['type', 'name'],
            store : this.MissionVehicles,                
            displayField: 'name',
            valueField: 'type',
            allowBlank:false 
            ,listeners : {
                Select: function(combo, records){
                  //  MissionRequestObject.grid3.getStore().getAt(0).data.VehicleId = records[0].data.type;
                          
                }
            }
        });   
    
 
         this.ComboMissionCosts = Ext.create('Ext.data.ArrayStore', {
            fields: ['type', 'name'],
            data : [
                ['1','با فوق العاده'],                  
                ['2','بدون فوق العاده'],                     
				
				
				
            ]
        });

    
        
        this.grid2 = <?= $grid2 ?>;
        this.gridCosts=<?= $gridCosts ?>;      
      //  this.grid3 = < ?= $grid3 ?>;
       
       
        this. MissTypes = Ext.create('Ext.data.ArrayStore', {
            fields: ['type', 'name'],
            data : [
                ['1','با فوق العاده'],
                ['2','بدون فوق العاده'],
				
				
				
            ]
        });
        this. MissionTypes = Ext.create('Ext.data.Store', {
            fields: ['type', 'name'],
            proxy: {
                type: 'jsonp',
                url: '../data/BaseInfo.data.php?task=SelectMissionTypes',
                reader: {
                    root: 'rows',
                    totalProperty: 'totalCount'
                }                            
            }
        });
        this. Dispatchers = Ext.create('Ext.data.ArrayStore', {
            fields: ['ouid', 'ptitle','PersonId'],
            proxy: {
                type: 'jsonp',
                url: '../data/BaseInfo.data.php?task=GetDispatchers',
                reader: {
                    root: 'rows',
                    totalProperty: 'totalCount'
                }                            
            }
        });
   

        this. MissionLocations = Ext.create('Ext.data.Store' , {
            fields : ['CityId','StateId','sname' , 'cname', ],
            proxy: {
                type: 'jsonp',
                url: '../data/BaseInfo.data.php?task=SelectMissionLocations',
                reader: {
                    root: 'rows',
                    totalProperty: 'totalCount'
                }
            }
        
        
        });
        this. PersonStore =  new Ext.data.Store({
            proxy: {type: 'jsonp',
                url: '../data/BaseInfo.data.php?task=SelectPersons',
                reader: {root: 'rows',totalProperty: 'totalCount'}
            },
            fields : ['PersonID','PFName','PLName'],
            pageSize: 10
        });
        
        
        
     
        
      
        
        
      
        
        
        
        
        
        this.formPanel = new Ext.form.Panel({  
            renderTo: this.get('FormDIV'),
            width : 820,
           
            title :  "فرم درخواست ماموریت اداری",
            //title : "فرم درخواست ماموریت اداری",
            frame : true,
            bodyPadding: '5 5 0',
            fieldDefaults: {labelWidth: 150},        
            layout: {
                type : "table",
                columns :2,
                width:800
            },
            items :  [           
                
                 {
					xtype : "container",
                                        colspan : 2, 
                                        height:30,
			layout : "hbox",
			items : [ {
                    xtype : "combobox",
                    
                    name : "DispatcherId",
                    itemId : "DispatcherId",
                    fieldLabel : "اعزام کننده",
                    fields: ['type', 'name'],
                    store :this.Dispatchers,                
                    displayField: 'ptitle',
                    valueField: 'PersonId',
                    width:350
                              
                },{
                     xtype: 'displayfield',
                     value:'**در صورت اعزام از سمت واحد محل خدمت ، نیاز به پرکردن این فیلد نمی باشد**'
                   
                  
                    
                },]},
                /*{
                    xtype: "combo",
                    itemId: "PersonID",
                    name: "PersonID",
                    store: this.PersonStore,
                    //displayField: 'fullname',
                    valueField: 'PersonID',
                    hiddenName: 'PersonID',
                    pageSize: 10,
                    width: 530,
                    typeAhead: false,
                    fieldLabel:  "انتخاب فرد",
                    allowBlank : false,	
                    //beforeLabelTextTpl: required,
                    labelWidth:150,
                    colspan: 2,
                    // emptyText: 'Ø¬Ø³ØªØ¬ÙˆÙŠ Ù�Ø±Ø¯ ...',
                    listConfig: {
                        loadingText: 'در حال جستجو...',
                        emptyText: 'فاقد اطلاعات',
                        itemCls : "search-item"
                    },
                    displayTpl: new Ext.XTemplate('<tpl for=".">{PLName} ({PersonID})</tpl>'),
                    tpl: new Ext.XTemplate(
                    '<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
                    , '<td height="23px">کد پرسنلی</td>'
                    , '<td height="23px">نام</td>'
                    , '<td>نام خانوادگی</td>'
                    , '</tr>',
                    '<tpl for=".">',
                    '<tr class="search-item">'
                    ,'<td> {PersonID}</td>'
                    , '<td>{PFName}</td>'
                    , '<td>{PLName}</td>'
                    , '</tr>'
                    , '</tpl>'
                    , '</table>'),
                    listeners: {
                        select: function (combo, records) {
                            Ext.getCmp("PersonID").setValue(records[0].data.PersonID);

                        }
                    }



                },*/
               {
                    xtype : "combobox",
                    colspan : 2,
                    name : "type",
                    itemId : "type",
                    fieldLabel : "نوع اعزام",
                    fields: ['type', 'name'],
                    store : this.MissionTypes,                
                    displayField: 'name',
                    valueField: 'type',               
                    allowBlank:false                
                },/* {
                    xtype : "combobox",
                    colspan : 2,
                    name : "MissType",
                    itemId : "MissType",
                    fieldLabel :"نوع ماموریت",
                    fields: ['type', 'name'],
                    store : this.MissTypes,                
                    displayField: 'name',
                    valueField: 'type',               
                    allowBlank:false ,  width :350            
                },*/
              
            
                {
                    colspan : 1,
                    xtype: "shdatefield",
                    name : "FromDate_Date",
                    itemId : "FromDate",
                    fieldLabel : "از تاریخ",
                    allowBlank:false                
                },
                {
                    colspan : 1,
                    xtype: 'timefield',
                    name: 'FromDate_Time',
                    fieldLabel: 'ساعت',
                     minValue: '0:30',
                    maxValue: '23:30',
                    increment: 30,
                    allowBlank:false ,
                      format     : 'H:i'
        
                },
                {
                    colspan : 1,
                    xtype: "shdatefield",
                    name : "ToDate_Date",
                    itemId : "ToDate",
                    fieldLabel : " لغایت",
                    allowBlank:false                
                },
                {
                    colspan : 1,
                    xtype: 'timefield',
                    name: 'ToDate_Time',
                    itemId : "ToTime",
                    fieldLabel: 'ساعت',
                    minValue: '0:30',
                    maxValue: '23:30',
                    increment: 30,
                    allowBlank:false  ,
                      format     : 'H:i'
        
                },
                {
                    xtype : "textarea",
                    name : "subject",
                    fieldLabel : "موضوع ماموریت",
                    width : 680,
                    rows : 1,
                    colspan : 2
                },
                {
                    xtype : "textarea",
                    name : "stuff",
                    fieldLabel : " وسایل مورد نیاز در ماموریت",
                    width : 680,
                    rows : 1,
                    colspan : 2,
                    allowBlank:false                
                },
               
                 {
                                xtype : "fieldset",
                             colspan:2,
                          // closable:true,
                                     //collapsible: true,
		//collapsed : true,
                                width:780,
                                style: "margin:0 4 5 10",
                                title : "شهر های ماموریت",
                                itemId : "f3",
                                defaults: {
							anchor: '100%'
						},
				layout:{ type:"table",
						 columns:2,
						  style : "width:100%"
					   }, 


                        items :[this.grid2]},
          
          
                //    ,
                {
                    xtype : 'hiddenfield',
                    name : 'RequestID',
                    itemId : 'RequestID',
                    value : ''
                }  ,
             
              //  this.grid3,
              
               
         
            
            ],
            buttons : [{
                    text : "ذخیره",
                    iconCls : "save",
                    handler : function(){
                        var t1=MissionRequestObject.grid2.getStore().getCount();
                        if (t1<1)
                        { alert("شهر ماموریت باید اضافه شود"); return;
                        }
                        var t2=MissionRequestObject.grid2.getStore().getCount();
                        if(t2<1)
                            {
                        alert("وسیله نقلیه باید اضافه شود "); return;
                            }
                        MissionRequestObject.formPanel.getForm().submit({
                            clientValidation: true,
                            url: '/HumanResources/missions/data/MissionRequest.data.php?task=SaveMissionPerson',
                            method : "POST",
                            /* params:{
                                RequestID:this.formID
                              
                            },*/
                            success : function(form,action){
                                if(action.result.success)
                                {
                                    MissionRequestObject.grid.getStore().load();
                                }
                                else
                                {
                                    alert("عملیات مورد نظر با شکست مواجه شد.");
                                }
                                MissionRequestObject.formPanel.hide();
                                MissionRequestObject.grid.show();
                            },
                             failure : function(form,action){
                            alert(action.result.data);
                        }
                        });
                    }
                },{
                    text : "ارسال درخواست",
                    iconCls : "send",
                    handler : function(){
                     var t1=MissionRequestObject.grid2.getStore().getCount();
                        if (t1<1)
                        { alert("شهر ماموریت باید اضافه شود"); return;
                        }
                        var t2=MissionRequestObject.grid2.getStore().getCount();
                        if(t2<1)
                            {
                        alert("وسیله نقلیه باید اضافه شود "); return;
                            }
                        MissionRequestObject.formPanel.getForm().submit({
                            clientValidation: true,
                            url: '/HumanResources/missions/data/MissionRequest.data.php?task=SaveMissionPerson&send=1',
                            method : "POST",
                            /* params:{
                                RequestID:this.formID
                              
                            },*/
                            success : function(form,action){
                                if(action.result.success)
                                {
                                    MissionRequestObject.grid.getStore().load();
                                }
                                else
                                {
                                    alert("عملیات مورد نظر با شکست مواجه شد.");
                                }
                                MissionRequestObject.formPanel.hide();
                                MissionRequestObject.grid.show();
                            },
                             failure : function(form,action){
                            alert(action.result.data);
                        }
                        });
                    }
                }
                ,{
                    text : "انصراف",
                    iconCls : "undo",
                    handler : function(){
                        MissionRequestObject.formPanel.hide();
                        MissionRequestObject.grid.show();
                    }
                }]
        }); 
        this.formPanel.hide();
    }
    MissionRequest.opRenderOriginalGrid = function(value, p, record)
    {
      
        return  "<div title='عملیات' class='setting' onclick='MissionRequestObject.OperationMenu(event);' " +
            "style='background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;height:16'></div>";
        
       
    }
    MissionRequest.prototype.OperationMenu = function(e)
    {
       
        // var record = grid.getSelectionModel().getLastSelected();
        var record = this.grid.getSelectionModel().getLastSelected();
        var op_menu = new Ext.menu.Menu();  
        
           
        if (record.data.ControllerPerson == null && record.data.status == '<?= RAW ?>')
        {   
            
         
            
            
            op_menu.add({
                text: ' برگشت درخواست',
                iconCls: 'undo',
                handler : function(){MissionRequestObject.ReturnRequest();}
            });
           /* op_menu.add({
                    text: 'مشاهده سابقه ',
                    iconCls: 'history',
                     handler : function(){MissionRequestObject.History();}
                });*/
        }   
        switch (record.data.status)
        {
            case '<?= DRAFT ?>' ://"DRAFT":        
                op_menu.add({
                    text: 'ویرایش',
                    iconCls: 'edit',
                    handler : function(){MissionRequestObject.EditRequest();}
                   
                });            
                op_menu.add({
                    text: ' ارسال ',
                    iconCls: 'send',
                    handler : function(){MissionRequestObject.SendRequest();}
                   
                });            
                op_menu.add({
                    text: ' حذف ',
                    iconCls: 'remove',
                    handler : function(){MissionRequestObject.DeleteRequest();}
                    
                });            
                break;
            case '<?= REJECTED ?>' ://"DRAFT":        
                op_menu.add({
                    text: 'ویرایش',
                    iconCls: 'edit',
                    handler : function(){MissionRequestObject.EditRequest();}
                   
                }); 
                op_menu.add({
                    text: 'مشاهده سابقه ',
                    iconCls: 'history',
                     handler : function(){MissionRequestObject.History();}
                });
                break;
         // case '< ?= ACCEPTED_ ?>' :  case '< ?= REPORTED ?>': //"ACCEPTED":
            case '<?= Accept_kargozini ?>' :  case '<?= REPORTED ?>': case '<?= Reject_Report ?>': //"ACCEPTED and Accept-kargozini"      
                op_menu.add({
                    text: ' ارسال گزارش',
                    iconCls: '02',
                    handler : function(){MissionRequestObject.SendReport();}
                });
                op_menu.add({
                    text: ' چاپ درخواست',
                    iconCls: 'print',
                    handler : function(){MissionRequestObject.PrintRequest();}
                });
                op_menu.add({
                    text: 'مشاهده سابقه ',
                    iconCls: 'history',
                     handler : function(){MissionRequestObject.History();}
                });
                break;  
             case '<?= Reject_Hoghoogh ?>'   :
                     op_menu.add({
                    text: 'مشاهده سابقه ',
                    iconCls: 'history',
                     handler : function(){MissionRequestObject.History();}
                });
                 break; 
                //"REPORTED":                
            case '<?= FINALIZED ?>': //"FINALIZED":
            case '<?= ADMITTED ?>'://"ADMITTED":
            case '<?= PAYED ?>' ://"PAYED":
               op_menu.add({
                    text: 'مشاهده گزارش ',
                    iconCls: 'view',
                    handler : function(){MissionRequestObject.ShowReport();}
                });
                op_menu.add({
                    text: 'مشاهده سابقه ',
                    iconCls: 'history',
                     handler : function(){MissionRequestObject.History();}
                });
                op_menu.add({
                    text: ' چاپ درخواست',
                    iconCls: 'print',
                     handler : function(){MissionRequestObject.PrintRequest();}
                 
                });
                break;
           
              case '<?= RAW ?>' : //"RAW":
                op_menu.add({
                    text: 'مشاهده سابقه ',
                    iconCls: 'history',
                    handler : function(){MissionRequestObject.History();}
                   
                });    
                         
                break;
                  case '<?= ACCEPTED_ ?>' : //"RAW":
                op_menu.add({
                    text: 'مشاهده سابقه ',
                    iconCls: 'history',
                    handler : function(){MissionRequestObject.History();}
                   
                });    
                         
                break;
                
           }
            /* op_menu.add({
                    text: ' چاپ درخواست',
                    iconCls: 'print',
                    handler : PrintRequest
                });
                break;  
                }  
         /*   case '<?= REJECTED ?>': //"REJECTED":
                op_menu.add({
                    text: 'مشاهده سابقه ',
                    iconCls: 'history',
                    handler : History
                });                
                break;
                
            case '<?= REPORTED ?>': //"REPORTED":                
            case '<?= FINALIZED ?>': //"FINALIZED":
            case '<?= ADMITTED ?>'://"ADMITTED":
            case '<?= PAYED ?>' ://"PAYED":
                op_menu.add({
                    text: 'مشاهده گزارش ',
                    iconCls: 'view',
                    handler : function(){ return ShowReport(grid.getSelectionModel().getLastSelected().data.RequestID)}
                });
                op_menu.add({
                    text: 'مشاهده سابقه ',
                    iconCls: 'history',
                    handler : History
                });
                op_menu.add({
                    text: ' چاپ درخواست',
                    iconCls: 'print',
                    handler : PrintRequest
                });
                break;
        }*/
            op_menu.showAt(e.pageX-120, e.pageY);      
        }

        MissionRequest.prototype.ReturnRequest=function(){    
            if(!confirm("آیا مایل به بازگشت درخواست هستید؟"))
                return;
            Ext.Ajax.request({
                url : "../data/MissionRequest.data.php",
                method : "POST",
                params : {
                    task : "ReturnRequest",
                    RequestID : this.grid.getSelectionModel().getLastSelected().data.RequestID                	  		
                },
                success: function(response,option){
                    //mask.hide();
                    var st = Ext.decode(response.responseText);
                    if(st.success)
                    {
                        alert("برگشت با موفقیت انجام شد");
                        MissionRequestObject.grid.getStore().load();
                    }
                    else
                    {
                        alert(st.data);
                    }
                },
                failure: function(){
                    alert ("خطا در اجرای عملیات");
                }		
            });
       
       
       
        }
        MissionRequest.prototype.DeleteRequest=function(){
     
            if(!confirm("آیا مایل به حذف درخواست هستید؟"))
                return;
          
            Ext.Ajax.request({
                url : "../data/MissionRequest.data.php",
                method : "POST",
                params : {
                    task : "DeleteRequest",
                    RequestID : this.grid.getSelectionModel().getLastSelected().data.RequestID                	  		
                },
                success : function(response){
                       
                    alert("حذف با موفقیت انجام شد")                
                    MissionRequestObject.grid.getStore().load();               
                },            
                failure : function(response){
                    alert('خطا در اجرای عملیات');  
                }
            });
            
      
        }
        MissionRequest.prototype.EditRequest=function(){
            // MissionTypes.load();
            // MissionVehicles.load();        
            
            // this.formPanel.down("[itemId=CancelBtn]").show();
          
            this.formPanel.getForm().reset();
            this.formPanel.show();
            var record = this.grid.getSelectionModel().getLastSelected();
            this.formPanel.loadRecord(record);
            this.formPanel.down('[itemId=type]').getStore().load();
            this.formPanel.down('[itemId=DispatcherId]').getStore().load();
            this.grid2.getStore().proxy.extraParams['RquestID'] =this.formPanel.down('[itemId=RequestID]').value;
            this.grid2.getStore().load();
          /*  this.grid3.getStore().proxy.extraParams['RquestID'] =this.formPanel.down('[itemId=RequestID]').value;
            this.grid3.getStore().load(); */
            /* this.formPanel.down('[itemId=cmp_ReceiptDate]').setValue(MiladiToShamsi(record.data.ReceiptDate));
       this.formPanel.down('[itemId=PersonID]').getStore().proxy.extraParams['query'] = record.data.PersonID;
        this.formPanel.down('[itemId=PersonID]').getStore().load();
      this.formPanel.down('[itemId=PersonID]').getStore().proxy.extraParams['query'] = '';
        this.formPanel.getComponent("PersonID").setValue(record.data.PersonID);*/
            // var record = this.grid.getSelectionModel().getLastSelected();        
            var fd = record.data.FromDate;
            record.data.FromDate_Date = fd.substr(0,10);
      
            t = fd.substr(11,5);
            if (t.substr(0,2) <= 12)
            {
                var ampm = ' AM';        
            }else
            {
                var ampm = ' PM';
                t = t.substr(0,2)-12 + t.substr(2,3) 
            }  
            if(t.substr(0,1) == 0 )
                t = t.substr(1,4)
            record.data.FromDate_Time = t + ampm;

        
            var fd = record.data.ToDate;
            record.data.ToDate_Date = fd.substr(0,10);
        
            t = fd.substr(11,5);
            if (t.substr(0,2) <= 12)
            {
                var ampm = ' AM';        
            }else
            {
                var ampm = ' PM';
                t = t.substr(0,2)-12 + t.substr(2,3) 
            }        
            if(t.substr(0,2) == '00' )
                t = '12' + t.substr(2,13)
            if(t.substr(0,1) == 0 )
                t = t.substr(1,4)
            record.data.ToDate_Time = t + ampm;
            this.formPanel.loadRecord(record); 
            /* ReqPanel.down('[itemId=MissionLocationID]').getStore().proxy.extraParams['city_id'] = record.data.city_id;
            ReqPanel.down('[itemId=MissionLocationID]').getStore().proxy.extraParams['state_id'] = record.data.state_id;
            ReqPanel.down('[itemId=MissionLocationID]').getStore().load();
            ReqPanel.down('[itemId=MissionLocationID]').getStore().proxy.extraParams['city_id'] = '';
            ReqPanel.down('[itemId=MissionLocationID]').getStore().proxy.extraParams['state_id'] = '';  
            var cid = record.data.city_id;                         
            ReqPanel.getForm().reset();
            ReqPanel.getForm().loadRecord(record);
            ReqPanel.down('[itemId=MissionLocationID]').setValue(cid.toString());  */  
          
        }
        MissionRequest.prototype.SendRequest=function(){
            if(!confirm("آیا از ارسال درخواست مطمئن هستید؟"))
                return;
          
            Ext.Ajax.request({
                url: '/HumanResources/missions/data/MissionRequest.data.php?task=SendMissionByGrid',
                method : "POST",
                params : {
                   
                    RequestID : this.grid.getSelectionModel().getLastSelected().data.RequestID  ,
                    FromDate1 : this.grid.getSelectionModel().getLastSelected().data.FromDate,
                    ToDate1 : this.grid.getSelectionModel().getLastSelected().data.ToDate                    
                },
                success : function(response){
                       
                    alert("درخواست با موفقیت ارسال شد")                
                    MissionRequestObject.grid.getStore().load();               
                },            
                failure : function(response){
                    alert('خطا در اجرای عملیات');  
                }
            });
        }
        MissionRequest.prototype.AddDestinationMission = function()
        {    
            var modelClass = MissionRequestObject.grid2.getStore().model;
            var record = new modelClass({
                InfoDestinationID: "",
                CityId:null,
                StateId:null,
                Address:'',
                TimeStay:null               
            });         
            MissionRequestObject.newMode  = 1 ;
            MissionRequestObject.grid2.plugins[0].cancelEdit();
            MissionRequestObject.grid2.getStore().insert(0, record);
            MissionRequestObject.grid2.plugins[0].startEdit(0, 0);    
        }
        MissionRequest.prototype.SaveDestination = function(store,record){
            RquestId =MissionRequestObject.formPanel.getComponent("RequestID").value;
            //  RquestId=this.formID;
       
            Ext.Ajax.request({
                url : this.address_prefix + "../data/MissionRequest.data.php?task=AddDestination&ReqId="+RquestId,
          
                method : "POST",
                params : {
                    //task : "AddDestination",
                    //RquestId : this.get("ReceiptId").value,
                    // RquestId :RquestId,
                    record : Ext.encode(record.data)
                },
                success : function(response){   
                    var sd = Ext.decode(response.responseText);                
                    if(sd.success)
                    {
                        //FactorObject.get('ReceiptId').value = sd.data;
                        // FactorObject.ExtraItemsGrid.getStore().proxy.extraParams['ReceiptID'] = FactorObject.get('ReceiptId').value;
                        MissionRequestObject.grid2.getStore().load(); 
                    }
                    else
                    {
                        if(sd.data != "")
                            alert(sd.data);
                        else
                            alert("عملیات مورد نظر با شکست مواجه شد");
                    }
                }
            });
        }
        MissionRequest.prototype.AddCostMission = function()
        {
     
 var record2 = this.grid.getSelectionModel().getLastSelected();
   //alert(record2.data.status);           
         //   if  (record2.data.status==3 )
     //{
            var modelClass = MissionRequestObject.gridCosts.getStore().model;
            var record = new modelClass({
                CostID: null,
                RequestId:null,
                CostType:null,
                fee:null,
                status:null
                
            });       
            MissionRequestObject.newMode  = 1 ;
            MissionRequestObject.gridCosts.plugins[0].cancelEdit();
            MissionRequestObject.gridCosts.getStore().insert(0, record);
            MissionRequestObject.gridCosts.plugins[0].startEdit(0, 0);   
     //}
        }
        MissionRequest.prototype.SaveCostMiss = function(store,record){
            
        //alert(record.data.fee);
var record2 = this.grid.getSelectionModel().getLastSelected();
 RquestId=record2.data.RequestID;
          //  RquestId =MissionRequestObject.formPanel.getComponent("RequestID").value;
            Ext.Ajax.request({
                url : this.address_prefix + "../data/MissionRequest.data.php?task=AddCosts&ReqId="+RquestId,
          
                method : "POST",
                params : {
               
                    record : Ext.encode(record.data)
               
                },
                success : function(response){   
                    var sd = Ext.decode(response.responseText);                
                    if(sd.success)
                    {
                   
                        MissionRequestObject.gridCosts.getStore().load(); 
                    }
                    else
                    {
                        if(sd.data != "")
                            alert(sd.data);
                        else
                            alert("عملیات مورد نظر با شکست مواجه شد");
                    }
                }
            });
        }
        MissionRequest.prototype.AddRequest = function()
        {
      
            Ext.Ajax.request({
                url:  '/HumanResources/missions/data/MissionRequest.data.php?task=CreateRequestID',
          
                method: 'POST',
           
                success: function(response){
                    var ret = Ext.decode(response.responseText);
                   // alert(ret.data);
                    //  MissionRequestObject.formID=response.responseText;
                    MissionRequestObject.formID=ret.data;	
                    MissionRequestObject.grid2.getStore().proxy.extraParams['RquestID'] =MissionRequestObject.formID,
            MissionRequestObject.grid2.getStore().load();
           // MissionRequestObject.grid3.getStore().proxy.extraParams['RquestID'] =MissionRequestObject.formID,
           // MissionRequestObject.grid3.getStore().load(); 
            MissionRequestObject.formPanel.getForm().reset();
            MissionRequestObject.formPanel.show();
            MissionRequestObject.formPanel.center();
            MissionRequestObject.formPanel.show();
            MissionRequestObject.grid.hide();
            MissionRequestObject.formPanel.down('[itemId=RequestID]').setValue(MissionRequestObject.formID);
                },
                failure: function(){}
            });
           // alert(this.formID);
            // alert(ret);
   
           
        }
        MissionRequest.prototype.opRender = function(value, p, record)
        {
    
            return   "<div  title='حذف اطلاعات' class='remove' onclick='MissionRequestObject.deleteDes();' " +
                "style='float:left;background-repeat:no-repeat;background-position:center;" +
                "cursor:pointer;width:50%;height:16'></div>" ;
        }
        MissionRequest.prototype.opRender2 = function(value, p, record)
        {
              var record2 = this.grid.getSelectionModel().getLastSelected();
            
            if  (record2.data.status==5 )
    {
       
            return   "<div  title='حذف اطلاعات' class='remove' onclick='MissionRequestObject.DeleteOtherCosts();' " +
                "style='float:left;background-repeat:no-repeat;background-position:center;" +
                "cursor:pointer;width:50%;height:16'></div>" ;
        }
        }
        MissionRequest.prototype.deleteDes = function()
        {
            if(!confirm("آیا از حذف اطمینان دارید؟"))
                return;
	
            var record = this.grid2.getSelectionModel().getLastSelected();
	
            //mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
            //mask.show();


            Ext.Ajax.request({
               url: '/HumanResources/missions/data/MissionRequest.data.php?task=removeDestination',
                params:{
                    Did: record.data.InfoDestinationID  
                       
                },
                method: 'POST',
                success: function(response,option){
                    //mask.hide();
                    var st = Ext.decode(response.responseText);
                    if(st.success)
                    {
                        alert("حذف با موفقیت انجام شد.");
                        MissionRequestObject.grid2.getStore().load();
                    }
                    else
                    {
                        alert(st.data);
                    }
                },
                failure: function(){}		
            });
        }
        MissionRequest.prototype.DeleteOtherCosts = function(v,p,r)
        {
            if(!confirm("آیا از حذف اطمینان دارید؟"))
                return;
	
            var record = this.gridCosts.getSelectionModel().getLastSelected();
	
            //mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
            //mask.show();


            Ext.Ajax.request({
                url: '/HumanResources/missions/data/MissionRequest.data.php?task=removeOtherCosts',
                params:{
                    CostID: record.data.CostID  
                       
                },
                method: 'POST',
                success: function(response,option){
                    //mask.hide();
                    var st = Ext.decode(response.responseText);
                    if(st.success)
                    {
                        alert("حذف با موفقیت انجام شد.");
                        MissionRequestObject.gridCosts.getStore().load();
                    }
                    else
                    {
                        alert(st.data);
                    }
                },
                failure: function(){}		
            });
        }
          MissionRequest.prototype.SendReport = function()
        {
           // this.grid.hide();
            
           
          
             var record = this.grid.getSelectionModel().getLastSelected();
               MissionRequestObject.gridCosts.getStore().proxy.extraParams['RquestID'] =record.data.RequestID;
               
            MissionRequestObject.gridCosts.getStore().load();
          // MissionRequestObject.ReportForm.loadRecord(record); 
          // alert(record.data.NumberDayMiss);
           var fd = record.data.FromDate;
             var fd2 = record.data.ToDate;
              var rd = record.data.FromDateReport;
            var rd2 = record.data.ToDateReport;
             var T1="";
             var T2="";
             var r1="";
             var r2="";
             t = fd.substr(11,5);
            if (t.substr(0,2) <= 12)
            {
                var ampm = ' AM';        
            }else
            {
                var ampm = ' PM';
                t = t.substr(0,2)-12 + t.substr(2,3) 
            }  
            if(t.substr(0,1) == 0 )
                t = t.substr(1,4)
            T1 = t + ampm;
            
                 t = fd2.substr(11,5);
            if (t.substr(0,2) <= 12)
            {
                var ampm = ' AM';        
            }else
            {
                var ampm = ' PM';
                t = t.substr(0,2)-12 + t.substr(2,3) 
            }  
            if(t.substr(0,1) == 0 )
                t = t.substr(1,4)
            T2 = t + ampm;
         
         
                  
            //-------------------------------تاریخ بعد از ویرایش
                   t = rd.substr(11,5);
            if (t.substr(0,2) <= 12)
            {
                var ampm = ' AM';        
            }else
            {
                var ampm = ' PM';
                t = t.substr(0,2)-12 + t.substr(2,3) 
            }  
            if(t.substr(0,1) == 0 )
                t = t.substr(1,4)
            r1 = t + ampm;
            
                 t = rd2.substr(11,5);
            if (t.substr(0,2) <= 12)
            {
                var ampm = ' AM';        
            }else
            {
                var ampm = ' PM';
                t = t.substr(0,2)-12 + t.substr(2,3) 
            }  
            if(t.substr(0,1) == 0 )
                t = t.substr(1,4)
            r2 = t + ampm;
            //--------------------------------------
            
            if(!this.ReportForm)
            {	
                 
                this.ReportForm = new Ext.window.Window({
                    closeAction : 'hide',
                    id:'ReportWindow',
                    bodyStyle: 'background:white; padding:10px;',
                    title : 'ارسال گزارش ماموریت',
                  applyTo : this.get('win'),
                    width :500,
 
                    bodyPadding: '5 5 0',
                    defaults: {
                        anchor: '100%'
                    },
                    layout :{
                        type : "table",
                        anchor:"100%",
                        columns :2
                       },
                    items : [
                        {
                  
                    xtype: "displayfield",
                    name : "FDate",
                    itemId : "FDate",
                    fieldLabel : "از تاریخ",
                     labelWidth:60,
                   // value:record.data.FromDate  ,
                    colspan:2
                },
                {
                  
                    xtype: 'displayfield',
                    name: 'TDate',
                     itemId : "TDate",
                    fieldLabel: 'تا تاریخ',
                     labelWidth:60,
                     //   value:record.data.ToDate,            
         colspan:2
                },
                  /*{
                  
                    xtype: 'displayfield',
                    name: 'NumberMiss',
                     itemId : "NumberMiss",
                    fieldLabel: 'مدت ماموریت',
                     labelWidth:160,
                     readOnly:true,
                      //  value:record.data.NumberMiss,            
         colspan:2
                },*/
               
                {
                                xtype : "fieldset",
                             colspan:2,
                         //  collapsible: true,
		//collapsed : true,
                                width:450,
                                style: "margin:0 4 5 10",
                                title : "ویرایش تاریخ ماموریت",
                                itemId : "f2",
                                defaults: {
							anchor: '100%'
						},
				layout:{ type:"table",
						 columns:2,
						  style : "width:100%"
					   }, 


                        items :[ {
                    colspan : 1,
                    xtype: "shdatefield",
                    name : "FromDateReport2",
                    itemId : "FromDateReport",
                    fieldLabel : "از تاریخ",
                    labelWidth:40,
                    allowBlank:false ,   listeners : {
                                    blur: function(cmp, event){
                                        var Fdate=MissionRequestObject.ReportForm.down('[itemId=FromDateReport]').getValue();  
                                        var Tdate=MissionRequestObject.ReportForm.down('[itemId=ToDateReport]').getValue(); 
               
                                        var FromToDays = parseInt(Fdate.getFullYear())*1+parseInt(Fdate.getMonth())*31+parseInt(Fdate.getDate());
                                        var   ToToDays = parseInt(Tdate.getFullYear())*1+parseInt(Tdate.getMonth())*31+parseInt(Tdate.getDate());
          
                                        MissionRequestObject.ReportForm.down('[itemId=NumberDayMiss]').setValue((ToToDays-FromToDays)+0.5); 
                                    }
                                } 
                   // value : rd.substr(0,10)
                },
                {
                    colspan : 1,
                    xtype: 'timefield',
                    name: 'FromDateR_Time',
                      itemId : "FromTimeR",
                    fieldLabel: 'ساعت',
                    minValue: '0:30 AM',
                    maxValue: '11:30 PM',
                    increment: 30,
                    labelWidth:40,
                    allowBlank:false 
                 // value : r1
        
                },
                {
                    colspan : 1,
                    xtype: "shdatefield",
                    name : "ToDateReport",
                    itemId : "ToDateReport",
                    fieldLabel : " لغایت",
                    allowBlank:false  ,
                     labelWidth:40,   listeners : {
                                    blur: function(cmp, event){
                                        var Fdate=MissionRequestObject.ReportForm.down('[itemId=FromDateReport]').getValue();  
                                        var Tdate=MissionRequestObject.ReportForm.down('[itemId=ToDateReport]').getValue(); 
               
                                        var FromToDays = parseInt(Fdate.getFullYear())*1+parseInt(Fdate.getMonth())*31+parseInt(Fdate.getDate());
                                        var   ToToDays = parseInt(Tdate.getFullYear())*1+parseInt(Tdate.getMonth())*31+parseInt(Tdate.getDate());
          
                                        MissionRequestObject.ReportForm.down('[itemId=NumberDayMiss]').setValue((ToToDays-FromToDays)+0.5); 
                                    }
                                } 
                    //  value : rd2.substr(0,10)
                },
                {
                    colspan : 1,
                    xtype: 'timefield',
                    name: 'ToDateR_Time',
                    itemId : "ToTimeR",
                    fieldLabel: 'ساعت',
                    minValue: '0:30 AM',
                    maxValue: '11:30 PM',
                    increment: 30,
                     labelWidth:40,
                    allowBlank:false 
                  // value:r2
        
                }, {
                  
                    xtype: 'displayfield',
                    name: 'NumberDayMiss',
                     itemId : "NumberDayMiss",
                    fieldLabel: 'مدت ماموریت',
                     labelWidth:160,
                     //readOnly:true,
                      //  value:record.data.NumberDayMiss,            
         colspan:2
                }]
                              
                                         
		                },
                                 {
                                xtype : "fieldset",
                             colspan:2,
                           closable:true,
                                     collapsible: true,
		collapsed : true,
                                width:450,
                                style: "margin:0 4 5 10",
                                title : "سایر هزینه ها",
                                itemId : "f3",
                                defaults: {
							anchor: '100%'
						},
				layout:{ type:"table",
						 columns:2,
						  style : "width:100%"
					   }, 


                        items :[ this.gridCosts]},
               {
                            xtype : "textarea",						
                            fieldLabel: 'خلاصه گزارش',
                            name: 'Report',
                            itemId : 'cmp_Report',
                            labelWidth:90,
                            colspan : 1,
			width :450
                          // value:record.data.MissionReport
									
                        },],
                    buttons :[
                        {
                            text : 'ارسال',
                            iconCls : 'send',
                            handler : function(){
                                MissionRequestObject.SendRpt();
                                this.up('window').hide();
                            }
                        },{
                            text : 'بازگشت',
                            iconCls : 'undo',
                            handler : function(){
                                this.up('window').hide();
						
                            }
                        }]			
                });
            }
		
	

            this.ReportForm.show();
            var record = this.grid.getSelectionModel().getLastSelected();
           
         
            var fd = record.data.FromDate;
            record.data.FromDate_Date = fd.substr(0,10);
      
            t = fd.substr(11,5);
            if (t.substr(0,2) <= 12)
            {
                var ampm = ' AM';        
            }else
            {
                var ampm = ' PM';
                t = t.substr(0,2)-12 + t.substr(2,3) 
            }  
            if(t.substr(0,1) == 0 )
                t = t.substr(1,4)
            record.data.FromDate_Time = t + ampm;

        
            var fd = record.data.ToDate;
            record.data.ToDate_Date = fd.substr(0,10);
        
            t = fd.substr(11,5);
            if (t.substr(0,2) <= 12)
            {
                var ampm = ' AM';        
            }else
            {
                var ampm = ' PM';
                t = t.substr(0,2)-12 + t.substr(2,3) 
            }        
            if(t.substr(0,2) == '00' )
                t = '12' + t.substr(2,13)
            if(t.substr(0,1) == 0 )
                t = t.substr(1,4)
            record.data.ToDate_Time = t + ampm;
          
              MissionRequestObject.ReportForm.down('[itemId=NumberDayMiss]').setValue(record.data.NumberMissReport);
            MissionRequestObject.ReportForm.down('[itemId=FDate]').setValue(record.data.FromDate);
          MissionRequestObject.ReportForm.down('[itemId=TDate]').setValue(record.data.ToDate);
       //    MissionRequestObject.ReportForm.down('[itemId=NumberMiss]').setValue(record.data.NumberMiss);
          MissionRequestObject.ReportForm.down('[itemId=FromDateReport]').setValue(rd.substr(0,10));  
          MissionRequestObject.ReportForm.down('[itemId=FromTimeR]').setValue(r1);  
          MissionRequestObject.ReportForm.down('[itemId=ToDateReport]').setValue(rd2.substr(0,10));  
          MissionRequestObject.ReportForm.down('[itemId=ToTimeR]').setValue(r2);  
          MissionRequestObject.ReportForm.down('[itemId=cmp_Report]').setValue(record.data.MissionReport);  
            
        
        }
        MissionRequest.prototype.SendRpt = function()
{	
	var record = this.grid.getSelectionModel().getLastSelected();	
        var rpt=this.ReportForm.getComponent('cmp_Report').getValue();
        var FD=MissionRequestObject.ReportForm.down("[itemId=FromDateReport]").getValue();
        var TD=MissionRequestObject.ReportForm.down("[itemId=ToDateReport]").getValue();
        var FT=MissionRequestObject.ReportForm.down("[itemId=FromTimeR]").getValue();
        var TT=MissionRequestObject.ReportForm.down("[itemId=ToTimeR]").getValue();
       // var rpt=this.ReportForm.getComponent('FromDateReport').getValue();
      //  alert(r);
     //  var FDaretR= this.ReportForm.getComponent('FromDateReport').getValue();
      // alert(FDaretR);
       
 	/*alert (record.data.StNo);
	alert(record.data.RequestItemID);*/
	Ext.Ajax.request({
		url : "../data/MissionRequest.data.php",
		method : 'POST',
		
			 params : {
                                        task : 'SendReport',
                                        RequestID : record.data.RequestID,
                                        Report:rpt,
                                        FdateR:ShamsiToMiladi(FD.format("Y-m-d")),
                                        TdateR:ShamsiToMiladi(TD.format("Y-m-d")),
                                        FTimeR:FT,
                                        TTimeR:TT,
                                        NumberMiss:record.data.NumberMiss
                                       
                                    },
                                    
                                    
                                    
                                     success: function(response,option){
                //mask.hide();
                var st = Ext.decode(response.responseText);
                if(st.success)
                {
                   // alert("درخواست تایید نهایی شد");
                      MissionRequestObject.grid.getStore().load();
                       //MissionRequestObject.up('window').store.load();
                       //MissionRequestObject.ReportForm.store.load();
                      
                }
                else
                {
                    alert(st.data);
                }
            },
            failure:function(response,option){
                            alert(response.result.data);}
                                    
                                    
                                    
		  
	});
        
       /*  MissionRequestObject.down('ReportForm').getForm().submit({
                            clientValidation: true,
                            url: "../data/MissionRequest.data.php",
                            method : "POST",
                            /* params:{
                                RequestID:this.formID
                              
                            },*/
                          /*  success : function(form,action){
                                if(action.result.success)
                                {
                                    MissionRequestObject.grid.getStore().load();
                                }
                                else
                                {
                                    alert("عملیات مورد نظر با شکست مواجه شد.");
                                }
                                MissionRequestObject.formPanel.hide();
                                MissionRequestObject.grid.show();
                            },
                             failure : function(form,action){
                            alert(action.result.data);
                        }
                        });*/
}     
  MissionRequest.prototype.History=function()
    {
    
    var record = this.grid.getSelectionModel().getLastSelected();
   //  if(!HistoryWin)
        //{
           this.HistoryWin = new Ext.window.Window({
                title: 'سابقه گردش درخواست',
                modal : true,
                autoScroll : true,
                width: 650,
                height : 300,
                closeAction : "hide",
                loader : {
                    url : "../ui/history.php",                    
                    scripts : true
                },
                buttons : [{
                        text : "بازگشت",
                        iconCls : "undo",
                        handler : function(){
                            MissionRequestObject.HistoryWin.hide(); 
                        }
                    }]
            });         
       // }
        this.HistoryWin.show();
        this.HistoryWin.center();
        this.HistoryWin.loader.load({
            params : {
                RequestID :record.data.RequestID
            }
        });
    }
        MissionRequest.prototype.ShowReport=function()
    {
   
       var record = this.grid.getSelectionModel().getLastSelected();
            MissionRequestObject.gridCosts.getStore().proxy.extraParams['RquestID'] =record.data.RequestID,
            MissionRequestObject.gridCosts.getStore().load();
           
             var r1="";
             var r2="";
             var T1="";
             var T2="";
           var fd = record.data.FromDate;
           var fd2 = record.data.ToDate;
            var rd = record.data.FromDateReport;
            var rd2 = record.data.ToDateReport;
          
             t = fd.substr(11,5);
            if (t.substr(0,2) <= 12)
            {
                var ampm = ' AM';        
            }else
            {
                var ampm = ' PM';
                t = t.substr(0,2)-12 + t.substr(2,3) 
            }  
            if(t.substr(0,1) == 0 )
                t = t.substr(1,4)
            T1 = t + ampm;
            
                 t = fd2.substr(11,5);
            if (t.substr(0,2) <= 12)
            {
                var ampm = ' AM';        
            }else
            {
                var ampm = ' PM';
                t = t.substr(0,2)-12 + t.substr(2,3) 
            }  
            if(t.substr(0,1) == 0 )
                t = t.substr(1,4)
            T2 = t + ampm;
           
            //-------------------------------تاریخ بعد از ویرایش
                   t = rd.substr(11,5);
            if (t.substr(0,2) <= 12)
            {
                var ampm = ' AM';        
            }else
            {
                var ampm = ' PM';
                t = t.substr(0,2)-12 + t.substr(2,3) 
            }  
            if(t.substr(0,1) == 0 )
                t = t.substr(1,4)
            r1 = t + ampm;
            
                 t = rd2.substr(11,5);
            if (t.substr(0,2) <= 12)
            {
                var ampm = ' AM';        
            }else
            {
                var ampm = ' PM';
                t = t.substr(0,2)-12 + t.substr(2,3) 
            }  
            if(t.substr(0,1) == 0 )
                t = t.substr(1,4)
            r2 = t + ampm;
            //--------------------------------------
            
            
         this.ReportForm = new Ext.window.Window({
                    closeAction : 'hide',
                    bodyStyle: 'background:white; padding:10px;',
                    title : 'گزارش ماموریت',
                    applyTo : this.get('win'),
                    width: 500,      
         

                    bodyPadding: '5 5 0',
                    defaults: {
                        anchor: '100%'
                    },
                    layout :{
                        type : "table",
                        anchor:"100%",
                        columns :1
                       },
                    items : [{
                  
                    xtype: "displayfield",
                    name : "FDate",
                    itemId : "FDate",
                    fieldLabel : "از تاریخ",
                     labelWidth:60,
                    value:record.data.FromDate  ,
                    colspan:2
                },
                {
                  
                    xtype: 'displayfield',
                    name: 'TDate',
                     itemId : "TDate",
                    fieldLabel: 'تا تاریخ',
                     labelWidth:60,
                        value:record.data.ToDate,            
         colspan:2
                },{
                                xtype : "fieldset",
                             colspan:2,
                           collapsible: true,
		collapsed : true,
                                width:450,
                                style: "margin:0 4 5 10",
                                title : "تغییر تاریخ ماموریت",
                                itemId : "f2",
                                defaults: {
							anchor: '100%'
						},
				layout:{ type:"table",
						 columns:2,
						  style : "width:100%"
					   }, 


                        items :[ {
                    colspan : 1,
                    xtype: "shdatefield",
                    name : "FromDateReport2",
                    itemId : "FromDateReport",
                    fieldLabel : "از تاریخ",
                    labelWidth:40,
                    allowBlank:false , 
                    value : rd.substr(0,10)
                },
                {
                    colspan : 1,
                    xtype: 'timefield',
                    name: 'FromDateR_Time',
                      itemId : "FromTimeR",
                    fieldLabel: 'ساعت',
                    minValue: '0:30 AM',
                    maxValue: '11:30 PM',
                    increment: 30,
                    labelWidth:40,
                    allowBlank:false ,
                   value : r1
        
                },
                {
                    colspan : 1,
                    xtype: "shdatefield",
                    name : "ToDateReport",
                    itemId : "ToDateReport",
                    fieldLabel : " لغایت",
                    allowBlank:false  ,
                     labelWidth:40,
                      value : rd2.substr(0,10)
                },
                {
                    colspan : 1,
                    xtype: 'timefield',
                    name: 'ToDateR_Time',
                    itemId : "ToTimeR",
                    fieldLabel: 'ساعت',
                    minValue: '0:30 AM',
                    maxValue: '11:30 PM',
                    increment: 30,
                     labelWidth:40,
                    allowBlank:false ,
                   value:r2
        
                },]
                              
                                         
		                },{
                                xtype : "fieldset",
                             colspan:2,
                           closable:true,
                                     collapsible: true,
		collapsed : true,
                                width:450,
                                style: "margin:0 4 5 10",
                                title : "سایر هزینه ها",
                                itemId : "f3",
                                defaults: {
							anchor: '100%'
						},
				layout:{ type:"table",
						 columns:2,
						  style : "width:100%"
					   }, 


                        items :[ this.gridCosts]},{
                            xtype : "textarea",						
                            fieldLabel: 'خلاصه گزارش',
                            name: 'Report',
                            itemId : 'cmp_Report',
                             labelWidth:90,
                            colspan : 2,
			width :450,
                        value:record.data.MissionReport
									
                        },     ]		
                });
               
              //  Ext.Ajax.request({
		/*url : "../data/MissionRequest.data.php",
		method : 'POST',
		
			 params : {
                                        task : 'GetReport',
                                        RequestID : record.data.RequestID
                                       
                                        
                                    },
		
		 success : function(response){ 
                
                             
               MissionRequestObject.ReportForm.getComponent('cmp_Report').setValue(response.responseText);
            },            
            failure : function(response){
                MissionRequestObject.ReportForm.getComponent('cmp_Report').setValue('خطا در دریافت گزارش');
            }
	});*/
         this.ReportForm.show();
    }
     MissionRequest.prototype.PrintRequest=function()
     {
     var RequestID = this.grid.getSelectionModel().getLastSelected().data.RequestID;       
        window.open("../ui/PrintRequest.php?RequestID=" + RequestID) ;
     }
       MissionRequest.MissionStatusRender = function(value, p, record)
    {
       var CP = record.data.ControllerPerson;
        var S = record.data.StatusTitle;
        
        //ACCEPTED , REJECTED , REPORTED , FINALIZED
        if (new Array('3','4','5','6').indexOf(record.data.status) > -1)            
            return S + " توسط " + CP;
        
        //RAW
        if (record.data.status == '2' && CP != null )
            return "تایید شده توسط" + " " + CP;
            
        return S;
    }
</script>    
