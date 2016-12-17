<?php

function SendSms($textMessage, $toNumber){
	
	// turn off the WSDL cache
	ini_set("soap.wsdl_cache_enabled", "0");
	try {

		$user = sms_config::$username;
		$pass = sms_config::$password;

		$client = new SoapClient(sms_config::$send_server);

		$getcredit_parameters = array(
			"username"=>$user,
			"password"=>$pass
		);
		$credit = $client->GetCredit($getcredit_parameters)->GetCreditResult;
		echo "Credit: ".$credit."<br />";

		$encoding = "UTF-8";//CP1256, CP1252
		$textMessage = iconv($encoding, 'UTF-8//TRANSLIT',$textMessage);

		$sendsms_parameters = array(
			'username' => $user,
			'password' => $pass,
			'from' => "50001333837392",
			'to' => array($toNumber),
			'text' => $textMessage,
			'isflash' => false,
			'udh' => "",
			'recId' => array(0),
			'status' => 0
		);

		$status = $client->SendSms($sendsms_parameters)->SendSmsResult;
		echo "Status: ".$status."<br />";

		$incomingMessagesClient = new SoapClient(sms_config::$receive_server);
		$res = $incomingMessagesClient->GetNewMessagesList($sendsms_parameters);
print_r($res);
		echo "<table border=1>";
		echo "<th>MsgID</th><th>MsgType</th><th>Body</th><th>SendDate</th><th>Sender</th><th>Receiver</th><th>Parts</th><th>IsRead</th>";
		$row = $res->GetNewMessagesListResult->Message;
			echo "<tr>"
				."<td>".$row->MsgID."</td>"
				."<td>".$row->MsgType."</td>"
				."<td>".$row->Body."</td>"
				."<td>".$row->SendDate."</td>"
				."<td>".$row->Sender."</td>"
				."<td>".$row->Receiver."</td>"
				."<td>".$row->Parts."</td>"
				."<td>".$row->IsRead."</td>"
				."</tr>";
		echo "</table>";

	} catch (SoapFault $ex) {
		echo $ex->faultstring;
	}
}

?>
