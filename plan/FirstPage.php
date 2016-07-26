<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once 'header.inc.php';
require_once 'plan/plan.class.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "plan/plan.data.php?task=GetExpertsSummary", "grid_div");

$dg->addColumn("", "RowID", "", true);
$dg->addColumn("", "StatusDesc", "", true);

$col = $dg->addColumn("شماره طرح", "PlanID", "");
$col->width = 90;

$col = $dg->addColumn("کارشناس", "fullname", "");

$col = $dg->addColumn("تاریخ ارسال", "RegDate", GridColumn::ColumnType_date);
$col->width = 90;

$col = $dg->addColumn("حوزه", "ScopeDesc");
$col->width = 70;

$col = $dg->addColumn("مهلت", "EndDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("تاریخ دریافت", "DoneDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("توضیحات دریافتی", "DoneDesc");
$col->width = 150;
$col->ellipsis = 50;

$col = $dg->addColumn('', '', 'string');
$col->renderer = "PlanStartPage.SeeRender";
$col->width = 40;
$col->align = "center";

$dg->emptyTextOfHiddenColumns = true;
$dg->EnablePaging = false;
$dg->height = 150;
$dg->width = 790;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->autoExpandColumn = "fullname";
$grid = $dg->makeGrid_returnObjects();


//.....................................................
$expertStr = "";
$dt = PLN_experts::Get(" AND e.PersonID=? AND StatusDesc='RAW'", array($_SESSION["USER"]["PersonID"]));
if($dt->rowCount() > 0)
{
	$dt = $dt->fetchAll();
	foreach($dt as $row)
	{
		$expertStr .= "<div class=arrow_left style=width:16px;height:16px;float:right></div>" ."طرح شماره ".
			"<a href=javascript:void(1); onclick='framework.OpenPage
				(\"../plan/plan/PlanInfo.php\", \"جداول اطلاعاتی طرح\",{PlanID : " . $row["PlanID"] . "});'>[ " . 
			$row["PlanID"] . " ]</a> جهت کارشناسی " . $row["ScopeDesc"] . " برای شما ارسال شده است و مهلت کارشناسی " .
			DateModules::miladi_to_shamsi($row["EndDate"]) . " می باشد." . "<br>";
	}
	$expertStr .= "<br>";
}

?>
<script>

PlanStartPage.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function PlanStartPage(){
	
	this.grid = <?= $grid ?>;
	this.grid.on("itemdblclick", function(view, record){
		framework.OpenPage("../plan/plan/PlanInfo.php", "جداول اطلاعاتی طرح", 
		{PlanID : record.data.PlanID});
	});	
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.StatusDesc == "RAW")
			return "pinkRow";
		return "";
	}	
	
	this.grid.render(this.get("div_grid"));
}

PlanStartPage.SeeRender = function(value, p, record){
	
	if(record.data.StatusDesc == "RAW")
		return;
	return "<div  title='' class='tick' onclick='PlanStartPageObject.SeeExpert();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

PlanStartPageObject = new PlanStartPage();

PlanStartPage.prototype.SeeExpert = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(this.grid, {msg:'در حال تایید...'});
	mask.show();

	Ext.Ajax.request({
		url : this.address_prefix + "plan/plan.data.php?task=SeeExpert",
		method : "POST",
		params : {
			RowID : record.data.RowID
		},

		success : function(response){
			var result = Ext.decode(response.responseText);
			mask.hide();
			PlanStartPageObject.grid.getStore().load();
		}
	});
}
</script>
<div style="line-height: 18px" >
	<?= $expertStr ?>
</div>
<center>
	
	<div id="div_grid"></div>
</center>