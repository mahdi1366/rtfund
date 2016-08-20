<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

AccDocs.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : "<?= $js_prefix_address ?>",

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	CycleIsOpen : true,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function AccDocs()
{
	this.form = this.get("mainForm");

	this.makeInfoWindow();
	this.makeDetailWindow();
	//--------------------------------------------------------------------------
	
	this.checkTafsiliCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields:["TafsiliID","TafsiliDesc"],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=GetAllTafsilis&TafsiliType=1',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			}
		}),
		emptyText:'انتخاب تفصیلی ...',
		typeAhead: false,
		pageSize : 10,
		valueField : "TafsiliID",
		displayField : "TafsiliDesc"
	});
	
	this.accountCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields:["AccountID","AccountDesc","StartNo","EndNo","StartNo2","EndNo2"],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectAccounts',
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad : true			
		}),
		emptyText:'انتخاب حساب ....',
		typeAhead: false,
		valueField : "AccountID",
		displayField : "AccountDesc"
	});
	
	this.ChequeStatusCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			autoLoad : true,
			proxy:{
				type: 'jsonp',
				url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectChequeStatuses',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields :  ['InfoID','InfoDesc']
		}),
		displayField: 'InfoDesc',
		valueField : "InfoID",
		queryMode: "local"
	});

	//--------------------------------------------------------------------------
	
	this.mainTab = new Ext.TabPanel({
        renderTo: this.get("div_tab"),
        activeTab: 0,		
        plain: true,
        defaults :{
            autoScroll: true,
            bodyPadding: 10
        },
        items: [{
			title: 'ردیف های سند',
			contentEl : this.get("tabitem_rows")
		},{
			title : "چک های سند",
			listeners :{
				activate : function(){
					var hrecord = AccDocsObject.grid.getStore().getAt(0);
					if(!hrecord)
						return;
					if(!AccDocsObject.checkGrid.rendered)
					{
						AccDocsObject.checkGrid.getStore().proxy.extraParams = {
							DocID : hrecord.data.DocID
						};
						this.add(AccDocsObject.checkGrid);
						return;
					}
					if(AccDocsObject.grid.getStore().proxy.extraParams.DocID != hrecord.data.DocID)
					{
						AccDocsObject.checkGrid.getStore().proxy.extraParams = {
							DocID : hrecord.data.DocID
						};
						AccDocsObject.checkGrid.getStore().load();
					}
				}
			}
		}]
	});
			
	this.summaryFS = new Ext.panel.Panel({
		renderTo : this.get("fs_summary"),
		width : 780,
		height : 40,
		frame : true,
		style : "font-weight:bold",
		layout :{
			type : "table",
			columns : 3
		},
		items :[{
				xtype : "displayfield",
				fieldLabel :"جمع بدهکار",
				labelWidth : 100,
				itemId : "cmp_bd",
				width : 250
			},{
				xtype : "displayfield",
				fieldLabel : "جمع بستانکار",
				labelWidth : 100,
				itemId : "cmp_bs",
				width : 250
			},{
				xtype : "container",
				html : '<div align="center" style="height: 26px;font-family: b titr;font-size: 14px;background-color:#FFB8C9;border : 1px solid red">سند تراز نمی باشد</div>',
				itemId : "cmp_balance",
				width : 200
			}]
	}); 
}

