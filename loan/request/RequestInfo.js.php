<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------
	
RequestInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

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
	this.grid.on("select", function(){
		record = RequestInfoObject.grid.getSelectionModel().getLastSelected();
		RequestInfoObject.PartsPanel.loadRecord(record);
		RequestInfoObject.PartsPanel.doLayout();
		RequestInfoObject.LoadSummary(record);
		RequestInfoObject.PartsPanel.down("[name=PayInterval]").setValue(record.data.PayInterval + " " + 
			(record.data.IntervalType == "DAY" ? "روز" : "ماه"));
	});
	this.grid.getStore().on("load", function(){
		if(this.getCount() > 0)
			RequestInfoObject.grid.getSelectionModel().select(0);
	});
	
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
					"guarantees","AgentGuarantee","StatusID","DocumentDesc","imp_VamCode","IsEnded",
					"MaxFundWage","SubAgentID"],
		autoLoad : true,
		listeners :{
			load : function(){
				
				me = RequestInfoObject;
				
				if(me.RequestRecord != null)
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
				oldInfo = record.data.imp_VamCode != null ? "شماره وام سیستم قدیم : " + record.data.imp_VamCode : "";
				me.companyPanel.down("[itemId=oldInfo]").update(oldInfo);
				//..........................................................
				if(record.data.IsEnded == "YES")
				{
					me.grid.down("[itemId=addPart]").hide();
				}
				//..........................................................
				if(record.data.AgentGuarantee == "YES")
					me.companyPanel.down("[name=AgentGuarantee]").setValue(true);
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
							me.companyPanel.down("[name=LoanPersonID]").setValue(this.getAt(0).data.PersonID);
						}
					});
				}	
				var R2 = false;
				if(record.data.ReqPersonID > 0)
				{
					R2 = me.companyPanel.down("[name=ReqPersonID]").getStore().load({
						params : {
							PersonID : record.data.ReqPersonID
						},
						callback : function(){
							me.companyPanel.down("[name=ReqPersonID]").setValue(this.getAt(0).data.PersonID);
						}
					});
				}				
				//..........................................................
				var t = setInterval(function(){
					if((!R1 || !R1.isLoading()) && (!R2 || !R2.isLoading()))
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
	
	if(RequestInfoObject.User == "Staff")
		return "<div  title='عملیات' class='setting' onclick='RequestInfoObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";

	if(record.data.IsStarted == "NO")
	{
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
}

RequestInfo.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	ReqRecord = this.store.getAt(0);
	
	var op_menu = new Ext.menu.Menu();

	if(record.data.imp_VamCode*1 > 0)
	{
		op_menu.add({text: 'اقساط',iconCls: 'list',
		handler : function(){ return RequestInfoObject.LoadInstallments(); }});
	
		op_menu.add({text: 'پرداخت های مشتری',iconCls: 'list',
		handler : function(){ return RequestInfoObject.LoadPays(); }});
		
		if(this.RequestRecord.data.IsEnded == "NO")
			op_menu.add({text: 'ویرایش',iconCls: 'edit', 
			handler : function(){ return RequestInfoObject.PartInfo(true); }});
		
		op_menu.showAt(e.pageX-120, e.pageY);
		return;
	}
	if(record.data.IsStarted == "NO" && this.RequestRecord.data.IsEnded == "NO")
	{
		if(record.data.StatusID == "70")
			op_menu.add({text: 'شروع گردش فرم',iconCls: 'refresh',
			handler : function(){ return RequestInfoObject.StartFlow(); }});
		
		op_menu.add({text: 'ویرایش',iconCls: 'edit', 
			handler : function(){ return RequestInfoObject.PartInfo(true); }});

		op_menu.add({text: 'حذف',iconCls: 'remove', 
			handler : function(){ return RequestInfoObject.DeletePart(); }});
				
	}	
	if(record.data.IsEnded == "YES")
	{
		op_menu.add({text: 'اقساط',iconCls: 'list',
		handler : function(){ return RequestInfoObject.LoadInstallments(); }});

		op_menu.add({text: 'پرداخت',iconCls: 'epay',
		handler : function(){ return RequestInfoObject.PayInfo(); }});
	
		if(record.data.IsPaid == "YES" && this.RequestRecord.data.IsEnded == "NO")
			op_menu.add({text: 'اتمام فاز و ایجاد فاز جدید',iconCls: "app",
			handler : function(){ return RequestInfoObject.EndPart(); }});
	}		
	
	if(record.data.StatusID == "70")
		op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return RequestInfoObject.ShowPartHistory(); }});
	
	op_menu.showAt(e.pageX-120, e.pageY);
}

