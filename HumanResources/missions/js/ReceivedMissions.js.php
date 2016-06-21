<script type="text/javascript">
       
    ReceivedMissions.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix : "<?= $js_prefix_address ?>",
        
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };   
       function OperationMenuRender(v,p,r){
        return  "<div title='عملیات' class='setting' onclick='OperationMenu(event);' " +
            "style='background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;height:16'></div>";
    }
     OperationMenu = function(e){
       // var record = grid.getSelectionModel().getLastSelected();
        var op_menu = new Ext.menu.Menu(); 
         op_menu.add({
                    text: ' چاپ درخواست',
                    iconCls: 'print',
                    handler : PrintRequest
                });  
                op_menu.add({
                    text: 'تایید',
                    iconCls: 'tick',
                    handler : Accept2
                });  
                 op_menu.showAt(e.pageX-120, e.pageY);    
     }
    function ReceivedMissions(){
        this.ShowTypes = Ext.create('Ext.data.Store', {
            fields: ['type', 'name'],
            data : [            
                {"type":"FINALIZED", "name":"مشاهده ماموریت های رسیده"},
                {"type":"ADMITTED", "name":"مشاهده ماموریت های تایید شده"}
            ]
        });
    };
    
    var ReceivedMissionsObject = new ReceivedMissions();
 
    ReceivedMissions.prototype.ZaribTextBoxRender = function(value,p,r){           
        var id = 'AreaCoef' + r.data.MissRequestsID;
        return "<center><input type='text' name='"+id+"'  id='"+id+"' size='6'>"+value+"</input></center>";
    };
    
    ReceivedMissions.prototype.AcceptRender= function (){
			
        return "<div title='تایید پرداخت' class='tick'  onClick='ReceivedMissionsObject.Accept(ReceivedMissionsObject.grid.getSelectionModel().getLastSelected());' " +
            "style='background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;height:16'></div>";
    };
 ReceivedMissions.prototype.ReturnRender= function (){
		
        var st = "";
	
		st += "<div title='برگشت' class='undo'  onClick='ReceivedMissionsObject.Return(ReceivedMissionsObject.grid_admitted.getSelectionModel().getLastSelected());' " +
				"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
		st += "<div  title='چاپ حکم' class='view' onclick='PrintRequest(grid_admitted)' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
		return st;
	
        /*return "<div title='برگشت' class='undo'  onClick='ReceivedMissionsObject.Return(ReceivedMissionsObject.grid_admitted.getSelectionModel().getLastSelected());' " +
            "style='background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;height:16'></div>";*/
    };
    
	ReceivedMissions.prototype.ACCItem = function(){ 
		ReceivedMissionsObject.get('MissDIV').hidden=true;
		ReceivedMissionsObject.get('AddMissDIV').hidden=false;                                                      
		if (!ReceivedMissionsObject.grid_admitted.rendered){
			ReceivedMissionsObject.grid_admitted.render(ReceivedMissionsObject.get('AddMissDIV'));
		}

	}	
	
	ReceivedMissions.prototype.RecieveItem = function(){ 
		ReceivedMissionsObject.get('MissDIV').hidden=false;
		ReceivedMissionsObject.get('AddMissDIV').hidden=true;     
	}
	
    ReceivedMissions.prototype.Accept = function(record){		
		
       /*  var id = 'AreaCoef' + record.data.MissRequestsID;
       var AreaCoef = document.getElementById(id).value;
        if (AreaCoef=='' ){
            alert('ضریب منطقه را وارد نمایید');
            return;
        }*/
        Ext.Msg.confirm('', 'آیا مایل به تایید هستید ؟', 
        function(btn) {
            if (btn === 'yes') {
                Ext.Ajax.request({
                    url : ReceivedMissionsObject.address_prefix + '../data/ReceivedMissions.data.php',              
                    method : "POST",
                    params : {
                        task : "Accept",
                        MissRequestsID : ReceivedMissionsObject.grid.getSelectionModel().getLastSelected().data.RequestID
                        
                    },
                    success : function(response){
                        alert(response.responseText)
                        ReceivedMissionsObject.grid.getStore().load();
                    },
                    failure : function(response){
                        alert('خطا در اجرای عملیات');
                    }
                });
            }
        });      
    };
     ReceivedMissions.prototype.Return = function(record){		
		
     
         Ext.Msg.confirm('', 'آیا مایل به برگشت درخواست هستید ؟', 
        function(btn) {
            if (btn === 'yes') {
                Ext.Ajax.request({
                    url : ReceivedMissionsObject.address_prefix + '../data/ReceivedMissions.data.php',              
                    method : "POST",
                    params : {
                        task : "ReturnRequest",
                        MissRequestsID : ReceivedMissionsObject.grid_admitted.getSelectionModel().getLastSelected().data.RequestID
                        
                    },
                    success : function(response){
                        alert(response.responseText)
                        ReceivedMissionsObject.grid_admitted.getStore().load();
                    },
                    failure : function(response){
                        alert('خطا در اجرای عملیات');
                    }
                });
            }
        });      
    };
    ReceivedMissions.prototype.ShowTypePanel = 
        new Ext.form.Panel({  
        itemId : 'ShowTypePanel',
        frame : false,
        border:false,
        fieldDefaults: {labelWidth: 150},  
        items :  [
           /* {            
                xtype : "combobox",
                width : 250,
                colspan : 1,
                name : "ShowType",
                itemId : "ShowType",
                fieldLabel : "",
                fields: ['type', 'name'],
                store : ReceivedMissionsObject.ShowTypes,                
                displayField: 'name',
                valueField: 'type',               
                allowBlank:false ,
                style : {
                    align : 'center',
                    margin: 'auto'
                },
                listeners : {
                    select : function(combo,records){
                        var record = records[0].data.type;                      
                        if (record == 'ADMITTED'){                            
                            ReceivedMissionsObject.get('MissDIV').hidden=true;
                            ReceivedMissionsObject.get('AddMissDIV').hidden=false;                                                      
                            if (!ReceivedMissionsObject.grid_admitted.rendered){
                                ReceivedMissionsObject.grid_admitted.render(ReceivedMissionsObject.get('AddMissDIV'));
                            }
                        }
                        else {                      
                            ReceivedMissionsObject.get('MissDIV').hidden=false;
                            ReceivedMissionsObject.get('AddMissDIV').hidden=true;                          
                        }
                    }
                }
            }*/
        ]
    });
	  function ShowReportRender(){
        return  "<div title='مشاهده گزارش' class='view' onclick='ShowReport(ReceivedMissionsObject.grid.getSelectionModel().getLastSelected().data.RequestID);' " +
            "style='background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;height:16'></div>";    
    }
	/*function ShowReport(ReqID){        
        var ReportForm = new Ext.form.Panel({               
            border : false, 
            bodyStyle: 'background-color:#FFFFFF;',
            items : [{                
                    xtype : "textarea",        
                    name : "Report",  
                    id : 'Report',
                    width : 500,              
                    height : 240,
                    margin: "25 19 25 25",
                    value : ''
                }]                  
        });  
        
        Ext.Ajax.request({
            url : "/HumanResources/missions/data/ReceivedMissions.data.php",
            method : "POST",
            params : {
                task : "GetReport",
                RequestID : ReqID
            },
            success : function(response){                
                ReportForm.getComponent('Report').setValue(response.responseText);
            },            
            failure : function(response){
                ReportForm.getComponent('Report').setValue('خطا در دریافت گزارش');
            }
        });  

        var ReportWin = new Ext.window.Window({     
            title: ' گزارش ماموریت',      
            modal : true,           
            autoScroll : true, 
            bodyStyle: 'background-color:#FFFFFF;',
            width: 550,      
            height : 360, 
            maxLength : 250,
            items : [ReportForm],
            buttons: [{              
                    text : "انصراف",        
                    iconCls : "cancel",        
                    handler : function(){
                        ReportWin.close();     
                    }
                }]            
        });          
        ReportWin.show();      
    }*/
	  function ShowReport(ReqID){ 
       
        var RequestID = ReqID;       
        window.open("/HumanResources/missions/ui/PrintRequest.php?RequestID=" + RequestID) ;
    }
        function PrintRequest(){  
         alert('dfdfd');
// ReceivedMissionsObject.grid_admitted.getSelectionModel().getLastSelected().data.RequestID
        var RequestID =ReceivedMissionsObject.grid.getSelectionModel().getLastSelected().data.RequestID       
       window.open("/HumanResources/missions/ui/PrintRequest.php?RequestID=" + RequestID) ;
    }
    function Accept2() 
    {		
		
       /*  var id = 'AreaCoef' + record.data.MissRequestsID;
       var AreaCoef = document.getElementById(id).value;
        if (AreaCoef=='' ){
            alert('ضریب منطقه را وارد نمایید');
            return;
        }*/
        Ext.Msg.confirm('', 'آیا مایل به تایید هستید ؟', 
        function(btn) {
            if (btn === 'yes') {
                Ext.Ajax.request({
                    url : ReceivedMissionsObject.address_prefix + '../data/ReceivedMissions.data.php',              
                    method : "POST",
                    params : {
                        task : "Accept",
                        MissRequestsID : ReceivedMissionsObject.grid.getSelectionModel().getLastSelected().data.RequestID
                        
                    },
                    success : function(response){
                        alert(response.responseText)
                        ReceivedMissionsObject.grid.getStore().load();
                    },
                    failure : function(response){
                        alert('خطا در اجرای عملیات');
                    }
                });
            }
        });      
    };
            
</script>






