<?php

include("nusoap.php");
include_once('enpayment.conf.php');

class Payment
{
////////////////////////////////////////////////////login///////////////////////////////////////////////////
public function login($username,$password) 
	{
	
	
	    $client = new nusoap_client('https://pna.shaparak.ir/ref-payment/jax/merchantAuth?wsdl',true);

		$err = $client->getError();
		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
			exit();
		}

	$params = array('loginRequest' => array('password' => $password,'username' => $username)); // print_r($params);
	$result = $client->call('login', $params);
	
	
	if ($client->fault) {
				echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
		} else {
				$err = $client->getError();
				if ($err) {
						echo '<h2>Error</h2><pre>' . $err . '</pre>';
				} else {
						//echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
				}
		}
				
	 //print_r($result);
     return $result;
	 }
////////////////////////////////////////////////////////VerifyTrans///////////////////////////////////////////////
public function VerifyTrans($login,$RefNum) 
	{

	$client = new nusoap_client('https://pna.shaparak.ir/ref-payment/jax/merchantAuth?wsdl',true);
		
		$err = $client->getError();
		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
			exit();
		}
	
	$params = array('context' => array('data' => array('entry' => array('key' => 'SESSION_ID','value' => $login))),'verifyRequest' => array('refNumList' => array($RefNum)));
	
	// print_r($params);
	$result = $client->call('verifyTransaction', $params);
	
	if ($client->fault) {
				echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
		} else {
				$err = $client->getError();
				if ($err) {
						echo '<h2>Error</h2><pre>' . $err . '</pre>';
				} else {
						//echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
				}
		}
				
	 //print_r($result);
     return $result;

}

///////////////////////////////////////////tokenVerify//////////////////////////////////////////////////////////////

public function tokenPurchaseVerifyTransaction($params) 
	{

	$login = $params['login'];
    $amount = $params['amount'];
    $token = $params['token'];	
	$referenceNumber = $params['RefNum'] ;	
	
	$client = new nusoap_client('https://pna.shaparak.ir/ref-payment/jax/merchantAuth?wsdl',true);
		
		$err = $client->getError();
		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
			exit();
		}

		
	$params = array('context' => array('data' => array('entry' => array('key' => 'SESSION_ID','value' => $login))),
	'purchaseVerificationDto' => array('amount' => $amount,'token' => $token,'referenceNumber' => $referenceNumber));
	
	//print_r($params);
		
	$result = $client->call('tokenPurchaseVerifyTransaction', $params);
	
	if ($client->fault) {
				echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
		} else {
				$err = $client->getError();
				if ($err) {
						echo '<h2>Error</h2><pre>' . $err . '</pre>';
				} else {
						//echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
				}
		}
				
	 //print_r($result);
     return $result;

}
////////////////////////////////////////////////////getPurchaseParamsToSign/////////////////////////////////////////////////////

public function getPurchaseParamsToSign($params) 
	{

	
$resNum = $params['resNum'];
$amount = $params['amount'];
$redirectUrl = $params['redirectUrl'] ;	

	   $client = new nusoap_client('https://pna.shaparak.ir/ref-payment/jax/merchantAuth?wsdl',true);
		
		$err = $client->getError();
		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			echo '<h2>Debug</h2><pre>' .htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
			exit();
		}

		
		
	$params = array('resNum' => $resNum,'amount' => $amount,'amountSpecified' => true,'redirectUrl' => $redirectUrl);
	
	//print_r($params);
	
	
	$result = $client->call('getPurchaseParamsToSign', $params);
	
	if ($client->fault) {
				echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
		} else {
				$err = $client->getError();
				if ($err) {
						echo '<h2>Error</h2><pre>' . $err . '</pre>';
				} else {
						//echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
				}
		}
				
	 //print_r($result);
     return $result;

}
//////////////////////////////////////////////////generateSignedPurchaseToken/////////////////////////////////////////////////////////////

