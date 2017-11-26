<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------
	
RequestInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	MenuID : '<?= isset($_POST["MenuID"]) ? $_POST["MenuID"] : 0 ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	RequestID : <?= $RequestID ?>,
	RequestRecord : null,
	User : '<?= $User ?>',
	ReadOnly : <?= $ReadOnly ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function RequestInfo(){
	
	this.mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال بارگذاري...'});
	this.mask.show();
	
	this.grid = <?= $grid ?>;
	this.grid.getStore().on("load", function(){
		if(this.getCount() > 0)
			RequestInfoObject.grid.getSelectionModel().select(this.getCount()-1);
	});
	this.grid.on("select", function(){
		record = RequestInfoObject.grid.getSelectionModel().getLastSelected();
		RequestInfoObject.PartsPanel.loadRecord(record);
		RequestInfoObject.PartsPanel.doLayout();
		RequestInfoObject.LoadSummary(record);
		RequestInfoObject.PartsPanel.down("[name=PayInterval]").setValue(record.data.PayInterval + " " + 
			(record.data.IntervalType == "DAY" ? "روز" : "ماه"));
	});
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsHistory == "YES")
			return "greenRow";
		return "";
	}	

	if(this.RequestID > 0)
		this.grid.getStore().proxy.extraParams = { RequestID : this.RequestID };
		
	this.BuildForms();
	
	if(this.RequestID > 0)
		this.LoadRequestInfo();
		
	if(this.RequestID == 0)
	{
		this.CustomizeForm(null);
		this.mask.hide();
	}	
}

RequestInfo.prototype.LoadRequestInfo = function(){
	
	this.store = new Ext.data.Store({
		proxy:{
			type: 'jsonp',
			url: this.address_prefix + "request.data.php?task=SelectAllRequests&RequestID=" + this.RequestID,
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["RequestID","BranchID","LoanID","BranchName","ReqPersonID","ReqFullname","LoanPersonID",
					"LoanFullname","ReqDate","ReqAmount","ReqDetails","BorrowerDesc","BorrowerID",
					"BorrowerMobile","guarantees","AgentGuarantee","FundGuarantee","StatusID","DocumentDesc","IsFree",
					"imp_GirandehCode","imp_VamCode","IsEnded","SubAgentID","PlanTitle","RuleNo"],
		autoLoad : true,
		listeners :{
			load : function(){
				
				me = RequestInfoObject;
				
				if(me.RequestRecord != null && me.RequestRecord.RequestID == me.RequestID)
				{
					me.RequestRecord = this.getAt(0);
					me.CustomizeForm(me.RequestRecord);
					me.grid.getStore().load();
					me.mask.hide();
					return;
				}			
				//..........................................................
				record = this.getAt(0);
				me.RequestRecord = record;
				me.companyPanel.loadRecord(record);
				//..........................................................
				oldInfo = record.data.imp_VamCode != null ? "شماره وام سیستم قدیم : " + 
					record.data.imp_VamCode + "[پرونده : " + record.data.imp_GirandehCode + "]" : "";
				me.companyPanel.down("[itemId=oldInfo]").update(oldInfo);
				//..........................................................
				if(record.data.IsEnded == "YES" && me.grid.down("[itemId=addPart]"))
				{
					me.grid.down("[itemId=addPart]").hide();
				}
				//..........................................................
				if(record.data.AgentGuarantee == "YES")
					me.companyPanel.down("[name=AgentGuarantee]").setValue(true);
				if(record.data.FundGuarantee == "YES")
					me.companyPanel.down("[name=FundGuarantee]").setValue(true);
				if(record.data.guarantees != null)
				{
					arr = record.data.guarantees.split(",");
					for(i=0; i<arr.length; i++)
						if(arr[i] != "")
							me.companyPanel.down("[name=guarantee_" + arr[i] + "]").setValue(true);
				}
				//..........................................................
				var R1 = false;
				if(record.data.LoanPersonID > 0)
				{
					R1 = me.companyPanel.down("[name=LoanPersonID]").getStore().load({
						params : {
							PersonID : record.data.LoanPersonID
						},
						callback : function(){
							if(this.getCount() > 0)
								me.companyPanel.down("[name=LoanPersonID]").setValue(this.getAt(0).data.PersonID);
						}
					});
				}	
				var R2 = false;
				var R3 = false;
				if(record.data.ReqPersonID > 0)
				{
					R2 = me.companyPanel.down("[name=ReqPersonID]").getStore().load({
						params : {
							PersonID : record.data.ReqPersonID
						},
						callback : function(){
							if(this.getCount() > 0)
								me.companyPanel.down("[name=ReqPersonID]").setValue(this.getAt(0).data.PersonID);
						}
					});
					
					R3 = me.companyPanel.down("[itemId=cmp_subAgent]").getStore().load({
						params : {
							PersonID : record.data.ReqPersonID
						},
						callback : function(){
						}
					});					
				}				
				//..........................................................
				var t = setInterval(function(){
					if((!R1 || !R1.isLoading()) && (!R2 || !R2.isLoading()) && (!R3 || !R3.isLoading()))
					{
						clearInterval(t);
						me.CustomizeForm(record);
						me.mask.hide();
					}
				}, 1000);
			}
		}
	});
}

RequestInfo.OperationRender = function(v,p,record){
	
	if(record.data.IsHistory == "YES")
		return;
	if(RequestInfoObject.User == "Staff")
		return "<div  title='عملیات' class='setting' onclick='RequestInfoObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";

	if(RequestInfoObject.User == "Agent" && record.data.StatusID == "1")
	{
		return "<div  title='ویرایش' class='edit' onclick='RequestInfoObject.PartInfo(true);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;float:right;height:16'></div>"/* + 

		"<div  title='حذف' class='remove' onclick='RequestInfoObject.DeletePart();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;float:right;height:16'></div>";*/
	}
}

RequestInfo.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	firstPart = this.grid.getStore().indexOf(record) == 0 ? true : false;
	ReqRecord = this.store.getAt(0);
	
	var op_menu = new Ext.menu.Menu();
	
	if(record.data.imp_VamCode*1 > 0)
	{
		if(this.EditAccess && this.RequestRecord.data.IsEnded == "NO")
			op_menu.add({text: 'ویرایش',iconCls: 'edit', 
			handler : function(){ return RequestInfoObject.PartInfo(true); }});
		
		op_menu.showAt(e.pageX-120, e.pageY);
		return;
	}
	if(record.data.IsStarted == "NO" && record.data.IsEnded == "NO")
	{
		if(record.data.StatusID == "70")
			op_menu.add({text: 'شروع گردش فرم',iconCls: 'refresh',
			handler : function(){ return RequestInfoObject.StartFlow(); }});
		if(this.EditAccess && firstPart)
			op_menu.add({text: 'ویرایش',iconCls: 'edit', 
			handler : function(){ return RequestInfoObject.PartInfo(true); }});
		if(this.RemoveAccess)
			op_menu.add({text: 'حذف',iconCls: 'remove', 
			handler : function(){ return RequestInfoObject.DeletePart(true); }});				
	}	
	if(record.data.IsEnded == "YES")
	{
		if(this.EditAccess && firstPart && record.data.IsDocRegister == "NO" && this.RequestRecord.data.IsEnded == "NO")
			op_menu.add({text: 'ویرایش',iconCls: 'edit', 
			handler : function(){ return RequestInfoObject.PartInfo(true); }});
			
		if(this.EditAccess && !firstPart)
			op_menu.add({text: 'حذف',iconCls: 'remove', 
			handler : function(){ return RequestInfoObject.DeletePart(false); }});	
	}		
	
	if(record.data.StatusID == "70")
		op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return RequestInfoObject.ShowPartHistory(); }});
	
	op_menu.showAt(e.pageX-120, e.pageY);
}

