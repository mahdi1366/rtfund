<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.12
//-------------------------
include('../header.inc.php');
require_once 'request.class.php';
include_once inc_dataGrid;

$framework = isset($_SESSION["USER"]["framework"]);
$PartID = 0;
$editable = false;
if($framework)
{
	if(!empty($_POST["PartID"]))
	{
		$PartID = $_POST["PartID"];

		$obj = new LON_ReqParts($PartID);
		$ReqObj = new LON_requests($obj->RequestID);

		if($ReqObj->IsEnded == "NO")
			$editable = true;
	}
	else
		$editable = true;
}	

$dg = new sadaf_datagrid("dg",$js_prefix_address . "request.data.php?task=GetPartPays","grid_div");

$dg->addColumn("", "BackPayID","", true);
$dg->addColumn("", "PartID","", true);
$dg->addColumn("", "PayTypeDesc","", true);
$dg->addColumn("", "LocalNo","", true);
$dg->addColumn("", "DocStatus","", true);
if($editable)
{
	$col = $dg->addColumn("نحوه پرداخت", "PayType");
	$col->editor = ColumnEditor::ComboBox(PdoDataAccess::runquery("select * from BaseInfo where typeID=6"), 
		"InfoID", "InfoDesc");
}
else
	$col = $dg->addColumn("نحوه پرداخت", "PayTypeDesc");
	
$col->width = 80;

$col = $dg->addColumn("تاریخ", "PayDate", GridColumn::ColumnType_date);
if($editable)
	$col->editor = ColumnEditor::SHDateField();
$col->width = 70;

$col = $dg->addColumn("مبلغ پرداخت", "PayAmount", GridColumn::ColumnType_money);
if($editable)
	$col->editor = ColumnEditor::CurrencyField();
$col->width = 80;

$col = $dg->addColumn("شناسه پیگیری", "PayRefNo");
$col->width = 100;

$col = $dg->addColumn("شماره فیش", "PayBillNo");
if($editable)
	$col->editor = ColumnEditor::TextField(true);
$col->width = 60;

$col = $dg->addColumn("شماره چک", "ChequeNo", "string");
$col->editor = ColumnEditor::NumberField(true);
$col->width = 60;

if($editable)
{
	$col = $dg->addColumn("بانک", "ChequeBank", "");
	$col->editor = ColumnEditor::ComboBox(PdoDataAccess::runquery("select * from ACC_banks"), 
	"BankID", "BankDesc", "", "", true);
}
else
	$col = $dg->addColumn("بانک", "BankDesc", "");
$col->width = 60;

$col = $dg->addColumn("شعبه", "ChequeBranch", "");
if($editable)
	$col->editor = ColumnEditor::TextField(true);
$col->width = 60;

if($editable)
{
	$col = $dg->addColumn("وضعیت چک", "ChequeStatus", "");
	$col->editor = ColumnEditor::ComboBox(PdoDataAccess::runquery("select * from BaseInfo where typeID=16"), 
	"InfoID", "InfoDesc", "", "", true);
}
else
	$col = $dg->addColumn("وضعیت چک", "ChequeStatusDesc", "");
$col->width = 80;


$col = $dg->addColumn("توضیحات", "details", "");
//$col->ellipsis = 30;
if($editable)
	$col->editor = ColumnEditor::TextField(true);

if($editable)
{
	$dg->enableRowEdit = true;
	//$dg->rowEditOkHandler = "function(store,record){return LoanPayObject.BeforeSave(store,record);}";
	$dg->rowEditOkHandler = "function(store,record){return LoanPayObject.SavePartPayment('',record);}";
	
	$dg->addButton("AddBtn", "ایجاد ردیف پرداخت", "add", "function(){LoanPayObject.AddPay();}");
	
	$col = $dg->addColumn("سند", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return LoanPay.RegDocRender(v,p,r);}";
	$col->width = 40;
	
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return LoanPay.DeleteRender(v,p,r);}";
	$col->width = 35;
}
if($framework)
{
	$dg->addButton("cmp_report", "گزارش پرداخت", "report", 
			"function(){LoanPayObject.PayReport();}");
}

$dg->height = 377;
$dg->width = 855;
$dg->emptyTextOfHiddenColumns = true;
$dg->EnableSearch = false;
$dg->HeaderMenu = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "PayDate";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "details";

$grid = $dg->makeGrid_returnObjects();

?>
<script type="text/javascript">

