<script type="text/javascript">
    /* -----------------------------
  //	Programmer	: s.taghizadeh
  //	Date		: 94.8
  ----------------------------- */
    // alert('2');
    AcceptKargozini.prototype = {
	
        address_prefix : "<?= $js_prefix_address ?>",
        formID:"<?= $formID ?>",
        get : function(elementID){
            return document.getElementById(elementID); //findChild(this.TabID, elementID);
        }
         
    };

       
    function AcceptKargozini()
    {
        this. MissionLocations = Ext.create('Ext.data.Store' , {
            fields : ['city_id','state_id','sname' , 'cname', ],
            proxy: {
                type: 'jsonp',
                url: '/HumanResources/missions/data/BaseInfo.data.php?task=SelectMissionLocations',
                reader: {
                    root: 'rows',
                    totalProperty: 'totalCount'
                }
            }
        
        
        });
        
        
    
        this.subItemCombo = new Ext.form.ComboBox({
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
                    AcceptKargoziniObject.grid2.getStore().getAt(0).data.CityId = records[0].data.city_id;
                    AcceptKargoziniObject.grid2.getStore().getAt(0).data.StateId = records[0].data.state_id;
                    this.collapse();
                }
            }
        });
        
        
        this.subItemComboDetailFrom = new Ext.form.ComboBox({
            store:this. MissionLocations , 
            emptyText:'جستجوی مکان...',
            typeAhead: false,
            allowBlank:false ,
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
                              
                          
                    //this.setValue(records[0].data.city_id + " " + records[0].data.state_id);
                    AcceptKargoziniObject.grid3.getStore().getAt(0).data.FromCityId = records[0].data.city_id;
                    AcceptKargoziniObject.grid3.getStore().getAt(0).data.FromStateId = records[0].data.state_id;
                    this.collapse();
                }
            }
        });
         
        this.subItemComboDetailTo = new Ext.form.ComboBox({
            store:this. MissionLocations , 
            emptyText:'جستجوی مکان...',
            typeAhead: false,
            allowBlank:false ,
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
                    AcceptKargoziniObject.grid3.getStore().getAt(0).data.ToCityId = records[0].data.city_id;
                    AcceptKargoziniObject.grid3.getStore().getAt(0).data.ToStateId = records[0].data.state_id;
                    this.collapse();
                }
            }
        });
        
        
        this. MissionVehicles = Ext.create('Ext.data.Store' , {
            fields : ['type','name'],
            proxy: {
                type: 'jsonp',
                url: '/HumanResources/missions/data/BaseInfo.data.php?task=SelectMissionVehicles',
                reader: {
                    root: 'rows',
                    totalProperty: 'totalCount'
                }                            
            }
        });
    
        this.SupplierVehicles = Ext.create('Ext.data.ArrayStore', {
            fields: ['parentID','CarSupplierId', 'title'],
            data : [
                ['1','1','واحد محل خدمت'],                               
            
                ['1','2','مدیریت اداری پشتیبانی'],
					
                ['2','1','واحد محل خدمت'],                               
            
                ['2','2','مدیریت اداری پشتیبانی'],
						
			
                ['3','0',''],                               
            
                ['4','0',''],
                ['5','0',''],
                ['6','0',''],
                  
								
            ]
        });  
        this.ComboMissionVehicles = new Ext.form.ComboBox({
     
               
              
            fields: ['type', 'name'],
            store : this.MissionVehicles,                
            displayField: 'name',
            valueField: 'type',
            allowBlank:false ,
            listeners : {
                Select: function(combo, records){
                    AcceptKargoziniObject.grid3.getStore().getAt(0).data.VehicleId = records[0].data.type;
                    var record = records[0];
                    var elem=AcceptKargoziniObject.ComboCarSupplier;
                    elem.setValue();
                    elem.getStore().clearFilter();
                    elem.getStore().filter('parentID',record.data.type)    
                }
            }
        });   
        this.ComboCarSupplier=new Ext.form.ComboBox({
     
               
              
            fields: ['CarSupplierId', 'title'],
            store : this.SupplierVehicles,                
            displayField: 'title',
            valueField: 'CarSupplierId'
            // allowBlank:false 
            ,listeners : {
                Select: function(combo, records){
                    AcceptKargoziniObject.grid3.getStore().getAt(0).data.CarSupplierId = records[0].data.type;
                          
                }
            }
        });   
    
    
    
    
        
        this.grid2 = <?= $grid2 ?>;
        this.grid3 = <?= $grid3 ?>;
        this.grid = <?= $grid ?>;
       
      
      
        
         
       
        this. MissTypes = Ext.create('Ext.data.ArrayStore', {
            fields: ['type', 'name'],
            data : [
                ['1','با فوق العاده'],
                ['0','بدون فوق العاده'],
				
				
				
            ]
        });
        this. MissionTypes = Ext.create('Ext.data.Store', {
            fields: ['type', 'name'],
            proxy: {
                type: 'jsonp',
                url: '/HumanResources/missions/data/BaseInfo.data.php?task=SelectMissionTypes',
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
                url: '/HumanResources/missions/data/BaseInfo.data.php?task=GetDispatchers',
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
                url: '/HumanResources/missions/data/BaseInfo.data.php?task=SelectMissionLocations',
                reader: {
                    root: 'rows',
                    totalProperty: 'totalCount'
                }
            }
        
        
        });
        this. PersonStore =  new Ext.data.Store({
            proxy: {type: 'jsonp',
                url: '/HumanResources/missions/data/BaseInfo.data.php?task=SelectPersons',
                reader: {root: 'rows',totalProperty: 'totalCount'}
            },
            fields : ['PersonID','PFName','PLName'],
            pageSize: 10
        });
        
        
        
     
        
      
        
        
      
        
        
        
        
        
        this.formPanel = new Ext.form.Panel({  
            renderTo: this.get('FormDIV'),
            width : 900,
            // height : 1000,
            title :  "فرم درخواست ماموریت اداری",
            //title : "فرم درخواست ماموریت اداری",
            frame : true,
            bodyPadding: '5 5 0',
            fieldDefaults: {labelWidth: 150},        
            layout: {
                type : "table",
                columns :2,
                width:1020
            },
            items :  [  {
                            xtype: 'displayfield',
                           fieldLabel : "مامور",
                            itemId : "PersonID2"
                    
                        }    ,     
               
                {
                            xtype : "displayfield",
                    
                          
                            itemId : "TitleDispatcher",
                            fieldLabel : "اعزام کننده"
                          
                              
                              
                        }
               ,
              
                 {
                            xtype: 'displayfield',
                           fieldLabel : "نوع اعزام",
                          // colspan :2,
                            itemId : "type"
                    
                        }  ,
                {
                            xtype: 'displayfield',
                           fieldLabel : "نوع ماموریت",
                          // colspan :2,
                            itemId : "MissType"
                    
            },
            
                {
                   // colspan : 2,
                    xtype: "displayfield",
                    name : "FromDate_Date",
                    itemId : "FromDate",
                    fieldLabel : "از تاریخ"  
                                 
                },
                {
                   // colspan : 2,
                    xtype: 'displayfield',
                    name: 'FromDate_Time',
                    fieldLabel: 'ساعت'
                               
        
                },
                {
                   // colspan : 2,
                    xtype: "displayfield",
                    name : "ToDate_Date",
                    itemId : "ToDate",
                    fieldLabel : " لغایت"
                },
                {
                   // colspan : 2,
                    xtype: 'displayfield',
                    name: 'ToDate_Time',
                    itemId : "ToTime",
                    fieldLabel: 'ساعت'
                                 
        
                },
                {
                    xtype : "displayfield",
                    name : "subject",
                    fieldLabel : "موضوع ماموریت",  
                    width : 680,
                    rows : 1,
                    colspan : 2
                   
                },
                {
                    xtype : "displayfield",
                    name : "stuff",
                    fieldLabel : " وسایل مورد نیاز در ماموریت",   
                    width : 680,
                    rows : 1,
                    colspan : 2
                             
                },
                {  xtype : "fieldset",
                    colspan:2,
                      
                    width:500,
                    // style: "margin:0 4 5 10",
                    title : "",
                    itemId : "f50",
                    defaults: {
                        anchor: '100%'
                    },
                    /*layout:{ type:"table",
                            columns:2,
                            style : "width:100%"
                        }, */
                    items: [
                        {
                            xtype: 'radiogroup',
                            fieldLabel: 'وسیله نقلیه',
                            allowBlank: false,
                            itemId: 'cmp_transport',    readOnly:true,
                            colspan:2,
                            width: 450,
                            items:
                                [{
                                    readOnly:true, boxLabel: 'تعیین شده ', name: 'transport', inputValue: 1,id: 'radio4'/*,
                                            listeners: {change: function () {
                                                    if (this.checked)
                                                         this.grid3
                                                        //this.up('form').down('[itemId=status]').setValue(this.getSubmitValue());
                                                }}*/
                                },
                                {
                                    readOnly:true, boxLabel: 'تعیین نشده', name: 'transport', inputValue: 2,id: 'radio5'/*,
                                            listeners: {change: function () {
                                                    if (this.checked)
                                                        this.up('form').down('[itemId=status]').setValue(this.getSubmitValue());
                                                }}*/},
                                {
                                    readOnly:true, boxLabel: 'نیاز نمی باشد', name: 'transport', inputValue: 3,itemId:'radio6'/*,
                                            listeners: {change: function () {
                                                    if (this.checked)
                                                        this.up('form').down('[itemId=status]').setValue(this.getSubmitValue());
                                                }}*/}]
                        },]},
                {
                    xtype : "fieldset",
                    colspan:1,
                      
                    width:500,
                    // style: "margin:0 4 5 10",
                    title : "",
                    itemId : "f8",
                    defaults: {
                        anchor: '100%'
                    },
                    /*layout:{ type:"table",
                            columns:2,
                            style : "width:100%"
                        }, */
                    items: [{
                            xtype: 'radiogroup',
                            fieldLabel: 'محل اقامت',
                            allowBlank: false,
                            itemId: 'cmp_PlaceStay',   
                            colspan: 2,
                            width: 450,
                            items:
                                [{
                                     readOnly:true,boxLabel: 'تعیین شده ', name: 'PlaceStay', inputValue: 1,id: 'radio1'/*,
                                            listeners: {change: function () {
                                                    if (this.checked)
                                                        this.up('form').down('[itemId=status]').setValue(this.getSubmitValue());
                                                }}*/
                                },
                                {
                                    readOnly:true, boxLabel: 'تعیین نشده', name: 'PlaceStay', inputValue: 2,id: 'radio2'/*,
                                            listeners: {change: function () {
                                                    if (this.checked)
                                                        this.up('form').down('[itemId=status]').setValue(this.getSubmitValue());
                                                }}*/},
                                {
                                    readOnly:true, boxLabel: 'نیاز نمی باشد', name: 'PlaceStay', inputValue: 3,itemId:'radio3'/*,
                                            listeners: {change: function () {
                                                    if (this.checked)
                                                        this.up('form').down('[itemId=status]').setValue(this.getSubmitValue());
                                                }}*/}]
                        },]
                },    
                {
                    xtype: 'hidden',
                    name: 'StatusStay',
                    itemId: 'PlaceStay',
                    value: 'aa'
                },
                
                {
                    xtype : "fieldset",
                    colspan:2,
                    closable:true,
                    collapsible: true,
                    collapsed : true,
                    width:750,
                    style: "margin:0 4 5 10",
                    title : "شهرهای ماموریت",
                    itemId : "f3",
                    defaults: {
                        anchor: '100%'
                    },
                    layout:{ type:"vbox",
                        columns:2,
                        style : "width:100%"
                    }, 


                    items :[  this.grid2]},
                 {
                  
                    xtype : "textarea",
                    name : "comments",
                    fieldLabel :"توضیحات",
                     itemId : 'comments',
                    width :500,
                    rows : 1,
                    colspan : 2
                           
                
                             
                },
                
                
                //    ,
            
                {
                    xtype: 'hidden',
                    name: 'Statustransport',
                    itemId: 'transport',
                    value: 'aa'
                },
                {
                    xtype : 'hiddenfield',
                    name : 'RequestID',
                    itemId : 'RequestID',
                    value : ''
                } ,
                {
                    xtype : "hidden",
                    colspan:2,
                    closable:true,
                    collapsible: true,
                    collapsed : true,
                    width:800,
                    style: "margin:0 4 5 10",
                    title :"وسیله نقلیه",
                    itemId : "f2",
                    defaults: {
                        anchor: '100%'
                    },
                    layout:{ type:"table",
                        columns:2,
                        style : "width:100%"
                    }, 


                    items :[  this.grid3]},
              
               
         
            
            ],
            buttons : [{
                    text : "تایید",
                    iconCls : "tick",
                  
                    itemId : "a1" ,
                    handler : function(){
                       // var t1=AcceptKargoziniObject.grid2.getStore().getCount();
                      //  if (t1<1)
                      //  { alert("شهر ماموریت باید اضافه شود"); return;
                       // }
                       // var t2=AcceptKargoziniObject.grid2.getStore().getCount();
                        /*  if(t2<1)
                        {
                            alert("وسیله نقلیه باید اضافه شود "); return;
                        }*/
                        AcceptKargoziniObject.formPanel.getForm().submit({
                           clientValidation: true,
                            url: '/HumanResources/missions/data/MissionRequest.data.php?task=AcceptKargozini',
                            method : "POST",
                            /* params:{
                                RequestID:this.formID
                              
                            },*/
                            success : function(form,action){
                                if(action.result.success)
                                {
                                    AcceptKargoziniObject.grid.getStore().load();
                                }
                                else
                                {
                                    alert("عملیات مورد نظر با شکست مواجه شد.");
                                }
                                AcceptKargoziniObject.formPanel.hide();
                                AcceptKargoziniObject.grid.show();
                                AcceptKargoziniObject.mainTab.show();
                            },
                            failure : function(form,action){
                                alert(action.result.data);
                            }
                        });
                    }
                }, {
                    text : "عدم تایید",
                    itemId : "r1" ,
                    iconCls : "cross",
                    handler : function(){
                        Ext.Ajax.request({
                            url : "/HumanResources/missions/data/MissionRequest.data.php",
                            method : "POST",
                            params : {
                                task : "RejectKargozini",
                                //  RequestID : this.grid.getSelectionModel().getLastSelected().data.RequestID 
                                RequestID:AcceptKargoziniObject.formPanel.down('[itemId=RequestID]').value,
                             comments:AcceptKargoziniObject.formPanel.down('[itemId=comments]').value
                            },
                            success : function(response){
                       
                                alert("با موفقیت انجام ")                
                                AcceptKargoziniObject.grid.getStore().load();               
                            },            
                            failure : function(response){
                                alert('خطا در اجرای عملیات');  
                            }
                        });
                        AcceptKargoziniObject.formPanel.hide();
                        AcceptKargoziniObject.grid.show();
                        AcceptKargoziniObject.mainTab.show();
                       
                    }
                },
                {
                    text : "انصراف",
                    iconCls : "undo",
                    handler : function(){
                        AcceptKargoziniObject.formPanel.hide();
                        AcceptKargoziniObject.grid.show();
                        AcceptKargoziniObject.mainTab.show();
                       
                    }
                }]
        }); 
        this.formPanel.hide();
        
        this.mainTab = new Ext.TabPanel({
            renderTo: this.get("MissionRequests"),
            activeTab: 0,
            plain: true,
            width: 900,
            height: 600,
            items:[{
                    itemId:'Requests',
                    title:'درخواست ماموریت',
				
                    items: [this.grid]
                }]


        });

        
    }
    AcceptKargozini.opRenderOriginalGrid = function(value, p, record)
    {
    
        return  "<div title='عملیات' class='setting' onclick='AcceptKargoziniObject.OperationMenu(event);' " +
            "style='background-repeat:no-repeat;background-position:right;" +
            "cursor:pointer;height:16'></div>";
        
       
    }
    AcceptKargozini.prototype.OperationMenu = function(e)
    {
       
        // var record = grid.getSelectionModel().getLastSelected();
       // var record = this.grid.getSelectionModel().getLastSelected();
        var op_menu = new Ext.menu.Menu();  
        
            
            op_menu.add({
                text: 'مشاهده و بررسی',
                iconCls: 'edit',
                handler : function(){AcceptKargoziniObject.EditRequest();}
                   
            });            
          
            op_menu.add({
                text: 'مشاهده سابقه',
                iconCls: 'history',
                handler : function(){AcceptKargoziniObject.History(0);}
                   
            });   
          
       
        op_menu.showAt(e.pageX-120, e.pageY);      
    }
    AcceptKargozini.prototype.EditRequest=function(){
      
          
       this.formPanel.getForm().reset();
        this.formPanel.show();
        var record = this.grid.getSelectionModel().getLastSelected();
        this.formPanel.loadRecord(record);
       // this.formPanel.down('[itemId=type]').getStore().load();
      
        this.grid2.getStore().proxy.extraParams['RquestID'] =this.formPanel.down('[itemId=RequestID]').value;
        this.grid2.getStore().load();
        this.grid3.getStore().proxy.extraParams['RquestID'] =this.formPanel.down('[itemId=RequestID]').value;
        this.grid3.getStore().load(); 
      /*  this.formPanel.down('[itemId=PersonID]').getStore().proxy.extraParams['query'] = record.data.PersonID;
        this.formPanel.down('[itemId=PersonID]').getStore().load();
        this.formPanel.down('[itemId=PersonID]').getStore().proxy.extraParams['query'] = '';*/
        this.formPanel.getComponent("PersonID2").setValue(record.data.person);
         this.formPanel.getComponent("type").setValue(record.data.TypeTitle);
          this.formPanel.getComponent("MissType").setValue(record.data.TypeTitleMiss);
        this.formPanel.getComponent("TitleDispatcher").setValue(record.data.TitleDispatcher);
      
       
          
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
      /*  if ((record.data.RequesterPersonId == <?= $_SESSION['PersonID'] ?> && record.data.status == '<?= DRAFT ?>')
            || ((record.data.RequesterPersonId == <?= $_SESSION['PersonID'] ?> && record.data.status == '<?= DRAFT ?>')))
      */
       // {
        
           
         //   this.formPanel.down('[itemId=DispatcherId]').getStore().load();
           
             
            
       // }   
      //  else
      //  {
            
          
     
   
     
    }
    AcceptKargozini.prototype.History=function()
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
                        AcceptKargoziniObject.HistoryWin.hide(); 
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
 
    AcceptKargozini.opRenderHistory = function(value, p, record)
    {
        var st = "";
        
        st += "<div  title='مشاهده سابقه' class='view' onclick='AcceptKargoziniObject.History();' " +
            "style='float:center;background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;height:16'></div>";
        return st;
    }
    AcceptKargozini.opRenderAccept=function(value, p, record){
        var st = "";
        
        st += "<div  title='مشاهده و بررسی' class='tick' onclick='AcceptKargoziniObject.EditRequest();' " +
            "style='float:center;background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;height:16'></div>";
        return st;
    }
  
</script>    
