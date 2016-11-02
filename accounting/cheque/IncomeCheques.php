<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.12
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "cheques.data.php?task=selectIncomeCheques", "grid_div");

$col = $dg->addColumn("", "OuterChequeID", "", true);
$col = $dg->addColumn("", "BackPayID", "", true);
$dg->addColumn("", "ChequeStatus", "", true);

$col = $dg->addColumn("صاحب چک", "fullname", "");

$col = $dg->addColumn("حساب", "CostDesc");
$col->width = 100;

$col = $dg->addColumn("شماره چک", "ChequeNo");
$col->width = 70;

$col = $dg->addColumn("تاریخ چک", "ChequeDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("مبلغ چک", "ChequeAmount", GridColumn::ColumnType_money);
$col->width = 80;

$col = $dg->addColumn("بانک", "BankDesc");
$col->width = 60;

$col = $dg->addColumn("شعبه", "ChequeBranch");
$col->width = 70;

$col = $dg->addColumn("وضعیت چک", "ChequeStatusDesc", "");
$col->width = 80;

$col = $dg->addColumn("اسناد", "docs", "");
$col->width = 80;

$dg->addButton("", "اضافه چک", "add", "function(){IncomeChequeObject.AddOuterCheque();}");
$dg->addButton("", "تغییر وضعیت چک", "refresh", "function(){IncomeChequeObject.beforeChangeStatus();}");
$dg->addButton("", "تعویض چک", "copy", "function(){IncomeChequeObject.ChangeCheque();}");

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 400;
$dg->width = 800;
$dg->title = "چک های دریافتی";
$dg->DefaultSortField = "ChequeDate";
$dg->DefaultSortDir = "Desc";
$dg->autoExpandColumn = "fullname";
$grid = $dg->makeGrid_returnObjects();

?>
<script>

IncomeCheque.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	ChangingCheque : false,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function IncomeCheque(){
	
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("div_form"),
		width : 600,
		frame : true,
		collapsible : true,
		collapsed : true,
		title : "فیلتر لیست",
		layout : {
			type : "table",
			columns : 2			
		},
		
		items : [{
			xtype : "numberfield",
			name : "FromNo",
			hideTrigger : true,
			fieldLabel : "از شماره چک"
		},{
			xtype : "numberfield",
			name : "ToNo",
			hideTrigger : true,
			fieldLabel : "تا شماره چک"
		},{
			xtype : "shdatefield",
			name : "FromDate",
			fieldLabel : "از تاریخ چک"
		},{
			xtype : "shdatefield",
			name : "ToDate",
			fieldLabel : "تا تاریخ چک"
		},{
			xtype : "currencyfield",
			name : "FromAmount",
			hideTrigger : true,
			fieldLabel : "از مبلغ"
		},{
			xtype : "currencyfield",
			name : "ToAmount",
			hideTrigger : true,
			fieldLabel : "تا مبلغ"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?' +
						"task=GetBankData",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['BankID','BankDesc'],
				autoLoad : true
			}),
			fieldLabel : "بانک",
			displayField : "BankDesc",
			queryMode : "local",
			valueField : "BankID",
			hiddenName :"ChequeBank"
		},{
			xtype : "textfield",
			name : "ChequeBranch",
			fieldLabel : "شعبه"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + 'cheques.data.php?' +
						"task=SelectIncomeChequeStatuses",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['InfoID','InfoDesc'],
				autoLoad : true
			}),
			fieldLabel : "وضعیت چک",
			displayField : "InfoDesc",
			queryMode : "local",
			valueField : "InfoID",
			hiddenName :"ChequeStatus"
		}],
		buttons :[{
			text : "جستجو",
			iconCls : "search",
			handler : function(){
				IncomeChequeObject.grid.getStore().loadPage(1);
			}
		},{
			text : "پاک کردن فرم",
			iconCls : "clear",
			handler : function(){
				this.up('form').getForm().reset();
			}
		}]
	});
	
	this.formPanel.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
		if(!IncomeChequeObject.grid.rendered)
			IncomeChequeObject.grid.render(IncomeChequeObject.get("div_grid"));
		else
			IncomeChequeObject.grid.getStore().loadPage(1);
		e.preventDefault();
		e.stopEvent();
		return false;
	});
		 
	this.grid = <?= $grid ?>;
	this.grid.getStore().proxy.form = this.get("MainForm");
	this.grid.render(this.get("div_grid"));
	
	this.OuterChequeWin = new Ext.window.Window({
		width : 400,
		height : 250,
		modal : true,
		closeAction : "hide",
		items : new Ext.form.Panel({
			items :[{
				xtype : "combo",
				width : 350,
				fieldLabel : "کد حساب",
				colspan : 2,
				store: new Ext.data.Store({
					fields:["CostID","CostCode","CostDesc", "TafsiliType",{
						name : "fullDesc",
						convert : function(value,record){
							return "[ " + record.data.CostCode + " ] " + record.data.CostDesc
						}				
					}],
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectCostCode',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					}
				}),
				typeAhead: false,
				name : "CostID",
				valueField : "CostID",
				displayField : "fullDesc",
				listConfig: {
					loadingText: 'در حال جستجو...',
					emptyText: 'فاقد اطلاعات'
				},
				listeners :{
					select : function(combo,records){
						if(records[0].data.TafsiliType != null)
						{
							combo = IncomeChequeObject.OuterChequeWin.down("[name=TafsiliID]");
							combo.enable();
							combo.setValue();
							combo.getStore().proxy.extraParams["TafsiliType"] = records[0].data.TafsiliType;
							combo.getStore().load();

							IncomeChequeObject.OuterChequeWin.
								down("[name=TafsiliType]").setValue(records[0].data.TafsiliType);
						}
					}
				}
			},{
				xtype : "combo",
				width : 350,
				disabled : true,
				fieldLabel : "تفصیلی",
				store: new Ext.data.Store({
					fields:["TafsiliID","TafsiliCode","TafsiliDesc"],
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=GetAllTafsilis',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					}
				}),
				typeAhead: false,
				pageSize : 10,
				name : "TafsiliID",
				valueField : "TafsiliID",
				displayField : "TafsiliDesc"
			},{
				xtype : "shdatefield",
				name : "ChequeDate",
				fieldLabel : "تاریخ چک"
			},{
				xtype : "currencyfield",
				name : "ChequeAmount",
				hideTrigger : true,
				fieldLabel : "مبلغ چک"
			},{
				xtype : "numberfield",
				name : "ChequeNo",
				hideTrigger : true,
				fieldLabel : "شماره چک"
			},{
				xtype : "combo",
				store : new Ext.data.Store({
					proxy:{
						type: 'jsonp',
						url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=GetBanks',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields :  ["BankID", "BankDesc"],
					autoLoad : true
				}),
				queryMode : "local",
				displayField: 'BankDesc',
				valueField : "BankID",
				name : "ChequeBank",
				fieldLabel : "بانک"
			},{
				xtype : "textfield",
				name : "ChequeBranch",
				fieldLabel : "شعبه"
			},{
				xtype : "hidden",
				name : "TafsiliType"
			}]
		}),
		buttons :[{
			text : "ذخیره",
			iconCls : "save",
			itemId : "btn_save",
			handler : function(){ IncomeChequeObject.SaveOuterCheque();}
		}]
	});
	Ext.getCmp(this.TabID).add(this.OuterChequeWin);
}