RequestInfo.prototype.MakePartsPanel = function(){
	
	this.PartsPanel =  new Ext.form.FormPanel({
		width: 735,
		border : 0,
		colspan : 2,
		minHeight:20,
		items: [{
			xtype : "fieldset",
			title : "شرایط پرداخت",
			layout : "column",
			columns : 2,
			items :[this.grid,{
				xtype : "container",
				width: 580,
				style : "margin-right:5px",
				layout : {
					type : "table",
					columns : 3
				},
				defaults : {
					xtype : "displayfield",
					hideTrigger : true,
					width : 200,
					labelWidth : 90,
					style : "margin-bottom:5px",
					fieldCls : "blueText"
				},
				items : [{
					fieldLabel: 'مبلغ پرداخت',
					name: 'PartAmount',
					renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
				},{
					fieldLabel: 'تاریخ پرداخت',
					name: 'PartDate',
					renderer : function(v){return MiladiToShamsi(v);}
				},{
					fieldLabel: 'فاصله اقساط',
					name: 'PayInterval'
				},{
					xtype : "container",
					layout : "hbox",
					items : [{
						xtype : "displayfield",
						fieldLabel: 'مدت تنفس',
						fieldCls : "blueText",
						name: 'DelayMonths',
						renderer : function(v){ return v + " ماه"}
					},{
						xtype : "displayfield",
						fieldCls : "blueText",
						name: 'DelayDays',
						renderer : function(v){ return v + " روز"}
					}]					
				},{
					fieldLabel: 'تعداد اقساط',
					name: 'InstallmentCount'
				},{
					fieldLabel: 'درصد دیرکرد',
					name: 'ForfeitPercent',
					renderer : function(v){ return v + " %"}
				},{
					fieldLabel: 'کارمزد تنفس',
					name: 'DelayPercent',
					renderer : function(v){ return v + " %"}
				},{
					fieldLabel: 'کارمزد مشتری',
					name: 'CustomerWage'	,		
					renderer : function(v){ return v + " %"}
				},{
					fieldLabel: 'سهم صندوق',
					name: 'FundWage',
					renderer : function(v){ return v + " %"}
				},{
					name : "WageReturn",
					fieldLabel: 'پرداخت کارمزد',
					renderer : function(v){
						if(v == "CUSTOMER") return "هنگام پرداخت وام";
						if(v == "AGENT") return 'سپرده سرمایه گذار';
						if(v == "INSTALLMENT") return 'طی اقساط';
					}
				},{
					name : "PayCompute",
					fieldLabel: 'محاسبه پرداخت',
					renderer : function(v){
						if(v == "forfeit") return "ابتدا کسر جریمه";
						if(v == "installment") return 'ابتدا کسر قسط';
					}
				},{
					name : "MaxFundWage",
					labelWidth : 110,
					fieldLabel: 'سقف کارمزد صندوق',
					renderer : function(v){ return Ext.util.Format.Money(v)	}
				},{
					name : "AgentReturn",
					fieldLabel: 'کارمزد سرمایه گذار',
					renderer : function(v){
						if(v == "CUSTOMER") return "هنگام پرداخت وام";
						if(v == "INSTALLMENT") return 'طی اقساط';
					}
				},{
					name : "AgentDelayReturn",
					fieldLabel: 'تنفس سرمایه گذار',
					renderer : function(v){
						if(v == "CUSTOMER") return "هنگام پرداخت وام";
						if(v == "INSTALLMENT") return 'طی اقساط';
					}
				},{
					name : "DelayReturn",
					fieldLabel: 'پرداخت تنفس',
					renderer : function(v){
						if(v == "CUSTOMER") return "هنگام پرداخت وام";
						if(v == "INSTALLMENT") return 'طی اقساط';
					}
				},{
					colspan : 3,
					xtype : "container",
					width : 580,
					contentEl : this.get("summaryDIV")
				},{
					xtype : "button",
					style : "margin-top:5px",
					text : "فرمول های محاسبات وام",
					iconCls : "process",
					handler : function(){
						window.open("/framework/help/compute.pdf");
					}
				}]
			}]
		}]
	});
}

RequestInfo.prototype.BuildForms = function(){

	this.MakePartsPanel();
	
	this.attachButtons = [{
		text : 'مدارک وام',
		iconCls : "attach",
		itemId : "cmp_LoanDocuments",
		handler : function(){ RequestInfoObject.LoanDocuments('loan'); }	
	},{
		text : 'مدارک وام گیرنده',
		iconCls : "attach",
		itemId : "cmp_PersonDocuments",
		handler : function(){ RequestInfoObject.LoanDocuments('person'); }
	},{
		text : 'ضامنین/وثیقه گذاران',
		iconCls : "list",
		itemId : "cmp_guarantors",
		handler : function(){ RequestInfoObject.LoanGuarantors(); }
	},{
		text : 'سابقه',
		iconCls : "history",
		itemId : "cmp_history",
		handler : function(){ RequestInfoObject.ShowHistory(); }
	},{
		text : 'پیام ها',
		iconCls : "comment",
		itemId : "cmp_comment",
		handler : function(){ RequestInfoObject.ShowMessages(); }
	},{
		text : 'رویدادها',
		iconCls : "task",
		itemId : "cmp_events",
		handler : function(){ RequestInfoObject.ShowEvents(); }
	}];
	
	this.companyPanel = new Ext.form.FormPanel({
		renderTo : this.get("mainForm"),
		width: 750,
		bodyStyle : "padding:2px",
		frame : true,
		layout : {
			type : "column",
			columns : 2
		},	
		defaults : {
			width : 350,				
			labelWidth : 130
		},
		items : [{
			xtype : "combo",
			hidden : true,
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../../framework/person/persons.data.php?' +
						"task=selectPersons&UserTypes=IsAgent,IsSupporter,IsShareholder",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['PersonID','fullname']
			}),
			fieldLabel : "معرفی کننده",
			pageSize : 25,
			displayField : "fullname",
			valueField : "PersonID",
			name : "ReqPersonID",
			itemId : "cmp_requester",
			listeners :{
				select : function(record){
					el = RequestInfoObject.companyPanel.down("[itemId=cmp_subAgent]");
					el.getStore().proxy.extraParams["PersonID"] = this.getValue();
					el.getStore().load();
				}
			}
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../../framework/person/persons.data.php?' +
						"task=selectSubAgents",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['SubID','SubDesc'],
				autoLoad : true
			}),
			fieldLabel : "زیر واحد سرمایه گذار",
			queryMode : "local",
			displayField : "SubDesc",
			valueField : "SubID",
			name : "SubAgentID",
			itemId : "cmp_subAgent"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../../framework/person/persons.data.php?' +
						"task=selectPersons&UserType=IsCustomer",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['PersonID','fullname']
			}),
			fieldLabel : "مشتری",
			displayField : "fullname",
			pageSize : 20,
			hidden : true,
			valueField : "PersonID",
			name : "LoanPersonID"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../loan/loan.data.php?task=GetAllLoans',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['LoanID','LoanDesc'],
				autoLoad : true					
			}),
			fieldLabel : "نوع وام",
			queryMode : 'local',
			hidden : true,
			displayField : "LoanDesc",
			valueField : "LoanID",
			name : "LoanID"
		},{
			xtype : "textfield",
			name : "BorrowerDesc",
			fieldLabel : "فرد حقیقی / حقوقی"
		},{
			xtype : "textfield",
			name : "BorrowerID",
			fieldLabel : "کد ملی / کد اقتصادی"
		},{
			xtype : "numberfield",
			hideTrigger : true,
			name : "BorrowerMobile",
			fieldLabel : "موبایل وام گیرنده"
			
		},{
			xtype : "currencyfield",
			name : "ReqAmount",
			allowBlank : false,
			fieldLabel : "مبلغ درخواست",
			hideTrigger: true
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../../framework/baseInfo/baseInfo.data.php?' +
						"task=SelectBranches",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['BranchID','BranchName'],
				autoLoad : true					
			}),
			fieldLabel : "شعبه اخذ وام",
			queryMode : 'local',
			allowBlank : false,
			hidden : true,
			displayField : "BranchName",
			valueField : "BranchID",
			name : "BranchID"
		},{
			xtype : "container",
			layout : "hbox",
			itemId : "setting",
			items :[{
				xtype : "checkbox",
				boxLabel : "وام بلاعوض می باشد",
				inputValue : "YES",
				name : "IsFree"
			},{
				xtype : "container",
				html : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
			},{
				xtype : "checkbox",
				name : "AgentGuarantee",
				value : "YES",
				boxLabel : "با ضمانت سرمایه گذار"
			},{
				xtype : "checkbox",
				name : "FundGuarantee",
				value : "YES",
				colspan : 2,
				boxLabel : "با ضمانت صندوق"
			}]
		},{
			xtype : "container",
			hidden : true,
			itemId : "oldInfo",
			width : 300,
			items : [{
				xtype : "numberfield",
				name : "imp_VamCode",
				hideTrigger : true,
				fieldLabel : "شماره وام قدیم"
			}],
			cls : "blueText"
		},{
			xtype : "fieldset",
			title : "تضمین",
			colspan : 2,
			layout : {
				type : "table",
				columns : 6
			},
			itemId : "cmp_guarantees",
			width : 700,
			height : 80,
			items :[{
				xtype : "checkbox",
				boxLabel: 'وثیقه ملکی',
				name: 'guarantee_1',	
				inputValue: 1,
				style : "margin-left : 20px"
			},{
				xtype : "checkbox",
				boxLabel: 'ضمانت بانکی',
				name: 'guarantee_2',	
				inputValue: 1,
				style : "margin-left : 20px"
			},{
				xtype : "checkbox",
				boxLabel: 'چک',
				name: 'guarantee_3',	
				inputValue: 1,
				style : "margin-left : 20px"
			},{
				xtype : "checkbox",
				boxLabel: 'سفته',
				name: 'guarantee_4',	
				inputValue: 1,
				style : "margin-left : 20px"
			},{
				xtype : "checkbox",
				boxLabel: 'کسر از حقوق',
				name: 'guarantee_5',	
				inputValue: 1,
				style : "margin-left : 20px"
			},{
				xtype : "checkbox",
				boxLabel: 'ماشین آلات',
				name: 'guarantee_6',	
				inputValue: 1,
				style : "margin-left : 20px"
			},{
				xtype : "checkbox",
				boxLabel: 'سایر اوراق بهادار بانکی',
				name: 'guarantee_7',	
				inputValue: 1,
				style : "margin-left : 20px"
			},{
				xtype : "checkbox",
				boxLabel: 'اوراق مشارکت بی نام',
				name: 'guarantee_8',	
				inputValue: 1,
				style : "margin-left : 20px"
			}]			
		},{
			xtype : "textarea",
			fieldLabel : "توضیحات",
			rows : 1,
			style : this.RequestID == 0 ? "margin-bottom:15px" : "",
			name : "ReqDetails"
		},{
			xtype : "textarea",
			fieldLabel : "توضیحات مدارک",
			rows : 1,
			name : "DocumentDesc"
		},{
			xtype : "textfield",
			fieldLabel : "عنوان طرح",
			name : "PlanTitle"
		},{
		xtype : "textfield",
			fieldLabel : "شماره مصوبه",
			name : "RuleNo"
		},
		this.PartsPanel
		],
		tbar :[{
			text : "پیوست ها",
			itemId : "cmp_menu",
			iconCls : "setting",
			menu : this.attachButtons
		},{
			text : "عملیات",
			hidden : true,
			itemId : "cmp_processes",
			iconCls : "process",
			menu : [{
				text: 'اقساط',
				itemId : "cmp_installments",
				iconCls: 'list',
				handler : function(){ return RequestInfoObject.LoadInstallments(); }
			},{
				text: 'پرداخت های مشتری',
				itemId : "cmp_BackPays",
				iconCls: 'list',
				handler : function(){ return RequestInfoObject.LoadBackPays(); }
			},{
				text: 'مراحل پرداخت',
				itemId : "cmp_payments",
				iconCls: 'epay',
				handler : function(){ return RequestInfoObject.LoadPayments(); }
			},{
				text : 'هزینه ها',
				iconCls : "account",
				hidden : true,
				itemId : "cmp_costs",
				handler : function(){ RequestInfoObject.ShowCosts(); }
			},{
				text : 'چک لیست',
				iconCls : "check",
				hidden : true,
				itemId : "cmp_checklist",
				handler : function(){ RequestInfoObject.ShowCheckList(); }
			},{
				text: 'چاپ رسید اقساط',
				iconCls: 'print', 
				handler : function(){ 
					window.open(ManageRequestObject.address_prefix + "PrintLoanDocs.php?type=checks&RequestID=" +
						RequestInfoObject.RequestID);	
				}
			},{
				text: 'چاپ رسید تضامین',
				iconCls: 'print', 
				handler : function(){ 
					window.open(ManageRequestObject.address_prefix + "PrintLoanDocs.php?type=tazmin&RequestID=" +
						RequestInfoObject.RequestID);	
				}
			},{
				text: 'چاپ کاردکس',iconCls: 'print', 
				handler : function(){ 
					window.open(ManageRequestObject.address_prefix + "../report/LoanSummary.php?RequestID=" +
						RequestInfoObject.RequestID);	
				}
			}]
		},'->',{
			text : 'ویرایش شرایط پرداخت',
			hidden : true,
			iconCls : "edit",
			itemId : "cmp_editPart",
			handler : function(){ RequestInfoObject.PartInfo(true); }
		},{
			text : 'مراحل پرداخت',
			hidden : true,
			iconCls : "list",
			itemId : "cmp_payments",
			handler : function(){ RequestInfoObject.LoadPayments(); }
		},{
			text : 'تایید ارسال مدارک',
			hidden : true,
			iconCls : "tick",
			itemId : "cmp_confirm50",
			handler : function(){ RequestInfoObject.beforeChangeStatus(50); }
		},{
			xtype : "button",
			itemId : "cmp_save",
			iconCls : "save",
			text : "ذخیره",
			handler : function(){ RequestInfoObject.SaveRequest('save'); }
		},{
			text : "ذخیره و ارسال درخواست",
			iconCls : "save",
			hidden : true,
			itemId : "cmp_saveAndSend",
			handler : function(){ RequestInfoObject.SaveRequest('send'); }
		},{
			text : 'تغییر وضعیت',
			hidden : true,
			iconCls : "refresh",
			itemId : "cmp_changeStatus",
			handler : function(){ RequestInfoObject.SetStatus(); }
		},{
			text : 'تایید درخواست',
			hidden : true,
			iconCls : "tick",
			itemId : "cmp_confirm30",
			handler : function(){ RequestInfoObject.beforeChangeStatus(30); }
		},{
			text : 'رد درخواست',
			hidden : true,
			iconCls : "cross",
			itemId : "cmp_reject20",
			handler : function(){ RequestInfoObject.beforeChangeStatus(20); }
		},{
			text : 'ارسال به مشتری جهت تکمیل مدارک',
			hidden : true,
			iconCls : "send",
			itemId : "cmp_SendToCustomer",
			handler : function(){ RequestInfoObject.ChangeStatus(40); }
		},{
			text : 'برگشت از مشتری',
			hidden : true,
			iconCls : "back",
			itemId : "cmp_returnFromCustomer",
			handler : function(){ RequestInfoObject.ChangeStatus(35, ""); }
		},{
			text : 'تایید مدارک مشتری',
			hidden : true,
			iconCls : "tick",
			itemId : "cmp_confirm70",
			handler : function(){ RequestInfoObject.beforeChangeStatus(70); }
		},{
			text : 'عدم تایید مدارک',
			hidden : true,
			iconCls : "cross",
			itemId : "cmp_reject60",
			handler : function(){ RequestInfoObject.beforeChangeStatus(60); }
		},{
			text : 'خاتمه وام',
			hidden : true,
			iconCls : "finish",
			itemId : "cmp_end",
			handler : function(){ RequestInfoObject.EndRequest(); }
		},{
			text : "برگشت خاتمه وام",
			hidden : true,
			iconCls : "undo",
			itemId : "cmp_undoend",
			handler : function(){ RequestInfoObject.ReturnEndRequest(); }
		}]
	});
		
	this.SendedPanel = new Ext.panel.Panel({
		hidden : true,
		renderTo : this.get("SendForm"),
		width : 400,
		style : "margin-top:30px",
		frame : true,
		items : [{
			xtype : "container",
			html : "<br>" + "درخواست شما با موفقیت ثبت گردید" + "<br><br>"
		},{
			xtype : "container",
			cls : "blueText",
			itemId : "requestID"
		},{
			xtype : "container",
			html : "<br>"
		}]
	});
		
	this.get("summaryDIV").style.display = "";
} 

