<?php

require_once '../header.inc.php';
?>
<script>
	
TestLoan.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function TestLoan(){
	
	this.panel = new Ext.form.Panel({
		width : 700,
		frame : true,
		renderTo : this.get("divPanel"),
		layout : {
			type : "table",
			columns : 3
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
			xtype : "currencyfield",
			name : "PartAmount",
			fieldLabel : "مبلغ پرداخت",
			width : 220
		},{
			xtype : "shdatefield",
			name : "PartDate",
			allowBlank : true,
			hideTrigger : false,
			fieldLabel : "تاریخ پرداخت",
			width : 200
		},{
			fieldLabel: 'تعداد اقساط',
			name: 'InstallmentCount',
			width : 200
		},{
			xtype : "container",
			layout : "hbox",
			width : 220,
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
			xtype : "container",
			width : 220,
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
			xtype : "fieldset",
			itemId : "fs_WageCompute",
			title : "نحوه دریافت کارمزد صندوق",
			width : 220,
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
			}]
		},{
			xtype : "fieldset",
			itemId : "fs_DelayCompute",
			title : "نحوه دریافت تنفس صندوق",
			width : 220,
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
			itemId : "fs_AgentDelayCompute",
			title : "نحوه دریافت تنفس سرمایه گذار",
			width : 220,
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
			itemId : "fs_AgentWageCompute",
			title : "نحوه دریافت کارمزد سرمایه گذار",
			width : 220,
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
			colspan :2,
			width : 400,
			style : "margin-right:10px",
			itemId : "fs_MaxFundWage",
			items : [{
				xtype : "currencyfield",
				hideTrigger : true,
				labelWidth : 120,
				value : 0,
				fieldLabel : "سقف کارمزد صندوق",
				name : "MaxFundWage"
			},{
				xtype : "container",
				html : "این کارمزد بر اساس نحوه دریافت کارمزد محاسبه می گردد."
			}]
		}],buttons : [{
			text : "محاسبه",
			handler : function(){ TestLoanObject.LoadSummary();}
		}]				
	});
} 

TestLoanObject = new TestLoan();