IncomeChequeObject = new IncomeCheque();

IncomeCheque.prototype.beforeChangeStatus = function(){
	
	if(!this.commentWin)
	{
		this.commentWin = new Ext.window.Window({
			width : 414,
			height : 85,
			modal : true,
			bodyStyle : "background-color:white",
			items : [{
				xtype : "combo",
				store: new Ext.data.Store({
					proxy:{
						type: 'jsonp',
						url: this.address_prefix + 'cheques.data.php?task=selectValidChequeStatuses',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields :  ['InfoID',"InfoDesc"]
				}),
				queryMode : "local",
				displayField: 'InfoDesc',
				valueField : "InfoID",
				width : 400,
				name : "DstID"
			}],
			closeAction : "hide",
			buttons : [{
				text : "تغییر وضعیت",				
				iconCls : "refresh",
				itemId : "btn_save"
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.commentWin);
	}
	var record = this.grid.getSelectionModel().getLastSelected();
	
	this.commentWin.down("[name=DstID]").setValue();
	this.commentWin.down("[name=DstID]").getStore().proxy.extraParams.SrcID = record.data.ChequeStatus;
	this.commentWin.down("[name=DstID]").getStore().load();
	
	this.commentWin.down("[itemId=btn_save]").setHandler(function(){
		status = this.up('window').down("[name=DstID]").getValue();
		if(status == "3")
			IncomeChequeObject.AccountInfoWin();
		else
			IncomeChequeObject.ChangeStatus();
	});
		
	this.commentWin.show();
	this.commentWin.center();
}

IncomeCheque.prototype.AccountInfoWin = function(){
	
	if(!this.BankWin)
	{
		this.BankWin = new Ext.window.Window({
			width : 400,
			height : 220,
			modal : true,
			closeAction : "hide",
			items : [{
				xtype : "combo",
				store: new Ext.data.Store({
					fields:["TafsiliID","TafsiliCode","TafsiliDesc",{
						name : "title",
						convert : function(v,r){ return "[ " + r.data.TafsiliCode + " ] " + r.data.TafsiliDesc;}
					}],
					proxy: {
						type: 'jsonp',
						url: '/accounting/baseinfo/baseinfo.data.php?task=GetAllTafsilis&TafsiliType=6',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					}
				}),
				emptyText:'انتخاب بانک ...',
				typeAhead: false,
				pageSize : 10,
				width : 385,
				valueField : "TafsiliID",
				itemId : "TafsiliID",
				displayField : "title"
			},{
				xtype : "combo",
				store: new Ext.data.Store({
					fields:["TafsiliID","TafsiliCode","TafsiliDesc",{
						name : "title",
						convert : function(v,r){ return "[ " + r.data.TafsiliCode + " ] " + r.data.TafsiliDesc;}
					}],
					proxy: {
						type: 'jsonp',
						url: '/accounting/baseinfo/baseinfo.data.php?task=GetAllTafsilis&TafsiliType=3',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					}
				}),
				emptyText:'انتخاب حساب ...',
				typeAhead: false,
				pageSize : 10,
				width : 385,
				valueField : "TafsiliID",
				itemId : "TafsiliID2",
				displayField : "title"
			},{
				xtype : "checkbox",
				boxLabel : "از حساب مرکز",
				itemId : "CenterAccount",
				inputValue : "1"
			},{
				xtype : "combo",
				store : new Ext.data.SimpleStore({
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + '../../framework/baseInfo/baseInfo.data.php?' +
							"task=SelectBranches",
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields : ['BranchID','BranchName'],
					autoLoad : true					
				}),
				fieldLabel : "شعبه واسط ",
				queryMode : 'local',
				width : 385,
				displayField : "BranchName",
				valueField : "BranchID",
				itemId : "BranchID"
			}],
			buttons :[{
				text : "ذخیره",
				iconCls : "save",
				itemId : "btn_save",
				handler : function(){IncomeChequeObject.ChangeStatus();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.BankWin);
	}
	this.BankWin.show();
	this.BankWin.down("[itemId=btn_save]").setHandler(function(){ 
		IncomeChequeObject.BankWin.hide();
		IncomeChequeObject.ChangeStatus(); 
	});
}

IncomeCheque.prototype.ChangeStatus = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	StatusID = this.commentWin.down("[name=DstID]").getValue();
	
	BankTafsili = StatusID == "3" ? IncomeChequeObject.BankWin.down("[itemId=TafsiliID]").getValue() : "";
	AccountTafsili = StatusID == "3" ? IncomeChequeObject.BankWin.down("[itemId=TafsiliID2]").getValue() : "";
	CenterAccount = StatusID == "3" ? IncomeChequeObject.BankWin.down("[itemId=CenterAccount]").getValue() : "";
	BranchID = StatusID == "3" ? IncomeChequeObject.BankWin.down("[itemId=BranchID]").getValue() : "";
		
	if(CenterAccount == true && BranchID == null)
	{
		Ext.MessageBox.alert("Error","برای ثبت حساب مرکز انتخاب شعبه واسط الزامی است");
		return;
	}
	
	if(StatusID == null || StatusID == "")
		return;
	
	mask = new Ext.LoadMask(this.grid, {msg:'در حال تغییر وضعیت ...'});
	mask.show();
	
	Ext.Ajax.request({
		methos : "post",
		url : this.address_prefix + "cheques.data.php",
		params : {
			task : "ChangeChequeStatus",
			BackPayID : record.data.BackPayID,
			OuterChequeID : record.data.OuterChequeID,
			StatusID : StatusID,
			BankTafsili : BankTafsili,
			AccountTafsili : AccountTafsili,
			CenterAccount : CenterAccount,
			BranchID : BranchID
		},
		
		success : function(response){
			mask.hide();
			IncomeChequeObject.commentWin.hide();
			
			result = Ext.decode(response.responseText);
			if(result.success)
				IncomeChequeObject.grid.getStore().load();
			else if(result.data != "")
				Ext.MessageBox.alert("",result.data);
			else
				Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
			
			
		}
	});
}

IncomeCheque.prototype.AddOuterCheque = function(){
	
	this.OuterChequeWin.show();
	this.OuterChequeWin.down("[name=CostID]").show();
	this.OuterChequeWin.down("[name=TafsiliID]").show();
	this.OuterChequeWin.down("[name=TafsiliID]").disable();
}

IncomeCheque.prototype.SaveOuterCheque = function(){
	
	mask = new Ext.LoadMask(this.OuterChequeWin, {msg:'در حال ذخيره سازي...'});
	mask.show();

	params = {};
	if(this.ChangingCheque)
	{
		var record = this.grid.getSelectionModel().getLastSelected();
		params.ChangingCheque = "true";
		params.RefOuterChequeID = record.data.OuterChequeID;
		params.RefBackPayID = record.data.BackPayID;
	}
	
	this.OuterChequeWin.down('form').getForm().submit({
		clientValidation: true,
		url: this.address_prefix + 'cheques.data.php?task=SaveOuterCheque',
		method : "POST",
		params : params,

		success : function(form,action){                
			IncomeChequeObject.grid.getStore().load();
			IncomeChequeObject.OuterChequeWin.hide();
			mask.hide();

		},
		failure : function(form,action)
		{
			Ext.MessageBox.alert("Error","عملیات مورد نظر با شکست مواجه شد");
			mask.hide();
		}
	});

}

IncomeCheque.prototype.ChangeCheque = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	if(!record)
	{
		Ext.MessageBox.alert("","انتخاب ردیف چک مورد تغییر الزامی است");
		return;
	}
	if(record.data.ChequeStatus == "<?= OUERCHEQUE_VOSUL ?>")
	{
		Ext.MessageBox.alert("","چک وصول شده قابل تغییر نمی باشد");
		return;
	}
	this.OuterChequeWin.show();
	this.OuterChequeWin.down("[name=CostID]").hide();
	this.OuterChequeWin.down("[name=TafsiliID]").hide();
	this.ChangingCheque = true;
}
</script>
<center>
	<br>
	<form id="MainForm">
		<div id="div_form"></div>
	</form>
	<br>
	<div id="div_grid"></div>
</center>