AccDocs.prototype.operationhMenu = function(e){

	var record = null;
	if(AccDocsObject.grid.getStore().count() > 0) 
	{    
		record = AccDocsObject.grid.getStore().getAt(0);
	}        
	var op_menu = new Ext.menu.Menu();
	if(this.CycleIsOpen)
	{
		if(this.AddAccess)
        {
			op_menu.add({text: 'ایجاد سند',iconCls: 'add', 
				handler : function(){ return AccDocsObject.AddDoc(); }})
		}

		if(record != null && record.data.DocStatus == "RAW")
		{
			if(this.EditAccess)
			{
				op_menu.add({text: 'ویرایش سند',iconCls: 'edit', 
					handler : function(){ return AccDocsObject.EditDoc(); } });
				
				op_menu.add({text: 'تایید سند',iconCls: 'tick', 
					handler : function(){ return AccDocsObject.confirmDoc(); } });
			}
			if(this.RemoveAccess && this.grid.getStore().currentPage == this.grid.getStore().totalCount)
				op_menu.add({text: 'حذف سند',iconCls: 'remove', 
					handler : function(){ return AccDocsObject.RemoveDoc(); } });
		}
		if(record != null && record.data.DocStatus == "CONFIRM" && this.EditAccess)
		{
			op_menu.add({text: 'برگشت از تایید',iconCls: 'undo', 
				handler : function(){ return AccDocsObject.UndoConfirmDoc(); } });
			
			op_menu.add({text: 'قطعی کردن سند',iconCls: 'archive', 
				handler : function(){ return AccDocsObject.archiveDoc(); } });
		}
	}
    if(record != null)           
		op_menu.add({text: 'چاپ سند',iconCls: 'print', 
			handler : function(){ return AccDocsObject.PrintDoc(); } });

	op_menu.showAt([e.getEl().getX()-60, e.getEl().getY()+20]);
}

