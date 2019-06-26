<?php
//-----------------------------
//	Date		: 1397.11
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "meeting.data.php?task=SelectMyMeetingAgendas", "grid_div");

$dg->addColumn("", "MeetingID", "", true);
$dg->addColumn("", "StatusID", "", true);
$dg->addColumn("", "MeetingType", "", true);
$dg->addColumn("", "EndTime", "", true);
$dg->addColumn("", "IsPresent", "", true);
$dg->addColumn("", "IsSign", "", true);

$col = $dg->addColumn("نوع جلسه", "MeetingTypeDesc");
$col->width = 120;

$col = $dg->addColumn("شماره جلسه", "MeetingNo", ""); 
$col->width = 100;

$col = $dg->addColumn("تاریخ جلسه", "MeetingDate", GridColumn::ColumnType_date);
$col->width = 100;

$col = $dg->addColumn("زمان جلسه", "StartTime");
$col->renderer = "function(v,p,r){return v + ' - ' + r.data.EndTime;}";
$col->width = 120;

$col = $dg->addColumn("مکان جلسه", "place");

$col = $dg->addColumn('اطلاعات', '', 'string');
$col->renderer = "MyMeetingAgenda.OperationRender";
$col->width = 120;
$col->align = "center";

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 450;
$dg->width = 750;
$dg->title = "دعوتنامه جلسات";
$dg->DefaultSortField = "MeetingDate";
$dg->autoExpandColumn = "place";
$grid = $dg->makeGrid_returnObjects();

?>
<center><br><div id="DivGrid"></div></center>
<script>

MyMeetingAgenda.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	MenuID : "<?= $_POST["MenuID"] ?>",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function MyMeetingAgenda(){
	
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("DivGrid"));
}
		
MyMeetingAgenda.prototype.MeetingInfo = function(){
	
	record = this.grid.getSelectionModel().getLastSelected();
	window.open(this.address_prefix + "PrintAgendas.php?MeetingID=" + record.data.MeetingID);
}

MyMeetingAgenda.OperationRender = function(value, p, record){
	
	return '<div class="x-btn x-btn-default-small x-icon-text-right x-btn-icon-text-right x-btn-default-small-icon-text-right">'+
  		'<button type="button" onclick=MyMeetingAgendaObject.MeetingInfo() class="x-btn-center">'+
		'<span class="x-btn-inner"">مشاهده دعوتنامه</span>'+
		'<span class="x-btn-icon info"></span></button></div>';
}

MyMeetingAgendaObject = new MyMeetingAgenda();

</script>