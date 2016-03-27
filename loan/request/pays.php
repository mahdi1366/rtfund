<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.12
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

$framework = isset($_SESSION["USER"]["framework"]);
$PartID = 0;
if($framework)
{
	if(empty($_POST["PartID"]))
		die();
	$PartID = $_POST["PartID"];
}	

$dg = new sadaf_datagrid("dg",$js_prefix_address . "request.data.php?task=GetPartPays","grid_div");

$dg->addColumn("", "PayID","", true);
$dg->addColumn("", "PartID","", true);
$dg->addColumn("", "PayTypeDesc","", true);

$col = $dg->addColumn("نحوه پرداخت", "PayType");
$col->editor = ColumnEditor::ComboBox(PdoDataAccess::runquery("select * from BaseInfo where typeID=6"), 
		"InfoID", "InfoDesc");
$col->width = 100;

$col = $dg->addColumn("تاریخ", "PayDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 80;

$col = $dg->addColumn("مبلغ پرداخت", "PayAmount", GridColumn::ColumnType_money);
$col->editor = ColumnEditor::CurrencyField();
$col->width = 90;

$col = $dg->addColumn("شناسه پیگیری", "PayRefNo");

$col = $dg->addColumn("شماره فیش", "PayBillNo");
$col->editor = ColumnEditor::TextField(true);
$col->width = 100;

$col = $dg->addColumn("شماره چک", "ChequeNo", "string");
$col->editor = ColumnEditor::NumberField(true);
$col->width = 80;

$col = $dg->addColumn("بانک", "ChequeBank", "");
$col->editor = ColumnEditor::ComboBox(PdoDataAccess::runquery("select * from ACC_banks"), 
	"BankID", "BankDesc", "", "", true);
$col->width = 70;

$col = $dg->addColumn("شعبه", "ChequeBranch", "");
$col->editor = ColumnEditor::TextField(true);
$col->width = 90;

if($framework)
{
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(store,record){return LoanPayObject.SavePartPayment(store,record);}";
	
	$dg->addButton = true;
	$dg->addHandler = "function(){LoanPayObject.AddPay();}";
	
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return LoanPay.DeleteRender(v,p,r);}";
	$col->width = 50;
}
$dg->height = 377;
$dg->width = 755;
$dg->emptyTextOfHiddenColumns = true;
$dg->EnableSearch = false;
$dg->HeaderMenu = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "PayDate";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "PayRefNo";

$grid = $dg->makeGrid_returnObjects();

?>
<script type="text/javascript">

LoanPay.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	
	framework : <?= $framework ? "true" : "false" ?>,
	PartID : <?= $PartID ?>,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function LoanPay()
{
	this.grid = <?= $grid ?>;
	if(this.framework)
	{
		this.grid.plugins[0].on("beforeedit", function(editor,e){
			if(e.record.data.PayRefNo != null && e.record.data.PayRefNo != "")
				return false;
		});
		
		this.grid.getStore().proxy.extraParams = {PartID : this.PartID};
		this.grid.render(this.get("div_grid"));
		return;
	}
	
	
	this.PartPanel = new Ext.form.FieldSet({
		title: "انتخاب وام",
		width: 700,
		renderTo : this.get("div_loans"),
		collapsible : true,
		collapsed : false,
		frame: true,
		items : [{
			xtype : "combo",
			store: new Ext.data.Store({
				autoLoad : true,
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + 'request.data.php?task=selectMyParts',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['PartAmount','PartDesc',"RequestID","PartDate", "PartID",{
					name : "fullTitle",
					convert : function(value,record){
						return "کد وام : " + record.data.RequestID + "  " + record.data.PartDesc + " به مبلغ " + 
							Ext.util.Format.Money(record.data.PartAmount) + " مورخ " + 
							MiladiToShamsi(record.data.PartDate);
					}
				}]
			}),
			displayField: 'fullTitle',
			valueField : "PartID",
			queryMode: "local",
			width : 600,
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct" style="height: 23px;">',
				'<td style="padding:7px">کد وام</td>',
				'<td style="padding:7px">فاز وام</td>',
				'<td style="padding:7px">مبلغ وام</td>',
				'<td style="padding:7px">تاریخ پرداخت</td> </tr>',
				'<tpl for=".">',
					'<tr class="x-boundlist-item" style="border-left:0;border-right:0">',
					'<td style="border-left:0;border-right:0" class="search-item">{RequestID}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{PartDesc}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">',
						'{[Ext.util.Format.Money(values.PartAmount)]}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{[MiladiToShamsi(values.PartDate)]}</td> </tr>',
				'</tpl>',
				'</table>'
			),
			itemId : "PartID",
			listeners :{
				select : function(){
					LoanPayObject.grid.getStore().proxy.extraParams = {
						PartID : this.getValue()
					};
					if(LoanPayObject.grid.rendered)
						LoanPayObject.grid.getStore().load();
					else
						LoanPayObject.grid.render(LoanPayObject.get("div_grid"));

					LoanPayObject.PartPanel.collapse();
				}
			}
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

var LoanPayObject = new LoanPay();
	
LoanPay.prototype.SavePartPayment = function(store, record){

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'request.data.php',
		method: "POST",
		params: {
			task: "SavePartPay",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				LoanPayObject.grid.getStore().load();
			}
			else
			{
				alert("خطا در اجرای عملیات");
			}
		},
		failure: function(){}
	});
}

LoanPay.prototype.AddPay = function(){

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		PayID: null,
		PartID : this.PartID
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

LoanPay.prototype.DeletePay = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = LoanPayObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'request.data.php',
			params:{
				task: "DeletePay",
				PayID : record.data.PayID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				LoanPayObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

</script>
<center>
	<div id="div_loans"></div>
	<div id="div_grid"></div>
</center>