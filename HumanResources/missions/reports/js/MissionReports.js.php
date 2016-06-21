<script type="text/javascript">
    //-----------------------------
    //	Programmer	: Fatemipour
    //	Date		: 94.03
    //-----------------------------

    Miss_report.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix : "<?= $js_prefix_address ?>",
       
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };
       
        
    function Miss_report()
    { 
        Ext.create('Ext.form.Panel', {
            width: 300,
            bodyPadding: 10,
            renderTo: this.get('MissDate'),
            border : false,
            items: [{
                    xtype: 'shdatefield',
                    anchor: '100%',
                    fieldLabel: 'از ',                    
                    name: 'from_date' ,
                    labelStyle : 'font-size:11px; font-family:tahoma; '
                }, {
                    xtype: 'shdatefield',
                    anchor: '100%',
                    labelStyle : 'font-size:11px; font-family:tahoma; ',
                    fieldLabel: 'لغایت',
                    name: 'to_date'                    
                }]            
        });

        
        this.form = this.get("form_MissReport");
        this.MissReportPanel = new Ext.Panel({
            applyTo: this.get("MissReportDIV"),
            frame : true,
            bodyStyle : "padding:5px",
            style : "margin-top:5px",
            contentEl : this.get("MissReportPNL"),
            title : " تنظیم گزارش",
            width : 700,
            buttons:[{
                    text : "مشاهده گزارش",
                    iconCls : "list",
                    handler : function(){Miss_reportObj.showResult('show');}
                },{
                    text : "خروجی excel",
                    iconCls : "excel",
                    handler : function(){Miss_reportObj.showResult('excel');}
                }]
        });
        Ext.get(this.get("MissReportPNL")).addKeyListener(13, function(){Miss_reportObj.showResult('show');});


    }
            
    Miss_reportObj = new Miss_report();
          
    Miss_report.prototype.showResult = function(type)
    {
        this.form.target = "_blank";
        this.form.method = "POST";
        this.form.action =  this.address_prefix + "../data/MissionReports.data.php";
        this.form.action += type == "excel" ? "?excel=true" : "";        
        this.form.submit();
        return;
    }
    /*var info = Ext.encode(this.up("form").getValues());
                    window.open(Miss_reportObj.address_prefix + "../data/reports.data.php?info="+info);            */
    
    /*
    
    Miss_reportObj.StatusStore.each(function(record,idx){
 alert(record.get('title'));
  {
   xtype:'checkboxfield',
   name : "" + record.get('InfoID') ,  
   boxLabel : recrd.get('title')
  }
});
     */

</script>