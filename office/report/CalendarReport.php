<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.10
//-----------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "../../framework/management/framework.data.php?task=SelectCalendarEvents", "grid_div");

$dg->addColumn("", "EventID", "", true);
$dg->addColumn("", "IsAllDay", "", true);

$col = $dg->addColumn("عنوان", "EventTitle", "");
$col->width = 120;

$col = $dg->addColumn("شرح", "EventDesc", "");
$col->ellipsis = 60;

$col = $dg->addColumn("شروع", "StartDate", GridColumn::ColumnType_date);
$col->width = 90;
$col->align = "center";

$col = $dg->addColumn("پایان", "EndDate", GridColumn::ColumnType_date);
$col->width = 90;
$col->align = "center";

$col = $dg->addColumn("از ساعت", "FromTime");
$col->width = 60;

$col = $dg->addColumn("تا ساعت", "ToTime");
$col->width = 60;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 500;
$dg->width = 800;
$dg->title = "گزارش رویدادهای تقویم";
$dg->DefaultSortField = "StartDate";
$dg->autoExpandColumn = "EventDesc";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
	
CalendarReport.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function CalendarReport(){
	
	this.grid = <?= $grid ?>;
	
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsAllDay == "YES")
			return "violetRow";
		return "";
	}	
	this.grid.render(this.get("DivGrid"));
}

CalendarReportObject = new CalendarReport();

</script>
<center>
	<br>
	<form id="MainForm">
		<div id="MainPanel"></div>
	</form>
	<br>
	<div id="DivGrid"></div>
</center>