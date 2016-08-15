<?php

if(isset($_POST['ok'])) 
{
	include_once('enpayment.php');

	$amount = $_POST["amount"];
	$payment = new Payment();
	$login = $payment->login(01301328,73012051);
	$login = $login['return'];

	$params['login'] = $login;
	$params['amount'] = $amount;	
	$params['token']= $_POST['token'];
	$params['RefNum']= $_POST['RefNum'];


	$VerifyTrans = $payment->tokenPurchaseVerifyTransaction($params);

	$VerifyTrans = $VerifyTrans['return'];
	$VerifyTrans = $VerifyTrans['resultTotalAmount'];

	echo $VerifyTrans;

	/*revers
	$params['login'] = $login;
	$params['mainTransactionRefNum']= $_POST['RefNum'] ;
	$params['reverseTransactionResNum'] = "1234567890";
	$params['amount'] = $amount ;	


	$reverseTrans = $payment->ReverseTrans($params);

	$reverseTrans  = $reverseTrans['return'];
	$reverseTrans  = $reverseTrans['refNum'];

	echo $reverseTrans ;
	*/
	$logout = $payment->logout($login);

}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="Styles/Mystyle.css" rel="stylesheet" type="text/css" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>
<div>
	<form method="post">
		<input name="amount"></input>
		<input type="submit" value="ok" name="ok"></input>
	</form>
</div>
</body>
</html>



