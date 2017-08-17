<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 96.05
//-----------------------------

require_once '../header.inc.php';

?>
<script>
FRW_ReportDB.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	MenuID : '<?= $_REQUEST["MenuID"] ?>',
	SourceObject : <?= $_REQUEST["SourceObject"] ?>,
	mainForm : "<?= $_REQUEST["mainForm"] ?>",
	formPanel : "<?= $_REQUEST["formPanel"] ?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function FRW_ReportDB(){
	
	this.runPanel = new Ext.form.Panel({
		renderTo : this.get("div1"),
		frame : false,
		width : 485,
		border : false,
		bodyStyle : "padding: 0 10px 0 10px",
		items :[{
			xtype : "fieldset",
			title : "اجرای گزارش",
			items : [{
				xtype : "combo",
				store : new Ext.data.Store({
					proxy:{
						type: 'jsonp',
						url: this.address_prefix + 'ReportDB.data.php?task=SelectReports&MenuID=' + this.MenuID,
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields :  ["ReportID", "title"],
					autoLoad : true
				}),
				displayField: 'title',
				valueField : "ReportID",
				width : 430,
				queryMode : 'local',
				name : "ReportID",
				allowBlank : false,
				fieldLabel : "انتخاب گزارش"
			},{
				xtype : "button",
				style : "float:left",
				iconCls : "report",
				text : "اجرای گزارش",
				handler : function(){ FRW_ReportDBObj.ShowReport(); }
			}]
		}]
	});
	
	this.addPanel = new Ext.form.Panel({
		renderTo : this.get("div2"),
		frame : false,
		width : 485,
		border : false,
		bodyStyle : "padding: 0 10px 0 10px",
		items :[{
			xtype : "fieldset",
			title : "ایجاد گزارش",
			items : [{
				xtype : "textfield",
				fieldLabel : "عنوان گزارش",
				width : 430,
				allowBlank : false,
				name : "ReportDBTitle"
			},{
				xtype : "button",
				iconCls : "save",
				style : "float:left",
				text : "ذخیره گزارش",
				handler : function(){ FRW_ReportDBObj.AddReport(); }
			}]
		}]
	});
	
	this.editPanel = new Ext.form.Panel({
		renderTo : this.get("div3"),
		frame : false,
		width : 485,
		border : false,
		bodyStyle : "padding: 0 10px 0 10px",
		items :[{
			xtype : "fieldset",
			title : "تغییر گزارش",
			items : [{
				xtype : "combo",
				store : new Ext.data.Store({
					proxy:{
						type: 'jsonp',
						url: this.address_prefix + 'ReportDB.data.php?task=SelectReports&MenuID=' + this.MenuID,
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields :  ["ReportID", "title"],
					autoLoad : true
				}),
				displayField: 'title',
				valueField : "ReportID",
				name : "ReportID",
				queryMode : 'local',
				allowBlank : false,
				width : 430,
				fieldLabel : "انتخاب گزارش"
			},{
				xtype : "textfield",
				width : 430,
				allowBlank : false,
				fieldLabel : "عنوان جدید",
				name : "ReportDBTitle"
			},{
				xtype : "button",
				style : "float:left",
				iconCls : "edit",
				text : "ویرایش عنوان گزارش",
				handler : function(){ FRW_ReportDBObj.EditReport(false); }
			},{
				xtype : "button",
				style : "float:left",
				iconCls : "edit",
				text : "ویرایش تنظیمات گزارش",
				handler : function(){ FRW_ReportDBObj.EditReport(true); }
			}]
		}]
	});
	
	this.deletePanel = new Ext.form.Panel({
		renderTo : this.get("div4"),
		frame : false,
		width : 485,
		border : false,
		bodyStyle : "padding: 0 10px 0 10px",
		items :[{
			xtype : "fieldset",
			title : "حذف گزارش",
			items : [{
				xtype : "combo",
				store : new Ext.data.Store({
					proxy:{
						type: 'jsonp',
						url: this.address_prefix + 'ReportDB.data.php?task=SelectReports&MenuID=' + this.MenuID,
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields :  ["ReportID", "title"],
					autoLoad : true
				}),
				displayField: 'title',
				valueField : "ReportID",
				width : 430,
				name : "ReportID",
				queryMode : 'local',
				allowBlank : false,
				fieldLabel : "انتخاب گزارش"
			},{
				xtype : "button",
				style : "float:left",
				iconCls : "remove",
				text : "حذف گزارش",
				handler : function(){ FRW_ReportDBObj.DeleteReport(); }
			}]
		}]
	});
}

FRW_ReportDBObj = new FRW_ReportDB();

FRW_ReportDB.prototype.ShowReport = function(){
	
	if(!this.ReportItemsStore)
	{
		this.ReportItemsStore = new Ext.data.SimpleStore({
			proxy:{
				type: 'jsonp',
				url: this.address_prefix + 'ReportDB.data.php?task=SelectReportItems',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields :  ["ElemName", "ElemValue"],
			listeners : {
				load : function(){
					
					me = FRW_ReportDBObj;
					for(i=0; i<this.totalCount; i++)
					{
						record = this.getAt(i);
						el = me.SourceObject[me.formPanel].down("[name=" + record.data.ElemName + "]");
						if(!el)
							el = me.SourceObject[me.formPanel].down("[hiddenName=" + record.data.ElemName + "]");
						if(!el)
						{
							el = me.SourceObject.get(record.data.ElemName);
							if(el.type == "checkbox")
								el.checked = record.data.ElemValue == "false" ? false : true;
							else	
								el.value = record.data.ElemValue;
							//------------ radio ---------------
							el = me.SourceObject.get(record.data.ElemName + "-" + record.data.ElemValue)
							if(el)
								el.checked = true;
							//----------------------------------
							continue;
						}
						el.setValue(record.data.ElemValue);
					}
					
					me.SourceObject.ReportWin.hide();
				}
			}
		});
	}
	
	if(!this.runPanel.getForm().isValid())
		return;
	
	this.ReportItemsStore.load({
		params : { ReportID : this.runPanel.down("[name=ReportID]").getValue() }
	});
	
}

FRW_ReportDB.prototype.AddReport = function(){
	
	if(!this.addPanel.getForm().isValid())
		return;
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	params = new Ext.FormSerializer(this.SourceObject.get(this.mainForm)).toObject();
	params = mergeObjects(params, this.addPanel.getForm().getValues());
	params.MenuID = this.MenuID;
	params.EditItems = "YES";
	//--------- multiseects ---------------
	multiElems = this.SourceObject[this.formPanel].query('multiselect');
	for(i=0; i<multiElems.length; i++)
		if(multiElems[i].getValue().toString() != "")
			params[multiElems[i].name] = multiElems[i].getValue().toString();
	//-------------------------------------

	Ext.Ajax.request({
		url: this.address_prefix +'ReportDB.data.php?task=SaveReport',
		method: "POST",
		form : this.get(this.mainForm),
		params: params,
		success: function(response){
			result = Ext.decode(response.responseText);
			mask.hide();
			if(result.success)
				Ext.MessageBox.alert("","گزارش مورد نظر با موفقیت ذخیره شد")
			else
				Ext.MessageBox.alert("ERROR","عملیات مورد نظر با شکست مواجه گردید")
			FRW_ReportDBObj.SourceObject.ReportWin.hide();
		}
	});
	
}

FRW_ReportDB.prototype.EditReport = function(EditAll){
	
	if(!EditAll)
		if(!this.editPanel.getForm().isValid())
			return;
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	if(!EditAll)
	{
		params = {
			ReportID : this.editPanel.down("[name=ReportID]").getValue(),
			MenuID : this.MenuID,
			EditItems : "NO",
			ReportDBTitle : this.editPanel.down("[name=ReportDBTitle]").getValue()
		};
	}
	else
	{
		params = new Ext.FormSerializer(this.SourceObject.get(this.mainForm)).toObject();
		params = mergeObjects(params, this.addPanel.getForm().getValues());
		params.MenuID = this.MenuID;
		params.EditItems = "YES";
		params.ReportID = this.editPanel.down("[name=ReportID]").getValue();
		params.ReportDBTitle = this.editPanel.down("[name=ReportDBTitle]").getValue();
		//--------- multiseects ---------------
		multiElems = this.SourceObject[this.formPanel].query('multiselect');
		for(i=0; i<multiElems.length; i++)
			if(multiElems[i].getValue().toString() != "")
				params[multiElems[i].name] = multiElems[i].getValue().toString();
		//-------------------------------------
	}
	
	Ext.Ajax.request({
		url: this.address_prefix +'ReportDB.data.php?task=SaveReport',
		method: "POST",
		form : this.get(this.mainForm),
		params: params,
		success: function(response){
			mask.hide();
			FRW_ReportDBObj.SourceObject.ReportWin.hide();
		}
	});
	
}

FRW_ReportDB.prototype.DeleteReport = function(){
	
	if(!this.deletePanel.getForm().isValid())
		return;
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'ReportDB.data.php?task=DeleteReport',
		method: "POST",
		form : this.get(this.mainForm),
		params: {
			ReportID : this.deletePanel.down("[name=ReportID]").getValue()
		},
		success: function(response){
			result = Ext.decode(response.responseText);
			mask.hide();
			if(result.success)
				Ext.MessageBox.alert("","گزارش مورد نظر با موفقیت حذف شد")
			else
				Ext.MessageBox.alert("ERROR","عملیات مورد نظر با شکست مواجه گردید")
			
			FRW_ReportDBObj.SourceObject.ReportWin.hide();
		}
	});
	
}

</script>
<div id="div1" ></div>
<div id="div2" ></div>
<div id="div3" ></div>
<div id="div4" ></div>