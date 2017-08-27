<?php

function ariana2_sendSMS($toNumber,$msgText,$type = 'number', &$SendError = "" ){
	
	$toList = array($toNumber);
	$msgList = array($msgText);
	
    $username = sms_config::$username;
    $password = sms_config::$password;
    $lineNumber = sms_config::$LineNumber;    
	
    require_once('nusoap.php');
    
    $soapclient = new nusoap_client(sms_config::$server,'wsdl');

    if ( (!$soapclient) || ($err = $soapclient->getError()) ) {
		$SendError = $err;
        return false;
	}
	$xmlNodes = '<Mobiles>';
	foreach ($toList as $n){
		$n='98'.$n;
		$xmlNodes .= "<string>{$n}</string>";
	}
	$xmlNodes .='</Mobiles><Messages>';
	foreach ($msgList as $m){
		$m=strip_tags($m);
		$m = toUTF8($m);
		$xmlNodes .= "<string>{$m}</string>";
	}
	$xmlNodes .='</Messages>';
	$xmlWrapper = "<Username>{$username}</Username><Password>{$password}</Password>".$xmlNodes;
	if($type=='name')
		$xmlWrapper.='<SendWithBusinessName>true</SendWithBusinessName>';
	if($type=='number')
		$xmlWrapper.="<LineNumber>{$lineNumber}</LineNumber>";

	$res = $soapclient->call('Send', '<Send xmlns="SMSPanelWebService">'.$xmlWrapper.'</Send>');

	$err = $soapclient->getError();
	if ($err) {
		$SendError = $err;
		return false;
	}
	
	if($res["SendResult"] == "1 messages inserted in send queue")
		return true;
	
	$SendError = $res["SendResult"];
	return false;
}

function toUTF8($str)
{
    $result = "";
    $unichar = "";
    for($i = 0 ; $i < mb_strlen($str,'UTF-8') ; $i++)
    {
        $char = mb_substr($str, $i, 1, 'UTF-8');
        if (mb_check_encoding($char, 'UTF-8')) {
            $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
            $unichar = hexdec(bin2hex($ret));
        } else {
            $unichar="0";
        }
        $result .= "&#".$unichar.";";
    }
    return $result;
}

?>
