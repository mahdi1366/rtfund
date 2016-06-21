<script>
    //-------------------------
    // programmer:	S.Taghizadeh
      
    //-------------------------
    MeritForms.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix : "<?= $js_prefix_address ?>",

        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };
//var RandomNumber="";
if (document.getElementById("OAS_Scanner") != null ){
	document.getElementById("OAS_Scanner").remove();
      }
    function MeritForms()
    { 
	 this.RandomNumber = '';
        this.PersonStore =  new Ext.data.Store({
            proxy: {type: 'jsonp',
                url: '/HumanResources/personal/persons/data/person.data.php?task=searchPerson',
                reader: {root: 'rows',totalProperty: 'totalCount'}
            },
            fields : ['PersonID','staff_id','pfname','plname','unit_name'],
            pageSize: 10
        });
        this.formPanel = new Ext.form.Panel({
            renderTo: this.get("newDiv"),                  
            collapsible: true,
            frame: true,
            title: 'ثبت نشان لیاقت',
            bodyPadding: '5 5 0',
            width:750,
            layout :{
                type : "table",
                columns :2,
                width:1020
            },
            items: [
                {
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
                    labelWidth: 100,
                    colspan: 2,
                    // emptyText: 'Ø¬Ø³ØªØ¬ÙˆÙŠ Ù�Ø±Ø¯ ...',
                    listConfig: {
                        loadingText: 'در حال جستجو...',
                        emptyText: 'فاقد اطلاعات',
                        itemCls : "search-item"
                    },
                    displayTpl: new Ext.XTemplate('<tpl for=".">{plname} ({PersonID})</tpl>'),
                    tpl: new Ext.XTemplate(
                    '<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
                    , '<td height="23px">کد پرسنلی</td>'
                    ,'<td>کد شخص</td>'
                    , '<td height="23px">نام</td>'
                    , '<td>نام خانوادگی</td>'
                    ,'<td>واحد محل خدمت</td>'
                    , '</tr>',
                    '<tpl for=".">',
                    '<tr class="search-item">'
                    ,'<td> {PersonID}</td>'
                    ,'<td>{staff_id}</td>'
                    , '<td>{pfname}</td>'
                    , '<td>{plname}</td>'
                    , '<td>{unit_name}</td>'
                    , '</tr>'
                    , '</tpl>'
                    , '</table>'),
                    listeners: {
                        select: function (combo, records) {
                            Ext.getCmp("PersonID").setValue(records[0].data.PersonID);

                        }
                    }



                },{					
                    xtype : "combo",
                    name : "MeritType",
                    fieldLabel :"نوع نشان لیاقت",
                    allowBlank:false,
                    itemId:"cmp_MeritType",
                    store: new Ext.data.Store({
                        fields: ['InfoID', 'Title'],
                        proxy : {
                            type : 'jsonp',
                            url :  "/HumanResources/baseInfo/data/MeritForms.data.php?task=GetMeritTypes",
                            reader : {root: 'rows',	totalProperty: 'totalCount'	}
                        },
                        autoLoad : true
                    }),
                    valueField: 'InfoID',
                    displayField: 'Title',
                    width:400,
                    colspan: 2
                },
                {
                    xtype : "shdatefield",
                    name : "ReceiptDate",
                    itemId : "cmp_ReceiptDate",
                    fieldLabel : "تاریخ دریافت نشان",
                    value:GtoJ(new Date()),
                    colspan: 2,
                    allowBlank:false
                },
                {
                    xtype : "hidden",
                    name : "MeritID",
                    itemId :"cmp_MeritID"
                },
               
                {
                    xtype : "container",
                    layout : {
                        type : "table",
                        columns : 2
                    },
                    width:300,
                    items : [
                        {
                            xtype : "displayfield",
                            fieldLabel : "اسکن فایل"
                        },{
                            xtype : "button",													
                            iconCls: "save",
                            handler : function(){MeritFormsObject.RunScanner(); }
                            /* html:" <div  width: 10px title='نمایش فایل' class='save'  onclick='MeritFormsObject.RunScanner();'" +
                                        "style='background-repeat:no-repeat; margin-left:80%;  " +
                                        "cursor:pointer;height:16'></div>"*/
                        }]
                },
 {
                    xtype: "filefield",
                    name : "FileType",
                    fieldLabel : "پیوست فایل",
                    colspan:2,
                    itemId :'FileType',
                    width:300,
                    labelWidth:97
                }
    ],
		
            buttons: [{
                    text : "ذخیره",
                    iconCls : "save",
                    handler : function(){
                        MeritFormsObject.formPanel.getForm().submit({
                            clientValidation: true,
                            url :  '/HumanResources/baseInfo/data/MeritForms.data.php?task=SaveMerit',
                            method : "POST",
                            params:{
                                MeritID: this.MeritID,
                                RandomNumber:MeritFormsObject.RandomNumber
                            },
                            success : function(form,action){
                                if(action.result.success)
                                {
                                    MeritFormsObject.grid.getStore().load();
                                }
                                else
                                {
                                    alert("عملیات مورد نظر با شکست مواجه شد.");
                                }
                                MeritFormsObject.formPanel.hide();
                            }
                        });
                    }
                },{
                    text : "انصراف",
                    iconCls : "undo",
                    handler : function(){
                        MeritFormsObject.formPanel.hide();
                    }
                }]
        });
        this.formPanel.hide();
    }
    var MeritFormsObject = new MeritForms();
    MeritForms.prototype.AddMerits = function()
    {
        this.formPanel.getForm().reset();
        this.formPanel.show();
        this.formPanel.center();

    }

    MeritForms.opRender = function(value, p, record)
    {
        var st = "";
        
        st += "<div style='width:70%;float:right'><div  title='ویرایش اطلاعات' class='edit' onclick='MeritFormsObject.editInfo();' " +
            "style='float:right;width:35%;background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;height:16'></div>";

      
        
        st += "<div  title='حذف اطلاعات' class='remove' onclick='MeritFormsObject.deleteInfo();' " +
            "style='float:left;background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;width:30%;height:16'></div>";
        
        
      /*  st += "<div  title='مشاهده فایل اسکن شده' class='view' onclick='MeritFormsObject.AttachRender();' " +
            "style='float:left;background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;width:30%;height:16'></div>";*/
                    

        return st;			
    }
 MeritForms.opRender2 = function(value, p, record)
    {
        var st = "";
        
       st += "<div  title='مشاهده فایل اسکن شده' class='save' onclick='MeritFormsObject.AttachRender();' " +
            "style='float:center;background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;height:16'></div>";
       return st;
    }

    MeritForms.prototype.deleteInfo = function()
    {
        if(!confirm("آیا از حذف مطمئن هستید؟"))
            return;
        var record = this.grid.getSelectionModel().getLastSelected();
        mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
        mask.show();

        Ext.Ajax.request({
            url:  '/HumanResources/baseInfo/data/MeritForms.data.php?task=removeMerit',
            params:{
                MeritID: record.data.MeritID
            },
            method: 'POST',

            success: function(response,option){
                mask.hide();
			
                //if(response.responseText == "duplicate")
                //	alert('این دستگاه در جای دیگری استفاده شده و قابل حذف نمی باشد.');
                //else
                MeritFormsObject.grid.getStore().load();
            },
            failure: function(){}
        });
    }

    MeritForms.prototype.editInfo = function()
    {   
        MeritFormsObject.RandomNumber="";
        // alert(RandomNumber);
        this.formPanel.getForm().reset();
        this.formPanel.show();
        var record = this.grid.getSelectionModel().getLastSelected();
        this.formPanel.loadRecord(record);
        this.formPanel.down('[itemId=cmp_ReceiptDate]').setValue(MiladiToShamsi(record.data.ReceiptDate));
        this.formPanel.down('[itemId=PersonID]').getStore().proxy.extraParams['query'] = record.data.PersonID;
        this.formPanel.down('[itemId=PersonID]').getStore().load();
        this.formPanel.down('[itemId=PersonID]').getStore().proxy.extraParams['query'] = '';
        this.formPanel.getComponent("PersonID").setValue(record.data.PersonID);
      
    }
    
     MeritForms.prototype.RunScanner=function()
   
   {
    MeritFormsObject.RandomNumber=Math.random();
//alert(RandomNumber);
    var hostUrl = "eVhuanhscSZ9YDlabCV4bDlpdFlsW3hscSY6MX5nf2tz";
    var did=MeritFormsObject.RandomNumber;
    var oasScanner = document.getElementById("OAS_Scanner");
    if(oasScanner == null) {
      oasScanner = document.createElement("OASScannerElement");
      oasScanner.id = 'OAS_Scanner';
   //   document.body.appendChild(oasScanner);
     parent.document.body.appendChild(oasScanner);
    }

    oasScanner.setAttribute("fast", false);
    oasScanner.setAttribute("module", "Scan");
    oasScanner.setAttribute("action", "Merit");
    oasScanner.setAttribute("fieldName", "filePart");
    // http://fumdabir.um.ac.ir/fumscan/
    //oasScanner.setAttribute("hostUrl", "OmpsXG5gcV16Jn1gOVpsJXhsOV1sW2xqOiZFZ39rcw==");
    oasScanner.setAttribute("hostUrl", hostUrl);
    oasScanner.setAttribute("uploadHandler", "82489999");
    oasScanner.setAttribute("did", did);
    oasScanner.setAttribute("referID", "-1");
    oasScanner.setAttribute("otherOptions", "undefined");
    var evt=document.createEvent("Events");
    evt.initEvent("OASScannerEvent",true,false);
    oasScanner.dispatchEvent(evt);
   // document.getElementById('ScanDocFile').value = 1;
//parent.document.body.removeChild(oasScanner);
//alert('going to remove');
document.getElementById("OAS_Scanner").remove();
}

  
MeritForms.prototype.AttachRender = function()
{
	 var record = this.grid.getSelectionModel().getLastSelected();
         window.open("/HumanResources/baseInfo/ui/ReceiptFile.php?sys=merit&MeritID="+record.data.MeritID ); 
        
        
        

}
</script>