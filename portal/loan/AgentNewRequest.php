<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg","/loan/request/request.data.php?task=GetRequestParts", "grid_div");

$dg->addColumn("", "RequestID", "", true);
$dg->addColumn("", "GroupID", "", true);
$dg->addColumn("", "GroupDesc", "", true);
$dg->addColumn("", "ForfeitPercent", "", true);
$dg->addColumn("", "FeePercent", "", true);

$dg->addColumn("", "PartCount", "", true);
$dg->addColumn("", "IntervalType", "", true);
$dg->addColumn("", "PartInterval", "", true);
$dg->addColumn("", "DelayCount", "", true);
$dg->addColumn("", "MaxAmount", "", true);

$col = $dg->addColumn("عنوان مرحله", "PartDesc", "");
$col->editor = ColumnEditor::TextField();
$col->sortable = false;

$dg->addButton("", "ایجاد مرحله جدید پرداخت", "add", "function(){NewLoanRequestObject.AddPart();}");

$dg->HeaderMenu = false;
$dg->hideHeaders = true;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 150;
$dg->width = 300;
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
		NewLoanRequestObject.mainPanel.loadRecord(record);
		NewLoanRequestObject.mainPanel.doLayout();
		NewLoanRequestObject.mainPanel.down("[name=ReqAmount]").setMaxValue(record.data.MaxAmount);
		NewLoanRequestObject.mainPanel.down("[name=ReqAmount]").setValue(record.data.MaxAmount);
	});
	
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
			title : "اطلاعات درخواست",
			items : [{
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
				fieldLabel : "مبلغ کل وام",
				hideTrigger: true,
				afterSubTpl: '<tpl>ریال</tpl>'
			},{
				xtype : "textarea",
				fieldLabel : "توضیحات",
				width : 550,
				rows : 1,
				colspan : 2,				
				name : "ReqDetails"
			}]
		},{
			xtype : "fieldset",
			title : "مراحل پرداخت وام",
			layout : "column",
			columns : 2,
			anchor : "100%",
			items :[this.grid,{
				xtype : "container",
				style : "margin-right:10px",
				layout : {
					type : "table",
					columns : 2
				},
				defaults : {
					xtype : "displayfield",
					labelWidth : 80,	
					hideTrigger : true,
					width : 150,
					labelWidth : 90,
					style : "margin-top:10px",
					fieldCls : "blueText"
				},
				items : [{
					fieldLabel: 'مبلغ پرداخت',
					name: 'PartAmount'
				},{
					fieldLabel: 'تاریخ پرداخت',
					name: 'PayDate'
				},{
					fieldLabel: 'فاصله اقساط',
					name: 'PayInterval'
				},{
					fieldLabel: 'تعداد ماه تنفس',
					name: 'DelayMonth'
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
			}]
		}],

		buttons : [{
			text : "ثبت درخواست وام و ارسال به صندوق",
			iconCls: 'save',
			handler: function() {
				
				if(!NewLoanRequestObject.grid.getSelectionModel().getLastSelected())
				{
					Ext.MessageBox.alert("","لطفا وام مورد نظر خود را با کلیک بر روی عنوان وام انتخاب نمایید.");
					return;
				}
				
				me = NewLoanRequestObject;
				mask = new Ext.LoadMask(me.mainPanel, {msg:'در حال ذخيره سازي...'});
				mask.show();  
				me.mainPanel.getForm().submit({
					clientValidation: true,
					url: me.address_prefix + '../../loan/request/request.data.php?task=SaveLoanRequest' , 
					method: "POST",
					params : {
						LoanID : NewLoanRequestObject.grid.getSelectionModel().getLastSelected().data.LoanID
					},
					success : function(form,action){
						mask.hide();
						me = NewLoanRequestObject;
						me.mainPanel.hide();
						me.SendedPanel.getComponent("requestID").update('شماره پیگیری درخواست : ' + action.result.data);
						me.SendedPanel.show();
					},
					failure : function(){
						mask.hide();
						//Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
					}
				});
			}

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
					fieldCls : "blueText"
				},				
				items :[{
					xtype : "textfield",
					name : "PartName",
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
					fieldLabel: 'تعداد ماه تنفس',
					name: 'DelayMonth'
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

			NewLoanRequestObject.RequestID = action.result.data;
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
<center>
	<div id="SendForm"></div>
</center>