RequestInfo.prototype.BuildForms = function(){

   this.PartsPanel =  new Ext.form.FormPanel({
		width: 750,
		border : 0,
		items: [{
			xtype : "fieldset",
			title : "مراحل پرداخت وام",
			layout : "column",
			columns : 2,
			items :[this.grid,{
				xtype : "container",
				style : "margin-right:10px",
				layout : {
					type : "table",
					columns : 3
				},
				defaults : {
					xtype : "displayfield",
					hideTrigger : true,
					width : 170,
					labelWidth : 80,
					style : "margin-bottom:5px",
					fieldCls : "blueText"
				},
				items : [{
					fieldLabel: 'مبلغ پرداخت',
					name: 'PartAmount',
					width : 200,
					renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
				},{
					fieldLabel: 'تاریخ پرداخت',
					name: 'PartDate',
					renderer : function(v){return MiladiToShamsi(v);}
				},{
					fieldLabel: 'فاصله اقساط',
					name: 'PayInterval'
				},{
					fieldLabel: 'مدت تنفس',
					name: 'DelayMonths',
					renderer : function(v){ return v + " ماه"}
				},{
					fieldLabel: 'تعداد اقساط',
					name: 'InstallmentCount'
				},{
					fieldLabel: 'درصد دیرکرد',
					name: 'ForfeitPercent',
					renderer : function(v){ return v + " %"}
				},{
					name : "WageReturn",
					width : 200,
					fieldLabel: 'پرداخت کارمزد',
					renderer : function(v){
						if(v == "CUSTOMER") return "هنگام پرداخت وام";
						if(v == "AGENT") return 'سپرده سرمایه گذار';
						if(v == "INSTALLMENT") return 'طی اقساط';
					}
				},{
					fieldLabel: 'کارمزد مشتری',
					name: 'CustomerWage'	,		
					renderer : function(v){ return v + " %"}
				},{
					fieldLabel: 'سهم صندوق',
					name: 'FundWage',
					renderer : function(v){ return v + " %"}
				},{
					colspan : 3,
					xtype : "container",
					width : 540,
					contentEl : this.get("summaryDIV")
				}]
			}]
		}]
	});
	
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
						"task=selectPersons&UserTypes=IsAgent,IsSupporter",
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
			displayField : "BranchName",
			valueField : "BranchID",
			name : "BranchID"
		},{
			xtype : "container",
			itemId : "oldInfo",
			width : 300,
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
			xtype : "checkbox",
			name : "AgentGuarantee",
			value : "YES",
			colspan : 2,
			fieldLabel : "با ضمانت سرمایه گذار"
		},{
			xtype : "textarea",
			fieldLabel : "توضیحات",
			width : 700,
			rows : 1,
			colspan : 2,				
			name : "ReqDetails"
		},{
			xtype : "textarea",
			fieldLabel : "توضیحات مدارک",
			colspan : 2,
			width : 700,
			rows : 1,
			name : "DocumentDesc"
		},{
			xtype : "container",
			colspan : 2,
			items : this.PartsPanel
		}],
		buttons :[{
			text : 'مدارک وام',
			hidden : true,
			iconCls : "attach",
			itemId : "cmp_LoanDocuments",
			handler : function(){ RequestInfoObject.LoanDocuments('loan'); }
		},{
			text : 'مدارک وام گیرنده',
			hidden : true,
			iconCls : "attach",
			itemId : "cmp_PersonDocuments",
			handler : function(){ RequestInfoObject.LoanDocuments('person'); }
		},{
			text : 'سابقه درخواست',
			iconCls : "history",
			hidden : true,
			itemId : "cmp_history",
			handler : function(){ RequestInfoObject.ShowHistory(); }
		},'->',{
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
		
		if(record == null)
		{
			this.companyPanel.down("[name=BorrowerDesc]").hide();
			this.companyPanel.down("[name=BorrowerID]").hide();
			this.companyPanel.down("[name=AgentGuarantee]").hide();
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
		}		
		
		//this.companyPanel.down("[name=ReqFullname]").hide();		
		this.companyPanel.down("[name=BranchID]").setValue(1);
		this.companyPanel.down("[name=BranchID]").hide();	
		this.companyPanel.down("[name=LoanPersonID]").hide();
		
		this.companyPanel.doLayout();
	}
	
	if(record != null)
	{
		if(this.User == "Agent" && record.data.StatusID != "1" && record.data.StatusID != "20")
		{
			this.companyPanel.getEl().readonly();
			this.companyPanel.down("[itemId=cmp_save]").hide();
			this.companyPanel.down("[itemId=cmp_saveAndSend]").hide();
			//this.grid.down("[itemId=addPart]").hide();
			this.grid.down("[dataIndex=PartID]").hide();
		}	
		if(this.User == "Staff")
		{
			if(record.data.IsEnded == "YES")
			{
				this.companyPanel.getEl().readonly();
				this.companyPanel.down("[itemId=cmp_save]").hide();
			}
			else
			{
				if(record.data.StatusID == "70")
				{
					if(record.data.imp_VamCode*1 == null || record.data.imp_VamCode == "")
					{
						this.companyPanel.getEl().readonly();
						this.companyPanel.down("[itemId=cmp_save]").hide();
					}					
				}
				if(record.data.ReqPersonID == null)
				{
					this.companyPanel.down("[name=BorrowerDesc]").hide();
					this.companyPanel.down("[name=BorrowerID]").hide();
				}
			}			
		}	
		if(this.User == "Customer")
		{
			//this.companyPanel.down("[itemId=cmp_requester]").show();
			this.companyPanel.down("[name=LoanPersonID]").hide();
			this.companyPanel.down("[name=BorrowerDesc]").hide();
			this.companyPanel.down("[name=BorrowerID]").hide();
			this.companyPanel.down("[name=ReqDetails]").hide();
			this.companyPanel.down("[itemId=cmp_save]").hide();
			this.companyPanel.down("[name=AgentGuarantee]").hide();
			
			this.companyPanel.getEl().readonly();
			
			this.grid.down("[itemId=addPart]").hide();
			this.grid.down("[dataIndex=PartID]").hide();	
			this.companyPanel.down("[itemId=cmp_saveAndSend]").hide();
			this.PartsPanel.down("[name=FundWage]").getEl().dom.style.display = "none";
			this.get("TR_FundWage").style.display = "none";
			this.get("TR_AgentWage").style.display = "none";
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
	
	if(record != null && this.User == "Staff")
	{
		this.companyPanel.down("[itemId=cmp_LoanDocuments]").show();
		this.companyPanel.down("[itemId=cmp_PersonDocuments]").show();
		this.companyPanel.down("[itemId=cmp_history]").show();
	}
	
	if(this.ReadOnly)
		return;
	
	if(record == null)
		return;
	
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
			
			if( mode == "send")
			{
				me.companyPanel.hide();
				me.PartsPanel.hide();
				me.SendedPanel.show();
				me.SendedPanel.getComponent("requestID").
					update('شماره پیگیری درخواست : ' + me.RequestID);
			}
		},
		failure : function(){
			mask.hide();
			//Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
		}
	});
}

