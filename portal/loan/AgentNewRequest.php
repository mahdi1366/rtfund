<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg","/loan/request/request.data.php?task=GetRequestParts", "grid_div");

$dg->addColumn("", "PartID", "", true);
$dg->addColumn("", "RequestID","", true);
$dg->addColumn("", "PayDate","", true);
$dg->addColumn("", "PartAmount","", true);
$dg->addColumn("", "PayCount","", true);
$dg->addColumn("", "IntervalType","", true);
$dg->addColumn("", "PayInteval","", true);
$dg->addColumn("", "DelayMonths","", true);
$dg->addColumn("", "ForfeitPercent","", true);
$dg->addColumn("", "CustomerFee","", true);
$dg->addColumn("", "FundFee","", true);
$dg->addColumn("", "AgentFee","", true);

$col = $dg->addColumn("عنوان مرحله", "PartDesc", "");
$col->editor = ColumnEditor::TextField();
$col->sortable = false;

$dg->addButton("", "ایجاد مرحله پرداخت", "add", "function(){NewLoanRequestObject.AddPart();}");

$dg->HeaderMenu = false;
$dg->hideHeaders = true;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 150;
$dg->width = 150;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "MaxAmount";
$dg->disableFooter = true;

$grid = $dg->makeGrid_returnObjects();
?>
<script>
	
NewLoanRequest.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	RequestID : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function NewLoanRequest()
{
	this.grid = <?= $grid ?>;
	this.grid.on("itemclick", function(){
		record = NewLoanRequestObject.grid.getSelectionModel().getLastSelected();
		NewLoanRequestObject.PartsPanel.loadRecord(record);
		NewLoanRequestObject.PartsPanel.doLayout();
	});
	
	//this.grid.getStore().proxy.extraParams = {RequestID: this.RequestID};
	
	this.mainPanel = new Ext.form.FormPanel({
		renderTo : this.get("mainForm"),
		width: 750,
		border : 0,
		items: [{
			xtype : "fieldset",
			layout : {
				type : "table",
				columns : 2
			},			
			defaults : {
				width : 400
			},
			title : "اطلاعات درخواست",
			items : [{
				xtype : "textfield",
				allowBlank : false,
				name : "CompanyName",
				beforeLabelTextTpl: required,
				fieldLabel : "شرکت وام گیرنده"
			},{
				xtype : "textfield",
				name : "NationalID",
				width : 305,
				allowBlank : false,
				beforeLabelTextTpl: required,
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
				width : 310,
				allowBlank : false,
				beforeLabelTextTpl: required,
				fieldLabel : "مبلغ کل وام",
				hideTrigger: true,
				afterSubTpl: '<tpl>ریال</tpl>'
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
				iconCls : "save",
				colspan : 2,
				style : "float:left;margin-left:20px",
				text : "ذخیره معرفی اخذ وام",
				handler : function(){ NewLoanRequestObject.SaveRequest(); }
			}]
		}]		
	});
	
	this.PartsPanel =  new Ext.form.FormPanel({
		renderTo : this.get("PartForm"),
		//hidden : true,
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
					style : "margin-top:10px",
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
					fieldLabel: 'کارمزد صندوق',
					name: 'FundFee',
					renderer : function(v){ return v + " %"}
				},{
					fieldLabel: 'کارمزد عامل',
					name: 'AgentFee',
					renderer : function(v){ return v + " %"}
				},{
					fieldLabel: 'کارمزد مشتری',
					name: 'CustomerFee'	,		
					renderer : function(v){ return v + " %"},
					colspan : 2
				}]
			}]
		}],
		buttons : [{
			text : "ذخیره و ارسال درخواست",
			iconCls : "save",
			handler : function(){ 	}
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
			html : "<br>" + "از منوی وام های دریافتی می توانید وضعیت درخواست خود را بررسی کنید" + "<br><br>"
		}]
	});
		
}

NewLoanRequestObject = new NewLoanRequest();

NewLoanRequest.prototype.SaveRequest = function(){

	mask = new Ext.LoadMask(this.mainPanel, {msg:'در حال ذخيره سازي...'});
	mask.show();  
	
	this.mainPanel.getForm().submit({
		clientValidation: true,
		url: this.address_prefix + '../../loan/request/request.data.php?task=SaveLoanRequest' , 
		method: "POST",
		params : {
			RequestID : this.RequestID
		},
		
		success : function(form,action){
			mask.hide();
			me = NewLoanRequestObject;
			
			me.RequestID = action.result.data;
			me.grid.getStore().proxy.extraParams = {RequestID: me.RequestID};
			me.grid.getStore().load();
			me.PartsPanel.show();
		},
		failure : function(){
			mask.hide();
			//Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
		}
	});
}

NewLoanRequest.prototype.AddPart = function(){
	
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
					allowBlank : false,
					beforeLabelTextTpl: required,
					fieldCls : "blueText"
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
						beforeLabelTextTpl: required,
						name: 'PayInterval',
						labelWidth: 90,
						width : 150
					},{
						xtype : "radio",
						boxLabel : "ماه",
						inputValue : "MONTH",
						checked : true,
						name : "IntervalType"
					},{
						xtype : "radio",
						boxLabel : "روز",
						inputValue : "DAY",
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
					fieldLabel: 'کارمزد صندوق',
					name: 'FundFee'
				},{
					fieldLabel: 'کارمزد عامل',
					name: 'AgentFee'
				},{
					fieldLabel: 'کارمزد مشتری',
					name: 'CustomerFee'	,		
					colspan : 2
				}]				
			}),
			buttons : [{
				text : "ذخیره",
				iconCls : "save",
				handler : function(){
					NewLoanRequestObject.SavePart();
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
}

NewLoanRequest.prototype.SavePart = function(){

	mask = new Ext.LoadMask(this.PartWin, {msg:'در حال ذخیره سازی ...'});
	//mask.show();

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

			NewLoanRequestObject.RequestID = action.result.data;
			NewLoanRequestObject.grid.getStore().proxy.extraParams = {RequestID: NewLoanRequestObject.RequestID};
			NewLoanRequestObject.grid.getStore().load();
			NewLoanRequestObject.PartWin.hide();
		},
		failure: function(){
			mask.hide();
		}
	});
}


</script>

	<div id="DivGrid"></div>
	<div id="mainForm"></div>
	<div id="PartForm"></div>
<center>
	<div id="SendForm"></div>
</center>