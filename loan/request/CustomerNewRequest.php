<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "../../loan/loan/loan.data.php?task=GetAllLoans", "grid_div");

$dg->addColumn("کد وام", "LoanID", "", true);
$dg->addColumn("", "GroupID", "", true);
$dg->addColumn("", "GroupDesc", "", true);
$dg->addColumn("درصد دیرکرد", "ForfeitPercent", "", true);
$dg->addColumn("درصد کارمزد", "WagePercent", "", true);

$dg->addColumn("", "PartCount", "", true);
$dg->addColumn("", "IntervalType", "", true);
$dg->addColumn("", "PartInterval", "", true);
$dg->addColumn("", "DelayCount", "", true);
$dg->addColumn("", "MaxAmount", "", true);

$col = $dg->addColumn("عنوان وام", "LoanDesc", "");
$col->sortable = false;

$dg->addObject("this.LoanGroups");

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

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function NewLoanRequest()
{
	this.LoanGroups = new Ext.form.ComboBox({
		store : new Ext.data.SimpleStore({
			proxy: {type: 'jsonp',
				url: this.address_prefix + '../../loan/loan/loan.data.php?task=SelectLoanGroups',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields : ['InfoID','InfoDesc'],
			autoLoad : true,
			listeners : {
				load : function(){
					me = NewLoanRequestObject;
					me.LoanGroups.setValue(this.getAt(0).data.InfoID);
				}
			}
		}),
		valueField : "InfoID",
		queryMode : "local",
		name : "GroupID",
		displayField : "InfoDesc",
		fieldLabel : "انتخاب گروه وام",
		listeners :{
			change : function(){
				me = NewLoanRequestObject;
				me.grid.getStore().proxy.extraParams.GroupID = this.getValue();
				me.grid.getStore().load();
			}
		}
	});
	
	this.grid = <?= $grid ?>;
	this.grid.on("itemclick", function(){
		record = NewLoanRequestObject.grid.getSelectionModel().getLastSelected();
		NewLoanRequestObject.mainPanel.loadRecord(record);
		NewLoanRequestObject.mainPanel.doLayout();
		NewLoanRequestObject.mainPanel.down("[name=ReqAmount]").setMaxValue(record.data.MaxAmount);
		NewLoanRequestObject.mainPanel.down("[name=ReqAmount]").setValue(record.data.MaxAmount);
	});
	
	this.grid.getStore().on("beforeload", function(){
		if(this.proxy.extraParams.GroupID == null)
			return false;
	});
	
	this.mainPanel = new Ext.form.FormPanel({
		renderTo : this.get("mainForm"),
		width: 770,
		border : 0,
		items: [{
			xtype : "fieldset",
			title : "انتخاب وام درخواستی",
			layout : "column",
			columns : 2,
			anchor : "100%",
			items :[this.grid,{
				xtype : "container",
				layout : {
					type : "table",
					columns : 2
				},
				defaults : {
					xtype : "displayfield",
					style : "margin-top:10px",
					labelWidth : 80,
					width : 220,
					fieldCls : "blueText"
				},
				items : [{
					xtype : "container",
					colspan :2 ,
					width: 300,
					style : "margin-right:5px; color:#0d6eb2",					
					html : "<font color=red>" + "توجه: " + "</font>" + "برای مشاهده جزئیات هر وام روی عنوان وام کلیک کنید."
				},{
					fieldLabel: 'سقف مبلغ',
					name: 'MaxAmount',
					renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
				},{
					fieldLabel: 'تعداد اقساط',
					name: 'PartCount'
				},{
					fieldLabel: 'فاصله اقساط',
					renderer : function(v){ return v + " روز"},
					name: 'PartInterval',
					value : 0
				},{
					fieldLabel: 'مدت تنفس',
					renderer : function(v){ return v + " ماه"},
					name: 'DelayCount'
				},{
					fieldLabel: 'مبلغ بیمه',
					name: 'InsureAmount',
					renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
				},{
					fieldLabel: 'مبلغ قسط اول',
					name: 'FirstPartAmount',
					renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
				},{
					fieldLabel: 'درصد سود',
					name: 'ProfitPercent',
					renderer : function(v){ return v + " %"},
					value : 0
				},{
					fieldLabel: 'درصد دیرکرد',
					renderer : function(v){ return v + " %"},
					name: 'ForfeitPercent'
				},{
					fieldLabel: 'درصد کارمزد',
					renderer : function(v){ return v + " %"},
					name: 'WagePercent'
				},{
					fieldLabel: 'مبلغ کارمزد',
					name: 'WageAmount',
					rrenderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
				}]
			}]
		},{
			xtype : "fieldset",
			title : "جزئیات درخواست",
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
				fieldLabel : "مبلغ درخواستی",
				hideTrigger: true,
				afterSubTpl: '<tpl>ریال</tpl>'
			},{
				xtype : "textarea",
				fieldLabel : "توضیحات",
				anchor : "90%",
				name : "ReqDetails"
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

NewLoanRequest.prototype.NewLoanRequest = function()
{
	if(this.get("new_pass").value != this.get("new_pass2").value)
	{
		return;
	}
}

</script>

	<div id="DivGrid"></div>
	<div id="mainForm"></div>
<center>
	<div id="SendForm"></div>
</center>