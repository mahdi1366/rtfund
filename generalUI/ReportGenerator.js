//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1396.05
//-----------------------------

ReportGenerator.prototype = {
	
	
};

ReportGenerator.FieldPrefix = "reportcolumn_fld_";
ReportGenerator.OrderPrefix = "reportcolumn_ord_";

function ReportGenerator()
{
	
}

ReportGenerator.ShowReportDB = function(obj, MenuID, mainForm, formPanel){

	if(!obj.ReportWin)
	{
		obj.ReportWin = new Ext.window.Window({
			title: 'سابقه گردش درخواست',
			modal : true,
			autoScroll : true,
			width: 500,
			height : 480,
			closeAction : "hide",
			loader : {
				url : "/framework/ReportDB/ReportDB.php",
				scripts : true
			},
			buttons : [{
					text : "بازگشت",
					iconCls : "undo",
					handler : function(){
						this.up('window').hide();
					}
				}]
		});
		Ext.getCmp(obj.TabID).add(obj.ReportWin);
		
	}
	obj.ReportWin.show();
	obj.ReportWin.center();
	obj.ReportWin.loader.load({
		params : {
			MenuID : MenuID,
			SourceObject : ObjectName(obj),
			ExtTabID : obj.ReportWin.getEl().id,
			mainForm : mainForm,
			formPanel : formPanel
		}
	});
}

ReportGenerator.setOrder = function(elem, mainForm, obj){

	var orderInputId = elem.name.replace(ReportGenerator.FieldPrefix, ReportGenerator.OrderPrefix);
	
	if(elem.checked)
	{
		var order = 0;
		var elems = obj.get(mainForm).getElementsByTagName("input");
		for(var i=0; i < elems.length; i++)
			if(elems[i].id.indexOf(ReportGenerator.OrderPrefix) != -1 && elems[i].value != "" && elems[i].value*1 > order)
				order = elems[i].value*1;
		
		var el = obj.get(orderInputId);
		if(el.value == null || el.value == "")
			el.value = order+1;
	}
	else
	{
		obj.get(orderInputId).value = "";
	}

}