AccDocs.prototype.makeInfoWindow = function()
{
	this.docWin = new Ext.window.Window({
		title: 'مشخصات سند',
		modal : true,
		width: 400,
		closeAction : "hide",
		items : new Ext.form.Panel({
			plain: true,
			border: 0,
			bodyPadding: 5,

			fieldDefaults: {
				labelWidth: 140
			},
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			items : [{
					xtype : "numberfield",
					fieldLabel: "شماره سند",
					name : "LocalNo",
					itemId : "LocalNo",
					hideTrigger : true
				},{
					xtype : "shdatefield",
					format: 'Y/m/d',
					width : 60,
					value : "<?= DateModules::shNow() ?>",
					fieldLabel: "تاریخ سند",
					name : "DocDate"
				},{
					xtype : "combo",
					store : new Ext.data.Store({
						proxy:{
							type: 'jsonp',
							url: this.address_prefix + 'doc.data.php?task=GetSubjects',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						fields :  ["InfoID", "InfoDesc"]
					}),
					displayField: 'InfoDesc',
					valueField : "InfoID",
					name : "SubjectID",
					fieldLabel : "موضوع سند"
				},{
					xtype : "textarea",
					fieldLabel: "توضیحات",
					name : "description"
				},{
					xtype : "hidden",
					name : "DocID"
				}
			],
			buttons : [
				{
					text : "ذخیره",
					iconCls : "save",
					handler : function(){ AccDocsObject.SaveDoc();	}
				},
				{
					text : "انصراف",
					iconCls : "undo",
					handler : function(){
						AccDocsObject.docWin.hide();
					}
				}
			]
		})
	});
}

AccDocs.prototype.makeDetailWindow = function()
{
	this.detailWin = new Ext.window.Window({
		title: 'ایجاد ردیف سند',
		modal : true,
		width: 630,
		closeAction : "hide",
		items : new Ext.form.Panel({
			plain: true,
			border: 0,
			bodyPadding: 5,
			fieldDefaults: {
				labelWidth: 100
			},
			layout: {
				type: 'table',
				columns : 2,
				align: 'stretch'
			},
			items : [{
					xtype : "combo",
					width : 610,
					fieldLabel : "کد حساب",
					colspan : 2,
					store: new Ext.data.Store({
						fields:["CostID","CostCode","CostDesc", "TafsiliType","TafsiliType2",{
							name : "fullDesc",
							convert : function(value,record){
								return "[ " + record.data.CostCode + " ] " + record.data.CostDesc
							}				
						}],
						proxy: {
							type: 'jsonp',
							url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectCostCode',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						}
					}),
					typeAhead: false,
					name : "CostID",
					valueField : "CostID",
					displayField : "fullDesc",
					listConfig: {
						loadingText: 'در حال جستجو...',
						emptyText: 'فاقد اطلاعات'
					},
					listeners :{
						select : function(combo,records){
							if(records[0].data.TafsiliType != null)
								AccDocsObject.detailWin.down("[name=TafsiliType]").
									setValue(records[0].data.TafsiliType);
							if(records[0].data.TafsiliType2 != null)
								AccDocsObject.detailWin.down("[name=TafsiliType2]").
									setValue(records[0].data.TafsiliType2);
						}
					}
				},{
					xtype : "combo",
					fieldLabel : "گروه تفصیلی",
					store: new Ext.data.Store({
						fields:["InfoID","InfoDesc"],
						proxy: {
							type: 'jsonp',
							url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectTafsiliGroups',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						autoLoad : true
					}),
					typeAhead: false,
					queryMode : "local",
					name : "TafsiliType",
					valueField : "InfoID",
					displayField : "InfoDesc",
					listeners : {
						select : function(combo,records){
							combo = AccDocsObject.detailWin.down("[name=TafsiliID]");
							combo.setValue();
							combo.getStore().proxy.extraParams["TafsiliType"] = this.getValue();
							combo.getStore().load();
						}
					}
				},
				{
					xtype : "combo",
					width : 350,
					fieldLabel : "تفصیلی",
					store: new Ext.data.Store({
						fields:["TafsiliID","TafsiliCode","TafsiliDesc",{
							name : "fullDesc",
							convert : function(v,r){
								return "[" + r.data.TafsiliCode + "]" + r.data.TafsiliDesc;
							}
						}],
						proxy: {
							type: 'jsonp',
							url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=GetAllTafsilis',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						listeners : {
							beforeload : function(store){
								if(!store.proxy.extraParams.TafsiliType)
								{
									group = AccDocsObject.detailWin.down("[name=TafsiliType]").getValue();
									if(group == "")
										return false;
									this.proxy.extraParams["TafsiliType"] = group;
								}
							}
						}
					}),
					typeAhead: false,
					pageSize : 10,
					name : "TafsiliID",
					valueField : "TafsiliID",
					displayField : "fullDesc"
				},{
					xtype : "combo",
					fieldLabel : "گروه تفصیلی 2",
					store: new Ext.data.Store({
						fields:["InfoID","InfoDesc"],
						proxy: {
							type: 'jsonp',
							url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectTafsiliGroups',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						autoLoad : true
					}),
					typeAhead: false,
					queryMode : "local",
					name : "TafsiliType2",
					valueField : "InfoID",
					displayField : "InfoDesc",
					listeners : {
						select : function(combo,records){
							combo = AccDocsObject.detailWin.down("[name=TafsiliID2]");
							combo.setValue();
							combo.getStore().proxy.extraParams["TafsiliType"] = this.getValue();
							combo.getStore().load();
						}
					}
				},{
					xtype : "combo",
					fieldLabel : "تفصیلی 2",
					width : 350,
					store: new Ext.data.Store({
						fields:["TafsiliID","TafsiliCode","TafsiliDesc",{
							name : "fullDesc",
							convert : function(v,r){
								return "[" + r.data.TafsiliCode + "]" + r.data.TafsiliDesc;
							}
						}],
						proxy: {
							type: 'jsonp',
							url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=GetAllTafsilis',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						listeners : {
							beforeload : function(store){
								if(!store.proxy.extraParams.TafsiliType)
								{
									group = AccDocsObject.detailWin.down("[name=TafsiliType2]").getValue();
									if(group == "")
										return false;
									this.proxy.extraParams["TafsiliType"] = group;
								}
							}
						}
					}),
					typeAhead: false,
					pageSize : 10,
					name : "TafsiliID2",
					valueField : "TafsiliID",
					displayField : "fullDesc"
				},{
					xtype : "currencyfield",
					fieldLabel : "مبلغ بدهکار",
					name : "DebtorAmount",
					hideTrigger : true
				},{
					xtype : "currencyfield",
					fieldLabel : "مبلغ بستانکار",
					name : "CreditorAmount",
					hideTrigger : true
				},{
					xtype : "textfield",
					fieldLabel : "شرح",
					name : "details",
					colspan : 2,
					width : 610
				},{
					xtype : "hidden",
					name : "ItemID"
				}
			],
			buttons : [
				{
					text : "ذخیره",
					iconCls : "save",
					handler : function(){ AccDocsObject.SaveItem();	}
				},
				{
					text : "انصراف",
					iconCls : "undo",
					handler : function(){
						AccDocsObject.detailWin.hide();
					}
				}
			]
		})
	});
	
}

var AccDocsObject = new AccDocs();

AccDocs.docRender = function(v,p,record)
{
	SubjectDesc = record.data.SubjectDesc == null ? "" : record.data.SubjectDesc;
	description = record.data.description == null ? "" : record.data.description;
	
	return "<table class='docInfo' width=100%>"+
		"<tr>"+
			"<td width=25%>شماره سند : <span class='blueText'>" + record.data.LocalNo + "</td>" +
			"<td width=25%>ثبت کننده سند : <span class='blueText'>" + record.data.regPerson + "</td>" +
			"<td width=25%>تاریخ سند : <span class='blueText'>" + MiladiToShamsi(record.data.DocDate) + "</td>" +
			"<td width=25%>نوع سند : <span class='blueText'>" + record.data.DocTypeDesc + "</td>" +
		"</tr>" + 
		"<tr>" +			
			"<td>موضوع : <span class='blueText'>" + SubjectDesc + "</td>" +
			"<td colspan=3>توضیحات : <span class='blueText' colspan=5>" + description + "</td>" +
		"</tr>" +
		"</table>";
}

AccDocs.prototype.afterHeaderLoad = function(store)
{
	if(store.getAt(0))
	{
		AccDocsObject.itemGrid.show();
		AccDocsObject.showDetail(store.getAt(0));
	}
	else
		AccDocsObject.itemGrid.hide();
}

AccDocs.prototype.AddDoc = function()
{
	AccDocsObject.docWin.down("form").getForm().reset();
	AccDocsObject.docWin.show();
	this.docWin.center();
	
	mask = new Ext.LoadMask(this.docWin, {msg:'در حال بارگذاری ...'});
	mask.show()
	
	Ext.Ajax.request({
		url : this.address_prefix + "doc.data.php?task=GetLastLocalNo",
		method : "post",
		
		success : function(response){
			AccDocsObject.docWin.down("[name=LocalNo]").setValue(response.responseText);
			mask.hide();
		}
	});
	
}

AccDocs.prototype.EditDoc = function()
{
	AccDocsObject.docWin.down("form").getForm().reset();
	this.docWin.show();
	this.docWin.center();

	var record = this.grid.getStore().getAt(0);
	record.data.DocDate = MiladiToShamsi(record.data.DocDate);
	this.docWin.down("form").loadRecord(record);

}

AccDocs.prototype.SaveDoc = function(){
	
	this.docWin.down('form').getForm().submit({
		clientValidation: true,
		url: AccDocsObject.address_prefix + 'doc.data.php?task=saveDoc',
		method : "POST",

		success : function(form,action){
			AccDocsObject.docWin.hide();
			AccDocsObject.grid.getStore().proxy.extraParams["query"] = "";
			if(AccDocsObject.docWin.down("[name=DocID]").getValue() != "")
				AccDocsObject.grid.getStore().load();
			else
				AccDocsObject.grid.getStore().loadPage(AccDocsObject.grid.getStore().totalCount+1);
		},
		failure : function(form,action)
		{
			if(action.result.data != "")
				Ext.MessageBox.alert("Error",action.result.data);
			else
				Ext.MessageBox.alert("Error","عملیات مورد نظر با شکست مواجه شد");
		}
	});
}

AccDocs.prototype.RemoveDoc = function()
{
	var record = this.grid.getStore().getAt(0);

	if(record.data.DocStatus == "DELETED")
	{
		Ext.MessageBox.alert("","این سند قبلا حذف شده است");
		return;
	}
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		
		if(btn == "no")
			return;
		me = AccDocsObject;
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف...'});
		mask.show();


		Ext.Ajax.request({
			url: me.address_prefix + 'doc.data.php?task=removeDoc',
			params:{
				DocID: record.data.DocID
			},
			method: 'POST',

			success: function(response){
				mask.hide();
				result = Ext.decode(response.responseText);
				if(!result.success)
				{
					Ext.MessageBox.alert("Error", "عملیات مورد نظر با شکست مواجه شد");
					return;
				}
				var currentPage = AccDocsObject.grid.getStore().currentPage;
				var totalPages = AccDocsObject.grid.getStore().totalCount;

				if(currentPage != totalPages)
					AccDocsObject.grid.getStore().loadPage(currentPage);
				else if(totalPages == 0)
					AccDocsObject.grid.getStore().loadPage(1);
				else
					AccDocsObject.grid.getStore().loadPage(totalPages-1);
			},
			failure: function(){}
		});
	});
}

AccDocs.prototype.confirmDoc = function()
{
	var record = this.grid.getStore().getAt(0);

	var r = this.itemGrid.getStore().getProxy().getReader().jsonData;
	r = r.message.split(',');
	
	if(r[0] != r[1])
	{
		Ext.MessageBox.alert("","به دلیل تراز نبودن سند قادر به تایید آن نمی باشید");
		return
	}
	
	if(r[0]*1 == 0)
	{
		Ext.MessageBox.alert("","سند فاقد مبلغ می باشد");
		return
	}
	Ext.MessageBox.confirm("","پس از تایید سند دیگر قادر به ویرایش و یا حذف سند نمی باشید."	, function(btn){
		if(btn == "no")
			return;
		
		me = AccDocsObject;
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال تایید سند ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'doc.data.php?task=confirm',
			params:{
				DocID: record.data.DocID
			},
			method: 'POST',

			success: function(response){
				result = Ext.decode(response.responseText);
				mask.hide();
				if(result.success)
					AccDocsObject.grid.getStore().load();
				else
					Ext.MessageBox.alert("Error", 
						result.data == "" ? "عملیات مورد نظر با شکست مواجه شد" : result.data);
			},
			failure: function(){}
		});
	});
}

