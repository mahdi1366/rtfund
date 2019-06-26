<?php
//-----------------------------
//	Date		: 1397.11
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "meeting.data.php?task=SelectMyMeetings", "grid_div");

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

$col = $dg->addColumn("دبیر جلسه", "fullname");

$col = $dg->addColumn('اطلاعات', '', 'string');
$col->renderer = "MyMeeting.OperationRender";
$col->width = 100;
$col->align = "center";

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 450;
$dg->width = 750;
$dg->title = "مدیریت جلسات";
$dg->DefaultSortField = "MeetingDate";
$dg->autoExpandColumn = "fullname";
$grid = $dg->makeGrid_returnObjects();

?>
<center>
	<div id="DivGrid" style="margin:8px;width:98%"></div>
	<table style="width:750px">
		<tr>
			<td width="30px" style="padding: 2px"><div style="width: 18px; height: 18px;" class="yellowRow">&nbsp;</div></td>
			<td>ردیف های زرد رنگ جلساتی هستند که مصوبات آنها را تایید و امضاء کرده اید.</td>
		</tr>
		<tr>
			<td><div style="padding: 2px"><div style="width: 18px; height: 18px;" class="pinkRow">&nbsp;</div></td>
			<td>ردیف های قرمز رنگ جلساتی هستند که در آنها شرکت نکرده اید.</td>
		</tr>
	</table>
</center>
<script>

MyMeeting.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	MenuID : "<?= $_POST["MenuID"] ?>",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function MyMeeting(){
	
	this.grid = <?= $grid ?>;
	this.grid.on("itemdblclick", function(view, record){
		MyMeeting.MeetingInfo(record.data.MeetingID);
	});	
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsPresent == "NO")
			return "pinkRow";
		if(record.data.IsSign == "YES")
			return "yellowRow";
		return "";
	}	
	this.grid.render(this.get("DivGrid"));
}
		
MyMeeting.prototype.MeetingInfo = function(){
	
	record = this.grid.getSelectionModel().getLastSelected();
	portal.OpenPage("/meeting/MeetingInfo.php", 
		{
			MeetingID : record.data.MeetingID,
			MenuID : this.MenuID
			
		});
}

MyMeeting.OperationRender = function(value, p, record){
	
	return '<div class="x-btn x-btn-default-small x-icon-text-right x-btn-icon-text-right x-btn-default-small-icon-text-right">'+
  		'<button type="button" onclick=MyMeetingObject.MeetingInfo() class="x-btn-center">'+
		'<span class="x-btn-inner"">اطلاعات جلسه</span>'+
		'<span class="x-btn-icon info"></span></button></div>';
}

MyMeetingObject = new MyMeeting();

</script>