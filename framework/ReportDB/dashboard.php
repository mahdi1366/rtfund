<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 96.05
//-----------------------------

require_once '../header.inc.php';
require_once './ReportDB.class.php';

?>
<script>
Dashboard.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	masks : [], 
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function Dashboard(){
	
	this.MainPanel = new Ext.panel.Panel({
		frame : false,
		style : "margin : 10px",
		renderTo : this.get("div1"),
		border : false,
		layout : "column",
		columns : 2,
		width : 780,
		defaults : {
			width : 370,
			height : 320,
			style : "margin : 10px"
		}
	});
	
	this.MainStore = new Ext.data.Store({
		proxy:{
			type: 'jsonp',
			url: this.address_prefix + 'ReportDB.data.php?task=SelectReports&IsDashboard=YES',
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields :  ["ReportID", "title", "reportPath"],
		autoLoad : true,
		listeners : {
			load : function(){
				for(i=0; i < this.totalCount; i++)
				{
					record = this.getAt(i);
					DashboardObj.MainPanel.add({
						xtype : "fieldset",
						style : "direction:ltr;text-align:center",
						autoscroll : true,
						title : record.data.title,
						itemId : "cmp_panel_" + record.data.ReportID ,
						loader : {
							url : "../" + record.data.reportPath + "?dashboard_show=true",
							scripts : true
						}
					});
					
					el = DashboardObj.MainPanel.getComponent("cmp_panel_" + record.data.ReportID);
					DashboardObj.masks["ReportID" + record.data.ReportID] = 
						new Ext.LoadMask(el, {msg:'در حال بارگذاری ...'});
					DashboardObj.masks["ReportID" + record.data.ReportID].show();
					el.loader.load({
						params : {
							rpcmp_ReportID : record.data.ReportID
						},
						callback : function(a,b,c,options){
							DashboardObj.masks["ReportID" + options.params.rpcmp_ReportID].hide();
						}
					});
				}
			}
		}
	})
	
}

DashboardObj = new Dashboard();

</script>
<div id="div1" ></div>