AccDocs.prototype.UndoConfirmDoc = function()
{
	var record = this.grid.getStore().getAt(0);

	Ext.MessageBox.confirm("","آیا مایل به برگشت از تایید می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = AccDocsObject;
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال تایید سند ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'doc.data.php?task=confirm',
			params:{
				DocID: record.data.DocID,
				undo : "true"
			},
			method: 'POST',

			success: function(response){
				result = Ext.decode(response.responseText);
				mask.hide();
				if(result.success)
					AccDocsObject.grid.getStore().load();
				else
					Ext.MessageBox.alert("Error", 
						result.data == "" ? "عملیات مورد نظر با شکست مواجه شد" : result.data);
			},
			failure: function(){}
		});
	});
}

AccDocs.prototype.archiveDoc = function()
{
	var record = this.grid.getStore().getAt(0);

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال قطعی کردن سند ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + 'doc.data.php?task=archive',
		params:{
			DocID: record.data.DocID
		},
		method: 'POST',

		success: function(response){
			mask.hide();
			AccDocsObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

AccDocs.prototype.PrintDoc = function()
{
	window.open(this.address_prefix + "print_doc.php?DocID=" + 
		this.grid.getStore().getAt(0).data.DocID);
}

AccDocs.prototype.SearchDoc = function(){

	Ext.Ajax.request({
		url : this.address_prefix + 'doc.data.php?task=GetSearchCount',
		method : 'POST',
		params : {
			Number : this.grid.down("[itemId=Number]").getValue()
		},
		success : function(response){
			var res = Ext.decode(response.responseText);
			if(res.success)
			{
				var totalCount=AccDocsObject.grid.getStore().totalCount;
				if((res.data*1) != totalCount)                                            
					AccDocsObject.grid.getStore().loadPage(res.data*1+1);
				else
					AccDocsObject.grid.getStore().loadPage(res.data*1);    
			}

		},
		failure : function(){}
	});

}    

//.........................................................

AccDocs.prototype.check_deleteRender = function()
{
	var record = AccDocsObject.grid.getStore().getAt(0);
	if(record.data.DocStatus != "RAW")
		return "";
	return  "<div title='حذف اطلاعات' class='remove' onclick='AccDocsObject.check_remove();' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;height:16'></div>";
}

AccDocs.prototype.check_Add = function()
{
	if(this.grid.getStore().getAt(0).data.DocStatus != "RAW")
		return;
	var modelClass = this.checkGrid.getStore().model;
	var record = new modelClass({
		ChequeID: "",
		DocID : this.grid.getStore().getAt(0).data.DocID
	});
	this.checkGrid.plugins[0].cancelEdit();
	this.checkGrid.getStore().insert(0, record);
	this.checkGrid.plugins[0].startEdit(0, 0);
}

AccDocs.prototype.check_Save = function(store,record)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + 'doc.data.php?task=saveChecks',
		method: 'POST',
		params: {
			record : Ext.encode(record.data)
		},
		form : this.get("checkForm"),

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				AccDocsObject.checkGrid.getStore().load();
			}
			else
			{
				if(st.data == "duplicate")
					alert("شماره چک وارد شده تکراری می باشد.");
				else
					alert(st.data);
			}
		},
		failure: function(){}
	});
}

