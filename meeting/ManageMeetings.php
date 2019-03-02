<?php
//-----------------------------
//	Date		: 1397.11
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "meeting.data.php?task=SelectAllMeetings", "grid_div");

$dg->addColumn("", "MeetingID", "", true);
$dg->addColumn("", "StatusID", "", true);
$dg->addColumn("", "MeetingType", "", true);
$dg->addColumn("", "EndTime", "", true);

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

$col = $dg->addColumn("وضعیت", "StatusDesc", "");
$col->width = 100;

$col = $dg->addColumn('عملیات', '', 'string');
$col->renderer = "Meetings.OperationRender";
$col->width = 50;
$col->align = "center";

$dg->addButton("", "ایجاد جلسه جدید", "add", "function(){Meetings.OpenMeeting(0)}");

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 500;
$dg->width = 800;
$dg->title = "مدیریت جلسات";
$dg->DefaultSortField = "MeetingDate";
$dg->autoExpandColumn = "fullname";
$grid = $dg->makeGrid_returnObjects();

//----------------------------------------------

require_once 'ManageMeetings.js.php';
?>
<center><br>
	<div id="rejectedDIV"></div>
	<div id="DivGrid"></div>	
</center>