RequestInfo.prototype.CustomizeForm = function(record){
	
	if(this.User == "Staff")
	{
		this.companyPanel.down("[itemId=cmp_saveAndSend]").hide();
		this.companyPanel.down("[itemId=cmp_requester]").show();
		this.companyPanel.down("[name=LoanID]").show();
		this.companyPanel.down("[name=BranchID]").show();
		this.companyPanel.down("[name=LoanPersonID]").show();		
		this.companyPanel.down("[itemId=oldInfo]").show();				
		
		if(record == null)
		{
			this.companyPanel.down("[name=BorrowerDesc]").hide();
			this.companyPanel.down("[name=BorrowerID]").hide();
			this.companyPanel.down("[name=BorrowerMobile]").hide();			
			this.companyPanel.down("[name=AgentGuarantee]").hide();
			this.companyPanel.down("[name=FundGuarantee]").hide();
			this.PartsPanel.hide();
		}
	}
	if(this.User == "Agent")
	{
		if(this.RequestID == 0)
			this.PartsPanel.hide();
		else
		{
			this.companyPanel.down("[itemId=cmp_save]").hide();
			this.companyPanel.down("[itemId=cmp_saveAndSend]").show();
			this.grid.hide();
			this.companyPanel.down("[itemId=cmp_editPart]").show();
			this.companyPanel.down("[itemId=cmp_payments]").show();			
		}		
		
		this.companyPanel.down("[name=BranchID]").setValue(1);
		this.companyPanel.down("[name=BranchID]").hide();	
		
		this.companyPanel.doLayout();
	}
	if(this.User == "Shareholder")
	{
		this.PartsPanel.hide();
		this.companyPanel.down("[itemId=cmp_save]").hide();
		this.companyPanel.down("[itemId=cmp_subAgent]").hide();
		this.companyPanel.down("[itemId=cmp_guarantees]").hide();
		this.companyPanel.down("[itemId=setting]").hide();
		this.companyPanel.down("[name=PlanTitle]").hide();
		this.companyPanel.down("[name=RuleNo]").hide();
		
		this.companyPanel.down("[itemId=cmp_saveAndSend]").show();
		this.grid.hide();
		this.companyPanel.down("[name=BranchID]").setValue(1);
		this.companyPanel.down("[name=BranchID]").hide();	
		this.companyPanel.doLayout();
	}
	
	if(record != null)
	{
		if(this.User == "Agent" && record.data.StatusID != "1" && record.data.StatusID != "20")
		{
			this.companyPanel.getEl().readonly();
			this.companyPanel.down("[itemId=cmp_save]").hide();
			this.companyPanel.down("[itemId=cmp_saveAndSend]").hide();
			this.companyPanel.down("[itemId=cmp_editPart]").hide();
			this.companyPanel.down("[itemId=cmp_payments]").hide();	
			this.grid.down("[dataIndex=PartID]").hide();
		}	
		
		if(this.User == "Staff")
		{
			if(record.data.IsEnded == "YES")
			{
				//this.companyPanel.getEl().readonly();
				this.companyPanel.getComponent("cmp_requester").setReadOnly(true);
				this.companyPanel.getComponent("cmp_subAgent").setReadOnly(true);
				this.companyPanel.down("[name=LoanPersonID]").setReadOnly(true);
				this.companyPanel.down("[name=LoanID]").setReadOnly(true);
				this.companyPanel.down("[name=BorrowerDesc]").setReadOnly(true);
				this.companyPanel.down("[name=BorrowerID]").setReadOnly(true);
				this.companyPanel.down("[name=BorrowerMobile]").setReadOnly(true);
				this.companyPanel.down("[name=ReqAmount]").setReadOnly(true);
				this.companyPanel.down("[name=BranchID]").setReadOnly(true);
				//this.companyPanel.down("[itemId=cmp_save]").hide();
			}
			else
			{
				if(record.data.StatusID == "70")
				{
					if(record.data.imp_VamCode*1 == null || record.data.imp_VamCode*1 == 0)
					{
						//this.companyPanel.getEl().readonly();
						this.companyPanel.getComponent("cmp_requester").setReadOnly(true);
						this.companyPanel.getComponent("cmp_subAgent").setReadOnly(true);
						this.companyPanel.down("[name=LoanPersonID]").setReadOnly(true);
						this.companyPanel.down("[name=LoanID]").setReadOnly(true);
						this.companyPanel.down("[name=BorrowerDesc]").setReadOnly(true);
						this.companyPanel.down("[name=BorrowerID]").setReadOnly(true);
						this.companyPanel.down("[name=BorrowerMobile]").setReadOnly(true);
						this.companyPanel.down("[name=ReqAmount]").setReadOnly(true);
						this.companyPanel.down("[name=BranchID]").setReadOnly(true);
						//this.companyPanel.down("[itemId=cmp_save]").hide();
					}					
				}
				if(record.data.ReqPersonID == null)
				{
					this.companyPanel.down("[name=BorrowerDesc]").hide();
					this.companyPanel.down("[name=BorrowerID]").hide();
					this.companyPanel.down("[name=BorrowerMobile]").hide();					
				}
			}		
			this.companyPanel.down("[itemId=cmp_events]").show();
			this.companyPanel.down("[itemId=cmp_costs]").show();
			this.companyPanel.down("[itemId=cmp_checklist]").show();			
			this.companyPanel.down("[itemId=cmp_processes]").show();	
		}	
		if(this.User == "Customer")
		{
			this.companyPanel.down("[name=BorrowerDesc]").hide();
			this.companyPanel.down("[name=BorrowerID]").hide();
			this.companyPanel.down("[name=BorrowerMobile]").hide();
			this.companyPanel.down("[name=ReqDetails]").hide();
			this.companyPanel.down("[itemId=cmp_save]").hide();
			this.companyPanel.down("[name=AgentGuarantee]").hide();
			this.companyPanel.down("[name=FundGuarantee]").hide();
			this.companyPanel.down("[itemId=cmp_events]").hide();
			this.companyPanel.down("[itemId=cmp_costs]").hide();
			this.companyPanel.down("[itemId=cmp_checklist]").hide();			
						
			this.companyPanel.getEl().readonly();
			
			//this.grid.down("[itemId=addPart]").hide();
			this.grid.down("[dataIndex=PartID]").hide();	
			this.companyPanel.down("[itemId=cmp_saveAndSend]").hide();
			
			this.PartsPanel.down("[name=FundWage]").getEl().dom.style.display = "none";
			this.PartsPanel.down("[name=WageReturn]").getEl().dom.style.display = "none";
			this.PartsPanel.down("[name=PayCompute]").getEl().dom.style.display = "none";
			this.PartsPanel.down("[name=MaxFundWage]").getEl().dom.style.display = "none";
			this.PartsPanel.down("[name=AgentReturn]").getEl().dom.style.display = "none";
			this.PartsPanel.down("[name=AgentDelayReturn]").getEl().dom.style.display = "none";
			this.PartsPanel.down("[name=DelayReturn]").getEl().dom.style.display = "none";
			this.get("TR_FundWage").style.display = "none";
			this.get("TR_AgentWage").style.display = "none";
			this.get("div_yearly").style.display = "none";
			this.companyPanel.down("[itemId=cmp_menu]").hide();
			this.companyPanel.down("toolbar").insert(0,this.attachButtons);

		}
		this.companyPanel.doLayout();
	}
	
	//..........................................................................
	this.companyPanel.down("[itemId=cmp_confirm30]").hide();
	this.companyPanel.down("[itemId=cmp_reject20]").hide();
	this.companyPanel.down("[itemId=cmp_SendToCustomer]").hide();
	this.companyPanel.down("[itemId=cmp_returnFromCustomer]").hide();
	this.companyPanel.down("[itemId=cmp_confirm70]").hide();
	this.companyPanel.down("[itemId=cmp_reject60]").hide();
	this.companyPanel.down("[itemId=cmp_changeStatus]").hide();
	this.companyPanel.down("[itemId=cmp_end]").hide();
	this.companyPanel.down("[itemId=cmp_undoend]").hide();
	this.companyPanel.down("[itemId=cmp_confirm50]").hide();
	
	if(record == null)
	{
		this.companyPanel.down("[itemId=cmp_LoanDocuments]").hide();
		this.companyPanel.down("[itemId=cmp_PersonDocuments]").hide();
		this.companyPanel.down("[itemId=cmp_history]").hide();
		this.companyPanel.down("[itemId=cmp_comment]").hide();
	}
	
	if(this.ReadOnly || !this.EditAccess)
	{
		this.companyPanel.down("[itemId=cmp_save]").hide();
		return;
	}
	
	if(record == null)
		return;
	
	if(this.User == "Customer")
	{
		if(record.data.StatusID == "40" || record.data.StatusID == "60")
			this.companyPanel.down("[itemId=cmp_confirm50]").show();
		return;
	}
	
	if(record.data.IsEnded == "YES")
	{
		this.companyPanel.down("[itemId=cmp_undoend]").show();
		return;
	}		
	
	if(record != null && this.User == "Staff")
	{
		//if('<?= $_SESSION["USER"]["UserName"] ?>' == 'admin')
		//{
			this.companyPanel.down("[itemId=cmp_changeStatus]").show();
		//}
		if(record.data.StatusID == "1")
		{
			this.companyPanel.down("[itemId=cmp_confirm30]").show();
		}
		if(record.data.StatusID == "10")
		{
			this.companyPanel.down("[itemId=cmp_confirm30]").show();
			this.companyPanel.down("[itemId=cmp_reject20]").show();
		}
		if(record.data.StatusID == "30" || record.data.StatusID == "35")
		{
			this.companyPanel.down("[itemId=cmp_SendToCustomer]").show();
		}
		if(record.data.StatusID == "40")
		{
			this.companyPanel.down("[itemId=cmp_returnFromCustomer]").show();
		}
		if(record.data.StatusID == "50")
		{
			this.companyPanel.down("[itemId=cmp_confirm70]").show();
			this.companyPanel.down("[itemId=cmp_reject60]").show();
		}
		if(record.data.IsEnded == "NO" && record.data.StatusID == "70")
		{
			this.companyPanel.down("[itemId=cmp_end]").show();
		}
	}
}

