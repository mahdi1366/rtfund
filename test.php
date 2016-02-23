<?php
require_once 'header.inc.php';

function get_data($url) {
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

echo get_data("http://82.99.224.135:9080/palayeshreport/reportWS/report.wsdl");
die();

	require_once('../libtejarat/nusoap.php');
    $ns = "http://sabapardazesh/reportWS/definitions";
    $wsdl2 = "http://82.99.224.135:9080/palayeshreport/reportWS/report.wsdl";
	
    $soapclient = new nusoap_client($wsdl2, '', '5.9.11.86', '81');
    $param = array();
    $param['accountNumber'] = "425273566";
    $param["dateFrom"] = "13940501";
	$param["timeFrom"] = "000000";
    $param["dateTo"] = "13940504";
    $param["timeTo"] = "235959";
    
    $param["paymentTypeId"] = 1;
    $param["bankPayerId"] = "2";
    $param["bankBranchCode"] = "";
	
    $result = $soapclient->call('reportRequest', $param, $ns);
	
	print_r($result);
?>
ALTER TABLE `rtfund`.`PLN_plans` ADD COLUMN `PlanDesc` VARCHAR(500) NOT NULL AFTER `PlanID`;

'' default ActDesc