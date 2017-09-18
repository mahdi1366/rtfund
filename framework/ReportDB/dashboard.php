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
		width : 1000
	});
	
	this.MainStore = new Ext.data.Store({
		proxy:{
			type: 'jsonp',
			url: this.address_prefix + 'ReportDB.data.php?task=SelectReports&IsManagerDashboard=YES',
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
						width : 980, 
						style : "margin : 10px",
						style : "direction:ltr;text-align:center",
						autoScroll : true,
						collapsible : true,
						collapsed : true,
						title : record.data.title,
						itemId : "cmp_panel_" + record.data.ReportID ,
						loader : {
							url : "../" + record.data.reportPath + "?dashboard_show=true",
							scripts : true
						},
						listeners : {
							expand : function(el){
								ReportID = el.itemId.replace("cmp_panel_", "");
								el.setHeight(350);
								if(!el.loader.isLoaded)
								{
									DashboardObj.masks["ReportID" + ReportID] = 
										new Ext.LoadMask(el, {msg:'در حال بارگذاری ...'});
									DashboardObj.masks["ReportID" + ReportID].show();
									el.loader.load({
										params : {
											rpcmp_ExtTabID : el.getEl().id,
											rpcmp_ReportID : ReportID
										},
										callback : function(a,b,c,options){
											DashboardObj.masks["ReportID" + options.params.rpcmp_ReportID].hide();
										}
									});
								}
							}
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