RequestInfoObject = new RequestInfo();

RequestInfo.prototype.SaveRequest = function(mode){

	mask = new Ext.LoadMask(this.companyPanel, {msg:'در حال ذخيره سازي...'});
	mask.show();  
	
	this.companyPanel.getForm().submit({
		clientValidation: true,
		url: this.address_prefix + '../../loan/request/request.data.php?task=SaveLoanRequest' , 
		method: "POST",
		params : {
			RequestID : this.RequestID,
			sending : mode == "send" ? "true" : "false"
		},
		
		success : function(form,action){
			mask.hide();
			me = RequestInfoObject;
			
			me.RequestID = action.result.data;
			
			me.grid.getStore().proxy.extraParams = {RequestID: me.RequestID};
			me.grid.getStore().load();
			
			if(me.User == "Agent")
				me.companyPanel.down("[itemId=cmp_save]").hide();
			
			me.PartsPanel.show();
			me.LoadRequestInfo();
			me.PartsPanel.doLayout();
			if( mode == "send")
			{
				me.companyPanel.hide();
				me.PartsPanel.hide();
				me.SendedPanel.show();
				me.SendedPanel.getComponent("requestID").
					update('شماره پیگیری درخواست : ' + me.RequestID);
			}
		},
		failure : function(form,action){
			mask.hide();
			if(action.result.data == "")
				Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
			else
				Ext.MessageBox.alert("Error",action.result.data);
		}
	});
}

