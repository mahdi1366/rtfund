<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1395.04
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "deposite.data.php?task=selectDeposites", "grid_div");

$col = $dg->addColumn("حساب", "CostDesc");
$col->width = 200;

$col = $dg->addColumn("تفصیلی", "TafsiliDesc");

$col = $dg->addColumn("مبلغ", "amount", GridColumn::ColumnType_money);
$col->width = 100;

if($accessObj->EditFlag)
	$dg->addButton("", "صدور سند سود سپرده", "process", "function(){DepositeObject.ComputeProfit()}");

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 400;
$dg->width = 800;
$dg->title = "سپرده های کوتاه مدت و بلند مدت";
$dg->DefaultSortField = "amount";
$dg->DefaultSortDir = "Desc";
$dg->autoExpandColumn = "TafsiliDesc";
$grid = $dg->makeGrid_returnObjects();

?>
<script>

Deposite.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Deposite(){
	
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("div_grid"));
}

DepositeObject = new Deposite();

Deposite.prototype.ComputeProfit = function(){
	
	msg = "آیا مایل به محاسبه و صدور سند سود سپرده های کوتاه مدت و بلند مدت می باشید؟";
	Ext.MessageBox.confirm("",msg,function(btn){
		if(btn == "no")
			return;
		
		me = DepositeObject;
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال تایید سند ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + '../docs/doc.data.php?task=ComputeDoc',
			params:{
				ComputeType : "DepositeProfit"
			},
			method: 'POST',

			success: function(response){
				result = Ext.decode(response.responseText);
				mask.hide();
				if(result.success)
					Ext.MessageBox.alert("", "سود مربوطه در سند " + result.data + " صادر گردید.");
				else
					Ext.MessageBox.alert("Error", 
						result.data == "" ? "عملیات مورد نظر با شکست مواجه شد" : result.data);
			},
			failure: function(){}
		});
	});
}
</script>
<center>
	<br>
	<div id="div_grid"></div>
</center>