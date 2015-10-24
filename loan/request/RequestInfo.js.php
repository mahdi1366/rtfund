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

function RequestInfo()
{
	this.grid = <?= $grid ?>;
	this.grid.on("itemclick", function(){
		record = RequestInfoObject.grid.getSelectionModel().getLastSelected();
		RequestInfoObject.PartsPanel.loadRecord(record);
		RequestInfoObject.PartsPanel.doLayout();
		RequestInfoObject.LoadSummary(record);
		RequestInfoObject.PartsPanel.down("[name=PayInterval]").setValue(record.data.PayInterval + " " + 
			(record.data.IntervalType == "DAY" ? "روز" : "ماه"));
	});
	
	this.paymentGrid = <?= $grid2 ?>;
	
	if(this.RequestID > 0)
	{
		this.grid.getStore().proxy.extraParams = { RequestID : this.RequestID };
		
		mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
		mask.show();  
		this.store = new Ext.data.Store({
			proxy:{
				type: 'jsonp',
				url: this.address_prefix + "request.data.php?task=SelectAllRequests&RequestID=" + this.RequestID,
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields : ["RequestID","BranchID","BranchName","ReqPersonID","ReqFullname","LoanPersonID",
						"LoanFullname","ReqDate","ReqAmount","ReqDetails","BorrowerDesc","BorrowerID",
						"assurance","AgentGuarantee","StatusID","DocumentDesc"],
			autoLoad : true,
			listeners :{
				load : function(){
					me = RequestInfoObject;
					
					me.companyPanel.loadRecord(this.getAt(0));
					if(this.getAt(0).data.AgentGuarantee == "YES")
						me.companyPanel.down("[name=AgentGuarantee]").setValue(true);
					
					me.StatusID = this.getAt(0).StatusID;
					if(me.User == "Agent" && me.StatusID != 1)
					{
						me.companyPanel.getEl().readonly();
						me.companyPanel.down("[itemId=cmp_save]").hide();
						me.PartsPanel.down("[itemId=cmp_save]").hide();
						me.grid.down("[itemId=addPart]").hide();
						me.grid.down("[dataIndex=PartID]").hide();
					}	
					if(me.User == "Staff" && me.StatusID != "10")
					{
						me.PartsPanel.down("[itemId=cmp_save]").hide();
					}	
					if(me.User == "Customer")
					{
						me.companyPanel.down("[name=LoanPersonID]").hide();
						me.companyPanel.down("[name=BorrowerDesc]").hide();
						me.companyPanel.down("[name=BorrowerID]").hide();
						me.companyPanel.down("[name=ReqDetails]").hide();
						me.companyPanel.down("[itemId=cmp_save]").hide();
						me.companyPanel.getEl().readonly();
						me.companyPanel.doLayout();
						
						me.grid.down("[itemId=addPart]").hide();
						me.grid.down("[dataIndex=PartID]").hide();	
						me.PartsPanel.down("[itemId=cmp_save]").hide();
						me.PartsPanel.down("[name=FundWage]").getEl().dom.style.display = "none";
						me.get("TR_FundWage").style.display = "none";
						me.get("TR_AgentWage").style.display = "none";
					}
					mask.hide();
				}
			}
		});
	}
	
	this.BuildForms();
	this.CustomizeForm();
}

RequestInfo.OperationRender = function(v,p,r){
	
	st = "<div align='center' title='ویرایش' class='edit' "+
		"onclick='RequestInfoObject.PartInfo(\"edit\");' " +
		"style='float:right;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:18px;height:16'></div>" + 
		
		"<div align='center' title='حذف' class='remove' "+
		"onclick='RequestInfoObject.DeletePart();' " +
		"style='float:right;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:18px;height:16'></div>";
	
	if(RequestInfoObject.User == "Staff")
		st += "<div align='center' title='اقساط' class='list' "+
		"onclick='RequestInfoObject.LoadPartPayments();' " +
		"style='float:right;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:18px;height:16'></div>";
	
	return st;
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
				fieldLabel : "ثبت کننده درخواست"
			},{
				xtype : "combo",
				store : new Ext.data.SimpleStore({
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + '../../person/persons.data.php?' +
							"task=selectPersons&UserType=IsCumstomer",
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
				xtype : "combo",
				store : new Ext.data.SimpleStore({
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + 'request.data.php?task=SelectAssurances',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields : ['InfoID','InfoDesc'],
					autoLoad : true					
				}),
				fieldLabel : "تضمین",
				queryMode : 'local',
				allowBlank : false,
				displayField : "InfoDesc",
				valueField : "InfoID",
				name : "assurance"
			},{
				xtype : "checkbox",
				name : "AgentGuarantee",
				value : "YES",
				fieldLabel : "با ضمانت عامل"
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
		width: 770,
		border : 0,
		items: [{
			xtype : "fieldset",
			title : "مراحل پرداخت وام",
			layout : "column",
			columns : 2,
			items :[{
				xtype : "container",
				colspan : 2,
				width : 770,
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
					name: 'PayDate',
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
					name: 'PayCount'
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
					width : 560,
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
		}]
	});
		
}

RequestInfo.prototype.CustomizeForm = function(){
	
	if(this.User == "Staff")
	{
		this.PartsPanel.down("[itemId=cmp_save]").hide();
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
	}
	if(this.User == "Customer")
	{
		
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
					name : "PayDate",
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
					name: 'PayCount'
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
		this.PartWin.down("[name=PayDate]").setValue(MiladiToShamsi(record.data.PayDate));
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
		
		PayMonth = MiladiToShamsi(record.data.PayDate).split('/')[1]*1;
		FirstYearPayCount = YearMonths - PayMonth;
		MidYearPayCount = Math.floor((record.data.PayCount-FirstYearPayCount) / YearMonths);
		LastYeatPayCount = (record.data.PayCount-FirstYearPayCount) % YearMonths;
		
		if(yearNo > MidYearPayCount+2)
			return 0;
		
		F9 = record.data.PayCount*1;
		var BeforeMonths = 0
		if(yearNo == 2)
			BeforeMonths = FirstYearPayCount;
		else if(yearNo > 2)
			BeforeMonths = FirstYearPayCount + (yearNo-2)*YearMonths;
		
		var curMonths = FirstYearPayCount;
		if(yearNo > 1 && yearNo <= MidYearPayCount+1)
			curMonths = YearMonths;
		else if(yearNo > MidYearPayCount+1)
			curMonths = LastYeatPayCount;
		
		var val = ((((F9-BeforeMonths)*(F9-BeforeMonths+1))-
			(F9-BeforeMonths-curMonths)*(F9-BeforeMonths-curMonths+1)))/(F9*(F9+1))*TotalWage;
		return Ext.util.Format.Money(Math.round(val));
	}

	YearMonths = 12;
	if(record.data.IntervalType == "DAY")
		YearMonths = Math.floor(365/
		record.data.PayInterval);

	FirstPay = roundUp(PMT(record.data.CustomerWage,	record.data.PayCount, record.data.PartAmount, YearMonths),-3);
	TotalWage = Math.round(ComputeWage(record.data.PartAmount, record.data.CustomerWage/100, record.data.PayCount, YearMonths));
	FundWage = Math.round((record.data.FundWage/record.data.CustomerWage)*TotalWage);
	AgentWage = TotalWage - FundWage;
	
	TotalDelay = Math.round(record.data.PartAmount*record.data.CustomerWage*record.data.DelayMonths/
					(YearMonths*100));
	LastPay = record.data.PartAmount*1 + TotalWage - FirstPay*(record.data.PayCount-1);
	
	this.get("SUM_PayAmount").innerHTML = Ext.util.Format.Money(FirstPay);
	this.get("SUM_LastPayAmount").innerHTML = Ext.util.Format.Money(LastPay);
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

RequestInfo.prototype.LoadPartPayments = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	if(!record)
	{
		Ext.MessageBox.alert("","ابتدا مرحله مورد نظر خود را انتخاب کنید");
		return;
	}
	
	if(!this.PartPaymentsWin)
	{
		this.PartPaymentsWin = new Ext.window.Window({
			width : 700,
			title : "لیست اقساط",
			height : 500,
			modal : true,
			items : this.paymentGrid,
			closeAction : "hide"
		});
		
		Ext.getCmp(this.TabID).add(this.PartPaymentsWin);
	}
	
	this.paymentGrid.getStore().proxy.extraParams = {
		PartID : record.data.PartID
	};
	this.paymentGrid.getStore().load();
	this.PartPaymentsWin.show();
	this.PartPaymentsWin.center();
}

RequestInfo.prototype.ComputePayments = function(){
	
	Ext.MessageBox.confirm("","در صورت محاسبه مجدد کلیه ردیف ها حذف و مجدد محاسبه و ایجاد می شوند <br>" + 
		"آیا مایل به محاسبه مجدد می باشید؟",function(btn){
		if(btn == "no")
			return;
		me = RequestInfoObject;
		var record = me.grid.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(me.PartPaymentsWin, {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'request.data.php',
			method: "POST",
			params: {
				task: "ComputePartPayments",
				PartID : record.data.PartID
			},
			success: function(response){
				mask.hide();
				RequestInfoObject.paymentGrid.getStore().load();
			}
		});
	});
	
}

RequestInfo.prototype.SavePartPayment = function(store, record){

	mask = new Ext.LoadMask(this.paymentGrid, {msg:'در حال ذخیره سازی ...'});
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
				RequestInfoObject.paymentGrid.getStore().load();
			}
			else
			{
				alert("خطا در اجرای عملیات");
			}
		},
		failure: function(){}
	});
}
</script>
