<?php

require_once '../loan/request/request.data.php';
ini_set("display_errors", "On");

$dt = PdoDataAccess::runquery("select * from LON_payments p join LON_requests using(RequestID) 
			join LON_ReqParts rp on(rp.RequestID=p.RequestID AND isHistory='NO')
		 where StatusID=70 and ReqPersonID not in(1003,2051) 
		  and (DelayReturn != 'INSTALLMENT' OR AgentDelayReturn != 'INSTALLMENT')");
flush();
ob_flush();
foreach($dt as $row)
{
	echo $row["RequestID"] . " - " . $row["PayID"] . " : ";
	$PartObj = LON_ReqParts::GetValidPartObj($row["RequestID"]);
	$result = ComputeWagesAndDelays($PartObj, $row["PayAmount"], $PartObj->PartDate, $row["PayDate"]);
	$amount = 0;
	if($row["DelayReturn"] != "INSTALLMENT")
		PdoDataAccess::runquery("update LON_payments set OldFundDelayAmount=? where PayID=?", array(
			$result["TotalFundDelay"], $row["PayID"]));
	if($row["AgentDelayReturn"] != "INSTALLMENT")
		PdoDataAccess::runquery("update LON_payments set OldAgentDelayAmount=? where PayID=?", array(
			$result["TotalAgentDelay"], $row["PayID"]));
	
	print_r(ExceptionHandler::PopAllExceptions());
	echo "<br>";
	flush();
	ob_flush();
}
die();
?>