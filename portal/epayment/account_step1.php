<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	93.06
//-------------------------
require_once("../header.inc.php");
require_once('enpayment.php');


// -------------- باید مجدد بازنگری شود ---------------------




if(empty($_SESSION["USER"]["PersonID"]))
{
	echo "دسترسی نامعتبر است.";
	die();
}

$resNum = $_REQUEST["RequestID"];
$redirectUrl = "http://portal.krrtf.ir/portal/epayment/epayment_step2.php";
$amount = $_REQUEST["amount"];
$_SESSION["USER"]["SHAPARAK_AMOUNT"] = $amount;

/////////////////state1

$payment = new Payment();
$login = $payment->login(username,password);
$login = $login['return'];

$params['resNum'] = $resNum;
$params['amount'] = $amount;
$params['redirectUrl'] = $redirectUrl;

$getPurchaseParamsToSign = $payment-> getPurchaseParamsToSign($params);

$getPurchaseParamsToSign =  $getPurchaseParamsToSign['return'];
$uniqueId =  $getPurchaseParamsToSign['uniqueId'];
$dataToSign = $getPurchaseParamsToSign['dataToSign'];

///////////////////////state2

$fm = fopen("msg.txt", "w");
fwrite($fm, $dataToSign);
fclose($fm);

$fs = fopen("signed.txt", "w");
fwrite($fs, "test");
fclose($fs);

openssl_pkcs7_sign(realpath("msg.txt"), realpath("signed.txt"), 
	 'file://'.realpath("/home/krrtfir/public_html/portal/epayment/certificate/Sandogh.pem"),
    array('file://'.realpath("/home/krrtfir/public_html/portal/epayment/certificate/Sandogh.pem"), "73012051"),
    array(),PKCS7_NOSIGS
    );

$data = file_get_contents("signed.txt");

$parts = explode("\n\n", $data, 2);
$string = $parts[1];

$parts1 = explode("\n\n", $string, 2);
$signature = $parts1[0];

///////////////////////state3

$login = $payment->login(username,password);
$login = $login['return'];

$params['signature'] = $signature;
$params['login'] = $login;	
$params['resNum']= $resNum;
$params['amount']= $amount ;
$params['uniqueId']= $uniqueId ;
$params['redirectUrl'] = $redirectUrl ;

$generateSignedPurchaseToken = $payment-> generateSignedPurchaseToken($params);
$generateSignedPurchaseToken = $generateSignedPurchaseToken['return'];
$generateSignedPurchaseToken = $generateSignedPurchaseToken['token']; 

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">  
<link href="Styles/Mystyle.css" rel="stylesheet" type="text/css" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>
    <form id="form1" action="https://pna.shaparak.ir/CardServices/tokenController" method="post">
           <label>Token:</label><input type="text" id="token" name="token" value="<?php echo $generateSignedPurchaseToken ?>" />
           <label>language:</label><input type="text" id="language" name="language" value="fa" size="5px"/>
    </form>
	<script>document.getElementById('form1').submit();</script>
</body>