LoanPay.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	
	framework : <?= $framework ? "true" : "false" ?>,
	PartID : <?= $PartID ?>,
	PartRecord : null,
	
	GroupPays : new Array(),
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function LoanPay()
{
	this.grid = <?= $grid ?>;
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.ChequeNo*1>0 && record.data.ChequeStatus != "2")
			return "yellowRow";
		return "";
	}	

	if(this.grid.plugins[0] != undefined)
		this.grid.plugins[0].on("beforeedit", function(editor,e){
			
			if(LoanPayObject.PartRecord != null && LoanPayObject.PartRecord.data.IsEnded == "YES")
				return false;
			
			if(e.record.data.BackPayID == null)
				return true;
			
			if(e.record.data.ChequeNo != null && e.record.data.ChequeStatus != "2")
				return true;
			
			return false;			
		});
		
	if(this.PartID > 0)
	{
		this.grid.getStore().proxy.extraParams = {PartID : this.PartID};
		this.grid.render(this.get("div_grid"));
		return;
	}
		
	this.PartPanel = new Ext.form.FieldSet({
		title: "انتخاب وام",
		width: 700,
		renderTo : this.get("div_loans"),
		frame: true,
		items : [{
			xtype : "combo",
			store: new Ext.data.Store({
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + 'request.data.php?task=selectParts',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['PartAmount',"IsEnded",'PartDesc',"RequestID","PartDate", 
					"PartID","loanFullname","InstallmentAmount",{
					name : "fullTitle",
					convert : function(value,record){
						return "کد وام : " + record.data.RequestID + "  " + record.data.PartDesc + " به مبلغ " + 
							Ext.util.Format.Money(record.data.PartAmount) + " مورخ " + 
							MiladiToShamsi(record.data.PartDate) + " " + record.data.loanFullname;
					}
				}]
			}),
			displayField: 'fullTitle',
			pageSize : 25,
			valueField : "PartID",
			width : 600,
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct" style="height: 23px;">',
				'<td style="padding:7px">کد وام</td>',
				'<td style="padding:7px">فاز وام</td>',
				'<td style="padding:7px">وام گیرنده</td>',
				'<td style="padding:7px">مبلغ وام</td>',
				'<td style="padding:7px">تاریخ پرداخت</td>',
				'<td style="padding:7px"></td>',
				'</tr>',
				'<tpl for=".">',
					'<tpl if="IsEnded == \'YES\'">',
						'<tr class="x-boundlist-item pinkRow" style="border-left:0;border-right:0">',
					'<tpl else>',
						'<tr class="x-boundlist-item" style="border-left:0;border-right:0">',
					'</tpl>',
					'<td style="border-left:0;border-right:0" class="search-item">{RequestID}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{PartDesc}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{loanFullname}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">',
						'{[Ext.util.Format.Money(values.PartAmount)]}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{[MiladiToShamsi(values.PartDate)]}</td>',
					'<tpl if="IsEnded == \'NO\'">',
						'<td class="search-item"><div align=center title="اضافه به پرداخت گروهی" class=add ',
							'onclick=LoanPayObject.AddToGroupPay(event,{RequestID},{PartID},{InstallmentAmount}); ',
							'style=background-repeat:no-repeat;',
							'background-position:center;cursor:pointer;width:20px;height:16></div></td>',
					'<tpl else>',
						'<td class="search-item"></td>',
					'</tpl>',
				' </tr>',
				'</tpl>',
				'</table>'
			),
			itemId : "PartID",
			listeners :{
				select : function(combo,records){
					me = LoanPayObject;
					
					me.grid.getStore().proxy.extraParams = {
						PartID : this.getValue()
					};
					if(me.grid.rendered)
						me.grid.getStore().load();
					else
						me.grid.render(me.get("div_grid"));					
					
					if(records[0].data.IsEnded == "YES")
					{
						me.grid.down("[itemId=AddBtn]").hide();
						me.grid.columns[13].hide();
						me.get("DiVEnded").style.display = "block";
					}
					else
					{
						me.grid.down("[itemId=AddBtn]").show();
						me.get("DiVEnded").style.display = "none";
						me.grid.columns[13].show();
					}
					
					me.PartRecord = records[0];
					me.PartID = records[0].data.PartID;
				}
			}
		},{
			xtype : "button",
			border : true,
			text : "پرداخت گروهی اقساط",
			iconCls : "list",
			handler : function(){ LoanPayObject.BeforeSaveGroupPay(); }
		}]
	});
	
}

