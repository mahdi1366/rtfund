<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once 'header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . 
		"request/request.data.php?task=SelectNewAgentLoans", "grid_div");

$col = $dg->addColumn("شماره وام", "RequestID", "");
$col->width = 90;

$col = $dg->addColumn("شعبه", "BranchName", "");
$col->width = 90;

$col = $dg->addColumn("تاریخ درخواست", "ReqDate", GridColumn::ColumnType_date);
$col->width = 110;

$col = $dg->addColumn("مبلغ درخواست", "ReqAmount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("معرفی کننده", "ReqFullname");

$col = $dg->addColumn("گیرنده وام", "LoanFullname");
$col->renderer = "function(v,p,r){return v == '' || v == null ? r.data.BorrowerDesc : v;}";
$col->width = 120;

$dg->emptyTextOfHiddenColumns = true;
$dg->EnablePaging = false;
$dg->height = 200;
$dg->width = 770;
$dg->title = "معرفی نامه های جدید وام";
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->disableFooter = true;
$dg->DefaultSortField = "ReqDate";
$dg->autoExpandColumn = "ReqFullname";
$grid1 = $dg->makeGrid_returnObjects();
//---------------------------------------------
$dg = new sadaf_datagrid("dg", $js_prefix_address . "request/request.data.php?task=SelectReadyToPayParts", "grid_div");

$col = $dg->addColumn("شماره وام", "RequestID", "");
$col->width = 70;
$col->align = "center";

$col = $dg->addColumn("مرحله وام", "PartDesc", "");
$col->width = 130;

$col = $dg->addColumn("تاریخ پرداخت", "PartDate", GridColumn::ColumnType_date);
$col->width = 100;

$col = $dg->addColumn("مبلغ ", "PartAmount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("معرفی کننده", "ReqFullname");
$col->width = 190;

$col = $dg->addColumn("گیرنده وام", "LoanFullname");
$col->renderer = "function(v,p,r){return v == '' || v == null ? r.data.BorrowerDesc : v;}";

$dg->emptyTextOfHiddenColumns = true;
$dg->EnablePaging = false;
$dg->height = 200;
$dg->width = 770;
$dg->title = "درخواست های وام آماده پرداخت";
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->disableFooter = true;
$dg->DefaultSortField = "PartDate";
$dg->autoExpandColumn = "LoanFullname";
$grid2 = $dg->makeGrid_returnObjects();
?>
<script>

StartPage.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function StartPage(){
	
	this.grid1 = <?= $grid1 ?>;
	this.grid1.on("itemdblclick", function(view, record){
		framework.OpenPage("../loan/request/RequestInfo.php", "اطلاعات درخواست", {RequestID : record.data.RequestID});
	});	
	this.grid1.render(this.get("DivGrid1"));
	//.......................................................
	this.grid2 = <?= $grid2 ?>;
	this.grid2.on("itemdblclick", function(view, record){
		framework.OpenPage("../loan/request/RequestInfo.php", "اطلاعات درخواست", {RequestID : record.data.RequestID});
	});	
	this.grid2.render(this.get("DivGrid2"));
}

StartPageObject = new StartPage();

</script>
<center><br>
	<div id="DivGrid1"></div><br>
	<div id="DivGrid2"></div>
</center>