public function generateSignedPurchaseToken($params) 
	{

	
	$login = $params['login'];
    $signature = $params['signature'];
    $uniqueId = $params['uniqueId'] ;
    $resNum = $params['resNum'] ;	
    $amount = $params['amount'] ;	
    $redirectUrl = $params['redirectUrl'];	
	
	$client = new nusoap_client('https://pna.shaparak.ir/ref-payment/jax/merchantAuth?wsdl',true);
		
		$err = $client->getError();
		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
			exit();
		}

	
	$param = array('context' => array('data' => array('entry' => array('key' => 'SESSION_ID','value' => $login))),'signature' => $signature,
	'uniqueId' => $uniqueId,
	'resNum' => $resNum ,
	'amount' => $amount,
	'amountSpecified'=> true,
	'redirectUrl' => $redirectUrl);
	
	//print_r($params);
	
	
	$result = $client->call('generateSignedPurchaseToken', $param);
	
	if ($client->fault) {
				echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
		} else {
				$err = $client->getError();
				if ($err) {
						echo '<h2>Error</h2><pre>' . $err . '</pre>';
				} else {
						//echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
				}
		}
				
	 //print_r($result);
     return $result;
	 
	 }
//////////////////////////////////////////////////logout//////////////////////////////////////////////////
public function logout($login) 
	{

	$client = new nusoap_client('https://pna.shaparak.ir/ref-payment/jax/merchantAuth?wsdl',true);
		
		$err = $client->getError();
		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
			exit();
		}

	$params = array('context' => array('data' => array('entry' => array('key' => 'SESSION_ID','value' => $login))));
	
	
	$result = $client->call('logout', $params); //print_r($params);
	
	if ($client->fault) {
				echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
		} else {
				$err = $client->getError();
				if ($err) {
						echo '<h2>Error</h2><pre>' . $err . '</pre>';
				} else {
						//echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
				}
		}
				
	//print_r($result);
     return $result;

}
///////////////////////////////////////////////////reportTransaction//////////////////////////////////////////////////////

public function reportTransaction($login) 
	{


	$client = new nusoap_client('https://pna.shaparak.ir/ref-payment/jax/merchantAuth?wsdl',true);
		
		$err = $client->getError();
		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
			exit();
		}

	$params = array('context' => array('data' => array('entry' => array('key' => 'SESSION_ID','value' => $login))),'reportRequest' => array('onlyReversed' => 'false','offset' => '0', 'length' => '20'));
	

	$result = $client->call('reportTransaction', $params); print_r($params);
	
	if ($client->fault) {
				echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
		} else {
				$err = $client->getError();
				if ($err) {
						echo '<h2>Error</h2><pre>' . $err . '</pre>';
				} else {
						//echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
				}
		}
				
	 
	 
	// print_r($result);
     return $result;

}

////////////////////////////////////////////////////detailReportTransaction/////////////////////////////////////////////////////
public function detailReportTransaction($login) 
	{

	
		
	$client = new nusoap_client('https://pna.shaparak.ir/ref-payment/jax/merchantAuth?wsdl',true);
		
		$err = $client->getError();
		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
			exit();
		}

	$params = array('context' => array('data' => array('entry' => array('key' => 'SESSION_ID','value' => $login))),'detailReportRequest' => array('offset' => '0', 'length' => '20'));
	
	
	$result = $client->call('detailReportTransaction', $params); print_r($params);
	
	if ($client->fault) {
				echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
		} else {
				$err = $client->getError();
				if ($err) {
						echo '<h2>Error</h2><pre>' . $err . '</pre>';
				} else {
						//echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
				}
		}
				
	 
	// print_r($result);
     return $result;

}
/////////////////////////////////////////////////ReverseTrans////////////////////////////////////////////////////////

 public function ReverseTrans($params)
	{
	
	$login = $params['login'];
    $mainTransactionRefNum = $params['mainTransactionRefNum'];
    $reverseTransactionResNum = $params['reverseTransactionResNum'] ;
    $amount = $params['amount'] ;	
  
	
	    $client = new nusoap_client('https://pna.shaparak.ir/ref-payment/jax/merchantAuth?wsdl',true);

		$err = $client->getError();
		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
			exit();
		}
		
		$params = array('context' => array('data' => array('entry' => array('key' => 'SESSION_ID','value' => $login))),
	    'reverseRequest' => array('amount' => $amount,'mainTransactionRefNum' => $mainTransactionRefNum,'reverseTransactionResNum' => $reverseTransactionResNum));
	   

	   $result = $client->call('reverseTransaction', $params);
		
		if ($client->fault) {
				echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
		} else {
				$err = $client->getError();
				if ($err) {
						echo '<h2>Error</h2><pre>' . $err . '</pre>';
				} else {
						//echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
				}
		}

       return $result;
	
}	   
////////////////////////////////////////////////////////////////////////////////////////////////////////	   
}	   