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
	//print_r(ExceptionHandler::PopAllExceptions());
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
			width:800px;
			height:1100px;
			background-image: url('prints/bg.jpg');
			background-size: 800px 1100px;
		}
		@media print {
			.page{
				background-image: none;
			}
		}
			
		.title {
			width:300px;
			font-family: titr; font-size: 18px;
			position: absolute;
			top : 75px; left : 225px;
		}
		.date {
			width:300px;
			font-family: titr; font-size: 16px;
			position: absolute;
			top : 130px; left : 215px;
		} 
		.number {
			width:300px;
			font-family: titr; font-size: 16px;
			position: absolute;
			top : 163px; left : 186px;
		}
		.context {
			text-align: justify;
			padding-left: 60px;
			padding-right: 60px;
			font-family: nazanin; 
			font-size: 18px;
		}
		.sign {
			width:300px; 
			float:left; 
			font-family: titr;
			margin-left: 20px;
		}
		</style>
	</head>
	<body dir="rtl">
	<center>
		<div class=page>
			<table style="width:95%; height:260mm">
				<tr style="height: 255px">
					<td>
						<br>
						<div class='title'><?= $ReqObj->_TypeDesc ?></div>
						<br>
						<div class='date'><?= DateModules::miladi_to_shamsi($ReqObj->StartDate) ?></div>
						<br>
						<div class='number'><?= $ReqObj->RequestID ?></div>
					</td>
				</tr>
				<tbody>
				<tr>
					<td align="center" style="vertical-align: top">
						
						<div class='context' >
						<?= $content ?>
						</div>
						<br><br><br>
						<div class='sign' align="center">
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
						
					</td>
				</tr>
				</tfoot>
			</table>
		</div>
	</center>
	</body>
</html>