AccDocs.prototype.check_remove = function()
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		
		if(btn == "no")
			return;
		
		me = AccDocsObject;
		var record = me.checkGrid.getSelectionModel().getLastSelected();

		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخيره سازي...'});
		mask.show();


		Ext.Ajax.request({
			url: me.address_prefix + 'doc.data.php?task=removeChecks',
			params:{
				ChequeID: record.data.ChequeID
			},
			method: 'POST',

			success: function(response){
				mask.hide();
				result = Ext.decode(response.responseText);
				if(!result.success)
				{
					Ext.MessageBox.alert("Error", "عملیات مورد نظر با شکست مواجه شد");
					return;
				}
				AccDocsObject.checkGrid.getStore().load();
			},
			failure: function(){}
		});
	});
}

AccDocs.beforeCheckEdit = function(editor,e){
	
	var record = AccDocsObject.grid.getStore().getAt(0);
	if(record.data.DocStatus != "RAW")
		return false;
}

//.........................................................

AccDocs.prototype.showDetail = function(record)
{
	this.mainTab.setActiveTab(0);
	this.itemGrid.getStore().proxy.extraParams["DocID"] = record.data.DocID;
	this.itemGrid.getStore().on("load", function(store){
		
		var r = store.getProxy().getReader().jsonData;
		r = r.message.split(',');
		AccDocsObject.summaryFS.getComponent("cmp_bd").setValue(Ext.util.Format.Money(r[0]) + "  ریال");
		AccDocsObject.summaryFS.getComponent("cmp_bs").setValue(Ext.util.Format.Money(r[1]) + "  ریال");
		if(r[0] == r[1])
		{
			AccDocsObject.summaryFS.getComponent("cmp_balance").getEl().dom.style.visibility = "hidden";
		}
		else
		{
			AccDocsObject.summaryFS.getComponent("cmp_balance").getEl().dom.style.visibility = "visible";
		}
	});
	
	if(!this.itemGrid.rendered)
		this.itemGrid.render(this.get("div_detail_dg"));
	else
		this.itemGrid.getStore().loadPage(1);
}

