<?php

include_once('enpayment.php');
$resNum =1000;
$redirectUrl ="test.php";
$amount =1000;


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

echo $dataToSign;
$fm = fopen("msg.txt", "w");
fwrite($fm, $dataToSign);
fclose($fm);

$fs = fopen("signed.txt", "w");
fwrite($fs, "test");
fclose($fs);

//
openssl_pkcs7_sign(realpath("msg.txt"), realpath("signed.txt"), "d:/webserver/rtfund/portal/epayment/test.pem",
    array("d:/webserver/rtfund/portal/epayment/test.pem", "73012051"),
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

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">  
<link href="Styles/Mystyle.css" rel="stylesheet" type="text/css" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>

    <form id="form1" action="https://pna.shaparak.ir/CardServices/tokenController" method="post">

           <label>Token:</label><input type="text" id="token" name="token" value="<?php echo $generateSignedPurchaseToken ?>" />
         
           <label>language:</label><input type="text" id="language" name="language" value="fa" size="5px"/>
            
           <input type="submit" id="btnPymnt" value="payment" name="btnPymnt"/>
    </form>
</body>