<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	93.06
//-------------------------
require_once '../header.inc.php';
require_once getenv("DOCUMENT_ROOT") . '/accounting/docs/import.data.php';
require_once getenv("DOCUMENT_ROOT") . '/accounting/baseinfo/baseinfo.class.php';
require_once 'nusoap.php';

ini_set("display_errors", "On");

$authority = $_REQUEST['au'];
$status = $_REQUEST['SwRespCode'];

function RegDoc($PayObj){
	
	$BranchID = 3;
	$BaseCostID = $PayObj->CostID;
	$PersonTafsili = FindTafsiliID($PayObj->PersonID, TAFTYPE_PERSONS);
	
	$CostID = COSTID_Bank;
	$TafsiliID = 3049; // ayande
	$TafsiliID2 = 3052; // kootahmodat 
	
	return RegisterInOutAccountDoc($BranchID, $PayObj->amount, 1, "پرداخت الکترونیک به شماره رهگیری " . 
			$PayObj->PayRefNo,
			$BaseCostID, TAFTYPE_PERSONS, $PersonTafsili, "", "", 
			$CostID, TAFTYPE_BANKS, $TafsiliID, TAFTYPE_ACCOUNTS, $TafsiliID2, true);
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
			
			$PayObj->PayRefNo = $invoiceNumber;
			$PayObj->StatusCode = $status;
			$PayObj->Edit();
			
			if ($status == 0) {
				// this is a succcessfull payment	
				$DocRegResult = RegDoc($PayObj);
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