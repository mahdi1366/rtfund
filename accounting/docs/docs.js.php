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

	//--------------------------------------------------------------------------
	
	this.CostCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields:["CostID","CostCode","CostDesc"],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectCostCode',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			}
		}),
		emptyText:'انتخاب کد حساب ....',
		typeAhead: false,
		//queryMode : "local",
		valueField : "CostID",
		displayField : "CostDesc",
		listConfig: {
			loadingText: 'در حال جستجو...',
			emptyText: 'فاقد اطلاعات'
		}
	});
	
	this.tafsiliGroupCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields:["InfoID","InfoDesc"],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectTafsiliGroups',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			autoLoad : true
		}),
		emptyText:"گروه تفصیلی",
		typeAhead: false,
		queryMode : "local",
		valueField : "InfoID",
		displayField : "InfoDesc",
		listeners : {
			select : function(combo,records){
				AccDocsObject.tafsiliCombo.setValue();
				AccDocsObject.tafsiliCombo.getStore().proxy.extraParams["TafsiliType"] = this.getValue();
				AccDocsObject.tafsiliCombo.getStore().load();
			}
		}
	});
	
	
	this.tafsiliCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields:["TafsiliID","TafsiliDesc"],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=GetAllTafsilis',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			listeners : {
				beforeload : function(store){
					if(!store.proxy.extraParams.TafsiliType)
					{
						group = AccDocsObject.tafsiliGroupCombo.getValue();
						if(group == "")
							return false;
						this.proxy.extraParams["TafsiliType"] = group;
					}
				}
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
            op_menu.add({text: 'اضافه برگه',iconCls: 'add', 
				handler : function(){ return AccDocsObject.AddDoc(); }})

		if(record != null && record.data.DocStatus == "RAW")
		{
			if(this.EditAccess)
			{
				op_menu.add({text: 'ویرایش برگه',iconCls: 'edit', 
					handler : function(){ return AccDocsObject.EditDoc(); } });
				
				op_menu.add({text: 'تایید برگه',iconCls: 'tick', 
					handler : function(){ return AccDocsObject.confirmDoc(); } });
			}
			if(this.RemoveAccess)
				op_menu.add({text: 'حذف برگه',iconCls: 'remove', 
					handler : function(){ return AccDocsObject.RemoveDoc(); } });
		}
		if(record != null && record.data.DocStatus == "CONFIRM" && this.EditAccess)
		{
			op_menu.add({text: 'بایگانی',iconCls: 'archive', 
				handler : function(){ return AccDocsObject.archiveDoc(); } });
		}
	}
    if(record != null)           
		op_menu.add({text: 'چاپ برگه',iconCls: 'print', 
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
					fieldLabel: "تاریخ سند",
					name : "DocDate"
				},
				{
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

var AccDocsObject = new AccDocs();

AccDocs.docRender = function(v,p,record)
{
	return "<table class='docInfo' width=100%>"+
		"<tr>"+
			"<td width=10%>کد سند : </td>" +
			"<td width=25% class='blueText'>" + record.data.LocalNo + "</td>" +
			"<td width=17%>ثبت کننده سند : </td>" +
			"<td class='blueText'>" + record.data.regPerson + "</td>" +
		"</tr>" + 
		"<tr>"+
			"<td>تاریخ سند : </td>" +
			"<td class='blueText'>" + MiladiToShamsi(record.data.DocDate) + "</td>" +
			"<td>تاریخ ثبت سند : </td>" +
			"<td class='blueText'>" + MiladiToShamsi(record.data.RegDate) + "</td>" +
		"</tr>" + 
		"<tr>" +
			"<td>توضیحات : </td>" +
			"<td class='blueText' colspan=3>" + (record.data.description == null ? "" : record.data.description) + "</td>" +
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
			AccDocsObject.grid.plugins[0].field.setValue('');
			AccDocsObject.grid.getStore().proxy.extraParams["query"] = "";
			AccDocsObject.grid.getStore().loadPage(action.result.data*1);
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
				mask.hide();
				AccDocsObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

AccDocs.prototype.archiveDoc = function()
{
	var record = this.grid.getStore().getAt(0);

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال بایگانی سند ...'});
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
		CheckID: "",
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
				CheckID: record.data.CheckID
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
		this.itemGrid.getStore().load();
}

AccDocs.beforeRowEdit = function(editor,e){
	
	var record = AccDocsObject.grid.getStore().getAt(0);
	if(record.data.DocStatus != "RAW")
		return false;
	
	if(e.record.data.locked == "1")
	{
		editor.editor.down("[itemId=cmp_DebtorAmount]").disable();
		editor.editor.down("[itemId=cmp_CreditorAmount]").disable();
		editor.editor.down("[itemId=cmp_details]").disable();
	}
	else
	{
		editor.editor.down("[itemId=cmp_DebtorAmount]").enable();
		editor.editor.down("[itemId=cmp_CreditorAmount]").enable();
		editor.editor.down("[itemId=cmp_details]").enable();
	}
	
}

AccDocs.deleteitemRender = function(v,p,record)
{
	if(record.data.locked == "YES")
		return "";
	var record = AccDocsObject.grid.getStore().getAt(0);
	if(record.data.DocStatus != "RAW")
		return "";
	
	return  "<div title='حذف اطلاعات' class='remove' onclick='AccDocsObject.removeItem();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;height:16'></div>";
}

AccDocs.prototype.AddItem = function()
{
	if(this.grid.getStore().getAt(0).data.DocStatus != "RAW")
		return;
	
	var modelClass = this.itemGrid.getStore().model;
	var record = new modelClass({
		ItemID : 0,
		DocID : this.grid.getStore().getAt(0).data.DocID,
		CostID : "",
		TafsiliID : "",
		AccountID : "",
		DebtorAmount : 0,
		CreditorAmount : 0,
		discription : ""
	});

	this.itemGrid.plugins[0].cancelEdit();
	this.itemGrid.getStore().insert(0, record);
	this.itemGrid.plugins[0].startEdit(0, 0);
}

AccDocs.prototype.SaveItem = function(store,record)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

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
				alert(st.data);
			}
		},
		failure: function(){}
	});
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