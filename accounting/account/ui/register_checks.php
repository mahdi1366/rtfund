<?php 
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

require_once '../../header.inc.php';

$dgh = new sadaf_datagrid("dg",$js_prefix_address."../data/acc_docs.data.php?task=selectChecks","div_dg");

$col = $dgh->addColumn("کد","checkID","",true);

$dgh->addColumn("کد سند","docID");
$col->width = 50;

$col = $dgh->addColumn("حساب", "accountTitle", "", true);
$col->renderer = "function(){return '';}";
$col->width = 100;

$col = $dgh->addColumn("شماره چک", "checkNo");
$col->width = 100;

$col = $dgh->addColumn("تاریخ سررسید", "checkDate", GridColumn::ColumnType_date);
$col->width = 100;

$col = $dgh->addColumn("مبلغ", "amount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dgh->addColumn("در وجه", "reciever");

$col = $dgh->addColumn("وضعیت چک", "checkStatus");
$col->editor = ColumnEditor::ComboBox(
		PdoDataAccess::runquery("select * from basic_info where TypeID=3"), "infoID", "title");
$col->width = 100;

$dgh->width = 780;
$dgh->title = "چک های صادره";
$dgh->DefaultSortField = "checkID";
$dgh->autoExpandColumn = "reciever";
$dgh->DefaultSortDir = "ASC";
$dgh->height = 500;

$dgh->EnableGrouping = true;
$dgh->DefaultGroupField = "accountTitle";

$dgh->enableRowEdit = true ;
$dgh->rowEditOkHandler = "function(v,p,r){ return registerChecksObject.check_Save(v,p,r);}";

$grid = $dgh->makeGrid_returnObjects();

?>
<script>
registerChecks.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : "<?= $js_prefix_address ?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function registerChecks()
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
						registerChecks.filterByDate();
					}
				}
			}
		}],
		frame : true,
		width : 450,
		buttons : [{
			text : "بارگزاری",
			iconCls : "refresh",
			handler : function(){registerChecks.filterByDate();}
		}]
	});
	
	this.checkGrid = <?= $grid ?>;
	this.checkGrid.render(this.get("div_check"));
	
}

var registerChecksObject = new registerChecks();

registerChecks.prototype.check_Save = function(store,record)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/acc_docs.data.php?task=saveChecks',
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
				registerChecksObject.checkGrid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

registerChecks.filterByDate = function(){
	registerChecksObject.checkGrid.getStore().proxy.extraParams["date"] = 
		registerChecksObject.get("cmp_date").value;
	registerChecksObject.checkGrid.getStore().load();
}
</script>
<center><br>
	<div id="fs_date"></div><br>
	<div id="div_check"></div>
</center>

