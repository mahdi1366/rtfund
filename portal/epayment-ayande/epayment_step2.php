<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	93.06
//-------------------------
require_once '../header.inc.php';
require_once getenv("DOCUMENT_ROOT") . '/loan/request/request.class.php';
require_once getenv("DOCUMENT_ROOT") . '/accounting/docs/import.data.php';
require_once getenv("DOCUMENT_ROOT") . '/accounting/baseinfo/baseinfo.class.php';
require_once 'nusoap.php';

ini_set("display_errors", "On");

$authority = $_REQUEST['au'];
$status = $_REQUEST['SwRespCode'];

function RegDoc($RequestID, $amount,  $PayRefNo){
	
	$dt = PdoDataAccess::runquery("select * from LON_BackPays where PayRefNo=?", array($PayRefNo));
	if(count($dt) > 0)
	{
		ExceptionHandler::PushException("<br> کد رهگیری قبلا ثبت شده است");
		return false;
	}
	
	$obj = new LON_BackPays();
	$obj->RequestID = $RequestID;
	$obj->PayType = 4;
	$obj->PayAmount = $amount;
	$obj->PayDate = PDONOW;
	$obj->PayRefNo = $PayRefNo;

	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();

	if(!$obj->Add($pdo))
		return false;

	$CostID = 253; // bank
	$TafsiliID = 3049; // ayande
	$TafsiliID2 = 3046; // jari
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
	{
		$pdo->rollBack();
		return false;
	}

	$pdo->commit();
	return true;
}

$dt = PdoDataAccess::runquery("select * from ACC_EPays where authority=?", array($authority));
if(count($dt) == 0)
{
	$result = "خطا در انتقال پارامترهای بانک";
}
else
{
	$PayObj = new ACC_EPays($dt[0]["PayID"]);
	if ($status == 0) 
	{
		$soapclient = new soapclient2('https://pec.shaparak.ir/pecpaymentgateway/EShopService.asmx?wsdl','wsdl');
		if ( (!$soapclient) OR ($err = $soapclient->getError()) ) 
		{
			// this is unsucccessfull connection
			$result = "خطا در اتصال به بانک";
		} 
		else 
		{
			$invoiceNumber = 0;
			$status = 1 ;   // default status
			$params = array(
					'pin' => BANK_AYANDEH_PIN,
					'authority' => $authority,
					'status' => $status ,
					'invoiceNumber' => $invoiceNumber ) ; // to see if we can change it
			$sendParams = array($params) ;
			$res = $soapclient->call('PaymentEnquiry', $sendParams);
			$status = $res["status"];
			$invoiceNumber = $res["invoiceNumber"];
			
			$PayObj->StatusCode = $status;
			$PayObj->Edit();
			
			if ($status == 0) {
				// this is a succcessfull payment	
				$DocRegResult = RegDoc($PayObj->RequestID, $PayObj->amount, $invoiceNumber);
				if(!$DocRegResult)
				{
					$PayObj->error = json_encode(ExceptionHandler::PopAllExceptions());
					$PayObj->Edit();
				}
				
				$result = "پرداخت الكترونيكي شما به درستي انجام گرفت. شماره رسيد بانكي زير براي شما صادر گرديده است: </p>";
				$result .= "<table width=80% align=center border=1 cellspacing=0 cellpadding=5 dir=rtl>
					<tr>
						<td>مبلغ پرداختي: </td>
						<td><b>" . number_format($PayObj->amount) . "</b> ریال  </td>
					</tr>
					<tr>
						<td> شماره پیگیری: </td>
						<td dir=ltr align=right><b>" . $invoiceNumber . "</b></td>
					</tr>
				</table>";				

			} else {

				$result = "<br> عملیات پرداخت قسط به درستی ثبت نگردیده است. " . 
					"<br> وجه کسر شده حداکثر تا 72 ساعت به حساب شما برگشت خواهد شد." ;
			}
		}
	}
	else
	{
		$result = "<br> عملیات پرداخت در بانک درست انجام نشده است" ;
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