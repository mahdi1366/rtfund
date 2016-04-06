<?php
include_once "../header.inc.php";
require_once 'request.class.php';
require_once 'request.data.php';

$temp = PdoDataAccess::runquery("select RequestID,PartID from LON_ReqParts");
foreach($temp as $row)
{
	$PartID = $row["PartID"];
	$dt = LON_installments::SelectAll("PartID=?" , array($PartID));
	$returnArr = ComputePayments($PartID, $dt);
	if($returnArr[ count($returnArr)-1 ]["TotalRemainder"]*1 == 0)
	{
		PdoDataAccess::runquery("update LON_requests set IsEnded='YES' where requestID=?", array($row["RequestID"]));
		
	}
}
die();
?>
