<?php
include_once "../header.inc.php";
require_once 'request.class.php';
require_once 'request.data.php';

$temp = PdoDataAccess::runquery("select p.RequestID,PartID from LON_ReqParts p join LON_requests using(RequestID)
	where IsEnded='YES'");
foreach($temp as $row)
{
	$PartID = $row["PartID"];
	$dt = LON_installments::SelectAll("PartID=?" , array($PartID));
	$returnArr = ComputePayments($PartID, $dt);
	if(count($returnArr) > 0 && $returnArr[ count($returnArr)-1 ]["TotalRemainder"]*1 > 0)
	{
		PdoDataAccess::runquery("update LON_requests set IsEnded='NO', StatusID=70
			where requestID=?", array($row["RequestID"]));
		echo $row["RequestID"] . "<br>";
	}
}
die();
?>
