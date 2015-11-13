<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------
	
RequestInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	RequestID : <?= $RequestID ?>,
	StatusID : 0,
	User : '<?= $User ?>',

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function RequestInfo(){
	
	this.mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	this.mask.show();
	
	this.grid = <?= $grid ?>;
	this.grid.on("itemclick", function(){
		record = RequestInfoObject.grid.getSelectionModel().getLastSelected();
		RequestInfoObject.PartsPanel.loadRecord(record);
		RequestInfoObject.PartsPanel.doLayout();
		RequestInfoObject.LoadSummary(record);
		RequestInfoObject.PartsPanel.down("[name=PayInterval]").setValue(record.data.PayInterval + " " + 
			(record.data.IntervalType == "DAY" ? "روز" : "ماه"));
	});
	
	this.InstallmentGrid = <?= $grid2 ?>;
	
	if(this.RequestID > 0)
		this.grid.getStore().proxy.extraParams = { RequestID : this.RequestID };
		
	this.BuildForms();
	
	if(this.RequestID > 0)
		var t = setInterval(function(){
			if(!RequestInfo.buildRender.isLoading())
			{
				clearInterval(t);
				RequestInfoObject.LoadRequestInfo();
			}
		}, 100);
		
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
		fields : ["RequestID","BranchID","BranchName","ReqPersonID","ReqPersonRole","ReqFullname","LoanPersonID",
					"LoanFullname","ReqDate","ReqAmount","ReqDetails","BorrowerDesc","BorrowerID",
					"guarantees","AgentGuarantee","StatusID","DocumentDesc","SupportPersonID"],
		autoLoad : true,
		listeners :{
			load : function(){
				me = RequestInfoObject;

				//..........................................................

				me.companyPanel.loadRecord(this.getAt(0));
				if(this.getAt(0).data.AgentGuarantee == "YES")
					me.companyPanel.down("[name=AgentGuarantee]").setValue(true);
				if(this.getAt(0).data.guarantees != null)
				{
					arr = this.getAt(0).data.guarantees.split(",");
					for(i=0; i<arr.length; i++)
						if(arr[i] != "")
							me.companyPanel.down("[name=guarantee_" + arr[i] + "]").setValue(true);
				}
				//..........................................................

				me.CustomizeForm(this.getAt(0));
				me.mask.hide();
			}
		}
	});
}

RequestInfo.OperationRender = function(v,p,r){
	
	return "<div  title='عملیات' class='setting' onclick='RequestInfoObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

RequestInfo.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	ReqRecord = this.store.getAt(0);
	
	var op_menu = new Ext.menu.Menu();

	if(this.User == "Staff")
	{
		if(record.data.IsStarted == "NO" && record.data.StatusID == "70")
		{
			op_menu.add({text: 'شروع گردش فرم',iconCls: 'refresh',
			handler : function(){ return RequestInfoObject.StartFlow(); }});
		}	
		if(record.data.IsEnded == "YES")
		{
			op_menu.add({text: 'اقساط',iconCls: 'list',
			handler : function(){ return RequestInfoObject.LoadInstallments(); }});
		
			if(record.data.IsPayed == "NO")
				op_menu.add({text: 'پرداخت',iconCls: 'epay',
				handler : function(){ return RequestInfoObject.PayPart(); }});
		}
		
	}	
	if(record.data.IsPayed == "NO" && record.data.IsStarted == "NO")
	{
		if((this.User == "Agent" && record.data.StatusID== "1") || 
			(this.User == "Staff" && record.data.StatusID != "70" && ReqRecord.data.ReqPersonRole != "Agent"))
		{
			op_menu.add({text: 'ویرایش',iconCls: 'edit', 
				handler : function(){ return RequestInfoObject.PartInfo("edit"); }});

			op_menu.add({text: 'حذف',iconCls: 'remove', 
				handler : function(){ return RequestInfoObject.DeletePart(); }});
		}
	}
	
	if(record.data.StatusID == "70")
		op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return RequestInfoObject.ShowHistory(); }});
	
	op_menu.showAt(e.pageX-120, e.pageY);
}