LoanPay.DeleteRender = function(v,p,r){
	
	if(r.data.PayRefNo != null &&  r.data.PayRefNo != "")
		return "";
	
	return "<div align='center' title='حذف' class='remove' "+
		"onclick='LoanPayObject.DeletePay();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

LoanPay.RegDocRender = function(v,p,r){
	
	if(r.data.LocalNo == null)
		return "<div align='center' title='صدور سند' class='send' "+
		"onclick='LoanPayObject.BeforeSave(1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
	else if(r.data.DocStatus == "RAW" && r.data.PayType != "4")
		return r.data.LocalNo + "<div align='center' title='ویرایش سند' class='edit' "+
		"onclick='LoanPayObject.BeforeSave(2);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
	else
		return r.data.LocalNo;
}

var LoanPayObject = new LoanPay();
	
LoanPay.prototype.BeforeSave = function(mode){
	
	record =  this.grid.getSelectionModel().getLastSelected(); 
	if(!this.BankWin)
	{
		this.BankWin = new Ext.window.Window({
			width : 400,
			height : 120,
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
			}],
			buttons :[{
				text : "ذخیره",
				iconCls : "save",
				itemId : "btn_save"
			},{
				text : "انصراف",
				iconCls : "undo",
				handler : function(){this.up('window').hide(); LoanPayObject.grid.getStore().load();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.BankWin);
	}
	
	if(record && record.data.ChequeNo*1 > 0 && record.data.ChequeStatus != "2")
	{
		LoanPayObject.SavePartPayment("", record);
		return;
	}
	
	this.BankWin.show();
	this.BankWin.down("[itemId=btn_save]").setHandler(function(){ 
		LoanPayObject.BankWin.hide();
		LoanPayObject.RegisterDoc(
			LoanPayObject.BankWin.down("[itemId=TafsiliID]").getValue(),
			LoanPayObject.BankWin.down("[itemId=TafsiliID2]").getValue(), record, mode); 
	});
}
	
LoanPay.prototype.SavePartPayment = function(BankTafsili, record){

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'request.data.php',
		method: "POST",
		params: {
			task: "SavePartPay",
			//BankTafsili : BankTafsili,
			record: Ext.encode(record.data),
			RegisterDoc : "0"
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				LoanPayObject.grid.getStore().load();
				if(record.data.ChequeNo*1 > 0 && record.data.ChequeStatus != "2")
					Ext.MessageBox.alert("","سند حسابداری هنگام وصول چک صادر می شود");
				else if(LoanPayObject.BankWin && LoanPayObject.BankWin.down("[name=RegisterDoc]").checked)
					Ext.MessageBox.alert("","سند حسابداری مربوطه صادر گردید");
			}
			else
			{
				Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
			}
		},
		failure: function(){}
	});
}

LoanPay.prototype.RegisterDoc = function(BankTafsili, AccountTafsili, record, mode){

	mask = new Ext.LoadMask(this.BankWin, {msg:'در حال ذخیره سازی ...'});
	mask.show();
	
	switch(mode){
		case 1 : task = "SavePartPay"; break;
		case 2 : task = "EditPartPayDoc"; break;
		case 3 : task = "GroupSavePay"; break;
	}
	params = {
			task: task,
			BankTafsili : BankTafsili,
			AccountTafsili : AccountTafsili,
			RegisterDoc : "1"
		};
		
	if(mode == 3)
	{
		params.parts = Ext.encode(this.GroupPays);
		params = mergeObjects(params, this.groupWin.down('form').getForm().getValues());
	}
	else if(mode == 2)
	{
		params.BackPayID = record.data.BackPayID;
	}
	else
		params.record = Ext.encode(record.data);

	Ext.Ajax.request({
		url: this.address_prefix +'request.data.php',
		method: "POST",
		params: params,
		
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				LoanPayObject.grid.getStore().load();
				if(record && record.data.ChequeNo*1 > 0 && record.data.ChequeStatus != "2")
					Ext.MessageBox.alert("","سند حسابداری هنگام وصول چک صادر می شود");
				if(mode == 1)
					Ext.MessageBox.alert("","سند حسابداری مربوطه صادر گردید");
				else if(mode == 2)
					Ext.MessageBox.alert("","سند حسابداری مربوطه ویرایش گردید");
				else
				{
					LoanPayObject.groupWin.hide();
					Ext.MessageBox.alert("","سند گروهی صادر گردید");
				}
			}
			else
			{
				if(st.data == "")
					Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
				else
					Ext.MessageBox.alert("",st.data);
			}
		},
		failure: function(){}
	});
}