AccDocs.prototype.beforeRowEdit = function(record){
	
	var hrecord = AccDocsObject.grid.getStore().getAt(0);
	if(hrecord.data.DocStatus != "RAW")
		return false;
	
	if(record.data.locked == "YES")
	{
		this.detailWin.down("[name=CostID]").disable();
		this.detailWin.down("[name=DebtorAmount]").disable();
		this.detailWin.down("[name=CreditorAmount]").disable();
		this.detailWin.down("[name=details]").disable();
		
		if(record.data.TafsiliType != null)
			this.detailWin.down("[name=TafsiliType]").disable();
		else
			this.detailWin.down("[name=TafsiliType]").enable();
		
		if(record.data.TafsiliID != null)
			this.detailWin.down("[name=TafsiliID]").disable();
		else
			this.detailWin.down("[name=TafsiliID]").enable();
		
		if(record.data.TafsiliType2 != null)
			this.detailWin.down("[name=TafsiliType2]").disable();
		else
			this.detailWin.down("[name=TafsiliType2]").enable();
		
		if(record.data.TafsiliID2 != null)
			this.detailWin.down("[name=TafsiliID2]").disable();
		else
			this.detailWin.down("[name=TafsiliID2]").enable();
	}
	else
	{
		this.detailWin.down("[name=CostID]").enable();
		this.detailWin.down("[name=DebtorAmount]").enable();
		this.detailWin.down("[name=CreditorAmount]").enable();
		this.detailWin.down("[name=details]").enable();
		this.detailWin.down("[name=TafsiliType]").enable();
		this.detailWin.down("[name=TafsiliID]").enable();
		this.detailWin.down("[name=TafsiliType2]").enable();
		this.detailWin.down("[name=TafsiliID2]").enable();
	}
}

