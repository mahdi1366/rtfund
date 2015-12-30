<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.10
//-----------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;

$LetterID = "";
$showDrafts = false;
$dt = PdoDataAccess::runquery("select * from OFC_letters where LetterStatus='RAW' 
	AND PersonID=" . $_SESSION["USER"]["PersonID"]);
if(count($dt) > 0)
{
	if(count($dt) == 1)
		$LetterID = $dt[0]["LetterID"];
	else 
		$showDrafts = true;
}

$dg = new sadaf_datagrid("dg", $js_prefix_address . "letter.data.php?task=SelectDraftLetters", "grid_div");

$dg->addColumn("", "LetterID", "", true);
$dg->addColumn("", "LetterTitle", "", true);

$col = $dg->addColumn("نوع نامه", "LetterType", "");
$col->renderer = "function(v,p,r){return v == 'INNER' ? 'داخلی' : 'صادره';}";
$col->width = 80;

$col = $dg->addColumn("تاریخ ایجاد", "LetterDate", GridColumn::ColumnType_date);
$col->width = 90;

$col = $dg->addColumn("عنوان نامه", "LetterTitle");

$col = $dg->addColumn("عملیات", "");
$col->renderer = "function(v,p,r){return Letter.DeleteRender(v,p,r);}";
$col->width = 50;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 420;
$dg->width = 770;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->title = "نامه های پیش نویس";
$dg->DefaultSortField = "LetterDate";
$dg->autoExpandColumn = "LetterTitle";
$grid = $dg->makeGrid_returnObjects();

?>

<script>

Letter.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	LetterID : '<?= $LetterID ?>',
	ShowDrafts : <?= $showDrafts ? "true" : "false" ?>,
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Letter(){
	
	this.grid = <?= $grid?>;
	
	if(this.ShowDrafts)
		this.grid.render(this.get("div_grid"));
	else
	{
		this.BuildForms();
		if(this.LetterID > 0)
		{
			this.mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال بارگذاری...'});
			this.mask.show();
			
			this.LoadLetter();
		}
	}
}

Letter.prototype.LoadLetter = function(){
		
	this.store = new Ext.data.Store({
		proxy : {
			type: 'jsonp',
			url: this.address_prefix + "letter.data.php?task=SelectLetter&LetterID=" + this.LetterID,
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["LetterID","LetterType","LetterTitle","SubjectID","summary","context"],
		autoLoad : true,
		listeners : {
			load : function(){
				me = LetterObject;
				//..........................................................
				record = this.getAt(0);
				me.letterPanel.loadRecord(record);
				
				LetterObject.mask.hide();
			}
		}
	});
}

Letter.DeleteRender = function(v,p,r){
	
	return "<div align='center' title='حذف' class='remove' "+
		"onclick='LetterObject.DeleteLetter();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

Letter.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	ReqRecord = this.store.getAt(0);
	
	var op_menu = new Ext.menu.Menu();

	if(this.User == "Staff")
	{
		if(record.data.imp_VamCode*1 > 0)
		{
			op_menu.add({text: 'اقساط',iconCls: 'list',
			handler : function(){ return LetterObject.LoadInstallments(); }});
		
			op_menu.showAt(e.pageX-120, e.pageY);
			return;
		}
		if(record.data.IsStarted == "NO" && record.data.StatusID == "70")
		{
			op_menu.add({text: 'شروع گردش فرم',iconCls: 'refresh',
			handler : function(){ return LetterObject.StartFlow(); }});
		
			op_menu.add({text: 'ویرایش',iconCls: 'edit', 
				handler : function(){ return LetterObject.PartInfo("edit"); }});
			
			op_menu.add({text: 'حذف',iconCls: 'remove', 
				handler : function(){ return LetterObject.DeletePart(); }});
		}	
		if(record.data.IsEnded == "YES")
		{
			op_menu.add({text: 'اقساط',iconCls: 'list',
			handler : function(){ return LetterObject.LoadInstallments(); }});
		
			if(record.data.IsPayed == "NO")
				op_menu.add({text: 'پرداخت',iconCls: 'epay',
				handler : function(){ return LetterObject.PayPart(); }});
			else if(record.data.DocStatus == "RAW")
				op_menu.add({text: 'برگشت پرداخت',iconCls: 'undo',
				handler : function(){ return LetterObject.ReturnPayPart(); }});
			else if(record.data.IsPartEnded == "NO")
				op_menu.add({text: 'اتمام مرحله و ایجاد مرحله جدید',iconCls: "app",
				handler : function(){ return LetterObject.EndPart(); }});
				
		}
		
	}	
	if(record.data.IsPayed == "NO" && record.data.IsStarted == "NO")
	{
		if((this.User == "Agent" && record.data.StatusID == "1") || 
			(this.User == "Staff" && record.data.StatusID != "70" && ReqRecord.data.ReqPersonRole != "Agent"))
		{
			op_menu.add({text: 'ویرایش',iconCls: 'edit', 
				handler : function(){ return LetterObject.PartInfo("edit"); }});

			op_menu.add({text: 'حذف',iconCls: 'remove', 
				handler : function(){ return LetterObject.DeletePart(); }});
		}
	}
	
	if(record.data.StatusID == "70")
		op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return LetterObject.ShowHistory(); }});
	
	op_menu.showAt(e.pageX-120, e.pageY);
}

