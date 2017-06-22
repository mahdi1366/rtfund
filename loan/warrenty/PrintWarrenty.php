<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.06
//-----------------------------

require_once '../header.inc.php';
require_once 'request.class.php';
require_once inc_CurrencyModule;

$ReqObj = new WAR_requests($_REQUEST["RequestID"]);
if($ReqObj->RequestID == "")
	die();

if($ReqObj->StatusID != WAR_STEPID_CONFIRM && empty($_POST["ReadOnly"]))
{
	/*echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" >';
	echo "<br><br><h2><center> فقط زمانی قادر به چاپ ضمانتنامه می باشید که تایید نهایی شده باشد </center></h2>";
	die();*/
}

$dt = WAR_requests::SelectAll("r.RequestID=?" , array($ReqObj->RequestID));
if($_SESSION["USER"]["UserName"] == "admin")
{
	//echo PdoDataAccess::GetLatestQueryString();
	print_r(ExceptionHandler::PopAllExceptions());
}
$record = $dt->fetch();
$record["LetterDate"] = DateModules::miladi_to_shamsi($record["LetterDate"]);
$record["StartDate"] = DateModules::miladi_to_shamsi($record["StartDate"]);
$record["EndDate"] = DateModules::miladi_to_shamsi($record["EndDate"]);
$record["amount_char"] = CurrencyModulesclass::CurrencyToString($record["amount"]);
$record["amount"] = number_format($record["amount"]);
$record["duration_month"] = DateModules::GetDiffInMonth($record["StartDate"], $record["EndDate"]);
$record["EndDate_char"] = DateModules::DateToString(DateModules::miladi_to_shamsi($record["EndDate"]));

$content = file_get_contents("prints/" . $ReqObj->TypeID . ".html");
$contentArr = explode("#", $content);
$content = "";
for ($i = 0; $i < count($contentArr); $i++) {
	if ($i % 2 == 0) 
	{
		$content .= $contentArr[$i];
		continue;
	}
		
	$content .= $record[ $contentArr[$i] ];
}

?>
<html>
	<head>
		<META http-equiv=Content-Type content="text/html; charset=UTF-8" >
		<link rel="stylesheet" type="text/css" href="/generalUI/fonts/fonts.css" /></head>
		<style>
		@media print {
			.pageBreak {page-break-before:always;height:1px;}
		}
		.page {
			width: 180mm;
			height: 260mm;
			border : 6px double black;
			margin : 1cm;
		}
		td {
			font-family: nazanin;
			font-size: 16px;
		}
		.address td{
			font-size: 11px;
		}
		</style>
	</head>
	<body dir="rtl">
	<center>
		<div class=page>
			<table style="width:95%; height:260mm">
				<tr>
					<td><br>
						<div style="width:150px; float:left" align="center">
						شماره : <?= $ReqObj->RequestID ?>
						<br> تاریخ : <?= DateModules::miladi_to_shamsi($ReqObj->StartDate) ?>
						</div>
					</td>
				</tr>
				<tbody>
				<tr>
					<td align="center" style="vertical-align: top">
						<span style="font-family: titr; font-size: 16px">
							بسمه تعالی
							<br>
							<?= $ReqObj->_TypeDesc ?>
							<br>
							**************************
							<br>
							<font style="font-size: 30px">
								غیـــــــر قابل انتـــقال
							</font>
							<br><br>
						</span>
						<div style="text-align: justify;">
						<?= $content ?>
						</div>
						<br><br><br><br><br>
						<div style="width:300px; float:left; font-family: titr" align="center">
							صندوق پژوهش و فناوری خراسان رضوی
							<br>
							مدیر عامل 
							<br>
							رسول عبدالهی
						</div>
					</td>
				</tr>
				</tbody>
				<tfoot>
				<tr style="height:150px;">
					<td colspan="2">
						<div style="width:200px; float:right;">
							<table cellpadding="0" cellspacing="0" style="width: 100%" class="address">
								<tr>
									<td colspan="2">
										نشانی : مشهد، کیلومتر 12 بزرگراه قوچان، مقابل شیر پگاه، پارک علم و فناوری خراسان
									</td>
								</tr>
								<tr>
									<td>تلفن : </td>
									<td align="left">051-35003441</td>
								</tr>
								<tr>
									<td colspan="2">
									شعبه: پردیس دانشگاه فردوسی مشهد، درب غربی
									</td>
								</tr>
								<tr>
									<td>تلفن :</td>
									<td align="left">051-38837392</td>
								</tr>
								<tr>
									<td>وب سایت :</td>
									<td align="left">www.krrtf.ir</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
				</tfoot>
			</table>
		</div>
	</center>
	</body>
</html>