RequestInfo.prototype.PartInfo = function(EditMode){
	
	if(!this.PartWin)
	{
		this.PartWin = new Ext.window.Window({
			width : 500,
			height : 500,
			modal : true,
			closeAction : 'hide',
			title : "ایجاد فاز جدید",
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
					fieldLabel : "عنوان فاز",
					colspan : 2,
					width : 450
				},{
					xtype : "currencyfield",
					name : "PartAmount",
					fieldLabel : "مبلغ پرداخت",
					maxValue : this.RequestRecord.data.RequestAmount,
					width : 220
				},{
					xtype : "shdatefield",
					name : "PartDate",
					allowBlank : true,
					hideTrigger : false,
					fieldLabel : "تاریخ پرداخت",
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
					fieldLabel: 'مدت تنفس',
					name: 'DelayMonths',
					afterSubTpl : "ماه"
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
					xtype : "fieldset",
					itemId : "fs_WageCompute",
					title : "نحوه دریافت کارمزد",
					colspan :2,
					width : 450,
					style : "margin-right:10px",
					items : [{
						xtype : "radio",
						boxLabel : "پرداخت کارمزد طی اقساط",
						name : "WageReturn",
						inputValue : "INSTALLMENT",
						checked : true
					},{
						xtype : "radio",
						boxLabel : "پرداخت کارمزد از سپرده سرمایه گذار",
						name : "WageReturn",
						inputValue : "AGENT"
					},{
						xtype : "radio",						
						boxLabel : "پرداخت کارمزد هنگام پرداخت وام",
						name : "WageReturn",
						inputValue : "CUSTOMER"
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
		this.PartWin.down("[name=PayInterval]").setValue(record.data.PayInterval*1);
		this.PartWin.down("[itemId=monthInterval]").setValue(record.data.IntervalType == "MONTH" ? true : false);
		this.PartWin.down("[itemId=dayInterval]").setValue(record.data.IntervalType == "DAY" ? true : false);
	}
	else
		this.PartWin.down('form').getForm().reset();
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
		failure: function(){
			mask.hide();
		}
	});
}

RequestInfo.prototype.DeletePart = function(){

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

RequestInfo.prototype.LoadSummary = function(record){

	function PMT(F8, F9, F7, YearMonths, PayInterval) {  
		
		if(F8 == 0)
			return F7/F9;
		
		if(PayInterval == 0)
			return F7;
				
		F8 = F8/(YearMonths*100);
		F7 = -F7;
		return F8 * F7 * Math.pow((1 + F8), F9) / (1 - Math.pow((1 + F8), F9)); 
	} 
	function ComputeWage(F7, F8, F9, YearMonths, PayInterval){
		
		if(PayInterval == 0)
			return 0;
		
		if(F8 == 0)
			return 0;
		
		return (((F7*F8/YearMonths*( Math.pow((1+(F8/YearMonths)),F9)))/
			((Math.pow((1+(F8/YearMonths)),F9))-1))*F9)-F7;
	}
	function roundUp(number, digits)
	{
		var factor = Math.pow(10,digits);
		return Math.ceil(number*factor) / factor;
	}
	function YearWageCompute(record,TotalWage,yearNo, YearMonths){
		
		PayMonth = MiladiToShamsi(record.data.PartDate).split('/')[1]*1;
		PayMonth = PayMonth*YearMonths/12;
		
		FirstYearInstallmentCount = YearMonths - PayMonth;
		MidYearInstallmentCount = Math.floor((record.data.InstallmentCount-FirstYearInstallmentCount) / YearMonths);
		LastYeatInstallmentCount = (record.data.InstallmentCount-FirstYearInstallmentCount) % YearMonths;
		
		if(yearNo > MidYearInstallmentCount+2)
			return 0;
		
		F9 = record.data.InstallmentCount*1;
		var BeforeMonths = 0
		if(yearNo == 2)
			BeforeMonths = FirstYearInstallmentCount;
		else if(yearNo > 2)
			BeforeMonths = FirstYearInstallmentCount + (yearNo-2)*YearMonths;
		
		var curMonths = FirstYearInstallmentCount;
		if(yearNo > 1 && yearNo <= MidYearInstallmentCount+1)
			curMonths = YearMonths;
		else if(yearNo > MidYearInstallmentCount+1)
			curMonths = LastYeatInstallmentCount;
		
		var val = ((((F9-BeforeMonths)*(F9-BeforeMonths+1))-
			(F9-BeforeMonths-curMonths)*(F9-BeforeMonths-curMonths+1)))/(F9*(F9+1))*TotalWage;
		
		val = Math.round(val);
		val = val < 0 ? 0 : val;
		return Ext.util.Format.Money(val);
	}

	YearMonths = 12;
	if(record.data.IntervalType == "DAY")
		YearMonths = Math.floor(365/record.data.PayInterval);
	
	TotalWage = Math.round(ComputeWage(record.data.PartAmount, record.data.CustomerWage/100, 
		record.data.InstallmentCount, YearMonths, record.data.PayInterval));
		
	if(record.data.WageReturn == "CUSTOMER")
		FirstPay = roundUp(PMT(0,record.data.InstallmentCount, 
			record.data.PartAmount, YearMonths, record.data.PayInterval),-3);	
	else
		FirstPay = roundUp(PMT(record.data.CustomerWage,record.data.InstallmentCount, 
			record.data.PartAmount, YearMonths, record.data.PayInterval),-3);	
			
	TotalWage = !isInt(TotalWage) ? 0 : TotalWage;	
	FundWage = Math.round((record.data.FundWage/record.data.CustomerWage)*TotalWage);
	FundWage = !isInt(FundWage) ? 0 : FundWage;
	AgentWage = TotalWage - FundWage;
	
	TotalDelay = Math.round(record.data.PartAmount*record.data.CustomerWage*record.data.DelayMonths/1200);
	if(record.data.WageReturn == "CUSTOMER")
		LastPay = record.data.PartAmount*1 - FirstPay*(record.data.InstallmentCount-1);
	else
		LastPay = record.data.PartAmount*1 + TotalWage - FirstPay*(record.data.InstallmentCount-1);
	
	if(record.data.InstallmentCount == 1)
		LastPay = 0;
	
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
		TotalDelay - (record.data.WageReturn == "CUSTOMER" ? TotalWage : 0));	
	
	this.get("SUM_TotalWage").innerHTML = Ext.util.Format.Money(TotalWage);	
	this.get("SUM_FundWage").innerHTML = Ext.util.Format.Money(FundWage);	
	this.get("SUM_AgentWage").innerHTML = Ext.util.Format.Money(AgentWage);	
	
	this.get("SUM_Wage_1Year").innerHTML = YearWageCompute(record, TotalWage, 1, YearMonths);
	this.get("SUM_Wage_2Year").innerHTML = YearWageCompute(record, TotalWage, 2, YearMonths);
	this.get("SUM_Wage_3Year").innerHTML = YearWageCompute(record, TotalWage, 3, YearMonths);
	this.get("SUM_Wage_4Year").innerHTML = YearWageCompute(record, TotalWage, 4, YearMonths);
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
	}
	this.HistoryWin.show();
	this.HistoryWin.center();
	this.HistoryWin.loader.load({
		params : {
			RequestID : this.RequestID
		}
	});
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
				url : "../../dms/documents.php",
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
		Ext.MessageBox.confirm("","آیا مایل به تایید می باشید؟", function(btn){
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
		
		success : function(){
			
			RequestInfoObject.LoadRequestInfo();
			if(RequestInfoObject.commentWin)
				RequestInfoObject.commentWin.hide();
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

RequestInfo.prototype.LoadInstallments = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	if(!record)
	{
		Ext.MessageBox.alert("","ابتدا فاز مورد نظر خود را انتخاب کنید");
		return;
	}
	
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
			ExtTabID : this.InstallmentsWin.getEl().id,
			PartID : record.data.PartID
		}
	});
	
	this.InstallmentsWin.show();
	this.InstallmentsWin.center();
}

RequestInfo.prototype.LoadPays = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	if(!record)
	{
		Ext.MessageBox.alert("","ابتدا فاز مورد نظر خود را انتخاب کنید");
		return;
	}
	
	if(!this.PayWin)
	{
		this.PayWin = new Ext.window.Window({
			width : 870,
			title : "لیست پرداخت ها",
			height : 410,
			modal : true,
			loader : {
				url : this.address_prefix + "pays.php",
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
			ExtTabID : this.PayWin.getEl().id,
			PartID : record.data.PartID
		}
	});
	
	this.PayWin.show();
	this.PayWin.center();
}
//.........................................................

RequestInfo.prototype.PayInfo = function(){
	
	if(!this.PayInfoWin)
	{
		this.PayInfoWin = new Ext.window.Window({
			width : 400,
			height : 500,
			autoScroll : true,
			modal : true,
			title : "مبلغ پرداخت",
			bodyStyle : "background-color:white",
			loader : {
				url : this.address_prefix + "PartPayInfo.php"
			},
			closeAction : "hide",
			buttons : [{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.PayInfoWin);
	}
	
	this.PayInfoWin.show();
	this.PayInfoWin.center();
	
	this.PayInfoWin.loader.load({
		params : {
			PartID : this.grid.getSelectionModel().getLastSelected().data.PartID
		}
	});
}

RequestInfo.prototype.PayPart = function(MaxAvailablePayAmount){
	
	if(!this.PayWin)
	{
		this.PayWin = new Ext.window.Window({
			width : 202,
			modal : true,
			title : "مبلغ پرداخت",
			bodyStyle : "padding-top:4px;background-color:white",
			items : [{
				xtype : "currencyfield",
				hideTrigger : true,
				width : 190,
				name : "PayAmount"
			}],
			closeAction : "hide",
			buttons : [{
				text : "پرداخت",				
				iconCls : "epay",
				handler : function(){
					me = RequestInfoObject;
					var record = me.grid.getSelectionModel().getLastSelected();

					mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
					mask.show();

					Ext.Ajax.request({
						url: me.address_prefix +'request.data.php',
						method: "POST",
						params: {
							task: "PayPart",
							PartID : record.data.PartID,
							PayAmount : me.PayWin.down("[name=PayAmount]").getValue()						
						},
						success: function(response){

							result = Ext.decode(response.responseText);
							if(!result.success)
								Ext.MessageBox.alert("", result.data);
							
							RequestInfoObject.PayWin.hide();
							RequestInfoObject.PayInfoWin.loader.load({
								params : {
									PartID : RequestInfoObject.grid.getSelectionModel().getLastSelected().data.PartID
								},
								callback : function(){mask.hide();}
							});
						}
					});
				}
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.PayWin);
	}
	
	this.PayWin.show();
	this.PayWin.center();
	this.PayWin.down("[name=PayAmount]").setValue(MaxAvailablePayAmount);
	this.PayWin.down("[name=PayAmount]").setMaxValue(MaxAvailablePayAmount);
}

RequestInfo.prototype.ReturnPayPart = function(DocID){
	
	Ext.MessageBox.confirm("","آیا مایل به برگشت پرداخت این فاز از وام می باشید؟",function(btn){
		
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
				task: "ReturnPayPart",
				PartID : record.data.PartID,
				DocID : DocID
			},
			success: function(response){
				
				result = Ext.decode(response.responseText);
				if(!result.success)
				{
					if(result.data == "")
						Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
					else
						Ext.MessageBox.alert("", result.data);
					mask.hide();
					return;
				}				
				RequestInfoObject.PayInfoWin.loader.load({
					params : {
						PartID : RequestInfoObject.grid.getSelectionModel().getLastSelected().data.PartID
					},
					callback : function(){mask.hide();}
				});
			}
		});
	});
}

RequestInfo.prototype.EndPart = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به اتمام این فاز از وام می باشید؟" 
		+ "<br>توجه : تغییرات اعمال شده قابل برگشت نمی باشد." 
		+ "<br>فقط زمانی این کار را انجام دهید که از انجام آن مطمئن هستید" , function(btn){
				
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
				task: "EndPart",
				PartID : record.data.PartID
			},
			success: function(response){
				
				result = Ext.decode(response.responseText);
				if(!result.success)
					Ext.MessageBox.alert("error", result.data);
				else
					Ext.MessageBox.alert("", "عملیات مورد نظر با موفقیت انجام گردید");
				
				mask.hide();
				RequestInfoObject.grid.getStore().load();
			}
		});
	});
}

RequestInfo.prototype.StartFlow = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به شروع گردش تایید پرداخت فاز می باشید؟",function(btn){
		
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

</script>
