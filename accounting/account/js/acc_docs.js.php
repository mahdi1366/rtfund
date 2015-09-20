<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

AccDocs.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : "<?= $js_prefix_address ?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function AccDocs()
{
	this.form = this.get("mainForm");

	this.makeInfoWindow();
	
	Ext.get(this.TabID).addKeyListener(Ext.EventObject.F12, function(keynumber,e){
		AccDocsObject.AddItem();
		e.preventDefault();
		e.stopEvent();
		return false;
	});
	
	//--------------------------------------------------------------------------
	
	this.kolStore = new Ext.data.Store({
		fields:["kolID","kolTitle"],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + '../data/kols.data.php?task=selectKol',
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		autoLoad : true
	});
	
	this.moinStore = new Ext.data.Store({
		fields:["kolID","moinID","moinTitle"],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + '../data/moins.data.php?task=selectMoin',
			reader: {root: 'rows',totalProperty: 'totalCount'}
		}
	});
	
	this.tafsiliStore = new Ext.data.Store({
		fields:["tafsiliID","tafsiliTitle"],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + '../data/tafsilis.data.php?task=selectTafsili',
			reader: {root: 'rows',totalProperty: 'totalCount'}
		}
	});
	
	this.tafsili2Store = new Ext.data.Store({
		fields:["tafsiliID","tafsiliTitle"],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + '../data/tafsilis.data.php?task=selectTafsili',
			reader: {root: 'rows',totalProperty: 'totalCount'}
		}
	});
	
	//--------------------------------------------------------------------------
	
	this.kolCombo = new Ext.form.ComboBox({
		store: this.kolStore,
		emptyText:'انتخاب کل ...',
		typeAhead: false,
		//queryMode : "local",
		valueField : "kolID",
		displayField : "kolTitle",
		listConfig: {
			loadingText: 'در حال جستجو...',
			emptyText: 'فاقد اطلاعات'
		},
		listeners : {
			select : function(combo,records){
				AccDocsObject.moinCombo.setValue();
				AccDocsObject.moinStore.proxy.extraParams["kolID"] = this.getValue();
				AccDocsObject.moinStore.load();
			}
		}
	});
	
	this.moinCombo = new Ext.form.ComboBox({
		store: this.moinStore,
		emptyText:'انتخاب معین ...',
		typeAhead: false,
		pageSize : 10,
		valueField : "moinID",
		displayField : "moinTitle",
		listConfig: {
			loadingText: 'در حال جستجو...',
			emptyText: 'فاقد اطلاعات'
		}
	});
	
	this.tafsiliCombo = new Ext.form.ComboBox({
		store: this.tafsiliStore,
		emptyText:'انتخاب تفصیلی ...',
		typeAhead: false,
		pageSize : 10,
		valueField : "tafsiliID",
		displayField : "tafsiliTitle",
		listConfig: {
			loadingText: 'در حال جستجو...',
			emptyText: 'فاقد اطلاعات'
		}		
	});
	
	this.checktafsiliCombo = new Ext.form.ComboBox({
		store: this.tafsiliStore,
		emptyText:'انتخاب تفصیلی ...',
		typeAhead: false,
		pageSize : 10,
		valueField : "tafsiliID",
		displayField : "tafsiliTitle",
		listConfig: {
			loadingText: 'در حال جستجو...',
			emptyText: 'فاقد اطلاعات'
		}		
	});
	
	this.tafsili2Combo = new Ext.form.ComboBox({
		store: this.tafsiliStore,
		emptyText:'انتخاب تفصیلی2 ...',
		typeAhead: false,
		pageSize : 10,
		valueField : "tafsiliID",
		displayField : "tafsiliTitle",
		listConfig: {
			loadingText: 'در حال جستجو...',
			emptyText: 'فاقد اطلاعات'
		}		
	});
	
	this.accountCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields:["accountID","accountTitle","StartNo","EndNo","StartNo2","EndNo2"],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + '../data/accounts.data.php?task=selectAccount',
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad : true			
		}),
		emptyText:'انتخاب حساب ....',
		typeAhead: false,
		valueField : "accountID",
		displayField : "accountTitle",
		listConfig: {
			loadingText: 'در حال جستجو...',
			emptyText: 'فاقد اطلاعات'
		}
	});
	
	this.mainTab = new Ext.TabPanel({
        renderTo: this.get("div_tab"),
        activeTab: 0,
		tabWidth: 'auto',
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
			loader : {
				url: this.address_prefix + "checks.php",
				method: "POST",
				scripts: true
			},
			listeners :{
				activate : function(){
					var hrecord = AccDocsObject.grid.getStore().getAt(0);
					this.loader.load({
						params : {
							docID : hrecord.data.docID,
							docStatus : hrecord.data.docStatus
						}
					});
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
					name : "docID",
					itemId : "cmp_docID",
					hideTrigger : true
				},{
					xtype : "numberfield",
					fieldLabel: "شماره سند مرجع/عطف",
					name : "ref_docID",
					itemId : "cmp_ref_docID",
					hideTrigger : true
				},
				{
					xtype : "shdatefield",
					format: 'Y/m/d',
					width : 60,
					fieldLabel: "تاریخ سند",
					allowBlank: false,
					name : "docDate"
				},
				{
					xtype : "textarea",
					fieldLabel: "توضیحات",
					name : "description"
				},
				{
					xtype : "textarea",
					fieldLabel: "ملاحظات",
					name : "detail"
				},
				{
					xtype : "hidden",
					name : "oldDocID",
					itemId : "cmp_oldDocID"
				}
			],
			buttons : [
				{
					text : "ذخیره",
					iconCls : "save",
					handler : function(){

						this.up('form').getForm().submit({
							clientValidation: true,
							url: AccDocsObject.address_prefix + '../data/acc_docs.data.php?task=saveDoc',
							method : "POST",
							success : function(form,action){
								AccDocsObject.docWin.hide();
								AccDocsObject.grid.plugins[0].field.setValue('');
								AccDocsObject.grid.getStore().proxy.extraParams["query"] = "";
								AccDocsObject.grid.getStore().loadPage(action.result.data*1);
							},
							failure : function(form,action)
							{
								if(action.result.data == "duplicate")
									alert("شماره سند وارد شده تکراری است");
								else if(action.result.data == "INVALID_ref")
									alert("شماره سند مرجع معتبر نمی باشد");
								else if(action.result.data == "NOTCONFIRM_ref")
									alert("سند مرجع/عطف هنوز تایید نشده است");
								else
									alert("عملیات مورد نظر با شکست مواجه شد");
							}
						});
					}
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
			"<td width=25% class='blue'>" + record.data.docID + "</td>" +
			"<td width=17%>ثبت کننده سند : </td>" +
			"<td class='blue'>" + record.data.regPerson + "</td>" +
			"<td width=17%>شماره سند انبار:</td>" +
			"<td class='blue'>" + (record.data.storeDocID == null ? '---' : record.data.storeDocID) + "</td>" + 
		"</tr>" + 
		"<tr>"+
			"<td>تاریخ سند : </td>" +
			"<td class='blue'>" + MiladiToShamsi(record.data.docDate) + "</td>" +
			"<td>تاریخ ثبت سند : </td>" +
			"<td class='blue'>" + MiladiToShamsi(record.data.regDate) + "</td>" +
			"<td width=17%>شماره سند مرجع:</td>" +
			"<td class='blue'>" + (record.data.ref_docID == null ? "---" : record.data.ref_docID) + "</td>" +
		"</tr>" + 
		"<tr>" +
			"<td>توضیحات : </td>" +
			"<td class='blue' colspan=3>" + (record.data.description == null ? "" : record.data.description) + "</td>" +
			"<td width=17%>شماره اسناد عطف:</td>" +
			"<td class='blue'>" +(record.data.atf == null ? "---" : record.data.atf) + "</td>" +
		"</tr>" +
		"<tr>" +
			"<td>ملاحظات : </td>" +
			"<td colspan=5>" + (record.data.detail == null ? "---" : record.data.detail) + "</td>" +
		"</tr>" +
		"</table>";
}

AccDocs.deleteRender = function(v,p,record)
{
	if(record.data.docStatus != "DELETED")
		return  "<div title='حذف اطلاعات' class='remove' onclick='AccDocsObject.remove();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;height:16'></div>";
	return "حذف شده";
}

AccDocs.prototype.afterHeaderLoad = function(store)
{
	if(store.getAt(0))
	{
		AccDocsObject.itemGrid.show();
		AccDocsObject.showDetail(store.getAt(0));
		//---------------------------------------------
		var doc_record = store.getAt(0);
		var toolbar = AccDocsObject.grid.getDockedItems('toolbar[dock="bottom"]')[0];

		toolbar.getComponent("updateDoc").hide();
		//toolbar.getComponent("printDoc").hide();		
		toolbar.getComponent("deleteDoc").hide();
		toolbar.getComponent("confirmDoc").hide();
		toolbar.getComponent("archiveDoc").hide();
		toolbar.getComponent("copyDoc").hide();
		
		if(doc_record.data.docStatus == "RAW")
		{
			toolbar.getComponent("updateDoc").show();
			toolbar.getComponent("deleteDoc").show();
			toolbar.getComponent("confirmDoc").show();
			toolbar.getComponent("copyDoc").show();
		}
				
		if(doc_record.data.docStatus == "CONFIRM")
			toolbar.getComponent("archiveDoc").show();	
	}
	else
		AccDocsObject.itemGrid.hide();
}

AccDocs.prototype.Add = function()
{
	AccDocsObject.docWin.down("form").getForm().reset();
	AccDocsObject.docWin.show();
	this.docWin.center();
}

AccDocs.prototype.Edit = function()
{
	AccDocsObject.docWin.down("form").getForm().reset();
	this.docWin.show();
	this.docWin.center();

	var record = this.grid.getStore().getAt(0);
	record.data.docDate = MiladiToShamsi(record.data.docDate);
	this.docWin.down("form").loadRecord(record);
	this.docWin.down("form").down("[itemId=cmp_oldDocID]").setValue(record.data.docID);

}

AccDocs.prototype.Copy = function()
{
	if(!this.CopyWin)
	{
		this.CopyWin = new Ext.window.Window({
			title : '',
			modal : true,
			width : 500,
			closeAction : "hide",
			items : new Ext.form.Panel({
				plain: true,
				border: 0,
				bodyPadding: 5,
				items : [{
					xtype : "numberfield",
					hideTrigger : true,
					size:5,
					name : "DocID",                        
					fieldLabel : "کد برگه ایی که می خواهید ردیف های آن در این برگه کپی شود",
					width : 450,
					labelWidth : 350
				}],
				buttons : [
					{
						text : "کپی",
						iconCls : "copy",
						handler : function(){
							
							var record = AccDocsObject.grid.getStore().getAt(0);
							
							Ext.Ajax.request({
								url : AccDocsObject.address_prefix + "../data/acc_docs.data.php?task=copyDoc",
								methos : "post",
								params : {
									src_DocID : this.up('form').down('[name=DocID]').getValue(),
									dst_DocID : record.data.docID
								},
								success : function(){
									AccDocsObject.itemGrid.getStore().load();
									AccDocsObject.CopyWin.hide();
									AccDocsObject.CopyWin.down('[name=DocID]').setValue();
								}
							});
						}
					},{
						text : "انصراف",
						iconCls : "undo",
						handler : function(){
							this.up('window').hide();
						}
					}
				]
			})
		});
	}
	this.CopyWin.show();
}

AccDocs.prototype.remove = function()
{
	var record = this.grid.getStore().getAt(0);

	if(record.data.docStatus == "DELETED")
	{
		alert("این سند قبلا حذف شده است");
		return;
	}
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال حذف...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/acc_docs.data.php?task=removeDoc',
		params:{
			docID: record.data.docID
		},
		method: 'POST',

		success: function(response){
			mask.hide();
			AccDocsObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

AccDocs.prototype.confirmDoc = function()
{
	var record = this.grid.getStore().getAt(0);

	var r = this.itemGrid.getStore().getProxy().getReader().jsonData;
	r = r.message.split(',');
	
	/*if(AccDocsObject.itemGrid.features[0].summaryData[AccDocsObject.itemGrid.columns[6].id] != 
			AccDocsObject.itemGrid.features[0].summaryData[AccDocsObject.itemGrid.columns[7].id])*/
	if(r[0] != r[1])
	{
		alert("به دلیل تراز نبودن سند قادر به تایید آن نمی باشید");
		return
	}

	if(!confirm("پس از تایید سند دیگر قادر به ویرایش و یا حذف سند نمی باشید."+
		"\n آیا مایل به تایید سند می باشید؟"))
		return;

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال تایید سند ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/acc_docs.data.php?task=confirm',
		params:{
			docID: record.data.docID
		},
		method: 'POST',

		success: function(response){
			mask.hide();
			AccDocsObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

AccDocs.prototype.archiveDoc = function()
{
	var record = this.grid.getStore().getAt(0);

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال بایگانی سند ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/acc_docs.data.php?task=archive',
		params:{
			docID: record.data.docID
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
	window.open(this.address_prefix + "print_doc.php?docID=" + 
		this.grid.getStore().getAt(0).data.docID);
}

//.........................................................

AccDocs.prototype.check_deleteRender = function()
{
	return  "<div title='حذف اطلاعات' class='remove' onclick='AccDocsObject.check_remove();' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;height:16'></div>";
}

AccDocs.prototype.check_Add = function()
{
	var modelClass = this.checkGrid.getStore().model;
	var record = new modelClass({
		checkID: "",
		docID : this.grid.getStore().getAt(0).data.docID
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
		url: this.address_prefix + '../data/acc_docs.data.php?task=saveChecks',
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
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;
	
	var record = this.checkGrid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/acc_docs.data.php?task=removeChecks',
		params:{
			checkID: record.data.checkID
		},
		method: 'POST',

		success: function(response){
			mask.hide();
			AccDocsObject.checkGrid.getStore().load();
		},
		failure: function(){}
	});
}

//.........................................................

AccDocs.prototype.showDetail = function(record)
{
	this.mainTab.setActiveTab(0);
	this.itemGrid.getStore().proxy.extraParams["docID"] = record.data.docID;
	this.itemGrid.getStore().on("load", function(store){
		
		var r = store.getProxy().getReader().jsonData;
		r = r.message.split(',');
		AccDocsObject.summaryFS.getComponent("cmp_bd").setValue(Ext.util.Format.Money(r[0]) + "  ریال");
		AccDocsObject.summaryFS.getComponent("cmp_bs").setValue(Ext.util.Format.Money(r[1]) + "  ریال");
		if(r[0] == r[1])
		{
			AccDocsObject.summaryFS.getComponent("cmp_balance").getEl().dom.style.visibility = "hidden";
			var toolbar = AccDocsObject.grid.getDockedItems('toolbar[dock="bottom"]')[0];
			toolbar.getComponent("printDoc").enable();	
		}
		else
		{
			AccDocsObject.summaryFS.getComponent("cmp_balance").getEl().dom.style.visibility = "visible";
			var toolbar = AccDocsObject.grid.getDockedItems('toolbar[dock="bottom"]')[0];
			toolbar.getComponent("printDoc").disable();	
		}
	});
	this.itemGrid.plugins[0].on("beforeedit", AccDocs.beforeRowEdit);
	
	if(!this.itemGrid.rendered)
		this.itemGrid.render(this.get("div_detail_dg"));
	else
		this.itemGrid.getStore().load();

	if(this.grid.getStore().getAt(0).data.docStatus == "RAW")
	{
		this.itemGrid.getDockedItems('toolbar[dock="top"]')[0].getComponent(1).show();
	}
	else
	{
		this.itemGrid.getDockedItems('toolbar[dock="top"]')[0].getComponent(1).hide();
	}
}

AccDocs.beforeRowEdit = function(editor,e){
	
	var record = AccDocsObject.grid.getStore().getAt(0);
	if(record.data.docStatus == "CONFIRM" || record.data.docStatus == "ARCHIVE")
		return false;
	if(e.record.data.locked == "1")
	{
		editor.editor.down("[itemId=cmp_bdAmount]").disable();
		editor.editor.down("[itemId=cmp_bsAmount]").disable();
		editor.editor.down("[itemId=cmp_details]").disable();
	}
	else
	{
		editor.editor.down("[itemId=cmp_bdAmount]").enable();
		editor.editor.down("[itemId=cmp_bsAmount]").enable();
		editor.editor.down("[itemId=cmp_details]").enable();
	}
	
}

AccDocs.deleteitemRender = function(v,p,record)
{
	var record = AccDocsObject.grid.getStore().getAt(0);
	if(record.data.docStatus == "CONFIRM" || record.data.docStatus == "ARCHIVE")
		return "";
	
	return  "<div title='حذف اطلاعات' class='remove' onclick='AccDocsObject.removeItem();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;height:16'></div>";
}

AccDocs.prototype.AddItem = function()
{
	if(this.grid.getStore().getAt(0).data.docStatus == "DELETED")
	{
		alert("این سند حذف شده است");
		return;
	}
	var modelClass = this.itemGrid.getStore().model;
	var record = new modelClass({
		rowID : 0,
		docID : this.grid.getStore().getAt(0).data.docID,
		kolID : "",
		moinID : "",
		tafsiliID : "",
		tafsili2ID : "",
		accountID : "",
		bdAmount : 0,
		bsAmount : 0,
		discription : ""
	});

	this.itemGrid.plugins[0].cancelEdit();
	this.itemGrid.getStore().insert(0, record);
	this.itemGrid.plugins[0].startEdit(0, 0);
	this.kolCombo.focus();
}

AccDocs.prototype.SaveItem = function(store,record)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/acc_docs.data.php?task=saveDocItem',
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

	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال حذف...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/acc_docs.data.php?task=removeDocItem',
		params:{
			docID : record.data.docID,
			rowID : record.data.rowID
		},
		method: 'POST',

		success: function(response){
			mask.hide();
			AccDocsObject.itemGrid.getStore().load();
		},
		failure: function(){}
	});
}

//-----------------------------------------------------------

AccDocs.prototype.storeDocRegister = function()
{
	if(!this.storeDocRegisterWindow )
	{
		this.storeDocRegisterWindow = new Ext.window.Window({
			title: 'صدور سند حسابداری برای فاکتور های خرید',
			modal : true,
			width: 500,
			closeAction : "hide",
			items : [{
					xtype : "numberfield",
					fieldLabel : "شماره سند",
					hideTrigger : true,
					labelWidth : 200,
					itemId : "cmp_docID"
				},{
					xtype : "shdatefield",
					fieldLabel : "تاریخ مورد نظر",
					labelWidth : 200,
					itemId : "cmp_date"
				}],
			buttons : [{
					text : "صدور",
					iconCls : "save",
					handler : function(){
						
						Ext.Ajax.request({
							url : AccDocsObject.address_prefix + "../data/acc_docs.data.php",
							method : "POST",
							params : {
								task : "StoreDocRegister",
								date :  this.up('window').getComponent("cmp_date").getRawValue(),
								DocID : this.up('window').getComponent("cmp_docID").getValue()
								
							},
							success : function(response){
								var sd = Ext.decode(response.responseText);
								if(sd.success)
								{
									AccDocsObject.grid.getStore().load();
									AccDocsObject.storeDocRegisterWindow.close();
								}
								else
									alert(sd.data);
							}
						});
					}
				},{
					text : "انصراف",
					handler : function(){this.up('window').hide();}
				}]
		});
	}
	
	this.storeDocRegisterWindow .show();
}

AccDocs.prototype.shareCompute = function(store,record)
{
		if(!this.shareCmpWin)
	{
		this.shareCmpWin = new Ext.window.Window({
			title: 'تقسیم سود سهام میان سهامداران',
			modal : true,
			width: 500,
			closeAction : "hide",
			items : [{
					xtype : "currencyfield",
					fieldLabel : "مبلغ کل سهام",
					hideTrigger : true,
					labelWidth : 200,
					itemId : "totalAmount"
				},{
					xtype : "currencyfield",
					fieldLabel : "مبلغ هر سهم",
					labelWidth : 200,
					itemId : "partAmount",
					hideTrigger : true,
					value : 100000
				}],
			buttons : [{
					text : "انجام عملیات",
					iconCls : "save",
					handler : function(){
						mask = new Ext.LoadMask(Ext.getCmp(AccDocsObject.TabID), {msg:'در حال ذخیره سازی ...'});
						mask.show();
						Ext.Ajax.request({
							url : AccDocsObject.address_prefix + "../data/acc_docs.data.php",
							method : "POST",
							params : {
								task : "sharesCompute",
								docID : AccDocsObject.grid.getStore().getAt(0).data.docID,
								totalProfit :  this.up('window').getComponent("totalAmount").getRawValue(),
								partAmount : this.up('window').getComponent("partAmount").getValue()
								
							},
							success : function(response){
								var sd = Ext.decode(response.responseText);
								mask.hide();
								if(sd.success)
								{
									AccDocsObject.grid.getStore().load();
									AccDocsObject.shareCmpWin.close();
								}
								else
									alert(sd.data);
							}
						});
					}
				},{
					text : "انصراف",
					handler : function(){this.up('window').hide();}
				}]
		});
	}
	
	this.shareCmpWin.show();
}


</script>