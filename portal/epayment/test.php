<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	93.06
//-------------------------
require_once '../header.inc.php';
require_once('../../libtejarat/nusoap.php');
require_once '../../loan/request/request.class.php';
require_once '../../accounting/docs/import.data.php';

ini_set("display_errors", "On");

function ShowStatus($ErrorCode) {
	switch ($ErrorCode) {
		case 100:
			$ErrorDesc = 'موفقیت تراکنش';
			break;
		case 110:
			$ErrorDesc = 'انصراف دارنده کارت';
			break;
			;
		case 120:
			$ErrorDesc = 'موجودی حساب کافی نیست';
			break;
			;
		case 121:
			$ErrorDesc = 'مبلغ مورد نظر اشتباه است';
			break;
			;
		case 130:
			$ErrorDesc = 'اطلاعات کارت اشتباه است';
			break;
		case 131:
			$ErrorDesc = 'رمز کارت اشتباه است';
			break;
			;
		case 132:
			$ErrorDesc = 'کارت مسدود شده است';
			break;
			;
		case 133:
			$ErrorDesc = 'کارت منقضی شده است';
			break;
		case 140:
			$ErrorDesc = 'زمان مورد نظر به پایان رسیده است';
			break;
		case 150:
			$ErrorDesc = 'خطای داخلی بانک';
			break;
		case 160:
			$ErrorDesc = 'خطای در اطلاعات CVV2 یا ExpDate';
			break;
		case 166:
			$ErrorDesc = 'بانک صادر کننده کارت شما مجوز انجام تراکنش را نداده است';
			break;
		default :
			$ErrorDesc = " خطای داخلی بانک";
			break;
	}
	return $ErrorDesc;
}

$result = "";
if (!isset($_REQUEST["resultCode"])) {
	$result = "تراکنش بی نتیجه";
}
else if ($_REQUEST["resultCode"] == 100) {

	$PartID = $_REQUEST["paymentId"];
	$totalAmount = 1000;

	if ($totalAmount > 0) {
		$result = "پرداخت الكترونيكي شما به درستي انجام گرفت. شماره رسيد بانكي زير براي شما صادر گرديده است: </p>";
		$result .= "<table width=80% align=center border=1 cellspacing=0 cellpadding=5 dir=rtl>
			<tr>
				<td>مبلغ پرداختي: </td>
				<td><b>" . number_format($totalAmount) . "</b> ریال  </td>
			</tr>
			<tr>
				<td> شماره پیگیری: </td>
				<td dir=ltr align=right><b>" . $_REQUEST['referenceId'] . "</b></td>
			</tr>
		</table>";

		$obj = new LON_pays();
		$obj->PartID = $PartID;
		$obj->PayType = 4;
		$obj->PayAmount = $totalAmount;
		$obj->PayDate = PDONOW;
		$obj->PayRefNo = $_REQUEST['referenceId'];
		
		$pdo = PdoDataAccess::getPdoObject();
		$pdo->beginTransaction();

		$error = false;
		if(!$obj->AddPay($pdo))
			$error = true;
		if(!$error)
			if(!RegisterCustomerPayDoc($obj, $pdo))
				$error = true;
		if($error)
		{
			print_r(ExceptionHandler::PopAllExceptions());
			$pdo->rollBack();
			$result .= "<br> عملیات پرداخت قسط در نرم افزار صندوق به درستی ثبت نگردید. " . 
					"<br> جهت اعمال آن با صندوق تماس بگیرید." ;
		}
		else
			$pdo->commit();
	}
	else
		ShowStatus($totalAmount);
}
else 
{
	$result = ShowStatus($_REQUEST["resultCode"]) . "<br>";
	
	if (isset($_REQUEST['referenceId'])) {
		$result .= '<p align=center><font face=tahoma size=3>';
		$result .= 'کد پیگیری:' . $_REQUEST['referenceId'];
		$result .= '</font></p>';
	}
}
?>
<html>
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>	
		<style>
			body{
				font-family: tahoma;
				font-size: 12px;
				font-weight: bold;
			}
			button{
				font-family: tahoma;
				font-size: 12px;
			}
		</style>
	</head>	
	<body dir="rtl">
	<center>
		<br>
		<br>
		<div style="width: 500px; border: 1px solid #99BBE8; background-color: #DFE9F6; padding:5px;border-radius: 4px; height: 220px;">
			<div style="background-color: white; width: 500px; height:80%">
				<br>
				<?= $result ?>
				<br>&nbsp;
			</div>
			<br><button style="" onclick="window.opener.location.reload(); window.close()">
				بازگشت به پرتال   <?= SoftwareName ?>
			</button>
			<br>&nbsp;
		</div>
	</center>
</body>
</html>