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
$dg->addColumn("مبلغ بیمه", "InsureAmount", "", true);
$dg->addColumn("مبلغ قسط اول", "FirstCostusAmount", "", true);
$dg->addColumn("درصد دیرکرد", "ForfeitPercent", "", true);
$dg->addColumn("درصد کارمزد", "FeePercent", "", true);
$dg->addColumn("مبلغ کارمزد", "FeeAmount", "", true);
$dg->addColumn("درصد سود", "ProfitPercent", "", true);

$dg->addColumn("", "CostusCount", "", true);
$dg->addColumn("", "CostusInterval", "", true);
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
					fieldLabel: 'سقف مبلغ',
					name: 'MaxAmount',
					renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
				},{
					fieldLabel: 'تعداد اقساط',
					name: 'CostusCount'
				},{
					fieldLabel: 'فاصله اقساط',
					renderer : function(v){ return v + " روز"},
					name: 'CostusInterval',
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
					name: 'FirstCostusAmount',
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
					name: 'FeePercent'
				},{
					fieldLabel: 'مبلغ کارمزد',
					name: 'FeeAmount',
					rrenderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
				}]
			}]
		},{
			xtype : "fieldset",
			title : "جزئیات درخواست",
			layout : "columns",
			columns : 2,
			items : [{
				xtype : "currencyfield",
				name : "ReqAmount",
				fieldLabel : "مبلغ درخواستی",
				hideTrigger: true
			},{
				xtype : "textfield",
				fieldLabel : "توضیحات",
				name : "ReqDetails"
			}]
		}],

		buttons : [{
			text : "ذخیره",
			iconCls: 'save',
			handler: function() {
				
				me = NewLoanRequestObject;
				mask = new Ext.LoadMask(me.mainPanel, {msg:'در حال ذخيره سازي...'});
				mask.show();  
				me.mainPanel.getForm().submit({
					clientValidation: true,
					url: me.address_prefix + 'global.data.php?task=SaveNewLoanRequest' , 
					method: "POST",
					
					success : function(form,result){
						mask.hide();
						Ext.MessageBox.alert("","اطلاعات با موفقیت ذخیره شد");
					},
					failure : function(){
						mask.hide();
						Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
					}
				});
			}

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