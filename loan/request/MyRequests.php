<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;


$dg = new sadaf_datagrid("dg", $js_prefix_address . "request.data.php?task=SelectMyRequests", "grid_div");

$dg->addColumn("", "StatusID", "", true);
$dg->addColumn("", "StatusID", "", true);

$col = $dg->addColumn("پیگیری", "RequestID", "");
$col->width = 50;

$col = $dg->addColumn("تاریخ درخواست", "ReqDate", GridColumn::ColumnType_date);
$col->width = 110;

$col = $dg->addColumn("شرکت", "BorrowerDesc", "");

$col = $dg->addColumn("مبلغ درخواست", "ReqAmount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("وضعیت", "StatusDesc", "");
$col->width = 100;

$col = $dg->addColumn("عملیات", "");
$col->renderer = "function(v,p,r){return MyRequest.OperationRender(v,p,r);}";
$col->width = 50;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 420;
$dg->width = 770;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->title = "درخواست ارسالی";
$dg->DefaultSortField = "ReqDate";
$dg->autoExpandColumn = "BorrowerDesc";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
	
MyRequest.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function MyRequest()
{
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("DivGrid"))
}

MyRequestObject = new MyRequest();

MyRequest.OperationRender = function(v,p,r){
	
	if(r.data.StatusID != "1")
		return "";
	return "<div align='center' title='ویرایش' class='edit' "+
		"onclick='MyRequestObject.EditRequest();' " +
		"style='float:right;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:18px;height:16'></div>" + 
		
		"<div align='center' title='حذف' class='remove' "+
		"onclick='MyRequestObject.DeleteRequest();' " +
		"style='float:right;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:18px;height:16'></div>";
}

MyRequest.prototype.EditRequest = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	portal.OpenPage("../loan/request/RequestInfo.php", {RequestID : record.data.RequestID});
}

MyRequest.prototype.DeleteRequest = function()
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = MyRequestObject;
		record = me.grid.getSelectionModel().getLastSelected();
		mask = new Ext.LoadMask(me.grid, {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'request.data.php',
			method: "POST",
			params: {
				task: "DeleteRequest",
				RequestID : record.data.RequestID
			},
			success: function(response){
				result = Ext.decode(response.responseText);
				mask.hide();
				if(result.success)
					MyRequestObject.grid.getStore().load();
				else
					Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد;")
			}
		});
	});

}

</script>
<center>
	<div id="DivGrid"></div>
</center>