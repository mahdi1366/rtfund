<script type="text/javascript">
    
    function OperationMenuRender(v,p,r){
        return  "<div title='عملیات' class='setting' onclick='OperationMenu(event);' " +
            "style='background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;height:16'></div>";
    }
    
    OperationMenu = function(e){
        var record = grid.getSelectionModel().getLastSelected();
        var op_menu = new Ext.menu.Menu();  
        if (record.data.ControllerPerson == null && record.data.status == '<?= RAW ?>')
        {            
            op_menu.add({
                text: ' برگشت درخواست',
                iconCls: 'undo',
                handler : ReturnRequest
            });
        }   
        switch (record.data.status)
        {
            case '<?= DRAFT ?>' ://"DRAFT":        
                op_menu.add({
                    text: 'ویرایش',
                    iconCls: 'edit',
                    handler : EditRequest
                });            
                op_menu.add({
                    text: ' ارسال ',
                    iconCls: 'send',
                    handler : SendRequest
                });            
                op_menu.add({
                    text: ' حذف ',
                    iconCls: 'remove',
                    handler : DeleteRequest
                });            
                break;
        
            case '<?= RAW ?>' : //"RAW":
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
                
            case '<?= ACCEPTED_ ?>': //"ACCEPTED":
                op_menu.add({
                    text: 'مشاهده سابقه ',
                    iconCls: 'history',
                    handler : History
                });
                op_menu.add({
                    text: ' ارسال گزارش',
                    iconCls: '02',
                    handler : SendReport
                });
                op_menu.add({
                    text: ' چاپ درخواست',
                    iconCls: 'print',
                    handler : PrintRequest
                });
                break;  
                
            case '<?= REJECTED ?>': //"REJECTED":
                op_menu.add({
                    text: 'مشاهده سابقه ',
                    iconCls: 'history',
                    handler : History
                });                
                break;
                
            case '<?= REPORTED ?>': //"REPORTED":                
            case '<?= FINALIZED ?>': //"FINALIZED":
            case '<?= ADMITTED?>'://"ADMITTED":
            case '<?= PAYED?>' ://"PAYED":
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
        }
        op_menu.showAt(e.pageX-120, e.pageY);        
    }
    
    function History (){        
        if(!HistoryWin)
        {
            var HistoryWin = new Ext.window.Window({
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
                            HistoryWin.hide(); 
                        }
                    }]
            });         
        }
        HistoryWin.show();
        HistoryWin.center();
        HistoryWin.loader.load({
            params : {
                RequestID : grid.getSelectionModel().getLastSelected().data.RequestID
            }
        });
    }
    
    function ReturnRequest(){    
        Ext.Msg.confirm('', 'آیا مایل به بازگشت درخواست هستید ؟', 
        function(btn) {
            if (btn === 'yes') {                
                Ext.Ajax.request({
                    url : "../data/MissionRequest.data.php",
                    method : "POST",
                    params : {
                        task : "ReturnRequest",
                        RequestID : grid.getSelectionModel().getLastSelected().data.RequestID                	  		
                    },
                    success : function(response){
                        alert(response.responseText)                
                        grid.getStore().load();                
                    },            
                    failure : function(response){
                        alert('خطا در اجرای عملیات');  
                    }
                });
            }
        });        
    }
    
    function DeleteRequest(){    
        Ext.Msg.confirm('', 'آیا مایل به حذف  درخواست هستید ؟', 
        function(btn) {
            if (btn === 'yes') {
                Ext.Ajax.request({
                    url : "../data/MissionRequest.data.php",
                    method : "POST",
                    params : {
                        task : "DeleteRequest",
                        RequestID : grid.getSelectionModel().getLastSelected().data.RequestID                	  		
                    },
                    success : function(response){
                        alert(response.responseText)                
                        grid.getStore().load();                
                    },            
                    failure : function(response){
                        alert('خطا در اجرای عملیات');  
                    }
                });
            }
        });
    }
    
    function EditRequest(){    
        MissionTypes.load();
        MissionVehicles.load();        
        ReqPanel.show();
        ReqPanel.down("[itemId=CancelBtn]").show();
        var record = grid.getSelectionModel().getLastSelected();        
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
                
        ReqPanel.down('[itemId=MissionLocationID]').getStore().proxy.extraParams['city_id'] = record.data.city_id;
        ReqPanel.down('[itemId=MissionLocationID]').getStore().proxy.extraParams['state_id'] = record.data.state_id;
        ReqPanel.down('[itemId=MissionLocationID]').getStore().load();
        ReqPanel.down('[itemId=MissionLocationID]').getStore().proxy.extraParams['city_id'] = '';
        ReqPanel.down('[itemId=MissionLocationID]').getStore().proxy.extraParams['state_id'] = '';  
        var cid = record.data.city_id;                         
        ReqPanel.getForm().reset();
        ReqPanel.getForm().loadRecord(record);
        ReqPanel.down('[itemId=MissionLocationID]').setValue(cid.toString());        
    }
    
    function SendRequest(){
        Ext.Msg.confirm('', 'آیا مایل به ارسال درخواست هستید ؟', 
        function(btn) {
            if (btn === 'yes') {                
                Ext.Ajax.request({
                    url : "../data/MissionRequest.data.php",
                    method : "POST",
                    params : {
                        task : "SendRequestByID",
                        RequestID : grid.getSelectionModel().getLastSelected().data.RequestID
                    },
                    success : function(response){
                        alert(response.responseText)                
                        grid.getStore().load();                
                    },            
                    failure : function(response){
                        alert('خطا در اجرای عملیات');  
                    }
                });
            }
        });
    }
    
    function PrintRequest(){   
        var RequestID = grid.getSelectionModel().getLastSelected().data.RequestID;       
        window.open("../ui/PrintRequest.php?RequestID=" + RequestID) ;
    }
    
    function SendReport(){
        var ReportForm = new Ext.form.Panel({               
            border : false, 
            bodyStyle: 'background-color:#FFFFFF;',
            items : [{                
                    xtype : "textarea",        
                    name : "Report",               
                    width : 500,              
                    height : 215,             
                    margin: "25 19 25 25"      
                }]                  
        });   

        var ReportWin = new Ext.window.Window({     
            title: 'ارسال گزارش ماموریت',      
            modal : true,           
            autoScroll : true,   
            bodyStyle: 'background-color:#FFFFFF;',
            width: 550,      
            height : 330, 
            maxLength : 250,
            items : [ReportForm],   
            buttons : [{              
                    text : "انصراف",        
                    iconCls : "cancel",        
                    handler : function(){
                        ReportWin.close();     
                    }      
                },
                {              
                    text : "ارسال",        
                    iconCls : "send",        
                    handler : function(){ 
                        Ext.Msg.confirm('', 'آیا مایل به ارسال گزارش  هستید ؟', 
                        function(btn) {
                            if (btn === 'yes') {                                
                                ReportForm.submit({
                                    ClientValidation : true, ////
                                    url : "../data/MissionRequest.data.php",
                                    params : {
                                        task : 'SendReport',
                                        RequestID : grid.getSelectionModel().getLastSelected().data.RequestID
                                    },
                                    method : "POST",
                                    success : function(form,action){
                                        alert(action.result.data);  
                                        grid.getStore().load();  
                                    },
                                    failure : function(form,action){
                                        alert(action.result.data);
                                        grid.getStore().load();  
                                    }
                                });                    
                                ReportWin.close();                        
                            }
                        });
                    }      
                }]
        });          
        ReportWin.show();  
    }
    
    function AddRequest(){
        ReqPanel.show();
    }
    
    
    var MissionTypes = Ext.create('Ext.data.Store', {
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
    
    var MissionVehicles = Ext.create('Ext.data.Store' , {
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

    var MissionLocations = Ext.create('Ext.data.Store' , {
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
    //--------------------------------------------------------------------------------
    var ReqPanel = new Ext.form.Panel({  
        name :  'ReqPanel',
        itemId : 'ReqPanel',
        width : 800,
        height : 260,
        title : "فرم درخواست ماموریت اداری",
        frame : true,
        fieldDefaults: {labelWidth: 150},        
        layout: {
            type: 'table',
            tableAttrs: {
                style: {width: '95%'}
            },
            columns: 2
        },
        items :  [
            {
                xtype : "hidden",
                name : "city_id",
                id : "city_id"
            },{
                xtype : "hidden",
                name : "state_id",
                id : "state_id"
            },
            {
                xtype : "combobox",
                colspan : 1,
                name : "type",
                itemId : "type",
                fieldLabel : " نوع ماموریت",
                fields: ['type', 'name'],
                store : MissionTypes,                
                displayField: 'name',
                valueField: 'type',               
                allowBlank:false                
            },
            {
                xtype : "combobox",
                colspan : 1,
               // name : "MissionLocationID",
                itemId : "MissionLocationID",
                fieldLabel : " محل ماموریت",
                store : MissionLocations,                
                displayField: 'cname',
                tpl: new Ext.XTemplate(
                '<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
                ,'<td height="23px">استان</td>'
                ,'<td>شهر</td>'
                ,'<tpl for=".">'
                ,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
                ,'<td style="border-left:0;border-right:0" class="search-item">{sname}</td>'
                ,'<td style="border-left:0;border-right:0" class="search-item">{cname}</td></tr>'
                ,'</tpl>'
                ,'</table>'),
                valueField: 'city_id', 
                allowBlank:false  ,
                listeners : {
                    select: function(combo, records){
                        Ext.getCmp("city_id").setValue(records[0].data.city_id);
                        Ext.getCmp("state_id").setValue(records[0].data.state_id);
                    }
                }
            },
            {
                xtype: "shdatefield",
                name : "FromDate_Date",
                itemId : "FromDate",
                fieldLabel : "از تاریخ",
                allowBlank:false                
            },
            {
                xtype: 'timefield',
                name: 'FromDate_Time',
                fieldLabel: 'ساعت',
                minValue: '0:30 AM',
                maxValue: '11:30 PM',
                increment: 30,
                allowBlank:false                
        
            },
            {
                xtype: "shdatefield",
                name : "ToDate_Date",
                itemId : "ToDate",
                fieldLabel : " لغایت",
                allowBlank:false                
            },
            {
                xtype: 'timefield',
                name: 'ToDate_Time',
                itemId : "ToTime",
                fieldLabel: 'ساعت',
                minValue: '0:30 AM',
                maxValue: '11:30 PM',
                increment: 30,
                allowBlank:false                
        
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
                xtype : "combobox",
                colspan : 2,
                name : "vehicle",
                itemId : "vehicle",
                fieldLabel : "  وسیله نقلیه رفت و برگشت",
                fields: ['type', 'name'],
                store : MissionVehicles,                
                displayField: 'name',
                valueField: 'type',
                allowBlank:false                
            },            
            {
                xtype : 'hiddenfield',
                name : 'RequestID',
                itemId : 'RequestID',
                value : ''
            }            
        ],
        buttons : [{
                iconCls : "save",
                text : "ذخیره پیش نویس",
                handler : function(){
                    ReqPanel.getForm().submit({
                        ClientValidation : true, ////
                        url : "../data/MissionRequest.data.php",
                        params : {
                            task : 'SaveRequestAsDraft'
                        },
                        method : "POST",
                        success : function(form,action){
                            if (action.result.success){
                                ReqPanel.getComponent('RequestID').setValue(action.result.data) ;
                                alert('پیش نویس با موفقیت ذخیره شد');                                
                            }
                            else{
                                alert('خطا در ذخیره پیش نویس');
                            }
                            grid.getStore().load();
                            ReqPanel.down("[itemId=CancelBtn]").hide();   
                            ReqPanel.getForm().reset();
                        },
                        failure : function(form,action){
                            alert(action.result.data);
                        }
                    });
                }
            },{
                text : "انصراف",
                iconCls : "cancel",
                itemId : 'CancelBtn',
                hidden : true,
                handler : function (){
                    ReqPanel.getForm().reset();
                    ReqPanel.down("[itemId=CancelBtn]").hide(); 
                }	
            },{
                iconCls : "send",
                text : "ارسال درخواست" ,
                handler : function(){
                    ReqPanel.getForm().submit({
                        ClientValidation : true, ////
                        url : "../data/MissionRequest.data.php?task=SendRequest",
                        method : "POST",
                        success : function(form,action){                            
                            alert(action.result.data);      
                            grid.getStore().load();
                            ReqPanel.down("[itemId=CancelBtn]").hide();
                            ReqPanel.getForm().reset();
                        },
                        failure : function(form,action){
                            alert(action.result.data);
                        }
                    });
                }
            }]
    });
    ReqPanel.hide();

</script>