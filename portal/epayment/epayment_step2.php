<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	93.06
//-------------------------
require_once '../header.inc.php';
require_once '../../loan/request/request.class.php';
require_once '../../accounting/docs/import.data.php';

ini_set("display_errors", "On");

if($_POST['State']== 'OK') {
	include_once('enpayment.php');

	$amount = $_SESSION["USER"]["SHAPARAK_AMOUNT"];

	$payment = new Payment();

	$login = $payment->login(username,password);
	$login = $login['return'];

	$params['login'] = $login;
	$params['amount'] = $amount;	
	$params['token']= $_POST['token'];
	$params['RefNum']= $_POST['RefNum'];
	$VerifyTrans = $payment->tokenPurchaseVerifyTransaction($params);

	$VerifyTrans = $VerifyTrans['return'];
	$VerifyTrans = $VerifyTrans['resultTotalAmount'];
	
	$totalAmount = $VerifyTrans;
	if ($totalAmount > 0) {
		$result = "پرداخت الكترونيكي شما به درستي انجام گرفت. شماره رسيد بانكي زير براي شما صادر گرديده است: </p>";
		$result .= "<table width=80% align=center border=1 cellspacing=0 cellpadding=5 dir=rtl>
			<tr>
				<td>مبلغ پرداختي: </td>
				<td><b>" . number_format($totalAmount) . "</b> ریال  </td>
			</tr>
			<tr>
				<td> شماره پیگیری: </td>
				<td dir=ltr align=right><b>" . $_POST['RefNum'] . "</b></td>
			</tr>
		</table>";

		$PartID = $_POST["ResNum"];
	
		$obj = new LON_BackPays();
		$obj->PartID = $PartID;
		$obj->PayType = 4;
		$obj->PayAmount = $totalAmount;
		$obj->PayDate = PDONOW;
		$obj->PayRefNo = $_POST['RefNum'];
		
		$pdo = PdoDataAccess::getPdoObject();
		$pdo->beginTransaction();

		$error = false;
		if(!$obj->AddPay($pdo))
			$error = true;
		if(!$error)
			if(!RegisterCustomerPayDoc(null, $obj, 2132, 1, $pdo))
				$error = true;
		if($error)
		{
			$pdo->rollBack();
			$result .= "<br> عملیات پرداخت قسط در نرم افزار صندوق به درستی ثبت نگردید. " . 
					"<br> جهت اعمال آن با صندوق تماس بگیرید." ;
		}
		else
			$pdo->commit();
	}

	$payment->logout($login);
}
else 
{
	if (isset($_POST['RefNum'])) {
		$result .= "خطا در پرداخت الکترونیک";
		$result .= '<p align=center><font face=tahoma size=3>';
		$result .= 'کد پیگیری:' . $_POST['referenceId'];
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