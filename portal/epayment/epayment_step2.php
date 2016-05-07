<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	93.06
//-------------------------
require_once '../header.inc.php';
require_once('../../libtejarat/nusoap.php');
require_once '../../loan/request/request.class.php';
require_once '../../accounting/docs/import.data.php';

ini_set("display_errors", "Off");

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
if (!isset($_POST["resultCode"])) {
	$result = "تراکنش بی نتیجه";
}
else if ($_POST["resultCode"] == 100) {

	$ns = 'http://tejarat/paymentGateway/definitions';
	$wsdl2 = "https://kica.shaparak.ir/epay/services";
	$soapclient = new nusoap_client($wsdl2, '', 'lb1.um.ac.ir', '81');
	$parameters = array(
		'merchantId' => $MerchantID,
		'referenceNumber' => $_POST['referenceId']);

	echo $totalAmount = $soapclient->call('verifyRequest', $parameters, $ns);
	//	echo $soapclient->request;
	//	echo '<br><br>';
	//	echo $soapclient->response;
	//	echo '<br><br>';

	if ($soapclient->fault) {
		echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>';
		print_r($totalAmount);
		echo '</pre>';
	} 
	//	echo $soapclient->request;
	//	echo '<br><br>';
	//	echo $soapclient->response;
	//	echo '<br><br>';
	//	print_r($totalAmount);

	$x = $soapclient->response;
	//	echo $x;

	$starttag = stripos($x, '<verifyresponse xmlns="http://tejarat/paymentGateway/definitions">');
	$endtag = stripos($x, '</verifyresponse>');

	$endofstarttag = $starttag + strlen('<verifyresponse xmlns="http://tejarat/paymentGateway/definitions">') - 1;
	$lenresponse = $endtag - $endofstarttag - 1;

	$result = substr($x, $endofstarttag + 1, $lenresponse);
	$totalAmount = $result;
	$totalAmount = intval($totalAmount);

	if ($totalAmount > 0) {
		$result = "پرداخت الكترونيكي شما به درستي انجام گرفت. شماره رسيد بانكي زير براي شما صادر گرديده است: </p>";
		$result .= "<table width=80% align=center border=1 cellspacing=0 cellpadding=5 dir=rtl>
			<tr>
				<td>مبلغ پرداختي: </td>
				<td><b>" . number_format($totalAmount) . "</b> ریال  </td>
			</tr>
			<tr>
				<td> شماره پیگیری: </td>
				<td dir=ltr align=right><b>" . $_POST['referenceId'] . "</b></td>
			</tr>
		</table>";

		$PartID = $_REQUEST["paymentId"];
	
		$obj = new LON_BackPays();
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
			$pdo->rollBack();
			$result .= "<br> عملیات پرداخت قسط د