<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------
	
RequestInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	RequestID : <?= $RequestID ?>,
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
	});
	
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
			fields : ["RequestID","BranchName","ReqPersonID","ReqFullname","LoanPersonID","LoanFullname",
						"ReqDate","ReqAmount","ReqDetails","BorrowerDesc","BorrowerID","assurance"],
			autoLoad : true,
			listeners :{
				load : function(){
					RequestInfoObject.companyPanel.loadRecord(this.getAt(0));
					mask.hide();
				}
			}
		});
	}
	
	this.BuildForms();
	this.CustomizeForm();
}

RequestInfo.OperationRender = function(v,p,r){
	
	return "<div align='center' title='ویرایش' class='edit' "+
		"onclick='RequestInfoObject.PartInfo(\"edit\");' " +
		"style='float:right;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:18px;height:16'></div>" + 
		
		"<div align='center' title='حذف' class='remove' "+
		"onclick='RequestInfoObject.DeletePart();' " +
		"style='float:right;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:18px;height:16'></div>";
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
				type : "table",
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
				name : "CompanyName",
				fieldLabel : "شرکت وام گیرنده"
			},{
				xtype : "textfield",
				name : "NationalID",
				fieldLabel : "کد اقتصادی"
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
				beforeLabelTextTpl: required,
				displayField : "BranchName",
				valueField : "BranchID",
				name : "BranchID"
			},{
				xtype : "currencyfield",
				name : "ReqAmount",
				allowBlank : false,
				beforeLabelTextTpl: required,
				fieldLabel : "مبلغ درخواست",
				hideTrigger: true
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
				beforeLabelTextTpl: required,
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
				xtype : "button",
				width : 150,
				itemId : "cmp_save",
				iconCls : "save",
				colspan : 2,
				style : "float:left;margin-left:20px",
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
					name: 'PayInteval'
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
					name: 'CustomerFee'	,		
					renderer : function(v){ return v + " %"}
				},{
					fieldLabel: 'سهم صندوق',
					name: 'FundFee',
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
		//this.companyPanel.getEl().readonly();
		this.companyPanel.down("[itemId=cmp_save]").hide();
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
					name: 'CustomerFee'	
				},{
					fieldLabel: 'کارمزد صندوق',
					name: 'FundFee'
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
		this.PartWin.down("[name=PayInterval]").setValue(record.data.PayInteval*1);
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
		return F8 * F7 * Math.pow((1 + F8), F9) / (1 - Math.pow((1 + F8), F9)); } 
	function ComputeFee(F7, F8, F9, YearMonths){
		
		return (((F7*F8/YearMonths*( Math.pow((1+(F8/YearMonths)),F9)))/
			((Math.pow((1+(F8/YearMonths)),F9))-1))*F9)-F7;
	}
	function roundUp(number, digits)
	{
		var factor = Math.pow(10,digits);
		return Math.ceil(number*factor) / factor;
	}
	function YearFeeCompute(record,TotalFee,yearNo, YearMonths){
		
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
			(F9-BeforeMonths-curMonths)*(F9-BeforeMonths-curMonths+1)))/(F9*(F9+1))*TotalFee;
		return Ext.util.Format.Money(Math.round(val));
	}

	YearMonths = 12;
	if(record.data.IntervalType == "DAY")
		YearMonths = Math.floor(365/
		record.data.PayInterval);

	FirstPay = roundUp(PMT(record.data.CustomerFee,	record.data.PayCount, record.data.PartAmount),-3);
	TotalFee = Math.round(ComputeFee(record.data.PartAmount,record.data.CustomerFee/100,record.data.PayCount));
	FundFee = Math.round((record.data.FundFee/record.data.CustomerFee)*TotalFee);
	AgentFee = TotalFee - FundFee;
	
	TotalDelay = Math.round(record.data.PartAmount*record.data.CustomerFee*record.data.DelayMonths/
					(YearMonths*100));
	LastPay = record.data.PartAmount*1 + TotalFee - FirstPay*(record.data.PayCount-1);
	
	this.get("SUM_PayAmount").innerHTML = Ext.util.Format.Money(FirstPay);
	this.get("SUM_LastPayAmount").innerHTML = Ext.util.Format.Money(LastPay);
	this.get("SUM_Delay").innerHTML = Ext.util.Format.Money(TotalDelay);
	this.get("SUM_NetAmount").innerHTML = Ext.util.Format.Money(record.data.PartAmount - TotalDelay);	
	
	this.get("SUM_TotalFee").innerHTML = Ext.util.Format.Money(TotalFee);	
	this.get("SUM_FundFee").innerHTML = Ext.util.Format.Money(FundFee);	
	this.get("SUM_AgentFee").innerHTML = Ext.util.Format.Money(AgentFee);	
	
	this.get("SUM_Fee_1Year").innerHTML = YearFeeCompute(record, TotalFee, 1);
	this.get("SUM_Fee_2Year").innerHTML = YearFeeCompute(record, TotalFee, 2);
	this.get("SUM_Fee_3Year").innerHTML = YearFeeCompute(record, TotalFee, 3);
	this.get("SUM_Fee_4Year").innerHTML = YearFeeCompute(record, TotalFee, 4);
}


</script>
