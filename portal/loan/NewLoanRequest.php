<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "loan.data.php?task=GetAllLoans", "grid_div");

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

$col = $dg->addColumn("عنوان وام", "LoanDesc", "");
$col->sortable = false;

$col = $dg->addColumn("سقف مبلغ", "MaxAmount", GridColumn::ColumnType_money);
$col->sortable = false;
$col->width = 140;

$dg->addObject("this.LoanGroups");

$dg->HeaderMenu = false;
$dg->hideHeaders = true;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 200;
$dg->width = 350;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "MaxAmount";


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
			listeners : {
				load : function(){
					me = NewLoanRequestObject;
					me.grid.getStore().proxy.extraParams.GroupID = this.getAt(0).data.InfoID;
					me.grid.render(me.get("DivGrid"));
					me.LoanGroups.setValue(this.getAt(0).data.InfoID);
				}
			}
		}),
		valueField : "InfoID",
		queryMode : "local",
		name : "GroupID",
		displayField : "InfoDesc",
		fieldLabel : "انتخاب گروه",
		listeners :{
			change : function(){
				
				me = NewLoanRequestObject;
				me.grid.getStore().proxy.extraParams.GroupID = this.getValue();
				me.grid.getStore().load();
			}
		}
	});
	
	this.grid = <?= $grid ?>;
	this.LoanGroups.getStore().load();
	
	
	this.mainPanel = new Ext.form.FormPanel({
		renderTo : this.get("mainForm"),
		frame: true,
		hidden : true,
		title: 'درخواست وام',
		width: 400,
		defaults: {
			anchor : "98%"
		},
		items: [{
			xtype : "textfield",
			fieldLabel: 'نام',
			name: 'fname'
		},{
			xtype : "textfield",
			fieldLabel: 'نام خانوادگی',
			name: 'lname'
		},{
			xtype : "textfield",
			regex: /^\d{10}$/,
			maskRe: /[\d\-]/,
			fieldLabel: 'کد ملی',
			name: 'NationalID'
		},{
			xtype : "textfield",
			regex: /^\d{10}$/,
			maskRe: /[\d\-]/,
			fieldLabel: 'کد اقتصادی',
			name: 'EconomicID'
		},{
			xtype : "textfield",
			regex: /^\d{11}$/,
			maskRe: /[\d\-]/,
			fieldLabel: 'شماره تلفن',
			name: 'PhoneNo'
		},{
			xtype : "textfield",
			regex: /^\d{11}$/,
			maskRe: /[\d\-]/,
			fieldLabel: 'تلفن همراه',
			name: 'mobile'
		},{
			xtype : "textfield",
			vtype : "email",
			fieldLabel: 'پست الکترونیک',
			name: 'email',
			fieldStyle : "direction:ltr"
		},{
			xtype : "textarea",
			fieldLabel: 'آدرس',
			name: 'address'
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