AccDocs.deleteitemRender = function(v,p,record)
{
	if(record.data.locked == "YES")
		return "";
	var record = AccDocsObject.grid.getStore().getAt(0);
	if(record.data.DocStatus != "RAW")
		return "";
	
	if(!AccDocsObject.EditAccess)
		return "";
	
	return "<div title='ویرایش' class='edit' onclick='AccDocsObject.EditItem();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;height:16;width:16px;;float:right'></div>" + 
		
		"<div title='حذف اطلاعات' class='remove' onclick='AccDocsObject.removeItem();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;height:16;width:16px;float:left'></div>";
}

AccDocs.prototype.AddItem = function()
{
	if(this.grid.getStore().getAt(0).data.DocStatus != "RAW")
		return;
	
	this.detailWin.show();
	this.detailWin.down('panel').getForm().reset();
	
	this.detailWin.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
		
		AccDocsObject.SaveItem();
		e.preventDefault();
		e.stopEvent();
		return false;
	});
	return;
}

AccDocs.prototype.EditItem = function()
{
	if(this.grid.getStore().getAt(0).data.DocStatus != "RAW")
		return;
		
	var record = this.itemGrid.getSelectionModel().getLastSelected();
	this.detailWin.down('panel').loadRecord(record);
	this.detailWin.show();
	
	this.detailWin.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
		
		AccDocsObject.SaveItem();
		e.preventDefault();
		e.stopEvent();
		return false;
	});
	
	mask = new Ext.LoadMask(this.detailWin, {msg:'در حال بارگذاری اطلاعات...'});
	mask.show();
	//....................................................
	R1 = this.detailWin.down("[name=CostID]").getStore().load({
		params : { CostID : record.data.CostID}
	});
	//....................................................
	R2 = false;
	if(record.data.TafsiliType != "" && record.data.TafsiliID != "")
	{
		this.detailWin.down("[name=TafsiliID]").getStore().proxy.extraParams.TafsiliType = 
			record.data.TafsiliType;
		R2 = this.detailWin.down("[name=TafsiliID]").getStore().load({
			params : { TafsiliID : record.data.TafsiliID}
		});
	}
	//....................................................
	R3 = false;
	if(record.data.TafsiliType2 != "" && record.data.TafsiliID2 != "")
	{
		this.detailWin.down("[name=TafsiliID2]").getStore().proxy.extraParams.TafsiliType = 
			record.data.TafsiliType2;
		R3 = this.detailWin.down("[name=TafsiliID2]").getStore().load({
			params : { TafsiliID : record.data.TafsiliID2}
		});
	}
	var t = setInterval(function(){
		if(!R1.isLoading() && (!R2 || !R2.isLoading()) && (!R3 || !R3.isLoading()))
		{
			clearInterval(t);
			mask.hide();
			AccDocsObject.beforeRowEdit(record);
		}
	}, 1000);
	//....................................................
	
}