Letter.prototype.BuildForms = function(){
	
	this.letterPanel = new Ext.form.FormPanel({
		renderTo : this.get("mainForm"),
		title : "مشخصات نامه",
		frame : true,
		height : 550,
		layout : {
			type : "table",
			columns : 2
		},
		defaults : {
			labelWidth : 60,
			width : 350
		},
		width: 780,
		items : [{
			xtype :"container",
			layout : "hbox",
			items : [{
				xtype : "radio",
				fieldLabel : "نوع نامه",
				labelWidth : 60,
				boxLabel: 'نامه داخلی',
				name: 'LetterType',
				style : "margin-right : 20px",
				checked : true,
				inputValue: 'INNER'
			},{
				xtype : "radio",
				boxLabel: 'نامه صادره',
				name: 'LetterType',
				inputValue: 'OUTER'
			}]
		},{
			xtype : "textarea",
			fieldLabel : "چکیده",
			name : "summary",
			width : 400,
			rows : 3,
			rowspan : 3
		},{
			xtype : "textfield",
			name : "LetterTitle",
			fieldLabel : "عنوان نامه"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + 'letter.data.php?task=selectSubjects',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['InfoID','InfoDesc'],
				autoLoad : true					
			}),
			fieldLabel : "موضوع نامه",
			displayField : "InfoDesc",
			valueField : "InfoID",
			name : "SubjectID"
		},{
			xtype : "container",
			width : 760,
			html : "<div id='Div_context'></div>",
			colspan : 2
		}],
		buttons :[{
			text : "ذخیره",
			iconCls : "save",
			handler : function(){
				LetterObject.SaveLetter();
			}
		}]
	});
	
	
	if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 )
		CKEDITOR.tools.enableHtml5Elements( document );

	// The trick to keep the editor in the sample quite small
	// unless user specified own height.
	CKEDITOR.config.width = 'auto';

	var initSample = ( function() {
		var wysiwygareaAvailable = isWysiwygareaAvailable(),
			isBBCodeBuiltIn = !!CKEDITOR.plugins.get( 'bbcode' );

		return function() {
			var editorElement = CKEDITOR.document.getById( 'Div_context' );

			if ( isBBCodeBuiltIn ) {
				editorElement.setHtml();
			}

			// Depending on the wysiwygare plugin availability initialize classic or inline editor.
			if ( wysiwygareaAvailable ) {
				CKEDITOR.replace( 'Div_context' );
			} else {
				editorElement.setAttribute( 'contenteditable', 'true' );
				CKEDITOR.inline( 'Div_context' );

				// TODO we can consider displaying some info box that
				// without wysiwygarea the classic editor may not work.
			}
		};

		function isWysiwygareaAvailable() {
			// If in development mode, then the wysiwygarea must be available.
			// Split REV into two strings so builder does not replace it :D.
			if ( CKEDITOR.revision == ( '%RE' + 'V%' ) ) {
				return true;
			}

			return !!CKEDITOR.plugins.get( 'wysiwygarea' );
		}
	} )();
	
	initSample();
	
}

Letter.prototype.CustomizeForm = function(record){
	
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

LetterObject = new Letter();

Letter.prototype.SaveLetter = function(){

	mask = new Ext.LoadMask(this.letterPanel, {msg:'در حال ذخيره سازي...'});
	mask.show();  
	
	this.letterPanel.getForm().submit({
		clientValidation: true,
		url: this.address_prefix + 'letter.data.php?task=SaveLetter' , 
		method: "POST",
		params : {
			LetterID : this.LetterID,
			context : CKEDITOR.instances.Div_context.getData()
		},
		
		success : function(form,action){
			mask.hide();
			LetterObject.LetterID = action.result.data;
		},
		failure : function(){
			mask.hide();
		}
	});
}

Letter.prototype.PartInfo = function(mode){
	
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
					LetterObject.SavePart();
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

Letter.prototype.SavePart = function(){

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
			LetterObject.grid.getStore().load();
			LetterObject.PartWin.hide();
		},
		failure: function(){
			mask.hide();
		}
	});
}

Letter.prototype.DeleteLetter = function(){

	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = LetterObject;
		record = me.grid.getSelectionModel().getLastSelected();
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'letter.data.php',
			method: "POST",
			params: {
				task: "DeleteLetter",
				LetterID : record.data.LetterID
			},
			success: function(response){
				result = Ext.decode(response.responseText);
				mask.hide();
				if(result.success)
					LetterObject.grid.getStore().load();
				else
					Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد;")
			}
		});
	});
}

Letter.prototype.LoadSummary = function(record){

	function PMT(F8, F9, F7, YearMonths) {  
		
		if(F8 == 0)
			return F7/F9;
				
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

	FirstPay = roundUp(PMT(record.data.CustomerWage,record.data.InstallmentCount, 
		record.data.PartAmount, YearMonths),-3);
	TotalWage = Math.round(ComputeWage(record.data.PartAmount, record.data.CustomerWage/100, 
		record.data.InstallmentCount, YearMonths));
	TotalWage = !isInt(TotalWage) ? 0 : TotalWage;	
	FundWage = Math.round((record.data.FundWage/record.data.CustomerWage)*TotalWage);
	FundWage = !isInt(FundWage) ? 0 : FundWage;
	AgentWage = TotalWage - FundWage;
	
	TotalDelay = Math.round(record.data.PartAmount*record.data.CustomerWage*record.data.DelayMonths/1200);
	LastPay = record.data.PartAmount*1 + TotalWage - FirstPay*(record.data.InstallmentCount-1);
	
	if(record.data.InstallmentCount == 1)
		LastPay = 0;
	
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


</script>
<script>
	



	</script>
<center>
	<br>
	<div id="mainForm"></div>
	<div id="div_grid"></div>
</center>