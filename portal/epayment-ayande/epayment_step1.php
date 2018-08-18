<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	93.06
//-------------------------
require_once("../header.inc.php");
require_once 'nusoap.php';
require_once getenv("DOCUMENT_ROOT") . '/accounting/baseinfo/baseinfo.class.php';

if(empty($_REQUEST["RequestID"]))
{
echo "دسترسی نامعتبر است.";
die();
}

$PayObj = new ACC_EPays();
$PayObj->amount = $_REQUEST["amount"];
$PayObj->PayDate = PDONOW;
$PayObj->PersonID = $_SESSION["USER"]["PersonID"];
$PayObj->RequestID = $_REQUEST["RequestID"];
$PayObj->Add();

$callbackUrl = "http://portal.krrtf.ir/portal/epayment-ayande/epayment_step2.php";
$amount = $_REQUEST["amount"];
$pin = BANK_AYANDEH_PIN;
$orderId = $PayObj->PayID;

$soapclient = new soapclient2('https://pec.shaparak.ir/pecpaymentgateway/EShopService.asmx?wsdl','wsdl');
if (!$err = $soapclient->getError())
	$soapProxy = $soapclient->getProxy();
if ( (!$soapclient) OR ($err = $soapclient->getError()) ) {
	echo BeginReport();
    echo "<center><br><br>" . 
			"سرویس بانک در حال حاضر از دسترس خارج است" . "<br><br></center>" ;
	die();
} 

$authority = 0 ;  // default authority
$status = 1 ;	// default status
$params = array(
			'pin' => $pin,
			'amount' => $amount,
			'orderId' => $orderId,
			'callbackUrl' => $callbackUrl,
			'authority' => $authority,
			'status' => $status
		  );
$sendParams = array($params) ;
$res = $soapclient->call('PinPaymentRequest', $sendParams);

$authority = $res['authority'];
$status = $res['status'];

if ( ($authority) and ($status==0) )  {
	// this is succcessfull connection
	$PayObj->authority = $authority;
	$PayObj->Edit();
	$parsURL = "https://pec.shaparak.ir/pecpaymentgateway/?au=" . $authority ;
	header("location:" . $parsURL);
	die();

} else {
	// this is unsucccessfull connection
	echo "<p dir=LTR>";
	if ($err = $soapclient->getError()) {
		echo "ERROR = $err <br /> " ;
  }
  echo "$authority <br />" ;
  echo "$status <br />" ;
  echo "$orderId <br />" ;
  echo "Couldn't get proper authority key " ;
  echo "</p>";

}
?>