<script type="text/javascript">	
    //-----------------------------
    //	Programmer	: A.GHolami
    //	Date		: 93.06
    //-----------------------------
    Report.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix : "<?= $js_prefix_address ?>",

        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };
	
Report.prototype.showReport = function(type)
{ 	
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "ReportEvaluate.php?showRes=1";
	this.form.action += type == "excel" ? "&excel=true" : "";	
	this.form.submit();	
	return;
}
	
function Report(){
	
		/*this.grid = < ?= $grid ?>;
		this.grid.render(this.get("grid"));*/


	this.filterPanel = new Ext.form.Panel({
		renderTo : this.get('mainpanel'),
		width :550,
		//height:200,
		titleCollapse : true,
		frame : true,
		collapsible : true,
		//collapsed : true,
		title : "",
		/*fieldDefaults: {
			labelWidth:120
		},*/
			layout: {
			type: 'table',
			columns: 1,
			tableAttrs: {
				style: {
					width: '100%'
				}
			}
		},
				defaults: {
			anchor: '100%'
			//defaultMargins: {top: 0, right: 0, bottom: 0, left: 0}
			//,labelWidth :72
		},
		fieldDefaults: {
			msgTarget: 'side'
				},
                      items : [{
                            xtype:"textfield" ,
                            fieldLabel: 'نام',
                            name: 'FName', 
                            width:300
                            //hideTrigger:true
                        },
                        {
                            xtype:"textfield" ,
                            fieldLabel: 'نام خانوادگی',
                            name: 'LName', 
                            width:300
                            //hideTrigger:true
                        },

		],
		/*buttons : [{
				iconCls : "save",
				text : "فیلتر لیست",
				handler : function(){
					this.up('form').collapse();
					ReportObject.grid.getStore().load();
					
				}
			},{
				iconCls : "clear",
				text : "پاک کردن فرم",
				handler : function(){
					this.up("form").getForm().reset();
				}
			}]*/
		buttons:[{
			text : "مشاهده گزارش",
			iconCls : "list",
			handler : function(){ReportObject.showReport('show');}
		},{
			text : "خروجی excel",
			iconCls : "excel",
			handler : function(){ReportObject.showReport('excel');}
		}]

	});



}


    Report.OperationRender = function(){
		
        return  "<div title='عملیات' class='setting' onclick='ReportObject.OperationMenu(event);' " +
            "style='background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;height:16'></div>";
    }
	
    
var ReportObject = new Report();


</script>


