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

	if ($amount > 0) {
		$result = "";
		$error = false;
		$dt = PdoDataAccess::runquery("select * from LON_BackPays where PayRefNo=?", array($_POST['RefNum']));
		if(count($dt) == 0)
		{
			$RequestID = $_POST["ResNum"];

			$obj = new LON_BackPays();
			$obj->RequestID = $RequestID;
			$obj->PayType = 4;
			$obj->PayAmount = $amount;
			$obj->PayDate = PDONOW;
			$obj->PayRefNo = $_POST['RefNum'];

			$pdo = PdoDataAccess::getPdoObject();
			$pdo->beginTransaction();

			if(!$obj->Add($pdo))
				$error = true;
			if(!$error)
			{
				$CostID = 253; // bank
				$TafsiliID = 2132; // eghtesadnovin
				$TafsiliID2 = 1; // jari
				$CenterAccount = false;
				$BranchID = "";
				$FirstCostID = "";
				$SecondCostID = "";
				
				$ReqObj = new LON_requests($obj->RequestID);
				if($ReqObj->BranchID != "3") // این درگاه مخصوص دانشگاه است و وام های شعبه های دیگر باید با حساب مرکز ثبت شوند
				{
					$CenterAccount = true;
					$BranchID = "3";
					$FirstCostID = 205;
					$SecondCostID = 17;
				}
				
				$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
				if($PersonObj->IsSupporter == "YES")
					$res = RegisterSHRTFUNDCustomerPayDoc(null, $obj, $CostID, $TafsiliID, $TafsiliID2, 
							$CenterAccount, $BranchID, $FirstCostID, $SecondCostID, $pdo);
				else
					$res = RegisterCustomerPayDoc(null, $obj, $CostID, $TafsiliID, $TafsiliID2, 
							$CenterAccount, $BranchID, $FirstCostID, $SecondCostID, $pdo);

				if(!$res)
					$error = true;
				
			}
		
			if($error)
			{
				$pdo->rollBack();
				$result = "<br> عملیات پرداخت قسط در نرم افزار صندوق به درستی ثبت نگردید. " . 
						"<br> وجه کسر شده حداکثر تا 72 ساعت به حساب شما برگشت خواهد شد." ;
			}
			else
			{
				$pdo->commit();
				
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

				$payment->logout($login);
				$result = "پرداخت الكترونيكي شما به درستي انجام گرفت. شماره رسيد بانكي زير براي شما صادر گرديده است: </p>";
				$result .= "<table width=80% align=center border=1 cellspacing=0 cellpadding=5 dir=rtl>
					<tr>
						<td>مبلغ پرداختي: </td>
						<td><b>" . number_format($amount) . "</b> ریال  </td>
					</tr>
					<tr>
						<td> شماره پیگیری: </td>
						<td dir=ltr align=right><b>" . $_POST['RefNum'] . "</b></td>
					</tr>
				</table>";
			}
		}		
	}	
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