RequestInfo.prototype.ShowHistory = function(){

	if(!this.HistoryWin)
	{
		this.HistoryWin = new Ext.window.Window({
			title: 'سابقه گردش درخواست',
			modal : true,
			autoScroll : true,
			width: 700,
			height : 500,
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "history.php",
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
		Ext.getCmp(this.TabID).add(this.HistoryWin);
		
		this.HistoryWin.show();
		this.HistoryWin.center();
		this.HistoryWin.loader.load({
			params : {
				RequestID : this.RequestID
			}
		});
		return;
	}
	
	this.HistoryWin.show();
	this.HistoryWin.center();
}

RequestInfo.prototype.LoanGuarantors = function(){

	if(!this.GuarantorWin)
	{
		this.GuarantorWin = new Ext.window.Window({
			title: 'ضامنین / وثیقه گذاران',
			modal : true,
			autoScroll : true,
			width: 750,
			height : 600,
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "guarantors.php",
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
		Ext.getCmp(this.TabID).add(this.GuarantorWin);
		
		this.GuarantorWin.show();
		this.GuarantorWin.center();
		this.GuarantorWin.loader.load({
			params : {
				ExtTabID : this.GuarantorWin.getEl().id,
				RequestID : this.RequestID
			}
		});
		return;
	}
	
	this.GuarantorWin.show();
	this.GuarantorWin.center();
}

RequestInfo.prototype.LoanDocuments = function(ObjectType){

	if(!this.documentWin)
	{
		this.documentWin = new Ext.window.Window({
			width : 720,
			height : 440,
			modal : true,
			autoScroll:true,
			bodyStyle : "background-color:white;padding: 4px 4px 4px 4px",
			closeAction : "hide",
			loader : {
				url : "../../office/dms/documents.php",
				scripts : true
			},
			buttons :[{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.documentWin);
	}

	this.documentWin.show();
	this.documentWin.center();
	
	this.documentWin.loader.load({
		scripts : true,
		params : {
			ExtTabID : this.documentWin.getEl().id,
			ObjectType : ObjectType,
			ObjectID : ObjectType == "loan" ? this.RequestID : this.RequestRecord.data.LoanPersonID
		}
	});
}

RequestInfo.prototype.beforeChangeStatus = function(StatusID){
	
	if(new Array(20,60).indexOf(StatusID) == -1)
	{
		message = "آیا مایل به تایید می باشید؟";
		if(StatusID == "50")
			message = "پس از تایید دیگر قادر به تغییر در اطلاعات نمی باشید<br>" +"آیا مایل به تایید می باشید؟";
	
		Ext.MessageBox.confirm("",message, function(btn){
			if(btn == "no")
				return;
			
			RequestInfoObject.ChangeStatus (StatusID, "");
		});
		return;
	}
	if(!this.commentWin)
	{
		this.commentWin = new Ext.window.Window({
			width : 412,
			height : 198,
			modal : true,
			title : "دلیل رد مدرک برای درخواست کننده",
			bodyStyle : "background-color:white",
			items : [{
				xtype : "textarea",
				width : 400,
				rows : 8,
				name : "StepComment"
			}],
			closeAction : "hide",
			buttons : [{
				text : "اعمال",				
				iconCls : "save",
				itemId : "btn_save"
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.commentWin);
	}
	this.commentWin.down("[itemId=btn_save]").setHandler(function(){
		RequestInfoObject.ChangeStatus(StatusID, 
			this.up('window').down("[name=StepComment]").getValue());});
	this.commentWin.show();
	this.commentWin.center();
}

RequestInfo.prototype.ChangeStatus = function(StatusID, StepComment){
	
	this.mask.show();
	
	Ext.Ajax.request({
		methos : "post",
		url : this.address_prefix + "request.data.php",
		params : {
			task : "ChangeRequestStatus",
			RequestID : this.RequestID,
			StatusID : StatusID,
			StepComment : StepComment
		},
		
		success : function(response){
			
			result = Ext.decode(response.responseText);
			if(StatusID == 50)
				Ext.MessageBox.alert("","مدارک شما به صندوق پژوهش و فناوری ارسال گردید");
			RequestInfoObject.LoadRequestInfo();
			if(RequestInfoObject.commentWin)
				RequestInfoObject.commentWin.hide();
			if(!result.success)
			{
				Ext.MessageBox.alert("ERROR", result.data == "" ? "عملیات مورد نظر با شکست مواجه شد" : result.data )
			}
		}
	});
}

RequestInfo.prototype.SetStatus = function(){
	
	if(!this.setStatusWin)
	{
		this.setStatusWin = new Ext.window.Window({
			width : 412,
			height : 198,
			modal : true,
			title : "تغییر وضعیت",
			defaults : {width : 380},
			bodyStyle : "background-color:white",
			items : [{
				xtype : "combo",
				store : new Ext.data.SimpleStore({
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + "request.data.php?task=selectRequestStatuses",
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields : ['InfoID','InfoDesc'],
					autoLoad : true					
				}),
				fieldLabel : "وضعیت جدید",
				queryMode : 'local',
				allowBlank : false,
				displayField : "InfoDesc",
				valueField : "InfoID",
				itemId : "StatusID"
			},{
				xtype : "textarea",
				itemId : "comment",
				fieldLabel : "توضیحات"
			}],
			closeAction : "hide",
			buttons : [{
				text : "تغییر وضعیت",				
				iconCls : "save",
				itemId : "btn_save",
				handler : function(){
					status = this.up('window').getComponent("StatusID").getValue();
					comment = this.up('window').getComponent("comment").getValue();
					RequestInfoObject.ChangeStatus(status, "[تغییر وضعیت]" + comment);
					this.up('window').hide();
				}
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.setStatusWin);
	}
	this.setStatusWin.show();
	this.setStatusWin.center();
}

RequestInfo.prototype.EndRequest = function(){
	
	this.mask.show();
	
	Ext.Ajax.request({
		methos : "post",
		url : this.address_prefix + "request.data.php",
		params : {
			task : "GetRequestTotalRemainder",
			RequestID : this.RequestID
		},

		success : function(response){
			result = Ext.decode(response.responseText);
			Ext.MessageBox.confirm("","مبلغ باقیمانده وام " + 
				Ext.util.Format.Money(result.data) + " ریال می باشد" +
				"<br>آیا مایل به خاتمه وام و صدور سند خاتمه می باشید؟", function(btn){
				
				if(btn == "no")
				{
					RequestInfoObject.mask.hide();
					return;
				}	

				me = RequestInfoObject;
				me.mask.show();

				Ext.Ajax.request({
					methos : "post",
					url : me.address_prefix + "request.data.php",
					params : {
						task : "EndRequest",
						RequestID : me.RequestID
					},

					success : function(response){
						result = Ext.decode(response.responseText);
						if(result.success)
						{
							Ext.MessageBox.alert("","سند مربوطه با موفقیت صادر گردید");
							RequestInfoObject.LoadRequestInfo();					
						}	
						else if(result.data == "")
							Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
						else
							Ext.MessageBox.alert("",result.data);

					}
				});
			});			

		}
	});
	
	
}

RequestInfo.prototype.ReturnEndRequest = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به برگشت خاتمه وام و ابطال سند خاتمه می باشید؟", function(btn){
				
		if(btn == "no")
			return;

		me = RequestInfoObject;
		me.mask.show();

		Ext.Ajax.request({
			methos : "post",
			url : me.address_prefix + "request.data.php",
			params : {
				task : "ReturnEndRequest",
				RequestID : me.RequestID
			},

			success : function(response){
				result = Ext.decode(response.responseText);
				if(result.success)
				{
					Ext.MessageBox.alert("","سند مربوطه با موفقیت باطل گردید");
					RequestInfoObject.LoadRequestInfo();					
				}	
				else if(result.data == "")
					Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
				else
					Ext.MessageBox.alert("",result.data);

				RequestInfoObject.mask.hide();
			}
		});
	});			
}

//.........................................................

RequestInfo.prototype.PartInfo = function(EditMode){
	
	if(!this.PartWin)
	{
		this.PartWin = new Ext.window.Window({
			width : 550,
			height : 560,
			modal : true,
			closeAction : 'hide',
			title : "شرایط وام",
			items : new Ext.form.Panel({
				layout : {
					type : "table",
					columns : 2
				},
				defaults : {
					xtype : "numberfield",
					labelWidth : 80,
					hideTrigger : true,
					width : 150,
					labelWidth : 90,
					allowBlank : false
				},				
				items :[{
					xtype : "textfield",
					name : "PartDesc",
					fieldLabel : "عنوان شرایط",
					colspan : 2,
					width : 450
				},{
					xtype : "currencyfield",
					name : "PartAmount",
					fieldLabel : "مبلغ وام",
					maxValue : this.RequestRecord.data.RequestAmount,
					width : 220
				},{
					xtype : "container",
					width : 300,
					layout : "hbox",
					items : [{
						xtype : "numberfield",
						labelWidth : 90,
						width: 150,
						hideTrigger : true,
						fieldLabel: 'مدت تنفس',
						name: 'DelayMonths',
						afterSubTpl : "ماه"
					},{
						xtype : "numberfield",
						hideTrigger : true,
						width: 50,
						name: 'DelayDays',
						afterSubTpl : "روز"
					}]
				},{
					xtype : "shdatefield",
					name : "PartDate",
					allowBlank : true,
					hideTrigger : false,
					fieldLabel : "تاریخ پرداخت",
					width : 200
				},{
					xtype : "shdatefield",
					name : "PartStartDate",
					hidden : true,
					allowBlank : true,
					hideTrigger : false,
					fieldLabel : "اعمال شرایط",
					width : 200
				},{
					xtype : "container",
					layout : "hbox",
					width : 250,
					items : [{
						xtype:'numberfield',
						fieldLabel: 'فاصله اقساط',
						hideTrigger : true,
						allowBlank : false,
						name: 'PayInterval',
						labelWidth: 90,
						width : 150
					},{
						xtype : "radio",
						boxLabel : "ماه",
						inputValue : "MONTH",
						itemId : "monthInterval",
						checked : true,
						name : "IntervalType"
					},{
						xtype : "radio",
						boxLabel : "روز",
						inputValue : "DAY",
						itemId : "dayInterval",
						name : "IntervalType"
					}]
				},{
					fieldLabel: 'تعداد اقساط',
					name: 'InstallmentCount'
				},{
					fieldLabel: 'درصد دیرکرد',
					name: 'ForfeitPercent'
				},{
					fieldLabel: 'کارمزد مشتری',
					name: 'CustomerWage'	
				},{
					fieldLabel: 'کارمزد صندوق',
					name: 'FundWage'
				},{
					fieldLabel: 'کارمزد تنفس',
					name: 'DelayPercent'					
				},{
					fieldLabel: 'دوره پرداخت(ماه)',
					name: 'PayDuration',
					allowBlank : true
				},{
					xtype : "fieldset",
					itemId : "fs_WageCompute",
					title : "نحوه دریافت کارمزد صندوق",
					width : 240,
					style : "margin-right:10px",
					items : [{
						xtype : "radio",
						boxLabel : "پرداخت کارمزد طی اقساط",
						name : "WageReturn",
						inputValue : "INSTALLMENT",
						checked : true
					},{
						xtype : "radio",						
						boxLabel : "پرداخت کارمزد هنگام پرداخت وام",
						name : "WageReturn",
						inputValue : "CUSTOMER"
					},{
						xtype : "radio",						
						boxLabel : "کسر کارمزد از حساب سرمایه گذار",
						name : "WageReturn",
						inputValue : "AGENT"
					}]
				},{
					xtype : "fieldset",
					itemId : "fs_DelayCompute",
					title : "نحوه دریافت تنفس صندوق",
					width : 200,
					style : "margin-right:10px",
					items : [{
						xtype : "radio",						
						boxLabel : "هنگام پرداخت وام",
						name : "DelayReturn",
						inputValue : "CUSTOMER",
						checked : true
					},{
						xtype : "radio",
						boxLabel : "طی اقساط",
						name : "DelayReturn",
						inputValue : "INSTALLMENT"
					}]
				},{
					xtype : "fieldset",
					itemId : "fs_AgentWageCompute",
					title : "نحوه دریافت کارمزد سرمایه گذار",
					width : 240,
					style : "margin-right:10px",
					items : [{
						xtype : "radio",
						boxLabel : "پرداخت کارمزد طی اقساط",
						name : "AgentReturn",
						inputValue : "INSTALLMENT",
						checked : true
					},{
						xtype : "radio",						
						boxLabel : "پرداخت کارمزد هنگام پرداخت وام",
						name : "AgentReturn",
						inputValue : "CUSTOMER"
					}]
				},{
					xtype : "fieldset",
					itemId : "fs_AgentDelayCompute",
					title : "نحوه دریافت تنفس سرمایه گذار",
					width : 200,
					style : "margin-right:10px",
					items : [{
						xtype : "radio",						
						boxLabel : "هنگام پرداخت وام",
						name : "AgentDelayReturn",
						inputValue : "CUSTOMER",
						checked : true
					},{
						xtype : "radio",
						boxLabel : "طی اقساط",
						name : "AgentDelayReturn",
						inputValue : "INSTALLMENT"
					}]
				},{
					xtype : "fieldset",
					colspan :2,
					width : 450,
					style : "margin-right:10px",
					itemId : "fs_PayCompute",
					items : [{
						xtype : "radio",
						boxLabel : "محاسبه  پرداخت بر اساس اول جریمه سپس قسط",
						name : "PayCompute",
						inputValue : "forfeit",
						checked : true
					},{
						xtype : "radio",
						boxLabel : "محاسبه پرداخت بر اساس اول اصل مبلغ قسط سپس جرایم",
						name : "PayCompute",
						inputValue : "installment"
					}]
				},{
					xtype : "fieldset",
					colspan :2,
					width : 450,
					style : "margin-right:10px",
					itemId : "fs_MaxFundWage",
					items : [{
						xtype : "currencyfield",
						hideTrigger : true,
						labelWidth : 120,
						fieldLabel : "سقف کارمزد صندوق",
						name : "MaxFundWage"
					},{
						xtype : "container",
						html : "این کارمزد بر اساس نحوه دریافت کارمزد محاسبه می گردد."
					}]
				},{
					xtype : "hidden",
					name : "PartID"
				}]				
			}),
			buttons : [{
				text : "ذخیره",
				iconCls : "save",
				handler : function(){
					RequestInfoObject.SavePart();
				}
			},{
				text : "انصراف",
				iconCls : "undo",
				handler : function(){
					this.up('window').hide();
				}
			}]
		});
		
		if(this.User == "Agent")
		{
			this.PartWin.down("[name=PartDate]").hide();
			this.PartWin.down("[itemId=fs_WageCompute]").hide();
			this.PartWin.down("[itemId=fs_AgentWageCompute]").hide();
			this.PartWin.down("[itemId=fs_DelayCompute]").hide();
			this.PartWin.down("[itemId=fs_AgentDelayCompute]").hide();
			this.PartWin.down("[itemId=fs_PayCompute]").hide();
			this.PartWin.down("[itemId=fs_MaxFundWage]").hide();
			this.PartWin.down("[name=PartAmount]").colspan = 2;
			this.PartWin.setHeight(250);
		}
	}
	
	this.PartWin.show();
	if(EditMode)
	{
		record = this.grid.getSelectionModel().getLastSelected();
		this.PartWin.down('form').loadRecord(record);
		this.PartWin.down("[name=PartDate]").setValue(MiladiToShamsi(record.data.PartDate));
		this.PartWin.down("[name=PartStartDate]").setValue(MiladiToShamsi(record.data.PartStartDate));
		this.PartWin.down("[name=PayInterval]").setValue(record.data.PayInterval*1);
		this.PartWin.down("[itemId=monthInterval]").setValue(record.data.IntervalType == "MONTH" ? true : false);
		this.PartWin.down("[itemId=dayInterval]").setValue(record.data.IntervalType == "DAY" ? true : false);
	}
	else
	{
		this.PartWin.down('form').getForm().reset();
		record = this.grid.getStore().getAt( this.grid.getStore().totalCount-1 );
		this.PartWin.down('form').loadRecord(record);
		this.PartWin.down("[name=PartDate]").setValue(MiladiToShamsi(record.data.PartDate));
		this.PartWin.down("[name=PartStartDate]").setValue(MiladiToShamsi(record.data.PartStartDate));
		this.PartWin.down("[name=PayInterval]").setValue(record.data.PayInterval*1);
		this.PartWin.down("[itemId=monthInterval]").setValue(record.data.IntervalType == "MONTH" ? true : false);
		this.PartWin.down("[itemId=dayInterval]").setValue(record.data.IntervalType == "DAY" ? true : false);
		this.PartWin.down("[name=PartID]").setValue();
	}
}

RequestInfo.prototype.SavePart = function(){

	if(this.PartWin.down('[name=MaxFundWage]').getValue()*1 > 0 && 
		this.PartWin.down('[name=FundWage]').getValue()*1 > 0 )
	{
		Ext.MessageBox.alert("Error","در صورتی که سقف کارمزد صندوق را تعیین می کنید باید کارمزد صندوق را صفر نمایید");
		return;
	}

	mask = new Ext.LoadMask(this.PartWin, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	this.PartWin.down('form').getForm().submit({
		clientValidation: true,
		url: this.address_prefix +'../../loan/request/request.data.php',
		method: "POST",
		params: {
			task: "SavePart",
			RequestID : this.RequestID
		},
		success: function(form,action){
			mask.hide();
			RequestInfoObject.grid.getStore().load();
			RequestInfoObject.PartWin.hide();
		},
		failure: function(form,action){
			if(action.result.data == "")
				Ext.MessageBox.alert("","عملیات مربوطه با شکست مواجه شد");
			else
				Ext.MessageBox.alert("",action.result.data);
			mask.hide();
		}
	});
}

RequestInfo.prototype.DeletePart = function(firstPart){

	if(!firstPart)
		Ext.MessageBox.alert("","در صورت حذف این شرایط سند اختلاف نیز حذف می گردد");

	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = RequestInfoObject;
		record = me.grid.getSelectionModel().getLastSelected();
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'request.data.php',
			method: "POST",
			params: {
				task: "DeletePart",
				PartID : record.data.PartID
			},
			success: function(response){
				result = Ext.decode(response.responseText);
				mask.hide();
				if(result.success)
					RequestInfoObject.grid.getStore().load();
				else
					Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد;")
			}
		});
	});
}

RequestInfo.prototype.SplitYears = function(startDate, endDate, TotalAmount){
	
	arr = startDate.split(/[\-\/]/);
	StartYear = arr[0]*1;
	
	totalDays = 0;
	yearDays = new Array();
	newStartDate = startDate;
	while(DateModule.IsDateGreater(endDate,newStartDate)){
		arr = newStartDate.split(/[\-\/]/);
		LastDayOfYear = DateModule.lastJDateOfYear(arr[0]);
		if(DateModule.IsDateGreater(LastDayOfYear, endDate))
			LastDayOfYear = endDate;
		
		thedays = DateModule.JDateMinusJDate(LastDayOfYear, newStartDate)+1;
		yearDays.push({
			year : StartYear, 
			days : thedays
		});
		totalDays += thedays;
		StartYear++;
		newStartDate = DateModule.AddToJDate(LastDayOfYear, 1);
	}
	TotalDays = DateModule.JDateMinusJDate(endDate, startDate)+1;
	sum = 0;
	for(i=0; i<yearDays.length; i++)
	{
		yearDays[i].amount = Math.round((yearDays[i].days/TotalDays)*TotalAmount);
		sum += yearDays[i].amount;
	}
	if(sum != TotalAmount)
		yearDays[i-1].amount += TotalAmount-sum;
	
	return yearDays;
}

RequestInfo.prototype.LoadSummary = function(record){

	if(record.data.ReqPersonID == "<?= SHEKOOFAI ?>")
		return this.LoadSummarySHRTFUND(record, null);
	
	function ComputeInstallmentAmount(TotalAmount,IstallmentCount,PayInterval){
		
		if(PayInterval == 0)
			return TotalAmount;
		
		return TotalAmount/IstallmentCount;
	}
	function ComputeWage(F7, F8, F9, IntervalType, PayInterval){
		
		if(PayInterval == 0)
			return 0;
		
		if(F8 == 0)
			return 0;
		
		if(IntervalType == "DAY")
			PayInterval = PayInterval/30;
		
		R = (F8/12)*PayInterval;
			
		return (((F7*R*( Math.pow((1+R),F9)))/((Math.pow((1+R),F9))-1))*F9)-F7;
	}
	function roundUp(number, digits){
		var factor = Math.pow(10,digits);
		return Math.ceil(number*factor) / factor;
	}
	function YearWageCompute(record,TotalWage,YearMonths){
				
		startDate = DateModule.AddToGDate(record.data.PartDate, record.data.DelayDays, record.data.DelayMonths);
		startDate = MiladiToShamsi(startDate);
		startDate = DateModule.AddToJDate(startDate, record.data.DelayDays, record.data.DelayMonths);
		startDate = startDate.split(/[\-\/]/);
		PayMonth = startDate[1];
		PayMonth = PayMonth*YearMonths/12;
		
		FirstYearInstallmentCount = Math.floor((12 - PayMonth)/(12/YearMonths));
		FirstYearInstallmentCount = record.data.InstallmentCount*1 < FirstYearInstallmentCount ? 
			record.data.InstallmentCount*1 : FirstYearInstallmentCount;
		MidYearInstallmentCount = Math.floor((record.data.InstallmentCount*1-FirstYearInstallmentCount) / YearMonths);
		MidYearInstallmentCount = MidYearInstallmentCount < 0 ? 0 : MidYearInstallmentCount;
		LastYeatInstallmentCount = (record.data.InstallmentCount-FirstYearInstallmentCount) % YearMonths;
		LastYeatInstallmentCount = LastYeatInstallmentCount < 0 ? 0 : LastYeatInstallmentCount;
		F9 = record.data.InstallmentCount*(12/YearMonths);

		yearNo = 1;
		StartYear = startDate[0]*1;
		returnArr = new Array();
		while(true)
		{
			if(yearNo > MidYearInstallmentCount+2)
				break;

			BeforeMonths = 0;
			if(yearNo == 2)
				BeforeMonths = FirstYearInstallmentCount;
			else if(yearNo > 2)
				BeforeMonths = FirstYearInstallmentCount + (yearNo-2)*YearMonths;

			curMonths = FirstYearInstallmentCount;
			if(yearNo > 1 && yearNo <= MidYearInstallmentCount+1)
				curMonths = YearMonths;
			else if(yearNo > MidYearInstallmentCount+1)
				curMonths = LastYeatInstallmentCount;

			BeforeMonths = BeforeMonths*(12/YearMonths);
			curMonths = curMonths*(12/YearMonths);

			val = ((((F9-BeforeMonths)*(F9-BeforeMonths+1))-
				(F9-BeforeMonths-curMonths)*(F9-BeforeMonths-curMonths+1)))/(F9*(F9+1))*TotalWage;

			returnArr.push({
				year : StartYear,
				amount : Ext.util.Format.Money(Math.round(val))
			});
			StartYear++;
			yearNo++;
		}
	
		return returnArr;
	}
	
	if(record.data.PayInterval > 0)
		YearMonths = (record.data.IntervalType == "DAY" ) ? 
			Math.floor(365/record.data.PayInterval) : 12/record.data.PayInterval;
	else
		YearMonths = 12;
		
	DelayDuration = DateModule.GDateMinusGDate(
		DateModule.AddToGDate(record.data.PartDate, record.data.DelayDays*1, record.data.DelayMonths*1), 
		record.data.PartDate);
		
	TotalWage = Math.round(ComputeWage(record.data.PartAmount, record.data.CustomerWage/100, 
		record.data.InstallmentCount, record.data.IntervalType, record.data.PayInterval));
		
	TotalWage = !isInt(TotalWage) ? 0 : TotalWage;	
	FundWage = Math.round((record.data.FundWage/record.data.CustomerWage)*TotalWage);
	FundWage = !isInt(FundWage) ? 0 : FundWage;
	AgentWage = TotalWage - FundWage;
	
	if(record.data.DelayDays*1 > 0)
		TotalDelay = Math.round(record.data.PartAmount*record.data.DelayPercent*DelayDuration/36500);
	else
		TotalDelay = Math.round(record.data.PartAmount*record.data.DelayPercent*record.data.DelayMonths/1200);
	
	//-------------------------- installments -----------------------------
	MaxWage = Math.max(record.data.CustomerWage, record.data.FundWage);
	CustomerFactor =	MaxWage == 0 ? 0 : record.data.CustomerWage/MaxWage;
	FundFactor =		MaxWage == 0 ? 0 : record.data.FundWage/MaxWage;
	AgentFactor =		MaxWage == 0 ? 0 : (record.data.CustomerWage-record.data.FundWage)/MaxWage;
	
	var extraAmount = 0;
	if(record.data.WageReturn == "INSTALLMENT")
	{
		if(record.data.MaxFundWage*1 > 0)
			extraAmount += record.data.MaxFundWage;
		else if(record.data.CustomerWage > record.data.FundWage)
			extraAmount += Math.round(TotalWage*FundFactor);
		else
			extraAmount += Math.round(TotalWage*CustomerFactor);		
	}		
	if(record.data.AgentReturn == "INSTALLMENT" && record.data.CustomerWage>record.data.FundWage)
		extraAmount += Math.round(TotalWage*AgentFactor);

	if(record.data.DelayReturn == "INSTALLMENT")
		extraAmount += TotalDelay*(record.data.FundWage/record.data.DelayPercent);
	if(record.data.AgentDelayReturn == "INSTALLMENT" && record.data.DelayPercent*1>record.data.FundWage*1)
		extraAmount += TotalDelay*((record.data.DelayPercent-record.data.FundWage)/record.data.DelayPercent);
	
	TotalAmount = record.data.PartAmount*1 + extraAmount;
	
	FirstPay = ComputeInstallmentAmount(TotalAmount,record.data.InstallmentCount, record.data.PayInterval);
	
	if(record.data.InstallmentCount > 1)
		FirstPay = roundUp(FirstPay,-3);
	else
		FirstPay = Math.round(FirstPay);
	
	if(record.data.DelayReturn == "INSTALLMENT")
		FirstPay += TotalDelay/record.data.InstallmentCount*1;
	
	LastPay = Math.round(TotalAmount + (record.data.DelayReturn == "INSTALLMENT" ? TotalDelay : 0) 
			- FirstPay*(record.data.InstallmentCount-1));

	//---------------------------------------------------------------------
	if(record.data.MaxFundWage*1 > 0)
	{
		tmp = record.data.WageReturn == "INSTALLMENT" ? 
			Math.round(record.data.MaxFundWage*1/record.data.InstallmentCount) : 0;
		
		this.get("SUM_InstallmentAmount").innerHTML = Ext.util.Format.Money(FirstPay + tmp);
		this.get("SUM_LastInstallmentAmount").innerHTML = Ext.util.Format.Money(LastPay + tmp);
		this.get("SUM_Delay").innerHTML = 0;
		this.get("SUM_NetAmount").innerHTML = Ext.util.Format.Money(record.data.PartAmount 
			 - (record.data.WageReturn == "CUSTOMER" ? TotalWage + record.data.MaxFundWage*1 : 0));	

		this.get("SUM_TotalWage").innerHTML = Ext.util.Format.Money(TotalWage + record.data.MaxFundWage*1);	
		this.get("SUM_FundWage").innerHTML = Ext.util.Format.Money(record.data.MaxFundWage);	
		this.get("SUM_AgentWage").innerHTML = Ext.util.Format.Money(AgentWage);	

		this.get("SUM_Wage_1Year").innerHTML = 0;
		this.get("SUM_Wage_2Year").innerHTML = 0;
		this.get("SUM_Wage_3Year").innerHTML = 0;
		this.get("SUM_Wage_4Year").innerHTML = 0;
		return;
	}
	
	this.get("SUM_InstallmentAmount").innerHTML = Ext.util.Format.Money(FirstPay);
	this.get("SUM_LastInstallmentAmount").innerHTML = Ext.util.Format.Money(LastPay);
	this.get("SUM_Delay").innerHTML = Ext.util.Format.Money(TotalDelay);
	this.get("SUM_NetAmount").innerHTML = Ext.util.Format.Money(record.data.PartAmount - 
		(record.data.DelayReturn == "CUSTOMER" ? TotalDelay : 0) - 
		(record.data.WageReturn == "CUSTOMER" ? TotalWage : 0));	
	
	this.get("SUM_TotalWage").innerHTML = Ext.util.Format.Money(TotalWage);	
	this.get("SUM_FundWage").innerHTML = Ext.util.Format.Money(FundWage);	
	this.get("SUM_AgentWage").innerHTML = Ext.util.Format.Money(AgentWage);	
	
	returnArr = YearWageCompute(record, TotalWage, YearMonths);
	
	this.get("SUM_Wage_1Year").innerHTML = returnArr[0] ? returnArr[0].amount : 0;
	this.get("SUM_Wage_2Year").innerHTML = returnArr[1] ? returnArr[1].amount : 0;
	this.get("SUM_Wage_3Year").innerHTML = returnArr[2] ? returnArr[2].amount : 0;
	this.get("SUM_Wage_4Year").innerHTML = returnArr[3] ? returnArr[3].amount : 0;
}

RequestInfo.prototype.LoadSummarySHRTFUND = function(record, paymentStore){

	if(paymentStore == null)
	{
		this.paymentStore = new Ext.data.Store({
			proxy:{
				type: 'jsonp',
				url: this.address_prefix + "request.data.php?"+
					"task=GetPartPayments&RequestID=" + this.RequestID,
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields : ["PayDate", "PayAmount"],
			autoLoad : true,
			listeners :{
				load : function(){
					RequestInfoObject.LoadSummarySHRTFUND(record, this);
				}
			}
		});
		return;
	}
	if(this.paymentStore.totalCount == 0)
		return;
	//--------------- total pay months -------------
	firstPay = MiladiToShamsi(this.paymentStore.getAt(0).data.PayDate);
	//LastPay = MiladiToShamsi(this.paymentStore.getAt(this.paymentStore.getCount()-1).data.PayDate);
	//paymentPeriod = DateModule.GetDiffInMonth(firstPay, LastPay);
	paymentPeriod = record.data.PayDuration*1;
	if(paymentPeriod == 0)
	{
		LastPay = MiladiToShamsi(this.paymentStore.getAt(this.paymentStore.getCount()-1).data.PayDate);
		paymentPeriod = DateModule.GetDiffInMonth(firstPay, LastPay);
	}
	//----------------------------------------------
	totalWage = 0;
	wages = new Array();
	for(j=0; j<this.paymentStore.getCount(); j++)
	{
		wages.push(new Array());
		wageindex = wages.length-1;
		for(i=0; i < record.data.InstallmentCount; i++)
		{
			monthplus = paymentPeriod + record.data.DelayMonths*1 + (i+1)*record.data.PayInterval*1;
			
			installmentDate = MiladiToShamsi(this.paymentStore.getAt(0).data.PayDate);
			installmentDate = DateModule.AddToJDate(installmentDate, 0, monthplus);
			installmentDate = ShamsiToMiladi(installmentDate);
			
			jdiff = DateModule.GDateMinusGDate(installmentDate, this.paymentStore.getAt(j).data.PayDate);
			
			wage = Math.round((this.paymentStore.getAt(j).data.PayAmount/record.data.InstallmentCount)*
				jdiff*record.data.CustomerWage/36500);
			wages[wageindex].push(wage);
			totalWage += wage;
		}
	}
	InstallmentAmount = Math.round(record.data.PartAmount/record.data.InstallmentCount) + 
				Math.round(totalWage/record.data.InstallmentCount);
			
	this.get("SUM_InstallmentAmount").innerHTML = Ext.util.Format.Money(InstallmentAmount);
	this.get("SUM_LastInstallmentAmount").innerHTML = Ext.util.Format.Money(InstallmentAmount);
	this.get("SUM_TotalWage").innerHTML = Ext.util.Format.Money(totalWage);	
	this.get("SUM_NetAmount").innerHTML = Ext.util.Format.Money(record.data.PartAmount);	
}

//.........................................................

RequestInfo.prototype.LoadInstallments = function(){
	
	if(!this.InstallmentsWin)
	{
		this.InstallmentsWin = new Ext.window.Window({
			width : 770,
			title : "لیست اقساط",
			height : 410,
			modal : true,
			loader : {
				url : this.address_prefix + "installments.php",
				method : "post",
				scripts : true
			},
			closeAction : "hide"
		});
		
		Ext.getCmp(this.TabID).add(this.InstallmentsWin);
	}
	this.InstallmentsWin.show();
	this.InstallmentsWin.center();
	
	this.InstallmentsWin.loader.load({
		params : {
			MenuID : this.MenuID,
			ExtTabID : this.InstallmentsWin.getEl().id,
			RequestID : this.RequestID
		}
	});
}

RequestInfo.prototype.LoadBackPays = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	if(!record)
	{
		Ext.MessageBox.alert("","ابتدا شرایط مورد نظر خود را انتخاب کنید");
		return;
	}
	
	if(!this.PayWin)
	{
		this.PayWin = new Ext.window.Window({
			width : 870,
			title : "لیست پرداخت های مشتری",
			height : 410,
			modal : true,
			loader : {
				url : this.address_prefix + "BackPays.php",
				method : "post",
				scripts : true
			},
			closeAction : "hide"
		});
		
		Ext.getCmp(this.TabID).add(this.PayWin);
	}
	this.PayWin.show();
	this.PayWin.center();
	
	this.PayWin.loader.load({
		params : {
			MenuID : this.MenuID,
			ExtTabID : this.PayWin.getEl().id,
			RequestID : this.RequestID
		}
	});
	
	this.PayWin.show();
	this.PayWin.center();
}
//.........................................................

RequestInfo.prototype.StartFlow = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به شروع گردش تایید پرداخت شرایط می باشید؟",function(btn){
		
		if(btn == "no")
			return;
		
		me = RequestInfoObject;
		var record = me.grid.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'request.data.php',
			method: "POST",
			params: {
				task: "StartFlow",
				PartID : record.data.PartID
			},
			success: function(response){
				mask.hide();
				RequestInfoObject.grid.getStore().load();
			}
		});
	});
}

RequestInfo.prototype.ShowPartHistory = function(){

	if(!this.HistoryWin)
	{
		this.HistoryWin = new Ext.window.Window({
			title: 'سابقه گردش',
			modal : true,
			autoScroll : true,
			width: 700,
			height : 500,
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "../../office/workflow/history.php",
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
		Ext.getCmp(this.TabID).add(this.HistoryWin);
	}
	this.HistoryWin.show();
	this.HistoryWin.center();
	this.HistoryWin.loader.load({
		params : {
			FlowID : 1,
			ObjectID : this.grid.getSelectionModel().getLastSelected().data.PartID
		}
	});
}

RequestInfo.prototype.LoadPayments = function(){
	
	if(!this.PaymentWin)
	{
		this.PaymentWin = new Ext.window.Window({
			width : 422,
			title : "مراحل پرداخت",
			height : 305,
			modal : true,
			loader : {
				url : this.address_prefix + "payments.php",
				method : "post",
				scripts : true
			},
			closeAction : "hide"
		});
		
		Ext.getCmp(this.TabID).add(this.PaymentWin);
	}
	this.PaymentWin.show();
	this.PaymentWin.center();
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	this.PaymentWin.loader.load({
		params : {
			MenuID : this.MenuID,
			ExtTabID : this.PaymentWin.getEl().id,
			RequestID : this.RequestID
		}
	});
}
//.........................................................

RequestInfo.prototype.ShowMessages = function(){

	if(!this.messagesWin)
	{
		this.messagesWin = new Ext.window.Window({
			width : 713,
			title : "پیام های وام",
			height : 435,
			modal : true,
			loader : {
				url : this.address_prefix + "messages.php",
				method : "post",
				scripts : true
			},
			closeAction : "hide"
		});
		
		Ext.getCmp(this.TabID).add(this.messagesWin);
		
		this.messagesWin.show();
		this.messagesWin.center();

		this.messagesWin.loader.load({
			params : {
				MenuID : this.MenuID,
				ExtTabID : this.messagesWin.getEl().id,
				RequestID : this.RequestID
			}
		});
		return;
	}
	this.messagesWin.show();
	this.messagesWin.center();
}

RequestInfo.prototype.ShowEvents = function(){

	if(!this.EventsWin)
	{
		this.EventsWin = new Ext.window.Window({
			title: 'رویدادهای مرتبط با وام',
			modal : true,
			autoScroll : true,
			width: 700, 
			height : 400,
			bodyStyle : "background-color:white",
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "events.php",
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
		Ext.getCmp(this.TabID).add(this.EventsWin);
		this.EventsWin.show();
		this.EventsWin.center();	
		this.EventsWin.loader.load({
			params : {
				MenuID : this.MenuID,
				ExtTabID : this.EventsWin.getEl().id,
				RequestID : this.RequestID
			}
		});
		return;
	}
	this.EventsWin.show();
	this.EventsWin.center();	
}

RequestInfo.prototype.ShowCosts = function(){

	if(!this.CostsWin)
	{
		this.CostsWin = new Ext.window.Window({
			title: 'هزینه های ضمانت نامه',
			modal : true,
			autoScroll : true,
			width: 600,
			height : 400,
			bodyStyle : "background-color:white",
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "costs.php",
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
		Ext.getCmp(this.TabID).add(this.CostsWin);
	}
	this.CostsWin.show();
	this.CostsWin.center();	
	this.CostsWin.loader.load({
		params : {
			MenuID : this.MenuID,
			ExtTabID : this.CostsWin.getEl().id,
			RequestID : this.RequestID
		}
	});
}

RequestInfo.prototype.ShowCheckList = function(){

	if(!this.CostsWin)
	{
		this.CostsWin = new Ext.window.Window({
			title: 'چک لیست',
			modal : true,
			autoScroll : true,
			width: 600,
			height : 400,
			bodyStyle : "background-color:white",
			closeAction : "hide",
			loader : {
				url : "baseInfo/checkValues.php",
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
		Ext.getCmp(this.TabID).add(this.CostsWin);
	}
	this.CostsWin.show();
	this.CostsWin.center();	
	this.CostsWin.loader.load({
		params : {
			MenuID : this.MenuID,
			ExtTabID : this.CostsWin.getEl().id,
			SourceID : this.RequestID,
			SourceType : <?= SOURCETYPE_LOAN ?>,
		}
	});
}

</script>