RequestInfo.prototype.BuildForms = function(){
	
	this.companyPanel = new Ext.form.FormPanel({
		renderTo : this.get("mainForm"),
		width: 750,
		border : 0,
		items: [{
			xtype : "fieldset",
			title : "اطلاعات درخواست",
			layout : {
				type : "column",
				columns : 2
			},			
			defaults : {
				width : 350,				
				labelWidth : 130
			},			
			items : [{
				xtype : "displayfield",
				fieldCls : "blueText",
				name : "ReqFullname",
				style : "margin-bottom:10px",
				fieldLabel : "درخواست کننده"
			},{
				xtype : "combo",
				hidden : true,
				store : new Ext.data.SimpleStore({
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + '../../person/persons.data.php?' +
							"task=selectPersons&UserType=IsSupporter",
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields : ['PersonID','fullname'],
					autoLoad : true					
				}),
				fieldLabel : "معرفی کننده",
				displayField : "fullname",
				valueField : "PersonID",
				name : "SupportPersonID",
				itemId : "cmp_Supporter"
			},{
				xtype : "combo",
				store : new Ext.data.SimpleStore({
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + '../../person/persons.data.php?' +
							"task=selectPersons&UserType=IsCustomer",
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields : ['PersonID','fullname'],
					autoLoad : true					
				}),
				fieldLabel : "مشتری",
				displayField : "fullname",
				valueField : "PersonID",
				name : "LoanPersonID"
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
						url: this.address_prefix + '../../framework/baseinfo/baseinfo.data.php?' +
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
				colspan : 2,
				style : "margin-right:5px",
				layout : "hbox",
				itemId : "cmp_guarantees",
				width : 700,
				height : 23,
				items : [{
					xtype : "displayfield",
					value: "تضمین :",
					width : 110
				}],
				listeners :{
					afterrender : function(){
						RequestInfo.buildRender = new Ext.data.SimpleStore({
							proxy: {
								type: 'jsonp',
								url: '/loan/request/request.data.php?task=Selectguarantees',
								reader: {root: 'rows',totalProperty: 'totalCount'}
							},
							fields : ['InfoID','InfoDesc'],
							autoLoad : true,
							listeners : {
								load : function(){
									for(i=0; i<this.getCount();i++)
									{
										record = this.getAt(i);
										RequestInfoObject.companyPanel.down("[itemId=cmp_guarantees]").add({
											xtype : "checkbox",
											boxLabel: record.data.InfoDesc,
											name: 'guarantee_' + record.data.InfoID,	
											inputValue: 1,
											style : "margin-left : 20px"
										});
									}
								}
							} 
						});
					}
				}
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
				xtype : "button",
				width : 100,
				itemId : "cmp_save",
				iconCls : "save",
				colspan : 2,
				style : "float:left;margin-left : 27px",
				text : "ذخیره",
				handler : function(){ RequestInfoObject.SaveRequest('save'); }
			}]
		}]		
	});
	
	this.PartsPanel =  new Ext.form.FormPanel({
		renderTo : this.get("PartForm"),
		width: 750,
		border : 0,
		items: [{
			xtype : "fieldset",
			title : "مراحل پرداخت وام",
			layout : "column",
			columns : 2,
			items :[{
				xtype : "container",
				colspan : 2,
				width : 750,
				cls : "blueText",
				html : "برای مشاهده جزئیات هر مرحله روی عنوان مرحله کلیک کنید" + "<hr>"
			},this.grid,{
				xtype : "container",
				style : "margin-right:10px",
				layout : {
					type : "table",
					columns : 3
				},
				defaults : {
					xtype : "displayfield",
					hideTrigger : true,
					width : 180,
					labelWidth : 80,
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
					fieldLabel: 'کارمزد مشتری',
					name: 'CustomerWage'	,		
					renderer : function(v){ return v + " %"}
				},{
					fieldLabel: 'سهم صندوق',
					name: 'FundWage',
					colspan : 2,
					renderer : function(v){ return v + " %"}
				},{
					colspan : 3,
					xtype : "container",
					width : 540,
					contentEl : this.get("summaryDIV")
				}]
			}]
		}],
		buttons : [{
			text : "ذخیره و ارسال درخواست",
			iconCls : "save",
			itemId : "cmp_save",
			handler : function(){ RequestInfoObject.SaveRequest('send'); }
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
		
}

RequestInfo.prototype.CustomizeForm = function(record){
	
	if(this.User == "Staff")
	{
		this.PartsPanel.down("[itemId=cmp_save]").hide();
		
		if(record == null)
		{
			this.companyPanel.down("[itemId=cmp_Supporter]").show();
			this.companyPanel.down("[name=ReqFullname]").hide();
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
			this.companyPanel.down("[itemId=cmp_save]").hide();
		
		this.companyPanel.down("[name=ReqFullname]").hide();
		this.companyPanel.down("[name=LoanPersonID]").hide();		
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
			this.PartsPanel.down("[itemId=cmp_save]").hide();
			this.grid.down("[itemId=addPart]").hide();
			this.grid.down("[dataIndex=PartID]").hide();
		}	
		if(this.User == "Staff")
		{
			if(record.data.ReqPersonRole == "Agent")
			{
				if(record.data.StatusID == "10")
				{
					this.companyPanel.getEl().readonly(new Array("LoanPersonID","DocumentDesc"));
				}
				else
				{
					this.companyPanel.getEl().readonly();
					this.companyPanel.down("[itemId=cmp_save]").hide();
				}
				this.companyPanel.doLayout();
				this.grid.down("[itemId=addPart]").hide();
				//this.grid.down("[dataIndex=PartID]").hide();
			}
			else
			{
				if(record.data.StatusID == "70")
				{
					this.companyPanel.getEl().readonly();
					this.companyPanel.down("[itemId=cmp_save]").hide();
				}
			}
			
			if(record.data.ReqPersonRole == "Staff")
			{
				this.companyPanel.down("[itemId=cmp_Supporter]").show();
				this.companyPanel.down("[name=ReqFullname]").hide();
				this.companyPanel.down("[name=BorrowerDesc]").hide();
				this.companyPanel.down("[name=BorrowerID]").hide();
			}
		}	
		if(this.User == "Customer")
		{
			this.companyPanel.down("[itemId=cmp_Supporter]").show();
			this.companyPanel.down("[name=LoanPersonID]").hide();
			this.companyPanel.down("[name=BorrowerDesc]").hide();
			this.companyPanel.down("[name=BorrowerID]").hide();
			this.companyPanel.down("[name=ReqDetails]").hide();
			this.companyPanel.down("[itemId=cmp_save]").hide();
			if(record.data.ReqPersonRole == "Staff")
				this.companyPanel.down("[name=AgentGuarantee]").hide();
			
			this.companyPanel.getEl().readonly();
			
			this.grid.down("[itemId=addPart]").hide();
			this.grid.down("[dataIndex=PartID]").hide();	
			this.PartsPanel.down("[itemId=cmp_save]").hide();
			this.PartsPanel.down("[name=FundWage]").getEl().dom.style.display = "none";
			this.get("TR_FundWage").style.display = "none";
			this.get("TR_AgentWage").style.display = "none";
		}
		this.companyPanel.doLayout();
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

RequestInfo.prototype.PartInfo = function(mode){
	
	if(!this.PartWin)
	{
		this.PartWin = new Ext.window.Window({
			width : 500,
			height : 230,
			modal : true,
			closeAction : 'hide',
			title : "ایجاد مرحله جدید",
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
					fieldLabel : "عنوان مرحله",
					colspan : 2,
					width : 500
				},{
					xtype : "currencyfield",
					name : "PartAmount",
					fieldLabel : "مبلغ پرداخت",
					width : 220
				},{
					xtype : "shdatefield",
					name : "PartDate",
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
	}
	
	this.PartWin.show();
	if(mode == "edit")
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

	function PMT(F8, F9, F7, YearMonths) {  
		F8 = F8/(YearMonths*100);
		F7 = -F7;
		return F8 * F7 * Math.pow((1 + F8), F9) / (1 - Math.pow((1 + F8), F9)); 
	} 
	function ComputeWage(F7, F8, F9, YearMonths){
		
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
		return Ext.util.Format.Money(Math.round(val));
	}

	YearMonths = 12;
	if(record.data.IntervalType == "DAY")
		YearMonths = Math.floor(365/record.data.PayInterval);

	FirstPay = roundUp(PMT(record.data.CustomerWage,record.data.InstallmentCount, record.data.PartAmount, YearMonths),-3);
	TotalWage = Math.round(ComputeWage(record.data.PartAmount, record.data.CustomerWage/100, record.data.InstallmentCount, YearMonths));
	FundWage = Math.round((record.data.FundWage/record.data.CustomerWage)*TotalWage);
	AgentWage = TotalWage - FundWage;
	
	TotalDelay = Math.round(record.data.PartAmount*record.data.CustomerWage*record.data.DelayMonths/1200);
	LastPay = record.data.PartAmount*1 + TotalWage - FirstPay*(record.data.InstallmentCount-1);
	
	this.get("SUM_InstallmentAmount").innerHTML = Ext.util.Format.Money(FirstPay);
	this.get("SUM_LastInstallmentAmount").innerHTML = Ext.util.Format.Money(LastPay);
	this.get("SUM_Delay").innerHTML = Ext.util.Format.Money(TotalDelay);
	this.get("SUM_NetAmount").innerHTML = Ext.util.Format.Money(record.data.PartAmount - TotalDelay);	
	
	this.get("SUM_TotalWage").innerHTML = Ext.util.Format.Money(TotalWage);	
	this.get("SUM_FundWage").innerHTML = Ext.util.Format.Money(FundWage);	
	this.get("SUM_AgentWage").innerHTML = Ext.util.Format.Money(AgentWage);	
	
	this.get("SUM_Wage_1Year").innerHTML = YearWageCompute(record, TotalWage, 1, YearMonths);
	this.get("SUM_Wage_2Year").innerHTML = YearWageCompute(record, TotalWage, 2, YearMonths);
	this.get("SUM_Wage_3Year").innerHTML = YearWageCompute(record, TotalWage, 3, YearMonths);
	this.get("SUM_Wage_4Year").innerHTML = YearWageCompute(record, TotalWage, 4, YearMonths);
}

//.........................................................

RequestInfo.prototype.LoadInstallments = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	if(!record)
	{
		Ext.MessageBox.alert("","ابتدا مرحله مورد نظر خود را انتخاب کنید");
		return;
	}
	
	if(!this.InstallmentsWin)
	{
		this.InstallmentGrid.plugins[0].on("beforeedit", function(editor,e){
			if(e.record.data.IsPayed == "YES")
				return false;
		});
		this.InstallmentsWin = new Ext.window.Window({
			width : 700,
			title : "لیست اقساط",
			height : 500,
			modal : true,
			items : this.InstallmentGrid,
			closeAction : "hide"
		});
		
		Ext.getCmp(this.TabID).add(this.InstallmentsWin);
	}
	
	this.InstallmentGrid.getStore().proxy.extraParams = {
		PartID : record.data.PartID
	};
	
	if(record.data.IsPayed == "YES")
		this.InstallmentGrid.down("[itemId=cmp_computeInstallment]").hide();
	else
		this.InstallmentGrid.down("[itemId=cmp_computeInstallment]").show();
	
	this.InstallmentGrid.getStore().load();
	this.InstallmentsWin.show();
	this.InstallmentsWin.center();
}

RequestInfo.prototype.ComputeInstallments = function(){
	
	Ext.MessageBox.confirm("","در صورت محاسبه مجدد کلیه ردیف ها حذف و مجدد محاسبه و ایجاد می شوند <br>" + 
		"آیا مایل به محاسبه مجدد می باشید؟",function(btn){
		if(btn == "no")
			return;
		me = RequestInfoObject;
		var record = me.grid.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(me.InstallmentsWin, {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'request.data.php',
			method: "POST",
			params: {
				task: "ComputeInstallments",
				PartID : record.data.PartID
			},
			success: function(response){
				mask.hide();
				RequestInfoObject.InstallmentGrid.getStore().load();
			}
		});
	});
	
}

RequestInfo.prototype.SavePartPayment = function(store, record){

	mask = new Ext.LoadMask(this.InstallmentGrid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'request.data.php',
		method: "POST",
		params: {
			task: "SavePartPayment",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				RequestInfoObject.InstallmentGrid.getStore().load();
			}
			else
			{
				alert("خطا در اجرای عملیات");
			}
		},
		failure: function(){}
	});
}

//.........................................................

RequestInfo.prototype.PayPart = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به پرداخت این مرحله از وام می باشید؟",function(btn){
		
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
				task: "PayPart",
				PartID : record.data.PartID
			},
			success: function(response){
				
				result = Ext.decode(response.responseText);
				if(!result.success)
					Ext.MessageBox.alert("", result.data);
				else
					Ext.MessageBox.alert("", "سند پرداخت با موفقیت صادر گردید");
				
				mask.hide();
				RequestInfoObject.grid.getStore().load();
			}
		});
	});
}

RequestInfo.prototype.StartFlow = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به شروع گردش تایید پرداخت مرحله می باشید؟",function(btn){
		
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

RequestInfo.prototype.ShowHistory = function(){

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