LoanPay.prototype.AddPay = function(){

	if(this.PartRecord != null && this.PartRecord.data.IsEnded == "YES")
	{
		Ext.MessageBox.alert("","این وام خاتمه یافته است");
		return;
	}
	
	defaultAmount = 0;alert(this.grid.getStore().totalCount);
	if(this.grid.getStore().totalCount > 0)
		defaultAmount = this.grid.getStore().getAt(0).data.PayAmount;
	
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		BackPayID: null,
		PartID : this.PartID,
		PayAmount : defaultAmount
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

LoanPay.prototype.DeletePay = function(){
	
	Ext.MessageBox.confirm("","در صورت حذف سند مربوطه نیز حذف خواهد شد. <br>"+"آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = LoanPayObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(me.grid, {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'request.data.php',
			params:{
				task: "DeletePay",
				BackPayID : record.data.BackPayID
			},
			method: 'POST',

			success: function(response,option){
				result = Ext.decode(response.responseText);
				if(result.success)
					LoanPayObject.grid.getStore().load();
				else if(result.data == "")
					Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
				else
					Ext.MessageBox.alert("",result.data);
				mask.hide();
				
			},
			failure: function(){}
		});
	});
}

LoanPay.prototype.PayReport = function(){

	window.open(this.address_prefix + "../report/LoanPayment.php?show=true&PartID=" + this.PartID);
}

LoanPay.prototype.AddToGroupPay = function(e ,RequestID, PartID, InstallmentAmount){

	if(!this.groupAmountWin)
	{
		this.groupAmountWin = new Ext.window.Window({
			width : 300,
			height : 100,
			modal : true,
			title : "نحوه پرداخت",
			bodyStyle : "background-color:white",
			items : [{
				xtype : "currencyfield",
				hideTrigger : true,
				fieldLabel : "مبلغ پرداخت"
			}],
			closeAction : "hide",
			buttons : [{
				text : "اضافه به پرداخت گروهی",				
				iconCls : "add",
				itemId : "btn_add"	
			}]

		});
	}
	this.groupAmountWin.down('currencyfield').setValue(InstallmentAmount);
	this.groupAmountWin.down("[itemId=btn_add]").setHandler(function(){
		amount = this.up('window').down('currencyfield').getValue();
		LoanPayObject.GroupPays.push(PartID + "_" + amount);
		LoanPayObject.groupAmountWin.hide();
	})
	this.groupAmountWin.show();
	this.groupAmountWin.center();
	e.stopImmediatePropagation();	
}

LoanPay.prototype.BeforeSaveGroupPay = function(){

	if(this.GroupPays.length == 0)
	{
		Ext.MessageBox.alert("","تا کنون وامی به پرداخت گروهی اضافه نشده است");
		return;
	}
	if(!this.groupWin)
	{
		this.groupWin = new Ext.window.Window({
			width : 300,
			height : 250,
			modal : true,
			title : "نحوه پرداخت",
			bodyStyle : "background-color:white",
			items : new Ext.form.Panel({
				items : [{
					xtype : "combo",
					store : new Ext.data.Store({
						proxy:{
							type: 'jsonp',
							url: this.address_prefix + 'request.data.php?task=GetPayTypes',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						fields :  ["InfoID", "InfoDesc"]
					}),
					displayField: 'InfoDesc',
					valueField : "InfoID",
					name : "PayType",
					allowBlank : false,
					fieldLabel : "نوع پرداخت"
				},{
					xtype : "shdatefield",
					name : "PayDate",
					allowBlank : false,
					fieldLabel : "تاریخ پرداخت"
				},{
					xtype : "textfield",
					name : "PayBillNo",
					fieldLabel : "شماره فیش"
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
							url: this.address_prefix + 'request.data.php?task=GetBanks',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						fields :  ["BankID", "BankDesc"]
					}),
					displayField: 'BankDesc',
					valueField : "BankID",
					name : "ChequeBank",
					fieldLabel : "بانک"
				},{
					xtype : "textfield",
					name : "ChequeBranch",
					fieldLabel : "شعبه"
				},{
					xtype : "combo",
					store : new Ext.data.Store({
						proxy:{
							type: 'jsonp',
							url: this.address_prefix + 'request.data.php?task=GetChequeStatuses',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						fields :  ["InfoID", "InfoDesc"]
					}),
					displayField: 'InfoDesc',
					valueField : "InfoID",
					name : "ChequeStatus",
					fieldLabel : "وضعیت چک"
				}]
			}),
			closeAction : "hide",
			buttons : [{
				text : "صدور گروهی اقساط",				
				iconCls : "save",
				itemId : "btn_save",
				handler : function(){
					if(!this.up('window').down('form').getForm().isValid())
						return;
					LoanPayObject.BeforeSave(3);
				}		
			}]

		});
	}
	this.groupWin.show();
	this.groupWin.center();
}



</script>
<center>
	<div id="div_loans"></div>
	<div style="display:none;color : red;font-weight: bold" id="DiVEnded">
		 این وام خاتمه یافته و قادر به تغییر در پرداخت های آن نمی باشید
		<br>&nbsp;</div>
	<div id="div_grid"></div>
</center>