AccDocs.prototype.SaveItem = function(store,record)
{
	if(this.detailWin.down('[name=DebtorAmount]').getValue() == null &&
		this.detailWin.down('[name=CreditorAmount]').getValue() == null)
	{
		Ext.MessageBox.alert("","ورود مبلغ بدهکار یا بستانکار الزامی است");
		return;
	}
	if(this.detailWin.down('[name=DebtorAmount]').getValue()*1 > 0 &&
		this.detailWin.down('[name=CreditorAmount]').getValue()*1 > 0)
	{
		Ext.MessageBox.alert("","امکان ورود هم مبلغ بدهکار و هم بستانکار ممکن نمی باشد");
		return;
	}
	if(this.detailWin.down('[name=CostID]').getValue() == null)
	{
		Ext.MessageBox.alert("","ورود کد حساب الزامی است");
		return;
	}
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	if(record)
	{
		Ext.Ajax.request({
			url: this.address_prefix + 'doc.data.php?task=saveDocItem',
			method: 'POST',
			params: {
				record : Ext.encode(record.data)
			},

			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);
				if(st.success)
				{
					AccDocsObject.itemGrid.getStore().load();
				}
				else
				{
					Ext.MessageBox.alert("Error",st.data);
				}
			},
			failure: function(){}
		});
	}
	else
	{
		this.detailWin.down('panel').getForm().submit({
			url: this.address_prefix + 'doc.data.php?task=saveDocItem',
			method: 'POST',
			params: {
				DocID : this.grid.getStore().getAt(0).data.DocID
			},

			success: function(form,action){
				mask.hide();
				if(action.result.success)
				{
					AccDocsObject.itemGrid.getStore().load();
					AccDocsObject.detailWin.hide();
				}
				else
				{
					Ext.MessageBox.alert("Error",action.result.data);
				}
			},
			failure: function(){}
		});
	}

	
}

AccDocs.prototype.removeItem = function()
{
	var record = this.itemGrid.getSelectionModel().getLastSelected();

	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		
		if(btn == "no")
			return;
		me = AccDocsObject;
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف...'});
		mask.show();


		Ext.Ajax.request({
			url: me.address_prefix + 'doc.data.php?task=removeDocItem',
			params:{
				DocID : record.data.DocID,
				ItemID : record.data.ItemID
			},
			method: 'POST',

			success: function(response){
				mask.hide();
				result = Ext.decode(response.responseText);
				if(!result.success)
				{
					Ext.MessageBox.alert("Error", "عملیات مورد نظر با شکست مواجه شد");
					return;
				}
				AccDocsObject.itemGrid.getStore().load();
			},
			failure: function(){}
		});
	});
}

//-----------------------------------------------------------

</script>