TestLoan.prototype.SplitYears = function(startDate, endDate, TotalAmount){
	
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

TestLoan.prototype.LoadSummary = function(){

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
	function roundUp(number, digits)
	{
		var factor = Math.pow(10,digits);
		return Math.ceil(number*factor) / factor;
	}
	function YearWageCompute(TotalWage,YearMonths){
		
		DelayDays = TestLoanObject.panel.down("[name=DelayDays]").getValue();
		DelayMonths = TestLoanObject.panel.down("[name=DelayMonths]").getValue();
		PartDate = TestLoanObject.panel.down("[name=PartDate]").getRawValue();
		InstallmentCount = TestLoanObject.panel.down("[name=InstallmentCount]").getValue();
		
		startDate = MiladiToShamsi(PartDate);
		startDate = DateModule.AddToJDate(startDate, DelayDays, DelayMonths);
		startDate = startDate.split(/[\-\/]/);
		PayMonth = startDate[1];
		PayMonth = PayMonth*YearMonths/12;
		
		FirstYearInstallmentCount = Math.floor((12 - PayMonth)/(12/YearMonths));
		FirstYearInstallmentCount = InstallmentCount*1 < FirstYearInstallmentCount ? 
			FirstYearInstallmentCount - InstallmentCount*1 : FirstYearInstallmentCount;
		MidYearInstallmentCount = Math.floor((InstallmentCount*1-FirstYearInstallmentCount) / YearMonths);
		MidYearInstallmentCount = MidYearInstallmentCount < 0 ? 0 : MidYearInstallmentCount;
		LastYeatInstallmentCount = (InstallmentCount-FirstYearInstallmentCount) % YearMonths;
		LastYeatInstallmentCount = LastYeatInstallmentCount < 0 ? 0 : LastYeatInstallmentCount;
		F9 = InstallmentCount*(12/YearMonths);

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
	
	DelayDays = this.panel.down("[name=DelayDays]").getValue();
	DelayMonths = this.panel.down("[name=DelayMonths]").getValue();
	PartDate = this.panel.down("[name=PartDate]").getRawValue();
	IntervalType = this.panel.down("[name=IntervalType]").getValue();
	PayInterval = this.panel.down("[name=PayInterval]").getValue();
	PartAmount = this.panel.down("[name=PartAmount]").getValue();
	CustomerWage = this.panel.down("[name=CustomerWage]").getValue()*1;
	InstallmentCount = this.panel.down("[name=InstallmentCount]").getValue();
	FundWage = this.panel.down("[name=FundWage]").getValue()*1;
	WageReturn = this.panel.down("[name=WageReturn]").getValue() == true ? "INSTALLMENT" : "CUSTOMER";
	MaxFundWage = this.panel.down("[name=MaxFundWage]").getValue();
	AgentReturn = this.panel.down("[name=AgentReturn]").getValue()  == true ? "INSTALLMENT" : "CUSTOMER";
	DelayReturn = this.panel.down("[name=DelayReturn]").getValue()  == true ? "CUSTOMER" : "INSTALLMENT";
	AgentDelayReturn = this.panel.down("[name=AgentDelayReturn]").getValue()  == true ? "CUSTOMER" : "INSTALLMENT";
	DelayPercent = this.panel.down("[name=DelayPercent]").getValue(); 
	
	MaxWage = Math.max(CustomerWage, FundWage);
	CustomerFactor =	MaxWage == 0 ? 0 : CustomerWage/MaxWage;
	FundFactor =		MaxWage == 0 ? 0 : FundWage/MaxWage;
	AgentFactor =		MaxWage == 0 ? 0 : (CustomerWage-FundWage)/MaxWage;
	
	PartDate = ShamsiToMiladi(PartDate);
	if(PayInterval > 0)
		YearMonths = (IntervalType == "DAY" ) ? 
			Math.floor(365/PayInterval) : 12/PayInterval;
	else
		YearMonths = 12;

	
	DelayDuration = DateModule.GDateMinusGDate(
		DateModule.AddToGDate(PartDate, DelayDays*1, DelayMonths*1), PartDate);
	TotalWage = Math.round(ComputeWage(PartAmount, CustomerWage/100, 
		InstallmentCount, IntervalType, PayInterval));
		
	TotalWage = !isInt(TotalWage) ? 0 : TotalWage;	
	FundWage = Math.round((FundWage/CustomerWage)*TotalWage);
	FundWage = !isInt(FundWage) ? 0 : FundWage;
	AgentWage = TotalWage - FundWage;
	
	if(DelayDays*1 > 0)
		TotalDelay = Math.round(PartAmount*DelayPercent*DelayDuration/36500);
	else
		TotalDelay = Math.round(PartAmount*DelayPercent*DelayMonths/1200);
	
	//-------------------------- installments -----------------------------
		
	var extraAmount = 0;
	if(WageReturn == "INSTALLMENT")
	{
		if(MaxFundWage*1 > 0)
			extraAmount += MaxFundWage;
		else if(CustomerWage > FundWage)
			extraAmount += Math.round(TotalWage*FundFactor);
		else
			extraAmount += Math.round(TotalWage*CustomerFactor);		
	}		
	if(AgentReturn == "INSTALLMENT" && CustomerWage>FundWage)
		extraAmount += Math.round(TotalWage*AgentFactor);

	if(DelayReturn == "INSTALLMENT")
		extraAmount += TotalDelay*(FundWage/DelayPercent);
	if(AgentDelayReturn == "INSTALLMENT" && DelayPercent>FundWage)
		extraAmount += TotalDelay*((DelayPercent-FundWage)/DelayPercent);
	
	TotalAmount = PartAmount*1 + extraAmount;
	
	FirstPay = ComputeInstallmentAmount(TotalAmount,InstallmentCount, PayInterval);
	
	if(InstallmentCount > 1)
		FirstPay = roundUp(FirstPay,-3);
	else
		FirstPay = Math.round(FirstPay);
	LastPay = Math.round(TotalAmount - FirstPay*(InstallmentCount-1));

	//---------------------------------------------------------------------
	
	if(MaxFundWage*1 > 0)
	{
		tmp = WageReturn == "INSTALLMENT" ? 
			Math.round(MaxFundWage*1/InstallmentCount) : 0;
		
		this.get("SUM_InstallmentAmount").innerHTML = Ext.util.Format.Money(FirstPay + tmp);
		this.get("SUM_LastInstallmentAmount").innerHTML = Ext.util.Format.Money(LastPay + tmp);
		this.get("SUM_Delay").innerHTML = 0;
		this.get("SUM_NetAmount").innerHTML = Ext.util.Format.Money(PartAmount 
			 - (WageReturn == "CUSTOMER" ? TotalWage + MaxFundWage*1 : 0));	

		this.get("SUM_TotalWage").innerHTML = Ext.util.Format.Money(TotalWage + MaxFundWage*1);	
		this.get("SUM_FundWage").innerHTML = Ext.util.Format.Money(MaxFundWage);	
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
	this.get("SUM_NetAmount").innerHTML = Ext.util.Format.Money(PartAmount - 
		(DelayReturn == "CUSTOMER" ? TotalDelay : 0) - 
		(WageReturn == "CUSTOMER" ? TotalWage : 0));	
	
	this.get("SUM_TotalWage").innerHTML = Ext.util.Format.Money(TotalWage);	
	this.get("SUM_FundWage").innerHTML = Ext.util.Format.Money(FundWage);	
	this.get("SUM_AgentWage").innerHTML = Ext.util.Format.Money(AgentWage);	
	
	returnArr = YearWageCompute(TotalWage, YearMonths);
	
	this.get("SUM_Wage_1Year").innerHTML = returnArr[0].amount;
	this.get("SUM_Wage_2Year").innerHTML = returnArr[1].amount;
	this.get("SUM_Wage_3Year").innerHTML = returnArr[2].amount > 0 ? returnArr[2].amount : 0;
	this.get("SUM_Wage_4Year").innerHTML = returnArr[3].amount > 0 ? returnArr[3].amount : 0;
}

TestLoan.prototype.LoadSummarySHRTFUND = function(record, paymentStore){

	if(paymentStore == null)
	{
		this.paymentStore = new Ext.data.Store({
			proxy:{
				type: 'jsonp',
				url: this.address_prefix + "request.data.php?task=GetPartPayments&PartID=" + record.data.PartID,
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields : ["PayDate", "PayAmount"],
			autoLoad : true,
			listeners :{
				load : function(){
					TestLoanObject.LoadSummarySHRTFUND(record, this);
				}
			}
		});
		return;
	}
	
	//--------------- total pay months -------------
	firstPay = MiladiToShamsi(this.paymentStore.getAt(0).data.PayDate);
	LastPay = MiladiToShamsi(this.paymentStore.getAt(this.paymentStore.getCount()-1).data.PayDate);
	paymentPeriod = DateModule.GetDiffInMonth(firstPay, LastPay);
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
			if(i == 3 && j == 1)
				alert(installmentDate + "\n" + this.paymentStore.getAt(j).data.PayDate + "\n" + jdiff);
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

</script>
<style>
	.summary {
		border : 1px solid #b5b8c8;
		border-collapse: collapse;
	}
	.summary td{
		border: 1px solid #b5b8c8;
		line-height: 21px;
		direction: ltr;
		padding: 0 5px;
	}
</style>
<center>
	<br>
	<div id="divPanel"></div>
	<div id="divPanel2"></div>
	<div id="summaryDIV">
		<table style="width:700px" class="summary">
			<tr>
				<td style="width:70px;background-color: #dfe8f6;">مبلغ هر قسط</td>
				<td style="background-color: #dfe8f6;">سود دوره تنفس</td>
				<td style="width:90px;direction:rtl;background-color: #dfe8f6;">کارمزد وام</td>
				<td><div id="SUM_TotalWage" class="blueText">&nbsp;</div></td>
				<td style="direction:rtl;width:85px;background-color: #dfe8f6;">کارمزد سال اول</td>
				<td><div id="SUM_Wage_1Year" class="blueText">&nbsp;</div></td>
			</tr>
			<tr>
				<td><div id="SUM_InstallmentAmount" class="blueText">&nbsp;</div></td>
				<td><div id="SUM_Delay" class="blueText">&nbsp;</div></td>
				<td style="direction:rtl;background-color: #dfe8f6;">سهم صندوق</td>
				<td><div id="SUM_FundWage" class="blueText">&nbsp;</div></td>
				<td style="direction:rtl;background-color: #dfe8f6;">کارمزد سال دوم</td>
				<td><div id="SUM_Wage_2Year" class="blueText">&nbsp;</div></td>
			</tr>
			<tr>
				<td style="background-color: #dfe8f6;">مبلغ قسط آخر</td>
				<td style="background-color: #dfe8f6;">خالص پرداختی</td>
				<td style="direction:rtl;background-color: #dfe8f6;">سهم سرمایه گذار</td>
				<td><div id="SUM_AgentWage" class="blueText">&nbsp;</div></td>
				<td style="direction:rtl;background-color: #dfe8f6;">کارمزد سال سوم</td>
				<td><div id="SUM_Wage_3Year" class="blueText">&nbsp;</div></td>
			</tr>
			<tr>
				<td><div id="SUM_LastInstallmentAmount" class="blueText">&nbsp;</div></td>
				<td><div id="SUM_NetAmount" class="blueText">&nbsp;</div></td>
				<td></td>
				<td></td>
				<td style="direction:rtl;background-color: #dfe8f6;">کارمزد سال چهارم</td>
				<td><div id="SUM_Wage_4Year" class="blueText">&nbsp;</div></td>
			</tr>			
		</table>
	</div> 
	<br>
</center>