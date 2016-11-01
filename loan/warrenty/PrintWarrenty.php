<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.06
//-----------------------------

require_once '../header.inc.php';
require_once 'request.class.php';
require_once 'config.inc.php';
require_once inc_CurrencyModule;

$ReqObj = new WAR_requests($_REQUEST["RequestID"]);
if($ReqObj->RequestID == "")
	die();

if($ReqObj->StatusID != WAR_STEPID_CONFIRM && empty($_POST["ReadOnly"]))
{
	echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" >';
	echo "<br><br><h2><center> فقط زمانی قادر به چاپ ضمانتنامه می باشید که تایید نهایی شده باشد </center></h2>";
	die();
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
			font-size: 14px;
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
						نظر به اینکه به موجب نامه شماره <?= $ReqObj->LetterNo ?>
						مورخ <?= DateModules::miladi_to_shamsi($ReqObj->LetterDate) ?>
						 <?= $ReqObj->organization ?>
						و بر طبق قرارداد منعقده بین <?= $ReqObj->organization ?>
						و <?= $ReqObj->_fullname ?>
						قرار است مبلغ <?= number_format($ReqObj->amount) ?>ریال 
						( <?= CurrencyModulesclass::CurrencyToString($ReqObj->amount) ?> ریال )
						بعنوان <?= $ReqObj->_TypeDesc ?>
						به <?= $ReqObj->_fullname ?>
						پرداخت گردد. این صندوق متعهد است هر مبلغی را تا میزان 
						<?= number_format($ReqObj->amount) ?>ریال 
						( <?= CurrencyModulesclass::CurrencyToString($ReqObj->amount) ?> ریال )
						که از طرف سازمان <?= $ReqObj->organization ?>
						مطالبه شود به محض دریافت اولین تقاضانامه کتبی و بدون اینکه احتیاج به صدور 
						اظهارنامه یا اقدامی از مجرای اداری، قضایی ویا مقام دیگری و یا ذکر علتی داشته باشد،
						مبلغ مورد درخواست <?= $ReqObj->_fullname ?>
						را در وجه یا حواله کرد <?= $ReqObj->organization ?>
						بپردازد.
						<br>
						این ضمانت نامه تا آخر ساعت اداری روز 
						<?= DateModules::miladi_to_shamsi($ReqObj->EndDate) ?>
						( <?= DateModules::DateToString(DateModules::miladi_to_shamsi($ReqObj->EndDate)) ?>)
							معتبر بوده و بنا به درخواست <?= $ReqObj->organization ?>
							برای مدتی که درخواست شود قابل تمدید خواهد بود و در صورتی که صندوق نتواند
							و یا نخواهد مدت ضمانت نامه را تمدید نماید و یا 
							<?= $ReqObj->_fullname ?>
							موجبات تمدید را قبل از انقضای مدت فوق نزد صندوق فراهم نسازد و صندوق را حاضر به 
							تمدید ننماید، صندوق در اینصورت متعهد است بدون اینکه احتیاج به مطالبه مجدد باشد
							مبلغ مرقوم فوق را در وجه یا حواله کرد 
							<?= $ReqObj->organization ?>
							پرداخت کند.
						</div>
						<br><br><br><br><br><br><br>
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