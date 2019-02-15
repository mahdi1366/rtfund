<?php
//-----------------------------
//	Date		: 97.11
//-----------------------------
require_once '../header.inc.php';
require_once './meeting.class.php';
require_once inc_reportGenerator;

$MeetingID = !empty($_REQUEST["MeetingID"]) ? (int)$_REQUEST["MeetingID"] : "";
if(empty($MeetingID))
	die();

$MeetingObj = new MTG_meetings($MeetingID);

$rpt = new ReportGenerator();
$rpt->mysql_resource = MTG_MeetingAgendas::Get("and ma.MeetingID=?", array($MeetingID));

$rpt->addColumn("دستور جلسه", "title");
$col = $rpt->addColumn("ارائه دهنده", "fullname");
$col->align = "center";
$col = $rpt->addColumn("زمان", "PresentTime");
$col->align = "center";
?>
<html>
	<meta content='text/html; charset=utf-8' http-equiv='Content-Type'/>
	<link rel="stylesheet" type="text/css" href="/generalUI/fonts/fonts.css" />
	<style media="print">
		.noPrint {display:none;}
	</style>
	<style>
		.header td{background-color: #cccccc; font-weight: bold;size: 12px;}
		td { font-family: Nazanin; font-size: 12pt; line-height: 25px; padding: 3px;}
		table { page-break-inside:auto }
		tr    { /*page-break-inside:avoid;*/ page-break-after:auto }
		thead { display:table-header-group }
		tfoot { display:table-footer-group }
		.info {font-weight: bold; text-decoration: underline}
	</style>	
	<body dir="rtl">
		<center>
			<div>
			<table style="width:19cm;height:100%">
				<thead>
				<tr style="height:150px">
					<td align="center" style="width:200px;">
						<img  src="/framework/icons/logo.jpg" style="width:120px">
					</td>
					<td align="center" style="font-family: titr;font-size: 14px;">
						<b>به نام خداوند جان و خرد</b>
					</td>
					<td style="width:200px;line-height: 25px;">
					شماره جلسه:<b> <?= $MeetingObj->MeetingID ?></b>
					<br>تاریخ جلسه: <b><?= DateModules::miladi_to_shamsi($MeetingObj->MeetingDate) ?></b>
					</td>
				</tr>
				</thead>
				<tr>
					<td colspan="3" style="padding-right:50px;padding-left: 50px;vertical-align: top;
						text-align: justify;text-justify: inter-word;">
						<br><span style="font-family: titr">
						اعضای محترم <?= $MeetingObj->_MeetingTypeDesc ?>
						<br>
						موضوع: دعوت‌نامه جلسه <?= $MeetingObj->MeetingID ?>
						<br><br>
						</span>
						با سلام و احترام؛
						<br>
						بدینوسیله از جنابعالی دعوت مینماید در جلسه‌ای
						که در تاریخ 
						<span class="info"><?= DateModules::miladi_to_shamsi($MeetingObj->MeetingDate) ?></span>
						ساعت <span class="info"><?= substr($MeetingObj->StartTime,0,5) ?></span>
						در محل <span class="info"><?= $MeetingObj->place ?></span>
						تشکیل خواهد شد شرکت فرمایید.
						<br>
						پیشاپیش از حضور به‌موقع شما سپاسگزارم.
						<br><br>
						<?= $rpt->generateReport() ?>
						<br><br>
						<div align="center" style="width:200px;float:left;font-weight: bold">
						با تشکر
						<br>
						رئیس <?= $MeetingObj->_MeetingTypeDesc ?>
						</div>
					</td>
				</tr>
			</table>
		</div>
			
		</center>
	</body>
</html>