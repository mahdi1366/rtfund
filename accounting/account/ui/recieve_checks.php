<?php 
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

require_once '../../header.inc.php';

$dgh = new sadaf_datagrid("dg",$js_prefix_address."../data/acc_docs.data.php?task=selectFactorChecks","div_dg");

$dgh->addColumn("کد","rowID","",true);

$col = $dgh->addColumn("شماره","rowID");
$col->width = 50;

$col = $dgh->addColumn("فاکتور","factorIDs");
$col->width = 50;

$col = $dgh->addColumn("صاحب چک","customerName");

$dgh->addColumn("", "bankTitle", "", true);

$col = $dgh->addColumn("شعبه", "branch");
$col->width = 50;

$col = $dgh->addColumn("شماره چک", "checkNo");
$col->width = 80;

$col = $dgh->addColumn("شماره حساب", "accountNo");
$col->width = 100;

$col = $dgh->addColumn("تاریخ چک", "checkDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dgh->addColumn("مبلغ", "amount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dgh->addColumn("وضعیت چک", "checkStatus");
$col->editor = ColumnEditor::ComboBox(
		PdoDataAccess::runquery("select * from basic_info where TypeID=3"), "infoID", "title");
$col->width = 70;

$col = $dgh->addColumn("تاریخ وصول", "payOffDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField(true);
$col->width = 70;

$col = $dgh->addColumn("شماره سند", "accDocID");
$col->editor = ColumnEditor::NumberField(true);
$col->width = 60;

//$dgh->width = 780;
$dgh->title = "چک های دریافتی";
$dgh->DefaultSortField = "rowID";
$dgh->autoExpandColumn = "customerName";
$dgh->DefaultSortDir = "ASC";
$dgh->height = 500;

$dgh->EnableGrouping = true;
$dgh->DefaultGroupField = "bankTitle";

$dgh->enableRowEdit = true ;
$dgh->rowEditOkHandler = "function(v,p,r){ return recieveChecksObject.check_Save(v,p,r);}";

$grid = $dgh->makeGrid_returnObjects();

?>
<script>
recieveChecks.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : "<?= $js_prefix_address ?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function recieveChecks()
{
	new Ext.panel.Panel({
		renderTo : this.get("fs_date"),
		items :[{
			xtype : "shdatefield",
			labelWidth : 250,
			fieldLabel : "فیلتر چک ها بر اساس تاریخ سررسید",
			inputId : "cmp_date",
			enableKeyEvents : true,
			listeners :{
				keyup : function(el,e){
					if(e.getKey() == e.ENTER)
					{
						el.setValue(el.getRawValue());
						recieveChecks.filterByDate();
					}
				}
			}
		}],
		frame : true,
		width : 450,
		buttons : [{
			text : "بارگزاری",
			iconCls : "refresh",
			handler : function(){recieveChecks.filterByDate();}
		}]
	});
	
	this.checkGrid = <?= $grid ?>;
	this.checkGrid.render(this.get("div_check"));
}

var recieveChecksObject = new recieveChecks();

recieveChecks.prototype.check_Save = function(store,record)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/acc_docs.data.php?task=saveRecieveCheck',
		method: 'POST',
		params: {
			record : Ext.encode(record.data)
		},
		form : this.get("checkForm"),

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				recieveChecksObject.checkGrid.getStore().load();
			}
			else
			{
				alert("شماره سند معتبر نمی باشد.");
			}
		},
		failure: function(){}
	});
}

recieveChecks.filterByDate = function(){
	recieveChecksObject.checkGrid.getStore().proxy.extraParams["date"] = 
		recieveChecksObject.get("cmp_date").value;
	recieveChecksObject.checkGrid.getStore().load();
}
</script>
<center><br>
	<div id="fs_date"></div><br>
	<div id="div_check"></div>
</center>

